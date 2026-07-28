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
    @include('layouts.manual')
    @include('layouts.calendar')


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>

    <!-- Frappe Gantt (JS bundled via npm/app.js as window.FrappeGantt; only the
         CSS is loaded here — the package's exports map doesn't expose a CSS
         subpath for Vite to bundle) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@1.2.2/dist/frappe-gantt.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <!--Calendar -->

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
    x-data="{
        sidebarOpen: false,
        manualOpen: false,
        activeModule: null,
        activeMenu: null
    }">


    @if(session('impersonate_original_id'))
        <div class="sticky top-0 z-60 flex flex-wrap items-center justify-center gap-3 bg-black px-4 py-2 text-center text-sm font-semibold text-white">
            <span>🔑 You are logged in as <strong>{{ auth()->user()->name ?? auth()->user()->username }}</strong>.</span>
            <form action="{{ route('users.stop-impersonate') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="rounded bg-white/20 px-3 py-1 transition hover:bg-white/30">
                    Return to my account
                </button>
            </form>
        </div>
    @endif

    <!-- HEADER -->
    <x-app.header_new />

    <!-- ================= OFF-CANVAS SIDEBAR ================= -->
    {{-- Backdrop, aside shell, Esc-to-close, and the sidebar content itself all live
         together in sidebar_menu.blade.php — do not re-wrap it here, it was previously
         nested inside a duplicate backdrop/aside pair which rendered the drawer twice. --}}
    <x-app.sidebar_menu />


    <!-- ================= MAIN CONTENT ================= -->
    <main class="min-h-[calc(100dvh-56px)] overflow-y-auto p-2">
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
        document.addEventListener('keydown', function(e) {

            if (e.key !== 'Enter') return;

            const target = e.target;
            const form = target.closest('form');
            if (!form) return; // not inside form

            const tag = target.tagName;
            const type = (target.type || '').toLowerCase();

            // ✅ Allow Enter inside textarea (new line)
            if (tag === 'TEXTAREA') return;

            // ✅ Allow Enter for buttons
            if (tag === 'BUTTON') return;

            // ✅ Allow Enter for file input
            if (type === 'file') return;

            e.preventDefault();

            const inputs = Array.from(
                form.querySelectorAll('input, select, textarea')
            ).filter(el =>
                !el.disabled &&
                el.type !== 'hidden' &&
                el.offsetParent !== null // visible only
            );

            const index = inputs.indexOf(target);

            if (index > -1 && index + 1 < inputs.length) {
                inputs[index + 1].focus();
            }
        });
    </script>

    @stack('scripts')

    {{-- Global session-expired handler: redirect to login on 401 for any AJAX call --}}
    <script>
        $(document).ajaxError(function (event, xhr) {
            if (xhr.status === 401) {
                window.location.href = '{{ route('login') }}';
            }
        });
    </script>

</body>

</html>
