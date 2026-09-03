<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $restaurant->name ?? 'Business menu' }} — Coming Soon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-hut-dark flex items-center justify-center px-4" @if($restaurant) style="{{ $restaurant->themeCssVariables() }}" @endif>
    <div class="text-center max-w-xl">
        @if(!empty($restaurant->logo_path))
            <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-6">
        @else
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-hut-yellow flex items-center justify-center text-4xl font-display font-bold text-hut-dark">{{ strtoupper(substr($restaurant->name ?? 'BUS', 0, 2)) }}</div>
        @endif
        <h1 class="text-3xl md:text-4xl font-display font-bold text-white mb-4">{{ $restaurant->name ?? 'This business' }} is updating its menu</h1>
        <p class="text-gray-300 mb-6">We’re getting everything ready for your orders. Check back soon, or contact the business from their profile page for updates.</p>
        @if(!empty($restaurant->phone))
            <p class="text-gray-300 mb-4">📞 {{ $restaurant->phone }}</p>
        @endif
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-full bg-white text-hut-dark px-5 py-3 font-semibold shadow-lg hover:bg-gray-100 transition">Back to business selection</a>
    </div>
</body>
</html>
