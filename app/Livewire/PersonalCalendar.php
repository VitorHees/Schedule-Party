<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

class PersonalCalendar extends Component
{
    // --- State ---
    public $currentMonth;
    public $currentYear;
    public $selectedDate;

    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isUpdateModalOpen = false;
    public $isCreatingGroup = false;

    public $eventId = null;
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;
    public $editingInstanceDate = null;

    // --- Form ---
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

    public $selected_group_id = null;

    #[Validate('required_if:isCreatingGroup,true|min:3')]
    public $new_group_name = '';
    public $new_group_color = '#A855F7';

    public $colors = [
        '#EF4444', '#F97316', '#F59E0B', '#10B981', '#06B6D4',
        '#3B82F6', '#6366F1', '#A855F7', '#EC4899', '#64748B',
    ];

    protected $listeners = ['open-create-event-modal' => 'openModal'];

    public function mount()
    {
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->start_date = Carbon::now()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    // --- Modal Logic ---

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

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->isUpdateModalOpen = false;
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
        $this->selected_group_id = null;
        $this->isCreatingGroup = false;
        $this->new_group_name = '';
        $this->new_group_color = '#A855F7';
        $this->resetValidation();
    }

    public function toggleCreateGroup()
    {
        $this->isCreatingGroup = !$this->isCreatingGroup;
    }

    public function selectGroup($groupId)
    {
        $this->selected_group_id = $this->selected_group_id === $groupId ? null : $groupId;
        $this->isCreatingGroup = false;
    }

    public function saveGroup()
    {
        $this->validate([
            'new_group_name' => 'required|min:2|max:50',
            'new_group_color' => 'required',
        ]);

        $group = Group::create([
            'calendar_id' => 1,
            'name' => $this->new_group_name,
            'color' => $this->new_group_color,
        ]);

        $group->users()->attach(Auth::id(), ['assigned_at' => now()]);
        $this->selected_group_id = $group->id;
        $this->isCreatingGroup = false;
        $this->new_group_name = '';
    }

    public function deleteSelectedGroup()
    {
        if ($this->selected_group_id) {
            $group = Group::find($this->selected_group_id);
            if ($group) {
                $group->delete();
                $this->selected_group_id = null;
            }
        }
    }

    // --- CRUD Logic ---

    public function editEvent($id, $instanceDate = null)
    {
        $event = Event::find($id);
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
        $this->selected_group_id = $event->groups()->first()?->id;

        if ($instanceDate) {
            $this->start_date = $instanceDate;
            $duration = $event->start_date->diffInDays($event->end_date);
            $this->end_date = Carbon::parse($instanceDate)->addDays($duration)->format('Y-m-d');
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

        if ($this->eventId) {
            $event = Event::find($this->eventId);
            if ($event->repeat_frequency !== 'none') {
                $this->isUpdateModalOpen = true;
                return;
            }
            $this->performUpdate($event);
        } else {
            $this->performCreate();
        }

        $this->isModalOpen = false;
    }

    public function performUpdate($event)
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        $event->update([
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
        ]);

        $event->groups()->sync($this->selected_group_id ? [$this->selected_group_id] : []);
    }

    public function performCreate()
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        if ($this->start_date === $this->end_date && $endDateTime->lt($startDateTime)) {
            $this->addError('end_time', 'End time error.');
            return;
        }

        $event = Event::create([
            'calendar_id' => 1,
            'created_by' => Auth::id(),
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'series_id' => Str::uuid()->toString(), // Always generate ID for new events
            'images' => [],
        ]);

        if ($this->selected_group_id) {
            $event->groups()->attach($this->selected_group_id);
        }
    }

    public function confirmUpdate($mode)
    {
        $event = Event::find($this->eventId);
        if (!$event) {
            $this->closeModal();
            return;
        }

        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        if ($mode === 'instance') {
            // Exclude from old series
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->editingInstanceDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);

            // Create new detached event
            $newEvent = $event->replicate();
            $newEvent->name = $this->title;
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = 'none';
            $newEvent->series_id = Str::uuid()->toString(); // New unique ID
            $newEvent->images = [];
            $newEvent->push();

            if ($this->selected_group_id) $newEvent->groups()->sync([$this->selected_group_id]);

        } elseif ($mode === 'future') {
            // FIX: If original event has no series_id (e.g. from seeder), generate one now!
            if (!$event->series_id) {
                $event->series_id = Str::uuid()->toString();
                $event->saveQuietly();
            }
            $commonSeriesId = $event->series_id;

            $originalEndDate = $event->repeat_end_date;

            // 1. Stop original series YESTERDAY
            $stopDate = Carbon::parse($this->editingInstanceDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);

            // 2. Start NEW Series TODAY (linked by same series_id)
            $newEvent = $event->replicate();
            $newEvent->name = $this->title;
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = $this->repeat_frequency;
            $newEvent->repeat_end_date = $originalEndDate;
            $newEvent->series_id = $commonSeriesId; // <--- Critical: Links the new event to the old one
            $newEvent->images = [];
            $newEvent->push();

            if ($this->selected_group_id) $newEvent->groups()->sync([$this->selected_group_id]);
        }

        $this->closeModal();
        $this->dispatch('event-updated');
    }

    // --- Deletion Logic ---

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
        $event = Event::find($this->eventToDeleteId);
        if (!$event) {
            $this->closeModal();
            return;
        }

        if ($mode === 'single' || ($mode === 'future' && $event->start_date->format('Y-m-d') === $this->eventToDeleteDate)) {
            if ($mode === 'future') {
                $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
            }
            $event->delete();
        }
        elseif ($mode === 'future') {
            $stopDate = Carbon::parse($this->eventToDeleteDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);
            $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate);
        }
        elseif ($mode === 'instance') {
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
        // If event has no series_id, it has no linked branches to delete.
        if (!$originalEvent->series_id) return;

        $relatedEvents = Event::where('series_id', $originalEvent->series_id)
            ->where('id', '!=', $originalEvent->id)
            ->get();

        foreach ($relatedEvents as $relEvent) {
            if ($relEvent->start_date->format('Y-m-d') >= $cutoffDate) {
                $relEvent->delete();
            }
        }
    }

    // --- Data Fetching ---

    public function getGroupsProperty()
    {
        return Group::all();
    }

    public function getEventsProperty()
    {
        $viewStart = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subDays(7);
        $viewEnd = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addDays(14);

        $rawEvents = Event::with('groups')
            ->where(function($q) use ($viewStart, $viewEnd) {
                $q->whereBetween('start_date', [$viewStart, $viewEnd])
                    ->orWhere('repeat_frequency', '!=', 'none');
            })->get();

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
                        $instance->end_date = $currentDate->copy()->setTimeFrom($event->end_date);

                        $duration = $event->start_date->diffInDays($event->end_date);
                        if ($duration > 0) $instance->end_date->addDays($duration);

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

    public function selectDate($date) { $this->selectedDate = $date; }
    public function nextMonth() {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month; $this->currentYear = $date->year;
    }
    public function previousMonth() {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month; $this->currentYear = $date->year;
    }
    public function goToToday() {
        $now = Carbon::now();
        $this->currentMonth = $now->month; $this->currentYear = $now->year;
        $this->selectedDate = $now->format('Y-m-d');
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

        return view('livewire.personal-calendar', [
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'monthName' => $date->format('F'),
            'eventsByDate' => $eventsByDate,
            'calendarDate' => $date,
        ]);
    }
}
