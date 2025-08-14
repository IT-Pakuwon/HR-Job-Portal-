<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Permohonan Kerja - Pakuwon Group</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff;
        }

        table {
            width: 100%;
            /* border-collapse: collapse; */
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 14px;
        }

        table,
        th,
        td,
        tr {
            /* border: none !important; */
        }

        td,
        th {
            border: 1px solid #333;
            padding: 6px 10px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            border: none;
        }

        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <table>
        <tr>
            <td colspan="4" class="title">PAKUWON GROUP</td>
        </tr>
        <tr>
            <td colspan="4" class="title">APPLICATION FOR EMPLOYMENT</td>
        </tr>
    </table>

    <br>


    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 14px;">
        <tr class="section-header">
            <td colspan="5"><strong>DATA PRIBADI</strong></td>
        </tr>
        <tr>
            <td>Nama Lengkap <em>(Full Name)</em>:</td>
            <td>{{ $applicant->full_name }}</td>
            <td>Jenis Kelamin <em>(Sex)</em>:</td>
            <td>{{ $applicant->gender }}</td>

            <!-- Foto hanya sejajar dengan 2 baris pertama -->
            <td rowspan="2" style="width: 25%; text-align: center; vertical-align: top;">
                <div style="width: 113px; height: 151px; border: 1px solid #000; margin: auto;">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="Photo"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span style="font-size: 12px; line-height: 151px; display: inline-block;">Pas Foto 3x4</span>
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td>Tempat/Tanggal Lahir <em>(Place/Date Birth)</em> :</td>
            <td>{{ $applicant->birth_place }},{{ $applicant->date_of_birth }}</td>
            <td>Umur <em>(Age)</em>:</td>
            <td>{{ $applicant->age }}</td>
        </tr>
        <tr>
            <td>Agama <em>(Religion)</em> :</td>
            <td>{{ $applicant->religion }}</td>
            <td>Golongan Darah <em>(Blood Type)</em>:</td>
            <td colspan="2">{{ $applicant->blood_type }}</td>
        </tr>
        <tr>
            <td>Warga Negara <em>(Nationality)</em>:</td>
            <td>{{ $applicant->citizenship }}</td>
            <td>NIK <em>(ID Number)</em> :</td>
            <td colspan="2">{{ $applicant->ktp_id }}</td>
        </tr>
        <tr>
            <td>Alamat Tinggal <em>(Present Address)</em> :</td>
            <td colspan="4">{{ $applicant->id_address }}, {{ $applicant->domicile_address }}
                {{ $applicant->domicile_city }}</td>
        </tr>
        <tr>
            <td>Berat Badan <em>(Weight)</em>:</td>
            <td>{{ $applicant->weight }} kg</td>
            <td>Tinggi Badan <em>(Height)</em>:</td>
            <td colspan="2">{{ $applicant->height }} cm</td>
        </tr>
    </table>



    <table style="margin-top: 10px">
        <tr class="section-header">
            <td colspan="4">KONTAK</td>
        </tr>
        <tr>
            <td>Email <em>(Email Address)</em> :</td>
            <td colspan="3">{{ $applicant->email_address }}</td>
        </tr>
        <tr>
            <td>Telepon Rumah <em>(Phone Number)</em>:</td>
            <td>{{ $applicant->phone_number }}</td>
            <td>Handphone <em>(Mobile Number)</em>:</td>
            <td>{{ $applicant->phone_number }}</td>
        </tr>

        <tr>
            <td>Facebook:</td>
            <td>{{ $applicant->sosmed_facebook_account }}</td>
            <td>Instagram:</td>
            <td>{{ $applicant->sosmed_instagram_account }}</td>
        </tr>
        <tr>
            <td>X (Twitter):</td>
            <td>{{ $applicant->sosmed_x_account }}</td>
            <td>LinkedIn:</td>
            <td>{{ $applicant->sosmed_linkedin_account }}</td>
        </tr>
        <!-- Tambahkan baris lainnya sesuai kebutuhan -->
    </table>

    <table style="margin-top: 10px">
        <tr class="section-header">
            <td colspan="3">KONTAK EMERGENSI</td>
        </tr>

        <tr style="text-align: center; font-weight: bold;">
            <td>Nama<br><em>(Name)</em></td>
            <td>Kontak<br><em>(Phone Number)</em></td>
            <td>Relation<br><em>(Relation)</em></td>
        </tr>
        <tr style="text-align: center;">
            <td>{{ $applicant->urgent_contact_name }}</td>
            <td>{{ $applicant->urgent_phone }}</td>
            <td>{{ $applicant->urgent_contact_relation }}</td>
        </tr>
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <!-- Family Background Section Title -->
        <tr class="section-header" style="background-color: #f0f0f0;">
            <td colspan="4" style="padding: 8px; font-weight: bold;">FAMILY BACKGROUND</td>
        </tr>

        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 20%;">Nama<br><em>(Name)</em></td>
            <td style="width: 10%;">Relasi<br><em>(Relation)</em></td>
            <td style="width: 20%;">Pendidikan<br><em>(Education)</em></td>
            <td style="width: 20%;">Pekerjaan<br><em>(Profession)</em></td>
        </tr>

        @foreach ($applicant_family as $p)
            <tr style="text-align: center;">
                <td>{{ $p->family_name }}</td>
                <td>{{ $p->family_type }}</td>
                <td>{{ $p->family_education }}</td>
                <td>{{ $p->family_profession }}</td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <!-- Marital Status -->
        <tr class="section-header" style="background-color: #f0f0f0;">
            <td colspan="4" style="padding: 8px; font-weight: bold;">MARITAL STATUS</td>
        </tr>
        <tr>
            <td>Status Pernikahan <em>(Marital Status) :</em></td>
            <td colspan="3">{{ $applicant->martial_status }}</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 20%;">Nama<br><em>(Name)</em></td>
            <td style="width: 10%;">Relasi<br><em>(Relation)</em></td>
            <td style="width: 20%;">Pendidikan<br><em>(Education)</em></td>
            <td style="width: 20%;">Pekerjaan<br><em>(Profession)</em></td>
        </tr>

        @foreach ($applicant_marital as $p)
            <tr style="text-align: center;">
                <td>{{ $p->core_family_name }}</td>
                <td>{{ $p->core_family_type }}</td>
                <td>{{ $p->core_family_education }}</td>
                <td>{{ $p->core_family_profession }}</td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr class="section-header">
            <td colspan="3" style="padding: 8px; font-weight: bold;">PENDIDIKAN & KETRAMPILAN</td>
        </tr>
        <tr class="section-header">
            <td colspan="3" style="padding: 8px; font-weight: bold;">1. FORMAL</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 20%;">Nama Sekolah / Universitas<br><em>(School / University Name)</em></td>
            <td style="width: 10%;">Tahun Mulai<br><em>(Start Year)</em></td>
            <td style="width: 20%;">Tahun Selesai<br><em>(End Year)</em></td>
        </tr>

        @foreach ($applicant_education as $p)
            <tr style="text-align: center;">
                <td>{{ $p->education_name }}</td>
                <td>{{ $p->start_year }}</td>
                <td>{{ $p->start_year }}</td>
            </tr>
        @endforeach

        <tr class="section-header">
            <td colspan="3" style="padding: 8px; font-weight: bold;">2. NON-FORMAL</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 20%;">Nama<br><em>(Course Name)</em></td>
            <td style="width: 10%;">Tahun Mulai<br><em>(Start Year)</em></td>
            <td style="width: 20%;">Tahun Berakhir<br><em>(End Year)</em></td>
        </tr>

        @foreach ($applicant_course as $p)
            <tr style="text-align: center;">
                <td>{{ $p->course_name }}</td>
                <td>{{ $p->start_year }}</td>
                <td>{{ $p->start_year }}</td>
            </tr>
        @endforeach


        <tr class="section-header">
            <td colspan="3" style="padding: 8px; font-weight: bold;">3. LANGUANGE</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="3" style="width: 20%;">Bahasa<br><em>(Languange)</em></td>
            {{-- <td style="width: 10%;">Tahun Mulai<br><em>(Start Year)</em></td>
            <td style="width: 20%;">Tahun Selesai<br><em>(End Year)</em></td> --}}
        </tr>

        @foreach ($applicant_language as $p)
            <tr>
                <td colspan="3">{{ $p->language_descr }}</td>
            </tr>
        @endforeach

        <tr class="section-header">
            <td colspan="3" style="padding: 8px; font-weight: bold;">4. SKILL</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="3" style="width: 20%;">Description<br></td>
            {{-- <td style="width: 10%;">Tahun Mulai<br><em>(Start Year)</em></td>
            <td style="width: 20%;">Tahun Selesai<br><em>(End Year)</em></td> --}}
        </tr>

        @foreach ($applicant_skill as $p)
            <tr>
                <td colspan="3">{{ $p->skill_descr }}</td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr class="section-header">
            <td colspan="2" style="padding: 8px; font-weight: bold;">STRENGTHS & WEAKNESSES</td>
        </tr>

        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 10%;">Tipe<br><em>(Type)</em></td>
            <td style="width: 20%;">Deskripsi<br><em>(Description)</em></td>

        </tr>

        @foreach ($applicant_sw as $p)
            <tr style="text-align: center;">
                <td>{{ $p->sw_type }}</td>
                <td>{{ $p->sw_descr }}</td>
            </tr>
        @endforeach

    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr class="section-header">
            <td colspan="4" style="padding: 8px; font-weight: bold;">WORK EXPERIENCE</td>
        </tr>

        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 20%;">Nama Perusahaan<br><em>(Company Name)</em></td>
            <td style="width: 20%;"><br> Jabatan <em>( Job Title )</em></td>
            <td style="width: 10%;">Tahun Mulai<br><em>(Start Year)</em></td>
            <td style="width: 10%;">Tahun Berakhir<br><em>(End Year)</em></td>

        </tr>

        @foreach ($applicant_working as $p)
            <tr style="text-align: center;">
                <td>{{ $p->company_name }}</td>
                <td>{{ $p->job_title }}</td>
                <td>{{ $p->start_date }}</td>
                <td>{{ $p->end_date }}</td>
            </tr>
        @endforeach
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr class="section-header">
            <td colspan="2" style="padding: 8px; font-weight: bold;">SALARY & EXPECTATION</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 50%;">Gaji Terakhir (THP) <br><em>( Existing / Last Salary (THP) )</em></td>
            <td style="width: 50%;">Gaji Yang Diharapkan (THP) <br><em>( Expected Salary (THO) )</em></td>
        </tr>
        <tr style="text-align: center;">
            <td>{{ $p->existing_last_thp }}</td>
            <td>{{ $p->expected_thp }}</td>
        </tr>

        <tr>
            <td colspan="2" style="padding: 8px;">
                <strong>Ekspektasi</strong> <em>(Expectations)</em>:<br>
                {{ $applicant->expectations }}
            </td>
        </tr>
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;" border="1">
        <!-- Section Header -->
        <tr class="section-header" style="background: #f0f0f0;">
            <td colspan="3" style="padding: 8px; font-weight: bold;">RELATIVE & REFERENCE</td>
        </tr>

        <!-- Relative Section -->
        <tr class="section-header" style="background: #f9f9f9;">
            <td colspan="3" style="padding: 8px; font-weight: bold;">1. Relative</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td style="width: 33%;">Nama<br><em>(Name)</em></td>
            <td style="width: 33%;">Divisi<br><em>(Division)</em></td>
            <td style="width: 34%;">Jabatan<br><em>(Work Status)</em></td>
        </tr>
        <tr style="text-align: center;">
            <td>{{ $applicant->relative_work_name }}</td>
            <td>{{ $applicant->relative_work_division }}</td>
            <td>{{ $applicant->relative_work_status }}</td>
        </tr>

        <!-- Reference Section -->
        <tr class="section-header" style="background: #f9f9f9;">
            <td colspan="3" style="padding: 8px; font-weight: bold;">2. Reference</td>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <td>Nama<br><em>(Name)</em></td>
            <td>Divisi<br><em>(Division)</em></td>
            <td>Kontak<br><em>(Contact)</em></td>
        </tr>
        <tr style="text-align: center;">
            <td>{{ $applicant->reference_name }}</td>
            <td>{{ $applicant->reference_division }}</td>
            <td>{{ $applicant->reference_contact_number }}</td>
        </tr>
    </table>

    <table style="margin-top: 10px; width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr class="section-header">
            <td colspan="1" style="padding: 8px; font-weight: bold;">APPLICATION CLOSURE</td>
        </tr>
        <tr>
            <td colspan="1" style="padding: 8px;">
                <strong>Applying Elsewhere ?</strong>:<br>
                {{ $applicant->apply_other_on_progress == 1 ? 'Yes' : 'No' }}
            </td>
        </tr>
        <tr>
            <td colspan="1" style="padding: 8px;">
                <strong>Details</strong>:<br>
                {{ $applicant->apply_other_on_progress_descr }}
            </td>
        </tr>
        <tr>
            <td colspan="1" style="padding: 8px;">
                <strong>Applied/Worked here before ? </strong>:<br>
                {{ $applicant->apply_status == 1 ? 'Yes' : 'No' }}
            </td>
        </tr>
    </table>



</body>

</html>
