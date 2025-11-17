<?php

namespace Database\Seeders;

use App\Models\CalendarUser;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserPermissionOverride;
use Illuminate\Database\Seeder;

class UserPermissionOverrideSeeder extends Seeder
{
    public function run(): void
    {
        // Example: Give Mike (regular user) the ability to create events in Team Calendar
        // even though regular users normally can't

        $mike = User::where('email', 'mike@example.com')->first();
        $createEventPermission = Permission::where('slug', 'create_event')->first();
        $createVotePermission = Permission::where('slug', 'create_vote')->first();

        // Find Mike's CalendarUser record for Team Calendar
        $mikeInTeamCalendar = CalendarUser::where('user_id', $mike->id)
            ->whereHas('calendar', fn($q) => $q->where('name', 'Team Calendar'))
            ->first();

        if ($mikeInTeamCalendar) {
            // Grant Mike permission to create events (override his regular role)
            UserPermissionOverride::create([
                'calendar_user_id' => $mikeInTeamCalendar->id,
                'permission_id' => $createEventPermission->id,
                'granted' => true,
            ]);

            // Grant Mike permission to create votes
            UserPermissionOverride::create([
                'calendar_user_id' => $mikeInTeamCalendar->id,
                'permission_id' => $createVotePermission->id,
                'granted' => true,
            ]);

            $this->command->info('✅ Granted Mike special permissions in Team Calendar');
        }

        // Example: Deny Alex the ability to join groups in Team Calendar
        $alex = User::where('email', 'alex@example.com')->first();
        $joinGroupPermission = Permission::where('slug', 'join_group')->first();

        $alexInTeamCalendar = CalendarUser::where('user_id', $alex->id)
            ->whereHas('calendar', fn($q) => $q->where('name', 'Team Calendar'))
            ->first();

        if ($alexInTeamCalendar) {
            UserPermissionOverride::create([
                'calendar_user_id' => $alexInTeamCalendar->id,
                'permission_id' => $joinGroupPermission->id,
                'granted' => false, // Deny permission
            ]);

            $this->command->info('✅ Denied Alex the ability to join groups in Team Calendar');
        }
    }
}
