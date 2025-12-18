<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

    <div class="mx-auto max-w-5xl space-y-8">
        {{-- HEADER --}}
        <x-calendar.header
            :calendar="$calendar"
            :monthName="$monthName"
            :currentYear="$currentYear"
            :currentMonth="$currentMonth"
            :selectedDate="$selectedDate"
        >
            <x-slot:actions>
                {{-- EXPORT BUTTON --}}
                <button wire:click="openExportModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                    <x-heroicon-o-arrow-up-on-square class="h-5 w-5" />
                    <span>Export</span>
                </button>

                {{-- LABELS BUTTON --}}
                <button wire:click="openManageGroupsModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                    <x-heroicon-o-tag class="h-5 w-5" />
                    <span>Labels</span>
                </button>

                {{-- NEW EVENT BUTTON --}}
                <button wire:click="openModal('{{ $selectedDate }}')" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-3 text-base font-bold text-white shadow-lg transition-all hover:bg-purple-700 hover:shadow-purple-500/20">
                    <x-heroicon-o-plus class="h-5 w-5" />
                    <span>New Event</span>
                </button>
            </x-slot:actions>
        </x-calendar.header>

        {{-- CALENDAR GRID --}}
        <x-calendar.grid
            :daysInMonth="$daysInMonth"
            :firstDayOfWeek="$firstDayOfWeek"
            :calendarDate="$calendarDate"
            :eventsByDate="$eventsByDate"
            :selectedDate="$selectedDate"
            :monthName="$monthName"
            :currentYear="$currentYear"
            :currentMonth="$currentMonth"
        />

        {{-- AGENDA STREAM --}}
        <div class="space-y-6">
            <div class="flex items-center gap-4 px-1">
                <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300">
                    <span class="text-[10px] font-bold uppercase">{{ \Carbon\Carbon::parse($selectedDate)->format('M') }}</span>
                    <span class="text-xl font-bold leading-none">{{ \Carbon\Carbon::parse($selectedDate)->format('j') }}</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Agenda</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F jS') }}</p>
                </div>
            </div>

            <div class="space-y-4">
                @if($this->selectedDateEvents->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-800/50">
                        <x-heroicon-o-calendar class="mx-auto h-12 w-12 text-gray-400" />
                        <h4 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">No plans yet</h4>
                        <button wire:click="openModal('{{ $selectedDate }}')" class="mt-4 text-sm font-bold text-purple-600 hover:text-purple-700 dark:text-purple-400">+ Add an event</button>
                    </div>
                @else
                    @foreach($this->selectedDateEvents as $event)
                        {{-- UPDATED: Added canExport prop --}}
                        <x-calendar.event-card :event="$event" :canExport="true" />
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- CREATE/EDIT EVENT MODAL --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto overflow-x-hidden bg-black/60 p-4 backdrop-blur-sm py-10">
            <div class="relative w-full max-w-lg transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $eventId ? 'Edit Event' : 'New Event' }}</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
                </div>

                <form wire:submit.prevent="saveEvent" class="space-y-6">
                    {{-- BASIC DETAILS --}}
                    <div class="space-y-3">
                        <input type="text" wire:model="title" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Event Title">
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Start</label>
                                <input type="date" wire:model.live="start_date" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @if(!$is_all_day) <input type="time" wire:model="start_time" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white"> @endif
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">End</label>
                                <input type="date" wire:model.live="end_date" min="{{ $start_date }}" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @if(!$is_all_day) <input type="time" wire:model="end_time" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white"> @endif
                            </div>
                        </div>
                    </div>

                    {{-- REPEAT --}}
                    <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 dark:border-gray-700">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_all_day" class="h-4 w-4 rounded text-purple-600 focus:ring-purple-500 dark:bg-gray-800">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">All Day</span>
                        </label>

                        <div class="flex flex-col items-end gap-2">
                            <select wire:model.live="repeat_frequency" class="rounded-lg border-none bg-transparent py-0 text-xs font-bold text-gray-600 focus:ring-0 dark:text-gray-400">
                                <option value="none">No Repeat</option>
                                @php $days = $this->durationInDays; @endphp
                                <option value="daily"   @if($days >= 1) disabled class="text-gray-300" @endif>Daily</option>
                                <option value="weekly"  @if($days >= 7) disabled class="text-gray-300" @endif>Weekly</option>
                                <option value="monthly" @if($days >= 28) disabled class="text-gray-300" @endif>Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            @if($repeat_frequency !== 'none')
                                <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-1">
                                    <label class="text-[10px] font-bold uppercase text-gray-400">Until</label>
                                    <input type="date" wire:model="repeat_end_date" class="rounded-lg border-gray-200 bg-gray-50 py-1 px-2 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ATTACHMENTS --}}
                    <div class="space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-500">Attachments</label>
                        </div>
                        <div class="flex flex-col gap-3">
                            <input type="file" wire:model="photos" multiple class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-gray-800 dark:file:text-purple-400">
                            @if(count($existing_images) > 0 || count($photos) > 0)
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($existing_images as $index => $url)
                                        <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                            <img src="{{ $url }}" class="w-full h-full object-cover">
                                            <button type="button" wire:click="removeExistingImage({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                                        </div>
                                    @endforeach
                                    @foreach($photos as $index => $photo)
                                        <div class="relative group aspect-square rounded-lg overflow-hidden border border-purple-200 ring-2 ring-purple-400">
                                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover opacity-80">
                                            <button type="button" wire:click="removePhoto({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- GROUPS SELECTION --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">Labels</h4>
                            <button type="button" wire:click="openManageGroupsModal" class="text-[10px] font-bold text-purple-600 hover:underline">+ Manage</button>
                        </div>

                        @if($this->availableGroups->isEmpty())
                            <p class="text-xs text-gray-400">No groups created yet. Click Manage to add one.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->availableGroups as $group)
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-2 py-1 transition-all hover:bg-gray-50 dark:hover:bg-gray-800 {{ in_array($group->id, $selected_group_ids) ? 'border-purple-50 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                        <input type="checkbox" wire:model="selected_group_ids" value="{{ $group->id }}" class="h-3 w-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                        <span class="text-xs font-medium flex items-center gap-1" style="color: {{ in_array($group->id, $selected_group_ids) ? $group->color : 'inherit' }}">
                                            <div class="h-2 w-2 rounded-full" style="background-color: {{ $group->color }}"></div>
                                            {{ $group->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- LOCATION & NOTES --}}
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" wire:model="location" placeholder="Location Name" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <input type="url" wire:model="url" placeholder="https://" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <textarea wire:model="description" rows="2" placeholder="Notes..." class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>

                    <button type="submit" class="w-full rounded-xl bg-purple-600 py-3 text-sm font-bold text-white hover:bg-purple-700 shadow-lg hover:shadow-purple-500/20">
                        {{ $eventId ? 'Update Event' : 'Save Event' }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- MANAGE GROUPS MODAL --}}
    @if($isManageGroupsModalOpen)
        <x-calendar.modals.manage-labels
            :items="$this->availableGroups"
            createMethod="createGroup"
            deleteMethod="deleteGroup"
            nameModel="group_name"
            colorModel="group_color"
            :showSelectableIcon="false"
        />
    @endif

    {{-- EXPORT MODAL --}}
    @if($showExportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0">
            {{-- Backdrop --}}
            <div wire:click="closeExportModal" class="fixed inset-0 bg-gray-900/75 transition-opacity"></div>

            {{-- Modal Panel --}}
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Export Events</h3>
                    <button wire:click="closeExportModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-6">

                    {{-- 1. Format Selection --}}
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-900 dark:text-white">Export To</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:has-[:checked]:bg-purple-900/20">
                                <input type="radio" wire:model.live="exportFormat" value="calendar" class="sr-only">
                                <span class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Calendar</span>
                                <x-heroicon-o-calendar class="mx-auto mt-1 h-6 w-6 text-purple-600" />
                            </label>

                            <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:border-gray-700 dark:has-[:checked]:bg-green-900/20">
                                <input type="radio" wire:model.live="exportFormat" value="excel" class="sr-only">
                                <span class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Excel</span>
                                <x-heroicon-o-table-cells class="mx-auto mt-1 h-6 w-6 text-green-600" />
                            </label>

                            <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:border-gray-700 dark:has-[:checked]:bg-red-900/20">
                                <input type="radio" wire:model.live="exportFormat" value="pdf" class="sr-only">
                                <span class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">PDF</span>
                                <x-heroicon-o-document-text class="mx-auto mt-1 h-6 w-6 text-red-600" />
                            </label>
                        </div>
                    </div>

                    {{-- 2. Target Calendar (Only if Format is Calendar) --}}
                    @if($exportFormat === 'calendar')
                        <div class="animate-in fade-in slide-in-from-top-2">
                            <label class="mb-2 block text-sm font-bold text-gray-900 dark:text-white">Select Shared Calendar</label>
                            <select wire:model.live="exportTargetCalendarId" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="">-- Choose a Calendar --</option>
                                @foreach($allCollaborativeCalendars as $cal)
                                    <option value="{{ $cal->id }}">{{ $cal->name }}</option>
                                @endforeach
                            </select>
                            @error('exportTargetCalendarId') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- 3. Export Scope --}}
                    @if($exportMode !== 'single')
                        <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                            <label class="mb-2 block text-sm font-bold text-gray-900 dark:text-white">Which events?</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:has-[:checked]:bg-purple-900/20">
                                    <input type="radio" wire:model.live="exportMode" value="all" class="text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">All Events</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:has-[:checked]:bg-purple-900/20">
                                    <input type="radio" wire:model.live="exportMode" value="label" class="text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">By Label</span>
                                </label>
                            </div>
                        </div>

                        @if($exportMode === 'label')
                            <div>
                                <select wire:model.live="exportLabelId" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">-- Select Label --</option>
                                    @foreach($this->availableGroups as $label)
                                        <option value="{{ $label->id }}">{{ $label->name }}</option>
                                    @endforeach
                                </select>
                                @error('exportLabelId') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Copy Labels Checkbox (Calendar Only) --}}
                        @if($exportFormat === 'calendar')
                            <div class="mt-4">
                                <label class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="exportWithLabel" class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        <strong>Copy labels?</strong>
                                        <span class="block text-xs text-gray-500">Creates labels in the destination calendar.</span>
                                    </span>
                                </label>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button wire:click="closeExportModal" class="rounded-xl px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="exportEvents" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-purple-700 hover:shadow-purple-500/20">
                        {{ $exportFormat === 'calendar' ? 'Start Transfer' : 'Download File' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- DELETE/UPDATE MODALS --}}
    @if($isDeleteModalOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white text-center shadow-2xl dark:bg-gray-800">
                <div class="p-6">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400"><x-heroicon-o-trash class="h-6 w-6" /></div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Delete Event?</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This cannot be undone.</p>
                </div>
                <div class="flex border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="confirmDelete('instance')" class="flex-1 py-4 text-sm font-bold text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">Only This</button>
                    <div class="w-px bg-gray-100 dark:bg-gray-700"></div>
                    <button wire:click="confirmDelete('future')" class="flex-1 py-4 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">All Future</button>
                </div>
                <div class="border-t border-gray-100 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900">
                    <button wire:click="closeModal" class="w-full rounded-lg py-2 text-xs font-bold uppercase text-gray-400 hover:text-gray-600">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    @if($isUpdateModalOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white text-center shadow-2xl dark:bg-gray-800">
                <div class="p-6">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        <x-heroicon-o-arrow-path class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Update Repeating Event?</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Do you want to update only this instance or all future occurrences?</p>
                </div>
                <div class="flex border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="confirmUpdate('instance')" class="flex-1 py-4 text-sm font-bold text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">Only This Event</button>
                    <div class="w-px bg-gray-100 dark:bg-gray-700"></div>
                    <button wire:click="confirmUpdate('future')" class="flex-1 py-4 text-sm font-bold text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20">All Future Events</button>
                </div>
                <div class="border-t border-gray-100 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900">
                    <button wire:click="closeModal" class="w-full rounded-lg py-2 text-xs font-bold uppercase text-gray-400 hover:text-gray-600">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
