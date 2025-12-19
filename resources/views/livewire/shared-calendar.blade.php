<div class="min-h-screen w-full bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

    <div class="mx-auto max-w-6xl space-y-8">
        {{-- HEADER --}}
        <x-calendar.header
            :calendar="$calendar"
            :monthName="$monthName"
            :currentYear="$currentYear"
            :currentMonth="$currentMonth"
            :selectedDate="$selectedDate"
        >
            <x-slot:actions>
                {{-- Only Owner/Admin/Permission-based Buttons --}}
                @if($this->checkPermission('invite_users'))
                    <button wire:click="openInviteModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                        <x-heroicon-o-user-plus class="h-5 w-5" />
                        <span>Invite</span>
                    </button>
                @endif

                @if($this->checkPermission('manage_settings'))
                    <button wire:click="openManageMembersModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                        <x-heroicon-o-users class="h-5 w-5" />
                        <span>Members</span>
                    </button>
                @endif

                @if($this->checkPermission('view_logs'))
                    <button wire:click="openLogsModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                        <x-heroicon-o-clipboard-document-list class="h-5 w-5" />
                        <span>Logs</span>
                    </button>
                @endif

                @if($this->checkPermission('view_events'))
                    <button wire:click="openExportModal" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-bold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-purple-400">
                        <x-heroicon-o-arrow-up-on-square class="h-5 w-5" />
                        <span>Export</span>
                    </button>
                @endif

                @if($this->checkPermission('create_events'))
                    <button wire:click="openModal('{{ $selectedDate }}')" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-3 text-base font-bold text-white shadow-lg transition-all hover:bg-purple-700 hover:shadow-purple-500/20">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        <span>New Event</span>
                    </button>
                @endif
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
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-800/50">
                            <x-heroicon-o-calendar class="mx-auto h-12 w-12 text-gray-400" />
                            <h4 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">No plans yet</h4>
                            @if($this->checkPermission('create_events'))
                                <button wire:click="openModal('{{ $selectedDate }}')" class="mt-4 text-sm font-bold text-purple-600 hover:text-purple-700 dark:text-purple-400">+ Add an event</button>
                            @endif
                        </div>
                    @else
                        @foreach($this->selectedDateEvents as $event)
                            <x-calendar.event-card
                                :event="$event"
                                :commentLimit="$commentLimits[$event->id] ?? 3"
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

                    {{-- ATTACHMENTS (Images & Files) --}}
                    @if($this->checkPermission('add_images'))
                        <div class="space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-500">Attachments (Images & Files)</label>
                            </div>
                            <div class="flex flex-col gap-3">
                                {{-- Updated File Input: Uses temp_photos and unique ID --}}
                                <input
                                    type="file"
                                    wire:model="temp_photos"
                                    id="upload-{{ $uploadIteration }}"
                                    multiple
                                    class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-gray-800 dark:file:text-purple-400"
                                >

                                {{-- Preview Loop --}}
                                @if(count($existing_images) > 0 || count($photos) > 0)
                                    <div class="grid grid-cols-4 gap-2">
                                        {{-- Existing Images --}}
                                        @foreach($existing_images as $index => $url)
                                            <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                @php $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION)); @endphp
                                                @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                                                    <img src="{{ $url }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="flex items-center justify-center w-full h-full bg-gray-100 text-xs font-bold uppercase text-gray-500">{{ $ext }}</div>
                                                @endif
                                                <button type="button" wire:click="removeExistingImage({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                                            </div>
                                        @endforeach

                                        {{-- New Uploads (Accumulator) --}}
                                        @foreach($photos as $index => $photo)
                                            <div class="relative group aspect-square rounded-lg overflow-hidden border border-purple-200 ring-2 ring-purple-400">
                                                @if(in_array($photo->guessExtension(), ['jpg','jpeg','png','gif','webp']))
                                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover opacity-80">
                                                @else
                                                    <div class="flex items-center justify-center w-full h-full bg-gray-100 text-xs font-bold uppercase text-gray-500">{{ $photo->guessExtension() }}</div>
                                                @endif
                                                <button type="button" wire:click="removePhoto({{ $index }})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><x-heroicon-o-x-mark class="w-3 h-3" /></button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- LOCATION & NOTES --}}
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" wire:model="location" placeholder="Location Name" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <input type="url" wire:model="url" placeholder="https://" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <textarea wire:model="description" rows="2" placeholder="Notes..." class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>

                    {{-- FEATURES TOGGLES (Permissions Check) --}}
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

                    {{-- FILTERS / RESTRICTIONS (Collapsed by default) --}}
                    <div x-data="{ expanded: false }" class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                        <button @click="expanded = !expanded" type="button" class="flex w-full items-center justify-between px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <span>Filters & Restrictions</span>
                            <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform" ::class="expanded ? 'rotate-180' : ''" />
                        </button>

                        <div x-show="expanded" class="border-t border-gray-100 p-4 space-y-4 dark:border-gray-700">
                            {{-- LABELS --}}
                            @if($this->checkPermission('add_labels'))
                                <div>
                                    <h5 class="mb-2 text-xs font-bold text-gray-400">Labels</h5>
                                    @if($this->calendar->groups->where('is_selectable', true)->isEmpty())
                                        <p class="text-xs text-gray-400">No labels available.</p>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($this->calendar->groups->where('is_selectable', true) as $group)
                                                <div class="flex items-center gap-1 rounded-lg border border-gray-200 px-2 py-1 dark:border-gray-700">
                                                    <input type="checkbox" wire:model="selected_group_ids" value="{{ $group->id }}" class="h-3 w-3 rounded text-purple-600">
                                                    <span class="text-xs" style="color: {{ $group->color }}">{{ $group->name }}</span>
                                                    {{-- Lock Icon for Mandatory --}}
                                                    <button type="button" wire:click="toggleRestriction({{ $group->id }})" title="Toggle Restriction" class="{{ $group_restrictions[$group->id] ?? true ? 'text-red-500' : 'text-gray-300' }}">
                                                        <x-heroicon-s-lock-closed class="h-3 w-3" />
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- GENDERS --}}
                            <div>
                                <h5 class="mb-2 text-xs font-bold text-gray-400">Gender</h5>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($this->genders as $gender)
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="checkbox" wire:model="selected_gender_ids" value="{{ $gender->id }}" class="h-3 w-3 rounded text-purple-600">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $gender->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- AGE & DISTANCE --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Min Age</label>
                                    <input type="number" wire:model="min_age" placeholder="e.g. 18" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                                <div class="space-y-1">
                                    {{-- Cleaned up Distance Input --}}
                                    <label class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Max Distance (KM)</label>
                                    <input type="number" wire:model="max_distance_km" placeholder="e.g. 20" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <p class="text-[10px] text-gray-400">Calculated from event Location.</p>
                                </div>
                            </div>

                            {{-- NSFW --}}
                            <label class="flex items-center gap-2 cursor-pointer pt-2">
                                <input type="checkbox" wire:model="is_nsfw" class="h-4 w-4 rounded text-red-600 focus:ring-red-500 dark:bg-gray-800">
                                <span class="text-xs font-bold text-red-600">NSFW (18+)</span>
                            </label>
                        </div>
                    </div>

                    {{-- POLL CREATION (Permission Check) --}}
                    @if($this->checkPermission('create_poll'))
                        <div x-data="{ expanded: false }" class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            <button @click="expanded = !expanded" type="button" class="flex w-full items-center justify-between px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <span>Add Poll</span>
                                <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform" ::class="expanded ? 'rotate-180' : ''" />
                            </button>

                            <div x-show="expanded" class="border-t border-gray-100 p-4 space-y-3 dark:border-gray-700">
                                <input type="text" wire:model="poll_title" placeholder="Poll Question" class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">

                                <div class="space-y-2">
                                    @foreach($poll_options as $index => $option)
                                        <div class="flex items-center gap-2">
                                            <input type="text" wire:model="poll_options.{{ $index }}" placeholder="Option {{ $index + 1 }}" class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                            @if(count($poll_options) > 2)
                                                <button type="button" wire:click="removePollOption({{ $index }})" class="text-red-400 hover:text-red-600"><x-heroicon-o-x-mark class="h-4 w-4" /></button>
                                            @endif
                                        </div>
                                    @endforeach
                                    <button type="button" wire:click="addPollOption" class="text-xs font-bold text-purple-600 hover:underline">+ Add Option</button>
                                </div>

                                <div class="flex items-center gap-4 pt-2">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[10px] font-bold text-gray-500">Max Selections</label>
                                        <input type="number" wire:model="poll_max_selections" min="1" class="w-16 rounded-lg border-gray-200 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    </div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="poll_is_public" class="h-4 w-4 rounded text-purple-600 dark:bg-gray-800">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Public Results</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="w-full rounded-xl bg-purple-600 py-3 text-sm font-bold text-white shadow-lg hover:bg-purple-700 hover:shadow-purple-500/20">
                        {{ $eventId ? 'Update Event' : 'Save Event' }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- EXPORT MODAL --}}
    @if($showExportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Export Events</h3>
                    <button wire:click="closeExportModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-6">
                    {{-- Format Selection --}}
                    <div>
                        <label class="mb-2 block text-sm font-bold text-gray-900 dark:text-white">Export To</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:border-gray-700 dark:has-[:checked]:bg-green-900/20">
                                <input type="radio" wire:model="exportFormat" value="excel" class="sr-only">
                                <span class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Excel</span>
                                <x-heroicon-o-table-cells class="mx-auto mt-1 h-6 w-6 text-green-600" />
                            </label>

                            <label class="cursor-pointer rounded-xl border border-gray-200 p-3 text-center transition-all has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:border-gray-700 dark:has-[:checked]:bg-red-900/20">
                                <input type="radio" wire:model="exportFormat" value="pdf" class="sr-only">
                                <span class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">PDF</span>
                                <x-heroicon-o-document-text class="mx-auto mt-1 h-6 w-6 text-red-600" />
                            </label>
                        </div>
                    </div>

                    {{-- Export Scope --}}
                    <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                        <label class="mb-2 block text-sm font-bold text-gray-900 dark:text-white">Which events?</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:has-[:checked]:bg-purple-900/20">
                                <input type="radio" wire:model.live="exportMode" value="all" class="text-purple-600 focus:ring-purple-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">All Visible</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 cursor-pointer has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 dark:border-gray-700 dark:has-[:checked]:bg-purple-900/20">
                                <input type="radio" wire:model.live="exportMode" value="label" class="text-purple-600 focus:ring-purple-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">By Label</span>
                            </label>
                        </div>
                    </div>

                    @if($exportMode === 'label')
                        <div>
                            <select wire:model="exportLabelId" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 text-sm focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="">-- Select Label --</option>
                                @foreach($this->calendar->groups as $label)
                                    <option value="{{ $label->id }}">{{ $label->name }}</option>
                                @endforeach
                            </select>
                            @error('exportLabelId') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button wire:click="closeExportModal" class="rounded-xl px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button wire:click="exportEvents" class="rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-purple-700 hover:shadow-purple-500/20">
                        Download File
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- OTHER MANAGEMENT MODALS (Invite, Members, Logs, Delete, Poll Reset) --}}

    {{-- INVITE MODAL --}}
    @if($isInviteModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Invite Members</h3>
                    <button wire:click="closeModal" class="rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700"><x-heroicon-o-x-mark class="h-5 w-5 text-gray-500" /></button>
                </div>

                <div class="flex border-b border-gray-100 dark:border-gray-700 mb-4">
                    <button wire:click="setInviteTab('create')" class="flex-1 py-2 text-sm font-bold border-b-2 {{ $inviteModalTab === 'create' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">Create Invite</button>
                    @if($this->checkPermission('view_active_links'))
                        <button wire:click="setInviteTab('list')" class="flex-1 py-2 text-sm font-bold border-b-2 {{ $inviteModalTab === 'list' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">Active Links</button>
                    @endif
                </div>

                @if($inviteModalTab === 'create')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Role</label>
                            <select wire:model="inviteRole" class="w-full rounded-lg border-gray-200 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Invite by Username</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="inviteUsername" placeholder="Enter username..." class="flex-1 rounded-lg border-gray-200 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <button wire:click="inviteUserByUsername" class="bg-purple-600 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-purple-700">Send</button>
                            </div>
                            @error('inviteUsername') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                            <label class="block text-xs font-bold text-gray-500 mb-1">Or Generate Link</label>
                            <button wire:click="generateInviteLink" class="w-full py-2 border border-purple-200 text-purple-700 rounded-lg text-sm font-bold hover:bg-purple-50 dark:border-purple-900 dark:text-purple-300 dark:hover:bg-purple-900/20">Generate Link</button>
                            @if($inviteLink)
                                <div class="mt-2 flex gap-2">
                                    <input type="text" readonly value="{{ $inviteLink }}" class="flex-1 rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600">
                                    <button onclick="navigator.clipboard.writeText('{{ $inviteLink }}')" class="text-xs font-bold text-purple-600">Copy</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="max-h-60 overflow-y-auto space-y-2">
                        @forelse($this->activeInvites as $invite)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 dark:bg-gray-900">
                                <div>
                                    <div class="text-xs font-bold dark:text-white">{{ $invite->role->name }} Link</div>
                                    <div class="text-[10px] text-gray-400">Created by {{ $invite->creator->username }}</div>
                                </div>
                                <button wire:click="deleteInvite({{ $invite->id }})" class="text-red-500 hover:text-red-700 text-xs font-bold">Revoke</button>
                            </div>
                        @empty
                            <p class="text-center text-sm text-gray-400 py-4">No active invite links.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- MEMBERS MODAL --}}
    @if($isManageMembersModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all dark:bg-gray-800">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Manage Members</h3>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"><x-heroicon-o-x-mark class="h-6 w-6" /></button>
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <div class="text-sm text-gray-500">{{ $this->members->count() }} Members</div>
                    @if($this->checkPermission('manage_role_permissions'))
                        <button wire:click="openManageRolesModal" class="text-sm font-bold text-purple-600 hover:underline">Manage Roles</button>
                    @endif
                </div>

                <div class="max-h-96 overflow-y-auto space-y-3">
                    @foreach($this->members as $member)
                        <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50">
                            <div class="flex items-center gap-3">
                                <img src="{{ $member->profile_picture ? Storage::url($member->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($member->username) }}" class="h-10 w-10 rounded-full bg-gray-200 object-cover">
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $member->username }}</div>
                                    <div class="text-xs text-gray-500">{{ $member->email }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                {{-- Role Selector --}}
                                @if($this->checkPermission('manage_user_permissions') && $member->id !== Auth::id())
                                    <select wire:change="changeRole({{ $member->id }}, $event.target.value)" class="rounded-lg border-gray-200 py-1 text-xs dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                        <option value="member" @selected($member->role_slug === 'member')>Member</option>
                                        <option value="admin" @selected($member->role_slug === 'admin')>Admin</option>
                                        <option value="owner">Make Owner</option>
                                    </select>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $member->role_name }}</span>
                                @endif

                                {{-- Action Buttons --}}
                                <div class="flex gap-2">
                                    @if($this->checkPermission('assign_labels'))
                                        <button wire:click="openManageMemberLabels({{ $member->id }})" class="text-gray-400 hover:text-purple-600" title="Assign Labels"><x-heroicon-o-tag class="h-4 w-4" /></button>
                                    @endif
                                    @if($this->checkPermission('manage_user_permissions'))
                                        <button wire:click="openPermissionsModal('users', {{ $member->id }})" class="text-gray-400 hover:text-blue-600" title="Permissions"><x-heroicon-o-key class="h-4 w-4" /></button>
                                    @endif
                                    @if($this->checkPermission('kick_users') && $member->id !== Auth::id())
                                        <button wire:click="kickMember({{ $member->id }})" class="text-gray-400 hover:text-red-600" title="Kick User"><x-heroicon-o-trash class="h-4 w-4" /></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- LOGS MODAL --}}
    @if($isLogsModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all dark:bg-gray-800">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Activity Logs</h3>
                    <button wire:click="closeModal" class="rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700"><x-heroicon-o-x-mark class="h-6 w-6 text-gray-500" /></button>
                </div>

                <div class="flex gap-4 mb-4">
                    <input type="text" wire:model.live="logSearch" placeholder="Search user..." class="flex-1 rounded-lg border-gray-200 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    <select wire:model.live="logActionFilter" class="rounded-lg border-gray-200 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                        <option value="">All Actions</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                        <option value="voted">Voted</option>
                    </select>
                </div>

                <div class="max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left text-sm text-gray-500">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Details</th>
                            <th class="px-4 py-3">Time</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($this->logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ $log->user->username ?? 'System' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 uppercase dark:bg-gray-700 dark:text-gray-300">{{ $log->action }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if(is_array($log->details))
                                        @foreach($log->details as $key => $val)
                                            <span class="block"><span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ is_array($val) ? implode(', ', $val) : $val }}</span>
                                        @endforeach
                                    @else
                                        {{ $log->details }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- DELETE/UPDATE CONFIRMATION MODALS --}}
    @if($isDeleteModalOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Event?</h3>
                <div class="mt-4 flex flex-col gap-2">
                    <button wire:click="confirmDelete('instance')" class="w-full rounded-lg bg-gray-100 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">Only This Instance</button>
                    <button wire:click="confirmDelete('future')" class="w-full rounded-lg bg-gray-100 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">This and Future</button>
                    <button wire:click="closeModal" class="mt-2 text-xs text-gray-400 hover:underline">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    @if($isUpdateModalOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Update Repeating Event?</h3>
                <div class="mt-4 flex flex-col gap-2">
                    <button wire:click="confirmUpdate('instance')" class="w-full rounded-lg bg-gray-100 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">Only This Instance</button>
                    <button wire:click="confirmUpdate('future')" class="w-full rounded-lg bg-gray-100 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">This and Future</button>
                    <button wire:click="closeModal" class="mt-2 text-xs text-gray-400 hover:underline">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    @if($isPollResetModalOpen)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-gray-800">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 mb-4"><x-heroicon-o-exclamation-triangle class="h-6 w-6" /></div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reset Poll?</h3>
                <p class="mt-2 text-sm text-gray-500">You changed the poll settings. All existing votes will be wiped.</p>
                <div class="mt-6 flex gap-3">
                    <button wire:click="closeModal" class="flex-1 rounded-lg py-2 text-sm font-bold text-gray-500 hover:bg-gray-100">Cancel</button>
                    <button wire:click="confirmPollReset" class="flex-1 rounded-lg bg-red-600 py-2 text-sm font-bold text-white hover:bg-red-700">Confirm & Wipe</button>
                </div>
            </div>
        </div>
    @endif

    {{-- OTHER COMPONENT MODALS --}}
    @if($isManageRolesModalOpen)
        <x-calendar.modals.manage-labels
            :items="$calendar->roles"
            createMethod="createRole"
            deleteMethod="deleteRole"
            nameModel="role_name"
            colorModel="role_color"
            :showSelectableIcon="false"
        />
    @endif

    @if($isParticipantsModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Attending</h3>
                    <button wire:click="closeModal" class="rounded-full p-1 hover:bg-gray-100 dark:hover:bg-gray-700"><x-heroicon-o-x-mark class="h-5 w-5 text-gray-500" /></button>
                </div>
                <div class="max-h-60 overflow-y-auto space-y-2">
                    @foreach($this->participantsList as $user)
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->profile_picture ? Storage::url($user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->username) }}" class="h-8 w-8 rounded-full bg-gray-200">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $user->username }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
