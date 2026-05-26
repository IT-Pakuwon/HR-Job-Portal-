<div class="col-span-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">

    <div class="flex items-start justify-between px-6 pt-6">

        <div>

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Monthly Ticket Trend
            </p>

            <div class="mt-2 flex items-end gap-3">

                <h3 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    248
                </h3>

                <span
                    class="mb-1 inline-flex items-center rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">

                    <svg class="mr-1 h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M5.22 12.22a.75.75 0 001.06 1.06L10 9.56l3.72 3.72a.75.75 0 001.06-1.06l-4.25-4.25a.75.75 0 00-1.06 0l-4.25 4.25z" />
                    </svg>

                    +12.4%

                </span>

            </div>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Compared to previous month
            </p>

        </div>

        <div x-data="{ open:false }" class="relative">

            <button
                @click="open=!open"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-white/5">

                <svg class="h-5 w-5 fill-current" viewBox="0 0 32 32">
                    <circle cx="16" cy="16" r="2" />
                    <circle cx="10" cy="16" r="2" />
                    <circle cx="22" cy="16" r="2" />
                </svg>

            </button>

            <div
                x-show="open"
                x-transition
                @click.outside="open=false"
                class="absolute right-0 z-20 mt-2 w-40 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-slate-800">

                <a href="#" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                    View Details
                </a>

                <a href="#" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                    Export Data
                </a>

            </div>

        </div>

    </div>

    <div class="mt-6 h-64 px-2 pb-2">

        <canvas id="premiumLineChart"></canvas>

    </div>

</div>
