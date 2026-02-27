@php
    $sum = [
        'total' => $rows->sum(fn($r)=>(float) data_get($r,$map['total'],0)),
        'jan'   => $rows->sum(fn($r)=>(float) data_get($r,$map['jan'],0)),
        'feb'   => $rows->sum(fn($r)=>(float) data_get($r,$map['feb'],0)),
        'mar'   => $rows->sum(fn($r)=>(float) data_get($r,$map['mar'],0)),
        'apr'   => $rows->sum(fn($r)=>(float) data_get($r,$map['apr'],0)),
        'may'   => $rows->sum(fn($r)=>(float) data_get($r,$map['may'],0)),
        'jun'   => $rows->sum(fn($r)=>(float) data_get($r,$map['jun'],0)),
        'jul'   => $rows->sum(fn($r)=>(float) data_get($r,$map['jul'],0)),
        'aug'   => $rows->sum(fn($r)=>(float) data_get($r,$map['aug'],0)),
        'sep'   => $rows->sum(fn($r)=>(float) data_get($r,$map['sep'],0)),
        'oct'   => $rows->sum(fn($r)=>(float) data_get($r,$map['oct'],0)),
        'nov'   => $rows->sum(fn($r)=>(float) data_get($r,$map['nov'],0)),
        'dec'   => $rows->sum(fn($r)=>(float) data_get($r,$map['dec'],0)),
    ];
@endphp

<table>
    <thead>
        <tr>
            <th style="width:5%">Account</th>
            <th style="width:6%">Activity</th>
            <th style="width:10%">Description</th>
            <th style="width:10%">Detail</th>
            <th style="width:4%">Qty</th>
            <th style="width:7%">Unit Price</th>
            <th style="width:7%">Total Budget</th>
            <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th>
            <th>May</th><th>Jun</th><th>Jul</th><th>Aug</th>
            <th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th>
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $r)
            <tr>
                <td class="text-center">{{ data_get($r,$map['account']) }}</td>
                <td class="text-center">{{ data_get($r,$map['activity']) }}</td>
                <td>{{ data_get($r,$map['descr']) }}</td>
                <td>{{ data_get($r,$map['detail']) }}</td>

                <td class="text-center">{{ $fmt(data_get($r,$map['qty']), 3) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['price']), 2) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['total']), 0) }}</td>

                <td class="text-right">{{ $fmt(data_get($r,$map['jan']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['feb']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['mar']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['apr']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['may']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['jun']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['jul']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['aug']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['sep']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['oct']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['nov']), 0) }}</td>
                <td class="text-right">{{ $fmt(data_get($r,$map['dec']), 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="19" class="text-center">No data</td>
            </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="6" class="text-right">{{ $totalLabel }}</td>
            <td class="text-right">{{ $fmt($sum['total'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['jan'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['feb'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['mar'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['apr'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['may'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['jun'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['jul'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['aug'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['sep'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['oct'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['nov'], 0) }}</td>
            <td class="text-right">{{ $fmt($sum['dec'], 0) }}</td>
        </tr>
    </tbody>
</table>