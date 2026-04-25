<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- Report Selector --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4">

            {{-- Meeting Room --}}
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

            {{-- Meeting Teams / Zoom --}}
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

            {{-- Booking Operational Car --}}
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

            {{-- Voucher Taxi --}}
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

        </div>

        {{-- Report Content --}}
        <div id="reportContainer">
            @include('pages.report-ga.meeting-room')
        </div>

        <div id="report-meeting-online" class="hidden">
            @include('pages.report-ga.meeting-online')
        </div>

        <div id="report-operational-car" class="hidden">
            @include('pages.report-ga.operational-car')
        </div>

        <div id="report-voucher-taxi" class="hidden">
            @include('pages.report-ga.voucher-taxi')
        </div>

    </div>

    <script>
            $(document).on('click', '.report-filter', function(e) {

                e.preventDefault();

                let report = $(this).data('report');

                $('#reportContainer > div').addClass('hidden');

                $('#report-' + report).removeClass('hidden');

            })
     </script>

</x-app-layout>
