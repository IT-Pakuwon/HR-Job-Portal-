<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-8 sm:px-6 lg:px-8">

        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-lg font-bold text-gray-800 md:text-lg dark:text-gray-100">Dashboard</h1>
            </div>

            {{-- <!-- Right: Actions -->
            <div class="grid grid-flow-col justify-start gap-2 sm:auto-cols-max sm:justify-end">

                <!-- Filter button -->
                <x-dropdown-filter align="right" />

                <!-- Datepicker built with flatpickr -->
                <x-datepicker />

                <!-- Add view button -->
                <button
                    class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
                    <svg class="xs:hidden shrink-0 fill-current" width="16" height="16" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="max-xs:sr-only">Add View</span>
                </button>

            </div> --}}

        </div>

        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">
            {{-- <x-dashboard.dashboard-agenda :agendas="$agendas" /> --}}
            <x-dashboard.dashboard-approval :tr_approval="$tr_approval" />
            {{-- <x-dashboard.dashboard-news :news="$news" /> --}}
        </div>

    </div>
</x-app-layout>
