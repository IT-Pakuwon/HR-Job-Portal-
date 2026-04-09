<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- Report Selector --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-1 md:grid-cols-2">

            {{-- CS DETAIL --}}
            <a href="#" data-report="cs"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-lg">
                        📊
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800">
                            Canvass Sheet Detail
                        </p>
                        <p class="text-xs text-gray-500">
                            Selected vendor items with PO / SPK
                        </p>
                    </div>

                </div>

            </a>

            {{-- CS TRACKING --}}
            <a href="#" data-report="cs-tracking"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg">
                        📈
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800">
                            Canvass Sheets Tracking
                        </p>
                        <p class="text-xs text-gray-500">
                            Monitor CS status & approval progress
                        </p>
                    </div>

                </div>

            </a>

        </div>

        {{-- Report Content --}}
        <div id="reportContainer">

            <div id="report-cs">
                @include('pages.report-cs.canvas-detail')
            </div>

            <div id="report-cs-tracking" class="hidden">
                @include('pages.report-cs.canvas-tracking')
            </div>

        </div>

    </div>

    <script>
    $(document).on('click', '.report-filter', function(e){
        e.preventDefault();

        let report = $(this).data('report');

        // hide semua
        $('#reportContainer > div').addClass('hidden');

        // show selected
        $('#report-' + report).removeClass('hidden');

        // optional: active style
        $('.report-filter').removeClass('ring-2 ring-indigo-400');
        $(this).addClass('ring-2 ring-indigo-400');
    });
    </script>
</x-app-layout>
