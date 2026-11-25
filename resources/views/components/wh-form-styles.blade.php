{{-- Row inputs of the warehouse item tables (Qty / Note / Site). Classes: wh-qty, wh-note, wh-site.
     <x-wh-form-styles /> once per page. Colours use the theme's blue so they follow the rest of the app. --}}
<style>
    .wh-qty, .wh-note, .wh-site {
        height: 2.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; background-color: #f9fafb;
        padding: 0 0.75rem; font-size: 0.875rem; color: #1f2937; box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }
    .wh-qty { width: 7rem; text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; }
    .wh-note { width: 100%; min-width: 12rem; }
    .wh-site {
        width: 10rem; appearance: none; -webkit-appearance: none; padding-right: 2rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.6rem center; background-size: 1rem;
    }
    .wh-qty::placeholder, .wh-note::placeholder { font-weight: 400; color: #9ca3af; }
    .wh-qty:hover, .wh-note:hover, .wh-site:hover { border-color: #d1d5db; }
    .wh-qty:focus, .wh-note:focus, .wh-site:focus {
        outline: none; background-color: #fff; border-color: var(--color-blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
    }
    .wh-qty[readonly], .wh-note[readonly], .wh-site[disabled], .wh-site[readonly] { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }
    .wh-site.wh-ro { background-image: none; padding-right: 0.75rem; width: 7rem; cursor: default; }
    .wh-qty.is-warning { border-color: #f59e0b; background-color: #fffbeb; }
    .wh-qty.is-invalid, .wh-qty.border-red-500 { border-color: #ef4444; background-color: #fef2f2; }
    .dark .wh-qty, .dark .wh-note, .dark .wh-site { background-color: #1f2937; border-color: #4b5563; color: #f3f4f6; }
    .dark .wh-qty:focus, .dark .wh-note:focus, .dark .wh-site:focus { background-color: #111827; border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, .2); }
</style>
