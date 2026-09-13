<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KitchenTicketController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $tickets = KitchenTicket::with('order')->where('restaurant_id', $id)->whereNotIn('status', ['completed', 'cancelled'])->orderByDesc('priority')->orderBy('created_at')->get();
        $orders = Order::where('restaurant_id', $id)->whereIn('status', ['pending', 'confirmed', 'preparing'])->latest()->limit(50)->get();
        return view('manager.kitchen-display.index', compact('tickets', 'orders'));
    }

    public function store(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'station' => 'nullable|string|max:80', 'priority' => 'nullable|integer|min:0|max:10', 'notes' => 'nullable|string|max:1000']);
        KitchenTicket::create([...$data, 'restaurant_id' => $id, 'ticket_number' => 'KT-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(4))]);
        return back()->with('success', 'Kitchen ticket queued.');
    }

    public function updateStatus(Request $request, KitchenTicket $ticket)
    {
        abort_unless($ticket->restaurant_id === $this->restaurantId(), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['queued', 'preparing', 'ready', 'completed', 'cancelled'])]]);
        $ticket->status = $data['status'];
        $ticket->started_at ??= $data['status'] === 'preparing' ? now() : null;
        $ticket->completed_at = in_array($data['status'], ['completed', 'cancelled'], true) ? now() : null;
        $ticket->save();
        return back()->with('success', 'Kitchen ticket updated.');
    }
}
