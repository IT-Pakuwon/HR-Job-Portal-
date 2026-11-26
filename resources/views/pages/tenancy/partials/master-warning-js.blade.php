/* =========================================================
 * TENANT WARNING (Tenant Relation / Finance)
 * ========================================================= */
const esc = s => $('<div>').text(s ?? '').html();
const docsText = r => {
    let simkb = [r.allow_simkb_in == 1 && 'Masuk', r.allow_simkb_out == 1 && 'Keluar', r.allow_simkb_inout == 1 && 'Masuk & Keluar'].filter(Boolean);
    return `SIK: ${r.allow_sik == 1 ? '✓' : '✗'} &nbsp;·&nbsp; SIA: ${r.allow_sia == 1 ? '✓' : '✗'} &nbsp;·&nbsp; SIMKB: ${simkb.length ? esc(simkb.join(', ')) : '✗'}`;
};

let warningTable = $('#tenantWarningsTable').DataTable({
    ajax: "{{ route('tenancy.master.tenant-warnings.json') }}",
    processing: true,
    serverSide: false,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    order: [],
    dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
    columns: [
        { data: 'id', orderable: false, searchable: false,
          render: id => `<div class="flex justify-center"><button type="button" class="manageWarningBtn ${window.listCls.action.amber}" data-id="${id}" title="Warning & documents"><i class="fas fa-exclamation-triangle text-[10px]"></i> Manage</button></div>` },
        { data: 'location.locationname', defaultContent: '-' },
        { data: 'floor.floor', defaultContent: '-' },
        { data: 'tenant_company.tenantcompanyname', defaultContent: '-' },
        { data: 'storename' },
        { data: 'unit', render: d => d || '-' },
        { data: 'is_warned', render: d => d == 1
            ? window.listBadge('red', 'Warned')
            : '<span class="text-gray-400">-</span>' },
        { data: 'warning_reason', render: (d, t, r) => r.is_warned == 1 && d
            ? `<span class="block max-w-[18rem] truncate text-left" title="${esc(d)}">${esc(d)}</span>`
            : '<span class="block text-left text-gray-400">-</span>' },
        { data: 'allow_sik', render: (d, t, r) => docsText(r) },
    ]
});
window.tsTables.warning = warningTable;

$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable.id !== 'tenantWarningsTable') return true;
    let row = warningTable.row(dataIndex).data() || {};
    let f = $('#filterWarnState').val(), site = $('#filterWarnSite').val(), floor = $('#filterWarnFloor').val();
    if (f !== '' && String(row.is_warned) !== f) return false;
    if (site && row.siteid !== site) return false;
    if (floor && (row.floor && row.floor.floor) !== floor) return false;
    return true;
});
$('#filterWarnState, #filterWarnSite, #filterWarnFloor').on('change', function() { warningTable.draw(); });

// Floor filter lists the floors found in the loaded rows (admin only; the select doesn't exist otherwise)
warningTable.on('xhr.dt', function(e, settings, json) {
    let $f = $('#filterWarnFloor');
    if (!$f.length) return;
    let cur = $f.val();
    let floors = [...new Set(((json && json.data) || []).map(r => r.floor && r.floor.floor).filter(Boolean))]
        .sort((a, b) => String(a).localeCompare(String(b), undefined, {numeric: true}));
    $f.html('<option value="">All Floors</option>' + floors.map(x => `<option value="${esc(x)}">${esc(x)}</option>`).join('')).val(floors.includes(cur) ? cur : '');
});

function renderWarning(d) {
    $('#warn_tenant_id').val(d.id);
    $('#warnTitle').text(d.storename || 'Tenant');
    $('#warnSub').text([d.company, d.place].filter(Boolean).join(' · ') || 'Warning & allowed documents');
    $('#warnActive').toggleClass('hidden', !d.is_warned);
    $('#warnNone').toggleClass('hidden', !!d.is_warned);
    $('#warnActiveReason').text(d.warning_reason || '');
    $('#warnReason, #warnClearReason').val('');
    $('#doc_sik').prop('checked', d.allow_sik == 1);
    $('#doc_sia').prop('checked', d.allow_sia == 1);
    $('#doc_simkb_in').prop('checked', d.allow_simkb_in == 1);
    $('#doc_simkb_out').prop('checked', d.allow_simkb_out == 1);
    $('#doc_simkb_inout').prop('checked', d.allow_simkb_inout == 1);
    $('#doc_sik, #doc_sia, #doc_simkb_in, #doc_simkb_out, #doc_simkb_inout').prop('disabled', !d.can_documents);
    $('#docSaveBtn').toggle(!!d.can_documents);
    $('#docReadOnly').toggleClass('hidden', !!d.can_documents);
    $('#warnHistory').html((d.history || []).map(h => `
        <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <div class="flex items-center justify-between gap-2">
                <span class="font-medium ${h.status === 'A' ? 'text-red-600' : 'text-gray-500'}">${h.status === 'A' ? 'Active' : 'Cleared'}</span>
                <span class="text-xs text-gray-400">${esc(h.issued_user)} · ${esc(h.issued_datetime)}</span>
            </div>
            <p class="mt-1 whitespace-pre-line text-gray-700 dark:text-gray-300">${esc(h.reason)}</p>
            ${h.status === 'C' ? `<p class="mt-2 border-t border-gray-100 pt-2 text-xs text-gray-500 dark:border-white/10">Cleared by ${esc(h.cleared_user)} · ${esc(h.cleared_datetime)}: ${esc(h.clear_reason)}</p>` : ''}
        </div>`).join('') || '<p class="text-gray-400">No warning history.</p>');
}

function loadWarning(id, open) {
    showLoading();
    $.get(`/tenancy/master/tenant-warnings/${id}`, function(d) {
        hideLoading();
        renderWarning(d);
        if (open) tsOpenModal('warningModal');
    }).fail(function() { hideLoading(); toastError('Gagal mengambil data tenant'); });
}

$(document).on('click', '.manageWarningBtn', function() { loadWarning($(this).data('id'), true); });

function warningPost(url, type, data, okMsg) {
    showLoading();
    $.ajax({
        url: url, type: type, data: data,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function() {
            hideLoading();
            toastSuccess(okMsg);
            warningTable.ajax.reload(null, false);
            loadWarning($('#warn_tenant_id').val(), false);
        },
        error: function(xhr) {
            hideLoading();
            let errs = xhr.responseJSON?.errors;
            toastError((errs && Object.values(errs)[0][0]) || xhr.responseJSON?.message || 'Gagal menyimpan');
        }
    });
}

$('#warnSetBtn').on('click', function() {
    let reason = $('#warnReason').val().trim();
    if (!reason) { toastError('Reason is required'); return; }
    warningPost(`/tenancy/master/tenant-warnings/${$('#warn_tenant_id').val()}/warn`, 'POST', { reason }, 'Warning set');
});
$('#warnClearBtn').on('click', function() {
    let clear_reason = $('#warnClearReason').val().trim();
    if (!clear_reason) { toastError('Reason to clear is required'); return; }
    warningPost(`/tenancy/master/tenant-warnings/${$('#warn_tenant_id').val()}/clear`, 'POST', { clear_reason }, 'Warning cleared');
});
$('#docSaveBtn').on('click', function() {
    let v = id => $(id).is(':checked') ? 1 : 0;
    warningPost(`/tenancy/master/tenant-warnings/${$('#warn_tenant_id').val()}/documents`, 'PUT',
        { allow_sik: v('#doc_sik'), allow_sia: v('#doc_sia'), allow_simkb_in: v('#doc_simkb_in'), allow_simkb_out: v('#doc_simkb_out'), allow_simkb_inout: v('#doc_simkb_inout') },
        'Allowed documents saved');
});
