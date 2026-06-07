<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

final class WeeklyDigestJob implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        // Get users who have weekly_digest enabled or haven't set explicit preference (default to enabled)
        $users = User::whereHas('notificationPreferences', function ($query) {
            $query->where('channel', 'weekly_digest')->where('enabled', true);
        })->orWhereDoesntHave('notificationPreferences')->get();

        foreach ($users as $user) {
            $notifications = $user->unreadNotifications()
                ->where('created_at', '>=', now()->subWeek())
                ->get();

            if ($notifications->isEmpty()) {
                continue;
            }

            Mail::raw("Weekly Digest for " . $user->name . ":\n\nYou have " . $notifications->count() . " unread notifications in the past week.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('LedgerScope Weekly Notification Digest');
            });
        }
    }
}
