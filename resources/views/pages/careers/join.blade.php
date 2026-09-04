<div class="flex flex-col">

    {{-- ── Phase 1 · Integrity Documents ──────────────────────────── --}}
    <div class="flex items-stretch gap-4">
        <div class="flex flex-shrink-0 flex-col items-center">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">1</div>
            <div class="mt-1 w-0.5 flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="mb-5 flex-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-700/60">
                <div class="flex items-center gap-2.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 1</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Integrity Documents</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-[10.5px] font-semibold text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">2 Documents</span>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                <div class="flex items-center justify-between px-5 py-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Pakta Integritas</p>
                    <form id="integritasForm" class="flex-shrink-0">
                        @csrf
                        <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                        <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                        <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                        <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                        <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-gray-400 hover:text-gray-800 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400">
                            <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                            Preview
                        </button>
                    </form>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Surat Pernyataan Penggunaan Fasilitas Elektronik</p>
                    <form id="pernyataanForm" class="flex-shrink-0">
                        @csrf
                        <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                        <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                        <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                        <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                        <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-gray-400 hover:text-gray-800 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400">
                            <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                            Preview
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Phase 2 · Onboarding Checklist ───────────────────────────── --}}
    <div class="flex items-stretch gap-4" id="docid_onboarding" data-docid="{{ optional($onboarding)->docid }}">
        <div class="flex flex-shrink-0 flex-col items-center">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">2</div>
            <div class="mt-1 w-0.5 flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="mb-5 flex-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-700/60">
                <div class="flex items-center gap-2.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 2</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Onboarding Checklist</p>
                </div>
                <span id="checklistCountPill"
                    class="hidden rounded-full bg-indigo-50 px-2.5 py-0.5 text-[10.5px] font-semibold text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400"></span>
            </div>

            <form id="checklistForm" class="px-5 py-4">
                @csrf
                <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                <div id="checklistArea" class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" id="btnUpdateChecklist"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                        Update
                    </button>
                    <button type="submit" id="btnSaveChecklist" style="display:none;"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-gray-900">
                        <svg class="checklist-spin hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span class="checklist-text">Save Checklist</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Phase 3 · Onboarding Schedule ────────────────────────────── --}}
    <div class="flex items-start gap-4">
        <div class="flex flex-shrink-0 flex-col items-center">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">3</div>
        </div>
        <div class="flex-1 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            @php($schedulePayroll = $payrolls->first())
            @php($isScheduled = !empty(optional($schedulePayroll)->work_start_date))
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-700/60">
                <div class="flex items-center gap-2.5">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 3</span>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Onboarding Schedule</p>
                </div>
                <span id="scheduleStatusPill"
                    class="rounded-full px-2.5 py-0.5 text-[10.5px] font-semibold {{ $isScheduled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' }}">
                    {{ $isScheduled ? 'Scheduled' : 'Pending' }}
                </span>
            </div>

            <form id="scheduleForm" class="p-5">
                @csrf
                <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">

                @php($scheduleMinWorkStart = optional($schedulePayroll)->work_start_date ? \Carbon\Carbon::parse($schedulePayroll->work_start_date)->format('Y-m-d') : '')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col">
                        <label for="sch_work_start_date"
                            class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Tanggal Mulai Kerja</label>
                        <input type="date" id="sch_work_start_date" name="work_start_date" disabled
                            value="{{ $scheduleMinWorkStart }}"
                            @if ($scheduleMinWorkStart) min="{{ $scheduleMinWorkStart }}" @endif
                            class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700/40"
                            required>
                        <p class="mt-1 text-[11px] text-gray-400">Tidak boleh sebelum tanggal yang sudah dikonfirmasi di Payroll.</p>
                    </div>
                    <div class="flex flex-col">
                        <label for="sch_availability_date"
                            class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Tanggal Selesai Kerja</label>
                        <input type="date" id="sch_availability_date" name="availability_date" disabled
                            value="{{ optional($schedulePayroll)->availability_date ? \Carbon\Carbon::parse($schedulePayroll->availability_date)->format('Y-m-d') : '' }}"
                            class="w-full rounded-lg border border-gray-300 p-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:disabled:bg-gray-700/40"
                            required>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" id="btnUpdateSchedule"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                        Update
                    </button>
                    <button type="submit" id="btnSaveSchedule" style="display:none;"
                        class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                        <span class="sch-text">Save Schedule &amp; Send Email</span>
                        <svg class="sch-spin ml-2 hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>

<script>
    $('#integritasForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('paktaintegritas.pdf') }}",
            method: 'POST',
            data: form.serialize(),
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(blob) {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // 👈 preview PDF di tab baru
            },
            error: function() {
                alert("Failed to generate PDF.");
            }
        });
    });
</script>

<script>
    $('#pernyataanForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('pernyataanelectonik.pdf') }}",
            method: 'POST',
            data: form.serialize(),
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(blob) {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // 👈 preview PDF di tab baru
            },
            error: function() {
                alert("Failed to generate PDF.");
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        const docidOnboarding = $('#docid_onboarding').data('docid');

        function setChecklistSaving(isSaving) {
            const $button = $('#btnSaveChecklist');
            $button.prop('disabled', isSaving);
            $button.find('.checklist-spin').toggleClass('hidden', !isSaving);
            $button.find('.checklist-text').text(isSaving ? 'Saving...' : 'Save Checklist');
        }

        function updateChecklistCountPill() {
            const $items = $('#checklistArea input[name="checklist[]"]');
            const total = $items.length;
            if (!total) {
                $('#checklistCountPill').addClass('hidden');
                return;
            }
            const checkedCount = $items.filter(':checked').length;
            $('#checklistCountPill').removeClass('hidden').text(checkedCount + ' / ' + total + ' Checked');
        }

        function setChecklistEditing(isEditing) {
            $('#checklistArea input[name="checklist[]"]').prop('disabled', !isEditing);
            $('#btnUpdateChecklist').css('display', isEditing ? 'none' : '');
            $('#btnSaveChecklist').css('display', isEditing ? '' : 'none');
        }

        $('#btnUpdateChecklist').on('click', function() {
            setChecklistEditing(true);
        });

        $('#checklistForm').off('submit.checklist').on('submit.checklist', function(event) {
            event.preventDefault();

            if (!docidOnboarding) {
                toastr.error('Onboarding belum dibuat atau DocID tidak tersedia.');
                return;
            }

            const checked = $('#checklistArea input[name="checklist[]"]:checked')
                .map(function() { return this.value; })
                .get();

            setChecklistSaving(true);

            $.ajax({
                url: "{{ route('onboarding.checklist.update') }}",
                type: 'POST',
                headers: { 'Accept': 'application/json' },
                data: {
                    _token: $('#checklistForm input[name="_token"]').val(),
                    docid_onboarding: docidOnboarding,
                    cpnyid: $('#checklistForm input[name="cpnyid"]').val(),
                    checked: checked
                }
            }).done(function(response) {
                if (response && response.success) {
                    toastr.success('Checklist berhasil disimpan.');
                    updateChecklistCountPill();
                    setChecklistEditing(false);
                } else {
                    toastr.error(response.message || response.error || 'Gagal menyimpan checklist.');
                }
            }).fail(function(xhr) {
                toastr.error(xhr.responseJSON?.message || xhr.responseJSON?.error ||
                    'Terjadi kesalahan saat menyimpan checklist.');
            }).always(function() {
                setChecklistSaving(false);
            });
        });

        if (!docidOnboarding) {
            $('#checklistArea').html(`
                <div class="col-span-full flex flex-col items-center gap-2 rounded-lg border border-dashed border-gray-200 bg-gray-50/60 px-5 py-8 text-center dark:border-gray-700 dark:bg-gray-700/20">
                    <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11.25 11.5H12v4.75h.75"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Checklist belum tersedia</p>
                    <p class="max-w-sm text-xs leading-relaxed text-gray-400 dark:text-gray-500">
                        Checklist onboarding otomatis dibuat setelah data <span class="font-semibold text-gray-500 dark:text-gray-400">Payroll Confirmation</span>
                        disimpan di tab <span class="font-semibold text-gray-500 dark:text-gray-400">Payroll</span>.
                    </p>
                </div>
            `);
            $('#btnUpdateChecklist').css('display', 'none');
            return; // ⟵ stop supaya tidak nembak /onboarding tanpa docid
        }

        const url = `/onboarding/${encodeURIComponent(docidOnboarding)}`;
        $.get(url, function(data) {
            let html = '';
            data.forEach(item => {
                const checked = item.checklist_onboarding_receive ? 'checked' : '';
                html += `
                <label class="flex items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2.5 dark:border-gray-600 ${item.checklist_onboarding_receive ? 'bg-emerald-50/40 dark:bg-emerald-900/10' : ''}">
                    <input type="checkbox" name="checklist[]" value="${item.id}" ${checked} disabled
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">${item.checklist_onboarding_descr}</span>
                </label>
            `;
            });
            $('#checklistArea').html(html);
            updateChecklistCountPill();
        }).fail(function(xhr) {
            toastr.error('Gagal memuat checklist onboarding.');
        });
    });
</script>

<script>
    $(function() {
        function setScheduleSaving(isSaving) {
            const $btn = $('#btnSaveSchedule');
            $btn.prop('disabled', isSaving);
            $btn.find('.sch-spin').toggleClass('hidden', !isSaving);
            $btn.find('.sch-text').text(isSaving ? 'Saving...' : 'Save Schedule');
        }

        function setScheduleEditing(isEditing) {
            $('#sch_work_start_date, #sch_availability_date').prop('disabled', !isEditing);
            $('#btnUpdateSchedule').css('display', isEditing ? 'none' : '');
            $('#btnSaveSchedule').css('display', isEditing ? '' : 'none');
        }

        $('#btnUpdateSchedule').on('click', function() {
            setScheduleEditing(true);
        });

        $('#scheduleForm').on('submit', function(e) {
            e.preventDefault();

            const payload = {
                _token: $(this).find('input[name="_token"]').val(),
                applicant_id: $(this).find('input[name="applicant_id"]').val(),
                jobapply_id: $(this).find('input[name="jobapply_id"]').val(),
                cpnyid: $(this).find('input[name="cpnyid"]').val(),
                availability_date: $('#sch_availability_date').val(),
                work_start_date: $('#sch_work_start_date').val()
            };

            if (!payload.availability_date || !payload.work_start_date) {
                toastr.error('Tanggal Mulai dan Tanggal Selesai Kerja wajib diisi.');
                return;
            }

            setScheduleSaving(true);

            $.ajax({
                    url: "{{ route('onboarding.schedule.update') }}",
                    type: 'POST',
                    data: payload,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .done(function(resp) {
                    if (resp && resp.success) {
                        toastr.success(resp.message ||
                            'Jadwal berhasil disimpan & email terkirim.');
                        setScheduleEditing(false);
                        $('#scheduleStatusPill')
                            .removeClass('bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400')
                            .addClass('bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400')
                            .text('Scheduled');
                    } else {
                        toastr.error(resp.message || 'Gagal menyimpan jadwal.');
                    }
                })
                .fail(function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const msgs = [];
                        Object.values(xhr.responseJSON.errors).forEach(arr => arr[0] && msgs.push(
                            arr[0]));
                        toastr.error(msgs.join('<br>'), 'Validation Error', {
                            escapeHtml: true
                        });
                    } else if (xhr.status === 422 && xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message, 'Validation Error', {
                            escapeHtml: true
                        });
                    } else {
                        toastr.error('Terjadi kesalahan saat menyimpan jadwal.');
                    }
                })
                .always(function() {
                    setScheduleSaving(false);
                });
        });
    });
</script>
