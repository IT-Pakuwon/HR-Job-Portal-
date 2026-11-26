{{-- Shared look for the Event Calendar popups: TomSelect fields (wrap the <select> in a div.ev-ts).
     Select2 fields on the same popups use .sf-field from the SPP form styles component. --}}
<style>
    .ev-ts .ts-wrapper.ts-wrapper { min-height: 40px; }
    .ev-ts .ts-wrapper.ts-wrapper .ts-control {
        display: flex; flex-wrap: wrap; align-items: center; gap: .25rem; min-height: 40px; padding: 3px 2rem 3px .75rem;
        background: #fff; border: 1px solid #d1d5db; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        font-size: .875rem; line-height: 1.25rem; color: #1f2937; transition: border-color .15s, box-shadow .15s;
    }
    .ev-ts .ts-wrapper.ts-wrapper:hover .ts-control { border-color: #93c5fd; }
    .ev-ts .ts-wrapper.ts-wrapper.focus .ts-control,
    .ev-ts .ts-wrapper.ts-wrapper.dropdown-active .ts-control { border-color: #3b82f6; border-radius: .5rem; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    .ev-ts .ts-wrapper.ts-wrapper .ts-control > input { font-size: .875rem; line-height: 1.25rem; color: inherit; }
    .ev-ts .ts-wrapper.ts-wrapper .ts-control > input::placeholder { color: #9ca3af; }

    /* chevron */
    .ev-ts .ts-wrapper.ts-wrapper .ts-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .6rem center; background-size: 1.1rem;
    }
    .ev-ts .ts-wrapper.ts-wrapper .ts-control::after { display: none !important; }

    /* multi-select chips */
    .ev-ts .ts-wrapper.ts-wrapper.multi.has-items .ts-control { padding-left: .375rem; }
    .ev-ts .ts-wrapper.ts-wrapper.multi .ts-control > div.item {
        display: inline-flex; align-items: center; gap: .375rem; margin: 0; padding: .125rem .25rem .125rem .5rem;
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: .375rem; color: #1d4ed8; font-size: .8125rem; text-shadow: none; box-shadow: none;
    }
    .ev-ts .ts-wrapper.ts-wrapper.multi .ts-control > div.item.active { background: #dbeafe; color: #1d4ed8; }
    .ev-ts .ts-wrapper.ts-wrapper.plugin-remove_button .item .remove {
        margin: 0; padding: 0 .25rem; border: 0 !important; border-radius: .25rem; color: #60a5fa; font-size: 1rem; line-height: 1;
    }
    .ev-ts .ts-wrapper.ts-wrapper.multi.has-items:not(.focus) .ts-control > input::placeholder { color: transparent; }
    .ev-ts .ts-wrapper.ts-wrapper.plugin-remove_button .item .remove:hover { background: transparent; color: #ef4444; }

    /* locked (location picked from the calendar row) */
    .ev-ts .ts-wrapper.ts-wrapper.disabled { opacity: 1; }
    .ev-ts .ts-wrapper.ts-wrapper.disabled .ts-control { background-color: #f3f4f6; border-color: #e5e7eb; color: #6b7280; opacity: 1; cursor: not-allowed; }

    /* dropdown */
    .ev-ts .ts-dropdown {
        z-index: 30; margin-top: 4px; overflow: hidden; background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .10); font-size: .875rem; color: #374151;
    }
    .ev-ts .ts-dropdown .option, .ev-ts .ts-dropdown .no-results { padding: .5rem .875rem; }
    .ev-ts .ts-dropdown .active { background: #eff6ff; color: #1d4ed8; }
    .ev-ts .ts-dropdown .selected { background: #dbeafe; color: #1d4ed8; font-weight: 600; }

    .dark .ev-ts .ts-wrapper.ts-wrapper .ts-control { background-color: #1f2937; border-color: #4b5563; color: #f3f4f6; }
    .dark .ev-ts .ts-wrapper.ts-wrapper .ts-control > input { background: transparent; color: #f3f4f6; }
    .dark .ev-ts .ts-wrapper.ts-wrapper.disabled .ts-control { background-color: rgba(255, 255, 255, .04); border-color: #374151; color: #9ca3af; }
    .dark .ev-ts .ts-wrapper.ts-wrapper.multi .ts-control > div.item { background: rgba(59, 130, 246, .18); border-color: rgba(59, 130, 246, .35); color: #bfdbfe; }
    .dark .ev-ts .ts-dropdown { background: #1f2937; border-color: #374151; color: #e5e7eb; }
    .dark .ev-ts .ts-dropdown .active { background: rgba(59, 130, 246, .18); color: #bfdbfe; }
    .dark .ev-ts .ts-dropdown .selected { background: rgba(59, 130, 246, .28); color: #dbeafe; }

    /* native date picker icon stays visible on dark fields */
    .dark .ev-date { color-scheme: dark; }
</style>
