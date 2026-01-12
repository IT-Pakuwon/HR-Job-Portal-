<x-app-layout>
    <style>
        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            /* akan ditampilkan via JS */
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        /* Kartu spinner di tengah */
        #loadingSpinnerContainer .loading-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        /* Spinner dual ring */
        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            /* indigo-500 */
            animation: spin 1s linear infinite;
            position: relative;
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
            /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em;
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinReverse {
            to {
                transform: rotate(-360deg);
            }
        }

        @keyframes blink {
            0% {
                opacity: .3;
                transform: translateY(0);
            }

            20% {
                opacity: 1;
                transform: translateY(-2px);
            }

            100% {
                opacity: .3;
                transform: translateY(0);
            }
        }
    </style>
    <div class="max-w-9xl mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <form id="bqForm" class="flex flex-col gap-8" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="bqid" value="{{ $bq->bqid }}">
            <input type="hidden" name="cpny_id" value="{{ $bq->cpny_id }}">

            <!-- Header Card -->
            <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">
                        🆔 {{ $bq->bqid }} - {{ $bq->csid }} - BQ CS Edit
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
            <div class="flex w-full flex-col rounded-2xl bg-white shadow-md dark:bg-gray-800">
                <div class="p-4">
                    <div
                        class="border-b border-gray-200 pb-4 text-lg font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                        BQ Detail
                    </div>

                    <div class="overflow-x-auto md:overflow-visible">
                        <div class="mb-3 flex justify-end">
                            <button type="button" id="btnAddRow"
                                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                + Add Row
                            </button>
                        </div>

                        <table class="min-w-full table-auto border text-sm text-gray-700 dark:text-gray-200"
                            id="bqTable">
                            <thead
                                class="hidden bg-gray-100 text-gray-900 md:table-header-group dark:bg-gray-700 dark:text-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-left font-semibold">No</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Line</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Description</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Qty</th>
                                    <th class="border px-4 py-3 text-left font-semibold">UoM</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Estimates</th>
                                    @foreach ($vendors as $v)
                                        <th class="align-center px-3 py-2 text-left">

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
                                                        class="absolute right-0 top-5 z-40 hidden w-56 rounded-md border bg-white p-3 text-xs shadow-lg group-hover:block">
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
                            <tbody class="block md:table-row-group">
                                @foreach ($details as $d)
                                    {{-- <tr class="block border-b md:table-row dark:border-gray-700"> --}}
                                    @php
                                        // bq_source: 0 = source awal, 1 = input/manual
                                        $removable = ((int)($d->bq_source ?? 0) === 1) ? 1 : 0;
                                    @endphp

                                    <tr class="block border-b md:table-row dark:border-gray-700" data-removable="{{ $removable }}"
                                        data-source="{{ (int)($d->bq_source ?? 0) }}">

                                        <!-- No -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">No:</span>
                                            <span class="bq-no-text">{{ $d->bq_no }}</span>
                                        </td>

                                        <!-- Line -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Line:</span>
                                            <span class="bq-line-text">{{ $d->bq_line_no }}</span>
                                        </td>


                                        {{-- <!-- No -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">No:</span>
                                            {{ $d->bq_no }}
                                        </td>

                                        <!-- Line -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Line:</span>
                                            {{ $d->bq_line_no }}
                                        </td> --}}

                                        <!-- Description (plain text like Create) -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Description:</span>
                                            <div
                                                class="bq-descr whitespace-normal break-words text-gray-800 dark:text-gray-200">
                                                {{ $d->bq_descr }}
                                            </div>
                                        </td>

                                        <!-- Qty (same as Create) -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Qty:</span>
                                            <input type="number" step="0.01" min="0"
                                                class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                                value="{{ number_format((float) $d->qty, 2, '.', '') }}">
                                        </td>

                                        <!-- UoM (plain text like Create) -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">UoM:</span>
                                            <div class="bq-uom text-center text-gray-800 md:w-20 dark:text-gray-200">
                                                {{ $d->uom }}
                                            </div>
                                        </td>

                                        <!-- Estimates (same as Create) -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Estimates:</span>

                                            <div class="grid grid-cols-2 gap-3 text-xs">
                                                <div class="flex flex-col gap-1">
                                                    <span>Est. Material</span>
                                                    <span class="text-gray-800 dark:text-gray-200">
                                                        {{ number_format((float) ($d->est_material_price ?? 0), 2, ',', '.') }}
                                                    </span>
                                                </div>

                                                <div class="flex flex-col gap-1">
                                                    <span>Est. Jasa</span>
                                                    <span class="text-gray-800 dark:text-gray-200">
                                                        {{ number_format((float) ($d->est_jasa_price ?? 0), 2, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Vendor Columns -->
                                        @foreach ($vendors as $v)
                                            @php
                                                $i = $v['idx'];
                                                $unitMat = $d->{"vendorproductprice{$i}"} ?? 0;
                                                $unitJsa = $d->{"vendorjasaprice{$i}"} ?? 0;
                                            @endphp

                                            <td class="block border px-4 py-2 md:table-cell md:border">
                                                <span class="font-medium md:hidden">{{ $v['name'] }}:</span>

                                                <div class="grid grid-cols-2 gap-3 text-xs">
                                                    <label class="flex flex-col gap-1">
                                                        <span>Total Material</span>
                                                        <input type="number" step="0.01" min="0"
                                                            value="{{ number_format((float) $unitMat, 2, '.', '') }}"
                                                            class="bq-price-mat w-full rounded-md border px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                    </label>

                                                    <label class="flex flex-col gap-1">
                                                        <span>Total Jasa</span>
                                                        <input type="number" step="0.01" min="0"
                                                            value="{{ number_format((float) $unitJsa, 2, '.', '') }}"
                                                            class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                    </label>
                                                </div>
                                            </td>
                                        @endforeach
                                        <td class="block border px-4 py-2 text-center md:table-cell md:border">
                                            @if ((int)($d->bq_source ?? 0) === 1)
                                                <button type="button"
                                                    class="btn-remove-row rounded-md bg-red-600 px-3 py-1 text-xs text-white hover:bg-red-700">
                                                    Remove
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn-remove-row cursor-not-allowed rounded-md bg-gray-300 px-3 py-1 text-xs text-gray-700 opacity-60"
                                                    disabled>
                                                    Remove
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>




                            <tfoot class="bg-gray-100 font-medium dark:bg-gray-700">
                                <tr>
                                    <td colspan="6" class="border px-4 py-4 text-right">Grand Total per Vendor</td>
                                    @foreach ($vendors as $i => $v)
                                        <td class="border px-4 py-4 text-left">
                                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                                Total Material: <span class="sum-mat font-semibold"
                                                    data-vendor="{{ $i + 1 }}">0</span>
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                                Total Jasa: <span class="sum-jsa font-semibold"
                                                    data-vendor="{{ $i + 1 }}">0</span>
                                            </div>
                                            <div class="mt-1 font-bold text-indigo-600 dark:text-indigo-400">
                                                Grand Total : <span class="sum-grand"
                                                    data-vendor="{{ $i + 1 }}">0</span>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div
                        class="flex justify-end gap-3 rounded-b-xl border-t border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-700/40">
                        <a href="{{ url()->previous() }}"
                            class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Back
                        </a>
                        <button type="button" id="btnSaveBQ"
                            class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    <script>
        (function() {
            const vendors = @json($vendors); // dari controller (ambil vendor via TrCS)

            const $form = document.getElementById('bqForm');
            const $btn = document.getElementById('btnSaveBQ');
            // const VENDOR_OFFSET = 5;
            const VENDOR_OFFSET = 6;


            const nf = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const toNum = v => isNaN(parseFloat(v)) ? 0 : parseFloat(v);
            const toFixed2 = n => Math.round(Number(n || 0) * 100) / 100;

            function cellText(td) {
                return (td?.textContent || '').trim();
            }

            function cellTextOrInput(td, inputSelector) {
                const inp = td?.querySelector(inputSelector);
                if (inp) return (inp.value || '').trim();
                return cellText(td);
            }

            function readNo(tr, tds) {
                const inp = tds[0]?.querySelector('.bq-no');
                if (inp) return (inp.value || '').trim();
                return (tds[0]?.querySelector('.bq-no-text')?.textContent || '').trim();
            }

            function readLine(tr, tds) {
                const inp = tds[1]?.querySelector('.bq-line');
                if (inp) return (inp.value || '').trim();
                return (tds[1]?.querySelector('.bq-line-text')?.textContent || '').trim();
            }

            function collectPayload() {
                const rows = [];
                document.querySelectorAll('#bqTable tbody tr').forEach(tr => {
                    const tds = tr.children;

                    const bq_no = readNo(tr, tds);
                    const line  = readLine(tr, tds);

                    const descrInp = tds[2]?.querySelector('.bq-descr-input');
                    const descrDiv = tds[2]?.querySelector('.bq-descr');
                    const descr = (descrInp ? descrInp.value : (descrDiv ? descrDiv.textContent : '')).trim();

                    const qty = toFixed2(tds[3].querySelector('.bq-qty')?.value || 0);

                    const uomInp = tds[4]?.querySelector('.bq-uom-input');
                    const uomDiv = tds[4]?.querySelector('.bq-uom');
                    const uom = (uomInp ? uomInp.value : (uomDiv ? uomDiv.textContent : '')).trim();

                    // bq_source dari dataset row (kalau sudah kamu set)
                    const bq_source = parseInt(tr.dataset.source || '0', 10);

                    const rowVendors = [];
                    vendors.forEach((v, i) => {
                        const td = tds[VENDOR_OFFSET + i];
                        const mat = toFixed2(td?.querySelector('.bq-price-mat')?.value || 0);
                        const jsa = toFixed2(td?.querySelector('.bq-price-jsa')?.value || 0);
                        rowVendors.push({ idx: i + 1, product_price: mat, jasa_price: jsa });
                    });

                    rows.push({
                        bq_no,
                        bq_line_no: line,
                        bq_descr: descr,
                        qty,
                        uom,
                        bq_source,
                        vendor: rowVendors
                    });
                });
                return rows;
            }




            // function collectPayload() {
            //     const rows = [];
            //     document.querySelectorAll('#bqTable tbody tr').forEach(tr => {
            //         const tds = tr.children;
            //         const bq_no = tds[0].textContent.trim();
            //         const line = tds[1].textContent.trim();
            //         const descr = tds[2].querySelector('.bq-descr').value.trim();
            //         const qty = toFixed2(tds[3].querySelector('.bq-qty').value);
            //         const uom = tds[4].querySelector('.bq-uom').value.trim();

            //         const rowVendors = [];
            //         vendors.forEach((v, i) => {
            //             const td = tds[VENDOR_OFFSET + i];
            //             const mat = toFixed2(td.querySelector('.bq-price-mat').value);
            //             const jsa = toFixed2(td.querySelector('.bq-price-jsa').value);
            //             rowVendors.push({
            //                 idx: i + 1,
            //                 product_price: mat,
            //                 jasa_price: jsa
            //             });
            //         });

            //         rows.push({
            //             bq_no,
            //             bq_line_no: line,
            //             bq_descr: descr,
            //             qty,
            //             uom,
            //             vendor: rowVendors
            //         });
            //     });
            //     return rows;
            // }

            function recalcVendor(idx) {
                let sumMat = 0,
                    sumJsa = 0;
                document.querySelectorAll('#bqTable tbody tr').forEach(tr => {
                    const qty = toNum(tr.querySelector('.bq-qty')?.value || 0);
                    const td = tr.children[VENDOR_OFFSET + (idx - 1)];
                    const mat = toNum(td.querySelector('.bq-price-mat')?.value || 0);
                    const jsa = toNum(td.querySelector('.bq-price-jsa')?.value || 0);
                    sumMat += qty * mat;
                    sumJsa += qty * jsa;
                });
                document.querySelector(`.sum-mat[data-vendor="${idx}"]`).textContent = nf.format(sumMat);
                document.querySelector(`.sum-jsa[data-vendor="${idx}"]`).textContent = nf.format(sumJsa);
                document.querySelector(`.sum-grand[data-vendor="${idx}"]`).textContent = nf.format(sumMat + sumJsa);
            }

            function recalcAll() {
                for (let i = 1; i <= Math.min(vendors.length, 6); i++) recalcVendor(i);
            }

            document.getElementById('bqTable').addEventListener('input', e => {
                if (e.target.matches('.bq-qty,.bq-price-mat,.bq-price-jsa')) recalcAll();
            });
            document.addEventListener('DOMContentLoaded', recalcAll);

            $btn.addEventListener('click', async function() {
                try {
                    const fd = new FormData($form);

                    // kirim vendors juga (minimal id & name) → WAJIB agar lolos validasi controller
                    const vendorsSlim = vendors.slice(0, 6).map(v => ({
                        id: v.id ?? v.vendor_id ?? null,
                        name: v.name ?? v.vendor_name ?? ''
                    }));

                    fd.append('vendors', JSON.stringify(vendorsSlim));
                    fd.append('details', JSON.stringify(collectPayload()));

                    // kalau route update pakai PUT, sertakan _method
                    fd.append('_method', 'PUT');

                    showOverlay('Submitting');

                    const res = await fetch("{{ route('bqcs.update', $hash_id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json' // pastikan server balas JSON saat error
                        },
                        body: fd
                    });

                    // aman-kan parsing: cek content-type
                    const ct = res.headers.get('content-type') || '';
                    const data = ct.includes('application/json') ? await res.json() : {
                        ok: false,
                        msg: await res.text()
                    };

                    if (res.ok && data.ok) {
                        hideOverlay();
                        toastr.success('BQ updated: ' + data.bqid);
                        window.location.href = "/editcs/{{ $cs_eid }}";
                        // alert('BQ updated: ' + data.bqid);
                    } else {
                        alert('Update failed: ' + (data.msg || res.statusText));
                        // console.debug(data);
                    }
                } catch (err) {
                    alert('Error: ' + err);
                }
            });
        })();
    </script>


    <script>
        // filter input numerik decimal (qty & price)
        (function() {
            const selector = '.bq-qty,.bq-price-mat,.bq-price-jsa';
            const CTRL_KEYS = new Set(['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End']);
            document.addEventListener('keydown', e => {
                if (!e.target.matches(selector)) return;
                const k = e.key;
                if (CTRL_KEYS.has(k)) return;
                if (k === 'e' || k === 'E' || k === '+' || k === '-') {
                    e.preventDefault();
                    return;
                }
                if (k >= '0' && k <= '9') return;
                if (k === '.') {
                    const v = e.target.value || '';
                    if (v.includes('.')) e.preventDefault();
                    return;
                }
                e.preventDefault();
            });
            document.addEventListener('input', e => {
                if (!e.target.matches(selector)) return;
                let v = e.target.value || '';
                v = v.replace(/,/g, '.').replace(/[^0-9.]/g, '');
                const parts = v.split('.');
                if (parts.length > 2) {
                    v = parts[0] + '.' + parts.slice(1).join('');
                }
                e.target.value = v;
            });
        })();
    </script>

    <script>
        (function() {
            const vendors = @json($vendors);
            const tbody = document.querySelector('#bqTable tbody');
            const btnAdd = document.getElementById('btnAddRow');

            function tdInput(cls, placeholder, type='text', value='') {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                const input = document.createElement('input');
                input.type = type;
                input.placeholder = placeholder || '';
                input.value = value;
                input.className = cls + ' w-full rounded-lg border px-2 py-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200';
                td.appendChild(input);
                return td;
            }

            function tdQty() {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                td.innerHTML = `
                    <input type="number" step="0.01" min="0"
                        class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        value="0.00">
                `;
                return td;
            }

            function tdEstimatesZero() {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                td.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="flex flex-col gap-1">
                            <span>Est. Material</span><span class="text-gray-800 dark:text-gray-200">0,00</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span>Est. Jasa</span><span class="text-gray-800 dark:text-gray-200">0,00</span>
                        </div>
                    </div>
                `;
                return td;
            }

            function tdVendor() {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';
                td.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <label class="flex flex-col gap-1">
                            <span>Total Material</span>
                            <input type="number" step="0.01" min="0"
                                class="bq-price-mat w-full rounded-md border px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                value="0.00">
                        </label>
                        <label class="flex flex-col gap-1">
                            <span>Total Jasa</span>
                            <input type="number" step="0.01" min="0"
                                class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                value="0.00">
                        </label>
                    </div>
                `;
                return td;
            }

            function tdAction(removable) {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 text-center md:table-cell md:border';

                if (!removable) {
                    td.innerHTML = `
                    <button type="button"
                        class="btn-remove-row cursor-not-allowed rounded-md bg-gray-300 px-3 py-1 text-xs text-gray-700 opacity-60"
                        disabled>Remove</button>`;
                } else {
                    td.innerHTML = `
                    <button type="button"
                        class="btn-remove-row rounded-md bg-red-600 px-3 py-1 text-xs text-white hover:bg-red-700">
                        Remove
                    </button>`;
                }
                return td;
            }

            // Add row
            btnAdd?.addEventListener('click', () => {
                const tr = document.createElement('tr');
                tr.className = 'block border-b md:table-row dark:border-gray-700';
                tr.dataset.removable = "1";
                tr.dataset.source = "1";

                tr.appendChild(tdInput('bq-no', 'No'));
                tr.appendChild(tdInput('bq-line', 'Line'));
                tr.appendChild(tdInput('bq-descr-input', 'Description'));
                tr.appendChild(tdQty());
                tr.appendChild(tdInput('bq-uom-input', 'UoM'));
                tr.appendChild(tdEstimatesZero());

                vendors.forEach(() => tr.appendChild(tdVendor()));
                tr.appendChild(tdAction(true));

                tbody.appendChild(tr);

                // trigger recalc via input event (biar footer update)
                tr.querySelector('.bq-qty')?.dispatchEvent(new Event('input', { bubbles: true }));
            });

            // Remove row (delegation)
            tbody?.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-remove-row');
                if (!btn) return;

                const tr = btn.closest('tr');
                if (!tr) return;

                if (tr.dataset.removable !== "1") return; // existing row tidak boleh

                tr.remove();
                document.getElementById('bqTable')?.dispatchEvent(new Event('input', { bubbles: true }));
            });
        })();
    </script>

</x-app-layout>
