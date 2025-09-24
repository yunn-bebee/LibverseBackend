<?php

namespace Modules\Notification\App\Notifications;

use App\Mail\LibiverseEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

abstract class BaseNotification extends Notification
{
    abstract public function getTitle(): string;
    abstract public function getMessage(): string;
    abstract public function getActionUrl(): ?string;
    abstract public function getActionText(): ?string;

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'user_id' => $notifiable->id,
            'type' => static::class,
            'channel' => $this->via($notifiable)[0] ?? 'database',
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'data' => [
                'title' => $this->getTitle(),
                'message' => $this->getMessage(),
            ],
        ];
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toMail($notifiable): LibiverseEmail
{
      return (new LibiverseEmail(
        $this->getTitle(),
        $this->getMessage(),
        $this->getActionUrl() ?? url('/'),
        $this->getActionText() ?? 'View'
    ))->to($notifiable->email);
}
}
