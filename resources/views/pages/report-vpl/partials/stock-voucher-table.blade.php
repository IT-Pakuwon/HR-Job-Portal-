<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-linear-to-r from-white to-purple-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-purple-900/10">
        <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300">
                <i class="fa-solid fa-ticket text-xs"></i>
            </span>
            Stock Voucher Report
        </h2>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-50 px-3 py-1 text-xs font-medium text-purple-700 shadow-sm dark:bg-purple-900/30 dark:text-purple-300">
            <i class="fa-solid fa-building"></i>
            {{ $cpnyid }}
            <span class="text-purple-300 dark:text-purple-600">&bull;</span>
            {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
        </span>
    </div>

    <div class="max-h-[70vh] overflow-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="sticky top-0 z-10 border-b-2 border-purple-100 bg-linear-to-b from-gray-50 to-gray-100/60 text-xs uppercase tracking-wide text-gray-500 shadow-sm dark:border-purple-900/40 dark:from-gray-900 dark:to-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold">No</th>
                    <th class="px-3 py-3 text-left font-semibold">Tenant</th>
                    <th class="px-3 py-3 text-left font-semibold">Expiry Date</th>
                    <th class="px-3 py-3 text-right font-semibold">Nominal</th>
                    <th class="px-3 py-3 text-right font-semibold">Beginning (Lbr)</th>
                    <th class="px-3 py-3 text-left font-semibold">Tgl In</th>
                    <th class="px-3 py-3 text-right font-semibold">In (Lbr)</th>
                    <th class="px-3 py-3 text-left font-semibold">Tgl Out</th>
                    <th class="px-3 py-3 text-right font-semibold">Out (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold">Ending</th>
                    <th class="px-3 py-3 text-right font-semibold">Total Nominal (Rp)</th>
                    <th class="px-3 py-3 text-left font-semibold">Diterima Dari</th>
                    <th class="px-3 py-3 text-left font-semibold">Untuk Pembayaran</th>
                    <th class="px-3 py-3 text-left font-semibold">Diambil Oleh</th>
                    <th class="px-3 py-3 text-left font-semibold">Keperluan</th>
                    <th class="px-3 py-3 text-left font-semibold">Receive No (DAS)</th>
                    <th class="px-3 py-3 text-left font-semibold">Transfer/Usage No (DAS)</th>
                    <th class="px-3 py-3 text-left font-semibold">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @php $tenantIndex = -1; @endphp
                @forelse($groups as $group)
                    @if($group['is_first_of_category'])
                        <tr class="bg-linear-to-r from-purple-50 to-purple-50/30 dark:from-purple-900/25 dark:to-purple-900/5">
                            <td colspan="18" class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-1 rounded-full bg-purple-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-purple-700 dark:text-purple-300">
                                        {{ $group['category_label'] }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @php
                        if ($group['is_first_of_tenant']) {
                            $tenantIndex++;
                        }
                        $zebra = $tenantIndex % 2 === 1;
                        $groupRows = count($group['rows']) ? $group['rows'] : [null];
                    @endphp

                    @foreach($groupRows as $rIndex => $row)
                        <tr class="group text-gray-700 transition-colors hover:bg-purple-50/50 dark:text-gray-300 dark:hover:bg-purple-900/10 {{ $zebra ? 'bg-gray-50/70 dark:bg-white/[0.02]' : '' }} {{ $rIndex === 0 && $group['is_first_of_tenant'] ? 'border-t-2 border-t-gray-200 dark:border-t-gray-600' : '' }}">
                            @if($rIndex === 0)
                                @if($group['is_first_of_tenant'])
                                    <td class="px-3 py-2.5 align-top font-medium text-gray-900 dark:text-gray-100" rowspan="{{ $group['tenant_rowspan'] }}">
                                        {{ $group['tenant_no'] }}
                                    </td>
                                    <td class="px-3 py-2.5 align-top font-medium text-gray-900 dark:text-gray-100" rowspan="{{ $group['tenant_rowspan'] }}">
                                        {{ $group['tenant'] }}
                                    </td>
                                @endif

                                <td class="px-3 py-2.5 align-top tabular-nums text-gray-600 dark:text-gray-400" rowspan="{{ $group['group_rowspan'] }}">
                                    {{ $group['expired_date']?->format('d-M-y') ?? '-' }}
                                </td>
                                <td class="px-3 py-2.5 align-top text-right tabular-nums text-gray-600 dark:text-gray-400" rowspan="{{ $group['group_rowspan'] }}">
                                    {{ number_format($group['nominal'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 align-top text-right tabular-nums text-gray-600 dark:text-gray-400" rowspan="{{ $group['group_rowspan'] }}">
                                    {{ number_format($group['beginning'], 0, ',', '.') }}
                                </td>
                            @endif

                            <td class="px-3 py-2.5 tabular-nums text-gray-500 dark:text-gray-400">{{ $row && $row['direction'] === 'in' ? $row['date']?->format('d-M-y') : '' }}</td>
                            <td class="px-3 py-2.5 text-right">
                                @if($row && $row['direction'] === 'in')
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold tabular-nums text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                        <i class="fa-solid fa-arrow-up text-[9px]"></i>{{ number_format($row['qty'], 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 tabular-nums text-gray-500 dark:text-gray-400">{{ $row && $row['direction'] === 'out' ? $row['date']?->format('d-M-y') : '' }}</td>
                            <td class="px-3 py-2.5 text-right">
                                @if($row && $row['direction'] === 'out')
                                    <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold tabular-nums text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                        <i class="fa-solid fa-arrow-down text-[9px]"></i>{{ number_format($row['qty'], 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            @if($rIndex === 0)
                                <td class="px-3 py-2.5 align-top text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100" rowspan="{{ $group['group_rowspan'] }}">
                                    {{ number_format($group['ending'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 align-top text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100" rowspan="{{ $group['group_rowspan'] }}">
                                    {{ number_format($group['total_nominal'], 0, ',', '.') }}
                                </td>
                            @endif

                            <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400">{{ $row['diterima_dari'] ?? '' }}</td>
                            <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400">{{ $row['untuk_pembayaran'] ?? '' }}</td>
                            <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400">{{ $row['diambil_oleh'] ?? '' }}</td>
                            <td class="px-3 py-2.5 text-gray-600 dark:text-gray-400">{{ $row['keperluan'] ?? '' }}</td>
                            <td class="px-3 py-2.5">
                                @if($row && $row['doc_label'] === 'Receive')
                                    <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $row['doc_no'] }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if($row && $row['doc_label'] !== 'Receive')
                                    <span class="inline-flex rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ $row['doc_no'] }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if($row && $row['keterangan'])
                                    <span class="inline-flex rounded-md bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">{{ $row['keterangan'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="18" class="px-3 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                                <span class="text-sm">No data for the selected company/period.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($groups))
                <tfoot class="sticky bottom-0 z-10 border-t-2 border-purple-100 bg-gray-50 dark:border-purple-900/40 dark:bg-gray-900">
                    <tr class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        <td colspan="9" class="px-3 py-3 text-right">Grand Total</td>
                        <td class="px-3 py-3 text-right tabular-nums text-purple-700 dark:text-purple-300">{{ number_format(collect($groups)->sum('ending'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-purple-700 dark:text-purple-300">{{ number_format(collect($groups)->sum('total_nominal'), 0, ',', '.') }}</td>
                        <td colspan="7"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</div>
