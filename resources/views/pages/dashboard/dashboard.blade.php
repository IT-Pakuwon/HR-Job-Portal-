<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <!-- Dashboard actions -->
        <div class="mb-4 sm:flex sm:items-center sm:justify-between">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-lg font-bold text-gray-800 md:text-lg dark:text-gray-100">Dashboard</h1>
            </div>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">
            <x-multidashboard.dashboard-approval :tr_approval="$tr_approval" :doctypes="$doctypes ?? []" />
        </div>

    </div>
</x-app-layout>
