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
                        Create BQ CS : 🆔 {{ $cs->csid }}</h2>
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
            <div class="flex w-full flex-col rounded-2xl bg-white shadow-md dark:bg-gray-800">
                <div class="p-4">
                    <div
                        class="border-b border-gray-200 pb-4 text-lg font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                        BQ Detail
                    </div>
                    <div class="overflow-x-auto md:overflow-visible">
                        <table class="min-w-full table-auto border text-sm text-gray-700 dark:text-gray-200">

                            <!-- HEADER -->
                            <thead
                                class="hidden bg-gray-100 text-gray-900 md:table-header-group dark:bg-gray-700 dark:text-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-left font-semibold">No</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Line</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Description</th>
                                    <th class="border px-4 py-3 text-center">Qty</th>
                                    <th class="border px-4 py-3 text-center">UoM</th>
                                    <th class="border px-4 py-3 text-left font-semibold">Estimates</th>

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
                                @foreach ($bqDetails as $d)
                                    <tr class="block border-b md:table-row dark:border-gray-700">

                                        <!-- No -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">No:</span>
                                            {{ $d->bq_no }}
                                        </td>

                                        <!-- Line -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Line:</span>
                                            {{ $d->bq_line_no }}
                                        </td>

                                        <!-- Description -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Description:</span>
                                            {{ $d->bq_descr }}
                                        </td>

                                        <!-- Qty -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Qty:</span>
                                            <input type="number"
                                                class="bq-qty w-full rounded-lg border px-2 py-1 text-right md:w-24">
                                        </td>

                                        <!-- UoM -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">UoM:</span>
                                            {{ $d->uom }}
                                        </td>

                                        <!-- Estimates -->
                                        <td class="block border px-4 py-2 md:table-cell md:border">
                                            <span class="font-medium md:hidden">Estimates:</span>
                                            <div class="grid grid-cols-2 gap-3 text-xs">
                                                <label class="flex flex-col gap-1">
                                                    <span>Est. Material</span>
                                                    <input
                                                        class="w-full rounded-md border bg-gray-100 px-2 py-1 text-right"
                                                        readonly>
                                                </label>
                                                <label class="flex flex-col gap-1">
                                                    <span>Est. Jasa</span>
                                                    <input
                                                        class="w-full rounded-md border bg-gray-100 px-2 py-1 text-right"
                                                        readonly>
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

                                    </tr>
                                @endforeach
                            </tbody>

                            <!-- FOOTER -->
                            <tfoot class="hidden bg-gray-100 md:table-footer-group dark:bg-gray-700">
                                <tr>
                                    <td colspan="6" class="border px-4 py-4 text-right font-bold">Grand Total per
                                        Vendor</td>
                                    @foreach ($vendors as $i => $v)
                                        <td class="border px-4 py-4 text-left">
                                            <div>Total Material: <span class="sum-mat"
                                                    data-vendor="{{ $i + 1 }}">0</span></div>
                                            <div>Total Jasa: <span class="sum-jsa"
                                                    data-vendor="{{ $i + 1 }}">0</span>
                                            </div>
                                            <div class="mt-1 font-bold text-indigo-600">Grand Total: <span
                                                    class="sum-grand" data-vendor="{{ $i + 1 }}">0</span></div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>

                        </table>
                    </div>


                    <!-- Action Buttons -->
                    <div
                        class="flex justify-end gap-3 rounded-b-xl border-t border-gray-200 p-4 dark:border-gray-700 dark:bg-gray-700/40">
                        <a href="{{ url()->previous() }}"
                            class="rounded-lg bg-gray-200 px-4 py-2 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                            Cancel
                        </a>
                        <button type="button" id="btnSaveBQ"
                            class="rounded-lg bg-indigo-600 px-5 py-2 text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400">
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

                    // const bqNo = tds[0].textContent.trim();
                    // const line = tds[1].textContent.trim();
                    // const descr = tds[2].textContent.trim();
                    // const qty = toFixed2(tds[3].querySelector('.bq-qty').value);
                    // const uom = tds[4].textContent.trim();
                    const bqNo = getCellValue(tds[0]); // cuma "1", bukan "No: 1"
                    const line = getCellValue(tds[1]); // cuma "1"
                    const descr = getCellValue(tds[2]); // hanya deskripsi
                    const qty = toFixed2(tds[3].querySelector('.bq-qty').value);
                    const uom = getCellValue(tds[4]); // cuma "LOT"


                    const rowVendors = [];
                    // kolom vendor mulai index 5
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
                    // .then(res => {
                    //     if (res.ok) {
                    //         alert('BQ saved: ' + res.bqid);
                    //         window.location.href = "{{ url('/csjobs') }}";
                    //     } else {
                    //         alert('Save failed: ' + (res.msg || ''));
                    //     }
                    // })
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



</x-app-layout>
