<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengeluaran Gudang</title>
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
                    style="width:50%;padding-left:50px;text-align:center;vertical-align:middle;font-size:18px;font-weight:700;text-decoration:underline;">
                    Bukti Pengeluaran Gudang
                </td>
                <td style="width:25%;text-align:right;vertical-align:middle;">
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="padding-left:50px;text-align:center;vertical-align:middle;font-size:18px;font-weight:700;">
                    Non Stock
                </td>
                <td></td>
            </tr>

            <!-- Meta Section -->
            <tr>
                <td colspan="2" style="vertical-align:top;padding-top:20px;">
                    <table style="width:100%;border-collapse:collapse;table-layout:auto;">
                        <tr>
                            <td style="width:28mm;">Received From</td>
                            <td style="width:4mm;">:</td>
                            <td>
                                <div><strong>OFFICE ONE ( VV00001 )</strong></div>
                                <div>ITC MANGGA DUA, LANTAI 1, BLOK E2 NO.26</div>
                                <div>KOTA ADM. JAKARTA UTARA 14439</div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="vertical-align:top;padding-top:20px;">
                    <table style="width:100%;border-collapse:collapse;table-layout:auto;">
                        <tr>
                            <td style="width:28mm;">STTB Nbr</td>
                            <td style="width:4mm;">:</td>
                            <td>GR25102033</td>
                        </tr>
                        <tr>
                            <td>Receipt Date</td>
                            <td>:</td>
                            <td>14/10/2025</td>
                        </tr>
                        <tr>
                            <td>PO Nbr</td>
                            <td>:</td>
                            <td>8000006160</td>
                        </tr>
                        <tr>
                            <td>SPPB Nbr</td>
                            <td>:</td>
                            <td>PB25090018</td>
                        </tr>
                        <tr>
                            <td>Department</td>
                            <td>:</td>
                            <td>WAREHOUSE ATK</td>
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
                <tr>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">1</td>
                    <td style="border:1px solid #000;padding:4px;">ATK-AMP-1011</td>
                    <td style="border:1px solid #000;padding:4px;">AMPLOP COKLAT POLOS UKURAN A3<br>( RB25090054 /
                        RB25090066 )</td>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">AW</td>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">PCS</td>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">200.00</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">2</td>
                    <td style="border:1px solid #000;padding:4px;">ATK-BRD-1001</td>
                    <td style="border:1px solid #000;padding:4px;">PAPAN JALAN/CLIP BOARD NAMA<br>( RB25090020 )</td>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">AW</td>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">PCS</td>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">1.00</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000;text-align:center;padding:4px;">3</td>
                    <td style="border:1px solid #000;padding:4px;">ATK-DOC-1006</td>
                    <td style="border:1px solid:#000;padding:4px;">BOX FILE<br>( RB25090005 )</td>
                    <td style="border:1px solid:#000;text-align:center;padding:4px;">AW</td>
                    <td style="border:1px solid:#000;text-align:center;padding:4px;">PCS</td>
                    <td style="border:1px solid:#000;text-align:center;padding:4px;">5.00</td>
                </tr>
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
                    <div>Arie Wibisono</div>
                    <div style="border-top:1px solid #000;width:60%;margin:6px auto 0;"></div>
                    <div style="font-size:11px;margin-top:2mm;">14/10/2025</div>
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
    </div>
</body>

</html>
