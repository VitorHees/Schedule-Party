<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-4 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">
    <div class="mx-auto max-w-6xl space-y-8">

        {{-- HEADER COMPONENT --}}
        <x-calendar.header
            :calendar="$calendar"
            :monthName="$monthName"
            :currentYear="$currentYear"
            :currentMonth="$currentMonth"
            :selectedDate="$selectedDate"
        >
            <x-slot:actions>
                <div class="flex flex-wrap gap-2">
                    {{-- EXPORT BUTTON --}}
                    <button wire:click="openExportModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                        <x-heroicon-o-arrow-up-on-square class="h-5 w-5" />
                        <span class="hidden sm:inline">Export</span>
                    </button>

                    {{-- LABELS BUTTON --}}
                    <button wire:click="openManageGroupsModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                        <x-heroicon-o-tag class="h-5 w-5" />
                        <span class="hidden sm:inline">Labels</span>
                    </button>

                    {{-- NEW EVENT BUTTON (Primary) --}}
                    <button wire:click="openCreateModal('{{ $selectedDate }}')" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-3 text-base font-bold text-white shadow-lg hover:bg-purple-700 transition-all hover:scale-105">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        <span>New Event</span>
                    </button>
                </div>
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

        {{-- AGENDA / EVENT LIST --}}
        <div class="space-y-6">
            <div class="flex items-center gap-4 px-1">
                <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300">
                    <span class="text-[10px] font-bold uppercase">{{ \Carbon\Carbon::parse($selectedDate)->format('M') }}</span>
                    <span class="text-xl font-bold">{{ \Carbon\Carbon::parse($selectedDate)->format('j') }}</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Agenda</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->format('l') }}</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($this->selectedDateEvents as $event)
                    <x-calendar.event-card
                        :event="$event"
                        :canExport="true"
                        wire:key="event-{{ $event->id }}-{{ $event->start_date }}"
                    />
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 py-12 text-center dark:border-gray-700 dark:bg-gray-800/50">
                        <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                            <x-heroicon-o-calendar class="h-8 w-8 text-gray-400" />
                        </div>
                        <h4 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">No plans yet</h4>
                        <p class="text-sm text-gray-500">Enjoy your free time!</p>
                        <button wire:click="openCreateModal('{{ $selectedDate }}')" class="mt-4 text-sm font-bold text-purple-600 hover:text-purple-700 hover:underline">
                            + Add Event
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= MODALS ================= --}}

    {{-- 1. CREATE / EDIT EVENT MODAL --}}
    <x-modal name="create_event" title="{{ $eventId ? 'Edit Event' : 'New Event' }}">
        <form wire:submit.prevent="saveEvent" class="space-y-5">
            {{-- Title --}}
            <div class="space-y-1">
                <input type="text" wire:model="title" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500" placeholder="Event Title (e.g., Dentist Appt)">
                @error('title') <span class="text-xs text-red-500 font-bold ml-1">{{ $message }}</span> @enderror
            </div>

            {{-- Date & Time --}}
            <x-calendar.date-range-picker
                :startDate="$start_date"
                :startTime="$start_time"
                :endDate="$end_date"
                :endTime="$end_time"
                :isAllDay="$is_all_day"
            />

            {{-- Repeat Logic --}}
            <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="is_all_day" class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">All Day</span>
                </label>
                <div class="flex flex-col items-end gap-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Repeats</span>
                        <select wire:model.live="repeat_frequency" class="rounded-lg border-none bg-transparent py-0 pl-0 pr-6 text-sm font-bold text-purple-600 focus:ring-0 dark:text-purple-400">
                            <option value="none">Never</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    @if($repeat_frequency !== 'none')
                        <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-1">
                            <label class="text-[10px] font-bold uppercase text-gray-400">Until</label>
                            <input type="date" wire:model="repeat_end_date" class="rounded-lg border-gray-200 bg-gray-50 py-1 px-2 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                    @endif
                </div>
            </div>

            {{-- File Uploader --}}
            <x-calendar.file-uploader
                :tempPhotos="$temp_photos"
                :existingImages="$existing_images"
                :photos="$photos"
                :uploadIteration="$uploadIteration"
            />

            {{-- Labels --}}
            @if($this->availableGroups->isNotEmpty())
                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">Labels</h4>
                        <button type="button" wire:click="openManageGroupsModal" class="text-[10px] font-bold text-purple-600 hover:underline">+ Manage</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->availableGroups as $group)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-1.5 transition-colors {{ in_array($group->id, $selected_group_ids) ? 'bg-purple-50 border-purple-200 dark:bg-purple-900/30 dark:border-purple-700' : 'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800' }}">
                                <input type="checkbox" wire:model="selected_group_ids" value="{{ $group->id }}" class="h-4 w-4 rounded text-purple-600 border-gray-300 focus:ring-purple-500">
                                <span class="text-xs font-bold" style="color: {{ $group->color }}">{{ $group->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center">
                    <button type="button" wire:click="openManageGroupsModal" class="text-xs font-bold text-purple-600 hover:underline">Create a Label</button>
                </div>
            @endif

            {{-- Location & URL --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="relative">
                    <x-heroicon-o-map-pin class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <input type="text" wire:model="location" placeholder="Location or Address" class="w-full rounded-xl border-gray-200 bg-gray-50 pl-10 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="relative">
                    <x-heroicon-o-link class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                    <input type="url" wire:model="url" placeholder="https://" class="w-full rounded-xl border-gray-200 bg-gray-50 pl-10 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>

            {{-- Description --}}
            <div class="space-y-1">
                <textarea wire:model="description" rows="3" placeholder="Notes, descriptions, or details..." class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <button type="submit" class="w-full rounded-xl bg-purple-600 py-3.5 text-sm font-bold text-white shadow-md hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 transition-all">
                {{ $eventId ? 'Update Event' : 'Save Event' }}
            </button>
        </form>
    </x-modal>

    {{-- 2. MANAGE LABELS MODAL --}}
    @if($activeModal === 'manage_groups')
        <x-calendar.modals.manage-labels
            :items="$this->availableGroups"
            createMethod="createGroup"
            deleteMethod="deleteGroup"
            nameModel="group_name"
            colorModel="group_color"
            :showSelectableIcon="false"
        />
    @endif

    {{-- 3. EXPORT MODAL --}}
    <x-modal name="export" title="Export Events">
        <div class="space-y-6">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase text-gray-500">Export Scope</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:hover:bg-gray-800 dark:has-[:checked]:bg-purple-900/30">
                        <input type="radio" wire:model.live="exportMode" value="all" class="sr-only">
                        <span class="block text-xs font-bold">All Events</span>
                    </label>
                    <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:hover:bg-gray-800 dark:has-[:checked]:bg-purple-900/30">
                        <input type="radio" wire:model.live="exportMode" value="label" class="sr-only">
                        <span class="block text-xs font-bold">By Label</span>
                    </label>
                    @if($exportEventId)
                        <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:hover:bg-gray-800 dark:has-[:checked]:bg-purple-900/30">
                            <input type="radio" wire:model.live="exportMode" value="single" class="sr-only">
                            <span class="block text-xs font-bold">Single</span>
                        </label>
                    @endif
                </div>
            </div>

            @if($exportMode === 'label')
                <div class="animate-in fade-in slide-in-from-top-2">
                    <select wire:model="exportLabelId" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">-- Choose --</option>
                        @foreach($this->availableGroups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="mb-2 block text-xs font-bold uppercase text-gray-500">Format / Destination</label>
                <div class="space-y-3">
                    <label class="flex items-center justify-between rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:ring-1 has-[:checked]:ring-purple-500 dark:border-gray-700 dark:hover:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                <x-heroicon-o-table-cells class="w-5 h-5" />
                            </div>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">Excel / CSV</span>
                        </div>
                        <input type="radio" wire:model.live="exportFormat" value="excel" class="h-4 w-4 text-purple-600">
                    </label>

                    <label class="flex items-center justify-between rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:ring-1 has-[:checked]:ring-purple-500 dark:border-gray-700 dark:hover:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <x-heroicon-o-document-text class="w-5 h-5" />
                            </div>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">PDF Document</span>
                        </div>
                        <input type="radio" wire:model.live="exportFormat" value="pdf" class="h-4 w-4 text-purple-600">
                    </label>

                    <label class="flex flex-col rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:ring-1 has-[:checked]:ring-purple-500 dark:border-gray-700 dark:hover:bg-gray-800">
                        <div class="flex w-full items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                    <x-heroicon-o-calendar class="w-5 h-5" />
                                </div>
                                <span class="font-bold text-sm text-gray-900 dark:text-white">To Shared Calendar</span>
                            </div>
                            <input type="radio" wire:model.live="exportFormat" value="calendar" class="h-4 w-4 text-purple-600">
                        </div>

                        @if($exportFormat === 'calendar')
                            <div class="ml-11 mt-2 animate-in fade-in slide-in-from-top-2">
                                <select wire:model="exportTargetCalendarId" class="w-full rounded-lg border-gray-300 text-xs dark:bg-gray-900 dark:border-gray-600">
                                    <option value="">-- Select Calendar --</option>
                                    @foreach($allCollaborativeCalendars as $cal)
                                        <option value="{{ $cal->id }}">{{ $cal->name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" wire:model="exportWithLabel" class="rounded text-purple-600">
                                        <span class="text-xs text-gray-500">Copy Labels</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </label>
                </div>
            </div>

            {{-- FOOTER BUTTONS (CONSISTENT SIZING) --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button wire:click="closeModal" class="rounded-xl px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                <button wire:click="exportEvents" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-purple-700 hover:shadow-purple-500/20">Export</button>
            </div>
        </div>
    </x-modal>

    {{-- OTHER CONFIRMATION MODALS (Unchanged logic, just ensure button classes match if needed) --}}
    <x-modal name="delete_confirmation" title="Delete Event?" maxWidth="sm">
        <div class="text-center p-2">
            <p class="text-sm text-gray-500 mb-6">This is a repeating event. How would you like to delete it?</p>
            <div class="flex flex-col gap-3">
                <button wire:click="confirmDelete('instance')" class="w-full rounded-xl bg-gray-100 py-3 text-sm font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-white">Only This Instance</button>
                <button wire:click="confirmDelete('future')" class="w-full rounded-xl bg-red-50 py-3 text-sm font-bold text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">This and Future Events</button>
                <button wire:click="closeModal" class="text-xs text-gray-400 underline hover:text-gray-600 mt-2">Cancel</button>
            </div>
        </div>
    </x-modal>

    <x-modal name="update_confirmation" title="Update Recurring Event" maxWidth="sm">
        <div class="text-center p-2">
            <p class="text-sm text-gray-500 mb-6">You are editing a repeating event.</p>
            <div class="flex flex-col gap-3">
                <button wire:click="confirmUpdate('instance')" class="w-full rounded-xl bg-gray-100 py-3 text-sm font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-white">Update Only This One</button>
                <button wire:click="confirmUpdate('future')" class="w-full rounded-xl bg-purple-50 py-3 text-sm font-bold text-purple-600 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400">Update All Future</button>
            </div>
        </div>
    </x-modal>
</div>
