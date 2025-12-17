<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class ManagePermissions extends Component
{
    public Calendar $calendar;
    public $isOpen = false;
    public $activeTab = '';
    public $userSearch = '';

    public $selectedEntityId = null;
    public $selectedEntityMode = null;

    // Pending State
    public $pendingRolePermissions = []; // [role_id => [perm_id => true]]
    // For Labels and Users, values are now: 'granted', 'denied', or unset (inherit)
    public $pendingLabelPermissions = [];
    public $pendingUserOverrides = [];
    public $hasUnsavedChanges = false;

    #[On('open-permissions-modal')]
    public function openModal($tab = null, $userId = null)
    {
        $this->isOpen = true;
        $this->reset(['selectedEntityId', 'selectedEntityMode', 'userSearch', 'hasUnsavedChanges']);

        if ($tab && in_array($tab, $this->visibleTabs)) {
            $this->activeTab = $tab;
        } else {
            $this->activeTab = $this->visibleTabs[0] ?? '';
        }

        if ($this->activeTab === 'roles') {
            $this->initRoleState();
        }

        if ($userId && $this->activeTab === 'users') {
            $userExists = $this->calendar->users()->where('users.id', $userId)->exists();
            if ($userExists) {
                $this->selectEntity($userId, 'user');
            }
        }
    }

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    #[Computed]
    public function visibleTabs()
    {
        $tabs = [];
        if (Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_role_permissions')) $tabs[] = 'roles';
        if (Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_label_permissions')) $tabs[] = 'labels';
        if (Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_user_permissions')) $tabs[] = 'users';

        if (empty($tabs) && $this->calendar->users()->where('user_id', Auth::id())->wherePivot('role_id', Role::where('slug', 'owner')->first()->id)->exists()) {
            return ['roles', 'labels', 'users'];
        }

        return $tabs;
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

    #[Computed]
    public function roles()
    {
        return Role::whereIn('slug', ['admin', 'member'])->get();
    }

    #[Computed]
    public function selectableLabels()
    {
        return $this->calendar->groups()->where('is_selectable', true)->get();
    }

    #[Computed]
    public function users()
    {
        return $this->calendar->users()
            ->with(['calendarUsers.role'])
            ->when($this->userSearch, fn($q) => $q->where('username', 'like', '%'.$this->userSearch.'%'))
            ->take(10)
            ->get();
    }

    public function setTab($tab)
    {
        if (in_array($tab, $this->visibleTabs)) {
            $this->activeTab = $tab;
            $this->selectedEntityId = null;
            $this->selectedEntityMode = null;
            $this->hasUnsavedChanges = false;

            if ($tab === 'roles') {
                $this->initRoleState();
            }
        }
    }

    public function selectEntity($id, $mode)
    {
        $this->selectedEntityId = $id;
        $this->selectedEntityMode = $mode;
        $this->hasUnsavedChanges = false;

        if ($mode === 'label') {
            $this->initLabelState($id);
        } elseif ($mode === 'user') {
            $this->initUserState($id);
        }
    }

    public function goBackToEntityList()
    {
        $this->selectedEntityId = null;
        $this->selectedEntityMode = null;
        $this->hasUnsavedChanges = false;
    }

    // --- STATE INITIALIZATION ---

    private function initRoleState()
    {
        $this->pendingRolePermissions = [];
        foreach ($this->roles as $role) {
            $this->pendingRolePermissions[$role->id] = $role->permissions()->pluck('permissions.id', 'permissions.id')->toArray();
        }
    }

    private function initLabelState($labelId)
    {
        $label = Group::find($labelId);
        $this->pendingLabelPermissions = [];
        if ($label) {
            foreach ($label->permissions as $perm) {
                // Pivot 'granted' is now available
                $this->pendingLabelPermissions[$perm->id] = $perm->pivot->granted ? 'granted' : 'denied';
            }
        }
    }

    private function initUserState($userId)
    {
        $calendarUser = $this->calendar->calendarUsers()->where('user_id', $userId)->first();
        $this->pendingUserOverrides = [];

        if ($calendarUser) {
            foreach ($calendarUser->permissionOverrides as $override) {
                $this->pendingUserOverrides[$override->permission_id] = $override->granted ? 'granted' : 'denied';
            }
        }
    }

    // --- TOGGLES ---

    public function toggleRolePermission($roleId, $permissionId)
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_role_permissions') && !$this->isOwner()) return;

        // Roles are still binary (Has permission or not)
        if (isset($this->pendingRolePermissions[$roleId][$permissionId])) {
            unset($this->pendingRolePermissions[$roleId][$permissionId]);
        } else {
            $this->pendingRolePermissions[$roleId][$permissionId] = true;
        }
        $this->hasUnsavedChanges = true;
    }

    public function toggleLabelPermission($permissionId)
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_label_permissions') && !$this->isOwner()) return;

        // Cycle: Inherit (unset) -> Granted -> Denied -> Inherit
        $current = $this->pendingLabelPermissions[$permissionId] ?? 'inherit';

        if ($current === 'inherit') {
            $this->pendingLabelPermissions[$permissionId] = 'granted';
        } elseif ($current === 'granted') {
            $this->pendingLabelPermissions[$permissionId] = 'denied';
        } else {
            unset($this->pendingLabelPermissions[$permissionId]); // Back to inherit
        }
        $this->hasUnsavedChanges = true;
    }

    public function toggleUserOverride($permissionId)
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_user_permissions') && !$this->isOwner()) return;

        // Cycle: Inherit (unset) -> Granted -> Denied -> Inherit
        $current = $this->pendingUserOverrides[$permissionId] ?? 'inherit';

        if ($current === 'inherit') {
            $this->pendingUserOverrides[$permissionId] = 'granted';
        } elseif ($current === 'granted') {
            $this->pendingUserOverrides[$permissionId] = 'denied';
        } else {
            unset($this->pendingUserOverrides[$permissionId]); // Back to inherit
        }
        $this->hasUnsavedChanges = true;
    }

    // --- SAVE ---

    public function save()
    {
        if ($this->activeTab === 'roles') {
            $this->saveRoles();
        } elseif ($this->activeTab === 'labels' && $this->selectedEntityId) {
            $this->saveLabel();
        } elseif ($this->activeTab === 'users' && $this->selectedEntityId) {
            $this->saveUser();
        }

        $this->hasUnsavedChanges = false;
        $this->dispatch('permissions-saved');
    }

    private function saveRoles()
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_role_permissions') && !$this->isOwner()) return;

        foreach ($this->pendingRolePermissions as $roleId => $permIds) {
            $role = Role::find($roleId);
            if ($role) {
                $role->permissions()->sync(array_keys($permIds));
            }
        }
    }

    private function saveLabel()
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_label_permissions') && !$this->isOwner()) return;

        $group = Group::find($this->selectedEntityId);
        if ($group && $group->calendar_id === $this->calendar->id) {
            // Prepare sync data: [id => ['granted' => bool]]
            $syncData = [];
            foreach ($this->pendingLabelPermissions as $permId => $status) {
                $syncData[$permId] = ['granted' => $status === 'granted'];
            }
            $group->permissions()->sync($syncData);
        }
    }

    private function saveUser()
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_user_permissions') && !$this->isOwner()) return;

        $user = User::find($this->selectedEntityId);
        $calendarUser = $this->calendar->calendarUsers()->where('user_id', $user->id)->first();

        if (!$calendarUser) return;

        // Recreate overrides based on pending state
        $calendarUser->permissionOverrides()->delete();

        foreach ($this->pendingUserOverrides as $permId => $status) {
            $calendarUser->permissionOverrides()->create([
                'permission_id' => $permId,
                'granted' => $status === 'granted'
            ]);
        }
    }

    private function isOwner()
    {
        $ownerRole = Role::where('slug', 'owner')->first();
        return $this->calendar->users()
            ->where('user_id', Auth::id())
            ->wherePivot('role_id', $ownerRole->id)
            ->exists();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['activeTab', 'userSearch', 'selectedEntityId', 'selectedEntityMode', 'hasUnsavedChanges']);
    }

    public function render()
    {
        return view('livewire.manage-permissions');
    }
}
