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
            return;
        }

        $calendar = $invitation->calendar;
        $user = Auth::user();

        if (!$calendar->users->contains($user->id)) {
            $calendar->users()->attach($user->id, [
                'role_id' => $invitation->role_id,
                'joined_at' => now(),
            ]);
        }

        $invitation->markAsUsed();

        // UPDATED: Redirect to the calendar.
        // This refreshes the layout (sidebar) and takes the user to their new calendar.
        return redirect()->route('calendar.shared', $calendar);
    }

    public function rejectInvitation($invitationId)
    {
        $invitation = Invitation::where('id', $invitationId)
            ->where('email', Auth::user()->email)
            ->first();

        if ($invitation) {
            $invitation->delete();
            $this->dispatch('action-message', message: 'Invitation ignored.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        // 0. Metadata: Fetch Roles for display
        $ownerRoleId = Role::where('slug', 'owner')->value('id');
        $userRoles = Role::pluck('name', 'id'); // Returns [1 => 'Owner', 2 => 'Member', ...]

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

        // 3. Upcoming Events (Filtered for Visibility)
        $calendarIds = $user->calendars()->pluck('calendars.id');

        // A. User's System Role (Owner/Member) per calendar
        $userCalendarSystemRoles = $user->calendars()->pluck('calendar_user.role_id', 'calendars.id');

        // B. User's Custom Group IDs (across all calendars)
        $userGroupIds = $user->groups()->pluck('groups.id');

        $rawUpcomingEvents = Event::whereIn('calendar_id', $calendarIds)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->with(['calendar', 'groups', 'genders'])
            ->take(20)
            ->get();

        $upcomingEvents = $rawUpcomingEvents->filter(function($event) use ($user, $userCalendarSystemRoles, $userGroupIds, $ownerRoleId) {
            // Owner Bypass: If user is Owner of this specific calendar, they see everything
            $systemRoleId = $userCalendarSystemRoles[$event->calendar_id] ?? null;
            if ($systemRoleId == $ownerRoleId) {
                return true;
            }

            // Gender Filter
            if ($event->genders->isNotEmpty()) {
                if (!$user->gender_id || !$event->genders->contains('id', $user->gender_id)) {
                    return false;
                }
            }

            // Age Filter
            if ($event->min_age) {
                if (!$user->birth_date || $user->birth_date->age < $event->min_age) {
                    return false;
                }
            }

            // Group/Role Visibility
            if ($event->groups->isNotEmpty() && $event->is_role_restricted) {
                if ($event->groups->pluck('id')->intersect($userGroupIds)->isEmpty()) {
                    return false;
                }
            }

            return true;
        })->take(3);

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
            'userRoles' => $userRoles,     // Pass roles map
            'ownerRoleId' => $ownerRoleId, // Pass owner ID
        ])
            ->layout('components.layouts.app', ['title' => __('Dashboard')]);
    }
}
