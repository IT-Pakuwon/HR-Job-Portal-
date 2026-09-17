<x-app-layout>
    <div class="mx-auto flex h-[calc(100dvh-72px)] w-full max-w-9xl flex-col overflow-hidden p-2" x-data="{
        currentFolder: @js($folder),
        accountEmail: @js($account?->email ?? ''),
        modalOpen: false,
        loading: false,
        email: null,
        busyId: null,
        toast: null,
        toastOk: true,
        syncing: false,
        loadingOlder: false,
        // Keyed by folder path: true once a Load-older-messages click came
        // back with no more history for that folder, so the button can stop
        // inviting further clicks that would just repeat No older messages
        // found. Lives on the outer component (not the panel partial) so it
        // survives refreshPanel()'s innerHTML swap.
        noMoreOlder: {},

        showToast(message, ok = true) {
            this.toast = message;
            this.toastOk = ok;
            setTimeout(() => { this.toast = null }, 4000);
        },

        // Folder now lives as a path segment (/mailbox/Drafts) rather than a
        // query param, so both link-interception and refresh need to read it
        // back out of the URL's path, not just its query string.
        paramsFromUrl(url) {
            const base = @js(parse_url(url('mailbox'), PHP_URL_PATH));
            const params = Object.fromEntries(url.searchParams.entries());
            const rest = url.pathname.slice(base.length).replace(/^\/+/, '');
            // A link to the default folder (INBOX) has no path segment at all
            // (plain /mailbox), so `rest` is empty — folder must still resolve
            // to 'INBOX' here, not be left unset, or currentFolder goes stale
            // (e.g. still 'Drafts' from before) and openEmail() misreads any
            // Inbox message as a draft.
            params.folder = rest ? rest.split('/').map(decodeURIComponent).join('/') : 'INBOX';
            return params;
        },
        // Intercept clicks on plain <a> links inside the mailbox panel (folder
        // switches, pagination) so they load via AJAX instead of a full reload.
        onPanelClick(e) {
            // Let modifier-clicks (open in new tab/window) and middle-click
            // through untouched instead of hijacking them into an in-page AJAX load.
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
            const a = e.target.closest('a[href]');
            if (!a) return;
            const url = new URL(a.getAttribute('href'), window.location.origin);
            const base = @js(parse_url(url('mailbox'), PHP_URL_PATH));
            if (url.pathname !== base && !url.pathname.startsWith(base + '/')) return;
            e.preventDefault();
            this.loadPanel(this.paramsFromUrl(url));
        },
        // `pushState: false` is used when re-rendering for a browser
        // Back/Forward navigation — the URL has already changed (that's what
        // fired the popstate event), so pushing again here would corrupt the
        // history stack instead of following it.
        // Shared by loadPanel()/refreshPanel(). Two things used to go wrong
        // here: (1) neither checked the response status, so a 422/500 error
        // page (e.g. the mailbox account got disconnected, or the mail
        // server was briefly unreachable) got its raw HTML dumped straight
        // into #mailbox-panel instead of showing an error; and (2) sending
        // 'Accept: text/html' meant an expired session made the auth
        // middleware 302-redirect to the login page rather than return a
        // JSON 401 — fetch() follows redirects transparently, so the LOGIN
        // PAGE's own HTML came back as if it were the panel partial. Asking
        // for JSON here doesn't change what a successful request gets back
        // (the controller always renders the same Blade view either way),
        // it only changes how auth failures are reported.
        fetchPanelHtml(url) {
            return fetch(url, { headers: { 'Accept': 'application/json' } }).then(async (r) => {
                if (!r.ok) {
                    const data = await r.json().catch(() => null);
                    throw new Error(data?.message || 'Failed to load mailbox.');
                }
                return r.text();
            });
        },
        loadPanel(params = {}, { pushState = true } = {}) {
            // Drop empty/default values so the address bar stays clean
            // (e.g. plain /mailbox instead of /mailbox/INBOX?q=&per_page=25) —
            // the server already falls back to these same defaults when absent.
            const defaults = { folder: 'INBOX', per_page: '25', q: '' };
            const clean = {};
            Object.entries(params).forEach(([k, v]) => {
                if (v === null || v === undefined || String(v) === (defaults[k] ?? '')) return;
                clean[k] = v;
            });
            // The /panel XHR endpoint takes folder as a query param (it's never
            // shown to the user); the visible address bar instead gets it as a
            // clean path segment, e.g. /mailbox/Drafts instead of /mailbox?folder=Drafts.
            const qs = new URLSearchParams(clean).toString();
            this.fetchPanelHtml('{{ route('mailbox.panel') }}' + (qs ? '?' + qs : ''))
                .then(html => {
                    document.getElementById('mailbox-panel').innerHTML = html;
                    if (pushState) {
                        const { folder, ...rest } = clean;
                        const folderPath = folder ? '/' + folder.split('/').map(encodeURIComponent).join('/') : '';
                        const restQs = new URLSearchParams(rest).toString();
                        const pageUrl = '{{ route('mailbox.index') }}' + folderPath + (restQs ? '?' + restQs : '');
                        window.history.pushState({}, '', pageUrl);
                    }
                    if (params.folder) this.currentFolder = params.folder;
                })
                .catch(err => this.showToast(err.message || 'Failed to load mailbox.', false));
        },
        refreshPanel() {
            const params = this.paramsFromUrl(new URL(window.location.href));
            const qs = new URLSearchParams(params).toString();
            this.fetchPanelHtml('{{ route('mailbox.panel') }}' + (qs ? '?' + qs : ''))
                .then(html => { document.getElementById('mailbox-panel').innerHTML = html; })
                .catch(err => this.showToast(err.message || 'Failed to refresh mailbox.', false));
        },
        syncNow() {
            if (this.syncing) return;
            this.syncing = true;
            fetch('{{ route('mailbox.sync') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ folder: this.currentFolder }),
            })
                .then(r => r.json())
                .then(data => {
                    this.syncing = false;
                    this.showToast(data.message || (data.success ? 'Synced.' : 'Sync failed.'), data.success !== false);
                    if (data.success) this.refreshPanel();
                })
                .catch(() => { this.syncing = false; this.showToast('Sync failed.', false); });
        },
        loadOlder() {
            if (this.loadingOlder) return;
            this.loadingOlder = true;
            const folder = this.currentFolder;
            fetch('{{ route('mailbox.load-more') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ folder }),
            })
                .then(r => r.json())
                .then(data => {
                    this.loadingOlder = false;
                    this.showToast(data.message || (data.success ? 'Loaded.' : 'Load failed.'), data.success !== false);
                    if (data.success) {
                        if (!data.hasMore) this.noMoreOlder[folder] = true;
                        if (data.fetched > 0) this.refreshPanel();
                    }
                })
                .catch(() => { this.loadingOlder = false; this.showToast('Load failed.', false); });
        },

        emailAttachments: [],
        emailAttachmentsLoading: false,
        // Remote images in a message body load over plain HTTP(S) the
        // moment the iframe renders them — a classic tracking-pixel /
        // read-receipt vector (confirms the address is live, leaks
        // IP/UA/open time). Blocked by default per message, same as most
        // webmail clients; a banner lets the user opt in for that one.
        // Quote characters below are written as \x27 and \x22 hex escapes
        // rather than literal marks, since this whole object is itself the
        // value of an x-data attribute quoted with one of those same marks
        // — writing it literally here would close that attribute early and
        // corrupt the page.
        imagesBlocked: false,
        hasRemoteImages(html) {
            if (!html) return false;
            return /<img\b[^>]*\bsrc\s*=\s*[\x27\x22]https?:\/\//i.test(html)
                || /url\(\s*[\x27\x22]?https?:\/\//i.test(html);
        },
        blockedImagesHtml(html) {
            if (!html) return html;
            const blank = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7';
            return html
                .replace(/(<img\b[^>]*\bsrc\s*=\s*)([\x27\x22])https?:\/\/[^\x27\x22]*\2/gi, `$1$2${blank}$2`)
                .replace(/url\(\s*([\x27\x22]?)https?:\/\/[^)\x27\x22]*\1\s*\)/gi, `url(${blank})`);
        },
        loadImages() {
            this.imagesBlocked = false;
        },
        openEmail(id) {
            if (this.currentFolder === 'Drafts') {
                this.openComposeForDraft(id);
                return;
            }
            this.modalOpen = true;
            this.loading = true;
            this.email = null;
            this.emailAttachments = [];
            this.imagesBlocked = false;
            fetch(`{{ url('mailbox') }}/${id}/content`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.email = data;
                    this.loading = false;
                    this.imagesBlocked = this.hasRemoteImages(data.body_html);
                    if (data.has_attachments) this.loadEmailAttachments(id);
                    // content() marks the message read server-side; the modal
                    // lives outside #mailbox-panel so this is safe to refresh
                    // without disturbing it, and keeps the sidebar unread
                    // badges/row styling in sync without a manual reload.
                    this.refreshPanel();
                })
                .catch(() => { this.loading = false; });
        },
        loadEmailAttachments(id) {
            this.emailAttachmentsLoading = true;
            fetch(`{{ url('mailbox') }}/${id}/attachments`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => { this.emailAttachments = data.attachments || []; this.emailAttachmentsLoading = false; })
                .catch(() => { this.emailAttachmentsLoading = false; });
        },
        formatFileSize(bytes) {
            if (!bytes) return '';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
            return bytes.toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
        },
        // Sender avatar shown in the read modal header: initials + a
        // deterministic color so the same sender always gets the same badge.
        initialsFor(str) {
            const name = (str || '').includes('<') ? str.split('<')[0].trim() : (str || '');
            const words = name.replace(/[^a-zA-Z0-9 ]/g, ' ').trim().split(/\s+/).filter(Boolean);
            if (words.length === 0) return '?';
            return words.length === 1 ? words[0].slice(0, 2).toUpperCase() : (words[0][0] + words[1][0]).toUpperCase();
        },
        avatarColor(str) {
            const palette = ['bg-blue-500', 'bg-emerald-500', 'bg-purple-500', 'bg-amber-500', 'bg-pink-500', 'bg-cyan-600', 'bg-indigo-500', 'bg-rose-500'];
            let hash = 0;
            for (let i = 0; i < (str || '').length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
            return palette[Math.abs(hash) % palette.length];
        },
        // Colored extension badge for attachment cards (PDF, DOCX, ZIP, ...).
        attachmentBadge(name) {
            const ext = (name || '').split('.').pop().toUpperCase();
            const map = {
                PDF: 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                DOC: 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                DOCX: 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                XLS: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
                XLSX: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
                PPT: 'bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400',
                PPTX: 'bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400',
                ZIP: 'bg-purple-100 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400',
                RAR: 'bg-purple-100 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400',
                '7Z': 'bg-purple-100 text-purple-600 dark:bg-purple-500/15 dark:text-purple-400',
                PNG: 'bg-pink-100 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400',
                JPG: 'bg-pink-100 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400',
                JPEG: 'bg-pink-100 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400',
                GIF: 'bg-pink-100 text-pink-600 dark:bg-pink-500/15 dark:text-pink-400',
            };
            return { ext: ext.slice(0, 4), classes: map[ext] || 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' };
        },
        closeModal() {
            this.modalOpen = false;
            this.email = null;
            this.emailAttachments = [];
            this.imagesBlocked = false;
        },

        archiveEmail(id) {
            this.askConfirm({
                title: 'Archive email?',
                message: 'This message will be moved to your Archive folder.',
                danger: false,
                confirmLabel: 'Archive',
                onConfirm: () => this.doArchive(id),
            });
        },
        doArchive(id) {
            if (this.busyId) return;
            this.busyId = id;
            fetch(`{{ url('mailbox') }}/${id}/archive`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { this.busyId = null; this.refreshPanel(); this.showToast(data.message || 'Archived.'); }
                    else { this.busyId = null; this.showToast(data.message || 'Archive failed.', false); }
                })
                .catch(() => { this.busyId = null; this.showToast('Archive failed.', false); });
        },
        deleteEmail(id, permanent) {
            this.askConfirm({
                title: permanent ? 'Delete permanently?' : 'Move to Trash?',
                message: permanent
                    ? 'This message will be permanently deleted. This cannot be undone.'
                    : 'This message will be moved to Trash.',
                danger: permanent,
                confirmLabel: permanent ? 'Delete permanently' : 'Move to Trash',
                onConfirm: () => this.doDelete(id, permanent),
            });
        },
        doDelete(id, permanent) {
            if (this.busyId) return;
            this.busyId = id;
            fetch(`{{ url('mailbox') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) { this.busyId = null; this.refreshPanel(); this.showToast(data.message || 'Deleted.'); }
                    else { this.busyId = null; this.showToast(data.message || 'Delete failed.', false); }
                })
                .catch(() => { this.busyId = null; this.showToast('Delete failed.', false); });
        },

        confirmOpen: false,
        confirmTitle: '',
        confirmMessage: '',
        confirmDanger: false,
        confirmLabel: 'Confirm',
        confirmCallback: null,
        askConfirm({ title, message, danger = false, confirmLabel = 'Confirm', onConfirm }) {
            this.confirmTitle = title;
            this.confirmMessage = message;
            this.confirmDanger = danger;
            this.confirmLabel = confirmLabel;
            this.confirmCallback = onConfirm;
            this.confirmOpen = true;
        },
        confirmYes() {
            const cb = this.confirmCallback;
            this.confirmOpen = false;
            this.confirmCallback = null;
            if (cb) cb();
        },
        confirmNo() {
            this.confirmOpen = false;
            this.confirmCallback = null;
        },

        composeOpen: false,
        composeSaving: false,
        composeTitle: 'New Message',
        composeDraftId: null,
        // Message whose attachments 'keep_attachment_indexes' carries over
        // (the draft itself when editing, or the original when forwarding) —
        // null for a reply, since replies don't carry the original's attachments.
        composeSourceId: null,
        // Original message's Message-ID header, for reply threading (In-Reply-To/References).
        composeInReplyTo: null,
        composeToList: [],
        composeToDraft: '',
        composeCcList: [],
        composeCcDraft: '',
        showCc: false,
        composeBccList: [],
        composeBccDraft: '',
        showBcc: false,
        composeSubject: '',
        composeAttachments: [], // { name, size, file? (new upload), draftIndex? (carried from original draft) }
        quill: null,

        // Chip-style recipient inputs (To/Cc/Bcc) share this logic. `field` is
        // 'To' | 'Cc' | 'Bcc', matching the composeXList/composeXDraft names.
        parseRecipients(raw) {
            if (!raw) return [];
            return raw.split(/[,;]+/).map(s => s.trim()).filter(Boolean);
        },
        commitRecipient(field) {
            const draftKey = 'compose' + field + 'Draft';
            const listKey = 'compose' + field + 'List';
            this.parseRecipients(this[draftKey]).forEach(email => {
                if (!this[listKey].includes(email)) this[listKey].push(email);
            });
            this[draftKey] = '';
        },
        onRecipientKeydown(e, field) {
            if (e.key === 'Enter' || e.key === ',' || e.key === ';') {
                e.preventDefault();
                this.commitRecipient(field);
            } else if (e.key === 'Backspace' && !this['compose' + field + 'Draft']) {
                this['compose' + field + 'List'].pop();
            }
        },
        handleRecipientPaste(e, field) {
            const text = (e.clipboardData || window.clipboardData).getData('text');
            if (!text || !/[,;]/.test(text)) return;
            e.preventDefault();
            const listKey = 'compose' + field + 'List';
            this.parseRecipients(text).forEach(email => {
                if (!this[listKey].includes(email)) this[listKey].push(email);
            });
        },
        // Collapses a Cc/Bcc field back behind its + Cc / + Bcc toggle and
        // clears whatever was entered, so the toggle accurately reflects
        // nothing set again.
        removeRecipientField(field) {
            this['show' + field] = false;
            this['compose' + field + 'List'] = [];
            this['compose' + field + 'Draft'] = '';
        },

        initQuillIfNeeded() {
            if (this.quill || typeof Quill === 'undefined') return;
            this.quill = new Quill('#composeEditor', {
                theme: 'snow',
                placeholder: 'Write your message...',
                modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] },
            });
        },
        // Shared reset for every compose entry point (new/draft/reply/forward)
        // so none of them can leak state from whatever was open before.
        resetComposeFields() {
            this.composeDraftId = null;
            this.composeSourceId = null;
            this.composeInReplyTo = null;
            this.composeToList = [];
            this.composeToDraft = '';
            this.composeCcList = [];
            this.composeCcDraft = '';
            this.showCc = false;
            this.composeBccList = [];
            this.composeBccDraft = '';
            this.showBcc = false;
            this.composeSubject = '';
            this.composeAttachments = [];
            if (this.quill) this.quill.setText('');
        },
        openComposeNew() {
            this.initQuillIfNeeded();
            this.resetComposeFields();
            this.composeTitle = 'New Message';
            this.composeOpen = true;
        },
        openComposeForDraft(id) {
            this.initQuillIfNeeded();
            this.resetComposeFields();
            this.composeOpen = true;
            this.composeTitle = 'Edit Draft';
            this.composeDraftId = id;
            this.composeSourceId = id;
            fetch(`{{ url('mailbox') }}/${id}/content`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.composeToList = this.parseRecipients(data.to_address || '');
                    this.composeCcList = this.parseRecipients(data.cc_address || '');
                    this.showCc = this.composeCcList.length > 0;
                    this.composeBccList = this.parseRecipients(data.bcc_address || '');
                    this.showBcc = this.composeBccList.length > 0;
                    this.composeSubject = data.subject || '';
                    if (this.quill) {
                        this.quill.root.innerHTML = data.body_html || (data.body_text || '').replace(/\n/g, '<br>');
                    }
                    if (data.has_attachments) {
                        fetch(`{{ url('mailbox') }}/${id}/attachments`, { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(d => {
                                (d.attachments || []).forEach(a => {
                                    this.composeAttachments.push({ name: a.name, size: a.size, draftIndex: a.index });
                                });
                            });
                    }
                });
        },
        // `all` = false is Reply (sender only), true is Reply All (sender +
        // the rest of the original To/Cc, minus the current account itself).
        // Replies never carry the original's attachments (composeSourceId
        // stays null), matching every other mail client's default.
        openComposeReply(id, all) {
            this.initQuillIfNeeded();
            this.resetComposeFields();
            this.composeOpen = true;
            this.composeTitle = all ? 'Reply All' : 'Reply';
            fetch(`{{ url('mailbox') }}/${id}/content`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    const self = (this.accountEmail || '').toLowerCase();
                    this.composeToList = data.from_address ? [data.from_address] : [];
                    if (all) {
                        const rest = this.parseRecipients(data.to_address || '').concat(this.parseRecipients(data.cc_address || ''));
                        rest.forEach(addr => {
                            const already = addr.toLowerCase() === self
                                || this.composeToList.some(a => a.toLowerCase() === addr.toLowerCase());
                            if (!already) this.composeCcList.push(addr);
                        });
                        this.showCc = this.composeCcList.length > 0;
                    }
                    this.composeSubject = /^re:/i.test(data.subject || '') ? data.subject : `Re: ${data.subject || ''}`;
                    this.composeInReplyTo = data.message_id || null;
                    if (this.quill) {
                        this.quill.root.innerHTML = this.quotedMessageHtml(data);
                    }
                });
        },
        openComposeForward(id) {
            this.initQuillIfNeeded();
            this.resetComposeFields();
            this.composeOpen = true;
            this.composeTitle = 'Forward';
            this.composeSourceId = id;
            fetch(`{{ url('mailbox') }}/${id}/content`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.composeSubject = /^fwd:/i.test(data.subject || '') ? data.subject : `Fwd: ${data.subject || ''}`;
                    if (this.quill) {
                        this.quill.root.innerHTML = this.forwardedMessageHtml(data);
                    }
                    if (data.has_attachments) {
                        fetch(`{{ url('mailbox') }}/${id}/attachments`, { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(d => {
                                (d.attachments || []).forEach(a => {
                                    this.composeAttachments.push({ name: a.name, size: a.size, draftIndex: a.index });
                                });
                            });
                    }
                });
        },
        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        },
        quotedMessageHtml(data) {
            const body = data.body_html || `<p>${this.escapeHtml(data.body_text).replace(/\n/g, '<br>')}</p>`;
            const who = data.from_name
                ? `${this.escapeHtml(data.from_name)} &lt;${this.escapeHtml(data.from_address)}&gt;`
                : this.escapeHtml(data.from_address);
            return `<p><br></p><p>On ${this.escapeHtml(data.date)}, ${who} wrote:</p>`
                + `<blockquote style='border-left:2px solid #ccc;margin-left:0;padding-left:12px;color:#666;'>${body}</blockquote>`;
        },
        forwardedMessageHtml(data) {
            const body = data.body_html || `<p>${this.escapeHtml(data.body_text).replace(/\n/g, '<br>')}</p>`;
            const from = data.from_name ? `${data.from_name} <${data.from_address || ''}>` : (data.from_address || '');
            return `<p><br></p><p>---------- Forwarded message ----------<br>`
                + `From: ${this.escapeHtml(from)}<br>`
                + `Date: ${this.escapeHtml(data.date)}<br>`
                + `Subject: ${this.escapeHtml(data.subject)}<br>`
                + `To: ${this.escapeHtml(data.to_address)}</p><p></p>${body}`;
        },
        closeCompose() {
            this.composeOpen = false;
        },
        onAttachmentsPicked(e) {
            Array.from(e.target.files || []).forEach(file => {
                this.composeAttachments.push({ name: file.name, size: file.size, file });
            });
            e.target.value = '';
        },
        removeComposeAttachment(idx) {
            this.composeAttachments.splice(idx, 1);
        },
        submitCompose(action) {
            if (this.composeSaving) return;
            this.commitRecipient('To');
            this.commitRecipient('Cc');
            this.commitRecipient('Bcc');
            this.composeSaving = true;

            const url = action === 'send' ? '{{ route('mailbox.send') }}' : '{{ route('mailbox.save-draft') }}';

            const form = new FormData();
            form.append('to', this.composeToList.join(', '));
            form.append('cc', this.composeCcList.join(', '));
            form.append('bcc', this.composeBccList.join(', '));
            form.append('subject', this.composeSubject);
            form.append('body', this.quill ? this.quill.root.innerHTML : '');
            if (this.composeDraftId) form.append('draft_id', this.composeDraftId);
            if (this.composeSourceId) form.append('source_id', this.composeSourceId);
            if (this.composeInReplyTo) form.append('in_reply_to', this.composeInReplyTo);
            this.composeAttachments.forEach(a => {
                if (a.file) form.append('attachments[]', a.file);
                else if (a.draftIndex !== undefined) form.append('keep_attachment_indexes[]', a.draftIndex);
            });

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: form,
            })
                .then(r => r.json())
                .then(data => {
                    this.composeSaving = false;
                    if (data.success) {
                        this.closeCompose();
                        this.refreshPanel();
                        this.showToast(data.message || 'Saved.');
                    } else {
                        this.showToast(data.message || 'Something went wrong.', false);
                    }
                })
                .catch(() => { this.composeSaving = false; this.showToast('Request failed.', false); });
        },

        settingsOpen: false,
        settingsSaving: false,
        settingsError: null,
        settingsConnected: false,
        settings: {
            email: '', imap_host: '', imap_port: 993, imap_encryption: 'ssl', imap_username: '',
            imap_password: '', smtp_host: '', smtp_port: 465, smtp_encryption: 'ssl',
        },
        // The settings modal is bound 1:1 to the /mailbox/settings URL: open it
        // and the address bar moves there; close it and the URL drops back to
        // wherever it should be. `push` is false when we're reacting to a
        // browser navigation (popstate) that already put us on that URL, so we
        // don't push a redundant duplicate history entry.
        isSettingsUrl(url) {
            return url.pathname === @js(parse_url(route('mailbox.settings'), PHP_URL_PATH));
        },
        openSettings(push = true) {
            this.settingsError = null;
            fetch('{{ route('mailbox.account-settings') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.settingsConnected = data.connected;
                    this.settings.email = data.email;
                    this.settings.imap_host = data.imap_host;
                    this.settings.imap_port = data.imap_port;
                    this.settings.imap_encryption = data.imap_encryption;
                    this.settings.imap_username = data.imap_username;
                    this.settings.imap_password = '';
                    this.settings.smtp_host = data.smtp_host;
                    this.settings.smtp_port = data.smtp_port;
                    this.settings.smtp_encryption = data.smtp_encryption;
                    this.settingsOpen = true;
                    if (push && !this.isSettingsUrl(window.location)) {
                        window.history.pushState({ mailboxSettings: true }, '', '{{ route('mailbox.settings') }}');
                    }
                });
        },
        closeSettings() {
            this.settingsOpen = false;
            if (!this.isSettingsUrl(window.location)) return;
            // history.state carries the { mailboxSettings: true } marker only on
            // an entry we pushed ourselves (the browser preserves it across
            // back/forward), which tells apart opening from a Settings button
            // while already in the app (a real back target exists) from
            // landing here via a real navigation (a bookmark, or the header's
            // link — nothing in-app to go back to).
            if (window.history.state && window.history.state.mailboxSettings) {
                window.history.back();
            } else {
                // Landed here via a real navigation (e.g. the header's Connect
                // now link, or a bookmark) — there's no in-app history to go
                // back to, so just swap the address bar.
                window.history.replaceState({}, '', '{{ route('mailbox.index') }}');
            }
        },
        saveSettings() {
            if (this.settingsSaving) return;
            this.settingsSaving = true;
            this.settingsError = null;
            fetch('{{ route('mailbox.account-settings.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.settings),
            })
                .then(r => r.json())
                .then(data => {
                    this.settingsSaving = false;
                    if (data.success) {
                        // The not-connected screen and the mailbox panel are two
                        // different server-rendered branches (no #mailbox-panel div
                        // exists yet to swap in), so a full navigation is unavoidable
                        // here — but flag it so the landing page can auto-sync right
                        // away instead of showing an empty inbox until a manual click.
                        // Always land on plain /mailbox (not wherever this modal was
                        // opened from, e.g. /mailbox/settings) now that there's an
                        // account to show.
                        try { sessionStorage.setItem('mailbox_just_connected', '1'); } catch (e) {}
                        window.location.href = '{{ route('mailbox.index') }}';
                    } else {
                        this.settingsError = data.message || 'Something went wrong.';
                    }
                })
                .catch(() => { this.settingsSaving = false; this.settingsError = 'Request failed.'; });
        },
        disconnectAccount() {
            this.askConfirm({
                title: 'Disconnect mailbox?',
                message: 'Your saved IMAP/SMTP credentials and cached messages for this mailbox will be removed. You can reconnect any time from Settings.',
                danger: true,
                confirmLabel: 'Disconnect',
                onConfirm: () => this.doDisconnectAccount(),
            });
        },
        doDisconnectAccount() {
            if (this.settingsSaving) return;
            this.settingsSaving = true;
            fetch('{{ route('mailbox.account-settings.disconnect') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    this.settingsSaving = false;
                    if (data.success) {
                        window.location.reload();
                    } else {
                        this.settingsError = data.message || 'Something went wrong.';
                    }
                })
                .catch(() => { this.settingsSaving = false; this.settingsError = 'Request failed.'; });
        },
    }" x-init="
        @if($autoOpenSettings ?? false)
            // We're already on /mailbox/settings (that's how this flag got set),
            // so openSettings() knows not to push a duplicate history entry —
            // closing the modal is what moves the URL, not opening it here.
            openSettings();
        @endif
        const openId = new URLSearchParams(window.location.search).get('open');
        if (openId) {
            openEmail(parseInt(openId));
            const u = new URL(window.location);
            u.searchParams.delete('open');
            window.history.replaceState({}, '', u);
        }
        let justConnected = false;
        try { justConnected = sessionStorage.getItem('mailbox_just_connected') === '1'; } catch (e) {}
        if (justConnected) {
            try { sessionStorage.removeItem('mailbox_just_connected'); } catch (e) {}
            syncNow();
        }
        window.addEventListener('popstate', () => {
            const url = new URL(window.location.href);
            if (this.isSettingsUrl(url)) {
                if (!this.settingsOpen) this.openSettings(false);
                return;
            }
            if (this.settingsOpen) this.settingsOpen = false;
            // #mailbox-panel only exists when an account is connected — a
            // popstate landing back on the not-connected card has nothing to
            // load a panel into.
            if (document.getElementById('mailbox-panel')) {
                this.loadPanel(this.paramsFromUrl(url), { pushState: false });
            }
        });
        // The `mailbox:fetch` scheduler job already pulls new mail from the
        // IMAP server into mailbox_emails every 5 minutes server-side — the
        // panel just needs to re-read that table periodically so new mail
        // shows up without a manual click. Reusing syncNow() here instead
        // would trigger a redundant live IMAP fetch per open tab.
        setInterval(() => {
            if (document.visibilityState !== 'hidden') this.refreshPanel();
        }, 5 * 60 * 1000);
    ">
        @if (!$account)
            <!-- NOT CONNECTED YET -->
            <div class="flex min-h-[70vh] items-center justify-center">
                <div class="mx-auto w-full max-w-md rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                    <div class="text-3xl">📧</div>
                    <h2 class="mt-3 text-base font-semibold text-gray-800 dark:text-gray-100">Connect your mailbox</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                        You haven't connected an email account yet. Add your IMAP/SMTP details to start reading and sending mail here.
                    </p>
                    <button type="button" @click="openSettings()"
                        class="mt-5 inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                        Connect Mailbox
                    </button>
                </div>
            </div>
        @else
            <div id="mailbox-panel" class="min-h-0 flex-1" x-on:click="onPanelClick($event)">
                @include('pages.mailbox._panel')
            </div>
        @endif

        <!-- READ MODAL: closes ONLY via the X or Close button (no backdrop / Escape close) -->
        <div x-show="modalOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

            <div x-show="modalOpen" x-transition
                class="relative flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-[#0f172a] dark:ring-white/10">
                <!-- Header: sender avatar + subject/from/date -->
                <div class="flex items-start justify-between gap-4 px-6 py-5">
                    <template x-if="email">
                        <div class="flex min-w-0 items-start gap-3.5">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white"
                                :class="avatarColor(email.from_name || email.from_address)"
                                x-text="initialsFor(email.from_name || email.from_address)"></span>
                            <div class="min-w-0 pt-0.5">
                                <h3 class="truncate text-base font-semibold leading-snug text-gray-900 dark:text-gray-100" x-text="email.subject || '(no subject)'"></h3>
                                <p class="mt-1 truncate text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium text-gray-800 dark:text-gray-100" x-text="email.from_name || email.from_address"></span>
                                    <template x-if="email.from_name"><span class="text-gray-400" x-text="'<' + email.from_address + '>'"></span></template>
                                </p>
                                <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">
                                    <span x-text="'To: ' + (email.to_address || '')"></span> &middot; <span x-text="email.date"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                    <template x-if="loading">
                        <div class="flex items-center gap-3.5">
                            <span class="h-11 w-11 shrink-0 animate-pulse rounded-full bg-gray-200 dark:bg-white/10"></span>
                            <div class="space-y-2">
                                <span class="block h-3.5 w-48 animate-pulse rounded bg-gray-200 dark:bg-white/10"></span>
                                <span class="block h-3 w-32 animate-pulse rounded bg-gray-200 dark:bg-white/10"></span>
                            </div>
                        </div>
                    </template>
                    <button type="button" @click="closeModal()"
                        class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.06] dark:hover:text-gray-200"
                        title="Close">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Quick action bar -->
                <template x-if="email">
                    <div class="flex flex-wrap items-center gap-1 border-y border-gray-100 bg-gray-50/70 px-4 py-2 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" @click="const id = email.id; closeModal(); openComposeReply(id, false)"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-white hover:text-gray-900 hover:shadow-sm dark:text-gray-300 dark:hover:bg-white/[0.07] dark:hover:text-white">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 17 4 12 9 7" /><path d="M20 18v-2a4 4 0 0 0-4-4H4" />
                            </svg>
                            Reply
                        </button>
                        <button type="button" @click="const id = email.id; closeModal(); openComposeReply(id, true)"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-white hover:text-gray-900 hover:shadow-sm dark:text-gray-300 dark:hover:bg-white/[0.07] dark:hover:text-white">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="7 17 2 12 7 7" /><polyline points="12 17 7 12 12 7" /><path d="M22 18v-2a4 4 0 0 0-4-4H2" />
                            </svg>
                            Reply All
                        </button>
                        <button type="button" @click="const id = email.id; closeModal(); openComposeForward(id)"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-white hover:text-gray-900 hover:shadow-sm dark:text-gray-300 dark:hover:bg-white/[0.07] dark:hover:text-white">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 17 20 12 15 7" /><path d="M4 18v-2a4 4 0 0 1 4-4h12" />
                            </svg>
                            Forward
                        </button>
                        <span class="mx-1 h-4 w-px bg-gray-200 dark:bg-white/10"></span>
                        <template x-if="currentFolder !== 'Archive'">
                            <button type="button" @click="const id = email.id; closeModal(); archiveEmail(id)" title="Archive"
                                class="inline-flex items-center justify-center rounded-full p-2 text-gray-500 transition hover:bg-white hover:text-gray-900 hover:shadow-sm dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-white">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" />
                                </svg>
                            </button>
                        </template>
                        <button type="button" @click="const id = email.id; const perm = currentFolder === 'Trash'; closeModal(); deleteEmail(id, perm)"
                            :title="currentFolder === 'Trash' ? 'Delete permanently' : 'Move to Trash'"
                            class="inline-flex items-center justify-center rounded-full p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z" />
                            </svg>
                        </button>
                    </div>
                </template>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <template x-if="loading">
                        <div class="space-y-3">
                            <span class="block h-3 w-full animate-pulse rounded bg-gray-100 dark:bg-white/[0.06]"></span>
                            <span class="block h-3 w-11/12 animate-pulse rounded bg-gray-100 dark:bg-white/[0.06]"></span>
                            <span class="block h-3 w-4/5 animate-pulse rounded bg-gray-100 dark:bg-white/[0.06]"></span>
                            <span class="block h-40 w-full animate-pulse rounded-lg bg-gray-100 dark:bg-white/[0.06]"></span>
                        </div>
                    </template>
                    <template x-if="!loading && email && email.body_html">
                        <div>
                            <template x-if="imagesBlocked">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-300">
                                    <span>Images in this message are hidden to protect your privacy.</span>
                                    <button type="button" @click="loadImages()"
                                        class="shrink-0 rounded-md bg-amber-600 px-2.5 py-1 font-semibold text-white hover:bg-amber-500">
                                        Load images
                                    </button>
                                </div>
                            </template>
                            <iframe class="h-[52vh] w-full rounded-xl border border-gray-200 bg-white shadow-inner dark:border-white/[0.06]"
                                sandbox="allow-popups allow-popups-to-escape-sandbox"
                                :srcdoc="imagesBlocked ? blockedImagesHtml(email.body_html) : email.body_html"></iframe>
                        </div>
                    </template>
                    <template x-if="!loading && email && !email.body_html">
                        <pre class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700 dark:text-gray-200" x-text="email.body_text"></pre>
                    </template>

                    <template x-if="!loading && email && email.has_attachments">
                        <div class="mt-5 border-t border-gray-100 pt-4 dark:border-white/[0.06]">
                            <p class="mb-2.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.44 11.05 12.25 20.24a5 5 0 0 1-7.07-7.07l9.19-9.19a3.5 3.5 0 0 1 4.95 4.95L9.64 18.36a2 2 0 1 1-2.83-2.83l8.49-8.48" />
                                </svg>
                                <span x-text="emailAttachmentsLoading ? 'Loading attachments…' : (emailAttachments.length + ' attachment' + (emailAttachments.length === 1 ? '' : 's'))"></span>
                            </p>
                            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                <template x-for="att in emailAttachments" :key="att.index">
                                    <a :href="`{{ url('mailbox') }}/${email.id}/attachments/${att.index}`"
                                        class="group flex items-center gap-3 rounded-xl border border-gray-200 p-2.5 transition hover:border-blue-300 hover:bg-blue-50/40 dark:border-white/[0.08] dark:hover:border-blue-400/30 dark:hover:bg-blue-500/[0.06]">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-[10px] font-bold"
                                            :class="attachmentBadge(att.name).classes" x-text="attachmentBadge(att.name).ext"></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200" x-text="att.name"></p>
                                            <p class="text-xs text-gray-400" x-text="formatFileSize(att.size)"></p>
                                        </div>
                                        <svg class="h-4 w-4 shrink-0 text-gray-300 transition group-hover:text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end border-t border-gray-100 px-6 py-3.5 dark:border-white/[0.06]">
                    <button type="button" @click="closeModal()"
                        class="inline-flex h-9 items-center justify-center rounded-lg px-4 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.06]">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- CONFIRM MODAL: closes ONLY via Cancel or the confirm action button -->
        <div x-show="confirmOpen" x-cloak style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50"></div>

            <div x-show="confirmOpen" x-transition
                class="relative w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-xl dark:bg-[#0f172a]">
                <div class="flex items-start gap-3 px-5 pt-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                        :class="confirmDanger ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'">
                        <template x-if="confirmDanger">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z" />
                            </svg>
                        </template>
                        <template x-if="!confirmDanger">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4" />
                            </svg>
                        </template>
                    </span>
                    <div class="min-w-0 pt-1">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100" x-text="confirmTitle"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" x-text="confirmMessage"></p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <button type="button" @click="confirmNo()"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                        Cancel
                    </button>
                    <button type="button" @click="confirmYes()"
                        class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-medium text-white transition"
                        :class="confirmDanger ? 'bg-red-600 hover:bg-red-500' : 'bg-blue-600 hover:bg-blue-500'"
                        x-text="confirmLabel">
                    </button>
                </div>
            </div>
        </div>

        <!-- COMPOSE MODAL: closes ONLY via the X or Close/Discard button -->
        <div x-show="composeOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50"></div>

            <div x-show="composeOpen" x-transition
                class="relative flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100" x-text="composeTitle"></h3>
                    <button type="button" @click="closeCompose()"
                        class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.06] dark:hover:text-gray-200"
                        title="Close">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4">
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">To</label>
                            <div class="flex items-center gap-2">
                                <button type="button" x-show="!showCc" x-cloak
                                    @click="showCc = true; $nextTick(() => $refs.composeCcInput.focus())"
                                    class="rounded-full border border-blue-200 px-2.5 py-1 text-xs font-semibold text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:border-blue-400/30 dark:text-blue-400 dark:hover:border-blue-400/50 dark:hover:bg-blue-500/10">
                                    Add Cc
                                </button>
                                <button type="button" x-show="!showBcc" x-cloak
                                    @click="showBcc = true; $nextTick(() => $refs.composeBccInput.focus())"
                                    class="rounded-full border border-blue-200 px-2.5 py-1 text-xs font-semibold text-blue-600 hover:border-blue-300 hover:bg-blue-50 dark:border-blue-400/30 dark:text-blue-400 dark:hover:border-blue-400/50 dark:hover:bg-blue-500/10">
                                    Add Bcc
                                </button>
                            </div>
                        </div>
                        <div @click="$refs.composeToInput.focus()"
                            class="flex min-h-10 w-full flex-wrap items-center gap-1.5 rounded-lg border border-gray-300 px-2 py-1.5 focus-within:border-blue-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:focus-within:border-blue-500">
                            <template x-for="(addr, idx) in composeToList" :key="idx">
                                <span class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-white/[0.06] dark:text-gray-100">
                                    <span x-text="addr"></span>
                                    <button type="button" @click="composeToList.splice(idx, 1)" class="text-gray-400 hover:text-red-500">✕</button>
                                </span>
                            </template>
                            <input x-ref="composeToInput" type="text" x-model="composeToDraft"
                                @keydown="onRecipientKeydown($event, 'To')" @blur="commitRecipient('To')" @paste="handleRecipientPaste($event, 'To')"
                                :placeholder="composeToList.length ? '' : 'name@example.com, name2@example.com'"
                                class="min-w-[8rem] flex-1 border-0 bg-transparent p-1 text-sm outline-none focus:ring-0 dark:text-gray-100 dark:placeholder-gray-500" />
                        </div>
                    </div>
                    <div x-show="showCc" x-cloak>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Cc</label>
                            <button type="button" @click="removeRecipientField('Cc')"
                                class="text-xs font-medium text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400">
                                Remove
                            </button>
                        </div>
                        <div @click="$refs.composeCcInput.focus()"
                            class="flex min-h-10 w-full flex-wrap items-center gap-1.5 rounded-lg border border-gray-300 px-2 py-1.5 focus-within:border-blue-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:focus-within:border-blue-500">
                            <template x-for="(addr, idx) in composeCcList" :key="idx">
                                <span class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-white/[0.06] dark:text-gray-100">
                                    <span x-text="addr"></span>
                                    <button type="button" @click="composeCcList.splice(idx, 1)" class="text-gray-400 hover:text-red-500">✕</button>
                                </span>
                            </template>
                            <input x-ref="composeCcInput" type="text" x-model="composeCcDraft"
                                @keydown="onRecipientKeydown($event, 'Cc')" @blur="commitRecipient('Cc')" @paste="handleRecipientPaste($event, 'Cc')"
                                :placeholder="composeCcList.length ? '' : 'name@example.com'"
                                class="min-w-[8rem] flex-1 border-0 bg-transparent p-1 text-sm outline-none focus:ring-0 dark:text-gray-100 dark:placeholder-gray-500" />
                        </div>
                    </div>
                    <div x-show="showBcc" x-cloak>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Bcc</label>
                            <button type="button" @click="removeRecipientField('Bcc')"
                                class="text-xs font-medium text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400">
                                Remove
                            </button>
                        </div>
                        <div @click="$refs.composeBccInput.focus()"
                            class="flex min-h-10 w-full flex-wrap items-center gap-1.5 rounded-lg border border-gray-300 px-2 py-1.5 focus-within:border-blue-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:focus-within:border-blue-500">
                            <template x-for="(addr, idx) in composeBccList" :key="idx">
                                <span class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-white/[0.06] dark:text-gray-100">
                                    <span x-text="addr"></span>
                                    <button type="button" @click="composeBccList.splice(idx, 1)" class="text-gray-400 hover:text-red-500">✕</button>
                                </span>
                            </template>
                            <input x-ref="composeBccInput" type="text" x-model="composeBccDraft"
                                @keydown="onRecipientKeydown($event, 'Bcc')" @blur="commitRecipient('Bcc')" @paste="handleRecipientPaste($event, 'Bcc')"
                                :placeholder="composeBccList.length ? '' : 'name@example.com'"
                                class="min-w-[8rem] flex-1 border-0 bg-transparent p-1 text-sm outline-none focus:ring-0 dark:text-gray-100 dark:placeholder-gray-500" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Subject</label>
                        <input type="text" x-model="composeSubject" placeholder="Subject"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Message</label>
                        <div id="composeEditor" style="height: 200px;" class="rounded-lg border border-gray-300 bg-white text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100"></div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Attachments <span class="font-normal text-gray-400">(max 5MB each)</span></label>
                            <button type="button" @click="$refs.composeAttachInput.click()"
                                class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                                📎 Attach files
                            </button>
                            <input type="file" x-ref="composeAttachInput" multiple class="hidden" @change="onAttachmentsPicked($event)" />
                        </div>
                        <div class="flex flex-wrap gap-2" x-show="composeAttachments.length > 0">
                            <template x-for="(att, idx) in composeAttachments" :key="idx">
                                <span class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-2 py-1 text-xs text-gray-700 dark:border-white/[0.08] dark:text-gray-200">
                                    <span class="max-w-[10rem] truncate" x-text="att.name"></span>
                                    <span class="text-gray-400" x-text="formatFileSize(att.size)"></span>
                                    <button type="button" @click="removeComposeAttachment(idx)" class="text-gray-400 hover:text-red-500">✕</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <button type="button" @click="submitCompose('draft')" :disabled="composeSaving"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                        Save Draft
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeCompose()"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                            Close
                        </button>
                        <button type="button" @click="submitCompose('send')" :disabled="composeSaving"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500 disabled:opacity-50">
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SETTINGS MODAL: closes ONLY via the X or Close button -->
        <div x-show="settingsOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50"></div>

            <div x-show="settingsOpen" x-transition
                class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Mailbox Settings</h3>
                    <button type="button" @click="closeSettings()"
                        class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.06] dark:hover:text-gray-200"
                        title="Close">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4">
                    <template x-if="settingsError">
                        <div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-400" x-text="settingsError"></div>
                    </template>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Your email address</label>
                        <input type="email" x-model="settings.email" placeholder="you@pakuwon.com"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                    </div>

                    <p class="pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Incoming (IMAP)</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Host</label>
                            <input type="text" x-model="settings.imap_host" disabled placeholder="mail3.pakuwon.com"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100 dark:disabled:bg-white/[0.03] dark:disabled:text-gray-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Port</label>
                            <input type="number" x-model="settings.imap_port" disabled
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100 dark:disabled:bg-white/[0.03] dark:disabled:text-gray-500" />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Mailbox username</label>
                            <input type="text" x-model="settings.imap_username" placeholder="you@pakuwon.com"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Encryption</label>
                            <select x-model="settings.imap_encryption" disabled
                                class="h-10 w-full rounded-lg border border-gray-300 px-2 text-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100 dark:disabled:bg-white/[0.03] dark:disabled:text-gray-500">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="starttls">STARTTLS</option>
                            </select>
                        </div>
                    </div>
                    <div x-data="{ showPassword: false }">
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Mailbox password <span class="font-normal normal-case text-gray-400">(leave blank to keep the current one)</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="settings.imap_password" placeholder="••••••••" autocomplete="new-password"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 pr-10 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                            <button type="button" @click="showPassword = !showPassword" tabindex="-1"
                                class="absolute right-0 top-0 flex h-10 w-10 items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg x-show="!showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.62 21.62 0 0 1 5.06-6.44M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a21.6 21.6 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                                    <path d="M1 1l22 22" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <p class="pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Outgoing (SMTP)</p>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Host</label>
                            <input type="text" x-model="settings.smtp_host" disabled placeholder="mx5.pakuwon.com"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100 dark:disabled:bg-white/[0.03] dark:disabled:text-gray-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Port</label>
                            <input type="number" x-model="settings.smtp_port" disabled
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100 dark:disabled:bg-white/[0.03] dark:disabled:text-gray-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Encryption</label>
                            <select x-model="settings.smtp_encryption" disabled
                                class="h-10 w-full rounded-lg border border-gray-300 px-2 text-sm disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100 dark:disabled:bg-white/[0.03] dark:disabled:text-gray-500">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="starttls">STARTTLS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <button type="button" x-show="settingsConnected" @click="disconnectAccount()" :disabled="settingsSaving"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50 dark:border-red-400/30 dark:text-red-400 dark:hover:bg-red-500/10">
                        Disconnect
                    </button>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" @click="closeSettings()"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                            Close
                        </button>
                        <button type="button" @click="saveSettings()" :disabled="settingsSaving"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500 disabled:opacity-50">
                            <span x-text="settingsSaving ? 'Testing connection…' : 'Save & Connect'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOAST -->
        <div x-show="toast" x-cloak x-transition style="display: none;"
            class="fixed bottom-4 right-4 z-[60] rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"
            :class="toastOk ? 'bg-green-600' : 'bg-red-600'"
            x-text="toast">
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>

    <style>
        /* Quill's Snow theme is hard-coded for light mode, so it needs
           explicit overrides to match the compose modal in dark mode. */
        .dark .ql-toolbar.ql-snow {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
        }

        .dark .ql-container.ql-snow {
            border-color: rgba(255, 255, 255, 0.08);
        }

        .dark .ql-editor {
            color: #f1f5f9;
        }

        .dark .ql-editor.ql-blank::before {
            color: #64748b;
        }

        .dark .ql-snow .ql-stroke {
            stroke: #94a3b8;
        }

        .dark .ql-snow .ql-fill,
        .dark .ql-snow .ql-stroke.ql-fill {
            fill: #94a3b8;
        }

        .dark .ql-snow .ql-picker {
            color: #94a3b8;
        }

        .dark .ql-toolbar.ql-snow button:hover,
        .dark .ql-toolbar.ql-snow button:focus,
        .dark .ql-toolbar.ql-snow button.ql-active {
            color: #f1f5f9;
        }

        .dark .ql-toolbar.ql-snow button:hover .ql-stroke,
        .dark .ql-toolbar.ql-snow button:focus .ql-stroke,
        .dark .ql-toolbar.ql-snow button.ql-active .ql-stroke {
            stroke: #f1f5f9;
        }

        .dark .ql-toolbar.ql-snow button:hover .ql-fill,
        .dark .ql-toolbar.ql-snow button:focus .ql-fill,
        .dark .ql-toolbar.ql-snow button.ql-active .ql-fill {
            fill: #f1f5f9;
        }

        .dark .ql-snow .ql-tooltip {
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.08);
            color: #f1f5f9;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .dark .ql-snow .ql-tooltip input[type="text"] {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: #f1f5f9;
        }

        .dark .ql-snow .ql-tooltip a.ql-action,
        .dark .ql-snow .ql-tooltip a.ql-remove {
            color: #60a5fa;
        }
    </style>
</x-app-layout>
