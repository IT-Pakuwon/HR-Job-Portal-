<x-app-layout>

<div class="flex flex-col" style="height:calc(100dvh - 56px)">

    {{-- ── Toolbar ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-4 px-5 shrink-0 border-b border-slate-100 bg-white/95 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95"
         style="height:54px">

        {{-- Left: back + divider + editable name --}}
        <div class="flex items-center gap-3 min-w-0">

            <a href="{{ route('card-chart.catalog') }}"
               class="shrink-0 flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-400
                      hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Catalog
            </a>

            <span class="h-4 w-px bg-slate-200 dark:bg-slate-700 shrink-0"></span>

            {{-- Editable title --}}
            <div class="group flex items-center gap-1.5 min-w-0">
                <input id="dbd-name" type="text" value="My Dashboard"
                       class="min-w-0 w-44 rounded-lg bg-transparent px-2 py-1 text-sm font-bold text-slate-800 outline-none
                              hover:bg-slate-50 focus:bg-slate-50 focus:ring-2 focus:ring-violet-200 focus:ring-offset-0
                              dark:text-white dark:hover:bg-slate-800/60 dark:focus:bg-slate-800/60
                              dark:focus:ring-violet-800/40 transition duration-150">
                <svg class="h-3 w-3 shrink-0 text-slate-300 group-hover:text-slate-400 dark:text-slate-600 dark:group-hover:text-slate-500 transition"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
        </div>

        {{-- Right: count + actions --}}
        <div class="flex items-center gap-2 shrink-0">

            {{-- Section count pill --}}
            <span id="dbd-count"
                  class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-500
                         dark:bg-slate-800 dark:text-slate-400">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                0 sections
            </span>

            <span class="h-4 w-px bg-slate-200 dark:bg-slate-700"></span>

            {{-- Reset (icon only) --}}
            <button id="dbd-btn-clear" title="Reset dashboard"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400
                           hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20 dark:hover:text-red-400
                           active:scale-95 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>

            {{-- Save --}}
            <button id="dbd-btn-save"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold text-white
                           bg-linear-to-r from-violet-600 to-violet-500
                           shadow-sm shadow-violet-200 dark:shadow-violet-900/30
                           hover:from-violet-700 hover:to-violet-600 active:scale-95 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save
            </button>
        </div>
    </div>

    {{-- ── Canvas ───────────────────────────────────────────────────────── --}}
    <div id="dbd-canvas" class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 p-5">

        {{-- Empty state / Template picker --}}
        <div id="dbd-empty" class="py-10 px-4">
            <div class="mx-auto max-w-3xl">
                <div class="mb-8 text-center">
                    <div class="mb-3 mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <svg class="h-7 w-7 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zm0 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                    </div>
                    <p class="text-base font-bold text-slate-700 dark:text-slate-300">Start with a template</p>
                    <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">Choose a pre-built layout or start from scratch</p>
                </div>
                <div id="dbd-template-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 items-stretch">
                    {{-- Filled by JS --}}
                </div>
            </div>
        </div>

        {{-- Sections container --}}
        <div id="dbd-sections" class="space-y-5"></div>

        {{-- Add section footer button (shown after first section) --}}
        <div id="dbd-add-row" class="hidden pt-3 flex justify-center">
            <button id="dbd-btn-add-section"
                    class="inline-flex items-center gap-2 rounded-xl border-2 border-dashed border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-400 hover:border-violet-400 hover:text-violet-500 hover:bg-violet-50 transition dark:border-slate-700 dark:bg-slate-900 dark:hover:border-violet-500 dark:hover:text-violet-400 dark:hover:bg-violet-900/20">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Section
            </button>
        </div>

    </div>
</div>

{{-- ── Layout Picker Modal ─────────────────────────────────────────── --}}
<div id="dbd-modal-layout" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="dbd-layout-backdrop"></div>
    <div class="relative z-10 w-full max-w-xl rounded-2xl bg-white shadow-2xl dark:bg-slate-900 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Choose Section Layout</h3>
            <button id="dbd-layout-close"
                    class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 transition text-sm">✕</button>
        </div>
        <div id="dbd-layout-options" class="grid grid-cols-3 gap-3 p-6">
            {{-- Filled by JS --}}
        </div>
    </div>
</div>

{{-- ── Chart Picker Modal ──────────────────────────────────────────── --}}
<div id="dbd-modal-chart" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="dbd-chart-backdrop"></div>
    <div class="relative z-10 w-full max-w-2xl flex flex-col rounded-2xl bg-white shadow-2xl dark:bg-slate-900 overflow-hidden" style="max-height:85vh">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 shrink-0">
            <h3 class="text-base font-bold text-slate-800 dark:text-white">Choose a Chart</h3>
            <button id="dbd-chart-close"
                    class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 transition text-sm">✕</button>
        </div>
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <input id="dbd-chart-search" type="text" placeholder="Search charts..."
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none focus:border-violet-400 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500 transition">
        </div>
        <div id="dbd-chart-catalog" class="flex-1 overflow-y-auto p-5">
            {{-- Filled by JS --}}
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/card-chart/drag-dashboard.js') }}"></script>
@endpush

</x-app-layout>
