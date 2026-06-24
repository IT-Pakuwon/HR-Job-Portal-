<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Application for Employment - Pakuwon Group</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #2d2d2d;
            background: #fff;
            padding: 24px 28px;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            padding-bottom: 14px;
            border-bottom: 2.5px solid #1a2744;
            margin-bottom: 16px;
        }
        .header-company {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 4px;
            color: #1a2744;
        }
        .header-sub {
            font-size: 9px;
            color: #888;
            letter-spacing: 3px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .header-line {
            width: 40px;
            height: 2px;
            background: #4f6eb0;
            margin: 6px auto 0;
        }

        /* ── Section ── */
        .section { margin-bottom: 14px; page-break-inside: avoid; }

        .section-title {
            background: #1a2744;
            color: #fff;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 10px;
            border-left: 3px solid #4f6eb0;
        }

        .sub-title {
            background: #edf0f8;
            color: #1a2744;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 0.8px;
            padding: 4px 10px;
            border-bottom: 1px solid #d5d8e8;
            border-left: 3px solid #4f6eb0;
        }

        /* ── Info table ── */
        .info-tbl {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e0e3ee;
        }
        .info-tbl td {
            padding: 5px 10px;
            border-bottom: 1px solid #edf0f7;
            vertical-align: top;
        }
        .info-tbl tr:last-child td { border-bottom: none; }
        .lbl {
            font-size: 8.5px;
            color: #888;
            font-style: italic;
            white-space: nowrap;
            width: 16%;
        }
        .val {
            font-size: 10px;
            font-weight: bold;
            color: #1a1a1a;
        }

        /* ── Data table ── */
        .data-tbl {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e0e3ee;
        }
        .data-tbl th {
            background: #edf0f8;
            color: #1a2744;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 5px 8px;
            border: 1px solid #d5d8e8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-tbl td {
            font-size: 9.5px;
            text-align: center;
            padding: 5px 8px;
            border: 1px solid #edf0f7;
            color: #333;
        }
        .data-tbl td.td-left { text-align: left; padding-left: 10px; }
        .data-tbl tr:nth-child(even) td { background: #fafbfe; }
        .empty-row td { color: #aaa; font-style: italic; text-align: center; }

        /* ── Photo ── */
        .photo-cell {
            width: 110px;
            text-align: center;
            vertical-align: top;
            padding: 10px;
            border-left: 1px solid #e0e3ee;
        }
        .photo-wrap {
            width: 88px;
            height: 118px;
            border: 1px solid #c8cce0;
            overflow: hidden;
            margin: 0 auto 4px auto;
            background: #f5f6fb;
        }
        .photo-wrap img { width: 100%; height: 100%; object-fit: cover; }
        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8.5px;
            color: #bbb;
        }
        .photo-label { font-size: 8px; color: #aaa; margin-top: 3px; }

        /* ── Badge ── */
        .badge-s {
            background: #d1fae5;
            color: #065f46;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-w {
            background: #fee2e2;
            color: #991b1b;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-active {
            background: #d1fae5;
            color: #065f46;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-inactive {
            background: #f3f4f6;
            color: #6b7280;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 8px;
        }

        /* ── Salary highlight ── */
        .salary-val {
            font-size: 10px;
            font-weight: bold;
            color: #065f46;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════ HEADER ══ --}}
<div class="header">
    <div class="header-company">PAKUWON GROUP</div>
    <div class="header-sub">Application for Employment</div>
    <div class="header-line"></div>
</div>

{{-- ═══════════════════════════════════════════ PERSONAL INFORMATION ══ --}}
<div class="section">
    <div class="section-title">Personal Information</div>
    <table style="width:100%; border-collapse:collapse; border:1px solid #e0e3ee;">
        <tr>
            <td style="vertical-align:top; padding:0; width:78%;">
                <table class="info-tbl" style="border:none;">
                    <tr>
                        <td class="lbl">Full Name</td>
                        <td class="val">{{ $applicant->full_name }}</td>
                        <td class="lbl">Nick Name</td>
                        <td class="val">{{ $applicant->nick_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Date of Birth</td>
                        <td class="val">{{ $applicant->birth_place }}, {{ \Carbon\Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y') }}</td>
                        <td class="lbl">Age</td>
                        <td class="val">{{ $applicant->age }} yrs</td>
                    </tr>
                    <tr>
                        <td class="lbl">Gender</td>
                        <td class="val">{{ $applicant->gender ?: '-' }}</td>
                        <td class="lbl">Religion</td>
                        <td class="val">{{ $applicant->religion ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nationality</td>
                        <td class="val">{{ $applicant->citizenship ?: '-' }}</td>
                        <td class="lbl">Blood Type</td>
                        <td class="val">{{ $applicant->blood_type ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">NIK</td>
                        <td class="val" colspan="3">{{ $applicant->ktp_id ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">ID Address</td>
                        <td class="val" colspan="3">{{ $applicant->id_address ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Present Address</td>
                        <td class="val" colspan="3">{{ $applicant->domicile_address ? $applicant->domicile_address.', '.$applicant->domicile_city : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Height</td>
                        <td class="val">{{ $applicant->height ? $applicant->height.' cm' : '-' }}</td>
                        <td class="lbl">Weight</td>
                        <td class="val">{{ $applicant->weight ? $applicant->weight.' kg' : '-' }}</td>
                    </tr>
                </table>
            </td>
            <td class="photo-cell">
                <div class="photo-wrap">
                    @if($photo)
                        <img src="{{ $photo }}" alt="Photo">
                    @else
                        <div class="photo-placeholder">Pas Foto<br>3 × 4</div>
                    @endif
                </div>
                <div class="photo-label">Photo</div>
            </td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════ CONTACT ══ --}}
<div class="section">
    <div class="section-title">Contact</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Email</td>
            <td class="val" colspan="3">{{ $applicant->email_address ?: '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Phone</td>
            <td class="val">{{ $applicant->phone_number ?: '-' }}</td>
            <td class="lbl">Mobile</td>
            <td class="val">{{ $applicant->mobile_phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Facebook</td>
            <td class="val">{{ $applicant->sosmed_facebook_account ?: '-' }}</td>
            <td class="lbl">Instagram</td>
            <td class="val">{{ $applicant->sosmed_instagram_account ?: '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">X (Twitter)</td>
            <td class="val">{{ $applicant->sosmed_x_account ?: '-' }}</td>
            <td class="lbl">LinkedIn</td>
            <td class="val">{{ $applicant->sosmed_linkedin_account ?: '-' }}</td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════ EMERGENCY CONTACT ══ --}}
<div class="section">
    <div class="section-title">Emergency Contact</div>
    <table class="data-tbl">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone Number</th>
                <th>Relation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="td-left">{{ $applicant->urgent_contact_name ?: '-' }}</td>
                <td>{{ $applicant->urgent_phone ?: '-' }}</td>
                <td>{{ $applicant->urgent_contact_relation ?: '-' }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ══════════════════════════════════════════ FAMILY BACKGROUND ══ --}}
<div class="section">
    <div class="section-title">Family Background</div>
    <table class="data-tbl">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Name</th>
                <th>Relation</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Education</th>
                <th>Profession</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_family as $p)
            <tr>
                <td class="td-left">{{ $p->family_name }}</td>
                <td>{{ $p->family_type }}</td>
                <td>{{ $p->family_gender ?: '-' }}</td>
                <td>{{ $p->family_birt_of_date ?: '-' }}</td>
                <td>{{ $p->family_education ?: '-' }}</td>
                <td>{{ $p->family_profession ?: '-' }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="6">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ════════════════════════════════════════════ MARITAL STATUS ══ --}}
<div class="section">
    <div class="section-title">Marital Status</div>
    <table class="info-tbl" style="margin-bottom:6px;">
        <tr>
            <td class="lbl">Status</td>
            <td class="val">{{ $applicant->martial_status ?: '-' }}</td>
        </tr>
    </table>
    <table class="data-tbl">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Name</th>
                <th>Relation</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Education</th>
                <th>Profession</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_marital as $p)
            <tr>
                <td class="td-left">{{ $p->core_family_name }}</td>
                <td>{{ $p->core_family_type }}</td>
                <td>{{ $p->core_family_gender ?: '-' }}</td>
                <td>{{ $p->core_family_birt_of_date ?: '-' }}</td>
                <td>{{ $p->core_family_education ?: '-' }}</td>
                <td>{{ $p->core_family_profession ?: '-' }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="6">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ══════════════════════════════════════ EDUCATION & SKILLS ══ --}}
<div class="section">
    <div class="section-title">Education & Skills</div>

    <div class="sub-title">1. Formal Education</div>
    <table class="data-tbl" style="margin-bottom:8px;">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Institution</th>
                <th>Type</th>
                <th>Start Year</th>
                <th>End Year</th>
                <th>GPA / Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_education as $p)
            <tr>
                <td class="td-left">{{ $p->education_name }}</td>
                <td>{{ $p->education_type ?: '-' }}</td>
                <td>{{ $p->start_year }}</td>
                <td>{{ $p->end_year }}</td>
                <td style="font-weight:bold; color:#1a2744;">{{ $p->education_score ?: '-' }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="5">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sub-title">2. Non-Formal / Training</div>
    <table class="data-tbl" style="margin-bottom:8px;">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Course / Training Name</th>
                <th>Type</th>
                <th>Start Year</th>
                <th>End Year</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_course as $p)
            <tr>
                <td class="td-left">{{ $p->course_name }}</td>
                <td>{{ $p->course_type ?: '-' }}</td>
                <td>{{ $p->start_year }}</td>
                <td>{{ $p->end_year }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="4">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sub-title">3. Language Proficiency</div>
    <table class="data-tbl" style="margin-bottom:8px;">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px; width:60%;">Language</th>
                <th>Proficiency Level</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_language as $p)
            <tr>
                <td class="td-left">{{ $p->language_descr }}</td>
                <td>{{ $p->language_score ?: '-' }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="2">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sub-title">4. Skills</div>
    <table class="data-tbl">
        <thead>
            <tr><th style="text-align:left; padding-left:10px;">Skill Description</th></tr>
        </thead>
        <tbody>
            @forelse($applicant_skill as $p)
            <tr><td class="td-left">{{ $p->skill_descr }}</td></tr>
            @empty
            <tr class="empty-row"><td>No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════ STRENGTHS & WEAKNESSES ══ --}}
<div class="section">
    <div class="section-title">Strengths & Weaknesses</div>
    <table class="data-tbl">
        <thead>
            <tr>
                <th style="width:15%;">Type</th>
                <th style="text-align:left; padding-left:10px;">Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_sw as $p)
            <tr>
                <td>
                    @if($p->sw_type === 'S')
                        <span class="badge-s">Strength</span>
                    @else
                        <span class="badge-w">Weakness</span>
                    @endif
                </td>
                <td class="td-left">{{ $p->sw_descr }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="2">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ══════════════════════════════════════════ WORK EXPERIENCE ══ --}}
<div class="section">
    <div class="section-title">Work Experience</div>
    <table class="data-tbl">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Company</th>
                <th style="text-align:left; padding-left:10px;">Job Title</th>
                <th>Start</th>
                <th>End</th>
                <th>Last THP</th>
                <th style="text-align:left; padding-left:8px;">Superior</th>
                <th style="text-align:left; padding-left:8px;">Reason for Leaving</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_working as $p)
            <tr>
                <td class="td-left">{{ $p->company_name }}</td>
                <td class="td-left">{{ $p->job_title }}</td>
                <td>{{ $p->start_date }}</td>
                <td>{{ $p->is_current ? 'Present' : ($p->end_date ?: '-') }}</td>
                <td style="color:#065f46; font-weight:bold;">{{ $p->last_thp ? 'Rp '.number_format((int)$p->last_thp,0,',','.') : '-' }}</td>
                <td class="td-left">{{ $p->superior_name ?: '-' }}</td>
                <td class="td-left">{{ $p->reason_for_leaving ?: '-' }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="7">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ══════════════════════════════════════ DRIVER LICENSE ══ --}}
@if(isset($applicant_driver_license) && $applicant_driver_license->count() > 0)
<div class="section">
    <div class="section-title">Driver License</div>
    <table class="data-tbl">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px; width:70%;">License Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applicant_driver_license as $dl)
            <tr>
                <td class="td-left">SIM {{ $dl->driver_license_descr }}</td>
                <td>
                    @if($dl->status)
                        <span class="badge-active">Active</span>
                    @else
                        <span class="badge-inactive">Inactive</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ═════════════════════════════════════ SALARY & EXPECTATION ══ --}}
<div class="section">
    <div class="section-title">Salary & Expectation</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Expected Salary (THP)</td>
            <td class="salary-val" colspan="3">Rp {{ isset($applicant->expected_thp) && $applicant->expected_thp ? number_format((int)$applicant->expected_thp, 0, ',', '.') : '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Career Achievement</td>
            <td class="val" colspan="3">{{ $applicant->applicant_achievement ?: '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Source of Information</td>
            <td class="val" colspan="3">{{ $applicant->source_information ?: '-' }}</td>
        </tr>
    </table>
</div>

{{-- ════════════════════════════════════ RELATIVE & REFERENCE ══ --}}
<div class="section">
    <div class="section-title">Relative & Reference</div>

    <div class="sub-title">1. Relative Working at Pakuwon</div>
    <table class="data-tbl" style="margin-bottom:8px;">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Name</th>
                <th style="text-align:left; padding-left:10px;">Division</th>
                <th>Work Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="td-left">{{ $applicant->relative_work_name ?: '-' }}</td>
                <td class="td-left">{{ $applicant->relative_work_division ?: '-' }}</td>
                <td>{{ $applicant->relative_work_status ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="sub-title">2. Reference</div>
    <table class="data-tbl">
        <thead>
            <tr>
                <th style="text-align:left; padding-left:10px;">Name</th>
                <th style="text-align:left; padding-left:10px;">Company</th>
                <th style="text-align:left; padding-left:10px;">Position</th>
                <th>Relation</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            @forelse($applicant_reference as $ref)
            <tr>
                <td class="td-left">{{ $ref->reference_name }}</td>
                <td class="td-left">{{ $ref->reference_company_name ?: '-' }}</td>
                <td class="td-left">{{ $ref->reference_job_position ?: '-' }}</td>
                <td>{{ $ref->reference_relation ?: '-' }}</td>
                <td>{{ $ref->reference_phone_number ?: '-' }}</td>
            </tr>
            @empty
            <tr class="empty-row"><td colspan="5">No data recorded</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════ APPLICATION DETAILS ══ --}}
<div class="section">
    <div class="section-title">Application Details</div>
    <table class="info-tbl">
        <tr>
            <td class="lbl">Applying Elsewhere?</td>
            <td class="val">{{ $applicant->apply_other_on_progress == 1 ? 'Yes' : 'No' }}</td>
            <td class="lbl">Details</td>
            <td class="val">{{ $applicant->apply_other_on_progress_descr ?: '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Applied / Worked Here Before?</td>
            <td class="val" colspan="3">{{ $applicant->apply_status == 1 ? 'Yes' : 'No' }}</td>
        </tr>
    </table>
</div>


</body>
</html>
