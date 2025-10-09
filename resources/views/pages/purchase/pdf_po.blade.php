<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order — PT. Artisan Wahyu</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .page {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .header-table {
            width: 100%;
            margin-bottom: 12px;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 6px;
            vertical-align: top;
            font-size: 10px;
        }

        .company-name {
            width: 40%;
            text-align: left;
        }

        .purchase-name {
            width: 60%;
            text-align: center;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p {
            margin: 0;
            line-height: 1.4;
        }

        hr {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            vertical-align: top;
            padding: 5px;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .details-table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 6px;
            margin-top: 20px;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            width: 200px;
            height: 100px;
            margin-top: 40px;
            padding-top: 10px;
        }

        .conditions {
            font-size: 10.5px;
            line-height: 1.4;
            padding: 0 5px 180px 5px;
            /* adds bottom padding so signature fits */
            text-align: justify;
        }

        .signature-box {
            position: absolute;
            bottom: 100px;
            /* anchors above footer */
            right: 40px;
            text-align: center;
            width: 230px;
            height: auto;
            font-size: 11px;
            line-height: 1.4;
        }

        .signature-box .title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 60px;
            display: block;
        }

        .signature-box .line {
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .fixed-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #000;
            padding: 6px 20px;
            background: #fff;
        }
    </style>
</head>

<body>
    <div class="page">
        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td class="company-name" style="width:40%;">
                    <h2><strong>PT. Artisan Wahyu</strong></h2>
                </td>
                <td class="purchase-name" style="width:20%;">
                    <h2 style="margin=0;"><strong>Order Pembelian</strong></h2>
                    <h3><strong>(Purchase Order)</strong></h3>
                </td>
                <td style=" width: 40%; vertical-align: bottom; text-align: left;font-size: 12px;">
                    <p><strong>PO No: 8000006117</strong></p>
                </td>
                <hr>
            </tr>
            <tr>
                <td colspan="2">
                    <p style="font-size: 11px; padding-left:5px">JL. Sultan Iskandar Muda No 8 Jakarta, DKI JAKARTA
                        60123</p>
                    <p style="font-size: 11px;padding-left:5px">Telp: (622) 129-0080 00 &nbsp;&nbsp; Fax: (622) 129-0531
                        91</p>
                    <table style="font-size:11px; border-collapse:collapse; margin-left:0; padding-left:0;">
                        <tr>
                            <td style="width:90px;">NPWP</td>
                            <td style="width:8px;">:</td>
                            <td>01.070.808.9-058.000</td>
                        </tr>
                        <tr>
                            <td style="vertical-align:top;">Alamat NPWP</td>
                            <td>:</td>
                            <td>
                                GEDUNG GANDARIA 8 OFFICE TOWER LT.32, JL SULTAN ISKANDAR MUDA KEBAYORAN LAMA UTARA,
                                KEBAYORAN LAMA, JAKARTA SELATAN, DKI JAKARTA 12240
                            </td>
                        </tr>
                    </table>

                    <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                    <div style="margin-top:4px">
                        <table style="font-size:11px; border-collapse:collapse; margin:0; padding:0;">
                            <tr>
                                <td style="width:95px;">Supplier Name</td>
                                <td style="width:6px;">:</td>
                                <td>INDO INTI SARANA, PT</td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td>:</td>
                                <td>JL. Sultan Iskandar Muda No 8 Jakarta, DKI JAKARTA 60123</td>
                            </tr>
                            <tr>
                                <td>Tel/Fax</td>
                                <td>:</td>
                                <td>01.070.808.9-058.000</td>
                            </tr>
                        </table>

                    </div>
                </td>
                <td>

                    <h2 style="font-size:12px;"><strong>SPPB No:
                            PB25090008</strong></h2>
                    <h2 style="font-size:12px;"><strong>(Ref: AW/PM/25-09/0061)</strong></h2>
                    <p style="font-size: 12px;">Tgl. PO / Date : 23 September 2025</p>

                    <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                    <div style="margin-top:4px">
                        <p style="vertical-align: top; text-align: left; font-size:12px;  ">
                            Jangka Waktu Pembayaran (Payment Terms) : <br> <strong>21 Days</strong> </p>
                        <h2 style="font-size:12px;"><strong></strong></h2>
                        <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                    </div>
                    <div style="margin-top:4px">
                        <p style="vertical-align: top; text-align: left; font-size:12px;  ">
                            Jadwal Pengiriman (Delivery Schedule) : <br> <strong>2 October 2025</strong> </p>
                        <h2 style="font-size:12px;"><strong></strong></h2>
                        <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                    </div>

                </td>
                <hr>
            </tr>
        </table>

        <p style="margin-top:8px;"><strong>YANG PERLU DIPERHATIKAN SUPPLIER:</strong></p>

        <table style="width:100%; font-size:11px; border:1px solid #000;">
            <tr>
                <td>
                    <ol style="margin:0; padding-left:16px;">
                        <li>Harap cantumkan No. PO & No. SPPB pada surat jalan.</li>
                        <li>Supplier wajib mencantumkan Nama, No. Rek & nama bank pada setiap invoice
                            penagihan.
                            Apabila tidak tertera maka invoice/kwitansi akan dikembalikan/ditolak oleh
                            bagian finance.
                        </li>
                        <li>Invoice asli bermaterai, faktur pajak asli + copy, PO asli, STTB asli, copy
                            NPWP, copy SKT,
                            copy SPPKP (bila ada), copy Akta Perusahaan.
                        </li>
                        <li>STTS asli merupakan tanda terima sah untuk pengambilan giro kemudian.
                            (Khusus Jakarta)</li>
                        <li>Kehilangan PO/STTB/STTS merupakan tanggung jawab Supplier sepenuhnya.</li>
                        <li>Maksimal penagihan adalah 2 (dua) bulan dari tanggal STTB. Jika melebihi
                            batas waktu tersebut,
                            maka tagihan dianggap hangus.
                        </li>
                    </ol>
                </td>
            </tr>
        </table>

        {{-- Details --}}
        <table class="details-table" style="margin-top:12px;">
            <thead>
                <tr>
                    <th style="width:28px;">No.</th>
                    <th>Jenis Barang (Description)</th>
                    <th style="width:70px;">Satuan</th>
                    <th style="width:80px;">Quantity</th>
                    <th style="width:110px;">Harga Satuan</th>
                    <th style="width:120px;">Net Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:center;">1</td>
                    <td>BESI HOLLOW UK. 40 X 40 X 0.35MM 4MTR GALVANIS</td>
                    <td style="text-align:center;">PCS</td>
                    <td style="text-align:right;">30.00</td>
                    <td style="text-align:right;">65,000.00</td>
                    <td style="text-align:right;">1,950,000.00</td>
                </tr>
                <tr>
                    <td style="text-align:center;">2</td>
                    <td>KUAS CAT 3" — ETERNA</td>
                    <td style="text-align:center;">PCS</td>
                    <td style="text-align:right;">6.00</td>
                    <td style="text-align:right;">15,000.00</td>
                    <td style="text-align:right;">90,000.00</td>
                </tr>
                <tr>
                    <td style="text-align:center;">3</td>
                    <td>MATA GERINDA POLES 4" — WD</td>
                    <td style="text-align:center;">PCS</td>
                    <td style="text-align:right;">5.00</td>
                    <td style="text-align:right;">11,000.00</td>
                    <td style="text-align:right;">55,000.00</td>
                </tr>
                <tr>
                    <td style="text-align:center;">4</td>
                    <td>PAKU RIVET 4,8 MM</td>
                    <td style="text-align:center;">PCS</td>
                    <td style="text-align:right;">100.00</td>
                    <td style="text-align:right;">200.00</td>
                    <td style="text-align:right;">20,000.00</td>
                </tr>
                <tr>
                    <td style="text-align:center;">5</td>
                    <td>SEALANT STRUKTUR/DODOL 688 SILICONE DOW CORNING
                        600ML</td>
                    <td style="text-align:center;">PCS</td>
                    <td style="text-align:right;">7.00</td>
                    <td style="text-align:right;">116,000.00</td>
                    <td style="text-align:right;">812,000.00</td>
                </tr>
                <tr>
                    <td colspan="4" rowspan="2">
                        <p><strong>Purpose/Project:</strong> Pengadaan Stock Material Civil
                            Bulan
                            Oktober 2025.</p>
                        <p style="margin-top:10px;">
                            Alamat pengirim barang ditujukan ke gudang PT. Artisan Wahyu, Mall Gandaria City
                            Lantai
                            Basement 1 (Kolom B11).<br>
                            Up: Bpk Arie Wibisono — Telp. 021-29052888 — HP. 081319571478.<br>
                            Hari / Jam: Senin - Jumat / 09.00 - 17.00.<br>
                            <strong>HARAP DITULIS NO. PO PADA SURAT JALAN.</strong>
                        </p>
                    </td>
                    <td style="text-align:right;">
                        <strong>Subtotal</strong>
                    </td>
                    <td style="text-align:right;">2,927,000.00</td>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <strong>PPN</strong>
                    </td>
                    <td style="text-align:right;">0.00</td>
                </tr>
                <tr>
                    <td colspan="4"> Say: <em>Dua juta
                            sembilan ratus
                            dua puluh tujuh
                            ribu
                            rupiah</em></td>
                    <td style="text-align:right;">
                        <strong>Total/Netto</strong>
                    </td>
                    <td style="text-align:right;">IDR 2,927,000.00</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align:right; margin-top:40px;">
            <div class="signature-box">
                <div class="title">Approved by System</div>
                <div class="line">Paraf by Supplier</div>
            </div>
        </div>


        <div class="fixed-footer">
            <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
            <div>Page: 1 of 2</div>
        </div>
    </div>

    <div class="page">
        <!-- KONDISI DAN PERSYARATAN -->
        <div class="conditions">
            <h4 style="text-transform:uppercase;">KONDISI DAN PERSYARATAN</h4>
            <ol style="padding-left:14px; margin:0;">
                <li style="text-align: justify"><strong>EFEKTIVITAS</strong><br>
                    Order Pembelian (PO) ini efektif dan mengikat setelah Supplier menerima dan menyetujui order
                    dengan menandatangani (PO) ini pada tempat yang telah
                    tersedia. PO ini merupakan kesepakatan tertulis yang berlaku bagi Para Pihak berikut segala
                    perubahannya (jika ada) dan oleh karenanya, Para Pihak akan
                    tunduk dan mematuhi PO ini.</li>
                <li style="text-align: justify"><strong>PENERIMAAN DAN PEMERIKSAAN.</strong>
                    <br> Barang-barang dan jasa-jasa yang dikirim harus diperiksa dan setujui oleh User, kami berhak
                    menolak barang-barang yang rusak dan menolak penerimaan
                    barang-barang atau jasa yang tidak sesuai dengan pemesanan, ketentuan, gambar serta data yang
                    telah dikonfirmasikan oleh User kepada Supplier. Biayabiaya yang timbul akibat penolakan
                    tersebut akan dibebankan kepada Supplier sepenuhnya.
                </li>
                <li style="text-align: justify"><strong>JAMINAN</strong>
                    <br>Dengan menyetujui dan menerima PO ini, maka Supplier menyatakan dan menjamin bahwa barang-barang
                    yang dikirim adalah sesuai dengan spesifikasi,
                    desain ataupun data spesifikasi lainnya yang telah dikonfirmasikan, telah memenuhi standar yang
                    berlaku, barang asli/bukan palsu dan/atau barang bajakan,
                    dan tidak akan terdapat kerusakan yang mungkin tidak terlihat pada saat pemeriksaan dan
                    percobaan. Supplier menjamin bahwa Supplier merupakan pihak
                    yang sah dan berwenang dan bertindak sebagai Supplier untuk menjual dan menyediakan
                    barang-barang serta jasa-jasa kepada User. Supplier dengan ini
                    membebaskan dan melepaskan User dari segala tuntutan atau gugatan dari pihak ketiga terkait
                    dengan ketentuan dalam Pasal 3 ini.
                </li>
                <li style="text-align: justify"><strong>GARANSI</strong>
                    <br> Apabila barang-barang atau jasa-jasa dikirim berdasarkan PO ini terdapat kerusakan, gagal
                    produksi atau pekerjaan tidak sesuai, maka Supplier wajib
                    memberikan garansi dari manufaktur yang resmi, asli, kepada user baik pergantian baru atau
                    reparasi garansi berdasarkan kondisi di lapangan User serta
                    kesepakatan Supplier dengan User dan untuk jasa-jasa maka Supplier wajib memberikan garansi
                    perbaikan pekerjaan tanpa adanya tambahan biaya
                    lainnya.
                </li>
                <li style="text-align: justify"><strong> KEPEMILIKAN</strong>
                    <br> Peralihan kepemilikan atas barang-barang akan diakui hanya setelah diterima oleh User atau
                    wakil
                    User yang berwenang dibuktikan dengan Berita Acara
                    Serah Terima barang-barang tersebut.
                </li>
                <li style="text-align: justify"><strong> HARGA</strong>
                    <br>Kami tidak akan membayar dengan harga yang lebih tinggi dari harga yang telah tercantum dalam
                    Order Pembelian (PO) kecuali terdapat persetujuan
                    tertulis yang telah ditanda tangani oleh pejabat kami yang berwenang
                </li>
                <li style="text-align: justify"><strong>PAJAK</strong><br>
                    <ol type="a" style="margin:4px 0 0 16px; padding:0;">
                        <li>Supplier menjamin dan menyatakan sah dan benar seluruh PPN atas Faktur Pajak yang
                            diterbitkan oleh Supplier
                            atas pembelian Barang Kena Pajak (BKP) / Jasa Kena Pajak (JKP) oleh User, dan Supplier
                            bertanggung jawab atas keabsahannya.
                        </li>
                        <li>Seluruh PPN atas Faktur Pajak sebagaimana tersebut pada Pasal 6.a wajib disetorkan dan
                            dilaporkan oleh Supplier
                            sesuai dengan ketentuan perpajakan yang berlaku.
                        </li>
                        <li>Apabila di kemudian hari seluruh PPN atas Faktur Pajak yang diterbitkan oleh Supplier
                            sebagaimana tersebut pada
                            Pasal 6.a tidak sesuai dengan ketentuan perpajakan yang berlaku, maka Supplier wajib
                            bertanggung jawab sepenuhnya
                            dan memberikan ganti rugi atas seluruh kerugian yang diderita oleh User serta membebaskan
                            dan melepaskan User
                            dari sanksi pajak yang disebabkan oleh pelanggaran Supplier sebagaimana dimaksud dalam Pasal
                            6 ini.
                        </li>
                    </ol>
                </li>
                <li style="text-align: justify"><strong>PENGIRIMAN</strong>
                    <br> Apabila terdapat sebagian atau seluruh barang dalam PO ini yang tidak dapat dikirim dalam waktu
                    yang telah ditentukan, maka Supplier wajib segera
                    memberikan konfirmasi tertulis kepada User. Keterlambatan yang tidak dikonfirmasikan sebelumnya
                    atau telah dikonfirmasikan kepada User dan Supplier
                    tetap tidak dapat memenuhi pengiriman barang sebagaimana disebutkan dalam PO ini dalam jangka
                    waktu 14 (empat belas hari) sejak tanggal pertama kali
                    keterlambatan, maka User berhak membatalkan PO ini
                </li>
                <li style="text-align: justify"><strong> PEMBATALAN</strong>
                    <br>
                    User berhak untuk membatalkan sebagian atau keseluruhan dari PO ini, jika barang yang dikirim
                    oleh Supplier tidak sesuai dengan persyaratan yang telah
                    disebutkan pada Pasal 3 diatas, atau jika Supplier melanggar salah satu dari syarat dan kondisi
                    yang telah disepakati dalam PO ini. User berhak
                    membatalkan PO ini jika Supplier mengalami kebangkrutan dan/atau tidak dapat memenuhi
                    kewajibannya. Sebagai akibat dari pembatalan PO, maka User
                    berhak untuk meminta kembali Harga/Uang yang telah dibayarkan oleh User kepada Supplier (jika
                    ada), dan oleh karenanya apabila terjadi pembatalan PO
                    secara keseluruhan atau sebagian atau terdapat permintaan pengembalian Harga/Uang maka Supplier
                    membebaskan User dari segala tuntutan dan/atau
                    segala gugatan serta ganti rugi.
                </li>
                <li style="text-align: justify"><strong>PERATURAN HUKUM YANG DIPAKAI</strong>
                    <br>Dalam PO ini juga diberlakukan peraturan yang ditetapkan dan disetujui oleh hukum yang berlaku
                    di Indonesia.
                </li>
                <li style="text-align: justify"><strong>PENGADILAN YANG DIPILIH</strong>
                    <br>Apabila terjadi perselisihan, para pihak sepakat memilih Pengadilan Negeri setempat, Indonesia
                    untuk menyelesaikan perselisihan tersebut.
                </li>
                <li style="text-align: justify"><strong> BAHASA</strong>
                    <br>Bahasa yang digunakan dan berlaku dalam PO ini adalah Bahasa Indonesia
                </li>
            </ol>
        </div>

        {{-- <div style="text-align:right;">
            <div class="signature-box">
                <div style="text-align:center; font-weight:bold; text-transform:uppercase;">
                    Disetujui oleh Supplier
                </div>
                <div style="border-top:1px solid #000; margin-top:60px; padding-top:5px; text-align:center;">
                    <strong></strong><br><br><br>

                </div>
            </div>
        </div> --}}

        <div style="text-align:right; margin-top:40px;">
            <div class="signature-box">
                <div class="title">Disetujui oleh Supplier</div>
                <div class="line">Supplier atau yang Berwenang / Supplier's Authorized Representative<br>
                    (Tanda Tangan dan Nama Jelas) / <em>Pls. Sign</em></div>
            </div>
        </div>


        <div class="fixed-footer">
            <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
            <div>Page: 2 of 2</div>
        </div>
    </div>
</body>

</html>
