<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order — {{ $company->cpny_name }}</title>
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

        /* DESCRIPTION */
        .description {
            font-size: 10.5px;
        }


        /* .fixed-footer {
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
        } */
    </style>
</head>

<body>
    <div class="page">
        {{-- Header --}}
        <table class="header-table">
            <tr>
                <td class="company-name" style="width:40%;">
                    <h2><strong>{{ $company->cpny_name }}</strong></h2>
                </td>
                <td class="purchase-name" style="width:20%;">
                    <h2 style="margin=0;"><strong>Order Pembelian</strong></h2>
                    <h3><strong>(Purchase Order)</strong></h3>
                </td>
                <td style=" width: 40%; vertical-align: bottom; text-align: left;font-size: 12px;">
                    <p><strong>PO No: {{ $po->ponbr }}</strong></p>
                </td>
                <hr>
            </tr>
            <tr>
                <td colspan="2">
                    <p style="font-size: 11px; padding-left:5px">{{ $company->address_line1 }}</p>
                    <p style="font-size: 11px;padding-left:5px">Telp: {{ $company->phone }} &nbsp;&nbsp; Fax:
                        {{ $company->fax }}
                        91</p>
                    <table style="font-size:11px; border-collapse:collapse; margin-left:0; padding-left:0;">
                        <tr>
                            <td style="width:90px;">NPWP</td>
                            <td style="width:8px;">:</td>
                            <td>{{ $company->tax_registration }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align:top;">Alamat NPWP</td>
                            <td>:</td>
                            <td>
                                {{ $company->tax_address_line }}
                            </td>
                        </tr>
                    </table>

                    <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                    <div style="margin-top:4px">
                        <table style="font-size:11px; border-collapse:collapse; margin:0; padding:0;">
                            <tr>
                                <td style="width:95px;">Supplier Name</td>
                                <td style="width:6px;">:</td>
                                <td>{{ $po->vendorname }}</td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td>:</td>
                                <td>{{ $po->vendoralamat }}</td>
                            </tr>
                            <tr>
                                <td>Tel/Fax</td>
                                <td>:</td>
                                <td>{{ $po->vendortelp }}</td>
                            </tr>
                        </table>

                    </div>
                </td>
                <td>

                    <h2 style="font-size:12px;"><strong>SPPB No:
                            {{ $po->sppbjktid }}</strong></h2>
                    <h2 style="font-size:12px;"><strong></strong></h2>
                    <p style="font-size: 12px;">Tgl. PO / Date :
                        {{ \Carbon\Carbon::parse($po->podate)->translatedFormat('d F Y') }}</p>

                    <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                    <div style="margin-top:4px">

                        <p style="margin:0; font-size:12px;">
                            Jangka Waktu Pembayaran (Payment Terms) :
                            <br>
                            <strong>{{ $poTerms->top_name ?? '-' }}</strong>
                        </p>

                        @if (!empty($po->vendornote))
                            <p style="margin:4px 0 0 0; font-size:12px;">
                                <strong>Note :</strong>
                                {{ $po->vendornote }}
                            </p>
                        @endif

                        <hr style="border:none; border-top:2px solid #000; margin:6px 0 0 0">

                    </div>
                    <div style="margin-top:4px">
                        <p style="vertical-align: top; text-align: left; font-size:12px;  ">
                            {{-- Jadwal Pengiriman (Delivery Schedule) : <br> <strong>{{ \Carbon\Carbon::parse($po->podeliverydate)->translatedFormat('d F Y') }}</strong> </p> --}}
                            Jadwal Pengiriman (Delivery Schedule) :
                            <br><strong>{{ optional($po->podeliverydate)->translatedFormat('d F Y') }}</strong>
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
                @php
                    $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
                    $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
                @endphp
                @foreach ($podetail as $i => $item)
                    <tr>
                        <td style="text-align:center;">{{ $i + 1 }}</td>
                        {{-- <td>{{ $item->inventory_descr }}</td> --}}
                        <td class="description">
                            <div style="font-weight:700;">
                                {{ $item->inventory_descr }}
                            </div>

                            @if (!empty($item->ponote_detail))
                                <div style="margin-top:1rem;">
                                    {{ $item->ponote_detail }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $item->uom }}</td>
                        <td style="text-align:right;">{{ $nf2($item->qty) }}</td>
                        <td style="text-align:right;">{{ $nf2($item->unitcost) }}</td>
                        <td style="text-align:right;">{{ $nf2($item->totalcost) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" rowspan="2">
                        <p class="whitespace-pre-line break-words"><strong>Purpose/Project:</strong>
                            {{ $po->keperluan }}</p>
                        <p class="whitespace-pre-line break-words" style="margin-top:10px;">
                            {{ $company->warehouse_note }}
                        </p>
                    </td>
                    <td style="text-align:right;">
                        <strong>Subtotal</strong>
                    </td>
                    <td style="text-align:right;">{{ $nf2($dpp) }}</td>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <strong>PPN</strong>
                    </td>
                    <td style="text-align:right;">{{ $nf2($ppn) }}</td>
                </tr>
                <tr>
                    <td colspan="4"> Say: <em>{{ $terbilang }}</em></td>
                    <td style="text-align:right;">
                        <strong>Total/Netto</strong>
                    </td>
                    <td style="text-align:right;">IDR {{ $nf2($grand) }}</td>
                </tr>
            </tbody>
        </table>

        <table style="width:100%; border-collapse:collapse;">
            <tbody>
                <tr>
                    <td style="height:200px; text-align:right; padding-left:40px;">
                        <strong>Approved by System</strong><br><br><br><br><br>

                        ___________________________<br>
                        Paraf by Supplier
                    </td>
                </tr>
            </tbody>
        </table>




        {{-- <div class="fixed-footer">
            <div>Created by: {{ $purchaser }}, Sent by: {{ $purchaser }}, On: {{ $now->format('d/m/Y H:i') }}</div>
            <div>Page: 1 of 2</div>
        </div> --}}
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


        <div style="text-align:right; margin-top:40px;">
            <div class="signature-box">
                <div class="title">Disetujui oleh Supplier</div>
                <div class="line">Supplier atau yang Berwenang / Supplier's Authorized Representative<br>
                    (Tanda Tangan dan Nama Jelas) / <em>Pls. Sign</em></div>
            </div>
        </div>


        {{-- <div class="fixed-footer">
            <div>Created by: Wahyudi Wahid, Sent by: Wahyudi Wahid, On: 9/23/2025 9:37:11 AM</div>
            <div>Page: 2 of 2</div>
        </div> --}}
    </div>





</body>

</html>
