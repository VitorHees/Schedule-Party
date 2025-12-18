<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Notifications extends Component
{
    public $notifications;
    public $unreadCount = 0;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function getListeners()
    {
        // This listens to the standard Laravel user channel
        return [
            "echo-private:App.Models.User." . Auth::id() . ",.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'notificationReceived',
        ];
    }

    public function notificationReceived($event)
    {
        $this->loadNotifications();
        // Optional: Dispatch a browser event for a toast/sound if you want
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        $this->unreadCount = $user->unreadNotifications()->count();
        $this->notifications = $user->notifications()->take(10)->get();
    }

    public function markAsRead($notificationId)
    {
        Auth::user()->notifications()->where('id', $notificationId)->first()?->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
