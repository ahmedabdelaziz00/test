<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Post $post
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Post: ' . $this->post->title,
        );
    }

    public function content(): Content
    {
        $title = e($this->post->title);
        $description = nl2br(e($this->post->description));

        return new Content(
            htmlString: <<<HTML
                <h2>{$title}</h2>
                <p>{$description}</p>
            HTML,
        );
    }
}
