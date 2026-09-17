<div id="jobsSection" class="mt-4">

    {{-- Filters --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-white/[0.02]">

        <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-3 dark:border-white/[0.06]">
            <i class="fa-solid fa-sliders text-xs text-slate-400"></i>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Filters</p>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="agr-label" for="jobs_cpny_filter">Company</label>
                    <select id="jobs_cpny_filter" class="agr-input agr-select2">
                        <option value="">All Companies</option>
                        @foreach ($allCompanies as $c)
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="agr-label" for="jobs_tenant_no">Tenant No</label>
                    <input type="text" id="jobs_tenant_no" placeholder="e.g. A0000012345" class="agr-input" />
                </div>

                <div>
                    <label class="agr-label" for="jobs_trade_name">Trade Name</label>
                    <input type="text" id="jobs_trade_name" placeholder="e.g. STEAK 21" class="agr-input" />
                </div>

                @php
                    $propertyTypeLabels = ['OFF' => 'Office', 'MALL' => 'Mall', 'APT' => 'Apartment', 'HOTEL' => 'Hotel'];
                @endphp
                <div>
                    <label class="agr-label" for="jobs_property_type">Property Type</label>
                    <select id="jobs_property_type" class="agr-input agr-select2">
                        <option value="">All Property Types</option>
                        @foreach ($propertyTypes as $code)
                            <option value="{{ $code }}">{{ $propertyTypeLabels[$code] ?? $code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-white/[0.06]">
                <button type="button" id="btnApplyJobsFilter" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    <i class="fa-solid fa-filter"></i> Apply
                </button>
                <button type="button" id="btnResetJobsFilter" class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/[0.08] dark:text-slate-300 dark:hover:bg-white/[0.06]">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                <a href="#" id="btnExportJobs" class="ml-auto inline-flex h-10 items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 hover:bg-green-100 dark:border-green-800/60 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30">
                    <i class="fa-solid fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>

    </div>

    {{-- Table --}}
    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-white/[0.06] dark:bg-white/[0.02]">
        <table id="jobsTable" class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                <tr>
                    <th class="px-4 py-3 text-left" style="width:60px;">Action</th>
                    <th class="px-4 py-3 text-left">Contract No</th>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Tenant No</th>
                    <th class="px-4 py-3 text-left">Trade Name</th>
                    <th class="px-4 py-3 text-left">Property Type</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left" style="width:140px;">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>
