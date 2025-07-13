<?php

return [
    Modules\Challenge\App\Providers\ChallengeServiceProvider::class,
    Modules\Notification\App\Providers\NotificationServiceProvider::class,
    Modules\Forum\App\Providers\ForumServiceProvider::class,
    Modules\Event\App\Providers\EventServiceProvider::class,
    Modules\Mention\App\Providers\MentionServiceProvider::class,
    Modules\Post\App\Providers\PostServiceProvider::class,
    Modules\Badge\App\Providers\BadgeServiceProvider::class,
    Modules\Book\App\Providers\BookServiceProvider::class,
   
    Modules\Auth\App\Providers\AuthServiceProvider::class,
      App\Providers\AppServiceProvider::class,
    Modules\User\App\Providers\UserServiceProvider::class,
];
