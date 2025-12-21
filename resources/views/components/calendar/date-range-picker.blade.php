@props(['startDate', 'startTime', 'endDate', 'endTime', 'isAllDay'])

<div class="grid grid-cols-2 gap-3">
    <div class="space-y-1">
        <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Start</label>
        <input type="date" wire:model.live="start_date" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @if(!$isAllDay)
            <input type="time" wire:model="start_time" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @endif
    </div>
    <div class="space-y-1">
        <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">End</label>
        <input type="date" wire:model.live="end_date" min="{{ $startDate }}" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @if(!$isAllDay)
            <input type="time" wire:model="end_time" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @endif
    </div>
</div>
