<?php

namespace App\Livewire;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

trait ManagesCalendarGroups
{
    // --- State ---
    public $isManageRolesModalOpen = false;

    public $role_name = '';
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
            // Permission check: only owners can create
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

        // FIXED: Allow owners to join labels even if they aren't strictly "selectable" for others,
        // OR simply ensure owners are subject to the same "selectable" rules but bypass restrictions.
        // Requirement: "Everyone should be able to join the selectable labels, owner included."

        $canJoin = $group->is_selectable;

        // Optional: If you want owners to be able to force-join non-selectable groups, uncomment:
        // if (property_exists($this, 'isOwner') && $this->isOwner) { $canJoin = true; }

        if (!$canJoin) {
            // For now, adhere to "is_selectable" strictly, or dispatch error.
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
