<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

    <div class="mx-auto max-w-6xl space-y-8">
        {{-- HEADER --}}
        <x-calendar.header
            :calendar="$calendar"
            :monthName="$monthName"
            :currentYear="$currentYear"
            :currentMonth="$currentMonth"
            :selectedDate="$selectedDate"
            :canCreateEvents="$this->checkPermission('create_events')"
        >
            <x-slot:actions>
                <div class="flex flex-wrap gap-2">
                    {{-- Manage Labels --}}
                    @if($this->checkPermission('create_labels') || $this->checkPermission('join_labels') || $this->checkPermission('join_private_labels'))
                        <button wire:click="openManageRolesModal" class="hidden md:inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                            <x-heroicon-o-tag class="h-4 w-4" />
                            <span>Labels</span>
                        </button>
                    @endif

                    {{-- Manage Members --}}
                    @if($this->checkPermission('manage_settings'))
                        <button wire:click="openManageMembersModal" class="hidden md:inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                            <x-heroicon-o-users class="h-4 w-4" />
                            <span>Members</span>
                        </button>
                    @endif

                    {{-- Permissions --}}
                    @if($this->checkPermission('manage_role_permissions') || $this->checkPermission('manage_label_permissions') || $this->checkPermission('manage_user_permissions'))
                        <button wire:click="openPermissionsModal" class="hidden md:inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                            <x-heroicon-o-shield-check class="h-4 w-4" />
                            <span>Permissions</span>
                        </button>
                    @endif

                    {{-- Invite --}}
                    @if($this->checkPermission('invite_users'))
                        <button wire:click="openInviteModal" class="hidden md:inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                            <x-heroicon-o-user-plus class="h-4 w-4" />
                            <span>Invite</span>
                        </button>
                    @endif

                    {{-- Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2 text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                            <x-heroicon-o-ellipsis-vertical class="h-5 w-5" />
                        </button>

                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="absolute right-0 top-full z-50 mt-2 w-48 origin-top-right overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                            style="display: none;"
                        >
                            {{-- EXPORT BUTTON --}}
                            @if($this->checkPermission('view_events'))
                                <button wire:click="openExportModal" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Export Calendar
                                </button>
                            @endif

                            @if($this->checkPermission('view_logs'))
                                <button wire:click="openLogsModal" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Activity Log
                                </button>
                            @endif

                            <div class="md:hidden border-t border-gray-100 dark:border-gray-700">
                                {{-- Mobile buttons --}}
                                @if($this->checkPermission('invite_users'))
                                    <button wire:click="openInviteModal" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                        Invite
                                    </button>
                                @endif
                                @if($this->checkPermission('create_labels') || $this->checkPermission('join_labels') || $this->checkPermission('join_private_labels'))
                                    <button wire:click="openManageRolesModal" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                        Manage Labels
                                    </button>
                                @endif

                                <button wire:click="openManageMembersModal" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Manage Members
                                </button>

                                @if($this->checkPermission('manage_role_permissions') || $this->checkPermission('manage_label_permissions') || $this->checkPermission('manage_user_permissions'))
                                    <button wire:click="openPermissionsModal" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                        Permissions
                                    </button>
                                @endif
                            </div>

                            <div class="border-t border-gray-100 dark:border-gray-700">
                                @if($this->isOwner)
                                    <button wire:click="promptDeleteCalendar" class="block w-full px-4 py-2 text-left text-sm font-bold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        Delete Calendar
                                    </button>
                                @else
                                    <button wire:click="promptLeaveCalendar" class="block w-full px-4 py-2 text-left text-sm font-bold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                        Leave Calendar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:actions>
        </x-calendar.header>

        {{-- CALENDAR GRID --}}
        @if($this->checkPermission('view_events'))
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
        @else
            <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center dark:border-gray-700 dark:bg-gray-800">
                <x-heroicon-o-lock-closed class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Access Restricted</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">You do not have permission to view events on this calendar.</p>
            </div>
        @endif

        {{-- AGENDA STREAM --}}
        @if($this->checkPermission('view_events'))
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
                        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 py-12 text-center dark:border-gray-700 dark:bg-gray-800/50">
                            <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-800">
                                <x-heroicon-o-calendar class="h-8 w-8 text-gray-400" />
                            </div>
                            <h4 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">No plans yet</h4>
                            <p class="text-sm text-gray-500">Events scheduled for this day will appear here.</p>
                            @if($this->checkPermission('create_events'))
                                <button wire:click="openModal('{{ $selectedDate }}')" class="mt-4 text-sm font-bold text-purple-600 hover:text-purple-700 hover:underline">+ Add an event</button>
                            @endif
                        </div>
                    @else
                        @foreach($this->selectedDateEvents as $event)
                            <x-calendar.event-card
                                wire:key="event-{{ $event->id }}"
                                :event="$event"
                                :commentLimit="$commentLimits[$event->id] ?? 5"
                                :newComment="$commentInputs[$event->id] ?? ''"
                                :pollSelections="$pollSelections"
                                :canExport="$this->checkPermission('view_events')"
                                :canEditAny="$this->checkPermission('edit_any_event')"
                                :canDeleteAny="$this->checkPermission('delete_any_event')"
                                :canViewComments="$this->checkPermission('view_comments')"
                                :canPostComments="$this->checkPermission('create_comment')"
                                :canDeleteAnyComment="$this->checkPermission('delete_any_comment')"
                                :canAttend="$this->checkPermission('rsvp_event')"
                                :canVote="$this->checkPermission('vote_poll')"
                            />
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ================= MODALS ================= --}}

    {{-- 1. CREATE / EDIT EVENT MODAL --}}
    @if($activeModal === 'create_event')
        <x-modal name="create_event" title="{{ $eventId ? 'Edit Event' : 'New Event' }}">
            <form wire:submit.prevent="{{ $eventId ? 'confirmUpdate(\'single\')' : 'performCreate' }}" class="space-y-5">
                {{-- Note: For Update, we intercept with a confirmation if repeating, handled by PHP. Here we default to performCreate or prompt --}}
                {{-- Actually, let's stick to a generic save method that decides --}}
                {{-- But referencing previous logic: --}}
                {{-- If ID exists, we trigger 'confirmUpdate' logic or standard update. Let's use a proxy method or direct binding --}}
                {{-- The simplest is to bind to a 'saveEvent' method in PHP which decides --}}
                {{-- Checking your PHP: You don't have a 'saveEvent' method. You have performCreate and performUpdate. --}}
                {{-- Let's add a proxy form submit handler inline or assume you added saveEvent. --}}
                {{-- I'll map it to 'performCreate' if new, or 'performUpdate' if editing (non-repeating). --}}
                {{-- However, since repeating events need a modal, let's just trigger a 'saveEvent' method I will assume you add, OR just use `wire:click` on the button. --}}
                {{-- Let's go with `wire:click` on the button to be safe. --}}

                {{-- Title --}}
                <div class="space-y-1">
                    <input
                        type="text"
                        wire:model="title"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:bg-white focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500 dark:focus:bg-gray-900 transition-colors"
                        placeholder="Event Title (e.g., Team Meeting)"
                    >
                    @error('title') <span class="text-xs text-red-500 font-bold ml-1">{{ $message }}</span> @enderror
                </div>

                {{-- Date & Time Picker Component --}}
                <x-calendar.date-range-picker
                    :startDate="$start_date"
                    :startTime="$start_time"
                    :endDate="$end_date"
                    :endTime="$end_time"
                    :isAllDay="$is_all_day"
                />

                {{-- Repeat & All Day Logic --}}
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
                                @php $days = $this->durationInDays; @endphp
                                <option value="daily"   @if($days >= 1) disabled class="text-gray-300" @endif>Daily</option>
                                <option value="weekly"  @if($days >= 7) disabled class="text-gray-300" @endif>Weekly</option>
                                <option value="monthly" @if($days >= 28) disabled class="text-gray-300" @endif>Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        @if($repeat_frequency !== 'none')
                            <div class="flex items-center gap-2 animate-in fade-in slide-in-from-top-1">
                                <label class="text-[10px] font-bold uppercase text-gray-400">Until</label>
                                <input type="date" wire:model="repeat_end_date" class="rounded-lg border-gray-200 bg-gray-50 py-1 px-2 text-xs text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:bg-gray-900">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- File Uploader Component --}}
                @if($this->checkPermission('add_images'))
                    <x-calendar.file-uploader
                        :tempPhotos="$temp_photos"
                        :existingImages="$existing_images"
                        :photos="$photos"
                        :uploadIteration="$uploadIteration"
                    />
                @endif

                {{-- Location & URL --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="relative">
                        <x-heroicon-o-map-pin class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                        <input
                            type="text"
                            wire:model="location"
                            placeholder="Location or Address"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 pl-10 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
                        >
                    </div>
                    <div class="relative">
                        <x-heroicon-o-link class="absolute left-3 top-3 h-5 w-5 text-gray-400" />
                        <input
                            type="url"
                            wire:model="url"
                            placeholder="https://"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 pl-10 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
                        >
                    </div>
                </div>

                {{-- Description --}}
                <div class="space-y-1">
                <textarea
                    wire:model="description"
                    rows="3"
                    placeholder="Notes, descriptions, or details..."
                    class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900 transition-colors"
                ></textarea>
                </div>

                {{-- Features Toggles --}}
                <div class="flex flex-wrap gap-4 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="comments_enabled" class="h-4 w-4 rounded text-purple-600 focus:ring-purple-500 dark:bg-gray-800">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Comments</span>
                    </label>
                    @if($this->checkPermission('rsvp_event'))
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="opt_in_enabled" class="h-4 w-4 rounded text-purple-600 focus:ring-purple-500 dark:bg-gray-800">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">RSVP / Opt-in</span>
                        </label>
                    @endif
                </div>

                {{-- Advanced Filters (Roles, Gender, Polls) --}}
                <div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-300">
                        <span>Target Audience & Filters</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform" ::class="open ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="open" class="border-t border-gray-100 p-4 space-y-4 dark:border-gray-700">
                        {{-- Roles/Labels --}}
                        @if($this->checkPermission('add_labels'))
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-bold uppercase tracking-wide text-gray-500">Labels (Roles)</h4>
                                    @if($this->checkPermission('create_labels'))
                                        <button type="button" wire:click="openManageRolesModal" class="text-[10px] font-bold text-purple-600 hover:underline">+ Manage</button>
                                    @endif
                                </div>

                                @if($this->availableRoles->isEmpty())
                                    <p class="text-xs text-gray-400">No labels available.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($this->availableRoles as $role)
                                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-2 dark:border-gray-700 {{ in_array($role->id, $selected_group_ids) ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-200' : '' }}">
                                                <label class="flex items-center gap-2 cursor-pointer flex-1">
                                                    <input type="checkbox" wire:model.live="selected_group_ids" value="{{ $role->id }}" class="h-3 w-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                                    <span class="text-xs font-medium flex items-center gap-1 {{ in_array($role->id, $selected_group_ids) ? 'text-purple-700 dark:text-purple-300' : 'text-gray-600 dark:text-gray-300' }}">
                                                    {{ $role->name }}
                                                        @if($role->is_private)
                                                            <x-heroicon-s-lock-closed class="w-3 h-3 text-red-400" title="Private (Owner Only)" />
                                                        @elseif($role->is_selectable)
                                                            <x-heroicon-o-hand-raised class="w-3 h-3 text-gray-400" title="Voluntary / Opt-in" />
                                                        @else
                                                            <x-heroicon-o-folder class="w-3 h-3 text-gray-400" title="Sorting Category" />
                                                        @endif
                                                </span>
                                                </label>

                                                @if(in_array($role->id, $selected_group_ids) && $role->is_selectable)
                                                    <div class="flex items-center gap-2 border-l border-gray-200 pl-3 dark:border-gray-600">
                                                    <span class="text-[10px] font-bold uppercase {{ ($group_restrictions[$role->id] ?? false) ? 'text-red-500' : 'text-gray-400' }}">
                                                        {{ ($group_restrictions[$role->id] ?? false) ? 'Private' : 'Public' }}
                                                    </span>
                                                        <button type="button" wire:click="toggleRestriction({{ $role->id }})"
                                                                class="relative inline-flex h-4 w-7 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($group_restrictions[$role->id] ?? false) ? 'bg-red-500' : 'bg-gray-200 dark:bg-gray-700' }}"
                                                                title="Toggle Visibility Barrier">
                                                            <span class="pointer-events-none inline-block h-3 w-3 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($group_restrictions[$role->id] ?? false) ? 'translate-x-3' : 'translate-x-0' }}"></span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

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

                        {{-- Age & Distance --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Min Age</label>
                                <input type="number" wire:model="min_age" placeholder="e.g. 18" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Max Distance (KM)</label>
                                <input type="number" wire:model="max_distance_km" placeholder="e.g. 20" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                            </div>
                        </div>

                        {{-- NSFW --}}
                        <label class="flex items-center gap-2 cursor-pointer pt-2">
                            <input type="checkbox" wire:model="is_nsfw" class="h-4 w-4 rounded text-red-600 focus:ring-red-500 dark:bg-gray-800">
                            <span class="text-xs font-bold text-red-600">NSFW (18+)</span>
                        </label>
                    </div>
                </div>

                {{-- Poll Creator --}}
                @if($this->checkPermission('create_poll'))
                    <div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-4 py-3 text-sm font-bold text-gray-700 dark:text-gray-300">
                            <span>{{ $eventId ? 'Manage Poll' : 'Add Poll (Vote)' }}</span>
                            <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="open || '{{ $poll_title }}'.length > 0" class="border-t border-gray-100 p-4 space-y-3 dark:border-gray-700">
                            <input type="text" wire:model="poll_title" placeholder="Question / Poll Title" class="w-full rounded-lg border-gray-200 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                            <div class="space-y-2">
                                @foreach($poll_options as $index => $option)
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="poll_options.{{ $index }}" placeholder="Option {{ $index + 1 }}" class="w-full rounded-lg border-gray-200 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                                        @if($index > 1)
                                            <button type="button" wire:click="removePollOption({{ $index }})" class="text-red-500"><x-heroicon-o-x-mark class="h-4 w-4" /></button>
                                        @endif
                                    </div>
                                @endforeach
                                <button type="button" wire:click="addPollOption" class="text-xs font-bold text-purple-600 hover:text-purple-700">+ Add Option</button>
                            </div>
                            <div class="flex items-center gap-4 border-t border-gray-100 pt-3 dark:border-gray-700">
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400">Max Votes:</label>
                                    <input type="number" wire:model="poll_max_selections" min="1" class="w-16 rounded-lg border-gray-200 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="poll_is_public" class="h-4 w-4 rounded text-purple-600 dark:bg-gray-800">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Public Results</span>
                                </label>
                            </div>
                            @if($eventId)
                                <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold mt-2">
                                    Note: Changing options or title will reset existing votes.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="saveEvent"
                    class="w-full rounded-xl bg-purple-600 py-3.5 text-sm font-bold text-white shadow-md hover:bg-purple-700 focus:ring-4 focus:ring-purple-200 transition-all"
                >
                    {{ $eventId ? 'Update Event' : 'Save Event' }}
                </button>
            </form>
        </x-modal>
    @endif

    {{-- 2. EXPORT MODAL --}}
    @if($activeModal === 'export')
        <x-modal name="export" title="Export Events">
            <div class="space-y-6">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-gray-500">Export Scope</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:hover:bg-gray-800 dark:has-[:checked]:bg-purple-900/30">
                            <input type="radio" wire:model.live="exportMode" value="all" class="sr-only">
                            <span class="block text-xs font-bold">All Events</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all hover:bg-gray-50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:hover:bg-gray-800 dark:has-[:checked]:bg-purple-900/30">
                            <input type="radio" wire:model.live="exportMode" value="label" class="sr-only">
                            <span class="block text-xs font-bold">By Label</span>
                        </label>
                    </div>
                </div>

                @if($exportMode === 'label')
                    <div class="animate-in fade-in slide-in-from-top-2">
                        <select wire:model.live="exportLabelId" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 text-sm text-gray-900 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                            <option value="">-- Select Label --</option>
                            @foreach($this->calendar->groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('exportLabelId') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
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
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="closeModal" class="rounded-xl px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                    <button wire:click="exportEvents" class="rounded-xl bg-purple-600 px-6 py-2 text-sm font-bold text-white shadow hover:bg-purple-700">Export</button>
                </div>
            </div>
        </x-modal>
    @endif

    @if($activeModal === 'edit_calendar_name')
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit Calendar Name</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit.prevent="updateCalendarName" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-gray-500">New Name</label>
                        {{-- Change wire:model and error reference --}}
                        <input
                            type="text"
                            wire:model="editingCalendarName"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold text-gray-900 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900"
                            placeholder="Enter name..."
                        >
                        @error('editingCalendarName') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="rounded-xl px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2 text-sm font-bold text-white hover:bg-purple-700 shadow-lg">Save Name</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- 3. MANAGE ROLES (LABELS) MODAL --}}
    @if($activeModal === 'manage_roles')
        <x-calendar.modals.manage-labels
            :items="$this->availableRoles"
            :editingGroupId="$editingGroupId"
            createMethod="createRole"
            deleteMethod="deleteRole"
            nameModel="role_name"
            colorModel="role_color"
            selectableModel="role_is_selectable"
            privateModel="role_is_private"
            toggleMethod="toggleRoleMembership"
            :assignedIds="$this->userRoleIds"
            :canCreate="$this->checkPermission('create_labels')"
            :canCreateSelectable="$this->checkPermission('create_selectable_labels')"
            :canCreatePrivate="$this->checkPermission('assign_labels')"
            :canDelete="$this->checkPermission('delete_any_label')"
        >
            <x-slot:actionSlot></x-slot:actionSlot>
        </x-calendar.modals.manage-labels>
    @endif

    {{-- 4. INVITE MODAL --}}
    @if($activeModal === 'invite')
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Invite People</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex border-b border-gray-200 mb-6 dark:border-gray-700">
                    <button wire:click="setInviteTab('create')" class="px-4 py-2 text-sm font-bold border-b-2 transition-colors {{ $inviteModalTab === 'create' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Create Invite</button>
                    @if($this->checkPermission('view_active_links'))
                        <button wire:click="setInviteTab('list')" class="px-4 py-2 text-sm font-bold border-b-2 transition-colors {{ $inviteModalTab === 'list' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">Active Links</button>
                    @endif
                </div>

                @if($inviteModalTab === 'create')
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Share Link</h3>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="text" readonly value="{{ $inviteLink ?? 'Click generate to create a link' }}" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                </div>
                                @if($inviteLink)
                                    <button
                                        x-data="{ copied: false }"
                                        @click="navigator.clipboard.writeText('{{ $inviteLink }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="shrink-0 rounded-xl bg-gray-900 px-4 py-2 text-sm font-bold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                    >
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" class="text-green-400">Copied!</span>
                                    </button>
                                @else
                                    <button wire:click="generateInviteLink" class="shrink-0 rounded-xl bg-purple-600 px-4 py-2 text-sm font-bold text-white hover:bg-purple-700 shadow-md">Generate</button>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">This link is permanent and can be used by multiple people.</p>
                        </div>
                        <div class="space-y-3">
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">Username</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="inviteUsername" placeholder="e.g. party_planner" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                                <button wire:click="inviteUserByUsername" class="rounded-xl bg-gray-900 px-3 py-2 text-sm font-bold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">Add</button>
                            </div>
                            @error('inviteUsername') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @else
                    <div class="max-h-[300px] overflow-y-auto pr-1">
                        @if($this->activeInvites->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">No active invite links found.</div>
                        @else
                            <div class="space-y-3">
                                @foreach($this->activeInvites as $invite)
                                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="flex-1 min-w-0 pr-4">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">{{ $invite->role->name ?? 'Member' }}</span>
                                                <span class="text-xs text-gray-400">By {{ $invite->creator->username ?? 'Unknown' }}</span>
                                            </div>
                                            <div class="mt-2 flex items-center gap-2">
                                                <code class="truncate rounded bg-gray-200 px-1.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">...{{ substr($invite->token, -8) }}</code>
                                                <button
                                                    x-data="{ copied: false }"
                                                    @click="navigator.clipboard.writeText('{{ route('invitations.accept', $invite->token) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                    class="text-xs font-bold hover:text-purple-700"
                                                    :class="copied ? 'text-green-500' : 'text-purple-600 dark:text-purple-400'"
                                                >
                                                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 shrink-0">
                                            <div class="text-center"><p class="text-lg font-bold text-gray-900 dark:text-white">{{ $invite->usage_count }}</p><p class="text-[10px] uppercase font-bold text-gray-400">Joined</p></div>
                                            @if($this->checkPermission('manage_invites'))
                                                <button wire:click="deleteInvite({{ $invite->id }})" class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" title="Revoke Link"><x-heroicon-o-trash class="h-5 w-5" /></button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- 5. ACTIVITY LOGS MODAL --}}
    @if($activeModal === 'logs')
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Activity Log</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <div class="mb-4 flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live="logSearch" placeholder="Search by username..." class="w-full rounded-xl border-gray-200 bg-gray-50 pl-10 pr-4 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="sm:w-1/3">
                        <select wire:model.live="logActionFilter" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900">
                            <option value="">All Actions</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                            <option value="commented">Commented</option>
                            <option value="joined">Joined</option>
                            <option value="left">Left</option>
                            <option value="voted">Voted</option>
                        </select>
                    </div>
                </div>
                <div class="max-h-[400px] overflow-y-auto pr-1 space-y-4">
                    @forelse($this->logs as $log)
                        <div class="flex items-start gap-3 border-b border-gray-100 pb-3 last:border-0 dark:border-gray-700">
                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                <x-heroicon-s-information-circle class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $log->description }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                            No activity found matching your criteria.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- 6. MANAGE MEMBERS MODAL --}}
    @if($activeModal === 'manage_members')
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Calendar Members</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Manage roles and access.</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <div class="max-h-[400px] overflow-y-auto pr-1">
                    <div class="space-y-3">
                        @foreach($this->members as $member)
                            @php
                                $isSelf = $member->id === auth()->id();
                                $targetIsOwner = $member->role_slug === 'owner';
                                $targetIsAdmin = $member->role_slug === 'admin';
                                $currentUserIsOwner = $this->isOwner;
                                $currentUserIsAdmin = $this->isAdmin;
                                $hasLabelPerm = $this->checkPermission('assign_labels');
                                $hasKickPerm = $this->checkPermission('kick_users');
                                $hasUserPermsPerm = $this->checkPermission('manage_user_permissions');
                                $showLabels = $hasLabelPerm;
                                $canKickTarget = $currentUserIsOwner || ($hasKickPerm && !$targetIsOwner && !$targetIsAdmin);
                                $showKick = $canKickTarget;
                                $canEditPerms = $currentUserIsOwner || ($hasUserPermsPerm && !$targetIsOwner && !$targetIsAdmin);
                                $showPermissions = $canEditPerms;
                                $showPromote = $currentUserIsOwner;
                                $showMenu = !$isSelf && ($showLabels || $showKick || $showPermissions || $showPromote);
                            @endphp
                            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $member->profile_picture ? Storage::url($member->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($member->username).'&background=random' }}" class="h-10 w-10 rounded-full bg-gray-200 object-cover">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $member->username }}
                                            @if($member->id === auth()->id()) <span class="text-xs font-normal text-gray-400">(You)</span> @endif
                                        </h4>
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $member->role_slug === 'owner' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' }}">
                                            {{ $member->role_name }}
                                        </span>
                                    </div>
                                </div>
                                @if($showMenu)
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="rounded-lg p-2 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                                            <x-heroicon-o-ellipsis-vertical class="h-5 w-5" />
                                        </button>
                                        <div x-show="open" @click.away="open = false" class="absolute right-0 top-full z-10 mt-1 w-48 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                            @if($showPermissions)
                                                <button wire:click="openPermissionsModal('users', {{ $member->id }})" class="w-full px-4 py-2 text-left text-xs font-bold text-purple-600 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/20 border-b border-gray-100 dark:border-gray-700">
                                                    Edit Permissions
                                                </button>
                                            @endif
                                            @if($showLabels)
                                                <button wire:click="openManageMemberLabels({{ $member->id }})" class="w-full px-4 py-2 text-left text-xs font-medium hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700">
                                                    Manage Labels
                                                </button>
                                            @endif
                                            @if($showKick)
                                                <button wire:click="kickMember({{ $member->id }})" class="w-full px-4 py-2 text-left text-xs font-bold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 border-b border-gray-100 dark:border-gray-700">
                                                    Kick User
                                                </button>
                                            @endif
                                            @if($showPromote)
                                                @if($member->role_slug !== 'admin')
                                                    <button wire:click="changeRole({{ $member->id }}, 'admin')" class="w-full px-4 py-2 text-left text-xs font-medium hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">Promote to Admin</button>
                                                @else
                                                    <button wire:click="changeRole({{ $member->id }}, 'member')" class="w-full px-4 py-2 text-left text-xs font-medium hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">Demote to Member</button>
                                                @endif
                                                <button wire:click="changeRole({{ $member->id }}, 'owner')" class="w-full px-4 py-2 text-left text-xs font-bold text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/20">Promote to Owner</button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- 7. MANAGE MEMBER LABELS MODAL --}}
    @if($activeModal === 'manage_member_labels')
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manage Labels</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">For {{ $managingMemberName }}</p>
                    </div>
                    <button wire:click="closeManageMemberLabels" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                    @php $selectableRoles = $this->availableRoles->where('is_selectable', true); @endphp
                    @forelse($selectableRoles as $role)
                        <div wire:key="member-label-{{ $role->id }}" class="flex items-center justify-between rounded-lg border border-gray-100 p-3 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="h-3 w-3 rounded-full" style="background-color: {{ $role->color }}"></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1">
                                        {{ $role->name }}
                                        @if($role->is_private)
                                            <x-heroicon-s-lock-closed class="w-3 h-3 text-red-400" title="Private (Owner Only)" />
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <button wire:click="toggleMemberLabel({{ $role->id }})" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none {{ in_array($role->id, $this->managingMemberRoles) ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                <span class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform {{ in_array($role->id, $this->managingMemberRoles) ? 'translate-x-5' : 'translate-x-1' }}"/>
                            </button>
                        </div>
                    @empty
                        <p class="text-center text-sm text-gray-500">No selectable labels found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- 8. TRANSFER OWNERSHIP CONFIRMATION --}}
    @if($activeModal === 'promote_owner')
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-amber-600 dark:text-amber-400">Transfer Ownership?</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <div class="mb-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
                    <p class="font-bold">Warning:</p>
                    <p>You are about to transfer ownership of this calendar. You will be demoted to Member and will lose full control.</p>
                </div>
                <form wire:submit.prevent="promoteOwner" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-gray-500">Confirm Password</label>
                        <input type="password" wire:model="promoteOwnerPassword" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900" placeholder="Your Password">
                        @error('promoteOwnerPassword') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="rounded-xl px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-xl bg-amber-600 px-6 py-2 text-sm font-bold text-white hover:bg-amber-700 shadow-lg hover:shadow-amber-500/20">Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- 9. DELETE CALENDAR MODAL --}}
    @if($activeModal === 'delete_calendar')
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
                        <input type="password" wire:model="deleteCalendarPassword" class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 font-semibold text-gray-900 focus:border-red-500 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:focus:bg-gray-900" placeholder="Your Password">
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

    {{-- 10. LEAVE CALENDAR MODAL --}}
    @if($activeModal === 'leave_calendar')
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

    {{-- 11. DELETE EVENT CONFIRMATION --}}
    <x-modal name="delete_confirmation" title="Delete Event?" maxWidth="sm">
        <div class="text-center p-2">
            <p class="text-sm text-gray-500 mb-6">This is a repeating event. How would you like to delete it?</p>
            <div class="flex flex-col gap-3">
                <button wire:click="confirmDelete('instance')" class="w-full rounded-xl bg-gray-100 py-3 text-sm font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-white">
                    Only This Instance
                    <span class="block text-[10px] font-normal text-gray-500">Deletes {{ \Carbon\Carbon::parse($eventToDeleteDate)->format('M j') }} only</span>
                </button>
                <button wire:click="confirmDelete('future')" class="w-full rounded-xl bg-red-50 py-3 text-sm font-bold text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400">
                    This and Future Events
                    <span class="block text-[10px] font-normal opacity-70">Stops the series here</span>
                </button>
                <button wire:click="closeModal" class="text-xs text-gray-400 underline hover:text-gray-600 mt-2">Cancel</button>
            </div>
        </div>
    </x-modal>

    {{-- 12. UPDATE EVENT CONFIRMATION --}}
    <x-modal name="update_confirmation" title="Update Recurring Event" maxWidth="sm">
        <div class="text-center p-2">
            <p class="text-sm text-gray-500 mb-6">You are editing a repeating event.</p>
            <div class="flex flex-col gap-3">
                <button wire:click="confirmUpdate('instance')" class="w-full rounded-xl bg-gray-100 py-3 text-sm font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-white">
                    Update Only This One
                    <span class="block text-[10px] font-normal text-gray-500">Splits from the series</span>
                </button>
                <button wire:click="confirmUpdate('future')" class="w-full rounded-xl bg-purple-50 py-3 text-sm font-bold text-purple-600 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400">
                    Update All Future
                    <span class="block text-[10px] font-normal opacity-70">Changes this and subsequent events</span>
                </button>
            </div>
        </div>
    </x-modal>

    {{-- 13. POLL RESET WARNING MODAL --}}
    @if($activeModal === 'poll_reset')
        <div class="fixed inset-0 z-[90] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white text-center shadow-2xl dark:bg-gray-800">
                <div class="p-6">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6" />
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Reset Votes?</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        You modified the poll. Saving this will <span class="font-bold text-red-500">erase all existing votes</span>.
                    </p>
                </div>
                <div class="flex border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="closeModal" class="flex-1 py-4 text-sm font-bold text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                    <div class="w-px bg-gray-100 dark:bg-gray-700"></div>
                    <button wire:click="confirmPollReset" class="flex-1 py-4 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Reset & Save</button>
                </div>
            </div>
        </div>
    @endif

    {{-- 14. PARTICIPANTS MODAL --}}
    @if($activeModal === 'participants')
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Who's Going?</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <div class="max-h-[300px] overflow-y-auto space-y-2 pr-1">
                    @forelse($this->participantsList as $participant)
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <img src="{{ $participant->profile_picture ? Storage::url($participant->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($participant->username).'&background=random' }}" class="h-8 w-8 rounded-full bg-gray-200 object-cover">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $participant->username }}</span>
                        </div>
                    @empty
                        <p class="text-center text-sm text-gray-500">No one has opted in yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- PERMISSIONS MANAGER COMPONENT --}}
    @livewire('manage-permissions', ['calendar' => $calendar])

</div>
