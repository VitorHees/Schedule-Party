@props(['permissions', 'roles', 'toggleAction', 'pendingPermissions' => null])

<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
        <tr>
            <th class="px-4 py-3 font-bold">Permission</th>
            @foreach($roles as $role)
                <th class="px-4 py-3 text-center font-bold">{{ $role->name }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        @foreach($permissions as $category => $perms)
            <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                <td colspan="{{ count($roles) + 1 }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                    {{ $category }}
                </td>
            </tr>
            @foreach($perms as $permission)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $permission->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $permission->description }}</p>
                    </td>
                    @foreach($roles as $role)
                        <td class="px-4 py-3 text-center">
                            <label class="relative inline-flex cursor-pointer items-center">
                                @php
                                    // Check pending state if provided, otherwise fall back to model
                                    $isChecked = $pendingPermissions
                                        ? isset($pendingPermissions[$role->id][$permission->id])
                                        : $role->hasPermission($permission->slug);
                                @endphp
                                <input type="checkbox"
                                       class="peer sr-only"
                                       @if($isChecked) checked @endif
                                       wire:click="{{ $toggleAction }}({{ $role->id }}, {{ $permission->id }})">

                                <div class="peer h-5 w-9 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-purple-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700 dark:border-gray-600"></div>
                            </label>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>
