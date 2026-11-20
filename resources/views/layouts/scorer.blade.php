<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Scorer Mode' }} — Barangay Hoops</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen lg:h-screen lg:overflow-hidden arena-obsidian-bg font-sans text-slate-100 antialiased selection:bg-orange-500 selection:text-white flex flex-col">
    <main class="flex-1 min-h-0 flex flex-col">
        {{ $slot }}
    </main>
</body>
</html>
