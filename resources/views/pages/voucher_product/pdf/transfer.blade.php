<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $transfer->transfer_id }}</title>
@include('pages.voucher_product.pdf._styles')
</head>
<body>

    <table class="doc-header">
        <tr>
            <td class="col-company">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-sub">{{ $transfer->department }}</div>
            </td>
            <td class="col-docbox">
                <div class="doc-ref">{{ $transfer->transfer_id }}</div>
                <div class="doc-date">{{ optional($transfer->transfer_date)->format('d F Y') ?? '-' }}</div>
            </td>
        </tr>
    </table>
    <hr class="header-rule">

    <div class="title-band"><h1>{{ $transfer->transfertype === 'ReturnTf' ? 'Return Transfer' : 'Transfer' }} Voucher / Product</h1></div>

    <table class="info-box">
        <tr>
            <td class="label">V/P Type</td><td class="value">{{ strtoupper($transfer->vp_type) === 'V' ? 'Voucher' : 'Product' }}</td>
            <td class="label">Type</td><td class="value">{{ $transfer->transfertype === 'ReturnTf' ? 'Return Transfer' : 'Transfer' }}</td>
        </tr>
        @if($transfer->transfertype === 'ReturnTf')
        <tr>
            <td class="label">Reference Transfer</td><td class="value" colspan="3">{{ $transfer->ref_transfer_id ?: '-' }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Remark</td><td class="value" colspan="3">{{ $transfer->transfer_remark ?: '-' }}</td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:10%">Product ID</th>
                <th>Product Name</th>
                <th style="width:10%">Expired Date</th>
                <th style="width:14%">From WHS</th>
                <th style="width:14%">To WHS</th>
                <th style="width:8%">Qty</th>
                <th style="width:7%">UOM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $dt)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $dt->product_id }}</td>
                <td>{{ $dt->product_name }}</td>
                <td class="center">{{ $dt->expired_date && $dt->expired_date->format('Y-m-d') !== '1900-01-01' ? $dt->expired_date->format('d M Y') : 'No Expired' }}</td>
                <td class="center">{{ $dt->from_whs_id }}</td>
                <td class="center">{{ $dt->to_whs_id }}</td>
                <td class="num">{{ number_format($dt->qty_transfer ?? 0, 0, ',', '.') }}</td>
                <td class="center">{{ $dt->product_uom }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @include('pages.voucher_product.pdf._approvals', [
        'approvals'   => $approvals,
        'createdUser' => $transfer->created_user,
        'createdDate' => $transfer->created_at,
    ])

    <div class="pdf-footer">
        <div class="footer-rule"></div>
        <table class="footer-table">
            <tr>
                <td>This is a system-generated document and does not require a physical signature.</td>
                <td class="footer-right">Printed {{ now()->format('d F Y H:i') }} &middot; <span class="footer-page"></span></td>
            </tr>
        </table>
    </div>

</body>
</html>
