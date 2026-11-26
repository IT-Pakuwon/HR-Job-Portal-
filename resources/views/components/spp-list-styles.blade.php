{{-- Shared look for the SPP list pages (SPPB, SPPJ, SPPK, SPPT): header filters (Select2) + DataTables toolbar/footer.
     Put class "dt-pretty" on the card that wraps the table. --}}
<style>
        /* ---- Header filters (Select2, styled like the other filter bars) ---- */
        .sppb-f .select2-container { width: 100% !important; }
        .sppb-f .select2-container--default .select2-selection--single {
            height: 40px; display: flex; align-items: center; background: #f8fafc;
            border: 1px solid #e2e8f0; border-radius: .625rem; transition: border-color .15s, box-shadow .15s, background-color .15s;
        }
        .sppb-f .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px; padding-left: 2.25rem; padding-right: 1.75rem; font-size: .8125rem; font-weight: 500; color: #334155; width: 100%;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sppb-f .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px; right: 4px; width: 26px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E") no-repeat center / 16px;
            transition: transform .2s ease;
        }
        .sppb-f .select2-container--default .select2-selection--single .select2-selection__arrow b { display: none !important; }
        .sppb-f .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow { transform: rotate(180deg); }
        .sppb-f .select2-container--default:hover .select2-selection--single { border-color: #93c5fd; background: #fff; }
        .sppb-f .select2-container--default.select2-container--focus .select2-selection--single,
        .sppb-f .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #3b82f6; background: #fff; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
        }
        .dark .sppb-f .select2-container--default .select2-selection--single { background: rgba(255,255,255,.04); border-color: rgba(255,255,255,.12); }
        .dark .sppb-f .select2-container--default .select2-selection--single .select2-selection__rendered { color: #e5e7eb; }
        .sppb-s2-dropdown { border: 1px solid #e5e7eb !important; border-radius: .75rem !important; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,.10); margin-top: 4px; }
        .sppb-s2-dropdown .select2-results__option { padding: .5rem .875rem; font-size: .8125rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sppb-s2-dropdown .select2-results__option--highlighted[aria-selected] { background: #eff6ff !important; color: #1d4ed8 !important; }
        .sppb-s2-dropdown .select2-results__option[aria-selected=true] { background: #dbeafe; color: #1d4ed8; font-weight: 600; }
        .sppb-s2-dropdown .select2-search--dropdown { padding: .5rem; }
        .sppb-s2-dropdown .select2-search--dropdown .select2-search__field { border-radius: .5rem; border: 1px solid #d1d5db; padding: .35rem .6rem; }
        .dark .sppb-s2-dropdown { border-color: #374151 !important; }

        /* ---- Prettier DataTables toolbar/footer (scoped: add class "dt-pretty" to a card to use) ---- */
        .dt-pretty .dt-toolbar {
            padding: 14px 18px; margin: 0 !important; gap: 10px !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .dt-pretty .dataTables_length, .dt-pretty .dataTables_filter { padding: 0 !important; }
        .dt-pretty .dataTables_length label {
            display: flex; align-items: center; gap: 4px; margin: 0; height: 38px; padding: 0 14px 0 16px;
            font-size: 13px; color: #6b7280; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            transition: border-color .15s, box-shadow .15s;
        }
        .dt-pretty .dataTables_length label:hover { border-color: #cbd5e1; }
        .dt-pretty .dataTables_length label:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
        .dt-pretty .dataTables_length select {
            -webkit-appearance: none; appearance: none; height: 30px; min-width: 0;
            padding: 0 24px 0 8px !important; font-size: 14px; font-weight: 700; color: #2563eb;
            background-color: transparent !important; border: 0 !important; border-radius: 8px !important;
            cursor: pointer; outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important; background-position: right 4px center !important; background-size: 16px !important;
        }

        /* Export buttons: soft tinted outline, fill on hover */
        .dt-pretty .dt-buttons { margin: 0 0 0 6px !important; gap: 8px; }
        .dt-pretty .dt-button {
            height: 38px; padding: 0 16px !important; border-radius: 10px !important;
            border: 1px solid transparent !important; box-shadow: none !important;
            font-size: 13px !important; font-weight: 600 !important; gap: 8px !important;
        }
        .dt-pretty .dt-button.buttons-excel { background: #f0fdf4 !important; color: #15803d !important; border-color: #bbf7d0 !important; }
        .dt-pretty .dt-button.buttons-excel:hover { background: #16a34a !important; color: #fff !important; border-color: #16a34a !important; }
        .dt-pretty .dt-button.buttons-csv { background: #f0f9ff !important; color: #0369a1 !important; border-color: #bae6fd !important; }
        .dt-pretty .dt-button.buttons-csv:hover { background: #0284c7 !important; color: #fff !important; border-color: #0284c7 !important; }

        /* Search: soft pill, expands + icon turns blue on focus */
        .dt-pretty .dataTables_wrapper .dataTables_filter input {
            height: 38px !important; width: 18rem !important; padding: 0 16px 0 42px !important;
            border-radius: 9999px !important; font-size: 13px !important;
            background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; box-shadow: none !important;
            background-position: 15px center !important; background-size: 16px !important;
            transition: width .25s ease, background-color .15s, border-color .15s, box-shadow .15s !important;
        }
        .dt-pretty .dataTables_wrapper .dataTables_filter input::placeholder { color: #94a3b8; }
        .dt-pretty .dataTables_wrapper .dataTables_filter input:hover { background-color: #fff !important; border-color: #cbd5e1 !important; }
        .dt-pretty .dataTables_wrapper .dataTables_filter input:focus {
            width: 24rem !important; background-color: #fff !important; border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .14) !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") !important;
        }
        .dark .dt-pretty .dataTables_wrapper .dataTables_filter input { background-color: rgba(255, 255, 255, .04) !important; border-color: rgba(255, 255, 255, .12) !important; }
        .dark .dt-pretty .dataTables_wrapper .dataTables_filter input:focus { background-color: rgba(255, 255, 255, .07) !important; border-color: #60a5fa !important; box-shadow: 0 0 0 3px rgba(96, 165, 250, .2) !important; }
        .dt-pretty .dataTables_info { padding: 14px 18px !important; font-size: 12px; color: #6b7280; }
        .dt-pretty .dataTables_paginate { padding: 8px 18px !important; }
        .dt-pretty .dataTables_wrapper > .dataTables_info,
        .dt-pretty .dataTables_wrapper > .dataTables_paginate { border-top: 1px solid #f1f5f9; }

        .dark .dt-pretty .dt-toolbar { border-bottom-color: rgba(255, 255, 255, .06); }
        .dark .dt-pretty .dataTables_length label { color: #9ca3af; background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .12); }
        .dark .dt-pretty .dataTables_length select { color: #93c5fd; }
        .dark .dt-pretty .dataTables_length select option { color: #111827; }
        .dark .dt-pretty .dt-button.buttons-excel { background: rgba(22, 163, 74, .12) !important; color: #86efac !important; border-color: rgba(134, 239, 172, .25) !important; }
        .dark .dt-pretty .dt-button.buttons-csv { background: rgba(2, 132, 199, .12) !important; color: #7dd3fc !important; border-color: rgba(125, 211, 252, .25) !important; }
        .dark .dt-pretty .dataTables_wrapper > .dataTables_info,
        .dark .dt-pretty .dataTables_wrapper > .dataTables_paginate { border-top-color: rgba(255, 255, 255, .06); }
        @media (max-width: 640px) {
            .dt-pretty .dt-toolbar { flex-wrap: wrap; }
            .dt-pretty .dataTables_filter { margin-left: 0 !important; width: 100%; }
            .dt-pretty .dataTables_filter input { width: 100% !important; }
        }

        /* ---- Legacy row buttons inside .dt-pretty tables (pages that still render grey/yellow doc links) ---- */
        .dt-pretty td a.bg-gray-500, .dt-pretty td a.bg-gray-600, .dt-pretty td a.bg-gray-700, .dt-pretty td a.bg-slate-600 {
            background: #eff6ff !important; color: #1d4ed8 !important; border: 1px solid #dbeafe; border-radius: 6px !important;
            padding: 4px 12px !important; transition: all .2s ease;
        }
        .dt-pretty td a.bg-gray-500:hover, .dt-pretty td a.bg-gray-600:hover, .dt-pretty td a.bg-gray-700:hover, .dt-pretty td a.bg-slate-600:hover {
            background: #2563eb !important; color: #fff !important; border-color: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 10px -3px rgba(37,99,235,.45);
        }
        .dt-pretty td a.bg-yellow-500, .dt-pretty td a.bg-amber-500 {
            background: #fffbeb !important; color: #92400e !important; border: 1px solid #fde68a; border-radius: 6px !important;
            padding: 4px 12px !important; transition: all .2s ease;
        }
        .dt-pretty td a.bg-yellow-500:hover, .dt-pretty td a.bg-amber-500:hover { background: #f59e0b !important; color: #fff !important; border-color: #f59e0b; }
        .dt-pretty td button.bg-blue-500, .dt-pretty td button.bg-red-500, .dt-pretty td button.bg-amber-500 {
            border-radius: 8px !important; box-shadow: 0 1px 2px rgba(0,0,0,.08); transition: transform .15s ease, box-shadow .15s ease;
        }
        .dt-pretty td button.bg-blue-500:hover, .dt-pretty td button.bg-red-500:hover, .dt-pretty td button.bg-amber-500:hover { transform: translateY(-1px); box-shadow: 0 4px 10px -3px rgba(0,0,0,.3); }
        .dt-pretty .dataTables_wrapper .dataTables_processing { background: transparent; }
        /* the global 8px table inset must also lower min-w-full, or the table sticks out of the card on the right */
        .dt-pretty .dataTables_wrapper.dt-inset > table.dataTable { min-width: calc(100% - 16px) !important; }
</style>
