<x-app-layout>
    @php
        $currentPage = in_array(Route::currentRouteName(), ['users', 'users-sby']) ? 'Users' : '';
        $usersSby = Route::is('users-sby') || Route::is('users-sby.*');
        $usersBase = $usersSby ? '/users-sby' : '/users';
    @endphp
    <style>
        /* =====================================================
           User drawer — flat, quiet, one accent (indigo)
           ===================================================== */
        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            background: #fff !important;
            transition: border-color .15s, box-shadow .15s;
        }
        .select2-container--default .select2-selection--multiple { min-height: 42px !important; padding: 3px 6px !important; }
        .select2-container--default .select2-selection--single { height: 42px !important; }
        .select2-container--default .select2-selection__rendered { line-height: 34px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px !important; padding-left: 12px !important; color: #0f172a; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .14);
        }
        /* chips: calm grey tags, bounded height (ul owns the scroll so floats stay contained) */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            max-height: 104px; overflow-y: auto; overflow-x: hidden; padding-right: 4px !important;
        }
        .select2-container--default .select2-selection__choice {
            background: #f1f5f9 !important;
            border: 0 !important;
            border-radius: 6px !important;
            color: #334155 !important;
            font-size: 12px;
            font-weight: 500;
            line-height: 22px;
            padding: 1px 8px 1px 4px !important;
            margin: 3px 4px 3px 0 !important;
        }
        .select2-container--default .select2-selection__choice__remove { color: #94a3b8 !important; margin-right: 4px !important; }
        .select2-container--default .select2-selection__choice__remove:hover { color: #e11d48 !important; }
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field { margin-top: 6px !important; }
        /* the lone "x" clear button floats before the chips and breaks alignment; chips are removable individually */
        .select2-container--default .select2-selection--multiple .select2-selection__clear { display: none !important; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice { float: left; display: inline-flex; align-items: center; height: 26px; }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { position: static !important; float: none !important; padding: 0 !important; }
        .dark .select2-container--default .select2-selection--multiple,
        .dark .select2-container--default .select2-selection--single { background: #1e293b !important; border-color: #334155 !important; }
        .dark .select2-container--default .select2-selection__choice { background: #334155 !important; color: #e2e8f0 !important; }

        /* ---- Drawer shell ---- */
        .um-drawer { max-width: 920px; max-height: 92vh; min-height: min(600px, 92vh); }
        #appModal:not(.hidden) .um-drawer { animation: umRise .28s cubic-bezier(.22, 1, .36, 1); }
        #appModal:not(.hidden) #umBackdrop { animation: umFade .25s ease-out; }
        @keyframes umRise { from { transform: translateY(18px) scale(.98); opacity: 0; } to { transform: none; opacity: 1; } }
        @keyframes umFade { from { opacity: 0; } to { opacity: 1; } }

        /* ---- Profile step ---- */
        .um-group-h { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .um-group-h h3 { font-size: 15px; font-weight: 600; color: #0f172a; line-height: 1.2; }
        .um-group-h p { font-size: 12.5px; color: #94a3b8; }
        .dark .um-group-h { border-color: #334155; }
        .dark .um-group-h h3 { color: #fff; }
        .um-group-i { display: inline-flex; height: 36px; width: 36px; flex: none; align-items: center; justify-content: center; border-radius: 10px; font-size: 14px; }
        .um-req { color: #f43f5e; font-style: normal; margin-left: 2px; }
        .um-field { position: relative; }
        .um-field > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #94a3b8; pointer-events: none; transition: color .15s; }
        .um-field:focus-within > i { color: #6366f1; }
        #appModal .um-input { display: block; width: 100%; height: 44px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; padding: 0 14px 0 40px; font-size: 14px; color: #0f172a; transition: border-color .15s, box-shadow .15s; }
        #appModal .um-input::placeholder { color: #cbd5e1; }
        #appModal .um-input:hover { border-color: #cbd5e1; }
        #appModal .um-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .14); outline: none; }
        #appModal .um-select { appearance: none; -webkit-appearance: none; cursor: pointer; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center; padding-right: 36px; }
        .dark #appModal .um-input { background-color: #1e293b; border-color: #334155; color: #fff; }
        /* origin selects: same type size as the inputs */
        .um-s2 > i { z-index: 2; }
        .um-s2 .select2-container--default .select2-selection--single { height: 44px !important; position: relative; }
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px !important; font-size: 14px; color: #0f172a;
            padding-left: 40px !important; padding-right: 54px !important;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #cbd5e1; }
        /* same chevron as the Position select; clear button sits just left of it */
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px !important; width: 36px; right: 0 !important; }
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__arrow b { display: none !important; }
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__arrow::after {
            content: ''; position: absolute; right: 14px; top: 50%; height: 12px; width: 12px; transform: translateY(-50%); transition: transform .15s;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat center;
        }
        .um-s2 .select2-container--open .select2-selection--single .select2-selection__arrow::after { transform: translateY(-50%) rotate(180deg); }
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute; right: 36px; top: 50%; transform: translateY(-50%); margin: 0; float: none;
            height: 20px; width: 20px; line-height: 19px; text-align: center; border-radius: 6px; font-size: 15px; color: #94a3b8; font-weight: 400;
        }
        .um-s2 .select2-container--default .select2-selection--single .select2-selection__clear:hover { background: #fee2e2; color: #e11d48; }

        /* dropdown panels (rendered inside #appModal) */
        #appModal .select2-dropdown { border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 16px 40px -12px rgba(15, 23, 42, .25); overflow: hidden; margin-top: 4px; }
        #appModal .select2-search--dropdown { padding: 8px; }
        #appModal .select2-search--dropdown .select2-search__field { height: 38px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0 12px; font-size: 13px; outline: none; }
        #appModal .select2-search--dropdown .select2-search__field:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .14); }
        #appModal .select2-results__options { padding: 0 6px 6px; max-height: 240px; }
        #appModal .select2-results__option { border-radius: 8px; padding: 8px 10px; font-size: 13px; color: #334155; }
        #appModal .select2-container--default .select2-results__option--highlighted[aria-selected],
        #appModal .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background: #eef2ff !important; color: #3730a3 !important; }
        #appModal .select2-container--default .select2-results__option--selected,
        #appModal .select2-container--default .select2-results__option[aria-selected=true] { background: #4f46e5 !important; color: #fff !important; font-weight: 600; }

        /* ---- Access scope: search box + tag tray ---- */
        .um-scope .select2-selection--multiple { position: relative; cursor: text; min-height: 42px !important; }
        .um-scope .select2-selection--multiple .select2-selection__choice { display: none !important; }
        .um-scope .select2-selection--multiple { padding: 0 !important; }
        .um-scope .select2-selection--multiple .select2-selection__rendered { max-height: none; overflow: visible; display: block; padding: 0 !important; margin: 0 !important; line-height: 1; }
        .um-scope .select2-search--inline { float: none; display: block; width: 100%; margin: 0; padding: 0; }
        .um-scope .select2-search--inline .select2-search__field {
            display: block; width: 100% !important; height: 40px; margin: 0 !important; box-sizing: border-box;
            padding: 0 12px 0 36px !important; font-size: 13px; line-height: 40px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") no-repeat 12px center;
        }
        .um-scope .select2-search__field::placeholder { color: #94a3b8; }
        .um-scope .select2-container { display: none !important; }
        .um-scope > label { margin-bottom: 8px; }
        .um-selall { margin-left: auto; font-size: 11px; font-weight: 600; color: #4338ca; padding: 2px 6px; border-radius: 6px; display: none; }
        .um-selall:hover { background: #eef2ff; }
        .um-selall + .um-clear { margin-left: 4px; }
        .um-pick { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; overflow: hidden; }
        .dark .um-pick { border-color: #475569; background: #1e293b; }
        .um-pick-s { border-bottom: 1px solid #eef2f6; }
        .dark .um-pick-s { border-color: #334155; }
        .um-pick-q { display: block; width: 100%; height: 38px; border: 0 !important; box-shadow: none !important; outline: 0; background: transparent url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") no-repeat 12px center; padding: 0 12px 0 36px; font-size: 13px; }
        .dark .um-pick-q { color: #fff; }
        .um-pick-list { height: 176px; overflow-y: auto; padding: 4px; }
        .um-opt { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 8px; font-size: 12.5px; color: #475569; cursor: pointer; }
        .um-opt:hover { background: #f8fafc; }
        .um-opt.on { background: #eef2ff; color: #1e293b; }
        .dark .um-opt { color: #cbd5e1; } .dark .um-opt:hover { background: #334155; } .dark .um-opt.on { background: #312e81; color: #fff; }
        .um-opt input { flex: none; width: 15px; height: 15px; accent-color: #4f46e5; }
        .um-opt b { flex: none; color: #4338ca; font-weight: 700; }
        .dark .um-opt b { color: #a5b4fc; }
        .um-opt span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .um-pick-empty { padding: 28px 8px; text-align: center; font-size: 12px; color: #94a3b8; }
        .um-clear { margin-left: auto; font-size: 11px; font-weight: 600; color: #e11d48; padding: 2px 6px; border-radius: 6px; display: none; }
        .um-clear:hover { background: #fff1f2; }
        .um-tags { display: none; flex-wrap: wrap; gap: 6px; margin-top: 10px; max-height: 120px; overflow-y: auto; padding: 2px; }
        .um-tags.has { display: flex; }
        .um-tag { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; border: 1px solid #e0e7ff; background: #f5f7ff; color: #475569; border-radius: 8px; padding: 3px 4px 3px 8px; font-size: 11.5px; animation: umPop .16s ease-out; }
        .um-tag b { color: #4338ca; font-weight: 700; }
        .um-tag > span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 120px; }
        .um-tags { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
        .um-tag button { display: inline-flex; height: 18px; width: 18px; align-items: center; justify-content: center; border-radius: 5px; color: #94a3b8; font-size: 8px; }
        .um-tag button:hover { background: #fee2e2; color: #e11d48; }
        .dark .um-tag { background: #312e81; border-color: #4338ca; color: #c7d2fe; }
        .dark .um-tag b { color: #fff; }

        /* ---- Wizard ---- */
        #appForm [data-step]:not(.um-active) { display: none; }
        #appForm [data-step].um-active { animation: umStepIn .25s ease-out; }
        @keyframes umStepIn { from { opacity: 0; transform: translateX(14px); } to { opacity: 1; transform: none; } }
        .um-st { flex: none; }
        .um-st-btn { display: flex; align-items: center; gap: 12px; text-align: left; }
        .um-st .um-num { position: relative; display: inline-flex; height: 34px; width: 34px; flex: none; align-items: center; justify-content: center; border-radius: 999px; border: 2px solid #cbd5e1; color: #94a3b8; font-size: 13px; font-weight: 700; transition: all .2s; }
        .um-st .um-num i { display: none; font-size: 12px; }
        .um-st .um-num em { font-style: normal; }
        .um-st-t b { display: block; font-size: 13px; color: #64748b; line-height: 1.2; }
        .um-st-t small { display: block; font-size: 11px; color: #94a3b8; }
        .um-st.active .um-num { border-color: #4f46e5; background: #4f46e5; color: #fff; box-shadow: 0 0 0 4px rgba(99, 102, 241, .18); }
        .um-st.active .um-st-t b { color: #0f172a; }
        .um-st.done .um-num { border-color: #10b981; background: #10b981; color: #fff; }
        .um-st.done .um-num i { display: inline; }
        .um-st.done .um-num em { display: none; }
        .um-st.done .um-st-t b { color: #0f172a; }
        .um-st-line { height: 2px; flex: 1; margin: 0 16px; border-radius: 2px; background: #e2e8f0; transition: background .3s; }
        .um-st-line.done { background: #10b981; }
        .dark .um-st.active .um-st-t b, .dark .um-st.done .um-st-t b { color: #fff; }
        @media (max-width: 640px) { .um-st-t small { display: none; } .um-st:not(.active) .um-st-t { display: none; } .um-st-line { margin: 0 8px; } }
        .um-invalid, .um-invalid.select2-selection, .um-invalid .select2-selection { border-color: #f43f5e !important; box-shadow: 0 0 0 3px rgba(244, 63, 94, .12) !important; }

        /* ---- Left rail ---- */
        .um-step { display: flex; width: 100%; align-items: center; gap: 12px; border-radius: 10px; padding: 10px 12px; text-align: left; font-size: 13px; font-weight: 500; color: #64748b; transition: background .15s, color .15s; }
        .um-step:hover { background: #f1f5f9; color: #0f172a; }
        .um-step .um-num { display: inline-flex; height: 24px; width: 24px; flex: none; align-items: center; justify-content: center; border-radius: 999px; border: 1.5px solid #cbd5e1; font-size: 11px; font-weight: 700; color: #94a3b8; transition: all .15s; }
        .um-step.active { background: #eef2ff; color: #3730a3; font-weight: 600; }
        .um-step.active .um-num { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .dark .um-step:hover { background: #334155; color: #fff; }
        .dark .um-step.active { background: #312e81; color: #e0e7ff; }
        .um-stat { display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #64748b; padding: 5px 0; }
        .um-stat b { font-size: 13px; color: #0f172a; font-variant-numeric: tabular-nums; }
        .dark .um-stat b { color: #fff; }

        /* ---- Sections ---- */
        .um-card { border-radius: 14px !important; box-shadow: none; }
        .um-num-lg { display: inline-flex; height: 28px; width: 28px; flex: none; align-items: center; justify-content: center; border-radius: 999px; background: #0f172a; color: #fff; font-size: 12px; font-weight: 700; }
        .dark .um-num-lg { background: #6366f1; }
        .um-label { margin-bottom: 6px; display: block; font-size: 13px; font-weight: 500; color: #334155; }
        .um-label i { color: #94a3b8; margin-right: 4px; font-size: 12px; }
        .dark .um-label { color: #cbd5e1; }
        .um-count { border-radius: 6px; background: #eef2ff; color: #4338ca; font-size: 11px; font-weight: 600; padding: 1px 7px; margin-left: 6px; }
        .dark .um-count { background: #312e81; color: #e0e7ff; }
        #appModal input[type=text], #appModal input[type=email], #appModal select.h-11 { border-radius: 10px; background: #fff; height: 42px; transition: border-color .15s, box-shadow .15s; }
        #appModal input[type=text]:focus, #appModal input[type=email]:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .14); outline: none; }
        .dark #appModal input[type=text], .dark #appModal input[type=email], .dark #appModal select.h-11 { background: #1e293b; }

        /* ---- User type ---- */
        .um-type { display: flex; align-items: center; gap: 12px; cursor: pointer; border: 1px solid #e2e8f0; border-radius: 12px; padding: 11px 14px; background: #fff; color: #334155; transition: all .15s; user-select: none; }
        .um-type i { display: inline-flex; height: 34px; width: 34px; flex: none; align-items: center; justify-content: center; border-radius: 10px; background: #f1f5f9; color: #64748b; font-size: 14px; transition: all .15s; }
        .um-type { padding: 10px 12px; gap: 10px; min-width: 0; }
        .um-type > div { min-width: 0; }
        .um-type b { display: block; font-size: 13px; line-height: 1.2; white-space: nowrap; }
        .um-type small { display: block; font-size: 11px; line-height: 1.3; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .um-type:hover { border-color: #a5b4fc; }
        .um-type.on { border-color: #6366f1; background: #eef2ff; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); }
        .um-type.on i { background: #4f46e5; color: #fff; }
        .um-type.on small { color: #6366f1; }
        .dark .um-type { background: #1e293b; border-color: #334155; color: #e2e8f0; }
        .dark .um-type i { background: #334155; }
        .dark .um-type.on { background: #312e81; border-color: #6366f1; }
        .um-shake { animation: umShake .4s; }
        .um-shake .um-type { border-color: #f43f5e; }
        @keyframes umShake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }

        /* ---- Role picker ---- */
        #umRoleCats { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 6px; scrollbar-width: thin; }
        .um-cat { display: inline-flex; align-items: center; gap: 6px; flex: none; white-space: nowrap; cursor: pointer; border-radius: 999px; border: 1px solid #e2e8f0; padding: 4px 6px 4px 10px; font-size: 12px; font-weight: 500; color: #475569; background: #fff; transition: all .15s; }
        .um-cat:hover { border-color: #a5b4fc; color: #0f172a; }
        .um-cat.active { background: #0f172a; border-color: #0f172a; color: #fff; }
        .um-cat .um-dot { margin: 0; }
        .um-dot { display: inline-block; height: 8px; width: 8px; flex: none; border-radius: 999px; }
        .um-on { border-radius: 999px; background: #e0e7ff; color: #4338ca; font-style: normal; font-size: 10.5px; font-weight: 700; padding: 0 7px; line-height: 17px; }
        .um-on.none { background: #f1f5f9; color: #94a3b8; }
        .um-cat.active .um-on { background: rgba(255,255,255,.18); color: #fff; }
        .dark .um-cat { background: #1e293b; border-color: #334155; color: #cbd5e1; }
        .dark .um-cat:hover { background: #334155; color: #fff; }
        .dark .um-cat.active { background: #6366f1; }

        #umRoleGrid { max-height: 360px; overflow-y: auto; padding-right: 4px; }
        .um-role { display: flex; align-items: center; gap: 10px; cursor: pointer; border-radius: 10px; border: 1px solid #eef2f6; border-left: 3px solid var(--c, #cbd5e1); padding: 8px 10px; font-size: 13px; color: #334155; transition: background .12s; user-select: none; }
        .um-role:hover { background: #f8fafc; }
        #umRoleGrid { gap: 6px; }
        .um-role > span:last-child { line-height: 1.25; word-break: break-word; }
        .um-role .um-tick { display: inline-flex; height: 18px; width: 18px; flex: none; align-items: center; justify-content: center; border-radius: 5px; border: 1.5px solid #cbd5e1; font-size: 9px; color: transparent; background: #fff; transition: all .12s; }
        .um-role.on { background: #f7f8ff; border-color: #c7d2fe; border-left-color: var(--c, #6366f1); color: #1e293b; font-weight: 500; }
        .um-role.on .um-tick { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .dark .um-role { color: #e2e8f0; }
        .dark .um-role:hover { background: #334155; }
        .dark .um-role { border-color: #334155; }
        .dark .um-role.on { background: #272f5a; border-color: #4f46e5; color: #e0e7ff; }

        .um-assigned { display: flex; flex-direction: column; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; background: #fff; min-height: 260px; max-height: 460px; }
        .dark .um-assigned { background: #1e293b; border-color: #334155; }
        .um-chip { display: flex; align-items: center; gap: 8px; border-radius: 8px; background: #f8fafc; border: 1px solid #eef2f6; font-size: 12px; color: #334155; padding: 5px 5px 5px 10px; animation: umPop .16s ease-out; }
        .dark .um-chip { background: #334155; border-color: #475569; color: #e2e8f0; }
        .um-chip-t { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .um-chip button { display: inline-flex; height: 20px; width: 20px; flex: none; align-items: center; justify-content: center; border-radius: 6px; color: #94a3b8; font-size: 9px; transition: all .15s; }
        .um-chip button:hover { background: #fee2e2; color: #e11d48; }
        .um-empty { display: flex; flex: 1; flex-direction: column; align-items: center; justify-content: center; gap: 8px; text-align: center; font-size: 12px; color: #94a3b8; padding: 24px 0; }
        .um-empty i { font-size: 26px; opacity: .6; }
        @keyframes umPop { from { transform: scale(.94); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* ---- Users list redesign ---- */
        #tabPanelList .um-filters label { display: block; margin-bottom: 5px; font-size: 11px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #64748b; }
        #tabPanelList .um-filters .select2-container { width: 100% !important; }
        #tabPanelList .um-filters .select2-container--default .select2-selection--single { height: 40px; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; display: flex; align-items: center; }
        #tabPanelList .um-filters .select2-container--default .select2-selection--single .select2-selection__rendered { font-size: 13px; line-height: 38px; padding-left: 12px; color: #334155; }
        #tabPanelList .um-filters .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 6px; }
        .dark #tabPanelList .um-filters .select2-container--default .select2-selection--single { background: #1e293b; border-color: #334155; }
        .dark #tabPanelList .um-filters .select2-container--default .select2-selection--single .select2-selection__rendered { color: #e2e8f0; }
        #tabPanelList .dt-toolbar { padding: 14px 20px 4px; flex-wrap: wrap; }
        #tabPanelList .dt-toolbar .dt-buttons { display: flex; gap: 6px; }
        #tabPanelList .dt-toolbar .dt-button { border-radius: 999px !important; padding: 5px 14px !important; font-size: 12px !important; font-weight: 600; box-shadow: none !important; border: 1px solid #e2e8f0 !important; background: #fff !important; color: #475569 !important; }
        #tabPanelList .dt-toolbar .dt-button:hover { background: #f1f5f9 !important; border-color: #a5b4fc !important; }
        #tabPanelList .dt-toolbar .dataTables_filter, #tabPanelList .dt-toolbar .dt-search { margin-left: auto; }
        .um-table thead th { background: #f8fafc; font-size: 11px; letter-spacing: .06em; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        .dark .um-table thead th { background: rgba(255,255,255,.03); border-color: rgba(255,255,255,.08); }
        .um-table tbody td { padding-top: 12px; padding-bottom: 12px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .dark .um-table tbody td { border-color: rgba(255,255,255,.06); }
        .um-table tbody tr:hover td { background: #f8faff; }
        .dark .um-table tbody tr:hover td { background: rgba(255,255,255,.03); }
        .um-table td.ul-name-cell { text-align: left !important; }
        #usersTable th:last-child, #usersTable td:last-child { width: 56px; min-width: 56px; padding-left: 0 !important; padding-right: 0 !important; text-align: center !important; }
        #usersTable th:last-child::before, #usersTable th:last-child::after { display: none !important; }
        #usersTable #selectAllUsers, #usersTable .userRowCheckbox { appearance: none; -webkit-appearance: none; display: inline-block; vertical-align: middle; height: 18px; width: 18px; margin: 0; border: 1.5px solid #cbd5e1; border-radius: 6px; background: #fff center / 12px no-repeat; cursor: pointer; transition: all .12s; }
        #usersTable #selectAllUsers:hover, #usersTable .userRowCheckbox:hover { border-color: #6366f1; }
        #usersTable #selectAllUsers:checked, #usersTable .userRowCheckbox:checked { background-color: #4f46e5; border-color: #4f46e5; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E"); }
        #usersTable #selectAllUsers:indeterminate { background-color: #4f46e5; border-color: #4f46e5; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round'%3E%3Cpath d='M6 12h12'/%3E%3C/svg%3E"); }
        .dark #usersTable #selectAllUsers:not(:checked):not(:indeterminate), .dark #usersTable .userRowCheckbox:not(:checked) { background-color: #1e293b; border-color: #475569; }
        #usersTable tr:has(.userRowCheckbox:checked) td { background: #f5f7ff; }
        .ul-user { display: flex; align-items: center; justify-content: flex-start; gap: 10px; min-width: 180px; width: 100%; text-align: left; }
        .ul-user b { flex: 1; min-width: 0; text-align: left; }
        .ul-av { display: inline-flex; height: 34px; width: 34px; flex: none; align-items: center; justify-content: center; border-radius: 999px; background: #e0e7ff; color: #4338ca; font-size: 12px; font-weight: 700; }
        .dark .ul-av { background: #312e81; color: #e0e7ff; }
        .ul-user b { display: block; font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.25; }
        .dark .ul-user b { color: #f1f5f9; }
        .ul-user small { display: block; font-size: 11.5px; color: #94a3b8; }
        .ul-chips { display: flex; flex-wrap: wrap; gap: 4px; max-width: 260px; }
        .ul-chip { border-radius: 6px; background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 600; padding: 1px 7px; line-height: 18px; }
        .dark .ul-chip { background: #334155; color: #e2e8f0; }
        .ul-chip.more { background: #eef2ff; color: #4338ca; }
        .ul-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 2px 10px; font-size: 12px; font-weight: 600; line-height: 20px; }
        .ul-pill::before { content: ''; height: 6px; width: 6px; border-radius: 999px; background: currentColor; }
        .ul-pill.ok { background: #dcfce7; color: #15803d; }
        .ul-pill.off { background: #fee2e2; color: #b91c1c; }
        .ul-act { display: flex; align-items: center; justify-content: center; gap: 6px; }
        .ul-btn { display: inline-flex; height: 30px; width: 30px; align-items: center; justify-content: center; border-radius: 8px; font-size: 12px; transition: all .15s; }
        .ul-btn.edit { background: #eef2ff; color: #4f46e5; } .ul-btn.edit:hover { background: #4f46e5; color: #fff; }
        .ul-btn.key { background: #fef3c7; color: #b45309; } .ul-btn.key:hover { background: #f59e0b; color: #fff; }
        .ul-btn.rst { background: #fee2e2; color: #dc2626; } .ul-btn.rst:hover { background: #ef4444; color: #fff; }
        .ul-muted { color: #64748b; font-size: 12.5px; }
        .ul-btn.del { background: #f1f5f9; color: #334155; } .ul-btn.del:hover { background: #0f172a; color: #fff; }
        .ul-tag { display: inline-block; border-radius: 6px; padding: 1px 8px; font-size: 11px; font-weight: 700; line-height: 18px; margin-right: 4px; }
        .ul-tag.email { background: #fef3c7; color: #92400e; }
        .ul-tag.npk { background: #ede9fe; color: #5b21b6; }
        .ul-tag.username { background: #e0f2fe; color: #075985; }
        .ul-tag.other { background: #f1f5f9; color: #475569; }
        #dupUsersTable tr.group-alt td { background: #fafbff; }
        .dark #dupUsersTable tr.group-alt td { background: rgba(255,255,255,.025); }
        #tabPanelDuplicates .dt-toolbar, #tabPanelInactive .dt-toolbar, #tabPanelSby .dt-toolbar { padding: 14px 20px 4px; flex-wrap: wrap; }
        #tabPanelDuplicates .dataTables_filter, #tabPanelInactive .dataTables_filter, #tabPanelSby .dataTables_filter { margin-left: auto; }
        .um-panel-head { display: flex; align-items: center; gap: 12px; }
        .um-panel-ic { display: inline-flex; height: 36px; width: 36px; flex: none; align-items: center; justify-content: center; border-radius: 10px; font-size: 15px; }
        .um-panel-ic.amber { background: #fef3c7; color: #b45309; } .um-panel-ic.slate { background: #f1f5f9; color: #475569; } .um-panel-ic.indigo { background: #e0e7ff; color: #4338ca; }

        /* ---- Footer summary ---- */
        .um-pill { display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-weight: 500; }
        .um-pill b { color: #0f172a; }
        .dark .um-pill b { color: #fff; }
        .um-pill + .um-pill::before { content: ''; display: inline-block; height: 3px; width: 3px; border-radius: 999px; background: #cbd5e1; margin-right: 6px; }
    </style>    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- Tab nav --}}
        <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
            <button type="button" id="tabBtnList"
                class="user-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-indigo-400">
                👥 Users List
            </button>
            @unless ($usersSby)
                <button type="button" id="tabBtnDuplicates"
                    class="user-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    🧬 Duplicate Users
                    <span id="dupCountBadge"
                        class="ml-1 hidden rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white"></span>
                </button>
            @endunless
                <button type="button" id="tabBtnInactive"
                    class="user-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    🚫 Inactive Users
                    <span id="inactiveCountBadge"
                        class="ml-1 hidden rounded-full bg-gray-500 px-2 py-0.5 text-xs font-bold text-white"></span>
                </button>
            @unless ($usersSby)
                <button type="button" id="tabBtnSby"
                    class="user-tab-btn rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    🏢 User SBY
                </button>
            @endunless
        </div>

        <div id="tabPanelList"
            class="rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div
                class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Users List</h2>
                <div class="flex items-center gap-2">
                    <span id="bulkSelectedCount" class="hidden text-sm font-medium text-gray-500 dark:text-gray-400"></span>
                    <button id="clearUserFilters" type="button"
                        class="inline-flex h-10 items-center justify-center rounded-lg px-3 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        <i class="fas fa-rotate-left mr-1.5 text-xs"></i> Clear filters
                    </button>
                    <button id="bulkDeactivateBtn" type="button" disabled
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-5 text-sm font-medium text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-40">
                        Deactivate Selected
                    </button>
                    <button id="addAppBtn"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        + Add User
                    </button>
                </div>
            </div>

            {{-- Filter Company & Department --}}
            <div class="um-filters grid gap-3 px-5 pt-4" style="grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));">
                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Company
                    </label>
                    <select id="filterCompany"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                        <option value="">All Company</option>
                        @foreach ($filterCompanies as $c)
                            <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }} - {{ $c->cpny_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Division
                    </label>
                    <select id="filterDivision"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                        <option value="">All Division</option>
                    </select>
                </div>

                @unless ($usersSby)
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Department
                        </label>
                        <select id="filterDepartment"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                            <option value="">All Department</option>
                            @foreach ($filterDepartments as $d)
                                <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless

                @unless ($usersSby)
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Business Unit
                        </label>
                        <select id="filterBusinessUnit"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                            <option value="">All Business Unit</option>
                            @foreach ($businessUnits as $bu)
                                <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_id }} -
                                    {{ $bu->business_unit_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            Filter Jabatan
                        </label>
                        <select id="filterJabatan"
                            class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                            <option value="">All Jabatan</option>
                            <option value="staff">staff</option>
                            <option value="manager">manager</option>
                        </select>
                    </div>
                @endunless

                <div class="min-w-[200px] flex-1">
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Filter Talenta Status
                    </label>
                    <select id="filterTalentaStatus"
                        class="w-full rounded-lg border border-gray-300 px-2 py-1 text-sm dark:bg-gray-700 dark:border-gray-700">
                        <option value="">All Talenta Status</option>
                        <option value="active">Active</option>
                        <option value="resigned">Resigned</option>
                        <option value="none">No Talenta Match</option>
                    </select>
                </div>
            </div>

            {{-- Table --}}
            <div class="relative mt-4 overflow-hidden">
                <table id="usersTable" class="um-table w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-48 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Username</th>
                            <th class="px-4 py-3 text-left font-medium">NPK</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Division</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Departement</th>
                            <th class="px-4 py-3 text-left font-medium">BusinessUnit</th>
                            <th class="px-4 py-3 text-left font-medium">Jabatan</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            <th class="w-36 px-4 py-3 text-left font-medium">Talenta Status</th>
                            <th class="w-10 px-4 py-3 text-center">
                                <input type="checkbox" id="selectAllUsers" title="Select all (filtered)">
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

        @unless ($usersSby)
        <div id="tabPanelDuplicates"
            class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                <div class="um-panel-head py-1">
                    <span class="um-panel-ic amber"><i class="fas fa-clone"></i></span>
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Duplicate Users</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Users sharing the same email, username, or NPK. Review and deactivate the extra
                            accounts to keep one active record per person.
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="dupUsersTable" class="um-table w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-40 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Matched By</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Username</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">NPK</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Departement</th>
                            <th class="px-4 py-3 text-left font-medium">BusinessUnit</th>
                            <th class="px-4 py-3 text-left font-medium">Jabatan</th>
                            <th class="px-4 py-3 text-left font-medium">Created</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            <th class="w-36 px-4 py-3 text-left font-medium">Talenta Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        @endunless

        <div id="tabPanelInactive"
            class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                <div class="um-panel-head py-1">
                    <span class="um-panel-ic slate"><i class="fas fa-user-slash"></i></span>
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">Inactive Users</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Users currently deactivated. Toggle the switch to reactivate an account.
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="inactiveUsersTable" class="um-table w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-48 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Username</th>
                            <th class="px-4 py-3 text-left font-medium">NPK</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Departement</th>
                            <th class="px-4 py-3 text-left font-medium">BusinessUnit</th>
                            <th class="px-4 py-3 text-left font-medium">Jabatan</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                            <th class="w-36 px-4 py-3 text-left font-medium">Talenta Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        @unless ($usersSby)
        <div id="tabPanelSby"
            class="hidden rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="border-b border-gray-100 px-5 py-2 dark:border-white/[0.06]">
                <div class="um-panel-head py-1">
                    <span class="um-panel-ic indigo"><i class="fas fa-building"></i></span>
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">User SBY</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Active users belonging to the SBY company group.
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="sbyUsersTable" class="um-table w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-48 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Username</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Division</th>
                            <th class="px-4 py-3 text-left font-medium">Jabatan</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        @endunless

        <!-- Modal -->
        <div id="appModal" class="fixed inset-0 z-50 hidden">
            <div id="umBackdrop" class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"></div>

            <div class="relative flex h-full items-center justify-center p-4">
                <div
                    class="um-drawer flex w-full flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-800">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-7 py-5 dark:border-slate-700">
                        <div class="flex min-w-0 items-center gap-4">
                            <div id="umAvatar"
                                class="flex h-11 w-11 flex-none items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 id="modalTitle" class="text-lg font-semibold text-slate-900 dark:text-white">Add User</h2>
                                    <span id="umMode" class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-700 dark:text-slate-300">New</span>
                                </div>
                                <p id="umSubtitle" class="truncate text-sm text-slate-500 dark:text-slate-400">
                                    Create and manage user permissions and access scopes.
                                </p>
                            </div>
                        </div>

                        <button id="closeModal" type="button"
                            class="rounded-lg p-2.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- STEPPER -->
                    <div class="border-b border-slate-200 bg-white px-8 py-4 dark:border-slate-700 dark:bg-slate-800">
                        <ol id="umStepper" class="flex items-center">
                            <li class="um-st active" data-step="1">
                                <button type="button" class="um-st-btn"><span class="um-num"><i class="fas fa-check"></i><em>1</em></span>
                                    <span class="um-st-t"><b>Profile</b><small>Who is this user</small></span></button>
                            </li>
                            <li class="um-st-line"></li>
                            <li class="um-st" data-step="2">
                                <button type="button" class="um-st-btn"><span class="um-num"><i class="fas fa-check"></i><em>2</em></span>
                                    <span class="um-st-t"><b>Access scope</b><small>Companies &amp; departments</small></span></button>
                            </li>
                            <li class="um-st-line"></li>
                            <li class="um-st" data-step="3">
                                <button type="button" class="um-st-btn"><span class="um-num"><i class="fas fa-check"></i><em>3</em></span>
                                    <span class="um-st-t"><b>Security</b><small>Type, homepage &amp; roles</small></span></button>
                            </li>
                        </ol>
                    </div>

                    <div class="flex min-h-0 flex-1">

                    <form id="appForm" novalidate class="flex min-h-0 min-w-0 flex-1 flex-col">
                        <input type="hidden" id="id">

                        <div id="umScroll" class="flex-1 scroll-smooth overflow-y-auto overflow-x-visible bg-slate-50 p-6 dark:bg-slate-900/40">

                            <div class="space-y-6">

                                <!-- USER INFORMATION -->
                                <div id="umSecProfile" data-step="1" class="space-y-5">

                                    <!-- Identity -->
                                    <div class="um-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                        <div class="um-group-h"><span class="um-group-i bg-indigo-50 text-indigo-600"><i class="fas fa-user"></i></span>
                                            <div><h3>Identity</h3><p>How this person signs in and is contacted.</p></div></div>

                                        <div class="grid gap-x-5 gap-y-5 md:grid-cols-2">
                                            <div>
                                                <label class="um-label" for="name">Full name <em class="um-req">*</em></label>
                                                <div class="um-field"><i class="fas fa-user"></i>
                                                    <input id="name" name="name" type="text" placeholder="e.g. John Doe" required autocomplete="off" class="um-input">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="um-label" for="npk">NPK</label>
                                                <div class="um-field"><i class="fas fa-hashtag"></i>
                                                    <input id="npk" name="npk" type="text" placeholder="e.g. 123456" autocomplete="off" class="um-input">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="um-label" for="email">Email address <em class="um-req">*</em></label>
                                                <div class="um-field"><i class="fas fa-envelope"></i>
                                                    <input id="email" name="email" type="email" placeholder="john.doe@example.com" required autocomplete="off" class="um-input">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="um-label" for="username">Username</label>
                                                <div class="um-field"><i class="fas fa-at"></i>
                                                    <input id="username" name="username" type="text" placeholder="username" autocomplete="off" class="um-input">
                                                </div>
                                                <p class="mt-1.5 text-xs text-slate-400">Filled from the email automatically. Type to override.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Organization -->
                                    <div class="um-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                        <div class="um-group-h"><span class="um-group-i bg-emerald-50 text-emerald-600"><i class="fas fa-briefcase"></i></span>
                                            <div><h3>Position &amp; origin</h3><p>Where this person belongs in the organization.</p></div></div>

                                        <div class="grid gap-x-5 gap-y-5 md:grid-cols-3">
                                            <div>
                                                <label class="um-label" for="jabatan">Position <em class="um-req">*</em></label>
                                                <div class="um-field"><i class="fas fa-id-badge"></i>
                                                    <select id="jabatan" name="jabatan" required class="um-input um-select">
                                                        <option value="">Select position</option>
                                                        <option value="staff">Staff</option>
                                                        <option value="manager">Manager</option>
                                                    </select>
                                                </div>
                                            </div>

                                            @unless ($usersSby)
                                                <div>
                                                    <label class="um-label">Origin company</label>
                                                    <div class="um-field um-s2"><i class="fas fa-building"></i>
                                                    <select id="origin_cpny_id" name="origin_cpny_id" class="w-full"
                                                        data-placeholder="Select origin company">
                                                        <option value="">-- Select Origin Company --</option>
                                                        @foreach ($company as $c)
                                                            <option value="{{ $c->cpny_id }}">
                                                                {{ $c->cpny_id }} - {{ $c->cpny_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="um-label">Origin department</label>
                                                    <div class="um-field um-s2"><i class="fas fa-sitemap"></i>
                                                    <select id="origin_department_id" name="origin_department_id" class="w-full"
                                                        data-placeholder="Select origin department">
                                                        <option value="">-- Select Origin Department --</option>
                                                        @foreach ($department as $d)
                                                            <option value="{{ $d->department_id }}">
                                                                {{ $d->department_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                </div>
                                            @endunless
                                        </div>
                                    </div>

                                </div>

                                <!-- ACCESS SCOPE -->
                                <div id="umSecAccess" data-step="2"
                                    class="um-card rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                    <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                        <span class="um-num-lg">2</span>
                                        <div>
                                            <h3 class="font-semibold text-slate-900 dark:text-white">Access Scope</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Configure organization access.</p>
                                        </div>
                                    </div>

                                    <div class="grid gap-5 p-6 md:grid-cols-2">

                                        <div class="md:col-span-2">
                                            <label class="um-label">Company Group</label>
                                            <select id="group_cpny_id" name="group_cpny_id" class="w-full"
                                                data-placeholder="Select company group (optional)">
                                                <option value="">-- All Groups --</option>
                                                <option value="JKT">JKT</option>
                                                <option value="SBY">SBY</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="um-label">Company</label>
                                            <select name="cpny_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select company access" required>
                                                <option></option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="um-label">Department</label>
                                            <select name="department_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select department access">
                                                <option></option>
                                                @foreach ($department as $d)
                                                    <option value="{{ $d->department_id }}">
                                                        {{ $d->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="um-label">Division</label>
                                            <select name="division_id[]" class="select2 w-full" multiple
                                                data-placeholder="Search and select division access">
                                                <option></option>
                                            </select>
                                        </div>

                                        @unless ($usersSby)
                                            <div>
                                                <label class="um-label">Business Unit</label>
                                                <select name="business_unit_id[]" class="select2 w-full" multiple
                                                    data-placeholder="Search and select business unit access">
                                                    <option></option>
                                                    @foreach ($businessUnits as $bu)
                                                        <option value="{{ $bu->business_unit_id }}">
                                                            {{ $bu->business_unit_id }} - {{ $bu->business_unit_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endunless

                                    </div>
                                </div>

                                <!-- SECURITY -->
                                <div id="umSecSecurity" data-step="3"
                                    class="um-card rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                                    <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                                        <span class="um-num-lg">3</span>
                                        <div>
                                            <h3 class="font-semibold text-slate-900 dark:text-white">Security & Permissions</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Configure roles and dashboard access.</p>
                                        </div>
                                    </div>

                                    <div class="space-y-7 p-6">

                                        <div class="grid gap-6 md:grid-cols-2">

                                            <div>
                                                <label class="um-label">
                                                    <i class="fas fa-house mr-1 text-indigo-400"></i> Homepage Dashboard
                                                </label>

                                                <select id="homepage" name="homepage"
                                                    class="h-11 w-full rounded-lg border border-slate-300 px-4 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                                    data-placeholder="Search and select homepage">

                                                    <option value="">Select Homepage</option>

                                                    @foreach ($screens as $screen)
                                                        <option value="{{ $screen->screen_id }}">
                                                            {{ $screen->screen_name }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                                <p class="mt-2 text-xs text-slate-400">The first screen this user lands on after login.</p>
                                            </div>

                                            <div>
                                                <label class="um-label">
                                                    <i class="fas fa-user-shield mr-1 text-indigo-400"></i> User Type
                                                </label>

                                                <select name="role[]" class="hidden" multiple>
                                                    <option></option>
                                                    <option value="user">User</option>
                                                    @unless ($usersSby)
                                                        <option value="admin">Admin</option>
                                                    @endunless
                                                    <option value="adminsby">Adminsby</option>
                                                </select>

                                                <div id="umTypeCards" class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                                    <div class="um-type" data-v="user"><i class="fas fa-user"></i><div><b>User</b><small>Standard access</small></div></div>
                                                    @unless ($usersSby)
                                                        <div class="um-type" data-v="admin"><i class="fas fa-crown"></i><div><b>Admin</b><small>Full control</small></div></div>
                                                    @endunless
                                                    <div class="um-type" data-v="adminsby"><i class="fas fa-shield-halved"></i><div><b>Adminsby</b><small>Surabaya admin</small></div></div>
                                                </div>
                                            </div>

                                        </div>

                                        <div>
                                            <div class="mb-3 flex items-center justify-between">
                                                <label class="um-label !mb-0">
                                                    <i class="fas fa-key mr-1 text-indigo-400"></i> Application Roles
                                                </label>
                                                <span class="text-xs text-slate-400">Click a card to assign it. Use the x on the right to remove.</span>
                                            </div>

                                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_250px]">

                                                <!-- Browse -->
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                                                    <div class="mb-3 flex flex-wrap items-center gap-2">
                                                        <div class="relative flex-1" style="min-width:200px">
                                                            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                                            <input id="umRoleSearch" type="text" placeholder="Search roles..."
                                                                class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                                        </div>
                                                        <button type="button" id="umRoleAll" class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100 dark:bg-slate-700 dark:text-indigo-300"><i class="fas fa-check-double mr-1"></i>Select shown</button>
                                                    </div>
                                                    <div id="umRoleCats" class="mb-3 flex flex-wrap gap-1.5"></div>
                                                    <div id="umRoleGrid" class="grid gap-1 sm:grid-cols-2"></div>
                                                    <p id="umRoleEmpty" class="hidden py-8 text-center text-sm text-slate-400"><i class="fas fa-magnifying-glass mb-2 block text-2xl opacity-40"></i>No roles match your search.</p>
                                                </div>

                                                <!-- Assigned -->
                                                <aside class="um-assigned">
                                                    <div class="mb-3 flex items-center justify-between">
                                                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white"><i class="fas fa-circle-check text-indigo-500"></i> Assigned</div>
                                                        <span id="umRoleCount" class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">0</span>
                                                    </div>
                                                    <div id="umRoleTray" class="flex flex-1 flex-col gap-1.5 overflow-y-auto"></div>
                                                    <button type="button" id="umRoleClear" class="mt-3 rounded-lg border border-slate-200 py-2 text-xs font-semibold text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 dark:border-slate-600 dark:text-slate-300"><i class="fas fa-trash-can mr-1"></i>Clear all</button>
                                                </aside>

                                            </div>

                                            <select name="role_ids[]" class="hidden" multiple>
                                                <option></option>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->role_id }}">
                                                        {{ $role->role_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- FOOTER -->
                        <div
                            class="flex items-center justify-between gap-3 border-t border-slate-200 bg-white px-8 py-4 dark:border-slate-700 dark:bg-slate-800">

                            <div class="flex items-center gap-4">
                                <button id="umBack" type="button"
                                    class="invisible rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                    <i class="fas fa-arrow-left mr-1.5 text-xs"></i> Back
                                </button>
                                <span id="umStepLabel" class="hidden text-xs font-medium text-slate-400 sm:inline">Step 1 of 3</span>
                            </div>

                            <div id="umFootSummary" class="hidden flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400 md:flex"></div>

                            <div class="flex items-center gap-3">
                            <button id="closeModalFooter" type="button"
                                class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                                Cancel
                            </button>

                            <button id="umNext" type="button"
                                class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                Next <i class="fas fa-arrow-right ml-1.5 text-xs"></i>
                            </button>

                            <button type="submit"
                                class="hidden rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                                Save User
                            </button>
                            </div>

                        </div>
                    </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="saveOverlay" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-lg bg-white px-5 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Saving user...</span>
        </div>
    </div>

    <style>
        tr.group-alt {
            background-color: rgba(250, 204, 21, 0.06);
        }
    </style>

    <script>
        const companiesData = {!! $company->toJson() !!};
        const divisionsData = {!! $divisions->toJson() !!};
        const isSbyUser = @json($usersSby);

        // Talenta Live status_talenta, matched to this ms_user row by NPK — display only,
        // does not change ms_user.status. '-' when there's no NPK or no Talenta match.
        function renderTalentaStatusBadge(data, type) {
            if (type && type !== 'display') return data ?? '';
            if (!data) return '<span class="ul-muted">—</span>';
            const isResigned = /resign/i.test(data);
            return `<span class="ul-pill ${isResigned ? 'off' : 'ok'}">${$('<i>').text(data).html()}</span>`;
        }

        // ---- Shared cell renderers for the user tables ----
        const ulEsc = v => $('<i>').text(v ?? '').html();
        function ulActions(data, type, row, withDelete) {
            if (type !== 'display') return data;
            return `<div class="ul-act">
                <label class="switch cursor-pointer" title="Active / Inactive">
                    <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                    <span class="slider round"></span>
                </label>
                <button type="button" class="editAppBtn ul-btn edit" data-id="${data}" title="Edit User"><i class="fas fa-pen"></i></button>
                <button type="button" class="impersonateBtn ul-btn key" data-id="${data}" title="Login As"><i class="fas fa-key"></i></button>
                <button type="button" class="resetPwdBtn ul-btn rst" data-id="${data}" title="Reset Password"><i class="fas fa-rotate-left"></i></button>
                ${withDelete ? `<button type="button" class="hardDeleteBtn ul-btn del" data-id="${data}" data-name="${ulEsc(row.name)}" title="Delete Permanently"><i class="fas fa-trash"></i></button>` : ''}
            </div>`;
        }
        function ulName(data, type) {
            if (type !== 'display') return data;
            const n = String(data ?? '').trim();
            const ini = n.split(/\s+/).slice(0, 2).map(w => w.charAt(0)).join('').toUpperCase() || '?';
            return `<div class="ul-user"><span class="ul-av">${ulEsc(ini)}</span><b>${ulEsc(n)}</b></div>`;
        }
        function ulStatus(data, type) {
            if (type !== 'display') return data;
            return data === 'A' ? '<span class="ul-pill ok">Active</span>' : '<span class="ul-pill off">Inactive</span>';
        }
        function ulMatched(data, type) {
            if (type !== 'display') return data ?? '';
            if (!data) return '';
            return String(data).split(', ').map(r => {
                const k = r.trim().toLowerCase();
                return `<span class="ul-tag ${['email', 'npk', 'username'].includes(k) ? k : 'other'}">${ulEsc(r)}</span>`;
            }).join('');
        }
        function ulDate(data, type) {
            if (type !== 'display') return data ?? '';
            return data ? `<span class="ul-muted">${ulEsc(new Date(data).toLocaleDateString())}</span>` : '<span class="ul-muted">—</span>';
        }

        // Comma-separated codes -> compact chips (first 4 + "+N"); raw text for search/sort/export.
        function renderChipList(data, type) {
            if (type !== 'display') return data ?? '';
            const items = String(data ?? '').split(',').map(x => x.trim()).filter(Boolean);
            if (!items.length) return '<span class="ul-muted">—</span>';
            const esc = v => $('<i>').text(v).html();
            const max = 4;
            const chips = items.slice(0, max).map(v => `<span class="ul-chip">${esc(v)}</span>`).join('');
            const more = items.length > max ? `<span class="ul-chip more">+${items.length - max}</span>` : '';
            return `<div class="ul-chips" title="${esc(items.join(', '))}">${chips}${more}</div>`;
        }

        // Rebuild the Company multi-select options, scoped to the chosen company group.
        function renderCompanyOptions(group, selectedIds = []) {
            const $select = $('select[name="cpny_id[]"]');
            $select.empty();

            companiesData
                .filter(c => !group || c.group_cpny_id === group)
                .forEach(c => {
                    const isSelected = selectedIds.includes(c.cpny_id);
                    $select.append(new Option(`${c.cpny_id} - ${c.cpny_name}`, c.cpny_id, isSelected, isSelected));
                });

            $select.trigger('change');
        }

        // Rebuild the Division multi-select options, scoped to the chosen company group.
        function renderDivisionOptions(group, selectedIds = []) {
            const $select = $('select[name="division_id[]"]');
            $select.empty();

            divisionsData
                .filter(d => !group || d.group_cpny_id === group)
                .forEach(d => {
                    const isSelected = selectedIds.includes(d.division_id);
                    $select.append(new Option(`${d.division_id} - ${d.division_name}`, d.division_id, isSelected, isSelected));
                });

            $select.trigger('change');
        }

        $(document).ready(function() {

            // ===== Tabs =====
            let dupTable = null;
            let dupTableLoaded = false;

            function initDupTable() {
                if (dupTableLoaded) return;
                dupTableLoaded = true;

                let lastGroupKey = null;
                let groupToggle = false;

                dupTable = $('#dupUsersTable').DataTable({
                    ajax: {
                        url: "{{ $usersSby ? route('users-sby.duplicates.json') : route('users.duplicates.json') }}",
                        dataSrc: function(json) {
                            updateDupBadge(json.data ? json.data.length : 0);
                            return json.data;
                        }
                    },
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    order: [],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                    createdRow: function(row, data) {
                        if (data.group_key !== lastGroupKey) {
                            groupToggle = !groupToggle;
                            lastGroupKey = data.group_key;
                        }
                        if (groupToggle) {
                            $(row).addClass('group-alt');
                        }
                    },
                    columns: [{
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: (d, t, r) => ulActions(d, t, r, true)
                        },
                        {
                            data: 'duplicate_reason',
                            className: 'no-pointer',
                            render: ulMatched
                        },
                        {
                            data: 'name',
                            className: 'no-pointer ul-name-cell',
                            render: ulName
                        },
                        {
                            data: 'username',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'email',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'npk',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'cpny_id',
                            className: 'no-pointer',
                            render: renderChipList
                        },
                        {
                            data: 'department_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'business_unit_id',
                            className: 'no-pointer',
                            render: renderChipList
                        },
                        {
                            data: 'jabatan',
                            className: 'no-pointer'
                        },
                        {
                            data: 'created_at',
                            className: 'no-pointer',
                            render: ulDate
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: ulStatus
                        },
                        {
                            data: 'talenta_status',
                            className: 'no-pointer',
                            render: renderTalentaStatusBadge
                        }
                    ]
                });
            }

            function updateDupBadge(count) {
                if (count > 0) {
                    $('#dupCountBadge').removeClass('hidden').text(count);
                } else {
                    $('#dupCountBadge').addClass('hidden');
                }
            }

            @unless ($usersSby)
                // Preload the duplicate count badge even before the tab is opened
                $.getJSON("{{ route('users.duplicates.json') }}", function(json) {
                    updateDupBadge(json.data ? json.data.length : 0);
                });
            @endunless

            // ===== Inactive Users tab =====
            let inactiveTable = null;
            let inactiveTableLoaded = false;

            function initInactiveTable() {
                if (inactiveTableLoaded) return;
                inactiveTableLoaded = true;

                inactiveTable = $('#inactiveUsersTable').DataTable({
                    ajax: {
                        url: "{{ $usersSby ? route('users-sby.inactive.json') : route('users.inactive.json') }}",
                        dataSrc: function(json) {
                            updateInactiveBadge(json.data ? json.data.length : 0);
                            return json.data;
                        }
                    },
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                    columns: [{
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: (d, t, r) => ulActions(d, t, r, false)
                        },
                        {
                            data: 'name',
                            className: 'no-pointer ul-name-cell',
                            render: ulName
                        },
                        {
                            data: 'username',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'npk',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'email',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'cpny_id',
                            className: 'no-pointer',
                            render: renderChipList
                        },
                        {
                            data: 'department_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'business_unit_id',
                            className: 'no-pointer',
                            render: renderChipList
                        },
                        {
                            data: 'jabatan',
                            className: 'no-pointer'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: ulStatus
                        },
                        {
                            data: 'talenta_status',
                            className: 'no-pointer',
                            render: renderTalentaStatusBadge
                        }
                    ]
                });
            }

            // ===== User SBY tab =====
            let sbyTable = null;
            let sbyTableLoaded = false;

            function initSbyTable() {
                if (sbyTableLoaded) return;
                sbyTableLoaded = true;

                sbyTable = $('#sbyUsersTable').DataTable({
                    ajax: "{{ route('users-sby.json') }}",
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                    columns: [{
                            data: null,
                            defaultContent: ''
                        },
                        {
                            data: 'id',
                            render: (d, t, r) => ulActions(d, t, r, false)
                        },
                        {
                            data: 'name',
                            className: 'no-pointer ul-name-cell',
                            render: ulName
                        },
                        {
                            data: 'username',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'email',
                            className: 'no-pointer ul-muted'
                        },
                        {
                            data: 'division_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'jabatan',
                            className: 'no-pointer'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: ulStatus
                        }
                    ]
                });
            }

            function updateInactiveBadge(count) {
                if (count > 0) {
                    $('#inactiveCountBadge').removeClass('hidden').text(count);
                } else {
                    $('#inactiveCountBadge').addClass('hidden');
                }
            }

            // Preload the inactive count badge even before the tab is opened
            $.getJSON("{{ $usersSby ? route('users-sby.inactive.json') : route('users.inactive.json') }}", function(json) {
                updateInactiveBadge(json.data ? json.data.length : 0);
            });

            function activateTab(tab) {
                const isList = tab === 'list';
                const isDuplicates = tab === 'duplicates';
                const isInactive = tab === 'inactive';
                const isSby = tab === 'sby';

                $('#tabPanelList').toggleClass('hidden', !isList);
                $('#tabPanelDuplicates').toggleClass('hidden', !isDuplicates);
                $('#tabPanelInactive').toggleClass('hidden', !isInactive);
                $('#tabPanelSby').toggleClass('hidden', !isSby);

                $('#tabBtnList')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isList)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isList);
                $('#tabBtnDuplicates')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isDuplicates)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isDuplicates);
                $('#tabBtnInactive')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isInactive)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isInactive);
                $('#tabBtnSby')
                    .toggleClass('bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400', isSby)
                    .toggleClass('bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400', !isSby);

                if (isList) {
                    table.columns.adjust().draw(false);
                } else if (isDuplicates) {
                    initDupTable();
                    if (dupTable) dupTable.columns.adjust().draw(false);
                } else if (isInactive) {
                    initInactiveTable();
                    if (inactiveTable) inactiveTable.columns.adjust().draw(false);
                } else if (isSby) {
                    initSbyTable();
                    if (sbyTable) sbyTable.columns.adjust().draw(false);
                }
            }

            $('#tabBtnList').on('click', function() {
                activateTab('list');
            });
            $('#tabBtnDuplicates').on('click', function() {
                activateTab('duplicates');
            });
            $('#tabBtnInactive').on('click', function() {
                activateTab('inactive');
            });
            $('#tabBtnSby').on('click', function() {
                activateTab('sby');
            });

            let table = $('#usersTable').DataTable({
                ajax: "{{ $usersSby ? route('users-sby.json') : route('users.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    },
                    {
                        targets: 6, // Division
                        visible: {{ $usersSby ? 'true' : 'false' }}
                    },
                    {
                        targets: 7, // Company
                        visible: {{ $usersSby ? 'false' : 'true' }}
                    },
                    {
                        targets: 8, // Department
                        visible: {{ $usersSby ? 'false' : 'true' }}
                    },
                    {
                        targets: 9, // Business Unit
                        visible: {{ $usersSby ? 'false' : 'true' }}
                    }
                ],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'User',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'User',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            orthogonal: 'export',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, {
                        data: 'id',
                        render: function(data, type, row) {
                            if (type !== 'display') return data;
                            return `<div class="ul-act">
                                <label class="switch cursor-pointer" title="Active / Inactive">
                                    <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                    <span class="slider round"></span>
                                </label>
                                <button type="button" class="editAppBtn ul-btn edit" data-id="${data}" title="Edit User"><i class="fas fa-pen"></i></button>
                                <button type="button" class="impersonateBtn ul-btn key" data-id="${data}" title="Login As"><i class="fas fa-key"></i></button>
                                <button type="button" class="resetPwdBtn ul-btn rst" data-id="${data}" title="Reset Password"><i class="fas fa-rotate-left"></i></button>
                            </div>`;
                        }
                    },

                    {
                        data: 'name',
                        className: 'no-pointer ul-name-cell',
                        render: function(data, type) {
                            if (type !== 'display') return data;
                            const n = String(data ?? '').trim();
                            const ini = n.split(/\s+/).slice(0, 2).map(w => w.charAt(0)).join('').toUpperCase() || '?';
                            return `<div class="ul-user"><span class="ul-av">${$('<i>').text(ini).html()}</span><b>${$('<i>').text(n).html()}</b></div>`;
                        }
                    },
                    {
                        data: 'username',
                        className: 'no-pointer ul-muted'
                    },
                    {
                        data: 'npk',
                        className: 'no-pointer ul-muted'
                    },
                    {
                        data: 'email',
                        className: 'no-pointer ul-muted'
                    },
                    {
                        data: 'division_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'cpny_id',
                        className: 'no-pointer',
                        render: renderChipList
                    },
                    {
                        data: 'department_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'business_unit_id',
                        className: 'no-pointer',
                        render: renderChipList
                    },
                    {
                        data: 'jabatan',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            return data === 'A' ?
                                '<span class="ul-pill ok">Active</span>' :
                                '<span class="ul-pill off">Inactive</span>';
                        }
                    },
                    {
                        data: 'talenta_status',
                        className: 'no-pointer',
                        render: renderTalentaStatusBadge
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data) {
                            return `<input type="checkbox" class="userRowCheckbox" value="${data}">`;
                        }
                    }
                ]
            });

            // Checkbox selection + bulk-deactivate for the Users List table (#usersTable).
            // Defined inline (not as a top-level function) so it can close over
            // inactiveTable/dupTable/sbyTable, which only exist inside this ready callback.
            // "Select all" selects every row matching the *current* search/filter, not just
            // the current page, so e.g. searching "Resigned" then Select All only grabs the
            // Talenta-resigned rows — matching what the user can see.
            function initBulkDeactivate(table) {
                const $selectAll = $('#selectAllUsers');
                const $bulkBtn = $('#bulkDeactivateBtn');
                const $countLabel = $('#bulkSelectedCount');
                let selected = new Set();

                function refreshBulkUI() {
                    $bulkBtn.prop('disabled', selected.size === 0);
                    if (selected.size > 0) {
                        $countLabel.removeClass('hidden').text(`${selected.size} selected`);
                    } else {
                        $countLabel.addClass('hidden').text('');
                        $selectAll.prop('checked', false);
                    }
                }

                $('#usersTable tbody').on('change', '.userRowCheckbox', function() {
                    const id = parseInt($(this).val(), 10);
                    if (this.checked) {
                        selected.add(id);
                    } else {
                        selected.delete(id);
                    }
                    refreshBulkUI();
                });

                $selectAll.on('change', function() {
                    const checked = this.checked;
                    table.rows({ search: 'applied' }).every(function() {
                        const rowData = this.data();
                        if (checked) {
                            selected.add(rowData.id);
                        } else {
                            selected.delete(rowData.id);
                        }
                        $(this.node()).find('.userRowCheckbox').prop('checked', checked);
                    });
                    refreshBulkUI();
                });

                // The id set on screen changes on every reload (add/edit/toggle/sync/bulk
                // action), so a stale selection could silently deactivate the wrong rows.
                table.on('xhr', function() {
                    selected.clear();
                    refreshBulkUI();
                });

                $bulkBtn.on('click', function() {
                    if (selected.size === 0) return;

                    Swal.fire({
                        icon: 'warning',
                        title: `Deactivate ${selected.size} user(s)?`,
                        text: 'Sets their account status to Inactive (X). You can reactivate from the Inactive Users tab.',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Yes, deactivate',
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: "{{ $usersBase }}/bulk-deactivate",
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            data: { ids: Array.from(selected) },
                            success: function(res) {
                                selected.clear();
                                refreshBulkUI();
                                table.ajax.reload(null, false);
                                if (inactiveTable) inactiveTable.ajax.reload(null, false);
                                if (dupTable) dupTable.ajax.reload(null, false);
                                if (sbyTable) sbyTable.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deactivated!',
                                    text: `${res.count} user(s) deactivated.`,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed!',
                                    text: xhr.responseJSON?.message || 'Gagal menonaktifkan user.'
                                });
                            }
                        });
                    });
                });
            }

            initBulkDeactivate(table);

            // ===== Filter Talenta Status =====
            // Custom predicate (not column().search()) because the cell only renders
            // the Talenta status as styled HTML — matching against row.talenta_status
            // directly is simpler and more reliable than parsing the rendered markup.
            // Guarded to #usersTable only since this extension is global to every
            // DataTable on the page (dup/inactive/sby tables don't have this field).
            $.fn.dataTable.ext.search.push(function(settings, searchData, dataIndex) {
                if (settings.nTable.id !== 'usersTable') return true;

                const val = $('#filterTalentaStatus').val();
                if (!val) return true;

                const rowData = settings.oInstance.api().row(dataIndex).data();
                const status = rowData && rowData.talenta_status ? String(rowData.talenta_status) : '';
                const isResigned = /resign/i.test(status);

                if (val === 'resigned') return isResigned;
                if (val === 'active') return status !== '' && !isResigned;
                if (val === 'none') return status === '';
                return true;
            });

            $('#filterTalentaStatus').on('change', function() {
                table.draw();
            });

            // ===== Filter Company (kolom 4) =====
            $('#filterCompany').on('change', function() {
                const val = $(this).val();

                table
                    .column(7) // cpny_id
                    .search(val || '', false, false)
                    .draw();
            });

            // ===== Filter Division (kolom 6) =====
            $('#filterDivision').on('change', function() {
                const val = $(this).val();

                table
                    .column(6) // division_id
                    .search(val || '', false, false)
                    .draw();
            });

            // ===== Filter Department (kolom 8) =====
            $('#filterDepartment').on('change', function() {
                const val = $(this).val();

                table
                    .column(8) // department_id
                    .search(val || '', false, false)
                    .draw();
            });

            $('#filterBusinessUnit').on('change', function() {
                const val = $(this).val();
                table
                    .column(9) // business_unit_id
                    .search(val || '', false, false)
                    .draw();
            });

            // ===== Filter Jabatan (kolom 10) =====
            $('#filterJabatan').on('change', function() {
                const val = $(this).val();
                table
                    .column(10) // jabatan
                    .search(val || '', false, false)
                    .draw();
            });



            // ===== Clear Filter =====
            $('#clearUserFilters').on('click', function() {

                // reset select2 UI + value
                $('#filterCompany').val(null).trigger('change');
                $('#filterDivision').val(null).trigger('change');
                $('#filterDepartment').val(null).trigger('change');
                $('#filterBusinessUnit').val(null).trigger('change');
                $('#filterJabatan').val(null).trigger('change');
                $('#filterTalentaStatus').val(null).trigger('change');

                // reset datatable filter untuk kolom yg benar
                table.column(6).search(''); // division
                table.column(7).search(''); // company
                table.column(8).search(''); // department
                table.column(9).search(''); // business unit
                table.column(10).search(''); // jabatan

                // reset global search juga kalau ada
                table.search('');

                table.draw();
            });



            $('#addAppBtn').click(function() {

                $('#modalTitle').text('Add User');

                $('#appForm')[0].reset();

                $('#id').val('');
                $('#username').val('');
                usernameManuallyEdited = false;

                $('#homepage').val(null).trigger('change');

                $('select[name="role[]"]').val(null).trigger('change');
                $('select[name="jabatan"]').val('').trigger('change');

                $('#group_cpny_id').prop('disabled', isSbyUser);
                if (isSbyUser) {
                    $('#group_cpny_id').val('SBY').trigger('change');
                    renderCompanyOptions('SBY', []);
                    renderDivisionOptions('SBY', []);
                } else {
                    $('#group_cpny_id').val('').trigger('change');
                    renderCompanyOptions(null, []);
                    renderDivisionOptions(null, []);
                }
                $('select[name="department_id[]"]').val(null).trigger('change');
                $('select[name="business_unit_id[]"]').val(null).trigger('change');
                $('select[name="role_ids[]"]').val(null).trigger('change');

                $('#origin_cpny_id').val('').trigger('change');
                $('#origin_department_id').val('').trigger('change');

                const $submitBtn = $('#appForm').find('button[type="submit"]');

                $submitBtn.data('loading', false);
                $submitBtn.prop('disabled', false).text('Save User');

                $('#closeModal').prop('disabled', false);

                $('#appModal').removeClass('hidden');
            });



            $(document).on('click', '.editAppBtn', function() {
                let appId = $(this).data('id');

                const $submitBtn = $('#appForm').find('button[type="submit"]');
                $submitBtn.data('loading', false);
                $submitBtn.prop('disabled', false).text('Save User');

                $('#closeModal').prop('disabled', false);

                $.get(`{{ $usersBase }}/${appId}/edit`, function(app) {

                    $('#modalTitle').text('Edit User');

                    $('#id').val(app.id);
                    $('#name').val(app.name);
                    $('#username').val(app.username);
                    $('#email').val(app.email);
                    $('#npk').val(app.npk);

                    $('#homepage').val(app.homepage).trigger('change');

                    $('select[name="jabatan"]').val(app.jabatan).trigger('change');
                    $('select[name="role[]"]').val(app.role).trigger('change');

                    $('#group_cpny_id').prop('disabled', isSbyUser);
                    $('#group_cpny_id').val(isSbyUser ? 'SBY' : app.group_cpny_id).trigger('change');
                    renderCompanyOptions(isSbyUser ? 'SBY' : app.group_cpny_id, app.cpny_id);
                    renderDivisionOptions(isSbyUser ? 'SBY' : app.group_cpny_id, app.division_id);
                    $('select[name="department_id[]"]').val(app.department_id).trigger('change');
                    $('select[name="business_unit_id[]"]').val(app.business_unit_id).trigger(
                        'change');

                    $('select[name="role_ids[]"]').val(app.role_ids).trigger('change');

                    $('#origin_cpny_id').val(app.origin_cpny_id).trigger('change');
                    $('#origin_department_id').val(app.origin_department_id).trigger('change');

                    $('#appModal').removeClass('hidden');
                });
            });



            // ✅ Toggle Status (Active <-> Inactive)
            $(document).on('change', '.toggleStatus', function() {
                let appId = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `{{ $usersBase }}/${appId}/toggle-status`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        status: newStatus
                    },
                    success: function() {
                        table.ajax.reload(null, false);
                        if (inactiveTable) inactiveTable.ajax.reload(null, false);
                        if (dupTable) dupTable.ajax.reload(null, false);
                        if (sbyTable) sbyTable.ajax.reload(null, false);
                    }
                });
            });

            $('#appForm').submit(function(e) {
                e.preventDefault();

                const $form = $('#appForm');
                const $submitBtn = $form.find('button[type="submit"]');
                const $closeBtn = $('#closeModal');
                const $overlay = $('#saveOverlay');

                if ($submitBtn.data('loading') === true) {
                    return;
                }

                let appId = $('#id').val();
                let url = appId ? `{{ $usersBase }}/${appId}` : "{{ $usersSby ? route('users-sby.store') : route('users.store') }}";
                let method = 'POST';

                let formData = new FormData(document.getElementById('appForm'));

                if (appId) {
                    formData.append('_method', 'PUT');
                }

                $submitBtn.data('loading', true);
                $submitBtn.prop('disabled', true).text('Saving User...');
                $closeBtn.prop('disabled', true);
                $overlay.removeClass('hidden').addClass('flex');

                $.ajax({
                    url: url,
                    type: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#appModal').addClass('hidden');
                        table.ajax.reload(null, false);
                        if (inactiveTable) inactiveTable.ajax.reload(null, false);
                        if (dupTable) dupTable.ajax.reload(null, false);
                        if (sbyTable) sbyTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Successfully!',
                            text: appId ? 'User berhasil diupdate.' :
                                'User berhasil ditambahkan.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal menyimpan data user.';

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            message = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .join('\n');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: message
                        });
                    },
                    complete: function() {
                        $submitBtn.data('loading', false);
                        $submitBtn.prop('disabled', false).text('Save User');
                        $closeBtn.prop('disabled', false);
                        $overlay.removeClass('flex').addClass('hidden');
                    }
                });
            });

            let usernameManuallyEdited = false;

            $('#username').on('input', function() {
                usernameManuallyEdited = $(this).val().trim() !== '';
            });

            $('#email').on('input', function() {
                if (!usernameManuallyEdited) {
                    $('#username').val($(this).val().split('@')[0] || '');
                }
            });

            $('#addAppBtn').on('click.resetUsernameFlag', function() {
                usernameManuallyEdited = false;
            });

            $('#closeModal, #closeModalFooter').on('click', function() {

                const $submitBtn = $('#appForm').find('button[type="submit"]');

                if ($submitBtn.data('loading') === true) return;

                $('#appModal').addClass('hidden');
            });
        });


        // ---- User modal: live avatar, mode badge, role picker, selection counters ----
        $(function () {
            const $roles = $('select[name="role_ids[]"]');
            const $types = $('select[name="role[]"]');
            const $grid = $('#umRoleGrid');

            // The blank placeholder <option> counts as a value — always strip it.
            const valsOf = function ($s) { return ($s.val() || []).map(String).filter(Boolean); };

            const CATS = [
                ['Finance', '#10b981', /finance|\bap\b|asset|cost|treasury|budget/i],
                ['HR', '#ec4899', /\bhc|recruit|training|hcbp/i],
                ['Operation', '#f59e0b', /operation|engineering|\bga\b|parking|warehouse|teknik|\bfo\b|\bbs\b|reception|event|vms|permit|office/i],
                ['Legal & Leasing', '#8b5cf6', /legal|leasing|tenan/i],
                ['Purchasing', '#06b6d4', /purchas|vendor/i],
                ['Voucher', '#ef4444', /\bvp\b|voucher/i],
                ['Project', '#3b82f6', /project|team|captain|meeting/i],
                ['Dashboard', '#6366f1', /dashboard|\bgm\b|\bom\b/i],
                ['IT & System', '#64748b', /\bit\b|\buser\b|system|spin wheel|admin/i]
            ];
            const OTHER_COLOR = '#94a3b8';
            function catOf(label) {
                for (const c of CATS) { if (c[2].test(label)) return c[0]; }
                return 'Other';
            }
            function colorOf(name) {
                for (const c of CATS) { if (c[0] === name) return c[1]; }
                return OTHER_COLOR;
            }

            $roles.find('option').each(function () {
                if (!this.value) return;
                const label = $(this).text().trim();
                const cat = catOf(label.toLowerCase());
                $('<div class="um-role" role="checkbox"></div>')
                    .attr('data-v', this.value)
                    .attr('data-t', label.toLowerCase())
                    .attr('data-c', cat)
                    .attr('title', label)
                    .css('--c', colorOf(cat))
                    .append('<span class="um-tick"><i class="fas fa-check"></i></span>')
                    .append($('<span></span>').text(label))
                    .appendTo($grid);
            });

            let activeCat = 'All';
            function renderCats() {
                const names = ['All'];
                CATS.forEach(function (c) { names.push(c[0]); });
                names.push('Other');
                const $c = $('#umRoleCats').empty();
                names.forEach(function (n) {
                    const $items = n === 'All' ? $grid.children() : $grid.children('[data-c="' + n + '"]');
                    if (!$items.length) return;
                    const on = $items.filter('.on').length;
                    const $b = $('<button type="button" class="um-cat"></button>')
                        .attr('data-cat', n).toggleClass('active', n === activeCat);
                    if (n !== 'All') $b.append($('<i class="um-dot"></i>').css('background', colorOf(n)));
                    $b.append(document.createTextNode(n));
                    $b.append($('<em class="um-on"></em>').toggleClass('none', !on).text(on + '/' + $items.length));
                    $b.appendTo($c);
                });
            }
            function applyFilter() {
                const q = ($('#umRoleSearch').val() || '').trim().toLowerCase();
                let shown = 0;
                $grid.children().each(function () {
                    const ok = (activeCat === 'All' || $(this).attr('data-c') === activeCat)
                        && (!q || String($(this).attr('data-t')).indexOf(q) !== -1);
                    $(this).toggle(ok);
                    if (ok) shown++;
                });
                $('#umRoleEmpty').toggleClass('hidden', shown > 0);
            }
            $('#umRoleCats').on('click', '.um-cat', function () {
                activeCat = $(this).attr('data-cat');
                renderCats();
                applyFilter();
            });

            function syncRoles() {
                const sel = new Set(valsOf($roles));
                $grid.children().each(function () {
                    $(this).toggleClass('on', sel.has(String($(this).attr('data-v'))));
                });
                $('#umRoleCount').text(sel.size);
                const $t = $('#umRoleTray').empty();
                if (!sel.size) {
                    $t.append('<div class="um-empty"><i class="fas fa-layer-group"></i><span>No roles assigned yet.<br>Pick some from the left.</span></div>');
                } else {
                    $grid.children('.on').each(function () {
                        $('<div class="um-chip"></div>')
                            .append($('<i class="um-dot"></i>').css('background', colorOf($(this).attr('data-c'))))
                            .append($('<span class="um-chip-t"></span>').text($(this).text()).attr('title', $(this).text()))
                            .append($('<button type="button" title="Remove"></button>').attr('data-v', $(this).attr('data-v')).html('<i class="fas fa-times"></i>'))
                            .appendTo($t);
                    });
                }
                renderCats();
            }
            function setRoles(vals) { $roles.val(vals).trigger('change'); }

            $('#umRoleTray').on('click', 'button[data-v]', function () {
                const v = String($(this).attr('data-v'));
                setRoles(valsOf($roles).filter(function (x) { return x !== v; }));
            });
            $grid.on('click', '.um-role', function () {
                const v = String($(this).attr('data-v'));
                const cur = new Set(valsOf($roles));
                if (cur.has(v)) cur.delete(v); else cur.add(v);
                setRoles(Array.from(cur));
            });
            $roles.on('change', syncRoles);

            $('#umRoleSearch').on('input', applyFilter);
            $('#umRoleAll').on('click', function () {
                const cur = new Set(valsOf($roles));
                $grid.children(':visible').each(function () { cur.add(String($(this).attr('data-v'))); });
                setRoles(Array.from(cur));
            });
            $('#umRoleClear').on('click', function () { setRoles([]); });

            // ---- User Type cards (multi-select, synced to hidden select) ----
            function syncTypes() {
                const sel = new Set(valsOf($types));
                $('#umTypeCards .um-type').each(function () {
                    $(this).toggleClass('on', sel.has(String($(this).attr('data-v'))));
                });
                $('#umTypeCards').removeClass('um-shake');
            }
            $('#umTypeCards').on('click', '.um-type', function () {
                const v = String($(this).attr('data-v'));
                const cur = new Set(valsOf($types));
                if (cur.has(v)) cur.delete(v); else cur.add(v);
                $types.val(Array.from(cur)).trigger('change');
            });
            $types.on('change', syncTypes);


            function refreshHeader() {
                const name = ($('#name').val() || '').trim();
                const email = ($('#email').val() || '').trim();
                const editing = !!$('#id').val();
                const ini = name ? name.split(/\s+/).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase() : '';
                $('#umAvatar').empty().append(ini ? $('<span>').text(ini) : '<i class="fas fa-user"></i>');
                $('#umMode').text(editing ? 'Editing' : 'New');
                $('#umSubtitle').text(editing && (name || email)
                    ? [name, email].filter(Boolean).join(' · ')
                    : 'Create and manage user permissions and access scopes.');
            }
            $('#name, #email').on('input', refreshHeader);

            // ---- 3-step wizard ----
            const LAST = 3;
            let step = 1;
            const scrollEl = document.getElementById('umScroll');

            function fieldTarget(el) {
                const $el = $(el);
                return $el.hasClass('select2-hidden-accessible') ? $el.next('.select2-container').find('.select2-selection') : $el;
            }
            function shake(el) {
                el.classList.remove('um-shake');
                void el.offsetWidth;
                el.classList.add('um-shake');
            }
            function validateStep(n) {
                let first = null;
                $('#appForm [data-step="' + n + '"]').find('[required]').each(function () {
                    let ok;
                    if (this.tagName === 'SELECT') ok = this.multiple ? valsOf($(this)).length > 0 : !!this.value;
                    else ok = this.value.trim() !== '' && (this.type !== 'email' || this.checkValidity());
                    const $t = fieldTarget(this);
                    $t.toggleClass('um-invalid', !ok);
                    if (!ok && !first) first = this;
                });
                if (n === 3 && !valsOf($types).length) {
                    shake(document.getElementById('umTypeCards'));
                    if (!first) first = document.getElementById('umTypeCards');
                }
                if (first && first.tagName === 'INPUT') first.focus();
                return !first;
            }
            function goTo(n) {
                step = Math.max(1, Math.min(LAST, n));
                $('#appForm [data-step]').each(function () {
                    $(this).toggleClass('um-active', Number($(this).attr('data-step')) === step);
                });
                $('#umStepper .um-st').each(function () {
                    const s = Number($(this).attr('data-step'));
                    $(this).toggleClass('active', s === step).toggleClass('done', s < step);
                });
                $('#umStepper .um-st-line').each(function (i) { $(this).toggleClass('done', i < step - 1); });
                $('#umBack').toggleClass('invisible', step === 1);
                $('#umNext').toggleClass('hidden', step === LAST);
                $('#appForm button[type="submit"]').toggleClass('hidden', step !== LAST);
                $('#umStepLabel').text('Step ' + step + ' of ' + LAST);
                scrollEl.scrollTop = 0;
            }
            function tryAdvanceTo(target) {
                for (let s = step; s < target; s++) {
                    if (!validateStep(s)) { goTo(s); return; }
                }
                goTo(target);
            }
            $('#umNext').on('click', function () { tryAdvanceTo(step + 1); });
            $('#umBack').on('click', function () { goTo(step - 1); });
            $('#umStepper').on('click', '.um-st', function () {
                const t = Number($(this).attr('data-step'));
                if (t <= step) goTo(t); else tryAdvanceTo(t);
            });
            $('#appForm').on('input change', function () { $('#appForm .um-invalid').removeClass('um-invalid'); });

            // Capture phase: runs before the existing save handler. Enter on steps 1-2 advances; Save validates every step.
            document.getElementById('appForm').addEventListener('submit', function (e) {
                if (step < LAST) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    tryAdvanceTo(step + 1);
                    return;
                }
                for (let s = 1; s <= LAST; s++) {
                    if (!validateStep(s)) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        goTo(s);
                        return;
                    }
                }
            }, true);

            function updateSummary() {
                const n = function (name) { return valsOf($('select[name="' + name + '"]')).length; };
                const r = n('role_ids[]'), c = n('cpny_id[]'), d = n('department_id[]');
                $('#umStatRoles').text(r);
                $('#umStatCompanies').text(c);
                $('#umStatDepts').text(d);
                const $f = $('#umFootSummary').empty();
                [[r, 'roles'], [c, 'companies'], [d, 'departments']].forEach(function (p) {
                    $('<span class="um-pill"></span>').append($('<b></b>').text(p[0])).append(document.createTextNode(' ' + p[1])).appendTo($f);
                });
            }
            $(document).on('change', '#appForm select', updateSummary);

            new MutationObserver(function () {
                if (!$('#appModal').hasClass('hidden')) {
                    goTo(1);
                    setTimeout(updateSummary, 0);
                    refreshHeader();
                    activeCat = 'All';
                    syncRoles();
                    syncTypes();
                    $('#umRoleSearch').val('').trigger('input');
                }
            }).observe(document.getElementById('appModal'), { attributes: true, attributeFilter: ['class'] });
            syncRoles();
            syncTypes();

            ['cpny_id[]', 'department_id[]', 'division_id[]', 'business_unit_id[]'].forEach(function (n) {
                const $s = $('select[name="' + n + '"]');
                if (!$s.length) return;
                const $field = $s.parent().addClass('um-scope');
                const $label = $field.children('label').first();
                const $b = $('<span class="um-count"></span>');
                const $clear = $('<button type="button" class="um-clear">Clear all</button>');
                $label.addClass('flex items-center').append($b).append($clear);
                // Checklist picker: the (hidden) select stays the source of truth for values.
                const $all = $('<button type="button" class="um-selall">Select shown</button>');
                $clear.before($all);
                const $pick = $('<div class="um-pick"></div>').appendTo($field);
                const $search = $('<input type="text" class="um-pick-q" autocomplete="off" placeholder="Search...">');
                $('<div class="um-pick-s"></div>').append($search).appendTo($pick);
                const $list = $('<div class="um-pick-list"></div>').appendTo($pick);

                const render = function () {
                    const vals = valsOf($s);
                    const q = $search.val().trim().toLowerCase();
                    const top = $list.scrollTop();
                    $b.text(vals.length + ' selected');
                    $clear.toggle(vals.length > 0);
                    $list.empty();
                    let shown = 0;
                    $s.find('option').each(function () {
                        if (!this.value) return;
                        const txt = $(this).text().trim();
                        if (q && txt.toLowerCase().indexOf(q) === -1) return;
                        shown++;
                        const on = vals.indexOf(String(this.value)) !== -1;
                        const i = txt.indexOf(' - ');
                        const $r = $('<label class="um-opt"></label>').toggleClass('on', on).attr('title', txt);
                        $r.append($('<input type="checkbox">').prop('checked', on).val(this.value));
                        if (i > 0) $r.append($('<b></b>').text(txt.slice(0, i))).append($('<span></span>').text(txt.slice(i + 3)));
                        else $r.append($('<span></span>').text(txt));
                        $list.append($r);
                    });
                    if (!shown) $list.append($('<div class="um-pick-empty"></div>').text($s.find('option[value!=""]').length ? 'No match.' : 'No options available.'));
                    $list.scrollTop(top);
                    $all.toggle(shown > 0 && !!q);
                };
                $list.on('change', 'input', function () {
                    const v = String(this.value);
                    const cur = valsOf($s).filter(function (x) { return x !== v; });
                    if (this.checked) cur.push(v);
                    $s.val(cur).trigger('change');
                });
                $all.on('click', function () {
                    const cur = valsOf($s);
                    $list.find('input').each(function () { if (cur.indexOf(String(this.value)) === -1) cur.push(String(this.value)); });
                    $s.val(cur).trigger('change');
                });
                $search.on('input', render);
                $clear.on('click', function () { $s.val([]).trigger('change'); });
                $s.on('change', render);
                render();
            });
        });

        $(document).ready(function() {

            $('.select2').each(function () {
                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: $('#appModal'),
                    placeholder: $(this).data('placeholder') || 'Search and select'
                });
            });

            $('#homepage').select2({
                width: '100%',
                allowClear: true,
                dropdownParent: $('#appModal'),
                placeholder: 'Search and select homepage'
            });

            $('#group_cpny_id').select2({
                width: '100%',
                allowClear: true,
                dropdownParent: $('#appModal'),
                placeholder: 'Select company group'
            });

            // Company and Division options are scoped to the selected group — re-filter on every manual change.
            $(document).on('change', '#group_cpny_id', function() {
                renderCompanyOptions($(this).val(), []);
                renderDivisionOptions($(this).val(), []);
            });

            $('#origin_cpny_id').select2({
                width: '100%',
                allowClear: true,
                dropdownParent: $('#appModal'),
                placeholder: 'Select origin company'
            });

            $('#origin_department_id').select2({
                width: '100%',
                allowClear: true,
                dropdownParent: $('#appModal'),
                placeholder: 'Select origin department'
            });

            $('#filterCompany').select2({
                placeholder: 'All Company',
                allowClear: true,
                width: '100%'
            });

            // Filter Division options are scoped to this page's company group (SBY vs JKT)
            divisionsData
                .filter(d => d.group_cpny_id === (isSbyUser ? 'SBY' : 'JKT'))
                .forEach(d => {
                    $('#filterDivision').append(new Option(`${d.division_id} - ${d.division_name}`, d.division_id));
                });

            $('#filterDivision').select2({
                placeholder: 'All Division',
                allowClear: true,
                width: '100%'
            });

            $('#filterDepartment').select2({
                placeholder: 'All Department',
                allowClear: true,
                width: '100%'
            });

            $('#filterBusinessUnit').select2({
                placeholder: 'All Business Unit',
                allowClear: true,
                width: '100%'
            });

            $('#filterJabatan').select2({
                placeholder: 'All Position',
                allowClear: true,
                width: '100%'
            });

            $('#filterTalentaStatus').select2({
                placeholder: 'All Talenta Status',
                allowClear: true,
                width: '100%'
            });

        });

        // 🔑 Login As (SweetAlert)
        $(document).on('click', '.impersonateBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let userId = $(this).data('id');

            Swal.fire({
                title: "Login As User?",
                text: "You will be logged in as this user.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Continue",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ $usersBase }}/${userId}/impersonate`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(res) {

                            Swal.fire({
                                title: "Successfully!",
                                text: res.message || "Login Successfully.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = res.redirect ?? window.location.href;
                            });

                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Failed!",
                                text: xhr.responseJSON?.message ||
                                    'Failed to log in as user.',
                                icon: "error"
                            });
                        }
                    });
                }

            });
        });

        // 🔁 Reset Password ke default: pakuwon1234#
        $(document).on('click', '.resetPwdBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let userId = $(this).data('id');

            Swal.fire({
                title: "Reset Password?",
                text: "Password user will be reset to default: pakuwon1234#",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Reset",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: `/users/${userId}/reset-password`,
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(res) {

                            Swal.fire({
                                title: "Successfully!",
                                text: res.message || "Password reset successfully.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });

                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Failed!",
                                text: xhr.responseJSON?.message ||
                                    "Reset password failed.",
                                icon: "error"
                            });
                        }
                    });

                }

            });
        });

        // 🗑️ Hard delete: permanently remove the user record
        $(document).on('click', '.hardDeleteBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let userId = $(this).data('id');
            let userName = $(this).data('name') || 'this user';

            Swal.fire({
                title: "Delete Permanently?",
                html: `This will <b>permanently delete</b> <b>${userName}</b> and all of its access records. This action cannot be undone.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#1f2937",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, Delete Permanently",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({
                        url: `/users/${userId}`,
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire({
                                title: "Deleted!",
                                text: res.message || "User permanently deleted.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });

                            if ($.fn.DataTable.isDataTable('#dupUsersTable')) {
                                $('#dupUsersTable').DataTable().ajax.reload(null, false);
                            }
                            if ($.fn.DataTable.isDataTable('#usersTable')) {
                                $('#usersTable').DataTable().ajax.reload(null, false);
                            }
                            if ($.fn.DataTable.isDataTable('#inactiveUsersTable')) {
                                $('#inactiveUsersTable').DataTable().ajax.reload(null, false);
                            }
                            if ($.fn.DataTable.isDataTable('#sbyUsersTable')) {
                                $('#sbyUsersTable').DataTable().ajax.reload(null, false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Failed!",
                                text: xhr.responseJSON?.message ||
                                    "Failed to delete user.",
                                icon: "error"
                            });
                        }
                    });
                }

            });
        });
    </script>


</x-app-layout>
