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

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <form id="itemreqForm" class="flex flex-col gap-6" enctype="multipart/form-data">
            @csrf

            {{-- HEADER --}}
            <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create Item Request</h2>
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
                                    {{ $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
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
                                    {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
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
                            <option value="STOCK">STOCK</option>
                            <option value="NONSTOCK">NON STOCK</option>
                        </select>
                    </div>

                    {{-- Inventory Description --}}
                    <div class="flex flex-col gap-2">
                        <label for="inventory_descr_req"
                            class="req block text-xs font-medium text-gray-700 dark:text-gray-300">
                            Inventory Description
                        </label>
                        <textarea name="inventory_descr_req" id="inventory_descr_req" rows="3" required
                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                    </div>

                </div>

            </div>

            {{-- ATTACHMENTS --}}
            <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                <details class="group" open>
                    <summary
                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                        <span>Attachments</span>
                        <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See details
                            →</span>
                        <span class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                            details ↓</span>
                    </summary>

                    <div class="flex flex-col pt-6">
                        <div id="attachmentsContainer">
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Add Attachment
                        </button>
                    </div>
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
                        <button type="submit" id="submitBtn"
                            class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Submit Approval</span>
                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
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

        // attachments
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

        // cancel
        $('#cancelBtn').on('click', function() {
            window.location.href = "/itemreq"; // sesuaikan bila route list kamu berbeda
        });

        // submit
        $('#itemreqForm').on('submit', function(e) {
            e.preventDefault();

            $('#submitBtn, #cancelBtn').prop('disabled', true);
            $('#btnText').text('Processing...');
            showOverlay('Submitting');

            const formData = new FormData(document.getElementById('itemreqForm'));

            $.ajax({
                    url: "{{ route('itemreq.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function(res) {
                    toastr.success(res.message || "Item Request created successfully!");
                    window.location.href = "/itemreq"; // sesuaikan route list
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
                    $('#btnText').text('Submit');
                    hideOverlay();
                });
        });

        // init
        $(function() {
            toggleDeleteButton();
        });
    </script>
</x-app-layout>
