<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Purchase Order — PT. Artisan Wahyu</title>
    <style>
        :root {
            --black: #111827;
            --gray-700: #374151;
            --gray-600: #4b5563;
            --gray-500: #6b7280;
            --gray-300: #d1d5db;
            --border: #cbd5e1;
            --indigo: #4f46e5;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--black);
            background: #f9fafb;
        }

        @page {
            size: A4;
            margin: 18mm 16mm;
        }

        .page {
            background: #fff;
            margin: 0 auto 16px;
            padding: 20px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
        }

        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: start;
            margin-bottom: 10px;
        }

        .title {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: .2px;
        }

        .subtitle {
            font-size: 12px;
            color: var(--gray-700);
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid var(--border);
            border-radius: 999px;
            font-size: 11px;
        }

        .section {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .section h3 {
            font-size: 12px;
            margin-bottom: 8px;
        }

        .kv {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 2px 10px;
            font-size: 12px;
        }

        .kv dt {
            color: var(--gray-600);
        }

        .kv dd {
            margin: 0;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-size: 12px;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--black);
        }

        tbody td {
            font-size: 12px;
            padding: 8px;
            border-bottom: 1px dashed var(--border);
            vertical-align: top;
        }

        .ar {
            text-align: right;
        }

        .row {
            display: flex;
            gap: 12px;
            /* spacing between columns */
            flex-wrap: wrap;
            /* allows wrapping on small screens */
        }

        .section.col {
            flex: 1;
            /* each column takes equal width */
            min-width: 280px;
            /* prevents collapsing when printing */
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px;
        }

        .kv {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 2px 10px;
            font-size: 12px;
        }

        .kv dt {
            color: var(--gray-600);
        }

        .kv dd {
            margin: 0;
            font-weight: 600;
        }


        .col {
            flex: 1;
        }

        .col-2 {
            flex: 0 0 50%;
        }

        .notes {
            font-size: 11px;
            line-height: 1.45;
        }

        .notes ol {
            margin: 6px 0 0 16px;
            padding: 0;
        }

        .totals {
            margin-top: 8px;
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }

        .totals table {
            width: 100%;
        }

        .totals td {
            padding: 6px 8px;
            font-size: 12px;
        }

        .totals tr:not(:last-child) td {
            border-bottom: 1px solid var(--border);
        }

        .say {
            margin-top: 6px;
            font-size: 12px;
            font-style: italic;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 18px;
        }

        .sigbox {
            border: 1px dashed var(--border);
            border-radius: 6px;
            padding: 12px;
            height: 90px;
            font-size: 12px;
        }

        .footer {
            border-top: 1px solid var(--border);
            margin-top: 20px;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: var(--gray-600);
        }

        .page-break {
            break-before: page;
        }

        @media print {
            body {
                background: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- PAGE 1 -->
    <div class="page">
        <div>
            <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
                <tr>
                    <!-- LEFT SIDE -->
                    <td style="vertical-align:top; padding:0;">
                        <div style="font-weight:700; font-size:14px;">PT. Artisan Wahyu</div>
                        <div style="font-size:12px;">JL. Sultan Iskandar Muda No 8 Jakarta, DKI JAKARTA 60123</div>
                        <div style="font-size:12px;">Telp: (622) 129-0080 00 &nbsp;&nbsp; Fax: (622) 129-0531 91</div>
                        <div style="font-size:12px;">NPWP : 01.070.808.9-058.000</div>
                        <div style="font-size:12px;">
                            Alamat NPWP : GEDUNG GANDARIA 8 OFFICE TOWER LT.32, JL SULTAN ISKANDAR MUDA KEBAYORAN LAMA
                            UTARA,
                            KEBAYORAN LAMA, JAKARTA SELATAN, DKI JAKARTA 12240
                        </div>
                        <div style="font-size:12px; color:#374151;">

                        </div>
                    </td>

                    <!-- RIGHT SIDE -->
                    <td style="vertical-align:top; text-align:right; padding:0; width:280px;">
                        <div
                            style="display:inline-block; padding:2px 8px; border:1px solid #cbd5e1; border-radius:999px; font-size:11px; margin-bottom:6px;">
                            Order Pembelian (Purchase Order)
                        </div>
                        <div style="font-size:12px; font-weight:700; color:#111827;">PO No: 8000006117</div>
                        <div style="font-size:10px; color:#374151;">
                            (Pls always indicate this PO No. on all related invoices and/or delivery
                            receipt.)
                        </div>
                        <div style="font-size:12px; font-weight:700; color:#111827;"></div>
                        <div style="font-size:10px; color:#374151;">
                            (Harap cantumkan No. PO ini pada invoice dan atau Surat Jalan.)
                        </div>


        </div>
        </td>
        </tr>
        </table>


        <div class="row">
            <div class="section col">
                <h3>Detail PO</h3>
                <dl class="kv">
                    <dt>Tgl. PO / Date</dt>
                    <dd>23 September 2025</dd>
                    <dt>Jangka Waktu Pembayaran</dt>
                    <dd>21 DAYS</dd>
                    <dt>Jadwal Pengiriman</dt>
                    <dd>2 October 2025</dd>
                </dl>
            </div>

            <div class="section col">
                <h3>Supplier</h3>
                <dl class="kv">
                    <dt>Supplier Name</dt>
                    <dd>INDO INTI SARANA, PT</dd>
                    <dt>Telp</dt>
                    <dd>02122111298</dd>
                    <dt>HP</dt>
                    <dd>081211351770</dd>
                </dl>
            </div>
        </div>

        <div class="section">
            <h3>Yang Perlu Diperhatikan Supplier</h3>
            <div class="notes">
                <ol>
                    <li>Harap cantumkan No. PO &amp; No SPPB pada surat jalan.</li>
                    <li>Supplier wajib mencantumkan Nama, No. Rek &amp; nama bank pada setiap invoice penagihan.
                    </li>
                    <li>Invoice asli bermaterai, faktur pajak asli + copy, PO asli, STTB asli, copy NPWP, copy SKT,
                        copy SPPKP (bila ada), copy Akta Perusahaan.</li>
                    <li>STTS asli merupakan tanda terima sah untuk pengambilan giro kemudian.</li>
                    <li>Kehilangan PO/STTB/STTS merupakan tanggung jawab Supplier sepenuhnya.</li>
                    <li>Maksimal penagihan adalah 2 (dua) bulan dari tanggal STTB; melebihi itu tagihan hangus.</li>
                </ol>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:28px;">No.</th>
                    <th>Jenis Barang (Description)</th>
                    <th style="width:70px;">Satuan</th>
                    <th class="ar" style="width:80px;">Quantity</th>
                    <th class="ar" style="width:110px;">Harga Satuan</th>
                    <th class="ar" style="width:120px;">Net Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>BESI HOLLOW UK. 40 X 40 X 0.35MM 4MTR GALVANIS</td>
                    <td>PCS</td>
                    <td class="ar">30.00</td>
                    <td class="ar">65,000.00</td>
                    <td class="ar">1,950,000.00</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>KUAS CAT 3" — ETERNA</td>
                    <td>PCS</td>
                    <td class="ar">6.00</td>
                    <td class="ar">15,000.00</td>
                    <td class="ar">90,000.00</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>MATA GERINDA POLES 4" — WD</td>
                    <td>PCS</td>
                    <td class="ar">5.00</td>
                    <td class="ar">11,000.00</td>
                    <td class="ar">55,000.00</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>PAKU RIVET 4,8 MM</td>
                    <td>PCS</td>
                    <td class="ar">100.00</td>
                    <td class="ar">200.00</td>
                    <td class="ar">20,000.00</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>SEALANT STRUKTUR/DODOL 688 SILICONE DOW CORNING 600ML</td>
                    <td>PCS</td>
                    <td class="ar">7.00</td>
                    <td class="ar">116,000.00</td>
                    <td class="ar">812,000.00</td>
                </tr>
            </tbody>
        </table>

        <div class="row" style="margin-top:10px; align-items:flex-start;">
            <div class="col">
                <div class="notes">
                    <p><strong>Purpose/Project:</strong> Pengadaan Stock Material Civil Bulan Oktober 2025.</p>
                    <p>Alamat pengirim barang ditujukan ke gudang PT. Artisan Wahyu, Mall Gandaria City Lantai
                        Basement 1 (Kolom B11). Up: Bpk Arie Wibisono — Telp. 021-29052888 — HP. 081319571478, Hari
                        / Jam : Senin - Jumat / 09.00 - 17.00. <strong>HARAP DITULIS NO.PO PADA SURAT
                            JALAN.</strong></p>
                </div>
                <div class="say">Say: <em>Dua juta sembilan ratus dua puluh tujuh ribu rupiah</em></div>
            </div>
            <div class="col" style="max-width: 260px; margin-left:auto;">
                <div class="totals">
                    <table>
                        <tr>
                            <td>Sub Total</td>
                            <td class="ar">2,927,000.00</td>
                        </tr>
                        <tr>
                            <td>PPN</td>
                            <td class="ar">0.00</td>
                        </tr>
                        <tr>
                            <td><strong>Total/Netto</strong></td>
                            <td class="ar"><strong>IDR 2,927,000.00</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sigbox"><strong>Approved by</strong><br />System</div>
            <div class="sigbox"><strong>Paraf Supplier</strong></div>
        </div>
    </div>

    <div class="footer">
        <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
        <div>Page: 1 of 2</div>
    </div>
    </div>

    <!-- PAGE 2 -->
    <div class="page">
        <div>
            <h2 style="font-size:14px; margin-bottom:8px;">KONDISI DAN PERSYARATAN</h2>
            <div class="notes" style="font-size:12px; line-height:1.6;">
                <ol>
                    <li><strong>EFEKTIVITAS</strong> — Order Pembelian ini efektif setelah Supplier menandatangani PO
                        ini.</li>
                    <li><strong>PENERIMAAN DAN PEMERIKSAAN</strong> — Barang/jasa yang dikirim harus diperiksa dan
                        disetujui oleh User.</li>
                    <li><strong>JAMINAN</strong> — Supplier menjamin kesesuaian spesifikasi, keaslian, dan kewenangan
                        penjualan.</li>
                    <li><strong>GARANSI</strong> — Supplier wajib memberikan garansi manufaktur resmi atau garansi
                        perbaikan.</li>
                    <li><strong>KEPEMILIKAN</strong> — Peralihan kepemilikan diakui setelah diterima oleh User.</li>
                    <li><strong>HARGA</strong> — Tidak akan melebihi harga tercantum kecuali ada persetujuan tertulis.
                    </li>
                    <li><strong>PAJAK</strong> — Supplier menjamin keabsahan PPN dan pelaporan perpajakan.</li>
                    <li><strong>PENGIRIMAN</strong> — Keterlambatan 14 hari memberi hak User membatalkan PO.</li>
                    <li><strong>PEMBATALAN</strong> — User dapat membatalkan bila ada pelanggaran syarat atau pailit.
                    </li>
                    <li><strong>PERATURAN HUKUM</strong> — Tunduk pada hukum Indonesia.</li>
                    <li><strong>PENGADILAN</strong> — Pengadilan Negeri setempat di Indonesia.</li>
                    <li><strong>BAHASA</strong> — Bahasa Indonesia digunakan dalam PO ini.</li>
                </ol>
            </div>

            <div class="signatures" style="margin-top:30px;">
                <div class="sigbox"><strong>Disetujui oleh Supplier</strong><br />(Tanda Tangan dan Nama Jelas)</div>
                <div class="sigbox"></div>
            </div>
        </div>

        <div class="footer">
            <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
            <div>Page: 2 of 2</div>
        </div>
    </div>
</body>

</html>
