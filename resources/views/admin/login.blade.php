<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen flex items-center justify-center bg-[#172b4d] px-4 py-10">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-sm border-t-4 border-[#7BA4D0]">
        <div class="text-center mb-6">
            <div
                class="w-14 h-14 bg-[#2E5E99] rounded-2xl flex items-center justify-center font-display font-bold text-white text-xl mx-auto mb-3">
                SA</div>
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
                <div class="relative mt-1">
                    <input id="admin-password" type="password" name="password" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 pr-16 focus:outline-none focus:border-hut-green">
                    <button type="button" data-password-toggle="admin-password"
                        class="absolute inset-y-0 right-2 px-2 text-xs font-semibold text-gray-500 hover:text-hut-dark"
                        aria-label="Show password">Show</button>
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-500">
                <input type="checkbox" name="remember" value="1" class="accent-hut-green" {{ old('remember') ? 'checked' : '' }}> Remember me
            </label>
            <button type="submit" class="btn-primary w-full">Log in</button>
        </form>
    </div>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.textContent = visible ? 'Show' : 'Hide';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        }));
    </script>
</body>

</html>