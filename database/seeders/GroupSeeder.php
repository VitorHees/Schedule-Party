<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $teamCalendar = Calendar::where('name', 'Team Calendar')->first();
        $familyCalendar = Calendar::where('name', 'Family Events')->first();
        $itFactoryCalendar = Calendar::where('name', 'IT Factory Events')->first();

        // Team Calendar groups
        $workGroup = Group::create([
            'calendar_id' => $teamCalendar->id,
            'name' => 'Work',
            'color' => '#3B82F6', // Blue
        ]);

        $socialGroup = Group::create([
            'calendar_id' => $teamCalendar->id,
            'name' => 'Social',
            'color' => '#10B981', // Green
        ]);

        $urgentGroup = Group::create([
            'calendar_id' => $teamCalendar->id,
            'name' => 'Urgent',
            'color' => '#EF4444', // Red
        ]);

        // Family Calendar groups
        $familyGroup = Group::create([
            'calendar_id' => $familyCalendar->id,
            'name' => 'Family',
            'color' => '#F59E0B', // Orange
        ]);

        $kidsGroup = Group::create([
            'calendar_id' => $familyCalendar->id,
            'name' => 'Kids Activities',
            'color' => '#8B5CF6', // Purple
        ]);

        // IT Factory groups
        $lecturesGroup = Group::create([
            'calendar_id' => $itFactoryCalendar->id,
            'name' => 'Lectures',
            'color' => '#14B8A6', // Teal
        ]);

        $examsGroup = Group::create([
            'calendar_id' => $itFactoryCalendar->id,
            'name' => 'Exams',
            'color' => '#DC2626', // Dark Red
        ]);

        // Assign users to groups
        $john = User::where('email', 'john@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $mike = User::where('email', 'mike@example.com')->first();
        $emily = User::where('email', 'emily@example.com')->first();
        $alex = User::where('email', 'alex@example.com')->first();

        // Team Calendar - Work group
        $workGroup->users()->attach([
            $john->id => ['assigned_at' => now()->subDays(60)],
            $sarah->id => ['assigned_at' => now()->subDays(50)],
            $mike->id => ['assigned_at' => now()->subDays(40)],
        ]);

        // Team Calendar - Social group
        $socialGroup->users()->attach([
            $john->id => ['assigned_at' => now()->subDays(60)],
            $sarah->id => ['assigned_at' => now()->subDays(50)],
            $alex->id => ['assigned_at' => now()->subDays(30)],
        ]);

        // Family Calendar - Family group
        $familyGroup->users()->attach([
            $sarah->id => ['assigned_at' => now()->subDays(100)],
            $emily->id => ['assigned_at' => now()->subDays(80)],
            $alex->id => ['assigned_at' => now()->subDays(70)],
        ]);

        // IT Factory - Lectures group
        $lecturesGroup->users()->attach([
            $mike->id => ['assigned_at' => now()->subDays(120)],
            $john->id => ['assigned_at' => now()->subDays(110)],
            $sarah->id => ['assigned_at' => now()->subDays(100)],
        ]);
    }
}
