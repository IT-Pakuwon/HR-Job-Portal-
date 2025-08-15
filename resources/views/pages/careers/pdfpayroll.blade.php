<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Payroll Confirmation</title>
  <style>
      @page {
        size: A4;
        margin: 12mm;
      }

      body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 0;
        line-height: 1.4;
      }

      table {
        width: 100%;
        border-collapse: collapse;
      }

      td {
        vertical-align: top;
        padding: 2px 4px;
      }

      .section-title, h2 {
        margin: 8px 0;
        font-size: 16px;
        text-align: center;
      }

      .signature-table td {
        font-size: 11px;
        line-height: 1.2;
        padding: 6px;
        text-align: center;
      }

      .rev {
        text-align: right;
        font-weight: bold;
        font-size: 11px;
      }
      h2 {
        text-align: center;
        margin-bottom: 5px;
      }
      .text-center {
        text-align: center;
      }
      .text-right {
        text-align: right;
      }
      .bold {
        font-weight: bold;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      }
      td {
        vertical-align: top;
        padding: 4px 2px;
      }
      .signature-section {
        margin-top: 50px;
      }
      .signature-table td {
        text-align: center;
        vertical-align: bottom;
        padding-top: 50px;
      }
      .footer-note {
        border-top: 1px solid black;
        margin-top: 40px;
        padding-top: 10px;
        font-style: italic;
        font-size: 12px;
        text-align: center;
      }

      table td:first-child {
        width: 180px;
      }
      table td:nth-child(2) {
        width: 10px;
      }
      .signature-table {
      width: 100%;
      table-layout: fixed;
      margin-top: 10px;
    }

    .signature-table td {
      text-align: center;
      vertical-align: top;
      padding: 0;
      height: 100px;
    }

    .sig-block {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
    }

    .sig-name {
      font-weight: bold;
    }

    .sig-role {
      margin-top: 4px;
    }

    .sig-date {
      margin-top: auto;
      padding-top: 8px;
    }

  </style>
</head>
<body>

  <div class="text-left bold">NPK</div>
  <div class="text-left bold">REV. IX/2023</div>

  <h2>PAYROLL CONFIRMATION</h2>
  <p class="text-center">(Diisi oleh HRD)</p>

  <table>
    <tr><td>NAMA LENGKAP</td><td>:</td><td>{{ $full_name }}</td></tr>
    <tr><td>JENIS KELAMIN</td><td>:</td><td>{{ $gender }}</td></tr>
    <tr><td>TEMPAT / TANGGAL LAHIR</td><td>:</td><td>{{ $birth_place }}, {{ $datebirth }}</td></tr>
    <tr><td>AGAMA</td><td>:</td><td>{{ $religion }}</td></tr>
    <tr><td>STATUS PERNIKAHAN</td><td>:</td><td>{{ $martial_status }}</td></tr>
    <tr><td>JUMLAH TANGGUNGAN</td><td>:</td><td>{{ $tax_liability }}</td></tr>
    <tr><td>NO KTP</td><td>:</td><td>{{ $ktp_id }}</td></tr>
    <tr><td>NO NPWP</td><td>:</td><td>{{ $npwp_id }}</td></tr>
    <tr><td>NO REKENING BCA / MANDIRI</td><td>:</td><td>{{ $bank_account }} ({{ $bank_name }})</td></tr>
    <tr><td>BUSINESS UNIT</td><td>:</td><td>{{ $cpnyid }}</td></tr>
    <tr><td>ACCOUNT CODE</td><td>:</td><td></td></tr>
    <tr><td>DEPARTEMEN</td><td>:</td><td>{{ $departementid }}</td></tr>
    <tr><td>LEVEL</td><td>:</td><td>{{ $job_level }}</td></tr>
    <tr><td>JABATAN</td><td>:</td><td>{{ $job_title }} {{ $job_level }}</td></tr>
    <tr><td>GAJI YANG DISEPAKATI</td><td>:</td><td>Rp. {{ $net_salary }}* (gaji Nett, belum dipotong BPJS TK 3%)</td></tr>
    <tr><td>FASILITAS LAIN</td><td>:</td><td>{{ $other_facility }}</td></tr>
    <tr><td>TANGGAL KESEDIAAN</td><td>:</td><td>{{ $availability_date }}</td></tr>
    <tr><td>TANGGAL MULAI BEKERJA</td><td>:</td><td>{{ $work_start_date }}</td></tr>
    <tr><td>STATUS KEPEGAWAIAN</td><td>:</td><td>{{ $employment_status }}</td></tr>
  </table>

  <div class="signature-section">
    <p class="bold">MENYETUJUI,</p><br><br><br>
    <table class="signature-table" width="100%">
        @php
            $row1 = $approvals->whereIn('aprvid', [1, 2, 3]);
            $row2 = $approvals->whereIn('aprvid', [4, 5]);
        @endphp

        <tr>
            <!-- Karyawan -->
            <td width="25%">
                <strong style="text-transform: uppercase;">{{ $full_name ?? '__________' }}</strong><br>
                KARYAWAN<br>
                Tgl : ........................
            </td>

            @foreach ($row1 as $approval)
                <td width="25%">
                    <strong>{{ strtoupper($approval->name) }}</strong><br>
                    @if ($approval->aprvid == 1)
                        HOD
                    @elseif (in_array($approval->aprvid, [2, 3]))
                        HCD DEPT
                    @else
                        {{ strtoupper($approval->jabatan ?? '') }}
                    @endif
                    <br>
                    Tgl : ........................
                </td>
            @endforeach

            {{-- Sisa kolom kalau kurang dari 3 --}}
            @for ($i = 0; $i < 3 - $row1->count(); $i++)
                <td width="25%"></td>
            @endfor
        </tr>

        <tr style="height: 60px;"></tr> 
        <br>  
        <tr>
            @foreach ($row2 as $approval)
                <td colspan="2" align="center">
                    <strong>{{ strtoupper($approval->name) }}</strong><br>
                    {{ strtoupper($approval->jabatan ?? 'DIRECTOR') }}<br>
                    Tgl : ........................
                </td>
            @endforeach

            {{-- Sisa kolom kalau kurang dari 2 --}}
            @for ($i = 0; $i < 2 - $row2->count(); $i++)
                <td colspan="2"></td>
            @endfor
        </tr>
    </table>
</div>


  <div class="footer-note">
    * Program JHT dan JP <u>mengikuti peraturan perundangan</u> yang berlaku
  </div>

</body>
</html>
