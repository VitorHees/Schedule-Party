<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<!-- Sidebar -->
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <!-- Toggle for mobile breakpoints -->
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <!-- App Branding -->
    <a href="#" class="mb-8 flex items-center space-x-2 rtl:space-x-reverse">
        <!-- Custom logo -->
        <div class="flex items-center justify-center rounded-3xl bg-purple-100 p-2 dark:bg-purple-700/20">
            {{-- stronger color in light, softer in dark --}}
            <x-app-logo-icon class="w-7 h-7 text-purple-500 dark:text-purple-300" />
        </div>
        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">My Calendar App</span>
    </a>

    <!-- Main navigation: Calendars -->
    <flux:navlist variant="outline">
        <flux:navlist.group heading="Calendars" class="grid">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                Dashboard
            </flux:navlist.item>
            <flux:navlist.item icon="calendar" href="#">
                All Calendars
            </flux:navlist.item>
            <flux:navlist.item icon="plus-circle" href="#">
                Create Calendar
            </flux:navlist.item>
            <flux:navlist.item icon="user-group" href="#">
                Shared With Me
            </flux:navlist.item>
            <flux:navlist.item icon="star" href="#">
                Favorites
            </flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer />

    <!-- Tools and Settings -->
    <flux:navlist variant="outline">
        <flux:navlist.item icon="bell" href="#">
            Notifications
        </flux:navlist.item>
        <flux:navlist.item icon="cog" href="#">
            Settings
        </flux:navlist.item>

        <!-- Theme Toggle -->
        <!--
            Minimal Alpine wrapper that reads/writes the same source-of-truth as the auth header:
            - Initializes from localStorage, $flux / Alpine.store('flux') if present, or 'system'
            - Calls window.Flux.applyAppearance when available (fallback toggles document .dark)
            - Persists to localStorage and updates Alpine.store('flux') when present
            This replaces the previous local-only `dark` toggle while preserving markup/classes.
        -->
        <div
            class="w-full px-4 mt-2"
            x-data="{
                // single source-of-truth value used by this toggle: 'light'|'dark'|'system'
                appearance: 'system',

                // initialize appearance: try localStorage, then $flux / Alpine.store('flux'), else default 'system'
                initAppearance() {
                    try {
                        const stored = localStorage.getItem('flux.appearance');
                        if (stored) {
                            this.appearance = stored;
                            return;
                        }
                    } catch (e) {
                        // ignore storage errors
                    }

                    // Try global $flux or Alpine store
                    if (window.$flux && window.$flux.appearance) {
                        this.appearance = window.$flux.appearance;
                        return;
                    }

                    if (window.Alpine && Alpine.store && Alpine.store('flux') && Alpine.store('flux').appearance) {
                        this.appearance = Alpine.store('flux').appearance;
                        return;
                    }

                    this.appearance = 'system';
                }
            }"
            x-init="
                // initialize appearance and ensure UI & global state are consistent
                initAppearance();

                // Apply via Flux if available, otherwise fallback to toggling document dark class
                if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
                    window.Flux.applyAppearance(appearance);
                } else {
                    // fallback: apply dark class when appearance === 'dark'
                    document.documentElement.classList.toggle('dark', appearance === 'dark');
                }

                // Watch local appearance changes and forward to Flux/store/localStorage/fallback
                $watch('appearance', value => {
                    // persist preference (best-effort)
                    try { localStorage.setItem('flux.appearance', value); } catch (e) {}

                    // update Alpine.store('flux') if present so other Alpine components that read it react
                    if (window.Alpine && Alpine.store && Alpine.store('flux')) {
                        try { Alpine.store('flux').appearance = value; } catch (e) {}
                    }

                    // Prefer window.Flux.applyAppearance if available (auth header uses this)
                    if (window.Flux && typeof window.Flux.applyAppearance === 'function') {
                        window.Flux.applyAppearance(value);
                    } else {
                        // fallback to toggling document class for Tailwind's dark: variants
                        document.documentElement.classList.toggle('dark', value === 'dark');
                    }
                });

                // If an Alpine store flux.appearance exists and may change elsewhere, subscribe to it
                if (window.Alpine && Alpine.store && Alpine.store('flux')) {
                    // watch the store's appearance and mirror it locally
                    $watch(() => Alpine.store('flux').appearance, value => {
                        if (value) appearance = value;
                    });
                }

                // Also try to mirror a global window.$flux if present and changes externally
                // (no native event, so best-effort polling is avoided; store watch above suffices for expected setup)
            "
        >
            <button
                type="button"
                @click="appearance = (appearance === 'dark') ? 'light' : 'dark'"
                class="flex items-center gap-2 rounded-md px-3 py-2 text-xs hover:opacity-90 transition"
                :class="appearance === 'dark'
                    ? 'bg-zinc-700 text-zinc-100 border dark:border-zinc-700 dark:hover:bg-blue-600'
                    : 'bg-zinc-100 text-zinc-700 border border-zinc-200 hover:bg-blue-200'"
            >
                <span x-show="appearance !== 'dark'">
                    <!-- Sun SVG -->
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <circle cx="10" cy="10" r="5"/>
                    </svg>
                </span>
                <span x-show="appearance === 'dark'">
                    <!-- Moon SVG -->
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M15 12a5 5 0 01-8-8 7 7 0 108 8z"/>
                    </svg>
                </span>
                <span class="sr-only">Toggle Theme</span>
                <span x-text="appearance === 'dark' ? 'Dark' : (appearance === 'light' ? 'Light' : 'System')"></span>
            </button>
        </div>
    </flux:navlist>

    <!-- External Links -->
    <flux:navlist variant="outline" class="mt-3">
        <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
            Documentation
        </flux:navlist.item>
        <!-- Inline SVG for GitHub -->
        <flux:navlist.item href="https://github.com/itfactory-tm/2appai01-2025-2026-webdev-personal-project-VitorHees" target="_blank">
            <span class="inline-block w-4 h-4 align-middle mr-2">
                <svg viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8a8 8 0 005.47 7.59c.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.22 2.2.82a7.6 7.6 0 012.01-.27c.68 0 1.36.09 2.01.27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.19 0 .21.15.46.55.38A8 8 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
            </span>
            Repository
        </flux:navlist.item>
    </flux:navlist>

    <!-- Desktop Profile + Logout -->
    <flux:dropdown class="hidden lg:block" position="bottom" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />
        <flux:menu class="w-[220px]">
            <div class="flex items-center gap-2 px-4 py-2 text-sm font-normal">
                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-full">
                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                        {{ auth()->user()->initials() }}
                    </span>
                </span>
                <div class="text-sm leading-tight">
                    <span class="font-semibold">{{ auth()->user()->name }}</span>
                    <span class="text-xs">{{ auth()->user()->email }}</span>
                </div>
            </div>
            <flux:menu.separator />
            <flux:menu.radio.group>
                <flux:menu.item href="#" icon="cog">Settings</flux:menu.item>
            </flux:menu.radio.group>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    Log Out
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

{{ $slot }}

@fluxScripts
</body>
</html>
