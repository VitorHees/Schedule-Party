<x-layouts.app :title="__('Dashboard')">
    {{-- Hero / Top overview styled like homepage hero but compact --}}
    <section class="relative py-10 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-purple-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 rounded-2xl shadow-lg">
        <!-- widened container: use a screen-sized max width so the hero can get closer to the edges on very wide screens -->
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-2">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    {{-- Logo + greeting --}}
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center rounded-full bg-purple-100 p-3 dark:bg-purple-700/20">
                            {{-- stronger color in light, softer in dark --}}
                            <x-app-logo-icon class="w-10 h-10 text-purple-500 dark:text-purple-300" />
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white">
                                Welcome back, {{ Auth::user()->name ?? 'there' }}
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Manage your personal and collaborative calendars, create new ones, or join a shared calendar.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 justify-start md:justify-end">
                    {{-- Primary CTA --}}
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-create-calendar-modal')"
                        class="inline-flex items-center rounded-2xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition"
                    >
                        <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5" />
                        New calendar
                    </button>

                    {{-- Secondary CTA --}}
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-join-calendar-modal')"
                        class="inline-flex items-center rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition"
                    >
                        <x-heroicon-o-user-group class="-ml-1 mr-2 h-5 w-5" />
                        Join shared calendar
                    </button>
                </div>
            </div>
        </div>

        {{-- Decorative gradient circles for visual continuity with homepage --}}
        <div class="pointer-events-none absolute -z-10 top-6 -right-16 w-48 h-48 bg-purple-200 dark:bg-purple-700/30 rounded-full blur-3xl opacity-40"></div>
        <div class="pointer-events-none absolute -z-10 -bottom-8 -left-12 w-48 h-48 bg-blue-200 dark:bg-blue-700/30 rounded-full blur-3xl opacity-30"></div>
    </section>

    <div class="mt-6 flex h-full w-full flex-1 flex-col gap-6">
        {{-- Overview cards — slightly more pronounced, consistent with homepage cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Personal calendars</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">1</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/40">
                    <x-heroicon-o-calendar class="h-6 w-6 text-purple-600 dark:text-purple-300" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Collaborative calendars</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">0</p>
                </div>
                <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/40">
                    <x-heroicon-o-users class="h-6 w-6 text-emerald-600 dark:text-emerald-300" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming events</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">0</p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/40">
                    <x-heroicon-o-clock class="h-6 w-6 text-purple-600 dark:text-purple-300" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Shared with you</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">0</p>
                </div>
                <div class="rounded-full bg-amber-100 p-3 dark:bg-amber-900/40">
                    <x-heroicon-o-share class="h-6 w-6 text-amber-600 dark:text-amber-300" />
                </div>
            </div>
        </div>

        {{-- Main content area: calendars list + upcoming --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Calendars list --}}
            <section class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Your calendars</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Personal and collaborative calendars you have access to.</p>
                    </div>

                    {{-- Search + filters (styled like homepage/search) --}}
                    <div class="flex flex-wrap gap-2 items-center">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                            </span>
                            <input
                                type="text"
                                class="w-56 rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                                placeholder="Search calendars…"
                                disabled
                            >
                        </div>

                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex h-6 items-center rounded-full bg-gray-100 px-2 dark:bg-gray-800">
                                Filters coming soon
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Placeholder list --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300">
                                <x-heroicon-o-calendar class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">Your personal calendar</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Auto-created for you on signup. Events here are private by default.</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300"
                            disabled
                        >
                            Open (soon)
                        </button>
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        No collaborative calendars yet.
                        <button
                            type="button"
                            x-data
                            @click="$dispatch('open-create-calendar-modal')"
                            class="ml-1 text-purple-600 underline underline-offset-2 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300"
                        >
                            Create one now
                        </button>
                        or
                        <button
                            type="button"
                            x-data
                            @click="$dispatch('open-join-calendar-modal')"
                            class="ml-1 text-purple-600 underline underline-offset-2 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300"
                        >
                            join a shared calendar
                        </button>.
                    </div>
                </div>
            </section>

            {{-- Agenda / upcoming events --}}
            <aside class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming events</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Soon this will show your next events across all calendars.</p>

                <div class="mt-4 rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    No events to display yet. Once calendars and events are implemented, your upcoming schedule will appear here.
                </div>
            </aside>
        </div>

        {{-- Note: modals and Livewire components keep using the same dispatch events as before --}}
    </div>
</x-layouts.app>
