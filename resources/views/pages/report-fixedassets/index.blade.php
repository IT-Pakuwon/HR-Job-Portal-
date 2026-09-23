<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- Report Selector --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-1 md:grid-cols-1">

            <a href="#" data-report="fa"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/40 dark:hover:bg-gray-800/70">

                <div class="flex items-center gap-4">

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-lg dark:bg-blue-500/15">
                        🏗️
                    </div>

                    <div class="flex flex-col">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">
                            Fixed Asset Receipt
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Fixed asset receipt detail & receiving status
                        </p>
                    </div>

                </div>

            </a>

        </div>

        {{-- Report Content --}}
        <div id="reportContainer">
            <div id="report-fa">
                @include('pages.report-fixedassets.fa-detail')
            </div>
        </div>

    </div>

</x-app-layout>
