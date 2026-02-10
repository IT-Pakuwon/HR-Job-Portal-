<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perintah Kerja — {{ $company->cpny_name }}</title>

    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        /* body {

} */


        .page {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
            padding-bottom: 10px;
            box-sizing: border-box;

            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            vertical-align: top;
            padding: 4px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            text-transform: uppercase;
            text-decoration: underline;
        }

        .signature {
            margin-top: 40px;
            text-align: right;
        }

        .signature .box {
            display: inline-block;
            text-align: center;
            width: 220px;
            height: 100px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 11px;
            line-height: 1.3;
        }

        /* Footer (Company + Paraf PIHAK KEDUA) */
        .footer {
            position: absolute;
            bottom: 55px;
            /* keep it above the fixed footer */
            left: 0;
            right: 0;
            font-size: 10px;
            line-height: 1.4;
            background: #fff;
            padding: 4px 25px;
            box-sizing: border-box;
        }

        .footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer td {
            vertical-align: top;
            padding: 0;
        }

        .footer td:first-child {
            text-align: left;
        }

        .footer td:last-child {
            text-align: right;
        }

        .footer .sign {
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 3px;
            margin-top: 2px;
            font-weight: bold;
        }

        /* Fixed footer (Created by + Page) */
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
            box-sizing: border-box;
        } */

        ol {
            counter-reset: item;
            padding-left: 18px;
            margin: 0;
        }

        ol>li {
            display: block;
            position: relative;
        }

        ol>li:before {
            content: counters(item, ".") ". ";
            counter-increment: item;
            position: absolute;
            left: -2em;
            width: 2em;
            text-align: right;
        }
    </style>
</head>

<body>
    {{-- PAGE 1 --}}
    <div class="page">
        <div class="header">
            <h2><strong>SURAT PERINTAH KERJA</strong></h2>
            <p>{{ $po->ponbr }}</p>
            <p></p>
        </div>

        <p><strong>Yang bertanda tangan di bawah ini:</strong></p>
        <p><strong>Perusahaan :</strong> {{ $company->cpny_name }}</p>
        <p><strong>Alamat :</strong> {{ $company->address_line1 }}</p>
        <p><strong>NPWP :</strong> {{ $company->tax_registration }}</p>
        <p><strong>Alamat NPWP :</strong> {{ $company->tax_address_line }}</p>
        <p>Untuk selanjutnya disebut "<strong>PIHAK I</strong>".</p>

        <br>

        <p><strong>Nama :</strong>{{ $po->vendorname }}</p>
        <p><strong>Jabatan :</strong></p>
        <p><strong>Perusahaan :</strong>{{ $po->vendorname }}</p>
        <p><strong>Alamat :</strong> {{ $po->vendoralamat }}</p>
        <p>Untuk selanjutnya disebut "<strong>PIHAK II</strong>".</p>
        <br>
        <p>Dengan ini PIHAK I memberikan tugas kepada PIHAK II untuk</p>

        <p><strong>Tanggal :</strong> {{ \Carbon\Carbon::parse($po->spkstartworkingdate)->translatedFormat('d F Y') }}
            s/d {{ \Carbon\Carbon::parse($po->spkendworkingdate)->translatedFormat('d F Y') }} (Pelaksanaan Pekerjaan).
        </p>
        <p><strong>Jenis Pekerjaan :</strong> Pekerjaan general check up diesel fire pump di ruang pompa Lt. B2</p>
        <br>
        @php
            $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
            $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
        @endphp
        <p><strong>Total Biaya :</strong> {{ $nf2($grand) }}</p>
        <p><strong>Terbilang :</strong> {{ $terbilang }}</p>
        <br>
        <p><strong>Menyelesaikan Pekerjaan Dalam Waktu :</strong> {{ $po->spktotalday }} Hari Kerja (Tidak Termasuk
            Hari Sabtu / Minggu /
            Hari Libur Nasional).</p>
        <p><strong>Waktu Pelaksanaan Pekerjaan :</strong> {{ $po->spkworkschedule }}.</p>
        <p><strong>Total Man Power :</strong> {{ $po->spkmanpower }} Orang.</p>
        <p><strong>PIC / Person In Charge :</strong> {{ $po->spkpic }}.</p>
        {{-- <p><strong>Cara Pembayaran : </strong> {{ $po->spkcarabayar }}.</p> --}}
        <p><strong>Cara Pembayaran : </strong> Transfer</p>


        <br>

        <table border="1">
            <thead style="background-color:#f2f2f2;">
                <tr>
                    <th>Lingkup Pekerjaan</th>
                    <th style="width:120px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($podetail as $i => $item)
                    <tr>
                        <td class="#">
                            <div style="font-weight:700;">
                                {{ $item->inventory_descr }}
                            </div>

                            @if (!empty($item->ponote_detail))
                                <div style="margin-top:1rem;">
                                    {{ $item->ponote_detail }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align:right;">{{ $nf2($item->totalcost) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td style="text-align:right;"><strong>PPN :</strong></td>
                    <td style="text-align:right;">{{ $nf2($ppn) }}</td>
                </tr>
                <tr>
                    <td style="text-align:right;"><strong>TOTAL :</strong></td>
                    <td style="text-align:right;">{{ $nf2($grand) }}</td>
                </tr>
            </tbody>
        </table>

        <p><strong>TOP :</strong> {{ $po->vendortop }}</p>
        <p>Pembayaran 14 Hari setelah Bast dan Invoice, include PPN dan PPH</p>

        {{-- Footer (company + paraf) --}}
        <div class="footer">
            <table>
                <tr>
                    <td>
                        <p>
                            <strong>{{ $po->cpny_id }} - {{ $company->cpny_name }}</strong><br>
                            {{ $company->address_line1 }}
                            Telp: {{ $company->phone }} Fax: {{ $company->fax }}
                        </p>
                    </td>
                    <td>
                        <div class="sign">Paraf PIHAK KEDUA</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Fixed bottom line --}}
        {{-- <div class="fixed-footer">
            <div>Created: Wahyu Dwi Harnowo, Sent by: Wahyu Dwi Harnowo, On: 9/23/2025 9:36:55 AM</div>
            <div>Page 1 of 3</div>
        </div> --}}
    </div>

    {{-- PAGE 2 --}}
    <div class="page">
        <style>
            /* Hierarchical numbering (1.1, 1.2, etc.) */
            ol {
                counter-reset: item;
                padding-left: 18px;
                margin: 0;
            }

            ol>li {
                display: block;
                position: relative;
                text-align: justify;
                margin-bottom: 3px;
            }

            ol>li:before {
                content: counters(item, ".") ". ";
                counter-increment: item;
                position: absolute;
                left: -2em;
                width: 2em;
                text-align: right;
            }
        </style>

        <h4 style="text-transform:uppercase;">TUGAS DAN KEWAJIBAN PIHAK KEDUA</h4>
        <ol>
            <li><strong>Tugas Pekerjaan</strong>
                <ol>
                    <li>PIHAK PERTAMA memberikan pekerjaan kepada PIHAK KEDUA sebagaimana Lingkup Pekerjaan, metode
                        kerja dan Schedule kerja yang telah disepakati
                        PARA PIHAK (PIHAK PERTAMA dan PIHAK KEDUA) tersebut yang merupakan satu kesatuan tidak
                        terpisahkan dengan Surat Perintah Kerja (SPK) dan
                        PIHAK KEDUA menerima untuk melakukan Pekerjaan sebagaimana yang telah diatur dalam SPK beserta
                        PIHAK KEDUA wajib mentaati dan
                        menyelesaikan Pekerjaan berdasarkan kesepakatan jangka waktu pelaksanaan pekerjaan sebagaimana
                        tersebut dalam SPK ini.</li>
                    <li>PIHAK KEDUA memiliki kemampuan dalam melaksanakan Pekerjaan sesuai dengan peraturan dan
                        perundang -undangan yang berlaku termasuk tidak
                        terbatas perijinan ketenaga kerjaan dan perijinan -perijinan terkait lainnya. PIHAK KEDUA
                        berkewajiban memberikan salinan perijinan yang berlaku jika
                        diminta oleh PIHAK PERTAMA.</li>
                    <li>Jika PIHAK KEDUA melanggar ketentuan dan/atau tidak memenuhi perijinan-perijinan yang berlaku
                        terkait dengan pelaksanaan Pekerjaan dan kegiatan
                        usahanya maka PIHAK PERTAMA dibebaskan dari segala tuntutan dan ganti rugi dari pihak lainnya
                        termasuk pemerintah akibat PIHAK KEDUA melakukan
                        kelalaian dalam memenuhi perijinan-perijinan yang berlaku.</li>
                    <li>Seluruh Material Wajib Mendapatkan Persetujuan PIHAK PERTAMA.</li>
                    <li>Form Checklist &amp; BAST (Berita Acara Serah Terima) Akan di print / email oleh PIHAK PERTAMA
                        (PIC) untuk ditanda tangani oleh PIHAK KEDUA (Supplier).
                        Setelah ditanda tanganinya BAST tersebut maka pekerjaan PIHAK KEDUA telah diserah terimakan
                        dengan baik &amp; sudah disetujui oleh PIHAK PERTAMA.</li>
                    <li>BAST yang telah di PRINT/EMAIL oleh User, wajib ditandatangani &amp; stempel perusahaan vendor
                        (dibuat 2 rangkap, ASLI) , dan dikembalikan dalam
                        waktu 2 (dua) hari kerja maksimal.</li>
                </ol>
            </li>

            <li><strong>Jangka Waktu Pelaksanaan</strong>
                <ol>
                    <li>PIHAK KEDUA wajib mentaati dan menyelesaikan Pekerjaan berdasarkan kesepakatan jangka waktu
                        pelaksanaan pekerjaan sebagaimana tersebut dalam
                        SPK ini.</li>
                    <li>Jika dalam pelaksanaan pekerjaan terjadi sesuatu hal tidak terduga ditempat /lokasi pekerjaan
                        sehingga PIHAK KEDUA terpaksa melakukan penundaan
                        atas pekerjaan tersebut, maka PIHAK KEDUA berkewajiban memberikan laporan tertulis disertai
                        dengan berita acara penundaan kepada PIHAK PERTAMA
                        untuk mendapatkan persetujuan dari PIHAK PERTAMA. Jika dalam pemeriksaan PIHAK KEDUA ternyata
                        penundaan yang dilakukan PIHAK KEDUA adalah
                        tidak wajar maka untuk penundaan Pekerjaan oleh PIHAK KEDUA tidak dapat dikabulkan.</li>
                    <li>PIHAK KEDUA berkewajiban berkoordinasi dengan PIHAK PERTAMA untuk dilakukan revisi/perubahan
                        jadwal pekerjaan dan atau revisi/perubahan metode
                        kerja apabila terjadi kendala pada saat pelaksanaan pekerjaan atas metode kerja sebelumnya,
                        revisi /penundaan tersebut disetujui oleh PARA PIHAK.
                        Revisi/perubahan jadwal pekerjaan dan revisi/penundaan tersebut dituangkan dalam Berita Acara
                        Keterlambatan.</li>
                    <li>PIHAK KEDUA wajib menyelesaikan pekerjaan yang dimaksud sesuai dengan jadwal perubahan dan atau
                        metode kerja terakhir yang telah disepakati PARA
                        PIHAK.</li>
                </ol>
            </li>

            <li><strong>Syarat-syarat Pelaksanaan Garansi</strong>
                <ol>
                    <li>Garansi Pekerjaan : 3 Bulan.</li>
                    <li>Garansi Pekerjaan yang dimaksud dalam SPK ini adalah termasuk tapi tidak terbatas penggantian
                        material yang rusak dengan material baru (termasuk tapi
                        tidak terabatas pada supply dan install atau untuk maintenance) untuk perbaikan pekerjaan yang
                        dianggap tidak sempurna oleh.</li>
                    <li>Garansi dari PIHAK KEDUA kepada PIHAK PERTAMA berlaku setelah ditanda tangani Berita Acara Serah
                        Terima (BAST) dimana Pekerjaan telah diterima
                        dengan baik, tepat dan benar sesuai permintaan dari PIHAK PERTAMA. Masa berlaku Garansi tertulis
                        dalam SPK ini.</li>
                    <li>Pelaksanaan Garansi tidak dipungut biaya apapun dari PIHAK KEDUA kepada PIHAK PERTAMA. PIHAK
                        KEDUA akan menyerahkan surat /kartu garansi
                        kepada PIHAK PERTAMA atas Pekerjaan yang dimaksud.</li>
                    <li>Jika ada klaim atas Pekerjaan PIHAK KEDUA maka PIHAK PERTAMA melakukan korespondensi kepada
                        PIHAK KEDUA baik melalui email dan/atau surat
                        yang ditunjukkan kepada PIHAK KEDUA, sejak dari tanggal terkirimnya klaim tersebut, selanjutnya
                        PIHAK KEDUA berkewajiban melakukan, perbaikan
                        selambat-lambatnya 2x24 (dua kali dua puluh empat) jam dan/atau sesuai dengan jadwal yang
                        disepakati PARA PIHAK.</li>
                    <li>Garansi tidak berlaku apabila adanya kejadian yang bersifat force majeure / bencana alam /
                        keadaan kahar (termasuk antara lain bencana alam, kebakaran,
                        banjir, peperangan, kerusakan massa, huru hara, tindakan -tindakan yang dilakukan oleh
                        Pemerintah). Bahwa diwajibkan PIHAK KEDUA memberitahukan
                        secara tertulis dalam kurun waktu 7 (tujuh) hari setelah terjadinya kejadian sebagaimana
                        tersebut dalam Angka 3.6 ini.</li>
                </ol>
            </li>

            <li><strong>Cara Pembayaran &amp; Pajak</strong>
                <ol>
                    <li>Pembayaran akan dilakukan kepada PIHAK KEDUA setelah diterimanya dokumen dari PIHAK KEDUA secara
                        lengkap dan benar sebagai berikut:
                        <ol>
                            <li>Invoice ASLI dan bermaterai;</li>
                            <li>E-Faktur Pajak ASLI;</li>
                            <li>SPK ASLI/ Copy yang telah ditandatangani lengkap oleh PARA PIHAK;</li>
                            <li>Berita Acara Serah Terima (BAST) Pekerjaan, dilampirkan juga ceklist termasuk dan tidak
                                terbatas foto sebelum dan sesudah pekerjaan dilakukan
                                oleh PIHAK KEDUA dan telah ditandatangani juga oleh PIHAK PERTAMA. Penandatanganan PIHAK
                                PERTAMA cukup dengan nama Person In
                                Charge (PIC) yang telah dituliskan kedalam Surat Perintah Kerja (SPK) ini; (selanjutnya
                                disebut "Dokumen Penagihan")</li>
                        </ol>
                    </li>
                    <li>Apabila Dokumen Penagihan tidak benar atau tidak lengkap maka PIHAK PERTAMA selanjutnya akan
                        mengembalikan dokumen untuk dilengkapi kembali
                        oleh PIHAK KEDUA.</li>
                    <li>PIHAK KEDUA menjamin dan menyatakan sah dan benar seluruh PPN atas Faktur Pajak yang diterbitkan
                        oleh PIHAK KEDUA atas pembelian pembelian
                        Barang Kena Pajak (BKP) /Jasa Kena Pajak (JKP) oleh PIHAK PERTAMA dan PIHAK KEDUA bertanggung
                        jawab atas keabsahannya.</li>
                    <li>Bahwa seluruh PPN atas Faktur Pajak sebagaimana tersebut diatas, wajib disetorkan dan dilaporkan
                        oleh PIHAK KEDUA sesuai dengan ketentuan
                        perpajakan yang berlaku.</li>
                    <li>Apabila di kemudian hari seluruh PPN atas Faktur Pajak yang diterbitkan oleh PIHAK KEDUA
                        sebagaimana tersebut di atas tidak sesuai dengan ketentuan
                        perpajakan yang berlaku maka PIHAK KEDUA wajib bertanggung jawab sepenuhnya dan memberikan ganti
                        rugi atas seluruh kerugian yang diderita oleh
                        PIHAK PERTAMA, membebaskan dan melepaskan PIHAK PERTAMA dari sanksi pajak yang disebabkan
                        pelanggaran oleh PIHAK PERTAMA sebagaimana
                        dimaksud dalam Ayat ini.</li>
                    <li>Untuk pembayaran menggunakan transfer. Supplier wajib mencantumkan nama, nomor rekening &amp;
                        nama bank pada setiap invoice penagihan.</li>
                    <li>Nama yang tercantum di giro adalah orang yang bisa melakukan setor giro ke bank, apabila nama
                        berbeda / diwakilkan maka wajib menggunakan surat kuasa.</li>
                    <li>Tanggal giro dibuat sampai tanggal dicairkan (efektif) maksimal 70 hari. Apabila lewat dari
                        tanggal / ketentuan tersebut maka giro dianggap hangus / tidak
                        dapat dicairkan. Untuk pembuatan giro ulang (revisi) akan dikenakan denda Rp 100.000 (Seratus
                        ribu rupiah) per giro.</li>
                    <li>Batas waktu penagihan SPK adalah maksimal 2 bulan dari tanggal BAST digital yang telah
                        ditandatangani oleh PARA PIHAK, melewati batas waktu tersebut
                        penagihan SPK tidak dapat di proses/Hangus.</li>
                    <li>Masing-masing lembar BAST &amp; Checklist (ASLI) diperuntukkan USER dan Vendor, untuk proses
                        penagihan selanjutnya.</li>
                </ol>
            </li>

            <li><strong>Penalty</strong>
                <ol>
                    <li>Penalty akan dikenakan kepada PIHAK KEDUA sebesar perhitungan yang tercantum pada SPK apabila
                        terjadi keterlambatan dalam penyelesaian atau
                        penyerahan Pekerjaan yang tidak sesuai dengan jangka waktu yang disepakati sebagaimana diatur
                        dalam SPK. Adapun nilai penalty sebagai berikut:
                        <ol>
                            <li>Penalty Rp. 100.000/hari keterlambatan (Nilai SPK ≤ Rp. 50.000.000)</li>
                            <li>Penalty Rp. 250.000/hari keterlambatan (Nilai SPK 50.000.001 - 150.000.000)</li>
                            <li>Penalty Rp. 500.000/hari keterlambatan (Nilai SPK ≥ Rp. 150.000.001)</li>

                        </ol>
                        Namun apabila pekerjaan tidak dapat diselesaikan oleh PIHAK KEDUA, maka PIHAK KEDUA memberikan
                        surat konfirmasi kepada PIHAK PERTAMA terkait
                        alasan keterlambatan tersebut.
                    </li>
                    <li>Penalty sebagaimana dimaksud Angka 5.1 diatas tetap berlaku apabila penundaan penyelesaian atau
                        penyerahan Pekerjaan dilakukan PIHAK KEDUA tanpa
                        persetujuan PIHAK PERTAMA.</li>
                    <li>Jika setelah dikenakan penalty maksimal 20 % dari total nilai DPP SPK dan PIHAK KEDUA masih
                        belum mampu menyelesaikan atau menyerahkan
                        Pekerjaan tersebut kepada PIHAK PERTAMA maka selanjutnya PIHAK PERTAMA akan menunjuk pihak lain
                        untuk menggantikan PIHAK KEDUA untuk
                        menyelesaikan Pekerjaan tersebut. Biaya penggantian akan dibebankan kepada PIHAK KEDUA.</li>
                    <li>Atas pemutusan Pekerjaan sebagaimana diatur ayat 5.3 diatas, PIHAK PERTAMA hanya melakukan
                        pembayaran kepada PIHAK KEDUA sebatas pekerjaan
                        yang telah diselesaikan dengan baik berdasarkan berita acara pekerjaan yang telah ditandatangani
                        PARA PIHAK.</li>
                    <li>Keterlambatan complete BAST, penalty dan pemotongan atas tagihan vendor (Bila ada), maka vendor
                        wajib koordinasi langsung dengan USER terkait.</li>
                </ol>
            </li>

            <li><strong>Pengakhiran Surat Perintah Kerja dan Akibatnya</strong>
                <ol>
                    <li>Apabila PIHAK KEDUA dalam melaksanakan Pekerjaan tidak memenuhi persyaratan sebagaimana yang
                        disetujui PARA PIHAK atau tidak mampu
                        menyelesaikan Pekerjaan maka PIHAK PERTAMA akan membuat surat teguran kepada PIHAK KEDUA dan
                        apabila sampai dengan batas maksimal yaitu 3
                        (tiga) kali diterbitkannya surat teguran kepada PIHAK KEDUA selanjutnya akan dilakukan
                        pengakhiran hubungan kerjasama SPK ini secara sepihak tanpa
                        adanya kewajiban lebih lanjut dan/atau kompensasi apapun yang harus dipenuhi oleh PIHAK PERTAMA
                        kepada PIHAK KEDUA. Atas pengakhiran ini PIHAK
                        PERTAMA berhak melakukan penunjukan dan /atau penggantian supplier lain.</li>
                    <li>Terhadap pengakhiran tersebut maka PIHAK PERTAMA hanya melakukan pembayaran kepada PIHAK KEDUA
                        sebatas untuk pekerjaan yang telah
                        terpasang/terselesaikan dengan baik dan benar oleh PIHAK KEDUA sesuai berita acara yang telah
                        ditandatangani PARA PIHAK. PIHAK PERTAMA
                        dibebaskan dari PIHAK KEDUA dari segala tuntutan dan gugatan dari pihak-pihak lain baik pidana
                        dan/atau perdata yang akibat pengakhiran ini.</li>
                </ol>
            </li>

            <li><strong>Lain-lain</strong>
                <ol>
                    <li>Bahwa dalam pelaksanaan Pekerjaan di Lokasi Pekerjaan yang ditetapkan PIHAK PERTAMA maka PIHAK
                        KEDUA wajib mematuhi segala peraturan dan
                        SOP yang berlaku di Lokasi Pekerjaan dari PIHAK PERTAMA. Segala bentuk tindakan kriminal,
                        kelalaian yang mengakibatkan kerugian, kehilangan,
                        kecelakaan, kematian ataupun luka-luka dikarenakan kelalaian dari PIHAK KEDUA maka PIHAK KEDUA
                        wajib bertanggung jawab serta membebaskan
                        PIHAK PERTAMA atas tuntutan, gugatan dan ganti kerugian dari pihak lainnya.</li>
                    <li>Dalam hal terjadi perselisihan dalam melaksanakan Perjanjian ini, maka akan diselesaikan oleh
                        PARA PIHAK secara musyawarah untuk mufakat, apabila
                        tidak dapat diselesaikan secara musyawarah maka Para Pihak dengan ini sepakat untuk memilih
                        domisili hukum yang tetap dan tidak berubah yaitu pada
                        Kantor Panitera Pengadilan Negeri Setempat.</li>
                    <li>Bahwa dengan ditandatangani SPK ini maka Ketentuan-ketentuan dan Syarat-syarat ini berlaku dan
                        wajib dipenuhi PARA PIHAK.</li>
                    <li>Apabila terjadi kecelakaan kerja dan / kematian pekerja PIHAK KEDUA dilokasi pekerjaan merupakan
                        tanggung jawab PIHAK KEDUA sepenuhnya, dan
                        PIHAK KEDUA membebaskan PIHAK PERTAMA dari segala tuntutan gugatan pidana atau perdata dan / hal
                        - hal lainya akibat kecelakaan kerja yang
                        mengakibatkan cacat dan / kematian pekerja tersebut.</li>
                </ol>
            </li>
        </ol>

        <p><strong>Demikian Surat Perintah Kerja ini dibuat sesuai dengan {{ $po->sppbjktid }} agar dapat dipergunakan
                sebagaimana
                mestinya.</strong></p>

        {{-- Footer (company + paraf) --}}
        <div class="footer">
            <table>
                <tr>
                    <td>
                        <p>
                            <strong>{{ $po->cpny_id }} - {{ $company->cpny_name }}</strong><br>
                            {{ $company->address_line1 }}
                            Telp: {{ $company->phone }} Fax: {{ $company->fax }}
                        </p>
                    </td>
                    <td>
                        <div class="sign">Paraf PIHAK KEDUA</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Fixed bottom line --}}
        {{-- <div class="fixed-footer">
            <div>Created: Wahyu Dwi Harnowo, Sent by: Wahyu Dwi Harnowo, On: 9/23/2025 9:36:55 AM</div>
            <div>Page 1 of 3</div>
        </div> --}}

    </div>

</body>

</html>
