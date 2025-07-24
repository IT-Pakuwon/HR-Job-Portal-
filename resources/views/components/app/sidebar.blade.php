<div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 z-40 bg-gray-900/30 transition-opacity duration-200 lg:z-auto lg:hidden"
        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" x-cloak></div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="lg:flex! no-scrollbar lg:sidebar-expanded:w-64 {{ $variant === 'v2' ? 'border-r border-gray-200 dark:border-gray-700/60' : 'shadow-xs' }} absolute left-0 top-0 z-40 flex h-[100dvh] w-64 shrink-0 flex-col overflow-y-scroll bg-white p-4 transition-all duration-200 ease-in-out lg:static lg:left-auto lg:top-auto lg:w-20 lg:translate-x-0 lg:overflow-y-auto dark:bg-gray-800"
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
                <a class="block" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/Logo Pakuwon.png') }}" class="h-8 w-8 object-contain"
                        alt="Pakuwon Logo" />
                </a>
                <span :class="{ 'hidden': !sidebarExpanded && window.innerWidth >= 1024 }"
                    class="whitespace-nowrap text-xl font-bold uppercase text-gray-700 transition-opacity duration-200 dark:text-gray-100"
                    x-show="sidebarExpanded || window.innerWidth < 1024" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    HR System
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
                    <li
                        class="bg-linear-to-r @if (in_array(Request::segment(1), ['stos'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                        <a class="@if (!in_array(Request::segment(1), ['stos'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="{{ route('stos') }}">
                            <div class="flex items-center">
                                <svg class="@if (in_array(Request::segment(1), ['stos'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
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
                    </li>
                    <!-- Dashboard -->
                    <li class="bg-linear-to-r @if (in_array(Request::segment(1), ['personnels', 'jobapplicant', 'careers'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                        x-data="{ open: {{ in_array(Request::segment(1), ['personnels', 'jobapplicant', 'careers']) ? 1 : 0 }} }">
                        <a class="@if (!in_array(Request::segment(1), ['personnels', 'jobapplicant', 'careers'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="@if (in_array(Request::segment(1), ['personnels', 'jobpostings', 'careers'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
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
                                    <svg class="@if (in_array(Request::segment(1), ['personnels', 'personnel-requisition', 'job-posting', 'applicant-portal'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                            <ul class="@if (!in_array(Request::segment(1), ['personnels', 'personnel-requisition', 'job-posting', 'applicant-portal'])) {{ 'hidden' }} @endif mt-1 pl-8"
                                :class="open ? 'block!' : 'hidden'">
                                <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('personnels')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
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
                            </ul>
                        </div>
                    </li>
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
