<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BusinessHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manager: weekly hours + same-day overrides (closed today, early close, extend).
 *
 * @author Mueez Ul Rehman
 */
class BusinessHoursController extends Controller
{
    public function edit()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $hours = BusinessHours::normalized($restaurant);
        $statusLabel = BusinessHours::label($restaurant);
        $accepting = BusinessHours::isAcceptingOnlineOrders($restaurant);

        return view('admin.restaurant-profile.hours', compact('restaurant', 'hours', 'statusLabel', 'accepting'));
    }

    public function updateWeekly(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $data = $request->validate([
            'opening_hours' => 'nullable|array',
            'accept_orders_when_closed' => 'nullable|boolean',
        ]);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $normalized = [];
        foreach ($days as $day) {
            $row = $data['opening_hours'][$day] ?? [];
            $normalized[$day] = [
                'open' => $row['open'] ?? '09:00',
                'close' => $row['close'] ?? '22:00',
                'closed' => ! empty($row['closed']),
            ];
        }

        $restaurant->update([
            'opening_hours' => $normalized,
            'accept_orders_when_closed' => $request->boolean('accept_orders_when_closed'),
        ]);

        return back()->with('success', 'Weekly opening hours saved.');
    }

    /**
     * Same-day controls: closed today, early close, extend close, clear overrides.
     */
    public function updateToday(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $action = $request->input('action'); // closed_today | open_today | early_close | extend | clear_overrides

        switch ($action) {
            case 'closed_today':
                $request->validate(['closed_message' => 'nullable|string|max:255']);
                $restaurant->update([
                    'is_closed_today' => true,
                    'closed_message' => $request->input('closed_message') ?: 'Closed today',
                    'early_close_at' => null,
                    'extend_close_at' => null,
                ]);
                $msg = 'Business marked closed today. Online orders blocked.';
                break;

            case 'open_today':
                $restaurant->update([
                    'is_closed_today' => false,
                    'closed_message' => null,
                ]);
                $msg = 'Closed-today flag cleared.';
                break;

            case 'early_close':
                $request->validate([
                    'early_close_time' => 'required|date_format:H:i',
                    'closed_message' => 'nullable|string|max:255',
                ]);
                $at = now()->setTimeFromTimeString($request->input('early_close_time'));
                $restaurant->update([
                    'is_closed_today' => false,
                    'early_close_at' => $at,
                    'extend_close_at' => null,
                    'closed_message' => $request->input('closed_message'),
                ]);
                $msg = 'Early close set for today at ' . $at->format('H:i');
                break;

            case 'extend':
                $request->validate(['extend_close_time' => 'required|date_format:H:i']);
                $at = now()->setTimeFromTimeString($request->input('extend_close_time'));
                $restaurant->update([
                    'is_closed_today' => false,
                    'extend_close_at' => $at,
                    'early_close_at' => null,
                ]);
                $msg = 'Hours extended today until ' . $at->format('H:i');
                break;

            case 'clear_overrides':
                $restaurant->update([
                    'is_closed_today' => false,
                    'early_close_at' => null,
                    'extend_close_at' => null,
                    'closed_message' => null,
                ]);
                $msg = 'Same-day overrides cleared. Weekly schedule applies.';
                break;

            default:
                return back()->withErrors(['action' => 'Unknown action.']);
        }

        return back()->with('success', $msg);
    }
}
