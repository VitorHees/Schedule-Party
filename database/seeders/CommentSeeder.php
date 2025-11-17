<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $meeting = Event::where('name', 'Weekly Team Meeting')->first();
        $lunch = Event::where('name', 'Company Lunch')->first();
        $conference = Event::where('name', 'Tech Conference 2025')->first();

        $john = User::where('email', 'john@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $mike = User::where('email', 'mike@example.com')->first();
        $alex = User::where('email', 'alex@example.com')->first();

        // Comments on Weekly Team Meeting
        Comment::create([
            'event_id' => $meeting->id,
            'user_id' => $sarah->id,
            'content' => 'Looking forward to this! @john_doe can you prepare the slides for the new feature demo?',
            'mentions' => [$john->id],
        ]);

        Comment::create([
            'event_id' => $meeting->id,
            'user_id' => $john->id,
            'content' => '@sarah_smith Sure thing! I\'ll have them ready by tomorrow morning.',
            'mentions' => [$sarah->id],
        ]);

        Comment::create([
            'event_id' => $meeting->id,
            'user_id' => $mike->id,
            'content' => 'Can we also discuss the database optimization issues?',
            'mentions' => [],
        ]);

        // Comments on Company Lunch
        Comment::create([
            'event_id' => $lunch->id,
            'user_id' => $john->id,
            'content' => 'I heard their carbonara is amazing! 🍝',
            'mentions' => [],
        ]);

        Comment::create([
            'event_id' => $lunch->id,
            'user_id' => $sarah->id,
            'content' => '@john_doe They also have great vegetarian options!',
            'mentions' => [$john->id],
        ]);

        Comment::create([
            'event_id' => $lunch->id,
            'user_id' => $alex->id,
            'content' => 'Count me in! What time should we leave the office?',
            'mentions' => [],
        ]);

        // Comments on Tech Conference
        Comment::create([
            'event_id' => $conference->id,
            'user_id' => $sarah->id,
            'content' => 'This looks incredible! Who\'s attending the AI workshop on day 2?',
            'mentions' => [],
        ]);

        Comment::create([
            'event_id' => $conference->id,
            'user_id' => $john->id,
            'content' => '@sarah_smith I am! Let\'s carpool together.',
            'mentions' => [$sarah->id],
        ]);
    }
}
