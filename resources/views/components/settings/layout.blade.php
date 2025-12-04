@props(['heading', 'subheading'])

<div class="flex flex-col gap-8 sm:flex-row">
    <aside class="w-full sm:w-64 shrink-0">
        <flux:navlist>
            <flux:navlist.item
                :href="route('settings.profile')"
                :current="request()->routeIs('settings.profile')"
                wire:navigate
            >
                {{ __('Profile') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="route('settings.password')"
                :current="request()->routeIs('settings.password')"
                wire:navigate
            >
                {{ __('Password') }}
            </flux:navlist.item>

            <flux:navlist.item
                :href="route('settings.two-factor')"
                :current="request()->routeIs('settings.two-factor')"
                wire:navigate
            >
                {{ __('Two-factor Auth') }}
            </flux:navlist.item>
        </flux:navlist>
    </aside>

    <div class="flex-1 min-w-0">
        <div class="mb-6">
            <flux:heading level="2" size="lg">{{ $heading }}</flux:heading>
            <flux:subheading>{{ $subheading }}</flux:subheading>
        </div>

        {{ $slot }}
    </div>
</div>
