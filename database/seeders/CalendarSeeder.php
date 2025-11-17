<?php

namespace Database\Seeders;

use App\Models\Calendar;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        $calendars = [
            [
                'name' => "John's Personal Calendar",
                'type' => 'personal',
                'groups_locked' => false,
            ],
            [
                'name' => "Sarah's Personal Calendar",
                'type' => 'personal',
                'groups_locked' => false,
            ],
            [
                'name' => "Mike's Personal Calendar",
                'type' => 'personal',
                'groups_locked' => false,
            ],
            [
                'name' => "Emily's Personal Calendar",
                'type' => 'personal',
                'groups_locked' => false,
            ],
            [
                'name' => "Alex's Personal Calendar",
                'type' => 'personal',
                'groups_locked' => false,
            ],
            [
                'name' => 'Team Calendar',
                'type' => 'collaborative',
                'groups_locked' => false,
            ],
            [
                'name' => 'Family Events',
                'type' => 'collaborative',
                'groups_locked' => true, // Admins only can assign groups
            ],
            [
                'name' => 'IT Factory Events',
                'type' => 'collaborative',
                'groups_locked' => false,
            ],
        ];

        foreach ($calendars as $calendar) {
            Calendar::create($calendar);
        }
    }
}
