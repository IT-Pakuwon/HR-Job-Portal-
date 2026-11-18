<style>
    /* Applicant profile â label/value tiles */
    .ap-dl { gap: .75rem !important; }
    .ap-dl > div:has(> dt) { background: #f9fafb; border: 1px solid #f3f4f6; border-radius: .875rem; padding: .75rem .875rem; transition: border-color .15s ease, background .15s ease; }
    .ap-dl > div:has(> dt):hover { border-color: #c7d2fe; background: #fff; }
    .dark .ap-dl > div:has(> dt) { background: rgba(55, 65, 81, .3); border-color: #374151; }
    .dark .ap-dl > div:has(> dt):hover { border-color: #6366f1; background: rgba(55, 65, 81, .5); }
</style>
@php
    $yn = fn($v) => in_array(strtolower((string) $v), ['y', 'yes', '1', 'true'], true) ? 'Yes' : 'No';
@endphp
 <div class="max-w-9xl mx-auto w-full">
    <div class="grid w-full grid-cols-1 gap-5 xl:grid-cols-12">

                <div class="flex flex-col gap-5 xl:col-span-6">

                    {{-- ── PROFILE HERO ──────────────────────────────────────────── --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">

                        {{-- Banner (solid colour + soft shapes) --}}
                        <div class="relative h-32 overflow-hidden bg-indigo-600">
                            <div class="pointer-events-none absolute -right-10 -top-14 h-52 w-52 rounded-full bg-white/10"></div>
                            <div class="pointer-events-none absolute -bottom-20 left-1/3 h-44 w-44 rounded-full bg-white/10"></div>
                            <div class="pointer-events-none absolute left-8 top-4 h-16 w-16 rounded-full bg-white/5"></div>
                            {{-- PDF buttons inside banner --}}
                            <div class="absolute right-4 top-4 flex flex-shrink-0 gap-2">
                                <form action="{{ route('applicantprofile.pdf') }}" method="POST" target="_blank">
                                    @csrf
                                    <input type="hidden" name="applicant_id"  value="{{ $applicant->applicant_id ?? '' }}">
                                    <input type="hidden" name="job_title"     value="{{ $career->job_title ?? '' }}">
                                    <input type="hidden" name="cpnyid"        value="{{ $career->cpnyid ?? '' }}">
                                    <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                                    <input type="hidden" name="job_level"     value="{{ $career->job_level ?? '' }}">
                                    <input type="hidden" name="mode"          value="preview">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-white/30 bg-white/15 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-white/30">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        Preview PDF
                                    </button>
                                </form>
                                <form action="{{ route('applicantprofile.pdf') }}" method="POST" target="pdf-download-frame">
                                    @csrf
                                    <input type="hidden" name="applicant_id"  value="{{ $applicant->applicant_id ?? '' }}">
                                    <input type="hidden" name="job_title"     value="{{ $career->job_title ?? '' }}">
                                    <input type="hidden" name="cpnyid"        value="{{ $career->cpnyid ?? '' }}">
                                    <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                                    <input type="hidden" name="job_level"     value="{{ $career->job_level ?? '' }}">
                                    <input type="hidden" name="mode"          value="download">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download PDF
                                    </button>
                                </form>
                                <iframe name="pdf-download-frame" style="display:none"></iframe>
                            </div>
                        </div>

                        {{-- Photo + Name --}}
                        <div class="px-6 pb-6">
                            <div class="flex flex-wrap items-end gap-x-5 gap-y-3">
                                <div class="relative z-10 -mt-14">
                                    <a href="{{ $photo ?: asset('images/sample.png') }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        title="Open full photo"
                                        class="inline-block cursor-zoom-in">
                                        <img src="{{ $photo ?: asset('images/sample.png') }}" alt="{{ $applicant->full_name }}"
                                            onerror="this.onerror=null;this.src='{{ asset('images/sample.png') }}';this.closest('a').href=this.src;"
                                            class="h-32 w-32 rounded-2xl border-4 border-white bg-white object-cover shadow-lg transition hover:opacity-90 dark:border-gray-800">
                                    </a>
                                </div>
                                <div class="min-w-0 flex-1 pb-1 pt-3">
                                    <h1 class="truncate text-2xl font-bold leading-tight tracking-tight text-gray-900 dark:text-white">{{ $applicant->full_name }}</h1>
                                    <a href="mailto:{{ $applicant->email_address }}" class="mt-1 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                        {{ $applicant->email_address }}
                                    </a>
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        @if (!empty($career->job_title))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                {{ $career->job_title }}
                                            </span>
                                        @endif
                                        @if (!empty($applicant->applicant_id))
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $applicant->applicant_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="mt-5 grid grid-cols-3 gap-3">
                                <div class="flex items-center gap-3 rounded-2xl bg-sky-50 p-3 dark:bg-sky-500/10">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-sky-600 shadow-sm dark:bg-gray-800 dark:text-sky-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xl font-bold leading-none text-gray-900 dark:text-white">{{ $applicant->age ?: ($applicant->date_of_birth ? \Carbon\Carbon::parse($applicant->date_of_birth)->age : '-') }}</p>
                                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-sky-600/80 dark:text-sky-300/80">Age</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl bg-amber-50 p-3 dark:bg-amber-500/10">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm dark:bg-gray-800 dark:text-amber-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xl font-bold leading-none text-gray-900 dark:text-white">{{ $applicant->height }}</p>
                                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-amber-600/80 dark:text-amber-300/80">Height (cm)</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 p-3 dark:bg-emerald-500/10">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm dark:bg-gray-800 dark:text-emerald-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-xl font-bold leading-none text-gray-900 dark:text-white">{{ $applicant->weight }}</p>
                                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-widest text-emerald-600/80 dark:text-emerald-300/80">Weight (kg)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── PERSONAL DETAILS ─────────────────────────────────────── --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Personal Details</h3>
                        </div>
                        <div class="p-6">
                            <dl class="ap-dl grid grid-cols-2 gap-x-6 gap-y-5">
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
                                            <span class="inline-flex rounded-xl bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400">{{ $applicant->blood_type }}</span>
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

                    @if ($isSby)
                    {{-- Additional Information --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Additional Information</h3>
                        </div>
                        <div class="p-6">
                            <dl class="ap-dl grid grid-cols-2 gap-x-6 gap-y-5">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">NPWP Number</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant_additional->npwp_id ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">BPJS Number</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant_additional->bpjs_id ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Preferred Job</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant_additional->preferred_job ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Preferred Work Environment</dt>
                                    <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $applicant_additional->preferred_work_environment ?? '—' }}</dd>
                                </div>
                            </dl>
                            @if (optional($applicant_additional)->additional_education_and_training)
                            <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-700">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Other Education &amp; Self-Development Activities</dt>
                                <dd class="mt-1 text-sm text-gray-800 dark:text-gray-100">{{ $applicant_additional->additional_education_and_training }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- ── CONTACT & ADDRESS ────────────────────────────────────── --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Contact & Address</h3>
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
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-600 dark:bg-pink-500/15 dark:text-pink-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Social Media</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-px bg-gray-100 dark:bg-gray-700">
                            <div class="bg-white px-5 py-4 dark:bg-gray-800">
                                <div class="mb-1 flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded bg-[#1877F2] text-[9px] font-black text-white">f</span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Facebook</span>
                                </div>
                                <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_facebook_account ?: '—' }}</p>
                            </div>
                            <div class="bg-white px-5 py-4 dark:bg-gray-800">
                                <div class="mb-1 flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded bg-pink-600 text-[9px] font-black text-white">ig</span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Instagram</span>
                                </div>
                                <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_instagram_account ?: '—' }}</p>
                            </div>
                            <div class="bg-white px-5 py-4 dark:bg-gray-800">
                                <div class="mb-1 flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded bg-black text-[9px] font-black text-white">&#120143;</span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Twitter / X</span>
                                </div>
                                <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_x_account ?: '—' }}</p>
                            </div>
                            <div class="bg-white px-5 py-4 dark:bg-gray-800">
                                <div class="mb-1 flex items-center gap-2">
                                    <span class="flex h-5 w-5 items-center justify-center rounded bg-[#0A66C2] text-[9px] font-black text-white">in</span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">LinkedIn</span>
                                </div>
                                <p class="break-all text-sm text-gray-800 dark:text-gray-200">{{ $applicant->sosmed_linkedin_account ?: '—' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- ── DETAIL INFORMATION ────────────────────────────────────── --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Detail Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="mb-4 rounded-xl bg-emerald-50 p-4 dark:bg-emerald-900/20">
                                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Expected Salary</p>
                                <p class="mt-1.5 text-base font-bold text-emerald-700 dark:text-emerald-300">
                                    Rp {{ isset($applicant->expected_thp) && $applicant->expected_thp ? number_format((int)$applicant->expected_thp, 0, ',', '.') : '—' }}
                                </p>
                            </div>
                            <dl class="ap-dl grid grid-cols-1 gap-4">
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
                    <div x-data="{ activeTab: 'Education' }" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">

                        {{-- Tab bar --}}
                        <div class="border-b border-gray-100 bg-gray-50/70 px-3 py-2.5 dark:border-gray-700/60 dark:bg-gray-700/30">
                            <nav class="flex gap-1 overflow-x-auto">
                                @foreach ([
                                    ['key'=>'Education',     'label'=>'Education'],
                                    ['key'=>'WorkExperience','label'=>'Work Experience'],
                                    ['key'=>'Skill',         'label'=>'Skill & Language'],
                                    ['key'=>'Certificate',   'label'=>'Certificate'],
                                    ['key'=>'sdanw',         'label'=>'Strengths & Weaknesses'],
                                    ...($isSby ? [['key'=>'Organization', 'label'=>'Organization']] : []),
                                ] as $t)
                                <button @click="activeTab = '{{ $t['key'] }}'"
                                    :class="activeTab === '{{ $t['key'] }}'
                                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30 font-semibold'
                                        : 'text-gray-500 hover:bg-white hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200'"
                                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm transition-all duration-150 focus:outline-none">
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
                                        {{-- Timeline stem --}}
                                        <div class="flex flex-col items-center">
                                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600   dark:bg-indigo-900/40 dark:text-indigo-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                                </svg>
                                            </div>
                                            @if (!$loop->last)
                                                <div class="mt-1 w-px flex-1 bg-indigo-200 dark:bg-indigo-700"></div>
                                            @endif
                                        </div>
                                        {{-- Card --}}
                                        <div class="flex-1 rounded-xl border border-gray-100 bg-gray-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/30 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:border-indigo-700 dark:hover:bg-indigo-900/10">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate font-semibold text-gray-900 dark:text-white">{{ $education->education_name }}</p>
                                                    <span class="mt-1.5 inline-flex items-center rounded-xl bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                        {{ $education->education_type }}
                                                    </span>
                                                </div>
                                                <div class="flex flex-shrink-0 flex-col items-end gap-1.5">
                                                    <span class="flex items-center gap-1 rounded-xl bg-white px-2.5 py-1 text-xs font-medium text-gray-500   ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-600 dark:text-gray-400">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        {{ $education->start_year }} &mdash; {{ $education->end_year }}
                                                    </span>
                                                    @if ($education->education_score)
                                                    <span class="flex items-center gap-1 rounded-xl bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-600 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                        GPA {{ $education->education_score }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($isSby && ($education->education_faculty || $education->education_cost))
                                            <div class="mt-2.5 flex flex-wrap gap-x-5 gap-y-1 border-t border-gray-100 pt-2.5 text-xs text-gray-500 dark:border-gray-600 dark:text-gray-400">
                                                @if ($education->education_faculty)
                                                <span><span class="font-semibold text-gray-400">Faculty:</span> {{ $education->education_faculty }}</span>
                                                @endif
                                                @if ($education->education_cost)
                                                <span><span class="font-semibold text-gray-400">Financed By:</span> {{ $education->education_cost }}</span>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                    <div class="py-10 text-center">
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
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
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $working->job_title }}</p>
                                                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ $working->company_name }}</p>
                                            </div>
                                            @if ($working->is_current)
                                                <span class="flex-shrink-0 rounded-xl bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">Currently Working</span>
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
                                        @if ($isSby && $working->task_summary)
                                        <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-600">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Job Description</p>
                                            <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $working->task_summary }}</p>
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
                                        <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-700/40">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $language->language_descr }}</span>
                                            <span class="rounded-xl bg-blue-100 px-3 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ $language->language_score }}</span>
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
                                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600   dark:bg-violet-900/40 dark:text-violet-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                                </svg>
                                            </div>
                                            @if (!$loop->last)
                                                <div class="mt-1 w-px flex-1 bg-violet-200 dark:bg-violet-700"></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 rounded-xl border border-gray-100 bg-gray-50 p-4 transition hover:border-violet-200 hover:bg-violet-50/30 dark:border-gray-700 dark:bg-gray-700/30 dark:hover:border-violet-700 dark:hover:bg-violet-900/10">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $course->course_name }}</p>
                                                    <span class="mt-1.5 inline-flex items-center rounded-xl bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                                                        {{ $course->course_type }}
                                                    </span>
                                                </div>
                                                <span class="flex items-center gap-1 rounded-xl bg-white px-2.5 py-1 text-xs font-medium text-gray-500   ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-600 dark:text-gray-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $course->start_year }} &mdash; {{ $course->end_year }}
                                                </span>
                                            </div>
                                            @if ($isSby && ($course->course_certificate || $course->course_cost || $course->course_score))
                                            <div class="mt-2.5 flex flex-wrap gap-x-5 gap-y-1 border-t border-gray-100 pt-2.5 text-xs text-gray-500 dark:border-gray-600 dark:text-gray-400">
                                                @if ($course->course_certificate)
                                                <span><span class="font-semibold text-gray-400">Certificate No:</span> {{ $course->course_certificate }}</span>
                                                @endif
                                                @if ($course->course_cost)
                                                <span><span class="font-semibold text-gray-400">Funded By:</span> {{ $course->course_cost }}</span>
                                                @endif
                                                @if ($course->course_score)
                                                <span><span class="font-semibold text-gray-400">Score:</span> {{ $course->course_score }}</span>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                    <div class="py-10 text-center">
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
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
                                        $strengths  = $applicant_sw->where('sw_type', 'Strengths');
                                        $weaknesses = $applicant_sw->where('sw_type', 'Weaknesses');
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
                                        <p class="mb-1 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Weaknesses</p>
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

                            @if ($isSby)
                            {{-- Organization --}}
                            <div x-show="activeTab === 'Organization'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="mb-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Have you been part of an organization?</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $yn(optional($applicant_additional)->has_exp_organization) }}</p>
                                </div>
                                <div class="space-y-3">
                                    @forelse ($applicant_organization as $org)
                                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $org->organization_name }}</p>
                                            @if ($org->organization_year)
                                            <span class="rounded-xl bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $org->organization_year }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($org->organization_position)
                                            <span>{{ $org->organization_position }}</span>
                                            @endif
                                            @if ($org->organization_type)
                                            <span>{{ $org->organization_type }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                    <p class="py-2 text-sm italic text-gray-400">No organization data available</p>
                                    @endforelse
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>

                    {{-- ── TAB GROUP 2 ───────────────────────────────────────────── --}}
                    <div x-data="{ activeTab: 'Family' }" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">

                        <div class="border-b border-gray-100 bg-gray-50/70 px-3 py-2.5 dark:border-gray-700/60 dark:bg-gray-700/30">
                            <nav class="flex gap-1 overflow-x-auto">
                                @foreach ([
                                    ['key'=>'Family',       'label'=>'Family Information'],
                                    ['key'=>'MaritalStatus','label'=>'Marital Status'],
                                    ['key'=>'Emergency',    'label'=>'Emergency Contact'],
                                    ['key'=>'Relative',     'label'=>'Relative Information'],
                                    ['key'=>'License',      'label'=>'Driver License'],
                                ] as $t)
                                <button @click="activeTab = '{{ $t['key'] }}'"
                                    :class="activeTab === '{{ $t['key'] }}'
                                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30 font-semibold'
                                        : 'text-gray-500 hover:bg-white hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200'"
                                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm transition-all duration-150 focus:outline-none">
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
                                    <div class="flex items-start gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 transition hover:border-indigo-100 hover:bg-indigo-50/20 dark:border-gray-700 dark:bg-gray-700/30">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl text-sm font-bold {{ $color }} dark:opacity-80">
                                            {{ $initials ?: '?' }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $family->family_name }}</span>
                                                @if ($family->family_type)
                                                <span class="rounded-xl bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $family->family_type }}</span>
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
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
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
                                    <div class="flex items-start gap-4 rounded-xl border border-gray-100 bg-gray-50 p-4 transition hover:border-rose-100 hover:bg-rose-50/20 dark:border-gray-700 dark:bg-gray-700/30">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl text-sm font-bold {{ $color }} dark:opacity-80">
                                            {{ $initials ?: '?' }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $family->core_family_name }}</span>
                                                @if ($family->core_family_type)
                                                <span class="rounded-xl bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">{{ $family->core_family_type }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                @if ($family->core_family_gender)
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    {{ $family->core_family_gender }}
                                                </span>
                                                @endif
                                                @if ($isSby && $family->core_family_place_of_birth)
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    {{ $family->core_family_place_of_birth }}
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
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        </div>
                                        <p class="text-sm italic text-gray-400">No marital data available</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Emergency Contact --}}
                            <div x-show="activeTab === 'Emergency'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-900/10">
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
                                <div class="flex items-start gap-4 rounded-xl border border-cyan-100 bg-cyan-50/50 p-5 dark:border-cyan-900/40 dark:bg-cyan-900/10">
                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300">
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
                                            <span class="inline-flex items-center rounded-xl bg-cyan-100 px-2.5 py-0.5 text-xs font-semibold text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">
                                                {{ $applicant->relative_work_status }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <p class="text-sm italic text-gray-400">No relative information available</p>
                                </div>
                                @endif
                            </div>

                            {{-- Driver License --}}
                            <div x-show="activeTab === 'License'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                @forelse (($applicant->driverLicenses ?? collect()) as $license)
                                <div class="mb-3 last:mb-0 flex items-center justify-between rounded-xl border bg-gray-50 px-5 py-4 transition
                                    {{ $license->status ? 'border-green-200 hover:border-green-300 hover:bg-green-50/30 dark:border-green-800 dark:bg-green-900/10' : 'border-gray-200 hover:bg-gray-100/60 dark:border-gray-700 dark:bg-gray-700/30' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $license->status ? 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                            </svg>
                                        </div>
                                        <span class="font-semibold text-gray-800 dark:text-gray-100">
                                            SIM {{ $license->driver_license_descr ?? '—' }}
                                        </span>
                                    </div>
                                    @if ($license->status)
                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-green-100 px-3 py-1 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            <span class="h-1.5 w-1.5 rounded-xl bg-green-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-3 py-1 text-xs font-bold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                            <span class="h-1.5 w-1.5 rounded-xl bg-gray-400"></span> Inactive
                                        </span>
                                    @endif
                                </div>
                                @empty
                                <div class="py-10 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                                    </div>
                                    <p class="text-sm italic text-gray-400">No driver license data available</p>
                                </div>
                                @endforelse
                            </div>

                        </div>
                    </div>

                    {{-- ── TAB GROUP 3 (History) ─────────────────────────────────── --}}
                    <div x-data="{ activeTab: 'Illness' }" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">

                        <div class="border-b border-gray-100 bg-gray-50/70 px-3 py-2.5 dark:border-gray-700/60 dark:bg-gray-700/30">
                            <nav class="flex gap-1 overflow-x-auto">
                                @foreach ([
                                    ['key'=>'Illness',  'label'=>'Illness History'],
                                    ['key'=>'Criminal', 'label'=>'Criminal History'],
                                    ...($isSby ? [['key'=>'Accident', 'label'=>'Accident History']] : []),
                                ] as $t)
                                <button @click="activeTab = '{{ $t['key'] }}'"
                                    :class="activeTab === '{{ $t['key'] }}'
                                        ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30 font-semibold'
                                        : 'text-gray-500 hover:bg-white hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200'"
                                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm transition-all duration-150 focus:outline-none">
                                    {{ $t['label'] }}
                                </button>
                                @endforeach
                            </nav>
                        </div>

                        <div class="p-6">

                            {{-- Illness History --}}
                            <div x-show="activeTab === 'Illness'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="mb-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Do you have a history of severe illness?</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $yn(optional($applicant_additional)->has_severe_illness_history) }}</p>
                                </div>
                                <dl class="ap-dl grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Year</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->illness_year ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Illness Name</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->illness_name ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Duration</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->illness_duration ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Treatment Location</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->treatment_location ?: '—' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Criminal History --}}
                            <div x-show="activeTab === 'Criminal'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="mb-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Do you have a criminal history?</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $yn(optional($applicant_additional)->has_criminal_history) }}</p>
                                </div>
                                <dl class="ap-dl grid grid-cols-1 gap-5">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Description</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->incident_description ?: '—' }}</dd>
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-5">
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Year</dt>
                                            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->incident_year ?: '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Consequence</dt>
                                            <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->incident_consequence ?: '—' }}</dd>
                                        </div>
                                    </div>
                                </dl>
                            </div>

                            @if ($isSby)
                            {{-- Accident History --}}
                            <div x-show="activeTab === 'Accident'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="mb-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Do you have a history of traffic accidents?</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $yn(optional($applicant_additional)->has_traffic_accident_history) }}</p>
                                </div>
                                <dl class="ap-dl grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-3">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Year</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->accident_year ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Accident</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->accident_name ?: '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Impact</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->accident_impact ?: '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            @endif

                        </div>
                    </div>

                    @if ($isSby)
                    {{-- Consent & Availability --}}
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-500/15 dark:text-teal-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Consent &amp; Availability</h3>
                        </div>
                        <div class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach ([
                                ['label' => 'Available to join immediately?',            'field' => 'joining_date_availability'],
                                ['label' => 'Willing to undergo medical check-up?',      'field' => 'willing_for_medical_check'],
                                ['label' => 'Willing to resign if medically unfit?',     'field' => 'willing_to_resign_if_unfit'],
                                ['label' => 'Willing to register for NPWP?',             'field' => 'willing_to_make_npwp'],
                                ['label' => 'Allow reference/background check?',         'field' => 'allow_reference_check'],
                            ] as $q)
                            <div class="flex items-center justify-between px-6 py-3">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $q['label'] }}</p>
                                <span class="rounded-xl bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $yn(optional($applicant_additional)->{$q['field']}) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Application Questions --}}
                    <div x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                        <button type="button" @click="open = !open"
                            class="flex w-full items-center justify-between gap-2 border-b border-gray-100 px-6 py-4 text-left dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Application Questions</h3>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-collapse x-cloak class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach ([
                                ['label' => 'Tell us about yourself',                    'field' => 'about_me'],
                                ['label' => 'Why are you a good fit for this position?', 'field' => 'applied_position_description'],
                                ['label' => 'Achievements & accomplishments',            'field' => 'achievements_and_accomplishments'],
                                ['label' => 'Challenge & solution',                      'field' => 'challenges_and_solutions'],
                                ['label' => 'Life lessons learned',                      'field' => 'life_lessons_learned'],
                                ['label' => 'Role model',                                'field' => 'role_model'],
                                ['label' => '5-year career goals',                       'field' => 'five_year_career_goals'],
                            ] as $q)
                            <div class="px-6 py-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $q['label'] }}</p>
                                <p class="mt-1 text-sm text-gray-800 dark:text-gray-100">{{ optional($applicant_additional)->{$q['field']} ?: '—' }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- ── ATTACHMENT & REFERENCE ────────────────────────────────── --}}
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        {{-- Attachment --}}
                        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                            <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Attachment</h3>
                        </div>
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ([
                                    ['label' => 'Curriculum Vitae',  'type' => 'cv',       'url' => $cv,       'color' => 'bg-red-100 dark:bg-red-900/30'],
                                    ['label' => 'Transkrip Nilai',   'type' => 'transkip', 'url' => $transkip, 'color' => 'bg-amber-100 dark:bg-amber-900/30'],
                                    ['label' => 'Ijazah',            'type' => 'ijazah',   'url' => $ijazah,   'color' => 'bg-emerald-100 dark:bg-emerald-900/30'],
                                ] as $doc)
                                <div class="flex items-center justify-between px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $doc['color'] }} text-lg">&#128196;</span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $doc['label'] }}</p>
                                            <p class="text-xs text-gray-400">PDF Document</p>
                                        </div>
                                    </div>
                                    @if ($doc['url'])
                                        <div class="flex shrink-0 items-center gap-2">
                                            <a href="{{ route('careers.download', ['hash' => $hash, 'type' => $doc['type'], 'disposition' => 'inline']) }}"
                                                target="_blank" rel="noopener" title="Preview"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-indigo-600 text-indigo-600 transition hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('careers.download', ['hash' => $hash, 'type' => $doc['type']]) }}"
                                                class="rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700">
                                                Download
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs italic text-gray-400">No file</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Reference Information --}}
                        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                            <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700/60">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-300"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg></span>
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-gray-100">Reference Information</h3>
                        </div>
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($applicant_reference as $ref)
                                <div class="flex items-start gap-4 px-6 py-4">
                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300 text-sm font-bold ring-4 ring-cyan-50 dark:ring-cyan-900/20">
                                        {{ strtoupper(substr($ref->reference_name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $ref->reference_name }}</p>
                                            @if ($ref->reference_relation)
                                            <span class="rounded-full bg-cyan-100 px-2 py-0.5 text-[11px] font-semibold text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">{{ $ref->reference_relation }}</span>
                                            @endif
                                        </div>
                                        @if ($ref->reference_job_position || $ref->reference_company_name)
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $ref->reference_job_position }}
                                            @if ($ref->reference_job_position && $ref->reference_company_name)
                                            <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                            @endif
                                            <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $ref->reference_company_name }}</span>
                                        </p>
                                        @endif
                                        @if ($ref->reference_phone_number || ($isSby && $ref->reference_address))
                                        <div class="mt-2 flex flex-col gap-1">
                                            @if ($ref->reference_phone_number)
                                            <p class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                                </svg>
                                                {{ $ref->reference_phone_number }}
                                            </p>
                                            @endif
                                            @if ($isSby && $ref->reference_address)
                                            <p class="flex items-start gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                                </svg>
                                                <span class="min-w-0">{{ $ref->reference_address }}</span>
                                            </p>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="flex flex-col items-center gap-2 py-10 text-center">
                                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <p class="text-sm italic text-gray-400">No reference data available</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                </div>

            </div>
</div>

        <script src="https://unpkg.com/lucide@latest"></script>
        <script>if(typeof lucide !== 'undefined') lucide.createIcons();</script>
