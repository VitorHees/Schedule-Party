<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache if necessary or just run updateOrCreate

        $permissions = [
            // --- EVENTS ---
            ['name' => 'View Events', 'slug' => 'view_events', 'category' => 'event', 'description' => 'Can view events'],
            // Implies editing/deleting OWN events
            ['name' => 'Create Events', 'slug' => 'create_events', 'category' => 'event', 'description' => 'Can create new events (and edit their own)'],
            // "Any" implies administrative override
            ['name' => 'Edit Any Event', 'slug' => 'edit_any_event', 'category' => 'event', 'description' => 'Can edit any event in the calendar'],
            ['name' => 'Delete Any Event', 'slug' => 'delete_any_event', 'category' => 'event', 'description' => 'Can delete any event in the calendar'],

            // --- LABELS (Groups) ---
            // Implies editing/deleting OWN labels
            ['name' => 'Create Labels', 'slug' => 'create_labels', 'category' => 'label', 'description' => 'Can create new labels'],
            ['name' => 'Assign Labels', 'slug' => 'assign_labels', 'category' => 'label', 'description' => 'Can attach labels to events'],
            ['name' => 'Delete Any Label', 'slug' => 'delete_any_label', 'category' => 'label', 'description' => 'Can delete any label'],
            // NEW: Allows bypassing the "Locked" status on private groups
            ['name' => 'Join Locked Labels', 'slug' => 'join_private_labels', 'category' => 'label', 'description' => 'Can join private/locked labels'],

            // --- INTERACTION ---
            ['name' => 'RSVP / Opt-in', 'slug' => 'opt_in_event', 'category' => 'interaction', 'description' => 'Can mark themselves as attending'],
            ['name' => 'Post Comments', 'slug' => 'create_comment', 'category' => 'interaction', 'description' => 'Can comment on events'],
            ['name' => 'Delete Any Comment', 'slug' => 'delete_any_comment', 'category' => 'interaction', 'description' => 'Can delete abusive comments'],
            ['name' => 'Create Polls', 'slug' => 'create_vote', 'category' => 'interaction', 'description' => 'Can attach polls to events'],
            ['name' => 'Vote', 'slug' => 'vote_polls', 'category' => 'interaction', 'description' => 'Can participate in polls'],

            // --- MANAGEMENT ---
            ['name' => 'Invite People', 'slug' => 'invite_users', 'category' => 'management', 'description' => 'Can generate invite links'],
            ['name' => 'Manage Invites', 'slug' => 'manage_invites', 'category' => 'management', 'description' => 'Can delete/revoke active invites'],
            ['name' => 'Kick Users', 'slug' => 'kick_users', 'category' => 'management', 'description' => 'Can remove members from the calendar'],
            ['name' => 'Manage Permissions', 'slug' => 'manage_permissions', 'category' => 'management', 'description' => 'Can change user roles'],
            ['name' => 'View Activity Logs', 'slug' => 'view_logs', 'category' => 'management', 'description' => 'Can view history of changes'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
