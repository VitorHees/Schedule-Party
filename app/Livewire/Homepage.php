<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Homepage extends Component
{
    /**
     * Render the homepage component
     *
     * This is currently static but can be made dynamic
     * by adding properties and methods to fetch data
     */
    #[Layout('components.layouts.guest')]
    public function render()
    {
        // In the future, you can add dynamic data here:
        // $upcomingEvents = Event::upcoming()->limit(3)->get();
        // $featuredCalendars = Calendar::featured()->get();

        return view('livewire.homepage');
    }
}
