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
            Please pay your outstanding invoice or contact the platform administrator to restore access.
        </p>
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('manager.subscription.show') }}" class="rounded-lg bg-hut-yellow px-4 py-2 font-semibold text-hut-dark hover:bg-amber-300">Review subscription</a>
            <a href="{{ route('manager.logout') }}" class="rounded-lg border border-white/20 px-4 py-2 text-white hover:bg-white/10" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
        </div>
        <form id="logout-form" action="{{ route('manager.logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</body>
</html>
