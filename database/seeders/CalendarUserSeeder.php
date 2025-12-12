<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarUserSeeder extends Seeder
{
    public function run(): void
    {
        $john = User::where('email', 'john@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $mike = User::where('email', 'mike@example.com')->first();
        $emily = User::where('email', 'emily@example.com')->first();
        $alex = User::where('email', 'alex@example.com')->first();

        $owner = Role::where('slug', 'owner')->first();
        $admin = Role::where('slug', 'admin')->first();
        // UPDATED: Use 'member' instead of 'regular'
        $member = Role::where('slug', 'member')->first();
        $guest = Role::where('slug', 'guest')->first();

        // Assign users to their personal calendars (all as owners)
        $personalCalendars = [
            ["John's Personal Calendar", $john],
            ["Sarah's Personal Calendar", $sarah],
            ["Mike's Personal Calendar", $mike],
            ["Emily's Personal Calendar", $emily],
            ["Alex's Personal Calendar", $alex],
        ];

        foreach ($personalCalendars as [$calendarName, $user]) {
            $calendar = Calendar::where('name', $calendarName)->first();
            if ($calendar && $user) {
                $calendar->users()->attach($user->id, [
                    'role_id' => $owner->id,
                    'joined_at' => now()->subDays(rand(30, 90)),
                ]);
            }
        }

        // Team Calendar assignments
        $teamCalendar = Calendar::where('name', 'Team Calendar')->first();
        if ($teamCalendar) {
            $teamCalendar->users()->attach([
                $john->id => ['role_id' => $owner->id, 'joined_at' => now()->subDays(60)],
                $sarah->id => ['role_id' => $admin->id, 'joined_at' => now()->subDays(50)],
                // UPDATED: Use $member->id
                $mike->id => ['role_id' => $member->id, 'joined_at' => now()->subDays(40)],
                $alex->id => ['role_id' => $member->id, 'joined_at' => now()->subDays(30)],
            ]);
        }

        // Family Events assignments
        $familyCalendar = Calendar::where('name', 'Family Events')->first();
        if ($familyCalendar) {
            $familyCalendar->users()->attach([
                $sarah->id => ['role_id' => $owner->id, 'joined_at' => now()->subDays(100)],
                // UPDATED: Use $member->id
                $emily->id => ['role_id' => $member->id, 'joined_at' => now()->subDays(80)],
                $alex->id => ['role_id' => $member->id, 'joined_at' => now()->subDays(70)],
            ]);
        }

        // IT Factory Events assignments
        $itFactoryCalendar = Calendar::where('name', 'IT Factory Events')->first();
        if ($itFactoryCalendar) {
            $itFactoryCalendar->users()->attach([
                $mike->id => ['role_id' => $owner->id, 'joined_at' => now()->subDays(120)],
                $john->id => ['role_id' => $admin->id, 'joined_at' => now()->subDays(110)],
                // UPDATED: Use $member->id
                $sarah->id => ['role_id' => $member->id, 'joined_at' => now()->subDays(100)],
                $emily->id => ['role_id' => $guest->id, 'joined_at' => now()->subDays(90)],
            ]);
        }
    }
}
