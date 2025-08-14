<!doctype html>
<html lang="id">
  <body style="margin:0;padding:16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">
    <p>Yth. <strong>{{ $hod }}</strong>,</p>
    <p>
      Mohon kehadiran Bapak/Ibu pada sesi <strong>Interview Kandidat</strong> berikut:
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:520px;border:1px solid #e5e7eb;border-collapse:collapse;">
      <tr>
        <td style="padding:8px;background:#f9fafb;width:140px;">Nama Kandidat</td>
        <td style="padding:8px;">: <strong>{{$full_name}}</strong></td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Posisi</td>
        <td style="padding:8px;">: {{$job_title}}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Hari/Tanggal</td>
        <td style="padding:8px;">: {{$interview_date}}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Waktu</td>
        <td style="padding:8px;">: {{$starttime}} - {{$endtime}} WIB </td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Tempat/Ruangan</td>
        <td style="padding:8px;">: {{$location}} </td>
      </tr>    
      <tr>
        <td style="padding:8px;background:#f9fafb;">Cek Kandidat</td>
        <td style="padding:8px;">:  <a href={{ $url }}>{{ $url }}</a></td>
      </tr>
    </table>
  

    <p>Salam hangat,</p>

    <hr style="border:none;border-top:1px solid #e5e7eb;margin:12px 0;">
    <p style="font-size:12px;color:#6b7280;">
        Tim Pakuwon Career
    </p>
  </body>
</html>
