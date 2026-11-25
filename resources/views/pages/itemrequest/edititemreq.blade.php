<x-app-layout>
    <x-spp-form-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        <form id="itemreqForm" class="flex flex-col gap-4" enctype="multipart/form-data">
            @csrf

            {{-- HEADER --}}
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Edit Item Request - {{ $itemReq->irid }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Choose the company, department and type, then describe the item you need.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2 lg:grid-cols-4">

                    {{-- Company --}}
                    <div class="sf-field flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                        <select name="cpny_id" class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required>
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpny_id }}" {{ $p->cpny_id == $itemReq->cpny_id ? 'selected' : '' }}>
                                    {{ $p->cpny_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="sf-field flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                        <select name="department_id" id="department_id" class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->department_id }}" {{ $p->department_id == $itemReq->department_id ? 'selected' : '' }}>
                                    {{ $p->department_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Inventory Type --}}
                    <div class="sf-field flex flex-col gap-1.5">
                        <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inventory Type</label>
                        <select name="inventory_type" class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" required>
                            <option value="">Select Type</option>
                            <option value="STOCK" {{ old('inventory_type', $itemReq->inventory_type) === 'STOCK' ? 'selected' : '' }}>STOCK</option>
                            <option value="NONSTOCK" {{ old('inventory_type', $itemReq->inventory_type) === 'NONSTOCK' ? 'selected' : '' }}>NON STOCK</option>
                        </select>
                    </div>

                    {{-- Inventory Description --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="inventory_descr_req" class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inventory Description</label>
                        <textarea name="inventory_descr_req" id="inventory_descr_req" rows="1" required
                            class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">{{ old('inventory_descr_req', $itemReq->inventory_descr_req) }}</textarea>
                    </div>

                </div>
            </div>

            {{-- ATTACHMENTS --}}
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                            <span>
                                <span class="req block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                            </span>
                        </span>
                        <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                        <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                    </summary>

                    {{-- Existing attachments (signed URL) --}}
                    <div id="attachmentsList" class="flex flex-col gap-2 px-6 pt-5">
                        @forelse ($attachments as $att)
                            <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                data-id="{{ $att->id }}">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></div>
                                    <div class="min-w-0">
                                        @if ($att->url)
                                            <a href="{{ $att->url }}" target="_blank" class="block truncate text-sm font-semibold text-blue-700 hover:underline dark:text-blue-300">{{ $att->display_name }}</a>
                                        @else
                                            <span class="block truncate text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $att->display_name }} (no link)</span>
                                        @endif
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ strtoupper($att->extention ?? '-') }}
                                            @if (!empty($att->size))
                                                &bull; {{ number_format($att->size / 1024, 0) }} KB
                                            @endif
                                            @if (!empty($att->created_at))
                                                &bull; {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                    class="removeAttachment2 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
                                    aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </div>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                No existing attachments.
                            </div>
                        @endforelse
                    </div>

                    <div class="flex flex-col px-6 pt-5">
                        <div id="attachmentsContainer">
                            <div class="attachment-row flex items-center gap-2">
                                <input type="file" name="attachments[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addAttachment"
                        class="mx-6 mb-6 mt-4 flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add Attachment
                    </button>
                </details>

                <div class="flex flex-row items-center justify-between gap-4 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                    <button id="backBtn" type="button" onclick="history.back()"
                        class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        <span>Back</span>
                    </button>
                    <div class="flex flex-row items-center gap-3">
                        <button id="cancelBtn" type="button"
                            class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn"
                            class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Submit Approval</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }

        function toggleDeleteButton() {
            if ($('.attachment-row').length > 1) $('.removeAttachment').removeClass('hidden');
            else $('.removeAttachment').addClass('hidden');
        }

        $(document).on('click', '#addAttachment', function() {
            $('#attachmentsContainer').append(`
                <div class="attachment-row mt-2 flex items-center gap-2">
                    <input type="file" name="attachments[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                    <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                </div>
            `);
            toggleDeleteButton();
        });

        $(document).on('click', '.removeAttachment', function() {
            $(this).closest('.attachment-row').remove();
            toggleDeleteButton();
        });

        $('#cancelBtn').on('click', function() {
            window.location.href = "/itemreq";
        });

        $('#itemreqForm').on('submit', function(e) {
            e.preventDefault();

            // =========================================
            // ✅ ATTACHMENT REQUIRED (EDIT VERSION)
            // =========================================

            let hasNewFile = false;

            $('#attachmentsContainer input[type="file"]').each(function() {
                if (this.files && this.files.length > 0) {
                    hasNewFile = true;
                    return false;
                }
            });

            const existingCount = $('#attachmentsList .attachment-row').length;

            if (!hasNewFile && existingCount === 0) {
                toastr.error('Minimal 1 attachment wajib tersedia.');

                const $firstFile = $('#attachmentsContainer input[type="file"]').first();

                if ($firstFile.length) {
                    $firstFile.addClass('is-invalid');
                    $('html,body').animate({
                        scrollTop: $firstFile.offset().top - 120
                    }, 300);
                }

                return;
            }

            // =========================================

            $('#submitBtn, #cancelBtn').prop('disabled', true);
            $('#btnText').text('Processing...');
            showOverlay('Updating');

            const formData = new FormData(document.getElementById('itemreqForm'));
            formData.append('_method', 'PUT'); // 👈 PENTING

            $.ajax({
                    url: "{{ route('itemreq.update', $hash) }}",
                    type: "POST", // bisa PUT, tapi ajax pakai POST + _method
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(res) {
                    toastr.success(res.message || "Item Request updated successfully!");
                    window.location.href = "/itemreq";
                })
                .fail(function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let msg = 'Mohon periksa input:<br>';
                        Object.keys(xhr.responseJSON.errors).forEach(k => {
                            msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                        });
                        toastr.error(msg);
                    } else if (xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error! Please check the input.');
                    }
                })
                .always(function() {
                    $('#submitBtn, #cancelBtn').prop('disabled', false);
                    $('#btnText').text('Update');
                    hideOverlay();
                });
        });

        $(document).on('change', '#attachmentsContainer input[type="file"]', function() {
            if (this.files.length > 0) {
                $(this).removeClass('is-invalid');
            }
        });

        $(function() {
            toggleDeleteButton();
            $('select[name="cpny_id"], #department_id, select[name="inventory_type"]').select2({
                width: '100%',
                minimumResultsForSearch: 8,
                dropdownCssClass: 'sf-s2-dropdown'
            });
        });
    </script>

    <script>
        $(document).on('click', '.removeAttachment2', function() {
            const $btn = $(this);
            const $row = $btn.closest('.attachment-row');
            const attachmentId = $row.data('id');

            if (!attachmentId) {
                toastr.error('Attachment ID tidak ditemukan.');
                return;
            }

            if (!confirm('Are you sure you want to remove this attachment?')) return;

            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span class="ml-2">Removing...</span>
            `);

            $.ajax({
                    url: "/remove-attachment/" + attachmentId,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        $row.slideUp(180, function() {
                            $(this).remove();
                        });
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(res?.message || 'Failed to remove attachment.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    toastr.error('Error! Unable to remove attachment.');
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });
    </script>

</x-app-layout>
