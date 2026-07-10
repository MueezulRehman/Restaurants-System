<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-hut-dark flex items-center justify-center px-4">
    <div class="max-w-xl text-center rounded-2xl bg-white/10 p-8 shadow-xl backdrop-blur">
        <div class="w-20 h-20 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-3xl mx-auto mb-5">TH</div>
        <h1 class="text-3xl font-display font-bold text-white mb-3">Subscription Expired</h1>
        <p class="text-gray-300 mb-6">
            Your restaurant access is temporarily paused because the current subscription is no longer active.
            Please contact the platform administrator to reactivate billing or extend access.
        </p>
        <a href="{{ route('manager.logout') }}" class="btn-accent" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
        <form id="logout-form" action="{{ route('manager.logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</body>
</html>
