<?php

namespace App\Livewire;

use App\Livewire\ManagesCalendarGroups;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Gender;
use App\Models\Country;
use App\Models\Zipcode; // Ensure this is imported
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

class SharedCalendar extends Component
{
    use WithFileUploads, ManagesCalendarGroups;

    public Calendar $calendar;

    // --- Navigation State ---
    public $currentMonth;
    public $currentYear;
    public $selectedDate;

    // --- Modal Visibility ---
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isUpdateModalOpen = false;
    public $isInviteModalOpen = false;
    public $isLeaveCalendarModalOpen = false;
    public $isDeleteCalendarModalOpen = false;

    // --- Invite State ---
    public $inviteLink = null;
    public $inviteUsername = '';
    public $inviteEmail = '';
    public $inviteRole = 'regular';

    // --- Delete Calendar State ---
    public $deleteCalendarPassword = '';

    // --- Event Management State ---
    public $eventId = null;
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;
    public $editingInstanceDate = null;

    // --- Form Fields ---
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
    #[Validate(['photos.*' => 'image|max:10240'])]
    public $photos = [];
    public $existing_images = [];

    // --- Advanced Filter Fields ---
    public $selected_group_ids = [];
    public $is_role_restricted = true;
    public $selected_gender_ids = [];
    public $min_age = null;
    public $max_distance_km = null;
    public $event_zipcode = '';
    public $event_country_id = null;
    public $is_nsfw = false;

    protected $listeners = ['open-create-event-modal' => 'openModal'];

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
        $user = Auth::user();

        $isMember = $user && $this->calendar->users->contains($user->id);
        $guestToken = request()->cookie('guest_access_' . $calendar->id);
        $isGuest = $guestToken && $this->calendar->calendarUsers()
                ->where('guest_token', $guestToken)
                ->exists();

        if (!$isMember && !$isGuest) {
            abort(403, 'Access denied.');
        }

        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->start_date = Carbon::now()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    // --- DATA & FILTERING ---

    public function getEventsProperty()
    {
        $viewStart = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subDays(7);
        $viewEnd = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addDays(14);

        $user = Auth::user();
        $userRoleIds = $this->userRoleIds; // From Trait

        $rawEvents = $this->calendar->events()
            ->with(['groups', 'genders', 'country'])
            ->where(function($query) use ($viewStart, $viewEnd) {
                $query->whereBetween('start_date', [$viewStart, $viewEnd])
                    ->orWhere('repeat_frequency', '!=', 'none');
            })
            ->get();

        // --- APPLY FILTERS ---
        $filteredEvents = $rawEvents->filter(function($event) use ($user, $userRoleIds) {
            if ($this->isOwner) return true; // Owner sees all

            // 1. Gender Filter
            if ($event->genders->isNotEmpty()) {
                if (!$user->gender_id || !$event->genders->contains('id', $user->gender_id)) {
                    return false;
                }
            }

            // 2. Age Filter
            if ($event->min_age) {
                if (!$user->birth_date || $user->birth_date->age < $event->min_age) {
                    return false;
                }
            }

            // 3. Role Visibility
            if ($event->groups->isNotEmpty() && $event->is_role_restricted) {
                if ($event->groups->pluck('id')->intersect($userRoleIds)->isEmpty()) {
                    return false;
                }
            }

            // 4. Distance Filter (Using your Model's logic!)
            if ($event->max_distance_km && $event->event_zipcode) {
                if (!$user->zipcode) return false; // User location unknown

                // Find event zipcode
                $eventZip = Zipcode::where('code', $event->event_zipcode)->first();
                if (!$eventZip) return false; // Event location unknown/invalid

                // Use the distanceTo method from your model
                $distance = $user->zipcode->distanceTo($eventZip);

                // If distance is null (coords missing) or too far, hide it
                if (is_null($distance) || $distance > $event->max_distance_km) {
                    return false;
                }
            }

            return true;
        });

        // Expand Repeating Events (Standard Logic)
        $processedEvents = collect();
        foreach ($filteredEvents as $event) {
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
                if ($event->repeat_end_date && $currentDate->format('Y-m-d') > $event->repeat_end_date->format('Y-m-d')) break;

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
                    case 'daily': $currentDate->addDay(); break;
                    case 'weekly': $currentDate->addWeek(); break;
                    case 'monthly': $currentDate->addMonth(); break;
                    case 'yearly': $currentDate->addYear(); break;
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

    // --- HELPERS (View) ---
    public function getGendersProperty() { return Gender::all(); }
    public function getCountriesProperty() { return Country::all(); }

    // --- EVENT CRUD (Updated save/edit/create/update) ---

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

    public function editEvent($id, $instanceDate = null)
    {
        $event = $this->calendar->events()->with(['groups', 'genders'])->find($id);
        if (!$event) return;

        $this->resetForm();
        $this->eventId = $event->id;
        $this->editingInstanceDate = $instanceDate ?? $event->start_date->format('Y-m-d');

        // Hydrate Basic Fields
        $this->title = $event->name;
        $this->description = $event->description;
        $this->location = $event->location;
        $this->url = $event->url;
        $this->is_all_day = $event->is_all_day;
        $this->repeat_frequency = $event->repeat_frequency;
        $this->repeat_end_date = $event->repeat_end_date ? $event->repeat_end_date->format('Y-m-d') : null;
        $this->existing_images = $event->images['urls'] ?? [];

        // Hydrate Advanced Filters
        $this->selected_group_ids = $event->groups->pluck('id')->toArray();
        $this->selected_gender_ids = $event->genders->pluck('id')->toArray();
        $this->is_role_restricted = $event->is_role_restricted;
        $this->min_age = $event->min_age;
        $this->max_distance_km = $event->max_distance_km;
        $this->event_zipcode = $event->event_zipcode;
        $this->event_country_id = $event->event_country_id;
        $this->is_nsfw = $event->is_nsfw ?? false;

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

    public function updatedIsNsfw()
    {
        if ($this->is_nsfw) {
            if (!$this->min_age || $this->min_age < 18) {
                $this->min_age = 18;
            }
        }
    }

    public function saveEvent()
    {
        $this->validate();

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

        $this->isModalOpen = false;
    }

    public function performUpdate($event)
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);
        $currentImages = $event->images ?? [];
        $currentImages['urls'] = $this->handleImageUploads();

        $event->update([
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'repeat_end_date' => $this->repeat_frequency !== 'none' ? $this->repeat_end_date : null,
            'images' => $currentImages,
            // Advanced Filters
            'min_age' => $this->min_age,
            'max_distance_km' => $this->max_distance_km,
            'event_zipcode' => $this->event_zipcode,
            'event_country_id' => $this->event_country_id,
            'is_role_restricted' => $this->is_role_restricted,
            'is_nsfw' => $this->is_nsfw,
        ]);

        $event->groups()->sync($this->selected_group_ids);
        $event->genders()->sync($this->selected_gender_ids);
    }

    public function performCreate()
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);
        $imagesPayload = ['urls' => $this->handleImageUploads()];

        $event = Event::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'name' => $this->title,
            'description' => $this->description,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'url' => $this->url,
            'repeat_frequency' => $this->repeat_frequency,
            'repeat_end_date' => $this->repeat_frequency !== 'none' ? $this->repeat_end_date : null,
            'series_id' => Str::uuid()->toString(),
            'images' => $imagesPayload,
            // Advanced Filters
            'min_age' => $this->min_age,
            'max_distance_km' => $this->max_distance_km,
            'event_zipcode' => $this->event_zipcode,
            'event_country_id' => $this->event_country_id,
            'is_role_restricted' => $this->is_role_restricted,
            'is_nsfw' => $this->is_nsfw,
        ]);

        if (!empty($this->selected_group_ids)) $event->groups()->attach($this->selected_group_ids);
        if (!empty($this->selected_gender_ids)) $event->genders()->attach($this->selected_gender_ids);
    }

    public function confirmUpdate($mode)
    {
        $event = $this->calendar->events()->find($this->eventId);
        if (!$event) { $this->closeModal(); return; }

        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);
        $newImages = ['urls' => $this->handleImageUploads()];

        $replData = [
            'name' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'is_all_day' => $this->is_all_day,
            'min_age' => $this->min_age,
            'max_distance_km' => $this->max_distance_km,
            'event_zipcode' => $this->event_zipcode,
            'event_country_id' => $this->event_country_id,
            'is_role_restricted' => $this->is_role_restricted,
            'is_nsfw' => $this->is_nsfw,
            'images' => $newImages,
        ];

        if ($mode === 'instance') {
            // Update original to exclude this date
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->editingInstanceDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);

            // Create new single event
            $newEvent = $event->replicate();
            $newEvent->fill($replData);
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = 'none';
            $newEvent->series_id = $event->series_id; // Keep in series or new? Usually keep.
            $newEvent->push();

            $newEvent->groups()->sync($this->selected_group_ids);
            $newEvent->genders()->sync($this->selected_gender_ids);

        } elseif ($mode === 'future') {
            // Cut off original
            $commonSeriesId = $event->series_id;
            $originalEndDate = $event->repeat_end_date;
            $stopDate = Carbon::parse($this->editingInstanceDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);

            // Create new future series
            $newEvent = $event->replicate();
            $newEvent->fill($replData);
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = $this->repeat_frequency;
            $newEvent->repeat_end_date = $this->repeat_frequency !== 'none' ? $this->repeat_end_date : $originalEndDate;
            $newEvent->series_id = $commonSeriesId;
            $newEvent->push();

            $newEvent->groups()->sync($this->selected_group_ids);
            $newEvent->genders()->sync($this->selected_gender_ids);
        }
        $this->closeModal();
        $this->dispatch('event-updated');
    }

    // --- REST OF COMPONENT ---

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
        $this->existing_images = [];

        // Filters
        $this->selected_group_ids = [];
        $this->selected_gender_ids = [];
        $this->is_role_restricted = true;
        $this->min_age = null;
        $this->max_distance_km = null;
        $this->event_zipcode = '';
        $this->event_country_id = null;
        $this->is_nsfw = false;

        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->isUpdateModalOpen = false;
        $this->isInviteModalOpen = false;
        $this->isLeaveCalendarModalOpen = false;
        $this->isDeleteCalendarModalOpen = false;
        $this->isManageRolesModalOpen = false;

        $this->reset('deleteCalendarPassword', 'inviteUsername', 'inviteEmail', 'inviteLink');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function getIsOwnerProperty()
    {
        if (!Auth::check()) return false;
        $ownerRole = Role::where('slug', 'owner')->first();
        if (!$ownerRole) return false;
        return $this->calendar->users()
            ->where('user_id', Auth::id())
            ->wherePivot('role_id', $ownerRole->id)
            ->exists();
    }

    // Standard Actions
    public function openInviteModal() { $this->reset('inviteLink', 'inviteUsername', 'inviteEmail'); $this->isInviteModalOpen = true; }
    public function generateInviteLink() { $role = Role::where('slug', $this->inviteRole)->first() ?? Role::where('slug', 'regular')->first(); $invitation = Invitation::create(['calendar_id' => $this->calendar->id, 'created_by' => Auth::id(), 'invite_type' => 'link', 'role_id' => $role->id, 'expires_at' => now()->addDays(7)]); $this->inviteLink = route('invitations.accept', $invitation->token); }
    public function inviteUserByUsername() { $this->validate(['inviteUsername' => 'required|exists:users,username']); $user = User::where('username', $this->inviteUsername)->first(); if ($this->calendar->users->contains($user->id)) { $this->addError('inviteUsername', 'User is already a member.'); return; } if (Invitation::where('calendar_id', $this->calendar->id)->where('email', $user->email)->whereNull('used_at')->where('expires_at', '>', now())->exists()) { $this->addError('inviteUsername', 'Invitation pending.'); return; } $role = Role::where('slug', $this->inviteRole)->first() ?? Role::where('slug', 'regular')->first(); Invitation::create(['calendar_id' => $this->calendar->id, 'created_by' => Auth::id(), 'invite_type' => 'email', 'email' => $user->email, 'role_id' => $role->id, 'expires_at' => now()->addDays(7)]); $this->closeModal(); $this->dispatch('action-message', message: 'Invitation sent!'); }
    public function promptLeaveCalendar() { $this->isLeaveCalendarModalOpen = true; }
    public function leaveCalendar() { if ($this->isOwner) return; if (Auth::check()) $this->calendar->users()->detach(Auth::id()); return redirect()->route('dashboard'); }
    public function promptDeleteCalendar() { $this->resetErrorBag(); $this->deleteCalendarPassword = ''; $this->isDeleteCalendarModalOpen = true; }
    public function deleteCalendar() { $this->validate(['deleteCalendarPassword' => 'required|current_password']); if (!$this->isOwner) abort(403); $this->calendar->delete(); return redirect()->route('dashboard'); }
    public function promptDeleteEvent($eventId, $date, $isRepeating) { $this->eventToDeleteId = $eventId; $this->eventToDeleteDate = $date; $this->eventToDeleteIsRepeating = $isRepeating; if ($isRepeating) { $this->isDeleteModalOpen = true; } else { $this->confirmDelete('single'); } }
    public function confirmDelete($mode) { $event = $this->calendar->events()->find($this->eventToDeleteId); if (!$event) { $this->closeModal(); return; } if ($mode === 'single' || ($mode === 'future' && $event->start_date->format('Y-m-d') === $this->eventToDeleteDate)) { if ($mode === 'future') { $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate); } $event->delete(); } elseif ($mode === 'future') { $stopDate = Carbon::parse($this->eventToDeleteDate)->subDay(); $event->update(['repeat_end_date' => $stopDate]); $this->deleteBranchedFutureEvents($event, $this->eventToDeleteDate); } elseif ($mode === 'instance') { $images = $event->images ?? []; $excluded = $images['excluded_dates'] ?? []; $excluded[] = $this->eventToDeleteDate; $images['excluded_dates'] = array_unique($excluded); $event->update(['images' => $images]); } $this->closeModal(); $this->dispatch('event-deleted'); }
    public function deleteBranchedFutureEvents($originalEvent, $cutoffDate) { if (!$originalEvent->series_id) return; $relatedEvents = $this->calendar->events()->where('series_id', $originalEvent->series_id)->where('id', '!=', $originalEvent->id)->get(); foreach ($relatedEvents as $relEvent) { if ($relEvent->start_date->format('Y-m-d') >= $cutoffDate) { $relEvent->delete(); } } }
    private function handleImageUploads() { $urls = $this->existing_images; foreach ($this->photos as $photo) { $path = $photo->store('events', 'public'); $urls[] = '/storage/' . $path; } return $urls; }
    public function getDurationInDaysProperty() { return Carbon::parse($this->start_date)->startOfDay()->diffInDays(Carbon::parse($this->end_date)->startOfDay()); }
    public function selectDate($date) { $this->selectedDate = $date; }
    public function nextMonth() { $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth(); $this->currentMonth = $date->month; $this->currentYear = $date->year; }
    public function previousMonth() { $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth(); $this->currentMonth = $date->month; $this->currentYear = $date->year; }
    public function goToToday() { $now = Carbon::now(); $this->currentMonth = $now->month; $this->currentYear = $now->year; $this->selectedDate = $now->format('Y-m-d'); }
    public function removeExistingImage($index) { unset($this->existing_images[$index]); $this->existing_images = array_values($this->existing_images); }
    public function removePhoto($index) { array_splice($this->photos, $index, 1); }
    public function render() { $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1); $daysInMonth = $date->daysInMonth; $firstDayOfWeek = $date->dayOfWeek; $eventsByDate = collect(); foreach ($this->events as $event) { $start = Carbon::parse($event->start_date)->startOfDay(); $end = Carbon::parse($event->end_date)->endOfDay(); $current = $start->copy(); while ($current->lte($end)) { $dateKey = $current->format('Y-m-d'); if (!$eventsByDate->has($dateKey)) $eventsByDate->put($dateKey, collect()); $eventsByDate[$dateKey]->push($event); $current->addDay(); } } return view('livewire.shared-calendar', ['daysInMonth' => $daysInMonth, 'firstDayOfWeek' => $firstDayOfWeek, 'monthName' => $date->format('F'), 'eventsByDate' => $eventsByDate, 'calendarDate' => $date]); }
}
