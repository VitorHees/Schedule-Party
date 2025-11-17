<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    public function run(): void
    {
        $teamCalendar = Calendar::where('name', 'Team Calendar')->first();
        $familyCalendar = Calendar::where('name', 'Family Events')->first();

        $john = User::where('email', 'john@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();

        $regular = Role::where('slug', 'regular')->first();
        $guest = Role::where('slug', 'guest')->first();

        // Active invitation link for Team Calendar
        Invitation::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $john->id,
            'invite_type' => 'link',
            'role_id' => $regular->id,
            'click_count' => 5,
            'last_clicked_at' => now()->subHours(3),
            'expires_at' => now()->addDays(7),
        ]);

        // Email invitation for Team Calendar
        Invitation::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $john->id,
            'invite_type' => 'email',
            'email' => 'newuser@example.com',
            'role_id' => $regular->id,
            'click_count' => 0,
            'expires_at' => now()->addDays(3),
        ]);

        // Guest invitation for Family Calendar
        Invitation::create([
            'calendar_id' => $familyCalendar->id,
            'created_by' => $sarah->id,
            'invite_type' => 'link',
            'role_id' => $guest->id,
            'click_count' => 2,
            'last_clicked_at' => now()->subDays(1),
            'expires_at' => now()->addDays(14),
        ]);

        // Expired invitation (for testing)
        Invitation::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $john->id,
            'invite_type' => 'link',
            'role_id' => $regular->id,
            'click_count' => 10,
            'last_clicked_at' => now()->subDays(5),
            'expires_at' => now()->subDays(2), // Expired 2 days ago
        ]);

        // Used invitation (for testing)
        Invitation::create([
            'calendar_id' => $familyCalendar->id,
            'created_by' => $sarah->id,
            'invite_type' => 'email',
            'email' => 'used@example.com',
            'role_id' => $regular->id,
            'click_count' => 1,
            'last_clicked_at' => now()->subDays(10),
            'expires_at' => now()->addDays(5),
            'used_at' => now()->subDays(10), // Already used
        ]);
    }
}
