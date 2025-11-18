<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * Validation rules for the login form.
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'lowercase', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Handle the login form submission.
     */
    public function login(): void
    {
        // Validate input
        $validated = $this->validate();

        // Attempt authentication
        if (! Auth::attempt(
            ['email' => $validated['email'], 'password' => $validated['password']],
            $this->remember
        )) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Regenerate the session to prevent fixation
        Session::regenerate();

        // Redirect to intended URL or dashboard
        $this->redirectIntended(
            default: route('dashboard', absolute: false),
            navigate: true
        );
    }
}
