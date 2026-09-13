<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="10">
<title>Now Serving — {{ $restaurant->name }}</title>
<style>
body{font-family:Arial,sans-serif;background:#0f172a;color:#f8fafc;margin:0;padding:5vw}.shell{max-width:1100px;margin:0 auto}.eyebrow{color:#94a3b8;text-transform:uppercase;letter-spacing:.16em;font-size:14px}.title{font-size:clamp(32px,6vw,72px);margin:10px 0 18px}.toolbar{display:flex;align-items:center;gap:16px;margin-bottom:28px;color:#94a3b8;font-size:14px}.refresh{border:1px solid #475569;border-radius:8px;background:#1e293b;color:#f8fafc;padding:8px 12px;cursor:pointer}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}.card{background:#1e293b;border-radius:20px;padding:28px;border:1px solid #334155}.doctor{color:#cbd5e1;font-size:20px}.token{font-size:clamp(72px,12vw,150px);font-weight:700;line-height:1;margin:18px 0}.status{color:#86efac;font-weight:700;text-transform:uppercase;letter-spacing:.12em}.empty{color:#cbd5e1;font-size:24px;padding:40px 0}
</style>
</head>
<body><main class="shell"><p class="eyebrow">{{ $restaurant->name }}</p><h1 class="title">Now Serving</h1><div class="toolbar"><button class="refresh" type="button" onclick="window.location.reload()">Refresh now</button><span>Last checked <time id="last-checked"></time></span></div>
@if($entries->isNotEmpty())<section class="grid">@foreach($entries as $entry)<article class="card"><div class="doctor">{{ $entry->doctor->name }}</div><div class="token">{{ $entry->token_number }}</div><div class="status">{{ $entry->status === 'in_progress' ? 'In consultation' : 'Please proceed' }}</div></article>@endforeach</section>@else<p class="empty">No patient is being called right now.</p>@endif
</main><script>document.getElementById('last-checked').textContent=new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});</script></body>
</html>
