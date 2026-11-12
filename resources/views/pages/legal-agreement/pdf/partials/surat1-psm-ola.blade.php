{{-- Surat 1 / Surat 2 for a PSM (Mall), OLA (Office) or Addendum: "Permohonan Pengembalian ...". Included by surat-letter
     with $kind (1 | 2) and $docLabel ('PSM' | 'OLA' | 'Addendum'); styled inline so it does not depend on the host template's CSS. --}}
@php
    $fmtP = fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
    $companyNameP = $company->cpny_name ?? $agreement->cpny_id;
    $buildingP = $profile['building'] ?: (\App\Services\AgreementLetterService::isOffice($agreement) ? 'gedung kami' : 'mal kami');
    $unitP = trim((string) $unitText);
    $tradeP = trim((string) $agreement->trade_name);
    $docP = $docs[0] ?? ['no' => '-', 'date' => null];
@endphp

<table style="width:100%;">
    <tr>
        <td>No. {{ $letter->letter_no }}</td>
        <td style="text-align:right;">{{ $company->city ?? 'Jakarta' }}, {{ $fmtP($letter->sent_at) }}</td>
    </tr>
</table>

@if ($kind === 2)
    <p style="margin-top:8px;">
        Lampiran:<br>
        - 1 (satu) berkas tanda terima pengiriman {{ $docLabel }}<br>
        - 1 (satu) berkas Surat 1
    </p>
@else
    <p style="margin-top:8px;">Lampiran: 1 (satu) berkas tanda terima pengiriman {{ $docLabel }}</p>
@endif

<div style="margin:12px 0;">
    Kepada Yth.<br>
    <strong>{{ $agreement->business_name }}</strong>
    @if ($agreement->business_address)
        <br>{{ $agreement->business_address }}
    @endif
    <br><br>Up. Direksi
</div>

<div style="margin:12px 0;">
    Perihal: <strong><u>Surat {{ $kind }}- Permohonan Pengembalian @if ($docLabel === 'Addendum'){{ $docLabel }}@else Perjanjian Sewa Menyewa (&ldquo;{{ $docLabel }}&rdquo;)@endif</u></strong>
</div>

<p>Dengan hormat,</p>

<p>Sebelumnya kami mengucapkan terima kasih atas dukungan Bapak/Ibu dengan menjadi salah satu tenant kami di <strong>{{ $buildingP }}</strong>.</p>

<p>
    @if ($kind === 2)Menindaklanjuti surat kami perihal permohonan pengembalian {{ $docLabel }} (terlampir), sehubungan dengan @else Sehubungan dengan @endif sewa menyewa di {{ $unitP !== '' ? $unitP : 'unit Bapak/Ibu' }}
    kami mohon bantuan dan kerjasama Bapak/Ibu untuk menandatangani dan mengembalikan 2 (dua) set {{ $docLabel }} yang telah kami
    kirimkan kepada pihak Bapak/Ibu (sebagaimana tanda terima terlampir), yaitu sebagai berikut:
</p>

<table style="margin:0 0 9px 22px; font-weight:bold;">
    <tr>
        <td style="width:110px;">{{ $docLabel }}</td>
        <td>Nomor {{ $docP['no'] }}@if ($docP['date']) tanggal {{ $fmtP($docP['date']) }}@endif</td>
    </tr>
    <tr>
        <td>Nama Penyewa</td>
        <td>{{ $agreement->business_name }}</td>
    </tr>
    <tr>
        <td>Nama Toko</td>
        <td>{{ $tradeP !== '' ? $tradeP : $agreement->business_name }}</td>
    </tr>
</table>

<p>Mohon masing-masing 2 (dua) set {{ $docLabel }} tersebut dikirimkan kepada kami ke alamat sebagai berikut:</p>

<div style="margin:0 0 9px 22px;">
    @forelse ($profile['return_address'] ?? [] as $line)
        {{ $line['text'] }}<br>
    @empty
        {{ $companyNameP }}<br>
        Legal Department<br>
    @endforelse
</div>

<p>
    Apabila setelah lewat jangka waktu tersebut kami belum menerima 2 (dua) set {{ $docLabel }} yang telah ditandatangani, maka
    dengan lewatnya waktu 2 (dua) minggu tersebut kami anggap Bapak/Ibu telah menandatangani dan menyetujui {{ $docLabel }} tersebut
    dan dengan demikian seluruh ketentuan dalam {{ $docLabel }} secara otomatis berlaku sah dan mengikat para pihak.
</p>

<p>Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

<div style="margin-top:14px;">
    Hormat kami,<br>
    <strong>{{ $companyNameP }}</strong>
    <div style="height:46px;"></div>
</div>

<div style="margin-top:14px; border-top:1px solid #333; padding-top:6px; font-size:9.5px;">
    <em>*Disclaimer:<br>
    Surat ini diterbitkan secara otomatis oleh Legal Lease Module System. Mohon abaikan surat ini apabila Bapak/Ibu telah menandatangani dan mengembalikan {{ $docLabel }} sebelum surat ini diterima.</em>
</div>
