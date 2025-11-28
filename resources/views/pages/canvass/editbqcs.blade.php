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
                        BQ CS Edit : 🆔 {{ $bq->bqid }}
                    </h2>
                </div>

                <div class="flex flex-col gap-4 text-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">Company</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $bq->cpny_id }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">CSID</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $bq->csid }}
                            </div>
                        </div>
                        <div>
                            <span class="block font-medium text-gray-700 dark:text-gray-300">SPPJ/K/T</span>
                            <div
                                class="rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                {{ $bq->sppjtid }}
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
                        <table class="min-w-full table-auto border text-sm text-gray-700 dark:text-gray-200"
                            id="bqTable">
                            <thead
                                class="bg-gray-100 text-gray-900 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-left font-semibold">No</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Line</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Description</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Qty</th>
                                    <th class="border px-4 py-3 text-left font-semibold">UoM</th>
                                    @foreach ($vendors as $v)
                                        <th class="align-center px-3 py-2 text-left">

                                            <div class="flex items-start justify-between gap-1">
                                                <div class="space-y-0.5">
                                                    <div class="text-sm font-semibold">
                                                        {{ $v['name'] }}
                                                    </div>

                                                    {{-- @if ($v['vendortop'])
                                                        <div class="text-xs text-gray-600 dark:text-gray-300">
                                                            Payment Term: <span
                                                                class="font-semibold">{{ $v['vendortop'] }}</span>
                                                        </div>
                                                    @endif --}}
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
                                </tr>
                            </thead>

                            <!-- BODY -->
                            <tbody class="block md:table-row-group">
                                @foreach ($details as $d)
                                    <tr
                                        class="transition odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800 dark:hover:bg-gray-700">
                                        <td class="border px-4 py-3">{{ $d->bq_no }}</td>
                                        <td class="border px-4 py-3">{{ $d->bq_line_no }}</td>
                                        <td class="border px-4 py-3">
                                            <textarea disabled
                                                class="bq-descr min-h-[40px] w-full cursor-not-allowed resize-none whitespace-normal break-words rounded-md border bg-gray-100 px-2 py-1 text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $d->bq_descr }}</textarea>

                                        </td>
                                        <td class="border px-4 py-3 text-center">
                                            <input type="number" step="0.01" min="0"
                                                class="bq-qty w-24 rounded-lg border px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                                value="{{ number_format((float) $d->qty, 2, '.', '') }}">
                                        </td>
                                        <td class="border px-4 py-3 text-center">
                                            <input type="text" disabled
                                                class="bq-uom w-20 cursor-not-allowed rounded-md border bg-gray-100 px-2 py-1 text-center text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400"
                                                value="{{ $d->uom }}">
                                        </td>

                                        @foreach ($vendors as $v)
                                            @php
                                                $i = $v['idx'];
                                                $unitMat = $d->{"vendorproductprice{$i}"} ?? 0; // harga/unit material
                                                $unitJsa = $d->{"vendorjasaprice{$i}"} ?? 0; // harga/unit jasa
                                                // kalau mau pakai total dari DB juga tersedia:
                                                // $totMat  = $d->{"vendortotalproductprice{$i}"} ?? 0;
                                                // $totJsa  = $d->{"vendortotaljasaprice{$i}"} ?? 0;
                                            @endphp
                                            <td class="border px-4 py-3 align-top">
                                                <div class="grid grid-cols-2 gap-3 text-xs">
                                                    <label class="flex flex-col gap-1">
                                                        <span class="font-medium text-gray-600 dark:text-gray-300">Harga
                                                            Material</span>
                                                        <input type="number" step="0.01" min="0"
                                                            value="{{ number_format((float) $unitMat, 2, '.', '') }}"
                                                            class="bq-price-mat w-full rounded-md border px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                    </label>
                                                    <label class="flex flex-col gap-1">
                                                        <span class="font-medium text-gray-600 dark:text-gray-300">Harga
                                                            Jasa</span>
                                                        <input type="number" step="0.01" min="0"
                                                            value="{{ number_format((float) $unitJsa, 2, '.', '') }}"
                                                            class="bq-price-jsa w-full rounded-md border px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                    </label>
                                                </div>
                                            </td>
                                        @endforeach

                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-gray-100 font-medium dark:bg-gray-700">
                                <tr>
                                    <td colspan="5" class="border px-4 py-4 text-right">Grand Total per Vendor</td>
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
                            class="rounded-lg bg-gray-200 px-4 py-2 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                            Back
                        </a>
                        <button type="button" id="btnSaveBQ"
                            class="rounded-lg bg-indigo-600 px-5 py-2 text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400">
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
            const VENDOR_OFFSET = 5;

            const nf = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const toNum = v => isNaN(parseFloat(v)) ? 0 : parseFloat(v);
            const toFixed2 = n => Math.round(Number(n || 0) * 100) / 100;

            function collectPayload() {
                const rows = [];
                document.querySelectorAll('#bqTable tbody tr').forEach(tr => {
                    const tds = tr.children;
                    const bq_no = tds[0].textContent.trim();
                    const line = tds[1].textContent.trim();
                    const descr = tds[2].querySelector('.bq-descr').value.trim();
                    const qty = toFixed2(tds[3].querySelector('.bq-qty').value);
                    const uom = tds[4].querySelector('.bq-uom').value.trim();

                    const rowVendors = [];
                    vendors.forEach((v, i) => {
                        const td = tds[VENDOR_OFFSET + i];
                        const mat = toFixed2(td.querySelector('.bq-price-mat').value);
                        const jsa = toFixed2(td.querySelector('.bq-price-jsa').value);
                        rowVendors.push({
                            idx: i + 1,
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
</x-app-layout>
