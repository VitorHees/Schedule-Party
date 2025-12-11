@props(['daysInMonth', 'firstDayOfWeek', 'calendarDate', 'eventsByDate', 'selectedDate', 'monthName', 'currentYear', 'currentMonth'])

<div class="relative overflow-visible rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
    <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-purple-100 blur-3xl opacity-50 dark:bg-purple-900/20"></div>
        <div class="absolute -bottom-10 -left-10 h-72 w-72 rounded-full bg-blue-100 blur-3xl opacity-50 dark:bg-blue-900/20"></div>
    </div>

    {{-- Controls --}}
    <div class="relative z-30 flex items-center justify-between border-b border-gray-100 px-6 py-6 dark:border-gray-700">
        <div class="flex items-center gap-2 text-3xl font-bold text-gray-900 dark:text-white relative">
            {{-- Month Selector --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="hover:text-purple-600 dark:hover:text-purple-400 transition-colors flex items-center decoration-dashed underline-offset-8 hover:underline cursor-pointer">
                    {{ $monthName }}
                </button>
                <div x-show="open" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-3 gap-2 z-50 ring-1 ring-black/5" style="display: none;">
                    @foreach(range(1, 12) as $m)
                        <button wire:click="setMonth({{ $m }}); open = false" class="p-2 rounded-lg text-sm font-bold {{ $currentMonth == $m ? 'bg-purple-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ \Carbon\Carbon::create()->month($m)->format('M') }}
                        </button>
                    @endforeach
                </div>
            </div>
            {{-- Year Selector --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="text-gray-400 dark:text-gray-500 hover:text-purple-600 dark:hover:text-purple-400 transition-colors decoration-dashed underline-offset-8 hover:underline cursor-pointer">
                    {{ $currentYear }}
                </button>
                <div x-show="open" class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-2 z-50 max-h-64 overflow-y-auto ring-1 ring-black/5" style="display: none;">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(range($currentYear - 5, $currentYear + 5) as $y)
                            <button wire:click="setYear({{ $y }}); open = false" class="p-2 rounded-lg text-sm font-bold {{ $currentYear == $y ? 'bg-purple-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                {{ $y }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 rounded-xl bg-gray-50 p-1 dark:bg-gray-700/50">
            <button wire:click="previousMonth" class="rounded-lg p-2 text-gray-500 hover:bg-white hover:text-purple-600 hover:shadow-sm dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-purple-400"><x-heroicon-s-chevron-left class="h-5 w-5" /></button>
            <button wire:click="goToToday" class="px-3 py-1 text-sm font-bold text-gray-600 hover:text-purple-600 dark:text-gray-300 dark:hover:text-purple-400">Today</button>
            <button wire:click="nextMonth" class="rounded-lg p-2 text-gray-500 hover:bg-white hover:text-purple-600 hover:shadow-sm dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-purple-400"><x-heroicon-s-chevron-right class="h-5 w-5" /></button>
        </div>
    </div>

    {{-- Grid --}}
    <div class="relative z-10 p-6">
        <div class="mb-4 grid grid-cols-7 text-center">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $day }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 gap-y-2 lg:gap-4">
            @for ($i = 0; $i < $firstDayOfWeek; $i++) <div class="min-h-[80px]"></div> @endfor
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateString = $calendarDate->copy()->setDay($day)->format('Y-m-d');
                    $isToday = $dateString === \Carbon\Carbon::now()->format('Y-m-d');
                    $isSelected = $selectedDate === $dateString;
                    $dayEvents = $eventsByDate[$dateString] ?? collect();
                @endphp
                <div wire:click="selectDate('{{ $dateString }}')" class="group relative flex min-h-[80px] cursor-pointer flex-col items-center rounded-xl border border-transparent p-2 hover:bg-purple-50 dark:hover:bg-purple-900/20 {{ $isSelected ? 'bg-purple-50 ring-2 ring-purple-500 dark:bg-purple-900/20 dark:ring-purple-400' : '' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-colors {{ $isToday ? 'bg-purple-600 text-white shadow-md' : ($isSelected ? 'text-purple-700 dark:text-purple-300' : 'text-gray-700 dark:text-gray-300') }}">{{ $day }}</span>
                    <div class="mt-2 flex flex-wrap justify-center gap-1">
                        @foreach($dayEvents->take(4) as $event)
                            {{-- Handle both 'mixed_color' (Shared) and Group Color (Personal) --}}
                            <div class="h-1.5 w-1.5 rounded-full" style="background: {{ $event->mixed_color ?? $event->groups->first()->color ?? '#A855F7' }};"></div>
                        @endforeach
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
