<div id="vms-vendors-page" class="vms-document-page vms-purchase-page" data-invoice-page>
<nav class="vms-vendor-tabs vms-document-tabs" aria-label="Tahap Invoice Register">
@foreach($tabs as $key=>$label)<a class="vms-order-type" href="{{ route('vms.invoice-register.index',array_merge(request()->except(['page','status']),['tab'=>$key])) }}" @if($key===$tab) aria-current="page" @endif><span class="vms-order-type-icon"><i class="fas {{ ['review'=>'fa-clipboard-check','completed'=>'fa-check-circle','scan'=>'fa-barcode'][$key] }}" aria-hidden="true"></i></span><span><small>{{ ['review'=>'REVIEW','completed'=>'COMPLETED','scan'=>'SCAN'][$key] }}</small><strong>{{ $label }}</strong></span>@if(isset($counts[$key]))<b>{{ number_format($counts[$key]) }}</b>@endif</a>@endforeach
</nav>
<section class="vms-document-panel"><header><div><h2>{{ $tabs[$tab] }}</h2><p class="vms-vendor-description">{{ ['review'=>'Pemeriksaan dokumen Tax dan Finance, dilanjutkan final approval Finance.','completed'=>'Invoice yang sudah selesai direview dan status penerimaan dokumen fisiknya.','scan'=>'Scan barcode atau masukkan nomor IR untuk memeriksa dokumen fisik.'][$tab] }}</p></div><a class="vms-vendor-link" href="{{ request()->fullUrl() }}">Refresh</a></header>
<p class="vms-document-feedback" style="margin:16px 24px" data-invoice-list-error role="alert" hidden></p><p class="vms-invoice-loading" role="status" hidden>Memuat Invoice Register...</p>
<form method="get" class="vms-vendor-filters" data-invoice-filters>
<input type="hidden" name="tab" value="{{ $tab }}">
<label>Perusahaan<select name="company"><option value="">Semua perusahaan</option>@foreach($companies as $company)<option value="{{ $company->CpnyID }}" @selected(($filters['company']??'')===$company->CpnyID)>{{ $company->CpnyID }} - {{ $company->CpnyName }}</option>@endforeach</select></label>
@if($tab!=='scan')
<label>Vendor<select name="vendor"><option value="">Semua vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->vendor_id }}" @selected((string)($filters['vendor']??'')===(string)$vendor->vendor_id)>{{ $vendor->vendor_nama }} ({{ $vendor->second_vendor_id }})</option>@endforeach</select></label>
<label>{{ $tab==='completed'?'Status Send':'Status Review' }}<select name="status">
@foreach($tab==='completed'?['all'=>'Semua status','not_send'=>'Not Send','approve'=>'Receive','return'=>'Return']:['except_reject'=>'Aktif (tanpa rejected)','all'=>'Semua status','P'=>'In Progress','user_inprogress'=>'In Progress (User Kontrak)','D'=>'Revised','A'=>'Final Review Finance','R'=>'Rejected','H'=>'Hold','L'=>'Cancel'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach
</select></label>
<label>Business Unit<select name="business_unit"><option value="">Semua business unit</option>@foreach($businessUnits as $unit)<option value="{{ $unit }}" @selected(($filters['business_unit']??'')===$unit)>{{ $unit }}</option>@endforeach</select></label>
@endif
<label>{{ $tab==='scan'?'Scan / Nomor Invoice Register':'Cari invoice' }}<input type="search" name="search" maxlength="100" value="{{ $filters['search']??'' }}" placeholder="{{ $tab==='scan'?'Scan barcode atau ketik nomor IR':'Nomor IR / pesanan / vendor' }}" @if($tab==='scan') autofocus @endif autocomplete="off"></label>
<label class="vms-order-limit">Tampilkan<select name="per_page">@foreach([10,25,50,100] as $size)<option @selected(($filters['per_page']??10)==$size)>{{ $size }}</option>@endforeach</select></label>
<a class="vms-vendor-link" href="{{ route('vms.invoice-register.index',['tab'=>$tab]) }}">Reset filter</a><noscript><button type="submit">Cari</button></noscript>
</form>
@if($tab!=='scan')<p class="vms-vendor-description" style="margin:0 24px 18px">Dokumen Tax / Finance: W = Waiting, R = Revised, A = Approved.</p>@endif
<div class="vms-order-table"><table><thead><tr><th>Register No</th><th>Date</th><th>Vendor Name</th><th>Type</th><th>PO / SPK / Kontrak No</th><th>Status Review</th>@if($tab!=='review')<th>Status Send</th>@endif<th>Tax</th><th>Finance</th><th>Action</th></tr></thead><tbody>
@forelse($invoices as $invoice)
@php($invoiceStats=$stats[$invoice->kind.':'.$invoice->id])
<tr><td><a class="vms-vendor-link" href="{{ route('vms.invoice-register.show',[$invoice->kind,$invoice->id]) }}" data-invoice-detail>{{ $invoice->number }}</a></td><td>{{ $invoice->date }}</td><td><strong>{{ $invoice->vendor_name }}</strong><small>{{ $invoice->company }} &middot; {{ $invoice->second_vendor_id }}</small></td><td>{{ ['Purchase Material'=>'PO','Purchase Service'=>'SPK','Kontrak'=>'Kontrak'][$invoice->module]??$invoice->module }}</td><td>{{ $invoice->order_number }}</td><td><span class="vms-document-badge {{ $invoice->status==='C'?'':(in_array($invoice->status,['R','D'])?'inactive':'optional') }}">{{ \App\Services\Vms\InvoiceRegisterDirectory::STATUSES[$invoice->status]??$invoice->status }}</span></td>
@if($tab!=='review')<td>{{ ['not_send'=>'Not Send','approve'=>'Receive','return'=>'Return','revise'=>'Revise'][$invoice->send_status]??'Not Send' }}</td>@endif
@foreach(['tax','finance'] as $department)<td><div class="vms-vendor-approval"><small>W: {{ $invoiceStats[$department]['W'] }} &nbsp; R: {{ $invoiceStats[$department]['R'] }} &nbsp; A: {{ $invoiceStats[$department]['A'] }}</small></div></td>@endforeach
<td><a class="vms-vendor-link" href="{{ route('vms.invoice-register.show',[$invoice->kind,$invoice->id]) }}" data-invoice-detail>{{ $tab==='review'?'Review':'Detail' }}</a></td></tr>
@empty<tr><td colspan="10" class="vms-vendor-empty">{{ $tab==='scan'&&blank($filters['search']??null)?'Scan atau masukkan nomor Invoice Register untuk mulai.':'Tidak ada Invoice Register yang sesuai dengan filter.' }}</td></tr>@endforelse
</tbody></table></div>
<footer class="vms-order-pagination"><span>Menampilkan {{ $invoices->firstItem()??0 }}–{{ $invoices->lastItem()??0 }} dari {{ number_format($invoices->total()) }} invoice</span><nav aria-label="Halaman invoice">@if($invoices->previousPageUrl())<a href="{{ $invoices->previousPageUrl() }}">Sebelumnya</a>@endif<span>{{ $invoices->currentPage() }} / {{ $invoices->lastPage() }}</span>@if($invoices->nextPageUrl())<a href="{{ $invoices->nextPageUrl() }}">Berikutnya</a>@endif</nav></footer>
</section></div>
