<?php

namespace App\Livewire;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PersonalCalendar extends Component
{
    // Calendar State
    public int $year;
    public int $month;
    public string $selectedDate;
    public $calendar;

    // Modals
    public bool $showEventModal = false;
    public bool $showGroupModal = false;

    // Event Form Data
    public $eventName;
    public $eventDescription;
    public $eventStartDate;
    public $eventStartTime = '09:00';
    public $eventEndDate;
    public $eventEndTime = '10:00';
    public $eventIsAllDay = false;
    public $eventLocation;
    public $eventUrl;
    public $eventRepeatFrequency = 'none';
    public $eventRepeatEndDate;
    public array $selectedGroups = [];

    // Group Form Data
    public $newGroupName;
    public $newGroupColor = '#7C3AED'; // Default purple

    public function mount()
    {
        $today = Carbon::now();
        $this->year = $today->year;
        $this->month = $today->month;
        $this->selectedDate = $today->toDateString();

        // Fetch or create the user's personal calendar
        $this->calendar = auth()->user()->calendars()
            ->where('type', 'personal')
            ->first();

        if (! $this->calendar) {
            $this->calendar = Calendar::create([
                'name' => 'My Personal Calendar',
                'type' => 'personal'
            ]);

            auth()->user()->calendars()->attach($this->calendar, [
                'role_id' => 1,
                'joined_at' => now()
            ]);
        }

        $this->resetEventForm();
    }

    public function getGridProperty()
    {
        $first = Carbon::createFromDate($this->year, $this->month, 1);
        $start = (clone $first)->startOfWeek(Carbon::SUNDAY);
        $days = [];
        for ($i = 0; $i < 42; $i++) {
            $days[] = (clone $start)->addDays($i);
        }
        return $days;
    }

    public function getEventsProperty()
    {
        return $this->calendar->events()
            ->with('groups')
            ->whereYear('start_date', $this->year)
            ->whereMonth('start_date', $this->month)
            ->get();
    }

    public function getUserGroupsProperty()
    {
        // CHANGED: Only fetch groups belonging to THIS calendar
        // This ensures the list is empty by default until you create one
        return $this->calendar->groups;
    }

    public function selectDay($date)
    {
        $this->selectedDate = $date;
        $this->eventStartDate = $date;
        $this->eventEndDate = $date;
        $this->showEventModal = true;
    }

    public function saveGroup()
    {
        $this->validate([
            'newGroupName' => 'required|string|max:255',
            'newGroupColor' => 'required|string',
        ]);

        Group::create([
            'name' => $this->newGroupName,
            'color' => $this->newGroupColor,
            'calendar_id' => $this->calendar->id,
        ]);

        $this->showGroupModal = false;
        $this->reset('newGroupName', 'newGroupColor');
    }

    // NEW: Delete Function
    public function deleteGroup($groupId)
    {
        $group = Group::find($groupId);

        // Security check: Only delete if it belongs to your calendar
        if ($group && $group->calendar_id === $this->calendar->id) {
            $group->delete();
        }
    }

    public function saveEvent()
    {
        $this->validate([
            'eventName' => 'required|string|max:255',
            'eventStartDate' => 'required|date',
        ]);

        $start = Carbon::parse($this->eventStartDate . ' ' . ($this->eventIsAllDay ? '00:00' : $this->eventStartTime));
        $end = $this->eventIsAllDay
            ? $start->copy()->endOfDay()
            : Carbon::parse(($this->eventEndDate ?? $this->eventStartDate) . ' ' . $this->eventEndTime);

        $event = $this->calendar->events()->create([
            'created_by' => auth()->id(),
            'name' => $this->eventName,
            'description' => $this->eventDescription,
            'start_date' => $start,
            'end_date' => $end,
            'is_all_day' => $this->eventIsAllDay,
            'location' => $this->eventLocation,
            'url' => $this->eventUrl,
            'repeat_frequency' => $this->eventRepeatFrequency,
            'repeat_end_date' => $this->eventRepeatEndDate ?: null,
        ]);

        if (!empty($this->selectedGroups)) {
            $event->groups()->attach($this->selectedGroups);
        }

        $this->showEventModal = false;
        $this->resetEventForm();
    }

    public function resetEventForm()
    {
        $this->eventName = '';
        $this->eventDescription = '';
        $this->eventStartDate = $this->selectedDate;
        $this->eventEndDate = $this->selectedDate;
        $this->eventStartTime = '09:00';
        $this->eventEndTime = '10:00';
        $this->eventIsAllDay = false;
        $this->selectedGroups = [];
        $this->eventRepeatFrequency = 'none';
        $this->eventLocation = '';
        $this->eventUrl = '';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.personal-calendar');
    }
}
