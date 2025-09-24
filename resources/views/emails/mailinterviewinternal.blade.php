<!doctype html>
<html lang="en">
  <body style="margin:0;padding:16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">
    <p>Dear <strong>{{ $hod }}</strong>,</p>
    <p>
      We kindly invite you to attend the following <strong>Candidate Interview</strong> session:
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:720px;border:1px solid #e5e7eb;border-collapse:collapse;">
      <tr>
        <td style="padding:8px;background:#f9fafb;width:140px;">Candidate Name</td>
        <td style="padding:8px;">: <strong>{{ $full_name }}</strong></td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Position</td>
        <td style="padding:8px;">: {{ $job_title }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Date</td>
        <td style="padding:8px;">: {{ $interview_date }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Time</td>
        <td style="padding:8px;">: {{ $starttime }} – {{ $endtime }} WIB (Western Indonesia Time)</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Location</td>
        <td style="padding:8px;">: {{ $location }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Address</td>
        <td style="padding:8px;">: {{ $location_address }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">View Candidate</td>
        <td style="padding:8px;">: <a href="{{ $url }}">{{ $url }}</a></td>
      </tr>
    </table>

    <p>Warm regards,</p>
    <strong>Talent Acquisition Pakuwon Group Jakarta</strong>
  </body>
</html>
