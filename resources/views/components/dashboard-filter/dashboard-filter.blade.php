@props([
    'companyGroups' => [],
    'areas' => [],
    'isGroupLocked' => false,
    'userGroupCpny' => null,
    'currentFilters' => [],
    'departments' => [],
    'companies' => [],
    'locations' => [],
    'ticketTypes' => [],
    'showDepartment' => true,
])

@php
    $isRecruitment = filled($companyGroups);
@endphp

@if($isRecruitment)
    {{-- ========================================================================
         RECRUITMENT DASHBOARD FILTER — pill-style segmented bar matching GM aesthetic
        ======================================================================== --}}
    <div id="recruitmentFilterWrap" class="w-full">
    <form method="GET" action="{{ route('recruitment.dashboard') }}"
          class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white shadow-sm sm:flex-row dark:border-slate-700/60 dark:bg-slate-900">

        {{-- Group 1: Filter items spread evenly across available width --}}
        <div class="flex flex-1 flex-wrap items-center divide-x divide-slate-200 border-b border-slate-200 sm:flex-nowrap sm:border-b-0 dark:divide-slate-700/60 dark:border-slate-700/60">

            {{-- Company Group --}}
            @if(count($companyGroups) > 0)
            <div class="flex flex-1 items-center justify-center gap-1.5 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <select id="filterGroupCpny" name="group_cpny"
                    class="w-full min-w-0 cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200 {{ $isGroupLocked ? 'opacity-60' : '' }}"
                    {{ $isGroupLocked ? 'disabled' : '' }}>
                    <option value="">All Groups</option>
                    @foreach($companyGroups as $g)
                        <option value="{{ $g }}" @selected(($currentFilters['group_cpny'] ?? '') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
                @if($isGroupLocked)
                    <input type="hidden" name="group_cpny" value="{{ $userGroupCpny }}" />
                @endif
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            @endif

            {{-- Area --}}
            @if(count($areas) > 0)
            <div class="flex flex-1 items-center justify-center gap-1.5 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
                <select id="filterArea" name="area"
                    class="w-full min-w-0 cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Areas</option>
                    @foreach($areas as $a)
                        <option value="{{ $a }}" @selected(($currentFilters['area'] ?? '') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            @endif

            {{-- Company --}}
            <div class="flex flex-1 items-center justify-center gap-1.5 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
                <select id="filterCompany" name="company"
                    class="w-full min-w-0 cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Companies</option>
                    @foreach($companies as $cpny)
                        <option value="{{ $cpny->cpnyid }}" @selected(($currentFilters['company'] ?? '') === $cpny->cpnyid)>
                            {{ $cpny->cpnyname }}
                        </option>
                    @endforeach
                </select>
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Department --}}
            <div class="flex flex-1 items-center justify-center gap-1.5 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <select id="filterDepartment" name="department"
                    class="w-full min-w-0 cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Depts</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" @selected(($currentFilters['department'] ?? '') === $dept->department_id)>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Location --}}
            <div class="flex flex-1 items-center justify-center gap-1.5 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <select id="filterLocation" name="location"
                    class="w-full min-w-0 cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" @selected(($currentFilters['location'] ?? '') === $loc)>{{ $loc }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Source --}}
            <div class="flex flex-1 items-center justify-center gap-1.5 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                </svg>
                <select id="filterSource" name="source"
                    class="w-full min-w-0 cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Sources</option>
                    <option value="career" @selected(($currentFilters['source'] ?? '') === 'career')>Job Applicant</option>
                    <option value="self" @selected(($currentFilters['source'] ?? '') === 'self')>Self Applicant</option>
                </select>
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Date range --}}
            <div class="flex flex-1 items-center justify-center gap-1 px-2 py-2">
                <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="date" name="from" value="{{ $currentFilters['from'] ?? '' }}"
                    class="w-full min-w-0 border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200 [color-scheme:light] dark:[color-scheme:dark]"
                    placeholder="From" />
                <span class="shrink-0 text-xs text-slate-400">–</span>
                <input type="date" name="to" value="{{ $currentFilters['to'] ?? '' }}"
                    class="w-full min-w-0 border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200 [color-scheme:light] dark:[color-scheme:dark]"
                    placeholder="To" />
            </div>

        </div>

        {{-- Group 2: Apply + Reset --}}
        <div class="flex items-center divide-x divide-slate-200 sm:border-l sm:border-slate-200 dark:divide-slate-700/60 dark:sm:border-slate-700/60">
            <button type="submit"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-violet-600 transition hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-500/10">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                Apply
            </button>

            @if(($currentFilters['from'] ?? '') || ($currentFilters['to'] ?? '') || ($currentFilters['department'] ?? '') || ($currentFilters['company'] ?? '') || ($currentFilters['location'] ?? '') || ($currentFilters['source'] ?? '') || ($currentFilters['group_cpny'] ?? '') || ($currentFilters['area'] ?? ''))
                <a href="{{ route('recruitment.dashboard') }}"
                    class="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800/50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
                    </svg>
                    Reset
                </a>
            @endif
        </div>

    </form>
    </div>

    <style>
        #recruitmentFilterWrap .select2-container { width: 100% !important; }
        #recruitmentFilterWrap .select2-selection--single {
            height: 32px; display: flex; align-items: center;
            border: none !important; background: transparent !important;
            padding: 0 1.25rem 0 0; font-size: 0.75rem;
        }
        #recruitmentFilterWrap .select2-container--open .select2-selection--single {
            box-shadow: none;
        }
        #recruitmentFilterWrap .select2-selection__rendered {
            padding: 0; line-height: 30px; color: #334155; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        #recruitmentFilterWrap .select2-selection__arrow { height: 30px; right: 0; }
        #recruitmentFilterWrap .select2-selection__arrow b {
            border-color: #94a3b8 transparent transparent transparent;
        }
        #recruitmentFilterWrap .select2-selection--single .select2-selection__clear {
            position: absolute !important; right: 18px !important;
            top: 50% !important; transform: translateY(-50%) !important;
            margin: 0 !important; font-size: 15px !important;
            font-weight: 400 !important; color: #94a3b8 !important;
            z-index: 1; display: none;
        }
        #recruitmentFilterWrap .select2-selection--single .select2-selection__clear:hover {
            color: #ef4444 !important;
        }
        #recruitmentFilterWrap .select2-dropdown {
            border-radius: 0.75rem; border: 1px solid #e2e8f0;
            overflow: hidden; margin-top: 4px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.05);
        }
        #recruitmentFilterWrap .select2-search__field {
            border-radius: 0.5rem; border: 1px solid #e2e8f0;
            padding: 0.375rem 0.5rem; font-size: 0.75rem; outline: none;
        }
        #recruitmentFilterWrap .select2-search__field:focus {
            border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.12);
        }
        #recruitmentFilterWrap .select2-results__option {
            font-size: 0.75rem; padding: 0.5rem 0.75rem;
            transition: background .1s;
        }
        #recruitmentFilterWrap .select2-results__option--highlighted[aria-selected] {
            background-color: #F5F3FF; color: #7C3AED;
        }
        #recruitmentFilterWrap .select2-results__option[aria-selected="true"] {
            background-color: #EDE9FE; color: #6D28D9; font-weight: 600;
        }

        .dark #recruitmentFilterWrap .select2-selection__rendered { color: #e2e8f0; }
        .dark #recruitmentFilterWrap .select2-selection__arrow b {
            border-color: #64748b transparent transparent transparent;
        }
        .dark #recruitmentFilterWrap .select2-dropdown {
            background-color: #1e293b; border-color: #475569;
        }
        .dark #recruitmentFilterWrap .select2-search__field {
            background-color: #334155; border-color: #475569; color: #e2e8f0;
        }
        .dark #recruitmentFilterWrap .select2-results__option { color: #e2e8f0; }
        .dark #recruitmentFilterWrap .select2-results__option--highlighted[aria-selected] {
            background-color: #4C1D95; color: #fff;
        }
        .dark #recruitmentFilterWrap .select2-results__option[aria-selected="true"] {
            background-color: #5B21B6; color: #fff;
        }
        .dark #recruitmentFilterWrap .select2-search__field:focus {
            border-color: #8B5CF6;
        }
    </style>
@else
    {{-- ========================================================================
         GM REPORT FILTER MODE (original)
         Usage: <x-dashboard-filter />
        ======================================================================== --}}

    {{-- ── Global styles for GM filter UI — emitted only once per page ─────── --}}
    @once
    <style>
        @keyframes gm-pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: .4; }
        }
        .gmPreset.is-active {
            border-color:     #8B5CF6 !important;
            background-color: #EDE9FE !important;
            color:            #7C3AED !important;
        }
        .gm-dept-item {
            display:     flex;
            align-items: center;
            gap:         8px;
            padding:     6px 8px;
            border-radius: 10px;
            cursor:      pointer;
            transition:  background 0.12s;
        }
        .gm-dept-item:hover { background: rgba(139, 92, 246, 0.08); }
        .gm-dept-item input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: #8B5CF6;
            cursor: pointer; flex-shrink: 0;
        }
        .gm-dept-item .dn {
            flex: 1; font-size: 11px; font-weight: 600;
            color: #374151; line-height: 1.3;
        }
        .gm-dept-item .di { font-size: 10px; font-family: monospace; color: #94A3B8; }
        .gm-dept-scroll::-webkit-scrollbar { width: 4px; }
        .gm-dept-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        .gm-page-btn {
            display: flex; align-items: center; justify-content: center;
            height: 28px; min-width: 28px; padding: 0 6px;
            border-radius: 8px; border: 1px solid #E2E8F0;
            font-size: 11px; font-weight: 600; color: #64748B;
            background: white; cursor: pointer; transition: all 0.12s;
        }
        .gm-page-btn:hover  { background: #F8FAFC; }
        .gm-page-btn.active { background: #8B5CF6; color: white; border-color: #8B5CF6; }
    </style>
    @endonce

    <div class="flex flex-1 flex-wrap items-center gap-2">
        <div class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white shadow-sm sm:flex-none sm:flex-row dark:border-slate-700/60 dark:bg-slate-900">

            {{-- Group 1: Company + Date --}}
            <div class="flex items-center divide-x divide-slate-200 border-b border-slate-200 sm:border-b-0 dark:divide-slate-700/60 dark:border-slate-700/60">

                <div id="gmCompanyLocked"
                    class="hidden items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span id="gmCompanyLockedText" class="uppercase tracking-wider"></span>
                </div>

                <div id="gmCompanyDropdown"
                    class="flex cursor-pointer items-center gap-1.5 px-3 py-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <svg class="h-3.5 w-3.5 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <select id="gmCompanyFilter"
                        class="cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                        <option value="">All Companies</option>
                    </select>
                    <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                @if(count($ticketTypes))
                <div class="flex cursor-pointer items-center gap-1.5 px-3 py-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <svg class="h-3.5 w-3.5 shrink-0 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <select id="gmTicketTypeFilter"
                        class="cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                        @foreach($ticketTypes as $tt)
                            @if(isset($tt['group']))
                                @if($loop->first || ($ticketTypes[$loop->index - 1]['group'] ?? null) !== $tt['group'])
                                    @if(!$loop->first) </optgroup> @endif
                                    <optgroup label="{{ $tt['group'] }}">
                                @endif
                                <option value="{{ $tt['value'] }}">{{ $tt['label'] }}</option>
                                @if($loop->last) </optgroup> @endif
                            @else
                                <option value="{{ $tt['value'] }}">{{ $tt['label'] }}</option>
                            @endif
                        @endforeach
                    </select>
                    <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                @endif

                <div class="relative">
                    <button id="gmDateBtn" type="button"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/50"
                        style="white-space:nowrap">
                        <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span id="gmDateLabel">This Year</span>
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="gmDatePanel"
                        class="absolute left-0 top-full z-50 mt-1.5 hidden w-72 max-w-[calc(100vw-1rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:right-0 sm:left-auto dark:border-slate-700/60 dark:bg-slate-900">
                        <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Quick Select</p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" data-preset="today" class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Today</button>
                            <button type="button" data-preset="this-week" class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">This Week</button>
                            <button type="button" data-preset="this-month" class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">This Month</button>
                            <button type="button" data-preset="last-month" class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Last Month</button>
                            <button type="button" data-preset="this-year" class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">This Year</button>
                            <button type="button" data-preset="last-year" class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Last Year</button>
                        </div>
                        <div class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-700/60">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Custom Range</p>
                            <div class="flex flex-col gap-2">
                                <div>
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">From</p>
                                    <input type="date" id="gmDateFrom"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                </div>
                                <div>
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">To</p>
                                    <input type="date" id="gmDateTo"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                </div>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <button id="gmClearCustom" type="button"
                                    class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                    Clear
                                </button>
                                <button id="gmApplyCustom" type="button"
                                    class="flex-1 rounded-xl bg-violet-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-700">
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Group 2: Department + Refresh --}}
            <div class="flex items-center divide-x divide-slate-200 sm:border-l sm:border-slate-200 dark:divide-slate-700/60 dark:sm:border-slate-700/60">

                @if($showDepartment)
                <div class="relative">
                    <button id="gmDeptBtn" type="button"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/50"
                        style="white-space:nowrap">
                        <svg class="h-3.5 w-3.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span id="gmDeptLabel">All Departments</span>
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="gmDeptPanel"
                        class="absolute left-0 top-full z-50 mt-1.5 hidden w-80 max-w-[calc(100vw-1rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:right-0 sm:left-auto dark:border-slate-700/60 dark:bg-slate-900">
                        <div class="relative mb-2">
                            <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input id="gmDeptSearch" type="text" placeholder="Search departments…"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-500">
                        </div>
                        <div class="mb-2 flex gap-1.5">
                            <button id="gmDeptSelectAll" type="button"
                                class="flex-1 rounded-lg bg-slate-100 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                Select All
                            </button>
                            <button id="gmDeptClear" type="button"
                                class="flex-1 rounded-lg bg-slate-100 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                Clear
                            </button>
                        </div>
                        <div id="gmDeptList"
                            class="gm-dept-scroll max-h-52 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50 p-1.5 dark:border-slate-700/60 dark:bg-slate-800/50">
                            <p class="py-4 text-center text-xs text-slate-400">Loading…</p>
                        </div>
                        <button id="gmDeptApply" type="button"
                            class="mt-3 w-full rounded-xl bg-violet-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-700">
                            Apply Filter
                        </button>
                    </div>
                </div>
                @endif

                <button id="gmRefreshBtn" type="button"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>

            </div>

        </div>
    </div>
@endif
