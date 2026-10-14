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
    }
</style>
