<!doctype html><html lang="id"><body style="font-family:Arial,sans-serif;color:#334155"><h2>{{ $title }}</h2><p>Yth. {{ $details['vendor'] }},</p>
@if($approved)<p>Registrasi Anda sebagai rekanan vendor Pakuwon Group telah disetujui. Silakan masuk untuk melengkapi data perusahaan dan dokumen.</p><p>Username: {{ $details['username'] }}</p><p>Gunakan password yang dibuat saat registrasi.</p>
@else<p>Registrasi Anda sebagai rekanan vendor Pakuwon Group belum memenuhi persyaratan yang berlaku.</p>@endif
@if(!empty($details['notes']))<p style="white-space:pre-line">Catatan: {{ $details['notes'] }}</p>@endif
<p><a href="{{ $portalUrl }}">Buka Vendor Portal</a></p><p>Terima kasih,<br>Pakuwon Group</p></body></html>
