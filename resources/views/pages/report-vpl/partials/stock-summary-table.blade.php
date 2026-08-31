@php
    $monthEnd  = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
    // Mirrors VplReportController::buildStockSummaryReport() — a period still in progress
    // (or in the future) ages against today, not a month-end that hasn't happened yet.
    $agingAsOf = \Carbon\Carbon::now()->lt($monthEnd) ? \Carbon\Carbon::now() : $monthEnd;
    $agingCols = $meta['aging'] ?? [];
    $sourceCols = $meta['sources'] ?? []; // column label => group label (Promotion/Leasing)
    $usedCols  = $meta['used'] ?? [];
    $totalCols = 12 + count($agingCols) + count($sourceCols) + count($usedCols);

    $tenantSubtotals = collect($rows)->where('type', 'tenant_subtotal');
    $forExport = $forExport ?? false;
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

    @unless($forExport)
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-linear-to-r from-white to-indigo-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-indigo-900/10">
        <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                <i class="fa-solid fa-layer-group text-xs"></i>
            </span>
            Stock &amp; Aging Summary
        </h2>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 shadow-sm dark:bg-indigo-900/30 dark:text-indigo-300">
            <i class="fa-solid fa-building"></i>
            {{ $cpnyid }}
            <span class="text-indigo-300 dark:text-indigo-600">&bull;</span>
            {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
            <span class="text-indigo-300 dark:text-indigo-600">&bull;</span>
            Aging as of {{ $agingAsOf->format('d-M-y') }}
        </span>
    </div>
    @endunless

    <div class="max-h-[70vh] overflow-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="sticky top-0 z-10 border-b-2 border-indigo-100 bg-linear-to-b from-gray-50 to-gray-100/60 text-xs uppercase tracking-wide text-gray-500 shadow-sm dark:border-indigo-900/40 dark:from-gray-900 dark:to-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold" rowspan="2">Tenant</th>
                    <th class="px-3 py-3 text-left font-semibold" rowspan="2">Source</th>
                    <th class="px-3 py-3 text-left font-semibold" rowspan="2">Expiry Date</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Nominal</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Beginning (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">In (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Out Loy (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Out Prm (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Out Ent (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Out Other (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Ending (Lbr)</th>
                    <th class="px-3 py-3 text-right font-semibold" rowspan="2">Value (Rp)</th>
                    <th class="px-3 py-2 text-center font-semibold border-l border-gray-200 dark:border-gray-700" colspan="{{ count($agingCols) }}">Aging Report Per {{ $agingAsOf->format('d-M-y') }} (Rp)</th>
                    <th class="px-3 py-2 text-center font-semibold border-l border-gray-200 dark:border-gray-700" colspan="{{ count($sourceCols) }}">Voucher Sources (Rp)</th>
                    <th class="px-3 py-2 text-center font-semibold border-l border-gray-200 dark:border-gray-700" colspan="{{ count($usedCols) }}">Voucher Used in Current Period (Rp)</th>
                </tr>
                <tr>
                    @foreach($agingCols as $label)
                        <th class="px-3 py-2 text-right font-semibold whitespace-nowrap {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ $label }}</th>
                    @endforeach
                    @foreach($sourceCols as $column => $group)
                        <th class="px-3 py-2 text-right font-semibold whitespace-nowrap {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ $column }}</th>
                    @endforeach
                    @foreach($usedCols as $label)
                        <th class="px-3 py-2 text-right font-semibold whitespace-nowrap {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @forelse($rows as $row)
                    @if($row['type'] === 'category_header')
                        <tr class="bg-linear-to-r from-indigo-50 to-indigo-50/30 dark:from-indigo-900/25 dark:to-indigo-900/5">
                            <td colspan="{{ $totalCols }}" class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-1 rounded-full bg-indigo-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                                        {{ $row['category_label'] }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                    @elseif($row['type'] === 'tenant_subtotal')
                        <tr class="border-t-2 border-t-gray-200 bg-gray-50/80 font-semibold text-gray-900 dark:border-t-gray-600 dark:bg-white/[0.03] dark:text-gray-100">
                            <td class="px-3 py-2.5">{{ $row['tenant'] }}</td>
                            <td class="px-3 py-2.5"></td>
                            <td class="px-3 py-2.5"></td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['nominal'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['beginning'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['in_total'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['out_loyalty'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['out_promotion'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['out_entertain'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['out_internal'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['ending'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($row['value'], 0, ',', '.') }}</td>
                            @foreach($agingCols as $label)
                                <td class="px-3 py-2.5 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ number_format($row['aging'][$label] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            @foreach($sourceCols as $column => $group)
                                <td class="px-3 py-2.5 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ number_format($row['sources'][$column] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            @foreach($usedCols as $label)
                                <td class="px-3 py-2.5 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ number_format($row['used'][$label] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>

                    @else
                        <tr class="text-gray-600 transition-colors hover:bg-indigo-50/50 dark:text-gray-400 dark:hover:bg-indigo-900/10">
                            <td class="px-3 py-2"></td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ $row['source_label'] }}</span>
                            </td>
                            <td class="px-3 py-2 tabular-nums">{{ $row['expired_date'] ? \Carbon\Carbon::parse($row['expired_date'])->format('d-M-y') : '-' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['nominal'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['beginning'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['in_total'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['out_loyalty'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['out_promotion'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['out_entertain'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['out_internal'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['ending'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['value'], 0, ',', '.') }}</td>
                            @foreach($agingCols as $label)
                                <td class="px-3 py-2 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">
                                    {{ $row['aging_bucket'] === $label ? number_format($row['value'], 0, ',', '.') : '' }}
                                </td>
                            @endforeach
                            @foreach($sourceCols as $column => $group)
                                <td class="px-3 py-2 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">
                                    {{ $row['source_column'] === $column ? number_format($row['source_value'], 0, ',', '.') : '' }}
                                </td>
                            @endforeach
                            @foreach($usedCols as $label)
                                <td class="px-3 py-2 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">
                                    {{ ($row['used_value'][$label] ?? 0) != 0 ? number_format($row['used_value'][$label], 0, ',', '.') : '' }}
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ $totalCols }}" class="px-3 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                                <span class="text-sm">No data for the selected company/period.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($tenantSubtotals->isNotEmpty())
                <tfoot class="sticky bottom-0 z-10 border-t-2 border-indigo-100 bg-gray-50 dark:border-indigo-900/40 dark:bg-gray-900">
                    <tr class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        <td colspan="3" class="px-3 py-3 text-right">Grand Total</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('nominal'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('beginning'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('in_total'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('out_loyalty'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('out_promotion'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('out_entertain'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('out_internal'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('ending'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300">{{ number_format($tenantSubtotals->sum('value'), 0, ',', '.') }}</td>
                        @foreach($agingCols as $label)
                            <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300 {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ number_format($tenantSubtotals->sum(fn($r) => $r['aging'][$label] ?? 0), 0, ',', '.') }}</td>
                        @endforeach
                        @foreach($sourceCols as $column => $group)
                            <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300 {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ number_format($tenantSubtotals->sum(fn($r) => $r['sources'][$column] ?? 0), 0, ',', '.') }}</td>
                        @endforeach
                        @foreach($usedCols as $label)
                            <td class="px-3 py-3 text-right tabular-nums text-indigo-700 dark:text-indigo-300 {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ number_format($tenantSubtotals->sum(fn($r) => $r['used'][$label] ?? 0), 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</div>
