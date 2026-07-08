<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taste Hut Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-hut-dark flex items-center justify-center px-4">
    <div class="text-center">
        <div class="w-20 h-20 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-3xl mx-auto mb-4">TH</div>
        <h1 class="text-2xl font-display font-bold text-white mb-2">Taste Hut Platform</h1>
        <p class="text-gray-300 mb-6">No restaurant found. Please log in as super admin to register one.</p>
        <a href="/admin/login" class="btn-accent">Go to Admin Panel →</a>
    </div>
</body>
</html>
