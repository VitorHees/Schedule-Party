<?php

namespace App\Livewire;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

trait ManagesCalendarGroups
{
    // --- State ---
    public $isManageRolesModalOpen = false;

    #[Validate('required|min:2|max:50')]
    public $role_name = '';

    #[Validate('required')]
    public $role_color = '#A855F7';

    public $role_is_selectable = false;

    // --- Actions ---

    public function openManageRolesModal()
    {
        $this->resetRoleForm();
        $this->isManageRolesModalOpen = true;
    }

    public function createRole()
    {
        $this->validate([
            'role_name' => 'required|min:2|max:50',
            'role_color' => 'required',
            'role_is_selectable' => 'boolean',
        ]);

        if (!Auth::check() || !$this->calendar->users->contains(Auth::id())) {
            // Permission check: You might want to restrict this to 'owner' only
            if (property_exists($this, 'isOwner') && !$this->isOwner) {
                abort(403, 'Only owners can create roles.');
            }
        }

        Group::create([
            'calendar_id' => $this->calendar->id,
            'name' => $this->role_name,
            'color' => $this->role_color,
            'is_selectable' => $this->role_is_selectable,
        ]);

        $this->resetRoleForm();
        // $this->dispatch('notify', 'Role created successfully.');
    }

    public function deleteRole($groupId)
    {
        if (property_exists($this, 'isOwner') && !$this->isOwner) {
            abort(403);
        }

        $group = $this->calendar->groups()->find($groupId);
        if ($group) {
            $group->delete();
        }
    }

    public function toggleRoleMembership($groupId)
    {
        if (!Auth::check()) return;

        $group = $this->calendar->groups()->find($groupId);
        if (!$group) return;

        // If it's NOT selectable, it's mandatory/global, so users can't leave it.
        // Owners generally bypass this restriction or manage it differently.
        if (!$group->is_selectable && (property_exists($this, 'isOwner') && !$this->isOwner)) {
            return;
        }

        $user = Auth::user();

        if ($group->users->contains($user->id)) {
            $group->users()->detach($user->id);
        } else {
            $group->users()->attach($user->id, ['assigned_at' => now()]);
        }
    }

    private function resetRoleForm()
    {
        $this->role_name = '';
        $this->role_color = '#A855F7';
        $this->role_is_selectable = false;
        $this->resetValidation();
    }

    // --- Computed Properties ---

    public function getAvailableRolesProperty()
    {
        return $this->calendar->groups;
    }

    public function getUserRoleIdsProperty()
    {
        return Auth::check()
            ? Auth::user()->groups()->where('calendar_id', $this->calendar->id)->pluck('groups.id')->toArray()
            : [];
    }
}
