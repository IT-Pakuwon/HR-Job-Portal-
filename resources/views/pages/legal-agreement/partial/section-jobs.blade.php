<div id="jobsSection" class="mt-4">

    <div class="dt-pretty overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-white/[0.06] dark:bg-white/[0.02]">

        <x-list-page-header title="Jobs"
            :subtitle="isset($kind) ? 'IFCA contracts waiting for an agreement' : 'Contracts waiting for an Agreement Follow Up'">
            <a href="#" id="btnExportJobs" class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 text-sm font-semibold text-green-700 transition hover:bg-green-100 dark:border-green-800/60 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30">
                <i class="fa-solid fa-file-excel"></i> Export to Excel
            </a>
        </x-list-page-header>

        {{-- Filters --}}
        <div class="border-b border-gray-100 bg-gray-50/60 px-5 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
            <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label class="agr-label" for="jobs_cpny_filter">Company</label>
                    <select id="jobs_cpny_filter" class="agr-input agr-select2">
                        <option value="">All Companies</option>
                        @foreach ($allCompanies as $c)
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_name }}</option>
                        @endforeach
                    </select>
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

                <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="btnApplyJobsFilter" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition bg-blue-600 font-semibold text-white hover:bg-blue-500">
                    <i class="fa-solid fa-filter"></i> Apply
                </button>
                <button type="button" id="btnResetJobsFilter" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
        <table id="jobsTable" class="w-full text-sm" @unless (isset($kind)) data-fu="1" @endunless>
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left" style="width:60px;">Action</th>
                    <th class="px-4 py-3 text-left">{{ isset($kind) ? 'Contract No' : 'No. PSM / OLA / Addendum' }}</th>
                    @unless (isset($kind))
                        <th class="px-4 py-3 text-left">Type</th>
                    @endunless
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Tenant No</th>
                    <th class="px-4 py-3 text-left">Trade Name</th>
                    <th class="px-4 py-3 text-left">Property Type</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    @unless (isset($kind))
                        <th class="px-4 py-3 text-left">Action</th>
                    @endunless
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        </div>

    </div>

</div>
