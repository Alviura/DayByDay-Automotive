<?php

namespace App\Http\Controllers;

use App\Services\NotificationInboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationInboxService $inbox)
    {
        $this->middleware('permission:notifications.view');
    }

    public function index(Request $request): View
    {
        $notifications = $this->inbox->paginate($request->user(), 25);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $this->inbox->unreadCount($request->user()),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $this->inbox->markRead($request->user(), $notification);

        $note = $request->user()->notifications()->find($notification);
        $url = $note?->data['url'] ?? route('notifications.index');

        return redirect()->to($url);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $count = $this->inbox->markAllRead($request->user());

        return back()->with('status', $count > 0
            ? "{$count} notification(s) marked as read."
            : 'No unread notifications.');
    }
}
