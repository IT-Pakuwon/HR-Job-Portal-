<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4">

            {{-- Header Card --}}
            <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                            🆔 {{ $bq->bqid }} - BQ CS
                        </h2>


                        {{-- <div class="flex flex-wrap items-center justify-end gap-2">
                            @foreach ($vendors as $v)
                                <a href="{{ route('bqcs.print.vendor', ['hash' => $hash, 'idx' => $v['idx']]) }}" target="_blank">
                                    <button
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1  text-sm  font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Print PDF - {{ $v['name'] }}
                                    </button>
                                </a>
                            @endforeach
                        </div> --}}
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open=!open" @click.outside="open=false"
                                class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Print PDF
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition.origin.top.right
                                class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                style="display:none;">
                                <div class="px-3 py-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
                                    Pilih Vendor
                                </div>

                                <div class="max-h-72 overflow-y-auto">
                                    @foreach ($vendors as $v)
                                        <a href="{{ route('bqcs.print.vendor', ['hash' => $hash, 'idx' => $v['idx']]) }}"
                                            target="_blank"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700"
                                            @click="open=false">
                                            {{ $v['name'] }}
                                            {{-- <span class="block  text-sm  text-gray-500 dark:text-gray-400">
                                                {{ $v['cp'] ?? '-' }} • {{ $v['telp'] ?? '-' }}
                                            </span> --}}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="flex flex-col gap-4 text-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">Company</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $bq->cpny_id }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">CSID</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $bq->csid }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">SPPJ/K/T</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $bq->sppjtid }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BQ Details --}}
            <div class="flex w-full flex-col rounded-xl bg-white shadow-md dark:bg-gray-800">
                <div class="p-4">
                    <div
                        class="border-b border-gray-200 pb-4 text-sm font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                        BQ Detail
                    </div>

                    @php
                        // siapkan total per vendor (material & jasa)
                        $sumMat = [];
                        $sumJsa = [];
                        foreach ($vendors as $v) {
                            $idx = $v['idx'];
                            $sumMat[$idx] = 0;
                            $sumJsa[$idx] = 0;
                        }
                    @endphp

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-max table-auto border text-sm text-gray-700 dark:text-gray-200" id="bqTable">
                            <thead
                                class="bg-gray-100 text-gray-900 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-left font-semibold">No</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Line</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Description</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Qty</th>
                                    <th class="border px-4 py-3 text-left font-semibold">UoM</th>
                                    @foreach ($vendors as $v)
                                        <th class="border px-4 py-3 text-left align-top font-semibold">
                                            <div>{{ $v['name'] }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">✉️
                                                {{ $v['cp'] ?? '-' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">☎️
                                                {{ $v['telp'] ?? '-' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">🏠
                                                {{ $v['addr'] ?? '-' }}
                                            </div>
                                            <div class="mt-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Material / Jasa
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($details as $d)
                                    @php
                                        $qty = (float) $d->qty;
                                    @endphp
                                    <tr
                                        class="transition odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                        <td class="border px-4 py-3">{{ $d->bq_no }}</td>
                                        <td class="border px-4 py-3">{{ $d->bq_line_no }}</td>
                                        <td class="border px-4 py-3">
                                            <div
                                                class="w-full rounded-md border border-gray-300 bg-gray-100 px-2 py-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                {{ $d->bq_descr }}
                                            </div>
                                        </td>
                                        <td class="border px-4 py-3 text-right align-middle">
                                            {{ number_format($qty, 2, ',', '.') }}
                                        </td>
                                        <td class="border px-4 py-3 text-center align-middle">
                                            {{ $d->uom }}
                                        </td>

                                        @foreach ($vendors as $v)
                                            @php
                                                $i = $v['idx'];
                                                $unitMat = (float) ($d->{"vendorproductprice{$i}"} ?? 0);
                                                $unitJsa = (float) ($d->{"vendorjasaprice{$i}"} ?? 0);
                                                $sumMat[$i] += $qty * $unitMat;
                                                $sumJsa[$i] += $qty * $unitJsa;
                                            @endphp
                                            <td class="border px-4 py-3 align-top">
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="font-medium text-gray-600 dark:text-gray-300">Harga
                                                            Material</span>
                                                        <div
                                                            class="rounded-md border border-gray-300 bg-gray-100 px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                            {{ number_format($unitMat, 2, ',', '.') }}
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col gap-1">
                                                        <span class="font-medium text-gray-600 dark:text-gray-300">Harga
                                                            Jasa</span>
                                                        <div
                                                            class="rounded-md border border-gray-300 bg-gray-100 px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                            {{ number_format($unitJsa, 2, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-gray-100 font-medium dark:bg-gray-700">
                                <tr>
                                    <td colspan="5" class="border px-4 py-4 text-right">Grand Total per Vendor</td>
                                    @foreach ($vendors as $v)
                                        @php
                                            $idx = $v['idx'];
                                            $totMat = $sumMat[$idx] ?? 0;
                                            $totJsa = $sumJsa[$idx] ?? 0;
                                            $grand = $totMat + $totJsa;
                                        @endphp
                                        <td class="border px-4 py-4 text-right">
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                Harga Total Material:
                                                <span class="font-semibold">
                                                    {{ number_format($totMat, 2, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                Harga Total Jasa:
                                                <span class="font-semibold">
                                                    {{ number_format($totJsa, 2, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="mt-1 font-bold text-indigo-600 dark:text-indigo-400">
                                                Grand Total :
                                                <span>
                                                    {{ number_format($grand, 2, ',', '.') }}
                                                </span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- <div
                        class="flex justify-end gap-3 rounded-b-xl border-t border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-700/40">
                        <a href="{{ url()->previous() }}"
                           class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Back
                        </a>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
