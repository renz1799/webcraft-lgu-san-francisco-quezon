<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications
    ) {}

    
    public function index(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $perPage = max(1, (int) $request->query('per_page', 20));

        return response()->json(
            $this->notifications->paginateForUser($userId, $perPage)
        );
    }

    public function header(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;

        $notifications = $this->notifications
            ->paginateForUser($userId, 5)
            ->items();

        return response()->json([
            'unread_count' => $this->notifications->unreadCount($userId),
            'notifications' => collect($notifications)->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'read_at' => $n->read_at,
                    'created_at' => optional($n->created_at)->toISOString(),
                    'url' => data_get($n->data, 'url', '#'),
                ];
            }),
        ]);
    }


    public function unreadCount(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;

        return response()->json([
            'unread' => $this->notifications->unreadCount($userId),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $userId = (string) $request->user()->id;

        return response()->json([
            'marked' => $this->notifications->markAsRead($id, $userId),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->id;

        return response()->json([
            'updated' => $this->notifications->markAllAsRead($userId),
        ]);
    }

    public function page(Request $request): View
    {
        $userId = (string) $request->user()->id;

        $perPage = max(1, (int) $request->query('per_page', 20));
        $filter = (string) $request->query('filter', 'all'); // all | unread

     $query = $this->notifications->queryForUser($userId);

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->orderByDesc('created_at')->paginate($perPage);

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $this->notifications->unreadCount($userId),
        ]);
    }

    public function markAllAsReadWeb(Request $request): RedirectResponse
    {
        $userId = (string) $request->user()->id;

        $this->notifications->markAllAsRead($userId);

        return back()->with('success', 'All notifications marked as read.');
    }
}
