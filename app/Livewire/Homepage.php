<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Homepage extends Component
{
    public int $year;
    public int $month; // 1-12
    public string $selectedDate; // Y-m-d
    public array $eventsMap = []; // month-key => ['Y-m-d' => [ ['title'=>..,'start'=>..,'end'=>..,'color'=>..], ... ]]

    public function mount(): void
    {
        $today = Carbon::now();
        $this->year = (int) $today->year;
        $this->month = (int) $today->month;
        $this->selectedDate = $today->toDateString();

        $this->generateMonthEvents($this->year, $this->month);
    }

    protected function monthKey(int $year, int $month): string
    {
        return sprintf('%04d-%02d', $year, $month);
    }

    // 6x7 grid starting Sunday covering visible month
    public function getGridProperty(): array
    {
        $first = Carbon::createFromDate($this->year, $this->month, 1);
        $start = (clone $first)->startOfWeek(Carbon::SUNDAY);
        $days = [];
        for ($i = 0; $i < 42; $i++) {
            $days[] = (clone $start)->addDays($i);
        }
        return $days;
    }

    // Events for selected date, sorted by start time
    public function getSelectedEventsProperty(): array
    {
        $key = $this->monthKey($this->year, $this->month);
        $events = $this->eventsMap[$key][$this->selectedDate] ?? [];
        usort($events, fn ($a, $b) => strcmp($a['start'], $b['start']));
        return $events;
    }

    public function prevMonth(): void
    {
        $current = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = (int) $current->year;
        $this->month = (int) $current->month;
        $this->selectedDate = Carbon::create($this->year, $this->month, 1)->toDateString();
        $this->generateMonthEvents($this->year, $this->month);
    }

    public function nextMonth(): void
    {
        $current = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = (int) $current->year;
        $this->month = (int) $current->month;
        $this->selectedDate = Carbon::create($this->year, $this->month, 1)->toDateString();
        $this->generateMonthEvents($this->year, $this->month);
    }

    public function selectDay(string $dateString): void
    {
        $date = Carbon::parse($dateString);
        if ($date->year !== $this->year || $date->month !== $this->month) {
            $this->year = (int) $date->year;
            $this->month = (int) $date->month;
            $this->generateMonthEvents($this->year, $this->month);
        }
        $this->selectedDate = $date->toDateString();
    }

    protected function generateMonthEvents(int $year, int $month): void
    {
        $key = $this->monthKey($year, $month);
        if (isset($this->eventsMap[$key])) {
            return;
        }

        $first = Carbon::create($year, $month, 1);
        $daysInMonth = (int) $first->daysInMonth;

        $daysWithEvents = collect(range(1, $daysInMonth))
            ->shuffle()
            ->take(random_int(4, 7))
            ->sort()
            ->values();

        $titles = [
            'Team Meeting',
            'Project Deadline',
            'Lunch with Sarah',
            'Dentist Appointment',
            'Gym Session',
            'Code Review',
            'Birthday Party',
            'Daily Standup',
            'Workshop',
            'Client Call',
            'Movie Night',
            'Grocery Run',
        ];

        $colors = [
            '#7C3AED', // purple-600
            '#2563EB', // blue-600
            '#16A34A', // green-600
            '#DC2626', // red-600
            '#F59E0B', // amber-500
            '#DB2777', // pink-600
            '#4F46E5', // indigo-600
        ];

        $monthEvents = [];

        foreach ($daysWithEvents as $day) {
            $date = Carbon::create($year, $month, (int) $day)->toDateString();
            $count = random_int(1, 3);
            $events = [];

            for ($i = 0; $i < $count; $i++) {
                $title = $titles[array_rand($titles)];

                // Random start time between 8:00 and 20:00
                $startHour = random_int(8, 20);
                $startMinute = [0, 15, 30, 45][array_rand([0,1,2,3])];
                $start = Carbon::createFromTime($startHour, $startMinute);

                // Duration between 30 and 120 minutes
                $duration = [30, 45, 60, 90, 120][array_rand([0,1,2,3,4])];
                $end = (clone $start)->addMinutes($duration);

                $events[] = [
                    'title' => $title,
                    'start' => $start->format('H:i'),
                    'end'   => $end->format('H:i'),
                    'color' => $colors[array_rand($colors)],
                ];
            }

            usort($events, fn ($a, $b) => strcmp($a['start'], $b['start']));
            $monthEvents[$date] = $events;
        }

        $this->eventsMap[$key] = $monthEvents;
    }

    #[Layout('components.layouts.auth')]
    public function render()
    {
        return view('livewire.homepage');
    }
}
