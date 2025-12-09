<?php

namespace App\Livewire;

use App\Models\Calendar;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class CreateCalendar extends Component
{
    public $isOpen = false;

    #[Validate('required|min:3|max:50')]
    public $name = '';

    #[On('open-create-calendar-modal')]
    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset('name');
        $this->resetValidation();
    }

    public function create()
    {
        $this->validate();

        // 1. Create the Calendar
        $calendar = Calendar::create([
            'name' => $this->name,
            'type' => 'collaborative',
            'groups_locked' => false,
        ]);

        // 2. Get Owner Role
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();

        // 3. Attach User as Owner
        $calendar->users()->attach(Auth::id(), [
            'role_id' => $ownerRole->id,
            'joined_at' => now(),
        ]);

        $this->closeModal();

        // 4. Redirect to the new calendar
        return redirect()->route('calendar.shared', $calendar);
    }

    public function render()
    {
        return view('livewire.create-calendar');
    }
}
