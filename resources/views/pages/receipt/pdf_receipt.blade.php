<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<title>Surat Tanda Terima Barang</title>
<style>
  :root{ --fs-12:12px; --fs-11:11px; --fs-10:10px; --border:1px solid #000; }
  *{ box-sizing: border-box; }
  html,body{ height:100%; }
  body{
    margin:0; font-family:"Times New Roman",Times,serif; color:#000;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  @page{ size:A4 portrait; margin:14mm 12mm 14mm 12mm; }

  .sheet{ width:210mm; min-height:297mm; position:relative; }
  .header{ display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
  .brand{ font-weight:600; font-size:var(--fs-12); }
  .title{ text-align:center; width:100%; font-weight:800; font-size:20px; text-decoration:underline; margin-top:2mm; }
  .asli-box{ border:var(--border); padding:3px 10px; font-weight:700; font-size:var(--fs-12); }

  .block{ margin-top:10px; font-size:var(--fs-12); }
  .rf-table{ width:100%; border-collapse:collapse; }
  .rf-table td{ vertical-align:top; padding:1px 0; }
  .rf-table .label{ width:28mm; }
  .rf-right{ width:70mm; font-size:var(--fs-12); }
  .rf-right .kv{ display:flex; gap:6px; justify-content:space-between; }
  .rf-right .kv .k{ width:32mm; text-align:left; }
  .rf-right .kv .v{ width:35mm; text-align:left; }

  table.items{ width:100%; border-collapse:collapse; margin-top:8px; font-size:var(--fs-12); }
  .items th,.items td{ border:var(--border); padding:4px 6px; }
  .items th{ text-align:center; font-weight:700; }
  .t-center{ text-align:center; } .t-right{ text-align:right; }
  .notes-row td{ height:28mm; }

  .signs{ width:100%; border-collapse:collapse; margin-top:6px; font-size:var(--fs-12); }
  .signs td{ border:var(--border); padding:8mm 8mm 6mm; vertical-align:bottom; height:38mm; position:relative; }
  .signs .head{ position:absolute; top:6px; left:8mm; font-weight:600; }
  .signline{ margin-top:10mm; border-top:1px solid #000; width:60%; }
  .sign-caption{ font-size:var(--fs-11); margin-top:2mm; }

  .footer{ position:fixed; left:12mm; right:12mm; bottom:10mm; font-size:var(--fs-11); display:flex; justify-content:space-between; }
  @media print{ .title{ margin-top:0; } }
</style>
</head>
<body>
  <div class="sheet">
    {{-- Header --}}
    <div class="header">
      <div class="brand">
        {{ optional($company)->cpny_name ?? 'AW - Artisan Wahyu, PT' }}
      </div>
      <div class="asli-box">{{ ($rcp->copy_mark ?? 'ASLI') }}</div>
    </div>
    <div class="title">Surat Tanda Terima Barang</div>

    {{-- Received From + Meta kanan --}}
    @php
      // Siapkan info supplier/vendor (fallback aman jika kolom tidak tersedia)
      $vendorName   = $rcp->vendor_name   ?? $rcp->supplier_name   ?? optional($rcp->vendor)->name   ?? '';
      $vendorCode   = $rcp->vendor_code   ?? $rcp->supplier_code   ?? optional($rcp->vendor)->code   ?? '';
      $addr1        = $rcp->vendor_addr1  ?? '';
      $addr2        = $rcp->vendor_addr2  ?? '';
      $city         = $rcp->vendor_city   ?? '';
      $zip          = $rcp->vendor_zip    ?? '';
      $rcpDate      = \Carbon\Carbon::parse($rcp->rcpdate ?? now())->format('d/m/Y');
      $deptName     = $rcp->department_name ?? $rcp->dept_name ?? '';
      $sppbNbr      = $rcp->sppb_nbr ?? $rcp->sppbnbr ?? '';
      $poNbr        = $rcp->ponbr ?? '';
    @endphp

    <div class="block">
      <table class="rf-table">
        <tr>
          <td class="label">Received From</td>
          <td style="width:4mm;">:</td>
          <td>
            <div><strong>{{ trim($po->vendorname.' '.($vendorCode ? "( $po->vendorid )" : '')) }}</strong></div>
            @if($po->vendoralamat) <div>{{ trim($po->vendoralamat) }}</div> @endif
            @if($addr2) <div>{{ $addr2 }}</div> @endif
            @if($po->vendorcp || $po->vendortelp) <div>{{ trim($po->vendorcp.' '.$po->vendortelp) }}</div> @endif
          </td>
          <td class="rf-right" rowspan="3">
            <div class="kv"><span class="k">STTB Nbr</span><span class="v">: {{ $rcp->receiptnbr }}</span></div>
            <div class="kv"><span class="k">Receipt Date</span><span class="v">: {{ $rcpDate }}</span></div>
            <div class="kv"><span class="k">PO Nbr</span><span class="v">: {{ $poNbr }}</span></div>
            <div class="kv"><span class="k">SPPB Nbr</span><span class="v">: {{ $rcp->sppbjktid }}</span></div>
            <div class="kv"><span class="k">Department</span><span class="v">: {{ $rcp->department_id }}</span></div>
          </td>
        </tr>
      </table>
    </div>

    {{-- Items --}}
    <table class="items">
      <thead>
        <tr>
          <th style="width:10mm;">No</th>
          <th style="width:35mm;">Inventory Code</th>
          <th>Description of Goods</th>
          <th style="width:16mm;">Site</th>
          <th style="width:16mm;">Unit</th>
          <th style="width:22mm;">Quantity</th>
        </tr>
      </thead>
      <tbody>
        @forelse($details as $i => $d)
          <tr>
            <td class="t-center">{{ $i + 1 }}</td>
            <td>{{ $d->inventoryid  ?? '' }}</td>
            <td>
              {{ $d->inventory_descr ?? '' }}
              @if(!empty($d->line_note) || !empty($d->remark))
                <br>( {{ $d->line_note ?? $d->remark }} )
              @endif
            </td>
            <td class="t-center">{{ $d->siteid  ?? '' }}</td>
            <td class="t-center">{{ $d->uom  ?? '' }}</td>
            <td class="t-right">{{ number_format((float)($d->qty_received ?? 0), 2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="t-center">Tidak ada detail.</td>
          </tr>
        @endforelse

        {{-- ruang kosong seperti contoh --}}
        <tr class="notes-row"><td colspan="6"></td></tr>
      </tbody>
    </table>

    {{-- Signatures --}}
    <table class="signs">
      <tr>
        <td>
          <div class="head">Input Computer</div>
          <div style="height:16mm"></div>
          <div>{{ $created }}</div>
          <div class="signline"></div>
          <div class="sign-caption">{{ $now->format('d/m/Y') }}</div>
        </td>
        <td>
          <div class="head">Diterima Oleh</div>
          <div style="height:24mm"></div>
          <div class="signline"></div>
        </td>
        <td>
          <div class="head">Disetujui Oleh</div>
          <div style="height:24mm"></div>
          <div class="signline"></div>
          <div class="sign-caption">Head of Warehouse Div.</div>
        </td>
      </tr>
    </table>

    {{-- Footer fallback (canvas sudah menulis page x of y) --}}
    <div class="footer">
      <div>Asli: Supplier, Copy: Gudang</div>
      <div>&nbsp;</div>
    </div>
  </div>
</body>
</html>
