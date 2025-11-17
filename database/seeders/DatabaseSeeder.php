<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Supporting data (no dependencies)
            CountrySeeder::class,
            ZipcodeSeeder::class,
            GenderSeeder::class,

            // Roles & Permissions
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // Users (needs countries, zipcodes, genders)
            UserSeeder::class,

            // Calendars (needs users as owners)
            CalendarSeeder::class,

            // Calendar-User assignments
            CalendarUserSeeder::class,

            // Groups (needs calendars)
            GroupSeeder::class,

            // Events (needs calendars, users, groups)
            EventSeeder::class,

            // Interactions (needs events, users)
            CommentSeeder::class,
            VoteSeeder::class,

            // Invitations (needs calendars, users, roles)
            InvitationSeeder::class,

            // Permission overrides (needs calendar_user, permissions)
            UserPermissionOverrideSeeder::class,
        ]);
    }
}
