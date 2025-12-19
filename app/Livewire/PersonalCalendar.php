<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // Required for OpenStreetMap
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class PersonalCalendar extends Component
{
    use WithFileUploads;

    public Calendar $calendar;

    // --- State & Navigation ---
    public $currentMonth;
    public $currentYear;
    public $selectedDate;

    // --- Modal Visibility ---
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isUpdateModalOpen = false;
    public $isManageGroupsModalOpen = false;

    // --- Export State ---
    public $showExportModal = false;
    public $exportTargetCalendarId = null;
    public $exportMode = 'all'; // 'all', 'label', 'single'
    public $exportFormat = 'calendar'; // 'calendar', 'excel', 'pdf'
    public $exportLabelId = null;
    public $exportWithLabel = false;
    public $exportEventId = null;

    // --- Event State ---
    public $eventId = null;
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;
    public $editingInstanceDate = null;

    // --- Group Management ---
    public $group_name = '';
    public $group_color = '#A855F7'; // Default purple
    public $group_is_selectable = true;

    // --- Event Form Fields ---
    #[Validate('required|min:3')]
    public $title = '';

    #[Validate('required|date')]
    public $start_date = '';

    #[Validate('required')]
    public $start_time = '10:00';

    #[Validate('required|date|after_or_equal:start_date')]
    public $end_date = '';

    #[Validate('required')]
    public $end_time = '11:00';

    public $is_all_day = false;
    public $location = '';
    public $url = '';
    public $description = '';

    public $repeat_frequency = 'none';
    public $repeat_end_date = null;

    // --- File Upload Logic (Buffer System) ---

    // 1. Accumulator for accepted files
    public $photos = [];

    // 2. Buffer for new input (bound to wire:model)
    #[Validate(['temp_photos.*' => 'file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip|max:10240'])]
    public $temp_photos = [];

    // 3. ID to force input reset
    public $uploadIteration = 0;

    public $existing_images = [];

    // --- Selection / Filters ---
    public $selected_group_ids = [];
    public $filter_group_ids = [];

    protected $listeners = ['open-create-event-modal' => 'openModal'];

    // --- FILE UPLOAD HOOK ---

    public function updatedTempPhotos()
    {
        $this->validate([
            'temp_photos.*' => 'file|mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip|max:10240'
        ]);

        foreach ($this->temp_photos as $photo) {
            $this->photos[] = $photo;
        }

        // Reset buffer and increment iteration to clear the file input
        $this->temp_photos = [];
        $this->uploadIteration++;
    }

    // --- LIFECYCLE ---

    public function mount()
    {
        $user = Auth::user();

        $calendar = Calendar::where('type', 'personal')
            ->whereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->first();

        if (!$calendar) {
            $calendar = Calendar::create([
                'name' => 'My Personal Calendar',
                'type' => 'personal',
            ]);
            $ownerRole = \App\Models\Role::where('slug', 'owner')->first();
            $roleId = $ownerRole ? $ownerRole->id : 1;
            $calendar->users()->attach($user->id, ['role_id' => $roleId, 'joined_at' => now()]);
        }

        $this->calendar = $calendar;

        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->start_date = Carbon::now()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    // --- PROPERTIES ---

    public function getAvailableGroupsProperty()
    {
        return $this->calendar->groups()->orderBy('name')->get();
    }

    public function getEventsProperty()
    {
        $viewStart = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subDays(7);
        $viewEnd = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addDays(14);

        $query = $this->calendar->events()
            ->with('groups')
            ->where(function($q) use ($viewStart, $viewEnd) {
                $q->whereBetween('start_date', [$viewStart, $viewEnd])
                    ->orWhere('repeat_frequency', '!=', 'none');
            });

        if (!empty($this->filter_group_ids)) {
            $query->whereHas('groups', function($q) {
                $q->whereIn('groups.id', $this->filter_group_ids);
            });
        }

        $rawEvents = $query->get();
        $processedEvents = collect();

        foreach ($rawEvents as $event) {
            $exclusions = $event->images['excluded_dates'] ?? [];

            if ($event->repeat_frequency === 'none') {
                if ($event->start_date->lt($viewEnd) && $event->end_date->gt($viewStart)) {
                    if (!in_array($event->start_date->format('Y-m-d'), $exclusions)) {
                        $processedEvents->push($event);
                    }
                }
                continue;
            }

            $eventDuration = $event->start_date->diff($event->end_date);
            $currentDate = Carbon::parse($event->start_date);

            while ($currentDate->lte($viewEnd)) {
                if ($event->repeat_end_date && $currentDate->format('Y-m-d') > $event->repeat_end_date->format('Y-m-d')) {
                    break;
                }

                if ($currentDate->gte($viewStart)) {
                    $dateString = $currentDate->format('Y-m-d');
                    if (!in_array($dateString, $exclusions)) {
                        $instance = clone $event;
                        $instance->id = $event->id;
                        $instance->start_date = $currentDate->copy()->setTimeFrom($event->start_date);
                        $instance->end_date = $instance->start_date->copy()->add($eventDuration);
                        $processedEvents->push($instance);
                    }
                }

                switch ($event->repeat_frequency) {
                    case 'daily':   $currentDate->addDay(); break;
                    case 'weekly':  $currentDate->addWeek(); break;
                    case 'monthly': $currentDate->addMonth(); break;
                    case 'yearly':  $currentDate->addYear(); break;
                    default: break 2;
                }
            }
        }
        return $processedEvents->sortBy('start_date');
    }

    public function getSelectedDateEventsProperty()
    {
        return $this->events->filter(function($event) {
            $checkDate = Carbon::parse($this->selectedDate);
            return $checkDate->between(
                Carbon::parse($event->start_date)->startOfDay(),
                Carbon::parse($event->end_date)->endOfDay()
            );
        });
    }

    public function getDurationInDaysProperty()
    {
        return Carbon::parse($this->start_date)->startOfDay()
            ->diffInDays(Carbon::parse($this->end_date)->startOfDay());
    }

    // --- NAVIGATION ---

    public function setMonth($month) { $this->currentMonth = $month; }
    public function setYear($year) { $this->currentYear = $year; }
    public function selectDate($date) { $this->selectedDate = $date; }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function goToToday()
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
        $this->selectedDate = $now->format('Y-m-d');
    }

    // --- MODAL MANAGEMENT ---

    public function openModal($date = null)
    {
        $this->resetForm();
        if ($date) {
            $this->selectedDate = $date;
            $this->start_date = $date;
            $this->end_date = $date;
        }
        $this->isModalOpen = true;
    }

    public function openManageGroupsModal()
    {
        $this->reset('group_name', 'group_color');
        $this->isManageGroupsModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->isUpdateModalOpen = false;
        $this->isManageGroupsModalOpen = false;
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->eventId = null;
        $this->editingInstanceDate = null;
        $this->title = '';
        $this->start_time = '10:00';
        $this->end_time = '11:00';
        $this->is_all_day = false;
        $this->location = '';
        $this->url = '';
        $this->description = '';
        $this->repeat_frequency = 'none';
        $this->repeat_end_date = null;
        $this->photos = [];
        $this->temp_photos = []; // Reset buffer
        $this->existing_images = [];
        $this->selected_group_ids = [];
        $this->resetValidation();
    }

    // --- EXPORT LOGIC ---

    public function openExportModal($eventId = null)
    {
        $this->reset(['exportTargetCalendarId', 'exportMode', 'exportLabelId', 'exportWithLabel', 'exportEventId', 'exportFormat']);

        if ($eventId) {
            $this->exportEventId = $eventId;
            $this->exportMode = 'single';
        } else {
            $this->exportMode = 'all';
        }

        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    protected function getEventsForExport()
    {
        if ($this->exportMode === 'single') {
            $event = $this->calendar->events()->with('groups')->find($this->exportEventId);
            return $event ? collect([$event]) : collect();
        }

        if ($this->exportMode === 'all') {
            return $this->calendar->events()->with('groups')->get();
        }

        if ($this->exportMode === 'label') {
            return $this->calendar->events()
                ->with('groups')
                ->whereHas('groups', function ($q) {
                    $q->where('groups.id', $this->exportLabelId);
                })
                ->get();
        }

        return collect();
    }

    public function exportEvents()
    {
        $rules = [
            'exportMode' => 'required|in:all,label,single',
            'exportFormat' => 'required|in:calendar,excel,pdf',
            'exportLabelId' => 'required_if:exportMode,label',
        ];

        if ($this->exportFormat === 'calendar') {
            $rules['exportTargetCalendarId'] = 'required|exists:calendars,id';
        }

        $this->validate($rules);

        $events = $this->getEventsForExport();

        if ($events->isEmpty()) {
            $this->addError('exportMode', 'No events found to export.');
            return;
        }

        // --- HANDLE FILE DOWNLOADS ---

        if ($this->exportFormat === 'excel') {
            $this->showExportModal = false;
            return Excel::download(new EventsExport($events), 'my-events.xlsx');
        }

        if ($this->exportFormat === 'pdf') {
            $this->showExportModal = false;
            $pdf = Pdf::loadView('exports.events-pdf', ['events' => $events]);
            return response()->streamDownload(
                fn () => print($pdf->output()),
                'my-events.pdf'
            );
        }

        // --- HANDLE CALENDAR TRANSFER ---

        $user = Auth::user();
        $targetCalendar = $user->calendars()
            ->where('calendars.id', $this->exportTargetCalendarId)
            ->where('type', 'collaborative')
            ->firstOrFail();

        $exportedCount = 0;
        $skippedCount = 0;

        foreach ($events as $event) {
            if ($this->replicateEvent($event, $targetCalendar, $this->exportWithLabel)) {
                $exportedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->showExportModal = false;

        $message = "{$exportedCount} event(s) exported.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} skipped as they already exist)";
        }

        $this->dispatch('action-message', message: $message);
    }

    protected function replicateEvent(Event $event, Calendar $targetCalendar, bool $withLabels): bool
    {
        $exists = Event::where('calendar_id', $targetCalendar->id)
            ->where('series_id', $event->series_id)
            ->where('start_date', $event->start_date)
            ->exists();

        if ($exists) {
            return false;
        }

        $user = Auth::user();

        $newEvent = $event->replicate(['id', 'calendar_id', 'created_by', 'created_at', 'updated_at']);
        $newEvent->calendar_id = $targetCalendar->id;
        $newEvent->created_by = $user->id;
        $newEvent->save();

        if ($withLabels) {
            $sourceGroups = $event->groups;
            $targetGroupIds = [];

            foreach ($sourceGroups as $sourceGroup) {
                $targetGroup = $targetCalendar->groups()->firstOrCreate(
                    ['name' => $sourceGroup->name],
                    [
                        'color' => $sourceGroup->color,
                        'is_selectable' => true,
                    ]
                );
                $targetGroupIds[] = $targetGroup->id;
            }

            if (!empty($targetGroupIds)) {
                $newEvent->groups()->attach($targetGroupIds);
            }
        }

        return true;
    }

    // --- CRUD OPERATIONS ---

    public function createGroup()
    {
        $this->validate([
            'group_name' => 'required|min:2|max:30',
            'group_color' => 'required',
        ]);
        $this->calendar->groups()->create([
            'name' => $this->group_name,
            'color' => $this->group_color,
            'is_selectable' => false,
        ]);
        $this->reset('group_name', 'group_color');
    }

    public function deleteGroup($groupId)
    {
        $group = $this->calendar->groups()->find($groupId);
        if ($group) {
            $group->delete();
        }
    }

    public function removeExistingImage($index)
    {
        unset($this->existing_images[$index]);
        $this->existing_images = array_values($this->existing_images);
    }

    public function removePhoto($index)
    {
        array_splice($this->photos, $index, 1);
    }

    private function handleImageUploads()
    {
        $urls = $this->existing_images;
        // Loop through Accumulator, NOT Temp Buffer
        foreach ($this->photos as $photo) {
            $path = $photo->store('events', 'public');
            $urls[] = '/storage/' . $path;
        }
        return $urls;
    }

    // --- GEOCODING ---

    public function geocodeLocation($address)
    {
        if (empty($address)) return null;

        try {
            $email = 'your-real-email@gmail.com';

            $response = Http::withHeaders([
                'User-Agent' => 'SchedulePartyApp/1.0 (' . $email . ')',
                'Referer'    => config('app.url')
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/search", [
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                return [$data['lon'], $data['lat']];
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    // --- EVENT CRUD ---

    public function editEvent($id, $instanceDate = null)
    {
        $event = $this->calendar->events()->find($id);
        if (!$event) return;

        $this->resetForm();
        $this->eventId = $event->id;
        $this->editingInstanceDate = $instanceDate ?? $event->start_date->format('Y-m-d');

        $this->title = $event->name;
        $this->description = $event->description;
        $this->location = $event->location;
        $this->url = $event->url;
        $this->is_all_day = $event->is_all_day;
        $this->repeat_frequency = $event->repeat_frequency;
        $this->repeat_end_date = $event->repeat_end_date ? $event->repeat_end_date->format('Y-m-d') : null;
        $this->existing_images = $event->images['urls'] ?? [];
        $this->selected_group_ids = $event->groups()->pluck('groups.id')->toArray();

        if ($instanceDate) {
            $this->start_date = $instanceDate;
            $duration = $event->start_date->diff($event->end_date);
            $this->end_date = Carbon::parse($instanceDate)->add($duration)->format('Y-m-d');
        } else {
            $this->start_date = $event->start_date->format('Y-m-d');
            $this->end_date = $event->end_date->format('Y-m-d');
        }
        $this->start_time = $event->start_date->format('H:i');
        $this->end_time = $event->end_date->format('H:i');
        $this->isModalOpen = true;
    }

    public function saveEvent()
    {
        $this->validate();

        $days = $this->durationInDays;
        if ($this->repeat_frequency === 'daily' && $days >= 1) {
            $this->addError('repeat_frequency', 'Event spans multiple days; cannot repeat daily.');
            return;
        }
        if ($this->repeat_frequency === 'weekly' && $days >= 7) {
            $this->addError('repeat_frequency', 'Event spans over a week; cannot repeat weekly.');
            return;
        }
        if ($this->repeat_frequency === 'monthly' && $days >= 28) {
            $this->addError('repeat_frequency', 'Event spans over a month; cannot repeat monthly.');
            return;
        }

        if ($this->eventId) {
            $event = $this->calendar->events()->find($this->eventId);
            if ($event->repeat_frequency !== 'none') {
                $this->isUpdateModalOpen = true;
                return;
            }
            $this->performUpdate($event);
        } else {
            $this->performCreate();
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->isModalOpen = false;
    }

    public function performCreate()
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        if ($this->start_date === $this->end_date && $endDateTime->lt($startDateTime)) {
            $this->addError('end_time', 'End time cannot be before start time.');
            return;
        }

        $imagesPayload = ['urls' => $this->handleImageUploads()];

        // --- Geocoding ---
        $lat = null;
        $lng = null;
        if ($this->location) {
            $coords = $this->geocodeLocation($this->location);
            if ($coords) {
                $lng = $coords[0];
                $lat = $coords[1];
            }
        }

        $event = Event::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'latitude' => $lat,
            'longitude' => $lng,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'repeat_end_date' => $this->repeat_frequency !== 'none' ? $this->repeat_end_date : null,
            'series_id' => Str::uuid()->toString(),
            'images' => $imagesPayload,
            'comments_enabled' => false,
        ]);

        if (!empty($this->selected_group_ids)) {
            $event->groups()->attach($this->selected_group_ids);
        }
    }

    public function performUpdate($event)
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $currentImages = $event->images ?? [];
        $currentImages['urls'] = $this->handleImageUploads();

        // --- Geocoding ---
        $lat = $event->latitude;
        $lng = $event->longitude;

        if ($this->location !== $event->location || (!$lat && $this->location)) {
            $coords = $this->geocodeLocation($this->location);
            if ($coords) {
                $lng = $coords[0];
                $lat = $coords[1];
            }
        }

        $event->update([
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'latitude' => $lat,
            'longitude' => $lng,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'repeat_end_date' => $this->repeat_frequency !== 'none' ? $this->repeat_end_date : null,
            'images' => $currentImages,
            'comments_enabled' => false,
        ]);

        $event->groups()->sync($this->selected_group_ids);
    }

    public function confirmUpdate($mode)
    {
        $event = $this->calendar->events()->find($this->eventId);
        if (!$event) {
            $this->closeModal();
            return;
        }

        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);
        $newImages = ['urls' => $this->handleImageUploads()];

        if (!$event->series_id) {
            $event->series_id = Str::uuid()->toString();
            $event->saveQuietly();
        }

        // --- Geocoding ---
        $lat = $event->latitude;
        $lng = $event->longitude;
        if ($this->location !== $event->location || (!$lat && $this->location)) {
            $coords = $this->geocodeLocation($this->location);
            if ($coords) {
                $lng = $coords[0];
                $lat = $coords[1];
            }
        }

        if ($mode === 'instance') {
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->editingInstanceDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);

            $newEvent = $event->replicate();
            $newEvent->name = $this->title;
            $newEvent->description = $this->description;
            $newEvent->location = $this->location;
            $newEvent->latitude = $lat;
            $newEvent->longitude = $lng;
            $newEvent->url = $this->url;
            $newEvent->is_all_day = $this->is_all_day;
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = 'none';
            $newEvent->repeat_end_date = null;
            $newEvent->series_id = $event->series_id;
            $newEvent->images = $newImages;
            $newEvent->comments_enabled = false;
            $newEvent->push();
            $newEvent->groups()->sync($this->selected_group_ids);

        } elseif ($mode === 'future') {
            $commonSeriesId = $event->series_id;
            $originalEndDate = $event->repeat_end_date;
            $stopDate = Carbon::parse($this->editingInstanceDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);

            $newEvent = $event->replicate();
            $newEvent->name = $this->title;
            $newEvent->description = $this->description;
            $newEvent->location = $this->location;
            $newEvent->latitude = $lat;
            $newEvent->longitude = $lng;
            $newEvent->url = $this->url;
            $newEvent->is_all_day = $this->is_all_day;
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = $this->repeat_frequency;
            $newEvent->repeat_end_date = $this->repeat_frequency !== 'none' ? $this->repeat_end_date : $originalEndDate;
            $newEvent->series_id = $commonSeriesId;
            $newEvent->images = $newImages;
            $newEvent->comments_enabled = false;
            $newEvent->push();
            $newEvent->groups()->sync($this->selected_group_ids);
        }

        $this->closeModal();
        $this->dispatch('event-updated');
    }

    public function promptDeleteEvent($eventId, $date, $isRepeating)
    {
        $this->eventToDeleteId = $eventId;
        $this->eventToDeleteDate = $date;
        $this->eventToDeleteIsRepeating = $isRepeating;

        if ($isRepeating) {
            $this->isDeleteModalOpen = true;
        } else {
            $this->confirmDelete('single');
        }
    }

    public function confirmDelete($mode)
    {
        $event = $this->calendar->events()->find($this->eventToDeleteId);
        if (!$event) {
            $this->closeModal();
            return;
        }

        if ($mode === 'single' || ($mode === 'future' && $event->start_date->format('Y-m-d') === $this->eventToDeleteDate)) {
            if ($mode === 'future') {
                $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
            }
            $event->delete();
        } elseif ($mode === 'future') {
            $stopDate = Carbon::parse($this->eventToDeleteDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);
            $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
        } elseif ($mode === 'instance') {
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->eventToDeleteDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);
        }

        $this->closeModal();
        $this->dispatch('event-deleted');
    }

    public function deleteBranchedFutureEvents($originalEvent, $cutoffDate)
    {
        if (!$originalEvent->series_id) return;

        $relatedEvents = $this->calendar->events()
            ->where('series_id', $originalEvent->series_id)
            ->where('id', '!=', $originalEvent->id)
            ->get();

        foreach ($relatedEvents as $relEvent) {
            if ($relEvent->start_date->format('Y-m-d') >= $cutoffDate) {
                $relEvent->delete();
            }
        }
    }

    public function render()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $date->daysInMonth;
        $firstDayOfWeek = $date->dayOfWeek;

        $eventsByDate = collect();
        foreach ($this->events as $event) {
            $start = Carbon::parse($event->start_date)->startOfDay();
            $end = Carbon::parse($event->end_date)->endOfDay();
            $current = $start->copy();

            while ($current->lte($end)) {
                $dateKey = $current->format('Y-m-d');
                if (!$eventsByDate->has($dateKey)) $eventsByDate->put($dateKey, collect());
                $eventsByDate[$dateKey]->push($event);
                $current->addDay();
            }
        }

        // Pass Collaborative Calendars for the Export Dropdown
        $allCollaborativeCalendars = Auth::user()->calendars()
            ->where('type', 'collaborative')
            ->orderBy('name')
            ->get()
            ->filter(function ($calendar) {
                return Auth::user()->hasPermissionInCalendar($calendar, 'import_personal_calendar');
            });

        return view('livewire.personal-calendar', [
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'monthName' => $date->format('F'),
            'eventsByDate' => $eventsByDate,
            'calendarDate' => $date,
            'allCollaborativeCalendars' => $allCollaborativeCalendars,
        ])->title('Personal Calendar');
    }
}
