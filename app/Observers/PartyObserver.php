<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\Comment;
use App\Models\CalendarUser;
use App\Models\User;
use App\Notifications\SystemNotification;

class PartyObserver
{
    // Trigger: New event in shared calendar
    public function createdEvent(Event $event): void
    {
        if ($event->calendar && $event->calendar->isCollaborative()) {
            $usersToNotify = $event->calendar->users->reject(fn($u) => $u->id === $event->created_by);

            foreach ($usersToNotify as $user) {
                $user->notify(new SystemNotification(
                    "New event '{$event->name}' in {$event->calendar->name}",
                    route('calendar.shared', $event->calendar)
                ));
            }
        }
    }

    // Trigger: Comment on shared event & Mentions
    public function createdComment(Comment $comment): void
    {
        $event = $comment->event;

        // 1. Notify Participants
        if ($event->calendar->isCollaborative()) {
            $recipients = $event->participants->merge([$event->creator])
                ->unique('id')
                ->reject(fn($u) => $u->id === $comment->user_id);

            foreach ($recipients as $user) {
                $user->notify(new SystemNotification(
                    "{$comment->user->username} commented on '{$event->name}'",
                    route('calendar.shared', $event->calendar) // Deep link to event if possible
                ));
            }
        }

        // 2. Notify Mentions (@vitorhees)
        if (!empty($comment->mentions)) {
            $mentionedUsers = User::whereIn('id', $comment->mentions)->get();
            foreach ($mentionedUsers as $user) {
                if ($user->id !== $comment->user_id) {
                    $user->notify(new SystemNotification(
                        "You were mentioned in a comment on '{$event->name}'",
                        route('calendar.shared', $event->calendar)
                    ));
                }
            }
        }
    }

    // Trigger: User kicked from calendar
    // Note: You must bind this to the "deleted" event of CalendarUser model
    public function deletedCalendarUser(CalendarUser $pivot): void
    {
        $user = User::find($pivot->user_id);
        $calendar = $pivot->calendar;

        if ($user && $calendar) {
            $user->notify(new SystemNotification(
                "You were removed from the calendar '{$calendar->name}'"
            ));
        }
    }
}
