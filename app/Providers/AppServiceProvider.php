<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Event;
use App\Models\Comment;
use App\Models\CalendarUser;
use App\Observers\PartyObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Trigger when a new Event is created
        Event::created(function (Event $event) {
            (new PartyObserver)->createdEvent($event);
        });

        // Trigger when a new Comment is created
        Comment::created(function (Comment $comment) {
            (new PartyObserver)->createdComment($comment);
        });

        // Trigger when a User is removed from a Calendar (CalendarUser pivot deleted)
        CalendarUser::deleted(function (CalendarUser $pivot) {
            (new PartyObserver)->deletedCalendarUser($pivot);
        });
    }
}
