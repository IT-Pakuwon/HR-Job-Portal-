<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'groups' ? 'Master Group' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <!-- Dashboard actions -->
        <div class="mb-4 sm:flex sm:items-center sm:justify-between"></div>
        <!-- Breadcrumb dengan Dropdown -->
        {{-- <div class="flex items-center justify-between mb-4 sm:mb-0">               
                <h1 class="text-lg md:text-lg text-gray-800 dark:text-gray-100 font-bold">{{ $currentPage }}</h1>               
                <nav class="flex items-center text-gray-600 dark:text-gray-300">
                    <a href="#" class="hover:text-gray-900 dark:hover:text-white">Settings</a>
                    <span class="mx-2">/</span>
                 
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center text-gray-800 dark:text-gray-100 font-bold">
                            Master <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                      
                        <ul x-show="open" @click.away="open = false"
                            class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded   z-10">
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
            </div> --}}
        <div class="grid">
            <x-groups.groups-01 />
        </div>
    </div>
</x-app-layout>
