<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex flex-col gap-3 lg:flex-row">

            <!-- MASTER LIST -->
            <aside class="shrink-0 lg:w-64">
                <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a] lg:sticky lg:top-2">
                    <div id="stNav" class="max-h-[78vh] space-y-3 overflow-y-auto p-1"></div>
                </div>
            </aside>

            <!-- CONTENT -->
            <section class="min-w-0 flex-1">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                        <div class="flex min-w-0 items-center gap-3">
                            <span id="stIcon" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300"></span>
                            <div class="min-w-0">
                                <h2 id="stTitle" class="truncate text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100"></h2>
                                <p id="stDesc" class="text-xs text-gray-400 dark:text-gray-500"></p>
                            </div>
                        </div>
                        <button id="stAddBtn" type="button"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                            + Add
                        </button>
                    </div>
                    <div id="stTableWrap" class="relative overflow-hidden p-2"></div>
                </div>
            </section>
        </div>

        <!-- ADD / VIEW / EDIT MODAL -->
        <div id="stModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[92vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="relative flex items-center justify-between gap-4 overflow-hidden border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5 text-white dark:border-white/[0.06]">
                    <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3.5">
                        <span id="stModalIcon" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/20 text-lg ring-1 ring-white/30"></span>
                        <div>
                            <h2 id="stModalTitle" class="text-base font-semibold leading-tight"></h2>
                            <p id="stModalSub" class="text-xs text-indigo-100"></p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white"
                        data-modal="stModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="stForm" class="flex flex-1 flex-col overflow-hidden" autocomplete="off">
                    <input type="hidden" id="st_id">
                    <div id="stFields" class="grid flex-1 grid-cols-1 gap-4 overflow-y-auto overflow-x-hidden px-6 py-6 md:grid-cols-2"></div>
                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <p class="hidden text-xs text-gray-400 sm:block"><span class="text-red-500">*</span> Required</p>
                        <div class="ml-auto flex gap-2">
                            <button type="button" class="tsModalClose rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                                data-modal="stModal">Cancel</button>
                            <button type="submit" id="stSaveBtn"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-4 focus:ring-indigo-500/30"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
                <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
            </div>
        </div>
    </div>

    <style>
        .st-input { width:100%; border-radius:0.75rem; border:1px solid rgb(229 231 235); background:rgb(249 250 251 / .6); padding:0.65rem 0.875rem; font-size:0.875rem; color:rgb(17 24 39); box-shadow:0 1px 2px rgb(0 0 0 / .04); transition:all .15s; }
        .st-input::placeholder { color:rgb(156 163 175); }
        .st-input:hover { border-color:rgb(165 180 252); background:#fff; }
        .st-input:focus { outline:none; border-color:rgb(86 177 243); background:#fff; box-shadow:0 0 0 4px rgb(86 177 243 / .15); }
        .st-input:disabled { background:rgb(243 244 246); color:rgb(107 114 128); cursor:not-allowed; }
        .dark .st-input { background:rgb(255 255 255 / .05); border-color:rgb(255 255 255 / .1); color:#fff; }
        .dark .st-input:focus { background:rgb(255 255 255 / .08); }
        textarea.st-input { min-height: 6rem; resize: vertical; }
        #stTableWrap td { white-space: normal; max-width: 360px; text-align: left; justify-items: start; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const ST_DEFS = @json($defs);
        const ST_BASE = "{{ url('tenancy/settings') }}";
        const ST_CSRF = "{{ csrf_token() }}";

        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }
        function toastError(text) { Swal.fire({ icon: 'error', title: 'Error', text: text || 'Something went wrong' }); }
        function toastSuccess(text) { Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1500, showConfirmButton: false }); }
        function statusBadge(data) {
            return data === 'A' ?
                '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
        }
        function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }

        function tsOpenModal(id) {
            let $m = $('#' + id);
            $m.removeClass('hidden').addClass('flex');
            requestAnimationFrame(function() { requestAnimationFrame(function() {
                $m.removeClass('opacity-0').addClass('opacity-100');
                $m.find('.ts-modal-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            }); });
        }
        function tsCloseModal(id) {
            let $m = $('#' + id);
            $m.removeClass('opacity-100').addClass('opacity-0');
            $m.find('.ts-modal-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(function() { $m.addClass('hidden').removeClass('flex'); }, 200);
        }

        $(document).ready(function() {
            let LK = { sites: [], doctypes: [], departments: [], suggest: {} };
            let siteName = {};
            let cur = null, dt = null;

            $(document).on('click', '.tsModalClose', function() { tsCloseModal($(this).data('modal')); });
            $(document).on('click', '.tsModalBackdrop', function(e) { if (e.target === this) tsCloseModal(this.id); });

            /* ---------- left navigation ---------- */
            function buildNav() {
                let groups = {};
                ST_DEFS.forEach(d => (groups[d.group] = groups[d.group] || []).push(d));
                let html = '';
                Object.keys(groups).forEach(function(g) {
                    html += `<div><p class="mb-1 px-2 text-[11px] font-bold uppercase tracking-wider text-gray-400">${esc(g)}</p><ul class="space-y-0.5">`;
                    groups[g].forEach(function(d) {
                        html += `<li><button type="button" data-key="${d.key}" class="stNavBtn flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10">
                            <i class="fas ${d.icon} w-4 text-center text-xs"></i><span class="truncate">${esc(d.label)}</span></button></li>`;
                    });
                    html += '</ul></div>';
                });
                $('#stNav').html(html);
            }

            function cellFor(f) {
                return function(d) {
                    if (d === null || d === undefined || d === '') return '<span class="text-gray-400">-</span>';
                    if (f.type === 'site') return esc(siteName[d] ? siteName[d] + ' (' + d + ')' : d);
                    if (f.type === 'time') return esc(String(d).substring(0, 5));
                    let s = String(d);
                    return s.length > 90 ? `<span title="${esc(s)}">${esc(s.substring(0, 90))}…</span>` : esc(s);
                };
            }

            function selectMaster(key) {
                let def = ST_DEFS.find(d => d.key === key) || ST_DEFS[0];
                cur = def;
                try { history.replaceState(null, '', '#' + def.key); } catch (e) {}

                $('.stNavBtn').removeClass('bg-indigo-600 text-white shadow-sm hover:bg-indigo-600').addClass('text-gray-600');
                $(`.stNavBtn[data-key="${def.key}"]`).addClass('bg-indigo-600 text-white shadow-sm hover:bg-indigo-600').removeClass('text-gray-600');
                $('#stIcon').html(`<i class="fas ${def.icon}"></i>`);
                $('#stTitle').text(def.label);
                $('#stDesc').text(def.desc);
                $('#stAddBtn').text('+ Add ' + def.label);

                if (dt) { dt.destroy(); dt = null; }
                let listFields = def.fields.filter(f => f.list);
                let head = '<th class="w-32 px-4 py-3 text-left font-medium">Actions</th>' +
                    listFields.map(f => `<th class="px-4 py-3 text-left font-medium">${esc(f.label)}</th>`).join('') +
                    '<th class="w-28 px-4 py-3 text-left font-medium">Status</th>';
                $('#stTableWrap').html(`<table id="stTable" class="w-full min-w-full border-separate border-spacing-0 text-sm"><thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">${head}</tr></thead><tbody></tbody></table>`);

                let columns = [{
                    data: 'id', orderable: false, searchable: false,
                    render: function(id, t, row) {
                        return `<div class="flex justify-center items-center space-x-2">
                            <label class="switch"><input type="checkbox" class="stToggle" data-id="${id}" ${row.status === 'A' ? 'checked' : ''}><span class="slider round"></span></label>
                            <button type="button" class="stView bg-gray-500 text-white px-2 py-1 rounded" data-id="${id}"><i class="fas fa-eye"></i></button>
                            <button type="button" class="stEdit bg-blue-500 text-white px-2 py-1 rounded" data-id="${id}"><i class="fas fa-edit"></i></button></div>`;
                    }
                }].concat(listFields.map(f => ({ data: f.name, render: cellFor(f), defaultContent: '-' })))
                  .concat([{ data: 'status', render: statusBadge }]);

                dt = $('#stTable').DataTable({
                    ajax: `${ST_BASE}/${def.key}/json`,
                    processing: true, serverSide: false, order: [],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                    columns: columns,
                });
            }

            /* ---------- form ---------- */
            function fieldHtml(f) {
                let id = 'sf_' + f.name, req = f.required ? 'required' : '';
                let label = `<label for="${id}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">${esc(f.label)}${f.required ? ' <span class="text-red-500">*</span>' : ''}</label>`;
                let ctl;
                if (f.type === 'textarea') {
                    ctl = `<textarea id="${id}" name="${f.name}" class="st-input" ${f.max ? 'maxlength="' + f.max + '"' : ''} placeholder="${esc(f.placeholder || '')}" ${req}></textarea>`;
                } else if (f.type === 'site' || f.type === 'doctype' || f.type === 'department') {
                    ctl = `<select id="${id}" name="${f.name}" class="st-input bg-none" ${req}><option value="">-- Select ${esc(f.label)} --</option>${optionsFor(f)}</select>`;
                } else {
                    let type = f.type === 'time' ? 'time' : (f.type === 'integer' ? 'number' : (f.type === 'number' ? 'number' : 'text'));
                    let extra = f.type === 'number' ? 'step="any"' : (f.type === 'integer' ? 'step="1"' : (f.type === 'time' ? 'step="60"' : ''));
                    let sug = f.suggest ? `list="dl_${cur.key}_${f.name}"` : '';
                    let dl = f.suggest ? `<datalist id="dl_${cur.key}_${f.name}">${((LK.suggest[cur.key] || {})[f.name] || []).map(v => `<option value="${esc(v)}">`).join('')}</datalist>` : '';
                    ctl = `<input type="${type}" id="${id}" name="${f.name}" class="st-input" ${extra} ${sug} ${f.max ? 'maxlength="' + f.max + '"' : ''} placeholder="${esc(f.placeholder || '')}" ${req}>${dl}`;
                }
                let span = (f.type === 'textarea' || f.name === 'username') ? 'md:col-span-2' : '';
                return `<div class="${span}">${label}${ctl}</div>`;
            }

            function optionsFor(f) {
                if (f.type === 'site') return LK.sites.map(s => `<option value="${esc(s.siteid)}">${esc(s.sitename)} (${esc(s.siteid)})</option>`).join('');
                if (f.type === 'doctype') return LK.doctypes.map(d => `<option value="${esc(d.doctype)}">${esc(d.doctype)} - ${esc(d.documentname)}</option>`).join('');
                return LK.departments.map(d => `<option value="${esc(d)}">${esc(d)}</option>`).join('');
            }

            function openModal(mode, row) {
                let ro = mode === 'view';
                $('#stFields').html(cur.fields.map(fieldHtml).join(''));
                $('#st_id').val('');
                $('#stModalIcon').html(`<i class="fas ${cur.icon}"></i>`);
                $('#stModalTitle').text((mode === 'add' ? 'Add ' : (ro ? 'View ' : 'Edit ')) + cur.label);
                $('#stModalSub').text(cur.desc);

                if (row) {
                    $('#st_id').val(row.id);
                    cur.fields.forEach(function(f) {
                        let v = row[f.name];
                        if (v === null || v === undefined) v = '';
                        if (f.type === 'time') v = String(v).substring(0, 5);
                        let $el = $('#sf_' + f.name);
                        // keep a value that is no longer in the dropdown (inactive / removed)
                        if ($el.is('select') && v !== '' && !$el.find('option[value="' + String(v).replace(/"/g, '\\"') + '"]').length) {
                            $el.append(`<option value="${esc(v)}">${esc(v)} (not listed)</option>`);
                        }
                        $el.val(v);
                    });
                }
                $('#stForm input, #stForm select, #stForm textarea').not('#st_id').prop('disabled', ro);
                $('#stSaveBtn').toggle(!ro);
                tsOpenModal('stModal');
            }

            $('#stAddBtn').on('click', function() { openModal('add', null); });

            $(document).on('click', '.stView, .stEdit', function() {
                let mode = $(this).hasClass('stView') ? 'view' : 'edit';
                showLoading();
                $.get(`${ST_BASE}/${cur.key}/${$(this).data('id')}/edit`, function(row) {
                    hideLoading();
                    openModal(mode, row);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Failed to load the record');
                    console.error(xhr.responseText);
                });
            });

            $('#stForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#st_id').val();
                let fd = new FormData();
                cur.fields.forEach(f => fd.append(f.name, $('#sf_' + f.name).val() ?? ''));
                if (id) fd.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: id ? `${ST_BASE}/${cur.key}/${id}` : `${ST_BASE}/${cur.key}`,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': ST_CSRF },
                    data: fd, processData: false, contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('stModal');
                        dt.ajax.reload(null, false);
                        toastSuccess(cur.label + ' saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        let errs = xhr.responseJSON && xhr.responseJSON.errors;
                        let first = errs ? Object.values(errs)[0][0] : null;
                        toastError(first || (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to save');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.stToggle', function() {
                let $c = $(this), id = $c.data('id');
                $.ajax({
                    url: `${ST_BASE}/${cur.key}/${id}/toggle-status`, type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': ST_CSRF },
                    data: { status: $c.is(':checked') ? 'A' : 'X' },
                    success: function() { dt.ajax.reload(null, false); },
                    error: function() { toastError('Failed to update status'); dt.ajax.reload(null, false); }
                });
            });

            $(document).on('click', '.stNavBtn', function() { selectMaster($(this).data('key')); });

            /* ---------- boot ---------- */
            buildNav();
            $.get(`${ST_BASE}/lookups`, function(res) {
                LK = res;
                (LK.sites || []).forEach(s => siteName[s.siteid] = s.sitename);
            }).always(function() {
                selectMaster((location.hash || '').replace('#', '') || ST_DEFS[0].key);
            });
        });
    </script>
</x-app-layout>
