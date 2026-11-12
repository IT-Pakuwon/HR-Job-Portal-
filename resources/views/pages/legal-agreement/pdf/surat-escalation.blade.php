<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tindak Lanjut - {{ $agreement->agreement_id }}</title>
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
    </style>
</head>

<body>

    {{-- Escalation (Tindak Lanjut) for a PSM (Mall) or OLA (Office), addressed to Marketing/Leasing. $docLabel is 'PSM' | 'OLA' | 'Addendum'. --}}
    @php
        $fmt = fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
        $building = $profile['building'] ?: (\App\Services\AgreementLetterService::isOffice($agreement) ? 'gedung kami' : 'mal kami');
        $unit = trim((string) $unitText);
        $trade = trim((string) $agreement->trade_name);
        $doc = $docs[0] ?? ['no' => '-', 'date' => null];
    @endphp

    <div>{{ $company->city ?? 'Jakarta' }}, {{ $fmt($sentDate) }}</div>

    <p style="margin-top:8px;">
        Lampiran:<br>
        - 1 (satu) berkas tanda terima pengiriman {{ $docLabel }}<br>
        - 1 (satu) berkas Surat 1 &amp; 2
    </p>

    <div style="margin:12px 0;">
        Kepada<br>
        <strong>Departemen Marketing/Leasing</strong>
    </div>

    <div style="margin:12px 0;">
        Perihal: <strong><u>Tindak Lanjut Permohonan Pengembalian {{ $docLabel }}</u></strong>
    </div>

    <p>Dengan hormat,</p>

    <p>
        Menindaklanjuti surat 1 dan 2 perihal permohonan pengembalian {{ $docLabel }} yang telah kami kirimkan kepada
        <strong>{{ $agreement->business_name }}</strong> (terlampir), sehubungan dengan sewa menyewa di
        {{ $building }}@if ($unit !== '') {{ $unit }}@endif,
        kami mohon bantuan dan kerjasama Bapak/Ibu untuk turut melakukan follow up dan mengingatkan
        <strong>{{ $agreement->business_name }}</strong> untuk menandatangani dan mengembalikan 2 (dua) set {{ $docLabel }} sebagai berikut:
    </p>

    <table style="margin:0 0 9px 22px; font-weight:bold;">
        <tr>
            <td style="width:110px;">{{ $docLabel }}</td>
            <td>Nomor {{ $doc['no'] }}@if ($doc['date']) tanggal {{ $fmt($doc['date']) }}@endif</td>
        </tr>
        <tr>
            <td>Nama Penyewa</td>
            <td>{{ $agreement->business_name }}</td>
        </tr>
        <tr>
            <td>Nama Toko</td>
            <td>{{ $trade !== '' ? $trade : $agreement->business_name }}</td>
        </tr>
    </table>

    <p>Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

    <div style="margin-top:24px; border-top:1px solid #333; padding-top:6px; font-size:9.5px;">
        <em>*Disclaimer:<br>
        Surat ini diterbitkan secara otomatis oleh Legal Lease Module System. Mohon abaikan surat ini apabila Penyewa telah menandatangani dan mengembalikan {{ $docLabel }} sebelum surat ini diterima.</em>
    </div>

</body>

</html>
