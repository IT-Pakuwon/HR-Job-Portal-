{{-- Shared look for the SPP* Create/Edit forms (SPPB, SPPJ, SPPK, SPPT): native-select chevron, header Select2 skin, lookup modals. --}}
<style>
        /* Create SPPB form: slim chevron for native selects */
        .sf-select {
            -webkit-appearance: none; appearance: none; padding-right: 2.25rem; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .6rem center; background-size: 1.1rem;
        }

        /* Header dropdowns (Select2) - same field look as the other inputs */
        .sf-field .select2-container { width: 100% !important; }
        .sf-field .select2-container--default .select2-selection--single {
            height: 40px; display: flex; align-items: center; background: #fff;
            border: 1px solid #d1d5db; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0,0,0,.05);
            transition: border-color .15s, box-shadow .15s;
        }
        .sf-field .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px; padding-left: .75rem; padding-right: 2rem; font-size: .875rem; color: #1f2937; width: 100%;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sf-field .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9ca3af; }
        .sf-field .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px; right: 4px; width: 26px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E") no-repeat center / 16px;
            transition: transform .2s ease;
        }
        .sf-field .select2-container--default .select2-selection--single .select2-selection__arrow b { display: none !important; }
        .sf-field .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow { transform: rotate(180deg); }
        .sf-field .select2-container--default:hover .select2-selection--single { border-color: #93c5fd; }
        .sf-field .select2-container--default.select2-container--focus .select2-selection--single,
        .sf-field .select2-container--default.select2-container--open .select2-selection--single { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.14); }
        .dark .sf-field .select2-container--default .select2-selection--single { background: #1f2937; border-color: #4b5563; }
        .dark .sf-field .select2-container--default .select2-selection--single .select2-selection__rendered { color: #f3f4f6; }
        .sf-field .select2-container--default.select2-container--disabled .select2-selection--single { background: #f3f4f6; cursor: not-allowed; }
        .sf-field .select2-container--default.select2-container--disabled .select2-selection__rendered { color: #9ca3af; }
        .dark .sf-field .select2-container--default.select2-container--disabled .select2-selection--single { background: rgba(255,255,255,.04); }
        .sf-s2-dropdown { border: 1px solid #e5e7eb !important; border-radius: .75rem !important; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,.10); margin-top: 4px; }
        .sf-s2-dropdown .select2-results__option { padding: .5rem .875rem; font-size: .875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sf-s2-dropdown .select2-results__option--highlighted[aria-selected] { background: #eff6ff !important; color: #1d4ed8 !important; }
        .sf-s2-dropdown .select2-results__option[aria-selected=true] { background: #dbeafe; color: #1d4ed8; font-weight: 600; }
        .sf-s2-dropdown .select2-results__option[aria-disabled=true] { color: #9ca3af; }
        .sf-s2-dropdown .select2-search--dropdown { padding: .5rem; }
        .sf-s2-dropdown .select2-search--dropdown .select2-search__field { border-radius: .5rem; border: 1px solid #d1d5db; padding: .35rem .6rem; }
        .dark .sf-s2-dropdown { border-color: #374151 !important; }

        /* Validation: the script marks the hidden <select> as .is-invalid -> show it on the Select2 box */
        .sf-field select.is-invalid ~ .select2-container .select2-selection--single { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.14) !important; }

        /* Lookup modals */
        .sf-modal tbody tr:hover td { background-color: rgb(248 250 252); }
        .dark .sf-modal tbody tr:hover td { background-color: rgb(255 255 255 / .04); }
        .sf-modal thead { z-index: 10; }
        /* Select2 inside the Location / Sub Location modal: same field look as the form */
        #modalLokasi .select2-container { width: 100% !important; }
        #modalLokasi .select2-container--default .select2-selection--single {
            height: 40px; display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0,0,0,.05);
            transition: border-color .15s, box-shadow .15s;
        }
        #modalLokasi .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: .75rem; font-size: .875rem; color: #1f2937; }
        #modalLokasi .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; right: 6px; }
        #modalLokasi .select2-container--default.select2-container--focus .select2-selection--single,
        #modalLokasi .select2-container--default.select2-container--open .select2-selection--single { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.14); }
    </style>
