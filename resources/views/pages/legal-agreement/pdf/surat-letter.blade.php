<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat {{ $kind }} - {{ $agreement->agreement_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 14mm 20mm 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11.5px;
            color: #111;
            line-height: 1.42;
            margin: 0;
            padding: 0;
        }

        p {
            margin: 0 0 9px;
            text-align: justify;
        }
    </style>
</head>

<body>

    {{-- Surat 1 / Surat 2 of a PSM (Mall), OLA (Office) or Addendum: one "Permohonan Pengembalian" letter. --}}
    @include('pages.legal-agreement.pdf.partials.surat1-psm-ola', [
        'docLabel' => in_array($agreement->agreement_type, ['ADDENDUM', 'FU_ADDENDUM'], true)
            ? 'Addendum'
            : (\App\Services\AgreementLetterService::isOffice($agreement) ? 'OLA' : 'PSM'),
    ])

</body>

</html>
