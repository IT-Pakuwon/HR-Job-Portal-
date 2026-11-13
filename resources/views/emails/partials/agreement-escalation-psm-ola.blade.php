{{-- Body of the escalation (Tindak Lanjut) email to Marketing/Leasing for a PSM (Mall) or OLA (Office). Needs $docLabel, $profile, $docs, $unitText. --}}
@php
    $fmtE = fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
    $buildingE = $profile['building'] ?: (\App\Services\AgreementLetterService::isOffice($agreement) ? 'gedung kami' : 'mal kami');
    $unitE = trim((string) $unitText);
    $tradeE = trim((string) $agreement->trade_name);
    $docE = $docs[0] ?? ['no' => '-', 'date' => null];
    $pE = 'margin:0 0 14px;font-size:13.5px;line-height:1.7;color:#334155;';
@endphp

<p style="{{ $pE }}"><strong>Subject : Tindak Lanjut Permohonan Pengembalian {{ $docLabel }}</strong></p>

<p style="{{ $pE }}">Kepada<br><strong>Departemen Marketing/Leasing</strong></p>

<p style="{{ $pE }}">Dengan hormat,</p>

<p style="{{ $pE }}">
    Menindaklanjuti surat 1 dan 2 perihal permohonan pengembalian {{ $docLabel }} yang telah kami kirimkan kepada
    <strong>{{ $agreement->business_name }}</strong> (terlampir), sehubungan dengan sewa menyewa di
    {{ $buildingE }}@if ($unitE !== '') {{ $unitE }}@endif,
    kami mohon bantuan dan kerjasama Bapak/Ibu untuk turut melakukan follow up dan mengingatkan
    <strong>{{ $agreement->business_name }}</strong> untuk menandatangani dan mengembalikan 2 (dua) set {{ $docLabel }} sebagai berikut:
</p>

<table cellpadding="0" cellspacing="0" border="0" style="margin:0 0 14px 16px;font-size:13.5px;line-height:1.7;color:#334155;font-weight:bold;">
    <tr><td style="width:120px;">{{ $docLabel }}</td><td>Nomor {{ $docE['no'] }}@if ($docE['date']) tanggal {{ $fmtE($docE['date']) }}@endif</td></tr>
    <tr><td>Nama Penyewa</td><td>{{ $agreement->business_name }}</td></tr>
    <tr><td>Nama Toko</td><td>{{ $tradeE !== '' ? $tradeE : $agreement->business_name }}</td></tr>
</table>

<p style="{{ $pE }}">Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

<p style="margin:24px 0 0;padding-top:10px;border-top:1px solid #cbd5e1;font-size:11.5px;line-height:1.6;color:#64748b;">
    <em>*Disclaimer:<br>
    Surat ini diterbitkan secara otomatis oleh Legal Lease Module System. Mohon abaikan surat ini apabila Penyewa telah menandatangani dan mengembalikan {{ $docLabel }} sebelum surat ini diterima.</em>
</p>
