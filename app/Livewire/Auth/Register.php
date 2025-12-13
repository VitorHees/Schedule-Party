<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'], // username in DB
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // attributes for your custom users table
        $attributes = [
            'username'        => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'is_active'       => true,
            'birth_date'      => null, // Set to null for new accounts
            'is_email_verified' => false,
        ];

        $user = User::create($attributes);

        // Fire the Registered event so Laravel sends the verification email
        event(new Registered($user));

        // Log them in
        Auth::login($user);

        // Send them to the "verify email" page instead of dashboard
        $this->redirect(route('verification.notice', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')->title('Register');
    }
}
