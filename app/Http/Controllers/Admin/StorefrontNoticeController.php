<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorefrontNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorefrontNoticeController extends Controller
{
    public function edit()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $notice = StorefrontNotice::query()->firstOrNew([
            'restaurant_id' => $restaurant->id,
        ], [
            'title' => 'Important update',
            'is_active' => false,
            'show_as_modal' => true,
        ]);

        return view('manager.restaurant-profile.storefront-notice', compact('restaurant', 'notice'));
    }

    public function update(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:1000',
            'is_active' => 'nullable|boolean',
            'show_as_modal' => 'nullable|boolean',
            'stop_orders' => 'nullable|boolean',
        ]);

        StorefrontNotice::updateOrCreate(
            ['restaurant_id' => $restaurant->id],
            [
                'title' => $data['title'],
                'message' => $data['message'],
                'is_active' => $request->boolean('is_active'),
                'show_as_modal' => $request->boolean('show_as_modal'),
            ]
        );

        $restaurant->update([
            'accept_orders_when_closed' => $request->boolean('is_active') && ! $request->boolean('stop_orders'),
        ]);

        return back()->with('success', 'Storefront notice updated.');
    }

    public function destroy()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        StorefrontNotice::query()->where('restaurant_id', $restaurant->id)->delete();
        $restaurant->update(['accept_orders_when_closed' => false]);

        return back()->with('success', 'Storefront notice cleared. Normal business hours now control ordering.');
    }
}