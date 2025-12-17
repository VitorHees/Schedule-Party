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

    // State for configuring a specific label or user
    public $selectedEntityId = null;
    public $selectedEntityMode = null; // 'label' or 'user'

    #[On('open-permissions-modal')]
    public function openModal($tab = null, $userId = null)
    {
        $this->isOpen = true;
        $this->reset(['selectedEntityId', 'selectedEntityMode', 'userSearch']);

        // Set Tab
        if ($tab && in_array($tab, $this->visibleTabs)) {
            $this->activeTab = $tab;
        } else {
            $this->activeTab = $this->visibleTabs[0] ?? '';
        }

        // If User ID provided and we are on Users tab, select the entity immediately
        if ($userId && $this->activeTab === 'users') {
            // Verify this user actually belongs to this calendar context to be safe
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

        // Fallback for Owner/Admin if permissions are missing from DB
        if (empty($tabs) && $this->calendar->users()->where('user_id', Auth::id())->wherePivot('role_id', Role::where('slug', 'owner')->first()->id)->exists()) {
            return ['roles', 'labels', 'users'];
        }

        return $tabs;
    }

    #[Computed]
    public function permissions()
    {
        return Permission::all()->groupBy('category');
    }

    #[Computed]
    public function roles()
    {
        return Role::whereIn('slug', ['admin', 'member'])->get();
    }

    #[Computed]
    public function selectableLabels()
    {
        return $this->calendar->groups()
            ->where('is_selectable', true)
            ->get();
    }

    #[Computed]
    public function users()
    {
        // Fix: Fetch users and include Pivot data to avoid "call to first() on null"
        return $this->calendar->users()
            ->with(['calendarUsers.role']) // Eager load if possible, or just rely on pivot
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
        }
    }

    public function selectEntity($id, $mode)
    {
        $this->selectedEntityId = $id;
        $this->selectedEntityMode = $mode;
    }

    public function goBackToEntityList()
    {
        $this->selectedEntityId = null;
        $this->selectedEntityMode = null;
    }

    // --- TOGGLES ---

    public function toggleRolePermission($roleId, $permissionId)
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_role_permissions') && !$this->isOwner()) return;

        $role = Role::find($roleId);
        $permission = Permission::find($permissionId);

        if ($role && $permission) {
            $role->permissions()->toggle($permission->id);
        }
    }

    public function toggleLabelPermission($permissionId)
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_label_permissions') && !$this->isOwner()) return;

        $group = Group::find($this->selectedEntityId);
        $permission = Permission::find($permissionId);

        if ($group && $permission && $group->calendar_id === $this->calendar->id) {
            $group->permissions()->toggle($permission->id);
        }
    }

    public function toggleUserOverride($permissionId)
    {
        if (!Auth::user()->hasPermissionInCalendar($this->calendar, 'manage_user_permissions') && !$this->isOwner()) return;

        $user = User::find($this->selectedEntityId);
        $permission = Permission::find($permissionId);

        if (!$user || !$permission) return;

        $calendarUser = $this->calendar->calendarUsers()->where('user_id', $user->id)->first();
        if (!$calendarUser) return;

        // Check existing override
        $override = $calendarUser->permissionOverrides()
            ->where('permission_id', $permission->id)
            ->first();

        if (!$override) {
            // Create Allow (True)
            $calendarUser->permissionOverrides()->create([
                'permission_id' => $permission->id,
                'granted' => true
            ]);
        } elseif ($override->granted === true) {
            // Switch to Deny (False)
            $override->update(['granted' => false]);
        } else {
            // Remove Override (Reset to Inherit)
            $override->delete();
        }
    }

    // Helper to check ownership locally
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
        $this->reset(['activeTab', 'userSearch', 'selectedEntityId', 'selectedEntityMode']);
    }

    public function render()
    {
        return view('livewire.manage-permissions');
    }
}
