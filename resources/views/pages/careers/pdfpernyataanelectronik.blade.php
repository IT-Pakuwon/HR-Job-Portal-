<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Surat Pernyataan Penggunaan Fasilitas Elektronik</title>
  <style>
    /* Set A4 Page Size */
    @page {
      size: A4;
      margin: 20mm; /* Adjust margins for A4 */
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 40px;
    }
    /* .info {
      margin-top: 20px;
      margin-bottom: 20px;
    }
    .info p {
      display: flex;
      margin: 5px 0;
    }

    .label {
      width: 150px; /* Fixed width for labels to align properly */
      /* text-align: left;
    } */

    .colon {
      width: 10px; /* Space for colon */
      text-align: center;
    }

    .value {
      flex-grow: 1; /* Allows the value to take up the remaining space */
    } */
    h2 {
      text-align: center;
      font-size: 14pt;
      font-weight: bold;
    }
    h3 {
      text-align: center;
      font-size: 12pt;
      font-weight: bold;
      margin-top: 0;
    }
    ol {
      padding-left: 20px;
      margin-top: 10px;
      margin-bottom: 20px;
    }
    .signature {
      margin-top: 40px;
    }
    .signature .left {
      float: left;
      text-align: left;
    }
    .info p, .signature p {
      margin: 0;
      padding: 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      }
      td {
        vertical-align: top;
        padding: 4px 2px;
      }
      table td:first-child {
        width: 180px;
      }
      table td:nth-child(2) {
        width: 10px;
      }
    p{
      text-align: justify;
    }
    li{
      text-align: justify;
    }
  </style>
</head>
<body>

  <h2>SURAT PERNYATAAN <br>
    PENGGUNAAN FASILITAS ELEKTRONIK PERUSAHAAN</h2><br><br>

  <p>Saya yang bertanda tangan di bawah ini:</p>

  <table>
    <tr><td>Nama</td><td>:</td><td>{{ $full_name }}</td></tr>
    <tr><td>Alamat</td><td>:</td><td>{{ $id_address }}</td></tr>
    <tr><td>No. KTP</td><td>:</td><td>{{ $ktp_id }}</td></tr>
    <tr><td>Perusahan</td><td>:</td><td>{{ $cpnyid }}</td></tr>
  </table>

  <table></table>

  <div>
    
  </div>
  <p>
    Dengan ini menyatakan bersedia mematuhi segala peraturan dan ketentuan yang
    ditetapkan Perusahaan dalam penggunaan perangkat, komputer, jaringan internet
    atau sistem elektronik milik Perusahaan (selanjutnya disebut <strong>“Fasilitas Perusahaan”</strong>)
    sebagai pendukung pekerjaan saya, yaitu :
  </p>

  <ol>
    <li>
      Tidak melakukan instalasi <i>software</i> apapun pada Fasilitas Perusahaan tanpa
      sepengetahuan dan persetujuan Management Perusahaan, apabila ditemukan
      adanya penggunaan <i>software</i> tersebut, baik <i>driver</i> maupun <i>software</i> milik pihak ketiga,
      maka instalasi <i>software</i> tersebut merupakan tanggung jawab saya pribadi dan saya
      bersedia dituntut secara hukum sebagai suatu bentuk pelanggaran hak cipta.
    </li>
    <li>
      Tidak menggunakan Fasilitas Perusahaan untuk kegiatan-kegiatan yang
      melanggar ketentuan-ketentuan dan Peraturan Perundang-undangan yang
      berlaku di Negara Republik Indonesia.
    </li>
    <li>
      Apabila saya melanggar pernyataan sebagaimana tersebut dalam Surat
      Pernyataan ini, saya bersedia dikenakan sanksi sesuai dengan peraturan dan
      ketentuan yang berlaku dan dengan ini saya melepaskan/membebaskan
      Perusahaan dari segala bentuk risiko, tanggung jawab, tuntutan dan/atau
      gugatan baik perdata maupun pidana dari pihak manapun.
    </li>
  </ol>

  <p>
    Demikian Surat Pernyataan ini dibuat dengan penuh kesadaran tanpa adanya
    paksaan dari pihak manapun.
  </p>

  <div class="signature">
    <div class="left">
      <p>Jakarta, {{ $date }}</p>
      <p>PEMBERI PERNYATAAN</p>
      <br><br><br>
      <p>________________________</p>
    </div>
  </div>

</body>
</html>
