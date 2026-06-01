<x-app-layout>
    <style>
        #bqTable th,
        #bqTable td {
            vertical-align: top;
        }

        #bqTable .bq-no,
        #bqTable .bq-line,
        #bqTable .bq-uom {
            min-width: 0;
        }

        #bqTable .bq-descr {
            line-height: 1.4;
        }
    </style>
    <div class="max-w-9xl mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <form id="bqForm" class="flex flex-col gap-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="csid" value="{{ $cs->csid }}">
            <input type="hidden" name="bqid" value="{{ $cs->bqid }}">
            <input type="hidden" name="cpny_id" value="{{ $cs->cpny_id }}">

            <!-- Header Card -->
            <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                        🆔 {{ $cs->csid }} - Create BQ CS
                    </h2>
                </div>

                <div class="flex flex-col gap-4 text-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">Company</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $cs->cpny_id }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">Department</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $cs->department_id }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">BQ ID</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $cs->bqid }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">SPPJ/K/T</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $cs->sppbjktid }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">Requester</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $cs->user_peminta }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BQ Details -->
            <div class="flex w-full flex-col rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                <div class="flex justify-between">
                    <div
                        class="justify-center pb-4 text-sm font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                        BQ Detail
                    </div>
                    {{-- <div class="mb-3 flex justify-end">
                        <button type="button" id="btnAddRow"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                            + Add Row
                        </button>
                    </div> --}}
                    <div class="mb-3 flex flex-wrap justify-end gap-2">
                        <a href="{{ route('bqcs.downloadTemplate', $hash) }}"
                            class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Download Template Excel
                        </a>

                        <input type="file" id="bqImportFile" class="hidden" accept=".xlsx,.xls">

                        <button type="button" id="btnImportExcel"
                            class="rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">
                            Import Excel
                        </button>

                        <button type="button" id="btnAddRow"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                            + Add Row
                        </button>
                    </div>
                </div>

                <div class="rounded-base relative overflow-x-auto">
                    <table class="text-body w-full table-auto text-left text-sm rtl:text-right" id="bqTable">
                        <colgroup>
                            <col style="width: 60px;">   {{-- No --}}
                            <col style="width: 60px;">   {{-- Line --}}
                            <col style="width: 420px;">  {{-- Description --}}
                            <col style="width: 100px;">  {{-- Qty --}}
                            <col style="width: 70px;">  {{-- UoM --}}
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
                                <th class="border px-4 py-3 text-left font-semibold">Estimates</th>

                                @foreach ($vendors as $v)
                                    <th class="align-center border px-4 py-3 text-left">
                                        <div class="flex items-start justify-between gap-1">
                                            <div class="space-y-0.5">
                                                <div class="text-sm font-semibold">
                                                    {{ $v['name'] }}
                                                </div>
                                            </div>

                                            <div class="group relative">
                                                <span
                                                    class="inline-flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-gray-300 text-[10px] font-bold">i</span>

                                                <div
                                                    class="absolute right-0 top-5 z-40 hidden w-56 rounded-md border bg-white p-3 text-sm shadow-lg group-hover:block">
                                                    <div><strong>Contact:</strong> {{ $v['cp'] ?: '-' }}</div>
                                                    <div><strong>Phone:</strong> {{ $v['telp'] ?: '-' }}</div>
                                                    <div><strong>Address:</strong> {{ $v['addr'] ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="border px-4 py-3 text-center font-semibold">Action</th>
                            </tr>
                        </thead>

                        <tbody class="#">
                            @foreach ($bqDetails as $d)
                                <tr class="border-b dark:border-gray-700" data-removable="0" data-bq-source="0">
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">No:</span>
                                        {{ $d->bq_no }}
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Line:</span>
                                        {{ $d->bq_line_no }}
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Description:</span>
                                        {{ $d->bq_descr }}
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Qty:</span>
                                        <input type="number" step="0.01" min="0"
                                            class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24"
                                            value="{{ number_format((float) ($d->qty ?? 0), 2, '.', '') }}">
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">UoM:</span>
                                        {{ $d->uom }}
                                    </td>

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
                                            class="btn-remove-row h-9 w-9 cursor-not-allowed items-center justify-center rounded border border-gray-300 bg-gray-200/30 text-gray-400"
                                            disabled>
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="hidden bg-gray-100 md:table-footer-group dark:bg-gray-700">
                            <tr>
                                <td colspan="6" class="border px-4 py-4 text-right font-bold">Grand Total per Vendor
                                </td>
                                @foreach ($vendors as $i => $v)
                                    <td class="border px-4 py-4 text-left">
                                        <div>Total Material: <span class="sum-mat"
                                                data-vendor="{{ $i + 1 }}">0</span></div>
                                        <div>Total Jasa: <span class="sum-jsa"
                                                data-vendor="{{ $i + 1 }}">0</span></div>
                                        <div class="mt-1 font-bold text-indigo-600">Grand Total: <span class="sum-grand"
                                                data-vendor="{{ $i + 1 }}">0</span></div>
                                    </td>
                                @endforeach
                                <td class="border px-4 py-4 text-left"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div
                    class="flex justify-end gap-3 rounded-b-xl border-t border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-700/40">
                    <a href="{{ url()->previous() }}"
                        class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </a>

                    <button type="button" id="btnSaveBQ"
                        class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg id="btnSaveBQSpinner" class="hidden h-5 w-5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span id="btnSaveBQText">Save BQ</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        #loadingOverlay {
            z-index: 9999;
        }

        .swal2-container {
            z-index: 20000 !important;
        }
    </style>

    <div id="loadingOverlay" class="fixed inset-0 z-[9999] hidden bg-black/40">
        <div class="flex h-full w-full items-center justify-center">
            <div class="rounded-xl bg-white px-6 py-4 shadow-xl dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span id="loadingOverlayText" class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Saving BQ...
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function showOverlay(text = 'Saving BQ...') {
            const overlay = document.getElementById('loadingOverlay');
            const textEl = document.getElementById('loadingOverlayText');
            if (textEl) textEl.textContent = text;
            if (overlay) overlay.classList.remove('hidden');
        }

        function hideOverlay() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.add('hidden');
        }
    </script>

    <script>
        (function() {
            const vendors = @json($vendors);
            const $form = document.getElementById('bqForm');
            const $btn = document.getElementById('btnSaveBQ');
            const $btnText = document.getElementById('btnSaveBQText');
            const $btnSpinner = document.getElementById('btnSaveBQSpinner');
            const $btnAddRow = document.getElementById('btnAddRow');
            let isSubmitting = false;

            function toFixed2(n) {
                n = Number(n || 0);
                return Math.round(n * 100) / 100;
            }

            function getCellValue(td) {
                const clone = td.cloneNode(true);
                clone.querySelectorAll('span').forEach(s => s.remove());
                return (clone.textContent || '').trim();
            }

            function setSavingState(saving) {
                isSubmitting = saving;

                if ($btn) {
                    $btn.disabled = saving;
                    $btn.classList.toggle('opacity-60', saving);
                    $btn.classList.toggle('cursor-not-allowed', saving);
                }

                if ($btnAddRow) {
                    $btnAddRow.disabled = saving;
                    $btnAddRow.classList.toggle('opacity-60', saving);
                    $btnAddRow.classList.toggle('cursor-not-allowed', saving);
                }

                if ($btnText) {
                    $btnText.textContent = saving ? 'Saving...' : 'Save BQ';
                }

                if ($btnSpinner) {
                    $btnSpinner.classList.toggle('hidden', !saving);
                }

                if (saving) {
                    showOverlay('Saving BQ...');
                } else {
                    hideOverlay();
                }

                $form.querySelectorAll('input, select, textarea, button').forEach(el => {
                    if (el.id === 'btnSaveBQ') return;

                    if (saving) {
                        el.setAttribute('data-prev-disabled', el.disabled ? '1' : '0');
                        el.disabled = true;
                    } else {
                        const prev = el.getAttribute('data-prev-disabled');
                        if (prev === '0') el.disabled = false;
                        el.removeAttribute('data-prev-disabled');
                    }
                });
            }

            $btn.addEventListener('click', function() {
                if (isSubmitting) return;

                const vHeader = vendors.slice(0, 6).map(v => ({
                    id: v.id,
                    name: v.name
                }));

                const details = [];
                const tbodyRows = $form.querySelectorAll('tbody tr');

                tbodyRows.forEach((tr) => {
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
                    const bq_source = parseInt(tr.dataset.bqSource || '0', 10);

                    const rowVendors = [];
                    vendors.forEach((v, i) => {
                        const td = tds[6 + i];
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

                setSavingState(true);

                fetch("{{ route('bqcs.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    })
                    .then(async r => {
                        let data = {};
                        try {
                            data = await r.json();
                        } catch (e) {
                            data = {
                                ok: false,
                                msg: 'Response server tidak valid.'
                            };
                        }

                        if (!r.ok) {
                            throw data;
                        }

                        return data;
                    })
                    .then(res => {
                        setSavingState(false);

                        if (res.ok) {
                            Swal.fire({
                                title: '✅ BQ Saved Successfully',
                                text: 'BQ ID: ' + res.bqid,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#4F46E5',
                                allowOutsideClick: false,
                                allowEscapeKey: false
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
                    .catch(err => {
                        setSavingState(false);

                        Swal.fire({
                            title: '❌ Save Failed',
                            text: err?.msg || err?.message || 'Terjadi kesalahan saat menyimpan data.',
                            icon: 'error',
                            confirmButtonText: 'Close'
                        });
                    });
            });
        })();
    </script>

    <script>
        (function() {
            const vendors = @json($vendors);
            const VENDOR_OFFSET = 6;
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

            function recalcVendor(vendorIdx) {
                let sumMat = 0,
                    sumJsa = 0;

                document.querySelectorAll('#bqForm tbody tr').forEach(tr => {
                    const tds = tr.children;
                    const qtyInput = tds[3].querySelector('.bq-qty');
                    const qty = toNum(qtyInput?.value || 0);

                    const tdVendor = tds[VENDOR_OFFSET + (vendorIdx - 1)];
                    if (!tdVendor) return;

                    const mat = toNum(tdVendor.querySelector('.bq-price-mat')?.value || 0);
                    const jsa = toNum(tdVendor.querySelector('.bq-price-jsa')?.value || 0);

                    sumMat += qty * mat;
                    sumJsa += qty * jsa;
                });

                const grand = sumMat + sumJsa;

                const matEl = document.querySelector(`.sum-mat[data-vendor="${vendorIdx}"]`);
                const jsaEl = document.querySelector(`.sum-jsa[data-vendor="${vendorIdx}"]`);
                const grandEl = document.querySelector(`.sum-grand[data-vendor="${vendorIdx}"]`);
                if (matEl) matEl.textContent = fmt(sumMat);
                if (jsaEl) jsaEl.textContent = fmt(sumJsa);
                if (grandEl) grandEl.textContent = fmt(grand);
            }

            function recalcAllVendors() {
                for (let i = 1; i <= Math.min(vendors.length, 6); i++) {
                    recalcVendor(i);
                }
            }

            window.recalcAllVendors = recalcAllVendors;

            document.getElementById('bqForm').addEventListener('input', (e) => {
                if (e.target.matches('.bq-qty,.bq-price-mat,.bq-price-jsa')) {
                    recalcAllVendors();
                }
            });

            document.addEventListener('DOMContentLoaded', recalcAllVendors);
            recalcAllVendors();
        })();
    </script>

    <script>
        (function() {
            function allowOnlyDecimal(el) {
                el.addEventListener('keypress', function(e) {
                    const char = String.fromCharCode(e.which);
                    if (!/[0-9.]/.test(char)) {
                        e.preventDefault();
                    }
                });

                el.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                    const parts = this.value.split('.');
                    if (parts.length > 2) {
                        this.value = parts[0] + '.' + parts.slice(1).join('');
                    }
                });
            }

            document.querySelectorAll('.bq-qty,.bq-price-mat,.bq-price-jsa').forEach(el => {
                allowOnlyDecimal(el);
            });
        })();
    </script>

    <script>
        (function() {
            const selector = '.bq-qty,.bq-price-mat,.bq-price-jsa';
            const CTRL_KEYS = new Set(['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End']);

            document.addEventListener('keydown', function(e) {
                if (!e.target.matches(selector)) return;

                const key = e.key;

                if (CTRL_KEYS.has(key)) return;

                if (key === 'e' || key === 'E' || key === '+' || key === '-') {
                    e.preventDefault();
                    return;
                }

                if (key >= '0' && key <= '9') return;

                if (key === '.') {
                    const v = e.target.value || '';
                    if (v.includes('.')) e.preventDefault();
                    return;
                }

                e.preventDefault();
            });

            document.addEventListener('input', function(e) {
                if (!e.target.matches(selector)) return;

                let v = e.target.value || '';
                v = v.replace(/,/g, '.');
                v = v.replace(/[^0-9.]/g, '');

                const parts = v.split('.');
                if (parts.length > 2) {
                    v = parts[0] + '.' + parts.slice(1).join('');
                }
                e.target.value = v;
            });

            document.addEventListener('blur', function(e) {
                if (!e.target.matches(selector)) return;

                const raw = e.target.value.trim();
                const num = parseFloat(raw === '' ? '0' : raw);
                const fixed = isNaN(num) ? '0.00' : num.toFixed(2);
                e.target.value = fixed;

                try {
                    if (typeof recalcAllVendors === 'function') recalcAllVendors();
                } catch (_) {}
            }, true);

            document.querySelectorAll(selector).forEach(el => {
                if (el.value.trim() === '') el.value = '0.00';
            });
        })();
    </script>

    <script>
        (function() {
            const vendors = @json($vendors);
            const tbody = document.querySelector('#bqForm tbody');

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

            function buildVendorTd() {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                td.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 text-sm">
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
                          class="btn-remove-row h-9 w-9 cursor-not-allowed items-center justify-center rounded border border-gray-300 bg-gray-200/30 text-gray-400"
                          disabled>🗑️</button>`;
                } else {
                    td.innerHTML = `
                        <button type="button"
                            class="btn-remove-row mt-4 rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-60">
                            🗑️
                        </button>`;
                }
                return td;
            }

            document.getElementById('btnAddRow').addEventListener('click', function() {
                if (this.disabled) return;

                const tr = document.createElement('tr');
                tr.className = 'block border-b md:table-row dark:border-gray-700';
                tr.dataset.removable = "1";
                tr.dataset.bqSource = "1";

                tr.appendChild(tdInput('bq-no', 'No', 'text', ''));
                tr.appendChild(tdInput('bq-line', 'Line', 'text', ''));
                tr.appendChild(tdInput('bq-descr', 'Description', 'text', ''));

                const tdQty = document.createElement('td');
                tdQty.className = 'block border px-4 py-2 md:table-cell md:border';
                tdQty.innerHTML =
                    `<input type="number" class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24" value="0.00">`;
                tr.appendChild(tdQty);

                tr.appendChild(tdInput('bq-uom', 'UoM', 'text', ''));

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

                vendors.forEach(() => {
                    tr.appendChild(buildVendorTd());
                });

                tr.appendChild(buildActionTd(true));

                tbody.appendChild(tr);

                const evt = new Event('input', {
                    bubbles: true
                });
                tr.querySelector('.bq-qty')?.dispatchEvent(evt);
            });

            tbody.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-remove-row');
                if (!btn) return;
                if (btn.disabled) return;

                const tr = btn.closest('tr');
                if (!tr) return;
                if (tr.dataset.removable !== "1") return;

                tr.remove();

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
                        <input type="text" class="bq-no w-full rounded-lg border px-2 py-1 text-center" value="${escapeHtml(row.bq_no)}">
                    </td>

                    <td class="border px-4 py-2">
                       <input type="text" class="bq-line w-full rounded-lg border px-2 py-1 text-center" value="${escapeHtml(row.bq_line_no)}">
                    </td>

                    <td class="border px-4 py-2">
                        <textarea class="bq-descr min-h-[42px] w-full resize-y rounded-lg border px-2 py-1">${escapeHtml(row.bq_descr)}</textarea>
                    </td>

                    <td class="border px-4 py-2">
                        <input type="number" step="0.01" min="0"
                            class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24"
                            value="${toFixed2(row.qty)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="text" class="bq-uom w-full rounded-lg border px-2 py-1 text-center" value="${escapeHtml(row.uom)}">
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
                            class="btn-remove-row mt-4 rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                            🗑️
                        </button>
                    `;
                } else {
                    actionTd.innerHTML = `
                        <button type="button"
                            class="btn-remove-row h-9 w-9 cursor-not-allowed items-center justify-center rounded border border-gray-300 bg-gray-200/30 text-gray-400"
                            disabled>
                            🗑️
                        </button>
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
