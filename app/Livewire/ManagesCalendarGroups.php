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

    public $editingGroupId = null;

    public function editRole($groupId)
    {
        $group = $this->calendar->groups()->find($groupId);
        if (!$group) return;

        $this->editingGroupId = $groupId;
        $this->role_name = $group->name;
        $this->role_color = $group->color;
        $this->role_is_selectable = $group->is_selectable;
        $this->role_is_private = $group->is_private;
    }

    public function openManageRolesModal()
    {
        // Allow access if the user has ANY of the label-related permissions
        // (This logic is usually in the component, but we keep the reset here)
        $this->resetRoleForm();
        $this->isManageRolesModalOpen = true;
    }

    public function createRole()
    {
        // Basis validatie voor naam en kleur
        $this->validate([
            'role_name' => 'required|min:2|max:50',
            'role_color' => 'required',
            'role_is_selectable' => 'boolean',
            'role_is_private' => 'boolean',
        ]);

        if ($this->editingGroupId) {
            // --- UPDATE LOGICA ---

            // Controleer of de gebruiker permissie heeft om labels te beheren/verwijderen
            if (method_exists($this, 'abortIfNoPermission')) {
                $this->abortIfNoPermission('delete_any_label');
            }

            $group = Group::find($this->editingGroupId);

            if ($group) {
                $group->update([
                    'name' => $this->role_name,
                    'color' => $this->role_color,
                    'is_selectable' => $this->role_is_selectable,
                    'is_private' => $this->role_is_private,
                ]);

                $this->calendar->logActivity('updated', 'Group', $group->id, Auth::user(), [
                    'name' => $group->name,
                    'type' => $group->is_selectable ? 'Selectable Label' : 'Role/Group'
                ]);
            }
        } else {
            // --- CREATE LOGICA ---

            if (method_exists($this, 'abortIfNoPermission')) {
                // 1. Basis permissie voor aanmaken
                $this->abortIfNoPermission('create_labels');

                // 2. Specifieke permissie voor 'selectable' labels
                if ($this->role_is_selectable) {
                    $this->abortIfNoPermission('create_selectable_labels');
                }

                // 3. Specifieke permissie voor 'private' labels (User Management vereist)
                if ($this->role_is_private) {
                    $this->abortIfNoPermission('assign_labels');
                }
            }

            $group = Group::create([
                'calendar_id' => $this->calendar->id,
                'name' => $this->role_name,
                'color' => $this->role_color,
                'is_selectable' => $this->role_is_selectable,
                'is_private' => $this->role_is_private,
            ]);

            $this->calendar->logActivity('created', 'Group', $group->id, Auth::user(), [
                'name' => $group->name,
                'type' => $group->is_selectable ? 'Selectable Label' : 'Role/Group'
            ]);
        }

        // Reset het formulier en de bewerk-status
        $this->resetRoleForm();
        $this->editingGroupId = null;
    }

    public function deleteRole($groupId)
    {
        // 4. Delete Permission
        if (method_exists($this, 'abortIfNoPermission')) {
            $this->abortIfNoPermission('delete_any_label');
        }

        $group = $this->calendar->groups()->find($groupId);
        if ($group) {
            $groupName = $group->name;
            $group->delete();

            $this->calendar->logActivity('deleted', 'Group', $groupId, Auth::user(), [
                'name' => $groupName
            ]);
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
            $this->calendar->logActivity('left_group', 'Group', $group->id, $user, [
                'group_name' => $group->name
            ]);
        } else {
            $group->users()->attach($user->id, ['assigned_at' => now()]);
            $this->calendar->logActivity('joined_group', 'Group', $group->id, $user, [
                'group_name' => $group->name
            ]);
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
