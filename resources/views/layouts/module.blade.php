@php $noBack = true; @endphp

<x-app-layout>

    <!-- HEADER -->
    <x-app.header variant="v2" />

    <!-- CLEAN FULL-PAGE WRAPPER -->
    <div class="flex w-full flex-col items-center">

        <!-- Title -->
        <div class="mb-12 mt-14 w-full max-w-6xl text-center">
            <h1 class="text-lg font-bold tracking-tight text-gray-900 dark:text-gray-100">
                Application Modules
            </h1>
        </div>

        <!-- GRID CONTAINER -->
        <div class="grid w-full max-w-7xl grid-cols-2 gap-8 sm:grid-cols-3 md:grid-cols-5">

            @php
                $card = "relative group cursor-pointer rounded-xl 
                        bg-white dark:bg-gray-800 
                        border border-gray-100 dark:border-gray-700
                        shadow-sm hover:shadow-md
                        transition-all duration-200 p-4 flex flex-col";

                $iconWrap = "h-12 w-12 rounded-xl 
                            bg-[#EDEAFF] dark:bg-[#3A3550]
                            flex items-center justify-center text-lg";

                $label = 'text-sm font-semibold text-gray-800 dark:text-gray-100';
            @endphp


            <!-- Recruitment -->
            <div class="group relative">
                <div class="{{ $card }}">
                    <div class="flex items-center gap-6">
                        <div class="{{ $iconWrap }}">👥</div>
                        <div class="{{ $label }}">Recruitment</div>
                    </div>
                </div>

                <!-- Dropdown -->
                <div
                    class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 overflow-hidden rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ route('personnels') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">
                        PRF
                    </a>
                    <a href="{{ route('jobapplicant') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">
                        Applicant Portal
                    </a>
                </div>
            </div>


            <!-- Applicants -->
            <a href="{{ route('applicants') }}" class="{{ $card }}">
                <div class="flex items-center gap-6">
                    <div class="{{ $iconWrap }}">🧾</div>
                    <div class="{{ $label }}">Applicants</div>
                </div>
            </a>


            <!-- Purchase -->
            <div class="group relative">
                <div class="{{ $card }}">
                    <div class="flex items-center gap-6">
                        <div class="{{ $iconWrap }}">🛒</div>
                        <div class="{{ $label }}">Purchase</div>
                    </div>
                </div>

                <div
                    class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 overflow-hidden rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-700 dark:bg-gray-800">
                    <a href="{{ route('polist') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">
                        PO List
                    </a>
                    <a href="{{ route('receiptlist') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">
                        Receipt List
                    </a>
                </div>
            </div>


            <!-- Request Form -->
            <div class="group relative">
                <div class="{{ $card }}">
                    <div class="flex items-center gap-6">
                        <div class="{{ $iconWrap }}">📝</div>
                        <div class="{{ $label }}">Request Form</div>
                    </div>
                </div>

                <div
                    class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-48 -translate-x-1/2 scale-95 overflow-hidden rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-700 dark:bg-gray-800">

                    <a href="{{ route('sppbs') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">SPP Barang</a>
                    <a href="{{ route('sppjs') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">SPP Jasa</a>
                    <a href="{{ route('sppks') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">SPP Kendaraan</a>
                    <a href="{{ route('sppts') }}"
                        class="block px-4 py-3 text-xs hover:bg-gray-100 dark:hover:bg-gray-700">SPP Tenant</a>
                </div>
            </div>


            <!-- Work Order -->
            <a href="{{ route('wos') }}" class="{{ $card }}">
                <div class="flex items-center gap-6">
                    <div class="{{ $iconWrap }}">🛠️</div>
                    <div class="{{ $label }}">Work Order</div>
                </div>
            </a>


            <!-- Warehouse -->
            <a href="{{ route('spbs') }}" class="{{ $card }}">
                <div class="flex items-center gap-6">
                    <div class="{{ $iconWrap }}">📦</div>
                    <div class="{{ $label }}">Warehouse</div>
                </div>
            </a>


            <!-- BAST -->
            <a href="{{ route('bastlist') }}" class="{{ $card }}">
                <div class="flex items-center gap-6">
                    <div class="{{ $iconWrap }}">📑</div>
                    <div class="{{ $label }}">BAST</div>
                </div>
            </a>


            <!-- RFCA -->
            <a href="{{ route('rfcalist') }}" class="{{ $card }}">
                <div class="flex items-center gap-6">
                    <div class="{{ $iconWrap }}">💵</div>
                    <div class="{{ $label }}">RFCA</div>
                </div>
            </a>


            <!-- CALR -->
            <a href="{{ route('calrlist') }}" class="{{ $card }}">
                <div class="flex items-center gap-6">
                    <div class="{{ $iconWrap }}">📝</div>
                    <div class="{{ $label }}">CALR</div>
                </div>
            </a>

        </div>
    </div>

</x-app-layout>
