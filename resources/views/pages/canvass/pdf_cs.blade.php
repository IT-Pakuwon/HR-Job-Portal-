@php
    // util
    function nf($n){ return number_format((float)$n, 2); }
@endphp

<style>
    /* Landscape-friendly layout */
    body { font-family: Arial, sans-serif; font-size: 11px; color:#000; }
    h2 { margin:0; font-size: 16px; text-align:center; font-weight:bold; }
    .sub { text-align:center; font-size:12px; margin-bottom:8px; }

    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #000; padding:6px; vertical-align:top; font-size:11px; }

    .meta td { border:1px solid #000; padding:6px; font-size:11px; }
    .meta .label { width:140px; font-weight:bold; }

    .nowrap { white-space:nowrap; }
    .center { text-align:center; }
    .right { text-align:right; }

    .muted { color:#444; font-size:10px; }
    .status { font-weight:700; }
    .status.blue{ color:blue; } .status.red{ color:red; } .status.orange{ color:orange; }

    .tick { font-weight: bold; font-size: 13px; }
</style>

<h2>{{ $title }}</h2>
<div class="sub">{{ $cpnyname }}</div>

<table class="meta">
    <tr>
        <td class="label">{{ $doc_type }} No</td><td>{{ $docid }}</td>
        <td class="label">Requester</td><td>{{ $created_by_name ?? $created_by_username }}</td>
    </tr>
    <tr>
        <td class="label">{{ $doc_type }} Date</td><td>{{ $csdate }}</td>
        <td class="label">Department</td><td>{{ $department_id }}</td>
    </tr>
    @if(!empty($requesttype_name))
    <tr>
        <td class="label">Request Type</td><td colspan="3">{{ $requesttype_name }}</td>
    </tr>
    @endif
</table>

<table class="meta">
    <tr>
        <td class="label">Keperluan</td><td colspan="3">{{ $keperluan }}</td>
    </tr>
</table>

{{-- Header vendor ringkas pada bagian atas tabel (dinamis) --}}
@php
    $vendorCols = max(1, $vendorCount);
@endphp

<table>
    <thead>
        <tr>
            <th class="center" style="width:28px">No</th>
            <th class="center" style="width:110px">Inventory ID</th>
            <th>Description</th>
            <th class="center" style="width:52px">Qty</th>
            <th class="center" style="width:52px">UoM</th>

            @for($i=0; $i<$vendorCols; $i++)
                <th class="center" style="width:140px">
                    Price Vendor {{ $i+1 }}
                </th>
            @endfor

            <th class="center" style="width:120px">Location</th>
            <th class="center" style="width:120px">Sub Location</th>
        </tr>

        {{-- baris info vendor (nama/CP/Alamat/Term) --}}
        <tr>
            <th colspan="5" class="center">Nama Vendor / Term / CP</th>
            @forelse($vendors as $v)
                <th>
                    <div><strong>{{ $v['name'] }}</strong></div>
                    @if($v['top'])<div class="muted">Term: {{ $v['top'] }}</div>@endif
                    @if($v['cp']) <div class="muted">CP: {{ $v['cp'] }}</div>@endif
                    @if($v['telp'])<div class="muted">Telp: {{ $v['telp'] }}</div>@endif
                    @if($v['addr'])<div class="muted">Alamat: {{ $v['addr'] }}</div>@endif
                </th>
            @empty
                <th>-</th>
            @endforelse
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($detail as $i => $dt)
            <tr>
                <td class="center">{{ $i+1 }}</td>
                <td>{{ $dt->inventoryid }}</td>
                <td>{{ $dt->inventory_descr }}</td>
                <td class="right">{{ nf($dt->qty) }}</td>
                <td class="center">{{ $dt->uom }}</td>

                {{-- kolom vendor dinamis per baris --}}
                @for($k=1; $k <= $vendorCols; $k++)
                    @php
                        $price = (float) ($dt->{"vendorprice{$k}"} ?? 0);
                        $total = (float) ($dt->{"vendortotalprice{$k}"} ?? 0);
                        $sel   = (bool) ($dt->{"vendor{$k}selected"} ?? false);
                    @endphp
                    <td class="right">
                        {{ nf($price) }}<br>
                        <span class="muted">{{ nf($total) }}</span>
                        @if($sel)
                            <div class="center"><span class="tick">✔</span></div>
                        @endif
                    </td>
                @endfor

                <td>{{ optional($dt->location)->location_name }}</td>
                <td>{{ optional($dt->subLocation)->sub_location_name }}</td>
            </tr>
        @empty
            <tr><td colspan="{{ 5 + $vendorCols + 2 }}" class="center">No items.</td></tr>
        @endforelse

        {{-- BARIS RINGKASAN --}}
        <tr>
            <td colspan="5" class="right"><strong>Total</strong></td>
            @for($k=1; $k <= $vendorCols; $k++)
                @php $sum = collect($detail)->sum(fn($d)=> (float) ($d->{"vendortotalprice{$k}"} ?? 0)); @endphp
                <td class="right"><strong>{{ nf($sum) }}</strong></td>
            @endfor
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="5" class="right"><strong>Amount Tax</strong></td>
            @for($k=1; $k <= $vendorCols; $k++)
                @php
                    // pakai nilai di header bila ada
                    $taxHdr = data_get($vendors[$k-1] ?? [], 'tax', 0);
                @endphp
                <td class="right"><strong>{{ nf($taxHdr) }}</strong></td>
            @endfor
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="5" class="right"><strong>Grand Total</strong></td>
            @for($k=1; $k <= $vendorCols; $k++)
                @php $grandHdr = data_get($vendors[$k-1] ?? [], 'grand', 0); @endphp
                <td class="right"><strong>{{ nf($grandHdr) }}</strong></td>
            @endfor
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

{{-- APPROVALS --}}
@php
    $stColor = in_array($status_doc, ['Approved','Completed']) ? 'blue'
             : (in_array($status_doc, ['Rejected','Cancel']) ? 'red'
             : ($status_doc === 'Hold' ? 'orange' : ''));
    $colsPerRow = $approve_count > 5 ? 4 : 3;
    $chunks = $approval->values()->chunk($colsPerRow);
    $idx = 1;
@endphp

<table>
    <thead>
        <tr>
            <th colspan="{{ 1 + $colsPerRow }}" style="text-align:left;">
                Status: <span class="status {{ $stColor }}">{{ $status_doc }}</span>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($chunks as $rowIndex => $chunk)
            <tr>
                @if ($rowIndex === 0)
                    <td rowspan="{{ $chunks->count() }}" style="width:180px">
                        <div><strong>{{ $created_by_name ?? $created_by_username }}</strong></div>
                        <div class="status blue">Created</div>
                        <div class="muted">{{ $req_date_fmt }}</div>
                    </td>
                @endif

                @foreach ($chunk as $ap)
                    @php
                        $label = match ($ap->status) { 'A'=>'Approved','R'=>'Rejected','P'=>'Waiting', default=>'Revised' };
                        $color = match ($ap->status) { 'A'=>'blue','R'=>'red','P'=>'orange', default=>'red' };
                        $dateStr = $ap->aprvdateafter ? \Carbon\Carbon::parse($ap->aprvdateafter)->format('d M Y H:i') : '';
                    @endphp
                    <td>
                        <div><strong>{{ $idx++ }}. {{ $ap->name }}</strong></div>
                        <div class="status {{ $color }}">{{ $label }}</div>
                        <div class="muted">{{ $dateStr }}</div>
                    </td>
                @endforeach

                @for ($x=$chunk->count(); $x<$colsPerRow; $x++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @empty
            <tr>
                <td>
                    <div><strong>{{ $created_by_name ?? $created_by_username }}</strong></div>
                    <div class="status blue">Created</div>
                    <div class="muted">{{ $req_date_fmt }}</div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
