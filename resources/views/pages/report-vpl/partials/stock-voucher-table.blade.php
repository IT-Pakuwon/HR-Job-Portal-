<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <th class="px-3 py-2">No</th>
                <th class="px-3 py-2">Tenant</th>
                <th class="px-3 py-2">Expiry Date</th>
                <th class="px-3 py-2 text-right">Nominal</th>
                <th class="px-3 py-2 text-right">Beginning (Lbr)</th>
                <th class="px-3 py-2">Tgl In</th>
                <th class="px-3 py-2 text-right">In (Lbr)</th>
                <th class="px-3 py-2">Tgl Out</th>
                <th class="px-3 py-2 text-right">Out (Lbr)</th>
                <th class="px-3 py-2 text-right">Ending</th>
                <th class="px-3 py-2 text-right">Total Nominal (Rp)</th>
                <th class="px-3 py-2">Diterima Dari</th>
                <th class="px-3 py-2">Untuk Pembayaran</th>
                <th class="px-3 py-2">Diambil Oleh</th>
                <th class="px-3 py-2">Keperluan</th>
                <th class="px-3 py-2">Receive No (DAS)</th>
                <th class="px-3 py-2">Transfer/Usage No (DAS)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            @forelse($groups as $group)
                @if($group['is_first_of_category'])
                    <tr class="bg-purple-50">
                        <td colspan="17" class="px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-purple-700">
                            {{ $group['category_label'] }}
                        </td>
                    </tr>
                @endif

                @php $groupRows = count($group['rows']) ? $group['rows'] : [null]; @endphp

                @foreach($groupRows as $rIndex => $row)
                    <tr class="bg-white text-gray-700">
                        @if($rIndex === 0)
                            @if($group['is_first_of_tenant'])
                                <td class="px-3 py-2 align-top font-medium" rowspan="{{ $group['tenant_rowspan'] }}">
                                    {{ $group['tenant_no'] }}
                                </td>
                                <td class="px-3 py-2 align-top font-medium" rowspan="{{ $group['tenant_rowspan'] }}">
                                    {{ $group['tenant'] }}
                                </td>
                            @endif

                            <td class="px-3 py-2 align-top" rowspan="{{ $group['group_rowspan'] }}">
                                {{ $group['expired_date']?->format('d-M-y') ?? '-' }}
                            </td>
                            <td class="px-3 py-2 align-top text-right" rowspan="{{ $group['group_rowspan'] }}">
                                {{ number_format($group['nominal'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 align-top text-right" rowspan="{{ $group['group_rowspan'] }}">
                                {{ number_format($group['beginning'], 0, ',', '.') }}
                            </td>
                        @endif

                        <td class="px-3 py-2">{{ $row && $row['direction'] === 'in' ? $row['date']?->format('d-M-y') : '' }}</td>
                        <td class="px-3 py-2 text-right">{{ $row && $row['direction'] === 'in' ? number_format($row['qty'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2">{{ $row && $row['direction'] === 'out' ? $row['date']?->format('d-M-y') : '' }}</td>
                        <td class="px-3 py-2 text-right">{{ $row && $row['direction'] === 'out' ? number_format($row['qty'], 0, ',', '.') : '' }}</td>

                        @if($rIndex === 0)
                            <td class="px-3 py-2 align-top text-right" rowspan="{{ $group['group_rowspan'] }}">
                                {{ number_format($group['ending'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 align-top text-right" rowspan="{{ $group['group_rowspan'] }}">
                                {{ number_format($group['total_nominal'], 0, ',', '.') }}
                            </td>
                        @endif

                        <td class="px-3 py-2">{{ $row['diterima_dari'] ?? '' }}</td>
                        <td class="px-3 py-2">{{ $row['untuk_pembayaran'] ?? '' }}</td>
                        <td class="px-3 py-2">{{ $row['diambil_oleh'] ?? '' }}</td>
                        <td class="px-3 py-2">{{ $row['keperluan'] ?? '' }}</td>
                        <td class="px-3 py-2">{{ $row && $row['doc_label'] === 'Receive' ? $row['doc_no'] : '' }}</td>
                        <td class="px-3 py-2">{{ $row && $row['doc_label'] !== 'Receive' ? $row['doc_no'] : '' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr class="bg-white">
                    <td colspan="17" class="px-3 py-8 text-center text-gray-500">
                        No data for the selected company/period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
