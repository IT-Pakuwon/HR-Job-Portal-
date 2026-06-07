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

<body class="font-inter antialiased bg-[#07051a] sm:h-screen sm:overflow-hidden">

    <main
        class="relative min-h-screen sm:h-full"
        style="background-color:#07051a; background-image:url('{{ asset('images/login/Background 2.png') }}'); background-size:cover; background-position:center;">

        {{-- Dark vignette overlay (consistent with left hero panel) --}}
        <div class="pointer-events-none absolute inset-0"
            style="background:radial-gradient(ellipse at center,rgba(0,0,0,0.10) 0%,rgba(0,0,0,0.55) 100%);"></div>

        {{-- Main Content --}}
        <div class="relative flex min-h-screen sm:h-screen items-center justify-center p-3 md:p-6 lg:p-8">

            {{ $slot }}

        </div>

    </main>

    @livewireScriptConfig

</body>

</html>
