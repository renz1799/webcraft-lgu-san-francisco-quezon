<?php

namespace App\Repositories\Eloquent;

use App\Models\TaskEvent;
use App\Models\User;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentTaskEventRepository implements TaskEventRepositoryInterface
{
    public function create(array $data): TaskEvent
    {
        // ✅ Auto-snapshot if not provided (prevents forgetting in services/seeders)
        if (!empty($data['actor_user_id'])) {
            $data['actor_name_snapshot'] ??= $this->resolveActorNameSnapshot($data['actor_user_id']);
            $data['actor_username_snapshot'] ??= $this->resolveActorUsernameSnapshot($data['actor_user_id']);
        }

        return TaskEvent::create($data);
    }

    public function getForTask(string $taskId): Collection
    {
        return TaskEvent::query()
            ->where('task_id', $taskId)
            ->orderByDesc('created_at')
            ->get();
    }

    private function resolveActorNameSnapshot(string $actorUserId): string
    {
        $actor = User::with('profile')->find($actorUserId);

        return $actor?->profile?->full_name
            ?: ($actor?->username ?: 'Unknown User');
    }

    private function resolveActorUsernameSnapshot(string $actorUserId): ?string
    {
        return User::query()
            ->whereKey($actorUserId)
            ->value('username');
    }
}
