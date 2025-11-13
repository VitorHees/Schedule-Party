<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Homepage extends Component
{
    // Calendar state
    public int $year;
    public int $month; // 1-12
    public string $selectedDate; // Y-m-d

    /**
     * Holds generated demo events per month to keep "random" data stable
     * Structure:
     * [
     *   'YYYY-MM' => [
     *      'YYYY-MM-DD' => [
     *          ['title' => 'Team Meeting', 'time' => '14:00', 'color' => '#7C3AED'],
     *          ...
     *      ],
     *   ],
     * ]
     */
    public array $eventsMap = [];

    public function mount(): void
    {
        // Default to "today" and select it.
        $today = Carbon::now();
        $this->year = (int) $today->year;
        $this->month = (int) $today->month;
        $this->selectedDate = $today->toDateString();

        // Generate demo events for the current month
        $this->generateMonthEvents($this->year, $this->month);
    }

    // Convenience: current month key
    protected function monthKey(int $year, int $month): string
    {
        return sprintf('%04d-%02d', $year, $month);
    }

    // Create a 6x7 grid starting on Sunday that covers the visible month
    public function getGridProperty(): array
    {
        $firstOfMonth = Carbon::createFromDate($this->year, $this->month, 1);
        $start = (clone $firstOfMonth)->startOfWeek(Carbon::SUNDAY);
        $days = [];

        // 6 weeks view (42 days) to cover all months cleanly
        for ($i = 0; $i < 42; $i++) {
            $days[] = (clone $start)->addDays($i);
        }

        return $days;
    }

    // Events for the currently selected date
    public function getSelectedEventsProperty(): array
    {
        $monthKey = $this->monthKey($this->year, $this->month);
        return $this->eventsMap[$monthKey][$this->selectedDate] ?? [];
    }

    // Navigate to the previous month
    public function prevMonth(): void
    {
        $current = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = (int) $current->year;
        $this->month = (int) $current->month;

        // When changing month, select the 1st of that month as requested
        $this->selectedDate = Carbon::create($this->year, $this->month, 1)->toDateString();

        $this->generateMonthEvents($this->year, $this->month);
    }

    // Navigate to the next month
    public function nextMonth(): void
    {
        $current = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = (int) $current->year;
        $this->month = (int) $current->month;

        // When changing month, select the 1st of that month as requested
        $this->selectedDate = Carbon::create($this->year, $this->month, 1)->toDateString();

        $this->generateMonthEvents($this->year, $this->month);
    }

    // Select a specific date (switch month if needed)
    public function selectDay(string $dateString): void
    {
        $date = Carbon::parse($dateString);

        // If clicked day belongs to a different month, switch the view as requested
        if ($date->year !== $this->year || $date->month !== $this->month) {
            $this->year = (int) $date->year;
            $this->month = (int) $date->month;
            $this->generateMonthEvents($this->year, $this->month);
        }

        $this->selectedDate = $date->toDateString();
    }

    // Generate a stable set of "random" demo events for a month (once per component lifecycle)
    protected function generateMonthEvents(int $year, int $month): void
    {
        $key = $this->monthKey($year, $month);
        if (isset($this->eventsMap[$key])) {
            return; // Already generated for this month
        }

        $first = Carbon::create($year, $month, 1);
        $daysInMonth = (int) $first->daysInMonth;

        // Pick 4-7 random days to "outline"
        $daysWithEvents = collect(range(1, $daysInMonth))
            ->shuffle()
            ->take(random_int(4, 7))
            ->sort()
            ->values();

        // Some event templates (titles) and a small color palette
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

            // 1-3 events per marked day
            $count = random_int(1, 3);
            $events = [];

            for ($i = 0; $i < $count; $i++) {
                $title = $titles[array_rand($titles)];
                $hour = random_int(8, 20);
                $minute = [0, 15, 30, 45][array_rand([0, 1, 2, 3])];
                $events[] = [
                    'title' => $title,
                    'time' => sprintf('%02d:%02d', $hour, $minute),
                    'color' => $colors[array_rand($colors)],
                ];
            }

            // Sort events by time for a nicer UI
            usort($events, fn ($a, $b) => strcmp($a['time'], $b['time']));
            $monthEvents[$date] = $events;
        }

        $this->eventsMap[$key] = $monthEvents;
    }

    #[Layout('components.layouts.guest')]
    public function render()
    {
        return view('livewire.homepage');
    }
}
