<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $d['data']['sianbr'] }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
    h1 { font-size: 16px; margin: 0 0 2px; }
    .sub { color: #64748b; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th, td { border: 1px solid #e2e8f0; padding: 5px 7px; text-align: left; vertical-align: top; }
    th { background: #f8fafc; width: 22%; color: #475569; font-weight: bold; }
    h2 { font-size: 12px; margin: 14px 0 5px; text-transform: uppercase; letter-spacing: .5px; color: #334155; }
    .badge { display: inline-block; padding: 2px 8px; background: #fef9c3; color: #a16207; font-weight: bold; }
</style>
</head>
<body>
@php
    $s = $d['data'];
    $time = fn ($t) => $t ? substr($t, 0, 5) : '-';
@endphp
<h1>SIA — {{ $s['sianbr'] }}</h1>
<div class="sub">Surat Izin Acara &middot; <span class="badge">{{ $s['status_label'] }}</span></div>

<table>
    <tr><th>SIA Date</th><td>{{ $s['siadate'] }}</td><th>Site</th><td>{{ $s['sitename'] }}</td></tr>
    <tr><th>Company</th><td>{{ $s['companyname'] }}</td><th>Location</th><td>{{ $s['locationname'] }}</td></tr>
    <tr><th>Tenant</th><td>{{ $s['storename'] }}</td><th>Floor / Unit</th><td>{{ trim(($s['floor'] ?? '') . ' / ' . ($s['unit'] ?? ''), ' /') }}</td></tr>
    <tr><th>Person in Charge</th><td>{{ $s['requestpenanggungjawab'] }}</td><th>Phone / Email</th><td>{{ $s['requestphone'] }} / {{ $s['requestemail'] }}</td></tr>
    <tr><th>Start Date</th><td>{{ $s['startdate'] }}</td><th>End Date</th><td>{{ $s['enddate'] }}</td></tr>
    <tr><th>Start Time</th><td>{{ $time($s['starttime'] ?? null) }}</td><th>End Time</th><td>{{ $time($s['endtime'] ?? null) }}</td></tr>
    <tr><th>Extended</th><td colspan="3">{{ ($s['totalextend'] ?? 0) > 0 ? $s['totalextend'].'x ('.($s['extendstartdate'] ?: '-').' – '.($s['extendenddate'] ?: '-').')' : '-' }}</td></tr>
    <tr><th>Event Type</th><td>{{ $s['eventtype'] }}</td><th>Event Location</th><td>{{ $s['eventlocation'] }}</td></tr>
    <tr><th>Guests / Media</th><td colspan="3">{{ $s['eventguests'] }}</td></tr>
    <tr><th>Event Coordinator</th><td>{{ $s['coordinatorname'] }}</td><th>PIC Security</th><td>{{ $s['picsecurity'] }}</td></tr>
    <tr><th>PIC Engineering</th><td>{{ $s['picengineering'] }}</td><th>Event Organizer</th><td>{{ $s['organizername'] ?: '-' }}{{ ($s['organizerpic'] ?? null) ? ' / ' . $s['organizerpic'] : '' }}</td></tr>
    <tr><th>Notes</th><td colspan="3">{{ $s['catatan'] ?: '-' }}</td></tr>
    <tr><th>Created By</th><td colspan="3">{{ $s['created_user'] }}</td></tr>
</table>

@if (!empty($d['reason']))
    <h2>Reason</h2>
    <table><tr><td>{{ $d['reason'] }}</td></tr></table>
@endif

<h2>Approval Workflow</h2>
<table>
    <tr><th style="width:8%">#</th><th>Role</th><th>User</th><th>Date</th></tr>
    @forelse ($d['approvals'] as $i => $a)
        <tr><td>{{ $i + 1 }}</td><td>{{ $a['accesstype'] }}</td><td>{{ $a['username'] }}</td><td>{{ $a['aprvdateafter'] ?: 'Waiting' }}</td></tr>
    @empty
        <tr><td colspan="4">No approval records.</td></tr>
    @endforelse
</table>

@if (count($d['attachments']))
    <h2>Attachments</h2>
    <table>
        @foreach ($d['attachments'] as $f)
            <tr><td>{{ $f['name'] }}.{{ $f['fileextension'] }}</td><td style="width:25%">{{ $f['created_datetime'] }}</td></tr>
        @endforeach
    </table>
@endif
</body>
</html>
