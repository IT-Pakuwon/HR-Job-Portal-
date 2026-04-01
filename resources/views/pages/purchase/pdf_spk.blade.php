<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perintah Kerja — {{ $company->cpny_name }}</title>

    <style>
        /* ================================= */
        /* PAGE SETUP */
        /* ================================= */

        * {
            font-family: "DejaVu Sans";
        }

        @page {
            size: A4;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: "DejaVu Sans";
            font-size: 9px;
            line-height: 1;
            color: #000;
        }

        /* ================================= */
        /* TEXT */
        /* ================================= */

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0 0 4px 0;
            line-height: 1.1;
            page-break-after: auto;
        }

        p {
            margin: 3px 0;
            text-align: justify;
            line-height: 1.1;
            page-break-inside: auto;
        }

        .bold {
            font-weight: bold;
        }


        .italic {
            font-style: italic;
        }

        span {
            font-weight: 600;
        }

        /* ================================= */
        /* PAGE STRUCTURE */
        /* ================================= */

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* ================================= */
        /* TABLE */
        /* ================================= */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 3px 4px;
            vertical-align: top;
        }

        th {
            font-weight: bold;
        }

        tr {
            page-break-inside: auto;
        }

        /* ================================= */
        /* LIST */
        /* ================================= */

        ol,
        ul {
            margin: 4px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 4px;
            text-align: justify;
            line-height: 1.1;
        }

        /* Prevent list items from splitting */
        li {
            page-break-inside: auto;
        }

        /* ================================= */
        /* HEADER */
        /* ================================= */

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* ================================= */
        /* LEGAL NUMBERING */
        /* ================================= */

        /* MAIN (1,2,3) */

        ol.main {
            counter-reset: section;
            list-style: none;
            padding-left: 0;
        }

        ol.main>li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 8px;
        }

        ol.main>li::before {
            counter-increment: section;
            content: counter(section) ". ";
            position: absolute;
            left: 0;
            width: 25px;
        }

        /* SUB (1.1,1.1) */

        ol.sub {
            counter-reset: subsection;
            list-style: none;
            padding-left: 0;
            margin-top: 4px;
        }

        ol.sub>li {
            position: relative;
            padding-left: 36px;
            margin-bottom: 6px;
        }

        ol.sub>li::before {
            counter-increment: subsection;
            content: counter(section) "." counter(subsection) ". ";
            position: absolute;
            left: 0;
            width: 34px;
        }

        /* LETTER (a,b,c) */

        ol.alpha {
            list-style-type: lower-alpha;
            padding-left: 22px;
            margin-top: 4px;
        }

        /* NUMBER BRACKET (1) */

        ol.num {
            list-style: none;
            counter-reset: num;
            padding-left: 24px;
        }

        ol.num>li {
            position: relative;
            padding-left: 18px;
            margin-bottom: 4px;
        }

        ol.num>li::before {
            counter-increment: num;
            content: counter(num) ") ";
            position: absolute;
            left: 0;
        }

        /* ================================= */
        /* SIGNATURE */
        /* ================================= */

        .signature {
            margin-top: 30px;
            text-align: right;
        }

        .signature .box {
            display: inline-block;
            text-align: center;
            width: 220px;
            height: 80px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        /* ================================= */
        /* FOOTER SPACE */
        /* ================================= */

        .footer-space {
            height: 35mm;
        }
    </style>
</head>

<body>
    {{-- PAGE 1 --}}
    <div class="page">

        {{-- ================= HEADER ================= --}}
        <div class="header" style="text-align:center; margin-bottom:25px;">

            <div class="bold" style="font-size:12px; text-decoration:underline; ">
                SURAT PERINTAH KERJA
            </div>

            <div style="margin-top:6px; font-size:11px;">
                <span style="display:inline-block;  min-width:160px; text-align:center;">
                    <span class="bold">No.{{ $po->ponbr }}</span>
                </span>
            </div>

        </div>


        {{-- ================= PIHAK PERTAMA ================= --}}
        <p style="margin-top: 8px">Pihak yang memberikan Pekerjaan di bawah ini:</p>

        <table style="margin-top:10px;">
            <tr>
                <td style="width:160px;">Perusahaan</td>
                <td>: {{ $company->cpny_name }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $company->address_line1 }}</td>
            </tr>
            <tr>
                <td>NPWP</td>
                <td>: {{ $company->tax_registration }}</td>
            </tr>
            <tr>
                <td>Alamat NPWP</td>
                <td>: {{ $company->tax_address_line }}</td>
            </tr>
        </table>

        <p>Untuk selanjutnya disebut <span class=bold>"PIHAK PERTAMA"</span>.</p>
        <br>

        {{-- ================= PIHAK KEDUA ================= --}}
        <p>Pihak yang menerima Pekerjaan di bawah ini:</p>

        <table>
            <tr>
                <td style="width:160px;">Nama</td>
                <td>: {{ \Illuminate\Support\Str::title($po->spkvendor) }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: {{ \Illuminate\Support\Str::title($po->spkvendorjabatan) }}</td>
            </tr>
            <tr>
                <td>Perusahaan</td>
                <td>: {{ \Illuminate\Support\Str::title($po->vendorname) }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $po->vendoralamat }}</td>
            </tr>
        </table>

        <p>Untuk selanjutnya disebut <span class=bold>"PIHAK KEDUA"</span>.</p>


        <p style="margin-top:20px;">
            PIHAK PERTAMA dan PIHAK KEDUA untuk selanjutnya secara bersama-sama disebut sebagai
            <span class=bold>“PARA PIHAK”</span> dan masing-masing disebut sebagai <span class=bold>“PIHAK”.</span>
        </p>


        {{-- ================= DETAIL PEKERJAAN ================= --}}
        <p style="margin-top:20px;">
            Dengan ini PIHAK PERTAMA memberikan tugas kepada PIHAK KEDUA untuk melaksanakan pekerjaan:
        </p>


        @php
            $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
            $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
        @endphp


        <table style="margin-top:10px;">
            <tr>
                <td style="width:200px;">1. <span class=bold>Pekerjaan</span></td>
                <td>: {{ $po->keperluan }}</td>
            </tr>

            <tr>
                <td>2. <span class=bold>Jangka Waktu</span></td>
                <td>:
                    {{ \Carbon\Carbon::parse($po->spkstartworkingdate)->translatedFormat('d F Y') }}
                    s/d
                    {{ \Carbon\Carbon::parse($po->spkendtworkingdate)->translatedFormat('d F Y') }}
                    (Timeline Schedule Terlampir)
                </td>
            </tr>

            <tr>
                <td>3. <span class=bold>Lokasi Pekerjaan</span></td>
                <td>: {{ $location ?? '-' }}</td>
            </tr>

            <tr>
                <td>4. <span class=bold>Biaya Pekerjaan</span></td>
                <td>:
                    Rp {{ $nf2($grand) }}
                    <br>
                    (Terbilang : {{ $terbilang }})
                </td>
            </tr>

            <tr>
                <td>5. <span class="bold">Cara Pembayaran</span></td>
                <td>: Transfer</td>

                {{-- <td>
                    : a. Uang Muka :

                    @if ($dpTerm)
                        @php
                            $dpPct = (float) $dpTerm->payment_pct;
                            $dpAmount = ($dpPct / 100) * $grand;
                        @endphp

                        Rp {{ $nf2($dpAmount) }} ({{ $dpPct }}%)
                    @else
                        Rp 0
                    @endif

                    <br>
                    &nbsp;&nbsp; b. Sistem Pembayaran :

                    @php
                        $terms = $paymentTerms->where('terms_type', '!=', 'DP')->unique('terms_name');
                    @endphp

                    @if ($terms->count())

                        @foreach ($terms as $term)
                            @php
                                $pct = (float) $term->payment_pct;
                                $amount = ($pct / 100) * $grand;
                            @endphp

                            <br>&nbsp;&nbsp;&nbsp;&nbsp;
                            {{ $term->terms_name }}
                            - Rp {{ $nf2($amount) }}
                            ({{ $pct }}%)
                        @endforeach
                    @else
                        <br>&nbsp;&nbsp;&nbsp;&nbsp;
                        {{ $poTerms->top_name ?? '-' }}
                        - Rp {{ $nf2($grand) }}

                    @endif
                </td> --}}
            </tr>
            <tr>
                <td>6. <span class=bold>Garansi Pekerjaan</span></td>
                <td>: {{ $po->spkwarranty ?? '-' }}</td>
            </tr>

            {{-- <tr>
                <td>7. <span class=bold>Nilai Garansi Pekerjaan</span></td>
                <td>: Rp {{ $nf2($grand * 0.05) }}</td>
            </tr> --}}
        </table>


        {{-- ================= TABLE PEKERJAAN ================= --}}
        <br>
        <table style="width:100%; border-collapse:collapse; font-size:9px; margin-top:10px;">

            <thead>
                <tr style="border-top:1px solid #000; border-bottom:1px solid #000;">
                    <th style="text-align:left; padding:6px 4px;">
                        Lingkup Pekerjaan
                    </th>

                    <th style="text-align:right; padding:6px 4px; width:160px;">
                        Amount
                    </th>
                </tr>
            </thead>

            <tbody>

                @foreach ($podetail as $item)
                    <tr>
                        <td style="padding:4px 4px;">
                            <span class=bold>{{ $item->inventory_descr }}</span>

                            @if ($item->ponote_detail)
                                <div style="margin-top:2px; font-size:9px;">
                                    {{ ucfirst(strtolower($item->ponote_detail)) }}
                                </div>
                            @endif
                        </td>

                        <td style="text-align:right; padding:4px 4px;">
                            {{ $nf2($item->totalcost) }}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td style="padding-top:16px;">
                        <div>
                            TOP&nbsp;&nbsp;:
                            {{ $poTerms->top_name ?? '-' }}
                        </div>

                        @if (!empty($po->vendornote))
                            <div style="margin-top:4px;">
                                <strong>Vendor Note :</strong>
                                {{ $po->vendornote }}
                            </div>
                        @endif
                    </td>

                    <td style="padding-top:16px;">
                        <table width="100%">
                            <tr>
                                <td style="text-align:left;">PPN&nbsp;&nbsp;:</td>
                                <td style="text-align:right;">Rp {{ $nf2($ppn) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top:6px;">
                        <span class=bold>TOTAL :</span>
                    </td>

                    <td style="text-align:right; border-top:1px solid #000; padding-top:6px;">
                        <span class=bold>Rp {{ $nf2($grand) }}</span>
                    </td>
                </tr>

            </tbody>

        </table>


        <table style="width:100%; border-collapse:collapse; font-size:9px; margin-top:10px;">
            @php
                $retensiTerm = $paymentTerms->where('terms_type', 'Retensi')->unique('terms_name')->first();

                $retensi = 0;

                if ($retensiTerm) {
                    $retensi = ((float) $retensiTerm->payment_pct / 100) * $grand;
                }
            @endphp
            {{-- RETENSI --}}
            @if ($retensi > 0)
                <tr>
                    <td style="width:180px; padding:3px 0;">
                        Retensi Pekerjaan
                    </td>

                    <td style="width:15px; text-align:center;">
                        :
                    </td>

                    <td>
                        Rp {{ $nf2($retensi) }} &nbsp;&nbsp; (5%)
                    </td>
                </tr>



                {{-- SYARAT KHUSUS --}}
                <tr>
                    <td style="padding-top:6px;">
                        Syarat Khusus
                    </td>

                    <td style="text-align:center; padding-top:6px;">
                        :
                    </td>

                    <td style="padding-top:6px; text-align:justify;">
                        Pencairan Retensi Pekerjaan dilaksanakan setelah BAST Retensi ditandatangani oleh <span
                            class=bold>
                            PARA
                            PIHAK.</span>
                    </td>
                </tr>
            @endif

            {{-- CONTACT PERSON --}}
            <tr>
                <td style="padding-top:6px;">
                    Contact Person
                </td>

                <td style="text-align:center; padding-top:6px;">
                    :
                </td>

                <td style="padding-top:6px;"></td>
            </tr>

        </table>


        <table style="width:100%; border-collapse:collapse; font-size:9px; margin-top:4px;">

            <tr>
                <td style="width:20px;">a.</td>

                <td style="width:140px;">
                    PIHAK PERTAMA
                </td>

                <td style="width:10px;">:</td>

                <td>
                    {{ $po->spkpic ?? '-' }},
                    {{ $po->spkpicjabatan ?? '-' }},
                    {{ $po->spkpicphone ?? '-' }},
                    {{ $po->spkpicemail ?? '-' }}
                </td>
            </tr>


            <tr>
                <td style="padding-top:3px;">b.</td>

                <td style="padding-top:3px;">
                    PIHAK KEDUA
                </td>

                <td style="padding-top:3px;">:</td>

                <td style="padding-top:3px;">
                    {{ $po->spkvendor ?? '-' }},
                    {{ $po->spkvendorjabatan ?? '-' }},
                    {{ $po->spkvendorphone ?? '-' }},
                    {{ $po->spkvendoremail ?? '-' }}
                </td>
            </tr>

        </table>
    </div>

    <div class="page">
        <div class="header" style="text-align:center; margin-bottom:8px;">

            <div style="font-size:12px; font-weight:bold;">
                SYARAT DAN KETENTUAN UMUM SURAT PERINTAH KERJA (SPK)
            </div>

        </div>
        <ol class="main">


            <li>
                <span class=bold>Tugas Pekerjaan</span>

                <ol class="sub">

                    <li>
                        PIHAK PERTAMA memberikan Pekerjaan kepada PIHAK KEDUA sebagaimana Lingkup Pekerjaan, metode
                        kerja dan schedule yang telah disepakati oleh PARA PIHAK, yang merupakan satu kesatuan dan
                        tidak terpisahkan dengan Surat Perintah Kerja ("<span class="bold">SPK</span>") ini. PIHAK
                        KEDUA dengan ini menyatakan
                        menerima dengan baik dan sanggup untuk melakukan Pekerjaan sebagaimana yang telah diatur
                        dalam SPK ini, serta PIHAK KEDUA wajib menaati seluruh ketentuan dan menyelesaikan Pekerjaan
                        berdasarkan Jangka Waktu sebagaimana disepakati dalam SPK ini.
                    </li>

                    <li>
                        PIHAK KEDUA dengan ini menyatakan serta menjamin memiliki kemampuan dalam melaksanakan
                        Pekerjaan sesuai dengan peraturan dan perundang-undangan yang berlaku termasuk dan tidak
                        terbatas perizinan Keselamatan dan Kesehatan Kerja ("<span class="bold">K3</span>"), perizinan
                        ketenagakerjaan serta
                        perizinan terkait lainnya, dan PIHAK KEDUA berkewajiban memberikan salinan perizinan yang
                        berlaku. Apabila PIHAK KEDUA melanggar ketentuan dan/atau tidak memenuhi perizinan yang
                        berlaku dengan pelaksanaan Pekerjaan dan kegiatan usahanya maka PIHAK PERTAMA dibebaskan
                        dari segala tuntutan dan ganti rugi dari pihak lainnya termasuk dari Pemerintah karena PIHAK
                        KEDUA melakukan kelalaian dalam memenuhi perizinan-perizinan yang berlaku.
                    </li>

                    <li>
                        PIHAK KEDUA dengan ini menyatakan serta menjamin bahwa wakil PIHAK KEDUA dalam SPK ini
                        mempunyai kuasa dan wewenang penuh untuk mengikatkan diri, baik untuk menandatangani SPK ini
                        maupun terlibat langsung dalam pelaksanaan SPK ini.
                    </li>

                    <li>
                        PIHAK KEDUA menjamin bahwa deskripsi dalam spesifikasi barang dan/atau bahan material yang
                        diadakan oleh PIHAK KEDUA adalah benar dalam segala hal dan sesuai dalam rangka tujuan
                        pemberian Pekerjaan dalam SPK ini.
                    </li>

                    <li>
                        PIHAK KEDUA wajib membuat rencana Pekerjaan, metode kerja yang benar dan mengutamakan
                        keselamatan kerja dan mutu Pekerjaan, termasuk mendapatkan persetujuan PIHAK PERTAMA terkait
                        seluruh spesifikasi barang dan/atau bahan material yang akan digunakan.
                    </li>

                    <li>
                        Apabila pengadaan barang dari PIHAK KEDUA dengan cara apapun hilang, musnah, rusak sebelum
                        diserah terimakan kepada PIHAK PERTAMA, maka PIHAK KEDUA bertanggung jawab sepenuhnya atas
                        segala kerugian yang timbul.
                    </li>

                    <li>
                        Apabila PIHAK KEDUA melaksanakan penyimpangan sebelum mendapatkan persetujuan dan/atau
                        instruksi tertulis dari PIHAK PERTAMA, maka PIHAK KEDUA wajib memperbaiki bagian Pekerjaan
                        yang menyimpang tersebut serta menanggung seluruh biaya yang timbul akibat dari perbaikan
                        yang dilakukan dan PIHAK KEDUA tidak berhak untuk meminta perpanjangan Jangka Waktu.
                    </li>

                    <li>
                        Tenaga kerja PIHAK KEDUA diwajibkan selalu menjaga, memperhatikan dan melaksanakan K3 pada
                        saat melakukan Pekerjaan.
                    </li>

                    <li>
                        Apabila terjadi kecelakaan kerja yang terbukti nyata karena kesalahan dari tenaga kerja
                        PIHAK KEDUA itu sendiri yang menyebabkan timbulnya luka, cacat atau kematian terhadap tenaga
                        kerja PIHAK KEDUA atau pihak ketiga manapun di Lokasi Pekerjaan merupakan tanggung jawab
                        PIHAK KEDUA sepenuhnya, dan PIHAK KEDUA membebaskan dan melepaskan PIHAK PERTAMA dari segala
                        tuntutan dan gugatan baik pidana atau perdata akibat kecelakaan kerja tersebut.
                    </li>

                    <li>
                        Pekerjaan tambah atau kurang wajib mendapatkan persetujuan tertulis terlebih dahulu dari
                        PIHAK PERTAMA, dengan harga satuan pekerjaan berdasarkan pada harga yang tercantum dalam SPK
                        ini atau yang akan disepakati dikemudian hari oleh PARA PIHAK.
                    </li>

                    <li>

                        <ol class="alpha">

                            <li>
                                Dalam pelaksanaan Pekerjaan, dokumen-dokumen berikut merupakan kesatuan dan bagian
                                yang tidak terpisahkan dari SPK:

                                <ol class="num">

                                    <li>Surat Perintah Kerja (SPK) berikut lampiran-lampirannya (jika ada)</li>
                                    <li>Berita Acara Negosiasi</li>
                                    <li>Berita Acara Klarifikasi</li>
                                    <li>Berita Acara <span class="italic">Aanwijzing / Pre Tender Meeting</span></li>
                                    <li>Spesifikasi Teknis dan/atau <span class="italic"> Terms of Reference (TOR)
                                        </span></li>
                                    <li><span class="italic">Bill of Quantity</span></li>

                                </ol>

                            </li>

                            <li>
                                Apabila terjadi pertentangan ketentuan antara suatu dokumen dengan ketentuan dalam
                                dokumen yang lain, maka yang berlaku adalah ketentuan dalam dokumen yang lebih
                                tinggi berdasarkan urutan hierarki pada ayat 1.11 huruf a diatas.
                            </li>

                        </ol>


                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Jangka Waktu Pelaksanaan</span>

                <ol class="sub">

                    <li>
                        PIHAK KEDUA wajib menaati dan menyelesaikan Pekerjaan sesuai dengan Jangka Waktu yang telah
                        ditetapkan dan disepakati dalam SPK ini.
                    </li>

                    <li>
                        Jika dalam pelaksanaan Pekerjaan terjadi sesuatu hal tidak terduga di Lokasi Pekerjaan sehingga
                        PIHAK KEDUA terpaksa melakukan penundaan atas Pekerjaan tersebut, maka PIHAK KEDUA wajib
                        memberikan laporan tertulis disertai dengan berita acara penundaan kepada PIHAK PERTAMA untuk
                        mendapatkan persetujuan dari PIHAK PERTAMA. Jika dalam pemeriksaan PIHAK PERTAMA ternyata
                        penundaan yang dilakukan PIHAK KEDUA adalah tidak wajar maka untuk penundaan Pekerjaan oleh
                        PIHAK KEDUA dapat ditinjau atau ditolak.
                    </li>

                    <li>
                        PIHAK KEDUA berkewajiban berkoordinasi dengan PIHAK PERTAMA untuk dilakukan revisi/perubahan
                        jadwal Pekerjaan dan / atau revisi / perubahan metode kerja apabila terjadi kendala pada saat
                        pelaksanaan Pekerjaan atas metode kerja sebelumnya, revisi /penundaan tersebut harus disetujui
                        oleh PIHAK PERTAMA. Revisi/perubahan jadwal Pekerjaan tersebut wajib dituangkan dalam Berita
                        Acara Keterlambatan.
                    </li>

                    <li>
                        PIHAK KEDUA wajib menyelesaikan Pekerjaan yang dimaksud sesuai dengan jadwal perubahan dan /
                        atau metode kerja terakhir yang telah disepakati PARA PIHAK.
                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Syarat-syarat Pelaksanaan Garansi</span>

                <ol class="sub">

                    <li>
                        PIHAK KEDUA wajib menyerahkan dokumen-dokumen garansi ( warranty ), salinan dokumen jaminan
                        kualitas, hasil pengujian dan sertifikat bahan/barang dan dokumen-dokumen lainnya sesuai
                        ketentuan dalam SPK ini.
                    </li>

                    <li>
                        Garansi Pekerjaan yang dimaksud dalam SPK ini adalah termasuk namun tidak terbatas penggantian
                        material yang rusak dengan material baru untuk perbaikan Pekerjaan yang dianggap tidak sempurna
                        oleh PIHAK PERTAMA.
                    </li>

                    <li>
                        Garansi dari PIHAK KEDUA kepada PIHAK PERTAMA berlaku sejak ditandatanganinya Berita Acara
                        Serah Terima ("<span class="bold">BAST</span>") dimana Pekerjaan telah diterima dengan baik,
                        tepat dan benar sesuai
                        permintaan dari PIHAK PERTAMA. Masa berlaku garansi tertulis dalam SPK ini.
                    </li>

                    <li>

                        <ol class="alpha">

                            <li>
                                Biaya atas Garansi sepenuhnya ditanggung oleh PIHAK KEDUA. PIHAK KEDUA akan menyerahkan
                                surat / kartu garansi kepada PIHAK PERTAMA atas Pekerjaan yang dimaksud.
                            </li>

                            <li>
                                PIHAK KEDUA wajib memastikan masa berlaku garansi tersebut sampai dengan jangka waktu
                                yang disepakati PARA PIHAK berakhir.
                            </li>

                        </ol>

                    </li>
                    <li>
                        Jika ada klaim atas Pekerjaan PIHAK KEDUA maka PIHAK PERTAMA melakukan korespondensi kepada
                        PIHAK KEDUA baik melalui email dan/atau surat yang ditujukan kepada PIHAK KEDUA, sejak dari
                        tanggal terkirimnya klaim tersebut, selanjutnya PIHAK KEDUA berkewajiban melakukan perbaikan
                        selambat-lambatnya 2x24 (dua kali dua puluh empat) jam dan/atau sesuai dengan jadwal yang
                        disepakati PARA PIHAK.
                    </li>

                    <li>
                        Garansi tidak berlaku apabila adanya kejadian yang bersifat <span class="italic">force
                            majeure</span> / bencana alam /
                        keadaan kahar (termasuk namun tidak terbatas pada kebijakan pemerintah, politik, militer,
                        peperangan, huru hara, bencana alam, pemogokan oleh karyawan PARA PIHAK, epidemi, pandemi,
                        blokade, pemberontakan, kebanjiran, kebakaran besar, gangguan listrik dan telekomunikasi). PIHAK
                        KEDUA wajib memberitahukan secara tertulis dalam kurun waktu paling lambat 7 (tujuh) hari
                        setelah terjadinya kejadian sebagaimana tersebut dalam Ayat 3.6 ini.
                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Harga Pekerjaan &amp; Cara Pembayaran</span>

                <ol class="sub">

                    <li>
                        Harga Pekerjaan sudah termasuk pajak-pajak dan perizinan sesuai peraturan perundang-undangan
                        yang berlaku.
                    </li>

                    <li>
                        Harga Pekerjaan tidak berubah selama Jangka Waktu SPK, kecuali terdapat perubahan Lingkup
                        Pekerjaan atau pekerjaan tambah kurang yang disetujui PIHAK PERTAMA.
                    </li>

                    <li>
                        Harga Pekerjaan berlaku hingga seluruh Lingkup Pekerjaan selesai dilaksanakan sesuai dengan
                        Jangka Waktu SPK.
                    </li>

                    <li>
                        Pembayaran akan dilakukan kepada PIHAK KEDUA setelah diterimanya dokumen dari PIHAK KEDUA secara
                        lengkap dan benar sebagai berikut:

                        <ol class="num">

                            <li>Kuitansi bermeterai dan Invoice Asli.</li>

                            <li>E-Faktur Pajak 2 (dua) Rangkap Asli.</li>

                            <li>SPK Asli yang telah ditandatangani dan di stempel perusahaan oleh PIHAK KEDUA.</li>

                            <li>
                                BAST Pekerjaan, formulir BAST akan diberikan oleh PIHAK PERTAMA untuk ditandatangani
                                dan di stempel perusahaan oleh PIHAK KEDUA (dibuat 2 rangkap Asli), berikut dengan
                                foto-foto Pekerjaan yang telah dilakukan oleh PIHAK KEDUA. Penandatanganan PIHAK PERTAMA
                                diwakili oleh <span class="italic">Person In Charge</span> (PIC) yang tercantum di dalam
                                SPK ini. Setelah
                                ditandatanganinya BAST tersebut maka Pekerjaan PIHAK KEDUA telah diserahterimakan dengan
                                baik dan sudah disetujui oleh PIHAK PERTAMA.
                            </li>

                        </ol>

                        <p>(selanjutnya seluruhnya disebut “<span class="bold">Dokumen Penagihan</span>”)</p>

                    </li>

                    <li>
                        Kecuali ditetapkan lain di dalam SPK, pembayaran kepada PIHAK KEDUA dilaksanakan
                        selambat-lambatnya 21 (dua puluh satu) hari kerja setelah seluruh Dokumen Penagihan secara
                        lengkap dan benar diterima oleh PIHAK PERTAMA.
                    </li>

                    <li>
                        Apabila Dokumen Penagihan tidak benar atau tidak lengkap maka PIHAK PERTAMA selanjutnya akan
                        mengembalikan dokumen untuk dilengkapi kembali oleh PIHAK KEDUA.
                    </li>

                    <li>
                        PIHAK KEDUA menjamin dan menyatakan sah dan benar seluruh PPN atas Faktur Pajak yang diterbitkan
                        oleh PIHAK KEDUA atas pembelian Barang Kena Pajak (BKP) / Jasa Kena Pajak (JKP) oleh PIHAK
                        PERTAMA dan PIHAK KEDUA bertanggung jawab atas keabsahannya.
                    </li>

                    <li>
                        Bahwa seluruh PPN atas Faktur Pajak sebagaimana tersebut di atas wajib disetorkan dan dilaporkan
                        oleh PIHAK KEDUA sesuai dengan ketentuan perpajakan yang berlaku.
                    </li>

                    <li>
                        Apabila di kemudian hari seluruh PPN atas Faktur Pajak yang diterbitkan oleh PIHAK KEDUA
                        sebagaimana tersebut di atas tidak sesuai dengan ketentuan perpajakan yang berlaku maka PIHAK
                        KEDUA wajib bertanggung jawab sepenuhnya dan memberikan ganti rugi atas seluruh kerugian yang
                        diderita oleh PIHAK PERTAMA, membebaskan dan melepaskan PIHAK PERTAMA dari sanksi pajak yang
                        disebabkan pelanggaran oleh PIHAK KEDUA sebagaimana dimaksud dalam Ayat ini.
                    </li>

                    <li>
                        Untuk pembayaran menggunakan transfer, PIHAK KEDUA wajib mencantumkan nama, nomor rekening dan
                        nama bank pada setiap invoice penagihan.
                    </li>

                    <li>
                        Apabila terjadi keterlambatan penyerahan dokumen tagihan, yaitu terhitung 2 (dua) bulan dari
                        tanggal BAST digital yang telah ditandatangani oleh PARA PIHAK, maka PIHAK PERTAMA berhak untuk
                        tidak memproses pembayaran kepada PIHAK KEDUA.
                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Penalti</span>

                <ol class="sub">

                    <li>
                        Jika PIHAK KEDUA dengan alasan apapun tidak dapat menyelesaikan Pekerjaan (yang ditandai dengan
                        BAST) sampai dengan berakhirnya Jangka Waktu SPK, maka PIHAK KEDUA wajib membayar denda penalti
                        keterlambatan kepada PIHAK PERTAMA, sesuai dengan besaran Penalti sebagai berikut:

                        <ol class="num">

                            <li>Penalti Rp100.000/hari keterlambatan (Nilai SPK ≤ Rp50.000.000)</li>

                            <li>Penalti Rp250.000/hari keterlambatan (Nilai SPK antara &gt; Rp50.000.000 sampai ≤
                                Rp150.000.000)</li>

                            <li>Penalti Rp500.000/hari keterlambatan (Nilai SPK antara &gt; Rp150.000.000 sampai ≤
                                Rp500.000.000)</li>

                            <li>Penalti 1 &#8240; (satu per mil) dari Nilai SPK / hari keterlambatan (Nilai SPK &gt;
                                Rp500.000.000)</li>

                        </ol>

                        <p>
                            Jumlah penalti keterlambatan sebagaimana dimaksud di atas dibatasi paling tinggi sebesar
                            20% (dua puluh persen) dari nilai total harga Pekerjaan sebagaimana tercantum dalam SPK ini.
                        </p>

                    </li>

                    <li>
                        Penalti sebagaimana dimaksud Ayat 5.1 di atas tetap berlaku apabila penundaan penyelesaian
                        atau penyerahan Pekerjaan dilakukan PIHAK KEDUA tanpa persetujuan PIHAK PERTAMA.
                    </li>

                    <li>
                        Jika setelah dikenakan penalti maksimal 20% dari total nilai DPP SPK dan PIHAK KEDUA masih
                        belum mampu menyelesaikan atau menyerahkan Pekerjaan tersebut kepada PIHAK PERTAMA,
                        maka selanjutnya PIHAK PERTAMA akan menunjuk pihak lain untuk menggantikan PIHAK KEDUA
                        untuk menyelesaikan Pekerjaan tersebut. Biaya penggantian akan dibebankan kepada PIHAK KEDUA.
                    </li>

                    <li>
                        Atas pemutusan Pekerjaan sebagaimana diatur ayat 5.3 di atas, PIHAK PERTAMA hanya
                        melakukan pembayaran kepada PIHAK KEDUA sebatas Pekerjaan yang telah diselesaikan dengan
                        baik berdasarkan berita acara pekerjaan yang telah ditandatangani PARA PIHAK.
                    </li>

                    <li>
                        PIHAK KEDUA wajib melakukan koordinasi dengan PIHAK PERTAMA terkait pemotongan
                        tagihan atas penalti keterlambatan yang dilakukan PIHAK KEDUA.
                    </li>

                    <li>
                        PIHAK KEDUA akan dikenakan Penalti apabila diketahui telah memanipulasi spesifikasi yang
                        telah ditetapkan, mengadakan barang/bahan material palsu atau bekas. Penalti tersebut sebesar
                        10 (sepuluh) kali lipat dari harga barang / bahan material yang tercantum dalam SPK dan
                        PIHAK KEDUA wajib mengganti barang / bahan material tersebut dengan barang / bahan material
                        yang sesuai dengan spesifikasi pada SPK ini. Penalti bersifat final dan tidak dapat
                        dikembalikan kepada PIHAK KEDUA.
                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Pengakhiran SPK</span>

                <ol class="sub">

                    <li>
                        SPK ini berakhir demi hukum setelah terpenuhinya seluruh kewajiban PARA PIHAK
                        sesuai Jangka Waktu SPK ini dan dibuktikan dengan BAST.
                    </li>

                    <li>
                        Apabila PIHAK KEDUA dalam melaksanakan Pekerjaan tidak memenuhi persyaratan
                        sebagaimana yang disetujui PARA PIHAK atau tidak mampu menyelesaikan Pekerjaan,
                        maka PIHAK PERTAMA akan membuat surat teguran kepada PIHAK KEDUA, dan apabila
                        sampai dengan batas maksimal yaitu 3 (tiga) kali diterbitkannya surat peringatan
                        kepada PIHAK KEDUA, selanjutnya akan dilakukan pengakhiran SPK secara sepihak
                        tanpa adanya kewajiban lebih lanjut dan/atau kompensasi dalam bentuk apapun yang
                        harus dipenuhi oleh PIHAK PERTAMA kepada PIHAK KEDUA. Atas pengakhiran ini PIHAK
                        PERTAMA berhak melakukan penunjukan dan/atau penggantian kepada pihak lain.
                    </li>

                    <li>
                        SPK ini sewaktu-waktu dapat diakhiri oleh PIHAK PERTAMA apabila terjadi,
                        namun tidak terbatas pada hal-hal sebagai berikut:

                        <ol class="alpha">

                            <li>Apabila harta PIHAK KEDUA berada dalam sitaan para krediturnya;</li>

                            <li>Apabila PIHAK KEDUA mengajukan proses kepailitan atau dikenakan tuntutan
                                kepailitan yang diajukan oleh pihak ketiga lainnya;</li>

                            <li>PIHAK KEDUA menutup atau dicabut izin usahanya;</li>

                            <li>Apabila PIHAK KEDUA melakukan Perbuatan Melawan Hukum (<span class="bold">PMH</span>).
                            </li>

                        </ol>

                    </li>

                    <li>
                        Dalam hal yang disebutkan dalam Ayat 6.3, PIHAK PERTAMA berhak untuk mengakhiri
                        SPK ini tanpa perlu adanya surat peringatan terlebih dahulu dan atas pengakhiran
                        sebagaimana dimaksud ayat ini PIHAK KEDUA membebaskan PIHAK PERTAMA dari segala
                        kewajiban lebih lanjut dan/atau kompensasi dalam bentuk apapun yang harus
                        dipenuhi setelah tanggal pengakhiran tersebut.
                    </li>

                    <li>
                        Terhadap pengakhiran tersebut maka PIHAK PERTAMA hanya melakukan pembayaran
                        kepada PIHAK KEDUA sebatas untuk Pekerjaan yang telah terpasang/terselesaikan
                        dengan baik dan benar oleh PIHAK KEDUA sesuai Berita Acara yang telah
                        ditandatangani PARA PIHAK. PIHAK PERTAMA dibebaskan oleh PIHAK KEDUA dari segala
                        tuntutan dan gugatan dari pihak-pihak lain baik pidana dan/atau perdata akibat
                        pemutusan ini.
                    </li>

                    <li>
                        PARA PIHAK sepakat untuk mengesampingkan berlakunya ketentuan Pasal 1266 dan
                        Pasal 1267 Kitab Undang-Undang Hukum Perdata, sehingga pemutusan atau pembatalan
                        dengan alasan-alasan sebagaimana diatur dalam SPK ini, secara sah cukup dilakukan
                        dengan pemberitahuan tertulis dari masing-masing PIHAK.
                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Kerahasiaan Informasi</span>

                <ol class="sub">

                    <li>
                        PARA PIHAK sepakat untuk tidak mengungkapkan dokumen, data, informasi, ataupun
                        hasil laporan Pekerjaan sehubungan dengan Surat Perintah Kerja ini (selanjutnya
                        disebut “<span class="bold"> Informasi Rahasia</span>”) dari PIHAK lainnya ke orang atau badan
                        manapun
                        selain daripada yang diperlukan dalam pelaksanaan tugas-tugas, peran-peran atau
                        fungsinya dalam Pekerjaan ini, tanpa mendapat persetujuan tertulis terlebih
                        dahulu dari PIHAK lainnya dan akan melakukan semua tindakan pencegahan yang
                        wajar untuk mencegah terjadinya kelalaian sehubungan dengan pengungkapan
                        Informasi Rahasia sebagaimana dimaksud di atas.
                    </li>

                    <li>
                        PARA PIHAK sepakat, tanpa mendapat persetujuan terlebih dahulu dari PIHAK lainnya
                        tidak akan menggunakan, membuat salinan atau mengalihkan Informasi Rahasia milik
                        PIHAK lainnya selain sebagaimana diperlukan dalam pelaksanaan tugas-tugas,
                        peran-peran atau fungsinya dalam SPK ini, dan akan melakukan semua tindakan
                        pencegahan yang wajar untuk mencegah terjadinya kelalaian dalam penggunaan,
                        pembuatan salinan atau pengalihan Informasi Rahasia tersebut serta menjamin
                        untuk menyimpan asli maupun fotokopi dari dokumen-dokumen atau surat-surat
                        dalam bentuk apapun sebagai pertinggal dan akan senantiasa menjaga
                        kerahasiaannya.
                    </li>

                    <li>
                        Informasi Rahasia tidak termasuk informasi-informasi yang dinyatakan di bawah ini:

                        <ol class="alpha">

                            <li>
                                Telah diterima atau berada dalam penguasaan salah satu Pihak tanpa ada
                                kewajiban untuk merahasiakan.
                            </li>

                            <li>
                                Telah menjadi dapat diketahui oleh pihak ketiga atau semua orang tanpa
                                adanya pembatasan.
                            </li>

                            <li>
                                Telah diketahui secara umum atau menjadi tersedia bagi umum tanpa adanya
                                pelanggaran terhadap ketentuan dari SPK ini.
                            </li>

                            <li>
                                Jika menurut hukum harus diungkapkan, setelah terlebih dahulu diberitahukan
                                kepada masing-masing Pihak sebelum dilakukannya pengungkapan informasi tersebut.
                            </li>

                        </ol>

                    </li>

                </ol>

            </li>

            <li>
                <span style="font-weight:bold; font-style:italic;">Force Majeure </span></span>

                <ol class="sub">

                    <li>
                        Keadaan <span style="font-style:italic;">Force Majeure</span> adalah segala keadaan atau
                        peristiwa yang terjadi di luar kekuasaan PARA PIHAK untuk mengatasinya, termasuk namun
                        tidak terbatas pada kebijakan pemerintah, politik, militer, peperangan, huru-hara,
                        bencana alam, pemogokan oleh karyawan PARA PIHAK, epidemi, pandemi, blokade,
                        pemberontakan, kebanjiran, kebakaran besar, gangguan listrik dan telekomunikasi yang
                        menghalangi secara langsung untuk melaksanakan kewajiban-kewajiban sesuai SPK ini.
                    </li>

                    <li>
                        Segala permasalahan yang timbul sebagai akibat dari terjadinya
                        <span style="font-style:italic;">Force Majeure</span> tersebut akan diselesaikan secara
                        musyawarah oleh PARA PIHAK.
                    </li>

                    <li>
                        PIHAK yang mengalami keadaan atau peristiwa yang sebagaimana yang dimaksud pada ayat
                        8.1 ini, wajib memberitahukan kepada PIHAK lainnya baik secara lisan maupun tertulis,
                        selambat-lambatnya 14 (empat belas) hari kalender terhitung sejak tanggal terjadinya
                        <span style="font-style:italic;">Force Majeure</span> tersebut.
                    </li>

                    <li>
                        Apabila PIHAK yang mengalami keadaan
                        <span style="font-style:italic;">Force Majeure</span> tersebut lalai untuk
                        memberitahukan kepada PIHAK lainnya sebagaimana dimaksud dalam ayat 8.3 ini,
                        maka seluruh kerugian, risiko dan konsekuensi yang mungkin timbul menjadi
                        beban dan tanggung jawab PIHAK yang mengalami
                        <span style="font-style:italic;">Force Majeure</span> tersebut.
                        Keterlambatan atau kelalaian PARA PIHAK dalam memberitahukan terjadinya
                        <span style="font-style:italic;">Force Majeure</span>, mengakibatkan tidak
                        diakuinya peristiwa tersebut sebagai
                        <span style="font-style:italic;">Force Majeure</span> oleh PIHAK lainnya.
                    </li>

                    <li>
                        Apabila keadaan <span style="font-style:italic;">Force Majeure</span> tersebut
                        menyebabkan SPK ini tidak dapat atau tidak efektif untuk dilanjutkan lagi,
                        maka SPK ini dapat diakhiri dengan tetap tunduk pada ketentuan Pasal 6
                        SPK ini, sebagaimana berlaku.
                    </li>

                </ol>

            </li>

            <li>
                <span class=bold>Hukum Yang Berlaku Dan Penyelesaian Perselisihan</span>

                <ol class="sub">

                    <li>
                        SPK ini tunduk pada ketentuan dan peraturan perundang-undangan yang berlaku di Indonesia.
                    </li>

                    <li>
                        Dalam hal terjadi perselisihan dalam melaksanakan Pekerjaan ini, maka akan diselesaikan oleh
                        PARA PIHAK secara musyawarah untuk mufakat. Apabila tidak dapat diselesaikan secara
                        musyawarah maka PARA PIHAK dengan ini sepakat untuk memilih domisili hukum yang tetap
                        dan tidak berubah yaitu pada Kantor Panitera Pengadilan Negeri setempat.
                    </li>

                </ol>

            </li>

            <li>
                <span style="font-weight:bold;">Pakta Integritas</span>

                <p>
                    Dalam rangka penerapan Tata Kelola Perusahaan Yang Baik
                    ( <span style="font-style:italic;">Good Corporate Governance</span>/GCG ),
                    PIHAK KEDUA berkomitmen untuk mematuhi dan menerapkan prinsip-prinsip transparansi,
                    akuntabilitas, dan tanggung jawab yang diterapkan oleh PIHAK PERTAMA dalam pelaksanaan SPK ini.
                    <u>Untuk itu</u> PIHAK KEDUA menerangkan dan menyatakan bahwa:
                </p>

                <ol class="sub">

                    <li>
                        PIHAK KEDUA tidak mempunyai benturan kepentingan dengan Perusahaan termasuk dengan
                        Dewan Komisaris, Direksi dan/atau Karyawan PIHAK PERTAMA, dan berkomitmen untuk tidak
                        melakukan perbuatan melawan hukum yang menyebabkan benturan kepentingan
                        ( <span style="font-style:italic;">Conflict of Interest</span> ) dalam pelaksanaan Pekerjaan
                        ini.
                    </li>

                    <li>
                        PIHAK KEDUA wajib mematuhi dan melaksanakan seluruh peraturan, Kode Etik
                        ( <span style="font-style:italic;">Code of Conduct</span> ), kebijakan dan standar
                        operasional prosedur yang ditetapkan PIHAK PERTAMA serta peraturan perundang-undangan
                        yang berlaku.
                    </li>

                    <li>
                        PIHAK KEDUA tidak akan melakukan perbuatan melawan hukum baik pidana (kriminal)
                        maupun perdata, melanggar etika, norma amoral dan pelanggaran asusila di lingkungan
                        PIHAK PERTAMA.
                    </li>

                    <li>
                        PIHAK KEDUA berkomitmen menerapkan tindakan Anti Korupsi, Anti Penyuapan
                        ( <span style="font-style:italic;">Anti Bribery</span>) ,
                        <span style="font-style:italic;">Anti Lobbying</span> dan Anti Pencucian Uang
                        ( <span style="font-style:italic;">Anti-Money Laundering</span> ), antara lain namun tidak
                        terbatas pada:

                        <ol class="alpha">
                            <li>
                                Tidak melakukan atau mengajak pihak lain untuk melakukan perbuatan korupsi,
                                kolusi, dan nepotisme (KKN).
                            </li>

                            <li>
                                Tidak meminta, menerima, atau memberikan gratifikasi, suap, atau imbalan
                                dalam bentuk apapun, baik secara langsung maupun tidak langsung, kepada
                                Direksi, atau karyawan PIHAK PERTAMA (termasuk keluarga dan kerabatnya).
                            </li>

                            <li>
                                Tidak menawarkan, memberikan, atau menyetujui pemberian hadiah, komisi,
                                rabat (keuntungan), atau bentuk lain kepada PIHAK PERTAMA atau karyawan
                                PIHAK PERTAMA dengan maksud memperoleh atau melaksanakan Pekerjaan.
                            </li>

                            <li>
                                Tidak melakukan aktivitas yang tidak transparan, seperti lobi yang
                                mengarah pada tindakan suap atau tindakan lain yang bertujuan mempengaruhi
                                pihak lain yang melanggar aturan perusahaan PIHAK PERTAMA. Selain itu,
                                tidak memberikan rekomendasi, mengambil keputusan, atau mengeluarkan
                                perintah yang memanfaatkan PIHAK PERTAMA secara langsung maupun tidak
                                langsung untuk kepentingan pribadi, keluarga, kerabat, teman dekat,
                                atau golongan tertentu.
                            </li>

                            <li>
                                Tidak melakukan tindakan pencucian uang selama kerjasama dengan PIHAK
                                PERTAMA, antara lain namun tidak terbatas pada:

                                <ol>
                                    <li>
                                        Menyetorkan, mentransfer, menempatkan, menyembunyikan atau
                                        menyamarkan asal usul dana atau aset yang diperoleh dari
                                        kejahatan atau kegiatan illegal untuk pembiayaan sebagai
                                        pemasok atau pemberian barang/jasa kepada PIHAK PERTAMA.
                                    </li>

                                    <li>
                                        Menggunakan perusahaan fiktif untuk mengaburkan kepemilikan
                                        dana illegal.
                                    </li>

                                    <li>
                                        Melakukan transaksi keuangan lainnya yang tidak wajar atau
                                        melanggar hukum dengan tujuan menghindari deteksi atau laporan
                                        kepada otoritas berwenang.
                                    </li>
                                </ol>
                            </li>
                        </ol>
                    </li>

                    <li>
                        PIHAK KEDUA wajib:
                        <ol class="alpha">
                            <li>
                                Melaporkan setiap indikasi pelanggaran hukum, kode etik, penyelenggaraan,
                                kecurangan atau tindakan lainnya yang tidak sesuai dengan etika, tata nilai,
                                peraturan dan kebijakan PIHAK PERTAMA yang diketahui dilakukan oleh pihak
                                ketiga maupun oleh Direksi atau karyawan PIHAK PERTAMA melalui mekanisme
                                <span style="font-style:italic;">Whistle Blowing System</span> (“<span
                                    class="bold">WBS</span>”)
                                yang ditentukan oleh PIHAK PERTAMA, yaitu:

                                <ol>
                                    <li>
                                        Mengunduh formulir pelaporan di
                                        <span class="bold">whs.pakuwonjati.com</span>
                                    </li>

                                    <li>
                                        Mengirimkan surat laporan tertulis dialamatkan kepada:
                                        <ul>
                                            <li>
                                                Team Pengelola Laporan WBS PT Pakuwon Jati Tbk<br>
                                                Gedung Perkantoran Gandaria 8 Lantai 32,<br>
                                                Jl. Sultan Iskandar Muda (Arteri Pondok Indah)<br>
                                                Kebayoran Lama, Jakarta Selatan.
                                            </li>

                                            <li>
                                                Team Pengelola Laporan WBS PT Pakuwon Jati Tbk<br>
                                                Pakuwon City Mall Lantai 5,<br>
                                                Jl. Kejawan Putih Mutiara No. 17, Surabaya.
                                            </li>
                                        </ul>
                                    </li>
                                </ol>
                            </li>

                            <li>
                                Menyampaikan informasi mengenai pelanggaran dengan akurat dan dapat
                                dipertanggungjawabkan. PIHAK PERTAMA akan menindaklanjuti setiap laporan
                                sesuai dengan prosedur yang berlaku dan akan menjaga kerahasiaan identitas
                                pelapor serta melindungi Pelapor dari segala bentuk tindakan balasan
                                ( <span style="font-style:italic;">retaliation</span> ).
                            </li>
                        </ol>
                    </li>

                    <li>
                        PIHAK KEDUA tidak akan melakukan penipuan, manipulasi data atau perbuatan
                        curang lainnya termasuk namun tidak terbatas pada dokumen-dokumen Perusahaan
                        PIHAK KEDUA dan segala dokumen perizinan PIHAK KEDUA dalam rangka mendapatkan
                        keuntungan dalam proses pengadaan barang/jasa/pekerjaan yang dilakukan.
                    </li>

                    <li>
                        PIHAK KEDUA wajib menjaga informasi dari PIHAK PERTAMA yang bersifat rahasia
                        ataupun informasi-informasi yang diketahui PIHAK KEDUA selama ataupun setelah
                        SPK ini berakhir/diakhiri.
                    </li>

                    <li>
                        PIHAK KEDUA menyatakan bahwa seluruh informasi, data dan komitmen yang
                        disampaikan adalah benar, sah dan dapat dipertanggungjawabkan. Apabila
                        PIHAK KEDUA melanggar ketentuan pasal ini, maka PIHAK KEDUA bersedia
                        bertanggungjawab secara hukum dan bersedia menerima sanksi yang dikenakan
                        oleh PIHAK PERTAMA antara lain pengakhiran SPK, penghapusan PIHAK KEDUA
                        sebagai rekanan PIHAK PERTAMA, pembayaran denda, pelaporan kepada otoritas
                        yang berwenang dan/atau tuntutan pidana sesuai ketentuan perundang-undangan
                        yang berlaku dan/atau sanksi lainnya. Biaya-biaya yang timbul akibat sanksi
                        tersebut menjadi tanggungjawab PIHAK KEDUA. Sehubungan dengan hal tersebut
                        PIHAK KEDUA membebaskan PIHAK PERTAMA dari segala tuntutan pidana dan gugatan
                        perdata dari pihak manapun.
                    </li>

                </ol>
            </li>

            <li>
                <span class=bold>Ketentuan Lain</span>

                <ol class="sub">

                    <li>
                        Bahwa dalam pelaksanaan Pekerjaan di Lokasi Pekerjaan yang ditetapkan PIHAK
                        PERTAMA maka PIHAK KEDUA wajib mematuhi segala peraturan dan Standar
                        Operasional Prosedur (SOP) yang berlaku dari PIHAK PERTAMA di Lokasi
                        Pekerjaan. Segala bentuk tindakan kriminal, kelalaian yang mengakibatkan
                        kerugian, kehilangan, kecelakaan, kematian ataupun luka-luka dikarenakan
                        kelalaian dari PIHAK KEDUA maka PIHAK KEDUA wajib bertanggung jawab serta
                        membebaskan dan melepaskan PIHAK PERTAMA atas tuntutan, gugatan dan
                        ganti kerugian dari pihak lainnya.
                    </li>

                    <li>
                        PIHAK KEDUA dilarang untuk mengalihkan Pekerjaan kepada pihak lain tanpa
                        persetujuan tertulis dari PIHAK PERTAMA.
                    </li>

                    <li>
                        PARA PIHAK atau para penggantinya maupun penerus haknya yang sah terikat
                        pada semua Syarat dan Ketentuan yang tercantum dalam SPK ini.
                    </li>

                    <li>
                        Hal-hal yang belum diatur atau belum cukup diatur dan karenanya perlu
                        dilakukan perubahan ataupun penambahan persyaratan dalam SPK ini,
                        akan ditetapkan oleh PARA PIHAK dalam bentuk addendum, yang merupakan
                        satu kesatuan dengan dan bagian yang tidak terpisahkan dari SPK ini.
                    </li>

                    <li>
                        Bahwa dengan ditandatanganinya SPK ini maka Syarat dan Ketentuan ini
                        berlaku dan wajib dipenuhi PARA PIHAK.
                    </li>

                    <li>
                        Bahwa setelah diterimanya SPK oleh PIHAK PERTAMA, maka PIHAK PERTAMA
                        telah sepakat dan setuju atas pemberian Pekerjaan kepada PIHAK KEDUA.
                    </li>

                </ol>

            </li>

        </ol>

        <p>Demikian SPK ini dibuat untuk dapat dilaksanakan PIHAK KEDUA, dengan catatan jika dalam waktu 7 (tujuh) hari
            SPK ini tidak ditandatangani / dikembalikan, maka PIHAK PERTAMA dapat membatalkan SPK ini secara sepihak
            dengan / tanpa pemberitahuan terlebih dahulu kepada PIHAK KEDUA.</p>
        <br><br>

        <br><br>
        <table style="width:100%; text-align:center; margin-top:40px;">
            <tr>
                <td style="width:50%;"></td>

                <td style="width:50%;">
                    _________, ____________ 2026
                    <br>
                    Tanda Tangan dan Stempel
                </td>
            </tr>

            <tr>
                <td></td>
                <td style="height:90px;"></td>
            </tr>

            <tr>
                <td></td>

                <td>
                    <div style="border-bottom:1px solid #000; width:240px; margin:0 auto;"></div>
                    <div style="margin-top:4px;">
                        {{ \Illuminate\Support\Str::title($po->spkvendor ?? '-') }}
                        <br>
                        {{ \Illuminate\Support\Str::title($po->spkvendorjabatan ?? '-') }}
                    </div>
                </td>
            </tr>
        </table>

    </div>
</body>


</html>
