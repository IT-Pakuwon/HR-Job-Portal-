<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'companies' ? 'Companies' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── TABS ────────────────────────────────────────────────────────────── --}}
        <div>
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-0">
                <button type="button" id="tab-company"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400"
                    onclick="switchCpnyTab('company')">
                    🏢 Company
                </button>
                <button type="button" id="tab-site"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchCpnyTab('site')">
                    📍 Site
                </button>
                <button type="button" id="tab-businessunit"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchCpnyTab('businessunit')">
                    🏬 Business Unit
                </button>
                <button type="button" id="tab-companybudget"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchCpnyTab('companybudget')">
                    💰 Company Budget
                </button>
                <button type="button" id="tab-project"
                    class="cpnyTabBtn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchCpnyTab('project')">
                    🗂️ Project
                </button>
            </div>

            {{-- ── TAB 1: Company ──────────────────────────────────────────────── --}}
            <div id="panel-company"
                class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏢 Company
                        List</h2>
                    <button id="addCompanyBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Company
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="companiesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Company ID</th>
                                <th class="px-4 py-3 text-left font-medium">Company Name</th>
                                <th class="px-4 py-3 text-left font-medium">City</th>
                                <th class="px-4 py-3 text-left font-medium">Province</th>
                                <th class="px-4 py-3 text-left font-medium">Area</th>
                                <th class="px-4 py-3 text-left font-medium">Company Group</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 2: Site ─────────────────────────────────────────────────── --}}
            <div id="panel-site"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">📍 Site
                        List</h2>
                    <button id="addSiteBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Site
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="siteTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-8 px-2 py-3 text-center"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Company ID</th>
                                <th class="px-4 py-3 text-left font-medium">Site ID</th>
                                <th class="px-4 py-3 text-left font-medium">Site Name</th>
                                <th class="px-4 py-3 text-left font-medium">City</th>
                                <th class="w-24 px-4 py-3 text-left font-medium">Default</th>
                                <th class="w-24 px-4 py-3 text-left font-medium">Parking</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 3: Business Unit ───────────────────────────────────────── --}}
            <div id="panel-businessunit"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏬 Business
                        Unit List</h2>
                    <button id="addBusinessUnitBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Business Unit
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 px-5 pt-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Company</label>
                        <select id="filterBuCompany" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                            <option value="">All Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->cpny_id }}">
                                    {{ $company->cpny_id }} - {{ $company->cpny_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="button" id="btnBuFilter"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Filter
                        </button>

                        <button type="button" id="btnBuResetFilter"
                            class="rounded-lg bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="relative mt-4 overflow-hidden">
                    <table id="businessUnitTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Business Unit ID</th>
                                <th class="px-4 py-3 text-left font-medium">Company</th>
                                <th class="px-4 py-3 text-left font-medium">Business Unit Name</th>
                                <th class="px-4 py-3 text-left font-medium">IFCA Entity</th>
                                <th class="px-4 py-3 text-left font-medium">Solomon Company</th>
                                <th class="px-4 py-3 text-left font-medium">Solomon Allocation</th>
                                <th class="px-4 py-3 text-left font-medium">Integration Type</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 4: Company Budget ──────────────────────────────────────── --}}
            <div id="panel-companybudget"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">💰 Company
                        Budget List</h2>
                    <button id="addCompanyBudgetBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Company Budget
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="companyBudgetTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Company Group</th>
                                <th class="px-4 py-3 text-left font-medium">Company ID</th>
                                <th class="px-4 py-3 text-left font-medium">Budget Project ID</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 5: Project ──────────────────────────────────────────────── --}}
            <div id="panel-project"
                class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🗂️ Project
                        List</h2>
                    <button id="addProjectBtn"
                        class="inline-flex h-9 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Project
                    </button>
                </div>

                <div class="relative overflow-hidden">
                    <table id="projectTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-10 px-4 py-3"></th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Project ID</th>
                                <th class="px-4 py-3 text-left font-medium">Project Name</th>
                                <th class="px-4 py-3 text-left font-medium">Company</th>
                                <th class="px-4 py-3 text-left font-medium">Area</th>
                                <th class="px-4 py-3 text-left font-medium">Company Group</th>
                                <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal: Company -->
        <div id="companyModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Company</h2>
                <form id="companyForm">
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div class="mb-3 md:col-span-1">
                            <label class="block text-gray-700 dark:text-white">Company Name</label>
                            <input type="text" id="cpny_name" name="cpny_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                            <input type="text" id="address_line1" name="address_line1"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                            <input type="text" id="address_line2" name="address_line2"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">City</label>
                            <input type="text" id="city" name="city"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Province</label>
                            <input type="text" id="province" name="province"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Postal Code</label>
                            <input type="text" id="postalcode" name="postalcode"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Phone</label>
                            <input type="text" id="phone" name="phone"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Area</label>
                            <select id="area_id" name="area_id" class="company-select2 w-full">
                                <option value="">-- Select Area --</option>
                                <option value="Yogyakarta">Yogyakarta</option>
                                <option value="Solo">Solo</option>
                                <option value="Surabaya">Surabaya</option>
                                <option value="Batam">Batam</option>
                                <option value="Semarang">Semarang</option>
                                <option value="Bali">Bali</option>
                                <option value="Jakarta">Jakarta</option>
                                <option value="Bekasi">Bekasi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company Group</label>
                            <select id="group_cpny_id" name="group_cpny_id" class="company-select2 w-full">
                                <option value="">-- Select Company Group --</option>
                                <option value="JKT">JKT</option>
                                <option value="SBY">SBY</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Fax</label>
                            <input type="text" id="fax" name="fax"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Tax Registration</label>
                            <input type="text" id="tax_registration" name="tax_registration"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Tax Address</label>
                            <input type="text" id="tax_address_line" name="tax_address_line"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Warehouse Note</label>
                            <textarea id="warehouse_note" name="warehouse_note" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700"
                                rows="2"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Site -->
        <div id="siteModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalSiteTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Site</h2>
                <form id="siteForm">
                    @csrf
                    <input type="hidden" id="site_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select id="site_cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Company --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Site ID</label>
                            <input type="text" id="site_siteid" name="siteid"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Site Name</label>
                            <input type="text" id="site_site_name" name="site_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 1</label>
                            <input type="text" id="site_addr1" name="site_addr1"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Address Line 2</label>
                            <input type="text" id="site_addr2" name="site_addr2"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">City</label>
                            <input type="text" id="site_city" name="site_city"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Province</label>
                            <input type="text" id="site_province" name="site_province"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Postal Code</label>
                            <input type="text" id="site_postalcode" name="site_postalcode"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">PIC</label>
                            <input type="text" id="site_pic" name="site_pic"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Phone</label>
                            <input type="text" id="site_phone" name="site_phone"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Fax</label>
                            <input type="text" id="site_fax" name="site_fax"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" id="site_default" name="site_default" value="1"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
                            <label for="site_default" class="text-gray-700 dark:text-white">Default Site</label>
                        </div>
                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" id="site_parking" name="site_parking" value="1"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-700">
                            <label for="site_parking" class="text-gray-700 dark:text-white">Parking Site</label>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeSiteModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Business Unit -->
        <div id="businessUnitModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalBuTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Business
                    Unit</h2>

                <form id="businessUnitForm">
                    @csrf
                    <input type="hidden" id="bu_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Business Unit ID</label>
                            <input type="text" id="business_unit_id" name="business_unit_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select id="bu_cpny_id" name="cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                                <option value="">-- Select Company --</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->cpny_id }}">
                                        {{ $company->cpny_id }} - {{ $company->cpny_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Business Unit Name</label>
                            <input type="text" id="business_unit_name" name="business_unit_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">IFCA Entity Code</label>
                            <input type="text" id="ifca_entity_cd" name="ifca_entity_cd"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Integration Type</label>
                            <select id="integration_type" name="integration_type"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">-- Select Type --</option>
                                <option value="IFCA">IFCA</option>
                                <option value="SOLOMON">SOLOMON</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Solomon Company ID</label>
                            <input type="text" id="solomon_cpny_id" name="solomon_cpny_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Solomon Allocation Code</label>
                            <input type="text" id="solomon_allocation_cd" name="solomon_allocation_cd"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeBuModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Company Budget -->
        <div id="companyBudgetModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="relative w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalCompanyBudgetTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add
                    Company Budget</h2>

                <form id="companyBudgetForm">
                    @csrf
                    <input type="hidden" id="cb_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company Group</label>
                            <select id="cb_group_cpny_id" name="group_cpny_id" class="cb-select2 w-full">
                                <option value="">-- Select Company Group --</option>
                                <option value="JKT">JKT</option>
                                <option value="SBY">SBY</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select id="cb_cpnyid" name="cpnyid" class="cb-select2 w-full" required>
                                <option value="">-- Select Company --</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->cpny_id }}" data-group="{{ $company->group_cpny_id }}">
                                        {{ $company->cpny_id }} - {{ $company->cpny_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Budget Project</label>
                            <select id="cb_budget_project_id" name="budget_project_id" class="cb-select2 w-full" required>
                                <option value="">-- Select Budget Project --</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->project_id }}">
                                        {{ $project->project_id }} - {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeCompanyBudgetModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Project -->
        <div id="projectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="relative w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalProjectTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add
                    Project</h2>

                <form id="projectForm">
                    @csrf
                    <input type="hidden" id="proj_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Project ID</label>
                            <input type="text" id="proj_project_id" name="project_id"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Project Name</label>
                            <input type="text" id="proj_project_name" name="project_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="mb-3 md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <input type="text" id="proj_cpny_name" name="cpny_name"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Area</label>
                            <select id="proj_area_id" name="area_id" class="proj-select2 w-full">
                                <option value="">-- Select Area --</option>
                                <option value="Yogyakarta">Yogyakarta</option>
                                <option value="Solo">Solo</option>
                                <option value="Surabaya">Surabaya</option>
                                <option value="Batam">Batam</option>
                                <option value="Semarang">Semarang</option>
                                <option value="Bali">Bali</option>
                                <option value="Jakarta">Jakarta</option>
                                <option value="Bekasi">Bekasi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-gray-700 dark:text-white">Company Group</label>
                            <select id="proj_group_cpny_id" name="group_cpny_id" class="proj-select2 w-full">
                                <option value="">-- Select Company Group --</option>
                                <option value="JKT">JKT</option>
                                <option value="SBY">SBY</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeProjectModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="loadingOverlay"
        class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() {
            $('#loadingOverlay').removeClass('hidden');
        }

        function hideLoading() {
            $('#loadingOverlay').addClass('hidden');
        }

        const initedCpnyTabs = { company: false, site: false, businessunit: false, companybudget: false, project: false };
        const cpnyActiveClasses = 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400';
        const cpnyInactiveClasses = 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400';

        function switchCpnyTab(tab) {
            const panels = { company: '#panel-company', site: '#panel-site', businessunit: '#panel-businessunit', companybudget: '#panel-companybudget', project: '#panel-project' };
            const btns   = { company: '#tab-company',   site: '#tab-site',   businessunit: '#tab-businessunit',   companybudget: '#tab-companybudget',   project: '#tab-project' };

            Object.keys(panels).forEach(function(key) {
                const isActive = key === tab;
                $(panels[key]).toggleClass('hidden', !isActive);
                $(btns[key])
                    .toggleClass(cpnyActiveClasses, isActive)
                    .toggleClass(cpnyInactiveClasses, !isActive);
            });

            if (!initedCpnyTabs[tab]) {
                initedCpnyTabs[tab] = true;
                if (tab === 'company') initCompanyTable();
                if (tab === 'site') initSiteTable();
                if (tab === 'businessunit') initBusinessUnitTable();
                if (tab === 'companybudget') initCompanyBudgetTable();
                if (tab === 'project') initProjectTable();
            } else if (tab === 'company' && window.companiesTable) {
                window.companiesTable.columns.adjust().responsive.recalc();
            } else if (tab === 'site' && window.siteTable) {
                window.siteTable.columns.adjust().responsive.recalc();
            } else if (tab === 'businessunit' && window.businessUnitTable) {
                window.businessUnitTable.columns.adjust().responsive.recalc();
            } else if (tab === 'companybudget' && window.companyBudgetTable) {
                window.companyBudgetTable.columns.adjust().responsive.recalc();
            } else if (tab === 'project' && window.projectTable) {
                window.projectTable.columns.adjust().responsive.recalc();
            }
        }

        $(document).ready(function() {

            // =========================================================
            // Company
            // =========================================================
            window.initCompanyTable = function() {
                window.companiesTable = $('#companiesTable').DataTable({
                    ajax: "{{ route('companies.json') }}",
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },

                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Company',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Company',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                            <div class="flex justify-center space-x-2">
                                                <label class="switch">
                                                    <input type="checkbox" class="toggleCompanyStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <button class="editCompanyBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        `;
                            }
                        },
                        {
                            data: 'cpny_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'cpny_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'city',
                            className: 'no-pointer'
                        },
                        {
                            data: 'province',
                            className: 'no-pointer'
                        },
                        {
                            data: 'area_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'group_cpny_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                    '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            // Init select2 for area / company group (single-select, inside modal)
            $('.company-select2').each(function() {
                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#companyModal')
                });
            });

            // Init select2 for company group (inside company budget modal)
            $('.cb-select2').each(function() {
                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#companyBudgetModal')
                });
            });

            // Init select2 for area / company group (inside project modal)
            $('.proj-select2').each(function() {
                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    dropdownParent: $('#projectModal')
                });
            });

            // Filter #cb_cpnyid options by the selected Company Group
            const cbCompanyOptions = $('#cb_cpnyid option').clone();

            function filterCbCompanyByGroup(groupId, keepValue) {
                const $select = $('#cb_cpnyid');
                const selected = keepValue !== undefined ? keepValue : $select.val();

                $select.empty().append(
                    cbCompanyOptions.filter(function() {
                        return $(this).val() === '' || !groupId || $(this).data('group') === groupId;
                    }).clone()
                );

                $select.val(selected).trigger('change');
            }

            $(document).on('change', '#cb_group_cpny_id', function() {
                filterCbCompanyByGroup($(this).val());
            });

            // Add
            $('#addCompanyBtn').click(function() {
                $('#modalTitle').text("Add Company");
                $('#companyForm')[0].reset();
                $('#id').val('');
                $('.company-select2').val('').trigger('change');
                $('#companyModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editCompanyBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/companies/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Company");
                    $('#id').val(c.id);
                    $('#cpny_id').val(c.cpny_id);
                    $('#cpny_name').val(c.cpny_name);
                    $('#address_line1').val(c.address_line1);
                    $('#address_line2').val(c.address_line2);
                    $('#city').val(c.city);
                    $('#province').val(c.province);
                    $('#postalcode').val(c.postalcode);
                    $('#area_id').val(c.area_id).trigger('change');
                    $('#group_cpny_id').val(c.group_cpny_id).trigger('change');
                    $('#phone').val(c.phone);
                    $('#fax').val(c.fax);
                    $('#tax_registration').val(c.tax_registration);
                    $('#tax_address_line').val(c.tax_address_line);
                    $('#warehouse_note').val(c.warehouse_note);

                    $('#companyModal').removeClass('hidden');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load company data'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status (company)
            $(document).on('change', '.toggleCompanyStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/companies/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.companiesTable.ajax.reload(null, false);
                    }
                });
            });

            // Submit form (create / update company)
            $('#companyForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/companies/${id}` : "{{ route('companies.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('companyForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#companyForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#companyForm button[type="submit"]').prop('disabled', false);

                        $('#companyModal').addClass('hidden');
                        window.companiesTable.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Company saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#companyForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data company'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#companyForm')[0].reset();
                $('#id').val('');
                $('.company-select2').val('').trigger('change');
                $('#companyModal').addClass('hidden');
            });

            // =========================================================
            // Site
            // =========================================================
            window.initSiteTable = function() {
                // populate company dropdown for the site form
                $.getJSON("{{ route('companies.json') }}", function(resp) {
                    let $select = $('#site_cpny_id');
                    (resp.data || []).forEach(function(c) {
                        $select.append(`<option value="${c.cpny_id}">${c.cpny_id} — ${c.cpny_name}</option>`);
                    });
                });

                window.siteTable = $('#siteTable').DataTable({
                    ajax: {
                        url: "{{ route('companies.sites.json') }}",
                        type: "GET",
                        dataSrc: 'data'
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Site',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Site',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, {
                            data: 'id',
                            className: 'text-center',
                            render: function(data, type, row) {
                                return `
                                            <div class="flex justify-center space-x-2">
                                                <label class="switch">
                                                    <input type="checkbox" class="toggleSiteStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                                    <span class="slider round"></span>
                                                </label>
                                                <button class="editSiteBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        `;
                            }
                        },
                        {
                            data: 'cpny_id'
                        },
                        {
                            data: 'siteid'
                        },
                        {
                            data: 'site_name'
                        },
                        {
                            data: 'site_city'
                        },
                        {
                            data: 'site_default',
                            className: 'text-center',
                            render: function(data) {
                                return data ? '✅' : '';
                            }
                        },
                        {
                            data: 'site_parking',
                            className: 'text-center',
                            render: function(data) {
                                return data ? '✅' : '';
                            }
                        },
                        {
                            data: 'status',
                            className: 'text-center',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="bg-green-300/30 text-green-600 font-semibold px-4 py-1 rounded">Active</span>' :
                                    '<span class="bg-red-300/30 text-red-600 font-semibold px-4 py-1 rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            // Add
            $('#addSiteBtn').click(function() {
                $('#modalSiteTitle').text("Add Site");
                $('#siteForm')[0].reset();
                $('#site_id').val('');
                $('#siteModal').removeClass('hidden');
            });

            // Edit
            $(document).on('click', '.editSiteBtn', function() {
                let id = $(this).data('id');

                $('#modalSiteTitle').text("Loading...");
                $('#siteForm')[0].reset();
                $('#site_id').val(id);
                $('#siteModal').removeClass('hidden');
                showLoading();

                $.get(`/companies/sites/${id}/edit`, function(data) {
                    $('#modalSiteTitle').text("Edit Site");

                    $('#site_cpny_id').val(data.cpny_id);
                    $('#site_siteid').val(data.siteid);
                    $('#site_site_name').val(data.site_name);
                    $('#site_addr1').val(data.site_addr1);
                    $('#site_addr2').val(data.site_addr2);
                    $('#site_city').val(data.site_city);
                    $('#site_province').val(data.site_province);
                    $('#site_postalcode').val(data.site_postalcode);
                    $('#site_pic').val(data.site_pic);
                    $('#site_phone').val(data.site_phone);
                    $('#site_fax').val(data.site_fax);
                    $('#site_default').prop('checked', !!data.site_default);
                    $('#site_parking').prop('checked', !!data.site_parking);

                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#siteModal').addClass('hidden');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data site'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status (site)
            $(document).on('change', '.toggleSiteStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/companies/sites/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.siteTable.ajax.reload(null, false);
                    }
                });
            });

            // Submit (create / update site)
            $('#siteForm').submit(function(e) {
                e.preventDefault();

                let id = $('#site_id').val();
                let url = id ? `/companies/sites/${id}` : "{{ route('companies.sites.store') }}";
                let method = 'POST';
                let formData = new FormData(document.getElementById('siteForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#siteForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#siteForm button[type="submit"]').prop('disabled', false);

                        $('#siteModal').addClass('hidden');
                        $('#siteForm')[0].reset();
                        $('#site_id').val('');
                        window.siteTable.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Site saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#siteForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data site';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeSiteModal').click(function() {
                $('#siteForm')[0].reset();
                $('#site_id').val('');
                $('#siteModal').addClass('hidden');
            });

            // =========================================================
            // Business Unit
            // =========================================================
            window.initBusinessUnitTable = function() {
                window.businessUnitTable = $('#businessUnitTable').DataTable({
                    ajax: {
                        url: "{{ route('business-units.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.cpny_id = $('#filterBuCompany').val();
                        },
                        dataSrc: "data",
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                        }
                    },
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [
                        {
                            targets: 0,
                            width: '28px',
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false
                        },
                        {
                            targets: 1,
                            orderable: false,
                            searchable: false
                        }
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Business Unit',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5, 6, 7, 8, 9]
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Business Unit',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: [2, 3, 4, 5, 6, 7, 8, 9]
                            }
                        }
                    ],
                    columns: [
                        {
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleBuStatus"
                                                data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editBusinessUnitBtn bg-blue-500 text-white px-2 py-1 rounded"
                                            data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'business_unit_id',
                            className: 'no-pointer'
                        },
                        {
                            data: null,
                            className: 'no-pointer',
                            render: function(data, type, row) {
                                return `${row.cpny_id ?? '-'}${row.cpny_name ? ' - ' + row.cpny_name : ''}`;
                            }
                        },
                        {
                            data: 'business_unit_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'ifca_entity_cd',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'solomon_cpny_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'solomon_allocation_cd',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'integration_type',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer text-center',
                            render: function(data) {
                                return data === 'A'
                                    ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            $('#btnBuFilter').click(function() {
                showLoading();
                window.businessUnitTable.ajax.reload(function() {
                    hideLoading();
                }, true);
            });

            $('#btnBuResetFilter').click(function() {
                $('#filterBuCompany').val('');
                showLoading();
                window.businessUnitTable.ajax.reload(function() {
                    hideLoading();
                }, true);
            });

            $('#addBusinessUnitBtn').click(function() {
                $('#modalBuTitle').text("Add Business Unit");
                $('#businessUnitForm')[0].reset();
                $('#bu_id').val('');
                $('#businessUnitModal').removeClass('hidden').addClass('flex');
            });

            $(document).on('click', '.editBusinessUnitBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/business-units/${id}/edit`, function(c) {
                    $('#modalBuTitle').text("Edit Business Unit");
                    $('#bu_id').val(c.id);
                    $('#business_unit_id').val(c.business_unit_id);
                    $('#bu_cpny_id').val(c.cpny_id);
                    $('#business_unit_name').val(c.business_unit_name);
                    $('#ifca_entity_cd').val(c.ifca_entity_cd);
                    $('#solomon_cpny_id').val(c.solomon_cpny_id);
                    $('#solomon_allocation_cd').val(c.solomon_allocation_cd);
                    $('#integration_type').val(c.integration_type);

                    $('#businessUnitModal').removeClass('hidden').addClass('flex');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data business unit'
                    });

                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', '.toggleBuStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/business-units/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.businessUnitTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal update status'
                        });
                        window.businessUnitTable.ajax.reload(null, false);
                    }
                });
            });

            $('#businessUnitForm').submit(function(e) {
                e.preventDefault();

                let id = $('#bu_id').val();
                let url = id ? `/business-units/${id}` : "{{ route('business-units.store') }}";
                let formData = new FormData(document.getElementById('businessUnitForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#businessUnitForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#businessUnitForm button[type="submit"]').prop('disabled', false);

                        $('#businessUnitModal').addClass('hidden').removeClass('flex');
                        $('#businessUnitForm')[0].reset();
                        $('#bu_id').val('');
                        window.businessUnitTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Business unit saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#businessUnitForm button[type="submit"]').prop('disabled', false);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan data business unit'
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeBuModal').click(function() {
                $('#businessUnitForm')[0].reset();
                $('#bu_id').val('');
                $('#businessUnitModal').addClass('hidden').removeClass('flex');
            });

            // =========================================================
            // Company Budget
            // =========================================================
            window.initCompanyBudgetTable = function() {
                window.companyBudgetTable = $('#companyBudgetTable').DataTable({
                    ajax: "{{ route('company-budgets.json') }}",
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Company Budget',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Company Budget',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleCompanyBudgetStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editCompanyBudgetBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="deleteCompanyBudgetBtn bg-red-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'group_cpny_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'cpnyid',
                            className: 'no-pointer'
                        },
                        {
                            data: 'budget_project_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                    '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            // Add
            $('#addCompanyBudgetBtn').click(function() {
                $('#modalCompanyBudgetTitle').text("Add Company Budget");
                $('#companyBudgetForm')[0].reset();
                $('#cb_id').val('');
                $('.cb-select2').val('').trigger('change');
                $('#companyBudgetModal').removeClass('hidden').addClass('flex');
            });

            // Edit
            $(document).on('click', '.editCompanyBudgetBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/company-budgets/${id}/edit`, function(c) {
                    $('#modalCompanyBudgetTitle').text("Edit Company Budget");
                    $('#cb_id').val(c.id);
                    $('#cb_group_cpny_id').val(c.group_cpny_id).trigger('change');
                    $('#cb_cpnyid').val(c.cpnyid).trigger('change');
                    $('#cb_budget_project_id').val(c.budget_project_id).trigger('change');

                    $('#companyBudgetModal').removeClass('hidden').addClass('flex');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data company budget'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status (company budget)
            $(document).on('change', '.toggleCompanyBudgetStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/company-budgets/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.companyBudgetTable.ajax.reload(null, false);
                    }
                });
            });

            // Delete (company budget)
            $(document).on('click', '.deleteCompanyBudgetBtn', function() {
                let id = $(this).data('id');

                Swal.fire({
                    icon: 'warning',
                    title: 'Delete company budget?',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#dc2626'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    showLoading();

                    $.ajax({
                        url: `/company-budgets/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function() {
                            hideLoading();
                            window.companyBudgetTable.ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            hideLoading();

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menghapus company budget'
                            });

                            console.error(xhr.responseText);
                        }
                    });
                });
            });

            // Submit form (create / update company budget)
            $('#companyBudgetForm').submit(function(e) {
                e.preventDefault();

                let id = $('#cb_id').val();
                let url = id ? `/company-budgets/${id}` : "{{ route('company-budgets.store') }}";
                let formData = new FormData(document.getElementById('companyBudgetForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#companyBudgetForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#companyBudgetForm button[type="submit"]').prop('disabled', false);

                        $('#companyBudgetModal').addClass('hidden').removeClass('flex');
                        $('#companyBudgetForm')[0].reset();
                        $('#cb_id').val('');
                        window.companyBudgetTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Company budget saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#companyBudgetForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data company budget';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeCompanyBudgetModal').click(function() {
                $('#companyBudgetForm')[0].reset();
                $('#cb_id').val('');
                $('.cb-select2').val('').trigger('change');
                $('#companyBudgetModal').addClass('hidden').removeClass('flex');
            });

            // =========================================================
            // Project
            // =========================================================
            window.initProjectTable = function() {
                window.projectTable = $('#projectTable').DataTable({
                    ajax: "{{ route('budget-projects.json') }}",
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'Project',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'Project',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleProjectStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editProjectBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="deleteProjectBtn bg-red-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'project_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'project_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'cpny_name',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'area_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'group_cpny_id',
                            className: 'no-pointer',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="w-full max-w-25 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Active</span>' :
                                    '<span class="w-full max-w-25 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded">Inactive</span>';
                            }
                        }
                    ]
                });
            };

            // Add
            $('#addProjectBtn').click(function() {
                $('#modalProjectTitle').text("Add Project");
                $('#projectForm')[0].reset();
                $('#proj_id').val('');
                $('.proj-select2').val('').trigger('change');
                $('#projectModal').removeClass('hidden').addClass('flex');
            });

            // Edit
            $(document).on('click', '.editProjectBtn', function() {
                let id = $(this).data('id');

                showLoading();

                $.get(`/budget-projects/${id}/edit`, function(p) {
                    $('#modalProjectTitle').text("Edit Project");
                    $('#proj_id').val(p.id);
                    $('#proj_project_id').val(p.project_id);
                    $('#proj_project_name').val(p.project_name);
                    $('#proj_cpny_name').val(p.cpny_name);
                    $('#proj_area_id').val(p.area_id).trigger('change');
                    $('#proj_group_cpny_id').val(p.group_cpny_id).trigger('change');

                    $('#projectModal').removeClass('hidden').addClass('flex');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data project'
                    });

                    console.error(xhr.responseText);
                });
            });

            // Toggle status (project)
            $(document).on('change', '.toggleProjectStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/budget-projects/${id}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        window.projectTable.ajax.reload(null, false);
                    }
                });
            });

            // Delete (project)
            $(document).on('click', '.deleteProjectBtn', function() {
                let id = $(this).data('id');

                Swal.fire({
                    icon: 'warning',
                    title: 'Delete project?',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#dc2626'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    showLoading();

                    $.ajax({
                        url: `/budget-projects/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function() {
                            hideLoading();
                            window.projectTable.ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            hideLoading();

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menghapus project'
                            });

                            console.error(xhr.responseText);
                        }
                    });
                });
            });

            // Submit form (create / update project)
            $('#projectForm').submit(function(e) {
                e.preventDefault();

                let id = $('#proj_id').val();
                let url = id ? `/budget-projects/${id}` : "{{ route('budget-projects.store') }}";
                let formData = new FormData(document.getElementById('projectForm'));

                if (id) {
                    formData.append('_method', 'PUT');
                }

                showLoading();
                $('#projectForm button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        $('#projectForm button[type="submit"]').prop('disabled', false);

                        $('#projectModal').addClass('hidden').removeClass('flex');
                        $('#projectForm')[0].reset();
                        $('#proj_id').val('');
                        window.projectTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Project saved successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#projectForm button[type="submit"]').prop('disabled', false);

                        let msg = 'Gagal menyimpan data project';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors)
                                .map(arr => arr.join(', '))
                                .join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });

                        console.error(xhr.responseText);
                    }
                });
            });

            $('#closeProjectModal').click(function() {
                $('#projectForm')[0].reset();
                $('#proj_id').val('');
                $('.proj-select2').val('').trigger('change');
                $('#projectModal').addClass('hidden').removeClass('flex');
            });

            // init first (visible) tab
            initCompanyTable();
            initedCpnyTabs.company = true;
        });
    </script>
</x-app-layout>
