<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $receive->receive_id }}</title>
@include('pages.voucher_product.pdf._styles')
</head>
<body>

    <table class="doc-header">
        <tr>
            <td class="col-company">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-sub">{{ $receive->department }}</div>
            </td>
            <td class="col-docbox">
                <div class="doc-ref">{{ $receive->receive_id }}</div>
                <div class="doc-date">{{ optional($receive->receive_date)->format('d F Y') ?? '-' }}</div>
            </td>
        </tr>
    </table>
    <hr class="header-rule">

    <div class="title-band"><h1>Receive Voucher / Product</h1></div>

    <table class="info-box">
        <tr>
            <td class="label">V/P Type</td><td class="value">{{ strtoupper($receive->vp_type) === 'V' ? 'Voucher' : 'Product' }}</td>
            <td class="label">Tenant</td><td class="value">{{ $receive->receive_tenant ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Source of Receive</td><td class="value">{{ $receive->receive_type ?: '-' }}</td>
            <td class="label">Dept of Receive</td><td class="value">{{ $receive->source_receive_dept ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Remark</td><td class="value" colspan="3">{{ $receive->receive_remark ?: '-' }}</td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:11%">Product ID</th>
                <th>Product Name</th>
                <th style="width:11%">Expired Date</th>
                <th style="width:7%">Qty</th>
                <th style="width:6%">UOM</th>
                <th style="width:11%">Price</th>
                <th style="width:12%">Total Price</th>
                <th style="width:14%">WHS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $dt)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $dt->product_id }}</td>
                <td>{{ $dt->product_name }}</td>
                <td class="center">{{ $dt->expired_date && $dt->expired_date->format('Y-m-d') !== '1900-01-01' ? $dt->expired_date->format('d M Y') : 'No Expired' }}</td>
                <td class="num">{{ number_format($dt->qty_receive ?? 0, 0, ',', '.') }}</td>
                <td class="center">{{ $dt->product_uom }}</td>
                <td class="num">{{ number_format($dt->product_price ?? 0, 0, ',', '.') }}</td>
                <td class="num">{{ number_format($dt->total_product_price ?? 0, 0, ',', '.') }}</td>
                <td class="center">{{ $dt->whs_id }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="label-cell">Grand Total</td>
                <td class="num">{{ number_format($details->sum('total_product_price'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @include('pages.voucher_product.pdf._approvals', [
        'approvals'   => $approvals,
        'createdUser' => $receive->created_user,
        'createdDate' => $receive->created_at,
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
