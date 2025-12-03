<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Personal Calendar</h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage your private schedule.</p>
        </div>

        <div class="flex gap-3">
            <button wire:click="$set('showGroupModal', true)" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 border border-gray-200">
                + New Group
            </button>
            <button wire:click="$set('showEventModal', true)" class="rounded-xl bg-purple-600 px-6 py-2 text-sm font-bold text-white shadow-lg transition hover:bg-purple-700 hover:shadow-xl">
                Create Event
            </button>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">

        {{-- Calendar Grid Section --}}
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
            {{-- Calendar Navigation --}}
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h2>
                <div class="flex gap-2">
                    <button wire:click="$set('month', {{ $month - 1 }})" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                        <x-heroicon-o-chevron-left class="w-5 h-5" />
                    </button>
                    <button wire:click="$set('month', {{ $month + 1 }})" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600">
                        <x-heroicon-o-chevron-right class="w-5 h-5" />
                    </button>
                </div>
            </div>

            {{-- Days Header --}}
            <div class="grid grid-cols-7 mb-2">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $day }}</div>
                @endforeach
            </div>

            {{-- The Grid --}}
            <div class="grid grid-cols-7 gap-2">
                @foreach($this->grid as $date)
                    @php
                        $isCurrentMonth = $date->month === $month;
                        $isToday = $date->isToday();
                        $dateEvents = $this->events->filter(fn($e) => $e->start_date->isSameDay($date));
                    @endphp

                    <div wire:click="selectDay('{{ $date->toDateString() }}')"
                         class="min-h-[100px] relative rounded-xl border border-transparent p-2 transition cursor-pointer hover:border-purple-200 hover:bg-purple-50 dark:hover:bg-gray-700
                         {{ $isCurrentMonth ? 'bg-white dark:bg-gray-800' : 'bg-gray-50/50 text-gray-400 dark:bg-gray-900/50' }}
                         {{ $isToday ? 'ring-2 ring-purple-500' : '' }}">

                        <span class="text-sm font-bold {{ $isToday ? 'text-purple-600' : 'text-gray-700 dark:text-gray-300' }}">
                            {{ $date->day }}
                        </span>

                        {{-- Event Dots/Bars --}}
                        <div class="mt-1 space-y-1">
                            @foreach($dateEvents as $event)
                                <div class="text-[10px] truncate rounded px-1 text-white shadow-sm"
                                     style="background: {{ $event->mixed_color }};">
                                    {{ $event->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Side Panel: Details & Groups --}}
        <div class="space-y-6">
            {{-- Selected Date Info --}}
            <div class="rounded-2xl bg-purple-50 p-6 dark:bg-purple-900/20">
                <h3 class="text-lg font-bold text-purple-900 dark:text-purple-100 mb-2">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, M jS') }}
                </h3>
                <p class="text-sm text-purple-700 dark:text-purple-300">
                    Click any day on the grid to add an event.
                </p>
            </div>

            {{-- Your Groups List --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">My Groups</h3>
                <div class="space-y-3">
                    @forelse($this->userGroups as $group)
                        <div class="group flex items-center justify-between"> {{-- Changed to justify-between --}}
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $group->color }}"></span>
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $group->name }}</span>
                            </div>

                            {{-- Delete Button --}}
                            <button wire:click="deleteGroup({{ $group->id }})"
                                    class="invisible group-hover:visible text-gray-400 hover:text-red-500 transition">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic">No groups created yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- CREATE EVENT MODAL --}}
    @if($showEventModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">New Event</h3>
                    <button wire:click="$set('showEventModal', false)" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Event Name</label>
                        <input type="text" wire:model="eventName" class="mt-1 w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600">
                        @error('eventName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Time & Date --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Start</label>
                            <input type="date" wire:model="eventStartDate" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                            @if(!$eventIsAllDay)
                                <input type="time" wire:model="eventStartTime" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">End</label>
                            <input type="date" wire:model="eventEndDate" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                            @if(!$eventIsAllDay)
                                <input type="time" wire:model="eventEndTime" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="eventIsAllDay" id="allDay" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <label for="allDay" class="text-sm text-gray-700 dark:text-gray-300">All Day Event</label>
                    </div>

                    {{-- Repeating --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Repeat</label>
                        <select wire:model="eventRepeatFrequency" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                            <option value="none">Does not repeat</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>

                    {{-- Groups --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Assign Groups</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($this->userGroups as $group)
                                <label class="cursor-pointer inline-flex items-center gap-2 rounded-full border px-3 py-1 transition
                                          {{ in_array($group->id, $selectedGroups) ? 'bg-purple-50 border-purple-500' : 'bg-white border-gray-200' }}">
                                    <input type="checkbox" wire:model="selectedGroups" value="{{ $group->id }}" class="hidden">
                                    <span class="w-2 h-2 rounded-full" style="background: {{ $group->color }}"></span>
                                    <span class="text-xs font-bold {{ in_array($group->id, $selectedGroups) ? 'text-purple-700' : 'text-gray-600' }}">
                                    {{ $group->name }}
                                </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Extra Info --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Location</label>
                            <input type="text" wire:model="eventLocation" placeholder="Home, Office..." class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">URL</label>
                            <input type="url" wire:model="eventUrl" placeholder="https://..." class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Description / Note</label>
                        <textarea wire:model="eventDescription" rows="2" class="mt-1 w-full rounded-xl border-gray-300 dark:bg-gray-700"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button wire:click="$set('showEventModal', false)" class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl">Cancel</button>
                        <button wire:click="saveEvent" class="px-6 py-2 text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-lg">Save Event</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- CREATE GROUP MODAL --}}
    @if($showGroupModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Create New Group</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Group Name</label>
                        <input type="text" wire:model="newGroupName" placeholder="e.g. Work, Family" class="mt-1 w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Color</label>
                        <div class="mt-2 flex gap-3 overflow-x-auto pb-2">
                            @foreach(['#7C3AED', '#2563EB', '#16A34A', '#DC2626', '#F59E0B', '#DB2777', '#000000'] as $color)
                                <button wire:click="$set('newGroupColor', '{{ $color }}')"
                                        class="w-8 h-8 rounded-full border-2 transition {{ $newGroupColor === $color ? 'border-gray-900 scale-110' : 'border-transparent' }}"
                                        style="background-color: {{ $color }}"></button>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <button wire:click="$set('showGroupModal', false)" class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl">Cancel</button>
                        <button wire:click="saveGroup" class="px-6 py-2 text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 rounded-xl">Create Group</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
