<x-app-layout>
    <style>
        .is-invalid {
            border-color: #ef4444 !important;
        }

        .error-feedback {
            display: block;
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
        }

        .req::after {
            content: " *";
            color: #dc2626;
            font-weight: 700;
        }

        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        #loadingSpinnerContainer .loading-card {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            animation: spin 1s linear infinite;
            position: relative;
        }

        #loadingSpinnerContainer .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;
            animation: spinReverse .75s linear infinite;
        }

        #loadingSpinnerContainer .loading-text {
            color: #e5e7eb;
            font-weight: 600;
            letter-spacing: .02em;
        }

        #loadingSpinnerContainer .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3) {
            animation-delay: .4s;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinReverse {
            to {
                transform: rotate(-360deg);
            }
        }

        @keyframes blink {
            0% {
                opacity: .3;
                transform: translateY(0);
            }

            20% {
                opacity: 1;
                transform: translateY(-2px);
            }

            100% {
                opacity: .3;
                transform: translateY(0);
            }
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <form id="itemreqForm" class="flex flex-col gap-6" enctype="multipart/form-data">
            @csrf

            {{-- HEADER --}}
            <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                        Edit Item Request - {{ $itemReq->irid }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

                    {{-- Company --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                        <select name="cpny_id"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpny_id }}"
                                    {{ $p->cpny_id == $itemReq->cpny_id ? 'selected' : '' }}>
                                    {{ $p->cpny_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-xs font-medium text-gray-700 dark:text-gray-300">Department</label>
                        <select name="department_id" id="department_id"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->department_id }}"
                                    {{ $p->department_id == $itemReq->department_id ? 'selected' : '' }}>
                                    {{ $p->department_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Inventory Type --}}
                    <div class="flex flex-col gap-2">
                        <label class="req block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Inventory Type
                        </label>
                        <select name="inventory_type"
                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            required>
                            <option value="">-- Select Type --</option>
                            <option value="STOCK"
                                {{ old('inventory_type', $itemReq->inventory_type) === 'STOCK' ? 'selected' : '' }}>
                                STOCK
                            </option>
                            <option value="NONSTOCK"
                                {{ old('inventory_type', $itemReq->inventory_type) === 'NONSTOCK' ? 'selected' : '' }}>
                                NON STOCK
                            </option>
                        </select>
                    </div>

                    {{-- Inventory Description (diperkecil supaya 1 baris) --}}
                    <div class="flex flex-col gap-2">
                        <label for="inventory_descr_req"
                            class="req block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Inventory Description
                        </label>
                        <textarea name="inventory_descr_req" id="inventory_descr_req" rows="3" required
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ old('inventory_descr_req', $itemReq->inventory_descr_req) }}</textarea>
                    </div>

                </div>

            </div>

            {{-- ATTACHMENTS --}}
            <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                <details class="group" open>
                    <summary
                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                        <span>Attachments</span>
                        <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                            details &rarr;</span>
                        <span class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                            details &darr;</span>
                    </summary>

                    {{-- Existing attachments (signed URL) --}}
                    <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                        @forelse ($attachments as $att)
                            <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                data-id="{{ $att->id }}">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        📎</div>
                                    <div class="min-w-0">
                                        @if ($att->url)
                                            <a href="{{ $att->url }}" target="_blank"
                                                class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                                {{ $att->display_name }}
                                            </a>
                                        @else
                                            <span class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                {{ $att->display_name }} (no link)
                                            </span>
                                        @endif
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ strtoupper($att->extention ?? '-') }}
                                            @if (!empty($att->size))
                                                • {{ number_format($att->size / 1024, 0) }} KB
                                            @endif
                                            @if (!empty($att->created_at))
                                                •
                                                {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                    class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                    aria-label="Remove attachment">
                                    🗑️
                                </button>
                            </div>
                        @empty
                            <div
                                class="rounded-lg border border-dashed border-gray-300 p-4 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                No existing attachments.
                            </div>
                        @endforelse
                    </div>

                    {{-- Upload baru --}}
                    <div id="attachmentsContainer" class="mt-6">
                        <div class="attachment-row flex items-center gap-2">
                            <input type="file" name="attachments[]"
                                class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                            <button type="button"
                                class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                🗑️
                            </button>
                        </div>
                    </div>

                    <button type="button" id="addAttachment"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add Attachment
                    </button>
                </details>

                <div class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                    <button id="backBtn" onclick="history.back()"
                        class="flex items-center justify-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Back</span>
                    </button>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <button id="cancelBtn" type="button"
                            class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn"
                            class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                <div class="attachment-row flex items-center gap-2">
                    <input type="file" name="attachments[]"
                        class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700
                               file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700
                               hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button"
                        class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        🗑️
                    </button>
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

        $(function() {
            toggleDeleteButton();
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
