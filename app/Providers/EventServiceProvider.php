<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Queue;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function boot()
    {
        parent::boot();

        Queue::looping(function (\Illuminate\Queue\Events\Looping $event) {

            if (cache()->get('queue-paused')) {
                info('Queue paused', [
                    "queue" => $event->queue
                ]);
                return false;
            }

            return true;

        });
    }
}
