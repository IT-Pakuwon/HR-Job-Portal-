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

<body class="h-screen overflow-hidden font-inter antialiased">

    <main
        class="relative h-full overflow-hidden"
        style="
            background:
            radial-gradient(circle at center,
                rgba(255,255,255,.28) 0%,
                rgba(255,255,255,.12) 20%,
                rgba(255,255,255,.04) 35%,
                rgba(0,0,0,0) 60%),
            linear-gradient(
                135deg,
                #8b1d17 0%,
                #7a1714 35%,
                #6b1312 100%
            );
        ">

        {{-- Top glow --}}
        <div
            class="pointer-events-none absolute left-1/2 top-0 h-[500px] w-[900px] -translate-x-1/2 rounded-full bg-white/15 blur-[180px]">
        </div>

        {{-- Left glow --}}
        <div
            class="pointer-events-none absolute left-0 top-1/2 h-[900px] w-[900px] -translate-y-1/2 rounded-full bg-white/10 blur-[220px]">
        </div>

        {{-- Right glow --}}
        <div
            class="pointer-events-none absolute right-0 top-1/2 h-[900px] w-[900px] -translate-y-1/2 rounded-full bg-white/10 blur-[220px]">
        </div>

        {{-- Bottom glow --}}
        <div
            class="pointer-events-none absolute bottom-0 left-1/2 h-[400px] w-[800px] -translate-x-1/2 rounded-full bg-white/5 blur-[180px]">
        </div>

        {{-- Main Content --}}
        <div class="relative flex h-screen  items-center justify-center p-3 md:p-6 lg:p-8">

            {{ $slot }}

        </div>

    </main>

    @livewireScriptConfig

</body>

</html>
