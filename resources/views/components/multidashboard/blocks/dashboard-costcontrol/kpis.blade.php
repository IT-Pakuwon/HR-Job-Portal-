{{-- KPI cards of dashboard-costcontrol. Placed by Dashboard Setup (DashboardLayout block "kpis"); dashboard-top includes it. --}}
    <div class="dsh-kpis">

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Waiting Approval</span>
            <span id="approvalCount" class="dsh-kpi-n">0</span>
            <span id="approvalBar" class="hidden" style="width:0%"></span>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Pending PO Mapping</span>
            <span id="poCount" class="dsh-kpi-n">0</span>
            <span id="poBar" class="hidden" style="width:0%"></span>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Pending Issue Mapping</span>
            <span id="issueCount" class="dsh-kpi-n">0</span>
            <span id="issueBar" class="hidden" style="width:0%"></span>
        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Budget Monitoring
                    </div>
                    <div id="budgetCount" class="mt-0.5 truncate text-xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-blue-500/10 p-2 text-base">
                    💰
                </div>
            </div>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">IM Budget</span>
            <span id="imBudgetCount" class="dsh-kpi-n">0</span>
            <span id="imBudgetBar" class="hidden" style="width:0%"></span>
        </div>

        @if($hasVpCollectionAccess ?? false)

            <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Expired Voucher (H-60/H-30)</span>
            <span id="expiredCount" class="dsh-kpi-n">0</span>
            <span id="expiredBar" class="hidden" style="width:0%"></span>
        </div>

            <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Waiting For Settlement</span>
            <span id="waitingSettlementCount" class="dsh-kpi-n">0</span>
            <span id="waitingSettlementBar" class="hidden" style="width:0%"></span>
        </div>

        @endif

    </div>
