@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pakuwon Group - Application Form</title>
    <style>
        body {
            font-family: 'Ariel', sans-serif;
            max-width: 900px;
            padding: 40px;
            border: 1px solid #ccc;
            background-color: #fff;
        }

        h1 {
            color: #004080;
            text-transform: uppercase;
            border-bottom: 2px solid #004080;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .cv-header {
            display: grid;
            grid-template-columns: 1fr 150px;
            gap: 30px;
            align-items: start;
            margin-bottom: 30px;
        }

        .cv-photo img {
            width: 150px;
            height: auto;
            border: 2px solid #ccc;
        }

        .cv-info p {
            margin: 4px 0;
        }

        .section-title {
            color: #004080;
            font-size: 18px;
            font-weight: bold;
            margin-top: 40px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        ul {
            padding-left: 20px;
            margin-top: 0;
        }

        li,
        p {
            line-height: 1.5;
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>

<body>

    <h1>Pakuwon Group - Application Profile</h1>

    <div class="cv-header">
        <div class="cv-info">
            <!-- <p><strong>Full Name:</strong> {{ $applicant->full_name }}</p>
            <p><strong>Nickname:</strong> {{ $applicant->nick_name }}</p> -->
            <!-- <p><strong>Date and Place of Birth:</strong> {{ $applicant->birth_place }}, {{ $date }}</p> -->
            <!-- <p><strong>Age:</strong> {{ $applicant->age }}</p>
            <p><strong>Gender:</strong> {{ $applicant->gender }}</p> -->
            <!-- <p><strong>Religion:</strong> {{ $applicant->religion }}</p>
            <p><strong>Blood Type:</strong> {{ $applicant->blood_type }}</p> -->
            <!-- <p><strong>Marital Status:</strong> {{ $applicant->martial_status }}</p> -->
            <!-- <p><strong>KTP ID:</strong> {{ $applicant->ktp_id }}</p> -->
            <!-- <p><strong>Citizenship:</strong> {{ $applicant->citizenship }}</p> -->
        </div>
        <div class="cv-photo">
            <img src="{{ $photo }}" alt="Applicant Photo"
                onerror="this.onerror=null;this.src='{{ asset('images/sample.png') }}';">
        </div>
    </div>

    <div class="two-columns">
        <!-- <div>
            <p><strong>ID Address:</strong> {{ $applicant->id_address }}</p>
            <p><strong>Domicile Address:</strong> {{ $applicant->domicile_address }}</p>
            <p><strong>Domicile City:</strong> {{ $applicant->domicile_city }}</p>
        </div> -->
        <!-- <div>
            <p><strong>Phone Number:</strong> {{ $applicant->phone_number }}</p>
            <p><strong>Mobile Phone:</strong> {{ $applicant->phone_number }}</p>
            <p><strong>Email:</strong> {{ $applicant->email_address }}</p>
        </div> -->
    </div>

    <div class="two-columns">
        <!-- <div>
            <p><strong>Height:</strong> {{ $applicant->height }} cm</p>
            <p><strong>Weight:</strong> {{ $applicant->weight }} kg</p>
        </div> -->
        <div>
            <!-- <p><strong>Facebook:</strong> {{ $applicant->sosmed_facebook_account }}</p>
            <p><strong>Instagram:</strong> {{ $applicant->sosmed_instagram_account }}</p>
            <p><strong>X (Twitter):</strong> {{ $applicant->sosmed_x_account }}</p>
            <p><strong>LinkedIn:</strong> {{ $applicant->sosmed_linkedin_account }}</p> -->
        </div>
    </div>
<!-- 
    <div class="section-title">EMERGENCY CONTACT</div>
    <p><strong>Name:</strong> {{ $applicant->urgent_contact_name }}</p>
    <p><strong>Phone:</strong> {{ $applicant->urgent_phone }}</p>
    <p><strong>Relation:</strong> {{ $applicant->urgent_contact_relation }}</p>

    <div class="section-title">SALARY & EXPECTATION</div>
    <p><strong>Existing Last THP:</strong> {{ $applicant->existing_last_thp }}</p>
    <p><strong>Expected THP:</strong> {{ $applicant->expected_thp }}</p>
    <p><strong>Expectations:</strong> {{ $applicant->expectations }}</p>

    <!-- <div class="section-title">RELATIVES AT COMPANY</div>
    <p><strong>Work Status:</strong> {{ $applicant->relative_work_status }}</p>
    <p><strong>Name:</strong> {{ $applicant->relative_work_name }}</p>
    <p><strong>Position:</strong> {{ $applicant->relative_work_division }}</p>

    <div class="section-title">REFERENCE</div>
    <p><strong>Name:</strong> {{ $applicant->reference_name }}</p>
    <p><strong>Position:</strong> {{ $applicant->reference_division }}</p>
    <p><strong>Contact:</strong> {{ $applicant->reference_contact_number }}</p> -->

    <div class="section-title">APPLICATION STATUS</div>
    <p><strong>Applying elsewhere?:</strong> {{ $applicant->apply_other_on_progress == 1 ? 'Yes' : 'No' }}</p>
    <p><strong>Details:</strong> {{ $applicant->apply_other_on_progress_descr }}</p>
    <p><strong>Applied/worked here before?:</strong> {{ $applicant->apply_status == 1 ? 'Yes' : 'No' }}</p> -->

    <!-- <div class="section-title">EDUCATION</div>
    <p><strong>FORMAL:</strong></p>
    @foreach ($applicant_education as $p)
        <p>{{ $p->start_year }} – {{ $p->start_year }}: <strong>{{ $p->education_name }}</strong></p>
    @endforeach
    <p style="margin-top:10px;"><strong>NON-FORMAL:</strong></p>
    @foreach ($applicant_course as $p)
        <p>{{ $p->start_year }} – {{ $p->end_year }}: <strong>{{ $p->course_name }}</strong></p>
    @endforeach -->

    <!-- <div class="section-title">SKILLS</div>
    <ul>
        @foreach ($applicant_skill as $p)
            <li>{{ $p->skill_descr }}</li>
        @endforeach
    </ul> -->

    <!-- <div class="section-title">LANGUAGES</div>
    <ul>
        @foreach ($applicant_language as $p)
            <li>{{ $p->language_descr }}</li>
        @endforeach
    </ul> -->

    <!-- <div class="section-title">WORK EXPERIENCE</div>
    <ul>
        @foreach ($applicant_working as $p)
            @php
                $startYear = Carbon::parse($p->start_year)->format('Y');
                $endYear = Carbon::parse($p->end_year)->format('Y');
            @endphp
            <li>{{ $startYear }} – {{ $endYear }}: <strong>{{ $p->company_name }}</strong> sebagai
                {{ $p->job_title }}</li>
        @endforeach
    </ul> -->

    <!-- <div class="section-title">STRENGTHS & WEAKNESSES</div>
    <ul>
        @foreach ($applicant_sw as $p)
            <li><strong>{{ $p->sw_type }}:</strong> {{ $p->sw_descr }}</li>
        @endforeach
    </ul> -->

    <!-- <div class="section-title">FAMILY BACKGROUND</div>
    <ul>
        @foreach ($applicant_family as $p)
            <li>{{ $p->family_name }} ({{ $p->family_type }}): <strong>{{ $p->family_education }}</strong> sebagai
                {{ $p->family_profession }}</li>
        @endforeach
    </ul> -->

    <!-- <div class="section-title">MARITAL STATUS & CHILDREN</div>
    <ul>
        @foreach ($applicant_marital as $p)
            <li>{{ $p->core_family_name }} ({{ $p->core_family_type }}):
                <strong>{{ $p->core_family_education }}</strong> sebagai {{ $p->core_family_profession }}
            </li>
        @endforeach
    </ul> -->

</body>

</html>
