<?php

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::query()
            ->where('notifiable_user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function markAsRead(string $notificationId, string $userId): bool
    {
        return Notification::query()
            ->where('id', $notificationId)
            ->where('notifiable_user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]) > 0;
    }

    public function markAllAsRead(string $userId): int
    {
        return Notification::query()
            ->where('notifiable_user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function unreadCount(string $userId): int
    {
        return Notification::query()
            ->where('notifiable_user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
