<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request, AdminNotificationService $notificationService): View
    {
        $user = auth()->user();
        $category = in_array($request->query('kategori'), ['produk', 'ulasan'], true)
            ? (string) $request->query('kategori')
            : 'semua';

        $allItems = $user->notifications()
            ->latest()
            ->get()
            ->map(fn (DatabaseNotification $notification) => $notificationService->formatNotification($notification));

        $counts = $notificationService->countByCategory($allItems);
        $filteredItems = $category === 'semua'
            ? $allItems
            : $allItems->where('category', $category)->values();

        $perPageParam = $request->query('per_page', 10);
        $perPage = in_array($perPageParam, ['all', 'semua'], true)
            ? max(1, $filteredItems->count())
            : (in_array((int) $perPageParam, [10, 25, 50], true) ? (int) $perPageParam : 10);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $notifications = new LengthAwarePaginator(
            $filteredItems->forPage($currentPage, $perPage)->values(),
            $filteredItems->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.notifikasi.index', [
            'notifications' => $notifications,
            'counts' => $counts,
            'activeCategory' => $category,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function poll(AdminNotificationService $notificationService): JsonResponse
    {
        return response()->json(
            $notificationService->summaryForAdmin(auth()->user())
        );
    }

    public function markRead(DatabaseNotification $notification, AdminNotificationService $notificationService): RedirectResponse
    {
        abort_unless((int) $notification->notifiable_id === (int) auth()->id(), 403);

        $notification->markAsRead();
        $target = $notificationService->normalizeInternalUrl(
            $notification->data['url'] ?? null,
            route('admin.dashboard', absolute: false)
        );

        return redirect()->to($target);
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
