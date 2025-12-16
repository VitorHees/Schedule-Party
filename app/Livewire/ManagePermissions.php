<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\Permission;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class ManagePermissions extends Component
{
    public Calendar $calendar;
    public $isOpen = false;
    public $activeTab = 'roles'; // roles, labels, users
    public $userSearch = '';

    #[On('open-permissions-modal')]
    public function openModal()
    {
        $this->isOpen = true;
    }

    public function mount(Calendar $calendar)
    {
        $this->calendar = $calendar;
    }

    #[Computed]
    public function permissions()
    {
        return Permission::all()->groupBy('category');
    }

    #[Computed]
    public function roles()
    {
        // REMOVED 'guest' from here.
        // Guests are strictly view-only and not configurable by users.
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
        return $this->calendar->users()
            ->when($this->userSearch, fn($q) => $q->where('username', 'like', '%'.$this->userSearch.'%'))
            ->take(10)
            ->get();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleRolePermission($roleId, $permissionId)
    {
        $role = Role::find($roleId);
        $permission = Permission::find($permissionId);

        if ($role && $permission) {
            $role->hasPermission($permission->slug)
                ? $role->revokePermission($permission)
                : $role->grantPermission($permission);
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['activeTab', 'userSearch']);
    }

    public function render()
    {
        return view('livewire.manage-permissions');
    }
}
