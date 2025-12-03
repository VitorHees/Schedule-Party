<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class PersonalCalendar extends Component
{
    // --- Calendar State ---
    public $currentMonth;
    public $currentYear;
    public $selectedDate;

    // --- Modal State ---
    public $isModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isCreatingGroup = false;

    // --- Delete State ---
    public $eventToDeleteId = null;
    public $eventToDeleteDate = null;
    public $eventToDeleteIsRepeating = false;

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

    // --- Group Logic ---
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
    }

    public function resetForm()
    {
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

    // --- Group Management ---

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
                $this->selected_group_id = null; // Reset selection
            }
        }
    }

    // --- Deletion Logic (Updated) ---

    public function promptDeleteEvent($eventId, $date, $isRepeating)
    {
        $this->eventToDeleteId = $eventId;
        $this->eventToDeleteDate = $date;
        $this->eventToDeleteIsRepeating = $isRepeating;

        if ($isRepeating) {
            $this->isDeleteModalOpen = true;
        } else {
            // Non-repeating events are always "delete all" (it's just one row)
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
            // If it's a non-repeating event OR we are at the very start of the series, delete the whole row.
            $event->delete();
        }
        elseif ($mode === 'future') {
            // "All Future Events": Stop the recurrence at YESTERDAY relative to the selected date.
            // This preserves history but hides it from today onwards.
            $stopDate = Carbon::parse($this->eventToDeleteDate)->subDay();
            $event->update(['repeat_end_date' => $stopDate]);
        }
        elseif ($mode === 'instance') {
            // "Only This Event": Add specific date to exclusions
            $images = $event->images ?? [];
            $excluded = $images['excluded_dates'] ?? [];
            $excluded[] = $this->eventToDeleteDate;

            $images['excluded_dates'] = array_unique($excluded);
            $event->update(['images' => $images]);
        }

        $this->closeModal();
        $this->dispatch('event-deleted');
    }

    // --- Event Saving ---

    public function saveEvent()
    {
        $this->validate();

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
            'images' => [], // Initialize for exclusions
        ]);

        if ($this->selected_group_id) {
            $event->groups()->attach($this->selected_group_id);
        }

        $this->isModalOpen = false;
    }

    // --- Data Fetching (Recurrence Engine) ---

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

            // Case A: Non-Repeating
            if ($event->repeat_frequency === 'none') {
                if ($event->start_date->lt($viewEnd) && $event->end_date->gt($viewStart)) {
                    if (!in_array($event->start_date->format('Y-m-d'), $exclusions)) {
                        $processedEvents->push($event);
                    }
                }
                continue;
            }

            // Case B: Repeating
            $currentDate = Carbon::parse($event->start_date);

            // Optimization: If event starts way before view, we can jump closer
            // but for simplicity and accuracy with "monthly/yearly", we iterate.
            // (Production apps use complex recurrence math libraries here)

            while ($currentDate->lte($viewEnd)) {
                // STOP if we hit the database-defined repeat_end_date (which we update on 'delete future')
                if ($event->repeat_end_date && $currentDate->gt($event->repeat_end_date)) {
                    break;
                }

                if ($currentDate->gte($viewStart)) {
                    $dateString = $currentDate->format('Y-m-d');

                    if (!in_array($dateString, $exclusions)) {
                        $instance = clone $event;
                        $instance->id = $event->id;
                        $instance->original_start = $event->start_date;

                        $instance->start_date = $currentDate->copy()->setTimeFrom($event->start_date);
                        $instance->end_date = $currentDate->copy()->setTimeFrom($event->end_date);

                        $duration = $event->start_date->diffInDays($event->end_date);
                        if ($duration > 0) {
                            $instance->end_date->addDays($duration);
                        }

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
