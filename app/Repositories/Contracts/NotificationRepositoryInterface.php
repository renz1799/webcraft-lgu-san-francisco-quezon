<?php

namespace App\Repositories\Contracts;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface NotificationRepositoryInterface
{
    public function create(array $data): Notification;

    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function queryForUser(string $userId): Builder;

    public function markAsRead(string $notificationId, string $userId): bool;

    public function markAllAsRead(string $userId): int;

    public function unreadCount(string $userId): int;

    public function insertMany(array $rows): void;
}
