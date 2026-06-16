<div class="space-y-4">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                HR Dashboard
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                PRF • Recruitment • Approval
            </p>

        </div>

        <div
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">

            <span class="h-2 w-2 rounded-lg bg-green-500"></span>

            <span class="text-slate-500 dark:text-slate-400">
                Next Refresh
            </span>

            <span id="dashboardRefreshTime" class="font-semibold text-slate-900 dark:text-white">

                --

            </span>

        </div>

    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting Approval
                    </div>

                    <div id="waitingApprovalCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">

                        0

                    </div>

                </div>

                <div class="rounded-lg bg-blue-500/10 p-2.5">
                    ✅
                </div>

            </div>

        </div>



        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting PRF
                    </div>

                    <div id="prfCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">

                        0

                    </div>

                </div>

                <div class="rounded-lg bg-orange-500/10 p-2.5">
                    📄
                </div>

            </div>

        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Unchecked Applicant
                    </div>

                    <div id="applicantCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">

                        0

                    </div>

                </div>

                <div class="rounded-lg bg-violet-500/10 p-2.5">
                    👤
                </div>

            </div>

        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Self Applicant
                    </div>

                    <div id="selfRegisterCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">

                        0

                    </div>

                </div>

                <div class="rounded-lg bg-cyan-500/10 p-2.5">
                    👥
                </div>

            </div>

        </div>

    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">

            <div class="flex flex-wrap gap-3">

                <button id="tab-approval">
                    ✅ Waiting Approval
                </button>

                <button id="tab-approval-history">
                    📋 Approval History
                </button>

                <button id="tab-prf">
                    📄 Waiting PRF
                </button>

                <button id="tab-applicant">
                    👤 Unchecked Applicant
                </button>

                <button id="tab-self-register">
                    👥 Self Register
                </button>

            </div>

        </div>

        <div class="border-b border-slate-200 p-4 dark:border-slate-700">

            <div class="grid gap-3 lg:grid-cols-12">

                <div class="lg:col-span-5">

                    <select id="dashboardFilter"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                        <option value="ALL">
                            All
                        </option>

                    </select>

                </div>

                <div class="lg:col-span-5">

                    <input id="dashboardSearch" type="text" placeholder="Search..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                </div>

                <div class="lg:col-span-2">

                    <div class="flex justify-end gap-2">

                        <button id="openAllDocument"
                            class="w-full flex-1 rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">

                            🚀 Open All

                        </button>

                        <button id="refreshDashboard"
                            class="w-full flex-1 rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700">

                            🔄 Refresh

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <div class="p-4">

            <table id="dashboardTable" class="display w-full text-xs">
            </table>

        </div>

    </div>

</div>

<script src="{{ asset('assets/js/multidashboard/dashhr.js') }}"></script>
