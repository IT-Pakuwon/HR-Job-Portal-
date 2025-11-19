<div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 z-40 bg-gray-900/30 transition-opacity duration-200 lg:z-auto lg:hidden"
        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" x-cloak></div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="lg:flex! no-scrollbar lg:sidebar-expanded:w-64 {{ $variant === 'v2' ? 'border-r border-gray-200 dark:border-gray-700/60' : '  ' }} absolute left-0 top-0 z-40 flex h-[100dvh] w-64 shrink-0 flex-col overflow-y-scroll bg-white p-4 transition-all duration-200 ease-in-out lg:static lg:left-auto lg:top-auto lg:w-20 lg:translate-x-0 lg:overflow-y-auto dark:bg-gray-800"
        :class="{
            'max-lg:translate-x-0': sidebarOpen,
            'max-lg:-translate-x-64': !sidebarOpen,
            'lg:w-64': sidebarExpanded,
            'lg:w-21': !sidebarExpanded
        }"
        @click.outside="sidebarOpen = false" @keydown.escape.window="sidebarOpen = false">
        <!-- Sidebar header -->
        <div class="flex items-center justify-start gap-4 py-4 pr-3 sm:px-2">
            <button class="text-gray-500 hover:text-gray-400 lg:hidden" @click.stop="sidebarOpen = !sidebarOpen"
                aria-controls="sidebar" :aria-expanded="sidebarOpen">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>

            <button
                class="hidden items-center justify-center rounded-full bg-white p-2 text-gray-500 transition-colors duration-200 hover:bg-gray-100 lg:flex dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                @click="sidebarExpanded = !sidebarExpanded; localStorage.setItem('sidebarExpanded', sidebarExpanded)">
                <span class="sr-only">Expand / collapse sidebar</span>
                <svg class="h-5 w-5 transform transition-transform duration-200"
                    :class="{ 'rotate-180': sidebarExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <div class="flex items-center gap-3">
                <span :class="{ 'hidden': !sidebarExpanded && window.innerWidth >= 1024 }"
                    class="whitespace-nowrap text-xl font-bold uppercase text-gray-700 transition-opacity duration-200 dark:text-gray-100"
                    x-show="sidebarExpanded || window.innerWidth < 1024" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    APP System
                </span>
            </div>
        </div>
        <div class="space-y-8">
            <!-- Pages group -->
            <div>
                <ul class="mt-3">
                    <!-- Dashboard -->
                    <li
                        class="bg-linear-to-r @if (in_array(Request::segment(1), ['dashboard'])) from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                        <a class="@if (!in_array(Request::segment(1), ['dashboard'])) hover:text-gray-900 dark:hover:text-white @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="{{ route('dashboard') }}">
                            <div class="flex items-center">

                                <!-- NEW DASHBOARD ICON -->
                                <svg class="@if (in_array(Request::segment(1), ['dashboard'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3v18h18M7 13v6m4-10v10m4-14v14" />
                                </svg>

                                <span
                                    class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                    Dashboard
                                </span>
                            </div>
                        </a>
                    </li>

                    <!-- Structure Organization -->

                    <li class="py-2 pl-4 pr-3 text-xs font-semibold uppercase tracking-wider text-gray-500 last:mb-0"
                        :class="{ 'lg:block': sidebarExpanded, 'lg:hidden': !sidebarExpanded }">
                        Human Resources
                    </li>
                    {{-- <li
                        class="bg-linear-to-r @if (in_array(Request::segment(1), ['stos', 'showstos'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                        <a class="@if (!in_array(Request::segment(1), ['stos', 'showstos'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="{{ route('stos', 'showstos') }}">
                            <div class="flex items-center">
                                <svg class="@if (in_array(Request::segment(1), ['stos', 'showstos'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M11.92 6.851c.044-.027.09-.05.137-.07.481-.275.758-.68.908-1.256.126-.55.169-.81.357-2.058.075-.498.144-.91.217-1.264-4.122.75-7.087 2.984-9.12 6.284a18.087 18.087 0 0 0-1.985 4.585 17.07 17.07 0 0 0-.354 1.506c-.05.265-.076.448-.086.535a1 1 0 0 1-1.988-.226c.056-.49.209-1.312.502-2.357a20.063 20.063 0 0 1 2.208-5.09C5.31 3.226 9.306.494 14.913.004a1 1 0 0 1 .954 1.494c-.237.414-.375.993-.567 2.267-.197 1.306-.244 1.586-.392 2.235-.285 1.094-.789 1.853-1.552 2.363-.748 3.816-3.976 5.06-8.515 4.326a1 1 0 0 1 .318-1.974c2.954.477 4.918.025 5.808-1.556-.628.085-1.335.121-2.127.121a1 1 0 1 1 0-2c1.458 0 2.434-.116 3.08-.429Z" />
                                </svg>
                                <span
                                    class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">ORG
                                    Structure</span>
                            </div>
                        </a>
                    </li> --}}
                    <!-- Dashboard -->
                    <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                            'personnels',
                            'createPersonnel',
                            'editPersonnel',
                            'showpersonnels',
                            'jobapplicant',
                            'changestos',
                            'showApplicant',
                            'showcareers',
                            'changestos',
                            'editChangesto',
                            'showChangesto',
                            'createChangesto',
                        ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                        x-data="{ open: {{ in_array(Request::segment(1), [
                            'personnels',
                            'createPersonnel',
                            'editPersonnel',
                            'showpersonnels',
                            'jobapplicant',
                            'changestos',
                            'showApplicant',
                            'showcareers',
                            'changestos',
                            'editChangesto',
                            'showChangesto',
                            'createChangesto',
                        ])
                            ? 1
                            : 0 }} }">
                        <a class="@if (
                            !in_array(Request::segment(1), [
                                'personnels',
                                'createPersonnel',
                                'editPersonnel',
                                'showpersonnels',
                                'jobapplicant',
                                'showApplicant',
                                'showcareers',
                                'changestos',
                                'editChangesto',
                                'showChangesto',
                                'createChangesto',
                            ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="@if (in_array(Request::segment(1), [
                                            'personnels',
                                            'createPersonnel',
                                            'editPersonnel',
                                            'jobapplicant',
                                            'changestos',
                                            'editChangesto',
                                            'showChangesto',
                                            'createChangesto',
                                            'showApplicant',
                                            'showcareers',
                                        ])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18 18v-1a3 3 0 00-3-3h-.75m-4.5 0H9a3 3 0 00-3 3v1m12-10.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-7.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm0 10.5v-1a2.999 2.999 0 00-2.25-2.902m6.75 3.902v-1a2.999 2.999 0 00-2.25-2.902" />
                                    </svg>

                                    <span
                                        class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Recruitment</span>
                                </div>
                                <div
                                    class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                    <svg class="@if (in_array(Request::segment(1), ['personnels', 'jobpostings', 'changestos'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                            <ul class="@if (
                                !in_array(Request::segment(1), [
                                    'personnels',
                                    'createPersonnel',
                                    'editPersonnel',
                                    // 'showpersonnels/{hash}',
                                    'jobapplicant',
                                    'showcareers',
                                    'changestos',
                                    'editChangesto',
                                    'showChangesto',
                                    'createChangesto',
                                    'showApplicant',
                                ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                :class="open ? 'block!' : 'hidden'">
                                <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('personnels', 'createPersonnel', 'editPersonnel', 'showpersonnels/{hash}')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('personnels') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">PRF</span>
                                    </a>
                                </li>
                                <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('jobapplicant')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('jobapplicant') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">Applicant
                                            Portal</span>
                                    </a>
                                </li>
                                {{-- <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('changestos')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('changestos') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">Request
                                            Additional</span>
                                    </a>
                                </li> --}}
                            </ul>
                        </div>
                    </li>
                    @auth
                        @if (auth()->user()->username === 'rikiparahat' ||
                                auth()->user()->username === 'bedriamaail' ||
                                auth()->user()->username === 'sugiartoongkowijoyo' ||
                                auth()->user()->username === 'ariwibowo' ||
                                auth()->user()->username === 'junpianto' ||
                                auth()->user()->username === 'ariewibisono' ||
                                auth()->user()->username === 'adefahmi' ||
                                auth()->user()->username === 'williemhalim')
                            <li class="py-2 pl-4 pr-3 text-xs font-semibold uppercase tracking-wider text-gray-500 last:mb-0"
                                :class="{ 'lg:block': sidebarExpanded, 'lg:hidden': !sidebarExpanded }">
                                Purchasing
                            </li>
                            <li
                                class="bg-linear-to-r @if (in_array(Request::segment(1), ['budgets', 'showbudgets', 'createbudgets', 'editbudgets'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                <a class="@if (!in_array(Request::segment(1), ['budgets', 'showbudgets', 'createbudgets', 'editbudgets'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="{{ route('budgets') }}">
                                    <div class="flex items-center">
                                        <svg class="@if (in_array(Request::segment(1), ['budgets', 'showbudgets', 'createbudgets', 'editbudgets'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 12.75v-.75a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v.75m-19.5 0v3a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-3m-19.5 0h19.5M6 9.75v-.75a3 3 0 013-3h6a3 3 0 013 3v.75" />
                                        </svg>

                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Budget</span>
                                    </div>
                                </a>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                                    'sppbs',
                                    'createsppbs',
                                    'showsppbs',
                                    'editsppbs',
                                    'pdf_sppbs',
                                    'sppjs',
                                    'createsppjs',
                                    'showsppjs',
                                    'editsppjs',
                                    'pdf_sppjs',
                                    'createbqsppj',
                                    'showbqsppjs',
                                    'editbqsppjs',
                                    'sppks',
                                    'createsppks',
                                    'showsppks',
                                    'editsppks',
                                    'pdf_sppks',
                                    'showbqsppks',
                                    'editbqsppks',
                                    'createbqsppks',
                                    'sppts',
                                    'createsppts',
                                    'showsppts',
                                    'editsppts',
                                    'pdf_sppts',
                                ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), [
                                    'sppbs',
                                    'createsppbs',
                                    'showsppbs',
                                    'editsppbs',
                                    'pdf_sppbs',
                                    'sppjs',
                                    'createsppjs',
                                    'showsppjs',
                                    'editsppjs',
                                    'pdf_sppjs',
                                    'createbqsppj',
                                    'showbqsppjs',
                                    'editbqsppjs',
                                    'sppks',
                                    'createsppks',
                                    'showsppks',
                                    'editsppks',
                                    'pdf_sppks',
                                    'showbqsppks',
                                    'editbqsppks',
                                    'createbqsppks',
                                    'sppts',
                                    'createsppts',
                                    'showsppts',
                                    'editsppts',
                                    'pdf_sppts',
                                ])
                                    ? 1
                                    : 0 }} }">
                                <a class="@if (
                                    !in_array(Request::segment(1), [
                                        'sppbs',
                                        'createsppbs',
                                        'showsppbs',
                                        'editsppbs',
                                        'pdf_sppbs',
                                        'sppjs',
                                        'createsppjs',
                                        'showsppjs',
                                        'editsppjs',
                                        'pdf_sppjs',
                                        'createbqsppj',
                                        'showbqsppjs',
                                        'editbqsppjs',
                                        'sppks',
                                        'createsppks',
                                        'showsppks',
                                        'editsppks',
                                        'pdf_sppks',
                                        'showbqsppks',
                                        'editbqsppks',
                                        'createbqsppks',
                                        'sppts',
                                        'createsppts',
                                        'showsppts',
                                        'editsppts',
                                        'pdf_sppts',
                                    ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="{{ in_array(Request::segment(1), [
                                                'sppbs',
                                                'createsppbs',
                                                'showsppbs',
                                                'editsppbs',
                                                'pdf_sppbs',
                                                'sppjs',
                                                'createsppjs',
                                                'showsppjs',
                                                'editsppjs',
                                                'pdf_sppjs',
                                                'createbqsppj',
                                                'showbqsppjs',
                                                'editbqsppjs',
                                                'sppks',
                                                'createsppks',
                                                'showsppks',
                                                'editsppks',
                                                'pdf_sppks',
                                                'showbqsppks',
                                                'editbqsppks',
                                                'createbqsppks',
                                                'sppts',
                                                'createsppts',
                                                'showsppts',
                                                'editsppts',
                                                'pdf_sppts',
                                            ])
                                                ? 'text-violet-500'
                                                : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5.25H7a2.25 2.25 0 01-2.25-2.25V4.5A2.25 2.25 0 017 2.25h3.136a2.25 2.25 0 012.06 1.314l.278.586a1.5 1.5 0 001.358.85H17A2.25 2.25 0 0119.25 7v12A2.25 2.25 0 0117 21.25z" />
                                            </svg>

                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Request
                                                Form</span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'sppbs',
                                                    'createsppbs',
                                                    'showsppbs',
                                                    'editsppbs',
                                                    'pdf_sppbs',
                                                    'sppjs',
                                                    'createsppjs',
                                                    'showsppjs',
                                                    'editsppjs',
                                                    'pdf_sppjs',
                                                    'createbqsppj',
                                                    'showbqsppjs',
                                                    'editbqsppjs',
                                                    'sppks',
                                                    'createsppks',
                                                    'showsppks',
                                                    'editsppks',
                                                    'pdf_sppks',
                                                    'showbqsppks',
                                                    'editbqsppks',
                                                    'createbqsppks',
                                                    'sppts',
                                                    'createsppts',
                                                    'showsppts',
                                                    'editsppts',
                                                    'pdf_sppts',
                                                ])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (!in_array(Request::segment(1), ['sppbs', 'createsppbs', 'showsppbs', 'editsppbs', 'pdf_sppbs'])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('sppbs', 'createsppbs', 'showsppbs', 'editsppbs', 'pdf_sppbs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppbs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Barang</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is(
                                                    'sppjs',
                                                    'createsppjs',
                                                    'showsppjs',
                                                    'editsppjs',
                                                    'pdf_sppjs',
                                                    'createbqsppj',
                                                    'showbqsppjs',
                                                    'editbqsppjs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppjs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Jasa</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is(
                                                    'sppks',
                                                    'createsppks',
                                                    'showsppks',
                                                    'editsppks',
                                                    'pdf_sppks',
                                                    'showbqsppks',
                                                    'editbqsppks',
                                                    'createbqsppks')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppks') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Kendaran</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('sppts', 'createsppts', 'showsppts', 'editsppts', 'pdf_sppts')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppts') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Tenant</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                                    'canvasssheet',
                                    'assignlist',
                                    'csjobs', // main page
                                    'cslist',
                                ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), [
                                    'canvasssheet',
                                    'assignlist',
                                    'csjobs', // main page
                                    'cslist',
                                ])
                                    ? 1
                                    : 0 }} }">
                                <a class="@if (
                                    !in_array(Request::segment(1), [
                                        'canvasssheet',
                                        'assignlist',
                                        'csjobs', // main page
                                        'cslist',
                                    ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="{{ in_array(Request::segment(1), [
                                                'canvasssheet',
                                                'assignlist',
                                                'csjobs', // main page
                                                'cslist',
                                            ])
                                                ? 'text-violet-500'
                                                : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 3h18M3 9h18M3 15h18M3 21h18M3 3v18M9 3v18M15 3v18M21 3v18" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Canvass
                                                Sheets</span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'canvasssheet',
                                                    'assignlist',
                                                    'csjobs', // main page
                                                    'cslist',
                                                ])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (
                                        !in_array(Request::segment(1), [
                                            'canvasssheet',
                                            'assignlist',
                                            'csjobs', // main page
                                            'cslist',
                                        ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('assignlist')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('assignlist') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">Assign
                                                    List</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('csjobs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('csjobs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">CS
                                                    Jobs</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('cslist')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('cslist') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">CS
                                                    List</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                                    'polist',
                                    'showpo',
                                    'receiptlist',
                                    'showreceipt',
                                    'editreceipts',
                                    'receipt',
                                    'receipt-return',
                                    'pdf_po',
                                ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), [
                                    'polist',
                                    'showpo',
                                    'receiptlist',
                                    'showreceipt',
                                    'editreceipts',
                                    'receipt',
                                    'receipt-return',
                                    'pdf_po',
                                ])
                                    ? 1
                                    : 0 }} }">
                                <a class="@if (
                                    !in_array(Request::segment(1), [
                                        'polist',
                                        'showpo',
                                        'receiptlist',
                                        'showreceipt',
                                        'editreceipts',
                                        'receipt',
                                        'receipt-return',
                                        'pdf_po',
                                    ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="{{ in_array(Request::segment(1), [
                                                'polist',
                                                'showpo',
                                                'receiptlist',
                                                'showreceipt',
                                                'editreceipts',
                                                'receipt',
                                                'receipt-return',
                                                'pdf_po',
                                            ])
                                                ? 'text-violet-500'
                                                : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 3h1.5l1.5 12h12l1.5-9H6.75m3 15a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm9 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                Purchase
                                            </span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'polist',
                                                    'showpo',
                                                    'receiptlist',
                                                    'showreceipt',
                                                    'editreceipts',
                                                    'receipt',
                                                    'receipt-return',
                                                    'pdf_po',
                                                ])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (
                                        !in_array(Request::segment(1), [
                                            'polist',
                                            'showpo',
                                            'receiptlist',
                                            'showreceipt',
                                            'editreceipts',
                                            'receipt',
                                            'receipt-return',
                                            'pdf_po',
                                        ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('polist')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('polist') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">PO
                                                    List</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('receiptlist')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('receiptlist') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">Receipt
                                                    List</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                                    'wos',
                                    'showwos',
                                    'editwos',
                                    'wo', // approve / reject / revise
                                    'wojobs',
                                    'pdf_wos',
                                ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), [
                                    'wos',
                                    'showwos',
                                    'editwos',
                                    'wo', // approve / reject / revise
                                    'wojobs',
                                    'pdf_wos',
                                ])
                                    ? 1
                                    : 0 }} }">
                                <a class="@if (
                                    !in_array(Request::segment(1), [
                                        'wos',
                                        'showwos',
                                        'editwos',
                                        'wo', // approve / reject / revise
                                        'wojobs',
                                        'pdf_wos',
                                    ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="{{ in_array(Request::segment(1), [
                                                'wos',
                                                'showwos',
                                                'editwos',
                                                'wo', // approve / reject / revise
                                                'wojobs',
                                                'pdf_wos',
                                            ])
                                                ? 'text-violet-500'
                                                : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.232 5.232a3 3 0 014.243 4.243l-7.5 7.5a3 3 0 01-4.243 0l-.707-.707a3 3 0 010-4.243l7.5-7.5z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                Work Order
                                            </span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'wos',
                                                    'showwos',
                                                    'editwos',
                                                    'wo', // approve / reject / revise
                                                    'wojobs',
                                                    'pdf_wos',
                                                ])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (
                                        !in_array(Request::segment(1), [
                                            'wos',
                                            'showwos',
                                            'editwos',
                                            'wo', // approve / reject / revise
                                            'wojobs',
                                            'pdf_wos',
                                        ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('wos')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('wos') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">WO
                                                    List</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('wojobs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('wojobs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">WO
                                                    Jobs</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                                    'spbs',
                                    'showspbs',
                                    'editspbs',
                                    'spb',
                                    'pdf_spbs',
                                    'issuelist',
                                    'showissue',
                                    'editissues',
                                    'issue',
                                    'pdf_issues',
                                ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), [
                                    'spbs',
                                    'showspbs',
                                    'editspbs',
                                    'spb',
                                    'pdf_spbs',
                                    'issuelist',
                                    'showissue',
                                    'editissues',
                                    'issue',
                                    'pdf_issues',
                                ])
                                    ? 1
                                    : 0 }} }">
                                <a class="@if (
                                    !in_array(Request::segment(1), [
                                        'spbs',
                                        'showspbs',
                                        'editspbs',
                                        'spb',
                                        'pdf_spbs',
                                        'issuelist',
                                        'showissue',
                                        'editissues',
                                        'issue',
                                        'pdf_issues',
                                    ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="{{ in_array(Request::segment(1), [
                                                'spbs',
                                                'showspbs',
                                                'editspbs',
                                                'spb',
                                                'pdf_spbs',
                                                'issuelist',
                                                'showissue',
                                                'editissues',
                                                'issue',
                                                'pdf_issues',
                                            ])
                                                ? 'text-violet-500'
                                                : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 2.25l8.25 4.5v10.5L12 21.75l-8.25-4.5V6.75L12 2.25z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                Warehouse
                                            </span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'spbs',
                                                    'showspbs',
                                                    'editspbs',
                                                    'spb',
                                                    'pdf_spbs',
                                                    'issuelist',
                                                    'showissue',
                                                    'editissues',
                                                    'issue',
                                                    'pdf_issues',
                                                ])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (
                                        !in_array(Request::segment(1), [
                                            'spbs',
                                            'showspbs',
                                            'editspbs',
                                            'spb',
                                            'pdf_spbs',
                                            'issuelist',
                                            'showissue',
                                            'editissues',
                                            'issue',
                                            'pdf_issues',
                                        ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('spbs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('spbs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPB
                                                    List</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (
                                        !in_array(Request::segment(1), [
                                            'spbs',
                                            'showspbs',
                                            'editspbs',
                                            'spb',
                                            'pdf_spbs',
                                            'issuelist',
                                            'showissue',
                                            'editissues',
                                            'issue',
                                            'pdf_issues',
                                        ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('spbjobs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('spbjobs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPB
                                                    Jobs</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('issuelist')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('issuelist') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">Issue
                                                    List</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li
                                class="bg-linear-to-r @if (in_array(Request::segment(1), ['imbudgets', 'showimbudgets', 'editimbudgets', 'imbudget', 'pdf_imbudgets'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                <a class="@if (!in_array(Request::segment(1), ['imbudgets', 'showimbudgets', 'editimbudgets', 'imbudget', 'pdf_imbudgets'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="{{ route('imbudgets') }}">
                                    <div class="flex items-center">
                                        <svg class="{{ in_array(Request::segment(1), ['imbudgets', 'showimbudgets', 'editimbudgets', 'imbudget', 'pdf_imbudgets'])
                                            ? 'text-violet-500'
                                            : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 7.5v9m3-9v9m-6 0h12m-12-9h12M6 4.5h12v15H6z" />
                                        </svg>

                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">IM
                                            Budget</span>
                                    </div>
                                </a>
                            </li>
                            <li
                                class="bg-linear-to-r @if (in_array(Request::segment(1), ['bastlist', 'showbast', 'editbasts', 'bast', 'pdf_bast', 'pdf_bast_vendor'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                <a class="@if (!in_array(Request::segment(1), ['bastlist', 'showbast', 'editbasts', 'bast', 'pdf_bast', 'pdf_bast_vendor'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="{{ route('bastlist') }}">
                                    <div class="flex items-center">
                                        <svg class="{{ in_array(Request::segment(1), ['bastlist', 'showbast', 'editbasts', 'bast', 'pdf_bast', 'pdf_bast_vendor'])
                                            ? 'text-violet-500'
                                            : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m2-3H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2z" />
                                        </svg>

                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Bast
                                            List</span>
                                    </div>
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>

        <!-- Expand / collapse button -->
        <div class="mt-auto hidden justify-end pt-3 lg:inline-flex 2xl:hidden">
            <div class="w-12 py-2 pl-4 pr-3">
                <button
                    class="text-gray-400 transition-colors hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                    @click="sidebarExpanded = !sidebarExpanded; localStorage.setItem('sidebarExpanded', sidebarExpanded)">
                    <span class="sr-only">Expand / collapse sidebar</span>
                    <svg class="shrink-0 fill-current text-gray-400 transition-transform duration-200 dark:text-gray-500"
                        :class="sidebarExpanded ? 'rotate-180' : 'rotate-0'" xmlns="http://www.w3.org/2000/svg"
                        width="16" height="16" viewBox="0 0 16 16">
                        <path
                            d="M15 16a1 1 0 0 1-1-1V1a1 1 0 1 1 2 0v14a1 1 0 0 1-1 1ZM8.586 7H1a1 1 0 1 0 0 2h7.586l-2.793 2.793a1 1 0 1 0 1.414 1.414l4.5-4.5A.997.997 0 0 0 12 8.01M11.924 7.617a.997.997 0 0 0-.217-.324l-4.5-4.5a1 1 0 0 0-1.414 1.414L8.586 7M12 7.99a.996.996 0 0 0-.076-.373Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
