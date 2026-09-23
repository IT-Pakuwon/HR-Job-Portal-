<!doctype html>
<html lang="en">
  <body style="margin:0;padding:16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#111;">
    <p>Dear <strong>{{ $name }}</strong>,</p>
    <p>
      The candidate for the position you requested via PRF <strong>{{ $docid }}</strong> has been approved to <strong>Join</strong>:
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:720px;border:1px solid #e5e7eb;border-collapse:collapse;">
      <tr>
        <td style="padding:8px;background:#f9fafb;width:160px;">Candidate Name</td>
        <td style="padding:8px;">: <strong>{{ $candidate_name }}</strong></td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Schedule Start</td>
        <td style="padding:8px;">: {{ $schedule_start }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Company</td>
        <td style="padding:8px;">: {{ $company }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Division</td>
        <td style="padding:8px;">: {{ $division }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">Job</td>
        <td style="padding:8px;">: {{ $job_title }}</td>
      </tr>
      <tr>
        <td style="padding:8px;background:#f9fafb;">View PRF</td>
        <td style="padding:8px;">: <a href="{{ $url }}">{{ $docid }}</a></td>
      </tr>
    </table>

    <p>Warm regards,</p>
    <strong>Talent Acquisition Pakuwon Group</strong>
  </body>
</html>
