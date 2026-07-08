<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| The customer tracking page subscribes to a PRIVATE channel named
| "order.{tracking_token}". Because the channel name itself embeds the
| unguessable UUID token, simply knowing/guessing a channel name to
| subscribe to is already impossible without having received that exact
| order's tracking link. The authorization callback below is intentionally
| open (returns true) because there is no logged-in customer account to
| check against — the UUID token IS the access credential, the same way
| a long unguessable link works for a shared Google Doc.
|
*/

Broadcast::channel('order.{trackingToken}', function ($user, $trackingToken) {
    return \App\Models\Order::where('tracking_token', $trackingToken)->exists();
});
