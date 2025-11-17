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
        $mealVote = Vote::create([
            'event_id' => $lunch->id,
            'title' => 'What should we order for lunch?',
            'allow_multiple' => false,
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
        $pizza->responses()->create(['user_id' => $john->id]);
        $pasta->responses()->create(['user_id' => $sarah->id]);
        $pasta->responses()->create(['user_id' => $alex->id]);
        $salad->responses()->create(['user_id' => $mike->id]);

        // Vote for Birthday Party - Gift Ideas
        $giftVote = Vote::create([
            'event_id' => $birthday->id,
            'title' => 'What gift should we get for Emily?',
            'allow_multiple' => true, // Allow voting for multiple options
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
        $book->responses()->create(['user_id' => $sarah->id]);
        $voucher->responses()->create(['user_id' => $sarah->id]);
        $gadget->responses()->create(['user_id' => $alex->id]);
    }
}
