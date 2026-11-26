<x-app-layout>
    <x-bq-table-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        <form id="bqForm" class="flex flex-col gap-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="csid" value="{{ $cs->csid }}">
            <input type="hidden" name="bqid" value="{{ $cs->bqid }}">
            <input type="hidden" name="cpny_id" value="{{ $cs->cpny_id }}">

            <!-- Header Card -->
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white"><span class="text-indigo-500"></span> {{ $cs->csid }} - Create BQ CS Kontrak</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Bill of quantity for this canvass sheet</p>
                                </div>
                            </div>
                        </div>
                    <div class="px-6 py-5">

                <!-- Grid Form -->
                <div class="flex flex-col gap-4 text-sm">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</span>
                            <div class="flex min-h-10 items-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                {{ $cs->cpny_id }}
                            </div>
                        </div>
                        <div>
                            <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</span>
                            <div class="flex min-h-10 items-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                {{ $cs->department_id }}
                            </div>
                        </div>
                        <div>
                            <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">BQ ID</span>
                            <div class="flex min-h-10 items-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                {{ $cs->bqid }}
                            </div>
                        </div>
                        <div>
                            <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPJ/K/T</span>
                            <div class="flex min-h-10 items-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                {{ $cs->sppbjktid }}
                            </div>
                        </div>
                        <div>
                            <span class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Requester</span>
                            <div class="flex min-h-10 items-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                {{ $cs->user_peminta }}
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

            <!-- BQ Details -->
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">BQ Detail</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Material and service prices per vendor</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('bqcs.downloadTemplate', $hash) }}"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 transition hover:border-green-600 hover:bg-green-600 hover:text-white dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-300">
                            Download Template Excel
                        </a>

                        <input type="file" id="bqImportFile" class="hidden" accept=".xlsx,.xls">

                        <button type="button" id="btnImportExcel"
                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                            Import Excel
                        </button>

                        <button type="button" id="btnAddRow"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60">
                            + Add Row
                        </button>
                            </div>
                        </div>
                    <div class="px-6 py-5">
                        <div class="relative overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">

                    <table class="text-body w-full table-auto text-left text-sm rtl:text-right" id="bqTable">
                        <colgroup>
                            <col style="width: 60px;">   {{-- No --}}
                            <col style="width: 60px;">   {{-- Line --}}
                            <col style="width: 420px;">  {{-- Description --}}
                            <col style="width: 100px;">  {{-- Qty --}}
                            <col style="width: 70px;">  {{-- UoM --}}
                            <col style="width: 60px;">
                            <col style="width: 150px;">  {{-- Estimates --}}

                            @foreach ($vendors as $v)
                                <col style="width: 300px;"> {{-- Vendor --}}
                            @endforeach

                            <col style="width: 80px;"> {{-- Action --}}
                        </colgroup>
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th class="border px-4 py-3 text-left font-semibold">No</th>
                                <th class="border px-4 py-3 text-left font-semibold">Line</th>
                                <th class="border px-4 py-3 text-left font-semibold">Description</th>
                                <th class="border px-4 py-3 text-left font-semibold">Qty</th>
                                <th class="border px-4 py-3 text-left font-semibold">UoM</th>
                                <th class="border px-4 py-3 text-left font-semibold">Duration</th>
                                <th class="border px-4 py-3 text-left font-semibold">Estimates</th>

                                @foreach ($vendors as $v)
                                    <th class="align-center border px-4 py-3 text-left">

                                        <div class="flex items-start justify-between gap-1">
                                            <div class="space-y-0.5">
                                                <div class="text-sm font-semibold">
                                                    {{ $v['name'] }}
                                                </div>
                                            </div>

                                            <!-- Tooltip -->
                                            <div class="group relative">
                                                <span
                                                    class="inline-flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-gray-300 text-[10px] font-bold">i</span>

                                                <div
                                                    class="absolute right-0 top-5 z-40 hidden w-56 rounded-md border bg-white p-3 text-sm shadow-lg group-hover:block dark:bg-gray-800">
                                                    <div><strong>Contact:</strong> {{ $v['cp'] ?: '-' }}
                                                    </div>
                                                    <div><strong>Phone:</strong> {{ $v['telp'] ?: '-' }}
                                                    </div>
                                                    <div><strong>Address:</strong> {{ $v['addr'] ?: '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </th>
                                @endforeach
                                <th class="border px-4 py-3 text-center font-semibold">Action</th>
                            </tr>
                        </thead>

                        <!-- BODY -->
                        <tbody class="#">
                            @foreach ($bqDetails as $d)
                                {{-- <tr class="block border-b md:table-row dark:border-gray-700"> --}}
                                <tr class="border-b dark:border-gray-700"data-removable="0" data-bq-source="0">


                                    <!-- No -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">No:</span>
                                        {{ $d->bq_no }}
                                    </td>

                                    <!-- Line -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Line:</span>
                                        {{ $d->bq_line_no }}
                                    </td>

                                    <!-- Description -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Description:</span>
                                        {{ $d->bq_descr }}
                                    </td>

                                    <!-- Qty -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Qty:</span>
                                        <input type="number" step="0.01" min="0"
                                            class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24"
                                            value="{{ number_format((float) ($d->qty ?? 0), 2, '.', '') }}">

                                    </td>

                                    <!-- UoM -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">UoM:</span>
                                        {{ $d->uom }}
                                    </td>

                                    {{-- <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Duration:</span>
                                        <span class="bq-duration">
                                            {{ (int) ($d->kontrak_duration_qty ?? 0) }}
                                        </span>
                                    </td> --}}
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Duration:</span>
                                        <input type="number" min="0" step="1"
                                            class="bq-duration-input w-full rounded-lg border px-2 py-1 text-right md:w-24"
                                            value="{{ (int) ($d->kontrak_duration_qty ?? 0) }}">
                                    </td>

                                    <!-- Estimates -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Estimates:</span>
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <label class="flex flex-col gap-1">
                                                <span>Est. Material</span>
                                                {{ number_format((float) ($d->est_material_price ?? 0), 2, ',', '.') }}
                                            </label>
                                            <label class="flex flex-col gap-1">
                                                <span>Est. Jasa</span>
                                                {{ number_format((float) ($d->est_jasa_price ?? 0), 2, ',', '.') }}
                                            </label>
                                        </div>
                                    </td>

                                    <!-- Vendor Columns -->
                                    @foreach ($vendors as $v)
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">{{ $v['name'] }}:</span>
                                            <div class="grid grid-cols-2 gap-3 text-sm">
                                                <label class="flex flex-col gap-1">
                                                    <span>Total Material</span>
                                                    <input type="number"
                                                        class="bq-price-mat w-full rounded-md border px-2 py-1 text-right">
                                                </label>
                                                <label class="flex flex-col gap-1">
                                                    <span>Total Jasa</span>
                                                    <input type="number"
                                                        class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right">
                                                </label>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="border px-4 py-2 text-center align-middle">
                                        <button type="button"
                                            class="btn-remove-row h-9 w-9 inline-flex shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300"
                                            disabled><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </td>


                                </tr>
                            @endforeach
                        </tbody>

                        <!-- FOOTER -->
                        <tfoot class="hidden bg-gray-100 md:table-footer-group dark:bg-gray-700">
                            <tr>
                                <td colspan="7" class="border px-4 py-4 text-right font-bold">Grand Total per Vendor
                                </td>
                                @foreach ($vendors as $i => $v)
                                    <td class="border px-4 py-4 text-left">

                                        <div>Total Material: <span class="sum-mat"
                                                data-vendor="{{ $i + 1 }}">0</span></div>
                                        <div>Total Jasa: <span class="sum-jsa"
                                                data-vendor="{{ $i + 1 }}">0</span>
                                        </div>
                                        <div class="mt-1 font-bold text-indigo-600">Grand Total: <span class="sum-grand"
                                                data-vendor="{{ $i + 1 }}">0</span></div>
                                    </td>
                                @endforeach
                                <td class="border px-4 py-4 text-left"></td>
                            </tr>
                        </tfoot>

                    </table>
                </div>


                <!-- Action Buttons -->
                    </div>
                    <div class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                    <a href="{{ url()->previous() }}"
                        class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 mr-auto">
                        Cancel
                    </a>
                    <button type="button" id="btnSaveBQ"
                        class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60">
                        Save BQ
                    </button>
                </div>
                </div>
    </div>
    </form>
    </div>


    <!-- Tambahkan di <head> atau sebelum </body> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        (function() {
            const vendors = @json($vendors);
            const $form = document.getElementById('bqForm');
            const $btn = document.getElementById('btnSaveBQ');

            function toFixed2(n) {
                n = Number(n || 0);
                return Math.round(n * 100) / 100;
            }

            function getCellValue(td) {
                const clone = td.cloneNode(true);
                clone.querySelectorAll('span').forEach(s => s.remove());
                return (clone.textContent || '').trim();
            }

            $btn.addEventListener('click', function() {
                // kumpulkan vendors untuk header (id + nama saja sudah cukup)
                const vHeader = vendors.slice(0, 6).map(v => ({
                    id: v.id,
                    name: v.name
                }));

                // kumpulkan detail
                const details = [];
                const tbodyRows = $form.querySelectorAll('tbody tr');
                tbodyRows.forEach((tr, rIdx) => {
                    const tds = tr.children;

                    function readCellTextOrInput(td, inputSelector) {
                        const inp = td.querySelector(inputSelector);
                        if (inp) return (inp.value || '').trim();
                        return getCellValue(td);
                    }

                    const bqNo = readCellTextOrInput(tds[0], '.bq-no');
                    const line = readCellTextOrInput(tds[1], '.bq-line');
                    const descr = readCellTextOrInput(tds[2], '.bq-descr');

                    const qtyEl = tds[3].querySelector('.bq-qty');
                    const qty = toFixed2(qtyEl ? qtyEl.value : 0);
                    const uom = readCellTextOrInput(tds[4], '.bq-uom');

                    // ✅ duration
                    // const durEl = tds[5]?.querySelector('.bq-duration') || tds[5]?.querySelector('.bq-duration-input');
                    const durEl = tds[5]?.querySelector('.bq-duration-input');
                    const duration = parseInt((durEl ? (durEl.value ?? durEl.textContent) : '0') || '0', 10) || 0;

                    // ✅ source flag dari <tr data-bq-source="0|1">
                    const bq_source = parseInt(tr.dataset.bqSource || '0', 10);

                    const rowVendors = [];
                    vendors.forEach((v, i) => {
                        const td = tds[7 + i]; // ✅ sebelumnya 6+i, sekarang 7+i
                        const mat = toFixed2(td.querySelector('.bq-price-mat').value);
                        const jsa = toFixed2(td.querySelector('.bq-price-jsa').value);
                        rowVendors.push({
                            idx: i + 1,
                            product_price: mat,
                            jasa_price: jsa
                        });
                    });

                    // details.push({
                    //     bq_no: bqNo,
                    //     bq_line_no: line,
                    //     bq_descr: descr,
                    //     qty: qty,
                    //     uom: uom,
                    //     kontrak_duration_qty: duration, // ✅ kirim duration
                    //     bq_source: bq_source,
                    //     vendor: rowVendors
                    // });

                    details.push({
                        bq_no: bqNo,
                        bq_line_no: line,
                        bq_descr: descr,
                        qty: qty,
                        uom: uom,
                        bq_source: bq_source,
                        bqtype: tr.dataset.bqtype || '',
                        kontrakcategory: tr.dataset.kontrakcategory || '',
                        kontrak_bq_id: tr.dataset.kontrakBqId || '',
                        kontrak_bq_type: tr.dataset.kontrakBqType || '',
                        kontrak_duration_qty: tr.dataset.kontrakDurationQty || 0,
                        vendor: rowVendors
                    });
                });


                const fd = new FormData($form);
                fd.append('vendors', JSON.stringify(vHeader));
                fd.append('details', JSON.stringify(details));

                fetch("{{ route('bqcs.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.ok) {
                            Swal.fire({
                                title: '✅ BQ Saved Successfully',
                                text: 'BQ ID: ' + res.bqid,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3193da',
                            }).then(() => {
                                window.location.href = "{{ url('/csjobs') }}";
                            });

                        } else {
                            Swal.fire({
                                title: '❌ Save Failed',
                                text: res.msg || 'Unknown error occurred.',
                                icon: 'error',
                                confirmButtonText: 'Close'
                            });
                        }
                    })

                    .catch(err => alert('Error: ' + err));
            });
        })();
    </script>
    <script>
        (function() {
            const vendors = @json($vendors);
            const VENDOR_OFFSET =
                7; // kolom vendor mulai dari index-td ke 5 (0-based): BQ No, Line, Descr, Qty, UoM -> 5
            const nf = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            function toNum(v) {
                const n = parseFloat(v);
                return isNaN(n) ? 0 : n;
            }

            function fmt(n) {
                return nf.format(toNum(n));
            }

            /** hitung ulang total utk 1 vendor (vendorIdx: 1..N) */
            function recalcVendor(vendorIdx) {
                let sumMat = 0,
                    sumJsa = 0;

                document.querySelectorAll('#bqForm tbody tr').forEach(tr => {
                    const tds = tr.children;

                    const qtyInput = tds[3].querySelector('.bq-qty');
                    const qty = toNum(qtyInput?.value || 0);

                    // ✅ duration ada di kolom index 5
                    const durEl = tds[5]?.querySelector('.bq-duration') || tds[5]?.querySelector('.bq-duration-input');
                    const duration = toNum(durEl ? (durEl.value ?? durEl.textContent) : 0);

                    const tdVendor = tds[VENDOR_OFFSET + (vendorIdx - 1)];
                    if (!tdVendor) return;

                    const mat = toNum(tdVendor.querySelector('.bq-price-mat')?.value || 0);
                    const jsa = toNum(tdVendor.querySelector('.bq-price-jsa')?.value || 0);

                    // ✅ rumus baru: qty * price * duration
                    sumMat += qty * mat * duration;
                    sumJsa += qty * jsa * duration;
                });

                // document.querySelectorAll('#bqForm tbody tr').forEach(tr => {
                //     const tds = tr.children;
                //     const qtyInput = tds[3].querySelector('.bq-qty');
                //     const qty = toNum(qtyInput?.value || 0);

                //     const tdVendor = tds[VENDOR_OFFSET + (vendorIdx - 1)];
                //     if (!tdVendor) return;

                //     const mat = toNum(tdVendor.querySelector('.bq-price-mat')?.value || 0);
                //     const jsa = toNum(tdVendor.querySelector('.bq-price-jsa')?.value || 0);

                //     sumMat += qty * mat;
                //     sumJsa += qty * jsa;
                // });

                const grand = sumMat + sumJsa;

                const matEl = document.querySelector(`.sum-mat[data-vendor="${vendorIdx}"]`);
                const jsaEl = document.querySelector(`.sum-jsa[data-vendor="${vendorIdx}"]`);
                const grandEl = document.querySelector(`.sum-grand[data-vendor="${vendorIdx}"]`);
                if (matEl) matEl.textContent = fmt(sumMat);
                if (jsaEl) jsaEl.textContent = fmt(sumJsa);
                if (grandEl) grandEl.textContent = fmt(grand);
            }

            /** hitung ulang semua vendor */
            function recalcAllVendors() {
                for (let i = 1; i <= Math.min(vendors.length, 6); i++) {
                    recalcVendor(i);
                }
            }

            // event: qty & price berubah -> recalc
            // document.getElementById('bqForm').addEventListener('input', (e) => {
            //     if (e.target.matches('.bq-qty,.bq-price-mat,.bq-price-jsa')) {
            //         recalcAllVendors();
            //     }
            // });
            document.getElementById('bqForm').addEventListener('input', (e) => {
                if (e.target.matches('.bq-qty,.bq-duration-input,.bq-price-mat,.bq-price-jsa')) {
                    recalcAllVendors();
                }
            });

            // kalkulasi awal saat halaman siap
            document.addEventListener('DOMContentLoaded', recalcAllVendors);
            // kalau script ini dimuat setelah DOM, panggil langsung juga:
            recalcAllVendors();
        })();
    </script>

    <x-bq-number-input />

    <script>
        (function() {
            const selector = '.bq-qty,.bq-price-mat,.bq-price-jsa';


            // Format ke 2 desimal saat blur; kosong → 0.00
            document.addEventListener('blur', function(e) {
                if (!e.target.matches(selector)) return;

                const raw = e.target.value.trim();
                const num = parseFloat(raw === '' ? '0' : raw);
                const fixed = isNaN(num) ? '0.00' : num.toFixed(2);
                e.target.value = fixed;

                // panggil kalkulasi ulang grand total (fungsi milikmu)
                try {
                    // kalau fungsi recalcAllVendors ada, panggil
                    if (typeof recalcAllVendors === 'function') recalcAllVendors();
                } catch (_) {}
            }, true);

            // Inisialisasi default value 0.00 bila ada input kosong saat load
            document.querySelectorAll(selector).forEach(el => {
                if (el.value.trim() === '') el.value = '0.00';
            });
        })();
    </script>

    <script>
        (function() {
            const vendors = @json($vendors);
            const tbody = document.querySelector('#bqForm tbody');

            // helper create td input text
            function tdInput(cls, placeholder = '', type = 'text', value = '') {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                const input = document.createElement('input');
                input.type = type;
                input.value = value;
                input.placeholder = placeholder;
                input.className = cls + ' w-full rounded-lg border px-2 py-1';
                td.appendChild(input);
                return td;
            }

            function tdText(value) {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                td.textContent = value ?? '';
                return td;
            }

            function buildVendorTd() {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                td.innerHTML = `
                    <div class="grid grid-cols-2 gap-3  text-sm ">
                        <label class="flex flex-col gap-1">
                            <span>Total Material</span>
                            <input type="number" class="bq-price-mat w-full rounded-md border px-2 py-1 text-right" value="0.00">
                        </label>
                        <label class="flex flex-col gap-1">
                            <span>Total Jasa</span>
                            <input type="number" class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right" value="0.00">
                        </label>
                    </div>
                `;
                return td;
            }

            function buildActionTd(removable) {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 text-center md:table-cell md:border';

                if (!removable) {
                    td.innerHTML = `
                        <button type="button"
                          class="btn-remove-row h-9 w-9 inline-flex shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300"
                        disabled><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>`;
                } else {
                    td.innerHTML = `
                        <button type="button"
                    class="btn-remove-row mt-4 h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>`;
                }
                return td;
            }

            // Add row handler
            document.getElementById('btnAddRow').addEventListener('click', function() {
                const tr = document.createElement('tr');
                tr.className = 'block border-b md:table-row dark:border-gray-700';
                tr.dataset.removable = "1";
                tr.dataset.bqSource = "1"; // ✅ input user


                // No (input)
                tr.appendChild(tdInput('bq-no', 'No', 'text', ''));
                // Line (input)
                tr.appendChild(tdInput('bq-line', 'Line', 'text', ''));
                // Description (input)
                tr.appendChild(tdInput('bq-descr', 'Description', 'text', ''));
                // Qty (input number)
                const tdQty = document.createElement('td');
                tdQty.className = 'block border px-4 py-2 md:table-cell md:border';
                tdQty.innerHTML =
                    `<input type="number" class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24" value="0.00">`;
                tr.appendChild(tdQty);

                // UoM (input)
                // tr.appendChild(tdInput('bq-uom', 'UoM', 'text', ''));

                // // Estimates (kosong / 0) - tidak perlu input, hanya display
                // const tdEst = document.createElement('td');
                // tdEst.className = 'block border px-4 py-2 md:table-cell md:border';
                // tdEst.innerHTML = `
                //     <div class="grid grid-cols-2 gap-3  text-sm ">
                //         <label class="flex flex-col gap-1">
                //             <span>Est. Material</span>
                //             <span>0,00</span>
                //         </label>
                //         <label class="flex flex-col gap-1">
                //             <span>Est. Jasa</span>
                //             <span>0,00</span>
                //         </label>
                //     </div>
                // `;
                // tr.appendChild(tdEst);

                // UoM (input)
                tr.appendChild(tdInput('bq-uom', 'UoM', 'text', ''));

                // ✅ Duration (input)
                const tdDur = document.createElement('td');
                tdDur.className = 'block border px-4 py-2 md:table-cell md:border';
                tdDur.innerHTML = `<input type="number" min="0" step="1" class="bq-duration-input w-full rounded-lg border px-2 py-1 text-right md:w-24" value="0">`;
                tr.appendChild(tdDur);

                // Estimates (display)
                const tdEst = document.createElement('td');
                tdEst.className = 'block border px-4 py-2 md:table-cell md:border';
                tdEst.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <label class="flex flex-col gap-1">
                            <span>Est. Material</span>
                            <span>0,00</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span>Est. Jasa</span>
                            <span>0,00</span>
                        </label>
                    </div>
                `;
                tr.appendChild(tdEst);

                // vendor columns
                vendors.forEach(() => {
                    tr.appendChild(buildVendorTd());
                });

                // action column
                tr.appendChild(buildActionTd(true));

                tbody.appendChild(tr);

                // trigger kalkulasi ulang total
                const evt = new Event('input', {
                    bubbles: true
                });
                tr.querySelector('.bq-qty')?.dispatchEvent(evt);
            });

            // Remove row handler (delegation)
            tbody.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-remove-row');
                if (!btn) return;

                const tr = btn.closest('tr');
                if (!tr) return;

                // hanya boleh kalau removable=1
                if (tr.dataset.removable !== "1") return;

                tr.remove();

                // trigger kalkulasi ulang total
                document.getElementById('bqForm').dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });
        })();
    </script>
    <script>
        (function() {
            const vendors = @json($vendors);
            const tbody = document.querySelector('#bqForm tbody');

            function toFixed2(n) {
                n = Number(n || 0);
                return isNaN(n) ? '0.00' : n.toFixed(2);
            }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, function(m) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    }[m];
                });
            }

            function buildImportedRow(row) {
                const tr = document.createElement('tr');
                tr.className = 'border-b dark:border-gray-700';
                tr.dataset.removable = String(Number(row.bq_source || 1) === 1 ? 1 : 0);
                tr.dataset.bqSource = String(row.bq_source ?? 1);

                const removable = tr.dataset.removable === '1';

                tr.innerHTML = `
                    <td class="border px-4 py-2">
                        <input type="text" class="bq-no w-full rounded-lg border px-2 py-1" value="${escapeHtml(row.bq_no)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="text" class="bq-line w-full rounded-lg border px-2 py-1" value="${escapeHtml(row.bq_line_no)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="text" class="bq-descr w-full rounded-lg border px-2 py-1" value="${escapeHtml(row.bq_descr)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="number" step="0.01" min="0"
                            class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24"
                            value="${toFixed2(row.qty)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="text" class="bq-uom w-full rounded-lg border px-2 py-1" value="${escapeHtml(row.uom)}">
                    </td>

                    <td class="border px-4 py-2">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <label class="flex flex-col gap-1">
                                <span>Est. Material</span>
                                <span>0,00</span>
                            </label>
                            <label class="flex flex-col gap-1">
                                <span>Est. Jasa</span>
                                <span>0,00</span>
                            </label>
                        </div>
                    </td>
                `;

                vendors.forEach((v, i) => {
                    const vendorRow = (row.vendor || []).find(x => Number(x.idx) === Number(v.idx)) || {};

                    const td = document.createElement('td');
                    td.className = 'block border px-4 py-2 md:table-cell md:border';
                    td.innerHTML = `
                        <span class="font-medium md:hidden">${escapeHtml(v.name)}:</span>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <label class="flex flex-col gap-1">
                                <span>Total Material</span>
                                <input type="number"
                                    class="bq-price-mat w-full rounded-md border px-2 py-1 text-right"
                                    value="${toFixed2(vendorRow.material_price)}">
                            </label>
                            <label class="flex flex-col gap-1">
                                <span>Total Jasa</span>
                                <input type="number"
                                    class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right"
                                    value="${toFixed2(vendorRow.jasa_price)}">
                            </label>
                        </div>
                    `;

                    tr.appendChild(td);
                });

                const actionTd = document.createElement('td');
                actionTd.className = 'border px-4 py-2 text-center align-middle';

                if (removable) {
                    actionTd.innerHTML = `
                        <button type="button"
                            class="btn-remove-row mt-4 h-9 w-9 inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    `;
                } else {
                    actionTd.innerHTML = `
                        <button type="button"
                            class="btn-remove-row h-9 w-9 inline-flex shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300"
                            disabled><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    `;
                }

                tr.appendChild(actionTd);

                return tr;
            }

            document.getElementById('btnImportExcel').addEventListener('click', function() {
                document.getElementById('bqImportFile').click();
            });

            document.getElementById('bqImportFile').addEventListener('change', function() {
                const file = this.files[0];

                if (!file) {
                    return;
                }

                const fd = new FormData();
                fd.append('file', file);
                fd.append('_token', '{{ csrf_token() }}');

                showOverlay('Importing Excel...');

                fetch("{{ route('bqcs.importTemplate', $hash) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (!response.ok) {
                            throw data;
                        }

                        return data;
                    })
                    .then(res => {
                        hideOverlay();

                        if (!res.ok) {
                            Swal.fire('Import gagal', res.msg || 'Import Excel gagal.', 'error');
                            return;
                        }

                        tbody.innerHTML = '';

                        (res.rows || []).forEach(row => {
                            tbody.appendChild(buildImportedRow(row));
                        });

                        if (typeof recalcAllVendors === 'function') {
                            recalcAllVendors();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Import berhasil',
                            text: `${res.rows.length} row berhasil dimuat ke table. Klik Save BQ untuk menyimpan.`,
                        });
                    })
                    .catch(err => {
                        hideOverlay();

                        Swal.fire({
                            icon: 'error',
                            title: 'Import gagal',
                            text: err?.msg || err?.message || 'Terjadi kesalahan saat import Excel.',
                        });
                    })
                    .finally(() => {
                        this.value = '';
                    });
            });
        })();
    </script>




</x-app-layout>
