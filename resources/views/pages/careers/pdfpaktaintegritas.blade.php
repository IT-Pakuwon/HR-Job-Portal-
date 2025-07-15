<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pernyataan Sikap dan Etika</title>
  <style>
    @page {
      size: A4;
      margin: 15mm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 0;
      line-height: 1.4;
    }

    h2 {
      text-align: center;
      font-size: 14pt;
      font-weight: bold;
      /* text-decoration: underline; */
    }

    ol {
      margin-top: 5px;
      margin-bottom: 5px;
    }

    .ttd {
      text-align: right;
      margin-top: 50px;
    }

    .biodata {
      text-align: right;  /* Align biodata to the right */
      margin-top: 20px;
    }

    table {
      width: auto;
      margin-left: auto;  /* Center the table horizontally */
      margin-right: 0;
    }

    td {
      padding: 4px 8px;
    }

    .label {
      text-align: left;
      width: 100px;
    }

    .colon {
      text-align: right;
      padding-left: 5px;
    }

    .value {
      text-align: left;
    }

    p {
      margin-bottom: 5px;
    }
  </style>
</head>
<body>

  <h2>Pernyataan Sikap dan Etika Bekerja dengan Integritas</h2><br>

  <p>
    Sebagai seorang karyawan yang perusahaannya tergabung dalam Pakuwon group,
    dengan ini saya menyatakan bahwa saya telah memahami sepenuhnya Sikap dan
    Kode Etik Kerja yang berlaku di Perusahaan sebagai bagian dari Ketentuan dan
    Peraturan Perusahaan, dan dengan ini saya menyatakan bahwa saya :
  </p>

  <ol>
    <li>Tidak akan mengambil / memindahkan (barang dan/atau uang), yang dilakukan dalam lingkungan perusahaan tanpa melalui prosedur yang sah / resmi.</li>
    <li>Tidak akan memperkaya diri sendiri dengan menggunakan, mengatasnamakan serta mempergunakan semua fasilitas perusahaan untuk kepentingan pribadi.</li>
    <li>Tidak akan merusak dengan sengaja atau karena kecerobohan milik / harta benda perusahaan, termasuk di dalamnya barang-barang yang dipakai sebagai perlengkapan kerja, demikian juga barang milik anggota pimpinan perusahaan, anggota staff dan teman teman.</li>
    <li>Tidak akan dengan sengaja atau ceroboh membiarkan teman sekerja atau pimpinan perusahaan dalam keadaan bahaya ditempat kerja.</li>
    <li>Tidak akan melakukan penganiayaan ataupun perkelahian fisik, mengancam, mengintimidasi atau menghina secara kasar terhadap keluarga pengusaha, pengunjung, keluarga pengunjung, anggota staff dan teman sekerja, begitu juga terhadap orang lain yang berada dalam lingkungan perusahaan.</li>
    <li>Tidak akan memberikan keterangan palsu atau yang dipalsukan/ keterangan yang tidak benar.</li>
    <li>Tidak akan melakukan pelecehan seksual dan atau tindakan asusila ditempat kerja.</li>
    <li>Tidak akan meminum minuman keras yang memabukan dan berjudi di dalam lingkungan perusahaan.</li>
    <li>Tidak akan membongkar rahasia perusahaan atau rumah tangga anggota pimpinan perusahaan, anggota staff dan teman sekerja.</li>
    <li>Tidak akan menggunakan narkoba, terlibat dalam pengedaran dan atau penjual narkoba.</li>
    <li>Tidak akan melakukan kerjasama dengan pihak luar untuk melakukan sesuatu yang akan merugikan perusahaan.</li>
    <li>Tidak akan melanggar norma-norma hukum atau norma kesusilaan yang berlaku di Indonesia.</li>
  </ol>

  <p>
    Maka untuk itu, saya berjanji akan mentaati/ mematuhi seluruh ketentuan yang
    dijelaskan dalam Sikap dan Kode Etik Kerja tersebut secara sadar dan tanpa
    paksaan. Selanjutnya, saya menyatakan bahwa saya akan dengan suka rela
    mengundurkan diri dari Perusahaan bilamana saya gagal mentaati atau malah
    melanggar ketentuan-ketentuan dalam Sikap dan Kode Etik Kerja tersebut.
  </p><br><br><br>


  

  <div class="ttd">
    <p>Yang Menyatakan,</p>
    <br><br><br>
    <p>____________________</p>
  </div>

  <div class="biodata">
    <table>
      <tr>
        <td class="label">Nama</td>
        <td class="colon">:</td>
        <td class="value">{{ $full_name }}</td>
      </tr>
      <tr>
        <td class="label">Jabatan</td>
        <td class="colon">:</td>
        <td class="value">{{ $job_title }}</td>
      </tr>
      <tr>
        <td class="label">Department</td>
        <td class="colon">:</td>
        <td class="value">{{ $departementid }}</td>
      </tr>
      <tr>
        <td class="label">Tanggal</td>
        <td class="colon">:</td>
        <td class="value">{{ $date }}</td>
      </tr>
    </table>
  </div>


</body>
</html>
