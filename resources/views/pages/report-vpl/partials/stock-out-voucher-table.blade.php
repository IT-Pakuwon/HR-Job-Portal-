@php
    $forExport  = $forExport ?? false;
    $detailRows = collect($rows)->where('type', 'detail');
    $colCount   = $forExport ? 13 : 14;
    $catLabel   = fn (string $label) => $label === 'NON F&B' ? 'Non F&B' : $label;
    // Export mode emits raw numbers (so Excel sees real, summable numbers) instead of
    // pre-formatted "50.000" strings — PhpSpreadsheet's HTML importer misreads the dot
    // as a decimal point and silently corrupts those. Excel-side formatting is applied
    // in the export's AfterSheet styling pass instead.
    $n = fn ($value) => $forExport ? $value : number_format($value, 0, ',', '.');
    $no = 0;
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

    @unless($forExport)
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-linear-to-r from-white to-amber-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-amber-900/10">
        <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300">
                <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
            </span>
            Laporan Stok Out Voucher &mdash; {{ $whsLabel }}
        </h2>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 shadow-sm dark:bg-amber-900/30 dark:text-amber-300">
            <i class="fa-solid fa-building"></i>
            {{ $cpnyid }}
            <span class="text-amber-300 dark:text-amber-600">&bull;</span>
            Bulan {{ strtoupper(\Carbon\Carbon::create()->month($month)->format('M')) }} {{ $year }}
        </span>
    </div>
    @endunless

    <div class="max-h-[70vh] overflow-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="sticky top-0 z-10 border-b-2 border-amber-100 bg-linear-to-b from-gray-50 to-gray-100/60 text-xs uppercase tracking-wide text-gray-500 shadow-sm dark:border-amber-900/40 dark:from-gray-900 dark:to-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold">No</th>
                    <th class="px-3 py-3 text-left font-semibold">Voucher ID</th>
                    <th class="px-3 py-3 text-left font-semibold">Tenant</th>
                    <th class="px-3 py-3 text-right font-semibold">Nominal Satuan Voucher</th>
                    <th class="px-3 py-3 text-left font-semibold">Expiry Date</th>
                    <th class="px-3 py-3 text-right font-semibold">Begin</th>
                    <th class="px-3 py-3 text-right font-semibold">In</th>
                    <th class="px-3 py-3 text-right font-semibold">Out</th>
                    <th class="px-3 py-3 text-right font-semibold">Retur</th>
                    <th class="px-3 py-3 text-right font-semibold">Ending</th>
                    <th class="px-3 py-3 text-right font-semibold">Nominal Out</th>
                    <th class="px-3 py-3 text-left font-semibold">Purpose</th>
                    <th class="px-3 py-3 text-left font-semibold">Remarks Out</th>
                    @unless($forExport)
                        <th class="px-3 py-3 text-center font-semibold">Action</th>
                    @endunless
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @forelse($rows as $row)
                    @if($row['type'] === 'category_header')
                        <tr class="bg-linear-to-r from-amber-50 to-amber-50/30 dark:from-amber-900/25 dark:to-amber-900/5">
                            <td colspan="{{ $colCount }}" class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-1 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                                        {{ $catLabel($row['category_label']) }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                    @else
                        @php $no++; @endphp
                        <tr class="text-gray-600 transition-colors hover:bg-amber-50/50 dark:text-gray-400 dark:hover:bg-amber-900/10">
                            <td class="px-3 py-2 tabular-nums">{{ $no }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $row['product_id'] }}</td>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['tenant'] }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['nominal']) }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $row['expired_date'] ? \Carbon\Carbon::parse($row['expired_date'])->format('d-M-y') : 'No Expired' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['beginning']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['in_total']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['out_total']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['retur']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100">{{ $n($row['ending']) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $n($row['nominal_out']) }}</td>
                            <td class="px-3 py-2">{{ implode(', ', $row['purpose']) }}</td>
                            <td class="px-3 py-2">{{ implode(', ', $row['remarks']) }}</td>
                            @unless($forExport)
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                            class="btnViewDocs inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-600 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-400"
                                            data-title="In &mdash; {{ $row['tenant'] }}"
                                            data-docs='@json($row['in_docs'])'
                                            title="View documents behind In">
                                            <i class="fa-solid fa-arrow-down text-xs"></i>
                                        </button>
                                        <button type="button"
                                            class="btnViewDocs inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-600 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-amber-900/20 dark:hover:text-amber-400"
                                            data-title="Out &mdash; {{ $row['tenant'] }}"
                                            data-docs='@json($row['out_docs'])'
                                            title="View documents behind Out">
                                            <i class="fa-solid fa-arrow-up text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            @endunless
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
                <tfoot class="sticky bottom-0 z-10 border-t-2 border-amber-100 bg-gray-50 dark:border-amber-900/40 dark:bg-gray-900">
                    <tr class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        <td colspan="5" class="px-3 py-3 text-right">Total</td>
                        <td class="px-3 py-3 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ $n($detailRows->sum('beginning')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ $n($detailRows->sum('in_total')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ $n($detailRows->sum('out_total')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ $n($detailRows->sum('retur')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ $n($detailRows->sum('ending')) }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-amber-700 dark:text-amber-300">{{ $n($detailRows->sum('nominal_out')) }}</td>
                        <td colspan="{{ $forExport ? 2 : 3 }}"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</div>

@unless($forExport)
{{-- Related document(s) modal — shared by both the In and Out Action buttons --}}
<div id="stkoutDocsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-lg dark:bg-gray-800">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                Documents &mdash; <span id="stkoutDocsTitle"></span>
            </h3>
            <button type="button" id="stkoutDocsClose"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700">
                &#10005;
            </button>
        </div>
        <div class="max-h-[60vh] overflow-y-auto p-4">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-2 py-2 text-left font-semibold">Type</th>
                        <th class="px-2 py-2 text-left font-semibold">DOCID</th>
                        <th class="px-2 py-2 text-left font-semibold">Date</th>
                        <th class="px-2 py-2 text-right font-semibold">Qty</th>
                    </tr>
                </thead>
                <tbody id="stkoutDocsBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        const $modal = $('#stkoutDocsModal');

        $(document).off('click.stkoutDocs').on('click.stkoutDocs', '.btnViewDocs', function () {
            const docs = $(this).data('docs') || [];
            const title = $(this).data('title') || '';

            $('#stkoutDocsTitle').html(title);

            const $body = $('#stkoutDocsBody').empty();

            if (!docs.length) {
                $body.append(
                    '<tr><td colspan="4" class="px-2 py-6 text-center italic text-gray-400 dark:text-gray-500">No documents found for this period.</td></tr>'
                );
            } else {
                docs.forEach(function (d) {
                    const qtyClass = d.qty < 0 ? 'text-rose-600 dark:text-rose-400' : '';
                    $body.append(
                        '<tr class="border-b border-gray-100 dark:border-gray-700">' +
                            '<td class="px-2 py-2 text-gray-500 dark:text-gray-400">' + d.doc_label + '</td>' +
                            '<td class="px-2 py-2"><a href="' + d.link + '" target="_blank" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">' + d.doc_no + '</a></td>' +
                            '<td class="px-2 py-2">' + (d.date || '-') + '</td>' +
                            '<td class="px-2 py-2 text-right tabular-nums ' + qtyClass + '">' + Number(d.qty).toLocaleString('id-ID') + '</td>' +
                        '</tr>'
                    );
                });
            }

            $modal.removeClass('hidden').addClass('flex');
        });

        $(document).off('click.stkoutDocsClose').on('click.stkoutDocsClose', '#stkoutDocsClose', function () {
            $modal.addClass('hidden').removeClass('flex');
        });
    })();
</script>
@endunless
