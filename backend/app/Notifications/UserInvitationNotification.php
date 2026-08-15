<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public bool $afterCommit = true;

    public function __construct(
        private readonly UserInvitation $invitation,
        private readonly string $recipientName,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $acceptUrl = $frontendUrl.'/invite/'.$this->invitation->token;

        return (new MailMessage)
            ->subject('You have been invited to LedgerScope')
            ->greeting("Hello {$this->recipientName},")
            ->line('An administrator invited you to join a LedgerScope workspace.')
            ->action('Accept invitation', $acceptUrl)
            ->line('This invitation expires in 72 hours.');
    }
}
