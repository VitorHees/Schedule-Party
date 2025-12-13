@props([
    'items',
    'createMethod',
    'deleteMethod',
    'nameModel',
    'colorModel',
    'selectableModel' => null,
    'showSelectableIcon' => true,
    'toggleMethod' => null,
    'assignedIds' => []
])

{{-- UPDATED: Added $attributes->merge() to the root div to support wire:key from the parent --}}
<div {{ $attributes->merge(['class' => 'fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm']) }}>
    <div class="w-full max-w-lg transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manage Labels</h2>
            <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="h-5 w-5" />
            </button>
        </div>

        {{-- Create New --}}
        <div class="mb-6 rounded-xl bg-gray-50 p-4 dark:bg-gray-900/50">
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-500">Create New Label</h3>
            <div class="flex flex-col gap-3">
                <div class="flex gap-2">
                    <input type="text" wire:model="{{ $nameModel }}" placeholder="Name (e.g. Birthdays)" class="flex-1 rounded-lg border-gray-200 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <input type="color" wire:model="{{ $colorModel }}" class="h-10 w-12 cursor-pointer rounded-lg border-none bg-transparent p-0">
                    <button wire:click="{{ $createMethod }}" class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-bold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">Add</button>
                </div>
                @if($selectableModel)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="{{ $selectableModel }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Selectable (Users can opt-in/out)</span>
                    </label>
                @endif
                @error($nameModel) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- List --}}
        <div class="space-y-3 max-h-[300px] overflow-y-auto">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Existing Labels</h3>
            @forelse($items as $item)
                {{-- Keep wire:key here for internal list stability --}}
                <div wire:key="label-item-{{ $item->id }}" class="flex items-center justify-between rounded-lg border border-gray-100 p-3 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="h-3 w-3 rounded-full" style="background-color: {{ $item->color }}"></div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1">
                                {{ $item->name }}
                                @if($showSelectableIcon && isset($item->is_selectable) && $item->is_selectable)
                                    <x-heroicon-o-hand-raised class="w-3 h-3 text-gray-400" title="Voluntary / Opt-in" />
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Join/Leave Button --}}
                        @if($toggleMethod && isset($item->is_selectable) && $item->is_selectable)
                            <button
                                wire:click="{{ $toggleMethod }}({{ $item->id }})"
                                class="px-3 py-1 text-xs font-bold rounded-lg transition-colors {{ in_array($item->id, $assignedIds) ? 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' : 'bg-purple-50 text-purple-600 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400' }}"
                            >
                                {{ in_array($item->id, $assignedIds) ? 'Leave' : 'Join' }}
                            </button>
                        @endif

                        {{ $actionSlot ?? '' }}

                        {{-- Delete Button --}}
                        <button wire:click="{{ $deleteMethod }}({{ $item->id }})" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30">
                            <x-heroicon-o-trash class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-sm text-gray-500">No labels created yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
