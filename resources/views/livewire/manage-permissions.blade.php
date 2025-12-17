<div>
    @if($isOpen)
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity">
            <div class="flex h-[700px] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl transition-all dark:bg-gray-800">

                {{-- HEADER --}}
                <div class="flex items-center justify-between px-8 pt-8 pb-6 bg-white dark:bg-gray-800 shrink-0">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">Permissions</h2>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Manage access for {{ $calendar->name }}</p>
                    </div>
                    <button wire:click="closeModal" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors dark:bg-gray-700/50 dark:text-gray-300 dark:hover:bg-gray-700">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                {{-- TABS --}}
                <div class="px-8 pb-4 shrink-0">
                    <div class="flex gap-2 border-b border-gray-100 dark:border-gray-700">
                        @foreach(['roles' => 'Roles', 'labels' => 'Labels', 'users' => 'Users'] as $key => $label)
                            @if(in_array($key, $this->visibleTabs))
                                <button
                                    wire:click="setTab('{{ $key }}')"
                                    class="px-4 py-3 text-sm font-bold border-b-2 transition-colors
                                    {{ $activeTab === $key
                                        ? 'border-purple-600 text-purple-600 dark:border-purple-400 dark:text-purple-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                                    }}"
                                >
                                    {{ $label }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- CONTENT --}}
                <div class="flex-1 overflow-y-auto px-8 py-4">

                    {{-- TAB 1: ROLES (Role Permissions) --}}
                    @if($activeTab === 'roles')
                        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="mb-6 rounded-xl bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                                <div class="flex gap-3">
                                    <x-heroicon-s-information-circle class="h-5 w-5 shrink-0" />
                                    <p>Global settings. These apply to everyone with the role, unless overridden by a Label or User setting.</p>
                                </div>
                            </div>
                            <x-permissions.role-matrix
                                :permissions="$this->permissions"
                                :roles="$this->roles"
                                toggleAction="toggleRolePermission"
                            />
                        </div>
                    @endif

                    {{-- TAB 2: LABELS (Label Permissions) --}}
                    @if($activeTab === 'labels')
                        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300 h-full">
                            @if($selectedEntityId && $selectedEntityMode === 'label')
                                {{-- Detail View: Matrix for 1 Label --}}
                                <div class="flex flex-col h-full">
                                    <button wire:click="goBackToEntityList" class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-700 mb-4">
                                        <x-heroicon-s-arrow-left class="h-4 w-4" /> Back to Labels
                                    </button>
                                    @php $label = $this->selectableLabels->find($selectedEntityId); @endphp
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="h-4 w-4 rounded-full" style="background-color: {{ $label->color }}"></div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $label->name }} Permissions</h3>
                                    </div>

                                    {{-- Custom Matrix for Single Label --}}
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-900">
                                            <tr>
                                                <th class="px-4 py-3 font-bold text-gray-900 dark:text-white rounded-l-lg">Permission</th>
                                                <th class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white rounded-r-lg">Granted</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach($this->permissions as $category => $perms)
                                                <tr class="bg-gray-50/50 dark:bg-gray-800/50"><td colspan="2" class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500">{{ $category }}</td></tr>
                                                @foreach($perms as $perm)
                                                    @php $hasPerm = $label->permissions->contains($perm->id); @endphp
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <div class="font-medium text-gray-900 dark:text-white">{{ $perm->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $perm->description }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <button wire:click="toggleLabelPermission({{ $perm->id }})"
                                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $hasPerm ? 'bg-purple-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $hasPerm ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                {{-- List View --}}
                                <div class="grid grid-cols-1 gap-3">
                                    <div class="mb-4 rounded-xl bg-purple-50 p-4 text-sm text-purple-700 dark:bg-purple-900/20 dark:text-purple-300">
                                        <p>Permissions granted here <strong>override</strong> Role permissions.</p>
                                    </div>
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
                                            <button wire:click="selectEntity({{ $label->id }}, 'label')" class="rounded-xl bg-white px-4 py-2 text-xs font-bold text-gray-900 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:ring-gray-600">
                                                Configure
                                            </button>
                                        </div>
                                    @empty
                                        <div class="text-center py-8 text-gray-500 text-sm">No selectable labels found.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- TAB 3: USERS (User Permissions) --}}
                    @if($activeTab === 'users')
                        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300 h-full">
                            @if($selectedEntityId && $selectedEntityMode === 'user')
                                {{-- Detail View: Permissions for 1 User --}}
                                <div class="flex flex-col h-full">
                                    <button wire:click="goBackToEntityList" class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-700 mb-4">
                                        <x-heroicon-s-arrow-left class="h-4 w-4" /> Back to Users
                                    </button>
                                    @php
                                        $user = \App\Models\User::find($selectedEntityId);
                                        $calendarUser = $calendar->calendarUsers()->where('user_id', $user->id)->first();
                                    @endphp
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold dark:bg-purple-900 dark:text-purple-300">
                                            {{ substr($user->username, 0, 2) }}
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->username }} Overrides</h3>
                                            <p class="text-xs text-gray-500">Overrides both Label and Role permissions.</p>
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-900">
                                            <tr>
                                                <th class="px-4 py-3 font-bold text-gray-900 dark:text-white rounded-l-lg">Permission</th>
                                                <th class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white rounded-r-lg">Status</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @foreach($this->permissions as $category => $perms)
                                                <tr class="bg-gray-50/50 dark:bg-gray-800/50"><td colspan="2" class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500">{{ $category }}</td></tr>
                                                @foreach($perms as $perm)
                                                    @php
                                                        $override = $calendarUser->permissionOverrides->where('permission_id', $perm->id)->first();
                                                        $status = $override ? ($override->granted ? 'granted' : 'denied') : 'inherit';
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <div class="font-medium text-gray-900 dark:text-white">{{ $perm->name }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            {{-- Tri-state Toggle Logic --}}
                                                            <button wire:click="toggleUserOverride({{ $perm->id }})"
                                                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition-all
                                                                    {{ $status === 'granted' ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : '' }}
                                                                    {{ $status === 'denied' ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : '' }}
                                                                    {{ $status === 'inherit' ? 'bg-gray-100 text-gray-600 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600' : '' }}
                                                                    "
                                                            >
                                                                @if($status === 'granted') <x-heroicon-s-check class="w-3 h-3" /> Allow
                                                                @elseif($status === 'denied') <x-heroicon-s-x-mark class="w-3 h-3" /> Deny
                                                                @else Inherit @endif
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                {{-- List View --}}
                                <div class="flex flex-col h-full">
                                    <div class="relative mb-6 shrink-0">
                                        <x-heroicon-o-magnifying-glass class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                                        <input type="text" wire:model.live.debounce.300ms="userSearch" placeholder="Search members..." class="w-full rounded-2xl border-gray-200 bg-gray-50 pl-11 py-3 text-sm font-medium focus:border-purple-500 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    </div>

                                    <div class="space-y-2 overflow-y-auto">
                                        @foreach($this->users as $user)
                                            <div class="flex items-center justify-between rounded-2xl border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm dark:bg-purple-900 dark:text-purple-300">
                                                        {{ substr($user->username, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->username }}</h4>
                                                        {{-- Fix: Correctly access the role via pivot --}}
                                                        <p class="text-xs text-gray-500">
                                                            @php
                                                                // Use the pivot if available, or find it manually
                                                                $roleName = $user->pivot->role_id
                                                                    ? \App\Models\Role::find($user->pivot->role_id)?->name
                                                                    : 'Member';
                                                            @endphp
                                                            {{ $roleName }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <button wire:click="selectEntity({{ $user->id }}, 'user')" class="mr-2 text-xs font-bold text-purple-600 hover:underline dark:text-purple-400">
                                                    Overrides
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>
