<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">
        {{-- Report Selector --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">

            {{-- SPB --}}
            <a href="#" data-report="spb"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-lg">
                        📦
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800">
                            SPB Detail
                        </p>
                        <p class="text-xs text-gray-500">
                            Inventory request monitoring
                        </p>
                    </div>

                </div>

            </a>

            {{-- ISSUE --}}
            <a href="#" data-report="issue"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-lg">
                        📤
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800">
                            Issue / BPG Detail
                        </p>
                        <p class="text-xs text-gray-500">
                            Inventory outgoing monitoring
                        </p>
                    </div>

                </div>

            </a>

            {{-- RECEIPT --}}
            <a href="#" data-report="receipt"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg">
                        📥
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800">
                            Receipt / STTB Detail
                        </p>
                        <p class="text-xs text-gray-500">
                            Inventory incoming monitoring
                        </p>
                    </div>

                </div>

            </a>

            {{-- INVENTORY MOVEMENT --}}
            <a href="#" data-report="movement"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-lg">
                        🔄
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800">
                            Inventory Movement
                        </p>
                        <p class="text-xs text-gray-500">
                            Full tracking (IN / OUT / Balance)
                        </p>
                    </div>

                </div>

            </a>

        </div>


        {{-- Report Content --}}
        <div id="reportContainer">

            <div id="report-spb">
                @include('pages.report-warehouse.spb-detail')
            </div>

            <div id="report-issue" class="hidden">
                @include('pages.report-warehouse.issue-detail')
            </div>

            <div id="report-receipt" class="hidden">
                @include('pages.report-warehouse.receipt-detail')
            </div>

            <div id="report-movement" class="hidden">
                @include('pages.report-warehouse.inventory-movement')
            </div>

            {{-- <div id="report-sppb" class="hidden">
                @include('pages.report-warehouse.sppb-detail')
            </div> --}}

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
