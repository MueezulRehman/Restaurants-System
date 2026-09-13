<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Token {{ $queueEntry->token_number }}</title><style>
body{font-family:Arial,sans-serif;margin:0;padding:24px;color:#17202a}.slip{max-width:360px;margin:0 auto;border:1px dashed #64748b;padding:24px;text-align:center}.label{font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.08em}.token{font-size:64px;font-weight:700;margin:12px 0}.meta{font-size:14px;margin:6px 0}@media print{body{padding:0}.slip{border:0}}
</style></head>
<body onload="window.print()"><main class="slip"><div class="label">{{ $restaurant->name }}</div><h1>{{ $queueEntry->doctor->name }}</h1><p class="meta">{{ $queueEntry->doctor->specialty }}</p><div class="token">{{ $queueEntry->token_number }}</div><p class="meta">{{ $queueEntry->queue_date->format('d M Y') }}</p><p class="meta">{{ $queueEntry->patient->name }}</p><p class="label">Please wait for your token to be called.</p><p class="meta"><a href="{{ route('medical-token.show', [$restaurant->slug, $queueEntry->public_token]) }}">View token status</a></p></main></body>
</html>
