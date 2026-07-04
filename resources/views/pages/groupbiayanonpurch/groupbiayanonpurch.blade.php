<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── TABS ────────────────────────────────────────────────────────────── --}}
        <div class="mt-4">
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-0">
                <button id="tab-master"
                    class="tab-btn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400"
                    onclick="switchTab('master')">
                    💰 Master Group Biaya
                </button>
                <button id="tab-budget"
                    class="tab-btn px-5 py-2.5 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                    onclick="switchTab('budget')">
                    📋 Budget Assignment
                </button>
            </div>

            {{-- ── TAB 1: Master Group Biaya ──────────────────────────────────── --}}
            <div id="panel-master" class="rounded-b-xl rounded-tr-xl bg-white p-4 dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700">
                <div class="mb-3 flex items-center justify-between">
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">💰 Master Group Biaya</h1>
                    @if($isAdmin)
                    <button id="addGroupBiayaBtn"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        + Add
                    </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table id="groupBiayaTable" class="text-body w-full text-left text-sm">
                        <thead class="border-b text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            <tr>
                                @if($isAdmin)
                                <th class="w-14 py-2 text-center">Act</th>
                                @endif
                                <th class="py-2 px-2 w-20">ID</th>
                                <th class="py-2 px-2">Description</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ── TAB 2: Budget Assignment ───────────────────────────────────── --}}
            <div id="panel-budget" class="hidden rounded-b-xl rounded-tr-xl bg-white dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700">

                @if($isCostCtrl)
                {{-- COMPANY FILTER ------------------------------------------------ --}}
                <div id="ccCompanyBar" class="hidden border-b border-gray-100 dark:border-gray-700 px-4 py-2 flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide shrink-0">Company:</span>
                    <div id="ccCompanyPills" class="flex gap-1.5 flex-wrap"></div>
                </div>

                {{-- 3-PANEL LAYOUT ------------------------------------------------ --}}
                {{-- On mobile: stacked vertically. On md+: side-by-side. --}}
                <div class="cc-three-panel flex flex-col md:flex-row md:divide-x divide-gray-200 dark:divide-gray-700">

                    {{-- LEFT: Department Fin list --}}
                    <div class="cc-panel-left flex flex-col border-b md:border-b-0 border-gray-200 dark:border-gray-700
                                w-full md:w-48 lg:w-56 xl:w-64 md:shrink-0">
                        <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-3 py-2.5 border-b border-gray-100 dark:border-gray-700">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department</p>
                            <input id="deptFinSearch" type="text" placeholder="Search…"
                                class="mt-1.5 w-full rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 text-xs outline-none focus:ring-1 focus:ring-indigo-400 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:placeholder-gray-400">
                        </div>
                        <div id="deptFinList" class="overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 cc-scroll-body"></div>
                    </div>

                    {{-- CENTER: Group Biaya --}}
                    <div class="cc-panel-center flex flex-col border-b md:border-b-0 border-gray-200 dark:border-gray-700
                                w-full md:flex-1 min-w-0">
                        <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Group Biaya</p>
                                <p id="ccDeptLabel" class="mt-0.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium truncate">— select a department —</p>
                            </div>
                            <button id="ccAssignGbBtn"
                                class="hidden shrink-0 inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                + Assign
                            </button>
                        </div>
                        <div id="ccGbEmpty" class="flex items-center justify-center text-sm text-gray-300 dark:text-gray-600 cc-scroll-body">
                            Select a department first
                        </div>
                        <div id="ccGbContent" class="hidden overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 cc-scroll-body"></div>
                    </div>

                    {{-- RIGHT: COA --}}
                    <div class="cc-panel-right flex flex-col w-full md:flex-1 min-w-0">
                        <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">COA (Account)</p>
                                <p id="ccGbLabel" class="mt-0.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium truncate">— select a group biaya —</p>
                            </div>
                            <button id="ccAssignCoaBtn"
                                class="hidden shrink-0 inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                + Assign
                            </button>
                        </div>
                        <div id="ccCoaEmpty" class="flex items-center justify-center text-sm text-gray-300 dark:text-gray-600 cc-scroll-body">
                            Select a group biaya first
                        </div>
                        <div id="ccCoaContent" class="hidden overflow-y-auto cc-scroll-body">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/80">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-300 w-28">Business Unit</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-300 w-28">Account ID</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-300">Description</th>
                                    </tr>
                                </thead>
                                <tbody id="ccCoaTableBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @else
                {{-- No access --}}
                <div class="flex h-72 flex-col items-center justify-center gap-3 text-gray-400 dark:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m0 0v2m0-2h2m-2 0H10m2-11a4 4 0 00-4 4v1H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2v-7a2 2 0 00-2-2h-2v-1a4 4 0 00-4-4z" />
                    </svg>
                    <p class="text-sm font-medium">Access restricted — requires <code class="rounded bg-gray-100 px-1 py-0.5 text-xs text-red-500 dark:bg-gray-700">COSTCTRLACCESS</code> role</p>
                </div>
                @endif
            </div>
        </div>

        @if($isAdmin)
        {{-- ── GROUP BIAYA MODAL (Add / Edit) ──────────────────────────────────── --}}
        <div id="groupBiayaModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-5 text-base font-bold text-gray-800 dark:text-white">Add Group Biaya</h2>

                <form id="groupBiayaForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Group Biaya ID</label>
                            <input type="text" id="groupbiaya_id" name="groupbiaya_id"
                                value="{{ $nextGroupbiayaId ?? '' }}"
                                class="w-full cursor-not-allowed rounded-lg border bg-gray-100 px-3 py-2 text-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                readonly required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Description</label>
                            <input type="text" id="groupbiayadescr" name="groupbiayadescr"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700 dark:text-white" required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Is Budget</label>
                            <select id="is_budget" name="is_budget"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700 dark:text-white">
                                <option value="false">No</option>
                                <option value="true">Yes</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600 dark:text-gray-300">Is Deposit</label>
                            <select id="is_deposit" name="is_deposit"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700 dark:text-white">
                                <option value="false">No</option>
                                <option value="true">Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="closeModal"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-600 dark:text-white">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── ASSIGN MODAL ─────────────────────────────────────────────────────── --}}
        <div id="assignModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-2xl rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-700">

                <div class="mb-5">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Assign Budget</h2>
                    <p id="assignModalLabel" class="mt-0.5 text-sm text-indigo-600 dark:text-indigo-300 font-medium"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Company Column --}}
                    <div class="flex flex-col">
                        <p class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Company</p>
                        <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-500" style="max-height:260px">
                            <label class="flex shrink-0 cursor-pointer items-center gap-2.5 border-b border-gray-200 bg-indigo-50 px-3 py-2.5 hover:bg-indigo-100 dark:border-gray-600 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50">
                                <input type="checkbox" id="allCompanies" class="h-4 w-4 accent-indigo-600">
                                <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">All Companies</span>
                            </label>
                            <div class="shrink-0 border-b border-gray-100 px-2 py-1.5 dark:border-gray-600">
                                <input type="text" id="companySearch" placeholder="Search company…"
                                    autocomplete="off"
                                    class="w-full rounded border-0 bg-gray-50 px-2 py-1 text-xs outline-none ring-0 focus:ring-1 focus:ring-indigo-400 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                            </div>
                            <div id="companyList" class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-600"></div>
                        </div>
                        <p id="companyCountHint" class="mt-1.5 text-xs text-gray-400"></p>
                    </div>

                    {{-- Department Column --}}
                    <div class="flex flex-col">
                        <p class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Department</p>
                        <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-500" style="max-height:260px">
                            <label class="flex shrink-0 cursor-pointer items-center gap-2.5 border-b border-gray-200 bg-indigo-50 px-3 py-2.5 hover:bg-indigo-100 dark:border-gray-600 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50">
                                <input type="checkbox" id="allDepartments" class="h-4 w-4 accent-indigo-600">
                                <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">All Departments</span>
                            </label>
                            <div class="shrink-0 border-b border-gray-100 px-2 py-1.5 dark:border-gray-600">
                                <input type="text" id="deptSearch" placeholder="Search department…"
                                    autocomplete="off"
                                    class="w-full rounded border-0 bg-gray-50 px-2 py-1 text-xs outline-none ring-0 focus:ring-1 focus:ring-indigo-400 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400">
                            </div>
                            <div id="departmentList" class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-600"></div>
                        </div>
                        <p id="deptCountHint" class="mt-1.5 text-xs text-gray-400"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input type="checkbox" id="assignIsBudget" class="h-4 w-4 accent-indigo-600">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Need Budget ?</span>
                    </label>
                </div>

                <div id="assignSummary"
                    class="mt-3 rounded-lg bg-gray-50 px-3 py-2.5 text-sm text-gray-400 dark:bg-gray-600 dark:text-gray-400">
                    No selection — choose at least one company and one department
                </div>

                <div id="assignPreview" class="hidden mt-2">
                    <div class="mb-1 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preview</p>
                        <span id="previewNote" class="text-xs text-gray-400 dark:text-gray-500"></span>
                    </div>
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-500">
                        <div class="overflow-y-auto" style="max-height:160px">
                            <table class="w-full text-xs">
                                <thead class="sticky top-0 border-b border-gray-200 bg-gray-50 dark:border-gray-500 dark:bg-gray-600">
                                    <tr>
                                        <th class="w-24 px-3 py-1.5 text-left font-semibold text-gray-500 dark:text-gray-300">Company</th>
                                        <th class="px-3 py-1.5 text-left font-semibold text-gray-500 dark:text-gray-300">Department</th>
                                        <th class="w-20 px-3 py-1.5 text-center font-semibold text-gray-500 dark:text-gray-300">Is Budget</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody" class="divide-y divide-gray-100 dark:divide-gray-600"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" id="closeAssignModal"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-600 dark:text-white">
                        Cancel
                    </button>
                    <button type="button" id="saveAssignBtn"
                        class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Preview
                    </button>
                </div>
            </div>
        </div>

        {{-- ── EDIT BUDGET MODAL ────────────────────────────────────────────────── --}}
        <div id="editBudgetModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-700">
                <h2 class="mb-5 text-base font-bold text-gray-800 dark:text-white">Edit Assignment</h2>

                <form id="editBudgetForm">
                    @csrf
                    <input type="hidden" id="editBudgetId">
                    <input type="hidden" id="editBudgetCpnyId">
                    <input type="hidden" id="editBudgetDeptId">

                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Company</label>
                        <div id="editBudgetCpnyDisplay"
                            class="rounded-lg border bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500"></div>
                    </div>

                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
                        <div id="editBudgetDeptDisplay"
                            class="rounded-lg border bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500"></div>
                    </div>

                    <div class="mb-5">
                        <label class="flex cursor-pointer items-center gap-2.5">
                            <input type="checkbox" id="editIsBudget" class="h-4 w-4 accent-indigo-600">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Is Budget</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" id="closeEditBudgetModal"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-600 dark:text-white">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if($isCostCtrl)
        {{-- ── ASSIGN GROUP BIAYA MODAL (COSTCTRL) ──────────────────────────────── --}}
        <div id="ccAssignGbModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-xl rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-700">
                <div class="mb-4">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Assign Group Biaya</h2>
                    <p id="ccGbModalLabel" class="mt-0.5 text-sm text-indigo-600 dark:text-indigo-300 font-medium"></p>
                </div>

                <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Select Group Biaya <span class="normal-case font-normal text-gray-400">(Ctrl+click or checkbox for multiple)</span>
                </div>

                <div class="mb-2">
                    <input type="text" id="ccGbPickSearch" placeholder="Search group biaya…"
                        class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-indigo-400 dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:placeholder-gray-400">
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-500" style="max-height:300px;overflow-y:auto" id="ccGbPickList">
                </div>

                <p id="ccGbPickCount" class="mt-1.5 text-xs text-gray-400"></p>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" id="ccCloseGbModal"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-600 dark:text-white">
                        Cancel
                    </button>
                    <button type="button" id="ccSaveGbBtn"
                        class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Assign Selected
                    </button>
                </div>
            </div>
        </div>

        {{-- ── ASSIGN COA MODAL (COSTCTRL) ──────────────────────────────────────── --}}
        <div id="ccAssignCoaModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-xl rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-700">
                <div class="mb-4">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Assign COA Account</h2>
                    <p id="ccCoaModalLabel" class="mt-0.5 text-sm text-indigo-600 dark:text-indigo-300 font-medium"></p>
                </div>

                {{-- Step 1: Business Unit --}}
                <div class="mb-3">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Business Unit</label>
                    <select id="ccBuSelect"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500">
                        <option value="">— select business unit —</option>
                    </select>
                </div>

                {{-- Step 2: COA list (loads after BU selected) --}}
                <div id="ccCoaPickSection" class="hidden">
                    <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Account (COA) <span class="normal-case font-normal text-gray-400">(Ctrl+click or checkbox for multiple)</span>
                    </div>
                    <div class="mb-2">
                        <input type="text" id="ccCoaPickSearch" placeholder="Search account…"
                            class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-indigo-400 dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:placeholder-gray-400">
                    </div>
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-500" style="max-height:260px;overflow-y:auto" id="ccCoaPickList">
                    </div>
                    <p id="ccCoaPickCount" class="mt-1.5 text-xs text-gray-400"></p>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" id="ccCloseCoaModal"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-600 dark:text-white">
                        Cancel
                    </button>
                    <button type="button" id="ccSaveCoaBtn"
                        class="hidden rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Assign Selected
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Loading overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700">Processing...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const IS_ADMIN = @json($isAdmin);

        function switchTab(tab) {
            const panels = { master: '#panel-master', budget: '#panel-budget' };
            const btns   = { master: '#tab-master',  budget: '#tab-budget'  };
            Object.keys(panels).forEach(key => {
                const isActive = key === tab;
                $(panels[key]).toggleClass('hidden', !isActive);
                $(btns[key])
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isActive)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isActive);
            });
            if (tab === 'budget') {
                // slight delay so the panel is visible before measuring
                requestAnimationFrame(resizeCcPanel);
            }
        }

        function resizeCcPanel() {
            if (window.innerWidth < 768) return;
            const panel = document.querySelector('.cc-three-panel');
            if (!panel) return;
            panel.style.height = '0px';           // collapse so page won't scroll during measure
            const rect = panel.getBoundingClientRect();
            const available = window.innerHeight - rect.top - 16;
            if (available > 150) {
                panel.style.height = available + 'px';
            }
        }

        window.addEventListener('resize', resizeCcPanel);

        function showLoading() { $('#loadingOverlay').removeClass('hidden').addClass('flex'); }
        function hideLoading() { $('#loadingOverlay').removeClass('flex').addClass('hidden'); }

        function ynBadge(value) {
            const v = String(value ?? '').toLowerCase();
            if (value === true || value === 1 || v === '1' || v === 'true' || v === 't' || v === 'y') {
                return '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-0.5 rounded-full text-xs">Yes</span>';
            }
            return '<span class="bg-gray-200/60 text-gray-500 font-semibold px-3 py-0.5 rounded-full text-xs">No</span>';
        }

        function normalizeBool(value) {
            if (value === true || value === 1 || value === '1' || value === 'true' || value === 't' || value === 'Y') return 'true';
            return 'false';
        }

        function normalizeBoolCheck(value) {
            const v = String(value ?? '').toLowerCase();
            return value === true || value === 1 || v === '1' || v === 'true' || v === 't' || v === 'y';
        }

        $(document).ready(function () {
            let nextGroupbiayaId        = @json($nextGroupbiayaId ?? '');
            let selectedGroupbiayaId    = null;
            let selectedGroupbiayaDescr = null;
            let companiesCache          = null;
            let departmentsCache        = null;
            let budgetTable             = null;
            let previewMode             = false;

            // ── Tab 1: Group Biaya DataTable ─────────────────────────────────────
            const gbColumns = [];

            if (IS_ADMIN) {
                gbColumns.push({
                    data: 'id',
                    className: 'text-center',
                    width: '56px',
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="flex justify-center items-center gap-1">
                                <label class="switch" style="transform:scale(0.8)">
                                    <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                    <span class="slider round"></span>
                                </label>
                                <button class="editGroupBiayaBtn bg-blue-500 text-white px-1.5 py-0.5 rounded text-xs" data-id="${data}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>`;
                    }
                });
            }

            gbColumns.push(
                { data: 'groupbiaya_id', width: '70px', className: 'text-xs font-mono text-gray-500 dark:text-gray-400 px-2 py-2.5' },
                { data: 'groupbiayadescr', className: 'text-sm px-2 py-2.5 text-gray-800 dark:text-gray-100' }
            );

            let table = $('#groupBiayaTable').DataTable({
                ajax: {
                    url: "{{ route('groupbiayanonpurch.json') }}",
                    type: 'GET',
                    dataSrc: 'data',
                    cache: false,
                    data: d => { d._t = Date.now(); }
                },
                processing: true,
                serverSide: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex flex-wrap items-center gap-2 mb-2"lBf>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Master_Group_Biaya',
                        className: 'bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Master_Group_Biaya',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700',
                        exportOptions: { columns: ':visible' }
                    }
                ],
                language: { search: '', searchPlaceholder: 'Search...' },
                columns: gbColumns,
            });

            // Row click on Tab 1 → go to Tab 2 with that group biaya selected
            $('#groupBiayaTable tbody').on('click', 'tr', function (e) {
                if ($(e.target).closest('.editGroupBiayaBtn, .toggleStatus, label, .slider').length) return;
                let data = table.row(this).data();
                if (!data) return;

                selectedGroupbiayaId    = data.groupbiaya_id;
                selectedGroupbiayaDescr = data.groupbiayadescr;

                $('#groupBiayaTable tbody tr').removeClass('gb-selected');
                $(this).addClass('gb-selected');

                $('#selectedIdBadge').text(selectedGroupbiayaId);
                $('#selectedDescrText').text(selectedGroupbiayaDescr);
                $('#rightEmpty').addClass('hidden');
                $('#rightContent').removeClass('hidden');

                switchTab('budget');
                loadBudgetTable(selectedGroupbiayaId);
            });

            // ── Tab 2: Budget Assignment DataTable ───────────────────────────────
            function loadBudgetTable(groupbiayaId) {
                if (budgetTable) {
                    budgetTable.ajax.url(`/groupbiaya-nonpurch/${groupbiayaId}/budget`).load();
                    return;
                }

                const budgetColumns = [
                    { data: null, defaultContent: '' },
                ];

                if (IS_ADMIN) {
                    budgetColumns.push({
                        data: 'id', className: 'text-center', width: '80px', orderable: false,
                        render: function (data, type, row) {
                            return `
                                <div class="flex justify-center items-center gap-1">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleBudgetStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button class="editBudgetBtn bg-blue-500 text-white px-2 py-1 rounded text-xs" data-id="${data}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>`;
                        }
                    });
                }

                budgetColumns.push(
                    { data: 'cpny_id', className: 'text-sm px-3' },
                    { data: 'department_id', className: 'text-sm px-3' },
                    { data: 'is_budget', className: 'text-center', render: d => ynBadge(d) },
                    {
                        data: 'status', className: 'text-center',
                        render: d => d === 'A'
                            ? '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-0.5 rounded-full text-xs">Active</span>'
                            : '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-0.5 rounded-full text-xs">Inactive</span>'
                    }
                );

                budgetTable = $('#budgetTable').DataTable({
                    ajax: {
                        url: `/groupbiaya-nonpurch/${groupbiayaId}/budget`,
                        type: 'GET',
                        dataSrc: 'data',
                    },
                    processing: true,
                    serverSide: false,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                    responsive: { details: { type: 'column', target: 0 } },
                    columnDefs: [{ targets: 0, width: '28px', className: 'dtr-control', orderable: false }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                    columns: budgetColumns,
                });
            }

            @if($isAdmin)
            // ── GROUP BIAYA: Add / Edit ──────────────────────────────────────────
            $('#addGroupBiayaBtn').click(function () {
                $('#modalTitle').text('Add Group Biaya');
                $('#groupBiayaForm')[0].reset();
                $('#id').val('');
                $('#groupbiaya_id').val(nextGroupbiayaId).prop('readonly', true)
                    .addClass('cursor-not-allowed bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300');
                $('#is_budget').val('false');
                $('#is_deposit').val('false');
                $('#groupBiayaModal').removeClass('hidden');
            });

            $(document).on('click', '.editGroupBiayaBtn', function () {
                let id = $(this).data('id');
                showLoading();
                $.get(`/groupbiaya-nonpurch/${id}/edit`, function (row) {
                    $('#modalTitle').text('Edit Group Biaya');
                    $('#id').val(row.id);
                    $('#groupbiaya_id').val(row.groupbiaya_id).prop('readonly', true)
                        .addClass('cursor-not-allowed bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300');
                    $('#groupbiayadescr').val(row.groupbiayadescr);
                    $('#is_budget').val(normalizeBool(row.is_budget));
                    $('#is_deposit').val(normalizeBool(row.is_deposit));
                    $('#groupBiayaModal').removeClass('hidden');
                    hideLoading();
                }).fail(function (xhr) {
                    hideLoading();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data' });
                });
            });

            $(document).on('change', '.toggleStatus', function () {
                let id = $(this).data('id'), newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/groupbiaya-nonpurch/${id}/toggle-status`, type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: () => table.ajax.reload(null, false),
                    error: xhr => {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
                    }
                });
            });

            $('#groupBiayaForm').submit(function (e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = id ? `/groupbiaya-nonpurch/${id}` : "{{ route('groupbiayanonpurch.store') }}";
                let formData = new FormData(document.getElementById('groupBiayaForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $('#groupBiayaForm button[type="submit"]').prop('disabled', true);
                $.ajax({
                    url, type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData, processData: false, contentType: false,
                    success: function (res) {
                        hideLoading();
                        $('#groupBiayaForm button[type="submit"]').prop('disabled', false);
                        if (!res || res.success === false) {
                            Swal.fire({ icon: 'error', title: 'Error', text: res?.message || 'Failed' });
                            return;
                        }
                        if (res.groupbiaya_id) {
                            const m = String(res.groupbiaya_id).match(/^GB(\d+)$/);
                            if (m) nextGroupbiayaId = 'GB' + String(parseInt(m[1]) + 1).padStart(3, '0');
                        }
                        $('#groupBiayaModal').addClass('hidden');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1500, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        hideLoading();
                        $('#groupBiayaForm button[type="submit"]').prop('disabled', false);
                        let msg = 'Failed to save';
                        if (xhr.status === 422 && xhr.responseJSON?.errors)
                            msg = Object.values(xhr.responseJSON.errors).map(a => a.join(', ')).join('\n');
                        else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });

            $('#closeModal').click(function () {
                $('#groupBiayaForm')[0].reset();
                $('#id').val('');
                $('#groupBiayaModal').addClass('hidden');
            });

            // ── ASSIGN MODAL ─────────────────────────────────────────────────────
            function fetchCompanies(cb) {
                if (companiesCache) { cb(companiesCache); return; }
                $.getJSON("{{ route('groupbiayanonpurch.companies') }}", d => { companiesCache = d; cb(d); });
            }

            function fetchDepartments(cb) {
                if (departmentsCache) { cb(departmentsCache); return; }
                $.getJSON("{{ route('groupbiayanonpurch.departments') }}", d => { departmentsCache = d; cb(d); });
            }

            function updateSummary() {
                let allCo   = $('#allCompanies').is(':checked');
                let allDept = $('#allDepartments').is(':checked');
                let cCount  = allCo   ? (companiesCache   ? companiesCache.length   : 0) : $('.companyCheck:checked').length;
                let dCount  = allDept ? (departmentsCache ? departmentsCache.length : 0) : $('.deptCheck:checked').length;
                let total   = cCount * dCount;

                if (total > 0) {
                    $('#assignSummary')
                        .text(`${cCount} compan${cCount !== 1 ? 'ies' : 'y'} × ${dCount} department${dCount !== 1 ? 's' : ''} = ${total} assignment${total !== 1 ? 's' : ''} will be created`)
                        .removeClass('text-gray-400 dark:text-gray-400')
                        .addClass('text-indigo-600 dark:text-indigo-300 font-medium');
                } else {
                    $('#assignSummary')
                        .text('No selection — choose at least one company and one department')
                        .removeClass('text-indigo-600 dark:text-indigo-300 font-medium')
                        .addClass('text-gray-400 dark:text-gray-400');
                }
                if (previewMode) {
                    previewMode = false;
                    $('#saveAssignBtn').text('Preview');
                    $('#assignPreview').addClass('hidden');
                }
            }

            function renderCompanyList(companies) {
                $('#companyList').html(companies.map(c => `
                    <label class="flex cursor-pointer items-center gap-2.5 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <input type="checkbox" class="companyCheck h-4 w-4 accent-indigo-600" value="${c.cpny_id}">
                        <span class="text-sm text-gray-700 dark:text-gray-200">${c.cpny_id} <span class="text-gray-400">–</span> ${c.cpny_name}</span>
                    </label>`).join(''));
                $('#companyCountHint').text(companies.length + ' companies available');
            }

            function renderDepartmentList(departments) {
                $('#departmentList').html(departments.map(d => `
                    <label class="flex cursor-pointer items-center gap-2.5 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <input type="checkbox" class="deptCheck h-4 w-4 accent-indigo-600" value="${d.department_id}">
                        <span class="text-sm text-gray-700 dark:text-gray-200">${d.department_id} <span class="text-gray-400">–</span> ${d.department_name}</span>
                    </label>`).join(''));
                $('#deptCountHint').text(departments.length + ' departments available');
            }

            function renderPreview(companies, departments) {
                const MAX = 200;
                let isBudget = $('#assignIsBudget').is(':checked');
                let rows = [], total = companies.length * departments.length;
                outer: for (let c of companies) {
                    for (let d of departments) {
                        rows.push({ c, d });
                        if (rows.length >= MAX) break outer;
                    }
                }
                let budgeCell = isBudget
                    ? '<span class="text-green-600 font-semibold">Yes</span>'
                    : '<span class="text-gray-400">No</span>';
                $('#previewTableBody').html(rows.map(r => `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600/50">
                        <td class="px-3 py-1 font-mono text-gray-600 dark:text-gray-300">${r.c}</td>
                        <td class="px-3 py-1 text-gray-700 dark:text-gray-200">${r.d}</td>
                        <td class="px-3 py-1 text-center">${budgeCell}</td>
                    </tr>`).join(''));
                $('#previewNote').text(rows.length < total
                    ? `Showing first ${rows.length} of ${total} rows`
                    : `${total} row${total !== 1 ? 's' : ''} total`);
                $('#assignPreview').removeClass('hidden');
            }

            $(document).on('input', '#companySearch', function () {
                let q = $(this).val().toLowerCase();
                $('#companyList label').each(function () { $(this).toggle($(this).text().toLowerCase().includes(q)); });
            });

            $(document).on('input', '#deptSearch', function () {
                let q = $(this).val().toLowerCase();
                $('#departmentList label').each(function () { $(this).toggle($(this).text().toLowerCase().includes(q)); });
            });

            $('#assignBtn').click(function () {
                if (!selectedGroupbiayaId) return;
                $('#assignModalLabel').text(selectedGroupbiayaId + ' — ' + selectedGroupbiayaDescr);
                $('#allCompanies, #allDepartments, #assignIsBudget').prop('checked', false);
                $('#companyList, #departmentList').removeClass('pointer-events-none opacity-40');
                $('#companySearch, #deptSearch').val('');
                previewMode = false;
                $('#saveAssignBtn').text('Preview');
                $('#assignPreview').addClass('hidden');
                updateSummary();
                fetchCompanies(function (companies) {
                    renderCompanyList(companies);
                    fetchDepartments(function (departments) {
                        renderDepartmentList(departments);
                        updateSummary();
                        $('#assignModal').removeClass('hidden');
                    });
                });
            });

            $(document).on('change', '#allCompanies', function () {
                let on = $(this).is(':checked');
                $('.companyCheck').prop({ checked: on, disabled: on });
                $('#companyList').toggleClass('pointer-events-none opacity-40', on);
                updateSummary();
            });

            $(document).on('change', '#allDepartments', function () {
                let on = $(this).is(':checked');
                $('.deptCheck').prop({ checked: on, disabled: on });
                $('#departmentList').toggleClass('pointer-events-none opacity-40', on);
                updateSummary();
            });

            $(document).on('change', '.companyCheck, .deptCheck', updateSummary);

            $(document).on('change', '#assignIsBudget', function () {
                if (previewMode) {
                    previewMode = false;
                    $('#saveAssignBtn').text('Preview');
                    $('#assignPreview').addClass('hidden');
                }
            });

            $('#closeAssignModal').click(() => {
                $('#assignModal').addClass('hidden');
                previewMode = false;
                $('#saveAssignBtn').text('Preview');
                $('#assignPreview').addClass('hidden');
            });

            $('#saveAssignBtn').click(function () {
                let companies = $('#allCompanies').is(':checked')
                    ? companiesCache.map(c => c.cpny_id)
                    : $('.companyCheck:checked').map(function () { return $(this).val(); }).get();
                let departments = $('#allDepartments').is(':checked')
                    ? departmentsCache.map(d => d.department_id)
                    : $('.deptCheck:checked').map(function () { return $(this).val(); }).get();

                if (!companies.length || !departments.length) {
                    Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please select at least one company and one department.' });
                    return;
                }

                if (!previewMode) {
                    renderPreview(companies, departments);
                    previewMode = true;
                    $(this).text('Confirm Save');
                    return;
                }

                showLoading();
                $.ajax({
                    url: "{{ route('groupbiayanonpurch.budget.batch') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        groupbiaya_id: selectedGroupbiayaId,
                        companies,
                        departments,
                        is_budget: $('#assignIsBudget').is(':checked'),
                    }),
                    success: function (res) {
                        hideLoading();
                        if (!res.success) { Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' }); return; }
                        $('#assignModal').addClass('hidden');
                        previewMode = false;
                        $('#saveAssignBtn').text('Preview');
                        budgetTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Done', text: res.message, timer: 2500, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        hideLoading();
                        let msg = 'Failed to save assignments';
                        if (xhr.status === 422 && xhr.responseJSON?.errors)
                            msg = Object.values(xhr.responseJSON.errors).map(a => a.join(', ')).join('\n');
                        else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    }
                });
            });

            // ── EDIT BUDGET (individual row) ─────────────────────────────────────
            $(document).on('click', '.editBudgetBtn', function () {
                let id = $(this).data('id');
                showLoading();
                $.get(`/groupbiaya-nonpurch/budget/${id}/edit`, function (row) {
                    $('#editBudgetId').val(row.id);
                    $('#editBudgetCpnyId').val(row.cpny_id);
                    $('#editBudgetDeptId').val(row.department_id);
                    $('#editBudgetCpnyDisplay').text(row.cpny_id);
                    $('#editBudgetDeptDisplay').text(row.department_id);
                    $('#editIsBudget').prop('checked', normalizeBoolCheck(row.is_budget));
                    $('#editBudgetModal').removeClass('hidden');
                    hideLoading();
                }).fail(function () {
                    hideLoading();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load assignment' });
                });
            });

            $('#editBudgetForm').submit(function (e) {
                e.preventDefault();
                let id = $('#editBudgetId').val();
                showLoading();
                $('#editBudgetForm button[type="submit"]').prop('disabled', true);
                $.ajax({
                    url: `/groupbiaya-nonpurch/budget/${id}`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: {
                        _method: 'PUT',
                        cpny_id: $('#editBudgetCpnyId').val(),
                        department_id: $('#editBudgetDeptId').val(),
                        is_budget: $('#editIsBudget').is(':checked') ? 'true' : 'false',
                    },
                    success: function (res) {
                        hideLoading();
                        $('#editBudgetForm button[type="submit"]').prop('disabled', false);
                        if (!res.success) { Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' }); return; }
                        $('#editBudgetModal').addClass('hidden');
                        budgetTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1500, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        hideLoading();
                        $('#editBudgetForm button[type="submit"]').prop('disabled', false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save' });
                    }
                });
            });

            $(document).on('change', '.toggleBudgetStatus', function () {
                let id = $(this).data('id'), newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/groupbiaya-nonpurch/budget/${id}/toggle-status`, type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: () => budgetTable.ajax.reload(null, false),
                    error: xhr => {
                        budgetTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed' });
                    }
                });
            });

            $('#closeEditBudgetModal').click(() => $('#editBudgetModal').addClass('hidden'));
            @endif

            @if($isCostCtrl)
            // ── TAB 2: COST CONTROLLER 3-PANEL ──────────────────────────────────
            let ccSelectedCpnyId    = null;
            let ccSelectedDeptId    = null;
            let ccSelectedDeptName  = null;
            let ccSelectedGbId      = null;
            let ccSelectedGbDescr   = null;
            let ccAllGbCache        = null;
            let ccBuCache           = null;
            let ccCurrentCoaCache   = null;

            // Load companies for this user on page load
            $.getJSON("{{ route('groupbiayanonpurch.cc.companies') }}", function (res) {
                let companies = res.data || [];
                if (companies.length === 0) {
                    // No company mapping — load all departments
                    loadCcDepartments(null);
                    return;
                }
                if (companies.length === 1) {
                    // Auto-select the single company, hide bar
                    ccSelectedCpnyId = companies[0].cpny_id;
                    loadCcDepartments(ccSelectedCpnyId);
                    return;
                }
                // Multiple companies: render pill tabs
                $('#ccCompanyBar').removeClass('hidden');
                $('#ccCompanyPills').html(companies.map(c => `
                    <button class="cc-cpny-pill px-3 py-1 rounded-full text-xs font-semibold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-indigo-400 hover:text-indigo-600 transition-colors"
                            data-id="${c.cpny_id}" data-name="${c.cpny_name}">
                        ${c.cpny_id}
                    </button>`).join(''));

                // Auto-select first company
                let first = companies[0];
                selectCompany(first.cpny_id);
            });

            function selectCompany(cpnyId) {
                ccSelectedCpnyId   = cpnyId;
                ccSelectedDeptId   = null;
                ccSelectedDeptName = null;
                ccSelectedGbId     = null;
                ccAllGbCache       = null;
                ccBuCache          = null;

                $('.cc-cpny-pill')
                    .removeClass('bg-indigo-600 text-white border-indigo-600 dark:bg-indigo-700 dark:border-indigo-500')
                    .addClass('bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600');
                $(`.cc-cpny-pill[data-id="${cpnyId}"]`)
                    .removeClass('bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600')
                    .addClass('bg-indigo-600 text-white border-indigo-600 dark:bg-indigo-700 dark:border-indigo-500');

                // Reset center & right panels
                $('#ccDeptLabel').text('— select a department —');
                $('#ccAssignGbBtn').addClass('hidden');
                $('#ccGbEmpty').removeClass('hidden');
                $('#ccGbContent').addClass('hidden').html('');
                $('#ccGbLabel').text('— select a group biaya —');
                $('#ccAssignCoaBtn').addClass('hidden');
                $('#ccCoaEmpty').removeClass('hidden');
                $('#ccCoaContent').addClass('hidden');

                loadCcDepartments(cpnyId);
            }

            $(document).on('click', '.cc-cpny-pill', function () {
                selectCompany($(this).data('id'));
            });

            function loadCcDepartments(cpnyId) {
                $('#deptFinList').html('<p class="px-3 py-4 text-xs text-center text-gray-400">Loading…</p>');
                let params = cpnyId ? { cpny_id: cpnyId } : {};
                $.getJSON("{{ route('groupbiayanonpurch.cc.departments-fin') }}", params, function (res) {
                    renderDeptFinList(res.data || []);
                });
            }

            function renderDeptFinList(depts) {
                if (!depts.length) {
                    $('#deptFinList').html('<p class="px-3 py-4 text-xs text-gray-400 text-center">No departments found</p>');
                    return;
                }
                $('#deptFinList').html(depts.map(d => `
                    <div class="cc-dept-item flex cursor-pointer items-center gap-2 px-3 py-2.5 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                         data-id="${d.department_fin_id}" data-name="${d.department_name}">
                        <span class="text-xs font-mono text-gray-500 dark:text-gray-400 w-16 shrink-0">${d.department_fin_id}</span>
                        <span class="text-xs text-gray-700 dark:text-gray-200 leading-tight">${d.department_name}</span>
                    </div>`).join(''));
            }

            // Dept fin search filter
            $('#deptFinSearch').on('input', function () {
                let q = $(this).val().toLowerCase();
                $('#deptFinList .cc-dept-item').each(function () {
                    $(this).toggle($(this).text().toLowerCase().includes(q));
                });
            });

            // Click dept → load center panel
            $(document).on('click', '.cc-dept-item', function () {
                ccSelectedDeptId   = $(this).data('id');
                ccSelectedDeptName = $(this).data('name');
                ccSelectedGbId     = null;
                ccSelectedGbDescr  = null;

                $('#deptFinList .cc-dept-item').removeClass('bg-indigo-100 dark:bg-indigo-900/40 font-semibold');
                $(this).addClass('bg-indigo-100 dark:bg-indigo-900/40 font-semibold');

                $('#ccDeptLabel').text(ccSelectedDeptId + ' — ' + ccSelectedDeptName);
                $('#ccAssignGbBtn').removeClass('hidden');

                // Reset right panel
                $('#ccGbLabel').text('— select a group biaya —');
                $('#ccAssignCoaBtn').addClass('hidden');
                $('#ccCoaEmpty').removeClass('hidden');
                $('#ccCoaContent').addClass('hidden');

                loadCcGroupbiaya(ccSelectedDeptId);
            });

            function loadCcGroupbiaya(deptId) {
                $('#ccGbEmpty').addClass('hidden');
                $('#ccGbContent').removeClass('hidden').html(
                    '<p class="px-4 py-6 text-xs text-center text-gray-400">Loading…</p>'
                );
                let params = ccSelectedCpnyId ? { cpny_id: ccSelectedCpnyId } : {};
                $.getJSON(`/groupbiaya-nonpurch/cc/${deptId}/groupbiaya`, params, function (res) {
                    let rows = res.data || [];
                    if (!rows.length) {
                        $('#ccGbContent').html('<p class="px-4 py-6 text-xs text-center text-gray-400">No group biaya assigned yet</p>');
                        return;
                    }
                    $('#ccGbContent').html(rows.map(r => `
                        <div class="cc-gb-item flex cursor-pointer items-center gap-2 px-4 py-2.5 hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors"
                             data-id="${r.groupbiaya_id}" data-descr="${r.groupbiayadescr}">
                            <span class="text-xs font-mono text-indigo-500 dark:text-indigo-400 w-14 shrink-0">${r.groupbiaya_id}</span>
                            <span class="text-xs text-gray-700 dark:text-gray-200 leading-tight">${r.groupbiayadescr}</span>
                        </div>`).join(''));
                });
            }

            // Click group biaya → load right panel (COA)
            $(document).on('click', '.cc-gb-item', function () {
                ccSelectedGbId    = $(this).data('id');
                ccSelectedGbDescr = $(this).data('descr');

                $('#ccGbContent .cc-gb-item').removeClass('bg-indigo-100 dark:bg-indigo-900/40 font-semibold');
                $(this).addClass('bg-indigo-100 dark:bg-indigo-900/40 font-semibold');

                $('#ccGbLabel').text(ccSelectedGbId + ' — ' + ccSelectedGbDescr);
                $('#ccAssignCoaBtn').removeClass('hidden');

                loadCcCoa(ccSelectedDeptId, ccSelectedGbId);
            });

            function loadCcCoa(deptId, gbId) {
                $('#ccCoaEmpty').addClass('hidden');
                $('#ccCoaContent').removeClass('hidden');
                $('#ccCoaTableBody').html('<tr><td colspan="3" class="px-4 py-4 text-xs text-center text-gray-400">Loading…</td></tr>');

                let params = ccSelectedCpnyId ? { cpny_id: ccSelectedCpnyId } : {};
                $.getJSON(`/groupbiaya-nonpurch/cc/${deptId}/${gbId}/coa`, params, function (res) {
                    let rows = res.data || [];
                    if (!rows.length) {
                        $('#ccCoaTableBody').html('<tr><td colspan="3" class="px-4 py-6 text-xs text-center text-gray-400">No COA assigned yet</td></tr>');
                        return;
                    }
                    $('#ccCoaTableBody').html(rows.map(r => `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400">${r.budget_business_unit_id ?? '—'}</td>
                            <td class="px-4 py-2 font-mono text-xs text-indigo-600 dark:text-indigo-400">${r.budget_account_id}</td>
                            <td class="px-4 py-2 text-xs text-gray-700 dark:text-gray-200">${r.account_descr ?? ''}</td>
                        </tr>`).join(''));
                });
            }

            // ── ASSIGN GROUP BIAYA MODAL ─────────────────────────────────────────
            $('#ccAssignGbBtn').click(function () {
                if (!ccSelectedDeptId) return;
                $('#ccGbModalLabel').text('Department: ' + ccSelectedDeptId + ' — ' + ccSelectedDeptName);
                $('#ccGbPickSearch').val('');
                $('#ccGbPickCount').text('');

                if (ccAllGbCache) {
                    renderGbPickList(ccAllGbCache);
                    $('#ccAssignGbModal').removeClass('hidden');
                    return;
                }
                showLoading();
                $.getJSON("{{ route('groupbiayanonpurch.cc.all-groupbiaya') }}", function (res) {
                    hideLoading();
                    ccAllGbCache = res.data || [];
                    renderGbPickList(ccAllGbCache);
                    $('#ccAssignGbModal').removeClass('hidden');
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load group biaya' }); });
            });

            function renderGbPickList(items) {
                $('#ccGbPickList').html(items.map(g => `
                    <label class="cc-gb-pick-item flex cursor-pointer items-center gap-2.5 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                        <input type="checkbox" class="ccGbCheck h-4 w-4 accent-indigo-600" value="${g.groupbiaya_id}">
                        <span class="font-mono text-xs text-indigo-500 dark:text-indigo-400 w-14 shrink-0">${g.groupbiaya_id}</span>
                        <span class="text-sm text-gray-700 dark:text-gray-200">${g.groupbiayadescr}</span>
                    </label>`).join(''));
                updateGbPickCount();
            }

            function updateGbPickCount() {
                let n = $('.ccGbCheck:checked').length;
                $('#ccGbPickCount').text(n > 0 ? `${n} selected` : '');
            }

            $(document).on('change', '.ccGbCheck', updateGbPickCount);

            $(document).on('input', '#ccGbPickSearch', function () {
                let q = $(this).val().toLowerCase();
                $('#ccGbPickList .cc-gb-pick-item').each(function () {
                    $(this).toggle($(this).text().toLowerCase().includes(q));
                });
            });

            $('#ccCloseGbModal').click(() => $('#ccAssignGbModal').addClass('hidden'));

            $('#ccSaveGbBtn').click(function () {
                let ids = $('.ccGbCheck:checked').map(function () { return $(this).val(); }).get();
                if (!ids.length) {
                    Swal.fire({ icon: 'warning', title: 'No selection', text: 'Please select at least one group biaya.' });
                    return;
                }
                showLoading();
                $.ajax({
                    url: "{{ route('groupbiayanonpurch.cc.assign-groupbiaya') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    contentType: 'application/json',
                    data: JSON.stringify({ cpny_id: ccSelectedCpnyId, department_fin_id: ccSelectedDeptId, groupbiaya_ids: ids }),
                    success: function (res) {
                        hideLoading();
                        if (!res.success) { Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' }); return; }
                        $('#ccAssignGbModal').addClass('hidden');
                        loadCcGroupbiaya(ccSelectedDeptId);
                        Swal.fire({ icon: 'success', title: 'Done', text: res.message, timer: 2000, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        hideLoading();
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save' });
                    }
                });
            });

            // ── ASSIGN COA MODAL ─────────────────────────────────────────────────
            $('#ccAssignCoaBtn').click(function () {
                if (!ccSelectedDeptId || !ccSelectedGbId) return;
                $('#ccCoaModalLabel').text(ccSelectedGbId + ' — ' + ccSelectedGbDescr + '  ·  Dept: ' + ccSelectedDeptId);
                $('#ccBuSelect').val('');
                $('#ccCoaPickSection').addClass('hidden');
                $('#ccSaveCoaBtn').addClass('hidden');
                $('#ccCoaPickList').html('');
                $('#ccCoaPickCount').text('');
                $('#ccCoaPickSearch').val('');

                if (ccBuCache) {
                    populateBuSelect(ccBuCache);
                    $('#ccAssignCoaModal').removeClass('hidden');
                    return;
                }
                showLoading();
                let buParams = ccSelectedCpnyId ? { cpny_id: ccSelectedCpnyId } : {};
                $.getJSON("{{ route('groupbiayanonpurch.cc.business-units') }}", buParams, function (res) {
                    hideLoading();
                    ccBuCache = res.data || [];
                    populateBuSelect(ccBuCache);
                    $('#ccAssignCoaModal').removeClass('hidden');
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load business units' }); });
            });

            function populateBuSelect(items) {
                let opts = '<option value="">— select business unit —</option>';
                opts += items.map(b => `<option value="${b.business_unit_id}">${b.business_unit_id} — ${b.business_unit_name}</option>`).join('');
                $('#ccBuSelect').html(opts);
            }

            $('#ccBuSelect').on('change', function () {
                let buId = $(this).val();
                if (!buId) { $('#ccCoaPickSection').addClass('hidden'); $('#ccSaveCoaBtn').addClass('hidden'); return; }

                $('#ccCoaPickSection').removeClass('hidden');
                $('#ccCoaPickList').html('<p class="px-3 py-4 text-xs text-center text-gray-400">Loading…</p>');
                $('#ccCoaPickSearch').val('');

                let coaParams = { department_fin_id: ccSelectedDeptId, business_unit_id: buId };
                if (ccSelectedCpnyId) coaParams.cpny_id = ccSelectedCpnyId;
                $.getJSON("{{ route('groupbiayanonpurch.cc.budget-coa') }}", coaParams, function (res) {
                    ccCurrentCoaCache = res.data || [];
                    renderCoaPickList(ccCurrentCoaCache);
                    $('#ccSaveCoaBtn').removeClass('hidden');
                }).fail(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load COA' }));
            });

            function renderCoaPickList(items) {
                if (!items.length) {
                    $('#ccCoaPickList').html('<p class="px-3 py-4 text-xs text-center text-gray-400">No accounts found for this selection</p>');
                    return;
                }
                $('#ccCoaPickList').html(items.map(a => `
                    <label class="cc-coa-pick-item flex cursor-pointer items-center gap-2.5 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                        <input type="checkbox" class="ccCoaCheck h-4 w-4 accent-emerald-600" value="${a.account_id}">
                        <span class="font-mono text-xs text-indigo-500 dark:text-indigo-400 w-28 shrink-0">${a.account_id}</span>
                        <span class="text-sm text-gray-700 dark:text-gray-200">${a.account_descr ?? ''}</span>
                    </label>`).join(''));
                updateCoaPickCount();
            }

            function updateCoaPickCount() {
                let n = $('.ccCoaCheck:checked').length;
                $('#ccCoaPickCount').text(n > 0 ? `${n} selected` : '');
            }

            $(document).on('change', '.ccCoaCheck', updateCoaPickCount);

            $(document).on('input', '#ccCoaPickSearch', function () {
                let q = $(this).val().toLowerCase();
                $('#ccCoaPickList .cc-coa-pick-item').each(function () {
                    $(this).toggle($(this).text().toLowerCase().includes(q));
                });
            });

            $('#ccCloseCoaModal').click(() => $('#ccAssignCoaModal').addClass('hidden'));

            $('#ccSaveCoaBtn').click(function () {
                let accountIds = $('.ccCoaCheck:checked').map(function () { return $(this).val(); }).get();
                if (!accountIds.length) {
                    Swal.fire({ icon: 'warning', title: 'No selection', text: 'Please select at least one account.' });
                    return;
                }
                showLoading();
                $.ajax({
                    url: "{{ route('groupbiayanonpurch.cc.assign-coa') }}",
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        cpny_id:           ccSelectedCpnyId,
                        department_fin_id: ccSelectedDeptId,
                        groupbiaya_id:     ccSelectedGbId,
                        business_unit_id:  $('#ccBuSelect').val(),
                        account_ids:       accountIds,
                    }),
                    success: function (res) {
                        hideLoading();
                        if (!res.success) { Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed' }); return; }
                        $('#ccAssignCoaModal').addClass('hidden');
                        loadCcCoa(ccSelectedDeptId, ccSelectedGbId);
                        Swal.fire({ icon: 'success', title: 'Done', text: res.message, timer: 2000, showConfirmButton: false });
                    },
                    error: function (xhr) {
                        hideLoading();
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save' });
                    }
                });
            });
            @endif
        });
    </script>

    <style>
        #groupBiayaTable tbody tr { cursor: pointer; }
        #groupBiayaTable tbody tr.gb-selected { background-color: #e0e7ff !important; }
        .dark #groupBiayaTable tbody tr.gb-selected { background-color: #2e2e6e !important; }
        #groupBiayaTable tbody tr:hover:not(.gb-selected) { background-color: #f8fafc; }
        #groupBiayaTable_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 4px 10px;
            font-size: 0.875rem;
            outline: none;
        }
        #groupBiayaTable_wrapper .dataTables_filter input:focus { border-color: #6366f1; }

        /* ── 3-Panel Responsive Scroll ─────────────────────────────────────────── */

        /* Mobile / stacked: each panel body has a fixed scroll height */
        .cc-scroll-body {
            overflow-y: auto;
            height: 40vh;
        }
        @media (min-width: 640px) {
            .cc-scroll-body { height: 45vh; }
        }

        /* md+: side-by-side. Height driven by JS; CSS cap prevents pre-JS overflow. */
        @media (min-width: 768px) {
            #panel-budget {
                overflow: hidden;           /* prevents page-level scroll */
            }
            .cc-three-panel {
                overflow: hidden;
                max-height: calc(100vh - 100px); /* hard cap fallback */
            }
            .cc-panel-left,
            .cc-panel-center,
            .cc-panel-right {
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            .cc-scroll-body {
                flex: 1 1 0%;
                height: auto;
                overflow-y: auto;
            }
        }

        /* Thin scrollbar */
        .cc-scroll-body::-webkit-scrollbar { width: 4px; }
        .cc-scroll-body::-webkit-scrollbar-track { background: transparent; }
        .cc-scroll-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }
        .dark .cc-scroll-body::-webkit-scrollbar-thumb { background: #4b5563; }
        .cc-scroll-body::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</x-app-layout>
