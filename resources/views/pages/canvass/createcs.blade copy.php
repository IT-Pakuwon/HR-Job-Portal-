<x-app-layout>
    <style>
        tfoot td {
            background: #fafafa;
        }

        /* warna abu muda */
        .sum-total,
        .sum-grand,
        .sum-selected {
            /* angka rata‑kanan */
            display: inline-block;
            min-width: 80px;
            text-align: right;
        }
    </style>

    <div class="p-4">
        {{-- ===== Header & tombol Add ===== --}}
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-sm font-semibold">Canvass Sheet : {{ $docno ?? 'CSxxxx' }}</h1>

            <button id="btnAddVendor"
                class="flex items-center gap-1 rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700">
                ➕ Add Vendor
            </button>
        </div>

        {{-- ===== Select2 (hidden) ===== --}}
        <select id="vendorSelect" class="hidden w-64"></select>

        {{-- ===== Tabel ===== --}}
        <div class="overflow-x-auto">
            <table id="cvTable" class="w-max table-auto whitespace-nowrap border">
                <thead>
                    <tr class="bg-gray-100 align-top">
                        <th class="w-64 border px-3 py-2">Description</th>
                        <th class="w-16 border px-3 py-2 text-center">Qty</th>
                        <th class="w-16 border px-3 py-2 text-center">UOM</th>
                        {{-- <th class="border px-3 py-2 w-28 text-right" id="th-total">Total</th> --}}
                    </tr>
                </thead>
                <tbody id="cvBody">
                    @foreach ($items as $row)
                        <tr>
                            <td class="border px-3 py-2">{{ $row->description }}</td>
                            <td class="qty border px-3 py-2 text-center" data-qty="{{ $row->qty }}">
                                {{ $row->qty }}</td>
                            <td class="border px-3 py-2 text-center">{{ $row->uom }}</td>

                            {{-- <td class="border px-3 py-2 text-right cell-total">0</td> --}}

                        </tr>
                    @endforeach
                </tbody>
                <!--  FOOTER  -->
                <tfoot>
                    <tr id="summaryRow" class="bg-gray-50 align-top">
                        <!-- 3 sel kosong awal (Description / Qty / UOM) -->
                        <td colspan="3" class="border px-3 py-2 text-right font-semibold">
                            Ringkasan
                        </td>
                        <!-- sel vendor akan disisipkan via JS -->
                    </tr>
                </tfoot>

            </table>

            <p id="emptyMsg" class="mt-2 text-xs italic text-gray-500">
                Belum ada vendor – klik “Add Vendor”.
            </p>


        </div>
    </div>

    {{-- CDN --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function() {

            /* =========================================================
               1. Ambil master vendor lalu isi <select>
            ========================================================= */
            let vendorMaster = []; // cache
            $.getJSON('/vendors', function(data) { // ← route API
                vendorMaster = data;
                data.forEach(v =>
                    $('#vendorSelect').append(new Option(v.name, v.id))
                );
            });

            /* =========================================================
               2. Inisialisasi Select2
            ========================================================= */
            $('#vendorSelect').select2({
                dropdownParent: $('body'),
                placeholder: 'Select',
                width: '250px'
            });

            /* =========================================================
               3. Event “Add Vendor”  → buka Select2
            ========================================================= */
            $('#btnAddVendor').on('click', function() {
                $('#vendorSelect').select2('open');
            });

            /* =========================================================
               4. Saat vendor dipilih → tambahkan kolom ke tabel
            ========================================================= */
            let vendorCount = 0; // berapa kolom vendor saat ini

            $('#vendorSelect').on('select2:select', function(e) {
                const id = Number(e.params.data.id);
                const vendor = vendorMaster.find(v => v.id === id);
                if (!vendor) return;

                // Cegah duplikat
                if ($('#th-vendor-' + id).length) {
                    alert('Vendor sudah ada');
                    $(this).val(null).trigger('change');
                    return;
                }

                // Tambah header kolom
                addHeader(id, vendor);
                // Tambah sel input harga di tiap baris existing
                addPriceCells(id);

                vendorCount++;
                $('#emptyMsg').toggle(vendorCount === 0);
                $(this).val(null).trigger('change'); // reset Select2
                $('#summaryBox').toggleClass('hidden', vendorCount === 0);
                recalcSummary(); // agar total dihitung ulang jika vendor dihapus

            });

            /* =========================================================
               5. Fungsi: tambah header vendor + tombol hapus
            ========================================================= */
            function addHeader(id, v) {
                const colWidth = '15rem';
                const $th = $(`
            <th id="th-vendor-${id}" class="border relative px-3 py-2" style="width:${colWidth}; max-width:${colWidth};">
                <div class="font-semibold text-center">${v.name}</div>

                <div class="text-xs text-gray-500 leading-4 mt-0.5 whitespace-normal break-words">
                    <div>✉️ ${v.contact ?? '-'}</div>
                    <div>☎️ ${v.phone ?? '-'}</div>
                    <div>🏠 ${v.address ?? '-'}</div>                  
                </div>

                <!-- dropdown Cara Bayar -->
                <div class="mt-1 flex justify-center">
                    <select name="cara_bayar_${id}"
                            class="cara-bayar border rounded text-xs px-1 py-0.5 focus:ring-indigo-500">
                        <option value="14D">14 Days</option>
                        <option value="30D">30 Days</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>

                <!-- tombol hapus -->
                <button class="btn-del absolute -top-1 -right-1
                            bg-red-600 text-white rounded-full
                            h-5 w-5 flex items-center justify-center text-xs hover:bg-red-700"
                        data-id="${id}">🗑</button>
            </th>
        `);

                $('#cvTable thead tr').append($th);

                const $sumTd = $(`
            <td id="td-sum-${id}" class="border px-3 py-2 text-xs space-y-1" style="width:${colWidth}; max-width:${colWidth};">
            <div><span class="font-semibold">Total&nbsp;&nbsp;</span><span class="sum-total"   >0</span></div>
            <div>
                PPN&nbsp;<input  type="number" class="sum-ppn w-12 border rounded px-1 text-right" value="0"> %
                PPh&nbsp;<input  type="number" class="sum-pph w-12 border rounded px-1 text-right ml-1" value="0"> %
            </div>
            <div><span class="font-semibold">Grand Total&nbsp;</span><span class="sum-grand"    >0</span></div>
            <div><span class="font-semibold">G.Total Selected </span><span class="sum-selected" >0</span></div>
            </td>
        `);

                $('#summaryRow').append($sumTd);

                /* — trigger hitung ulang bila PPN / PPh vendor ini diubah — */
                $sumTd.find('.sum-ppn, .sum-pph').on('input', function() {
                    recalcSummaryVendor(id);
                });
            }

            /* =========================================================
               6. Fungsi: tambah cell harga untuk kolom vendor baru
            ========================================================= */
            function addPriceCells(id) {
                $('#cvBody tr').each(function(rowIdx) {

                    /* ---- input harga ---- */
                    const $input = $(`
                <input type="number" min="0" step="0.01"
                    class="price-input w-full border rounded px-1 text-right"
                    data-row="${rowIdx}" data-vendor="${id}">
            `);

                    /* ---- sel tabel ---- */
                    const $td = $(`
                <td class="border px-3 py-2">
                    <div class="flex flex-col items-center gap-0.5 w-full">
                        <!-- elemen-elemen akan disisipkan di sini -->
                    </div>
                </td>
            `);

                    /* label total */
                    const $total = $(
                        `<small class="total-label text-right text-xs font-bold text-gray-600">0</small>`
                        );

                    /* radio “Pilih vendor”  */
                    const $radio = $(`
                <div class="flex justify-center mt-0.5">
                    <input type="radio"
                        name="selected_vendor_${rowIdx}"
                        value="${id}"
                        class="pick-vendor h-3 w-3 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                </div>
            `);

                    /* rangkai & tempel */
                    $td.find('div').append($input, $total, $radio);
                    $(this).append($td);

                    /* hitung total saat harga berubah */
                    $input.on('input', function() {
                        calcCellTotal($(this));
                    });
                });
            }

            function calcCellTotal($input) {
                const $tr = $input.closest('tr');
                const qty = Number($tr.find('.qty').data('qty') || 0);
                const price = Number($input.val() || 0);
                const total = qty * price;

                $input.next('.total-label').text(total.toLocaleString());

                recalcSummaryVendor(Number($input.data('vendor')));
            }

            /* =========================================================
               7. Hapus kolom vendor (delegasi event)
            ========================================================= */
            /* ---------- Hapus kolom vendor ---------- */
            $(document).on('click', '.btn-del', function() {
                const id = $(this).data('id');
                const $header = $('#th-vendor-' + id);
                const colIdx = $header.index(); // posisi sebelum di-remove

                $header.remove(); // hapus <th>
                $(`#td-sum-${id}`).remove();

                $('#cvBody tr').each(function() { // hapus sel se-kolom
                    $(this).children('td').eq(colIdx).remove();
                });

                vendorCount--;
                $('#emptyMsg').toggle(vendorCount === 0);
            });


        });
    </script>
    <script>
        function formatNum(n) {
            return (+n || 0).toLocaleString('id-ID');
        }
    </script>

    <script>
        function recalcSummary() {
            /* ---------- Total (semua baris x vendor) ---------- */
            let grandTotalCells = 0;
            $('#cvBody .total-label').each(function() {
                grandTotalCells += Number($(this).text().replace(/[^0-9\-]/g, '') || 0);
            });
            $('#sumTotal').text(formatNum(grandTotalCells));

            /* ---------- Grand Total +   PPN / PPh ---------- */
            const ppn = Number($('#inpPpn').val() || 0) / 100;
            const pph = Number($('#inpPph').val() || 0) / 100;
            const grand = grandTotalCells * (1 + ppn + pph); // contoh sederhana
            $('#sumGrand').text(formatNum(grand));

            /* ---------- Total Selected (tiap baris radio terpilih) ---------- */
            let sumSelected = 0;
            $('#cvBody tr').each(function() {
                const $sel = $(this).find('input.pick-vendor:checked');
                if ($sel.length) {
                    const $totalLbl = $sel.closest('td').find('.total-label');
                    sumSelected += Number($totalLbl.text().replace(/[^0-9\-]/g, '') || 0);
                }
            });
            $('#sumSelected').text(formatNum(sumSelected));
        }
    </script>
    <script>
        // $(document).on('change', '.pick-vendor', recalcSummary);
        $(document).on('change', '.pick-vendor', function() {
            const vid = Number($(this).val());
            recalcSummaryVendor(vid);
        });

        $('#inpPpn, #inpPph').on('input', recalcSummary);
    </script>

    <script>
        function recalcSummaryVendor(vendorId) {
            // 1️⃣ total semua baris × harga vendor ini
            let total = 0;
            $(`input.price-input[data-vendor="${vendorId}"]`).each(function() {
                const price = Number($(this).val() || 0);
                const qty = Number($(this).closest('tr').find('.qty').data('qty') || 0);
                total += price * qty;
            });

            const $sumCell = $(`#td-sum-${vendorId}`);
            $sumCell.find('.sum-total').text(formatNum(total));

            // 2️⃣ grand total
            const ppn = Number($sumCell.find('.sum-ppn').val() || 0) / 100;
            const pph = Number($sumCell.find('.sum-pph').val() || 0) / 100;
            const grand = total * (1 + ppn + pph);
            $sumCell.find('.sum-grand').text(formatNum(grand));

            // 3️⃣ total selected (hanya baris yg radio‑nya menunjuk vendor ini)
            let selTotal = 0;
            $('#cvBody tr').each(function() {
                const picked = $(this).find(`input.pick-vendor:checked`).val();
                if (Number(picked) === vendorId) {
                    const lbl = $(this).find(`input.price-input[data-vendor="${vendorId}"]`)
                        .next('.total-label');
                    selTotal += Number(lbl.text().replace(/[^0-9]/g, '') || 0);
                }
            });
            $sumCell.find('.sum-selected').text(formatNum(selTotal));
        }
    </script>

</x-app-layout>
