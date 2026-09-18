(function () {
    'use strict';

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function init() {
        var container = document.getElementById('pendingFeedbackReminders');
        if (!container) return;

        fetch('/training-list/pending-feedback', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : { data: [] }; })
            .then(function (res) {
                var rows = res.data || [];
                if (!rows.length) return;

                container.innerHTML = rows.map(function (row) {
                    var name = esc(row.training_name || '-');
                    var bySuffix = row.speaker_name ? ' by ' + esc(row.speaker_name) : '';
                    var href = '/training-list/feedback/' + encodeURIComponent(row.eid);

                    return '<a href="' + href + '" class="mb-2 flex items-center justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 shadow-sm transition hover:bg-amber-100 dark:border-amber-700/60 dark:bg-amber-900/20 dark:text-amber-300 dark:hover:bg-amber-900/30">'
                        + '<span>📝 Please fill the feedback for training "' + name + '"' + bySuffix + '</span>'
                        + '<span aria-hidden="true">&rarr;</span>'
                        + '</a>';
                }).join('');
            })
            .catch(function () {});
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
