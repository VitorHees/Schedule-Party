@props(['calendar', 'monthName', 'currentYear', 'currentMonth', 'selectedDate', 'canCreateEvents' => false])

<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    {{-- Title Section --}}
    <div class="min-w-0 flex-1">
        <h1
            class="text-3xl font-bold tracking-tight text-gray-900 truncate dark:text-white"
            title="{{ $calendar->name }}"
        >
            {{ $calendar->name }}
        </h1>
        <p class="mt-1 text-lg text-gray-600 dark:text-gray-400">
            {{ $calendar->type === 'personal' ? 'Your Private Schedule' : 'Collaborative Schedule' }}
        </p>
    </div>

    {{-- Actions Section --}}
    <div class="flex shrink-0 items-center gap-3">
        {{ $actions ?? '' }}

        @if($canCreateEvents)
            <button wire:click="openModal('{{ $selectedDate }}')" class="group inline-flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-base font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-purple-700 hover:shadow-xl dark:bg-purple-500 dark:hover:bg-purple-600">
                <x-heroicon-o-plus class="h-5 w-5 transition-transform group-hover:rotate-90" />
                <span>New Event</span>
            </button>
        @endif
    </div>
</div>
