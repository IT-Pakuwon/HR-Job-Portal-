<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid w-full grid-cols-1 gap-5 xl:grid-cols-12">

            <div class="flex flex-col gap-5 xl:col-span-6">

                {{-- ── PROFILE HERO ──────────────────────────────────────────── --}}
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">

                    {{-- Banner --}}
                    <div class="relative h-28 bg-gradient-to-br from-slate-800 via-indigo-700 to-violet-600">
                        <div class="absolute inset-0" style="background-image:radial-gradient(circle at 15% 60%,rgba(165,180,252,.35) 0,transparent 55%),radial-gradient(circle at 85% 20%,rgba(139,92,246,.3) 0,transparent 50%)"></div>
                        {{-- PDF buttons inside banner --}}
                        <div class="absolute right-4 top-4 flex-shrink-0 flex gap-2">
            {{-- ── Action Dropdown ──────────────────── --}}
            @if (!in_array($career->status ?? '', ['R', 'X']) && auth()->user()->hasRole('RECACCALLDEPT'))
            <div class="relative" x-data="{ actOpen: false }" @click.outside="actOpen = false">
                <button @click="actOpen = !actOpen"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 bg-white/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-white/30">
                    ⚡ Actions
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="actOpen" x-cloak x-transition
                    class="absolute right-0 top-full z-50 mt-1 w-44 rounded-xl border border-gray-100 bg-white py-1 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <button data-slf-action="tag"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-indigo-700 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-900/30">
                        🏷️ Tagging
                    </button>
                    <button data-slf-action="map"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-900/30">
                        🔗 Mapping
                    </button>
                    <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                    <button data-slf-action="reject"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                        ✕ Reject
                    </button>
                </div>
            </div>
            @endif
                            <form action="{{ route('applicantprofile.pdf') }}" method="POST" target="_blank">
                                @csrf
                                <input type="hidden" name="applicant_id"  value="{{ $applicant->applicant_id ?? '' }}">
                                <input type="hidden" name="job_title"     value="{{ $career->job_title ?? '' }}">
                                <input type="hidden" name="cpnyid"        value="AW">
                                <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                                <input type="hidden" name="job_level"     value="-">
                                <input type="hidden" name="mode"          value="preview">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 bg-white/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-white/30">
                                    &#128196; Preview PDF
                                </button>
                            </form>
                            <form action="{{ route('applicantprofile.pdf') }}" method="POST" target="pdf-download-frame">
                                @csrf
                                <input type="hidden" name="applicant_id"  value="{{ $applicant->applicant_id ?? '' }}">
                                <input type="hidden" name="job_title"     value="{{ $career->job_title ?? '' }}">
                                <input type="hidden" name="cpnyid"        value="AW">
                                <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                                <input type="hidden" name="job_level"     value="-">
                                <input type="hidden" name="mode"          value="download">
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 bg-white/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-white/30">
                                    &#8659; Download PDF
                                </button>
                            </form>
                            <iframe name="pdf-download-frame" style="display:none"></iframe>
                        </div>
                    </div>

                    {{-- Photo + Name --}}
                    <div class="px-6 pb-6">
                        <div class="relative z-10 -mt-14 mb-4">
                            <img src="{{ $photo }}" alt="{{ $applicant->full_name }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/sample.png') }}';"
                                class="h-28 w-28 rounded-lg border-4 border-white object-cover dark:border-gray-800">
                        </div>

                        <h1 class="text-xl font-bold leading-tight text-gray-900 dark:text-white">{{ $applicant->full_name }}</h1>
                        <p class="mt-0.5 text-sm text-indigo-600 dark:text-indigo-400">{{ $applicant->email_address }}</p>

                        {{-- Stats --}}
                        <div class="mt-5 flex gap-0 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <div class="flex-1 text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applicant->age }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Age</p>
                            </div>
                            <div class="flex-1 border-x border-gray-100 text-center dark:border-gray-700">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applicant->height }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Height (cm)</p>
                            </div>
                            <div class="flex-1 text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $applicant->weight }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Weight (kg)</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── PERSONAL DETAILS ─────────────────────────────────────── --}}
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="h-4 w-1 rounded-lg bg-indigo-500"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Personal Details</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-5">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Birth Place</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->birth_place ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Date of Birth</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Gender</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->gender ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Blood Type</dt>
                                <dd class="mt-1">
                                    @if($applicant->blood_type)
                                        <span class="inline-flex rounded-lg bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400">{{ $applicant->blood_type }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Citizenship</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->citizenship ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">KTP ID</dt>
                                <dd class="mt-1 break-all text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->ktp_id ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Marital Status</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->martial_status ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Religion</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->religion ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- ── CONTACT & ADDRESS ────────────────────────────────────── --}}
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="h-4 w-1 rounded-lg bg-emerald-500"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Contact & Address</h3>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-gray-700">
                        <div class="grid grid-cols-2 gap-4 px-6 py-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Phone</p>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->phone_number ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Mobile</p>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant->mobile_phone ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Domicile Address <span class="font-normal normal-case italic">(KTP)</span></p>
                            <p class="mt-1 text-sm text-gray-800 dark:text-gray-100">{{ $applicant->id_address ?: '—' }}</p>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Residential Address</p>
                            <p class="mt-1 text-sm text-gray-800 dark:text-gray-100">
                                {{ trim(($applicant->domicile_address ?? '') . ' ' . ($applicant->domicile_city ?? '')) ?: '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ── SOCIAL MEDIA ─────────────────────────────────────────── --}}
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="h-4 w-1 rounded-lg bg-pink-500"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Social Media</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-px bg-gray-100 dark:bg-gray-700">
                        <div class="bg-white px-5 py-4 dark:bg-gray-800">
                            <div class="mb-1 flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded bg-[#1877F2] text-[9px] font-black text-white">f</span>
                                <span class="text-xs font-semibold text-gray-500">Facebook</span>
                            </div>
                            <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_facebook_account ?: '—' }}</p>
                        </div>
                        <div class="bg-white px-5 py-4 dark:bg-gray-800">
                            <div class="mb-1 flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400 text-[9px] font-black text-white">ig</span>
                                <span class="text-xs font-semibold text-gray-500">Instagram</span>
                            </div>
                            <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_instagram_account ?: '—' }}</p>
                        </div>
                        <div class="bg-white px-5 py-4 dark:bg-gray-800">
                            <div class="mb-1 flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded bg-black text-[9px] font-black text-white">&#120143;</span>
                                <span class="text-xs font-semibold text-gray-500">Twitter / X</span>
                            </div>
                            <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_x_account ?: '—' }}</p>
                        </div>
                        <div class="bg-white px-5 py-4 dark:bg-gray-800">
                            <div class="mb-1 flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded bg-[#0A66C2] text-[9px] font-black text-white">in</span>
                                <span class="text-xs font-semibold text-gray-500">LinkedIn</span>
                            </div>
                            <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_linkedin_account ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- ── DETAIL INFORMATION ────────────────────────────────────── --}}
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="h-4 w-1 rounded-lg bg-amber-500"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Detail Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4 rounded-lg bg-emerald-50 p-4 dark:bg-emerald-900/20">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Expected Salary</p>
                            <p class="mt-1.5 text-base font-bold text-emerald-700 dark:text-emerald-300">
                                Rp {{ isset($applicant->expected_thp) && $applicant->expected_thp ? number_format((int)$applicant->expected_thp, 0, ',', '.') : '—' }}
                            </p>
                        </div>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Last Salary</dt>
                                <dd class="mt-1 text-sm text-gray-800 dark:text-gray-100">
                                    Rp {{ isset($applicant->existing_last_thp) && $applicant->existing_last_thp ? number_format((int)$applicant->existing_last_thp, 0, ',', '.') : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Expectations</dt>
                                <dd class="mt-1 text-sm text-gray-800 dark:text-gray-100">
                                    Rp {{ isset($applicant->expectations) && $applicant->expectations ? number_format((int)$applicant->expectations, 0, ',', '.') : '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Career Achievement</dt>
                                <dd class="mt-1 text-sm text-gray-800 dark:text-gray-100">{{ $applicant->applicant_achievement ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Job Source Information</dt>
                                <dd class="mt-1 text-sm text-gray-800 dark:text-gray-100">{{ $applicant->source_information ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <div class="flex flex-col gap-5 min-w-0 xl:col-span-6">

                {{-- ── TAB GROUP 1 ───────────────────────────────────────────── --}}
                <div x-data="{ activeTab: 'Education' }" class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">

                    {{-- Tab bar --}}
                    <div class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/40">
                        <nav class="-mb-px flex overflow-x-auto">
                            @foreach ([
                                ['key'=>'Education',     'label'=>'Education'],
                                ['key'=>'WorkExperience','label'=>'Work Experience'],
                                ['key'=>'Skill',         'label'=>'Skill & Language'],
                                ['key'=>'Certificate',   'label'=>'Certificate'],
                                ['key'=>'sdanw',         'label'=>'Strengths & Weaknesses'],
                            ] as $t)
                            <button @click="activeTab = '{{ $t['key'] }}'"
                                :class="activeTab === '{{ $t['key'] }}'
                                    ? 'border-b-[3px] border-indigo-500 text-indigo-600 bg-white dark:bg-gray-800 dark:text-indigo-400 font-semibold'
                                    : 'border-b-[3px] border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="whitespace-nowrap px-5 py-3.5 text-sm transition-colors duration-150 focus:outline-none">
                                {{ $t['label'] }}
                            </button>
                            @endforeach
                        </nav>
                    </div>

                    <div class="p-6">

                        {{-- Education --}}
                        <div x-show="activeTab === 'Education'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-0">
                                @forelse ($applicant_education as $education)
                                <div class="relative flex gap-4 pb-5 last:pb-0">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                            </svg>
                                        </div>
                                        @if (!$loop->last)
                                            <div class="mt-1 w-px flex-1 bg-gradient-to-b from-indigo-200 to-transparent dark:from-indigo-700"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 rounded-lg border border-gray-100 bg-gray-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/30 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:border-indigo-700 dark:hover:bg-indigo-900/10">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate font-semibold text-gray-900 dark:text-white">{{ $education->education_name }}</p>
                                                <span class="mt-1.5 inline-flex items-center rounded-lg bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $education->education_type }}
                                                </span>
                                            </div>
                                            <div class="flex flex-shrink-0 flex-col items-end gap-1.5">
                                                <span class="flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-600 dark:text-gray-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $education->start_year }} &mdash; {{ $education->end_year }}
                                                </span>
                                                @if ($education->education_score)
                                                <span class="flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                    GPA {{ $education->education_score }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm italic text-gray-400">No education data available</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Work Experience --}}
                        <div x-show="activeTab === 'WorkExperience'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-4">
                                @forelse ($applicant_working as $working)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $working->job_title }}</p>
                                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $working->company_name }}</p>
                                        </div>
                                        @if ($working->is_current)
                                            <span class="flex-shrink-0 rounded-lg bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">Currently Working</span>
                                        @endif
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $working->start_date }} — {{ $working->is_current ? 'Present' : ($working->end_date ?? '—') }}
                                    </p>
                                    @if($working->superior_name || $working->reason_for_leaving || $working->last_thp)
                                    <div class="mt-3 grid grid-cols-1 gap-3 border-t border-gray-200 pt-3 dark:border-gray-600 sm:grid-cols-3">
                                        @if($working->superior_name)
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Superior</p>
                                            <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $working->superior_name }}</p>
                                        </div>
                                        @endif
                                        @if($working->reason_for_leaving)
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Reason for Leaving</p>
                                            <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $working->reason_for_leaving }}</p>
                                        </div>
                                        @endif
                                        @if($working->last_thp)
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Last THP</p>
                                            <p class="mt-0.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format((int)$working->last_thp, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="py-8 text-center text-sm italic text-gray-400">No work experience data available</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Skill & Language --}}
                        <div x-show="activeTab === 'Skill'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="mb-6">
                                <p class="mb-1 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Skills</p>
                                @forelse ($applicant_skill as $skill)
                                    <div class="flex items-start gap-2.5 border-b border-gray-100 py-2 last:border-0 dark:border-gray-700/40">
                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $skill->skill_descr }}</span>
                                    </div>
                                @empty
                                    <p class="py-2 text-sm italic text-gray-400">No skill data available</p>
                                @endforelse
                            </div>
                            <div class="border-t border-gray-100 pt-5 dark:border-gray-700">
                                <p class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Languages</p>
                                <div class="space-y-2">
                                    @forelse ($applicant_language as $language)
                                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-gray-700/40">
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $language->language_descr }}</span>
                                        <span class="rounded-lg bg-blue-100 px-3 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $language->language_score }}</span>
                                    </div>
                                    @empty
                                    <p class="text-sm italic text-gray-400">No language data available</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Certificate --}}
                        <div x-show="activeTab === 'Certificate'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-0">
                                @forelse ($applicant_course as $course)
                                <div class="relative flex gap-4 pb-5 last:pb-0">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                            </svg>
                                        </div>
                                        @if (!$loop->last)
                                            <div class="mt-1 w-px flex-1 bg-gradient-to-b from-violet-200 to-transparent dark:from-violet-700"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 rounded-lg border border-gray-100 bg-gray-50 p-4 transition hover:border-violet-200 hover:bg-violet-50/30 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:border-violet-700 dark:hover:bg-violet-900/10">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $course->course_name }}</p>
                                                <span class="mt-1.5 inline-flex items-center rounded-lg bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                                                    {{ $course->course_type }}
                                                </span>
                                            </div>
                                            <span class="flex items-center gap-1 rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-600 dark:text-gray-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $course->start_year }} &mdash; {{ $course->end_year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm italic text-gray-400">No certificate data available</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Strengths & Weaknesses --}}
                        <div x-show="activeTab === 'sdanw'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            @if($applicant_sw->isEmpty())
                                <p class="py-8 text-center text-sm italic text-gray-400">No strengths &amp; weaknesses data available</p>
                            @else
                                @php
                                    $strengths  = $applicant_sw->where('sw_type', 'S');
                                    $weaknesses = $applicant_sw->where('sw_type', '!=', 'S');
                                @endphp
                                @if($strengths->isNotEmpty())
                                    <p class="mb-1 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Strengths</p>
                                    @foreach ($strengths as $sw)
                                        <div class="flex items-start gap-2.5 border-b border-gray-100 py-2 last:border-0 dark:border-gray-700/40">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $sw->sw_descr }}</span>
                                        </div>
                                    @endforeach
                                @endif
                                @if($weaknesses->isNotEmpty())
                                    <p class="mb-1 mt-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Weaknesses</p>
                                    @foreach ($weaknesses as $sw)
                                        <div class="flex items-start gap-2.5 border-b border-gray-100 py-2 last:border-0 dark:border-gray-700/40">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $sw->sw_descr }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── TAB GROUP 2 ───────────────────────────────────────────── --}}
                <div x-data="{ activeTab: 'Family' }" class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">

                    <div class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/40">
                        <nav class="-mb-px flex overflow-x-auto">
                            @foreach ([
                                ['key'=>'Family',       'label'=>'Family Information'],
                                ['key'=>'MaritalStatus','label'=>'Marital Status'],
                                ['key'=>'Emergency',    'label'=>'Emergency Contact'],
                                ['key'=>'Relative',     'label'=>'Relative Information'],
                            ] as $t)
                            <button @click="activeTab = '{{ $t['key'] }}'"
                                :class="activeTab === '{{ $t['key'] }}'
                                    ? 'border-b-[3px] border-indigo-500 text-indigo-600 bg-white dark:bg-gray-800 dark:text-indigo-400 font-semibold'
                                    : 'border-b-[3px] border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="whitespace-nowrap px-5 py-3.5 text-sm transition-colors duration-150 focus:outline-none">
                                {{ $t['label'] }}
                            </button>
                            @endforeach
                        </nav>
                    </div>

                    <div class="p-6">

                        {{-- Family --}}
                        <div x-show="activeTab === 'Family'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-3">
                                @forelse ($applicant_family as $family)
                                @php
                                    $initials = collect(explode(' ', $family->family_name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                                    $colors = ['bg-indigo-100 text-indigo-700','bg-emerald-100 text-emerald-700','bg-rose-100 text-rose-700','bg-amber-100 text-amber-700','bg-cyan-100 text-cyan-700','bg-violet-100 text-violet-700'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp
                                <div class="flex items-start gap-4 rounded-lg border border-gray-100 bg-gray-50 p-4 transition hover:border-indigo-100 hover:bg-indigo-50/20 dark:border-gray-700 dark:bg-gray-700/30">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold {{ $color }} dark:opacity-80">
                                        {{ $initials ?: '?' }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $family->family_name }}</span>
                                            @if ($family->family_type)
                                            <span class="rounded-lg bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $family->family_type }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($family->family_gender)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ $family->family_gender }}
                                            </span>
                                            @endif
                                            @if ($family->family_birt_of_date)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $family->family_birt_of_date }}
                                            </span>
                                            @endif
                                            @if ($family->family_education)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                                {{ $family->family_education }}
                                            </span>
                                            @endif
                                            @if ($family->family_profession)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                {{ $family->family_profession }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <p class="text-sm italic text-gray-400">No family data available</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Marital Status --}}
                        <div x-show="activeTab === 'MaritalStatus'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-3">
                                @forelse ($applicant_marital as $family)
                                @php
                                    $initials = collect(explode(' ', $family->core_family_name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                                    $colors = ['bg-rose-100 text-rose-700','bg-pink-100 text-pink-700','bg-fuchsia-100 text-fuchsia-700','bg-purple-100 text-purple-700'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp
                                <div class="flex items-start gap-4 rounded-lg border border-gray-100 bg-gray-50 p-4 transition hover:border-rose-100 hover:bg-rose-50/20 dark:border-gray-700 dark:bg-gray-700/30">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg text-sm font-bold {{ $color }} dark:opacity-80">
                                        {{ $initials ?: '?' }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $family->core_family_name }}</span>
                                            @if ($family->core_family_type)
                                            <span class="rounded-lg bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">{{ $family->core_family_type }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($family->core_family_gender)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ $family->core_family_gender }}
                                            </span>
                                            @endif
                                            @if ($family->core_family_birt_of_date)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $family->core_family_birt_of_date }}
                                            </span>
                                            @endif
                                            @if ($family->core_family_education)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                                {{ $family->core_family_education }}
                                            </span>
                                            @endif
                                            @if ($family->core_family_profession)
                                            <span class="flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                {{ $family->core_family_profession }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    </div>
                                    <p class="text-sm italic text-gray-400">No marital data available</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Emergency Contact --}}
                        <div x-show="activeTab === 'Emergency'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-900/10">
                                <p class="mb-4 text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">&#128680; Emergency Contact</p>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Name</p>
                                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-100">{{ $applicant->urgent_contact_name ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Relation</p>
                                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-100">{{ $applicant->urgent_contact_relation ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Phone</p>
                                        <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-100">{{ $applicant->urgent_phone ?: '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Relative Information --}}
                        <div x-show="activeTab === 'Relative'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            @if ($applicant->relative_work_name)
                            <div class="flex items-start gap-4 rounded-lg border border-cyan-100 bg-cyan-50/50 p-5 dark:border-cyan-900/40 dark:bg-cyan-900/10">
                                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $applicant->relative_work_name }}</p>
                                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        @if ($applicant->relative_work_division)
                                        <span class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            {{ $applicant->relative_work_division }}
                                        </span>
                                        @endif
                                        @if ($applicant->relative_work_status)
                                        <span class="inline-flex items-center rounded-lg bg-cyan-100 px-2.5 py-0.5 text-xs font-semibold text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">
                                            {{ $applicant->relative_work_status }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="py-10 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <p class="text-sm italic text-gray-400">No relative information available</p>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── ATTACHMENT & REFERENCE ────────────────────────────────── --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    {{-- Attachment --}}
                    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                        <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="h-4 w-1 rounded-lg bg-violet-500"></span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Attachment</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ([
                                ['label' => 'Curriculum Vitae',   'type' => 'cv',       'url' => $cv,       'color' => 'bg-red-100 dark:bg-red-900/30'],
                                ['label' => 'Transkrip Nilai',    'type' => 'transkip', 'url' => $transkip, 'color' => 'bg-amber-100 dark:bg-amber-900/30'],
                                ['label' => 'Ijazah',             'type' => 'ijazah',   'url' => $ijazah,   'color' => 'bg-emerald-100 dark:bg-emerald-900/30'],
                            ] as $doc)
                            <div class="flex items-center justify-between px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $doc['color'] }} text-lg">&#128196;</span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $doc['label'] }}</p>
                                        <p class="text-xs text-gray-400">PDF Document</p>
                                    </div>
                                </div>
                                @if ($doc['url'])
                                    <a href="{{ route('selfregister.download', ['hash' => $hash, 'type' => $doc['type']]) }}"
                                        class="shrink-0 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700">
                                        Download
                                    </a>
                                @else
                                    <span class="text-xs italic text-gray-400">No file</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Reference Information --}}
                    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                        <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="h-4 w-1 rounded-lg bg-cyan-500"></span>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Reference Information</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @if ($applicant->reference_name)
                            <div class="flex items-start gap-4 px-6 py-4">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300 text-sm font-bold">
                                    {{ strtoupper(substr($applicant->reference_name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $applicant->reference_name }}</p>
                                    @if ($applicant->reference_division)
                                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $applicant->reference_division }}</span>
                                    </div>
                                    @endif
                                    @if ($applicant->reference_contact_number)
                                    <p class="mt-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        &#128222; {{ $applicant->reference_contact_number }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="py-8 text-center">
                                <p class="text-sm italic text-gray-400">No reference data available</p>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>if(typeof lucide !== 'undefined') lucide.createIcons();</script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('applicantprofile');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const url = form.getAttribute('action') || "{{ route('applicantprofile.pdf') }}";
                const formData = new FormData(form);

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/pdf'
                        },
                        body: formData
                    });

                    if (!res.ok) {
                        const text = await res.text();
                        console.error('PDF ERROR:', res.status, text);
                        alert('Gagal generate PDF (HTTP ' + res.status + '). Cek console.');
                        return;
                    }

                    const contentType = (res.headers.get('content-type') || '').toLowerCase();

                    if (!contentType.includes('application/pdf')) {
                        const text = await res.text();
                        console.error('NOT PDF response:', contentType, text);
                        try {
                            const json = JSON.parse(text);
                            alert(json.message || 'Gagal generate PDF');
                        } catch (_) {
                            alert('Gagal generate PDF (response bukan PDF). Cek console.');
                        }
                        return;
                    }

                    const blob = await res.blob();

                    if (!(blob instanceof Blob) || blob.size === 0) {
                        console.error('Invalid blob:', blob);
                        alert('PDF kosong / invalid. Cek console.');
                        return;
                    }

                    const pdfUrl = URL.createObjectURL(blob);
                    window.open(pdfUrl, '_blank');
                    setTimeout(() => URL.revokeObjectURL(pdfUrl), 60_000);

                } catch (err) {
                    console.error('Fetch error:', err);
                    alert('Gagal generate PDF. Cek console.');
                }
            });
        });
    </script>

<!-- Tagging Modal — same as selfapplicant -->
<div id="taggingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">Tag Applicant</h2>
            <button id="closeTaggingModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
        </div>
        <input type="hidden" id="tagApplicantId" value="{{ $hash }}">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
            <select id="tagDivisionSelect" class="w-full" style="width:100%">
                <option value="">-- Select Division --</option>
                @php $showDivisions = \App\Models\Division::select('division_id','division_name')->where('status','A')->orderBy('division_name')->get(); @endphp
                @foreach($showDivisions as $div)
                    <option value="{{ $div->division_id }}">{{ $div->division_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select id="tagDeptSelect" class="w-full" style="width:100%">
                <option value="">-- Select Division first --</option>
            </select>
        </div>
        <div class="flex justify-end gap-3">
            <button id="closeTaggingModalBtn" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
            <button id="saveTagging" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700">Save Tag</button>
        </div>
    </div>
</div>

<!-- Mapping Modal — same as selfapplicant -->
<div id="mappingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="w-full max-w-5xl transform rounded-2xl bg-white p-8 shadow-2xl transition-all duration-300 scale-95 opacity-0" id="mappingModalContent">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Mapping Applicant</h2>
                <p class="text-sm text-gray-500">Assign candidate to job posting</p>
            </div>
            <button id="closeMappingModal" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
        </div>
        <input type="hidden" id="mapApplicantId" value="{{ $hash }}">
        <div class="mb-8 flex items-center gap-4 w-full">
            <div class="min-w-[200px] rounded-xl bg-gray-100 px-5 py-3 text-center text-base font-semibold text-gray-700 shadow-inner">
                <span id="mapDocId">{{ $career->docid ?? '' }}</span>
            </div>
            <div class="text-gray-400 text-2xl">→</div>
            <div class="flex-1 min-w-0">
                <select id="jobPostingSelect" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 hover:border-gray-400 transition"></select>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <button id="closeMappingModalBtn" class="rounded-lg px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700">Cancel</button>
            <button id="saveMapping" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow">Save Mapping</button>
        </div>
    </div>
</div>

<script>
$(function () {
    // ── Action dropdown buttons ──────────────────────────────────
    $(document).on('click', '[data-slf-action]', function () {
        const action = $(this).data('slf-action');

        if (action === 'tag') {
            $('#tagApplicantId').val(@json($hash));
            $('#taggingModal').removeClass('hidden').addClass('flex');

        } else if (action === 'map') {
            $('#mapApplicantId').val(@json($hash));
            loadJobPostings();
            $('#mappingModal').removeClass('hidden').addClass('flex');
            setTimeout(() => {
                $('#mappingModalContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }, 10);

        } else if (action === 'reject') {
            Swal.fire({
                title: 'Reject Applicant',
                text: 'Are you sure you want to reject this applicant?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                confirmButtonColor: '#dc2626',
            }).then(result => {
                if (!result.isConfirmed) return;
                $.post("{{ route('applicant.reject.store') }}", {
                    applicant_id: @json($hash),
                    _token: '{{ csrf_token() }}'
                }).done(function () {
                    Swal.fire({ icon: 'success', title: 'Rejected', timer: 1200, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1300);
                }).fail(function () {
                    Swal.fire('Error', 'Failed to reject.', 'error');
                });
            });
        }
    });

    // ── Tagging ──────────────────────────────────────────────────
    $('#tagDivisionSelect').select2({ dropdownParent: $('#taggingModal'), placeholder: '🔍 Search Division...', width: '100%', allowClear: true });

    function initDeptSelect2() {
        const $dept = $('#tagDeptSelect');
        if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
        $dept.select2({ dropdownParent: $('#taggingModal'), placeholder: '🔍 Search Department...', width: '100%', allowClear: true });
    }
    initDeptSelect2();

    $('#tagDivisionSelect').on('change', function () {
        const divId = $(this).val();
        const $dept = $('#tagDeptSelect');
        if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
        $dept.html('<option value="">-- Select Division first --</option>');
        initDeptSelect2();
        if (!divId) return;
        $dept.html('<option value="">Loading...</option>');
        $.get("{{ route('applicant.departments') }}", { division_id: divId }, function (data) {
            if ($dept.hasClass('select2-hidden-accessible')) $dept.select2('destroy');
            $dept.html('<option value="">-- Select Department --</option>');
            data.forEach(d => $dept.append(`<option value="${d.department_id}">${d.department_name}</option>`));
            initDeptSelect2();
        });
    });

    $('#saveTagging').on('click', function () {
        const divisionId   = $('#tagDivisionSelect').val();
        const departmentId = $('#tagDeptSelect').val();
        if (!divisionId || !departmentId) { Swal.fire('Incomplete', 'Please select both division and department.', 'warning'); return; }
        $.post("{{ route('applicant.tag.store') }}", {
            applicant_id: @json($hash), division_id: divisionId, department_id: departmentId, _token: '{{ csrf_token() }}'
        }).done(function () {
            Swal.fire({ icon: 'success', title: 'Tagged!', timer: 1200, showConfirmButton: false });
            $('#taggingModal').addClass('hidden').removeClass('flex');
        }).fail(function () { Swal.fire('Error', 'Failed to save tag.', 'error'); });
    });

    $('#closeTaggingModal, #closeTaggingModalBtn').on('click', function () {
        $('#taggingModal').addClass('hidden').removeClass('flex');
    });

    // ── Mapping ──────────────────────────────────────────────────
    function closeMappingModal() {
        $('#mappingModalContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => $('#mappingModal').addClass('hidden').removeClass('flex'), 200);
    }
    $('#closeMappingModal, #closeMappingModalBtn').on('click', closeMappingModal);

    function loadJobPostings() {
        $.get("{{ route('jobposting.list') }}", function (res) {
            let $select = $('#jobPostingSelect');
            $select.empty().append('<option value="">Select Job Posting</option>');
            res.forEach(item => $select.append(`<option value="${item.docid}">${item.docid} - ${item.job_name}</option>`));
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            $select.select2({ dropdownParent: $('#mappingModal'), placeholder: '🔍 Search Job Posting...', width: '100%', allowClear: true });
        });
    }

    $('#saveMapping').on('click', function () {
        const jobId = $('#jobPostingSelect').val();
        if (!jobId) { Swal.fire('Incomplete', 'Please select a job posting.', 'warning'); return; }
        $.post("{{ route('applicant.mapping.store') }}", {
            applicant_id: @json($hash), jobposting_docid: jobId, _token: '{{ csrf_token() }}'
        }).done(function () {
            Swal.fire({ icon: 'success', title: 'Mapped!', timer: 1200, showConfirmButton: false });
            closeMappingModal();
        }).fail(function (xhr) {
            Swal.fire('Error', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to map.', 'error');
        });
    });
});
</script>

</x-app-layout>
