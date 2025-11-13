@props([
    'title' => '',
    'start' => null, // 'H:i'
    'end' => null,   // 'H:i'
    'color' => '#7C3AED',
])

@php
    $startLabel = $start ? \Carbon\Carbon::createFromFormat('H:i', $start)->format('g:i A') : null;
    $endLabel = $end ? \Carbon\Carbon::createFromFormat('H:i', $end)->format('g:i A') : null;
@endphp

<div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
    <div class="w-2 h-2 rounded-full" style="background-color: {{ $color }}"></div>
    <div class="flex-1">
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $title }}</div>
        @if($startLabel)
            <div class="text-xs text-gray-600 dark:text-gray-400">
                {{ $startLabel }}@if($endLabel) – {{ $endLabel }}@endif
            </div>
        @endif
    </div>
</div>
