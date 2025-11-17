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
        $regular = Role::where('slug', 'regular')->first();
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
            $calendar->users()->attach($user->id, [
                'role_id' => $owner->id,
                'joined_at' => now()->subDays(rand(30, 90)),
            ]);
        }

        // Team Calendar assignments
        $teamCalendar = Calendar::where('name', 'Team Calendar')->first();
        $teamCalendar->users()->attach([
            $john->id => ['role_id' => $owner->id, 'joined_at' => now()->subDays(60)],
            $sarah->id => ['role_id' => $admin->id, 'joined_at' => now()->subDays(50)],
            $mike->id => ['role_id' => $regular->id, 'joined_at' => now()->subDays(40)],
            $alex->id => ['role_id' => $regular->id, 'joined_at' => now()->subDays(30)],
        ]);

        // Family Events assignments
        $familyCalendar = Calendar::where('name', 'Family Events')->first();
        $familyCalendar->users()->attach([
            $sarah->id => ['role_id' => $owner->id, 'joined_at' => now()->subDays(100)],
            $emily->id => ['role_id' => $regular->id, 'joined_at' => now()->subDays(80)],
            $alex->id => ['role_id' => $regular->id, 'joined_at' => now()->subDays(70)],
        ]);

        // IT Factory Events assignments
        $itFactoryCalendar = Calendar::where('name', 'IT Factory Events')->first();
        $itFactoryCalendar->users()->attach([
            $mike->id => ['role_id' => $owner->id, 'joined_at' => now()->subDays(120)],
            $john->id => ['role_id' => $admin->id, 'joined_at' => now()->subDays(110)],
            $sarah->id => ['role_id' => $regular->id, 'joined_at' => now()->subDays(100)],
            $emily->id => ['role_id' => $guest->id, 'joined_at' => now()->subDays(90)],
        ]);
    }
}
