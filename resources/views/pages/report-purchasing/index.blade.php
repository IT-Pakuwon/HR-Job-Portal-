<x-app-layout>
    @php
        $user = auth()->user();
        $isWarehouse = $user->hasRole('WHSACCESS');
        $isCostCtrl = $user->hasRole('COSTCTRLACCESS');
    @endphp
    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- REPORT SELECTOR --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">

            {{-- SPPB --}}
            <a href="#" data-report="sppb"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/40 dark:hover:bg-gray-800/70">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-lg dark:bg-sky-500/15">
                        📑
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">
                            SPPB Detail
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Purchase request monitoring
                        </p>
                    </div>

                </div>

            </a>
            @if (!$isWarehouse || $isCostCtrl)
                {{-- SPPJ --}}
                <a href="#" data-report="sppj"
                    class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/40 dark:hover:bg-gray-800/70">

                    <div class="flex items-center gap-4">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-lg dark:bg-indigo-500/15">
                            📄
                        </div>

                        <div class="flex flex-col">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                SPPJ Detail
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Service procurement monitoring
                            </p>
                        </div>

                    </div>

                </a>


                {{-- SPPT --}}
                <a href="#" data-report="sppt"
                    class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/40 dark:hover:bg-gray-800/70">

                    <div class="flex items-center gap-4">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-lg dark:bg-purple-500/15">
                            🧾
                        </div>

                        <div class="flex flex-col">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                SPPT Detail
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Tenant procurement monitoring
                            </p>
                        </div>

                    </div>

                </a>


                {{-- SPPK --}}
                <a href="#" data-report="sppk"
                    class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/40 dark:hover:bg-gray-800/70">

                    <div class="flex items-center gap-4">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg dark:bg-emerald-500/15">
                            📘
                        </div>

                        <div class="flex flex-col">
                            <p class="font-semibold text-gray-800 dark:text-gray-200">
                                SPPK Detail
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Vehicle procurement monitoring
                            </p>
                        </div>

                    </div>

                </a>
            @endif
        </div>



        {{-- REPORT CONTENT --}}
        <div id="reportContainer">

            <div id="report-sppb">
                @include('pages.report-purchasing.sppb-detail')
            </div>

            <div id="report-sppj" class="hidden">
                @include('pages.report-purchasing.sppj-detail')
            </div>

            <div id="report-sppt" class="hidden">
                @include('pages.report-purchasing.sppt-detail')
            </div>

            <div id="report-sppk" class="hidden">
                @include('pages.report-purchasing.sppk-detail')
            </div>
        </div>
    </div>


    <script>
        $(document).on('click', '.report-filter', function(e) {

            e.preventDefault()

            let report = $(this).data('report')

            $('#reportContainer > div').addClass('hidden')

            $('#report-' + report).removeClass('hidden')

        })
    </script>

</x-app-layout>
