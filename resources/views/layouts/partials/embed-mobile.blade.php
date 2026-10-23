{{-- Phone / tablet hardening for any document page shown inside My APP's review sheet (iframe#mpDocFrame).
     Everything is scoped to html.mp-frame, which only exists inside that frame, so the normal desktop pages
     and other embeds are untouched. Pure CSS (no build step); breakpoints follow the frame's own width. --}}
<script>
    try {
        if (window.frameElement && window.frameElement.id === 'mpDocFrame') document.documentElement.classList.add(
            'mp-frame');
    } catch (e) {}
</script>
<script>
    // Inside My APP's review sheet only. The document pages finish an approve / reject / revise by calling window.close()
    // and, when that does nothing, by redirecting to their list page (the desktop "browser view"). Here that is replaced by
    // telling the app, which closes the sheet, refreshes its list and confirms the action.
    (function() {
        try {
            if (!(window.frameElement && window.frameElement.id === 'mpDocFrame')) return;
        } catch (e) {
            return;
        }
        var origin = location.origin;
        var send = function(msg) {
            try {
                parent.postMessage(msg, origin);
            } catch (e) {}
        };
        var isAction = function(method, url) {
            var path = String(url || '').split('?')[0];
            return /^post$/i.test(method || '') && /(approve|reject|revise)/i.test(path) && !
                /validate|check|permission/i.test(path);
        };
        var kind = function(url) {
            return /reject/i.test(url) ? 'reject' : (/revise/i.test(url) ? 'revise' : 'approve');
        };
        var worked = function(text) {
            try {
                var j = JSON.parse(text);
                return j.success === true || j.ok === true || j.status === 'success';
            } catch (e) {
                return false;
            }
        };

        var open = XMLHttpRequest.prototype.open,
            sendX = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function(method, url) {
            this.__mp = {
                method: method,
                url: url
            };
            return open.apply(this, arguments);
        };
        XMLHttpRequest.prototype.send = function() {
            var x = this;
            x.addEventListener('load', function() {
                if (x.__mp && isAction(x.__mp.method, x.__mp.url) && x.status >= 200 && x.status <
                    300 && worked(x.responseText)) send({
                    type: 'mp-doc-action',
                    action: kind(x.__mp.url)
                });
            });
            return sendX.apply(this, arguments);
        };

        var nativeFetch = window.fetch;
        if (nativeFetch) window.fetch = function(input, init) {
            var url = typeof input === 'string' ? input : (input && input.url),
                method = (init && init.method) || (input && input.method) || 'GET';
            var p = nativeFetch.apply(this, arguments);
            if (isAction(method, url)) p.then(function(r) {
                return r.clone().text().then(function(t) {
                    if (r.ok && worked(t)) send({
                        type: 'mp-doc-action',
                        action: kind(url)
                    });
                });
            }).catch(function() {});
            return p;
        };

        window.close = function() {
            send({
                type: 'mp-doc-close'
            });
        };

        // Collapse / expand toggle in each document card header (shown on phone / tablet only, see .mp-collapse-btn):
        // folds the card down to its header so a long card does not push the rest of the document off screen.
        // pages whose /show.../{eid} link opens the document as a modal over its list page (see html.mp-doc-modal CSS)
        if (/^\/show(vouchertaxi|bookingcar|ticket|oprtekticket|perizinan|accessrequest|itrecommendation|usagevp|settlementvp|transfervp|receivevp)\//
            .test(location.pathname)) document.documentElement.classList.add('mp-doc-modal');

        // Modal documents on a phone (html.mp-doc-modal): the detail modal can only be left through the sheet's own Back
        // button. Esc is swallowed, and if the modal ever closes by itself (an action finishing, popstate...) the sheet
        // closes too, back to My APP home, instead of exposing the list page behind it.
        if (document.documentElement.classList.contains('mp-doc-modal')) {
            var phone = window.matchMedia('(max-width: 640px)');
            document.addEventListener('keydown', function(e) {
                if (phone.matches && (e.key === 'Escape' || e.key === 'Esc')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            }, true);

            var docModalIds = ['viewVoucherModal', 'viewBookingModal', 'ticketDetailModal', 'permitDetailModal', 'detailModal', 'showModal',
                'viewModal'
            ];
            var isShown = function(el) {
                if (!el || el.classList.contains('hidden')) return false;
                var cs = getComputedStyle(el);
                return cs.display !== 'none' && cs.visibility !== 'hidden';
            };
            // any other dialog of the page (edit / process opened from the detail) still showing?
            var anyDialogShown = function() {
                return Array.prototype.some.call(document.querySelectorAll('[id$="Modal"]'), isShown);
            };
            var watchDocModals = function() {
            docModalIds.forEach(function(id) {
                var el = document.getElementById(id);
                if (!el) return;
                var wasOpen = isShown(el);
                new MutationObserver(function() {
                    var open = isShown(el);
                    if (open) {
                        wasOpen = true;
                        return;
                    }
                    if (!wasOpen || !phone.matches) return;
                    wasOpen = false;
                    setTimeout(function() {
                        if (!anyDialogShown()) send({
                            type: 'mp-doc-close'
                        });
                    }, 450);
                }).observe(el, {
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });
            });
            };
            // this partial sits in <head>: the page's modals only exist once the document has been parsed
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', watchDocModals);
            else watchDocModals();
        }

        var addCollapseToggles = function() {
            document.querySelectorAll('main header.sticky').forEach(function(h) {
                // tab headers (contain a nav) already have their own minimize button
                if (h.querySelector('.mp-collapse-btn, nav') || !h.parentElement) return;
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'mp-collapse-btn';
                b.textContent = '−';
                b.setAttribute('aria-label', 'Collapse');
                b.setAttribute('aria-expanded', 'true');
                b.addEventListener('click', function() {
                    var closed = h.parentElement.classList.toggle('mp-collapsed');
                    b.classList.toggle('is-closed', closed);
                    b.textContent = closed ? '+' : '−';
                    b.setAttribute('aria-expanded', closed ? 'false' : 'true');
                    b.setAttribute('aria-label', closed ? 'Expand' : 'Collapse');
                });
                h.appendChild(b);
            });
        };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', addCollapseToggles);
        else addCollapseToggles();
    })();
</script>
<style>
    html.mp-frame,
    html.mp-frame body {
        overflow-x: hidden;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    html.mp-frame main {
        padding-bottom: calc(.5rem + env(safe-area-inset-bottom)) !important;
    }

    /* the review sheet is an app screen: scrolls, but no visible scrollbar (page, modals and inner scroll boxes) */
    html.mp-frame,
    html.mp-frame * {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html.mp-frame::-webkit-scrollbar,
    html.mp-frame *::-webkit-scrollbar {
        display: none;
    }

    /* the sheet has its own title bar and Close button, so the Home > Module > Show Details breadcrumb is noise;
       the approve / revise / reject buttons that share its row stay and take the whole row */
    html.mp-frame nav[aria-label="Breadcrumb"] {
        display: none;
    }

    html.mp-frame nav[aria-label="Breadcrumb"]+div {
        flex: 1 1 auto;
        justify-content: flex-end;
    }

    html.mp-frame div:has(> nav[aria-label="Breadcrumb"]):not(:has(> nav + div)) {
        display: none;
    }

    html.mp-frame .mp-collapse-btn {
        display: none;
    }

    html.mp-frame .mp-collapsed>:not(header.sticky) {
        display: none !important;
    }

    html.mp-frame .mp-collapsed>header.sticky {
        border-radius: .75rem !important;
    }

    /* Revise / Reject reason dialogs (overlay > panel > h2 + textarea + [Cancel, Confirm]); one markup shared by every
       document page. Phone: slides up as a bottom sheet; wider: centred card. Reject = red confirm, Revise = amber. */
    html.mp-frame .fixed.inset-0:has(> div > h2 + textarea) {
        align-items: flex-end;
        background: rgba(17, 24, 39, .55);
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
        padding: 0 !important;
    }

    html.mp-frame .fixed.inset-0>div[class*="max-w-"]:has(> h2 + textarea) {
        width: 100%;
        max-width: 30rem !important;
        margin: 0 auto;
        padding: 1.25rem 1.25rem calc(1.25rem + env(safe-area-inset-bottom)) !important;
        border-radius: 1.25rem 1.25rem 0 0 !important;
        background: #fff;
        box-shadow: 0 -10px 40px rgba(0, 0, 0, .25);
        animation: mp-sheet-up .2s ease-out;
    }

    html.mp-frame.dark .fixed.inset-0>div[class*="max-w-"]:has(> h2 + textarea) {
        background: #1f2937;
    }

    @keyframes mp-sheet-up {
        from {
            transform: translateY(1.5rem);
            opacity: 0;
        }
    }

    @media (min-width: 641px) {
        html.mp-frame .fixed.inset-0:has(> div > h2 + textarea) {
            align-items: center;
            padding: .5rem !important;
        }

        html.mp-frame .fixed.inset-0>div[class*="max-w-"]:has(> h2 + textarea) {
            border-radius: 1.25rem !important;
            padding-bottom: 1.25rem !important;
        }
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>h2 {
        margin: 0 0 .25rem !important;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -.01em;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>h2::after {
        content: "Tell the requester what needs to change.";
        display: block;
        margin-top: .125rem;
        font-size: .8125rem;
        font-weight: 400;
        letter-spacing: 0;
        color: #6b7280;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea):has(button[id*="eject" i])>h2::after {
        content: "Explain why this is being rejected.";
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>textarea {
        display: block;
        width: 100%;
        min-height: 7.5rem;
        margin: .75rem 0 0 !important;
        padding: .75rem .875rem !important;
        border: 1px solid #d1d5db;
        border-radius: .875rem !important;
        background: #f9fafb;
        color: #111827;
        resize: none;
        outline: none;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>textarea:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .2);
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea):has(button[id*="eject" i])>textarea:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .2);
    }

    html.mp-frame.dark .fixed.inset-0>div:has(> h2 + textarea)>textarea {
        border-color: #4b5563;
        background: #111827;
        color: #f3f4f6;
    }

    html.mp-frame.dark .fixed.inset-0>div:has(> h2 + textarea)>h2::after {
        color: #9ca3af;
    }

    /* Cancel + confirm: two equal full-width buttons */
    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>div:last-child {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .625rem;
        margin-top: 1rem !important;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>div:last-child>button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 2.875rem;
        padding: 0 1rem !important;
        border-radius: .875rem !important;
        font-size: .9375rem !important;
        font-weight: 600;
        line-height: 1.2;
        box-shadow: none;
        touch-action: manipulation;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>div:last-child>button:first-child {
        background: #f3f4f6 !important;
        color: #374151 !important;
    }

    html.mp-frame.dark .fixed.inset-0>div:has(> h2 + textarea)>div:last-child>button:first-child {
        background: #374151 !important;
        color: #e5e7eb !important;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea)>div:last-child>button:last-child {
        background: #d97706 !important;
        color: #fff !important;
    }

    html.mp-frame .fixed.inset-0>div:has(> h2 + textarea):has(button[id*="eject" i])>div:last-child>button:last-child {
        background: #dc2626 !important;
    }

    html.mp-frame img,
    html.mp-frame video,
    html.mp-frame canvas {
        max-width: 100%;
        height: auto;
    }

    html.mp-frame pre,
    html.mp-frame code {
        white-space: pre-wrap;
        overflow-wrap: break-word;
    }

    @media (max-width: 1024px) {

        /* iOS zooms the whole page when a field under 16px gets focus, then never zooms back out */
        html.mp-frame input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="file"]):not([type="color"]),
        html.mp-frame select,
        html.mp-frame textarea {
            font-size: 16px !important;
            min-height: 2.5rem;
            max-width: 100%;
        }

        html.mp-frame textarea {
            min-height: 5rem;
        }

        /* finger-sized actions (Approve / Reject / Revise / Submit ...) */
        html.mp-frame button[type="submit"],
        html.mp-frame .swal2-styled {
            min-height: 2.5rem;
        }

        html.mp-frame button,
        html.mp-frame [role="button"],
        html.mp-frame summary {
            touch-action: manipulation;
        }

        /* Wide tables keep readable columns and scroll sideways inside themselves (never squeezed to one letter per line).
           Cells get a minimum width, fixed-layout tables are switched to auto so those minimums are honoured, and words are
           only ever broken when a single word is longer than the screen (break-word, which unlike "anywhere" does not shrink columns). */
        html.mp-frame table {
            display: block;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            table-layout: auto !important;
        }

        /* a table already inside its own scrolling wrapper stays a normal table (fills the width, wrapper scrolls) */
        html.mp-frame .overflow-x-auto>table,
        html.mp-frame .overflow-auto>table,
        html.mp-frame .overflow-x-scroll>table,
        html.mp-frame .overflow-scroll>table,
        html.mp-frame .table-responsive>table,
        html.mp-frame .dataTables_wrapper table {
            display: table;
            max-width: none;
            overflow: visible;
        }

        html.mp-frame table th,
        html.mp-frame table td {
            min-width: 5.5rem;
            word-break: normal;
            overflow-wrap: normal;
            hyphens: manual;
        }

        html.mp-frame table th:first-child,
        html.mp-frame table td:first-child {
            min-width: 3rem;
        }

        html.mp-frame table td:has(> input[type="checkbox"]):not(:has(*:not(input))),
        html.mp-frame table th:has(> input[type="checkbox"]):not(:has(*:not(input))) {
            min-width: 2.5rem;
        }

        html.mp-frame table td,
        html.mp-frame table th {
            max-width: 28rem;
        }

        html.mp-frame .dataTables_wrapper {
            overflow-x: auto;
        }

        html.mp-frame .dataTables_wrapper .dataTables_length,
        html.mp-frame .dataTables_wrapper .dataTables_filter,
        html.mp-frame .dataTables_wrapper .dataTables_info,
        html.mp-frame .dataTables_wrapper .dataTables_paginate {
            float: none;
            text-align: left;
            margin: .25rem 0;
        }

        html.mp-frame .dataTables_wrapper .dataTables_filter input {
            width: 100%;
            margin-left: 0;
        }

        /* a single very long value (email, URL) wraps instead of pushing the page wide; table cells are left alone (see above) */
        html.mp-frame main p,
        html.mp-frame main label,
        html.mp-frame main a,
        html.mp-frame main span {
            overflow-wrap: break-word;
        }

        html.mp-frame table a,
        html.mp-frame table span,
        html.mp-frame table p,
        html.mp-frame table label {
            overflow-wrap: normal;
        }

        /* alerts / dialogs fit the screen */
        html.mp-frame .swal2-popup {
            width: min(34rem, calc(100vw - 1rem)) !important;
            max-width: calc(100vw - 1rem) !important;
            padding: 1rem !important;
            font-size: .9375rem;
            box-sizing: border-box;
        }

        html.mp-frame .swal2-title {
            font-size: 1.125rem;
            overflow-wrap: anywhere;
        }

        html.mp-frame .swal2-html-container {
            max-height: 62vh;
            max-height: 62dvh;
            overflow-y: auto;
            margin: .75rem 0 0;
            overflow-wrap: anywhere;
        }

        html.mp-frame .swal2-actions {
            flex-wrap: wrap;
            gap: .5rem;
            width: 100%;
        }

        html.mp-frame .swal2-actions .swal2-styled {
            flex: 1 1 8rem;
            margin: 0;
        }

        html.mp-frame .swal2-textarea,
        html.mp-frame .swal2-input {
            width: 100% !important;
            margin: .5rem 0 0 !important;
            font-size: 16px !important;
        }

        /* the page's own modals (fixed overlay + centred panel): panel never taller than the screen, scrolls inside */
        html.mp-frame .fixed.inset-0 {
            padding: .5rem;
        }

        html.mp-frame .fixed.inset-0>[class*="max-w-"]:not(.absolute),
        html.mp-frame .fixed.inset-0>*>[class*="max-w-"]:not(.absolute) {
            max-width: 100% !important;
            max-height: calc(100vh - 1rem);
            max-height: calc(100dvh - 1rem);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    @media (max-width: 640px) {

        /* Documents that open as a modal over their list page, reached through their /show.../{eid} link (Voucher Taxi,
           Booking Car, Eng Ticket, Access Request, IT Recommendation, the four VPL voucher pages): the sheet's script adds
           html.mp-doc-modal for those links. The detail modal then fills the whole screen with no X / Close, and the list
           page behind it is never shown. The sheet's own Back button is the only way out. */
        html.mp-doc-modal #viewVoucherModal,
        html.mp-doc-modal #viewBookingModal,
        html.mp-doc-modal #ticketDetailModal,
        html.mp-doc-modal #permitDetailModal,
        html.mp-doc-modal #detailModal,
        html.mp-doc-modal #showModal,
        html.mp-doc-modal #viewModal {
            padding: 0 !important;
            align-items: stretch !important;
            justify-content: stretch !important;
        }

        html.mp-doc-modal #viewVoucherModal [class*="max-w-"],
        html.mp-doc-modal #viewBookingModal [class*="max-w-"],
        html.mp-doc-modal #ticketDetailModal [class*="max-w-"],
        html.mp-doc-modal #permitDetailModal [class*="max-w-"],
        html.mp-doc-modal #detailModal [class*="max-w-"],
        html.mp-doc-modal #showModal [class*="max-w-"],
        html.mp-doc-modal #viewModal [class*="max-w-"],
        html.mp-doc-modal #ticketDetailModal .modal-panel,
        html.mp-doc-modal #detailModal .modal-panel {
            width: 100% !important;
            max-width: none !important;
            height: 100vh !important;
            height: 100dvh !important;
            min-height: 0 !important;
            max-height: none !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
        }

        html.mp-doc-modal #closeViewVoucherModal,
        html.mp-doc-modal #closeViewVoucherModalFooter,
        html.mp-doc-modal #closeViewBookingModal,
        html.mp-doc-modal #closeViewBookingModalFooter,
        html.mp-doc-modal #closeViewModal,
        html.mp-doc-modal #closeViewModalFooter,
        html.mp-doc-modal #btnCloseShowModal,
        html.mp-doc-modal #btnCloseShowModalFooter,
        html.mp-doc-modal #ticketDetailModal .btn-close-modal,
        html.mp-doc-modal #permitDetailModal .btnCloseDetail,
        html.mp-doc-modal #detailModal .btn-close-modal {
            display: none !important;
        }

        /* no flash of the list page before the detail modal appears */
        html.mp-doc-modal main {
            visibility: hidden;
        }

        html.mp-doc-modal main #viewVoucherModal,
        html.mp-doc-modal main #viewBookingModal,
        html.mp-doc-modal main #ticketDetailModal,
        html.mp-doc-modal main #permitDetailModal,
        html.mp-doc-modal main #detailModal,
        html.mp-doc-modal main #showModal,
        html.mp-doc-modal main #viewModal {
            visibility: visible;
        }

        /* two-up form/detail grids that have no responsive class stack on a phone */
        html.mp-frame main .grid.grid-cols-2:not([class*="sm:grid-cols"]):not([class*="md:grid-cols"]):not([class*="lg:grid-cols"]):not(.keep-cols),
        html.mp-frame main .grid.grid-cols-3:not([class*="sm:grid-cols"]):not([class*="md:grid-cols"]):not([class*="lg:grid-cols"]):not(.keep-cols) {
            grid-template-columns: minmax(0, 1fr);
        }

        html.mp-frame main [class*="flex"][class*="justify-between"]:not(.flex-nowrap):not(.keep-row) {
            flex-wrap: wrap;
        }

        html.mp-frame main h1 {
            font-size: 1.25rem;
        }

        /* Fixed-height cards (h-[400px] overflow-y-auto on the info card, h-[235..430px] on the open detail tab) make a
           scroll box inside the scrolling sheet. In the sheet they grow to their content and the sheet scrolls as one page. */
        html.mp-frame main [class~="h-[235px]"]:not(.chart-container),
        html.mp-frame main [class~="h-[400px]"]:not(.chart-container),
        html.mp-frame main [class~="h-[430px]"]:not(.chart-container),
        html.mp-frame main [class~="h-[500px]"]:not(.chart-container) {
            height: auto !important;
            max-height: none !important;
            overflow-y: visible !important;
        }

        /* Approve / Revise / Reject (x-approval-actions, which replaces the hidden breadcrumb): one full-width row,
           buttons share it equally and are finger-sized. */
        html.mp-frame nav[aria-label="Breadcrumb"]+div {
            flex: 1 1 100%;
            width: 100%;
        }

        html.mp-frame nav[aria-label="Breadcrumb"]+div>div,
        html.mp-frame nav[aria-label="Breadcrumb"]+div>div>div.flex {
            width: 100%;
        }

        html.mp-frame nav[aria-label="Breadcrumb"]+div>div>div.flex>button,
        html.mp-frame nav[aria-label="Breadcrumb"]+div>div>div.flex>a {
            flex: 1 1 0;
            min-width: 0;
        }

        html.mp-frame nav[aria-label="Breadcrumb"]+div>div>div.flex>button,
        html.mp-frame nav[aria-label="Breadcrumb"]+div>div>div.flex>a>button {
            display: inline-flex;
            width: 100%;
            min-height: 2.75rem;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            font-size: .9375rem;
        }

        /* Document card header, phone layout: document number with the status (dot + text) beneath it on the left,
           a small round icon-only Print button on the right. Relies on the shared markup header.sticky > h1 + div. */
        html.mp-frame main header.sticky {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            column-gap: .75rem;
            row-gap: .125rem;
            padding: .75rem 1rem;
            background-color: #f9fafb !important;
            border-bottom: 1px solid #e5e7eb;
        }

        html.mp-frame.dark main header.sticky {
            background-color: #374151 !important;
            border-bottom-color: #4b5563;
        }

        html.mp-frame main header.sticky>h1 {
            grid-column: 1;
            grid-row: 1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .25rem .5rem;
            font-size: 1.0625rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -.01em;
            overflow-wrap: anywhere;
        }

        /* the "ID" badge is redundant next to the number */
        html.mp-frame main header.sticky>h1>span[class*="bg-purple-100"] {
            display: none;
        }

        /* extra tags next to the number (PO / SPK / IFCA status / urgent) stay small */
        html.mp-frame main header.sticky>h1>span:not([class*="bg-purple-100"]) {
            font-size: .6875rem;
            padding: .125rem .5rem;
            border-radius: 9999px;
            letter-spacing: 0;
        }

        /* the status/print wrapper dissolves so its children sit in the header grid */
        html.mp-frame main header.sticky>div {
            display: contents;
        }

        /* status: plain coloured text with a dot, no pill */
        html.mp-frame main header.sticky>div>span:first-child {
            grid-column: 1;
            grid-row: 2;
            justify-self: start;
            gap: .375rem;
            padding: 0 !important;
            background: none !important;
            font-size: .8125rem !important;
            line-height: 1.25;
        }

        html.mp-frame main header.sticky>div>span:first-child::before {
            content: "";
            width: .5rem;
            height: .5rem;
            border-radius: 9999px;
            background: currentColor;
        }

        html.mp-frame main header.sticky>div>:not(:first-child) {
            grid-column: 2;
            grid-row: 1 / span 2;
            align-self: center;
        }

        /* Print PDF: round icon-only button (label kept for screen readers via font-size 0) */
        html.mp-frame main header.sticky>div button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0 !important;
            gap: 0;
            font-size: 0 !important;
            border-radius: 9999px;
            background: rgba(99, 102, 241, .12) !important;
            color: #4f46e5 !important;
            box-shadow: none;
        }

        html.mp-frame main header.sticky>div button:disabled {
            background: rgba(128, 128, 128, .15) !important;
            color: #9ca3af !important;
        }

        html.mp-frame.dark main header.sticky>div button:not(:disabled) {
            color: #a5b4fc !important;
        }

        /* collapse / expand: same small round gradient button the tabs card already has ("−" open, "+" folded);
           the invisible ::after widens the tap area beyond its 20px look */
        html.mp-frame main header.sticky>.mp-collapse-btn {
            position: relative;
            display: inline-flex;
            grid-column: 3;
            grid-row: 1 / span 2;
            align-self: center;
            align-items: center;
            justify-content: center;
            width: 1.25rem;
            height: 1.25rem;
            padding: 0;
            border: 0;
            border-radius: 9999px;
            background: linear-gradient(to bottom, #a5b4fc, #818cf8, #6366f1);
            color: #1e1b4b;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .25), inset 0 1px 0 rgba(255, 255, 255, .6), inset 0 -1px 1px rgba(0, 0, 0, .15);
            touch-action: manipulation;
        }

        html.mp-frame.dark main header.sticky>.mp-collapse-btn {
            background: linear-gradient(to bottom, #818cf8, #6366f1, #4f46e5);
            color: #eef2ff;
        }

        html.mp-frame main header.sticky>.mp-collapse-btn::after {
            content: "";
            position: absolute;
            inset: -.625rem;
        }

        html.mp-frame main header.sticky {
            grid-template-columns: minmax(0, 1fr) auto auto;
        }

        html.mp-frame main header.sticky>div button svg {
            display: none;
        }

        html.mp-frame main header.sticky>div button::before {
            content: "";
            width: 1.125rem;
            height: 1.125rem;
            background: currentColor;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'/%3E%3Crect x='6' y='14' width='12' height='8'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'/%3E%3Crect x='6' y='14' width='12' height='8'/%3E%3C/svg%3E") center / contain no-repeat;
        }
    }
</style>
