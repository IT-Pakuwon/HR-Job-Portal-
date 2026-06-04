<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- Report Selector --}}
        {{-- Tailwind safelist: lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 --}}
        @php
            $lgCols = match(true) {
                $tabCount <= 1 => 'lg:grid-cols-1',
                $tabCount == 2 => 'lg:grid-cols-2',
                $tabCount == 3 => 'lg:grid-cols-3',
                $tabCount == 4 => 'lg:grid-cols-4',
                default        => 'lg:grid-cols-6',
            };
        @endphp
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 {{ $lgCols }}">

            {{-- Meeting Room --}}
            @if($hasCSACCESS)
            <a href="#" data-report="meeting-room"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-lg">
                        🏢
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Meeting Room</p>
                        <p class="text-xs text-gray-500">Room booking & usage</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Meeting Teams / Zoom --}}
            @if($hasADMIN)
            <a href="#" data-report="meeting-online"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-lg">
                        💻
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Meeting Teams / Zoom</p>
                        <p class="text-xs text-gray-500">Online meeting activity</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Booking Operational Car --}}
            @if($hasGAACCESS)
            <a href="#" data-report="operational-car"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 text-lg">
                        🚗
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Operational Car</p>
                        <p class="text-xs text-gray-500">Vehicle booking & usage</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Voucher Taxi --}}
            @if($hasGAACCESS)
            <a href="#" data-report="voucher-taxi"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-100 text-lg">
                        🎫
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Voucher Taxi</p>
                        <p class="text-xs text-gray-500">Taxi voucher usage</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Free Parking --}}
            @if($hasGAACCESS)
            <a href="#" data-report="free-parking"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-lg">
                        🅿️
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Free Parking</p>
                        <p class="text-xs text-gray-500">Parking access & usage</p>
                    </div>
                </div>
            </a>
            @endif

            {{-- Car Expense --}}
            @if($hasGAACCESS)
            <a href="#" data-report="car-expense"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-lg">
                        💰
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Car Expense</p>
                        <p class="text-xs text-gray-500">Vehicle cost & expense</p>
                    </div>
                </div>
            </a>
            @endif

        </div>

        {{-- REPORT CONTENT --}}
        <div id="reportContainer">

            @if($hasCSACCESS)
            <div id="report-meeting-room" @class(['hidden' => $defaultReport !== 'meeting-room'])>
                @include('pages.report-ga.meeting-room')
            </div>
            @endif

            @if($hasADMIN)
            <div id="report-meeting-online" @class(['hidden' => $defaultReport !== 'meeting-online'])>
                @include('pages.report-ga.meeting-online')
            </div>
            @endif

            @if($hasGAACCESS)
            <div id="report-operational-car" @class(['hidden' => $defaultReport !== 'operational-car'])>
                @include('pages.report-ga.operational-car')
            </div>

            <div id="report-voucher-taxi" class="hidden">
                @include('pages.report-ga.voucher-taxi')
            </div>

            <div id="report-free-parking" class="hidden">
                @include('pages.report-ga.free-parking')
            </div>

            <div id="report-car-expense" class="hidden">
                @include('pages.report-ga.car-expense')
            </div>
            @endif

        </div>

    </div>

    <script>
        $(document).on('click', '.report-filter', function(e) {

            e.preventDefault();

            let report = $(this).data('report');

            $('#reportContainer > div').addClass('hidden');

            $('#report-' + report).removeClass('hidden');

        });
    </script>

</x-app-layout>
