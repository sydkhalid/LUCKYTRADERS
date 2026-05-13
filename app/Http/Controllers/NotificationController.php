<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', 'in:all,read,unread'],
        ])['status'] ?? 'all';

        $notifications = $request->user()
            ->erpNotifications()
            ->when($filter === 'read', fn ($query) => $query->read())
            ->when($filter === 'unread', fn ($query) => $query->unread())
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('notifications.index', compact('notifications', 'filter'));
    }

    public function dropdown(Request $request, NotificationAlertService $alerts): JsonResponse
    {
        $notifications = $request->user()
            ->erpNotifications()
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'unread_count' => $alerts->unreadCount($request->user()),
            'items' => $notifications->map(fn (Notification $notification) => $this->jsonItem($notification))->values(),
            'index_url' => route('notifications.index'),
            'mark_all_read_url' => route('notifications.read-all'),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'notification' => $this->jsonItem($notification->fresh()),
                'unread_count' => $request->user()->erpNotifications()->unread()->count(),
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAsUnread(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsUnread();

        if ($request->expectsJson()) {
            return response()->json([
                'notification' => $this->jsonItem($notification->fresh()),
                'unread_count' => $request->user()->erpNotifications()->unread()->count(),
            ]);
        }

        return back()->with('success', 'Notification marked as unread.');
    }

    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()
            ->erpNotifications()
            ->unread()
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    private function authorizeNotification(Request $request, Notification $notification): void
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);
    }

    private function jsonItem(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'module' => $notification->module,
            'severity' => $notification->severity,
            'title' => $notification->title,
            'message' => $notification->message,
            'action_url' => $notification->action_url,
            'read' => (bool) $notification->read_at,
            'read_url' => route('notifications.read', $notification),
            'unread_url' => route('notifications.unread', $notification),
            'created_at' => $notification->created_at?->format('d M Y h:i A'),
            'created_at_human' => $notification->created_at?->diffForHumans(),
        ];
    }
}
