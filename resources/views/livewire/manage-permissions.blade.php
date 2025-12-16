<div>
    @if($isOpen)
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity">
            <div class="flex h-[650px] w-full max-w-2xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl transition-all dark:bg-gray-800">

                {{-- HEADER --}}
                <div class="flex items-center justify-between px-8 pt-8 pb-6">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Permissions</h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Manage access for {{ $calendar->name }}</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors dark:bg-gray-700/50 dark:text-gray-300 dark:hover:bg-gray-700">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                {{-- TABS --}}
                <div class="px-8 pb-6">
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-900/50">
                        @foreach(['roles' => 'Roles', 'labels' => 'Labels', 'users' => 'Users'] as $key => $label)
                            <button
                                wire:click="setTab('{{ $key }}')"
                                class="rounded-lg py-2.5 text-sm font-bold transition-all
                                {{ $activeTab === $key
                                    ? 'bg-white text-gray-900 shadow-sm ring-1 ring-black/5 dark:bg-gray-700 dark:text-white dark:ring-white/10'
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200'
                                }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- CONTENT --}}
                <div class="flex-1 overflow-y-auto px-8 min-h-0">

                    {{-- TAB 1: ROLES --}}
                    @if($activeTab === 'roles')
                        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="mb-6 rounded-xl bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                                <div class="flex gap-3">
                                    <x-heroicon-s-information-circle class="h-5 w-5 shrink-0" />
                                    <p>Global settings for each role.</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-100 overflow-hidden dark:border-gray-700">
                                <x-permissions.role-matrix
                                    :permissions="$this->permissions"
                                    :roles="$this->roles"
                                    toggleAction="toggleRolePermission"
                                />
                            </div>
                        </div>
                    @endif

                    {{-- TAB 2: LABELS --}}
                    @if($activeTab === 'labels')
                        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300 space-y-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Settings for specific labels.</p>

                            <div class="grid grid-cols-1 gap-3">
                                @forelse($this->selectableLabels as $label)
                                    <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50/50 p-4 transition-all hover:border-purple-200 dark:border-gray-700 dark:bg-gray-800/50">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 flex items-center justify-center rounded-full bg-white shadow-sm dark:bg-gray-800">
                                                <div class="h-4 w-4 rounded-full" style="background-color: {{ $label->color }}"></div>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $label->name }}</h4>
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    {{ $label->is_private ? 'Private' : 'Public' }}
                                                </span>
                                            </div>
                                        </div>
                                        <button class="rounded-xl bg-white px-4 py-2 text-xs font-bold text-gray-900 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                            Configure
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500 text-sm">No selectable labels found.</div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- TAB 3: USERS --}}
                    @if($activeTab === 'users')
                        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300 h-full flex flex-col">
                            <div class="relative mb-6">
                                <x-heroicon-o-magnifying-glass class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                                <input type="text" wire:model.live.debounce.300ms="userSearch" placeholder="Search members..." class="w-full rounded-2xl border-gray-200 bg-gray-50 pl-11 py-3 text-sm font-medium focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div class="space-y-2 pb-4">
                                @foreach($this->users as $user)
                                    <div class="flex items-center justify-between rounded-2xl border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm dark:bg-purple-900 dark:text-purple-300">
                                                {{ substr($user->username, 0, 2) }}
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->username }}</h4>
                                                <p class="text-xs text-gray-500">{{ $user->roles->first()?->name ?? 'Member' }}</p>
                                            </div>
                                        </div>
                                        <button class="mr-2 text-xs font-bold text-purple-600 hover:underline dark:text-purple-400">
                                            Overrides
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                {{-- FOOTER --}}
                <div class="border-t border-gray-100 p-6 dark:border-gray-700 mt-auto bg-white dark:bg-gray-800">
                    <button wire:click="closeModal" class="w-full rounded-xl bg-gray-900 py-3.5 text-sm font-bold text-white shadow-lg hover:bg-gray-800 hover:shadow-xl hover:-translate-y-0.5 transition-all dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                        Done
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
