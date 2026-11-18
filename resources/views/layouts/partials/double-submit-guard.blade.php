<script>
/*
 * Global double-submit guard.
 *
 * Stops a double click / double Enter from saving the same record twice.
 * Covers every write the app makes from the browser:
 *   - jQuery $.ajax / $.post / $.get and jQuery.ajax({type:'PUT'|'DELETE'…})
 *   - window.fetch with a non-GET method
 *   - ordinary <form> submits that navigate the page
 *
 * A write is a "duplicate" when its method + URL + payload match one that is
 * still in flight, or that succeeded within the last COOLDOWN_MS. The
 * duplicate is silently dropped (its callbacks never run, so no second toast,
 * error dialog or list refresh). A failed request is released immediately, so
 * the user can fix the problem and try again. GET/HEAD are never touched.
 *
 * Opt out for one call with { guard: false } (jQuery) or the header
 * X-No-Guard: 1 (fetch).
 */
(function () {
    'use strict';
    if (window.__doubleSubmitGuard) return;
    window.__doubleSubmitGuard = true;

    var COOLDOWN_MS = 800;      // block identical writes right after a success
    var MAX_PENDING_MS = 30000; // a request hanging longer than this is forgotten
    var SKIP_URL = /\/livewire\b|\/broadcasting\b/i;
    var seen = Object.create(null); // key -> { at: ms, done: bool }

    function summarize(data) {
        if (data == null) return '';
        if (typeof data === 'string') return data;
        if (typeof FormData !== 'undefined' && data instanceof FormData) {
            var parts = [];
            data.forEach(function (v, k) {
                if (k === '_token') return;
                parts.push(k + '=' + (v && v.name !== undefined ? 'file:' + v.name + ':' + v.size : v));
            });
            return parts.join('&');
        }
        if (typeof URLSearchParams !== 'undefined' && data instanceof URLSearchParams) {
            return data.toString().replace(/(^|&)_token=[^&]*/g, '');
        }
        try {
            return (window.jQuery ? jQuery.param(data) : JSON.stringify(data)).replace(/(^|&)_token=[^&]*/g, '');
        } catch (e) {
            return '';
        }
    }

    function isDuplicate(key) {
        var s = seen[key];
        if (!s) return false;
        if (!s.done && Date.now() - s.at > MAX_PENDING_MS) { delete seen[key]; return false; }
        return true;
    }

    function begin(key) { seen[key] = { at: Date.now(), done: false }; }

    function finish(key, ok) {
        if (!ok) { delete seen[key]; return; }
        seen[key] = { at: Date.now(), done: true };
        setTimeout(function () { if (seen[key] && seen[key].done) delete seen[key]; }, COOLDOWN_MS);
    }

    // ── jQuery ────────────────────────────────────────────────────────────
    if (window.jQuery) {
        var origAjax = jQuery.ajax;
        jQuery.ajax = function (url, options) {
            var o = (typeof url === 'object' && url) ? url : (options || {});
            var target = (typeof url === 'string') ? url : (o.url || '');
            var method = String(o.type || o.method || 'GET').toUpperCase();

            if (method === 'GET' || method === 'HEAD' || o.guard === false || SKIP_URL.test(target)) {
                return origAjax.apply(this, arguments);
            }

            var key = method + ' ' + target + ' ' + summarize(o.data);
            if (isDuplicate(key)) {
                // Inert promise: callbacks of the ignored click never fire.
                return jQuery.extend(jQuery.Deferred().promise(), { abort: function () {} });
            }

            begin(key);
            var xhr = origAjax.apply(this, arguments);
            if (xhr && typeof xhr.then === 'function') {
                xhr.then(function () { finish(key, true); }, function () { finish(key, false); });
            } else {
                finish(key, false);
            }
            return xhr;
        };
    }

    // ── fetch ─────────────────────────────────────────────────────────────
    if (typeof window.fetch === 'function') {
        var origFetch = window.fetch.bind(window);
        window.fetch = function (input, init) {
            init = init || {};
            var method = String(init.method || (input && input.method) || 'GET').toUpperCase();
            var target = typeof input === 'string' ? input : (input && input.url) || '';
            var headers = init.headers || {};
            var noGuard = headers instanceof Headers ? headers.get('X-No-Guard') : headers['X-No-Guard'];

            if (method === 'GET' || method === 'HEAD' || noGuard || SKIP_URL.test(target)) {
                return origFetch(input, init);
            }

            var key = method + ' ' + target + ' ' + summarize(init.body);
            if (isDuplicate(key)) return new Promise(function () {}); // ignored click: never settles

            begin(key);
            var p = origFetch(input, init);
            p.then(function (r) { finish(key, r && r.ok); }, function () { finish(key, false); });
            return p;
        };
    }

    // ── Plain form submits (page navigates) ───────────────────────────────
    // Bubble phase: if a handler already took over (ajax form), it called
    // preventDefault and the guards above apply instead.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM' || e.defaultPrevented) return;
        if (form.dataset.submitting === '1') { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        setTimeout(function () {
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = true; });
        }, 0);
        // A submit that doesn't leave the page (a file download, target=_blank)
        // must not leave the form locked.
        setTimeout(function () {
            delete form.dataset.submitting;
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = false; });
        }, 8000);
    });
    // Back/forward cache: a restored page must be usable again.
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('form[data-submitting="1"]').forEach(function (f) {
            delete f.dataset.submitting;
            f.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = false; });
        });
    });
})();
</script>
