<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $restaurant->name ?? 'Storefront Unavailable' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-hut-dark flex items-center justify-center px-4">
    <div class="text-center max-w-xl">
        <div class="w-20 h-20 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-3xl mx-auto mb-4">TH</div>
        <h1 class="text-3xl font-display font-bold text-white mb-4">Storefront Unavailable</h1>
        <p class="text-gray-300 mb-4">
            {{ $restaurant->name ?? 'This restaurant' }} is not currently accepting online orders.
        </p>
        <p class="text-gray-400 mb-6">
            That may be because the business is inactive, the subscription plan is inactive or expired, or the menu is temporarily disabled.
        </p>
        <a href="/" class="btn-accent">Back to Home</a>
    </div>
</body>
</html>
