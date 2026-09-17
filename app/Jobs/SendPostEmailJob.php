<?php

namespace App\Jobs;

use App\Mail\PostPublishedMail;
use App\Models\Post;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPostEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly Post $post,
        public readonly User $user
    ) {}

    public function handle(): void
    {
        $alreadySent = SentEmail::where('post_id', $this->post->id)
            ->where('user_id', $this->user->id)
            ->exists();

        if ($alreadySent) {
            return;
        }

        Mail::to($this->user->email)->send(new PostPublishedMail($this->post));

        SentEmail::create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);
    }
}
