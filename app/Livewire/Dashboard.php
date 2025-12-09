<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Calendar;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\Role;

class Dashboard extends Component
{
    public function acceptInvitation($invitationId)
    {
        $invitation = Invitation::where('id', $invitationId)
            ->where('email', Auth::user()->email)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return; // Or show error message
        }

        $calendar = $invitation->calendar;
        $user = Auth::user();

        // Add user to calendar if not already there
        if (!$calendar->users->contains($user->id)) {
            $calendar->users()->attach($user->id, [
                'role_id' => $invitation->role_id,
                'joined_at' => now(),
            ]);
        }

        $invitation->markAsUsed();

        $this->dispatch('action-message', message: 'Invitation accepted!');
    }

    public function rejectInvitation($invitationId)
    {
        $invitation = Invitation::where('id', $invitationId)
            ->where('email', Auth::user()->email)
            ->first();

        if ($invitation) {
            // We can delete it to "ignore" it
            $invitation->delete();
            $this->dispatch('action-message', message: 'Invitation ignored.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        // 1. Personal Calendar Stats
        $personalCalendar = $user->calendars()->where('type', 'personal')->first();
        $todaysEventsCount = 0;
        $nextPersonalEvent = null;

        if ($personalCalendar) {
            $todaysEventsCount = $personalCalendar->events()
                ->whereDate('start_date', now())
                ->count();

            $nextPersonalEvent = $personalCalendar->events()
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->first();
        }

        // 2. Collaborative Calendars
        $collaborativeCalendars = $user->calendars()
            ->where('type', 'collaborative')
            ->withCount('users')
            ->take(5)
            ->get();

        // 3. Upcoming Events (from ALL calendars user is part of)
        $calendarIds = $user->calendars()->pluck('calendars.id');

        $upcomingEvents = Event::whereIn('calendar_id', $calendarIds)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(3)
            ->with('calendar')
            ->get();

        // 4. Pending Invitations
        $invitations = Invitation::where('email', $user->email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->with(['calendar', 'creator', 'role'])
            ->get();

        return view('dashboard', [
            'todaysEventsCount' => $todaysEventsCount,
            'nextPersonalEvent' => $nextPersonalEvent,
            'collaborativeCalendars' => $collaborativeCalendars,
            'upcomingEvents' => $upcomingEvents,
            'invitations' => $invitations,
        ])
            ->layout('components.layouts.app', ['title' => __('Dashboard')]);
    }
}
