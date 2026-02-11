@php $noBack = true; @endphp
<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <!-- HEADER -->
    <x-app.header variant="v2" /> --}}
    {{-- ================= LEFT : APPLICATION MODULES ================= --}}
    {{-- 
        <div class="grid h-[50%] grid-cols-1 gap-4 md:gap-6 lg:grid-cols-1 xl:grid-cols-1">
            <div class="flex flex-col gap-4 rounded-xl border bg-white p-4 dark:border-gray-600 dark:bg-gray-800">


                <!-- TITLE -->
                <div class="shrink-0">
                    <h1 class="text-lg font-bold text-gray-900 md:text-lg dark:text-gray-100">Application Modules
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Select a module to continue</p>
                </div>

                @php
                    /* MOCK COUNTS */ $counts = [
                        'recruitment' => 3,
                        'applicants' => 5,
                        'purchase' => 2,
                        'warehouse' => 1,
                        'request' => 0,
                        'workorder' => 0,
                        'bast' => 0,
                        'rfca' => 0,
                        'calr' => 0,
                    ];
                    $card =
                        ' group relative cursor-pointer rounded-xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm flex flex-col items-center justify-center text-center ';
                    $icon = 'mb-2 text-lg';
                    $label = ' text-sm  font-semibold text-gray-800';
                    $badge =
                        ' absolute top-2 right-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5  text-sm  font-semibold text-white shadow ';
                @endphp
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">

                    <div class="group relative">
                        <div
                            class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            @if ($counts['recruitment'] > 0)
                                <span
                                    class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                    {{ $counts['recruitment'] }}
                                </span>
                            @endif
                            <div class="mb-2 text-lg">👥</div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recruitment</div>
                        </div>

                        <div
                            class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                            <a href="{{ route('personnels') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                PRF
                            </a>

                            <a href="{{ route('jobapplicant') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                Applicant Portal
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('applicants') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['applicants'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                {{ $counts['applicants'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">🧾</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Applicants</div>
                    </a>

                    <div class="group relative">
                        <div
                            class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            @if ($counts['purchase'] > 0)
                                <span
                                    class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                    {{ $counts['purchase'] }}
                                </span>
                            @endif
                            <div class="mb-2 text-lg">🛒</div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Purchase</div>
                        </div>

                        <div
                            class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                            <a href="{{ route('polist') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                PO List
                            </a>

                            <a href="{{ route('receiptlist') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                Receipt List
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('wos') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">🛠️</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Work Order</div>
                    </a>

                    <a href="{{ route('spbs') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['warehouse'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                {{ $counts['warehouse'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">📦</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Warehouse</div>
                    </a>

                    <a href="{{ route('bastlist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">📑</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">BAST</div>
                    </a>

                    <a href="{{ route('rfcalist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">💵</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">RFCA</div>
                    </a>

                    <a href="{{ route('calrlist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">📝</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">CALR</div>
                    </a>

                    <a href="https://mail3.pakuwon.com/" target="#"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">


                        <svg class="h-8 w-8 text-indigo-500 transition group-hover:scale-110" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75m19.5 0L12 13.5 2.25 6.75" />
                        </svg>

                        <span class="mt-3 text-sm font-medium text-gray-700">
                            Email
                        </span>
                    </a>

                    <a href="https://pakuwon.isort.id/login" target="_blank"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">

                        <svg class="h-8 w-8 text-indigo-500 transition group-hover:scale-110" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v18h18M7.5 15v-6m4.5 6V6m4.5 9v-3" />
                        </svg>

                        <span class="mt-3 text-sm font-medium text-gray-700">
                            ISort
                        </span>
                    </a>


                </div>
            </div>

        </div> --}}

    <div class="flex flex-col gap-2">
        <div class="grid">
            <!-- LEFT: Notifications -->
            <div class="overflow-hidden">
                @include('partials.menu')
            </div>
        </div>
        <div class="grid grid-cols-1 gap-2 md:gap-2 lg:grid-cols-2">

            <!-- LEFT: Notifications -->
            <div class="overflow-hidden">
                @include('partials.notification')
            </div>

            <!-- RIGHT: Calendar -->
            <div class="overflow-hidden">
                @include('partials.calendar-widget')
            </div>

        </div>
        <div class="grid grid-cols-1 gap-2 md:gap-2">

            <!-- LEFT: Notifications -->
            <div class="overflow-hidden">
                @include('partials.summarydash')
            </div>
            {{-- 
            <!-- RIGHT: Calendar -->
            <div class="overflow-hidden">
                @include('partials.calendar-widget')
            </div> --}}
        </div>

    </div>

    </div>




</x-app-layout>
