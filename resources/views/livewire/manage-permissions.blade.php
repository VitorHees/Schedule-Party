<div>
    @if($isOpen)
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="flex h-[600px] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all dark:bg-gray-800">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-100 p-6 dark:border-gray-700">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Permissions Manager</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Control access levels for {{ $calendar->name }}</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                {{-- TABS --}}
                <div class="flex border-b border-gray-100 bg-gray-50/50 px-6 dark:border-gray-700 dark:bg-gray-900/30">
                    @foreach(['roles' => 'Roles', 'labels' => 'Labels', 'users' => 'Users'] as $key => $label)
                        <button
                            wire:click="setTab('{{ $key }}')"
                            class="mr-6 border-b-2 py-3 text-sm font-bold transition-colors {{ $activeTab === $key ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- CONTENT AREA --}}
                <div class="flex-1 overflow-y-auto p-0">

                    {{-- TAB 1: ROLES --}}
                    @if($activeTab === 'roles')
                        <div class="animate-in fade-in slide-in-from-bottom-2">
                            <div class="bg-blue-50 p-4 text-xs text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                                <span class="font-bold">Note:</span> These settings apply globally to all users with these roles.
                            </div>

                            {{-- REUSABLE COMPONENT USED HERE --}}
                            <x-permissions.role-matrix
                                :permissions="$permissions"
                                :roles="$roles"
                                toggleAction="toggleRolePermission"
                            />
                        </div>
                    @endif

                    {{-- TAB 2: LABELS --}}
                    @if($activeTab === 'labels')
                        <div class="p-6 animate-in fade-in slide-in-from-bottom-2">
                            <p class="mb-4 text-sm text-gray-500">Configure who can view or assign specific labels.</p>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                @foreach($selectableLabels as $label)
                                    <div class="flex items-center justify-between rounded-xl border border-gray-200 p-4 hover:border-purple-300 dark:border-gray-700 dark:hover:border-purple-500 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="h-4 w-4 rounded-full shadow-sm" style="background-color: {{ $label->color }}"></div>
                                            <div>
                                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $label->name }}</h4>
                                                <p class="text-[10px] uppercase text-gray-400">{{ $label->is_private ? 'Private' : 'Public' }}</p>
                                            </div>
                                        </div>
                                        <button wire:click="configureLabel({{ $label->id }})" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                            Configure
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- TAB 3: USERS --}}
                    @if($activeTab === 'users')
                        <div class="p-6 animate-in fade-in slide-in-from-bottom-2">
                            <div class="mb-4">
                                <input type="text" wire:model.live.debounce.300ms="userSearch" placeholder="Search user to override permissions..." class="w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div class="space-y-2">
                                @foreach($users as $user)
                                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs">
                                                {{ substr($user->username, 0, 2) }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->username }}</span>
                                        </div>
                                        <button wire:click="configureUser({{ $user->id }})" class="text-xs font-medium text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                            View Overrides
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                {{-- FOOTER --}}
                <div class="border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex justify-end">
                        <button wire:click="closeModal" class="rounded-xl bg-gray-900 px-6 py-2 text-sm font-bold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
