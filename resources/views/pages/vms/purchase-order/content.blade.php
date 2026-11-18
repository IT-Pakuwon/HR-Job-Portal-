<div id="vms-vendors-page" class="vms-document-page vms-purchase-page" data-order-directory="{{ $type }}" data-order-scope="{{ $scopeKey }}" data-order-vendor-options="{{ $loadVendorOptions ? '1' : '0' }}">
<nav class="vms-vendor-tabs vms-document-tabs" aria-label="Jenis pesanan">
@foreach($types as $key=>$label)<a class="vms-order-type" data-order-card="{{ $key }}" href="{{ route('vms.purchase-orders.index',array_merge(request()->except('page'),['type'=>$key])) }}" @if($type===$key) aria-current="page" @endif><span class="vms-order-type-icon"><i class="fas {{ ['po'=>'fa-shopping-cart','spk'=>'fa-file-alt','kontrak'=>'fa-file-contract'][$key] }}" aria-hidden="true"></i></span><span><small>{{ strtoupper($key) }}</small><strong>{{ $label }}</strong></span><b>{{ number_format($counts[$key]) }}</b></a>@endforeach
</nav>
<section class="vms-document-panel">
<header><div><h2>{{ $types[$type] }}</h2><p class="vms-vendor-description">Daftar pesanan vendor dan status dokumen perusahaan.</p></div><a class="vms-vendor-link" data-order-refresh href="{{ request()->fullUrl() }}"><i class="fas fa-sync-alt" aria-hidden="true"></i> Refresh</a></header>
<p class="vms-document-feedback" style="margin:16px 24px" data-order-list-error role="alert" hidden></p><p class="vms-order-loading" role="status" hidden>Memuat pesanan...</p>
<form method="get" class="vms-vendor-filters" data-order-filters>
<input type="hidden" name="type" value="{{ $type }}">
<label>Perusahaan<select name="company"><option value="">Semua perusahaan</option>@foreach($companies as $company)<option value="{{ $company->CpnyID }}" @selected(($filters['company']??'')===$company->CpnyID)>{{ $company->CpnyID }} - {{ $company->CpnyName }}</option>@endforeach</select></label>
<label>Vendor<select name="vendor"><option value="">Semua vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->vendor_id }}" @selected(($filters['vendor']??'')===$vendor->vendor_id)>{{ $vendor->vendorname }} ({{ $vendor->vendor_id }})</option>@endforeach</select></label>
<label>Cari pesanan<input type="search" name="search" value="{{ $filters['search']??'' }}" placeholder="Nomor pesanan / nama vendor" maxlength="100"></label>
<label class="vms-order-limit">Tampilkan<select name="per_page">@foreach([10,25,50,100] as $size)<option @selected(($filters['per_page']??10)==$size)>{{ $size }}</option>@endforeach</select></label>
<a class="vms-vendor-link" href="{{ route('vms.purchase-orders.index',['type'=>$type]) }}">Reset filter</a>
<noscript><button type="submit">Terapkan</button></noscript>
</form>
<div class="vms-order-table"><table><thead><tr>
@if($type==='kontrak')<th>Nomor Kontrak</th><th>Nomor SK</th><th>Sub Department</th><th>Kontrak Date</th><th>Company / Vendor</th><th>Purchaser</th><th>Periode Start</th><th>Periode End</th><th>E-Kontrak</th>
@else<th>{{ $type==='po'?'PO':'SPK' }} Number</th><th>Date</th><th>Type</th><th>Company / Vendor</th><th>Purchaser</th><th>Total Amount (Rp)</th><th>Status Receipt</th><th>Status Payment</th><th>E-{{ strtoupper($type) }}</th>@endif
</tr></thead><tbody>
@forelse($orders as $order)<tr>
@if($type==='kontrak')<td><a class="vms-vendor-link vms-order-number" href="{{ route('vms.purchase-orders.show',[$type,$order->id]) }}" data-order-detail>{{ $order->kontrakid }}</a>@if($order->nopklegal)<small>{{ $order->nopklegal }}</small>@endif</td><td>{{ $order->nosk?:'-' }}</td><td>{{ $order->department_id?:'-' }}</td><td>{{ substr((string)$order->kontrakdate,0,10) }}</td>
@else<td><a class="vms-vendor-link vms-order-number" href="{{ route('vms.purchase-orders.show',[$type,$order->id]) }}" data-order-detail>{{ $order->ponbr }}</a></td><td>{{ substr((string)$order->podate,0,10) }}</td><td>{{ $order->material_service }}</td>@endif
<td><strong>{{ $order->vendorname }}</strong><small>{{ $order->cpny_id }} &middot; {{ $order->vendor_id }}</small></td><td>{{ $order->purchaser_name?:($order->purchaser?:'-') }}</td>
@if($type==='kontrak')<td>{{ substr((string)$order->startdate,0,10)?:'-' }}</td><td>{{ substr((string)$order->enddate,0,10)?:'-' }}</td>
@else<td class="vms-order-money">{{ number_format((float)($order->grandtotalamt??((float)$order->totalamt+(float)$order->taxamt)),2,',','.') }}</td>@php($receiptTone=(float)$order->ordered_qty<=0?'unknown':((float)$order->received_qty>=(float)$order->ordered_qty?'received':((float)$order->received_qty>0?'partial':'pending'))) <td><span class="vms-po-receipt vms-po-receipt--{{ $receiptTone }}">{{ ['unknown'=>'Belum tersedia','received'=>'Sudah Diterima','partial'=>'Diterima Sebagian','pending'=>'Belum Diterima'][$receiptTone] }}</span></td><td><span class="vms-order-muted">Belum tersedia</span></td>@endif
<td><span class="vms-po-document vms-po-document--{{ ['C'=>'approved','D'=>'revised','P'=>'waiting'][$order->document_status]??'empty' }} vms-document-badge {{ $order->document_status==='C'?'':($order->document_status==='D'?'inactive':'optional') }}">{{ ['C'=>'Sudah Approval','D'=>'File Direvisi','P'=>'Menunggu Approval'][$order->document_status]??'Belum Upload' }}</span></td>
</tr>@empty<tr><td colspan="9" class="vms-vendor-empty">Tidak ada pesanan yang sesuai dengan filter.</td></tr>@endforelse
</tbody></table></div>
<footer class="vms-order-pagination"><span>Menampilkan {{ $orders->firstItem()??0 }}–{{ $orders->lastItem()??0 }} dari {{ number_format($orders->total()) }} pesanan</span><nav aria-label="Halaman pesanan">@if($orders->previousPageUrl())<a href="{{ $orders->previousPageUrl() }}">Sebelumnya</a>@endif<span>{{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>@if($orders->nextPageUrl())<a href="{{ $orders->nextPageUrl() }}">Berikutnya</a>@endif</nav></footer>
</section></div>
