<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <!-- Dashboard actions -->
        <div class="mb-4 sm:flex sm:items-center sm:justify-between">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-lg font-bold text-gray-800 md:text-lg dark:text-gray-100">Dashboard</h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col justify-start gap-2 sm:auto-cols-max sm:justify-end">
                <!-- Filter button -->
                <x-dropdown-filter align="right" />
                <!-- Datepicker built with flatpickr -->
                <x-datepicker />
            </div>
        </div>
        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">

            <!-- Line chart (Acme Plus) -->
            <x-dashboard.dashboard-card-01 :dataFeed="$dataFeed" />

            <!-- Line chart (Acme Advanced) -->
            <x-dashboard.dashboard-card-02 :dataFeed="$dataFeed" />

            <!-- Line chart (Acme Professional) -->
            <x-dashboard.dashboard-card-03 :dataFeed="$dataFeed" />

            <!-- Bar chart (Direct vs Indirect) -->
            <x-dashboard.dashboard-card-04 />

            <!-- Line chart (Real Time Value) -->
            <x-dashboard.dashboard-card-05 />

            <!-- Doughnut chart (Top Countries) -->
            <x-dashboard.dashboard-card-06 />

            <!-- Table (Top Channels) -->
            <x-dashboard.dashboard-card-07 />

            <!-- Line chart (Sales Over Time) -->
            <x-dashboard.dashboard-card-08 />

            <!-- Stacked bar chart (Sales VS Refunds) -->
            <x-dashboard.dashboard-card-09 />

            <!-- Card (Customers) -->
            <x-dashboard.dashboard-card-10 />

            <!-- Card (Reasons for Refunds) -->
            <x-dashboard.dashboard-card-11 />

            <!-- Card (Recent Activity) -->
            <x-dashboard.dashboard-card-12 />

            <!-- Card (Bawaan) -->
            <x-dashboard.dashboard-card-13 />

            <!--Calender Event -->
            <x-dashboard.dashboard-card-14 />

            <!-- Table without no Search and button -->
            <x-dashboard.dashboard-card-15 />

            <!-- Table with no Search and button -->
            <x-dashboard.dashboard-card-16 />

            <!-- Card (Income/Expenses) -->
            <x-dashboard.dashboard-card-17 />

            {{-- <!-- Calender Event -->
            <x-dashboard.dashboard-card-18 /> --}}

            <!-- Carousel News With without -->
            <x-dashboard.dashboard-card-19 />

            <!-- Carousel News With Image -->
            <x-dashboard.dashboard-card-20 />

            <!-- Carousel News only image -->
            <x-dashboard.dashboard-card-21 />

            <!-- Carousel News With without -->
            <x-dashboard.dashboard-card-22 />

            <!-- Carousel Count -->
            <x-dashboard.dashboard-card-23 />

            <!-- Carousel Bar Chart -->
            <x-dashboard.dashboard-card-24 />

            <!-- Carousel Bar Chart & Line Chart -->
            <x-dashboard.dashboard-card-25 />

            <!-- Carousel Single Area Chart -->
            <x-dashboard.dashboard-card-26 />

            <!-- Carousel Multiple Area Chart -->
            <x-dashboard.dashboard-card-27 />

            <!-- Carousel Multi Area Chart With Tooltip-->
            <x-dashboard.dashboard-card-28 />

            <!-- Carousel Curved Area Chart-->
            <x-dashboard.dashboard-card-29 />

            {{-- <!-- Carousel Multi Bar Chart-->
             <x-dashboard.dashboard-card-30 />

             <!-- Carousel Single Line Chart-->
             <x-dashboard.dashboard-card-31 />

             <!-- Carousel Multi Area Chart-->
             <x-dashboard.dashboard-card-32 />

             <!-- Carousel Curved Area Chart-->
             <x-dashboard.dashboard-card-33 />

             <!-- Carousel Horizontal bar Chart-->
             <x-dashboard.dashboard-card-34 />

             <!-- Carousel Doughnut Chart-->
             <x-dashboard.dashboard-card-35 />

             <!-- Carousel Bubble Chart-->
             <x-dashboard.dashboard-card-36 />

             <!-- Carousel Pie Chart-->
             <x-dashboard.dashboard-card-37 /> --}}



        </div>

    </div>
</x-app-layout>
