<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CalendarUser;
use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AcceptInvitation extends Component
{
    public function mount($token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (!$invitation->isValid()) {
            abort(404, 'Invitation expired or invalid.');
        }

        $calendar = $invitation->calendar;

        // 1. Logged In User Flow
        if (Auth::check()) {
            $user = Auth::user();

            // Check if already a member
            if (!$calendar->users->contains($user->id)) {
                // Determine Role: Use invitation role if set, otherwise 'regular'
                $roleId = $invitation->role_id ?? Role::where('slug', 'regular')->first()->id;

                $calendar->users()->attach($user->id, [
                    'role_id' => $roleId,
                    'joined_at' => now(),
                ]);
            }

            // Increment click count
            $invitation->incrementClickCount();

            return redirect()->route('calendar.shared', $calendar);
        }

        // 2. Guest Flow
        // Create a unique guest token for this session
        $guestToken = Str::uuid()->toString();
        $guestRole = Role::where('slug', 'guest')->first();

        // If 'guest' role doesn't exist, fallback to 'regular' or handle error
        $roleId = $guestRole ? $guestRole->id : Role::where('slug', 'regular')->first()->id;

        CalendarUser::create([
            'calendar_id' => $calendar->id,
            'user_id' => null, // Guests have no user_id
            'role_id' => $roleId,
            'guest_token' => $guestToken,
            'joined_at' => now(),
        ]);

        $invitation->incrementClickCount();

        // Redirect with a long-lived cookie to identify this guest
        return redirect()->route('calendar.shared', $calendar)
            ->withCookie(cookie()->forever('guest_access_' . $calendar->id, $guestToken));
    }

    public function render()
    {
        // This view is technically required by Livewire but won't be seen
        // because mount() redirects immediately.
        return <<<'HTML'
        <div>
            </div>
        HTML;
    }
}
