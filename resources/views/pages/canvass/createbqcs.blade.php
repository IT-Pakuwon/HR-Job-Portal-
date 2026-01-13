<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <form id="bqForm" class="flex flex-col gap-4" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="csid" value="{{ $cs->csid }}">
            <input type="hidden" name="bqid" value="{{ $cs->bqid }}">
            <input type="hidden" name="cpny_id" value="{{ $cs->cpny_id }}">

            <!-- Header Card -->
            <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-xl font-extrabold text-gray-800 dark:text-white"><span
                            class="text-indigo-500"></span>
                        🆔 {{ $cs->csid }} - Create BQ CS</h2>
                </div>

                <!-- Grid Form -->
                <div class="flex flex-col gap-4 text-sm">
                    <!-- Row 1 -->
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
            <div class="flex w-full flex-col rounded-2xl bg-white p-4 shadow-md dark:bg-gray-800">
                <div class="flex justify-between">
                    <div
                        class="justify-center pb-4 text-lg font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                        BQ Detail
                    </div>
                    <div class="mb-3 flex justify-end">
                        <button type="button" id="btnAddRow"
                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            + Add Row
                        </button>
                    </div>

                </div>
                <div class="rounded-base relative overflow-x-auto">

                    <table class="text-body w-full table-auto text-left text-sm rtl:text-right" id="bqTable">
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

                                    <!-- Estimates -->
                                    <td class="border px-4 py-2">
                                        <span class="font-medium md:hidden">Estimates:</span>
                                        <div class="grid grid-cols-2 gap-3 text-xs">
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
                                            <div class="grid grid-cols-2 gap-3 text-xs">
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

                        <!-- FOOTER -->
                        <tfoot class="hidden bg-gray-100 md:table-footer-group dark:bg-gray-700">
                            <tr>
                                <td colspan="6" class="border px-4 py-4 text-right font-bold">Grand Total per Vendor
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
                <div
                    class="flex justify-end gap-3 rounded-b-xl border-t border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-700/40">
                    <a href="{{ url()->previous() }}"
                        class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </a>
                    <button type="button" id="btnSaveBQ"
                        class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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

                    // ✅ source flag dari <tr data-bq-source="0|1">
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

                    details.push({
                        bq_no: bqNo,
                        bq_line_no: line,
                        bq_descr: descr,
                        qty: qty,
                        uom: uom,
                        bq_source: bq_source, // ✅ controller tinggal pakai ini
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
                                confirmButtonColor: '#4F46E5',
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
                6; // kolom vendor mulai dari index-td ke 5 (0-based): BQ No, Line, Descr, Qty, UoM -> 5
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

            /** hitung ulang semua vendor */
            function recalcAllVendors() {
                for (let i = 1; i <= Math.min(vendors.length, 6); i++) {
                    recalcVendor(i);
                }
            }

            // event: qty & price berubah -> recalc
            document.getElementById('bqForm').addEventListener('input', (e) => {
                if (e.target.matches('.bq-qty,.bq-price-mat,.bq-price-jsa')) {
                    recalcAllVendors();
                }
            });

            // kalkulasi awal saat halaman siap
            document.addEventListener('DOMContentLoaded', recalcAllVendors);
            // kalau script ini dimuat setelah DOM, panggil langsung juga:
            recalcAllVendors();
        })();
    </script>

    <script>
        (function() {
            // Batasi input hanya angka + titik
            function allowOnlyDecimal(el) {
                el.addEventListener('keypress', function(e) {
                    const char = String.fromCharCode(e.which);
                    // hanya izinkan angka (0-9) dan titik (.)
                    if (!/[0-9.]/.test(char)) {
                        e.preventDefault();
                    }
                });
                el.addEventListener('input', function(e) {
                    // hapus semua karakter non-angka/non-titik
                    this.value = this.value.replace(/[^0-9.]/g, '');
                    // hanya boleh 1 titik
                    const parts = this.value.split('.');
                    if (parts.length > 2) {
                        this.value = parts[0] + '.' + parts.slice(1).join('');
                    }
                });
            }

            // pasang ke semua field qty & price
            document.querySelectorAll('.bq-qty,.bq-price-mat,.bq-price-jsa').forEach(el => {
                allowOnlyDecimal(el);
            });
        })();
    </script>

    <script>
        (function() {
            const selector = '.bq-qty,.bq-price-mat,.bq-price-jsa';

            // Izinkan tombol kontrol
            const CTRL_KEYS = new Set(['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End']);

            // Cegah input tidak valid di keydown
            document.addEventListener('keydown', function(e) {
                if (!e.target.matches(selector)) return;

                const key = e.key;

                // izinkan tombol kontrol
                if (CTRL_KEYS.has(key)) return;

                // blokir e/E/+/- (notasi scientific & tanda)
                if (key === 'e' || key === 'E' || key === '+' || key === '-') {
                    e.preventDefault();
                    return;
                }

                // angka OK
                if (key >= '0' && key <= '9') return;

                // titik desimal: hanya boleh satu
                if (key === '.') {
                    const v = e.target.value || '';
                    if (v.includes('.')) e.preventDefault();
                    return;
                }

                // selain itu -> blok
                e.preventDefault();
            });

            // Sanitasi saat input (hapus selain digit/titik, merge >1 titik jadi satu)
            document.addEventListener('input', function(e) {
                if (!e.target.matches(selector)) return;

                let v = e.target.value || '';
                // ganti koma → titik (kalau ada)
                v = v.replace(/,/g, '.');
                // buang karakter non angka/titik
                v = v.replace(/[^0-9.]/g, '');

                // pastikan hanya 1 titik
                const parts = v.split('.');
                if (parts.length > 2) {
                    v = parts[0] + '.' + parts.slice(1).join('');
                }
                e.target.value = v;
            });

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
                    <div class="grid grid-cols-2 gap-3 text-xs">
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
                    class="btn-remove-row mt-4 rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                        🗑️
                        </button>`;
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
                tr.appendChild(tdInput('bq-uom', 'UoM', 'text', ''));

                // Estimates (kosong / 0) - tidak perlu input, hanya display
                const tdEst = document.createElement('td');
                tdEst.className = 'block border px-4 py-2 md:table-cell md:border';
                tdEst.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 text-xs">
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




</x-app-layout>
