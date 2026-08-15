<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $type,
        private readonly ?string $actionUrl = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (! $notifiable instanceof User) {
            return $channels;
        }

        $preferences = $notifiable->notificationPreferences()
            ->whereIn('channel', ['app', 'email'])
            ->get()
            ->keyBy('channel');

        if ($preferences->get('app')?->enabled === false) {
            $channels = array_values(array_diff($channels, ['database']));
        }

        if ($preferences->get('email')?->enabled === false) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->message);

        if ($this->actionUrl) {
            $mail->action('View Details', $this->actionUrl);
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
        ];
    }
}
