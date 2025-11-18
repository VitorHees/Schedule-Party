<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- Top bar: greeting + primary actions --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Welcome back, {{ auth()->user()->username ?? auth()->user()->email }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Here you can manage all your personal and collaborative calendars.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                {{-- Create new calendar --}}
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-create-calendar-modal')"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5" />
                    New calendar
                </button>

                {{-- Join shared calendar --}}
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-join-calendar-modal')"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                >
                    <x-heroicon-o-user-group class="-ml-1 mr-2 h-5 w-5" />
                    Join shared calendar
                </button>
            </div>
        </div>

        {{-- Overview cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Personal calendars</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        {{-- Placeholder – will be replaced with real counts via Livewire --}}
                        1
                    </p>
                </div>
                <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/40">
                    <x-heroicon-o-calendar class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Collaborative calendars</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        0
                    </p>
                </div>
                <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/40">
                    <x-heroicon-o-users class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming events</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        0
                    </p>
                </div>
                <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/40">
                    <x-heroicon-o-clock class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/60">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Shared with you</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-50">
                        0
                    </p>
                </div>
                <div class="rounded-full bg-amber-100 p-3 dark:bg-amber-900/40">
                    <x-heroicon-o-share class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
        </div>

        {{-- Main content: calendars list + placeholder for upcoming agenda --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Calendars list --}}
            <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Your calendars
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Personal and collaborative calendars you have access to.
                        </p>
                    </div>

                    {{-- Search + filters placeholder – will be wired to Livewire later --}}
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                            </span>
                            <input
                                type="text"
                                class="w-full rounded-lg border border-gray-300 bg-white py-1.5 pl-8 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
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

                {{-- Placeholder list – will be replaced by Livewire calendars component --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                                <x-heroicon-o-calendar class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    Your personal calendar
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Auto-created for you on signup. Events here are private by default.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
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
                            class="ml-1 text-blue-600 underline underline-offset-2 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Create one now
                        </button>
                        or
                        <button
                            type="button"
                            x-data
                            @click="$dispatch('open-join-calendar-modal')"
                            class="ml-1 text-blue-600 underline underline-offset-2 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            join a shared calendar
                        </button>.
                    </div>
                </div>
            </section>

            {{-- Agenda / upcoming events placeholder --}}
            <aside class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900/60">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Upcoming events
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Soon this will show your next events across all calendars.
                </p>

                <div class="mt-4 rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    No events to display yet. Once calendars and events are implemented,
                    your upcoming schedule will appear here.
                </div>
            </aside>
        </div>

        {{-- Modals will be Livewire/Alpine driven later, events are already being dispatched above. --}}
    </div>
</x-layouts.app>
