<?php

namespace App\Livewire;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

trait ManagesCalendarGroups
{
    public $isManageRolesModalOpen = false;

    public $role_name = '';
    public $role_color = '#A855F7';
    public $role_is_selectable = false;
    public $role_is_private = false;

    // --- Permission Helper (Duplicate of Main Component or rely on main if mixed in) ---
    // If this trait is only used in SharedCalendar, we can assume $this->checkPermission exists.
    // Otherwise, replicate logic or interface.

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
            'role_is_private' => 'boolean',
        ]);

        if (method_exists($this, 'abortIfNoPermission')) {
            $this->abortIfNoPermission('create_labels');
        }

        Group::create([
            'calendar_id' => $this->calendar->id,
            'name' => $this->role_name,
            'color' => $this->role_color,
            'is_selectable' => $this->role_is_selectable,
            'is_private' => $this->role_is_private,
        ]);

        $this->resetRoleForm();
    }

    public function deleteRole($groupId)
    {
        if (method_exists($this, 'abortIfNoPermission')) {
            $this->abortIfNoPermission('delete_any_label');
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

        if (!$group->is_selectable) return;

        // NEW LOGIC: Join Locked Labels
        if ($group->is_private) {
            if (method_exists($this, 'checkPermission')) {
                if (!$this->checkPermission('join_private_labels') && !$this->isOwner) {
                    $this->dispatch('action-message', message: 'This group is locked.');
                    return;
                }
            }
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
        $this->role_is_private = false;
        $this->resetValidation();
    }

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
