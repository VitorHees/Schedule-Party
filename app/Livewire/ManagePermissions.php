<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Group;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class ManagePermissions extends Component
{
    public Calendar $calendar;
    public $isOpen = false;
    public $activeTab = 'roles'; // roles, labels, users

    // Data Holders
    // public $permissions; // REMOVED: Grouped collections crash public properties
    public $roles;
    public $selectableLabels;
    public $users;

    // Search states
    public $userSearch = '';

    #[On('open-permissions-modal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->loadData();
    }

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    // Moved logic here. Computed properties are cached per request and don't cause hydration errors.
    #[Computed]
    public function permissions()
    {
        return Permission::all()->groupBy('category');
    }

    public function loadData()
    {
        // 1. Permissions are now handled by the computed property above

        // 2. Load Roles (excluding Owner usually, as Owner has all perms, but customizable)
        $this->roles = Role::whereIn('slug', ['admin', 'member'])->get();

        // 3. Load Selectable Labels (Groups)
        $this->selectableLabels = $this->calendar->groups()
            ->where('is_selectable', true)
            ->get();

        // 4. Load Users (Simple limit for now)
        $this->users = $this->calendar->users()
            ->when($this->userSearch, fn($q) => $q->where('username', 'like', '%'.$this->userSearch.'%'))
            ->take(10)
            ->get();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // --- TAB 1: ROLES LOGIC ---

    public function toggleRolePermission($roleId, $permissionId)
    {
        $role = Role::find($roleId);
        $permission = Permission::find($permissionId);

        if (!$role || !$permission) return;

        // Check if exists
        $hasPermission = $role->permissions()->where('permissions.id', $permissionId)->exists();

        if ($hasPermission) {
            $role->revokePermission($permission);
        } else {
            $role->grantPermission($permission);
        }

        // Refresh data to update UI state check
        $this->loadData();
    }

    // --- TAB 2: LABELS LOGIC (Foundation) ---

    public function configureLabel($labelId)
    {
        // Placeholder: Set a state to show detailed settings for this label
        // e.g., $this->editingLabelId = $labelId;
    }

    // --- TAB 3: USERS LOGIC (Foundation) ---

    public function configureUser($userId)
    {
        // Placeholder: Set a state to show overrides for this user
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset('activeTab', 'userSearch');
    }

    public function render()
    {
        return view('livewire.manage-permissions');
    }
}
