<?php

namespace Modules\Notification\App\Notifications;

use App\Models\User;
use Modules\Notification\App\Notifications\BaseNotification;

class GenericNotification extends BaseNotification
{
    protected $user;
    protected $title;
    protected $message;
    protected $actionUrl;
    protected $actionText;

    public function __construct(User $user, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null)
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function getActionText(): ?string
    {
        return $this->actionText;
    }
}

