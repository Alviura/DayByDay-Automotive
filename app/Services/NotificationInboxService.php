<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationInboxService
{
    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * @return Collection<int, DatabaseNotification>
     */
    public function recent(User $user, int $limit = 5): Collection
    {
        return $user->notifications()->latest()->take($limit)->get();
    }

    public function paginate(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->notifications()->latest()->paginate($perPage)->withQueryString();
    }

    public function markRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->first()?->markAsRead();
    }

    public function markAllRead(User $user): int
    {
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();

        return $count;
    }
}
