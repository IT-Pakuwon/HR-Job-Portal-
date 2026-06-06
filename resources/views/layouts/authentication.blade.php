<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Pakuwon System') }}</title>

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/Logo Pakuwon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <script>
        if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        }
    </script>
</head>

<body class="font-inter antialiased sm:h-screen sm:overflow-hidden">

    <main
        class="relative min-h-screen sm:h-full"
        style="background:#07051a;">

        {{-- Deep base gradient --}}
        <div class="pointer-events-none absolute inset-0"
            style="background:linear-gradient(135deg,#0d0b2e 0%,#0f0c38 40%,#130e42 100%);"></div>

        {{-- Top-left indigo bloom --}}
        <div
            class="pointer-events-none absolute -left-40 -top-40 h-[700px] w-[700px] rounded-full blur-[120px]"
            style="background:radial-gradient(circle,rgba(99,102,241,0.35) 0%,rgba(79,70,229,0.15) 50%,transparent 70%);"></div>

        {{-- Bottom-right violet bloom --}}
        <div
            class="pointer-events-none absolute -bottom-40 -right-40 h-[700px] w-[700px] rounded-full blur-[120px]"
            style="background:radial-gradient(circle,rgba(139,92,246,0.30) 0%,rgba(109,40,217,0.12) 50%,transparent 70%);"></div>

        {{-- Center subtle glow --}}
        <div
            class="pointer-events-none absolute left-1/2 top-1/2 h-[500px] w-[900px] -translate-x-1/2 -translate-y-1/2 rounded-full blur-[160px]"
            style="background:radial-gradient(ellipse,rgba(99,102,241,0.12) 0%,transparent 70%);"></div>

        {{-- Main Content --}}
        <div class="relative flex min-h-screen sm:h-screen items-center justify-center p-3 md:p-6 lg:p-8">

            {{ $slot }}

        </div>

    </main>

    @livewireScriptConfig

</body>

</html>
