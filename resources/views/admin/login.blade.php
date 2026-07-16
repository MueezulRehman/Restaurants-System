<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center bg-hut-dark px-4">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-sm">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-xl mx-auto mb-2">PL</div>
            <h1 class="font-display font-bold text-hut-dark text-lg">Platform Admin</h1>
            <p class="text-xs text-gray-400">Owner & platform admin access only</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.attempt') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Phone number</label>
                <input type="text" name="phone" required autofocus value="{{ old('phone') }}"
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required
                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-500">
                <input type="checkbox" name="remember" class="accent-hut-green"> Remember me
            </label>
            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>
    </div>
</body>
</html>
