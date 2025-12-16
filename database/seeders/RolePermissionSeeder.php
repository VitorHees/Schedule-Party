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

        // 1. GUEST: View Only (Events + Comments)
        if ($guest) {
            $guest->permissions()->sync(
                Permission::whereIn('slug', [
                    'view_events',
                    'view_comments' // Guests can read chat by default
                ])->pluck('id')
            );
        }

        // 2. MEMBER: Basic Interaction
        if ($member) {
            $member->permissions()->sync(
                Permission::whereIn('slug', [
                    'view_events',
                    'view_comments', // <--- Added
                    'create_events',
                    'rsvp_event',
                    'create_comment',
                    'vote_poll',
                ])->pluck('id')
            );
        }

        // 3. ADMIN & OWNER: All
        if ($admin) $admin->permissions()->sync(Permission::all()->pluck('id'));
        if ($owner) $owner->permissions()->sync(Permission::all()->pluck('id'));
    }
}
