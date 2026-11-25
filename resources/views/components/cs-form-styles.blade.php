{{-- Look for the Create CS / Edit CS comparison table and its inputs (cells are also built by JS, so this is CSS-only). --}}
<style>
    #cvTable { border-collapse: separate; border-spacing: 0; border: 1px solid #e5e7eb !important; border-radius: .75rem; overflow: hidden; }
    #cvTable th, #cvTable td { border-color: #eef0f3 !important; }
    #cvTable thead th { background: #f8fafc !important; color: #6b7280; font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; padding-top: .75rem; padding-bottom: .75rem; }
    #cvTable tfoot td { background: #f8fafc !important; }
    #cvTable tbody tr:hover td { background-color: #f8fbff; }
    .dark #cvTable { border-color: rgba(255,255,255,.1) !important; }
    .dark #cvTable thead th, .dark #cvTable tfoot td { background: rgba(255,255,255,.04) !important; }
    #cvTable .qty-input, #cvTable .note-input, #cvTable .price-input, #cvTable .vendornote, #csForm #csnote {
        border: 1px solid #d1d5db !important; border-radius: .5rem !important; background: #fff; transition: border-color .15s, box-shadow .15s;
    }
    #cvTable .qty-input:focus, #cvTable .note-input:focus, #cvTable .price-input:focus, #cvTable .vendornote:focus, #csForm #csnote:focus {
        outline: none; border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,.14) !important;
    }
    #csForm #csnote { padding: .75rem 1rem; font-size: .8125rem; }
    #cvTable .btn-lastprice { border-radius: .5rem; transition: all .15s; }
    #cvTable .btn-lastprice:hover { border-color: #3b82f6; background: #eff6ff; }
    .dark #cvTable .qty-input, .dark #cvTable .note-input, .dark #cvTable .price-input, .dark #cvTable .vendornote, .dark #csForm #csnote { background: #1f2937; border-color: #4b5563 !important; }
    /* roomier cells, readable inputs */
    #cvTable tbody td { padding: .75rem .75rem; vertical-align: middle; font-size: 13px; }
    #cvTable .qty-input { width: 6.5rem; padding: .4rem .6rem; font-size: 13px; font-weight: 600; text-align: right; font-variant-numeric: tabular-nums; }
    #cvTable .note-input { min-height: 2.5rem; padding: .45rem .65rem; font-size: 13px; resize: none; overflow: hidden; field-sizing: content; max-height: 9rem; }
    #cvTable .price-input { padding: .4rem .6rem; font-size: 13px; font-variant-numeric: tabular-nums; }
    #cvTable td.text-right.font-semibold { white-space: nowrap; font-variant-numeric: tabular-nums; }
    #cvTable .btn-lastprice { height: 1.9rem; width: 1.9rem; margin-left: .5rem; vertical-align: middle; }
    #cvTable tfoot td { padding: .75rem 1rem; font-size: 12px; color: #6b7280; }
</style>
