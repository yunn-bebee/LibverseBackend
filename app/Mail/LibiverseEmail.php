<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LibiverseEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $content; // Changed from $message to $content
    public $actionUrl;
    public $actionText;

    public function __construct(string $title, string $content, ?string $actionUrl = null, ?string $actionText = null)
    {
        $this->title = $title;
        $this->content = $content; // Changed from $message to $content
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->from('noreply@libiverse.com', 'Libiverse')
                    ->view('general')
                    ->with([
                        'title' => $this->title,
                        'content' => $this->content, // Changed from $message to $content
                        'actionUrl' => $this->actionUrl,
                        'actionText' => $this->actionText,
                    ]);
    }
}
