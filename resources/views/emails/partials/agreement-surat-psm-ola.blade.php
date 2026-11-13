{{-- Body of the Surat 1 / Surat 2 email for a PSM (Mall) or OLA (Office). Needs $kind (1|2), $docLabel, $profile, $docs, $unitText, $company. --}}
@php
    $fmtE = fn ($value) => \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
    $buildingE = $profile['building'] ?: (\App\Services\AgreementLetterService::isOffice($agreement) ? 'gedung kami' : 'mal kami');
    $unitE = trim((string) $unitText);
    $tradeE = trim((string) $agreement->trade_name);
    $docE = $docs[0] ?? ['no' => '-', 'date' => null];
    $companyE = $company->cpny_name ?? $agreement->cpny_id;
    $pE = 'margin:0 0 14px;font-size:13.5px;line-height:1.7;color:#334155;';
@endphp

<p style="{{ $pE }}"><strong>Subject : Surat {{ $kind }}- Permohonan Pengembalian @if ($docLabel === 'Addendum'){{ $docLabel }}@else Perjanjian Sewa Menyewa (“{{ $docLabel }}”)@endif</strong></p>

<p style="{{ $pE }}">
    Kepada Yth.<br>
    <strong>{{ $agreement->business_name }}</strong>
    @if ($agreement->business_address)<br>{{ $agreement->business_address }}@endif
    <br><br>Up. Direksi
</p>

<p style="{{ $pE }}">Dengan hormat,</p>

<p style="{{ $pE }}">Sebelumnya kami mengucapkan terima kasih atas dukungan Bapak/Ibu dengan menjadi salah satu tenant kami di <strong>{{ $buildingE }}</strong>.</p>

<p style="{{ $pE }}">
    @if ($kind === 2)Menindaklanjuti surat kami perihal permohonan pengembalian {{ $docLabel }} (terlampir), sehubungan dengan @else Sehubungan dengan @endif sewa menyewa di {{ $unitE !== '' ? $unitE : 'unit Bapak/Ibu' }}
    kami mohon bantuan dan kerjasama Bapak/Ibu untuk menandatangani dan mengembalikan 2 (dua) set {{ $docLabel }} yang telah kami
    kirimkan kepada pihak Bapak/Ibu (sebagaimana tanda terima terlampir), yaitu sebagai berikut:
</p>

<table cellpadding="0" cellspacing="0" border="0" style="margin:0 0 14px 16px;font-size:13.5px;line-height:1.7;color:#334155;font-weight:bold;">
    <tr><td style="width:120px;">{{ $docLabel }}</td><td>Nomor {{ $docE['no'] }}@if ($docE['date']) tanggal {{ $fmtE($docE['date']) }}@endif</td></tr>
    <tr><td>Nama Penyewa</td><td>{{ $agreement->business_name }}</td></tr>
    <tr><td>Nama Toko</td><td>{{ $tradeE !== '' ? $tradeE : $agreement->business_name }}</td></tr>
</table>

<p style="{{ $pE }}">Mohon masing-masing 2 (dua) set {{ $docLabel }} tersebut dikirimkan kepada kami ke alamat sebagai berikut:</p>

<p style="{{ $pE }}margin-left:16px;">
    @forelse ($profile['return_address'] ?? [] as $line)
        {{ $line['text'] }}<br>
    @empty
        {{ $companyE }}<br>
        Legal Department<br>
    @endforelse
</p>

<p style="{{ $pE }}">
    Apabila setelah lewat jangka waktu tersebut kami belum menerima 2 (dua) set {{ $docLabel }} yang telah ditandatangani, maka
    dengan lewatnya waktu 2 (dua) minggu tersebut kami anggap Bapak/Ibu telah menandatangani dan menyetujui {{ $docLabel }} tersebut
    dan dengan demikian seluruh ketentuan dalam {{ $docLabel }} secara otomatis berlaku sah dan mengikat para pihak.
</p>

<p style="{{ $pE }}">Demikian kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

<p style="{{ $pE }}">Hormat kami,<br><strong>{{ $companyE }}</strong></p>

<p style="margin:24px 0 0;padding-top:10px;border-top:1px solid #cbd5e1;font-size:11.5px;line-height:1.6;color:#64748b;">
    <em>*Disclaimer:<br>
    Surat ini diterbitkan secara otomatis oleh Legal Lease Module System. Mohon abaikan surat ini apabila Bapak/Ibu telah menandatangani dan mengembalikan {{ $docLabel }} sebelum surat ini diterima.</em>
</p>
