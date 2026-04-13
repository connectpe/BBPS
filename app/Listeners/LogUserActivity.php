<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\UserActivityEvent;
use App\Models\UsersLog as ModelsUsersLog;

class LogUserActivity
{
    /**
     * Create the event listener.
     */
    // public function __construct()
    // {
    //     //
    // }

    /**
     * Handle the event.
     */
    public function handle(UserActivityEvent $event): void
    {
        ModelsUsersLog::create([
            'user_id' => $event->user->id,
            'action' => $event->action,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
