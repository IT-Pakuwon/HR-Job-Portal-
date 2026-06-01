<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <form id="bqForm" class="flex flex-col gap-8" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="bqid" value="{{ $bq->bqid }}">
            <input type="hidden" name="cpny_id" value="{{ $bq->cpny_id }}">

            <!-- Header Card -->
            <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
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
                        <a href="{{ route('bqcs.downloadEditTemplate', $hash_id) }}"
                            class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                            Download Current Excel
                        </a>

                        <input type="file" id="bqImportFile" class="hidden" accept=".xlsx,.xls">

                        <button type="button" id="btnImportExcel"
                            class="rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-60">
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
                            @foreach ($details as $d)
                                @php
                                    $removable = (int) ($d->bq_source ?? 0) === 1 ? 1 : 0;
                                @endphp

                                <tr class="border-b dark:border-gray-700" data-removable="{{ $removable }}"
                                    data-source="{{ (int) ($d->bq_source ?? 0) }}">

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">No:</span>
                                        <span class="bq-no-text">{{ $d->bq_no }}</span>
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Line:</span>
                                        <span class="bq-line-text">{{ $d->bq_line_no }}</span>
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Description:</span>
                                        <div
                                            class="bq-descr whitespace-normal break-words text-gray-800 dark:text-gray-200">
                                            {{ $d->bq_descr }}
                                        </div>
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Qty:</span>
                                        <input type="number" step="0.01" min="0"
                                            class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                            value="{{ number_format((float) $d->qty, 2, '.', '') }}">
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">UoM:</span>
                                        <div class="bq-uom text-center text-gray-800 md:w-20 dark:text-gray-200">
                                            {{ $d->uom }}
                                        </div>
                                    </td>

                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Estimates:</span>

                                        <div class="grid grid-cols-2 gap-3 text-sm">
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

                                    @foreach ($vendors as $v)
                                        @php
                                            $i = $v['idx'];
                                            $unitMat = $d->{"vendorproductprice{$i}"} ?? 0;
                                            $unitJsa = $d->{"vendorjasaprice{$i}"} ?? 0;
                                        @endphp

                                        <td class="border px-4 py-2">
                                            <span class="font-medium md:hidden">{{ $v['name'] }}:</span>

                                            <div class="grid grid-cols-2 gap-3 text-sm">
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

                                    <td class="border px-4 py-2 text-center align-middle">
                                        @if ((int) ($d->bq_source ?? 0) === 1)
                                            <button type="button" title="Remove row"
                                                class="btn-remove-row mt-4 rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-60">
                                                🗑️
                                            </button>
                                        @else
                                            <button type="button" title="Cannot remove" disabled
                                                class="mx-auto flex h-9 w-9 cursor-not-allowed items-center justify-center rounded border border-gray-300 bg-gray-200/30 text-gray-400">
                                                🗑️
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
                                        <div class="flex flex-row justify-between gap-6">
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                Total Material: <span class="sum-mat font-semibold"
                                                    data-vendor="{{ $i + 1 }}">0</span>
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                                Total Jasa: <span class="sum-jsa font-semibold"
                                                    data-vendor="{{ $i + 1 }}">0</span>
                                            </div>
                                        </div>
                                        <div class="mt-1 font-bold text-indigo-600 dark:text-indigo-400">
                                            Grand Total : <span class="sum-grand"
                                                data-vendor="{{ $i + 1 }}">0</span>
                                        </div>
                                    </td>
                                @endforeach
                                <td class="border"> </td>
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
                        class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg id="btnSaveBQSpinner" class="hidden h-5 w-5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span id="btnSaveBQText">Save</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            background: rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(2px);
        }

        #loadingSpinnerContainer .loading-card {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            color: #111827;
            padding: 16px 20px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .18);
            min-width: 220px;
        }

        #loadingSpinnerContainer .loading-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 9999px;
            animation: spin 0.8s linear infinite;
        }

        #loadingSpinnerContainer .loading-text {
            font-size: 14px;
            font-weight: 600;
        }

        .loading-ellipsis span {
            animation: blink 1.4s infinite both;
        }

        .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s;
        }

        .swal2-container {
            z-index: 20000 !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes blink {

            0%,
            80%,
            100% {
                opacity: 0.2;
            }

            40% {
                opacity: 1;
            }
        }
    </style>

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
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
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
            const VENDOR_OFFSET = 6;
            let isSubmitting = false;

            const nf = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            const toNum = v => isNaN(parseFloat(v)) ? 0 : parseFloat(v);
            const toFixed2 = n => Math.round(Number(n || 0) * 100) / 100;

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
                    $btnText.textContent = saving ? 'Saving...' : 'Save';
                }

                if ($btnSpinner) {
                    $btnSpinner.classList.toggle('hidden', !saving);
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

                if (saving) {
                    showOverlay('Saving BQ');
                } else {
                    hideOverlay();
                }
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
                    const line = readLine(tr, tds);

                    const descrInp = tds[2]?.querySelector('.bq-descr-input');
                    const descrDiv = tds[2]?.querySelector('.bq-descr');
                    const descr = (descrInp ? descrInp.value : (descrDiv ? descrDiv.textContent : '')).trim();

                    const qty = toFixed2(tds[3].querySelector('.bq-qty')?.value || 0);

                    const uomInp = tds[4]?.querySelector('.bq-uom-input');
                    const uomDiv = tds[4]?.querySelector('.bq-uom');
                    const uom = (uomInp ? uomInp.value : (uomDiv ? uomDiv.textContent : '')).trim();

                    const bq_source = parseInt(tr.dataset.source || '0', 10);

                    const rowVendors = [];
                    vendors.forEach((v, i) => {
                        const td = tds[VENDOR_OFFSET + i];
                        const mat = toFixed2(td?.querySelector('.bq-price-mat')?.value || 0);
                        const jsa = toFixed2(td?.querySelector('.bq-price-jsa')?.value || 0);
                        rowVendors.push({
                            idx: Number(v.idx || (i + 1)),
                            product_price: mat,
                            jasa_price: jsa
                        });
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

            function recalcVendor(idx) {
                let sumMat = 0,
                    sumJsa = 0;

                document.querySelectorAll('#bqTable tbody tr').forEach(tr => {
                    const qty = toNum(tr.querySelector('.bq-qty')?.value || 0);
                    const td = tr.children[VENDOR_OFFSET + (idx - 1)];
                    const mat = toNum(td?.querySelector('.bq-price-mat')?.value || 0);
                    const jsa = toNum(td?.querySelector('.bq-price-jsa')?.value || 0);
                    sumMat += qty * mat;
                    sumJsa += qty * jsa;
                });

                const elMat = document.querySelector(`.sum-mat[data-vendor="${idx}"]`);
                const elJsa = document.querySelector(`.sum-jsa[data-vendor="${idx}"]`);
                const elGrand = document.querySelector(`.sum-grand[data-vendor="${idx}"]`);

                if (elMat) elMat.textContent = nf.format(sumMat);
                if (elJsa) elJsa.textContent = nf.format(sumJsa);
                if (elGrand) elGrand.textContent = nf.format(sumMat + sumJsa);
            }

            // function recalcAll() {
            //     for (let i = 1; i <= Math.min(vendors.length, 6); i++) recalcVendor(i);
            // }
            window.recalcAll = function() {
                for (let i = 1; i <= Math.min(vendors.length, 6); i++) recalcVendor(i);
            };

            document.getElementById('bqTable').addEventListener('input', e => {
                if (e.target.matches('.bq-qty,.bq-price-mat,.bq-price-jsa')) recalcAll();
            });

            // document.addEventListener('DOMContentLoaded', recalcAll);
            // recalcAll();
            document.addEventListener('DOMContentLoaded', window.recalcAll);
            window.recalcAll();

            $btn.addEventListener('click', async function() {
                if (isSubmitting) return;

                try {
                    const fd = new FormData($form);

                    const vendorsSlim = vendors.slice(0, 6).map(v => ({
                        id: v.id ?? v.vendor_id ?? null,
                        name: v.name ?? v.vendor_name ?? ''
                    }));

                    fd.append('vendors', JSON.stringify(vendorsSlim));
                    fd.append('details', JSON.stringify(collectPayload()));
                    fd.append('_method', 'PUT');

                    setSavingState(true);

                    const res = await fetch("{{ route('bqcs.update', $hash_id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: fd
                    });

                    const ct = res.headers.get('content-type') || '';
                    const data = ct.includes('application/json') ? await res.json() : {
                        ok: false,
                        msg: await res.text()
                    };

                    setSavingState(false);

                    if (res.ok && data.ok) {
                        await Swal.fire({
                            title: '✅ BQ Updated Successfully',
                            text: 'BQ ID: ' + data.bqid,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#4F46E5',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });

                        window.location.href = "/editcs/{{ $cs_eid }}";
                    } else {
                        await Swal.fire({
                            title: '❌ Update Failed',
                            text: data.msg || res.statusText || 'Unknown error.',
                            icon: 'error',
                            confirmButtonText: 'Close'
                        });
                    }
                } catch (err) {
                    setSavingState(false);

                    await Swal.fire({
                        title: '❌ Error',
                        text: err?.message || String(err),
                        icon: 'error',
                        confirmButtonText: 'Close'
                    });
                }
            });
        })();
    </script>

    <script>
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

            function tdInput(cls, placeholder, type = 'text', value = '') {
                const td = document.createElement('td');
                td.className = 'block border px-4 py-2 md:table-cell md:border';

                const input = document.createElement('input');
                input.type = type;
                input.placeholder = placeholder || '';
                input.value = value;
                input.className = cls +
                    ' w-full rounded-lg border px-2 py-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200';

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
                    <div class="grid grid-cols-2 gap-3 text-sm">
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
                    <div class="grid grid-cols-2 gap-3 text-sm">
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

            btnAdd?.addEventListener('click', () => {
                if (btnAdd.disabled) return;

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

                tr.querySelector('.bq-qty')?.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });

            tbody?.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-remove-row');
                if (!btn) return;
                if (btn.disabled) return;

                const tr = btn.closest('tr');
                if (!tr) return;
                if (tr.dataset.removable !== "1") return;

                tr.remove();

                document.getElementById('bqTable')?.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            });
        })();
    </script>
    <script>
        (function() {
            const vendors = @json($vendors);
            const tbody = document.querySelector('#bqTable tbody');

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

            function buildVendorTd(vendorRow) {
                const td = document.createElement('td');
                td.className = 'border px-4 py-2';

                td.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <label class="flex flex-col gap-1">
                            <span>Total Material</span>
                            <input type="number" step="0.01" min="0"
                                class="bq-price-mat w-full rounded-md border px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                value="${toFixed2(vendorRow?.product_price ?? vendorRow?.material_price ?? 0)}">
                        </label>

                        <label class="flex flex-col gap-1">
                            <span>Total Jasa</span>
                            <input type="number" step="0.01" min="0"
                                class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                value="${toFixed2(vendorRow?.jasa_price ?? 0)}">
                        </label>
                    </div>
                `;

                return td;
            }

            function buildActionTd(removable) {
                const td = document.createElement('td');
                td.className = 'border px-4 py-2 text-center align-middle';

                if (removable) {
                    td.innerHTML = `
                        <button type="button" title="Remove row"
                            class="btn-remove-row mt-4 rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-60">
                            🗑️
                        </button>
                    `;
                } else {
                    td.innerHTML = `
                        <button type="button" title="Cannot remove" disabled
                            class="mx-auto flex h-9 w-9 cursor-not-allowed items-center justify-center rounded border border-gray-300 bg-gray-200/30 text-gray-400">
                            🗑️
                        </button>
                    `;
                }

                return td;
            }

            function buildImportedRow(row) {
                const tr = document.createElement('tr');

                const source = Number(row.bq_source ?? row.source ?? 1);
                const removable = source === 1;

                tr.className = 'border-b dark:border-gray-700';
                tr.dataset.removable = removable ? '1' : '0';
                tr.dataset.source = String(source);

                tr.innerHTML = `
                    <td class="border px-4 py-2">
                        <input type="text"
                            class="bq-no w-full rounded-lg border px-2 py-1 text-center dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            value="${escapeHtml(row.bq_no)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="text"
                            class="bq-line w-full rounded-lg border px-2 py-1 text-center dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            value="${escapeHtml(row.bq_line_no)}">
                    </td>

                    <td class="border px-4 py-2">
                        <textarea
                            class="bq-descr-input min-h-[42px] w-full resize-y rounded-lg border px-2 py-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">${escapeHtml(row.bq_descr)}</textarea>
                    </td>

                    <td class="border px-4 py-2">
                        <input type="number" step="0.01" min="0"
                            class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            value="${toFixed2(row.qty)}">
                    </td>

                    <td class="border px-4 py-2">
                        <input type="text"
                            class="bq-uom-input w-full rounded-lg border px-2 py-1 text-center dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            value="${escapeHtml(row.uom)}">
                    </td>

                    <td class="border px-4 py-2">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex flex-col gap-1">
                                <span>Est. Material</span>
                                <span class="text-gray-800 dark:text-gray-200">${escapeHtml(row.est_material_price_fmt || '0,00')}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span>Est. Jasa</span>
                                <span class="text-gray-800 dark:text-gray-200">${escapeHtml(row.est_jasa_price_fmt || '0,00')}</span>
                            </div>
                        </div>
                    </td>
                `;

                vendors.forEach((v, i) => {
                    const idx = Number(v.idx || (i + 1));
                    const vendorRow = (row.vendor || []).find(x => Number(x.idx) === idx) || {};
                    tr.appendChild(buildVendorTd(vendorRow));
                });

                tr.appendChild(buildActionTd(removable));

                return tr;
            }

            async function chooseImportMode() {
                const result = await Swal.fire({
                    title: 'Import Excel',
                    text: 'Pilih cara import data Excel.',
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Replace All Details',
                    denyButtonText: 'Append Rows',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#7c3aed',
                    denyButtonColor: '#059669',
                });

                if (result.isConfirmed) return 'replace';
                if (result.isDenied) return 'append';
                return null;
            }

            document.getElementById('btnImportExcel')?.addEventListener('click', function() {
                document.getElementById('bqImportFile')?.click();
            });

            document.getElementById('bqImportFile')?.addEventListener('change', async function() {
                const file = this.files[0];

                if (!file) {
                    return;
                }

                const mode = await chooseImportMode();

                if (!mode) {
                    this.value = '';
                    return;
                }

                const fd = new FormData();
                fd.append('file', file);
                fd.append('_token', '{{ csrf_token() }}');

                showOverlay('Importing Excel');

                fetch("{{ route('bqcs.importEditTemplate', $hash_id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
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

                        if (mode === 'replace') {
                            tbody.innerHTML = '';
                        }

                        (res.rows || []).forEach(row => {
                            tbody.appendChild(buildImportedRow(row));
                        });

                        if (typeof recalcAll === 'function') {
                            recalcAll();
                        } else {
                            document.getElementById('bqTable')?.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Import berhasil',
                            text: `${res.rows.length} row berhasil dimuat. Klik Save untuk menyimpan ke database.`,
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