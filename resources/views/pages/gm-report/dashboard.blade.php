<x-app-layout>

 <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ROW 1 --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3 lg:grid-cols-3">
            @include('components.dashboard.dashboard-card-01')
            @include('components.dashboard.dashboard-card-02')
            @include('components.dashboard.dashboard-card-03')
        </div>

        {{-- ROW 6 --}}
        <div class="grid grid-cols-1 gap-6">
            @include('components.dashboard.dashboard-card-14')
        </div>

    </div>

</x-app-layout>
