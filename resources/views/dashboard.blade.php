<x-layouts.app :title="__('Dashboard')">
    {{--
        WRAPPER:
        Uses the exact gradient from homepage.blade.php
        (from-purple-50 via-white to-blue-50)
        to match the branding perfectly.
    --}}
    <div class="min-h-screen w-full bg-gradient-to-br from-purple-50 via-white to-blue-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 lg:p-10">

        {{-- HEADER --}}
        <header class="mb-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Dashboard
                </h1>
                <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                    Welcome back, <span class="font-bold text-purple-600 dark:text-purple-400">{{ auth()->user()->username ?? 'Party Planner' }}</span>.
                </p>
            </div>

            {{-- Primary Action: "Start Free Trial" button style from Homepage --}}
            <div>
                <button
                    @click="$dispatch('open-create-calendar-modal')"
                    class="group inline-flex items-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-base font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:bg-purple-700 hover:shadow-xl dark:bg-purple-500 dark:hover:bg-purple-600"
                >
                    <x-heroicon-o-plus class="h-5 w-5 transition-transform group-hover:rotate-90" />
                    <span>New Calendar</span>
                </button>
            </div>
        </header>

        {{-- 1. HERO: PERSONAL CALENDAR --}}
        {{-- Styled to match the "Calendar Preview" card on Homepage --}}
        <section class="relative mb-12 overflow-hidden rounded-2xl border border-gray-200 bg-white p-8 shadow-2xl transition-all hover:shadow-purple-500/10 dark:border-gray-700 dark:bg-gray-800">

            {{-- Decorative blurs matching homepage hero --}}
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
                        You have <span class="font-bold text-gray-900 dark:text-white">4 events</span> scheduled for today.
                    </p>

                    {{-- Next Event Highlight --}}
                    <div class="mt-8 flex items-start gap-4 border-t border-gray-100 pt-6 dark:border-gray-700">
                        <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300">
                            <span class="text-[10px] font-bold uppercase">Next</span>
                            <x-heroicon-s-clock class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-purple-600 dark:text-purple-400">Starting in 30m</p>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Grocery Run</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Local Market • 10:00 AM</p>
                        </div>
                    </div>
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
            <div class="mb-6 px-1">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Collaborative Spaces</h3>
            </div>

            {{-- Scroll Container --}}
            <div class="-mx-6 flex snap-x space-x-6 overflow-x-auto px-6 pb-8 md:mx-0 md:px-0 scrollbar-hide">

                {{-- Card 1: Family Trips --}}
                <div class="group relative min-w-[320px] snap-center rounded-2xl border border-gray-200 bg-white p-6 shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex justify-between">
                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                            Admin
                        </span>
                        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Family Trips</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Last updated 2h ago</p>

                    <div class="mt-6 flex items-center -space-x-3">
                        <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-gray-800" src="https://ui-avatars.com/api/?name=Dad&background=random" alt="">
                        <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-gray-800" src="https://ui-avatars.com/api/?name=Mom&background=random" alt="">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 ring-2 ring-white text-xs font-bold text-gray-600 dark:bg-gray-700 dark:ring-gray-800 dark:text-gray-300">+3</span>
                    </div>
                </div>

                {{-- Card 2: Project Alpha --}}
                <div class="group relative min-w-[320px] snap-center rounded-2xl border border-gray-200 bg-white p-6 shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex justify-between">
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            Member
                        </span>
                        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Project Alpha</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Deadline: Nov 24</p>

                    <div class="mt-6 flex items-center -space-x-3">
                        <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-gray-800" src="https://ui-avatars.com/api/?name=Boss&background=0D8ABC&color=fff" alt="">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 ring-2 ring-white text-xs font-bold text-gray-600 dark:bg-gray-700 dark:ring-gray-800 dark:text-gray-300">+12</span>
                    </div>
                </div>

                {{-- Card 3: Book Club --}}
                <div class="group relative min-w-[320px] snap-center rounded-2xl border border-gray-200 bg-white p-6 shadow-lg transition-all hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex justify-between">
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            Member
                        </span>
                        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Book Club</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monthly meetup</p>

                    <div class="mt-6 flex items-center -space-x-3">
                        <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-gray-800" src="https://ui-avatars.com/api/?name=Sarah&background=random" alt="">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 ring-2 ring-white text-xs font-bold text-gray-600 dark:bg-gray-700 dark:ring-gray-800 dark:text-gray-300">+4</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. EVENTS & INVITES GRID --}}
        <div class="grid gap-8 lg:grid-cols-3">

            {{-- Upcoming Events --}}
            <div class="lg:col-span-2">
                <h3 class="mb-6 px-1 text-2xl font-bold text-gray-900 dark:text-white">Closest Events</h3>

                <div class="space-y-4">
                    {{-- Event 1 --}}
                    <div class="group flex items-center gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-purple-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300">
                            <span class="text-xs font-bold uppercase">Nov</span>
                            <span class="text-xl font-bold leading-none">24</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Design Sync</h4>
                                <span class="hidden rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300 sm:inline-block">
                                    Vote Required
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">2:00 PM • Project Alpha • Online</p>
                        </div>
                    </div>

                    {{-- Event 2 --}}
                    <div class="group flex items-center gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-pink-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-pink-50 text-pink-700 dark:bg-pink-900/20 dark:text-pink-300">
                            <span class="text-xs font-bold uppercase">Nov</span>
                            <span class="text-xl font-bold leading-none">25</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Dinner & Drinks</h4>
                                <div class="flex gap-2">
                                    <x-heroicon-s-eye class="h-4 w-4 text-pink-400" title="Girls Only" />
                                    <x-heroicon-s-map-pin class="h-4 w-4 text-blue-400" title="< 20km" />
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">8:00 PM • Personal • Downtown</p>
                        </div>
                    </div>

                    {{-- Event 3 --}}
                    <div class="group flex items-center gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-green-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300">
                            <span class="text-xs font-bold uppercase">Nov</span>
                            <span class="text-xl font-bold leading-none">28</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Weekend Hike</h4>
                                <span class="hidden rounded-full bg-green-100 px-2.5 py-1 text-[10px] font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400 sm:inline-block">
                                    Opted In
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">10:00 AM • Family Trips • National Park</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invites Widget --}}
            <div>
                <h3 class="mb-6 px-1 text-2xl font-bold text-gray-900 dark:text-white">Invites</h3>

                <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <x-heroicon-s-envelope class="h-5 w-5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                <span class="font-bold">Boss</span> invited you to <span class="text-purple-600 dark:text-purple-400">Marketing Q4</span>
                            </p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-400">Role: Admin</p>

                            <div class="mt-5 flex gap-3">
                                <button class="flex-1 rounded-xl bg-gray-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                    Accept
                                </button>
                                <button class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    Ignore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tip Box --}}
                <div class="mt-6 rounded-2xl bg-purple-50 p-6 dark:bg-purple-900/20">
                    <div class="flex gap-3">
                        <x-heroicon-o-light-bulb class="h-6 w-6 shrink-0 text-purple-600 dark:text-purple-400" />
                        <p class="text-sm leading-relaxed text-purple-900 dark:text-purple-100">
                            <strong>Tip:</strong> Export events from collaborative calendars to your personal one.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:create-calendar />
</x-layouts.app>
