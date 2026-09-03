<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $settlement->settlement_id }}</title>
@include('pages.voucher_product.pdf._styles')
</head>
<body>

    <table class="doc-header">
        <tr>
            <td class="col-company">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-sub">{{ $settlement->department }}</div>
            </td>
            <td class="col-docbox">
                <div class="doc-ref">{{ $settlement->settlement_id }}</div>
                <div class="doc-date">{{ optional($settlement->settlement_date)->format('d F Y') ?? '-' }}</div>
            </td>
        </tr>
    </table>
    <hr class="header-rule">

    <div class="title-band"><h1>Settlement Voucher / Product</h1></div>

    <table class="info-box">
        <tr>
            <td class="label">V/P Type</td><td class="value">{{ strtoupper($settlement->vp_type) === 'V' ? 'Voucher' : 'Product' }}</td>
            <td class="label">Reference Usage</td><td class="value">{{ $settlement->usage_id ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Remark</td><td class="value" colspan="3">{{ $settlement->settlement_remark ?: '-' }}</td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:6%">No</th>
                <th style="width:12%">Product ID</th>
                <th>Product Name</th>
                <th style="width:12%">Expired Date</th>
                <th style="width:10%">Qty Usage</th>
                <th style="width:10%">Qty Settlement</th>
                <th style="width:10%">Qty Remain</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $dt)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $dt->product_id }}</td>
                <td>{{ $dt->product_name }}</td>
                <td class="center">{{ $dt->expired_date && $dt->expired_date->format('Y-m-d') !== '1900-01-01' ? $dt->expired_date->format('d M Y') : 'No Expired' }}</td>
                <td class="num">{{ number_format($dt->qty_usage ?? 0, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($dt->qty_settlement ?? 0, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($dt->qty_remain ?? 0, 0, ',', '.') }}</td>
                <td>{{ $dt->settlement_remark ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @include('pages.voucher_product.pdf._approvals', [
        'approvals'   => $approvals,
        'createdUser' => $settlement->created_user,
        'createdDate' => $settlement->created_at,
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
