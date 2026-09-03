@php
    $statusMeta = [
        'A' => ['label' => 'Approved',       'pending' => false],
        'R' => ['label' => 'Rejected',       'pending' => false],
        'D' => ['label' => 'Revision Req.',  'pending' => false],
    ];
@endphp
<div class="section-label">Approval</div>
<table class="sign-table">
    <thead>
        <tr>
            <th>Prepared By</th>
            @foreach($approvals as $i => $ap)
                <th>Approved By {{ count($approvals) > 1 ? '('.($i + 1).')' : '' }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="sign-name">{{ $createdUser ?: '-' }}</div>
                <div class="sign-meta">Created &middot; {{ optional($createdDate)->format('d M Y H:i') }}</div>
            </td>
            @foreach($approvals as $ap)
                @php $meta = $statusMeta[$ap->status] ?? ['label' => 'Pending', 'pending' => true]; @endphp
                <td>
                    <div class="sign-name">{{ $ap->aprv_name }}</div>
                    <div class="sign-meta {{ $meta['pending'] ? 'sign-meta-pending' : '' }}">{{ $meta['label'] }}{{ $ap->aprv_dateafter ? ' · '.\Illuminate\Support\Carbon::parse($ap->aprv_dateafter)->format('d M Y H:i') : '' }}</div>
                </td>
            @endforeach
        </tr>
    </tbody>
</table>
