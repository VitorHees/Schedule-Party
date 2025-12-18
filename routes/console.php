<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Event;
use App\Notifications\SystemNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Check for events starting in 1 hour
Schedule::call(function () {
    // We look for events starting between 59 and 61 minutes from now
    // to ensure we catch them exactly once (since the schedule runs every minute)
    $events = Event::query()
        ->whereBetween('start_date', [
            now()->addMinutes(59),
            now()->addMinutes(61)
        ])
        ->get();

    foreach ($events as $event) {
        // Notify all participants who have opted in
        foreach ($event->participants as $user) {
            $user->notify(new SystemNotification(
                message: "Event '{$event->name}' starts in 1 hour!",
                url: route('calendar.personal') // Adjust if you have a specific event route
            ));
        }
    }
})->everyMinute();
