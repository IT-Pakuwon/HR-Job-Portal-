/* Dashboard Legal Leasing: the "Incomplete Document" and "Follow Up Document" tabs.
   The two lists are rendered (hidden) by blocks/dashboard-legal-leasing/tabs.blade.php; this script adds a tab for each to the
   approval panel's tab bar and moves the lists into the same card. Selecting one of ours hides the approval panel's own body
   (filters, list, pager) and shows the list; clicking Waiting Approval / Approval History undoes that and the approval panel's
   own script carries on as before. Same idea as the My Activity tab (multidashboard/my-activity.blade.php), which is added after
   ours because its script comes later in the page. */
(function () {
    const TAB_SEL = 'button[id^="tab-"]';
    const REFRESH_MS = 60000;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function setup() {
        const first = document.getElementById('tab-waiting');
        const sections = [...document.querySelectorAll('.lgl-sec')];
        if (!first || !sections.length) return;

        const bar = first.parentElement;
        const header = bar.parentElement;
        const card = header && header.parentElement;
        if (!card || card === document.body) return;

        const mine = [];
        const state = {};

        sections.forEach((sec) => {
            const key = sec.dataset.lglKey;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.id = 'tab-' + key;
            btn.className = 'dsh-tab';
            btn.textContent = sec.dataset.label;
            bar.appendChild(btn);
            card.appendChild(sec);
            mine.push({ key, btn, sec });
            state[key] = { page: 1, loaded: false, timer: null, req: 0 };
        });

        const el = (sec, name) => sec.querySelector('[data-lgl="' + name + '"]');
        const bodyParts = () => [...card.children].filter((c) => c !== header && !c.classList.contains('lgl-sec') && c.id !== 'myActivityRoot');

        function row(key, r) {
            const waiting = r.days == null ? '' : (r.days === 0 ? 'today' : r.days + ' day' + (r.days > 1 ? 's' : ''));
            const late = r.days != null && r.days > 14;
            const link = '<a href="' + esc(r.url) + '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">' + esc(r.agreement_id) + '</a>';
            const td = 'px-3 py-2 align-top text-slate-600 dark:text-slate-300';
            let cells = '<td class="whitespace-nowrap ' + td + '">' + link + (r.no_psm ? '<div class="mt-1 text-[11px] text-slate-400">' + esc(r.no_psm) + '</div>' : '') + '</td>'
                + '<td class="whitespace-nowrap ' + td + '">' + esc(r.type_label) + '</td>'
                + '<td class="' + td + '">' + esc(r.tenant || '-') + '<div class="text-[11px] text-slate-400">' + esc(r.company || '') + '</div></td>'
                + '<td class="whitespace-nowrap ' + td + '">' + esc(r.unit || '-') + '</td>'
                + '<td class="whitespace-nowrap ' + td + '"><span class="lgl-pill lgl-ok">' + esc(r.step) + '</span></td>';

            if (key === 'incomplete') {
                const names = (r.missing || []);
                const shown = names.slice(0, 3).map(esc).join(', ') + (names.length > 3 ? ' +' + (names.length - 3) + ' more' : '');
                cells += '<td class="whitespace-nowrap ' + td + '">' + esc(r.docs_received) + ' / ' + esc(r.docs_total) + ' received</td>'
                    + '<td class="' + td + '" title="' + esc(names.join(', ')) + '"><span class="font-semibold text-amber-600 dark:text-amber-400">' + names.length + '</span> · ' + shown + '</td>';
            } else {
                cells += '<td class="whitespace-nowrap ' + td + '">' + esc(r.since || '-') + (waiting ? ' <span class="lgl-pill ' + (late ? 'lgl-late' : 'lgl-ok') + '">' + waiting + '</span>' : '') + '</td>';
            }

            cells += '<td class="' + td + '">' + esc(r.pic_legal || '-') + '</td>';

            return '<tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30">' + cells + '</tr>';
        }

        function load(key) {
            const m = mine.find((x) => x.key === key);
            const st = state[key];
            const sec = m.sec;
            const params = new URLSearchParams({
                page: st.page,
                per_page: el(sec, 'size').value,
                search: el(sec, 'search').value.trim(),
                type: el(sec, 'type').value,
            });
            const myReq = ++st.req;

            return fetch(sec.dataset.url + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                credentials: 'same-origin',
            })
                .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
                .then((res) => {
                    if (myReq !== st.req) return; // a newer request is already on its way
                    st.loaded = true;
                    const rows = res.data || [];
                    el(sec, 'rows').innerHTML = rows.map((r) => row(key, r)).join('');
                    el(sec, 'empty').classList.toggle('hidden', rows.length > 0);
                    el(sec, 'info').textContent = 'Showing ' + res.from + ' to ' + res.to + ' of ' + res.total + ' entries';
                    el(sec, 'prev').disabled = res.page <= 1;
                    el(sec, 'next').disabled = res.page >= res.last_page;
                })
                .catch(() => {
                    el(sec, 'info').textContent = 'Could not load the list';
                });
        }

        function open(key) {
            const st = state[key];
            bar.querySelectorAll(TAB_SEL).forEach((b) => { b.className = 'dsh-tab'; });
            mine.forEach((x) => {
                if (x.key === key) x.btn.className = 'dsh-tab dsh-tab-on';
                x.sec.hidden = x.key !== key;
                clearInterval(state[x.key].timer);
            });
            bodyParts().forEach((c) => c.setAttribute('data-lgl-hide', ''));
            load(key);
            // keep the open list as fresh as the other tabs
            st.timer = setInterval(() => { if (!document.hidden) load(key); }, REFRESH_MS);
        }

        function close() {
            mine.forEach((x) => { x.sec.hidden = true; x.btn.className = 'dsh-tab'; clearInterval(state[x.key].timer); });
            card.querySelectorAll('[data-lgl-hide]').forEach((c) => c.removeAttribute('data-lgl-hide'));
        }

        mine.forEach((x) => {
            x.btn.addEventListener('click', () => open(x.key));

            let t = null;
            const reset = () => { state[x.key].page = 1; load(x.key); };
            el(x.sec, 'search').addEventListener('input', () => { clearTimeout(t); t = setTimeout(reset, 300); });
            el(x.sec, 'type').addEventListener('change', reset);
            el(x.sec, 'size').addEventListener('change', reset);
            el(x.sec, 'prev').addEventListener('click', () => { if (state[x.key].page > 1) { state[x.key].page--; load(x.key); } });
            el(x.sec, 'next').addEventListener('click', () => { state[x.key].page++; load(x.key); });
        });

        // Delegated + capture, so it runs before the approval panel's own click handler (which then reloads its list as usual).
        bar.addEventListener('click', (e) => {
            const t = e.target.closest(TAB_SEL);
            if (!t || mine.some((x) => x.btn === t)) return;
            close();
        }, true);

        // the refresh button on the approval panel refreshes the open list too
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#refreshApproval')) return;
            const open = mine.find((x) => !x.sec.hidden);
            if (open) load(open.key);
        });
    }

    // Run right now, not on DOMContentLoaded: the approval panel and the lists are already above this script, and the My Activity
    // tab (added by a later script) must come after ours in the tab bar.
    setup();
})();
