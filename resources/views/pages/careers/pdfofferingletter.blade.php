<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Surat Penawaran Kerja</title>
  <style>

    @page {
      size: A4;
      margin: 12mm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 0;
      line-height: 1.4;
    }

    h2 {
      font-size: 14px; /* was 16px */
      margin-bottom: 8px;
    }

    table td {
      padding: 4px; /* was 5px */
    }
    .signature-section {
      margin-top: 5px;
      font-size: 11px; /* reduced */
    }

    .signature-table td {
      padding-top: 30px; /* was 40px */
    }


    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }


    table td:first-child {
      width: 30%;
    }

    .signature-table {
      width: 100%;
      table-layout: fixed;
    }


    .signature-table td div {
      text-align: center;
    }

    .footer-note {
      font-style: italic;
      font-size: 10px;
      text-align: center;
      margin-top: 30px;
    }

  </style>
</head>
<body>
  <div style="text-align: center; margin-bottom: 10px;">
    @if($logo =='AW')
      <img src="{{ public_path('images/gc.png') }}" alt="Company Logo" style="height: 70px;">
    @elseif ($logo =='EP')
      <img src="{{ public_path('images/kk.png') }}" alt="Company Logo" style="height: 70px;">
    @elseif ($logo =='PSA')
      <img src="{{ public_path('images/bm.png') }}" alt="Company Logo" style="height: 70px;">
    @elseif ($logo =='GPS')
      <img src="{{ public_path('images/pmb.jpeg') }}" alt="Company Logo" style="height: 70px;">
    @endif
  </div>

  <p>Jakarta, {{ $date }}</p>

  <p>Kepada Yth.<br>{{ $full_name }}<br>Di Tempat.</p>

  <p><strong>Perihal: SURAT PENAWARAN KERJA</strong></p>

  <p>Dengan ini diberitahukan bahwa Saudara diterima bekerja di <strong>{{ $cpnyid }}</strong> dengan syarat-syarat dan kondisi sebagai berikut:</p>

  <table>
    <tr>
      <td colspan="2">
        <strong>1. Jabatan</strong><br>
        Saudara ditempatkan untuk posisi {{ $job_level }}.<br>
        Bertanggung jawab langsung kepada Manager atau pejabat lain yang ditunjuk kemudian. 
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>2. Gaji</strong><br>
        Rp. {{ $net_salary }} ({{ $salary_words }})
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>3. Kepesertaan BPJS</strong><br>
        Anda akan didaftarkan pada kepesertaan BPJS Ketenagakerjaan yang meliputi program: Jaminan Hari Tua (JHT), Jaminan Kematian (JKM), Jaminan Kecelakaan Kerja (JKK), dan Jaminan Pensiun (JP).<br>
        Anda wajib berkontribusi sebesar 2% dari gaji Anda setiap bulannya untuk program JHT dan kontribusi program Jaminan Pensiun sebesar 1% dari Rp. 10.547.400,- (batas maksimal berdasarkan surat dari BPJS Ketenagakerjaan nomor: B/91/022025 tanggal 27 Februari 2025).
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>4. Status Kepegawaian</strong><br>
        Perjanjian Kerja Waktu Tertentu (PKWT).
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>5. Periode Kontrak</strong><br>
        Saudara mulai bekerja pada tanggal yang telah disepakati, dengan masa periode kontrak 3 Bulan. Terhitung dari ________________
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>Tunjangan</strong><br>
        <ul>
          <li>Tunjangan Pengobatan 1 (satu) bulan gaji bagi yang masa kerjanya telah mencapai 12 (dua belas) bulan berturut-turut dan proporsional bagi yang telah melewati masa kerja 3 bulan namun belum mencapai 12 (dua belas) bulan masa kerja, dan dikurangi kontribusi aktif BPJS Kesehatan 5% di tiap bulannya.</li>
          <li>Tunjangan Hari Raya 1 (satu) bulan gaji bagi yang masa kerjanya telah mencapai 12 (dua belas) bulan berturut-turut dan proporsional bagi yang telah melewati masa kerja 1 bulan namun belum mencapai 12 (dua belas) bulan masa kerja.</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>6. Hak Cuti</strong><br>
        Mendapatkan hak cuti selama 12 (dua belas) hari dalam 1 (satu) tahun masa kerja. Hak cuti dapat diambil setelah melampaui 1 (satu) tahun bekerja.
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <strong>7. Lain-lain</strong><br>
        Hal-hal lain yang belum diatur di dalam Surat Penawaran Kerja ini akan diatur kemudian sesuai dengan Peraturan Perusahaan.
      </td>
    </tr>       
  </table>

  <p>Apabila Saudara menerima penawaran ini, harap menandatangani turunan surat ini sebagai bukti persetujuan Saudara dan mengembalikannya kepada Perusahaan.</p>

  <div class="signature-section">
    <table class="signature-table">
      <tr>
        <td>
          <strong>Hormat Kami,</strong><br><br><br><br>
          <strong>_______________</strong><br>
          HC Dev Ops Senior Manager<br>
          Tgl: ........................
        </td>
        <td>
          <strong>Mengetahui,</strong><br><br><br><br>
          <strong>_______________</strong><br>
          Karyawan<br>
          Tgl: ........................
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
