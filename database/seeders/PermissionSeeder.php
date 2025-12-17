<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // --- EVENTS ---
            ['name' => 'View Events', 'slug' => 'view_events', 'category' => 'event', 'description' => 'Can view events on the calendar'],
            ['name' => 'Create Events', 'slug' => 'create_events', 'category' => 'event', 'description' => 'Can create new events'],
            ['name' => 'Edit Any Event', 'slug' => 'edit_any_event', 'category' => 'event', 'description' => 'Can edit events created by others'],
            ['name' => 'Delete Any Event', 'slug' => 'delete_any_event', 'category' => 'event', 'description' => 'Can delete events created by others'],
            ['name' => 'Add Images', 'slug' => 'add_images', 'category' => 'event', 'description' => 'Can upload images to events'],

            // --- LABELS ---
            ['name' => 'Create Labels', 'slug' => 'create_labels', 'category' => 'label', 'description' => 'Can create standard labels'],
            ['name' => 'Create Selectable Labels', 'slug' => 'create_selectable_labels', 'category' => 'label', 'description' => 'Can create filterable labels'],
            ['name' => 'Add Labels to Events', 'slug' => 'add_labels', 'category' => 'label', 'description' => 'Can attach labels to events'],
            ['name' => 'Assign Labels to Users', 'slug' => 'assign_labels', 'category' => 'label', 'description' => 'Can manage user labels'],
            ['name' => 'Delete Any Label', 'slug' => 'delete_any_label', 'category' => 'label', 'description' => 'Can delete any label'],
            ['name' => 'Join Public Labels', 'slug' => 'join_labels', 'category' => 'label', 'description' => 'Can join public selectable labels'],
            ['name' => 'Join Private Labels', 'slug' => 'join_private_labels', 'category' => 'label', 'description' => 'Can join locked labels'],

            // --- INTERACTION ---
            ['name' => 'View Comments', 'slug' => 'view_comments', 'category' => 'interaction', 'description' => 'Can view the comment section'],
            ['name' => 'Attend Events', 'slug' => 'rsvp_event', 'category' => 'interaction', 'description' => 'Can change attendance status'],
            ['name' => 'Post Comments', 'slug' => 'create_comment', 'category' => 'interaction', 'description' => 'Can post comments on events'],
            ['name' => 'Delete Any Comment', 'slug' => 'delete_any_comment', 'category' => 'interaction', 'description' => 'Can delete abusive comments'],
            ['name' => 'Create Polls', 'slug' => 'create_poll', 'category' => 'interaction', 'description' => 'Can create polls'],
            ['name' => 'Vote in Polls', 'slug' => 'vote_poll', 'category' => 'interaction', 'description' => 'Can vote in polls'],

            // --- MANAGEMENT ---
            ['name' => 'Invite Users', 'slug' => 'invite_users', 'category' => 'management', 'description' => 'Can invite new members'],
            ['name' => 'View Active Links', 'slug' => 'view_active_links', 'category' => 'management', 'description' => 'Can see active invite links'],
            ['name' => 'Manage Invites', 'slug' => 'manage_invites', 'category' => 'management', 'description' => 'Can revoke invitations'],
            ['name' => 'Kick Users', 'slug' => 'kick_users', 'category' => 'management', 'description' => 'Can remove members'],
            ['name' => 'View Activity Logs', 'slug' => 'view_logs', 'category' => 'management', 'description' => 'Can view history'],
            ['name' => 'Import Personal Calendar', 'slug' => 'import_personal_calendar', 'category' => 'management', 'description' => 'Can import events from personal calendar'],

            // --- PERMISSION MANAGEMENT (SPLIT) ---
            ['name' => 'Manage Role Permissions', 'slug' => 'manage_role_permissions', 'category' => 'management', 'description' => 'Can configure global role access'],
            ['name' => 'Manage Label Permissions', 'slug' => 'manage_label_permissions', 'category' => 'management', 'description' => 'Can configure permissions per label'],
            ['name' => 'Manage User Permissions', 'slug' => 'manage_user_permissions', 'category' => 'management', 'description' => 'Can override permissions for specific users'],
        ];

        // Prepare list of slugs to keep
        $slugs = array_map(fn($p) => $p['slug'], $permissions);

        // Remove permissions that are not in the list (e.g. the old 'manage_permissions')
        Permission::whereNotIn('slug', $slugs)->delete();

        // Create or update the new permissions
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
