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
        $regular = Role::where('slug', 'regular')->first();
        $admin = Role::where('slug', 'admin')->first();
        $owner = Role::where('slug', 'owner')->first();

        // Guest: Read-only (can only view)
        $guest->permissions()->attach(
            Permission::whereIn('slug', [
                'view_event',
            ])->pluck('id')
        );

        // Regular User: Can view, comment, vote, opt-in, join groups
        $regular->permissions()->attach(
            Permission::whereIn('slug', [
                'view_event',
                'create_comment',
                'edit_own_comment',
                'participate_vote',
                'opt_in_event',
                'join_group',
            ])->pluck('id')
        );

        // Admin: Everything except delete calendar and assign roles
        $admin->permissions()->attach(
            Permission::whereNotIn('slug', [
                'delete_calendar',
            ])->pluck('id')
        );

        // Owner: Full access to everything
        $owner->permissions()->attach(
            Permission::all()->pluck('id')
        );
    }
}
