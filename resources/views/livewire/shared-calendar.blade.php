<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

    <div class="mx-auto max-w-5xl space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                {{-- Dynamic Title based on the Calendar Model --}}
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $calendar->name }}</h1>
                <p class="mt-1 text-lg text-gray-600 dark:text-gray-400">Collaborative Schedule</p>
            </div>
            <div>
                <button wire:click="openModal('{{ $selectedDate }}')" class="group inline-flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-base font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-purple-700 hover:shadow-xl dark:bg-purple-500 dark:hover:bg-purple-600">
                    <x-heroicon-o-plus class="h-5 w-5 transition-transform group-hover:rotate-90" />
                    <span>New Event</span>
                </button>
            </div>
        </div>

        {{-- CALENDAR GRID --}}
        <div class="relative overflow-visible rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
            <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-purple-100 blur-3xl opacity-50 dark:bg-purple-900/20"></div>
                <div class="absolute -bottom-10 -left-10 h-72 w-72 rounded-full bg-blue-100 blur-3xl opacity-50 dark:bg-blue-900/20"></div>
            </div>

            {{-- CALENDAR HEADER --}}
            <div class="relative z-30 flex items-center justify-between border-b border-gray-100 px-6 py-6 dark:border-gray-700">
                <div class="flex items-center gap-2 text-3xl font-bold text-gray-900 dark:text-white relative">

                    {{-- Month Selector --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false" class="hover:text-purple-600 dark:hover:text-purple-400 transition-colors flex items-center decoration-dashed underline-offset-8 hover:underline cursor-pointer">
                            {{ $monthName }}
                        </button>
                        <div x-show="open" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 grid grid-cols-3 gap-2 z-50 ring-1 ring-black/5" style="display: none;">
                            @foreach(range(1, 12) as $m)
                                <button wire:click="setMonth({{ $m }}); open = false" class="p-2 rounded-lg text-sm font-bold {{ $currentMonth == $m ? 'bg-purple-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                    {{ \Carbon\Carbon::create()->month($m)->format('M') }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Year Selector --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.outside="open = false" class="text-gray-400 dark:text-gray-500 hover:text-purple-600 dark:hover:text-purple-400 transition-colors decoration-dashed underline-offset-8 hover:underline cursor-pointer">
                            {{ $currentYear }}
                        </button>
                        <div x-show="open" class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-2 z-50 max-h-64 overflow-y-auto ring-1 ring-black/5" style="display: none;">
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(range($currentYear - 5, $currentYear + 5) as $y)
                                    <button wire:click="setYear({{ $y }}); open = false" class="p-2 rounded-lg text-sm font-bold {{ $currentYear == $y ? 'bg-purple-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        {{ $y }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-xl bg-gray-50 p-1 dark:bg-gray-700/50">
                    <button wire:click="previousMonth" class="rounded-lg p-2 text-gray-500 hover:bg-white hover:text-purple-600 hover:shadow-sm dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-purple-400"><x-heroicon-s-chevron-left class="h-5 w-5" /></button>
                    <button wire:click="goToToday" class="px-3 py-1 text-sm font-bold text-gray-600 hover:text-purple-600 dark:text-gray-300 dark:hover:text-purple-400">Today</button>
                    <button wire:click="nextMonth" class="rounded-lg p-2 text-gray-500 hover:bg-white hover:text-purple-600 hover:shadow-sm dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-purple-400"><x-heroicon-s-chevron-right class="h-5 w-5" /></button>
                </div>
            </div>

            <div class="relative z-10 p-6">
                <div class="mb-4 grid grid-cols-7 text-center">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $day }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 gap-y-2 lg:gap-4">
                    @for ($i = 0; $i < $firstDayOfWeek; $i++) <div class="min-h-[80px]"></div> @endfor
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateString = $calendarDate->copy()->setDay($day)->format('Y-m-d');
                            $isToday = $dateString === \Carbon\Carbon::now()->format('Y-m-d');
                            $isSelected = $selectedDate === $dateString;
                            $dayEvents = $eventsByDate[$dateString] ?? collect();
                        @endphp
                        <div wire:click="selectDate('{{ $dateString }}')" class="group relative flex min-h-[80px] cursor-pointer flex-col items-center rounded-xl border border-transparent p-2 hover:bg-purple-50 dark:hover:bg-purple-900/20 {{ $isSelected ? 'bg-purple-50 ring-2 ring-purple-500 dark:bg-purple-900/20 dark:ring-purple-400' : '' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-colors {{ $isToday ? 'bg-purple-600 text-white shadow-md' : ($isSelected ? 'text-purple-700 dark:text-purple-300' : 'text-gray-700 dark:text-gray-300') }}">{{ $day }}</span>
                            <div class="mt-2 flex flex-wrap justify-center gap-1">
                                @foreach($dayEvents->take(4) as $event)
                                    <div class="h-1.5 w-1.5 rounded-full" style="background: {{ $event->mixed_color ?? '#A855F7' }};"></div>
                                @endforeach
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

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
                        @php
                            $groupName = $event->groups->first()?->name ?? 'General';
                            $groupColor = $event->mixed_color ?? '#A855F7';
                            $isRepeating = $event->repeat_frequency !== 'none';
                            $images = $event->images['urls'] ?? [];
                        @endphp
                        <div class="group relative flex flex-col md:flex-row items-stretch overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:-translate-y-0.5 hover:border-purple-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 md:static md:w-1.5 shrink-0" style="background: {{ $groupColor }}"></div>
                            <div class="flex-1 flex flex-col md:flex-row p-6 gap-6">
                                <div class="flex flex-col items-start min-w-[80px]">
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }}</span>
                                    @if(!$event->is_all_day)
                                        <span class="text-xs font-medium text-gray-400">{{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}</span>
                                    @endif
                                    @if($isRepeating)
                                        <x-heroicon-s-arrow-path class="w-3 h-3 text-gray-400 mt-1" title="Repeating" />
                                    @endif
                                </div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset" style="background-color: {{ $groupColor }}10; color: {{ $groupColor }}; ring-color: {{ $groupColor }}20;">
                                            {{ $groupName }}
                                        </span>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $event->name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $event->description }}</p>
                                    <div class="pt-2 flex flex-wrap gap-4">
                                        @if($event->location)
                                            <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                <x-heroicon-s-map-pin class="h-4 w-4 text-blue-400" />
                                                {{ $event->location }}
                                            </div>
                                        @endif
                                        @if($event->url)
                                            <a href="{{ $event->url }}" target="_blank" class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-purple-600 hover:underline dark:text-purple-400">
                                                <x-heroicon-s-link class="h-4 w-4" />
                                                Link
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if(count($images) > 0)
                                <div class="w-full md:w-1/3 min-w-[250px] bg-gray-50 dark:bg-gray-900 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700">
                                    @if(count($images) === 1)
                                        <div class="h-48 md:h-full w-full">
                                            <img src="{{ $images[0] }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500 cursor-pointer" onclick="window.open('{{ $images[0] }}', '_blank')">
                                        </div>
                                    @else
                                        <div class="h-48 md:h-full w-full grid grid-cols-2 gap-0.5">
                                            @foreach(array_slice($images, 0, 4) as $index => $img)
                                                <div class="relative w-full h-full overflow-hidden {{ $loop->first && count($images) == 3 ? 'row-span-2' : '' }}">
                                                    <img src="{{ $img }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500 cursor-pointer" onclick="window.open('{{ $img }}', '_blank')">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 dark:bg-black/50 rounded-lg p-1 shadow-sm backdrop-blur-sm z-20">
                                <button wire:click="editEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}')" class="p-1.5 text-gray-500 hover:text-purple-600 dark:text-gray-300">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                                <button wire:click="promptDeleteEvent({{ $event->id }}, '{{ $event->start_date->format('Y-m-d') }}', {{ $isRepeating ? 'true' : 'false' }})" class="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-300">
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- CREATE/EDIT EVENT MODAL (Identical to Personal Calendar, handled by SharedCalendar component) --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $eventId ? 'Edit Event' : 'New Event' }}</h2>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"><x-heroicon-o-x-mark class="h-5 w-5" /></button>
                </div>

                <form wire:submit.prevent="saveEvent" class="space-y-4">
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

                    {{-- IMAGES & GROUPS (Simplified for brevity, ensure full implementation matches PersonalCalendar if needed) --}}
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

                    <div class="space-y-2 rounded-xl bg-gray-50 p-3 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-500">Group / Role</label>
                            @if($selected_group_id && !$isCreatingGroup)
                                <button type="button" wire:click="deleteSelectedGroup" class="flex items-center gap-1 text-[10px] font-bold uppercase text-red-500 hover:text-red-600"><x-heroicon-o-trash class="h-3 w-3" /> Delete</button>
                            @endif
                        </div>
                        @if(!$isCreatingGroup)
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="$set('selected_group_id', null)" class="rounded-full px-3 py-1 text-xs font-bold border transition-all {{ is_null($selected_group_id) ? 'bg-gray-200 text-gray-800 border-gray-300' : 'bg-white text-gray-500 border-gray-200 hover:border-purple-300' }}">None</button>
                                @foreach($this->groups as $group)
                                    <button type="button" wire:click="selectGroup({{ $group->id }})" class="rounded-full px-3 py-1 text-xs font-bold border transition-all flex items-center gap-1 {{ $selected_group_id === $group->id ? 'ring-2 ring-offset-1' : 'opacity-80 hover:opacity-100' }}" style="background-color: {{ $group->color }}20; color: {{ $group->color }}; border-color: {{ $group->color }}40; ring-color: {{ $group->color }};">{{ $group->name }}</button>
                                @endforeach
                                <button type="button" wire:click="toggleCreateGroup" class="rounded-full border border-dashed border-gray-300 px-3 py-1 text-xs font-bold text-gray-400 hover:border-purple-400 hover:text-purple-600">+ New</button>
                            </div>
                        @else
                            <div class="animate-in fade-in zoom-in-95 duration-200 space-y-3 p-1">
                                <div class="flex gap-2">
                                    <input type="text" wire:model="new_group_name" placeholder="Name" class="w-full rounded-lg border-gray-200 bg-white px-3 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800">
                                    <button type="button" wire:click="toggleCreateGroup" class="shrink-0 text-xs font-bold text-gray-400 hover:text-gray-600">Cancel</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($colors as $color)
                                        <button type="button" wire:click="$set('new_group_color', '{{ $color }}')" class="h-5 w-5 rounded-full border transition-transform hover:scale-110 {{ $new_group_color === $color ? 'border-gray-900 scale-125 ring-1 ring-offset-1' : 'border-transparent' }}" style="background-color: {{ $color }};"></button>
                                    @endforeach
                                </div>
                                <button type="button" wire:click="saveGroup" class="w-full rounded-lg bg-gray-900 py-1.5 text-xs font-bold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">Save Group</button>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" wire:model="location" placeholder="Location" class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
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

    {{-- DELETE & UPDATE MODALS (Same as Personal Calendar) --}}
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
</div>
