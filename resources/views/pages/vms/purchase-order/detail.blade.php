<div class="vms-order-detail" data-detail-type="{{ $type }}">
    <div class="vms-order-detail-hero">
        <span class="vms-order-detail-icon" aria-hidden="true"><i class="fas {{ ['po'=>'fa-shopping-cart','spk'=>'fa-file-alt','kontrak'=>'fa-file-contract'][$type] }}"></i></span>
        <div><span class="vms-order-detail-eyebrow">{{ ['po'=>'PURCHASE ORDER','spk'=>'SURAT PERINTAH KERJA','kontrak'=>'KONTRAK'][$type] }}</span><h3>{{ $type==='kontrak'?$order->kontrakid:$order->ponbr }}</h3><p>{{ $order->vendorname }} &middot; {{ $order->cpny_id }}</p></div>
    </div>
    <section class="vms-order-detail-section" aria-label="Informasi pesanan">
        <h4>Informasi pesanan</h4>
        <dl class="vms-order-detail-grid">
            <div><dt>Vendor ID</dt><dd>{{ $order->vendor_id ?: '-' }}</dd></div>
            <div><dt>Purchaser</dt><dd>{{ $order->purchaser_name ?: ($order->purchaser ?: '-') }}</dd></div>
            <div><dt>Tanggal</dt><dd>{{ ($type==='kontrak'?$order->kontrakdate:$order->podate) ?: '-' }}</dd></div>
            <div><dt>Status</dt><dd><span class="vms-order-detail-status">{{ $order->status ?: '-' }}</span></dd></div>
        </dl>
    </section>
    @if($type==='kontrak')
    <section class="vms-order-detail-section" aria-label="Informasi kontrak">
        <h4>Informasi kontrak</h4>
        <dl class="vms-order-detail-grid">
            <div><dt>Nomor SK</dt><dd>{{ $order->nosk ?: '-' }}</dd></div>
            <div><dt>Nomor PK Legal</dt><dd>{{ $order->nopklegal ?: '-' }}</dd></div>
            <div><dt>Sub Department</dt><dd>{{ $order->department_id ?: '-' }}</dd></div>
            <div><dt>Periode</dt><dd>{{ $order->startdate ?: '-' }} s/d {{ $order->enddate ?: '-' }}</dd></div>
        </dl>
        @if($order->kontaknote)<div class="vms-order-detail-note"><strong>Catatan</strong><p class="vms-order-notes">{{ $order->kontaknote }}</p></div>@endif
    </section>
    @else
    <section class="vms-order-detail-section" aria-label="Ringkasan nilai">
        <h4>Ringkasan nilai</h4>
        <dl class="vms-order-detail-amounts">
            <div><dt>Subtotal</dt><dd>Rp {{ number_format((float)$order->totalamt,2,',','.') }}</dd></div>
            <div><dt>Pajak</dt><dd>Rp {{ number_format((float)$order->taxamt,2,',','.') }}</dd></div>
            <div><dt>Total pesanan</dt><dd>Rp {{ number_format((float)($order->grandtotalamt??((float)$order->totalamt+(float)$order->taxamt)),2,',','.') }}</dd></div>
        </dl>
    </section>
    <section class="vms-order-detail-section vms-order-detail-lines" aria-label="Detail barang dan jasa">
        <h4>Detail barang dan jasa</h4>
        <div class="vms-order-table"><table><thead><tr><th>Line</th><th>Inventory ID</th><th>Deskripsi</th><th>Qty</th><th>UOM</th><th>Unit Cost (Rp)</th><th>Tax (Rp)</th><th>Total (Rp)</th></tr></thead><tbody>@forelse($lines as $line)<tr><td>{{ $line->linenbr }}</td><td>{{ $line->inventoryid }}</td><td>{{ $line->inventory_descr }}</td><td>{{ number_format((float)$line->qty,2,',','.') }}</td><td>{{ $line->uom }}</td><td>{{ number_format((float)$line->unitcost,2,',','.') }}</td><td>{{ number_format((float)$line->taxamt,2,',','.') }}</td><td>{{ number_format((float)$line->totalcost,2,',','.') }}</td></tr>@empty<tr><td colspan="8">Belum ada detail pesanan.</td></tr>@endforelse</tbody></table></div>
    </section>
    @endif
</div>
