<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Pakuwon System') }}</title>

    <!-- ================= GLOBAL CSS / JS ================= -->
    @include('layouts.datatable')
    @include('layouts.status')
    @include('layouts.calendar')
    @include('layouts.sidebar')


    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/Logo Pakuwon.png') }}">

    <!-- ================= FONTS ================= -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- ================= CORE JS ================= -->
    <!-- jQuery (ONLY ONCE, MUST BE FIRST) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Alpine -->
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- ================= DATATABLES ================= -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Extensions -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">

    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

    <!-- Export dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- ================= UI PLUGINS ================= -->
    <!-- Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>


    <!-- ================= VITE ================= -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire -->
    @livewireStyles

    <!-- ================= DARK MODE INIT ================= -->
    <script>
        const isDark = localStorage.getItem('dark-mode') === 'true';
        if (isDark) {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        } else {
            document.documentElement.style.colorScheme = 'light';
        }
    </script>
</head>

<body class="font-inter bg-gray-100 text-gray-600 antialiased dark:bg-gray-900 dark:text-gray-400"
    x-data="{ sidebarOpen: false }">


    <!-- HEADER -->
    <x-app.header_new />

    <!-- ================= OFF-CANVAS SIDEBAR ================= -->
    <div x-cloak>

        <!-- BACKDROP -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/40"></div>

        <!-- SIDEBAR -->
        <aside x-show="sidebarOpen" x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full" @keydown.escape.window="sidebarOpen = false"
            class="fixed left-0 top-0 z-50 h-[100dvh] w-72 overflow-y-auto bg-white shadow-xl dark:bg-gray-800">

            <!-- ONLY CONTENT -->
            <div class="p-4">
                <x-app.sidebar_menu />
            </div>

        </aside>
    </div>


    <!-- ================= MAIN CONTENT ================= -->
    <main class="min-h-[calc(100dvh-56px)] overflow-y-auto p-4">
        {{ $slot }}
    </main>

    <!-- ================= LIVEWIRE ================= -->
    @livewireScriptConfig

    <!-- ================= DARK MODE TOGGLE ================= -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('light-switch');
            if (!toggle) return;

            toggle.checked = localStorage.getItem('dark-mode') === 'true';

            toggle.addEventListener('change', () => {
                if (toggle.checked) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.style.colorScheme = 'dark';
                    localStorage.setItem('dark-mode', 'true');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.style.colorScheme = 'light';
                    localStorage.setItem('dark-mode', 'false');
                }
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
