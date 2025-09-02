<?php

namespace Modules\Notification\App\Notifications;

use App\Models\User;

class ExampleNotification extends BaseNotification
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getTitle(): string
    {
        return 'Welcome to Libiverse!';
    }

    public function getMessage(): string
    {
        return "Hello {$this->user->name}, welcome to our community of book lovers!";
    }

    public function getActionUrl(): ?string
    {
        return url('/dashboard');
    }

    public function getActionText(): ?string
    {
        return 'Go to Dashboard';
    }
}
