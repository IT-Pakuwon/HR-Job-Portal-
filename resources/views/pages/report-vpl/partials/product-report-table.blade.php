<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-linear-to-r from-white to-sky-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-sky-900/10">
        <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300">
                <i class="fa-solid fa-boxes-stacked text-xs"></i>
            </span>
            Laporan Stok Product
        </h2>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-700 shadow-sm dark:bg-sky-900/30 dark:text-sky-300">
            <i class="fa-solid fa-building"></i>
            {{ $cpnyid }}
            <span class="text-sky-300 dark:text-sky-600">&bull;</span>
            Bulan {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
        </span>
    </div>

    <div class="max-h-[70vh] overflow-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="sticky top-0 z-10 border-b-2 border-sky-100 bg-linear-to-b from-gray-50 to-gray-100/60 text-xs uppercase tracking-wide text-gray-500 shadow-sm dark:border-sky-900/40 dark:from-gray-900 dark:to-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-3 text-left font-semibold">No</th>
                    <th class="px-3 py-3 text-left font-semibold">Perusahaan</th>
                    <th class="px-3 py-3 text-left font-semibold">Image Product</th>
                    <th class="px-3 py-3 text-left font-semibold">Nama Barang</th>
                    <th class="px-3 py-3 text-left font-semibold">Expiry Date</th>
                    <th class="px-3 py-3 text-right font-semibold">Price</th>
                    <th class="px-3 py-3 text-right font-semibold">Total Nominal</th>
                    <th class="px-3 py-3 text-left font-semibold">Tgl Terima</th>
                    <th class="px-3 py-3 text-right font-semibold">Begin</th>
                    <th class="px-3 py-3 text-right font-semibold">In</th>
                    <th class="px-3 py-3 text-right font-semibold">Out</th>
                    <th class="px-3 py-3 text-right font-semibold">Ending</th>
                    <th class="px-3 py-3 text-right font-semibold">Price Out</th>
                    <th class="px-3 py-3 text-left font-semibold">Name</th>
                    <th class="px-3 py-3 text-left font-semibold">Department</th>
                    <th class="px-3 py-3 text-left font-semibold">Remarks In</th>
                    <th class="px-3 py-3 text-left font-semibold">Remarks Out</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @forelse($rows as $r)
                    @if(($r['type'] ?? null) === 'category_header')
                        <tr class="bg-linear-to-r from-sky-50 to-sky-50/30 dark:from-sky-900/25 dark:to-sky-900/5">
                            <td colspan="17" class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-1 rounded-full bg-sky-500"></span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-sky-700 dark:text-sky-300">
                                        {{ $r['category_label'] }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @continue
                    @endif

                    <tr class="text-gray-700 transition-colors hover:bg-sky-50/50 dark:text-gray-300 dark:hover:bg-sky-900/10">
                        <td class="px-3 py-2.5 align-top font-medium text-gray-900 dark:text-gray-100">{{ $r['no'] }}</td>
                        <td class="px-3 py-2.5 align-top text-gray-600 dark:text-gray-400">{{ $r['perusahaan'] }}</td>
                        <td class="px-3 py-2.5 align-top">
                            @if($r['photo_url'])
                                <button type="button" class="js-prodrpt-photo block h-12 w-12 cursor-zoom-in rounded-md ring-1 ring-gray-200 transition hover:ring-2 hover:ring-sky-400 dark:ring-gray-600" data-photo="{{ $r['photo_url'] }}" data-caption="{{ $r['nama_barang'] }}">
                                    <img src="{{ $r['photo_url'] }}" alt="{{ $r['nama_barang'] }}" class="h-12 w-12 rounded-md object-cover" onerror="this.closest('.js-prodrpt-photo').outerHTML='<span class=&quot;flex h-12 w-12 items-center justify-center rounded-md bg-gray-100 text-gray-300 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-500 dark:ring-gray-600&quot;><i class=&quot;fa-solid fa-image-slash&quot;></i></span>'">
                                </button>
                            @else
                                <span class="flex h-12 w-12 items-center justify-center rounded-md bg-gray-100 text-gray-300 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-gray-500 dark:ring-gray-600">
                                    <i class="fa-solid fa-image"></i>
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 align-top font-medium text-gray-900 dark:text-gray-100">{{ $r['nama_barang'] }}</td>
                        <td class="px-3 py-2.5 align-top tabular-nums text-gray-600 dark:text-gray-400">{{ $r['expired_date']?->format('d-M-y') ?? '-' }}</td>
                        <td class="px-3 py-2.5 align-top text-right tabular-nums text-gray-600 dark:text-gray-400">Rp {{ number_format($r['price'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2.5 align-top text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($r['total_nominal'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2.5 align-top tabular-nums text-gray-600 dark:text-gray-400">{{ $r['tgl_terima']?->format('d-M-y') ?? '-' }}</td>
                        <td class="px-3 py-2.5 align-top text-right tabular-nums text-gray-600 dark:text-gray-400">{{ number_format($r['begin'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2.5 align-top text-right">
                            @if($r['in'] > 0)
                                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold tabular-nums text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    <i class="fa-solid fa-arrow-up text-[9px]"></i>{{ number_format($r['in'], 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 align-top text-right">
                            @if($r['out'] > 0)
                                <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold tabular-nums text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                    <i class="fa-solid fa-arrow-down text-[9px]"></i>{{ number_format($r['out'], 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 align-top text-right tabular-nums font-semibold text-gray-900 dark:text-gray-100">{{ number_format($r['ending'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2.5 align-top text-right tabular-nums text-gray-600 dark:text-gray-400">Rp {{ number_format($r['price_out'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2.5 align-top text-gray-600 dark:text-gray-400">{{ $r['requester_name'] ?? '' }}</td>
                        <td class="px-3 py-2.5 align-top text-gray-600 dark:text-gray-400">{{ $r['department'] ?? '' }}</td>
                        <td class="px-3 py-2.5 align-top text-gray-600 dark:text-gray-400">{{ $r['remarks_in'] ?? '' }}</td>
                        <td class="px-3 py-2.5 align-top text-gray-600 dark:text-gray-400">{{ $r['remarks_out'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="px-3 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-500">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                                <span class="text-sm">No data for the selected company/period.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($rows))
                <tfoot class="sticky bottom-0 z-10 border-t-2 border-sky-100 bg-gray-50 dark:border-sky-900/40 dark:bg-gray-900">
                    <tr class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        <td colspan="6" class="px-3 py-3 text-right">Grand Total</td>
                        <td class="px-3 py-3 text-right tabular-nums text-sky-700 dark:text-sky-300">Rp {{ number_format(collect($rows)->where('type', 'detail')->sum('total_nominal'), 0, ',', '.') }}</td>
                        <td></td>
                        <td class="px-3 py-3 text-right tabular-nums text-sky-700 dark:text-sky-300">{{ number_format(collect($rows)->where('type', 'detail')->sum('begin'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-sky-700 dark:text-sky-300">{{ number_format(collect($rows)->where('type', 'detail')->sum('in'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-sky-700 dark:text-sky-300">{{ number_format(collect($rows)->where('type', 'detail')->sum('out'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-sky-700 dark:text-sky-300">{{ number_format(collect($rows)->where('type', 'detail')->sum('ending'), 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right tabular-nums text-sky-700 dark:text-sky-300">Rp {{ number_format(collect($rows)->where('type', 'detail')->sum('price_out'), 0, ',', '.') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

</div>

{{-- Click-to-preview lightbox for the Image Product thumbnails --}}
<div id="prodrpt_photoModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4" style="display:none">
    <div class="relative max-h-[85vh] max-w-2xl">
        <button type="button" id="prodrpt_photoModal_close" class="absolute -top-3 -right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white text-gray-700 shadow-md hover:bg-gray-100">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="prodrpt_photoModal_img" src="" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl">
        <p id="prodrpt_photoModal_caption" class="mt-2 text-center text-sm font-medium text-white"></p>
    </div>
</div>

<script>
    (function () {
        var $modal = $('#prodrpt_photoModal');

        function openPhotoModal(url, caption) {
            $('#prodrpt_photoModal_img').attr('src', url);
            $('#prodrpt_photoModal_caption').text(caption || '');
            $modal.css('display', 'flex');
        }

        function closePhotoModal() {
            $modal.css('display', 'none');
            $('#prodrpt_photoModal_img').attr('src', '');
        }

        // Namespaced + re-bound on every load since #prodrpt_table's HTML (including
        // this script) is fully replaced by AJAX each time the report is shown/exported.
        $(document).off('click.prodrptPhoto').on('click.prodrptPhoto', '.js-prodrpt-photo', function () {
            openPhotoModal($(this).data('photo'), $(this).data('caption'));
        });

        $(document).off('click.prodrptPhotoClose').on('click.prodrptPhotoClose', '#prodrpt_photoModal_close', closePhotoModal);

        $(document).off('click.prodrptPhotoOverlay').on('click.prodrptPhotoOverlay', '#prodrpt_photoModal', function (e) {
            if (e.target === this) {
                closePhotoModal();
            }
        });

        $(document).off('keydown.prodrptPhoto').on('keydown.prodrptPhoto', function (e) {
            if (e.key === 'Escape') {
                closePhotoModal();
            }
        });
    })();
</script>
