// ============================================================
// process.js — Voucher Taxi
// GA (General Affairs) process: actual budget & expense owner
// ============================================================

const VoucherTaxiProcess = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentEid:      null,
        currentDocid:    null,
        currentVoucher:  null,
        isSubmitting:    false,
    },

    // --------------------------------------------------------
    // INIT — attach event listeners
    // --------------------------------------------------------
    init() {
        VoucherTaxiProcess.attachEventListeners();
    },

    // --------------------------------------------------------
    // ATTACH EVENT LISTENERS
    // --------------------------------------------------------
    attachEventListeners() {
        // Change expense owner checkbox → show/hide the form fields
        document.getElementById('changeExpenseOwner')
            ?.addEventListener('change', (e) => {
                const section = document.getElementById('expenseOwnerSection');
                if (section) {
                    section.classList.toggle('hidden', !e.target.checked);
                }
                if (!e.target.checked) {
                    // Clear the fields
                    VoucherTaxiHelper.setSelect('process_cpny_id_expense', '');
                    VoucherTaxiHelper.setSelect('process_department_id_expense', '');
                    VoucherTaxiHelper.setSelect('process_user_peminta_expense', '');
                }
            });

        // Actual budget input — format as user types
        document.getElementById('actual_budget_display')
            ?.addEventListener('input', (e) => {
                VoucherTaxiProcess.formatBudgetInput(e.target);
            });

        // Process button click (from detail modal)
        document.getElementById('processVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiProcess.openProcess();
            });

        // Submit button
        document.getElementById('submitProcessVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiProcess.submit();
            });
    },

    // --------------------------------------------------------
    // FORMAT BUDGET INPUT (currency formatting)
    // --------------------------------------------------------
    formatBudgetInput(inputEl) {
        let value = inputEl.value.replace(/[^0-9]/g, '');

        if (value.length === 0) {
            inputEl.value = '';
            return;
        }

        // Format with thousand separators
        value = parseInt(value, 10).toLocaleString('id-ID');
        inputEl.value = value;
    },

    // --------------------------------------------------------
    // OPEN PROCESS MODAL
    // --------------------------------------------------------
    async openProcess() {
        const eid = VoucherTaxi.state.currentEid;
        const docid = VoucherTaxi.state.currentDocid;

        if (!eid || !docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        try {
            // Fetch voucher data
            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.detail(eid)
            );

            if (!response.success) {
                VoucherTaxi.toast('error', response.message ?? 'Failed to load voucher');
                return;
            }

            const voucher = response.data;

            // Validate status (must be C or F)
            if (!['C', 'F'].includes(voucher.status)) {
                VoucherTaxi.toast('warning', 'Only approved or processed vouchers can be processed.');
                return;
            }

            // Store and populate
            VoucherTaxiProcess.state.currentEid = eid;
            VoucherTaxiProcess.state.currentDocid = docid;
            VoucherTaxiProcess.state.currentVoucher = voucher;

            VoucherTaxiProcess.populateProcess(voucher);

            // Open modal
            VoucherTaxiModal.openProcess();

        } catch (err) {
            console.error('Open process error:', err);
            VoucherTaxi.toast('error', 'Failed to open process form');
        }
    },

    // --------------------------------------------------------
    // POPULATE PROCESS MODAL
    // --------------------------------------------------------
    populateProcess(voucher) {
        // Store IDs
        document.getElementById('process_docid').value = voucher.docid ?? '';

        // Display information
        VoucherTaxiHelper.setText('process_docno', voucher.docid ?? '-');
        VoucherTaxiHelper.setText('process_requester', voucher.user_name ?? voucher.user_peminta ?? '-');
        VoucherTaxiHelper.setText('process_date', VoucherTaxi.formatDate(voucher.date_used) ?? '-');
        VoucherTaxiHelper.setText('process_company', voucher.cpny_id ?? '-');
        VoucherTaxiHelper.setText('process_department', voucher.department_id ?? '-');
        VoucherTaxiHelper.setText('process_trip', voucher.type_trip ?? '-');
        VoucherTaxiHelper.setText('process_route', `${voucher.origin} → ${voucher.destination}` ?? '-');
        VoucherTaxiHelper.setText('process_purpose', voucher.purpose_descr ?? '-');

        // Reset form fields
        VoucherTaxiHelper.setValue('actual_budget_display', '');
        document.getElementById('changeExpenseOwner').checked = false;

        // Hide expense owner section by default
        const expenseSection = document.getElementById('expenseOwnerSection');
        if (expenseSection) expenseSection.classList.add('hidden');

        // Reset expense owner selects
        VoucherTaxiHelper.setSelect('process_cpny_id_expense', '');
        VoucherTaxiHelper.setSelect('process_department_id_expense', '');
        VoucherTaxiHelper.setSelect('process_user_peminta_expense', '');

        // Init Select2 for expense owner dropdowns
        VoucherTaxiProcess.initSelect2();
    },

    // --------------------------------------------------------
    // INIT SELECT2 FOR EXPENSE OWNER SELECTS
    // --------------------------------------------------------
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        const config = {
            dropdownParent: $('#processVoucherModal'),
            width:          '100%',
            allowClear:     false,
        };

        const init = ($el, extra = {}) => {
            if (!$el.length) return;
            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
            $el.select2({ ...config, ...extra });
        };

        init($('#process_cpny_id_expense'), { placeholder: 'Select Company' });
        init($('#process_department_id_expense'), { placeholder: 'Select Department' });
        init($('#process_user_peminta_expense'), { placeholder: 'Select Employee' });

        // Department change → reload employees filtered by dept
        $('#process_department_id_expense').off('change.processVoucher').on('change.processVoucher', function () {
            VoucherTaxiProcess.loadEmployees($(this).val());
        });
    },

    // --------------------------------------------------------
    // LOAD EMPLOYEES BY DEPARTMENT (for expense owner section)
    // --------------------------------------------------------
    loadEmployees(deptId) {
        const $select = $('#process_user_peminta_expense');

        if (!deptId) {
            $select.html('<option value="">Select Employee</option>').trigger('change');
            return;
        }

        fetch(`/vouchertaxi/employee-by-department?department_id=${encodeURIComponent(deptId)}`, {
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                $select.html('<option value="">Select Employee</option>');
                (res.data ?? []).forEach(emp => {
                    $select.append(`<option value="${emp.username}">${emp.name}</option>`);
                });
                $select.trigger('change');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load employees'));
    },

    // --------------------------------------------------------
    // VALIDATE FORM
    // --------------------------------------------------------
    validate() {
        const budgetDisplay = document.getElementById('actual_budget_display')?.value?.trim();
        const changeExpense = document.getElementById('changeExpenseOwner')?.checked;

        // Actual budget is required
        if (VoucherTaxiHelper.isEmpty(budgetDisplay)) {
            VoucherTaxi.toast('warning', 'Actual budget is required');
            return false;
        }

        // If changing expense owner, all fields are required
        if (changeExpense) {
            const cpny = VoucherTaxiHelper.getValue('process_cpny_id_expense');
            const dept = VoucherTaxiHelper.getValue('process_department_id_expense');
            const emp = VoucherTaxiHelper.getValue('process_user_peminta_expense');

            if (VoucherTaxiHelper.isEmpty(cpny)) {
                VoucherTaxi.toast('warning', 'Please select company for expense owner');
                return false;
            }

            if (VoucherTaxiHelper.isEmpty(dept)) {
                VoucherTaxi.toast('warning', 'Please select department for expense owner');
                return false;
            }

            if (VoucherTaxiHelper.isEmpty(emp)) {
                VoucherTaxi.toast('warning', 'Please select employee for expense owner');
                return false;
            }
        }

        return true;
    },

    // --------------------------------------------------------
    // CONVERT BUDGET TO NUMBER (remove formatting)
    // --------------------------------------------------------
    parseBudget(displayValue) {
        // Remove all non-digit characters
        const cleaned = displayValue.replace(/[^0-9]/g, '');
        return cleaned ? parseInt(cleaned, 10) : 0;
    },

    // --------------------------------------------------------
    // SUBMIT FORM
    // --------------------------------------------------------
    async submit() {
        if (VoucherTaxiProcess.state.isSubmitting) return;

        if (!VoucherTaxiProcess.validate()) return;

        const docid = document.getElementById('process_docid')?.value || VoucherTaxiProcess.state.currentDocid;

        if (!docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        VoucherTaxiProcess.state.isSubmitting = true;
        VoucherTaxiHelper.setButtonLoading('submitProcessVoucherBtn', true);

        try {
            // Parse actual budget (convert from display format)
            const budgetDisplay = document.getElementById('actual_budget_display')?.value || '';
            const actualBudget = VoucherTaxiProcess.parseBudget(budgetDisplay);

            const changeExpense = document.getElementById('changeExpenseOwner')?.checked ?? false;
            const cpnyExpense = changeExpense ? VoucherTaxiHelper.getValue('process_cpny_id_expense') : null;
            const deptExpense = changeExpense ? VoucherTaxiHelper.getValue('process_department_id_expense') : null;
            const empExpense = changeExpense ? VoucherTaxiHelper.getValue('process_user_peminta_expense') : null;

            const payload = {
                actual_budget: actualBudget,
                change_expense_owner: changeExpense,
                cpny_id_expense: cpnyExpense,
                department_id_expense: deptExpense,
                user_peminta_expense: empExpense,
            };

            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.process(docid),
                {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify(payload),
                }
            );

            if (response.success) {
                VoucherTaxi.toast('success', response.message ?? 'Voucher processed successfully');

                // Close modal after short delay
                setTimeout(() => {
                    VoucherTaxiModal.closeProcess();
                    // Refresh detail modal
                    VoucherTaxiDetailModal.refresh();
                    // Refresh list
                    VoucherTaxiDatalist.refresh();
                }, 500);
            } else {
                VoucherTaxi.toast('error', response.message ?? 'Failed to process voucher');
            }

        } catch (err) {
            console.error('Process submit error:', err);
            let message = 'Failed to process voucher';

            if (err.status === 403) {
                message = 'You do not have permission to process this voucher';
            } else if (err.status === 422 && err.data?.errors) {
                message = Object.values(err.data.errors).flat()[0] ?? message;
            } else if (err.data?.message) {
                message = err.data.message;
            }

            VoucherTaxi.toast('error', message);

        } finally {
            VoucherTaxiProcess.state.isSubmitting = false;
            VoucherTaxiHelper.setButtonLoading('submitProcessVoucherBtn', false);
        }
    },

    // --------------------------------------------------------
    // CLEANUP ON MODAL CLOSE
    // --------------------------------------------------------
    cleanup() {
        VoucherTaxiProcess.state.currentEid = null;
        VoucherTaxiProcess.state.currentDocid = null;
        VoucherTaxiProcess.state.currentVoucher = null;
        VoucherTaxiProcess.state.isSubmitting = false;

        // Reset form
        VoucherTaxiHelper.resetForm('processVoucherForm');

        // Hide expense owner section
        const section = document.getElementById('expenseOwnerSection');
        if (section) section.classList.add('hidden');
    },

    // --------------------------------------------------------
    // GET PROCESS SUMMARY
    // --------------------------------------------------------
    getProcessSummary(voucher) {
        if (!voucher) return {};

        return {
            docid: voucher.docid,
            requester: voucher.user_peminta,
            date: VoucherTaxi.formatDate(voucher.date_used),
            route: `${voucher.origin} → ${voucher.destination}`,
            purpose: voucher.purpose_descr,
            tripType: voucher.type_trip,
        };
    },

    // --------------------------------------------------------
    // CAN SHOW PROCESS BUTTON
    // --------------------------------------------------------
    canShowProcessButton(voucher) {
        // Show process button if:
        // 1. User is GA (checked server-side)
        // 2. Status is C (Approved) or F (Already Processed)
        // 3. Voucher is in approved workflow

        if (!voucher) return false;
        return ['C', 'F'].includes(voucher.status);
    },
};
