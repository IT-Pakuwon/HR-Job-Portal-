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

        table.meta td {
            vertical-align: top;
            padding: 0;
        }

        table.meta td.k {
            width: 42px;
        }

        .addressee {
            margin: 14px 0 12px;
            font-weight: bold;
        }

        .subject {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 12px 0;
        }

        ol.docs {
            margin: 0 0 12px 8px;
            padding-left: 22px;
            font-weight: bold;
        }

        ol.docs li {
            margin-bottom: 2px;
        }

        .address-block {
            margin: 0 0 12px 22px;
        }

        .signature {
            margin-top: 18px;
        }

        .signature .space {
            height: 46px;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 9px;
            color: #444;
        }
    </style>
</head>

<body>

    @php
        $fmt = fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
        $unitText = $unitText ? ' '.$unitText : '';
        $building = $profile['building'] ? 'Gedung Perkantoran '.$profile['building'] : 'gedung kami';
        $companyName = $company->cpny_name ?? $agreement->cpny_id;
    @endphp

    <div>{{ $company->city ?? 'Jakarta' }}, {{ $fmt($letter->sent_at) }}</div>

    <table class="meta" style="margin-top:12px;">
        <tr>
            <td class="k">No.</td>
            <td>: {{ $letter->letter_no }}</td>
        </tr>
        <tr>
            <td class="k">Lamp</td>
            <td>:
                @if ($kind === 2)
                    - Surat 1 No. {{ $surat1->letter_no }} tanggal {{ $fmt($surat1->sent_at) }}<br>
                    &nbsp;&nbsp;- Tanda terima
                @else
                    - Tanda terima
                @endif
            </td>
        </tr>
    </table>

    <div class="addressee">
        Kepada Yth.<br>
        {{ mb_strtoupper($agreement->business_name) }}
        @if ($agreement->business_address)
            <br>{{ $agreement->business_address }}
        @endif
        <br><br>
        Up. Direksi
    </div>

    <div class="subject">Perihal: Surat {{ $kind }} - Permohonan Pengembalian Perjanjian Sewa Menyewa(&ldquo;PSM&rdquo;)/Addendum</div>

    <p>Dengan hormat,</p>

    <p>
        Sebelumnya kami mengucapkan terima kasih atas dukungan Bapak/Ibu dengan menjadi salah satu tenant kami di
        {{ $building }}.
    </p>

    <p>
        @if ($kind === 2)
            Menindaklanjuti surat kami sebelumnya Nomor {{ $surat1->letter_no }} tanggal {{ $fmt($surat1->sent_at) }}
            perihal Permohonan Pengembalian Perjanjian Sewa Menyewa &ldquo;PSM&rdquo;/Addendum, sehubungan dengan
        @else
            Sehubungan dengan
        @endif
        sewa menyewa unit kantor {{ $agreement->business_name }} di {{ $building }}{{ $unitText }}
        kami mohon bantuan dan kerjasama Bapak/Ibu untuk menandatangani dan mengembalikan PSM/Addendum yang telah
        kami kirimkan kepada pihak Bapak/Ibu yaitu sebagai berikut:
    </p>

    <ol class="docs">
        @foreach ($docs as $doc)
            <li>{{ $doc['label'] }} No. {{ $doc['no'] }}@if ($doc['date']) tanggal {{ $fmt($doc['date']) }}@endif{{ $loop->last ? '.' : ';' }}</li>
        @endforeach
    </ol>

    <p>
        Mohon masing-masing 2 (dua) set PSM/Addendum tersebut dikirimkan kepada kami ke alamat sebagai berikut:
    </p>

    <div class="address-block">
        @forelse ($profile['return_address'] ?? [] as $line)
            @if (!empty($line['bold']))
                <strong>{{ $line['text'] }}</strong><br>
            @else
                {{ $line['text'] }}<br>
            @endif
        @empty
            <strong>{{ $companyName }}</strong><br>
            <strong>Legal Department</strong><br>
        @endforelse
    </div>

    <p>
        Apabila lewat jangka waktu {{ $periodText }} sejak tanggal surat ini (paling lambat
        {{ $fmt($deadline) }}) kami belum menerima 2 (dua) set PSM/Addendum yang telah ditandatangani, maka dengan
        lewatnya waktu {{ $periodText }} tersebut kami anggap Bapak/Ibu telah menandatangani dan menyetujui
        PSM/Addendum tersebut dan dengan demikian seluruh ketentuan dalam PSM/Addendum secara otomatis berlaku sah
        dan mengikat para pihak.
    </p>

    <p>Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

    <div class="signature">
        Hormat kami,<br>
        <strong>{{ $companyName }}</strong>
        <div class="space"></div>
        <strong style="text-decoration:underline;">Approved by System</strong>
    </div>

    @if (!empty($profile['footer']))
        <div class="footer">
            <strong>{{ mb_strtoupper($companyName) }}</strong><br>
            {{ $profile['footer'] }}
        </div>
    @endif

</body>

</html>
