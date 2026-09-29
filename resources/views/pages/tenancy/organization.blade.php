<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2" x-data="{ activeTab: 'site' }">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <nav class="flex border-b border-gray-100 dark:border-white/[0.06]">
                <button type="button" @click="activeTab = 'site'; $nextTick(() => tsAdjustTable('site'))"
                    :class="activeTab === 'site' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Site
                </button>
                <button type="button" @click="activeTab = 'company'; $nextTick(() => tsAdjustTable('company'))"
                    :class="activeTab === 'company' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Company
                </button>
                <button type="button" @click="activeTab = 'department'; $nextTick(() => tsAdjustTable('department'))"
                    :class="activeTab === 'department' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Department
                </button>
            </nav>

            <!-- SITE PANEL -->
            <div x-show="activeTab === 'site'" x-cloak>
                <div class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏙️ Site List</h2>
                    <button id="addSiteBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Site
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="sitesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Site ID</th>
                                <th class="px-4 py-3 text-left font-medium">Site Name</th>
                                <th class="px-4 py-3 text-left font-medium">Company</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- COMPANY PANEL -->
            <div x-show="activeTab === 'company'" x-cloak>
                <div class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🏢 Company List</h2>
                    <button id="addCompanyBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Company
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="companiesTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Company Name</th>
                                <th class="px-4 py-3 text-left font-medium">City</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- DEPARTMENT PANEL -->
            <div x-show="activeTab === 'department'" x-cloak>
                <div class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">🗂️ Department List</h2>
                    <button id="addDepartmentBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add Department
                    </button>
                </div>
                <div class="relative overflow-hidden p-2">
                    <table id="departmentsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                                <th class="px-4 py-3 text-left font-medium">Doctype</th>
                                <th class="px-4 py-3 text-left font-medium">Site</th>
                                <th class="px-4 py-3 text-left font-medium">Department</th>
                                <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SITE MODAL -->
        <div id="siteModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-city"></i>
                        </span>
                        <div>
                            <h2 id="siteModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Site</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Site profile &amp; company assignment</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="siteModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="siteForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="site_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Site Info</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site ID <span class="text-red-500">*</span></label>
                                    <input type="text" id="site_siteid" name="siteid" maxlength="5"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site Name <span class="text-red-500">*</span></label>
                                    <input type="text" id="site_sitename" name="sitename"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site Type</label>
                                    <input type="text" id="site_sitetype" name="sitetype"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Company <span class="text-red-500">*</span></label>
                                    <select id="site_companyid" name="companyid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Company --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 dark:border-white/[0.06]">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Contact</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site Company Name</label>
                                    <input type="text" id="site_sitecompanyname" name="sitecompanyname"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                    <input type="text" id="site_sitephone" name="sitephone"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Fax</label>
                                    <input type="text" id="site_sitefax" name="sitefax"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
                                    <textarea id="site_siteaddress" name="siteaddress" rows="2"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]"></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="siteModal">Cancel</button>
                        <button type="submit" id="siteSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- COMPANY MODAL -->
        <div id="companyModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-building"></i>
                        </span>
                        <div>
                            <h2 id="companyModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Company</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Parent company profile</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="companyModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="companyForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="cmp_id" name="id">
                    <div class="grid flex-1 grid-cols-1 gap-4 overflow-y-auto px-6 py-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" id="cmp_companyname" name="companyname"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">City</label>
                            <input type="text" id="cmp_companycity" name="companycity"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="companyModal">Cancel</button>
                        <button type="submit" id="companySaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DEPARTMENT MODAL -->
        <div id="departmentModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-sitemap"></i>
                        </span>
                        <div>
                            <h2 id="departmentModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add Department</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Department under a site &amp; doctype</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="departmentModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="departmentForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="dep_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Assignment</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Doctype <span class="text-red-500">*</span></label>
                                    <select id="dep_doctype" name="doctype" class="ts-select2 w-full" required>
                                        <option value="">-- Select Doctype --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site <span class="text-red-500">*</span></label>
                                    <select id="dep_siteid" name="siteid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Site --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 dark:border-white/[0.06]">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Department Info</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Department ID <span class="text-red-500">*</span></label>
                                    <select id="dep_departmentid" name="departmentid" class="ts-select2 w-full" required>
                                        <option value="">-- Select Department ID --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Department Name</label>
                                    <select id="dep_departmentname" name="departmentname" class="ts-select2 w-full">
                                        <option value="">-- Select Department Name --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Template</label>
                                    <input type="text" id="dep_template" name="template"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">URL Access</label>
                                    <input type="text" id="dep_urlaccess" name="urlaccess"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="departmentModal">Cancel</button>
                        <button type="submit" id="departmentSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
                <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
            </div>
        </div>
    </div>

    <style>
        .ts-select2-container.select2-container--default .select2-selection--single {
            height: 42px !important;
            display: flex;
            align-items: center;
            border-radius: 0.5rem !important;
            border: 1px solid rgb(209 213 219) !important;
            padding: 0 !important;
            background-color: #fff !important;
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 0.75rem !important;
            color: rgb(17 24 39);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 6px !important;
        }
        .ts-select2-container.select2-container--default.select2-container--focus .select2-selection--single,
        .ts-select2-container.select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: rgb(156 163 175);
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff !important;
        }
        html.dark .ts-select2-dropdown.select2-dropdown {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-dropdown .select2-search--dropdown .select2-search__field {
            background-color: #0b1220 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option {
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5 !important;
            color: #fff !important;
        }
        .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgb(243 244 246) !important;
            cursor: not-allowed;
        }
        html.dark .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toastError(text) {
            Swal.fire({ icon: 'error', title: 'Error', text: text || 'Something went wrong' });
        }

        function toastSuccess(text) {
            Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1500, showConfirmButton: false });
        }

        function statusBadge(data) {
            return data === 'A' ?
                '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
        }

        window.tsTables = {};

        function tsAdjustTable(name) {
            if (window.tsTables[name]) {
                window.tsTables[name].columns.adjust().draw(false);
            }
        }

        function tsAutoSelect($select, selectedValue, values) {
            if (selectedValue) {
                $select.val(selectedValue);
            } else if (values.length === 1) {
                $select.val(values[0]).trigger('change');
            }
        }

        function tsOpenModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('hidden').addClass('flex');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    $modal.removeClass('opacity-0').addClass('opacity-100');
                    $modal.find('.ts-modal-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                });
            });
        }

        function tsCloseModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('opacity-100').addClass('opacity-0');
            $modal.find('.ts-modal-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(function() {
                $modal.addClass('hidden').removeClass('flex');
            }, 200);
        }

        $(document).ready(function() {

            $(document).on('click', '.tsModalClose', function() {
                tsCloseModal($(this).data('modal'));
            });

            $(document).on('click', '.tsModalBackdrop', function(e) {
                if (e.target === this) tsCloseModal(this.id);
            });

            /* =========================================================
             * COMPANY (loaded first — Site depends on its options)
             * ========================================================= */
            let companyTable = $('#companiesTable').DataTable({
                ajax: "{{ route('tenancy.organization.companies.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleCompanyStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewCompanyBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editCompanyBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'companyname' },
                    { data: 'companycity', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.company = companyTable;

            function loadCompanyOptions($select, selectedId) {
                return $.get("{{ route('tenancy.organization.companies.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(c) {
                        $select.append(`<option value="${c.id}">${c.companyname}</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(c => c.id));
                    $select.trigger('change');
                });
            }

            function openCompanyModal(mode, data) {
                let readOnly = mode === 'view';
                $('#companyForm')[0].reset();
                $('#cmp_id').val('');
                $('#companyModalTitle').text(mode === 'add' ? 'Add Company' : (mode === 'view' ? 'View Company' : 'Edit Company'));
                $('#companyForm input, #companyForm select').prop('disabled', readOnly);
                $('#companySaveBtn').toggle(!readOnly);

                if (data) {
                    $('#cmp_id').val(data.id);
                    $('#cmp_companyname').val(data.companyname);
                    $('#cmp_companycity').val(data.companycity);
                }

                tsOpenModal('companyModal');
            }

            $('#addCompanyBtn').click(function() {
                openCompanyModal('add', null);
            });

            $(document).on('click', '.viewCompanyBtn, .editCompanyBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewCompanyBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/organization/companies/${id}/edit`, function(d) {
                    hideLoading();
                    openCompanyModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data company');
                    console.error(xhr.responseText);
                });
            });

            $('#companyForm').submit(function(e) {
                e.preventDefault();
                let id = $('#cmp_id').val();
                let url = id ? `/tenancy/organization/companies/${id}` : "{{ route('tenancy.organization.companies.store') }}";
                let formData = new FormData(document.getElementById('companyForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('companyModal');
                        companyTable.ajax.reload(null, false);
                        toastSuccess('Company saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan company');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleCompanyStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/organization/companies/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { companyTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * SITE
             * ========================================================= */
            let siteTable = $('#sitesTable').DataTable({
                ajax: "{{ route('tenancy.organization.sites.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleSiteStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewSiteBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editSiteBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'siteid' },
                    { data: 'sitename' },
                    { data: 'company.companyname', defaultContent: '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.site = siteTable;

            $('#site_companyid').select2({
                width: '100%',
                dropdownParent: $('#siteModal'),
                placeholder: '-- Select Company --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function openSiteModal(mode, data) {
                let readOnly = mode === 'view';
                $('#siteForm')[0].reset();
                $('#site_id').val('');
                $('#siteModalTitle').text(mode === 'add' ? 'Add Site' : (mode === 'view' ? 'View Site' : 'Edit Site'));
                $('#siteForm input, #siteForm select, #siteForm textarea').prop('disabled', readOnly);
                $('#site_siteid').prop('disabled', readOnly || mode === 'edit');
                $('#site_companyid').prop('disabled', readOnly).trigger('change');
                $('#siteSaveBtn').toggle(!readOnly);

                loadCompanyOptions($('#site_companyid'), data ? data.companyid : null);

                if (data) {
                    $('#site_id').val(data.id);
                    $('#site_siteid').val(data.siteid);
                    $('#site_sitename').val(data.sitename);
                    $('#site_sitetype').val(data.sitetype);
                    $('#site_sitecompanyname').val(data.sitecompanyname);
                    $('#site_sitephone').val(data.sitephone);
                    $('#site_sitefax').val(data.sitefax);
                    $('#site_siteaddress').val(data.siteaddress);
                }

                tsOpenModal('siteModal');
            }

            $('#addSiteBtn').click(function() {
                openSiteModal('add', null);
            });

            $(document).on('click', '.viewSiteBtn, .editSiteBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewSiteBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/organization/sites/${id}/edit`, function(d) {
                    hideLoading();
                    openSiteModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data site');
                    console.error(xhr.responseText);
                });
            });

            $('#siteForm').submit(function(e) {
                e.preventDefault();
                let id = $('#site_id').val();
                let url = id ? `/tenancy/organization/sites/${id}` : "{{ route('tenancy.organization.sites.store') }}";
                let formData = new FormData(document.getElementById('siteForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('siteModal');
                        siteTable.ajax.reload(null, false);
                        toastSuccess('Site saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan site');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleSiteStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/organization/sites/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { siteTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * DEPARTMENT
             * ========================================================= */
            let departmentTable = $('#departmentsTable').DataTable({
                ajax: "{{ route('tenancy.organization.departments.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleDepartmentStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewDepartmentBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editDepartmentBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'doctype' },
                    { data: 'site.sitename', render: (d, type, row) => d ? `${d} (${row.siteid})` : row.siteid },
                    { data: 'departmentname', render: (d, type, row) => d || row.departmentid },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.department = departmentTable;

            $('#dep_doctype').select2({
                width: '100%',
                dropdownParent: $('#departmentModal'),
                placeholder: '-- Select Doctype --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            $('#dep_siteid').select2({
                width: '100%',
                dropdownParent: $('#departmentModal'),
                placeholder: '-- Select Site --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            $('#dep_departmentid').select2({
                width: '100%',
                dropdownParent: $('#departmentModal'),
                placeholder: '-- Select Department ID --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            $('#dep_departmentname').select2({
                width: '100%',
                dropdownParent: $('#departmentModal'),
                placeholder: '-- Select Department Name --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            // departmentid/departmentname always come in matching pairs (no historical row
            // has ever diverged from its pair), so picking either one drives the other via
            // this catalog lookup instead of the user typing a name that's really a fixed code.
            let departmentCatalog = [];
            let syncingDepartmentPair = false;

            function loadDepartmentCatalog() {
                return $.get("{{ route('tenancy.organization.departments.catalog') }}", function(res) {
                    departmentCatalog = res.data || [];
                    let $idSelect = $('#dep_departmentid');
                    let $nameSelect = $('#dep_departmentname');
                    $idSelect.find('option:not(:first)').remove();
                    $nameSelect.find('option:not(:first)').remove();
                    departmentCatalog.forEach(function(d) {
                        $idSelect.append(`<option value="${d.departmentid}">${d.departmentid}</option>`);
                        $nameSelect.append(`<option value="${d.departmentname}">${d.departmentname}</option>`);
                    });
                });
            }

            $('#dep_departmentid').on('change', function() {
                if (syncingDepartmentPair) return;
                let match = departmentCatalog.find(d => d.departmentid === $(this).val());
                syncingDepartmentPair = true;
                $('#dep_departmentname').val(match ? match.departmentname : null).trigger('change');
                syncingDepartmentPair = false;
            });

            $('#dep_departmentname').on('change', function() {
                if (syncingDepartmentPair) return;
                let match = departmentCatalog.find(d => d.departmentname === $(this).val());
                syncingDepartmentPair = true;
                $('#dep_departmentid').val(match ? match.departmentid : null).trigger('change');
                syncingDepartmentPair = false;
            });

            function loadDoctypeOptions($select, selectedValue) {
                return $.get("{{ route('tenancy.organization.departments.doctypes') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(dt) {
                        $select.append(`<option value="${dt.doctype}">${dt.doctype} - ${dt.documentname}</option>`);
                    });
                    tsAutoSelect($select, selectedValue, (res.data || []).map(dt => dt.doctype));
                    $select.trigger('change');
                });
            }

            function loadSiteOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(site) {
                        $select.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(s => s.siteid));
                    $select.trigger('change');
                });
            }

            function openDepartmentModal(mode, data) {
                let readOnly = mode === 'view';
                $('#departmentForm')[0].reset();
                $('#dep_id').val('');
                $('#departmentModalTitle').text(mode === 'add' ? 'Add Department' : (mode === 'view' ? 'View Department' : 'Edit Department'));
                $('#departmentForm input, #departmentForm select').prop('disabled', readOnly);
                $('#dep_doctype, #dep_siteid, #dep_departmentid, #dep_departmentname').prop('disabled', readOnly).trigger('change');
                $('#departmentSaveBtn').toggle(!readOnly);

                loadDoctypeOptions($('#dep_doctype'), data ? data.doctype : null);
                loadSiteOptions($('#dep_siteid'), data ? data.siteid : null);

                $.when(loadDepartmentCatalog()).done(function() {
                    $('#dep_departmentid').val(data ? data.departmentid : '').trigger('change');
                });

                if (data) {
                    $('#dep_id').val(data.id);
                    $('#dep_template').val(data.template);
                    $('#dep_urlaccess').val(data.urlaccess);
                }

                tsOpenModal('departmentModal');
            }

            $('#addDepartmentBtn').click(function() {
                openDepartmentModal('add', null);
            });

            $(document).on('click', '.viewDepartmentBtn, .editDepartmentBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewDepartmentBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/organization/departments/${id}/edit`, function(d) {
                    hideLoading();
                    openDepartmentModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data department');
                    console.error(xhr.responseText);
                });
            });

            $('#departmentForm').submit(function(e) {
                e.preventDefault();
                let id = $('#dep_id').val();
                let url = id ? `/tenancy/organization/departments/${id}` : "{{ route('tenancy.organization.departments.store') }}";
                let formData = new FormData(document.getElementById('departmentForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('departmentModal');
                        departmentTable.ajax.reload(null, false);
                        toastSuccess('Department saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan department');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleDepartmentStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/organization/departments/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { departmentTable.ajax.reload(null, false); }
                });
            });

        });
    </script>
</x-app-layout>
