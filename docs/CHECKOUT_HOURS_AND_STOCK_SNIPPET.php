<?php
/**
 * Snippets to merge into CheckoutController::store()
 */

// --- After $restaurant = $this->resolveRestaurant($request) ---

/*
if (! $restaurant->isAcceptingOnlineOrders()) {
    $msg = $restaurant->closed_message
        ?: ('This business is closed right now. ' . ($restaurant->nextOpenLabel() ?? 'Please order during opening hours.'));

    return back()->withErrors(['cart' => $msg])->withInput();
}
*/

// --- Inside cart menu_item loop: CHECK stock only, do not decrement ---

/*
if ($menuItem->track_stock) {
    \App\Support\OrderStockService::assertMenuItemStock($menuItem, (float) $line['quantity']);
}
*/

// --- DELETE any previous block that did $mi->update(['stock_quantity' => $after]) after order create ---
// Stock is reserved only when manager confirms (OrderStockService::decrementOnConfirm).
