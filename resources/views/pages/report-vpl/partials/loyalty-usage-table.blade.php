@php
    $detailRows  = collect($rows)->where('type', 'detail');
    $totalEnding = $detailRows->sum('ending_stock');
    $totalUsage  = $detailRows->sum('usage_qty');
    $totalRate   = $totalEnding > 0 ? $totalUsage / $totalEnding : null;
    $forExport   = $forExport ?? false;
    $colCount    = $forExport ? 5 : 6;
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

    @unless($forExport)
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-linear-to-r from-white to-emerald-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-emerald-900/10">
        <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                <i class="fa-solid fa-gift text-xs"></i>
            </span>
            Loyalty Usage Rate
        </h2>

        <div class="flex flex-1 items-center justify-end gap-3">
            <div class="relative w-full max-w-xs">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                <input type="text" id="loyusg_search" placeholder="Search tenant..."
                    class="w-full rounded-lg border border-gray-300 bg-white py-1.5 pl-8 pr-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
            </div>

            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 shadow-sm dark:bg-emerald-900/30 dark:text-emerald-300">
                <i class="fa-solid fa-building"></i>
                {{ $cpnyid }}
                <span class="text-emerald-300 dark:text-emerald-600">&bull;</span>
                {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
            </span>
        </div>
    </div>
    @endunless

    <div class="max-h-[70vh] overflow-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="sticky top-0 z-10 border-b-2 border-emerald-100 bg-linear-to-b from-gray-50 to-gray-100/60 text-xs uppercase tracking-wide text-gray-500 shadow-sm dark:border-emerald-900/40 dark:from-gray-900 dark:to-gray-900 dark:text-gray-400">
                <tr>
                    @php
                        $cols = [
                            'tenant' => 'Tenant',
                            null => 'Expiry Date',
                            'ending_stock' => 'Stock at WHLOYALTY (Lbr)',
                            'usage_qty' => 'Usage at WHLOYALTY (Lbr)',
                            'usage_rate' => 'Usage Rate',
                        ];
                    @endphp
                    @foreach($cols as $sortKey => $label)
                        <th class="px-3 py-3 {{ $sortKey ? 'cursor-pointer select-none' : '' }} {{ in_array($label, ['Tenant', 'Expiry Date']) ? 'text-left' : 'text-right' }}"
                            @if($sortKey) data-sort-key="{{ $sortKey }}" @endif>
                            <span class="inline-flex items-center gap-1 {{ in_array($label, ['Stock at WHLOYALTY (Lbr)', 'Usage at WHLOYALTY (Lbr)', 'Usage Rate']) ? 'justify-end' : '' }}">
                                {{ $label }}
                                @if($sortKey)
                                    <i class="fa-solid fa-sort text-[10px] text-gray-300 dark:text-gray-600 sort-icon"></i>
                                @endif
                            </span>
                        </th>
                    @endforeach
                    @unless($forExport)
                        <th class="px-3 py-3 text-center font-semibold">Action</th>
                    @endunless
                </tr>
            </thead>

            @forelse($rows as $row)
                @if($row['type'] === 'category_header')
                    <tbody>
                        <tr class="bg-linear-to-r from-emerald-50 to-emerald-50/30 dark:from-emerald-900/25 dark:to-emerald-900/5 category-header-row">
                            <td colspan="{{ $colCount }}" class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-1 rounded-full bg-emerald-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                                        {{ $row['category_label'] }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                @else
                    <tbody class="tenant-block"
                        data-tenant="{{ Illuminate\Support\Str::lower($row['tenant']) }}"
                        data-ending_stock="{{ $row['ending_stock'] }}"
                        data-usage_qty="{{ $row['usage_qty'] }}"
                        data-usage_rate="{{ $row['usage_rate'] ?? '' }}">
                        <tr class="text-gray-600 transition-colors hover:bg-emerald-50/50 dark:text-gray-400 dark:hover:bg-emerald-900/10">
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">{{ $row['tenant'] }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $row['expired_date'] ? \Carbon\Carbon::parse($row['expired_date'])->format('d-M-y') : '-' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['ending_stock'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($row['usage_qty'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ $row['usage_rate'] === null ? '-' : number_format($row['usage_rate'] * 100, 2, ',', '.').'%' }}
                            </td>
                            @unless($forExport)
                                <td class="px-3 py-2 text-center">
                                    <button type="button"
                                        class="btnViewDocs inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 text-gray-500 transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-600 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-400"
                                        data-tenant="{{ $row['tenant'] }}"
                                        data-docs='@json($row['docs'])'
                                        title="View related usage documents">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </td>
                            @endunless
                        </tr>
                    </tbody>
                @endif
            @empty
                <tbody>
                    <tr>
                        <td colspan="{{ $colCount }}" class="px-3 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                                <span class="text-sm">No data for the selected company/period.</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endforelse

            @if($detailRows->isNotEmpty())
                <tfoot class="sticky bottom-0 z-10 border-t-2 border-emerald-100 bg-gray-50 dark:border-emerald-900/40 dark:bg-gray-900">
                    <tr class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        <td colspan="2" class="px-3 py-3 text-right">Grand Total</td>
                        <td class="px-3 py-3 text-right tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($totalEnding, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-emerald-700 dark:text-emerald-300">{{ number_format($totalUsage, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-emerald-700 dark:text-emerald-300">
                            {{ $totalRate === null ? '-' : number_format($totalRate * 100, 2, ',', '.').'%' }}
                        </td>
                        @unless($forExport)
                            <td></td>
                        @endunless
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</div>

@unless($forExport)
{{-- Related usage documents modal --}}
<div id="loyusgDocsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-lg dark:bg-gray-800">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                Usage Documents &mdash; <span id="loyusgDocsTenant"></span>
            </h3>
            <button type="button" id="loyusgDocsClose"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700">
                &#10005;
            </button>
        </div>
        <div class="max-h-[60vh] overflow-y-auto p-4">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-2 py-2 text-left font-semibold">DOCID</th>
                        <th class="px-2 py-2 text-left font-semibold">Date</th>
                        <th class="px-2 py-2 text-right font-semibold">Qty</th>
                    </tr>
                </thead>
                <tbody id="loyusgDocsBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        const $modal = $('#loyusgDocsModal');

        $(document).off('click.loyusgDocs').on('click.loyusgDocs', '.btnViewDocs', function () {
            const docs = $(this).data('docs') || [];
            const tenant = $(this).data('tenant') || '';

            $('#loyusgDocsTenant').text(tenant);

            const $body = $('#loyusgDocsBody').empty();

            if (!docs.length) {
                $body.append(
                    '<tr><td colspan="3" class="px-2 py-6 text-center italic text-gray-400 dark:text-gray-500">No usage documents found for this period.</td></tr>'
                );
            } else {
                docs.forEach(function (d) {
                    $body.append(
                        '<tr class="border-b border-gray-100 dark:border-gray-700">' +
                            '<td class="px-2 py-2"><a href="' + d.link + '" target="_blank" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">' + d.usage_id + '</a></td>' +
                            '<td class="px-2 py-2">' + (d.usage_date || '-') + '</td>' +
                            '<td class="px-2 py-2 text-right tabular-nums">' + Number(d.qty).toLocaleString('id-ID') + '</td>' +
                        '</tr>'
                    );
                });
            }

            $modal.removeClass('hidden').addClass('flex');
        });

        $(document).off('click.loyusgDocsClose').on('click.loyusgDocsClose', '#loyusgDocsClose', function () {
            $modal.addClass('hidden').removeClass('flex');
        });

        // Search — filter tenant blocks by name, hide category headers left with no visible tenants.
        $('#loyusg_search').off('input').on('input', function () {
            const term = $(this).val().toLowerCase().trim();

            $('.tenant-block').each(function () {
                const match = !term || $(this).data('tenant').toString().includes(term);
                $(this).toggle(match);
            });

            $('.category-header-row').each(function () {
                const $tbody = $(this).closest('tbody');
                let $next = $tbody.next('.tenant-block');
                let hasVisible = false;

                while ($next.length) {
                    if ($next.is(':visible')) {
                        hasVisible = true;
                        break;
                    }
                    $next = $next.next('.tenant-block');
                }

                $tbody.toggle(hasVisible);
            });
        });

        // Sort — reorder tenant-block <tbody> elements by the clicked column (flattens categories while sorted).
        let sortState = { key: null, dir: 1 };

        $('th[data-sort-key]').off('click').on('click', function () {
            const key = $(this).data('sort-key');
            sortState.dir = (sortState.key === key) ? -sortState.dir : 1;
            sortState.key = key;

            $('th[data-sort-key] .sort-icon').attr('class', 'fa-solid fa-sort text-[10px] text-gray-300 dark:text-gray-600 sort-icon');
            $(this).find('.sort-icon').attr('class', 'fa-solid fa-sort-' + (sortState.dir === 1 ? 'up' : 'down') + ' text-[10px] text-emerald-500 sort-icon');

            const $table = $(this).closest('table');
            const $blocks = $table.find('tbody.tenant-block').detach().get();

            $blocks.sort(function (a, b) {
                const av = key === 'tenant' ? $(a).data('tenant').toString() : parseFloat($(a).data(key));
                const bv = key === 'tenant' ? $(b).data('tenant').toString() : parseFloat($(b).data(key));

                const aEmpty = (typeof av === 'number' && isNaN(av));
                const bEmpty = (typeof bv === 'number' && isNaN(bv));
                if (aEmpty && bEmpty) return 0;
                if (aEmpty) return 1;
                if (bEmpty) return -1;

                if (av < bv) return -1 * sortState.dir;
                if (av > bv) return 1 * sortState.dir;
                return 0;
            });

            $table.find('tbody.category-header-row, .category-header-row').closest('tbody').hide();
            const $tfoot = $table.find('tfoot');
            $blocks.forEach(function (el) {
                if ($tfoot.length) $(el).insertBefore($tfoot); else $table.append(el);
            });
        });
    })();
</script>
@endunless
