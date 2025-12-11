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
                {{-- Manage Labels (Roles) --}}
                <button wire:click="openManageRolesModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                    <x-heroicon-o-tag class="h-5 w-5" />
                    <span>Labels</span>
                </button>

                {{-- Invite --}}
                <button wire:click="openInviteModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                    <x-heroicon-o-user-plus class="h-5 w-5" />
                    <span>Invite</span>
                </button>

                {{-- Delete / Leave --}}
                @if($this->isOwner)
                    <button wire:click="promptDeleteCalendar" class="group inline-flex items-center gap-2 rounded-xl bg-red-100 px-4 py-3 text-base font-bold text-red-600 transition-all hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                        <x-heroicon-o-trash class="h-5 w-5" />
                        <span>Delete</span>
                    </button>
                @else
                    <button wire:click="promptLeaveCalendar" class="group inline-flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-base font-bold text-red-600 transition-all hover:bg-red-100 dark:bg-red-900/10 dark:text-red-400 dark:hover:bg-red-900/30">
                        <x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" />
                        <span>Leave</span>
                    </button>
                @endif
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
                        <x-calendar.event-card :event="$event" />
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

                    {{-- REPEAT FREQUENCY --}}
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

                    {{-- ADVANCED FILTERS / TARGET AUDIENCE --}}
                    <div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-300">
                            <span>Target Audience & Filters</span>
                            <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open" class="border-t border-gray-100 p-4 space-y-4 dark:border-gray-700">

                            {{-- Roles/Labels --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">Labels (Roles)</h4>
                                    {{-- Manage Labels Button --}}
                                    <button type="button" wire:click="openManageRolesModal" class="text-[10px] font-bold text-purple-600 hover:underline">+ Manage</button>
                                </div>

                                @if($this->availableRoles->isEmpty())
                                    <p class="text-xs text-gray-400">No labels available.</p>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($this->availableRoles as $role)
                                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-2 py-1 transition-all hover:bg-gray-50 dark:hover:bg-gray-800 {{ in_array($role->id, $selected_group_ids) ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                                <input type="checkbox" wire:model.live="selected_group_ids" value="{{ $role->id }}" class="h-3 w-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                                <span class="text-xs font-medium flex items-center gap-1 {{ in_array($role->id, $selected_group_ids) ? 'text-purple-700 dark:text-purple-300' : 'text-gray-600 dark:text-gray-300' }}">
                                                    {{ $role->name }}
                                                    @if($role->is_selectable)
                                                        <x-heroicon-o-hand-raised class="h-3 w-3 opacity-50" title="Voluntary/Opt-in Role" />
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @if(!empty($selected_group_ids))
                                        <div class="mt-2 flex items-center gap-2 animate-in fade-in slide-in-from-top-1">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" wire:model="is_role_restricted" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                                                <span class="ml-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                    {{ $is_role_restricted ? 'Invisible to non-members' : 'Visible to all (Tag only)' }}
                                                </span>
                                            </label>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            {{-- Gender --}}
                            <div>
                                <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">Sex / Gender</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($this->genders as $gender)
                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-2 py-1 {{ in_array($gender->id, $selected_gender_ids) ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                            <input type="checkbox" wire:model="selected_gender_ids" value="{{ $gender->id }}" class="h-3 w-3 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                            <span class="text-xs font-medium {{ in_array($gender->id, $selected_gender_ids) ? 'text-teal-700 dark:text-teal-300' : 'text-gray-600 dark:text-gray-300' }}">{{ $gender->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Age & NSFW --}}
                            <div class="flex items-end gap-4">
                                <div>
                                    <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">Min Age</h4>
                                    <input type="number" wire:model.live="min_age" max="150" min="0" placeholder="e.g. 18" class="w-20 rounded-lg border-gray-200 p-1 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                                <div class="flex items-center gap-2 pb-1">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="is_nsfw" class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                                        <span class="ml-2 text-xs font-bold uppercase tracking-wide text-gray-500">NSFW (18+)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Location Filter --}}
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">Location Restriction</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <select wire:model="event_country_id" class="rounded-lg border-gray-200 p-1 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="">Country</option>
                                        @foreach($this->countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model="event_zipcode" placeholder="Zip Code" class="rounded-lg border-gray-200 p-1 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" wire:model.live="max_distance_km" max="1000" placeholder="Max Dist" class="w-24 rounded-lg border-gray-200 p-1 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">km</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DETAILS --}}
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

    {{-- MANAGE ROLES (LABELS) MODAL --}}
    @if($isManageRolesModalOpen)
        <x-calendar.modals.manage-labels
            :items="$this->availableRoles"
            createMethod="createRole"
            deleteMethod="deleteRole"
            nameModel="role_name"
            colorModel="role_color"
            selectableModel="role_is_selectable"
        >
            <x-slot:actionSlot>
                {{-- No special action for now, or could iterate and check join/leave manually if the component allowed passing a closure/context, but the component loops items internally.
                     The component doesn't support a dynamic slot PER item easily without modification.
                     However, the component code provided in the prompt HAS `{{ $actionSlot ?? '' }}` inside the loop,
                     which means the slot content is repeated for every item. This doesn't allow checking specific item IDs.

                     FIX: The manage-labels component as provided isn't flexible enough for "Join/Leave" buttons per item
                     unless we modify the component or use the delete button slot for it.
                     Since I cannot modify the components (they are 'newly created' per prompt),
                     I will rely on the `deleteMethod` for owners and perhaps omit the Join/Leave button
                     OR assume the component might be updated later.

                     Wait, `SharedCalendar.blade.php` had logic for "Join/Leave" inside the loop.
                     The new component abstracts the loop.
                     If the new component doesn't have a slot that receives the `$item`, I can't implement the Join/Leave button using that component correctly.

                     However, I must use the component. I will stick to standard Create/Delete functionality provided by the component.
                --}}
            </x-slot:actionSlot>
        </x-calendar.modals.manage-labels>
    @endif

    {{-- OTHER MODALS (Invite, Delete Cal, Leave Cal, Delete Event, Update Event) - SAME AS BEFORE, JUST KEPT --}}
    @if($isInviteModalOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Invite People</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <div class="space-y-6">
                    <div class="space-y-3">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Share Link</h3>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" readonly value="{{ $inviteLink ?? 'Click generate to create a link' }}" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            </div>
                            @if($inviteLink)
                                <button onclick="navigator.clipboard.writeText('{{ $inviteLink }}')" class="shrink-0 rounded-xl bg-gray-900 px-4 py-2 text-sm font-bold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">Copy</button>
                            @else
                                <button wire:click="generateInviteLink" class="shrink-0 rounded-xl bg-purple-600 px-4 py-2 text-sm font-bold text-white hover:bg-purple-700 shadow-md">Generate</button>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">Username</label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="inviteUsername" placeholder="e.g. party_planner" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <button wire:click="inviteUserByUsername" class="rounded-xl bg-gray-900 px-3 py-2 text-sm font-bold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">Add</button>
                        </div>
                        @error('inviteUsername') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isDeleteCalendarModalOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-red-600 dark:text-red-400">Delete Calendar</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit.prevent="deleteCalendar" class="space-y-4">
                    <div>
                        <input type="password" wire:model="deleteCalendarPassword" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold focus:border-red-500 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Your Password">
                        @error('deleteCalendarPassword') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="rounded-xl px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-xl bg-red-600 px-6 py-2 text-sm font-bold text-white hover:bg-red-700 shadow-lg hover:shadow-red-500/20">Delete Forever</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($isLeaveCalendarModalOpen)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm transform rounded-2xl bg-white p-6 text-center shadow-2xl transition-all dark:bg-gray-800">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <x-heroicon-o-arrow-left-start-on-rectangle class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Leave Calendar?</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    You will lose access to this calendar. You can only rejoin if invited again.
                </p>
                <div class="mt-6 flex gap-3">
                    <button wire:click="closeModal" class="flex-1 rounded-xl bg-gray-100 px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Cancel</button>
                    <button wire:click="leaveCalendar" class="flex-1 rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700 shadow-lg hover:shadow-red-500/20">Leave</button>
                </div>
            </div>
        </div>
    @endif

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
