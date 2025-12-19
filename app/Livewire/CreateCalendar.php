<?php

namespace App\Livewire;

use App\Models\Calendar;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateCalendar extends Component
{
    public $isOpen = false;

    #[Validate('required|min:3|max:50')]
    public $name = '';

    public $pendingRolePermissions = [];

    #[On('open-create-calendar-modal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->initRoleState();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset('name', 'pendingRolePermissions');
        $this->resetValidation();
    }

    private function initRoleState()
    {
        $this->pendingRolePermissions = [];

        // 1. Fetch all permissions to map slugs to IDs
        $allPermissions = Permission::all();

        // 2. Define the Default Slugs (Mirroring RolePermissionSeeder)
        $defaults = [
            'member' => [
                'view_events', 'view_comments', 'rsvp_event',
                'vote_poll', 'create_comment', 'join_labels'
            ],
            // Admin has everything EXCEPT these specific ones
            'admin' => $allPermissions->pluck('slug')
                ->reject(fn($s) => in_array($s, ['manage_role_permissions', 'import_personal_calendar']))
                ->toArray(),
        ];

        // 3. Populate pending permissions based on these defaults
        foreach ($this->roles as $role) {
            if (array_key_exists($role->slug, $defaults)) {
                $allowedSlugs = $defaults[$role->slug];

                // Find the IDs for these slugs
                $ids = $allPermissions->whereIn('slug', $allowedSlugs)
                    ->pluck('id', 'id')
                    ->toArray();

                $this->pendingRolePermissions[$role->id] = $ids;
            } else {
                // Fallback for unknown roles: Load from DB
                $this->pendingRolePermissions[$role->id] = $role->permissions()
                    ->pluck('permissions.id', 'permissions.id')
                    ->toArray();
            }
        }
    }

    #[Computed]
    public function roles()
    {
        return Role::whereIn('slug', ['admin', 'member'])->get();
    }

    #[Computed]
    public function permissions()
    {
        $allPermissions = Permission::all()->keyBy('slug');
        $structure = [
            'General Access' => ['view_events', 'view_comments', 'rsvp_event', 'vote_poll'],
            'Content Creation' => ['create_events', 'add_images', 'create_poll', 'create_comment', 'import_personal_calendar'],
            'Organization & Labels' => ['add_labels', 'create_labels', 'assign_labels', 'create_selectable_labels', 'join_labels', 'join_private_labels', 'delete_any_label'],
            'Moderation' => ['edit_any_event', 'delete_any_event', 'delete_any_comment'],
            'Member Management' => ['invite_users', 'view_active_links', 'manage_invites', 'kick_users'],
            'System Administration' => ['manage_role_permissions', 'manage_label_permissions', 'manage_user_permissions', 'view_logs'],
        ];

        $grouped = collect();
        foreach ($structure as $category => $slugs) {
            $perms = collect();
            foreach ($slugs as $slug) {
                if ($allPermissions->has($slug)) $perms->push($allPermissions->get($slug));
            }
            if ($perms->isNotEmpty()) $grouped->put($category, $perms);
        }
        return $grouped;
    }

    public function toggleRolePermission($roleId, $permissionId)
    {
        if (isset($this->pendingRolePermissions[$roleId][$permissionId])) {
            unset($this->pendingRolePermissions[$roleId][$permissionId]);
        } else {
            $this->pendingRolePermissions[$roleId][$permissionId] = true;
        }
    }

    public function create()
    {
        $this->validate();

        // 1. Create the Calendar
        $calendar = Calendar::create([
            'name' => $this->name,
            'type' => 'collaborative',
            'groups_locked' => false,
        ]);

        // 2. Get Owner Role
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        // 3. Attach User as Owner
        $calendar->users()->attach(Auth::id(), [
            'role_id' => $ownerRole->id,
            'joined_at' => now(),
        ]);

        // 4. Update Role Permissions (This overwrites global roles with your new defaults + edits)
        foreach ($this->pendingRolePermissions as $roleId => $permIds) {
            $role = Role::find($roleId);
            if ($role) {
                $role->permissions()->sync(array_keys($permIds));
            }
        }

        $this->closeModal();

        // 5. Redirect to the new calendar
        return redirect()->route('calendar.shared', $calendar);
    }

    public function render()
    {
        return view('livewire.create-calendar');
    }
}
