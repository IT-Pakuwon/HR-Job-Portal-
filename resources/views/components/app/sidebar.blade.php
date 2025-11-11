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
                        class="bg-linear-to-r @if (in_array(Request::segment(1), ['dashboard'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                        <a class="@if (!in_array(Request::segment(1), ['dashboard'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="{{ route('dashboard') }}">
                            <div class="flex items-center">
                                <svg class="@if (in_array(Request::segment(1), ['dashboard'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M5.936.278A7.983 7.983 0 0 1 8 0a8 8 0 1 1-8 8c0-.722.104-1.413.278-2.064a1 1 0 1 1 1.932.516A5.99 5.99 0 0 0 2 8a6 6 0 1 0 6-6c-.53 0-1.045.076-1.548.21A1 1 0 1 1 5.936.278Z" />
                                    <path
                                        d="M6.068 7.482A2.003 2.003 0 0 0 8 10a2 2 0 1 0-.518-3.932L3.707 2.293a1 1 0 0 0-1.414 1.414l3.775 3.775Z" />
                                    <span
                                        class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                        Dashboard</span>
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
                            // 'showpersonnels/{hash}',
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
                            // 'showpersonnels/{hash}',
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
                                // 'showpersonnels/{hash}',
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
                                            // 'showpersonnels/{hash}',
                                            'jobapplicant',
                                            'changestos',
                                            'editChangesto',
                                            'showChangesto',
                                            'createChangesto',
                                            'showApplicant',
                                            'showcareers',
                                        ])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
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
                                <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('changestos')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('changestos') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">Request
                                            Additional</span>
                                    </a>
                                </li>
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
                                class="bg-linear-to-r @if (in_array(Request::segment(1), ['budgets', 'showbudgets', 'createBudget', 'editBudget'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                <a class="@if (!in_array(Request::segment(1), ['budgets', 'showbudgets', 'createBudget', 'editBudget'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="{{ route('budgets', 'showbudgets', 'createBudget', 'editBudget') }}">
                                    <div class="flex items-center">
                                        <svg class="@if (in_array(Request::segment(1), ['budgets', 'showbudgets', 'createBudget', 'editBudget'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d=" M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342
                                                                                                                                                                                                                                                        1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                        </svg>
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Budget</span>
                                    </div>
                                </a>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                                    'sppjs',
                                    'createsppjs',
                                    'showsppjs',
                                    'editsppjs',
                                    'sppbs',
                                    'createsppbs',
                                    'showsppbs',
                                    'editsppbs',
                                    'sppks',
                                    'createsppks',
                                    'showsppks',
                                    'editsppks',
                                    'sppts',
                                    'createsppts',
                                    'showsppts',
                                    'editsppts',
                                ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), [
                                    'sppjs',
                                    'createsppjs',
                                    'showsppjs',
                                    'editsppjs',
                                    'sppbs',
                                    'createsppbs',
                                    'showsppbs',
                                    'editsppbs',
                                    'sppks',
                                    'createsppks',
                                    'showsppks',
                                    'editsppks',
                                    'sppts',
                                    'createsppts',
                                    'showsppts',
                                    'editsppts',
                                ])
                                    ? 1
                                    : 0 }} }">
                                <a class="@if (
                                    !in_array(Request::segment(1), [
                                        'sppjs',
                                        'createsppjs',
                                        'showsppjs',
                                        'editsppjs',
                                        'sppbs',
                                        'createsppbs',
                                        'showsppbs',
                                        'editsppbs',
                                        'sppks',
                                        'createsppks',
                                        'showsppks',
                                        'editsppks',
                                        'sppts',
                                        'createsppts',
                                        'showsppts',
                                        'editsppts',
                                    ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'sppjs',
                                                    'createsppjs',
                                                    'showsppjs',
                                                    'editsppjs',
                                                    'sppbs',
                                                    'createsppbs',
                                                    'showsppbs',
                                                    'editsppbs',
                                                    'sppks',
                                                    'createsppks',
                                                    'showsppks',
                                                    'editsppks',
                                                    'sppts',
                                                    'createsppts',
                                                    'showsppts',
                                                    'editsppts',
                                                ])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" HEIGHT="16" WIDTH="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                            </svg>

                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Request
                                                Form</span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), [
                                                    'sppjs',
                                                    'createsppjs',
                                                    'showsppjs',
                                                    'editsppjs',
                                                    'sppbs',
                                                    'createsppbs',
                                                    'showsppbs',
                                                    'editsppbs',
                                                    'sppks',
                                                    'createsppks',
                                                    'showsppks',
                                                    'editsppks',
                                                    'sppts',
                                                    'createsppts',
                                                    'showsppts',
                                                    'editsppts',
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
                                            'sppjs',
                                            'createsppjs',
                                            'showsppjs',
                                            'editsppjs',
                                            'sppbs',
                                            'createsppbs',
                                            'showsppbs',
                                            'editsppbs',
                                            'sppks',
                                            'createsppks',
                                            'showsppks',
                                            'editsppks',
                                            'sppts',
                                            'createsppts',
                                            'showsppts',
                                            'editsppts',
                                        ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('sppbs', 'createsppbs', 'showsppbs', 'editsppbs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppbs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Barang</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('sppjs', 'createsppjs', 'showsppjs', 'editsppjs')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppjs') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Jasa</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('sppks')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppks') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Kendaran</span>
                                            </a>
                                        </li>
                                        <li class="mb-1 last:mb-0">
                                            <a class="@if (Route::is('sppts')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                href="{{ route('sppts') }}">
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-m font-medium duration-200 lg:opacity-0 2xl:opacity-100">SPP
                                                    Tenant</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), ['canvasssheet', 'assignlist', 'csjobs', 'cslist'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), ['canvasssheet', 'assignlist', 'csjobs', 'cslist']) ? 1 : 0 }} }">
                                <a class="@if (!in_array(Request::segment(1), ['canvasssheet', 'assignlist', 'csjobs', 'cslist'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['canvasssheet', 'cslist', 'csjobs', 'assignlist'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Canvass
                                                Sheets</span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), ['canvasssheet', 'cslist', 'csjobs', 'assignlist'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (!in_array(Request::segment(1), ['canvasssheet', 'cslist', 'csjobs', 'assignlist'])) {{ 'hidden' }} @endif mt-1 pl-8"
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
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), ['polist', 'receiptlist'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), ['polist', 'receiptlist']) ? 1 : 0 }} }">
                                <a class="@if (!in_array(Request::segment(1), ['polist', 'receiptlist'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['polist', 'receiptlist'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 3h1.386c.51 0 .955.343 1.09.835l.383 1.432M7.5 14.25h9.75m0 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-9.75 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm9.75 0L18.75 6.75H6.6M6.6 6.75 5.25 3M6.6 6.75h12.15a1.125 1.125 0 0 1 1.107 1.347l-1.005 4.5a1.125 1.125 0 0 1-1.107.903H7.5" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                Purchase
                                            </span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), ['polist', 'receiptlist'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (!in_array(Request::segment(1), ['polist', 'receiptlist'])) {{ 'hidden' }} @endif mt-1 pl-8"
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
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), ['wos'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), ['wos']) ? 1 : 0 }} }">
                                <a class="@if (!in_array(Request::segment(1), ['wos'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['wos'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 3h1.386c.51 0 .955.343 1.09.835l.383 1.432M7.5 14.25h9.75m0 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-9.75 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm9.75 0L18.75 6.75H6.6M6.6 6.75 5.25 3M6.6 6.75h12.15a1.125 1.125 0 0 1 1.107 1.347l-1.005 4.5a1.125 1.125 0 0 1-1.107.903H7.5" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                Work Order
                                            </span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), ['wos'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (!in_array(Request::segment(1), ['wos'])) {{ 'hidden' }} @endif mt-1 pl-8"
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
                            <li class="bg-linear-to-r @if (in_array(Request::segment(1), ['spbs'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                x-data="{ open: {{ in_array(Request::segment(1), ['spbs']) ? 1 : 0 }} }">
                                <a class="@if (!in_array(Request::segment(1), ['spbs'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['spbs'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 3h1.386c.51 0 .955.343 1.09.835l.383 1.432M7.5 14.25h9.75m0 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-9.75 0a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm9.75 0L18.75 6.75H6.6M6.6 6.75 5.25 3M6.6 6.75h12.15a1.125 1.125 0 0 1 1.107 1.347l-1.005 4.5a1.125 1.125 0 0 1-1.107.903H7.5" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                Warehouse
                                            </span>
                                        </div>
                                        <div
                                            class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                            <svg class="@if (in_array(Request::segment(1), ['spbs'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                    <ul class="@if (!in_array(Request::segment(1), ['spbs'])) {{ 'hidden' }} @endif mt-1 pl-8"
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
                                    <ul class="@if (!in_array(Request::segment(1), ['issuelist'])) {{ 'hidden' }} @endif mt-1 pl-8"
                                        :class="open ? 'block!' : 'hidden'">
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
                                class="bg-linear-to-r @if (in_array(Request::segment(1), ['imbudgets', 'showimbudgets', 'editimbudget'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                <a class="@if (!in_array(Request::segment(1), ['imbudgets', 'showimbudgets', 'editimbudget'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="{{ route('imbudgets', 'showimbudgets', 'editimbudget') }}">
                                    <div class="flex items-center">
                                        <svg class="@if (in_array(Request::segment(1), ['imbudgets', 'showimbudgets', 'editimbudget'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d=" M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342
                                                                                                                                                                                                                                                        1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                        </svg>
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">IM Budget</span>
                                    </div>
                                </a>
                            </li>
                            <li
                                class="bg-linear-to-r @if (in_array(Request::segment(1), ['bastlist', 'showimbudgets', 'editimbudget'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                <a class="@if (!in_array(Request::segment(1), ['bastlist', 'showimbudgets', 'editimbudget'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                    href="{{ route('bastlist', 'showimbudgets', 'editimbudget') }}">
                                    <div class="flex items-center">
                                        <svg class="@if (in_array(Request::segment(1), ['bastlist', 'showimbudgets', 'editimbudget'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d=" M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342
                                                                                                                                                                                                                                                        1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                        </svg>
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-m ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Bast List</span>
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
