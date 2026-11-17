<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminActivityNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class AdminNotificationService
{
    public function notifyAdmins(array $payload, ?int $exceptUserId = null): void
    {
        if (isset($payload['url'])) {
            $payload['url'] = $this->normalizeInternalUrl((string) $payload['url']);
        }

        User::where('role', 'admin')
            ->when($exceptUserId, fn ($query) => $query->where('id', '!=', $exceptUserId))
            ->get()
            ->each(fn (User $admin) => $admin->notify(new AdminActivityNotification($payload)));
    }

    public function normalizeInternalUrl(?string $url, string $fallback = '/admin/dashboard'): string
    {
        $raw = trim((string) $url);
        if ($raw === '') {
            return $fallback;
        }

        $parts = parse_url($raw);
        if (! is_array($parts) || empty($parts['path'])) {
            return $fallback;
        }

        $path = '/' . ltrim($parts['path'], '/');
        if (str_starts_with($path, '//')) {
            return $fallback;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }

    public function resolveCategory(array $data): string
    {
        $entityType = strtolower((string) ($data['entity_type'] ?? ''));
        $activity = strtolower((string) ($data['activity'] ?? ''));
        $title = strtolower((string) ($data['title'] ?? ''));

        if ($entityType === 'ulasan' || str_contains($activity, 'ulasan') || str_contains($title, 'ulasan')) {
            return 'ulasan';
        }

        return 'produk';
    }

    public function formatNotification(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];

        return [
            'id' => $notification->id,
            'data' => $data,
            'category' => $this->resolveCategory($data),
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at?->locale('id')->diffForHumans(),
        ];
    }

    public function summaryForAdmin(User $admin, int $limit = 15): array
    {
        $items = $admin->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->formatNotification($notification))
            ->values();

        return [
            'notifications' => $items,
            'unread_count' => $admin->unreadNotifications()->count(),
            'counts' => $this->countByCategory($items),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $formattedItems
     * @return array{semua: int, produk: int, ulasan: int}
     */
    public function countByCategory(Collection $formattedItems): array
    {
        return [
            'semua' => $formattedItems->count(),
            'produk' => $formattedItems->where('category', 'produk')->count(),
            'ulasan' => $formattedItems->where('category', 'ulasan')->count(),
        ];
    }
}
