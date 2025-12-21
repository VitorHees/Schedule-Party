<div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

    {{-- HEADER --}}
    <header class="mb-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                Dashboard
            </h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                Welcome back, <span class="font-bold text-purple-600 dark:text-purple-400">{{ auth()->user()->username ?? auth()->user()->name }}</span>.
            </p>
        </div>

        <div>
            <button
                @click="Livewire.dispatch('open-create-calendar-modal')"
                class="group inline-flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-base font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-purple-700 hover:shadow-xl dark:bg-purple-500 dark:hover:bg-purple-600"
            >
                <x-heroicon-o-plus class="h-5 w-5 transition-transform group-hover:rotate-90" />
                <span>New Calendar</span>
            </button>
        </div>
    </header>

    {{-- 1. HERO: PERSONAL CALENDAR --}}
    <section class="relative mb-12 overflow-hidden rounded-2xl border border-gray-200 bg-white p-8 shadow-2xl transition-all hover:shadow-purple-500/10 dark:border-gray-700 dark:bg-gray-800">
        <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-purple-100 blur-3xl opacity-50 dark:bg-purple-900/20"></div>
        <div class="pointer-events-none absolute -bottom-10 -left-10 h-72 w-72 rounded-full bg-blue-100 blur-3xl opacity-50 dark:bg-blue-900/20"></div>

        <div class="relative z-10 flex flex-col justify-between gap-8 lg:flex-row lg:items-end">
            <div class="flex-1">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-purple-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                    <x-heroicon-s-user class="h-3 w-3" />
                    My Calendar
                </div>

                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Personal Space</h2>
                <p class="mt-2 max-w-xl text-lg text-gray-600 dark:text-gray-400">
                    You have <span class="font-bold text-gray-900 dark:text-white">{{ $todaysEventsCount }} event{{ $todaysEventsCount !== 1 ? 's' : '' }}</span> scheduled for today.
                </p>

                {{-- Next Event Highlight --}}
                @if($nextPersonalEvent)
                    <div class="mt-8 flex items-start gap-4 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300">
                            <span class="text-[10px] font-bold uppercase">Next</span>
                            <x-heroicon-s-clock class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-purple-600 dark:text-purple-400">
                                {{ $nextPersonalEvent->start_date->isToday() ? 'Today' : $nextPersonalEvent->start_date->diffForHumans() }}
                            </p>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $nextPersonalEvent->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $nextPersonalEvent->location ? $nextPersonalEvent->location . ' • ' : '' }}
                                {{ $nextPersonalEvent->start_date->format('g:i A') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="mt-8 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events scheduled.</p>
                    </div>
                @endif
            </div>

            {{-- Action Button --}}
            <div class="w-full lg:w-auto">
                <a
                    href="{{ route('calendar.personal') }}"
                    wire:navigate
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-900 px-8 py-4 text-lg font-bold text-white shadow-lg transition-all hover:bg-gray-800 hover:shadow-xl dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 lg:w-auto"
                >
                    Open Calendar <x-heroicon-s-arrow-right class="h-5 w-5" />
                </a>
            </div>
        </div>
    </section>

    {{-- 2. COLLABORATIVE SPACES --}}
    <section class="mb-12">
        <div class="mb-6 px-1 flex items-center justify-between">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Collaborative Spaces</h3>
        </div>

        @if($collaborativeCalendars->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-800/50">
                <x-heroicon-o-users class="mx-auto h-12 w-12 text-gray-400" />
                <h4 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">No shared calendars yet</h4>
                <button @click="Livewire.dispatch('open-create-calendar-modal')" class="mt-2 text-sm font-bold text-purple-600 hover:text-purple-700 dark:text-purple-400">
                    Create one now
                </button>
            </div>
        @else
            <div class="-mx-6 flex snap-x space-x-6 overflow-x-auto px-6 pb-8 md:mx-0 md:px-0 scrollbar-hide">
                @foreach($collaborativeCalendars as $calendar)
                    <a href="{{ route('calendar.shared', $calendar) }}" wire:navigate class="group relative min-w-[320px] snap-center rounded-2xl border border-gray-200 bg-white p-6 shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800 block">
                        <div class="mb-4 flex justify-between">
                            {{-- Role Badge --}}
                            @php
                                $roleId = $calendar->pivot->role_id;
                                $roleName = $userRoles[$roleId] ?? 'Member';
                                $isOwner = $roleId === $ownerRoleId;
                            @endphp

                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $isOwner ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' }}">
                                {{ $roleName }}
                            </span>

                            <x-heroicon-o-arrow-right class="h-5 w-5 text-gray-400 group-hover:text-purple-600 transition-colors" />
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $calendar->name }}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $calendar->users_count }} member{{ $calendar->users_count !== 1 ? 's' : '' }}
                        </p>

                        <div class="mt-6 flex items-center -space-x-3">
                            @foreach($calendar->users->take(3) as $member)
                                <img
                                    src="{{ $member->profile_picture ? Storage::url($member->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($member->username).'&background=random' }}"
                                    alt="{{ $member->username }}"
                                    class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 bg-gray-200"
                                >
                            @endforeach
                            @if($calendar->users_count > 3)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 ring-2 ring-white text-xs font-bold text-gray-600 dark:bg-gray-700 dark:ring-gray-800 dark:text-gray-300">
                                    +{{ $calendar->users_count - 3 }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 3. EVENTS & INVITES GRID --}}
    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Upcoming Events --}}
        <div class="lg:col-span-2">
            <h3 class="mb-6 px-1 text-2xl font-bold text-gray-900 dark:text-white">Closest Events</h3>

            <div class="space-y-4">
                @forelse($upcomingEvents as $event)
                    <div class="group relative flex items-start gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-purple-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 w-full text-left">
                        <a href="{{ route('calendar.shared', ['calendar' => $event->calendar, 'selectedDate' => $event->start_date->format('Y-m-d')]) }}"
                           wire:navigate
                           class="absolute inset-0 z-0 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <span class="sr-only">View Event</span>
                        </a>

                        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-gray-50 text-gray-700 dark:bg-gray-900/50 dark:text-gray-300 relative pointer-events-none">
                            <span class="text-xs font-bold uppercase">{{ $event->start_date->format('M') }}</span>
                            <span class="text-xl font-bold leading-none">{{ $event->start_date->format('j') }}</span>
                        </div>

                        <div class="flex-1 pointer-events-none">
                            @if($event->calendar->isCollaborative())
                                <span class="mb-1 inline-block rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $event->calendar->name }}
                                </span>
                            @endif

                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $event->name }}</h4>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $event->start_date->format('g:i A') }}
                                        @if($event->location) • {{ $event->location }} @endif
                                    </p>
                                </div>

                                {{-- Unified Badge Logic (Matches Calendar View) --}}
                                <div class="flex flex-wrap justify-end gap-1.5 max-w-[200px]">
                                    @foreach($event->groups as $group)
                                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                              style="background-color: {{ $group->color }}10; color: {{ $group->color }}; border-color: {{ $group->color }}30;">
                                            {{ $group->name }}
                                        </span>
                                    @endforeach

                                    @if($event->is_nsfw)
                                        <span class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-600 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                                            <x-heroicon-s-exclamation-triangle class="h-3 w-3" /> NSFW
                                        </span>
                                    @endif
                                    @foreach($event->genders as $gender)
                                        <span class="inline-flex items-center gap-1 rounded-md border border-teal-200 bg-teal-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-teal-600 dark:border-teal-800 dark:bg-teal-900/20 dark:text-teal-400">
                                            <x-heroicon-s-user class="h-3 w-3" /> {{ $gender->name }}
                                        </span>
                                    @endforeach
                                    @if($event->min_age)
                                        <span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $event->min_age }}+
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">No upcoming events found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Invites Widget --}}
        <div>
            <h3 class="mb-6 px-1 text-2xl font-bold text-gray-900 dark:text-white">Invites</h3>

            @forelse($invitations as $invite)
                <div class="relative mb-4 overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <x-heroicon-s-envelope class="h-5 w-5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                <span class="font-bold">{{ $invite->creator->username ?? 'Someone' }}</span> invited you to <span class="text-purple-600 dark:text-purple-400">{{ $invite->calendar->name }}</span>
                            </p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-400">Role: {{ $invite->role->name }}</p>

                            <div class="mt-5 flex gap-3">
                                <button wire:click="acceptInvitation({{ $invite->id }})" class="flex-1 rounded-xl bg-gray-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                    Accept
                                </button>
                                <button wire:click="rejectInvitation({{ $invite->id }})" class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    Ignore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-center dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No pending invites.</p>
                </div>
            @endforelse

            {{-- Tip Box --}}
            <div class="mt-6 rounded-2xl bg-purple-50 p-6 dark:bg-purple-900/20">
                <div class="flex gap-3">
                    <x-heroicon-o-light-bulb class="h-6 w-6 shrink-0 text-purple-600 dark:text-purple-400" />
                    <p class="text-sm leading-relaxed text-purple-900 dark:text-purple-100">
                        <strong>Tip:</strong> Create a Shared Calendar to start planning group events easily!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
