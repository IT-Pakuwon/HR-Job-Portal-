<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $cpnyname }} – {{ $doc_type }} {{ $perpost }}</title>

    <style>
        @page { size: A3 landscape; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; color:#000; }

        h2 { text-align: center; font-size: 18px; font-weight: bold; margin: 0; }
        .subtitle { text-align: center; font-size: 13px; margin: 2px 0 12px 0; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid #000; padding: 4px 3px; vertical-align: middle; }
        th { background: #f2f2f2; font-weight: bold; text-align: center; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-row { background: #e9e9e9; font-weight: bold; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

@php
    $fmt = function($v, $d=0){
        $n = is_numeric($v) ? (float)$v : 0;
        return number_format($n, $d, '.', ',');
    };

    $rows = collect($detail ?? []);

    // ✅ sesuai model BudgetDetail kamu
    $map = [
        'group'  => 'activity_type',      // OPEX / CAPEX
        'account'=> 'account_id',
        'activity'=> 'activity_id',
        'descr'  => 'activity_descr',
        'detail' => 'activity_detail',
        'qty'    => 'qty_budget',
        'price'  => 'unit_price_budget',
        'total'  => 'totalbudget',

        'jan'=>'period01_budget','feb'=>'period02_budget','mar'=>'period03_budget','apr'=>'period04_budget',
        'may'=>'period05_budget','jun'=>'period06_budget','jul'=>'period07_budget','aug'=>'period08_budget',
        'sep'=>'period09_budget','oct'=>'period10_budget','nov'=>'period11_budget','dec'=>'period12_budget',
    ];

    $opex  = $rows->filter(fn($r) => strtoupper(trim((string) data_get($r, $map['group']))) === 'OPEX');
    $capex = $rows->filter(fn($r) => strtoupper(trim((string) data_get($r, $map['group']))) === 'CAPEX');

    // fallback jika activity_type kosong semua
    if ($opex->isEmpty() && $capex->isEmpty()) {
        $opex = $rows;
        $capex = collect();
    }
@endphp

{{-- ===================== OPEX ===================== --}}
<h2>{{ $cpnyname }}</h2>
<p class="subtitle">OPEX - {{ $doc_type }} {{ $perpost }}</p>

@include('pages.budgets.partials_budget',[
    'rows' => $opex,
    'map'  => $map,
    'fmt'  => $fmt,
    'totalLabel' => 'TOTAL OPEX'
])

<div class="page-break"></div>

{{-- ===================== CAPEX ===================== --}}
<h2>{{ $cpnyname }}</h2>
<p class="subtitle">CAPEX - {{ $doc_type }} {{ $perpost }}</p>

@include('pages.budgets.partials_budget',[
    'rows' => $capex,
    'map'  => $map,
    'fmt'  => $fmt,
    'totalLabel' => 'TOTAL CAPEX'
])

</body>
</html>