@php
    $forExport  = $forExport ?? false;
    $perpost    = $year.str_pad((string) $month, 2, '0', STR_PAD_LEFT);
    $detailRows = collect($groups)->where('type', 'detail');
    $colCount   = 10 + count($purposeCols); // Perpost/Company/WhsOwner/ProductID/Expiry/Name/Begin/In/Transfer/[Out...]/End
    // Export mode emits raw numbers (so Excel sees real, summable numbers) instead of
    // pre-formatted "50.000" strings — PhpSpreadsheet's HTML importer misreads the dot
    // as a decimal point and silently corrupts those. Excel-side formatting is applied
    // in the export's AfterSheet styling pass instead.
    $n = fn ($value) => $forExport ? $value : number_format($value, 0, ',', '.');
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

    @unless($forExport)
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-linear-to-r from-white to-violet-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-violet-900/10">
        <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300">
                <i class="fa-solid fa-scale-balanced text-xs"></i>
            </span>
            Trial Balance Summary Group
        </h2>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-medium text-violet-700 shadow-sm dark:bg-violet-900/30 dark:text-violet-300">
            <i class="fa-solid fa-building"></i>
            {{ $cpnyid }}
            <span class="text-violet-300 dark:text-violet-600">&bull;</span>
            {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
            <span class="text-violet-300 dark:text-violet-600">&bull;</span>
            Perpost {{ $perpost }}
        </span>
    </div>
    @endunless

    <div class="max-h-[70vh] overflow-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="sticky top-0 z-10 border-b-2 border-violet-100 bg-linear-to-b from-gray-50 to-gray-100/60 text-xs uppercase tracking-wide text-gray-500 shadow-sm dark:border-violet-900/40 dark:from-gray-900 dark:to-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold">Perpost</th>
                    <th class="px-3 py-3 text-left font-semibold">Company</th>
                    <th class="px-3 py-3 text-left font-semibold">WhsOwner</th>
                    <th class="px-3 py-3 text-left font-semibold">Product ID</th>
                    <th class="px-3 py-3 text-left font-semibold">Expired Date</th>
                    <th class="px-3 py-3 text-left font-semibold">Name</th>
                    <th class="px-3 py-3 text-right font-semibold">Begin Qty</th>
                    <th class="px-3 py-3 text-right font-semibold">In</th>
                    <th class="px-3 py-3 text-right font-semibold">Transfer</th>
                    @foreach($purposeCols as $label)
                        <th class="px-3 py-3 text-right font-semibold whitespace-nowrap {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">Out<br>{{ $label }}</th>
                    @endforeach
                    <th class="px-3 py-3 text-right font-semibold border-l border-gray-200 dark:border-gray-700">End Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @forelse($groups as $row)
                    @if($row['type'] === 'category_header')
                        <tr class="bg-linear-to-r from-violet-50 to-violet-50/30 dark:from-violet-900/25 dark:to-violet-900/5">
                            <td colspan="{{ $colCount }}" class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-1 rounded-full bg-violet-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-violet-700 dark:text-violet-300">
                                        {{ $row['category_label'] }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                    @else
                        <tr class="text-gray-600 transition-colors hover:bg-violet-50/50 dark:text-gray-400 dark:hover:bg-violet-900/10">
                            <td class="px-3 py-2 tabular-nums">{{ $perpost }}</td>
                            <td class="px-3 py-2">{{ $cpnyid }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-md bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">{{ $row['whs_id'] }}</span>
                            </td>
                            <td class="px-3 py-2">{{ $row['product_id'] }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $row['expired_date'] ? \Carbon\Carbon::parse($row['expired_date'])->format('d-M-y') : 'No Expired' }}</td>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['tenant'] }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['beginning']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['in_total']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['transfer']) }}</td>
                            @foreach($purposeCols as $label)
                                <td class="px-3 py-2 text-right tabular-nums {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ $n($row['out'][$label] ?? 0) }}</td>
                            @endforeach
                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100 border-l border-gray-200 dark:border-gray-700">{{ $n($row['ending']) }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ $colCount }}" class="px-3 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                                <span class="text-sm">No data for the selected company/period.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($detailRows->isNotEmpty())
                <tfoot class="sticky bottom-0 z-10 border-t-2 border-violet-100 bg-gray-50 dark:border-violet-900/40 dark:bg-gray-900">
                    <tr class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        <td colspan="6" class="px-3 py-3 text-right">Grand Total</td>
                        <td class="px-3 py-3 text-right tabular-nums text-violet-700 dark:text-violet-300">{{ $n($detailRows->sum('beginning')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-violet-700 dark:text-violet-300">{{ $n($detailRows->sum('in_total')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-violet-700 dark:text-violet-300">{{ $n($detailRows->sum('transfer')) }}</td>
                        @foreach($purposeCols as $label)
                            <td class="px-3 py-3 text-right tabular-nums text-violet-700 dark:text-violet-300 {{ $loop->first ? 'border-l border-gray-200 dark:border-gray-700' : '' }}">{{ $n($detailRows->sum(fn($r) => $r['out'][$label] ?? 0)) }}</td>
                        @endforeach
                        <td class="px-3 py-3 text-right tabular-nums text-violet-700 dark:text-violet-300 border-l border-gray-200 dark:border-gray-700">{{ $n($detailRows->sum('ending')) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</div>
