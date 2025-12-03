<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="!p-0">
        {{ $slot }}
        <x-personal.footer />
    </flux:main>
</x-layouts.app.sidebar>
