/**
 * VoucherTaxi - Process Module
 * GA voucher processing and budget entry
 */

const VoucherTaxiProcess = {
    formId: '#processVoucherForm',
    isSubmitting: false,

    /**
     * Initialize
     */
    init() {
        this.initEventHandlers();
    },

    /**
     * Initialize event handlers
     */
    initEventHandlers() {
        const self = this;

        // Form submission
        $(document).on('submit', this.formId, function(e) {
            e.preventDefault();
            self.submit();
        });

        // Change expense owner checkbox
        $(document).on('change', '#changeExpenseOwner', function() {
            if ($(this).is(':checked')) {
                $('#expenseOwnerSection').removeClass('hidden');
                $(self.formId).find('[name="cpny_id_expense"], [name="department_id_expense"], [name="user_peminta_expense"]').prop('required', true);
            } else {
                $('#expenseOwnerSection').addClass('hidden');
                $(self.formId).find('[name="cpny_id_expense"], [name="department_id_expense"], [name="user_peminta_expense"]').prop('required', false);
            }
        });

        // Budget input formatting
        $(document).on('input', '#actual_budget_display', function() {
            let value = $(this).val();

            // Remove non-digits
            value = value.replace(/\D/g, '');

            // Format with thousand separators
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
            }

            $(this).val(value);

            // Store actual number in hidden field
            $('#actual_budget').val(value.replace(/\D/g, ''));
        });
    },

    /**
     * Load process form
     */
    load(docid) {
        VoucherTaxi.showLoading();

        VoucherTaxiRoute.fetchDetail(VoucherTaxi.state.currentVoucherId)
            .done((response) => {
                if (response.success && response.data) {
                    this.populateForm(response.data, docid);
                    VoucherTaxiModal.openProcess();
                }
            })
            .fail(() => {
                VoucherTaxi.showError('Failed to load voucher for processing');
            })
            .always(() => {
                VoucherTaxi.hideLoading();
            });
    },

    /**
     * Populate form with voucher data
     */
    populateForm(data, docid) {
        const form = $(this.formId);

        // Store docid
        form.find('[name="process_docid"]').val(docid);
        form.find('[id="process_docid"]').val(docid);

        // Display information
        $('#process_docno').text(data.docid);
        $('#process_requester').text(data.user_name || data.user_peminta);
        $('#process_date').text(VoucherTaxi.formatDate(data.date_used));
        $('#process_company').text(data.cpny_id);
        $('#process_department').text(data.department_id);
        $('#process_trip').text(data.type_trip);
        $('#process_route').text(`${data.origin} → ${data.destination}`);
        $('#process_purpose').text(data.purpose_descr);

        // Pre-fill with existing budget if any
        if (data.actual_budget && data.actual_budget > 0) {
            const formatted = VoucherTaxi.formatCurrency(data.actual_budget);
            form.find('#actual_budget_display').val(formatted);
            form.find('#actual_budget').val(data.actual_budget);
        }

        // Pre-fill expense owner
        form.find('[name="cpny_id_expense"]').val(data.cpny_id_expense || '').trigger('change');
        form.find('[name="user_peminta_expense"]').val(data.user_peminta_expense || '');
    },

    /**
     * Validate form
     */
    validate() {
        const form = $(this.formId);
        const actualBudget = form.find('[name="actual_budget"]').val();

        if (!actualBudget || parseInt(actualBudget) === 0) {
            VoucherTaxi.showError('Please enter the actual budget amount');
            return false;
        }

        // Validate expense owner if selected
        if (form.find('[name="change_expense_owner"]').is(':checked')) {
            const cpny = form.find('[name="cpny_id_expense"]').val();
            const dept = form.find('[name="department_id_expense"]').val();
            const user = form.find('[name="user_peminta_expense"]').val();

            if (!cpny || !dept || !user) {
                VoucherTaxi.showError('Please select the new expense owner (Company, Department, Employee)');
                return false;
            }
        }

        return true;
    },

    /**
     * Get form data
     */
    getFormData() {
        const form = $(this.formId);

        return {
            actual_budget: form.find('[name="actual_budget"]').val(),
            change_expense_owner: form.find('[name="change_expense_owner"]').is(':checked') ? 1 : 0,
            cpny_id_expense: form.find('[name="cpny_id_expense"]').val() || null,
            department_id_expense: form.find('[name="department_id_expense"]').val() || null,
            user_peminta_expense: form.find('[name="user_peminta_expense"]').val() || null,
        };
    },

    /**
     * Submit form
     */
    submit() {
        if (this.isSubmitting) return;

        // Validate
        if (!this.validate()) {
            return;
        }

        this.isSubmitting = true;
        const docid = $(this.formId).find('[name="process_docid"]').val();
        const data = this.getFormData();

        VoucherTaxi.showLoading();

        VoucherTaxiRoute.processVoucher(docid, data)
            .done((response) => {
                if (response.success) {
                    VoucherTaxi.showSuccess(response.message);
                    this.reset();
                    VoucherTaxiModal.closeProcess();

                    // Refresh detail
                    setTimeout(() => {
                        VoucherTaxiDetailModal.refresh();
                    }, 1000);
                }
            })
            .fail((xhr) => {
                const message = xhr.responseJSON?.message || 'Failed to process voucher';
                VoucherTaxi.showError(message);
            })
            .always(() => {
                VoucherTaxi.hideLoading();
                this.isSubmitting = false;
            });
    },

    /**
     * Reset form
     */
    reset() {
        const form = $(this.formId);
        form[0].reset();

        // Reset checkboxes and sections
        $('#changeExpenseOwner').prop('checked', false);
        $('#expenseOwnerSection').addClass('hidden');

        // Clear budget display
        $('#actual_budget_display').val('');
        $('#actual_budget').val('');
    },

    /**
     * Disable form
     */
    disable() {
        $(this.formId).find('input, select, textarea, button').prop('disabled', true);
    },

    /**
     * Enable form
     */
    enable() {
        $(this.formId).find('input, select, textarea, button').prop('disabled', false);
    },
};

// Initialize on document ready
$(document).ready(() => {
    VoucherTaxiProcess.init();
});
