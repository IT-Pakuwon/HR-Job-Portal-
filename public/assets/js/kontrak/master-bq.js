(function () {
    'use strict';
    $(function () {
        const root = document.getElementById('masterKontrakBq');
        if (!root) return;
        const dialog = document.getElementById('masterBqDialog');
        const form = document.getElementById('masterBqForm');
        const errors = $('#masterBqErrors');
        let editingId = null;
        let saving = false;
        const categoryFilter = $('#masterBqCategoryFilter');
        const formCategory = $(form.elements.kontrakcategory);
        const searchableCategories = typeof $.fn.select2 === 'function';

        $('#masterCategoryForm [name="kontrakcategory"], #masterBqForm [name="kontrak_bq_id"], #masterBqForm [name="kontrak_bq_type"], #masterBqForm [name="kontrak_bq_uom"]').on('input', function () {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            if (start !== null && end !== null) this.setSelectionRange(start, end);
        });

        if (searchableCategories) {
            categoryFilter.select2({ width: '100%' });
            $('#masterBqTypeFilter').select2({ width: '100%', minimumResultsForSearch: Infinity });
            formCategory.select2({
                width: '100%',
                dropdownParent: $(dialog),
                placeholder: 'Select Category'
            });
            // A hidden Select2 field cannot receive native validation focus.
            formCategory.prop('required', false);
            formCategory.next('.select2').find('.select2-selection').attr('aria-required', 'true');
        }
        const text = $.fn.dataTable.render.text();
        const table = $('#masterBqTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 10,
            order: [[2, 'asc'], [4, 'asc']],
            ajax: {
                url: root.dataset.json,
                data: function (data) {
                    data.kontrakcategory = $('#masterBqCategoryFilter').val();
                    data.kontrak_bq_type = $('#masterBqTypeFilter').val();
                }
            },
            columns: [
                { data: 'id', orderable: false, searchable: false, render: function (id, type) {
                    return type === 'display' ? '<button type="button" data-bq-edit="' + Number(id) + '" title="Edit Kontrak BQ" aria-label="Edit Kontrak BQ" class="h-8 w-8 rounded text-indigo-600 hover:bg-indigo-50"><i class="fas fa-pen" aria-hidden="true"></i></button>' : id;
                } },
                { data: 'kontrak_bq_id', render: text },
                { data: 'kontrakcategory', render: text },
                { data: 'kontrak_bq_type', render: text },
                { data: 'kontrak_bq_line_no', type: 'num', render: text },
                { data: 'kontrak_bq_descr', render: text },
                { data: 'kontrak_bq_uom', render: text },
                { data: 'kontrak_duration_qty', render: $.fn.dataTable.render.number(',', '.', 3) },
                { data: 'status', render: function (value, type) {
                    if (type !== 'display') return value;
                    return value === 'A' ? 'Active' : 'Inactive';
                } }
            ]
        });
        $('#masterBqCategoryFilter, #masterBqTypeFilter').on('change', function () {
            if (categoryTable) highlightCategory();
            table.ajax.reload();
        });

        function openForm(row) {
            form.reset();
            editingId = row ? row.id : null;
            errors.empty().addClass('hidden');
            $('#masterBqModalTitle').text(row ? 'Edit Kontrak Bq' : 'Add Kontrak Bq');
            ['kontrak_bq_id', 'kontrakcategory', 'kontrak_bq_type', 'kontrak_bq_line_no', 'kontrak_bq_descr', 'kontrak_bq_uom', 'kontrak_duration_qty'].forEach(function (name) {
                form.elements[name].value = row ? (row[name] ?? '') : '';
            });
            if (!row) {
                form.elements.kontrakcategory.value = $('#masterBqCategoryFilter').val();
                form.elements.kontrak_bq_type.value = $('#masterBqTypeFilter').val();
                form.elements.kontrak_duration_qty.value = '0';
            }
            form.elements.active.checked = !row || row.status === 'A';
            if (searchableCategories) formCategory.trigger('change.select2');
            dialog.showModal();
        }
        $('#masterBqAdd').on('click', function () { openForm(null); });
        $('#masterBqTable').on('click', '[data-bq-edit]', function () {
            const id = Number(this.dataset.bqEdit);
            const row = table.rows().data().toArray().find(item => Number(item.id) === id);
            if (row) openForm(row);
        });
        $('[data-bq-close]').on('click', function () { if (!saving) dialog.close(); });
        dialog.addEventListener('cancel', function (event) { if (saving) event.preventDefault(); });
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (saving || !form.reportValidity()) return;
            if (!form.elements.kontrakcategory.value) {
                errors.text('Category wajib dipilih.').removeClass('hidden');
                if (searchableCategories) formCategory.select2('open');
                else form.elements.kontrakcategory.focus();
                return;
            }
            const data = Object.fromEntries(new FormData(form));
            data.status = form.elements.active.checked ? 'A' : 'I';
            delete data.active;
            saving = true;
            $('#masterBqSave, [data-bq-close]').prop('disabled', true);
            $('#masterBqSave span').text('Saving...');
            errors.empty().addClass('hidden');
            try {
                const response = await fetch(root.dataset.store + (editingId ? '/' + editingId : ''), {
                    method: editingId ? 'PUT' : 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const messages = result.errors ? Object.values(result.errors).flat() : [result.message || 'Gagal menyimpan Kontrak BQ.'];
                    messages.forEach(message => errors.append($('<p>').text(message)));
                    errors.removeClass('hidden');
                    return;
                }
                const types = $('#masterBqTypeFilter');
                if (!Array.from(types[0].options).some(option => option.value === data.kontrak_bq_type)) {
                    types.append(new Option(data.kontrak_bq_type, data.kontrak_bq_type));
                    $('#masterBqTypes').append($('<option>').val(data.kontrak_bq_type));
                }
                dialog.close();
                $('#masterBqNotice').text(result.message).removeClass('hidden').addClass('text-emerald-700');
                table.ajax.reload(null, false);
            } catch (error) {
                errors.text('Gagal menghubungi server. Silakan coba kembali.').removeClass('hidden');
            } finally {
                saving = false;
                $('#masterBqSave, [data-bq-close]').prop('disabled', false);
                $('#masterBqSave span').text('Save');
            }
        });

        const categoryDialog = document.getElementById('masterCategoryDialog');
        const categoryForm = document.getElementById('masterCategoryForm');
        const categoryErrors = $('#masterCategoryErrors');
        let categoryTable;
        let categoryId = null;
        let categorySaving = false;

        function initializeCategories() {
            if (categoryTable) {
                categoryTable.columns.adjust().responsive.recalc();
                return;
            }
            categoryTable = $('#masterCategoryTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: 10,
                order: [[1, 'asc']],
                ajax: root.dataset.categories,
                columns: [
                    { data: 'id', orderable: false, searchable: false, render: function (id, type) {
                        return type === 'display' ? '<button type="button" data-category-edit="' + Number(id) + '" title="Edit Kategori" aria-label="Edit Kategori" class="h-8 w-8 rounded text-indigo-600 hover:bg-indigo-50"><i class="fas fa-pen" aria-hidden="true"></i></button>' : id;
                    } },
                    { data: 'kontrakcategory', render: text },
                    { data: 'kontrakcategory_descr', render: text },
                    { data: 'status', render: function (value, type) {
                        return type === 'display' ? (value === 'A' ? 'Active' : 'Inactive') : value;
                    } }
                ]
            });
        }

        function highlightCategory() {
            const selected = categoryFilter.val();
            categoryTable.rows({ page: 'current' }).every(function () {
                $(this.node()).toggleClass('category-selected', !!selected && this.data().kontrakcategory === selected);
            });
        }

        initializeCategories();
        categoryTable.on('draw', highlightCategory);
        $('#masterCategoryTable tbody').on('click', 'tr', function (event) {
            if ($(event.target).closest('button, a').length) return;
            const row = categoryTable.row(this).data();
            if (!row) return;
            categoryFilter.val(row.kontrakcategory).trigger('change');
        });

        function openCategory(row) {
            categoryForm.reset();
            categoryId = row ? row.id : null;
            categoryErrors.empty().addClass('hidden');
            $('#masterCategoryTitle').text(row ? 'Edit Kategori' : 'Add Kategori');
            categoryForm.elements.kontrakcategory.value = row ? row.kontrakcategory : '';
            categoryForm.elements.kontrakcategory.readOnly = !!row;
            categoryForm.elements.kontrakcategory_descr.value = row ? row.kontrakcategory_descr : '';
            categoryForm.elements.active.checked = !row || row.status === 'A';
            categoryDialog.showModal();
        }
        $('#masterCategoryAdd').on('click', function () { openCategory(null); });
        $('#masterCategoryTable').on('click', '[data-category-edit]', function () {
            const id = Number(this.dataset.categoryEdit);
            const row = categoryTable.rows().data().toArray().find(item => Number(item.id) === id);
            if (row) openCategory(row);
        });
        $('[data-category-close]').on('click', function () { if (!categorySaving) categoryDialog.close(); });
        categoryDialog.addEventListener('cancel', function (event) { if (categorySaving) event.preventDefault(); });
        categoryForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (categorySaving || !categoryForm.reportValidity()) return;
            const data = Object.fromEntries(new FormData(categoryForm));
            data.status = categoryForm.elements.active.checked ? 'A' : 'I';
            delete data.active;
            categorySaving = true;
            $('#masterCategorySave, [data-category-close]').prop('disabled', true);
            $('#masterCategorySave span').text('Saving...');
            categoryErrors.empty().addClass('hidden');
            try {
                const response = await fetch(root.dataset.categories + (categoryId ? '/' + categoryId : ''), {
                    method: categoryId ? 'PUT' : 'POST',
                    headers: {
                        'Accept': 'application/json', 'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const messages = result.errors ? Object.values(result.errors).flat() : [result.message || 'Gagal menyimpan kategori.'];
                    messages.forEach(message => categoryErrors.append($('<p>').text(message)));
                    categoryErrors.removeClass('hidden');
                    return;
                }
                const saved = result.data;
                [document.getElementById('masterBqCategoryFilter'), form.elements.kontrakcategory].forEach(select => {
                    let option = Array.from(select.options).find(item => item.value === saved.kontrakcategory);
                    if (!option) {
                        option = new Option('', saved.kontrakcategory);
                        select.add(option);
                    }
                    option.text = saved.kontrakcategory + ' - ' + saved.kontrakcategory_descr;
                    if (searchableCategories) $(select).trigger('change.select2');
                });
                categoryDialog.close();
                $('#masterCategoryNotice').text(result.message).removeClass('hidden');
                categoryTable.ajax.reload(null, false);
            } catch (error) {
                categoryErrors.text('Gagal menghubungi server. Silakan coba kembali.').removeClass('hidden');
            } finally {
                categorySaving = false;
                $('#masterCategorySave, [data-category-close]').prop('disabled', false);
                $('#masterCategorySave span').text('Save');
            }
        });
    });
})();
