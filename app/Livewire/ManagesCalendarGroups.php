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

    public function openManageRolesModal()
    {
        // Allow access if the user has ANY of the label-related permissions
        // (This logic is usually in the component, but we keep the reset here)
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
            // 1. Basic Create Permission
            $this->abortIfNoPermission('create_labels');

            // 2. Selectable Permission
            if ($this->role_is_selectable) {
                $this->abortIfNoPermission('create_selectable_labels');
            }

            // 3. Private Permission (Requires 'assign_labels' - User Management)
            if ($this->role_is_private) {
                $this->abortIfNoPermission('assign_labels');
            }
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
        // 4. Delete Permission
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
        if (!$group || !$group->is_selectable) return;

        // Permission Check
        if (method_exists($this, 'checkPermission') && !$this->isOwner) {
            if ($group->is_private) {
                if (!$this->checkPermission('join_private_labels')) {
                    $this->dispatch('action-message', message: 'This group is locked.');
                    return;
                }
            } else {
                if (!$this->checkPermission('join_labels') && !$this->checkPermission('create_labels')) {
                    $this->dispatch('action-message', message: 'Permission denied.');
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
        if ($this->isOwner || (method_exists($this, 'checkPermission') && $this->checkPermission('create_labels'))) {
            return $this->calendar->groups;
        }

        return $this->calendar->groups->filter(function ($group) {
            if (!$group->is_selectable) return false;

            $canSeePublic = method_exists($this, 'checkPermission') && $this->checkPermission('join_labels');
            $canSeePrivate = method_exists($this, 'checkPermission') && $this->checkPermission('join_private_labels');

            return $group->is_private ? $canSeePrivate : $canSeePublic;
        });
    }

    public function getUserRoleIdsProperty()
    {
        return Auth::check()
            ? Auth::user()->groups()->where('calendar_id', $this->calendar->id)->pluck('groups.id')->toArray()
            : [];
    }
}
