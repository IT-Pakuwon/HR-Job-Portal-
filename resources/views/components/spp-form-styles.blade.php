{{-- Shared look for the SPP* Create/Edit forms (SPPB, SPPJ, SPPK, SPPT): native-select chevron, header Select2 skin, lookup modals. --}}
<style>
        /* Create SPPB form: slim chevron for native selects */
        .sf-select {
            -webkit-appearance: none; appearance: none; padding-right: 2.25rem; cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .6rem center; background-size: 1.1rem;
        }

        /* Dark mode: the global ".dark select" arrow is barely visible, keep the same chevron as light mode */
        .dark select.sf-select:not([multiple]) {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right .6rem center; background-size: 1.1rem; padding-right: 2.25rem;
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

        /* Multi-select (Select2), e.g. Kepada / Tembusan: chips and the search box share one field */
        .sf-field .select2-container--default .select2-selection--multiple {
            display: flex; flex-wrap: wrap; align-items: center; gap: .25rem; position: relative; height: auto; min-height: 40px;
            padding: 3px 2rem 3px .375rem; background: #fff; border: 1px solid #d1d5db; border-radius: .5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,.05); cursor: text; transition: border-color .15s, box-shadow .15s;
        }
        .sf-field .select2-container--default:hover .select2-selection--multiple { border-color: #93c5fd; }
        .sf-field .select2-container--default.select2-container--focus .select2-selection--multiple,
        .sf-field .select2-container--default.select2-container--open .select2-selection--multiple { border: 1px solid #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.14); }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__rendered { display: contents; }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: inline-flex; align-items: center; gap: .375rem; float: none; max-width: 100%; margin: 0; padding: .125rem .5rem;
            background: #eff6ff; border: 1px solid #bfdbfe; border-radius: .375rem; color: #1d4ed8; font-size: .8125rem; line-height: 1.25rem;
        }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__choice__display { padding: 0; cursor: default; }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: static; margin: 0; padding: 0; border: 0; background: transparent; color: #60a5fa; font-size: 1rem; line-height: 1;
        }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover { color: #ef4444; background: transparent; }
        .sf-field .select2-container--default .select2-selection--multiple .select2-search--inline { display: flex; flex: 1 1 8rem; float: none; margin: 0; }
        .sf-field .select2-container--default .select2-selection--multiple .select2-search__field {
            width: 100% !important; height: 28px !important; margin: 0; padding: 0 .375rem; overflow: hidden; resize: none;
            border: 0; outline: 0; box-shadow: none; background: transparent; font-family: inherit; font-size: .875rem; line-height: 28px; color: #1f2937;
        }
        .sf-field .select2-container--default .select2-selection--multiple .select2-search__field::placeholder { color: #9ca3af; }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__clear {
            position: absolute; top: 50%; right: .625rem; transform: translateY(-50%); float: none; margin: 0; padding: 0;
            border: 0; background: transparent; color: #94a3b8; font-size: 1rem; line-height: 1;
        }
        .sf-field .select2-container--default .select2-selection--multiple .select2-selection__clear:hover { color: #ef4444; }
        .sf-field select.is-invalid ~ .select2-container .select2-selection--multiple { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.14) !important; }
        .dark .sf-field .select2-container--default .select2-selection--multiple { background: #1f2937; border-color: #4b5563; }
        .dark .sf-field .select2-container--default .select2-selection--multiple .select2-search__field { color: #f3f4f6; }
        .dark .sf-field .select2-container--default .select2-selection--multiple .select2-selection__choice { background: rgba(59,130,246,.18); border-color: rgba(59,130,246,.35); color: #bfdbfe; }

        /* Clearable single dropdown: keep long text clear of the "x" */
        .sf-field .select2-container--default .select2-selection--single:has(.select2-selection__clear) .select2-selection__rendered { padding-right: 3.5rem; }

        /* The scripts insert the error text right after the <select>; show it under the Select2 box instead */
        .sf-field > .error-feedback { order: 1; }

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
