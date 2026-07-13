@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Stock Adjustment History</h1>

    <form action="{{ route('manager.stock.adjustments.store') }}" method="POST">
        @csrf
        <label>Menu Item</label>
        <select name="menu_item_id">
            @foreach(App\Models\MenuItem::where('restaurant_id', auth()->user()->effectiveRestaurant()->id)->get() as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
        <label>Adjustment Type</label>
        <select name="adjustment_type">
            <option value="in">In</option>
            <option value="out">Out</option>
            <option value="correction">Correction</option>
        </select>
        <label>Quantity</label>
        <input type="number" name="quantity" min="1" required>
        <label>Reason</label>
        <input type="text" name="reason">
        <button type="submit">Adjust Stock</button>
    </form>

    <h2>Adjustments</h2>
    <ul>
        @foreach($adjustments as $adjustment)
            <li>{{ $adjustment->created_at->format('Y-m-d') }} — {{ $adjustment->menuItem->name }} changed by {{ $adjustment->quantity_changed }}</li>
        @endforeach
    </ul>
</div>
@endsection
