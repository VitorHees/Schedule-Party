<flux:dropdown>
    {{-- The Trigger: Your Bell Icon with a Counter --}}
    <flux:navlist.item icon="bell" class="cursor-pointer">
        Notifications
        @if($unreadCount > 0)
            <flux:badge color="red" size="sm" class="ms-auto" inset="top right">{{ $unreadCount }}</flux:badge>
        @endif
    </flux:navlist.item>

    {{-- The Content: Real-time List --}}
    <flux:menu class="w-80 max-h-96 overflow-y-auto">
        <div class="px-4 py-2 font-bold text-sm border-b dark:border-zinc-700">Recent Notifications</div>

        @forelse($notifications as $notification)
            <flux:menu.item :href="$notification->data['url'] ?? '#'" wire:click="markAsRead('{{ $notification->id }}')">
                <div class="flex flex-col gap-1">
                    <span class="font-medium text-sm">{{ $notification->data['message'] }}</span>
                    <span class="text-xs text-zinc-500">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
            </flux:menu.item>
        @empty
            <div class="p-4 text-center text-sm text-zinc-500">No new notifications</div>
        @endforelse
    </flux:menu>
</flux:dropdown>
