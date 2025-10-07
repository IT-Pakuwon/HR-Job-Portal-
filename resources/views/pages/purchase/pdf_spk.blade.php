<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Surat Perintah Kerja (SPK) — PT. Artisan Wahyu</title>
  <style>
    :root{
      --ink:#111827; --muted:#4b5563; --line:#cbd5e1; --brand:#4f46e5;
    }
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;color:var(--ink);font:12px/1.4 Arial,Helvetica,sans-serif;background:#fff}

    /* Print layout */
    @page{size:A4; margin:18mm 16mm}
    .page{width:210mm; min-height:297mm; margin:0 auto; position:relative}
    .content{padding:0}

    h1,h2,h3,h4{margin:0}
    .header{display:grid; grid-template-columns:1fr auto; gap:8px; align-items:start; margin-bottom:12px}
    .company{font-weight:700; font-size:14px}
    .sub{color:var(--muted)}
    .badge{display:inline-block; padding:2px 8px; border:1px solid var(--line); border-radius:999px; font-size:11px}

    .row{display:flex; gap:12px}
    .col{flex:1}
    .section{border:1px solid var(--line); border-radius:6px; padding:10px; margin-bottom:10px}
    .section h3{font-size:12px; margin-bottom:6px}

    .kv{display:grid; grid-template-columns:160px 1fr; gap:2px 10px}
    .kv dt{color:var(--muted)}
    .kv dd{margin:0; font-weight:600}

    table{width:100%; border-collapse:collapse}
    thead th{font-size:12px; text-align:left; padding:8px; border-bottom:1px solid #000}
    tbody td{padding:8px; border-bottom:1px dashed var(--line); vertical-align:top}
    .ar{text-align:right}

    .notes{font-size:11px}
    .say{margin-top:6px; font-style:italic}

    .totals{margin-top:8px}
    .totals table{width:260px; border:1px solid var(--line)}
    .totals td{padding:6px 8px}
    .totals tr:not(:last-child) td{border-bottom:1px solid var(--line)}

    .signatures{display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:18px}
    .sigbox{border:1px dashed var(--line); border-radius:6px; padding:12px; height:90px}

    .footer{position:absolute; left:0; right:0; bottom:0; font-size:10.5px; color:var(--muted)}
    .footer .line{height:1px; background:var(--line); margin-bottom:6px}
    .footer .row{justify-content:space-between; align-items:center}

    .page-break{page-break-before:always}

    @media print{ .no-print{display:none!important} .page{box-shadow:none} }
  </style>
</head>
<body>
  <!-- PAGE 1: Ringkasan SPK -->
  <div class="page">
    <div class="content">
      <div class="header">
        <div>
          <div class="company">PT. Artisan Wahyu</div>
          <div class="sub">JL. Sultan Iskandar Muda No 8, Jakarta, DKI JAKARTA</div>
          <div class="sub">Telp: (622) 129-0080 00 &nbsp; Fax: (622) 129-0531 91</div>
          <div class="sub">NPWP : 01.070.808.9-058.000</div>
          <div class="sub">Alamat NPWP : GEDUNG GANDARIA 8 OFFICE TOWER LT.32, JL SULTAN ISKANDAR MUDA KEBAYORAN LAMA UTARA, KEBAYORAN LAMA, JAKARTA SELATAN, DKI JAKARTA 12240</div>
        </div>
        <div style="text-align:right">
          <div class="badge">SURAT PERINTAH KERJA (SPK)</div>
          <div style="margin-top:6px; font-weight:700">No. SPK: 8000006114</div>
          <div class="sub">(Ref: AW/PS/25-09/0011)</div>
        </div>
      </div>

      <div class="section">
        <h3>Para Pihak</h3>
        <div class="row">
          <div class="col">
            <dl class="kv">
              <dt>PIHAK I</dt><dd>PT. Artisan Wahyu</dd>
              <dt>Alamat</dt><dd>JL. Sultan Iskandar Muda No 8, Jakarta, DKI JAKARTA</dd>
            </dl>
          </div>
          <div class="col">
            <dl class="kv">
              <dt>PIHAK II</dt><dd>RODA NURMALA, PT</dd>
              <dt>Alamat</dt><dd>Komplek Perkantoran Royal Sunter, Jl. Danau Sunter Selatan Blok F No. 16-17, Kel. Sunter Jaya, Kec. Tanjung Priok</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="section col">
          <h3>Detail Pekerjaan</h3>
          <dl class="kv">
            <dt>Tanggal Pelaksanaan</dt><dd>23 September 2025 s/d 02 Desember 2025</dd>
            <dt>Jenis Pekerjaan</dt><dd>Pekerjaan general check up diesel fire pump di ruang pompa Lt. B2</dd>
            <dt>Total Biaya</dt><dd>13,875,000.00</dd>
            <dt>Terbilang</dt><dd>Tiga belas juta delapan ratus tujuh puluh lima ribu rupiah</dd>
            <dt>Menyelesaikan Dalam</dt><dd>28 Hari Kerja (Tidak Termasuk Sabtu/Minggu/Hari Libur Nasional)</dd>
            <dt>Waktu Pelaksanaan</dt><dd>Senin s/d Jumat, 09.00 – 18.00 WIB</dd>
            <dt>Total Man Power</dt><dd>5 Orang</dd>
            <dt>PIC</dt><dd>Bapak Jhon — HP 0813-12000866</dd>
            <dt>Cara Pembayaran</dt><dd>Transfer</dd>
          </dl>
        </div>
        <div class="section col">
          <h3>Lingkup Pekerjaan</h3>
          <table>
            <thead>
              <tr>
                <th style="width:28px">No.</th>
                <th>Deskripsi</th>
                <th class="ar" style="width:120px">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>JASA PEKERJAAN GENERAL CHECK UP FIRE PUMP (ELECTRIC, DIESEL, JOCKEY PUMP)</td>
                <td class="ar">12,500,000.00</td>
              </tr>
            </tbody>
          </table>
          <div class="row totals" style="justify-content:flex-end; margin-top:8px">
            <table style="width:260px">
              <tr><td>PPN</td><td class="ar">1,375,000.00</td></tr>
              <tr><td><strong>TOTAL</strong></td><td class="ar"><strong>13,875,000.00</strong></td></tr>
            </table>
          </div>
          <div class="notes" style="margin-top:6px">
            <strong>TOP</strong>: 14 DAYS — Pembayaran 14 hari setelah BAST dan Invoice (termasuk PPN &amp; PPh)
          </div>
        </div>
      </div>

      <div class="signatures">
        <div class="sigbox"><strong>Paraf PIHAK KEDUA</strong></div>
        <div class="sigbox"><strong>Disetujui PIHAK I</strong><br/>PT. Artisan Wahyu</div>
      </div>

      <div class="footer">
        <div class="line"></div>
        <div class="row">
          <div>Created: Wahyu Dwi Harnowo &nbsp; | &nbsp; Sent by: Wahyu Dwi Harnowo &nbsp; | &nbsp; On: 23/09/2025 09:36:55</div>
          <div>Page 1 of 3</div>
        </div>
      </div>
    </div>
  </div>

  <!-- PAGE 2: Tugas & Kewajiban, Garansi, Pembayaran -->
  <div class="page page-break">
    <div class="content">
      <h2 style="font-size:14px; margin-bottom:8px">TUGAS DAN KEWAJIBAN PIHAK KEDUA</h2>
      <div class="notes">
        <ol>
          <li><strong>Tugas Pekerjaan</strong>
            <ol>
              <li>PIHAK PERTAMA memberikan Pekerjaan sebagaimana lingkup/metode/schedule yang disepakati dan menjadi satu kesatuan dengan SPK ini. PIHAK KEDUA wajib menyelesaikan sesuai jangka waktu.</li>
              <li>PIHAK KEDUA wajib memenuhi peraturan/perizinan yang berlaku dan memberikan salinan perizinan saat diminta.</li>
              <li>Pelanggaran/ketidakpatuhan perizinan menjadi tanggung jawab PIHAK KEDUA sepenuhnya.</li>
              <li>Seluruh material wajib mendapatkan persetujuan PIHAK PERTAMA.</li>
              <li>Form Checklist &amp; BAST dicetak/dikirim User (PIC) untuk ditandatangani PIHAK KEDUA. BAST asli 2 rangkap dikembalikan maksimal 2 hari kerja.</li>
            </ol>
          </li>
          <li><strong>Jangka Waktu Pelaksanaan</strong>
            <ol>
              <li>PIHAK KEDUA wajib menyelesaikan sesuai jadwal dalam SPK.</li>
              <li>Jika terjadi hal tidak terduga dan perlu penundaan, PIHAK KEDUA wajib melapor tertulis untuk persetujuan PIHAK PERTAMA; penundaan tidak wajar dapat ditolak.</li>
              <li>Revisi jadwal/metode dilakukan berkoordinasi dan disetujui PARA PIHAK, dituangkan dalam berita acara.</li>
              <li>PIHAK KEDUA wajib mengikuti jadwal/metode terakhir yang disepakati.</li>
            </ol>
          </li>
          <li><strong>Syarat-syarat Pelaksanaan Garansi</strong>
            <ol>
              <li>Garansi pekerjaan: 3 bulan sejak BAST ditandatangani, meliputi penggantian/perbaikan tanpa biaya.</li>
              <li>Klaim dari PIHAK PERTAMA wajib ditindaklanjuti paling lambat 2×24 jam atau sesuai jadwal yang disepakati.</li>
              <li>Garansi tidak berlaku pada kejadian force majeure; wajib pemberitahuan tertulis dalam 7 hari.</li>
            </ol>
          </li>
          <li><strong>Cara Pembayaran &amp; Pajak</strong>
            <ol>
              <li>Pembayaran dilakukan setelah dokumen lengkap: Invoice bermaterai, E-Faktur, SPK, BAST + checklist &amp; foto sebelum/sesudah yang ditandatangani PIC.</li>
              <li>Dokumen tidak lengkap akan dikembalikan untuk dilengkapi.</li>
              <li>PPN atas Faktur Pajak harus sah &amp; disetor sesuai ketentuan; ketidaksesuaian menjadi tanggung jawab PIHAK KEDUA.</li>
              <li>Pembayaran via transfer; cantumkan nama, nomor rekening, dan nama bank pada invoice.</li>
            </ol>
          </li>
        </ol>
      </div>

      <div class="footer">
        <div class="line"></div>
        <div class="row">
          <div>Created: Wahyu Dwi Harnowo &nbsp; | &nbsp; Sent by: Wahyu Dwi Harnowo &nbsp; | &nbsp; On: 23/09/2025 09:36:55</div>
          <div>Page 2 of 3</div>
        </div>
      </div>
    </div>
  </div>

  <!-- PAGE 3: Penalty, Pengakhiran, Lain-lain -->
  <div class="page page-break">
    <div class="content">
      <h2 style="font-size:14px; margin-bottom:8px">KETENTUAN LAIN</h2>
      <div class="notes">
        <ol start="5">
          <li><strong>Penalty</strong>
            <ol>
              <li>Penalty atas keterlambatan:
                <ol type="1">
                  <li>Rp100.000/hari (Nilai SPK ≤ Rp50.000.000)</li>
                  <li>Rp250.000/hari (Rp50.000.001 – Rp149.999.999)</li>
                  <li>Rp500.000/hari (Nilai SPK ≥ Rp150.000.000)</li>
                </ol>
                Tanpa persetujuan PIHAK PERTAMA, penundaan tetap dikenakan penalty.
              </li>
              <li>Jika penalty mencapai 20% DPP dan Pekerjaan belum selesai, PIHAK PERTAMA dapat menunjuk pihak lain; biaya dibebankan ke PIHAK KEDUA.</li>
              <li>Pembayaran hanya untuk pekerjaan yang benar-benar terselesaikan dengan berita acara.</li>
            </ol>
          </li>
          <li><strong>Pengakhiran SPK</strong>
            <ol>
              <li>Jika PIHAK KEDUA tidak memenuhi persyaratan, setelah 3 kali teguran, PIHAK PERTAMA dapat mengakhiri SPK tanpa kompensasi.</li>
              <li>Pembayaran hanya untuk pekerjaan yang telah terpasang/terselesaikan dengan baik sesuai berita acara.</li>
            </ol>
          </li>
          <li><strong>Lain-lain</strong>
            <ol>
              <li>PIHAK KEDUA wajib mematuhi peraturan &amp; SOP lokasi kerja; risiko/kerugian karena kelalaian PIHAK KEDUA menjadi tanggung jawabnya.</li>
              <li>Sengketa diselesaikan musyawarah; jika tidak, domisili pada Pengadilan Negeri setempat.</li>
              <li>Ketentuan-ketentuan dan Syarat-syarat ini berlaku dengan ditandatanganinya SPK.</li>
              <li>Kecelakaan kerja/kematian pekerja PIHAK KEDUA di lokasi menjadi tanggung jawab PIHAK KEDUA; PIHAK PERTAMA dibebaskan dari tuntutan.</li>
            </ol>
          </li>
        </ol>
      </div>

      <div class="section" style="margin-top:16px">
        <div class="row">
          <div class="col">
            <div class="kv">
              <dt>Mengetahui</dt><dd>Jakarta, 23 September 2025</dd>
              <dt>Untuk</dt><dd>PJ25090002</dd>
            </div>
          </div>
        </div>
        <div class="signatures" style="margin-top:12px">
          <div class="sigbox"><strong>Tanda Tangan &amp; Stempel</strong><br/>PIHAK II — RODA NURMALA, PT</div>
          <div class="sigbox"><strong>Paraf PIHAK KEDUA</strong></div>
        </div>
      </div>

      <div class="footer">
        <div class="line"></div>
        <div class="row">
          <div>Created: Wahyu Dwi Harnowo &nbsp; | &nbsp; Sent by: Wahyu Dwi Harnowo &nbsp; | &nbsp; On: 23/09/2025 09:36:55</div>
          <div>Page 3 of 3</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Optional print button for preview (hidden when printing) -->
  <div class="no-print" style="position:fixed; right:16px; bottom:16px">
    <button onclick="window.print()" style="padding:10px 14px; border:1px solid var(--line); background:#fff; border-radius:8px; cursor:pointer">🖨️ Print</button>
  </div>
</body>
</html>
