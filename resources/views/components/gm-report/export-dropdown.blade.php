{{--
    GM Report Export Dropdown
    Usage: <x-gm-report.export-dropdown
               :pdf="route('gm.export.pdf')"
               :csv="route('gm.export.csv')"
               :xlsx="route('gm.export.xlsx')"
           />
    JS dependency: gm-core.js (window.gmUtils.buildParams) must be loaded on the page.
--}}
@props([
    'pdf'  => '#',
    'csv'  => '#',
    'xlsx' => '#',
])

<div class="relative w-full sm:w-auto" id="gmExportWrap">

    <button id="gmExportBtn" type="button"
            onclick="document.getElementById('gmExportDropdown').classList.toggle('hidden')"
            class="flex w-full items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-white
                   px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm
                   transition hover:bg-slate-50 sm:w-auto sm:justify-start
                   dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300
                   dark:hover:bg-slate-800/50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Export
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 opacity-60" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div id="gmExportDropdown"
         class="hidden absolute right-0 top-full z-50 mt-1.5 min-w-[140px]
                rounded-xl border border-slate-200/80 bg-white py-1 shadow-lg
                dark:border-slate-700/60 dark:bg-slate-800">

        <a id="gmExport_pdf" href="{{ $pdf }}"
           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700
                  hover:bg-red-50 hover:text-red-600
                  dark:text-slate-300 dark:hover:bg-red-500/10 dark:hover:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0
                         0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Export PDF
        </a>

        <a id="gmExport_csv" href="{{ $csv }}"
           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700
                  hover:bg-emerald-50 hover:text-emerald-600
                  dark:text-slate-300 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1
                         1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
        </a>

        <a id="gmExport_xlsx" href="{{ $xlsx }}"
           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700
                  hover:bg-violet-50 hover:text-violet-600
                  dark:text-slate-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2
                         2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Export XLSX
        </a>

    </div>
</div>

@once
<script>
(function () {
    var exportRoutes = {
        pdf  : '{{ $pdf }}',
        csv  : '{{ $csv }}',
        xlsx : '{{ $xlsx }}',
    };

    function updateExportLinks() {
        var params = window.gmUtils ? window.gmUtils.buildParams() : '';
        ['pdf', 'csv', 'xlsx'].forEach(function (fmt) {
            var el = document.getElementById('gmExport_' + fmt);
            if (el) el.href = exportRoutes[fmt] + params;
        });
    }

    document.addEventListener('gm:filter', updateExportLinks);

    document.addEventListener('click', function (e) {
        var wrap = document.getElementById('gmExportWrap');
        var dd   = document.getElementById('gmExportDropdown');
        if (wrap && dd && !wrap.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });
})();
</script>
@endonce
