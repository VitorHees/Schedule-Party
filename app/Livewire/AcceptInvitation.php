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
                $roleId = $invitation->role_id ?? Role::where('slug', 'member')->first()->id;

                $calendar->users()->attach($user->id, [
                    'role_id' => $roleId,
                    'joined_at' => now(),
                ]);

                // Increment usage count only on successful join
                $invitation->incrementUsageCount();

                // Log Activity
                $calendar->logActivity('joined', 'User', $user->id, $user);
            }

            // Always increment click count for analytics
            $invitation->incrementClickCount();

            // If it was an email invite, mark as fully used
            if ($invitation->invite_type === 'email') {
                $invitation->markAsUsed();
            }

            return redirect()->route('calendar.shared', $calendar);
        }

        // 2. Guest Flow
        // Create a unique guest token for this session
        $guestToken = Str::uuid()->toString();
        $guestRole = Role::where('slug', 'guest')->first();
        $roleId = $guestRole ? $guestRole->id : Role::where('slug', 'member')->first()->id;

        CalendarUser::create([
            'calendar_id' => $calendar->id,
            'user_id' => null, // Guests have no user_id
            'role_id' => $roleId,
            'guest_token' => $guestToken,
            'joined_at' => now(),
        ]);

        // Increment counts
        $invitation->incrementClickCount();
        $invitation->incrementUsageCount();

        // Log Activity for Guest (No user object, using system/null)
        $calendar->logActivity('joined_guest', 'Calendar', $calendar->id, null, ['guest_id' => $guestToken]);

        // Redirect with a long-lived cookie to identify this guest
        return redirect()->route('calendar.shared', $calendar)
            ->withCookie(cookie()->forever('guest_access_' . $calendar->id, $guestToken));
    }

    public function render()
    {
        return <<<'HTML'
        <div></div>
        HTML;
    }
}
