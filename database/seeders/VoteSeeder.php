<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteOption;
use Illuminate\Database\Seeder;

class VoteSeeder extends Seeder
{
    public function run(): void
    {
        $lunch = Event::where('name', 'Company Lunch')->first();
        $birthday = Event::where('name', "Emily's Birthday Party")->first();

        $john = User::where('email', 'john@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();
        $mike = User::where('email', 'mike@example.com')->first();
        $alex = User::where('email', 'alex@example.com')->first();

        // Vote for Company Lunch - Meal Selection
        if ($lunch) {
            $mealVote = Vote::create([
                'event_id' => $lunch->id,
                'title' => 'What should we order for lunch?',
                'max_allowed_selections' => 1, // Was allow_multiple = false
                'is_public' => true,
            ]);

            $pizza = VoteOption::create([
                'vote_id' => $mealVote->id,
                'option_text' => 'Pizza Margherita',
            ]);

            $pasta = VoteOption::create([
                'vote_id' => $mealVote->id,
                'option_text' => 'Pasta Carbonara',
            ]);

            $salad = VoteOption::create([
                'vote_id' => $mealVote->id,
                'option_text' => 'Caesar Salad',
            ]);

            $risotto = VoteOption::create([
                'vote_id' => $mealVote->id,
                'option_text' => 'Mushroom Risotto',
            ]);

            // Cast some votes
            if ($john) $pizza->responses()->create(['user_id' => $john->id]);
            if ($sarah) $pasta->responses()->create(['user_id' => $sarah->id]);
            if ($alex) $pasta->responses()->create(['user_id' => $alex->id]);
            if ($mike) $salad->responses()->create(['user_id' => $mike->id]);
        }

        // Vote for Birthday Party - Gift Ideas
        if ($birthday) {
            $giftVote = Vote::create([
                'event_id' => $birthday->id,
                'title' => 'What gift should we get for Emily?',
                'max_allowed_selections' => 3, // Was allow_multiple = true. Allowing up to 3 choices.
                'is_public' => true,
            ]);

            $book = VoteOption::create([
                'vote_id' => $giftVote->id,
                'option_text' => 'Book Collection',
            ]);

            $gadget = VoteOption::create([
                'vote_id' => $giftVote->id,
                'option_text' => 'Smart Watch',
            ]);

            $voucher = VoteOption::create([
                'vote_id' => $giftVote->id,
                'option_text' => 'Shopping Voucher',
            ]);

            // Cast multiple votes
            if ($sarah) {
                $book->responses()->create(['user_id' => $sarah->id]);
                $voucher->responses()->create(['user_id' => $sarah->id]);
            }
            if ($alex) {
                $gadget->responses()->create(['user_id' => $alex->id]);
            }
        }
    }
}
