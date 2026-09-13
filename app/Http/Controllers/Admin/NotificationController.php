<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->isSuperAdmin() ? null : $user->effectiveRestaurantId();

        $query = Notification::query()->when($restaurantId, fn($query) => $query->where('restaurant_id', $restaurantId))->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate(15)->withQueryString();

        $stats = [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
        ];

        return view($user->isSuperAdmin() ? 'super-admin.notifications.index' : 'manager.notifications.index', compact('notifications', 'stats', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:order_update,feedback_reply,low_stock,delivery_update,custom',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'channels' => 'nullable|array',
            'channels.*' => 'required|in:email,whatsapp,push',
        ]);

        $channels = array_values(array_unique($validated['channels'] ?? ['email']));

        $notification = Notification::create([
            'restaurant_id' => Auth::user()->effectiveRestaurantId(),
            'user_id' => null,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'channels' => $channels,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->route('manager.notifications.index')
            ->with('success', "Notification {$notification->title} created.");
    }

    public function feed(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->isSuperAdmin() ? null : $user->effectiveRestaurantId();
        $after = (int) $request->query('after', 0);

        $notifications = Notification::query()
            ->where('type', 'order_update')
            ->when($restaurantId, fn ($query) => $query->where('restaurant_id', $restaurantId))
            ->where('id', '>', $after)
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'restaurant_id' => $notification->restaurant_id,
            ])
            ->values();

        return response()->json(['notifications' => $notifications]);
    }

    public function markAsRead(int $notificationId)
    {
        $notification = Notification::query()->whereKey($notificationId)->firstOrFail();
        $this->authorizeRestaurant($notification);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        $route = Auth::user()->isSuperAdmin() ? 'admin.notifications.index' : 'manager.notifications.index';

        return redirect()->route($route)->with('success', 'Notification marked as read.');
    }

    protected function authorizeRestaurant(Notification $notification): void
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin' && $notification->restaurant_id !== $user->restaurant_id) {
            abort(403, 'You do not have access to this notification.');
        }
    }
}
