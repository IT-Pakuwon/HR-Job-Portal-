@php
    $isPR = strtolower($rcp->receipttype ?? '') === 'pr';

    $docTitle = $isPR ? 'Surat Tanda Terima Barang' : 'Surat Pengembalian Barang';

    $qtyField = $isPR ? 'qty_received' : 'qty_return';
@endphp
<!DOCTYPE html>
<html lang="id">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tanda Terima Barang</title>
</head>

<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.3;color:#000;">
    <div style="width:100%;page-break-after:auto;">
        <!-- Header -->
        <table style="width:100%;border-collapse:collapse;margin-bottom:6mm;">
            <tr>
                <td style="width:25%;text-align:left;vertical-align:middle;font-weight:700;">
                    {{ optional($company)->cpny_name ?? 'PT. ARTISAN WAHYU' }}
                </td>
                <td
                    style="width:50%;padding-left:50px;text-align:center;vertical-align:middle;font-size:18px;font-weight:700;">
                    {{ $docTitle }}
                </td>
                <td style="width:25%;text-align:right;vertical-align:middle;">
                    <div
                        style="display:inline-block;border:1px solid #000;padding:3px 10px;font-weight:700;font-size:12px;">
                        {{ $rcp->copy_mark ?? 'ASLI' }}
                    </div>
                </td>
            </tr>

            <!-- Meta Section -->
            <tr>
                <td colspan="2" style="vertical-align:top;padding-top:20px;">
                    <table style="width:100%;border-collapse:collapse;table-layout:auto;">
                        <tr>
                            <td style="width:28mm;">Received From</td>
                            <td style="width:4mm;">:</td>
                            <td>
                                <div><strong>{{ $po->vendorname ?? '' }} ( {{ $po->vendorid ?? '' }} )</strong></div>
                                <div>{{ $po->vendoralamat ?? '' }}</div>
                                {{-- <div>KOTA ADM. JAKARTA UTARA 14439</div> --}}
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="vertical-align:top;padding-top:20px;">
                    <table style="width:100%;border-collapse:collapse;table-layout:auto;">
                        <tr>
                            <td style="width:28mm;">STTB Nbr</td>
                            <td style="width:4mm;">:</td>
                            <td>{{ $rcp->receiptnbr ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Receipt Date</td>
                            <td>:</td>
                            <td>{{ $rcp->receiptdate ? \Carbon\Carbon::parse($rcp->receiptdate)->format('d/m/Y') : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td>PO Nbr</td>
                            <td>:</td>
                            <td>{{ $rcp->ponbr ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>SPPB Nbr</td>
                            <td>:</td>
                            <td>{{ $rcp->sppbjktid ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td>:</td>
                            <td>{{ $rcp->department_id ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Items -->
        <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:10px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="border:1px solid #000;padding:4px 6px;width:10%;text-align:center;">No</th>
                    <th style="border:1px solid #000;padding:4px 6px;width:15%;text-align:center;">Inventory Code</th>
                    <th style="border:1px solid #000;padding:4px 6px;text-align:center;">Description of Goods</th>
                    <th style="border:1px solid #000;padding:4px 6px;width:10%;text-align:center;">Site</th>
                    <th style="border:1px solid #000;padding:4px 6px;width:10%;text-align:center;">Unit</th>
                    <th style="border:1px solid #000;padding:4px 6px;width:10%;text-align:center;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
                    $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
                @endphp
                @foreach ($rcpdetails as $i => $item)
                    <tr>
                        <td style="border:1px solid #000;text-align:center;padding:4px;">{{ $i + 1 }}</td>
                        <td style="border:1px solid #000;padding:4px;">{{ $item->inventoryid }}</td>
                        <td style="border:1px solid #000;padding:4px;">{{ $item->inventory_descr }}</td>
                        <td style="border:1px solid #000;text-align:center;padding:4px;">{{ $item->siteid }}</td>
                        <td style="border:1px solid #000;text-align:center;padding:4px;">{{ $item->uom }}</td>
                        <td style="border:1px solid #000;text-align:center;padding:4px;">
                            {{ $nf2($item->{$qtyField}) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="6" style="border:1px solid #000;height:25mm;">&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <!-- Signature Section -->
        <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:2px;">
            <tr>
                <td style="border:1px solid #000;text-align:center;vertical-align:bottom;height:30mm;">
                    <div style="font-weight:600;margin-bottom:18mm;">Input Computer</div>
                    <div>{{ ucwords(strtolower(optional($rcp->creator)->name)) }}</div>
                    <div style="border-top:1px solid #000;width:60%;margin:6px auto 0;"></div>
                    <div style="font-size:11px;margin-top:2mm;">
                        {{ $rcp->receiptdate ? \Carbon\Carbon::parse($rcp->created_at)->format('d/m/Y') : '' }}</div>
                </td>
                <td style="border:1px solid #000;text-align:center;vertical-align:bottom;height:30mm;">
                    <div style="font-weight:600;margin-bottom:18mm;">Diterima Oleh</div>
                    <div>&nbsp;</div>
                    <div style="border-top:1px solid #000;width:60%;margin:6px auto 0;"></div>
                    <div style="font-size:11px;margin-top:2mm;">&nbsp;</div>
                </td>
                <td style="border:1px solid #000;text-align:center;vertical-align:bottom;height:30mm;">
                    <div style="font-weight:600;margin-bottom:18mm;">Disetujui Oleh</div>
                    <div>&nbsp;</div>
                    <div style="border-top:1px solid #000;width:60%;margin:6px auto 0;"></div>
                    <div style="font-size:11px;margin-top:2mm;">Head of Warehouse Div.</div>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <table style="width:100%;border-collapse:collapse;font-size:11px;margin-top:4px;">
            <tr>
                <td style="text-align:left;padding-top:4px;">Asli: Supplier, Copy: Gudang</td>
            </tr>
        </table>
    </div>
</body>

</html>
