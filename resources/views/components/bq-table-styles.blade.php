{{-- Shared look for the Canvass BQ table (#bqTable). Styles the server rows and the rows the page script adds,
     so both stay identical. <x-bq-table-styles /> once per page. --}}
<style>
    #bqTable { border-collapse: separate; border-spacing: 0; }
    #bqTable thead th {
        border: 0; border-bottom: 1px solid #e5e7eb; background: #f9fafb; padding: .75rem; vertical-align: top;
        font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280;
    }
    #bqTable thead th .text-sm { font-size: 12px; color: #374151; }
    #bqTable tbody td { border: 0; border-bottom: 1px solid #f1f5f9; padding: .625rem .75rem; }
    #bqTable thead th + th, #bqTable tbody td + td, #bqTable tfoot td + td { border-left: 1px solid #f1f5f9; }
    #bqTable tfoot { background: #eff6ff; }
    #bqTable tfoot td { border: 0; border-top: 1px solid #bfdbfe; }
    #bqTable input[type="number"], #bqTable input[type="text"], #bqTable textarea {
        min-height: 2.25rem; border: 1px solid #d1d5db; border-radius: .5rem; background: #fff; font-size: .875rem; color: #1f2937;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: border-color .15s, box-shadow .15s;
    }
    #bqTable input:focus, #bqTable textarea:focus { outline: none; border-color: var(--color-blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    #bqTable input:disabled, #bqTable input[readonly], #bqTable textarea[readonly] { background: #f3f4f6; color: #6b7280; }
    #bqTable input.is-invalid, #bqTable input.border-red-500, #bqTable textarea.is-invalid { border-color: #ef4444; }

    .dark #bqTable thead th { background: rgba(255, 255, 255, .04); border-color: #374151; color: #9ca3af; }
    .dark #bqTable thead th .text-sm { color: #e5e7eb; }
    .dark #bqTable tbody td, .dark #bqTable thead th + th, .dark #bqTable tbody td + td, .dark #bqTable tfoot td + td { border-color: #374151; }
    .dark #bqTable tfoot { background: rgba(59, 130, 246, .1); }
    .dark #bqTable tfoot td { border-color: rgba(59, 130, 246, .35); }
    .dark #bqTable input, .dark #bqTable textarea { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
</style>
