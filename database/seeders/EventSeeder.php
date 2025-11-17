<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $teamCalendar = Calendar::where('name', 'Team Calendar')->first();
        $familyCalendar = Calendar::where('name', 'Family Events')->first();
        $itFactoryCalendar = Calendar::where('name', 'IT Factory Events')->first();

        $john = User::where('email', 'john@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $mike = User::where('email', 'mike@example.com')->first();

        $workGroup = Group::where('name', 'Work')->first();
        $socialGroup = Group::where('name', 'Social')->first();
        $urgentGroup = Group::where('name', 'Urgent')->first();
        $familyGroup = Group::where('name', 'Family')->first();
        $lecturesGroup = Group::where('name', 'Lectures')->first();
        $examsGroup = Group::where('name', 'Exams')->first();

        // Team Calendar Events
        $meeting = Event::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $john->id,
            'name' => 'Weekly Team Meeting',
            'description' => 'Discuss project progress, blockers, and upcoming deadlines.',
            'start_date' => now()->addDays(2)->setTime(10, 0),
            'end_date' => now()->addDays(2)->setTime(11, 0),
            'is_all_day' => false,
            'location' => 'Conference Room A',
            'repeat_frequency' => 'weekly',
            'comments_enabled' => true,
            'opt_in_enabled' => true,
        ]);
        $meeting->groups()->attach($workGroup->id);
        $meeting->participants()->attach($john->id, ['status' => 'opted_in']);
        $meeting->participants()->attach($sarah->id, ['status' => 'opted_in']);

        $lunch = Event::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $sarah->id,
            'name' => 'Company Lunch',
            'description' => 'Team building lunch at the new Italian restaurant downtown.',
            'start_date' => now()->addDays(5)->setTime(12, 0),
            'end_date' => now()->addDays(5)->setTime(14, 0),
            'is_all_day' => false,
            'location' => 'La Bella Vista Restaurant',
            'url' => 'https://labellavista.be',
            'repeat_frequency' => 'none',
            'comments_enabled' => true,
            'opt_in_enabled' => true,
        ]);
        $lunch->groups()->attach([$workGroup->id, $socialGroup->id]);

        $deadline = Event::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $john->id,
            'name' => 'Project Deadline',
            'description' => 'Final submission for Q4 deliverables.',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(10),
            'is_all_day' => true,
            'repeat_frequency' => 'none',
            'comments_enabled' => true,
            'opt_in_enabled' => false,
        ]);
        $deadline->groups()->attach([$workGroup->id, $urgentGroup->id]);

        // Family Calendar Events
        $birthday = Event::create([
            'calendar_id' => $familyCalendar->id,
            'created_by' => $sarah->id,
            'name' => "Emily's Birthday Party",
            'description' => 'Surprise birthday celebration at home!',
            'start_date' => now()->addDays(15)->setTime(18, 0),
            'end_date' => now()->addDays(15)->setTime(22, 0),
            'is_all_day' => false,
            'location' => 'Home',
            'repeat_frequency' => 'yearly',
            'comments_enabled' => true,
            'opt_in_enabled' => true,
        ]);
        $birthday->groups()->attach($familyGroup->id);

        // IT Factory Events
        $lecture = Event::create([
            'calendar_id' => $itFactoryCalendar->id,
            'created_by' => $mike->id,
            'name' => 'Laravel Advanced Concepts',
            'description' => 'Deep dive into Eloquent relationships, scopes, and query optimization.',
            'start_date' => now()->addDays(3)->setTime(9, 0),
            'end_date' => now()->addDays(3)->setTime(12, 0),
            'is_all_day' => false,
            'location' => 'Room 204',
            'repeat_frequency' => 'weekly',
            'repeat_end_date' => now()->addMonths(3),
            'comments_enabled' => true,
            'opt_in_enabled' => false,
        ]);
        $lecture->groups()->attach($lecturesGroup->id);

        $exam = Event::create([
            'calendar_id' => $itFactoryCalendar->id,
            'created_by' => $mike->id,
            'name' => 'Final Exam: Web Development',
            'description' => 'Comprehensive exam covering Laravel, Livewire, and Tailwind CSS.',
            'start_date' => now()->addDays(30)->setTime(14, 0),
            'end_date' => now()->addDays(30)->setTime(17, 0),
            'is_all_day' => false,
            'location' => 'Exam Hall B',
            'repeat_frequency' => 'none',
            'comments_enabled' => false,
            'opt_in_enabled' => false,
        ]);
        $exam->groups()->attach($examsGroup->id);

        $conference = Event::create([
            'calendar_id' => $teamCalendar->id,
            'created_by' => $john->id,
            'name' => 'Tech Conference 2025',
            'description' => 'Annual technology conference featuring talks on AI, cloud computing, and web development.',
            'start_date' => now()->addDays(45)->setTime(9, 0),
            'end_date' => now()->addDays(47)->setTime(18, 0),
            'is_all_day' => true,
            'location' => 'Brussels Expo',
            'url' => 'https://techconf2025.be',
            'max_distance_km' => 50,
            'comments_enabled' => true,
            'opt_in_enabled' => true,
        ]);
        $conference->groups()->attach($workGroup->id);
    }
}
