<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guest = Role::where('slug', 'guest')->first();
        $member = Role::where('slug', 'member')->first();
        $admin = Role::where('slug', 'admin')->first();
        $owner = Role::where('slug', 'owner')->first();

        // 1. GUEST: View Only
        if ($guest) {
            $guest->permissions()->sync(
                Permission::whereIn('slug', [
                    'view_events',
                    'view_comments'
                ])->pluck('id')
            );
        }

        // 2. MEMBER: Updated defaults
        // Removed: create_events
        // Added: join_labels
        if ($member) {
            $member->permissions()->sync(
                Permission::whereIn('slug', [
                    'view_events',
                    'view_comments',
                    'rsvp_event',      // "attend events"
                    'vote_poll',       // "vote in polls"
                    'create_comment',  // "post comments"
                    'join_labels',     // "join public labels"
                ])->pluck('id')
            );
        }

        // 3. ADMIN: All permissions EXCEPT 'manage_role_permissions' and 'import_personal_calendar'
        if ($admin) {
            $admin->permissions()->sync(
                Permission::whereNotIn('slug', ['manage_role_permissions', 'import_personal_calendar'])->pluck('id')
            );
        }

        // 4. OWNER: Still has everything
        if ($owner) {
            $owner->permissions()->sync(Permission::all()->pluck('id'));
        }
    }
}
