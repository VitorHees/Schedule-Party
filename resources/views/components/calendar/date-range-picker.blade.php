@props(['startDate', 'startTime', 'endDate', 'endTime', 'isAllDay'])

<div class="grid grid-cols-2 gap-3">
    <div class="space-y-1">
        <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Start</label>
        <input
            type="date"
            wire:model.live="start_date"
            class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
        >
        @if(!$isAllDay)
            <input
                type="time"
                wire:model="start_time"
                class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
            >
        @endif
    </div>
    <div class="space-y-1">
        <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">End</label>
        <input
            type="date"
            wire:model.live="end_date"
            min="{{ $startDate }}"
            class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
        >
        @if(!$isAllDay)
            <input
                type="time"
                wire:model="end_time"
                class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
            >
        @endif
    </div>
</div>
