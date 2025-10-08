<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order — PT. Artisan Wahyu</title>
</head>
<style>
    @page {
        size: A4;
        margin: 12mm;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
    }

    .header-table {
        width: 100%;
        /* border: 1px solid #000; */
        margin-bottom: 12px;
        table-layout: fixed;
    }

    .header-table td {
        padding: 6px;
        vertical-align: top;
        font-size: 10px;
    }

    .company-name {
        width: 40%;
        vertical-align: top;
        text-align: left;
        padding-left: 12px;
        font-size: 10px;
        margin: 0;
        padding: 0;
        line-height: 1;
        /* border: 1px solid #000; */
    }

    .purchase-name {
        width: 60%;
        vertical-align: top;
        text-align: center;
        padding-left: 12px;
        font-size: 10px;
        margin: 0;
        padding: 0;
        line-height: 1;
        /* border: 1px solid #000; */
    }

    table {
        border-collapse: collapse;
        border-spacing: 1;
    }

    td {
        padding: 0;
        margin: 0;
        vertical-align: top;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p {
        margin: 0;
        line-height: 1.5;
    }
</style>

<body>
    {{-- Header --}}
    <div>
        <table class="header-table">
            <tr>
                <td class="company-name" style="width:40%;">
                    <h2><strong>PT. Artisan Wahyu</strong></h2>
                </td>
                <td class="purchase-name" style="width:20%;">
                    <h2 style="margin=0;"><strong>Order Pembelian</strong></h2>
                    <h3><strong>(Purchase Order)</strong></h3>
                </td>
                <td style=" width: 40%; vertical-align: bottom; text-align: left;font-size: 12px;">
                    <p><strong>PO No: 8000006117</strong></p>
                </td>
                <hr>
            </tr>
            <tr>
                <td colspan="2">
                    <p style="font-size: 11px; padding-left:5px">JL. Sultan Iskandar Muda No 8 Jakarta, DKI JAKARTA
                        60123</p>
                    <p style="font-size: 11px;padding-left:5px">Telp: (622) 129-0080 00 &nbsp;&nbsp; Fax: (622) 129-0531
                        91</p>
                    <table style="font-size:11px; border-collapse:collapse; margin-left:0; padding-left:0;">
                        <tr>
                            <td style="width:90px;">NPWP</td>
                            <td style="width:8px;">:</td>
                            <td>01.070.808.9-058.000</td>
                        </tr>
                        <tr>
                            <td style="vertical-align:top;">Alamat NPWP</td>
                            <td>:</td>
                            <td>
                                GEDUNG GANDARIA 8 OFFICE TOWER LT.32, JL SULTAN ISKANDAR MUDA KEBAYORAN LAMA UTARA,
                                KEBAYORAN LAMA, JAKARTA SELATAN, DKI JAKARTA 12240
                            </td>
                        </tr>
                    </table>

                    <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                </td>
                <td>
                    <p style="vertical-align: top; text-align: left;  ">
                        (Pls always indicate this PO No. all related invoices and/
                        or delivery receipt.)(Harap cantuman No. PO ini pada
                        invoice dan atau Surat Jalan.)</p>
                    <h2 style="font-size:12px;"><strong>SPPB No:
                            PB25090008</strong></h2>
                    <h2 style="font-size:12px;"><strong>(Ref: AW/PM/25-09/0061)</strong></h2>
                    <p style="font-size: 12px;">Tgl. PO / Date : 23 September 2025</p>
                    <hr style="border:none; border-top:2px solid #000; margin:4px 0; margin-top:4px">
                </td>
                <hr>
            </tr>
            <tr>
                <td colspan="2" style="vertical-align:top; padding:0;">
                    <table style="font-size:11px; border-collapse:collapse; margin:0; padding:0;">
                        <tr>
                            <td style="width:95px;">Supplier Name</td>
                            <td style="width:6px;">:</td>
                            <td>INDO INTI SARANA, PT</td>
                        </tr>
                        <tr>
                            <td>Address</td>
                            <td>:</td>
                            <td>JL. Sultan Iskandar Muda No 8 Jakarta, DKI JAKARTA 60123</td>
                        </tr>
                        <tr>
                            <td>Tel/Fax</td>
                            <td>:</td>
                            <td>01.070.808.9-058.000</td>
                        </tr>
                    </table>

                    <hr style="border:none; border-top:2px solid #000; margin:6px 0 4px 0;">
                </td>
                <td colspan="2" style="vertical-align:top; padding:0;">
                    <table style="font-size:11px; border-collapse:collapse; margin:0; padding:0;">
                        <tr>
                            <td style="width:95px;">Jangka Waktu Pembayaran</td>
                        </tr>
                        <tr>
                            <td>(Payment Terms)</td>
                            <td>:</td>
                            <td>21DAYS</td>
                        </tr>
                        <tr>
                            <td>Tel/Fax</td>
                            <td>:</td>
                            <td>01.070.808.9-058.000</td>
                        </tr>
                    </table>

                    <hr style="border:none; border-top:2px solid #000; margin:6px 0 4px 0;">
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
