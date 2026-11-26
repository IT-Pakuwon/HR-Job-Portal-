{{-- Shared look for the Voucher & Product popups (Receive, Transfer, Usage, Settlement, Master).
     Give each popup root the class "vpl-pop"; its Select2 fields, tables and file inputs pick up this style. --}}
<style>
    /* the global table css breaks words anywhere, which split numbers and ids in the narrow popup columns */
    .vpl-pop table :is(th, td), .vpl-pop table :is(th, td) * { word-break: normal; overflow-wrap: normal; }
    .modal-panel { backface-visibility: hidden; }
    .modal-scroll { scrollbar-width: thin; }
    .apv-badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }

    /* the global table margin pushed the last column past the card edge */
    .dt-pretty table.dataTable,
    .dt-pretty .dataTables_wrapper.dt-inset > table.dataTable { margin-left: 0 !important; margin-right: 0 !important; }

    /* ---- Select2 fields inside popups ---- */
    .vpl-pop .select2-container { width: 100% !important; }
    .vpl-pop .select2-container--default .select2-selection--single {
        position: relative; height: 40px; display: flex; align-items: center; background: #fff;
        border: 1px solid #d1d5db; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: border-color .15s, box-shadow .15s;
    }
    .vpl-pop .select2-container--default .select2-selection--single .select2-selection__rendered {
        width: 100%; line-height: 38px; padding: 0 2rem 0 .75rem; font-size: .875rem; color: #1f2937;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .vpl-pop .select2-container--default .select2-selection--single:has(.select2-selection__clear) .select2-selection__rendered { padding-right: 3.5rem; }
    .vpl-pop .select2-container--default .select2-selection--single .select2-selection__clear { position: absolute; right: 2rem; top: 0; height: 38px; line-height: 36px; margin: 0; padding: 0; font-size: 1rem; font-weight: 500; color: #94a3b8; }
    .vpl-pop .select2-container--default .select2-selection--single .select2-selection__clear:hover { color: #ef4444; }
    .vpl-pop .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9ca3af; }
    .vpl-pop .select2-container--default .select2-selection--single .select2-selection__arrow {
        position: absolute; top: 0; right: 4px; width: 26px; height: 38px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E") no-repeat center / 16px;
        transition: transform .2s ease;
    }
    .vpl-pop .select2-container--default .select2-selection--single .select2-selection__arrow b { display: none !important; }
    .vpl-pop .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow { transform: rotate(180deg); }
    .vpl-pop .select2-container--default:hover .select2-selection--single { border-color: #93c5fd; }
    .vpl-pop .select2-container--default.select2-container--focus .select2-selection--single,
    .vpl-pop .select2-container--default.select2-container--open .select2-selection--single { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    .vpl-pop .select2-container--default.select2-container--disabled .select2-selection--single { background: #f3f4f6; border-color: #e5e7eb; cursor: not-allowed; }
    .vpl-pop .select2-container--default.select2-container--disabled .select2-selection__rendered { color: #9ca3af; }
    .dark .vpl-pop .select2-container--default .select2-selection--single { background: #1f2937; border-color: #4b5563; }
    .dark .vpl-pop .select2-container--default .select2-selection--single .select2-selection__rendered { color: #f3f4f6; }
    .dark .vpl-pop .select2-container--default.select2-container--disabled .select2-selection--single { background: rgba(255, 255, 255, .04); border-color: #374151; }

    /* dropdown lists are appended to <body>, so these cannot be scoped to the popup */
    .select2-dropdown { background: #fff; border: 1px solid #e5e7eb !important; border-radius: .75rem !important; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, .10); }
    .select2-container--open .select2-dropdown--below { margin-top: 4px; }
    .select2-results__option { padding: .5rem .875rem; font-size: .875rem; color: #374151; }
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background: #eff6ff; color: #1d4ed8; }
    .select2-container--default .select2-results__option[aria-selected=true] { background: #dbeafe; color: #1d4ed8; font-weight: 600; }
    .select2-search--dropdown { padding: .5rem; }
    .select2-search--dropdown .select2-search__field { border-radius: .5rem; border: 1px solid #d1d5db !important; padding: .35rem .6rem; color: #1f2937; }
    .dark .select2-dropdown { background: #1f2937; border-color: #374151 !important; }
    .dark .select2-results__option { color: #e5e7eb; }
    .dark .select2-container--default .select2-results__option--highlighted[aria-selected] { background: rgba(59, 130, 246, .2); color: #bfdbfe; }
    .dark .select2-container--default .select2-results__option[aria-selected=true] { background: rgba(59, 130, 246, .3); color: #dbeafe; }
    .dark .select2-search--dropdown .select2-search__field { background: #111827; border-color: #4b5563 !important; color: #f3f4f6; }

    /* date picker icon stays visible on dark fields */
    .dark .vpl-pop input[type=date] { color-scheme: dark; }

    /* SweetAlert2 dark mode */
    .dark .swal2-popup { background: #1e293b; color: #e2e8f0; }
    .dark .swal2-title, .dark .swal2-html-container { color: #e2e8f0; }
    .dark .swal2-close { color: #94a3b8; }
    .dark .swal2-input, .dark .swal2-textarea, .dark .swal2-select { background: #0f172a; color: #e2e8f0; border-color: #334155; }
    .dark .swal2-validation-message { background: #0f172a; color: #f87171; }

    /* Product search popup: long names wrap instead of being cut with "..." */
    #productSearchTable td:nth-child(2) { white-space: normal; overflow: visible; text-overflow: clip; max-width: 260px; }
</style>
