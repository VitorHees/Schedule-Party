<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Group;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;

class SharedCalendar extends Component
{
    use WithFileUploads;

    public Calendar $calendar;

    // --- Navigation State ---
    public $currentMonth;
    public $currentYear;
    public $selectedDate;

    // --- Modal Visibility State ---
    public $isModalOpen = false;            // Event Create/Edit
    public $isDeleteModalOpen = false;      // Event Delete
    public $isUpdateModalOpen = false;      // Event Update (Series)
    public $isCreatingGroup = false;        // Group Creation inside Event Modal
    public $isInviteModalOpen = false;      // Invite User
    public $isLeaveCalendarModalOpen = false; // Leave Calendar (Member/Guest)
    public $isDeleteCalendarModalOpen = false;// Delete Calendar (Owner)

    // --- Invitation State ---
    public $inviteLink = null;
    public $inviteUsername = '';
    public $inviteEmail = ''; // Optional, for future email sending implementation
    public $inviteRole = 'regular';

    // --- Delete Calendar State ---
    public $deleteCalendarPassword = '';

    // --- Event Management State ---
    public $eventId = null;
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;
    public $editingInstanceDate = null;

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

    #[Validate(['photos.*' => 'image|max:10240'])]
    public $photos = [];
    public $existing_images = [];

    public $selected_group_id = null;

    // --- Group Creation State ---
    #[Validate('required_if:isCreatingGroup,true|min:3')]
    public $new_group_name = '';
    public $new_group_color = '#A855F7';

    public $colors = [
        '#EF4444', '#F97316', '#F59E0B', '#10B981', '#06B6D4',
        '#3B82F6', '#6366F1', '#A855F7', '#EC4899', '#64748B',
    ];

    protected $listeners = ['open-create-event-modal' => 'openModal'];

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
        $user = Auth::user();

        // 1. Check if authenticated user is a member
        $isMember = $user && $this->calendar->users->contains($user->id);

        // 2. Check if user is a guest (via cookie)
        $guestToken = request()->cookie('guest_access_' . $calendar->id);
        $isGuest = $guestToken && $this->calendar->calendarUsers()
                ->where('guest_token', $guestToken)
                ->exists();

        // 3. Deny access if neither
        if (!$isMember && !$isGuest) {
            abort(403, 'You do not have access to this calendar.');
        }

        // Initialize Calendar View
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->start_date = Carbon::now()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    // --- 1. INVITATION LOGIC ---

    public function openInviteModal()
    {
        $this->reset('inviteLink', 'inviteUsername', 'inviteEmail');
        $this->isInviteModalOpen = true;
    }

    public function generateInviteLink()
    {
        $role = Role::where('slug', $this->inviteRole)->first() ?? Role::where('slug', 'regular')->first();

        // Create an invitation record for the link
        $invitation = Invitation::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(),
            'invite_type' => 'link',
            'role_id' => $role->id,
            'expires_at' => now()->addDays(7),
        ]);

        // Assuming route 'invitations.accept' is defined as /invite/{token}
        $this->inviteLink = route('invitations.accept', $invitation->token);
    }

    public function inviteUserByUsername()
    {
        $this->validate([
            'inviteUsername' => 'required|exists:users,username',
        ]);

        $user = User::where('username', $this->inviteUsername)->first();

        if ($this->calendar->users->contains($user->id)) {
            $this->addError('inviteUsername', 'This user is already a member of this calendar.');
            return;
        }

        // Directly attach the user as a regular member
        $role = Role::where('slug', 'regular')->first();

        $this->calendar->users()->attach($user->id, [
            'role_id' => $role->id,
            'joined_at' => now(),
        ]);

        $this->closeModal();
        // Ideally invoke a success notification here
    }

    // --- 2. LEAVE / DELETE CALENDAR LOGIC ---

    public function promptLeaveCalendar()
    {
        $this->isLeaveCalendarModalOpen = true;
    }

    public function leaveCalendar()
    {
        // Owner should not leave; they must delete or transfer ownership (handled by deleteCalendar)
        if ($this->isOwner) {
            return;
        }

        if (Auth::check()) {
            $this->calendar->users()->detach(Auth::id());
        } else {
            // Guest Logic: In a real app, we'd delete the guest record from DB
            // based on the cookie token. For now, we redirect them away.
            // Cookie deletion would happen in the response.
        }

        return redirect()->route('dashboard');
    }

    public function promptDeleteCalendar()
    {
        $this->resetErrorBag();
        $this->deleteCalendarPassword = '';
        $this->isDeleteCalendarModalOpen = true;
    }

    public function deleteCalendar()
    {
        $this->validate([
            'deleteCalendarPassword' => 'required|current_password',
        ]);

        if (!$this->isOwner) {
            abort(403, 'Unauthorized');
        }

        $this->calendar->delete();

        return redirect()->route('dashboard');
    }

    // --- 3. EVENT CRUD LOGIC ---

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
        $this->selected_group_id = $event->groups()->first()?->id;

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
        if ($this->repeat_frequency === 'daily' && $days >= 1) { $this->addError('repeat_frequency', 'Event spans multiple days; cannot repeat daily.'); return; }
        if ($this->repeat_frequency === 'weekly' && $days >= 7) { $this->addError('repeat_frequency', 'Event spans over a week; cannot repeat weekly.'); return; }
        if ($this->repeat_frequency === 'monthly' && $days >= 28) { $this->addError('repeat_frequency', 'Event spans over a month; cannot repeat monthly.'); return; }

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

    private function handleImageUploads()
    {
        $urls = $this->existing_images;
        foreach ($this->photos as $photo) {
            $path = $photo->store('events', 'public');
            $urls[] = '/storage/' . $path;
        }
        return $urls;
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
        ]);

        $event->groups()->sync($this->selected_group_id ? [$this->selected_group_id] : []);
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

        $event = Event::create([
            'calendar_id' => $this->calendar->id,
            'created_by' => Auth::id(), // Nullable if guest, but DB likely requires it. Guests might need restricted creation.
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
        ]);

        if ($this->selected_group_id) {
            $event->groups()->attach($this->selected_group_id);
        }
    }

    public function confirmUpdate($mode)
    {
        $event = $this->calendar->events()->find($this->eventId);
        if (!$event) { $this->closeModal(); return; }

        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        if (!$event->series_id) {
            $event->series_id = Str::uuid()->toString();
            $event->saveQuietly();
        }

        $newImages = ['urls' => $this->handleImageUploads()];

        if ($mode === 'instance') {
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->editingInstanceDate;
            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);

            $newEvent = $event->replicate();
            $newEvent->name = $this->title;
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = 'none';
            $newEvent->repeat_end_date = null;
            $newEvent->series_id = $event->series_id;
            $newEvent->images = $newImages;
            $newEvent->push();

            if ($this->selected_group_id) $newEvent->groups()->sync([$this->selected_group_id]);

        } elseif ($mode === 'future') {
            $commonSeriesId = $event->series_id;
            $originalEndDate = $event->repeat_end_date;

            $stopDate = Carbon::parse($this->editingInstanceDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);

            $newEvent = $event->replicate();
            $newEvent->name = $this->title;
            $newEvent->start_date = $startDateTime;
            $newEvent->end_date = $endDateTime;
            $newEvent->repeat_frequency = $this->repeat_frequency;
            $newEvent->repeat_end_date = $this->repeat_frequency !== 'none' ? $this->repeat_end_date : $originalEndDate;
            $newEvent->series_id = $commonSeriesId;
            $newEvent->images = $newImages;
            $newEvent->push();

            if ($this->selected_group_id) $newEvent->groups()->sync([$this->selected_group_id]);
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
        if (!$event) { $this->closeModal(); return; }

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

    // --- HELPER METHODS ---

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isDeleteModalOpen = false;
        $this->isUpdateModalOpen = false;
        $this->isInviteModalOpen = false;
        $this->isLeaveCalendarModalOpen = false;
        $this->isDeleteCalendarModalOpen = false;

        $this->reset('deleteCalendarPassword', 'inviteUsername', 'inviteEmail', 'inviteLink');
        $this->resetErrorBag();
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
        $this->existing_images = [];
        $this->selected_group_id = null;
        $this->isCreatingGroup = false;
        $this->new_group_name = '';
        $this->new_group_color = '#A855F7';
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

    public function getGroupsProperty()
    {
        return $this->calendar->groups;
    }

    public function getEventsProperty()
    {
        $viewStart = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->subDays(7);
        $viewEnd = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->addDays(14);

        $rawEvents = $this->calendar->events()
            ->with('groups')
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

    // --- VIEW HELPERS ---

    public function selectDate($date) { $this->selectedDate = $date; }
    public function nextMonth() { $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth(); $this->currentMonth = $date->month; $this->currentYear = $date->year; }
    public function previousMonth() { $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth(); $this->currentMonth = $date->month; $this->currentYear = $date->year; }
    public function goToToday() { $now = Carbon::now(); $this->currentMonth = $now->month; $this->currentYear = $now->year; $this->selectedDate = $now->format('Y-m-d'); }

    public function toggleCreateGroup() { $this->isCreatingGroup = !$this->isCreatingGroup; }
    public function selectGroup($groupId) { $this->selected_group_id = $this->selected_group_id === $groupId ? null : $groupId; $this->isCreatingGroup = false; }

    public function saveGroup()
    {
        $this->validate([
            'new_group_name' => 'required|min:2|max:50',
            'new_group_color' => 'required',
        ]);

        $group = Group::create([
            'calendar_id' => $this->calendar->id,
            'name' => $this->new_group_name,
            'color' => $this->new_group_color,
        ]);

        // If authenticated, track who made it
        if (Auth::check()) {
            $group->users()->attach(Auth::id(), ['assigned_at' => now()]);
        }

        $this->selected_group_id = $group->id;
        $this->isCreatingGroup = false;
        $this->new_group_name = '';
    }

    public function deleteSelectedGroup()
    {
        if ($this->selected_group_id) {
            $group = Group::where('calendar_id', $this->calendar->id)->find($this->selected_group_id);
            if ($group) {
                $group->delete();
                $this->selected_group_id = null;
            }
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

        return view('livewire.shared-calendar', [
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'monthName' => $date->format('F'),
            'eventsByDate' => $eventsByDate,
            'calendarDate' => $date,
        ]);
    }
}
