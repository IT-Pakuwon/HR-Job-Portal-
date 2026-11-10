(function () {
    'use strict';
    $(function () {
        const root = document.getElementById('sendApprovalEmail');
        if (!root) return;
        const user = $('#approvalEmailUser');
        const send = $('#approvalEmailSend');
        const notice = $('#approvalEmailNotice');
        let sequence = 0;
        let loadedUser = null;
        let sending = false;
        const text = $.fn.dataTable.render.text();
        const table = $('#approvalEmailTable').DataTable({
            data: [], pageLength: 25, order: [], responsive: true,
            columns: [
                { data: 'docname', render: text }, { data: 'docid', render: text },
                { data: 'cpnyid', render: text }, { data: 'deptname', render: text },
                { data: 'date', render: text }, { data: 'createdby', render: text },
                { data: 'info', render: text }
            ]
        });
        user.select2({
            width: '100%', placeholder: 'Cari nama...', allowClear: true, minimumInputLength: 2,
            ajax: { url: root.dataset.users, dataType: 'json', delay: 300, data: params => ({ q: params.term }) }
        });

        async function loadPending() {
            const username = user.val();
            const current = ++sequence;
            loadedUser = null;
            send.prop('disabled', true);
            table.clear().draw();
            $('#approvalEmailRecipient').text('-');
            $('#approvalEmailTotal').text('0');
            if (!username) { notice.addClass('hidden'); return; }
            notice.text('Memuat pending approval...').removeClass('hidden');
            try {
                const response = await fetch(root.dataset.pending + '?' + new URLSearchParams({ username }), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json().catch(() => ({}));
                if (current !== sequence) return;
                if (!response.ok) throw new Error(result.message || 'Gagal memuat pending approval.');
                table.rows.add(result.documents || []).draw();
                $('#approvalEmailRecipient').text(result.email || '-');
                $('#approvalEmailTotal').text(result.total || 0);
                loadedUser = username;
                send.prop('disabled', sending || !result.total || !result.email);
                if (result.skipped_count) {
                    notice.text(result.skipped_count + ' approval tidak memiliki detail dokumen yang cocok dan tidak disertakan dalam email.').removeClass('hidden');
                } else if (!result.email) {
                    notice.text('Notification email pengguna belum diisi.').removeClass('hidden');
                } else if (!result.total) {
                    notice.text('Tidak ada pending approval.').removeClass('hidden');
                } else {
                    notice.addClass('hidden');
                }
            } catch (error) {
                if (current === sequence) notice.text(error.message).removeClass('hidden');
            }
        }
        user.on('change', loadPending);
        $('#approvalEmailSearch').on('submit', function (event) { event.preventDefault(); if (!sending) loadPending(); });
        send.on('click', async function () {
            if (sending || !loadedUser || loadedUser !== user.val()) return;
            sending = true;
            send.prop('disabled', true).find('span').text('Sending...');
            user.prop('disabled', true);
            $('#approvalEmailFind').prop('disabled', true);
            try {
                const response = await fetch(root.dataset.send, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json', 'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ username: loadedUser })
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || 'Gagal mengirim email.');
                notice.text(result.message).removeClass('hidden');
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Email berhasil dikirim',
                        text: result.message,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                notice.text(error.message).removeClass('hidden');
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Email gagal dikirim',
                        text: error.message,
                        confirmButtonText: 'OK'
                    });
                }
            } finally {
                sending = false;
                send.prop('disabled', false).find('span').text('Send Email');
                user.prop('disabled', false);
                $('#approvalEmailFind').prop('disabled', false);
            }
        });
    });
})();
