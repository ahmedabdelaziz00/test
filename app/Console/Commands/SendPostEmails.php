<?php

namespace App\Console\Commands;

use App\Jobs\SendPostEmailJob;
use App\Models\Post;
use App\Models\Subscription;
use Illuminate\Console\Command;
use App\Models\Website;

class SendPostEmails extends Command
{
    protected $signature   = 'emails:send';
    protected $description = 'Send new post emails to subscribers that have not been sent yet';

    public function handle(): void
    {
        $websites = Website::with('subscriptions.user')->get();

        foreach ($websites as $website) {
            $subscribers = $website->subscriptions->pluck('user');

            if ($subscribers->isEmpty()) {
                continue;
            }

            $posts = Post::where('website_id', $website->id)
                ->whereDoesntHave('sentEmails', function ($query) use ($subscribers) {
                    $query->whereIn('user_id', $subscribers->pluck('id'));
                })
                ->get();

            foreach ($posts as $post) {
                foreach ($subscribers as $user) {
                    $alreadySent = $post->sentEmails()
                        ->where('user_id', $user->id)
                        ->exists();

                    if (!$alreadySent) {
                        SendPostEmailJob::dispatch($post, $user);
                        $this->info("Queued email for [{$user->email}] - Post: [{$post->title}]");
                    }
                }
            }
        }

        $this->info('Done! All pending emails have been queued.');
    }
}
