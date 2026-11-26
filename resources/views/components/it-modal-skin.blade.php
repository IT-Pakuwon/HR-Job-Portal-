{{-- Shared look for the popups of Ticket Support, IT Recommendation and Access Request.
     Role classes: itm (overlay, also .sf-field for dropdowns) > itm-dialog > itm-head (+ itm-close) | itm-body > itm-card | itm-foot (+ itm-btn).
     Fields, tables and boxes that the page scripts build inside a popup are styled too. Include once per page. --}}
<style>
    .itm .modal-backdrop { background-color: rgba(15, 23, 42, .5); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }
    #modalCreateTicket.itm { background-color: rgba(15, 23, 42, .5); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }
    .itm:not(:has(> .modal-backdrop)) { background-color: rgba(15, 23, 42, .5); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px); }
    .itm .itm-dialog { border: 0; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, .35), 0 0 0 1px rgba(15, 23, 42, .05); }

    /* header: icon badge, title, actions, close */
    .itm .itm-head {
        display: flex; flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: flex-start; gap: .75rem;
        padding: .875rem 1.5rem; border-bottom: 1px solid #f1f5f9; background: #fff;
    }
    .itm .itm-head::before {
        content: var(--itm-icon, "\f15c"); font-family: "Font Awesome 6 Free"; font-weight: 900; font-size: 1rem; line-height: 1;
        display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem;
        border-radius: .75rem; background: var(--color-blue-600); color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
    }
    .itm .itm-head > :first-child { flex: 1 1 12rem; min-width: 0; }
    .itm .itm-head h2, .itm .itm-head h3 { font-size: 1rem; line-height: 1.4; font-weight: 700; letter-spacing: -.01em; color: #1f2937; }
    .itm .itm-head p { margin-top: 0; font-size: .75rem; line-height: 1.25rem; color: #6b7280; }
    .itm .itm-head span.rounded-full, .itm .itm-head span.rounded-lg { white-space: nowrap; } /* status badge stays on one line */
    .itm .itm-head button { min-height: 2.25rem; height: 2.25rem; border-radius: .5rem; }
    .itm .itm-head .itm-close { width: 2.25rem; padding: 0; border: 0; background: transparent; color: #9ca3af; box-shadow: none; }
    .itm .itm-head .itm-close:hover { background: #f3f4f6; color: #374151; }

    #createTicketModal, #ticketDetailModal, #modalCreateTicket { --itm-icon: "\f145"; }
    #responseTicketModal { --itm-icon: "\f4fc"; }
    #processTicketModal { --itm-icon: "\f04b"; }
    #pendingTicketModal { --itm-icon: "\f28b"; }
    #transferTicketModal { --itm-icon: "\f362"; }
    #envisionTicketModal { --itm-icon: "\f0eb"; }
    #completeTicketModal { --itm-icon: "\f00c"; }
    #reopenTicketModal { --itm-icon: "\f2f9"; }
    #soListModal { --itm-icon: "\f46d"; }
    #createModal { --itm-icon: "\f2db"; }
    #processModal { --itm-icon: "\f085"; }
    #editRecommendationModal { --itm-icon: "\f304"; }
    #attachmentModal { --itm-icon: "\f0c6"; }
    #requestModal { --itm-icon: "\f084"; }
    #processHardwareModal { --itm-icon: "\f2db"; }
    #processSoftwareModal { --itm-icon: "\f121"; }

    /* body, section cards, footer */
    .itm .itm-body { background-color: #f8fafc; }
    .itm .itm-card, .itm .itm-dialog div.rounded-lg.border.bg-white { border-radius: .875rem; border-color: #eef2f6; box-shadow: 0 1px 2px rgba(15, 23, 42, .05); }
    .itm .itm-foot { background-color: #fbfcfd; border-top: 1px solid #f1f5f9; }
    .itm .itm-foot button { min-height: 2.5rem; border-radius: .5rem; font-weight: 600; }
    .itm .itm-btn {
        display: inline-flex; align-items: center; justify-content: center; min-height: 2.5rem; padding: 0 1.25rem;
        border: 1px solid #d1d5db; border-radius: .5rem; background: #fff; font-size: .875rem; color: #374151; box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
    }
    .itm .itm-btn:hover { background: #f3f4f6; color: #111827; }
    .itm .itm-primary {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 2.5rem; padding: 0 1.25rem; border: 0; border-radius: .5rem;
        background-color: var(--color-blue-600); font-size: .875rem; font-weight: 600; color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
    }
    .itm button.itm-primary:hover:not(:disabled) { background-color: var(--color-blue-700); color: #fff; }
    .itm .itm-primary:disabled { opacity: .6; cursor: not-allowed; }
    /* the dark navy buttons become the app's blue */
    .itm .bg-slate-900:not(.modal-backdrop) { background-color: var(--color-blue-600); color: #fff; }
    .itm .itm-dialog button.bg-gray-900 { background-color: var(--color-blue-600); color: #fff; }
    .itm .itm-dialog button.bg-gray-900:hover:not(:disabled) { background-color: var(--color-blue-700); }
    .itm button.bg-slate-900:hover:not(:disabled) { background-color: var(--color-blue-700); }

    /* fields */
    .itm label:not(:has(input, div, p, i, svg)):not(.dataTables_wrapper label) { font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }
    .itm input:is(:not([type]), [type="text"], [type="search"], [type="date"], [type="datetime-local"], [type="time"], [type="number"], [type="password"], [type="email"], [type="tel"]):not(.select2-search__field):not(.ql-tooltip input):not(.dataTables_filter input),
    .itm select:not(.select2-hidden-accessible):not(.dataTables_length select), .itm textarea:not(.select2-search__field) {
        border: 1px solid #d1d5db; border-radius: .5rem; background-color: #fff; font-size: .875rem; color: #1f2937;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .05); transition: border-color .15s, box-shadow .15s;
    }
    .itm input:is(:not([type]), [type="text"], [type="search"], [type="date"], [type="datetime-local"], [type="time"], [type="number"], [type="password"], [type="email"], [type="tel"]):not(.select2-search__field):not(.ql-tooltip input):not(.dataTables_filter input),
    .itm select:not(.select2-hidden-accessible):not(.dataTables_length select):not([multiple]) { height: 2.5rem; padding-left: .75rem; padding-right: .75rem; }
    .itm select:not(.select2-hidden-accessible):not(.dataTables_length select):not([multiple]) {
        -webkit-appearance: none; appearance: none; padding-right: 2.25rem; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%239ca3af'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .6rem center; background-size: 1.1rem;
    }
    .itm input:not([type="checkbox"]):not([type="radio"]):focus, .itm select:focus, .itm textarea:focus { outline: none; border-color: var(--color-blue-500); box-shadow: 0 0 0 3px rgba(59, 130, 246, .14); }
    .itm input:is([readonly], :disabled):not([type="checkbox"]):not([type="radio"]):not(.select2-search__field), .itm select:disabled, .itm textarea:is([readonly], :disabled) { background-color: #f3f4f6 !important; color: #6b7280 !important; } /* read only fields stay grey */
    .itm input.is-invalid, .itm textarea.is-invalid { border-color: #ef4444; }

    /* field titles written as divs */
    .itm .itm-dialog div.mb-2.text-sm.font-medium { font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }

    /* text editor */
    .itm .ql-toolbar.ql-snow { border-color: #d1d5db; border-radius: .5rem .5rem 0 0; background: #f9fafb; }
    .itm .ql-container.ql-snow { border-color: #d1d5db; border-radius: 0 0 .5rem .5rem; background: #fff; }

    /* upload areas */
    .itm .itm-dialog .border-dashed { border-width: 2px; border-radius: .75rem; }
    .itm .itm-dialog label.border-dashed:hover, .itm .itm-dialog div.border-dashed.cursor-pointer:hover { border-color: #60a5fa; background-color: #eff6ff; }

    /* tables, including the ones the scripts build */
    .itm .itm-dialog table:not(.ql-editor table) thead th { font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; }

    /* ticket detail tabs */
    .itm .ticket-detail-tab.active { background: var(--color-blue-600); border-color: var(--color-blue-600); color: #fff; }

    /* dark */
    .dark .itm .itm-dialog { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, .6), 0 0 0 1px rgba(255, 255, 255, .08); }
    .dark .itm .itm-head { background: #0f172a; border-color: rgba(255, 255, 255, .06); }
    .dark .itm .itm-head h2, .dark .itm .itm-head h3 { color: #fff; }
    .dark .itm .itm-head p { color: #9ca3af; }
    .dark .itm .itm-head .itm-close:hover { background: rgba(255, 255, 255, .08); color: #e5e7eb; }
    .dark .itm .itm-body { background-color: #0b1220; }
    .dark .itm .itm-card, .dark .itm .itm-dialog div.rounded-lg.border.bg-white { border-color: rgba(255, 255, 255, .07); }
    .dark .itm .itm-foot { background-color: #0f172a; border-color: rgba(255, 255, 255, .06); }
    .dark .itm .itm-btn { background: #1f2937; border-color: #4b5563; color: #e5e7eb; }
    .dark .itm .itm-btn:hover { background: #374151; color: #fff; }
    .dark .itm .bg-slate-900:not(.modal-backdrop), .dark .itm button[class*="dark:bg-white "], .dark .itm button.bg-slate-900 { background-color: var(--color-blue-600); color: #fff; }
    .dark .itm input:is(:not([type]), [type="text"], [type="search"], [type="date"], [type="datetime-local"], [type="time"], [type="number"], [type="password"], [type="email"], [type="tel"]):not(.select2-search__field):not(.ql-tooltip input):not(.dataTables_filter input),
    .dark .itm select:not(.select2-hidden-accessible):not(.dataTables_length select), .dark .itm textarea:not(.select2-search__field) { background-color: #111827; border-color: #4b5563; color: #f3f4f6; }
    .dark .itm input:is([readonly], :disabled):not([type="checkbox"]):not([type="radio"]):not(.select2-search__field), .dark .itm select:disabled, .dark .itm textarea:is([readonly], :disabled) { background-color: rgba(255, 255, 255, .05) !important; color: #9ca3af !important; }
    .dark .itm .ql-toolbar.ql-snow { background: rgba(255, 255, 255, .04); border-color: #4b5563; }
    .dark .itm .ql-container.ql-snow { background: #111827; border-color: #4b5563; }
    .dark .itm .itm-dialog label.border-dashed:hover, .dark .itm .itm-dialog div.border-dashed.cursor-pointer:hover { background-color: rgba(59, 130, 246, .08); }
</style>
<script>
    // dropdown lists opened from a popup get the same skin as the field
    document.addEventListener('DOMContentLoaded', function() {
        $(document).on('select2:open', function(e) {
            if ($(e.target).closest('.itm').length) $('.select2-container--open .select2-dropdown').addClass('sf-s2-dropdown');
        });
    });
</script>
