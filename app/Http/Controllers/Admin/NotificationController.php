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
        $restaurantId = Auth::user()->restaurant_id;

        $query = Notification::where('restaurant_id', $restaurantId)->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate(15)->withQueryString();

        $stats = [
            'pending' => Notification::where('restaurant_id', $restaurantId)->where('status', 'pending')->count(),
            'sent' => Notification::where('restaurant_id', $restaurantId)->where('status', 'sent')->count(),
            'failed' => Notification::where('restaurant_id', $restaurantId)->where('status', 'failed')->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
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
            'restaurant_id' => Auth::user()->restaurant_id,
            'user_id' => Auth::id(),
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

    public function markAsRead(Notification $notification)
    {
        $this->authorizeRestaurant($notification);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.');
    }

    protected function authorizeRestaurant(Notification $notification): void
    {
        $user = Auth::user();

        if ($user->role !== 'super_admin' && $notification->restaurant_id !== $user->restaurant_id) {
            abort(403, 'You do not have access to this notification.');
        }
    }
}
