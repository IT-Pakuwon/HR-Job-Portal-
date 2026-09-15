<x-app-layout>
    <div class="mx-auto flex h-[calc(100dvh-72px)] w-full max-w-9xl flex-col overflow-hidden p-2" x-data="{
        currentFolder: @js($folder),
        modalOpen: false,
        loading: false,
        email: null,
        busyId: null,
        toast: null,
        toastOk: true,
        syncing: false,

        showToast(message, ok = true) {
            this.toast = message;
            this.toastOk = ok;
            setTimeout(() => { this.toast = null }, 4000);
        },

        // Intercept clicks on plain <a> links inside the mailbox panel (folder
        // switches, pagination) so they load via AJAX instead of a full reload.
        onPanelClick(e) {
            const a = e.target.closest('a[href]');
            if (!a) return;
            const url = new URL(a.getAttribute('href'), window.location.origin);
            if (url.pathname !== @js(parse_url(url('mailbox'), PHP_URL_PATH))) return;
            e.preventDefault();
            this.loadPanel(Object.fromEntries(url.searchParams.entries()));
        },
        loadPanel(params = {}) {
            const qs = new URLSearchParams(params).toString();
            fetch('{{ route('mailbox.panel') }}' + (qs ? '?' + qs : ''), { headers: { 'Accept': 'text/html' } })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('mailbox-panel').innerHTML = html;
                    const pageUrl = '{{ route('mailbox.index') }}' + (qs ? '?' + qs : '');
                    window.history.pushState({}, '', pageUrl);
                    if (params.folder) this.currentFolder = params.folder;
                })
                .catch(() => this.showToast('Failed to load mailbox.', false));
        },
        refreshPanel() {
            const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());
            const qs = new URLSearchParams(params).toString();
            fetch('{{ route('mailbox.panel') }}' + (qs ? '?' + qs : ''), { headers: { 'Accept': 'text/html' } })
                .then(r => r.text())
                .then(html => { document.getElementById('mailbox-panel').innerHTML = html; })
                .catch(() => this.showToast('Failed to refresh mailbox.', false));
        },
        syncNow() {
            if (this.syncing) return;
            this.syncing = true;
            fetch('{{ route('mailbox.sync') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    this.syncing = false;
                    this.showToast(data.message || (data.success ? 'Synced.' : 'Sync failed.'), data.success !== false);
                    if (data.success) this.refreshPanel();
                })
                .catch(() => { this.syncing = false; this.showToast('Sync failed.', false); });
        },

        emailAttachments: [],
        emailAttachmentsLoading: false,
        openEmail(id) {
            if (this.currentFolder === 'Drafts') {
                this.openComposeForDraft(id);
                return;
            }
            this.modalOpen = true;
            this.loading = true;
            this.email = null;
            this.emailAttachments = [];
            fetch(`{{ url('mailbox') }}/${id}/content`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.email = data;
                    this.loading = false;
                    if (data.has_attachments) this.loadEmailAttachments(id);
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
        closeModal() {
            this.modalOpen = false;
            this.email = null;
            this.emailAttachments = [];
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
        composeDraftId: null,
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
        openComposeNew() {
            this.initQuillIfNeeded();
            this.composeDraftId = null;
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
            this.composeOpen = true;
        },
        openComposeForDraft(id) {
            this.initQuillIfNeeded();
            this.composeOpen = true;
            this.composeDraftId = id;
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
            fetch(`{{ url('mailbox') }}/${id}/content`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.composeToList = this.parseRecipients(data.to_address || '');
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
        settings: {
            email: '', imap_host: '', imap_port: 993, imap_encryption: 'ssl', imap_username: '',
            imap_password: '', smtp_host: '', smtp_port: 465, smtp_encryption: 'ssl',
        },
        openSettings() {
            this.settingsError = null;
            fetch('{{ route('mailbox.account-settings') }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
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
                });
        },
        closeSettings() {
            this.settingsOpen = false;
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
                        window.location.reload();
                    } else {
                        this.settingsError = data.message || 'Something went wrong.';
                    }
                })
                .catch(() => { this.settingsSaving = false; this.settingsError = 'Request failed.'; });
        },
    }" x-init="
        const openId = new URLSearchParams(window.location.search).get('open');
        if (openId) {
            openEmail(parseInt(openId));
            const u = new URL(window.location);
            u.searchParams.delete('open');
            window.history.replaceState({}, '', u);
        }
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
            <div class="absolute inset-0 bg-black/50"></div>

            <div x-show="modalOpen" x-transition
                class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl dark:bg-[#0f172a]">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <div class="min-w-0">
                        <template x-if="email">
                            <div>
                                <h3 class="truncate text-base font-semibold text-gray-800 dark:text-gray-100" x-text="email.subject"></h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                                    From: <span class="font-medium text-gray-700 dark:text-gray-200" x-text="email.from_name || email.from_address"></span>
                                    <template x-if="email.from_name"><span x-text="'<' + email.from_address + '>'"></span></template>
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    <span x-text="'To: ' + (email.to_address || '')"></span> &middot; <span x-text="email.date"></span>
                                </p>
                            </div>
                        </template>
                        <template x-if="loading">
                            <p class="text-sm text-gray-400">Loading...</p>
                        </template>
                    </div>
                    <button type="button" @click="closeModal()"
                        class="shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.06] dark:hover:text-gray-200"
                        title="Close">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-5">
                    <template x-if="loading">
                        <p class="text-sm text-gray-400">Loading email...</p>
                    </template>
                    <template x-if="!loading && email && email.body_html">
                        <iframe class="h-[55vh] w-full rounded-lg border border-gray-200 bg-white dark:border-white/[0.06]"
                            sandbox="" :srcdoc="email.body_html"></iframe>
                    </template>
                    <template x-if="!loading && email && !email.body_html">
                        <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200" x-text="email.body_text"></pre>
                    </template>

                    <template x-if="!loading && email && email.has_attachments">
                        <div class="mt-4 border-t border-gray-100 pt-4 dark:border-white/[0.06]">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                <span x-text="emailAttachmentsLoading ? 'Loading attachments…' : (emailAttachments.length + ' attachment' + (emailAttachments.length === 1 ? '' : 's'))"></span>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="att in emailAttachments" :key="att.index">
                                    <a :href="`{{ url('mailbox') }}/${email.id}/attachments/${att.index}`"
                                        class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21.44 11.05 12.25 20.24a5 5 0 0 1-7.07-7.07l9.19-9.19a3.5 3.5 0 0 1 4.95 4.95L9.64 18.36a2 2 0 1 1-2.83-2.83l8.49-8.48" />
                                        </svg>
                                        <span x-text="att.name"></span>
                                        <span class="text-gray-400" x-text="formatFileSize(att.size)"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2">
                        <template x-if="email && currentFolder !== 'Archive'">
                            <button type="button" @click="const id = email.id; closeModal(); archiveEmail(id)"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
                                Archive
                            </button>
                        </template>
                        <template x-if="email">
                            <button type="button" @click="const id = email.id; const perm = currentFolder === 'Trash'; closeModal(); deleteEmail(id, perm)"
                                class="inline-flex h-10 items-center justify-center rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-500/30 dark:hover:bg-red-500/10">
                                <span x-text="currentFolder === 'Trash' ? 'Delete permanently' : 'Move to Trash'"></span>
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="closeModal()"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/[0.08] dark:text-gray-200 dark:hover:bg-white/[0.04]">
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
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100" x-text="composeDraftId ? 'Edit Draft' : 'New Message'"></h3>
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
                            <input type="text" x-model="settings.imap_host" placeholder="mail3.pakuwon.com"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Port</label>
                            <input type="number" x-model="settings.imap_port"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
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
                            <select x-model="settings.imap_encryption"
                                class="h-10 w-full rounded-lg border border-gray-300 px-2 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="starttls">STARTTLS</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Mailbox password <span class="font-normal normal-case text-gray-400">(leave blank to keep the current one)</span>
                        </label>
                        <input type="password" x-model="settings.imap_password" placeholder="••••••••"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                    </div>

                    <p class="pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Outgoing (SMTP)</p>
                    <div class="grid grid-cols-4 gap-2">
                        <div class="col-span-2">
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Host</label>
                            <input type="text" x-model="settings.smtp_host" placeholder="mx5.pakuwon.com"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Port</label>
                            <input type="number" x-model="settings.smtp_port"
                                class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Encryption</label>
                            <select x-model="settings.smtp_encryption"
                                class="h-10 w-full rounded-lg border border-gray-300 px-2 text-sm dark:border-white/[0.08] dark:bg-white/[0.02] dark:text-gray-100">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="starttls">STARTTLS</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4 dark:border-white/[0.06]">
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
