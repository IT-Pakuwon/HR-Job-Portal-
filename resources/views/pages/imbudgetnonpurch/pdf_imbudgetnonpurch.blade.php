<style>
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #000;
    }

    h2 {
        margin: 0;
        font-size: 16px;
        text-align: center;
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
        font-size: 11px;
    }

    th {
        text-align: center;
        background: #f7f7f7;
        font-weight: bold;
    }

    .meta-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .meta-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 12px;
        vertical-align: top;
        word-wrap: break-word;
        white-space: normal;
    }

    .meta-label {
        width: 140px;
        font-weight: bold;
        background: #f7f7f7;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 11px;
        vertical-align: top;
        word-wrap: break-word;
        white-space: normal;
        text-align: left;
    }

    .items-table th {
        text-align: center;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .muted {
        font-size: 10px;
        color: #555;
    }

    .sig-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    .sig-table th,
    .sig-table td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
        font-size: 11px;
    }

    .sig-table th {
        background: #f7f7f7;
        text-align: left;
        font-weight: bold;
    }

    .sig-name {
        font-weight: bold;
    }

    .sig-status {
        margin: 2px 0;
        font-size: 11px;
    }

    .sig-num {
        font-weight: bold;
        margin-right: 4px;
    }

    .status {
        font-weight: bold;
    }

    .status.blue {
        color: blue;
    }

    .status.red {
        color: red;
    }

    .status.orange {
        color: orange;
    }

    .status.black {
        color: #000;
    }
</style>

<h2>{{ $title }}</h2>
<p style="text-align:center; margin-top:4px; margin-bottom:12px;">
    {{ $cpnyname ?? $cpny_id }}
</p>

<table class="meta-table">
    <tbody>
        <tr>
            <td class="meta-label">{{ $doc_type }} No</td>
            <td>{{ $docid }}</td>
            <td class="meta-label">Name</td>
            <td>{{ $created_by_name ?? $created_by_username }}</td>
        </tr>

        <tr>
            <td class="meta-label">{{ $doc_type }} Date</td>
            <td>{{ $imnonpurchasedate }}</td>
            <td class="meta-label">Department</td>
            <td>{{ $department_id }}</td>
        </tr>

        <tr>
            <td class="meta-label">Company</td>
            <td>{{ $cpny_id }}</td>
            <td class="meta-label">User Peminta</td>
            <td>{{ $user_peminta ?? '-' }}</td>
        </tr>

        @php
            $type = $imnonpurchasetype ?? '-';

            $money = function ($value) {
                return number_format((float) ($value ?? 0), 2);
            };
        @endphp

        <tr>
            <td class="meta-label">Type</td>
            <td>{{ $type }}</td>
            <td class="meta-label">Created Date</td>
            <td>{{ $created_at_fmt }}</td>
        </tr>

        @if ($type === 'Budget Reallocation')
            <tr>
                <td class="meta-label">Request Budget</td>
                <td class="text-right">{{ $money($request_budget ?? 0) }}</td>

                <td class="meta-label">Budget From</td>
                <td class="text-right">{{ $money($budget_from ?? 0) }}</td>
            </tr>

            <tr>
                <td class="meta-label">Budget To</td>
                <td class="text-right">{{ $money($budget_to ?? 0) }}</td>

                <td class="meta-label"></td>
                <td></td>
            </tr>
        @elseif ($type === 'Unbudgeted')
            <tr>
                <td class="meta-label">Request Budget</td>
                <td class="text-right">{{ $money($request_budget ?? 0) }}</td>

                <td class="meta-label">Expenditure</td>
                <td>{{ $expenditure_type ?? '-' }}</td>
            </tr>
        @elseif ($type === 'Over Budget')
            <tr>
                <td class="meta-label">Request Budget</td>
                <td class="text-right">{{ $money($request_budget ?? 0) }}</td>

                <td class="meta-label">Existing Budget</td>
                <td class="text-right">{{ $money($existing_budget ?? 0) }}</td>
            </tr>

            <tr>
                <td class="meta-label">Over Budget</td>
                <td class="text-right">{{ $money($over_budget ?? 0) }}</td>

                <td class="meta-label"></td>
                <td></td>
            </tr>
        @else
            <tr>
                <td class="meta-label">Request Budget</td>
                <td class="text-right">{{ $money($request_budget ?? 0) }}</td>

                <td class="meta-label">Existing Budget</td>
                <td class="text-right">{{ $money($existing_budget ?? 0) }}</td>
            </tr>

            <tr>
                <td class="meta-label">Budget From</td>
                <td class="text-right">{{ $money($budget_from ?? 0) }}</td>

                <td class="meta-label">Budget To</td>
                <td class="text-right">{{ $money($budget_to ?? 0) }}</td>
            </tr>

            <tr>
                <td class="meta-label">Over Budget</td>
                <td class="text-right">{{ $money($over_budget ?? 0) }}</td>

                <td class="meta-label">Expenditure</td>
                <td>{{ $expenditure_type ?? '-' }}</td>
            </tr>
        @endif

        <tr>
            <td class="meta-label">Keperluan</td>
            <td colspan="3">
                {!! nl2br(e($imbudgetkeperluan ?? '-')) !!}
            </td>
        </tr>
    </tbody>
</table>

<table class="items-table">
    <thead>
        <tr>
            <th style="width:25px;">No</th>
            <th style="width:180px;">Description / Note</th>
            <th style="width:55px;">Qty / UoM</th>
            <th style="width:80px;">Price</th>
            <th style="width:90px;">Total Price</th>
            <th style="width:90px;">Budget Perpost</th>
            <th style="width:150px;">Budget Department</th>
            <th style="width:150px;">Budget Account / Activity</th>
        </tr>
    </thead>

    <tbody>
        @forelse($detail as $i => $dt)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>

                <td>
                    {{ $dt->imnonpurchase_descr ?? '-' }}

                    @if (!empty($dt->imnonpurchase_note))
                        <br>
                        <span class="muted">
                            Note: {{ $dt->imnonpurchase_note }}
                        </span>
                    @endif
                </td>

                <td class="text-right">
                    {{ number_format((float) ($dt->qty ?? 0), 2) }}
                    <br>
                    <span class="muted">{{ $dt->uom ?? '-' }}</span>
                </td>

                <td class="text-right">
                    {{ number_format((float) ($dt->price ?? 0), 2) }}
                </td>

                <td class="text-right">
                    {{ number_format((float) ($dt->total_price ?? 0), 2) }}
                </td>

                <td>
                    {{ $dt->budget_perpost ?? '-' }}
                </td>

                <td>
                    {{ $dt->budget_cpny_id ?? '-' }}
                    @if (!empty($dt->budget_business_unit_id))
                        - {{ $dt->budget_business_unit_id }}
                    @endif

                    <br>

                    <span class="muted">
                        {{ $dt->budget_department_fin_id ?? '-' }}
                    </span>
                </td>

                <td>
                    {{ $dt->budget_account_id ?? '-' }}

                    @if (!empty($dt->budget_activity_id))
                        - {{ $dt->budget_activity_id }}
                    @endif

                    <br>

                    <span class="muted">
                        {{ $dt->budget_activity_descr ?? '-' }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No items.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@php
    $stColor = match (true) {
        in_array($status_doc, ['Approved', 'Completed']) => 'blue',
        in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
        $status_doc === 'Hold' => 'orange',
        default => 'black',
    };

    $colsPerRow = $approve_count > 5 ? 4 : 3;
    $chunks = $approval->values()->chunk($colsPerRow);
    $idx = 1;
    $totalCols = 1 + $colsPerRow;
@endphp

<table class="sig-table">
    <thead>
        <tr>
            <th colspan="{{ $totalCols }}">
                Status:
                <span class="status {{ $stColor }}">
                    {{ $status_doc }}
                </span>
            </th>
        </tr>
    </thead>

    <tbody>
        @forelse($chunks as $rowIndex => $chunk)
            <tr>
                @if ($rowIndex === 0)
                    <td rowspan="{{ $chunks->count() }}" style="width:160px;">
                        <div class="sig-name">
                            {{ $created_by_name ?? $created_by_username }}
                        </div>
                        <div class="sig-status blue">Created</div>
                        <div>{{ $req_date_fmt }}</div>
                    </td>
                @endif

                @foreach ($chunk as $dt2)
                    @php
                        $label = match ($dt2->status) {
                            'A' => 'Approved',
                            'R' => 'Rejected',
                            'P' => 'Waiting',
                            default => 'Revised',
                        };

                        $color = match ($dt2->status) {
                            'A' => 'blue',
                            'R' => 'red',
                            'P' => 'orange',
                            default => 'red',
                        };

                        $dateStr = $dt2->aprv_dateafter
                            ? \Carbon\Carbon::parse($dt2->aprv_dateafter)->format('d M Y H:i')
                            : '';
                    @endphp

                    <td>
                        <div>
                            <span class="sig-num">{{ $idx++ }}.</span>
                            <span class="sig-name">{{ $dt2->aprv_name }}</span>
                        </div>

                        <div class="sig-status {{ $color }}">
                            {{ $label }}
                        </div>

                        <div>{{ $dateStr }}</div>
                    </td>
                @endforeach

                @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @empty
            <tr>
                <td>
                    <div class="sig-name">
                        {{ $created_by_name ?? $created_by_username }}
                    </div>
                    <div class="sig-status blue">Created</div>
                    <div>{{ $req_date_fmt }}</div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>