<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'description' => 'Read-only access to shared calendars. Cannot edit or vote.'
            ],
            [
                'name' => 'Regular User',
                'slug' => 'regular',
                'description' => 'Standard calendar member with limited permissions.'
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Can manage calendar settings, users, and has full edit permissions.'
            ],
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Full control over the calendar including deletion and ownership transfer.'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
