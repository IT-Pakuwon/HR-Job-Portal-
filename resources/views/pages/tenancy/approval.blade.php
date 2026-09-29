<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div class="flex flex-row items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/[0.06] sm:flex-row sm:items-center">
                <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">✅ Approval List</h2>
                <button id="addApprovalBtn"
                    class="inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition hover:bg-blue-500">
                    + Add Approval
                </button>
            </div>
            <div class="relative overflow-hidden p-2">
                <table id="approvalsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Doctype</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="w-16 px-4 py-3 text-left font-medium">Order</th>
                            <th class="px-4 py-3 text-left font-medium">Username(s)</th>
                            <th class="px-4 py-3 text-left font-medium">Condition</th>
                            <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- APPROVAL MODAL -->
        <div id="approvalModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                    <h2 id="approvalModalTitle" class="text-base font-semibold text-gray-800 dark:text-gray-100">✅ Add Approval</h2>
                    <button type="button" class="tsModalClose text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200"
                        data-modal="approvalModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="approvalForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="apr_id" name="id">
                    <div class="grid flex-1 grid-cols-1 gap-4 overflow-y-auto px-5 py-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Doctype <span class="text-red-500">*</span></label>
                            <select id="apr_doctype" name="doctype"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                <option value="">-- Select Doctype --</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site <span class="text-red-500">*</span></label>
                            <select id="apr_siteid" name="siteid"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                <option value="">-- Select Site --</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Department ID</label>
                            <input type="text" id="apr_departmentid" name="departmentid"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]"
                                placeholder="Leave blank to apply to all departments">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Order <span class="text-red-500">*</span></label>
                            <input type="number" id="apr_urutan" name="urutan" step="0.1" min="0" max="99.9"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Username(s) <span class="text-red-500">*</span></label>
                            <input type="text" id="apr_username" name="username"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]"
                                placeholder="e.g. johndoe,janedoe" required>
                            <p class="mt-1 text-xs text-gray-400">Comma-separated list of one or more approver usernames.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Access Type <span class="text-red-500">*</span></label>
                            <input type="text" id="apr_accesstype" name="accesstype" value="Approve"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Condition <span class="text-red-500">*</span></label>
                            <select id="apr_conditiontype" name="conditiontype"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:disabled:bg-white/[0.03]" required>
                                <option value="Normal">Normal</option>
                                <option value="Extend">Extend</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-4 dark:border-white/[0.06]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="approvalModal">Cancel</button>
                        <button type="submit" id="approvalSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toastError(text) {
            Swal.fire({ icon: 'error', title: 'Error', text: text || 'Something went wrong' });
        }

        function toastSuccess(text) {
            Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1500, showConfirmButton: false });
        }

        function statusBadge(data) {
            return data === 'A' ?
                '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
        }

        function tsAutoSelect($select, selectedValue, values) {
            if (selectedValue) {
                $select.val(selectedValue);
            } else if (values.length === 1) {
                $select.val(values[0]).trigger('change');
            }
        }

        function tsOpenModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('hidden').addClass('flex');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    $modal.removeClass('opacity-0').addClass('opacity-100');
                    $modal.find('.ts-modal-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                });
            });
        }

        function tsCloseModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('opacity-100').addClass('opacity-0');
            $modal.find('.ts-modal-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(function() {
                $modal.addClass('hidden').removeClass('flex');
            }, 200);
        }

        $(document).ready(function() {

            $(document).on('click', '.tsModalClose', function() {
                tsCloseModal($(this).data('modal'));
            });

            $(document).on('click', '.tsModalBackdrop', function(e) {
                if (e.target === this) tsCloseModal(this.id);
            });

            let approvalTable = $('#approvalsTable').DataTable({
                ajax: "{{ route('tenancy.approval.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleApprovalStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewApprovalBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editApprovalBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'doctype' },
                    { data: 'site.sitename', render: (d, type, row) => d ? `${d} (${row.siteid})` : row.siteid },
                    { data: 'departmentid', render: d => d ?? 'ALL' },
                    { data: 'urutan' },
                    { data: 'username' },
                    { data: 'conditiontype' },
                    { data: 'status', render: statusBadge },
                ]
            });

            function loadDoctypeOptions($select, selectedValue) {
                return $.get("{{ route('tenancy.approval.doctypes') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(dt) {
                        $select.append(`<option value="${dt.doctype}">${dt.doctype} - ${dt.documentname}</option>`);
                    });
                    tsAutoSelect($select, selectedValue, (res.data || []).map(dt => dt.doctype));
                });
            }

            function loadSiteOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(site) {
                        $select.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(s => s.siteid));
                });
            }

            function openApprovalModal(mode, data) {
                let readOnly = mode === 'view';
                $('#approvalForm')[0].reset();
                $('#apr_id').val('');
                $('#approvalModalTitle').text(mode === 'add' ? 'Add Approval' : (mode === 'view' ? 'View Approval' : 'Edit Approval'));
                $('#approvalForm input, #approvalForm select').prop('disabled', readOnly);
                $('#approvalSaveBtn').toggle(!readOnly);

                loadDoctypeOptions($('#apr_doctype'), data ? data.doctype : null);
                loadSiteOptions($('#apr_siteid'), data ? data.siteid : null);

                if (data) {
                    $('#apr_id').val(data.id);
                    $('#apr_departmentid').val(data.departmentid);
                    $('#apr_urutan').val(data.urutan);
                    $('#apr_username').val(data.username);
                    $('#apr_accesstype').val(data.accesstype);
                    $('#apr_conditiontype').val(data.conditiontype);
                } else {
                    $('#apr_accesstype').val('Approve');
                    $('#apr_conditiontype').val('Normal');
                }

                tsOpenModal('approvalModal');
            }

            $('#addApprovalBtn').click(function() {
                openApprovalModal('add', null);
            });

            $(document).on('click', '.viewApprovalBtn, .editApprovalBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewApprovalBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/approval/${id}/edit`, function(d) {
                    hideLoading();
                    openApprovalModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data approval');
                    console.error(xhr.responseText);
                });
            });

            $('#approvalForm').submit(function(e) {
                e.preventDefault();
                let id = $('#apr_id').val();
                let url = id ? `/tenancy/approval/${id}` : "{{ route('tenancy.approval.store') }}";
                let formData = new FormData(document.getElementById('approvalForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('approvalModal');
                        approvalTable.ajax.reload(null, false);
                        toastSuccess('Approval saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan approval');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleApprovalStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/approval/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { approvalTable.ajax.reload(null, false); }
                });
            });

        });
    </script>
</x-app-layout>
