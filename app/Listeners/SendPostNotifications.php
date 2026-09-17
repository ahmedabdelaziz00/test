<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Jobs\SendPostEmailJob;
use App\Models\Subscription;

class SendPostNotifications
{
    public function handle(PostPublished $event): void
    {
        $post = $event->post;

        Subscription::where('website_id', $post->website_id)
            ->with('user')
            ->get()
            ->each(function (Subscription $subscription) use ($post) {
                SendPostEmailJob::dispatch($post, $subscription->user);
            });
    }
}
