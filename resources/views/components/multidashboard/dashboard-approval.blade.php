@php $dashCd = 'DASHAPPROVAL'; @endphp
<div class="space-y-4" @unless ($embedded ?? false) x-data="{ panel: null }" @endunless>

    @unless ($embedded ?? false)
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                {{ ($myPage ?? false) ? 'My Page' : 'Approval Dashboard' }}
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ ($myPage ?? false) ? 'Waiting Approval • Approval History • My Document • My Message • My Files' : 'Waiting Approval • Approval History' }}
            </p>
        </div>

        <div class="flex items-center gap-2">

            <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-3 pr-1 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                <span class="text-slate-500 dark:text-slate-400">Next refresh</span>
                <span id="approvalCountdown" class="font-semibold tabular-nums text-slate-900 dark:text-white">--</span>
                <button id="refreshApproval" type="button" title="Refresh now"
                    class="flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>

            @unless (($myPage ?? false) || ($embedded ?? false))
            <button id="updateNotificationBtn" type="button" title="Update Notification"
                class="relative flex items-center justify-center rounded-full border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="updateNotificationBadge"
                    class="hidden absolute -right-1.5 -top-1.5 min-w-[18px] rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-[18px] text-white">
                    0
                </span>
            </button>
            @endunless

        </div>

    </div>

    @endunless
    @if (!(($myPage ?? false) || ($embedded ?? false)))
    {{-- the approval panel (Waiting Approval / Approval History) is this dashboard's "tabs" block, see blocks/dashboard-approval/tabs;
         a component does not see the page's variables, so the ones the panel reads go along --}}
    <x-multidashboard.dashboard-top :dash-cd="$dashCd"
        :block-data="['doctypes' => $doctypes ?? collect(), 'akses_cc' => $akses_cc ?? false, 'isGA' => $isGA ?? false, 'myPage' => false, 'embedded' => false]" />
    @else
    {{-- My Page / embedded: no top area, the panel stands alone --}}
    @include('components.panel.approval')
    @endif
</div>

