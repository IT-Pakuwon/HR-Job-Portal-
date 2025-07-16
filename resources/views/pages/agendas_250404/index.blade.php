<x-app-layout>
    @php
    $currentPage = Route::currentRouteName() == 'tasks' ? 'Master Task' : '';
    @endphp
    <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto">
        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8"></div>
        <!-- Breadcrumb dengan Dropdown -->
            <div class="flex items-center justify-between mb-4 sm:mb-0">
                <!-- Title Page -->
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ $currentPage }}</h1>
                <!-- Breadcrumb -->
                <nav class="flex items-center text-gray-600 dark:text-gray-300">
                    <a href="#" class="hover:text-gray-900 dark:hover:text-white">Settings</a>
                    <span class="mx-2">/</span>

                    <!-- Dropdown untuk Master -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-gray-800 dark:text-gray-100 font-bold">
                            Master <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <ul x-show="open" @click.away="open = false"
                            class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded shadow-lg z-10">
                            <li><a href="{{ route('account') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">My Account</a></li>
                            <li><a href="{{ route('screens') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Screen</a></li>
                            <li><a href="{{ route('applications') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Application</a></li>
                            <li><a href="{{ route('groups') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Group</a></li>
                            <li><a href="{{ route('mastercard') }}" class="block px-4 py-2 hover:bg-gray-200 dark:hover:bg-gray-700">Master Card</a></li>
                        </ul>
                    </div>
                    <span class="mx-2">/</span>
                    <span class="text-gray-800 dark:text-gray-100 font-bold">{{ $currentPage }}</span>
                </nav>
            </div>
            <div class="grid">
                {{-- <x-agendas.agendas-01 /> --}}
        </div>
    </div>
</x-app-layout>
