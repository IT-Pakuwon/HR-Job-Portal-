<style>
  .sig-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  .sig-table td, .sig-table th { border: 1px solid #000; padding: 6px; vertical-align: top; font-size: 11px; word-wrap: break-word; }
  .sig-name   { font-weight: bold; }
  .sig-status { margin: 2px 0; }
  .sig-num    { font-weight: bold; margin-right: 6px; }
  .meta-table { width:100%; border-collapse:collapse; table-layout:fixed; margin:8px 0 6px; }
  .meta-table td { border:1px solid #000; padding:6px; font-size:12px; }
  .meta-label { width:140px; font-weight:bold; }
  .small { font-size: 11px; color: #444; }
</style>

<h2 style="text-align:center"><span style="font-size:16px"><strong>{{ $title }}</strong></span></h2>
<p style="text-align:center">{{ $cpnyname }}</p>
<br>

<table class="meta-table">
  <tbody>
    <tr>
      <td class="meta-label">{{ $doc_type }} No</td>
      <td>: {{ $docid }}</td>
    </tr>
    <tr>
      <td class="meta-label">Name</td>
      <td>: {{ $created_by_name ?? $created_by_username }}</td>
    </tr>
    <tr>
      <td class="meta-label">Keperluan</td>
      <td>: {{ $keperluan }}</td>
    </tr>
    <tr>
      <td class="meta-label">{{ $doc_type }} Date</td>
      <td>: {{ $sppjdate }}</td>
    </tr>
    <tr>
      <td class="meta-label">Department</td>
      <td>: {{ $department_id }}</td>
    </tr>
    @if(!empty($requesttype_name))
    <tr>
      <td class="meta-label">Request Type</td>
      <td>: {{ $requesttype_name }}</td>
    </tr>
    @endif
  </tbody>
</table>

<hr>

{{-- Tabel detail barang --}}
<table style="width:100%; border-collapse:collapse; border:1px solid #000;">
  <thead>
    <tr>
      <th style="text-align:center; width:30px;">No</th>
      <th style="text-align:center; width:100px;">InventoryID</th>
      <th style="text-align:center;">Description</th>
      <th style="text-align:center; width:60px;">Qty</th>
      <th style="text-align:center; width:60px;">UoM</th>
      <th style="text-align:center; width:120px;">Location</th>
      <th style="text-align:center; width:140px;">Sub Location</th>
    </tr>
  </thead>
  <tbody>
    @forelse($detail as $i => $dt)
      <tr>
        <td style="text-align:center;">{{ $i+1 }}</td>
        <td>{{ $dt->inventoryid }}</td>
        <td>{{ $dt->inventory_descr }}</td>
        <td style="text-align:right;">{{ number_format((float)$dt->qty, 2) }}</td>
        <td style="text-align:center;">{{ $dt->uom }}</td>
        <td>{{ optional($dt->location)->location_name }}</td>
        <td>{{ optional($dt->subLocation)->sub_location_name }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="7" style="text-align:center;">No items.</td>
      </tr>
    @endforelse
  </tbody>
</table>

@php
  // Warna status
  $stColor = 'black';
  if (in_array($status_doc, ['Approved','Completed'])) {
      $stColor = 'blue';
  } elseif (in_array($status_doc, ['Rejected','Cancel'])) {
      $stColor = 'red';
  } elseif ($status_doc === 'Hold') {
      $stColor = 'orange';
  }

  $colsPerRow = ($approve_count > 5) ? 4 : 3;   // 4 kolom saat landscape, 3 saat portrait
  $chunks     = $approval->values()->chunk($colsPerRow);
  $idx        = 1;
  $totalCols  = 1 + $colsPerRow;               // 1 (Created By) + kolom approver
@endphp

<br>

<table class="sig-table">
  <thead>
    <tr>
      <th colspan="{{ $totalCols }}">
        Status:
        <span style="color: {{ $stColor }};">{{ $status_doc }}</span>
        {{-- <span class="small"> — Printed at {{ now()->format('d M Y H:i') }}</span> --}}
      </th>
    </tr>
  </thead>
  <tbody>
    @forelse($chunks as $rowIndex => $chunk)
      <tr>
        {{-- Kolom "Created By" di kiri (rowspan = jumlah baris) --}}
        @if($rowIndex === 0)
          <td rowspan="{{ $chunks->count() }}" style="width:160px;">
            <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
            <div class="sig-status" style="color:blue">Created</div>
            <div>{{ $req_date_fmt }}</div>
          </td>
        @endif

        {{-- Kolom approver --}}
        @foreach($chunk as $dt2)
          @php
            $apStatus = $dt2->status;
            $label = $apStatus === 'A' ? 'Approved'
                   : ($apStatus === 'R' ? 'Rejected'
                   : ($apStatus === 'P' ? 'Waiting' : 'Revised'));
            $color = $apStatus === 'A' ? 'blue'
                   : ($apStatus === 'R' ? 'red'
                   : ($apStatus === 'P' ? 'orange' : 'red'));
            $dateStr = $dt2->aprvdateafter ? \Carbon\Carbon::parse($dt2->aprvdateafter)->format('d M Y H:i') : '';
          @endphp
          <td>
            <div>
              <span class="sig-num">{{ $idx++ }}.</span>
              <span class="sig-name">{{ $dt2->name }}</span>
            </div>
            <div class="sig-status" style="color: {{ $color }}">{{ $label }}</div>
            <div>{{ $dateStr }}</div>
          </td>
        @endforeach

        {{-- Filler bila kolom belum penuh --}}
        @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
          <td>&nbsp;</td>
        @endfor
      </tr>
    @empty
      <tr>
        <td>
          <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
          <div class="sig-status" style="color:blue">Created</div>
          <div>{{ $req_date_fmt }}</div>
        </td>
      </tr>
    @endforelse
  </tbody>
</table>
