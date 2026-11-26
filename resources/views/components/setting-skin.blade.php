{{-- Shared look for the Settings pages (list, Create and Edit popups), matching the other restyled menus.
     Role classes: set-card (list card), set-head + set-title + set-ico + set-hint (card header), set-tabs (tab bar),
     set-pop (popup overlay: div > dialog > title/header + form), set-fields (any other box whose fields need the same look).
     Use together with the SPP list styles component. --}}
<style>
    /* ---- card header ---- */
    .set-head { display: flex; flex-wrap: wrap; align-items: center !important; gap: .75rem 1rem; padding: .875rem 1.25rem !important; }
    .set-title { display: flex; align-items: center; gap: .75rem; margin: 0; font-size: 1rem !important; line-height: 1.35; font-weight: 700; color: #1f2937; }
    .set-ico { display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: .75rem; background: var(--color-blue-600); color: #fff; font-size: .95rem; box-shadow: 0 1px 2px rgba(0, 0, 0, .08); }
    .set-hint { display: block; font-size: .75rem; line-height: 1.1rem; font-weight: 500; color: #6b7280; }
    .set-head > button, .set-head > a, .set-head > div > button, .set-head > div > a { min-height: 2.5rem; border-radius: .5rem; }
    .dark .set-title { color: #f3f4f6; }
    .dark .set-hint { color: #9ca3af; }

    /* ---- tabs ---- */
    .set-tabs { display: inline-flex !important; flex-wrap: wrap; gap: .25rem !important; margin-bottom: .75rem !important; padding: .25rem; background: #fff; border: 1px solid #e5e7eb !important; border-radius: .75rem; box-shadow: 0 1px 2px rgba(0, 0, 0, .05); }
    .set-tabs > button { border: 0 !important; border-radius: .5rem !important; padding: .5rem 1rem !important; background: transparent !important; color: #4b5563 !important; font-size: .875rem; font-weight: 600; transition: background-color .15s, color .15s; }
    .set-tabs > button:hover { background: #f3f4f6 !important; color: #111827 !important; }
    .set-tabs > button.text-indigo-600, .set-tabs > button.text-blue-600 { background: var(--color-blue-600) !important; color: #fff !important; box-shadow: 0 1px 2px rgba(0, 0, 0, .12); }
    .set-tabs ~ .set-card { border-radius: .75rem !important; border-top-width: 1px !important; }
    .dark .set-tabs { background: #0f172a; border-color: rgba(255, 255, 255, .1) !important; }
    .dark .set-tabs > button { color: #d1d5db !important; }
    .dark .set-tabs > button:hover { background: rgba(255, 255, 255, .06) !important; color: #fff !important; }

    /* the global table margin pushed the last column past the card edge */
    .dt-pretty .dataTables_wrapper.dt-inset > table.dataTable, .dt-pretty table.dataTable { margin-left: 0 !important; margin-right: 0 !important; }

    /* ---- row buttons built by the page scripts ---- */
    .set-card td button:is(.bg-blue-500, .bg-blue-600, .bg-indigo-500, .bg-indigo-600, .bg-yellow-500, .bg-amber-500, .bg-red-500, .bg-red-600, .bg-green-500, .bg-green-600, .bg-emerald-600) {
        display: inline-flex; align-items: center; justify-content: center; gap: .375rem; min-width: 2rem; min-height: 2rem;
        border: 1px solid #dbeafe !important; border-radius: .375rem !important; background: #eff6ff !important; color: #2563eb !important; font-size: .75rem; font-weight: 600; transition: all .2s ease;
    }
    .set-card td button:is(.bg-blue-500, .bg-blue-600, .bg-indigo-500, .bg-indigo-600, .bg-yellow-500, .bg-amber-500, .bg-red-500, .bg-red-600, .bg-green-500, .bg-green-600, .bg-emerald-600):hover { transform: translateY(-1px); box-shadow: 0 4px 10px -3px rgba(0, 0, 0, .25); background: #2563eb !important; border-color: #2563eb !important; color: #fff !important; }
    .set-card td button:is(.bg-yellow-500, .bg-amber-500) { border-color: #fde68a !important; background: #fffbeb !important; color: #92400e !important; }
    .set-card td button:is(.bg-yellow-500, .bg-amber-500):hover { background: #f59e0b !important; border-color: #f59e0b !important; color: #fff !important; }
    .set-card td button:is(.bg-red-500, .bg-red-600) { border-color: #fee2e2 !important; background: #fef2f2 !important; color: #dc2626 !important; }
    .set-card td button:is(.bg-red-500, .bg-red-600):hover { background: #dc2626 !important; border-color: #dc2626 !important; color: #fff !important; }
    .set-card td button:is(.bg-green-500, .bg-green-600, .bg-emerald-600) { border-color: #a7f3d0 !important; background: #ecfdf5 !important; color: #047857 !important; }
    .set-card td button:is(.bg-green-500, .bg-green-600, .bg-emerald-600):hover { background: #059669 !important; border-color: #059669 !important; color: #fff !important; }

    /* ---- fields: filters on the card, popups, and inline forms ---- */
    :is(.set-card, .set-pop, .set-fields) :is(input[type=text], input[type=email], input[type=number], input[type=password], input[type=date], input[type=time], input[type=datetime-local], input[type=month], input:not([type]), select:not([multiple]), textarea):not(.border-0):not(.dataTables_length select):not(.dataTables_filter input):not(.select2-search__field) {
        min-height: 2.5rem; border: 1px solid #d1d5db; border-radius: .5rem; background-color: #fff; font-size: .875rem; color: #1f2937; box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: border-color .15s, box-shadow .15s;
    }
    :is(.set-card, .set-pop, .set-fields) :is(input:not([type=checkbox]):not([type=radio]), select, textarea):not(.border-0):not(.dataTables_filter input):focus { outline: none; border-color: var(--color-blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    :is(.set-card, .set-pop, .set-fields) :is(input[readonly], input:disabled, select:disabled, textarea[readonly]):not([type=checkbox]):not([type=radio]) { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    :is(.set-pop, .set-fields) input[type=file] { width: 100%; padding: .375rem .75rem; border: 1px dashed #d1d5db; border-radius: .5rem; background: #fff; font-size: .875rem; color: #4b5563; }
    :is(.set-card, .set-pop, .set-fields) label.block:not(:has(input)) { margin-bottom: .375rem; font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }
    .dark :is(.set-card, .set-pop, .set-fields) :is(input:not([type=checkbox]):not([type=radio]), select, textarea):not(.border-0):not(.dataTables_length select):not(.dataTables_filter input) { background-color: #111827; border-color: #4b5563; color: #f3f4f6; }
    .dark :is(.set-card, .set-pop, .set-fields) label.block:not(:has(input)) { color: #9ca3af; }
    .dark :is(.set-pop, .set-fields) input[type=date] { color-scheme: dark; }

    /* Select2 inside cards and popups */
    :is(.set-card, .set-pop, .set-fields) .select2-container--default .select2-selection--single { height: 40px; display: flex; align-items: center; background: #fff; border: 1px solid #d1d5db; border-radius: .5rem; }
    :is(.set-card, .set-pop, .set-fields) .select2-container--default .select2-selection--single .select2-selection__rendered { width: 100%; line-height: 38px; padding-left: .75rem; font-size: .875rem; color: #1f2937; }
    :is(.set-card, .set-pop, .set-fields) .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 6px; }
    :is(.set-card, .set-pop, .set-fields) .select2-container--default .select2-selection--multiple { min-height: 40px; border: 1px solid #d1d5db; border-radius: .5rem; }
    :is(.set-card, .set-pop, .set-fields) .select2-container--default.select2-container--focus .select2-selection, :is(.set-card, .set-pop, .set-fields) .select2-container--default.select2-container--open .select2-selection { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    .dark :is(.set-card, .set-pop, .set-fields) .select2-container--default .select2-selection { background: #111827; border-color: #4b5563; }
    .dark :is(.set-card, .set-pop, .set-fields) .select2-container--default .select2-selection__rendered { color: #f3f4f6; }

    /* ---- popups: overlay > dialog > title (or header block) + form ---- */
    .set-pop { padding: 1rem; background: rgba(15, 23, 42, .5) !important; -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }
    .set-pop > div { max-height: 92vh; overflow-y: auto; padding: 0 !important; background: #fff; border-radius: 1rem !important; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, .35), 0 0 0 1px rgba(15, 23, 42, .05); animation: set-pop-in .18s ease-out; }
    @keyframes set-pop-in { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .set-pop > div { animation: none; } }
    .set-pop > div > h2, .set-pop > div > div:first-child:has(> h2) { position: sticky; top: 0; z-index: 5; margin: 0 !important; padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid #f1f5f9; }
    .set-pop > div > h2 { display: flex; align-items: center; gap: .75rem; }
    .set-pop > div > div:first-child:has(> h2) { display: grid; grid-template-columns: auto 1fr; column-gap: .75rem; align-items: center; }
    .set-pop > div > div:first-child:has(> h2) > * { grid-column: 2; }
    .set-pop > div > h2::before, .set-pop > div > div:first-child:has(> h2)::before { content: "\f044"; display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; border-radius: .75rem; background: var(--color-blue-600); color: #fff; font-family: "Font Awesome 6 Free"; font-size: 1rem; font-weight: 900; }
    .set-pop > div > div:first-child:has(> h2)::before { grid-column: 1; grid-row: 1 / span 3; }
    .set-pop h2 { font-size: 1rem !important; line-height: 1.4; font-weight: 700; color: #1f2937; }
    .set-pop > div > form { padding: 1.25rem 1.5rem 0; }
    .set-pop > div > div:not(:first-child), .set-pop > div > p { margin-left: 1.5rem; margin-right: 1.5rem; }
    .set-pop > div > div:first-child:has(> h2) + * { margin-top: 1.25rem; }

    /* footer bar: inside a form, or the last block of a popup without one */
    .set-pop form > div:last-child:has(> button), .set-pop > div > div:last-child:has(> button) {
        position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .75rem;
        margin: 1.25rem -1.5rem 0 !important; padding: 1rem 1.5rem; background: #fbfcfd; border-top: 1px solid #f1f5f9;
    }
    .set-pop > div > div:last-child:has(> button) { margin: 1.25rem 0 0 !important; }
    .set-pop :is(form > div:last-child, div > div:last-child) > button { margin: 0 !important; min-height: 2.5rem; border: 1px solid #d1d5db; border-radius: .5rem; padding: .5rem 1.5rem; background: #fff !important; color: #374151 !important; font-size: .875rem; font-weight: 600; box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: background-color .15s; }
    .set-pop :is(form > div:last-child, div > div:last-child) > button:hover { background: #f3f4f6 !important; }
    .set-pop :is(form > div:last-child, div > div:last-child) > button:is([type=submit], .bg-blue-500, .bg-blue-600, .bg-indigo-500, .bg-indigo-600, .bg-green-500, .bg-green-600, .bg-emerald-600) { border-color: var(--color-blue-600); background: var(--color-blue-600) !important; color: #fff !important; }
    .set-pop :is(form > div:last-child, div > div:last-child) > button:is([type=submit], .bg-blue-500, .bg-blue-600, .bg-indigo-500, .bg-indigo-600, .bg-green-500, .bg-green-600, .bg-emerald-600):hover { background: var(--color-blue-700) !important; }

    .dark .set-pop > div { background: #1f2937; }
    .dark .set-pop > div > h2, .dark .set-pop > div > div:first-child:has(> h2) { background: #1f2937; border-color: #374151; }
    .dark .set-pop h2 { color: #fff; }
    .dark .set-pop form > div:last-child:has(> button), .dark .set-pop > div > div:last-child:has(> button) { background: #1f2937; border-color: #374151; }
    .dark .set-pop :is(form > div:last-child, div > div:last-child) > button { border-color: #4b5563; background: #374151 !important; color: #e5e7eb !important; }
    .dark .set-pop :is(form > div:last-child, div > div:last-child) > button:is([type=submit], .bg-blue-500, .bg-blue-600, .bg-indigo-500, .bg-indigo-600, .bg-green-500, .bg-green-600, .bg-emerald-600) { border-color: var(--color-blue-600); background: var(--color-blue-600) !important; color: #fff !important; }
</style>
