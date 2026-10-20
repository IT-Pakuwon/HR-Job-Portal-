<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat {{ $kind }} - {{ $agreement->agreement_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 14mm 20mm 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11.5px;
            color: #111;
            line-height: 1.42;
            margin: 0;
            padding: 0;
        }

        p {
            margin: 0 0 9px;
            text-align: justify;
        }

        .addressee {
            margin: 12px 0 12px;
        }

        .subject {
            text-align: center;
            margin: 12px 0;
        }

        .subject span {
            font-weight: bold;
            text-decoration: underline;
        }

        .docs {
            margin: 0 0 9px;
            font-weight: bold;
        }

        .address-block {
            margin: 0 0 9px 22px;
            font-weight: bold;
            font-style: italic;
        }

        .signature {
            margin-top: 14px;
        }

        .signature .space {
            height: 46px;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 8.5px;
            color: #333;
        }
    </style>
</head>

<body>

    @php
        $fmt = fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
        $companyName = $company->cpny_name ?? $agreement->cpny_id;
        $building = $profile['building'] ?: 'mal kami';
        $trade = trim((string) $agreement->trade_name);
        $showTrade = $trade !== '' && mb_strtolower($trade) !== mb_strtolower(trim((string) $agreement->business_name));
        $tradeQuoted = '&ldquo;'.e(mb_strtoupper($trade ?: $agreement->business_name)).'&rdquo;';
        $footerLines = (array) ($profile['footer'] ?? []);
    @endphp

    <table style="width:100%;">
        <tr>
            <td>No. {{ $letter->letter_no }}</td>
            <td style="text-align:right;">{{ $company->city ?? 'Jakarta' }}, {{ $fmt($letter->sent_at) }}</td>
        </tr>
    </table>

    <div class="addressee">
        Kepada Yth.<br>
        @if (filled($agreement->pic_penyewa))
            <strong>Bapak/Ibu {{ $agreement->pic_penyewa }}</strong><br>
        @endif
        @if ($showTrade)
            <strong>{!! '&ldquo;'.e(mb_strtoupper($trade)).'&rdquo;' !!}</strong><br>
        @endif
        <strong>{{ $agreement->business_name }}</strong>
        @if ($agreement->business_address)
            <br><strong>{{ $agreement->business_address }}</strong>
        @endif
    </div>

    <div class="subject">
        Perihal:
        <span>{{ $kind === 2 ? 'Surat Kedua ' : '' }}Pengembalian Perjanjian Sewa Menyewa (&ldquo;PSM&rdquo;)</span>
    </div>

    <p>Dengan hormat,</p>

    <p>Terima kasih atas kepercayaan Bapak/Ibu telah memilih <strong>{{ $building }}</strong> sebagai tempat kegiatan usaha.</p>

    <p>
        @if ($kind === 2)
            Menindaklanjuti surat kami <strong>No. {{ $surat1->letter_no }} tanggal {{ $fmt($surat1->sent_at) }}</strong>
            sehubungan dengan
        @else
            Sehubungan dengan
        @endif
        sewa menyewa unit toko <strong>{!! $tradeQuoted !!}</strong> di {{ $building }}@if ($unitText), {{ $unitText }}@endif,
        dan untuk menghindari penandatanganan PSM/Addendum tertunda lebih lama serta mengingat operasional Unit Toko
        <strong>{!! $tradeQuoted !!}</strong> terus berjalan, maka mohon bantuan dan kerjasama Bapak/Ibu agar
        masing-masing 2 (dua) set :
    </p>

    <div class="docs">
        @foreach ($docs as $doc)
            {{ $doc['label'] }} Nomor {{ $doc['no'] }}@if ($doc['date']) tanggal {{ $fmt($doc['date']) }}@endif<br>
        @endforeach
    </div>

    <p>
        yang telah kami kirimkan kepada Bapak/Ibu, untuk segera ditandatangani dan dikembalikan kepada kami dalam
        jangka waktu {{ $periodText }} terhitung sejak tanggal surat ini (paling lambat {{ $fmt($deadline) }}).
    </p>

    <p>
        Apabila lewat jangka waktu tersebut 2 (dua) set Perjanjian Sewa Menyewa/Addendum tersebut belum ditandatangani
        dan dikembalikan kepada pihak kami, maka dengan lewatnya waktu {{ $periodText }} tersebut kami anggap
        Bapak/Ibu telah menandatangani dan menyetujui Perjanjian Sewa Menyewa/Addendum dan dengan demikian seluruh
        ketentuan dalam Perjanjian Sewa Menyewa/Addendum secara otomatis berlaku sah dan mengikat para pihak.
    </p>

    <p>Mohon masing-masing 2 (dua) set PSM/Addendum tersebut dikirimkan kepada kami di alamat sebagai berikut:</p>

    <div class="address-block">
        @forelse ($profile['return_address'] ?? [] as $line)
            {{ $line['text'] }}<br>
        @empty
            {{ $companyName }}<br>
            Legal Department<br>
        @endforelse
    </div>

    <p>Mohon abaikan surat ini apabila dokumen tersebut di atas telah dikembalikan.</p>

    <p>Demikian kami sampaikan, terima kasih atas perhatian dan kerjasamanya.</p>

    <div class="signature">
        Hormat kami,<br>
        <strong>{{ $companyName }}</strong>
        <div class="space"></div>
        <strong style="text-decoration:underline;">Approved by System</strong>
    </div>

    @if ($footerLines)
        <div class="footer">
            <strong>{{ mb_strtoupper($companyName) }}</strong><br>
            @foreach ($footerLines as $line)
                {{ $line }}<br>
            @endforeach
        </div>
    @endif

</body>

</html>
