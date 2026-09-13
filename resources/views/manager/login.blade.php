<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Internal Login — {{ config('app.name', 'CodeIbex') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-page">
    <main class="auth-page__background">
        <div class="auth-card">
            <div class="mb-6 text-center">
                <div class="auth-card__mark">CI</div>
                <h1 class="auth-card__title">Internal Login</h1>
                <p class="auth-card__subtitle">Sign in to your business or platform workspace</p>
            </div>

            @if ($errors->any())
                <div class="auth-card__error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('manager.login.attempt') }}" method="POST" class="auth-form">
                @csrf
                <div>
                    <label for="internal-phone">Phone number</label>
                    <input id="internal-phone" type="text" name="phone" value="{{ old('phone') }}" required autofocus>
                </div>
                <div>
                    <label for="internal-password">Password</label>
                    <div class="auth-password">
                        <input id="internal-password" type="password" name="password" required>
                        <button type="button" data-password-toggle="internal-password" aria-label="Show password">Show</button>
                    </div>
                </div>
                <label class="auth-remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
                <button type="submit" class="auth-form__submit">Log in</button>
            </form>
        </div>
    </main>
</body>

</html>
