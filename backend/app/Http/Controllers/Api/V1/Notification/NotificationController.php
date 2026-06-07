<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

final class NotificationController extends Controller
{
    /**
     * List all notifications for the authenticated user (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        return ApiResponse::success($notifications);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markRead(Request $request, DatabaseNotification $notification): JsonResponse
    {
        if ($notification->notifiable_id !== $request->user()->id) {
            return ApiResponse::forbidden('Access denied.');
        }

        $notification->markAsRead();

        return ApiResponse::success(null, 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return ApiResponse::success(null, 'All notifications marked as read.');
    }

    /**
     * Get user notification preferences.
     */
    public function preferences(Request $request): JsonResponse
    {
        $prefs = $request->user()->notificationPreferences()->get();

        return ApiResponse::success($prefs);
    }

    /**
     * Update user notification preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.channel' => ['required', 'string', 'in:email,app,weekly_digest'],
            'preferences.*.event_type' => ['required', 'string', 'in:document_request,review_note,finding'],
            'preferences.*.enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $validated): void {
            foreach ($validated['preferences'] as $prefData) {
                $user->notificationPreferences()->updateOrCreate(
                    [
                        'channel' => $prefData['channel'],
                        'event_type' => $prefData['event_type'],
                    ],
                    [
                        'enabled' => $prefData['enabled'],
                    ]
                );
            }
        });

        return ApiResponse::success($user->notificationPreferences()->get(), 'Notification preferences updated.');
    }
}
