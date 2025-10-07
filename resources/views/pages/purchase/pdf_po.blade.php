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
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: var(--black); }

    /* Page size for print */
    @page { size: A4; margin: 18mm 16mm; }
    .page { width: 210mm; min-height: 297mm; padding: 0; margin: 0 auto; background: #fff; position: relative; }
    .content { padding: 0; }

    .row { display: flex; gap: 12px; }
    .col { flex: 1; }
    .col-2 { flex: 0 0 50%; }

    h1,h2,h3,h4 { margin: 0; }

    .header { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: start; margin-bottom: 10px; }
    .title { font-weight: 700; font-size: 14px; letter-spacing: .2px; }
    .subtitle { font-size: 12px; color: var(--gray-700); }

    .badge { display: inline-block; padding: 2px 8px; border: 1px solid var(--border); border-radius: 999px; font-size: 11px; }

    .section { border: 1px solid var(--border); border-radius: 6px; padding: 10px; margin-bottom: 10px; }
    .section h3 { font-size: 12px; margin-bottom: 8px; }

    .kv { display: grid; grid-template-columns: 140px 1fr; gap: 2px 10px; font-size: 12px; }
    .kv dt { color: var(--gray-600); }
    .kv dd { margin: 0; font-weight: 600; }

    table { width: 100%; border-collapse: collapse; }
    thead th { font-size: 12px; text-align: left; padding: 8px; border-bottom: 1px solid var(--black); }
    tbody td { font-size: 12px; padding: 8px; border-bottom: 1px dashed var(--border); vertical-align: top; }
    .ar { text-align: right; }

    .notes { font-size: 11px; line-height: 1.45; }
    .notes ol { margin: 6px 0 0 16px; padding: 0; }

    .totals { margin-top: 8px; }
    .totals .row { justify-content: flex-end; }
    .totals table { width: 260px; border: 1px solid var(--border); }
    .totals td { padding: 6px 8px; font-size: 12px; }
    .totals tr:not(:last-child) td { border-bottom: 1px solid var(--border); }

    .say { margin-top: 6px; font-size: 12px; font-style: italic; }

    .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 18px; }
    .sigbox { border: 1px dashed var(--border); border-radius: 6px; padding: 12px; height: 90px; font-size: 12px; }

    .footer { position: absolute; bottom: 0; left: 0; right: 0; display: flex; justify-content: space-between; align-items: center; padding-top: 8px; font-size: 10.5px; color: var(--gray-600); }
    .footer .line { height: 1px; background: var(--border); margin-bottom: 6px; }

    .page-break { page-break-before: always; }

    /* Print tweaks */
    @media print {
      .no-print { display: none !important; }
      .page { box-shadow: none; }
    }
  </style>
</head>
<body>
  <!-- PAGE 1 -->
  <div class="page">
    <div class="content">
      <div class="header">
        <div>
          <div class="title">PT. Artisan Wahyu</div>
          <div class="subtitle">JL. Sultan Iskandar Muda No 8 Jakarta, DKI JAKARTA 60123</div>
          <div class="subtitle">Telp: (622) 129-0080 00 &nbsp;&nbsp; Fax: (622) 129-0531 91</div>
          <div class="subtitle">NPWP : 01.070.808.9-058.000</div>
          <div class="subtitle">Alamat NPWP : GEDUNG GANDARIA 8 OFFICE TOWER LT.32, JL SULTAN ISKANDAR MUDA KEBAYORAN LAMA UTARA, KEBAYORAN LAMA, JAKARTA SELATAN, DKI JAKARTA 12240</div>
        </div>
        <div style="text-align:right;">
          <div class="badge">Order Pembelian (Purchase Order)</div>
          <div style="margin-top:6px;font-size:12px;font-weight:700;">PO No: 8000006117</div>
        </div>
      </div>

      <div class="row" style="margin-bottom:8px; align-items: center;">
        <div class="subtitle"><strong>(Pls always indicate this PO No. on all related invoices and/or delivery receipt.)</strong></div>
        <div class="subtitle" style="margin-left:auto;">SPPB No: PB25090008</div>
      </div>
      <div class="subtitle" style="margin-top:-4px;">(Harap cantumkan No. PO ini pada invoice dan atau Surat Jalan.)</div>
      <div class="subtitle" style="margin:6px 0 10px 0;">(Ref: AW/PM/25-09/0061)</div>

      <div class="row">
        <div class="section col">
          <h3>Detail PO</h3>
          <dl class="kv">
            <dt>Tgl. PO / Date</dt><dd>23 September 2025</dd>
            <dt>Jangka Waktu Pembayaran</dt><dd>21 DAYS</dd>
            <dt>Jadwal Pengiriman</dt><dd>2 October 2025</dd>
          </dl>
        </div>
        <div class="section col">
          <h3>Supplier</h3>
          <dl class="kv">
            <dt>Supplier Name</dt><dd>INDO INTI SARANA, PT</dd>
            <dt>Telp</dt><dd>02122111298</dd>
            <dt>HP</dt><dd>081211351770</dd>
          </dl>
        </div>
      </div>

      <div class="section">
        <h3>Yang Perlu Diperhatikan Supplier</h3>
        <div class="notes">
          <ol>
            <li>Harap cantumkan No. PO &amp; No SPPB pada surat jalan.</li>
            <li>Supplier wajib mencantumkan Nama, No. Rek &amp; nama bank pada setiap invoice penagihan. Apabila tidak tertera maka invoice/kwitansi akan dikembalikan/ditolak oleh bagian finance.</li>
            <li>Invoice asli bermaterai, faktur pajak asli + copy, PO asli, STTB asli, copy NPWP, copy SKT, copy SPPKP (bila ada), copy Akta Perusahaan.</li>
            <li>STTS asli merupakan tanda terima sah untuk pengambilan giro kemudian. (Khusus Jakarta)</li>
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
          <div class="section" style="border: none; padding: 0;">
            <div class="notes" style="margin-bottom:6px;"><strong>Purpose/Project:</strong> Pengadaan Stock Material Civil Bulan Oktober 2025.</div>
            <div class="notes">
              Alamat pengirim barang ditujukan ke gudang PT. Artisan Wahyu, Mall Gandaria City Lantai Basement 1 (Kolom B11). Up: Bpk Arie Wibisono — Telp. 021-29052888 — HP. 081319571478, Hari / Jam : Senin - Jumat / 09.00 - 17.00. Lalu terima STTB (Surat Tanda Terima Barang) asli utk lampiran invoice. <strong>HARAP DITULIS NO.PO PADA SURAT JALAN.</strong>
            </div>
          </div>
          <div class="say">Say: <em>Dua juta sembilan ratus dua puluh tujuh ribu rupiah</em></div>
        </div>
        <div class="col" style="max-width: 320px; margin-left:auto;">
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
        <div class="sigbox"><strong>Approved by</strong><br/>System</div>
        <div class="sigbox"><strong>Paraf Supplier</strong></div>
      </div>

      <div class="footer">
        <div style="flex:1;">
          <div class="line"></div>
          <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
        </div>
        <div>Page: 1 of 2</div>
      </div>
    </div>
  </div>

  <!-- PAGE 2: Terms & Conditions -->
  <div class="page page-break">
    <div class="content">
      <h2 style="font-size:14px; margin-bottom:8px;">KONDISI DAN PERSYARATAN</h2>
      <div class="notes" style="font-size:12px; line-height:1.6;">
        <ol>
          <li><strong>EFEKTIVITAS</strong><br/>Order Pembelian (PO) ini efektif dan mengikat setelah Supplier menerima dan menyetujui order dengan menandatangani PO ini pada tempat yang telah tersedia. PO ini merupakan kesepakatan tertulis yang berlaku bagi Para Pihak berikut segala perubahannya (jika ada) dan oleh karenanya, Para Pihak akan tunduk dan mematuhi PO ini.</li>
          <li><strong>PENERIMAAN DAN PEMERIKSAAN</strong><br/>Barang/jasa yang dikirim harus diperiksa dan disetujui oleh User. Kami berhak menolak barang yang rusak atau tidak sesuai. Biaya akibat penolakan dibebankan kepada Supplier.</li>
          <li><strong>JAMINAN</strong><br/>Supplier menjamin kesesuaian spesifikasi, keaslian barang, dan kewenangan untuk menjual/menyediakan barang/jasa kepada User serta membebaskan User dari tuntutan pihak ketiga.</li>
          <li><strong>GARANSI</strong><br/>Jika ditemukan kerusakan/kegagalan, Supplier wajib memberikan garansi manufaktur resmi (barang) atau garansi perbaikan (jasa) sesuai kondisi lapangan dan kesepakatan.</li>
          <li><strong>KEPEMILIKAN</strong><br/>Peralihan kepemilikan diakui setelah diterima oleh User atau wakil yang berwenang, dibuktikan dengan Berita Acara Serah Terima.</li>
          <li><strong>HARGA</strong><br/>Pembayaran tidak akan melebihi harga yang tercantum dalam PO kecuali ada persetujuan tertulis pejabat berwenang.</li>
          <li><strong>PAJAK</strong><br/>
            a) Supplier menjamin keabsahan PPN atas Faktur Pajak yang diterbitkan.<br/>
            b) Seluruh PPN wajib disetorkan dan dilaporkan sesuai ketentuan perpajakan yang berlaku.<br/>
            c) Bila terjadi ketidaksesuaian, Supplier bertanggung jawab sepenuhnya dan memberikan ganti rugi atas kerugian User.
          </li>
          <li><strong>PENGIRIMAN</strong><br/>Jika sebagian/seluruh barang tidak dapat dikirim sesuai waktu, Supplier wajib segera konfirmasi tertulis. Keterlambatan 14 hari sejak konfirmasi tetap tidak terpenuhi memberi hak User membatalkan PO.</li>
          <li><strong>PEMBATALAN</strong><br/>User berhak membatalkan sebagian/seluruh PO bila terjadi pelanggaran syarat, ketidaksesuaian, atau Supplier pailit; User berhak meminta kembali pembayaran (jika ada).</li>
          <li><strong>PERATURAN HUKUM</strong><br/>PO tunduk pada hukum yang berlaku di Indonesia.</li>
          <li><strong>PENGADILAN</strong><br/>Para pihak memilih Pengadilan Negeri setempat di Indonesia.</li>
          <li><strong>BAHASA</strong><br/>Bahasa yang digunakan dan berlaku dalam PO ini adalah Bahasa Indonesia.</li>
        </ol>
      </div>

      <div class="signatures" style="margin-top:30px;">
        <div class="sigbox"><strong>Disetujui oleh Supplier</strong><br/>(Tanda Tangan dan Nama Jelas)</div>
        <div class="sigbox"></div>
      </div>

      <div class="footer">
        <div style="flex:1;">
          <div class="line"></div>
          <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
        </div>
        <div>Page: 2 of 2</div>
      </div>
    </div>
  </div>

  <!-- Optional print button for preview (hidden on print) -->
  <div class="no-print" style="position:fixed; right:16px; bottom:16px;">
    <button onclick="window.print()" style="padding:10px 14px; border:1px solid var(--border); background:#fff; border-radius:8px; cursor:pointer;">🖨️ Print</button>
  </div>
</body>
</html>
