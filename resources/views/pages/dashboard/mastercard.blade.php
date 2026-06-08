<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
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
            <x-old-dashboard.dashboard-card-01 :dataFeed="$dataFeed" />

            <!-- Line chart (Acme Advanced) -->
            <x-old-dashboard.dashboard-card-02 :dataFeed="$dataFeed" />

            <!-- Line chart (Acme Professional) -->
            <x-old-dashboard.dashboard-card-03 :dataFeed="$dataFeed" />

            <!-- Bar chart (Direct vs Indirect) -->
            <x-old-dashboard.dashboard-card-04 />

            <!-- Line chart (Real Time Value) -->
            <x-old-dashboard.dashboard-card-05 />

            <!-- Doughnut chart (Top Countries) -->
            <x-old-dashboard.dashboard-card-06 />

            <!-- Table (Top Channels) -->
            <x-old-dashboard.dashboard-card-07 />

            <!-- Line chart (Sales Over Time) -->
            <x-old-dashboard.dashboard-card-08 />

            <!-- Stacked bar chart (Sales VS Refunds) -->
            <x-old-dashboard.dashboard-card-09 />

            <!-- Card (Customers) -->
            <x-old-dashboard.dashboard-card-10 />

            <!-- Card (Reasons for Refunds) -->
            <x-old-dashboard.dashboard-card-11 />

            <!-- Card (Recent Activity) -->
            <x-old-dashboard.dashboard-card-12 />

            <!-- Card (Bawaan) -->
            <x-old-dashboard.dashboard-card-13 />

            <!--Calender Event -->
            <x-old-dashboard.dashboard-card-14 />

            {{-- Cards 15-29 removed (components not available) --}}
            {{-- <x-old-dashboard.dashboard-card-15 />
            <x-old-dashboard.dashboard-card-16 />
            <x-old-dashboard.dashboard-card-17 />
            <x-old-dashboard.dashboard-card-18 />
            <x-old-dashboard.dashboard-card-19 />
            <x-old-dashboard.dashboard-card-20 />
            <x-old-dashboard.dashboard-card-21 />
            <x-old-dashboard.dashboard-card-22 />
            <x-old-dashboard.dashboard-card-23 />
            <x-old-dashboard.dashboard-card-24 />
            <x-old-dashboard.dashboard-card-25 />
            <x-old-dashboard.dashboard-card-26 />
            <x-old-dashboard.dashboard-card-27 />
            <x-old-dashboard.dashboard-card-28 />
            <x-old-dashboard.dashboard-card-29 /> --}}

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
