<div class="space-y-4">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                Approval Dashboard
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Waiting Approval • Approval History
            </p>
        </div>

        <div class="flex items-center gap-3">

            <div
                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">

                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                <span class="text-slate-500 dark:text-slate-400">
                    Last Refresh
                </span>

                <span id="approvalRefreshTime"
                    class="font-semibold text-slate-900 dark:text-white">
                    --
                </span>

            </div>

            <div
                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">

                <span class="text-slate-500 dark:text-slate-400">
                    Next refresh
                </span>

                <span id="approvalCountdown"
                    class="font-semibold tabular-nums text-slate-900 dark:text-white">
                    00:20
                </span>

            </div>

        </div>

    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-3">

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting Approval
                    </div>

                    <div id="waitingCount"
                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>

                <div class="rounded-lg bg-emerald-500/10 p-2.5">
                    ✅
                </div>

            </div>

        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting &gt; 3 Days
                    </div>

                    <div id="longWaitingCount"
                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>

                <div class="rounded-lg bg-amber-500/10 p-2.5">
                    ⏰
                </div>

            </div>

        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">

            <div class="flex items-center justify-between">

                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Approved Today
                    </div>

                    <div id="approvedTodayCount"
                        class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>

                <div class="rounded-lg bg-blue-500/10 p-2.5">
                    📋
                </div>

            </div>

        </div>

    </div>

    <div
        class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">

            <div class="flex flex-wrap gap-3">

                <button id="tab-waiting">
                    ✅ Waiting Approval
                </button>

                <button id="tab-history">
                    📋 Approval History
                </button>

            </div>

        </div>

        <div class="border-b border-slate-200 p-4 dark:border-slate-700">

            <div class="grid gap-3 lg:grid-cols-12">

                <div class="lg:col-span-5">

                    <select id="approvalDoctype"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                        <option value="ALL">
                            All Doctype
                        </option>

                        @foreach($doctypes ?? collect() as $dt)
                            <option value="{{ $dt->doctype }}">
                                {{ $dt->doctype }}
                                {{ $dt->doctype_descr ? ' - '.$dt->doctype_descr : '' }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="lg:col-span-5">

                    <input
                        id="approvalSearch"
                        type="text"
                        placeholder="Search document..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                </div>

                <div class="lg:col-span-2">

                    <div class="flex w-full justify-end gap-2">

                        <button
                            id="openAllWaiting"
                            class="flex-1 rounded-lg bg-black px-4 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">

                            🚀 Open All

                        </button>

                        <button
                            id="refreshApproval"
                            class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700">

                            🔄 Refresh

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <div class="p-4">

            <table
                id="approvalTable"
                class="display w-full text-xs">
            </table>

        </div>

    </div>

</div>

    <script src="{{ asset('assets/js/multidashboard/dashapproval.js') }}"></script>

