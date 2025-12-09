<div>
    @if($isOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create Shared Calendar</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form wire:submit.prevent="create" class="space-y-4">
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">Calendar Name</label>
                        <input type="text" id="name" wire:model="name" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="e.g., Family Trips, Project Alpha">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="rounded-xl px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2 text-sm font-bold text-white hover:bg-purple-700 shadow-lg hover:shadow-purple-500/20">
                            Create Calendar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
