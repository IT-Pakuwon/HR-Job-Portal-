@extends('emails.layouts.master')

@section('title', 'Surat Pengingat Pertama')

@section('icon', '📄')

@section('header', 'Surat Pengingat Pertama')

@section('subtitle')
Pengembalian dokumen PSM/Addendum yang telah ditandatangani.
@endsection

@section('content')

<p style="margin:0 0 16px;font-size:13.5px;line-height:1.7;color:#334155;">
    Yth. Bapak/Ibu <strong>{{ $agreement->pic_penyewa ?: $agreement->business_name }}</strong>,
</p>

<p style="margin:0 0 16px;font-size:13.5px;line-height:1.7;color:#334155;">
    Bersama email ini kami sampaikan <strong>Surat Pengingat Pertama</strong> (terlampir) sehubungan dengan
    dokumen PSM/Addendum No. <strong>{{ $agreement->no_psm_or_addendum ?: '-' }}</strong> untuk
    <strong>{{ $agreement->business_name }}</strong>, yang hardcopy-nya telah kami kirimkan pada tanggal
    <strong>{{ $agreement->psm_or_addendum_delivery_date ? \Carbon\Carbon::parse($agreement->psm_or_addendum_delivery_date)->format('d M Y') : '-' }}</strong> namun
    hingga saat ini belum kami terima kembali dalam keadaan telah ditandatangani.
</p>

<p style="margin:0 0 24px;font-size:13.5px;line-height:1.7;color:#334155;">
    Mohon periksa surat resmi dan tanda terima pengiriman yang terlampir pada email ini untuk detail lebih
    lanjut. Kami menantikan pengembalian dokumen dimaksud sesuai batas waktu yang tercantum pada surat terlampir.
</p>

<p style="margin:24px 0 0;font-size:12px;line-height:1.7;color:#64748b;">
    Email ini juga diteruskan kepada PIC Legal, PIC Leasing, dan pihak yang mengajukan dokumen ini sebagai
    tembusan. Apabila ada pertanyaan, silakan hubungi Divisi Legal kami.
</p>

@endsection
