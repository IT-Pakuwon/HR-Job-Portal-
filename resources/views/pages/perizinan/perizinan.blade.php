<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-8">
            @php
                $attachmentListUrlTemplate = route('attachments.list', [
                    'doctype' => 'MIK',
                    'refnbr' => '__REFNBR__',
                ]);
                $activityAttachmentListUrlTemplate = route('attachments.list', [
                    'doctype' => '__DOCTYPE__',
                    'refnbr' => '__REFNBR__',
                ]);
                $cards = [
                    ['all', 'All Permits', $allPerizinan, 'border-slate-600 bg-slate-100 text-slate-700'],
                    ['active', 'Active', $activePerizinan, 'border-blue-600 bg-blue-50 text-blue-700'],
                    ['expiring', 'Expiring ≤ 30 Days', $expiringPerizinan, 'border-amber-600 bg-amber-50 text-amber-700'],
                    ['expiry_30_60', '30 - 60 Days', $expiry30To60, 'border-cyan-600 bg-cyan-50 text-cyan-700'],
                    ['expiry_60_90', '60 - 90 Days', $expiry60To90, 'border-violet-600 bg-violet-50 text-violet-700'],
                    ['expiry_90_plus', '≥ 90 Days', $expiry90Plus, 'border-fuchsia-600 bg-fuchsia-50 text-fuchsia-700'],
                    ['expired', 'Expired', $expiredPerizinan, 'border-red-600 bg-red-50 text-red-700'],
                    ['completed', 'Completed', $completedPerizinan, 'border-emerald-600 bg-emerald-50 text-emerald-700'],
                ];
            @endphp

            @foreach ($cards as [$filter, $label, $count, $color])
                <a href="#" class="status-filter block h-full" data-filter="{{ $filter }}">
                    <div class="status-card flex h-full items-center justify-between gap-3 rounded-lg border p-3 transition hover:-translate-y-1 hover:shadow-md {{ $color }}">
                        <p class="text-sm font-semibold">{{ $label }}</p>
                        <p class="text-lg font-bold">{{ number_format($count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 id="tableTitle" class="mr-2 text-base font-extrabold text-gray-700 dark:text-white">All Permits</h1>
                    <select id="filterExpiryYear" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Expiry Years</option>
                        @foreach ($expiryPeriods->pluck('year')->unique()->values() as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                    <select id="filterExpiryMonth" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Expiry Months</option>
                    </select>
                    <select id="filterCategory" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->perizinan_category }}">{{ $category->perizinancategory_descr ?: $category->perizinan_category }}</option>
                        @endforeach
                    </select>
                    <select id="filterSite" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <option value="">All Sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->siteid }}">{{ $site->cpny_id }} - {{ $site->site_name }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="btnResetFilters" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Reset</button>
                </div>
                @if ($hasGaAccess)
                    <button type="button" id="btnCreatePerizinan"
                        class="self-start rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 xl:self-auto">
                        + Create
                    </button>
                @endif
            </div>

            <div class="relative overflow-hidden">
                <table id="perizinanTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="dtr-control w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Permit ID</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Title</th>
                            <th class="px-4 py-3 text-left font-medium">Description</th>
                            <th class="px-4 py-3 text-left font-medium">Start</th>
                            <th class="px-4 py-3 text-left font-medium">End</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Information</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="perizinanModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="max-h-[95vh] w-full max-w-7xl overflow-y-auto rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <h2 id="modalTitle" class="text-lg font-bold text-gray-800 dark:text-white">Create Permit</h2>
                <button type="button" class="btnCloseModal text-2xl text-gray-500 hover:text-gray-800 dark:text-gray-400">&times;</button>
            </div>

            <form id="perizinanForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="editPerizinanId">
                <input type="hidden" id="reminder_days_before_end" name="reminder_days_before_end" value="90">
                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Company <span class="text-red-500">*</span></label>
                        <select id="cpnyid" name="cpnyid" class="w-full rounded-lg border px-3 py-2" required>
                            <option value="">Select Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company }}">{{ $company }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Site <span class="text-red-500">*</span></label>
                        <select id="site_id" name="site_id" class="w-full rounded-lg border px-3 py-2" required disabled>
                            <option value="">Select Company first</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Department <span class="text-red-500">*</span></label>
                        <select id="departementid" name="departementid" class="w-full rounded-lg border px-3 py-2" required disabled>
                            <option value="">Select Company first</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Permit Category <span class="text-red-500">*</span></label>
                        <select id="perizinan_category" name="perizinan_category" class="w-full rounded-lg border px-3 py-2" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->perizinan_category }}">
                                    {{ $category->perizinancategory_descr ?: $category->perizinan_category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold">Permit Title <span class="text-red-500">*</span></label>
                        <input type="text" id="perizinan_title" name="perizinan_title" class="w-full rounded-lg border px-3 py-2" maxlength="255" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">User Approval <span class="text-red-500">*</span></label>
                        <select id="user_dept_approval" name="user_dept_approval[]"
                            class="user-select2 w-full rounded-lg border px-3 py-2" multiple required>
                            @foreach ($approvers as $approver)
                                <option value="{{ $approver->username }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">User Peminta Dept <span class="text-red-500">*</span></label>
                        <select id="user_dept_peminta" name="user_dept_peminta[]"
                            class="user-select2 w-full rounded-lg border px-3 py-2" multiple required>
                            @foreach ($approvers as $approver)
                                <option value="{{ $approver->username }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold">Description</label>
                        <textarea id="perizinan_descr" name="perizinan_descr" rows="3" class="w-full rounded-lg border px-3 py-2"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" id="startdate" name="startdate" class="w-full rounded-lg border px-3 py-2" required>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <label for="enddate" class="block text-sm font-semibold">End Date</label>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <input type="hidden" name="expired_date" value="0">
                                <input type="checkbox" id="expired_date" name="expired_date" value="1"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700" checked>
                                <span>Has expiration</span>
                            </label>
                        </div>
                        <input type="date" id="enddate" name="enddate" class="w-full rounded-lg border px-3 py-2" required>
                        <p id="noExpiryHint" class="mt-1 hidden text-xs text-gray-500 dark:text-gray-400">This permit has no expiration date.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Issue Date</label>
                        <input type="date" id="issue_date" name="issue_date" class="w-full rounded-lg border px-3 py-2">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Application Handling Method</label>
                        <select id="application_handling_method" name="application_handling_method" class="w-full rounded-lg border px-3 py-2">
                            <option value="">Select Handling Method</option>
                            <option value="INTERNAL">Internal</option>
                            <option value="EXTERNAL">External</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Issuing Authority</label>
                        <input type="text" id="issuing_authority" name="issuing_authority" class="w-full rounded-lg border px-3 py-2" maxlength="255">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Submission Channel</label>
                        <input type="text" id="submission_channel" name="submission_channel" class="w-full rounded-lg border px-3 py-2" maxlength="255">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Legal Contract Number</label>
                        <input type="text" id="no_kontrak_legal" name="no_kontrak_legal" class="w-full rounded-lg border px-3 py-2" maxlength="255">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">SPPBJKT ID</label>
                        <input type="text" id="sppbjktid" name="sppbjktid" class="w-full rounded-lg border px-3 py-2" maxlength="255">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">CS ID</label>
                        <input type="text" id="csid" name="csid" class="w-full rounded-lg border px-3 py-2" maxlength="255">
                    </div>
                </div>

                <div class="px-5 pb-5">
                    <div class="mb-2 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 dark:text-white">Permit Details</h3>
                        <button type="button" id="btnAddRow" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">+ Add Row</button>
                    </div>
                    <div class="overflow-x-auto rounded-lg border">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Permit Item</th>
                                    <th class="w-40 px-3 py-2 text-left">Qty</th>
                                    <th class="w-20 px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="detailRows"></tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <div id="editAttachmentSection" class="mb-4 hidden">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="block text-sm font-semibold">Existing Attachments</label>
                                <span id="editAttachmentCount" class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">0</span>
                            </div>
                            <div id="editAttachmentList" class="overflow-hidden rounded-lg border dark:border-gray-700"></div>
                        </div>
                        <label class="mb-1 block text-sm font-semibold">Attachment</label>
                        <input type="file" id="attachments" name="attachments[]" multiple
                            class="w-full rounded-lg border px-3 py-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum 5 MB per file. You can select multiple files. New files will be appended in edit mode.</p>
                    </div>
                    <div id="formErrors" class="mt-4 hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
                </div>

                <div class="flex justify-end gap-3 border-t px-5 py-4 dark:border-gray-700">
                    <button type="button" class="btnCloseModal rounded-lg border px-4 py-2 text-sm font-semibold">Cancel</button>
                    <button type="submit" id="btnSave" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <span id="saveSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span id="saveText">Submit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="permitDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="flex max-h-[94vh] w-full max-w-7xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 id="detailPermitId" class="text-xl font-extrabold text-gray-900 dark:text-white">-</h2>
                        <span id="detailStatus" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700 dark:bg-gray-900 dark:text-gray-300">-</span>
                    </div>
                    <p id="detailTitle" class="mt-1 text-sm text-gray-500 dark:text-gray-400">-</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnDetailAction" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">⚡ Action</button>
                    <button type="button" class="btnCloseDetail rounded-lg border px-4 py-2 text-sm font-semibold">Close</button>
                </div>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:grid-cols-2 lg:overflow-hidden">
                <div class="overflow-y-auto border-r p-6 dark:border-gray-700">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div><p class="text-xs text-gray-400">Permit Date</p><p id="detailDate" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Category</p><p id="detailCategory" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Company</p><p id="detailCompany" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Site</p><p id="detailSite" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Department</p><p id="detailDepartment" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Requester</p><p id="detailRequester" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Start Date</p><p id="detailStartDate" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">End Date</p><p id="detailEndDate" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Issue Date</p><p id="detailIssueDate" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Handling Method</p><p id="detailHandlingMethod" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Issuing Authority</p><p id="detailIssuingAuthority" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Submission Channel</p><p id="detailSubmissionChannel" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">Legal Contract Number</p><p id="detailLegalContract" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">SPPBJKT ID</p><p id="detailSppbjktId" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">CS ID</p><p id="detailCsId" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">User Approval</p><p id="detailApprovers" class="mt-1 text-sm font-medium">-</p></div>
                        <div><p class="text-xs text-gray-400">User Peminta Dept</p><p id="detailDeptRequesters" class="mt-1 text-sm font-medium">-</p></div>
                        <div class="sm:col-span-2"><p class="text-xs text-gray-400">Description</p><div id="detailDescription" class="mt-2 rounded-lg bg-gray-50 p-4 text-sm whitespace-pre-wrap dark:bg-gray-700">-</div></div>
                    </div>

                    <div class="mb-2 mt-6 flex items-center gap-2">
                        <h3 class="font-bold">Attachments</h3>
                        <span id="detailAttachmentCount" class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">0</span>
                    </div>
                    <div class="overflow-hidden rounded-lg border dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Filename</th>
                                    <th class="px-3 py-2 text-left">Created By</th>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-right">Size</th>
                                </tr>
                            </thead>
                            <tbody id="detailAttachments"></tbody>
                        </table>
                    </div>
                </div>

                <div class="flex min-h-0 flex-col">
                    <div class="flex gap-2 border-b px-6 pt-4 dark:border-gray-700">
                        <button type="button" data-detail-tab="items" class="detail-tab-btn rounded-t-lg border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-gray-500 hover:text-indigo-600">
                            Permit Items <span id="detailItemTotal" class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">0</span>
                        </button>
                        <button type="button" data-detail-tab="tracking" class="detail-tab-btn rounded-t-lg border-b-2 border-indigo-600 px-4 py-3 text-sm font-semibold text-indigo-600">
                            Tracking Timeline
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto p-6">
                        <div id="detailTabItems" class="detail-tab-panel hidden">
                            @if ($hasUserPermitAccess)
                                <div id="showItemsEditToolbar" class="mb-3 flex items-center justify-between gap-3">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">You can update the permit items below.</p>
                                    <div class="flex gap-2">
                                        <button type="button" id="btnAddShowItem" class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">+ Add Row</button>
                                        <button type="button" id="btnSaveShowItems" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                            <span id="showItemsSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                            <span>Save Items</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <div class="overflow-hidden rounded-lg border dark:border-gray-700">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-3 py-3 text-left">Permit Item</th>
                                            <th class="w-32 px-3 py-3 text-right">Quantity</th>
                                            @if ($hasUserPermitAccess)<th id="showItemsActionHeader" class="w-16 px-3 py-3"></th>@endif
                                        </tr>
                                    </thead>
                                    <tbody id="detailItems"></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="detailTabTracking" class="detail-tab-panel">
                            <div id="activityTimeline" class="space-y-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="activityModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-bold">Permit Action</h2>
                <button type="button" class="btnCloseActivity text-2xl text-gray-500 dark:text-gray-400">&times;</button>
            </div>
            <form id="activityForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="activityPermitId">
                <div class="space-y-4 p-5">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Response <span class="text-red-500">*</span></label>
                        <textarea id="response_descr" name="response_descr" rows="4" class="w-full rounded-lg border px-3 py-2" required></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Work Status <span class="text-red-500">*</span></label>
                        <select id="status_pekerjaan" name="status_pekerjaan" class="w-full rounded-lg border px-3 py-2" required>
                            <option value="">Select Status</option>
                            <option value="WAITING">Waiting</option>
                            <option value="PROCESS">Process</option>
                            <option value="REJECTED">Rejected</option>
                            <option value="CANCELLED">Cancelled</option>
                            <option value="DONE">Done</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Attachments</label>
                        <input type="file" id="activityAttachments" name="attachments[]" multiple
                            class="w-full rounded-lg border px-3 py-2"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum 5 MB per file. You can select multiple files.</p>
                    </div>
                    <div id="activityErrors" class="hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
                </div>
                <div class="flex justify-end gap-3 border-t px-5 py-4 dark:border-gray-700">
                    <button type="button" class="btnCloseActivity rounded-lg border px-4 py-2 text-sm font-semibold">Cancel</button>
                    <button type="submit" id="btnSaveActivity" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        <span id="activitySpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span>Save Action</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            let activeFilter = 'all';
            const expiryPeriods = @json($expiryPeriods);
            const hasGaAccess = @json($hasGaAccess);
            const hasUserPermitAccess = @json($hasUserPermitAccess);
            const titles = {
                all: 'All Permits',
                active: 'Active Permits',
                expiring: 'Permits Expiring ≤ 30 Days',
                expired: 'Expired Permits',
                completed: 'Completed Permits',
                expiry_30_60: 'Permits Expiring in 30 - 60 Days',
                expiry_60_90: 'Permits Expiring in 60 - 90 Days',
                expiry_90_plus: 'Permits Expiring in ≥ 90 Days'
            };

            const escapeHtml = (value) => $('<div>').text(value ?? '').html();
            const formatDate = (value) => {
                if (!value) return '-';
                const date = new Date(`${value}T00:00:00`);
                return Number.isNaN(date.getTime()) ? escapeHtml(value) : date.toLocaleDateString('en-GB');
            };
            const formatMonthYear = (value) => {
                if (!value) return '-';
                const date = new Date(`${String(value).substring(0, 10)}T00:00:00`);
                return Number.isNaN(date.getTime())
                    ? escapeHtml(value)
                    : date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
            };
            const formatLongDateId = (value) => {
                if (!value) return '-';
                const date = new Date(`${String(value).substring(0, 10)}T00:00:00`);
                return Number.isNaN(date.getTime())
                    ? escapeHtml(value)
                    : date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
            };
            const permitStatusConfig = (value) => {
                const status = (value || '-').toString().toUpperCase();
                const statuses = {
                    P: { label: 'On Progress', color: 'bg-blue-100 text-blue-700' },
                    C: { label: 'Completed', color: 'bg-emerald-100 text-emerald-700' },
                    R: { label: 'Rejected', color: 'bg-red-100 text-red-700' },
                    X: { label: 'Cancelled', color: 'bg-red-100 text-red-700' }
                };
                return statuses[status] || { label: status, color: 'bg-gray-100 text-gray-700' };
            };
            const statusBadge = (value) => {
                const config = permitStatusConfig(value);
                return `<span class="rounded-full px-2 py-1 text-xs font-semibold ${config.color}">${escapeHtml(config.label)}</span>`;
            };

            const table = $('#perizinanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 25,
                columnDefs: [
                    { targets: 1, visible: hasGaAccess }
                ],
                ajax: {
                    url: @json(route('perizinan.json')),
                    data: function (data) {
                        data.filter = activeFilter;
                        data.expiry_year = $('#filterExpiryYear').val();
                        data.expiry_month = $('#filterExpiryMonth').val();
                        data.category = $('#filterCategory').val();
                        data.site_id = $('#filterSite').val();
                    }
                },
                order: [[8, 'asc']],
                columns: [
                    { data: null, defaultContent: '', orderable: false, searchable: false, className: 'dtr-control' },
                    {
                        data: 'perizinan_id', orderable: false, searchable: false,
                        render: (value, _type, row) => {
                            if (!hasGaAccess) return '';

                            const status = String(row.status || '').toUpperCase();
                            const isOnProgress = status === 'P';
                            const canRenew = ['C', 'R', 'X'].includes(status) && !row.has_blocking_renewal;
                            const editButton = !isOnProgress ? '' : `
                            <button type="button" class="btnEdit inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white hover:bg-amber-600" data-id="${escapeHtml(value)}" title="Edit permit" aria-label="Edit permit">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
                            </button>`;
                            const activityButton = !isOnProgress ? '' : `
                            <button type="button" class="btnActivity inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" data-id="${escapeHtml(value)}" title="Permit action" aria-label="Permit action">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2 3 14h9l-1 8 10-12h-9z"/></svg>
                            </button>`;
                            const renewButton = !canRenew ? '' : `
                            <button type="button" class="btnRenew inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" data-id="${escapeHtml(value)}" title="Renew permit" aria-label="Renew permit">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 11a8.1 8.1 0 00-15.5-2M4 4v5h5"/><path d="M4 13a8.1 8.1 0 0015.5 2M20 20v-5h-5"/></svg>
                            </button>`;
                            return `<div class="flex items-center gap-1">
                            ${editButton}
                            ${activityButton}
                            ${renewButton}
                        </div>`;
                        }
                    },
                    {
                        data: 'perizinan_id',
                        render: (value, _type, row) => `<button type="button" class="btnShowPermit inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 transition hover:bg-indigo-100" data-id="${escapeHtml(value)}" data-hash="${escapeHtml(row.detail_hash || '')}">${escapeHtml(value || '-')}</button>`
                    },
                    {
                        data: null,
                        render: (_value, _type, row) => {
                            const company = row.cpny_id || '-';
                            return row.site_name ? `${escapeHtml(company)}-${escapeHtml(row.site_name)}` : escapeHtml(company);
                        }
                    },
                    { data: 'category_name', defaultContent: '-' },
                    { data: 'perizinan_title', defaultContent: '-' },
                    { data: 'perizinan_descr', defaultContent: '-' },
                    { data: 'startdate', render: formatMonthYear },
                    { data: 'enddate', render: formatMonthYear },
                    { data: 'status', render: statusBadge },
                    { data: 'information', defaultContent: '-', orderable: false }
                ]
            });

            $('.status-filter').on('click', function (event) {
                event.preventDefault();
                activeFilter = $(this).data('filter');
                $('#tableTitle').text(titles[activeFilter] || titles.all);
                $('.status-card').removeClass('ring-2 ring-indigo-500');
                $(this).find('.status-card').addClass('ring-2 ring-indigo-500');
                table.ajax.reload();
            });

            $('.status-filter[data-filter="all"] .status-card').addClass('ring-2 ring-indigo-500');

            function updateExpiryMonths() {
                const selectedYear = $('#filterExpiryYear').val();
                const currentMonth = $('#filterExpiryMonth').val();
                const months = [...new Set(expiryPeriods
                    .filter(period => !selectedYear || String(period.year) === String(selectedYear))
                    .map(period => Number(period.month)))]
                    .filter(month => month >= 1 && month <= 12)
                    .sort((a, b) => a - b);
                const $month = $('#filterExpiryMonth').html('<option value="">All Expiry Months</option>');

                months.forEach(month => {
                    const monthName = new Intl.DateTimeFormat('en', { month: 'long' }).format(new Date(2000, month - 1, 1));
                    $month.append(new Option(monthName, month));
                });

                $month.val(months.includes(Number(currentMonth)) ? currentMonth : '');
            }

            updateExpiryMonths();
            $('#filterExpiryYear').on('change', function () {
                updateExpiryMonths();
                table.ajax.reload();
            });
            $('#filterExpiryMonth, #filterCategory, #filterSite').on('change', function () {
                table.ajax.reload();
            });
            $('#btnResetFilters').on('click', function () {
                $('#filterExpiryYear, #filterCategory, #filterSite').val('');
                updateExpiryMonths();
                $('#filterExpiryMonth').val('');
                table.ajax.reload();
            });

            const $modal = $('#perizinanModal');
            const $detailModal = $('#permitDetailModal');
            const $activityModal = $('#activityModal');
            const baseUrl = @json(url('/perizinan'));
            const showPermitBaseUrl = @json(url('/showperizinan'));
            const departmentUrl = @json(route('perizinan.departments'));
            const siteUrl = @json(route('perizinan.sites'));
            const removeAttachmentBaseUrl = @json(url('/remove-attachment'));
            const csrfToken = @json(csrf_token());
            const attachmentListUrlTemplate = @json($attachmentListUrlTemplate);
            const activityAttachmentListUrlTemplate = @json($activityAttachmentListUrlTemplate);
            const initialPermitId = @json($openPermitId ?? null);

            $('#user_dept_approval, #user_dept_peminta').each(function () {
                $(this).select2({
                    placeholder: $(this).is('#user_dept_approval') ? 'Search user approval...' : 'Search user peminta dept...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $modal
                });
            });

            const formatDateTime = (value) => {
                if (!value) return '-';
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? escapeHtml(value) : date.toLocaleString('en-GB');
            };

            function statusClass(status) {
                return ({
                    SUBMITTED: 'bg-violet-100 text-violet-700',
                    PROCESS: 'bg-blue-100 text-blue-700',
                    WAITING: 'bg-amber-100 text-amber-700',
                    REJECTED: 'bg-red-100 text-red-700',
                    CANCELLED: 'bg-gray-200 text-gray-700',
                    DONE: 'bg-emerald-100 text-emerald-700'
                })[(status || '').toUpperCase()] || 'bg-gray-100 text-gray-700';
            }

            function openActivityModal(permitId) {
                $('#activityForm')[0].reset();
                $('#activityPermitId').val(permitId);
                $('#activityErrors').addClass('hidden').empty();
                $activityModal.removeClass('hidden').addClass('flex');
            }

            function formatFileSize(bytes) {
                const size = Number(bytes || 0);
                if (!size) return '-';
                if (size < 1024) return `${size} B`;
                if (size < 1048576) return `${(size / 1024).toFixed(1)} KB`;
                return `${(size / 1048576).toFixed(1)} MB`;
            }

            function loadPermitAttachments(permitId) {
                const $tbody = $('#detailAttachments');
                $('#detailAttachmentCount').text('0');
                $tbody.html('<tr><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Loading attachments...</td></tr>');

                const listUrl = attachmentListUrlTemplate.replace('__REFNBR__', encodeURIComponent(permitId));
                $.get(listUrl)
                    .done(function (response) {
                        const rows = response.success ? (response.attachments || []) : [];
                        $('#detailAttachmentCount').text(rows.length);
                        if (!rows.length) {
                            $tbody.html('<tr><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No attachments found.</td></tr>');
                            return;
                        }

                        $tbody.html(rows.map(attachment => {
                            const baseName = attachment.name || attachment.display_name || attachment.filename || 'Attachment';
                            const extension = attachment.extention ? `.${attachment.extention}` : '';
                            const fileName = baseName.toLowerCase().endsWith(extension.toLowerCase()) ? baseName : `${baseName}${extension}`;
                            const fileLink = attachment.url
                                ? `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="font-semibold text-indigo-600 hover:underline">📎 ${escapeHtml(fileName)}</a>`
                                : `<span>📎 ${escapeHtml(fileName)} <span class="text-xs text-red-500">(link unavailable)</span></span>`;
                            return `<tr class="border-t dark:border-gray-700">
                                <td class="px-3 py-2">${fileLink}</td>
                                <td class="px-3 py-2">${escapeHtml(attachment.created_user || attachment.created_by || '-')}</td>
                                <td class="px-3 py-2">${formatDateTime(attachment.created_at)}</td>
                                <td class="px-3 py-2 text-right">${formatFileSize(attachment.size)}</td>
                            </tr>`;
                        }).join(''));
                    })
                    .fail(function () {
                        $tbody.html('<tr><td colspan="4" class="px-3 py-6 text-center text-red-500">Failed to load attachments.</td></tr>');
                    });
            }

            function loadActivityAttachments(activity) {
                const $container = $(`#activityAttachments-${activity.id}`);
                if (!$container.length || !activity.attachment_refnbr || !activity.attachment_doctype) return;

                const listUrl = activityAttachmentListUrlTemplate
                    .replace('__DOCTYPE__', encodeURIComponent(activity.attachment_doctype))
                    .replace('__REFNBR__', encodeURIComponent(activity.attachment_refnbr));

                $.get(listUrl)
                    .done(function (response) {
                        const attachments = response.success ? (response.attachments || []) : [];
                        if (!attachments.length) {
                            $container.addClass('hidden').empty();
                            return;
                        }

                        $container.removeClass('hidden').html(`
                            <p class="mb-2 text-xs font-semibold text-gray-500 dark:text-gray-400">Attachments (${attachments.length})</p>
                            <div class="flex flex-wrap gap-2">
                                ${attachments.map(attachment => {
                                    const fileName = attachment.name || attachment.display_name || attachment.attachment_name || attachment.filename || 'Attachment';
                                    return attachment.url
                                        ? `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/40">&#128206; ${escapeHtml(fileName)}</a>`
                                        : `<span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2.5 py-1.5 text-xs dark:bg-gray-700">&#128206; ${escapeHtml(fileName)}</span>`;
                                }).join('')}
                            </div>`);
                    })
                    .fail(() => $container.addClass('hidden').empty());
            }

            function renderEditAttachments(rows) {
                const $list = $('#editAttachmentList');
                $('#editAttachmentCount').text(rows.length);

                if (!rows.length) {
                    $list.html('<div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">No existing attachments.</div>');
                    return;
                }

                $list.html(rows.map(attachment => {
                    const baseName = attachment.name || attachment.display_name || attachment.filename || 'Attachment';
                    const extension = attachment.extention ? `.${attachment.extention}` : '';
                    const fileName = baseName.toLowerCase().endsWith(extension.toLowerCase()) ? baseName : `${baseName}${extension}`;
                    const nameHtml = attachment.url
                        ? `<a href="${escapeHtml(attachment.url)}" target="_blank" rel="noopener noreferrer" class="font-semibold text-indigo-600 hover:underline">📎 ${escapeHtml(fileName)}</a>`
                        : `<span>📎 ${escapeHtml(fileName)}</span>`;

                    return `<div class="attachment-row flex items-center justify-between gap-3 border-b px-4 py-3 last:border-b-0 dark:border-gray-700" data-id="${escapeHtml(attachment.id)}">
                        <div class="min-w-0">
                            <div class="truncate">${nameHtml}</div>
                            <p class="mt-1 text-xs text-gray-400">${escapeHtml(attachment.created_user || attachment.created_by || '-')} · ${formatDateTime(attachment.created_at)} · ${formatFileSize(attachment.size)}</p>
                        </div>
                        <button type="button" class="removeAttachment2 inline-flex shrink-0 items-center gap-1 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100" title="Remove attachment">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14M10 10v6m4-6v6"/>
                            </svg>
                        </button>
                    </div>`;
                }).join(''));
            }

            function loadEditAttachments(permitId) {
                $('#editAttachmentSection').removeClass('hidden');
                $('#editAttachmentCount').text('0');
                $('#editAttachmentList').html('<div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">Loading attachments...</div>');
                const listUrl = attachmentListUrlTemplate.replace('__REFNBR__', encodeURIComponent(permitId));

                $.get(listUrl)
                    .done(response => renderEditAttachments(response.success ? (response.attachments || []) : []))
                    .fail(() => $('#editAttachmentList').html('<div class="p-4 text-center text-sm text-red-500">Failed to load attachments.</div>'));
            }

            function closeActivityModal() {
                if ($('#btnSaveActivity').prop('disabled')) return;
                $activityModal.addClass('hidden').removeClass('flex');
            }

            function activateDetailTab(tab) {
                $('.detail-tab-panel').addClass('hidden');
                $(`#detailTab${tab === 'items' ? 'Items' : 'Tracking'}`).removeClass('hidden');
                $('.detail-tab-btn')
                    .removeClass('border-indigo-600 text-indigo-600')
                    .addClass('border-transparent text-gray-500');
                $(`.detail-tab-btn[data-detail-tab="${tab}"]`)
                    .removeClass('border-transparent text-gray-500')
                    .addClass('border-indigo-600 text-indigo-600');
            }

            function showDetailItemRow(item = {}) {
                return `<tr class="show-detail-row border-t dark:border-gray-700">
                    <td class="p-2">
                        <input type="text" class="show-item-name w-full rounded-lg border px-3 py-2" value="${escapeHtml(item.item_perizinan || '')}" maxlength="255" required>
                    </td>
                    <td class="p-2">
                        <input type="number" class="show-item-qty w-full rounded-lg border px-3 py-2 text-right" value="${escapeHtml(item.qty_perizinan || 1)}" min="0.01" step="0.01" required>
                    </td>
                    <td class="p-2 text-center">
                        <button type="button" class="btnRemoveShowItem inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400" title="Remove permit item" aria-label="Remove permit item">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/>
                            </svg>
                        </button>
                    </td>
                </tr>`;
            }

            async function showPermit(permitId) {
                const response = await $.get(`${baseUrl}/${encodeURIComponent(permitId)}`);
                const permit = response.data;
                $('#detailPermitId').text(permit.perizinan_id || '-');
                $('#detailTitle').text(permit.perizinan_title || '-');
                const permitStatus = permitStatusConfig(permit.status);
                $('#detailStatus')
                    .attr('class', `rounded-full px-3 py-1 text-xs font-bold ${permitStatus.color}`)
                    .text(permitStatus.label);
                $('#detailDate').text(formatDate(permit.perizinan_date));
                $('#detailCategory').text(permit.category?.perizinancategory_descr || permit.perizinan_category || '-');
                $('#detailCompany').text(permit.cpny_id || '-');
                $('#detailSite').text(permit.site?.site_name || permit.site_id || '-');
                $('#detailDepartment').text(permit.department?.department_name || permit.department_fin_id || '-');
                $('#detailRequester').text(permit.user_peminta || '-');
                $('#detailStartDate').text(formatLongDateId(permit.startdate));
                $('#detailEndDate').text(permit.expired_date ? formatLongDateId(permit.enddate) : 'No expiration date');
                $('#detailIssueDate').text(formatLongDateId(permit.issue_date));
                $('#detailHandlingMethod').text(permit.application_handling_method
                    ? permit.application_handling_method.charAt(0).toUpperCase() + permit.application_handling_method.slice(1).toLowerCase()
                    : '-');
                $('#detailIssuingAuthority').text(permit.issuing_authority || '-');
                $('#detailSubmissionChannel').text(permit.submission_channel || '-');
                $('#detailLegalContract').text(permit.no_kontrak_legal || '-');
                if (permit.sppbjktid && permit.sppbjkt_url) {
                    $('#detailSppbjktId').html(`
                        <a href="${escapeHtml(permit.sppbjkt_url)}" target="_blank" rel="noopener noreferrer"
                            class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline dark:text-indigo-400">
                            ${escapeHtml(permit.sppbjktid)}
                            <span aria-hidden="true">&#8599;</span>
                        </a>`);
                } else {
                    $('#detailSppbjktId').text(permit.sppbjktid || '-');
                }
                if (permit.csid && permit.cs_url) {
                    $('#detailCsId').html(`
                        <a href="${escapeHtml(permit.cs_url)}" target="_blank" rel="noopener noreferrer"
                            class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline dark:text-indigo-400">
                            ${escapeHtml(permit.csid)}
                            <span aria-hidden="true">&#8599;</span>
                        </a>`);
                } else {
                    $('#detailCsId').text(permit.csid || '-');
                }
                $('#detailApprovers').text((permit.user_dept_approval || '').split(',').filter(Boolean).join(', ') || '-');
                $('#detailDeptRequesters').text((permit.user_dept_peminta || '').split(',').filter(Boolean).join(', ') || '-');
                $('#detailDescription').text(permit.perizinan_descr || '-');
                $('#btnDetailAction')
                    .data('id', permit.perizinan_id)
                    .toggleClass('hidden', !hasGaAccess || String(permit.status || '').toUpperCase() !== 'P');
                $('#btnSaveShowItems').data('id', permit.perizinan_id);
                loadPermitAttachments(permit.perizinan_id);

                const items = permit.details || [];
                const canEditPermitItems = hasUserPermitAccess && String(permit.status || '').toUpperCase() !== 'C';
                $('#showItemsEditToolbar, #showItemsActionHeader').toggleClass('hidden', !canEditPermitItems);
                const totalItemQty = items.reduce((total, item) => total + Number(item.qty_perizinan || 0), 0);
                $('#detailItemTotal').text(new Intl.NumberFormat('en-US', {
                    maximumFractionDigits: 2
                }).format(totalItemQty));
                if (canEditPermitItems) {
                    $('#detailItems').html((items.length ? items : [{}]).map(showDetailItemRow).join(''));
                } else {
                    $('#detailItems').html(items.length ? items.map(item => `
                        <tr class="border-t dark:border-gray-700">
                            <td class="px-3 py-2">${escapeHtml(item.item_perizinan || '-')}</td>
                            <td class="px-3 py-2 text-right">${escapeHtml(item.qty_perizinan ?? '-')}</td>
                        </tr>`).join('') : '<tr><td colspan="2" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No permit items.</td></tr>');
                }

                const activities = permit.activities || [];
                $('#activityTimeline').html(activities.length ? activities.map(activity => {
                    const status = (activity.status_pekerjaan || '-').toUpperCase();
                    return `<div class="relative border-l-2 border-gray-200 pb-2 pl-6 dark:border-gray-600">
                        <span class="absolute -left-2.5 top-0 h-5 w-5 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-gray-800"></span>
                        <div class="rounded-lg border p-4 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold">${escapeHtml(activity.response_descr || '-')}</p>
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold ${statusClass(status)}">${escapeHtml(status)}</span>
                            </div>
                            <p class="mt-2 text-xs text-gray-400">${escapeHtml(activity.pic_perizinan || '-')} · ${formatDateTime(activity.response_date)}</p>
                            <div id="activityAttachments-${escapeHtml(activity.id)}" class="mt-3 hidden border-t pt-3 dark:border-gray-700"></div>
                        </div>
                    </div>`;
                }).join('') : '<div class="rounded-lg border border-dashed p-8 text-center text-sm text-gray-500 dark:text-gray-400">No tracking activity yet.</div>');

                activities.forEach(loadActivityAttachments);
                activateDetailTab('tracking');

                $detailModal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');
            }

            function addDetailRow(detail = {}) {
                $('#detailRows').append(`
                    <tr class="border-t detail-row">
                        <td class="p-2"><input type="text" name="item_perizinan[]" value="${escapeHtml(detail.item_perizinan || '')}" class="w-full rounded-lg border px-3 py-2" maxlength="255" required></td>
                        <td class="p-2"><input type="number" name="qty_perizinan[]" value="${escapeHtml(detail.qty_perizinan || 1)}" class="w-full rounded-lg border px-3 py-2" min="0.01" step="0.01" required></td>
                        <td class="p-2 text-center">
                            <button type="button" class="btnRemoveRow inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400" title="Remove permit item" aria-label="Remove permit item">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v5M14 11v5"/>
                                </svg>
                            </button>
                        </td>
                    </tr>`);
            }

            function setSaving(saving) {
                $('#btnSave').prop('disabled', saving).toggleClass('opacity-60', saving);
                $('#saveSpinner').toggleClass('hidden', !saving);
                $('#saveText').text(saving ? 'Submitting...' : 'Submit');
            }

            function syncExpiryField() {
                const hasExpiry = $('#expired_date').is(':checked');
                $('#enddate').prop('disabled', !hasExpiry).prop('required', hasExpiry);
                $('#noExpiryHint').toggleClass('hidden', hasExpiry);
                if (!hasExpiry) $('#enddate').val('');
            }

            function openModal() {
                $modal.removeClass('hidden').addClass('flex');
                $('body').addClass('overflow-hidden');
            }

            function closeModal() {
                if ($('#btnSave').prop('disabled')) return;
                $modal.addClass('hidden').removeClass('flex');
                $('body').removeClass('overflow-hidden');
            }

            async function loadDepartments(companyId, selected = '') {
                const $department = $('#departementid');
                $department.prop('disabled', true).html('<option value="">Loading...</option>');
                if (!companyId) {
                    $department.html('<option value="">Select Company first</option>');
                    return;
                }
                const rows = await $.get(departmentUrl, { cpny_id: companyId });
                $department.html('<option value="">Select Department</option>');
                rows.forEach(row => $department.append(new Option(row.department_name, row.department_fin_id)));
                $department.prop('disabled', false).val(selected);
            }

            async function loadSites(companyId, selected = '') {
                const $site = $('#site_id');
                $site.prop('disabled', true).html('<option value="">Loading...</option>');
                if (!companyId) {
                    $site.html('<option value="">Select Company first</option>');
                    return;
                }
                const rows = await $.get(siteUrl, { cpny_id: companyId });
                $site.html('<option value="">Select Site</option>');
                rows.forEach(row => $site.append(new Option(row.site_name, row.siteid)));
                $site.prop('disabled', false).val(selected);
            }

            function resetForm() {
                $('#perizinanForm')[0].reset();
                $('#user_dept_approval, #user_dept_peminta').val(null).trigger('change');
                $('#expired_date').prop('checked', true);
                $('#reminder_days_before_end').val('90');
                syncExpiryField();
                $('#editPerizinanId').val('');
                $('#modalTitle').text('Create Permit');
                $('#editAttachmentSection').addClass('hidden');
                $('#editAttachmentList').empty();
                $('#editAttachmentCount').text('0');
                $('#site_id').prop('disabled', true).html('<option value="">Select Company first</option>');
                $('#departementid').prop('disabled', true).html('<option value="">Select Company first</option>');
                $('#detailRows').empty();
                $('#formErrors').addClass('hidden').empty();
                addDetailRow();
            }

            $('#btnCreatePerizinan').on('click', function () {
                resetForm();
                openModal();
            });
            $('.btnCloseModal').on('click', closeModal);
            $('#cpnyid').on('change', function () {
                loadSites(this.value);
                loadDepartments(this.value);
            });
            $('#expired_date').on('change', syncExpiryField);
            $('#btnAddRow').on('click', () => addDetailRow());
            $(document).on('click', '.btnRemoveRow', function () {
                if ($('.detail-row').length === 1) {
                    $(this).closest('tr').find('input').val('');
                    return;
                }
                $(this).closest('tr').remove();
            });

            $('#btnAddShowItem').on('click', function () {
                $('#detailItems').append(showDetailItemRow());
            });
            $(document).on('click', '.btnRemoveShowItem', function () {
                if ($('.show-detail-row').length === 1) {
                    Swal.fire('Required', 'At least one permit item is required.', 'warning');
                    return;
                }
                $(this).closest('.show-detail-row').remove();
            });
            $('#btnSaveShowItems').on('click', async function () {
                const permitId = $(this).data('id');
                const items = $('.show-item-name').map((_, input) => $(input).val().trim()).get();
                const quantities = $('.show-item-qty').map((_, input) => $(input).val()).get();

                if (!permitId || !items.length || items.some(item => !item) || quantities.some(qty => !qty || Number(qty) <= 0)) {
                    Swal.fire('Validation', 'Complete every permit item and enter a quantity greater than zero.', 'warning');
                    return;
                }

                const confirmation = await Swal.fire({
                    icon: 'question',
                    title: 'Save permit items?',
                    text: 'The current permit item list will be updated.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save',
                    cancelButtonText: 'Cancel'
                });
                if (!confirmation.isConfirmed) return;

                const $button = $(this);
                $button.prop('disabled', true).addClass('opacity-60');
                $('#showItemsSpinner').removeClass('hidden');

                try {
                    const response = await $.ajax({
                        url: `${baseUrl}/${encodeURIComponent(permitId)}/details`,
                        method: 'PUT',
                        data: {
                            _token: csrfToken,
                            item_perizinan: items,
                            qty_perizinan: quantities
                        }
                    });
                    await showPermit(permitId);
                    activateDetailTab('items');
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1300, showConfirmButton: false });
                } catch (error) {
                    const errors = error.responseJSON?.errors;
                    const message = errors ? Object.values(errors).flat()[0] : (error.responseJSON?.message || 'Failed to update permit items.');
                    Swal.fire('Error', message, 'error');
                } finally {
                    $button.prop('disabled', false).removeClass('opacity-60');
                    $('#showItemsSpinner').addClass('hidden');
                }
            });

            $(document).on('click', '.removeAttachment2', async function () {
                const $btn = $(this);
                const $row = $btn.closest('.attachment-row');
                const attachmentId = $row.data('id');

                if (!attachmentId) {
                    Swal.fire('Error', 'Attachment ID was not found.', 'error');
                    return;
                }

                const confirmation = await Swal.fire({
                    icon: 'warning',
                    title: 'Remove attachment?',
                    text: 'This attachment will be removed from the permit.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    reverseButtons: true
                });

                if (!confirmation.isConfirmed) return;

                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html(`
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Removing...
                `);

                $.ajax({
                    url: `${removeAttachmentBaseUrl}/${attachmentId}`,
                    type: 'POST',
                    data: {
                        _method: 'PUT',
                        _token: csrfToken
                    }
                }).done(function (response) {
                    if (response && response.success) {
                        $row.slideUp(180, function () {
                            $(this).remove();
                            const remaining = $('#editAttachmentList .attachment-row').length;
                            $('#editAttachmentCount').text(remaining);
                            if (!remaining) {
                                $('#editAttachmentList').html('<div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">No existing attachments.</div>');
                            }
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed',
                            text: 'Attachment removed successfully.',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', response?.message || 'Failed to remove attachment.', 'error');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                }).fail(function (xhr) {
                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message || xhr.responseJSON?.error || 'Unable to remove attachment.',
                        'error'
                    );
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
            });

            $(document).on('click', '.btnShowPermit', async function () {
                try {
                    const hash = $(this).data('hash');
                    await showPermit($(this).data('id'));
                    if (hash) {
                        window.history.replaceState({}, '', `${showPermitBaseUrl}/${encodeURIComponent(hash)}`);
                    }
                } catch (error) {
                    Swal.fire('Error', error.responseJSON?.message || 'Failed to load permit details.', 'error');
                }
            });

            $(document).on('click', '.btnActivity', function () {
                openActivityModal($(this).data('id'));
            });
            $('#btnDetailAction').on('click', function () {
                openActivityModal($(this).data('id'));
            });
            $('.btnCloseDetail').on('click', function () {
                $detailModal.addClass('hidden').removeClass('flex');
                $('body').removeClass('overflow-hidden');
                window.history.replaceState({}, '', baseUrl);
            });
            $('.btnCloseActivity').on('click', closeActivityModal);
            $(document).on('click', '.detail-tab-btn', function () {
                activateDetailTab($(this).data('detail-tab'));
            });

            $('#activityForm').on('submit', function (event) {
                event.preventDefault();
                const permitId = $('#activityPermitId').val();
                const formData = new FormData(this);
                const $button = $('#btnSaveActivity');
                $button.prop('disabled', true).addClass('opacity-60');
                $('#activitySpinner').removeClass('hidden');
                $('#activityErrors').addClass('hidden').empty();

                $.ajax({
                    url: `${baseUrl}/${encodeURIComponent(permitId)}/activities`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: async function (response) {
                        $activityModal.addClass('hidden').removeClass('flex');
                        table.ajax.reload(null, false);
                        await showPermit(permitId);
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1300, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors;
                        const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.message || 'Failed to save activity.'];
                        $('#activityErrors').removeClass('hidden').html(messages.map(message => `<div>• ${escapeHtml(message)}</div>`).join(''));
                        Swal.fire('Error', messages[0], 'error');
                    },
                    complete: function () {
                        $button.prop('disabled', false).removeClass('opacity-60');
                        $('#activitySpinner').addClass('hidden');
                    }
                });
            });

            async function openEditPermit(id) {
                resetForm();
                setSaving(true);
                try {
                    const response = await $.get(`${baseUrl}/${encodeURIComponent(id)}/edit`);
                    const data = response.data;
                    $('#editPerizinanId').val(data.perizinan_id);
                    $('#modalTitle').text(`Edit Permit - ${data.perizinan_id}`);
                    loadEditAttachments(data.perizinan_id);
                    $('#cpnyid').val(data.cpny_id);
                    await Promise.all([
                        loadSites(data.cpny_id, data.site_id),
                        loadDepartments(data.cpny_id, data.department_fin_id)
                    ]);
                    $('#perizinan_category').val(data.perizinan_category);
                    $('#perizinan_title').val(data.perizinan_title);
                    $('#perizinan_descr').val(data.perizinan_descr);
                    $('#startdate').val((data.startdate || '').substring(0, 10));
                    $('#expired_date').prop('checked', Boolean(data.expired_date));
                    syncExpiryField();
                    $('#enddate').val((data.enddate || '').substring(0, 10));
                    $('#reminder_days_before_end').val(String(data.reminder_days_before_end || 90));
                    $('#application_handling_method').val(data.application_handling_method || '');
                    $('#issuing_authority').val(data.issuing_authority || '');
                    $('#submission_channel').val(data.submission_channel || '');
                    $('#no_kontrak_legal').val(data.no_kontrak_legal || '');
                    $('#issue_date').val((data.issue_date || '').substring(0, 10));
                    $('#user_dept_approval').val((data.user_dept_approval || '').split(',').filter(Boolean)).trigger('change');
                    $('#user_dept_peminta').val((data.user_dept_peminta || '').split(',').filter(Boolean)).trigger('change');
                    $('#sppbjktid').val(data.sppbjktid || '');
                    $('#csid').val(data.csid || '');
                    $('#detailRows').empty();
                    (response.details.length ? response.details : [{}]).forEach(addDetailRow);
                    openModal();
                } catch (error) {
                    Swal.fire('Error', error.responseJSON?.message || 'Failed to load permit data.', 'error');
                } finally {
                    setSaving(false);
                }
            }

            $(document).on('click', '.btnEdit', function () {
                openEditPermit($(this).data('id'));
            });

            $(document).on('click', '.btnRenew', async function () {
                const $button = $(this);
                const permitId = $button.data('id');
                const confirmation = await Swal.fire({
                    icon: 'question',
                    title: 'Renew this permit?',
                    html: `A new Permit ID will be created from <b>${escapeHtml(permitId)}</b>.<br>Start Date and End Date must be completed in the edit form.`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, create renewal',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#059669',
                    reverseButtons: true
                });

                if (!confirmation.isConfirmed) return;

                $button.prop('disabled', true).addClass('opacity-60');
                Swal.fire({
                    title: 'Creating renewal...',
                    text: 'Please wait.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const response = await $.ajax({
                        url: `${baseUrl}/${encodeURIComponent(permitId)}/renew`,
                        method: 'POST',
                        data: { _token: csrfToken }
                    });

                    table.ajax.reload(null, false);
                    await Swal.fire({
                        icon: 'success',
                        title: 'Renewal Created',
                        html: `New Permit ID: <b>${escapeHtml(response.perizinan_id)}</b>`,
                        confirmButtonText: 'Continue to Edit'
                    });
                    await openEditPermit(response.perizinan_id);
                } catch (error) {
                    Swal.fire('Error', error.responseJSON?.error || error.responseJSON?.message || 'Failed to create permit renewal.', 'error');
                } finally {
                    $button.prop('disabled', false).removeClass('opacity-60');
                }
            });

            $('#perizinanForm').on('submit', function (event) {
                event.preventDefault();
                const id = $('#editPerizinanId').val();
                const formData = new FormData(this);
                if (id) formData.append('_method', 'PUT');
                setSaving(true);
                $('#formErrors').addClass('hidden').empty();

                $.ajax({
                    url: id ? `${baseUrl}/${encodeURIComponent(id)}` : baseUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        Swal.fire({ icon: 'success', title: 'Success', text: response.message, timer: 1400, showConfirmButton: false })
                            .then(() => window.location.href = response.redirect || baseUrl);
                    },
                    error: function (xhr) {
                        setSaving(false);
                        const errors = xhr.responseJSON?.errors;
                        const messages = errors ? Object.values(errors).flat() : [xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to save data.'];
                        $('#formErrors').removeClass('hidden').html(messages.map(message => `<div>• ${escapeHtml(message)}</div>`).join(''));
                        Swal.fire('Error', messages[0], 'error');
                    }
                });
            });

            if (initialPermitId) {
                showPermit(initialPermitId).catch(function (error) {
                    Swal.fire('Error', error.responseJSON?.message || 'Failed to open permit details.', 'error');
                });
            }
        });
    </script>
</x-app-layout>
