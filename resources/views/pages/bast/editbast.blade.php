<x-app-layout>
    <x-spp-form-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="bastForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf

                    {{-- penting: hash id bast untuk update --}}
                    <input type="hidden" name="hash" value="{{ $hash }}">
                    <input type="hidden" name="ponbr" value="{{ $bast->ponbr }}">

                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Edit BAST</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Update the location, photos and files.</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 px-6 py-5">

                        @php
                            $labelClass = 'block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                            $valueClass = 'mt-0.5 block text-sm font-medium text-gray-800 dark:text-gray-100';
                        @endphp

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <span class="{{ $labelClass }}">BAST ID</span>
                                <span class="{{ $valueClass }}">{{ $bast->bastid }}</span>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">PO Nbr</span>
                                <span class="{{ $valueClass }}">{{ $bast->ponbr }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Company</span>
                                <span class="{{ $valueClass }}">{{ $bast->cpny_id }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Department</span>
                                <span class="{{ $valueClass }}">{{ $bast->department_id }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Start Date</span>
                                <span class="{{ $valueClass }}">
                                    {{ $bast->startdate ? \Carbon\Carbon::parse($bast->startdate)->format('Y-m-d') : '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">End Date</span>
                                <span class="{{ $valueClass }}">
                                    {{ $bast->enddate ? \Carbon\Carbon::parse($bast->enddate)->format('Y-m-d') : '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">User Peminta</span>
                                <span class="{{ $valueClass }}">{{ $bast->user_peminta }}</span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>

                        <!-- Row 2 -->
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <span class="{{ $labelClass }}">CS ID</span>
                                <span class="{{ $valueClass }}">{{ $bast->csid }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">SPPB/J/K/T</span>
                                <span class="{{ $valueClass }}">{{ $bast->sppbjktid }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Vendor</span>
                                <span class="{{ $valueClass }}">{{ $bast->vendorname }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Progress %</span>
                                <span class="{{ $valueClass }}">{{ $bast->progress_pct }}%</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Payment %</span>
                                <span class="{{ $valueClass }}">{{ $bast->payment_pct }}%</span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>

                        <!-- Row 3 -->
                        <div class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2">
                            <div>
                                <span class="{{ $labelClass }}">Keperluan</span>
                                <span class="{{ $valueClass }}">{{ $bast->keperluan }}</span>
                            </div>

                            <!-- Location -->
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="{{ $labelClass }}">Location</span>

                                <span id="lokasi_display" class="{{ $valueClass }}">
                                    @if ($bast->location_id && $bast->sub_location_id)
                                        {{ optional($bast->location)->location_name ?? $bast->location_id }}
                                        —
                                        {{ optional($bast->subLocation)->sub_location_name ?? $bast->sub_location_id }}
                                    @else
                                        Pilih Location & Sub Location
                                    @endif
                                </span>

                                <button type="button" id="btnLokasi"
                                    class="inline-flex h-8 items-center rounded-lg border border-blue-100 bg-blue-50 px-3 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                                    Change
                                </button>

                                <input type="hidden" name="location_id" id="location_id"
                                    value="{{ $bast->location_id }}">
                                <input type="hidden" name="sub_location_id" id="sub_location_id"
                                    value="{{ $bast->sub_location_id }}">
                            </div>
                        </div>
                        </div>
                    </div>

                    {{-- ===== Photo Before (from BQ = bqid) ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Photo Before</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Condition before the work (from the BQ)</p>
                            </div>
                        </div>

                        <div class="px-6 py-5">
                            <div id="bqAttachmentGrid"
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                            <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                                Loading...
                            </p>
                        </div>
                        </div>
                    </div>

                    {{-- ===== Photo After (existing + add new) ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                                    <span>
                                        <span class="block text-base font-bold text-gray-800 dark:text-white">Photo After</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Condition after the work</span>
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                                <div class="px-6 py-5">

                                {{-- Existing Photo After --}}
                                <div class="mb-4">
                                    <div class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Existing
                                        Photo After</div>
                                    <div id="afterExistingGrid"
                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                        <p
                                            class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                                            Loading...</p>
                                    </div>
                                </div>

                                <div class="flex h-auto flex-col justify-start">
                                    <div id="hiddenInputs"></div>
                                    <input type="file" id="hiddenPicker" class="hidden" accept="image/*" multiple>

                                    <div id="attachmentsGrid"
                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                        <button type="button" id="addAttachmentTile"
                                            class="flex aspect-[4/3] items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 text-gray-500 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 dark:border-gray-600 dark:bg-gray-700/40 dark:text-gray-400">
                                            <div class="flex flex-col items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-sm font-medium">Add Photo</span>
                                            </div>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Accepted: JPG/PNG, maks 5 MB per foto.</p>
                                </div>
                                </div>
                            </details>
                        </div>
                    </div>

                    <!-- Modal Lokasi -->
                    <div id="modalLokasi"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <!-- Header -->
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Location & Sub
                                    Location</h3>
                                <button type="button" id="closeLokasi"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <!-- Body -->
                            <div class="py-1">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <!-- Location -->
                                    <div class="sf-field">
                                        <label
                                            class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</label>
                                        <select id="modal_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <option value="">Select</option>
                                        </select>
                                        <small class="mt-1.5 block text-xs text-gray-400 dark:text-gray-500">Wajib
                                            memilih Location.</small>
                                    </div>

                                    <!-- Sub Location -->
                                    <div class="sf-field">
                                        <label
                                            class="req mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sub
                                            Location</label>
                                        <select id="modal_sub_location_id"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <option value="">Select</option>
                                        </select>
                                        <small class="mt-1.5 block text-xs text-gray-400 dark:text-gray-500">Wajib
                                            memilih sub location.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="-mx-6 -mb-6 mt-5 flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                                <button type="button" id="cancelLokasi"
                                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    Cancel
                                </button>
                                <button type="button" id="saveLokasi"
                                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Attachments BA (existing + add new) ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                    <span>
                                        <span class="block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                    </span>
                                </span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                            <div class="flex flex-col gap-3 px-6 py-5">

                            <div class="flex flex-col gap-3">
                                <div id="baExistingList" class="flex flex-col gap-3">
                                    <p class="py-3 text-center italic text-gray-500 dark:text-gray-400">Loading...</p>
                                </div>

                                <div class="flex flex-col gap-3">
                                    <div class="flex flex-col gap-3" id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments_ba[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                    </div>

                                    <button type="button" id="addAttachment"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Add Attachment
                                </button>
                                </div>
                            </div>
                            </div>
                        </details>

                        <div class="flex flex-row flex-wrap items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                            {{-- <a href="{{ url()->previous() }}"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">Cancel</a> --}}
                            <button type="button" id="cancelBastBtn"
                                class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                Cancel
                            </button>
                            <button type="submit" id="submitBtn"
                                class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Update</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Overlay HTML ===== --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading" class="hidden">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    {{-- Overlay helpers --}}
    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.removeClass('hidden');
            $ov.find('.loading-text').html((text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>');
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120, function() {
                $(this).addClass('hidden');
            });
        }
    </script>

    {{-- VALIDATION HELPERS + SUBMIT --}}
    <script>
        $(function() {
            function clearFormErrors() {
                $('#bastForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#bastForm .error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }

            $(document).on('input change', '#bastForm input, #bastForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            // dynamic add attachments_ba
            $('#addAttachment').on('click', function() {
                $('#attachmentsContainer').append(
                    '<div class="attachment-row flex items-center gap-2">' +
                    '<input type="file" name="attachments_ba[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">' +
                    '<button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' +
                    '</div>'
                );
            });
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
            });

            $('#bastForm').on('submit', function(e) {
                e.preventDefault();
                clearFormErrors();

                const lokasiVal = $('#lokasi_display').text().trim();
                if (!lokasiVal || lokasiVal.includes('Pilih Location')) {
                    toastr.error('Location & Sub Location wajib dipilih.');
                    return;
                }

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Updating');

                const formData = new FormData(document.getElementById('bastForm'));
                formData.append('_method', 'PUT'); // ✅ ini penting

                $.ajax({
                        url: "{{ route('bast.update', ['hash' => $hash]) }}",
                        type: "POST", // tetap POST karena FormData + spoof PUT
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('input[name="_token"]').val()
                        }
                    })

                    .done(function(res) {
                        toastr.success(res.message || 'Bast updated successfully!');
                        window.location.href = "/bastlist";
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(xhr.responseJSON.errors).forEach(k => {
                                msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                            });
                            toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error! Please check the input.');
                        }
                    })
                    .always(function() {
                        $('#submitBtn').prop('disabled', false);
                        $('#btnText').text('Update');
                        hideOverlay();
                    });
            });
        });
    </script>

    {{-- Photo Before grid (doctype BQ, refnbr = bqid) --}}
    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bast->bqid]));
            const $grid = $('#bqAttachmentGrid');

            function cardTpl(at) {
                const name = at.name || at.display_name || '(no name)';
                const by = at.created_user ?? at.created_by ?? '-';
                const dateStr = at.created_at ? dayjs(at.created_at).format("DD MMM 'YY") : '-';
                const ext = (at.extention || '').toLowerCase();
                const href = at.url || '#';
                const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext);

                const thumb = isImg && at.url ?
                    `<img src="${href}" alt="${name}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" referrerpolicy="no-referrer">` :
                    `<div class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                        <span class="text-lg">${ ext === 'pdf' ? '📕' : '📄' }</span>
                       </div>`;

                return `
                    <div class="group relative flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-gray-500 dark:border-gray-700 dark:bg-gray-800 min-w-[120px]">
                        <a ${at.url ? `href="${href}" target="_blank"` : ''} class="relative block aspect-square overflow-hidden">
                            ${thumb}
                            <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>
                        </a>
                        <div class="px-2 py-2">
                            <div class="truncate text-sm font-medium text-gray-900 dark:text-gray-100" title="${name}">
                                ${name}${ext ? `<span class="text-gray-400">.${ext}</span>` : ''}
                            </div>
                            <div class="mt-0.5 space-y-0.5">
                                <div class="truncate text-[11px] text-gray-500 dark:text-gray-400" title="${by}">${by}</div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">${dateStr}</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderGrid(rows) {
                $grid.empty();
                if (!rows || !rows.length) {
                    $grid.append(
                        `<p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">No attachments found.</p>`
                        );
                    return;
                }
                rows.forEach(at => $grid.append(cardTpl(at)));
            }

            function refresh() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderGrid(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            refresh();
        });
    </script>

    {{-- Existing Photo After (doctype BQ, refnbr = bastid) --}}
    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $bast->bastid]));
            const $grid = $('#afterExistingGrid');

            function tpl(at) {
                const name = at.name || at.display_name || '(no name)';
                const href = at.url || '#';
                const ext = (at.extention || '').toLowerCase();
                const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext);

                const thumb = isImg && at.url ?
                    `<img src="${href}" class="h-full w-full object-cover" loading="lazy" referrerpolicy="no-referrer">` :
                    `<div class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700"><span class="text-lg">📄</span></div>`;

                return `
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <a ${at.url ? `href="${href}" target="_blank"` : ''} class="block aspect-square overflow-hidden">
                            ${thumb}
                        </a>
                        <div class="px-2 py-1 text-[11px] truncate text-gray-600 dark:text-gray-300" title="${name}">${name}</div>
                    </div>
                `;
            }

            function render(rows) {
                $grid.empty();
                if (!rows || !rows.length) {
                    $grid.append(
                        `<p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">No photo after uploaded.</p>`
                        );
                    return;
                }
                rows.forEach(at => $grid.append(tpl(at)));
            }

            $.get(listUrl)
                .done(res => {
                    if (res.success) render(res.attachments);
                    else toastr.error(res.message || 'Failed to load photo after.');
                })
                .fail(() => toastr.error('Failed to load photo after.'));
        });
    </script>

    {{-- Existing Attachments BA list (doctype BA, refnbr = bastid) --}}
    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'BA', 'refnbr' => $bast->bastid]));
            const $wrap = $('#baExistingList');

            function render(rows) {
                $wrap.empty();
                if (!rows || !rows.length) {
                    $wrap.append(
                        `<p class="py-3 text-center italic text-gray-500 dark:text-gray-400">No attachments.</p>`
                        );
                    return;
                }

                rows.forEach(at => {
                    const name = at.name || at.display_name || '(no name)';
                    const by = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm') : '-';
                    const href = at.url || '#';

                    $wrap.append(`
                        <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">📎</div>
                                <div class="min-w-0">
                                    ${at.url ? `<a href="${href}" target="_blank" class="block truncate font-medium text-blue-700 hover:underline dark:text-blue-300">${name}</a>` : `<span class="block truncate font-medium text-gray-700 dark:text-gray-200">${name} (no link)</span>`}
                                    <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">by ${by} • ${dateStr}</div>
                                </div>
                            </div>
                        </div>
                    `);
                });
            }

            $.get(listUrl)
                .done(res => {
                    if (res.success) render(res.attachments);
                    else toastr.error(res.message || 'Failed to load attachments.');
                })
                .fail(() => toastr.error('Failed to load attachments.'));
        });
    </script>

    {{-- Photo After picker -> create hidden file inputs attachments[] --}}
    <script>
        (function() {
            const grid = document.getElementById('attachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            if (!grid || !addTile || !picker || !hiddenInputs) return;

            const MAX_SIZE = 5 * 1024 * 1024; // 5MB
            const MAX_FILES = 24;
            const chosenKeys = new Set();

            addTile.addEventListener('click', () => picker.click());

            picker.addEventListener('change', function() {
                const files = Array.from(this.files || []);
                files.forEach(file => tryAddFile(file));
                this.value = '';
            });

            function tryAddFile(file) {
                if (!file || !file.type.startsWith('image/')) {
                    toastr?.error?.('File bukan gambar.');
                    return;
                }
                if (file.size > MAX_SIZE) {
                    toastr?.error?.(`Ukuran melebihi 5MB: ${file.name}`);
                    return;
                }
                if (hiddenInputs.querySelectorAll('input[type="file"][name="attachments[]"]').length >= MAX_FILES) {
                    toastr?.error?.(`Maksimal ${MAX_FILES} foto.`);
                    return;
                }
                const key = `${file.name}::${file.size}`;
                if (chosenKeys.has(key)) {
                    toastr?.warning?.(`Lewati duplikat: ${file.name}`);
                    return;
                }
                chosenKeys.add(key);
                addPhotoCard(file, key);
            }

            function addPhotoCard(file, key) {
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'attachments[]';
                input.accept = 'image/*';
                input.className = 'hidden';

                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;

                const id = 'att_' + Math.random().toString(36).slice(2);
                input.dataset.ref = id;
                hiddenInputs.appendChild(input);

                const url = URL.createObjectURL(file);
                const card = document.createElement('div');
                card.className = 'relative group rounded-xl border overflow-hidden';
                card.dataset.ref = id;

                card.innerHTML = `
                    <div class="relative aspect-[4/3] overflow-hidden">
                        <img src="${url}" alt="attachment" class="absolute inset-0 h-full w-full object-cover transition group-hover:scale-105" />
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
                    </div>
                    <button type="button" title="Remove"
                        class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">✕</button>
                `;

                card.querySelector('button').addEventListener('click', function() {
                    var ref = card.getAttribute('data-ref');
                    var hidden = hiddenInputs.querySelector('input[data-ref="' + ref + '"]');
                    if (hidden) hidden.remove();

                    chosenKeys.delete(key);
                    URL.revokeObjectURL(url);
                    card.remove();
                });


                grid.insertBefore(card, addTile);
            }
        })();
    </script>

    {{-- Location modal (cpny fix dari bast) --}}
    <script>
        $(function() {
            const cpny = @json($bast->cpny_id);

            // popup dropdowns: same Select2 look as the other forms; the list has to live inside the popup
            $('#modal_location_id, #modal_sub_location_id').select2({
                width: '100%',
                dropdownParent: $('#modalLokasi'),
                dropdownCssClass: 'sf-s2-dropdown'
            });

            function openLokasiModal() {
                $('#modalLokasi').removeClass('hidden').addClass('flex');
            }

            function closeLokasiModal() {
                $('#modalLokasi').addClass('hidden').removeClass('flex');
            }

            $('#btnLokasi').on('click', function() {
                $('#modal_location_id').empty().append('<option value="">Select</option>').trigger('change.select2');
                $('#modal_sub_location_id').empty().append('<option value="">Select</option>').trigger('change.select2');

                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`, function(list) {
                    list.forEach(it => $('#modal_location_id').append(new Option(it.text, it
                        .value)));

                    // preselect current
                    const currentLoc = @json($bast->location_id);
                    if (currentLoc) $('#modal_location_id').val(currentLoc).trigger('change');

                    openLokasiModal();
                });
            });

            $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

            $('#modal_location_id').on('change', function() {
                const loc = $(this).val();
                const $sub = $('#modal_sub_location_id');
                $sub.empty().append('<option value="">Select</option>').trigger('change.select2');
                if (!loc) return;

                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`,
                    function(list) {
                        list.forEach(it => $sub.append(new Option(it.text, it.value)));

                        // preselect current subloc (only if loc matches)
                        const currentSub = @json($bast->sub_location_id);
                        if (currentSub) $sub.val(currentSub).trigger('change.select2');
                    });
            });

            $('#saveLokasi').on('click', function() {
                const locVal = $('#modal_location_id').val();
                const locTxt = $('#modal_location_id option:selected').text();
                const subVal = $('#modal_sub_location_id').val();
                const subTxt = $('#modal_sub_location_id option:selected').text();

                if (!locVal || !subVal) {
                    toastr.error('Pilih Location dan Sub Location.');
                    return;
                }

                $('#location_id').val(locVal);
                $('#sub_location_id').val(subVal);
                $('#lokasi_display').text(`${locTxt} — ${subTxt}`);
                closeLokasiModal();
            });
        });
    </script>
    <script>
        $(function () {
            $('#cancelBastBtn').on('click', function () {
                const $btn = $(this);

                if (!confirm('Apakah Anda yakin ingin cancel BAST ini? Status BAST akan menjadi Cancel dan PO Term bisa dibuat BAST kembali.')) {
                    return;
                }

                $btn.prop('disabled', true).text('Cancelling...');
                showOverlay('Cancelling');

                $.ajax({
                    url: "{{ route('bast.cancel', ['hash' => $hash]) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                })
                .done(function (res) {
                    if (res && res.success) {
                        toastr.success(res.message || 'BAST berhasil di-cancel.');
                        window.location.href = "/bastlist";
                    } else {
                        toastr.error(res?.message || 'Gagal cancel BAST.');
                        $btn.prop('disabled', false).text('Cancel');
                    }
                })
                .fail(function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error! Gagal cancel BAST.');
                    }

                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).text('Cancel');
                })
                .always(function () {
                    hideOverlay();
                });
            });
        });
    </script>
</x-app-layout>
