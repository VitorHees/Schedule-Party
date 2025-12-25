<flux:dropdown>
    {{-- Trigger: Bell Icon met een zwevende indicator --}}
    <flux:navlist.item icon="bell" class="relative cursor-pointer group">
        Notifications
        @if($unreadCount > 0)
            <span class="absolute right-2 top-2 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-purple-600 text-[10px] items-center justify-center text-white font-bold">
                    {{ $unreadCount }}
                </span>
            </span>
        @endif
    </flux:navlist.item>

    {{-- Menu Content --}}
    <flux:menu class="w-80 p-0 shadow-2xl border-zinc-200 dark:border-zinc-700 overflow-hidden">
        {{-- Header --}}
        <div class="px-4 py-3 bg-zinc-50/50 dark:bg-zinc-900/50 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
            <span class="font-bold text-xs uppercase tracking-widest text-zinc-500">Notifications</span>
            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-[10px] text-purple-600 dark:text-purple-400 hover:underline font-bold focus:outline-none"
                >
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Scrollable List --}}
        <div class="max-h-96 overflow-y-auto custom-scrollbar">
            @forelse($notifications as $notification)
                <flux:menu.item
                    :href="$notification->data['url'] ?? '#'"
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="p-4 border-b border-zinc-100 dark:border-zinc-800/50 last:border-0 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                >
                    <div class="flex gap-3">
                        {{-- Icon gebaseerd op type (optioneel, maar staat erg goed) --}}
                        <div class="mt-1 flex-shrink-0">
                            @if(str_contains($notification->data['message'], 'Invite'))
                                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-full text-purple-600 dark:text-purple-400">
                                    <x-heroicon-o-user-plus class="w-4 h-4" />
                                </div>
                            @else
                                <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-full text-zinc-500">
                                    <x-heroicon-o-bell class="w-4 h-4" />
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1">
                            <span class="font-semibold text-sm leading-tight {{ $notification->read_at ? 'text-zinc-500' : 'text-zinc-900 dark:text-white' }}">
                                {{ $notification->data['message'] }}
                            </span>
                            <span class="text-[10px] font-medium text-zinc-400 flex items-center gap-1">
                                <x-heroicon-o-clock class="w-3 h-3" />
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </flux:menu.item>
            @empty
                <div class="p-8 text-center flex flex-col items-center gap-2">
                    <x-heroicon-o-bell-slash class="w-8 h-8 text-zinc-300 dark:text-zinc-600" />
                    <p class="text-xs font-medium text-zinc-500">All caught up! No new notifications.</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="p-2 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            <flux:menu.item href="{{ route('notifications') }}" class="flex justify-center text-xs font-bold text-zinc-500 hover:text-purple-600 transition-colors">
                View All Notifications
            </flux:menu.item>
        </div>
    </flux:menu>
</flux:dropdown>
