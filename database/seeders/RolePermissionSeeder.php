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
        // UPDATED: Look for 'member' instead of 'regular'
        $member = Role::where('slug', 'member')->first();
        $admin = Role::where('slug', 'admin')->first();
        $owner = Role::where('slug', 'owner')->first();

        // Guest: Read-only (can only view)
        if ($guest) {
            $guest->permissions()->attach(
                Permission::whereIn('slug', [
                    'view_event',
                ])->pluck('id')
            );
        }

        // Member (formerly Regular User): Can view, comment, vote, opt-in, join groups
        if ($member) {
            $member->permissions()->attach(
                Permission::whereIn('slug', [
                    'view_event',
                    'create_comment',
                    'edit_own_comment',
                    'participate_vote',
                    'opt_in_event',
                    'join_group',
                ])->pluck('id')
            );
        }

        // Admin: Everything except delete calendar and assign roles
        if ($admin) {
            $admin->permissions()->attach(
                Permission::whereNotIn('slug', [
                    'delete_calendar',
                ])->pluck('id')
            );
        }

        // Owner: Full access to everything
        if ($owner) {
            $owner->permissions()->attach(
                Permission::all()->pluck('id')
            );
        }
    }
}
