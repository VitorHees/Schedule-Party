@props(['name', 'title', 'activeModal', 'maxWidth' => 'lg'])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ][$maxWidth] ?? 'max-w-lg';
@endphp

<div
    x-show="$wire.activeModal === '{{ $name }}'"
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm py-10"
    x-cloak
    style="display: none;"
>
    <div class="relative w-full {{ $maxWidthClass }} transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
