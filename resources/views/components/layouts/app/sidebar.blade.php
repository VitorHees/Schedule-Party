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
        <div class="flex items-center justify-center rounded-3xl bg-purple-100 p-3 dark:bg-purple-700/20">
            {{-- stronger color in light, softer in dark --}}
            <x-app-logo-icon class="w-10 h-10 text-purple-500 dark:text-purple-300" />
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
        <div class="w-full px-4 mt-2">
            <button
                x-data="{ dark: false }"
                @click="dark = !dark; document.documentElement.classList.toggle('dark', dark)"
                class="flex items-center gap-2 rounded-md bg-zinc-100 dark:bg-zinc-700 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-100 hover:bg-blue-200 dark:hover:bg-blue-600 transition"
            >
                <span x-show="!dark">
                    <!-- Sun SVG -->
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><circle cx="10" cy="10" r="5"/></svg>
                </span>
                <span x-show="dark">
                    <!-- Moon SVG -->
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M15 12a5 5 0 01-8-8 7 7 0 108 8z"/></svg>
                </span>
                Toggle Theme
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
                <svg fill="currentColor" viewBox="0 0 24 24" class="w-4 h-4">
                    <path d="M12 .5C5.73.5.5 5.73.5 12c0 5.08 3.29 9.37 7.86 10.88.57.11.77-.25.77-.55v-2.05c-3.2.7-3.87-1.54-3.87-1.54-.52-1.32-1.28-1.68-1.28-1.68-1.04-.72.08-.71.08-.71 1.15.08 1.75 1.18 1.75 1.18 1.02 1.76 2.68 1.25 3.33.95.1-.74.4-1.25.73-1.54-2.55-.29-5.23-1.28-5.23-5.69 0-1.26.45-2.29 1.19-3.09-.12-.29-.52-1.46.11-3.04 0 0 .98-.31 3.22 1.18a11.2 11.2 0 015.87 0c2.24-1.49 3.22-1.18 3.22-1.18.63 1.58.23 2.75.12 3.04.74.8 1.19 1.83 1.19 3.09 0 4.42-2.69 5.4-5.25 5.69.41.35.78 1.04.78 2.1v3.12c0 .3.2.66.78.55A10.98 10.98 0 0023.5 12C23.5 5.73 18.27.5 12 .5z"/>
                </svg>
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
</flux:sidebar>

<!-- Mobile User Menu -->
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-down"
        />
        <flux:menu>
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
