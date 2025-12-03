<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    {{-- Branding --}}
    <a href="{{ route('dashboard') }}" class="mb-8 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <div class="flex items-center justify-center rounded-3xl bg-purple-100 p-3 dark:bg-purple-700/20">
            <x-app-logo-icon class="w-10 h-10 text-purple-500 dark:text-purple-300" />
        </div>
        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ config('app.name', 'Schedule Party') }}</span>
    </a>

    {{-- Primary Navigation --}}
    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
            Dashboard
        </flux:navlist.item>

        <flux:navlist.item icon="calendar" href="#">
            Personal Calendar
        </flux:navlist.item>

        <flux:navlist.item icon="users" href="#">
            Shared Calendars
        </flux:navlist.item>
    </flux:navlist>

    <flux:spacer />

    {{-- Tools & Settings --}}
    <flux:navlist variant="outline">
        <flux:navlist.item icon="bell" href="#">
            Notifications
        </flux:navlist.item>

        <flux:navlist.item icon="cog" :href="route('settings.profile')" wire:navigate>
            Settings
        </flux:navlist.item>

        {{-- Mode Toggle (Styled to match sidebar items) --}}
        <div class="w-full">
            <button
                type="button"
                x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium"
            >
                <flux:icon.moon class="size-5 dark:hidden" />
                <flux:icon.sun class="size-5 hidden dark:block" />

                <span class="truncate dark:hidden">Dark Mode</span>
                <span class="hidden truncate dark:block">Light Mode</span>
            </button>
        </div>

        {{-- Logout Button --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:navlist.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                Log Out
            </flux:navlist.item>
        </form>
    </flux:navlist>

    {{-- User Identity (Footer) --}}
    <div class="mt-4 flex items-center gap-3 border-t border-zinc-200 px-2 py-4 dark:border-zinc-700">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-sm font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
            {{ auth()->user()->initials() }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</p>
        </div>
    </div>
</flux:sidebar>

{{-- Mobile Header --}}
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
    <flux:dropdown position="top" align="end">
        <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
        <flux:menu>
            <flux:menu.radio.group>
                <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>Settings</flux:menu.item>
            </flux:menu.radio.group>
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">Log Out</flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

{{ $slot }}

@fluxScripts
</body>
</html>
