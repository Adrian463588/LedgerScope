<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\AppNotification;

final class NotificationService
{
    /**
     * Send a notification to a single user.
     */
    public function notify(User $user, string $title, string $message, string $type, ?string $actionUrl = null): void
    {
        $user->notify(new AppNotification($title, $message, $type, $actionUrl));
    }

    /**
     * Send a notification to multiple users.
     *
     * @param iterable<User> $users
     */
    public function notifyMany(iterable $users, string $title, string $message, string $type, ?string $actionUrl = null): void
    {
        foreach ($users as $user) {
            $this->notify($user, $title, $message, $type, $actionUrl);
        }
    }
}
