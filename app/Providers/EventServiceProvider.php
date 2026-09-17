<?php

namespace App\Providers;

use App\Events\PostPublished;
use App\Listeners\SendPostNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PostPublished::class => [
            SendPostNotifications::class,
        ],
    ];
}
