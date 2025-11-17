<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Event permissions
            ['name' => 'View Event', 'slug' => 'view_event', 'category' => 'event', 'description' => 'Can view events in the calendar'],
            ['name' => 'Create Event', 'slug' => 'create_event', 'category' => 'event', 'description' => 'Can create new events'],
            ['name' => 'Edit Event', 'slug' => 'edit_event', 'category' => 'event', 'description' => 'Can edit existing events'],
            ['name' => 'Delete Event', 'slug' => 'delete_event', 'category' => 'event', 'description' => 'Can delete events'],

            // Comment permissions
            ['name' => 'Create Comment', 'slug' => 'create_comment', 'category' => 'comment', 'description' => 'Can comment on events'],
            ['name' => 'Edit Own Comment', 'slug' => 'edit_own_comment', 'category' => 'comment', 'description' => 'Can edit their own comments'],
            ['name' => 'Delete Any Comment', 'slug' => 'delete_any_comment', 'category' => 'comment', 'description' => 'Can delete any comment'],

            // Vote permissions
            ['name' => 'Create Vote', 'slug' => 'create_vote', 'category' => 'vote', 'description' => 'Can create polls on events'],
            ['name' => 'Participate in Vote', 'slug' => 'participate_vote', 'category' => 'vote', 'description' => 'Can vote in polls'],

            // Opt-in permissions
            ['name' => 'Opt In/Out of Events', 'slug' => 'opt_in_event', 'category' => 'event', 'description' => 'Can opt in or out of events'],

            // User management
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'category' => 'user', 'description' => 'Can add/remove users from calendar'],
            ['name' => 'Kick Users', 'slug' => 'kick_users', 'category' => 'user', 'description' => 'Can remove users from calendar'],
            ['name' => 'Assign Roles', 'slug' => 'assign_roles', 'category' => 'user', 'description' => 'Can change user roles'],

            // Calendar management
            ['name' => 'Edit Calendar Settings', 'slug' => 'edit_calendar', 'category' => 'calendar', 'description' => 'Can edit calendar settings'],
            ['name' => 'Delete Calendar', 'slug' => 'delete_calendar', 'category' => 'calendar', 'description' => 'Can delete the entire calendar'],
            ['name' => 'View Activity Log', 'slug' => 'view_activity_log', 'category' => 'calendar', 'description' => 'Can view calendar activity history'],
            ['name' => 'Create Invitation', 'slug' => 'create_invitation', 'category' => 'calendar', 'description' => 'Can create calendar invite links'],
            ['name' => 'Manage Invitations', 'slug' => 'manage_invitations', 'category' => 'calendar', 'description' => 'Can view and revoke invitations'],

            // Group management
            ['name' => 'Create Group', 'slug' => 'create_group', 'category' => 'group', 'description' => 'Can create new groups'],
            ['name' => 'Edit Group', 'slug' => 'edit_group', 'category' => 'group', 'description' => 'Can edit group settings'],
            ['name' => 'Delete Group', 'slug' => 'delete_group', 'category' => 'group', 'description' => 'Can delete groups'],
            ['name' => 'Assign Users to Groups', 'slug' => 'assign_group', 'category' => 'group', 'description' => 'Can assign users to groups'],
            ['name' => 'Join Group', 'slug' => 'join_group', 'category' => 'group', 'description' => 'Can join groups (when unlocked)'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
