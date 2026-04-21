<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="csForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="doc" value="{{ $doc }}">
                    <input type="hidden" name="src_id" value="{{ $src_id }}">
                    <input type="hidden" name="sppbjktid" value="{{ $refnbr }}">
                    <input type="hidden" name="cpny_id" value="{{ $header->cpny_id }}">
                    <input type="hidden" name="department_id" value="{{ $header->department_id }}">
                    <input type="hidden" name="bqid" value="{{ $bqid ?? ($header->bqid ?? '') }}">
                    <input type="hidden" name="woid" value="{{ $header->woid ?? '' }}">
                    <input type="hidden" name="spbid" value="{{ $header->spbid ?? '' }}">
                    <input type="hidden" name="keperluan" value="{{ $header->keperluan ?? '' }}">
                    <input type="hidden" name="bqtype" value="{{ $bqtype ?? ($header->bqtype ?? '') }}">
                    <input type="hidden" name="budget_perpost"
                        value="{{ $budget_perpost ?? ($header->budget_perpost ?? '') }}">
                    <input type="hidden" name="user_peminta"
                        value="{{ $user_peminta ?? ($header->created_by ?? '') }}">
                    <input type="hidden" name="assigndate" value="{{ $header->assigndate ?? '' }}">
                    <input type="hidden" name="prev_csid" value="{{ $prev_csid ?? ($poHeader->csid ?? '') }}">

                    <div
                        class="flex w-full flex-col gap-4 rounded-2xl bg-white px-8 py-6 text-xs shadow-sm dark:bg-gray-900">
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="font-bold text-gray-800 dark:text-white">
                                @if ($doc === 'PO')
                                    Create CS Reuse for PO - {{ $docno }}
                                @elseif ($doc === 'KONTRAK')
                                    Create CS for Kontrak - {{ $docno }}
                                @else
                                    Create CS
                                @endif
                            </h2>
                        </div>

                        @php
                            $labelClass = 'font-semibold text-gray-800 dark:text-gray-200';
                            $valueClass = 'text-gray-600 dark:text-gray-400';
                        @endphp

                        <div class="grid grid-cols-1 gap-y-3 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <span class="{{ $labelClass }}">SPPB/J/K/T ID:</span>
                                @if (!empty($sourceShowUrl))
                                    <a href="{{ $sourceShowUrl }}" target="_blank"
                                        class="ml-1 text-indigo-600 underline hover:text-indigo-800">
                                        {{ $refnbr }}
                                    </a>
                                @else
                                    <span class="{{ $valueClass }}">{{ $refnbr }}</span>
                                @endif
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">User:</span>
                                <span class="{{ $valueClass }}">
                                    {{ ucwords(strtolower(optional($header->creator)->name)) }}
                                </span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Company:</span>
                                <span class="{{ $valueClass }}">{{ $header->cpny_id }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Department:</span>
                                <span class="{{ $valueClass }}">{{ $header->department_id }}</span>
                            </div>

                            <div>
                                <span class="{{ $labelClass }}">Purchaser:</span>
                                <span class="{{ $valueClass }}">
                                    {{ ucwords(strtolower(optional($header->purchaser)->name)) }}
                                </span>
                            </div>

                            @if (in_array($doc, ['SPPJ', 'SPPT']))
                                <div>
                                    <span class="{{ $labelClass }}">BQ ID:</span>
                                    <span class="{{ $valueClass }}">{{ $header->bqid }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-800"></div>

                        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    <span class="{{ $labelClass }}">Vendor:</span>

                                    <select id="vendorSelect"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs text-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                        <option value="">Select</option>
                                    </select>

                                    <span class="text-gray-500 dark:text-gray-400">
                                        Vendor can be selected more than once.
                                    </span>
                                </div>

                                <div class="flex w-full flex-col gap-2">
                                    <span class="{{ $labelClass }}">Purpose:</span>

                                    <div class="{{ $valueClass }} whitespace-pre-line break-words">
                                        {{ $header->keperluan }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <span class="{{ $labelClass }}">Note CS:</span>

                                <textarea name="csnote" id="csnote"
                                    class="min-h-[180px] w-full rounded-md border border-gray-300 bg-white p-4 text-xs text-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex w-full flex-col rounded-xl bg-white shadow-md dark:bg-gray-800">
                        <div class="p-4">
                            <div
                                class="border-b border-gray-200 pb-4 text-xs font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                CS Detail
                            </div>
                            <div class="mt-4 overflow-x-auto">
                                <table id="cvTable"
                                    class="w-max table-auto border text-xs text-gray-700 dark:text-gray-200">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="w-64 border px-3 py-2">Inventory Descr</th>
                                            <th class="w-20 border px-3 py-2 text-center">Qty</th>
                                            <th class="w-20 border px-3 py-2 text-center">UOM</th>
                                            <th class="w-40 border px-3 py-2 text-center">Note</th>
                                            <th class="w-32 border px-3 py-2 text-center">Last Price</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cvBody">
                                        @foreach ($items as $row)
                                            <tr data-inventoryid="{{ $row->inventoryid ?? '' }}"
                                                data-inventory_descr="{{ $row->inventory_descr }}"
                                                data-uom="{{ $row->uom }}"
                                                data-lastprice="{{ (float) ($row->last_unitcost ?? 0) }}"
                                                data-original_qty="{{ (float) $row->qty }}"
                                                data-note="{{ $row->csnote_detail ?? '' }}"
                                                data-sppb_no="{{ $row->sppb_no ?? '' }}"
                                                data-sppj_no="{{ $row->sppj_no ?? '' }}"
                                                data-sppk_no="{{ $row->sppk_no ?? '' }}"
                                                data-sppt_no="{{ $row->sppt_no ?? '' }}">
                                                <td class="border px-3 py-2 align-top">
                                                    <div class="flex flex-col gap-1">
                                                        <span class="font-medium text-gray-800 dark:text-gray-100">
                                                            {{ $row->inventory_descr ?? '-' }}
                                                        </span>

                                                        @if (!empty($row->inventory_sub_type) || !empty($row->inventory_category))
                                                            <div class="text-xs text-gray-400">
                                                                {{ $row->inventory_sub_type ?? '-' }}
                                                                @if (!empty($row->inventory_sub_type) && !empty($row->inventory_category))
                                                                    -
                                                                @endif
                                                                {{ $row->inventory_category ?? '-' }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td class="border px-3 py-2 text-center">
                                                    <input type="text"
                                                        class="qty-input w-full rounded-md border border-gray-400 px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                                        value="{{ number_format((float) $row->qty, 2, ',', '') }}"
                                                        inputmode="decimal" autocomplete="off" placeholder="0,00"
                                                        aria-label="Qty">
                                                </td>

                                                <td class="border px-3 py-2 text-center">{{ $row->uom }}</td>

                                                <td class="border px-3 py-2 text-center">
                                                    <textarea
                                                        class="note-input w-full resize-none rounded-md border border-gray-400 px-2 py-1 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                                        rows="2" autocomplete="off" placeholder="Add note..." aria-label="Note">{{ $row->note ?? '' }}</textarea>
                                                </td>

                                                <td class="border px-3 py-2 text-right font-semibold">
                                                    {{ number_format((float) ($row->last_unitcost ?? 0), 2, ',', '.') }}
                                                    <button type="button"
                                                        class="btn-lastprice inline-flex h-7 w-7 items-center justify-center rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                                        title="View Last Price History"
                                                        data-inventoryid="{{ $row->inventoryid }}"
                                                        data-inventorydescr="{{ $row->inventory_descr ?? '' }}">
                                                        🔍
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr id="summaryRow" class="bg-gray-50 dark:bg-gray-700">
                                            <td colspan="5" class="border px-3 py-2 text-right font-semibold">
                                                Summary
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="lastPriceModal" class="fixed inset-0 z-[4000] hidden">
                        <div id="lastPriceModalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                        <div
                            class="absolute left-1/2 top-1/2 w-[92vw] max-w-4xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl dark:bg-gray-800">
                            <div class="flex items-center justify-between border-b px-4 py-3 dark:border-gray-700">
                                <div class="flex flex-col">
                                    <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100">Last Price
                                        History</h3>
                                    <h3 id="lpTitle" class="text-xs font-semibold text-gray-800 dark:text-gray-100">
                                    </h3>
                                </div>
                                <button type="button" id="lastPriceModalClose"
                                    class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="p-4">
                                <div id="lpLoading" class="mb-3 hidden text-xs text-gray-600 dark:text-gray-300">
                                    Loading...
                                </div>

                                <div
                                    class="max-h-[60vh] overflow-auto rounded border border-gray-200 dark:border-gray-700">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-semibold">PO Nbr</th>
                                                <th class="px-3 py-2 text-left font-semibold">PO Date</th>
                                                <th class="px-3 py-2 text-left font-semibold">CS ID</th>
                                                <th class="px-3 py-2 text-left font-semibold">Vendor</th>
                                                <th class="px-3 py-2 text-right font-semibold">Unit Cost</th>
                                                <th class="px-3 py-2 text-left font-semibold">Purchaser</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lpBody"
                                            class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        </tbody>
                                    </table>
                                </div>

                                <div id="lpEmpty" class="mt-3 hidden text-xs text-gray-500 dark:text-gray-300">
                                    No history found.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                            <div
                                class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                                <h3 class="text-xs font-bold text-gray-800 dark:text-white">Attachments
                                    {{ $doc }}</h3>
                            </div>

                            @if (($attachment ?? collect())->count())
                                <div class="mt-4 overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead class="text-gray-600 dark:text-gray-300">
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="p-3 text-left font-semibold">Filename</th>
                                                <th class="p-3 text-left font-semibold">Created By</th>
                                                <th class="p-3 text-left font-semibold">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($attachment as $at)
                                                <tr
                                                    class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                    <td class="px-3 py-2">
                                                        @if (!empty($at->url))
                                                            <a href="{{ $at->url }}" target="_blank"
                                                                class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                                📎 {{ $at->display_name }}
                                                            </a>
                                                        @else
                                                            <span
                                                                class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-300">
                                                                📎 {{ $at->display_name }}
                                                            </span>
                                                            <span class="ml-2 text-xs text-red-500">(link
                                                                unavailable)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">{{ $at->created_by }}</td>
                                                    <td class="px-3 py-2">{{ $at->created_at }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Attachment Empty.</p>
                            @endif
                        </div>

                        <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                            <div
                                class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                                <h3 class="text-xs font-bold text-gray-800 dark:text-white">Attachments CS</h3>
                            </div>

                            <div class="flex flex-col pt-6" id="attachmentsContainer">
                                <div class="attachment-row flex items-center gap-2">
                                    <input type="file" name="attachments[]"
                                        class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                    <button type="button"
                                        class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Attachment
                            </button>

                            <div
                                class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                                <button id="backBtn" onclick="history.back()"
                                    class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    <span>Back</span>
                                </button>

                                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                    <button type="button" id="saveBtn"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-md bg-green-600 px-4 py-2 text-white md:w-auto">
                                        <span id="saveText">Save CS</span>
                                        <svg id="saveSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                            </path>
                                        </svg>
                                    </button>

                                    {{-- jika dipakai lagi nanti --}}
                                    {{-- <button type="submit" id="submitBtn"
                                        class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                        <span id="btnText">Submit Approval</span>
                                        <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                            </path>
                                        </svg>
                                    </button> --}}
                                </div>
                            </div>
                        </div>
                </form>
            </div>

            <div id="taxModal" class="fixed inset-0 z-[3000] hidden">
                <div id="taxModalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div
                    class="absolute left-1/2 top-1/2 w-[90vw] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b px-4 py-3 dark:border-gray-700">
                        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-100">Pilih Pajak</h3>
                        <button id="taxModalClose"
                            class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                    </div>
                    <div class="p-4">
                        <div class="mb-3 flex items-center gap-2">
                            <input id="taxSearch" type="text" placeholder="Cari taxid/descr..."
                                class="w-full rounded border border-gray-300 px-3 py-2 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        </div>
                        <div class="max-h-[55vh] overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">
                                            Tax ID</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">
                                            Rate (%)</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">
                                            Description</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody id="taxTableBody"
                                    class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                CS Created Successfully!
            </div>
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

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
    </script>

    <script>
        $(document).ready(function() {
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2">
                        <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                        <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
                    </div>
                `);
                toggleDeleteButton();
            });

            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4/lodash.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function parseQty(val) {
            if (typeof val !== 'string') val = String(val ?? '');
            val = val.trim();
            val = val.replace(/[^0-9.,]/g, '');

            const lastComma = val.lastIndexOf(',');
            const lastDot = val.lastIndexOf('.');
            const decimalSep = (lastComma > lastDot) ? ',' : '.';

            if (decimalSep === ',') {
                val = val.replace(/\./g, '');
                val = val.replace(',', '.');
            } else {
                val = val.replace(/,/g, '');
            }

            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        function formatQty2(val) {
            const n = isNaN(val) ? 0 : Number(val);
            return n.toFixed(2).replace('.', ',');
        }

        function parsePrice(val) {
            if (typeof val !== 'string') val = String(val ?? '');
            val = val.trim();
            val = val.replace(/[^0-9.,]/g, '');

            const lastComma = val.lastIndexOf(',');
            const lastDot = val.lastIndexOf('.');
            const decimalSep = (lastComma > lastDot) ? ',' : '.';

            if (decimalSep === ',') {
                val = val.replace(/\./g, '').replace(',', '.');
            } else {
                val = val.replace(/,/g, '');
            }

            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        function formatPrice2(n) {
            const num = isNaN(n) ? 0 : Number(n);
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }

        function formatNumID(n) {
            n = Number(n || 0);
            return n.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function numFromText(text) {
            if (!text) return 0;

            return parseFloat(
                String(text)
                .trim()
                .replace(/\./g, '')
                .replace(',', '.')
                .replace(/[^0-9.-]/g, '')
            ) || 0;
        }

        function round2(n) {
            return Math.round((+n + Number.EPSILON) * 100) / 100;
        }
    </script>

    <script>
        let vendorMaster = [];
        let vendorCount = 0;
        let vendorInstanceSeq = 0;

        function nextVendorColKey() {
            vendorInstanceSeq++;
            return 'vcol_' + vendorInstanceSeq;
        }

        function formatNum(n) {
            return (+n || 0).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function getVendorColumns() {
            return $('#cvTable thead th[id^="th-vendor-"]');
        }

        function recalcSummaryVendor(colKey) {
            colKey = String(colKey);

            const $sumCell = $(`#td-sum-${colKey}`);
            if (!$sumCell.length) return;

            let total = 0;
            let selBase = 0;

            $('#cvBody tr').each(function() {
                const $tr = $(this);
                const qty = parseQty($tr.find('.qty-input').val());
                const $priceInput = $tr.find(`input.price-input[data-col-key="${colKey}"]`);
                if (!$priceInput.length) return;

                const price = parsePrice($priceInput.val());
                const lineTotal = qty * price;

                total += lineTotal;

                const picked = String($tr.find('input.pick-vendor:checked').val() || '');
                if (picked === colKey) {
                    selBase += lineTotal;
                }

                $priceInput.closest('td').find('.total-label').text(formatNum(lineTotal));
            });

            $sumCell.find('.sum-total').text(formatNum(total));

            const ppn = Number($sumCell.find('.sum-ppn').val() || 0) / 100;
            const pph = Number($sumCell.find('.sum-pph').val() || 0) / 100;

            const grand = total + (total * ppn) + (total * pph);
            const selGrand = selBase + (selBase * ppn) + (selBase * pph);

            $sumCell.find('.sum-grand').text(formatNum(grand));
            $sumCell.find('.sum-selected').text(formatNum(selGrand));
            $sumCell.find('.sum-selected-base').text(String(selBase));
        }

        function recalcAllVendors() {
            getVendorColumns().each(function() {
                const colKey = String($(this).data('col-key'));
                recalcSummaryVendor(colKey);
            });
        }

        window.calcCellTotal = function($input) {
            const $tr = $input.closest('tr');
            const qty = parseQty($tr.find('.qty-input').val());
            const price = parsePrice($input.val());
            const total = qty * price;

            $input.closest('td').find('.total-label').text(formatNum(total));
            recalcSummaryVendor(String($input.data('col-key')));
        };

        function addHeader(colKey, v) {
            const TOPS = @json($tops->map(fn($t) => ['id' => $t->topid, 'name' => $t->top_name]));
            const TOPS_OPTIONS_HTML =
                '<option value="" disabled selected>Select TOP</option>' +
                TOPS.map(t => `<option value="${_.escape(String(t.id))}">${_.escape(t.name)}</option>`).join('');

            const safeColKey = _.escape(String(colKey));
            const safeVendorId = _.escape(String(v.id));
            const safeVendorCode = _.escape(String(v.vendor_id ?? ''));
            const safeVendorName = _.escape(String(v.vendor_name ?? ''));
            const safeVendorAddr = _.escape(String(v.vendor_addr1 ?? ''));
            const safeVendorPhone = _.escape(String(v.phone_number ?? ''));
            const safeVendorCp = _.escape(String(v.contact_person ?? ''));

            const duplicateNo = $(`#cvTable thead th[data-vendor-id="${String(v.id)}"]`).length + 1;
            const displayName = `${safeVendorName} (${duplicateNo})`;

            const $th = $(`
                <th id="th-vendor-${safeColKey}"
                    class="relative border px-3 py-2 align-top
                        w-72 max-w-xs sm:w-80 sm:max-w-sm md:w-96 md:max-w-md lg:w-[20rem]"
                    data-col-key="${safeColKey}"
                    data-vendor-id="${safeVendorId}"
                    data-vendor-code="${safeVendorCode}"
                    data-vendor-name="${safeVendorName}"
                    data-vendor-addr="${safeVendorAddr}"
                    data-vendor-phone="${safeVendorPhone}"
                    data-vendor-cp="${safeVendorCp}">
                    <div class="flex flex-col text-left text-xs">
                        <div class="flex items-center gap-1 font-bold text-gray-800 dark:text-gray-100 break-words">
                            <span>${displayName}</span>

                            <div class="relative group inline-block">
                                <div class="flex h-4 w-4 items-center justify-center rounded-full bg-gray-200 text-gray-700 text-[10px] dark:bg-gray-700 dark:text-gray-200 cursor-default">
                                    i
                                </div>

                                <div class="pointer-events-none absolute left-1/2 top-full z-50 mt-2
                                            w-64 -translate-x-1/2 rounded-md bg-gray-900 p-3 text-xs
                                            text-gray-200 shadow-lg opacity-0 invisible
                                            group-hover:opacity-100 group-hover:visible transition-opacity duration-200">
                                    <div class="font-semibold text-white mb-1">${safeVendorName}</div>
                                    <div class="space-y-1 text-gray-300 leading-4">
                                        <div>✉️ ${safeVendorCp || '-'}</div>
                                        <div>☎️ ${safeVendorPhone || '-'}</div>
                                        <div>🏠 ${safeVendorAddr || '-'}</div>
                                    </div>
                                    <div class="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Payment Term:</span>
                            <select name="cara_bayar_${safeColKey}"
                                class="cara-bayar w-40 rounded-full border border-gray-300 bg-white px-3 py-1
                                    text-xs font-medium shadow-sm focus:border-indigo-500 focus:ring
                                    focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                ${TOPS_OPTIONS_HTML}
                            </select>
                        </div>

                        <div class="mt-2">
                            <textarea id="vendornote_${safeColKey}" name="vendornote_${safeColKey}"
                                class="vendornote mt-1 w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-xs text-gray-900 shadow-sm
                                    focus:border-indigo-500 focus:ring focus:ring-indigo-500/50
                                    dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                rows="2" placeholder="Vendor Note"></textarea>
                        </div>
                    </div>

                    <button type="button"
                        class="btn-del absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs text-white shadow hover:bg-red-700"
                        data-col-key="${safeColKey}">
                        ✕
                    </button>
                </th>
            `);

            $('#cvTable thead tr').append($th);

            const $sumTd = $(`
                <td id="td-sum-${safeColKey}" class="border px-3 py-2 text-xs align-top" data-col-key="${safeColKey}">
                    <div class="flex flex-col gap-2 text-gray-700 dark:text-gray-200">
                        <div><span class="font-semibold">Total:</span> <span class="sum-total">0,00</span></div>

                        <div class="flex justify-between gap-2">
                            <div class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 dark:bg-gray-700">
                                <span class="text-xs font-medium whitespace-nowrap shrink-0 min-w-[25px]">PPN</span>
                                <input type="number"
                                    class="sum-ppn tax-input w-16 rounded border border-gray-300 px-1 text-right text-xs focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                                    value="11.00" step="0.01" min="0">
                                <button type="button"
                                    class="btn-pick-tax rounded bg-indigo-100 px-1 text-xs text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-800 dark:text-white dark:hover:bg-indigo-700"
                                    data-for="ppn" data-col-key="${safeColKey}" title="Pilih PPN">
                                    🔍
                                </button>
                                <input type="hidden" class="sum-ppn-id" value="PPN11">
                            </div>

                            <div class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 dark:bg-gray-700">
                                <span class="text-xs font-medium whitespace-nowrap shrink-0 min-w-[25px]">PPh</span>
                                <input type="number"
                                    class="sum-pph tax-input w-16 rounded border border-gray-300 px-1 text-right text-xs focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                                    value="0" step="0.01" min="0">
                                <button type="button"
                                    class="btn-pick-tax rounded bg-indigo-100 px-1 text-xs text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-800 dark:text-white dark:hover:bg-indigo-700"
                                    data-for="pph" data-col-key="${safeColKey}" title="Pilih PPh">
                                    🔍
                                </button>
                                <input type="hidden" class="sum-pph-id" value="">
                            </div>
                        </div>

                        <div><span class="font-semibold">Grand Total:</span> <span class="sum-grand">0,00</span></div>
                        <div><span class="font-semibold">G.Total Selected:</span> <span class="sum-selected">0,00</span><span class="sum-selected-base hidden">0</span></div>
                    </div>
                </td>
            `);

            $('#summaryRow').append($sumTd);

            $sumTd.find('.sum-ppn, .sum-pph').on('input', function() {
                recalcSummaryVendor(colKey);
            });
        }

        function addPriceCells(colKey) {
            $('#cvBody tr').each(function(rowIdx) {
                const $input = $(`
                    <input
                        type="text"
                        class="price-input w-full rounded-md border border-gray-400 px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        data-row="${rowIdx}" data-col-key="${colKey}"
                        value="0" inputmode="decimal" autocomplete="off" placeholder="0">
                `);

                const $td = $(`
                    <td class="border px-3 py-2">
                        <div class="flex flex-col items-center gap-0.5 w-full"></div>
                    </td>
                `);

                const $total = $(`<small class="total-label text-right text-xs dark:text-gray-300 font-bold text-gray-600">0,00</small>`);
                const $radio = $(`
                    <div class="flex justify-center mt-0.5">
                        <input type="radio" name="selected_vendor_${rowIdx}" value="${colKey}"
                            class="pick-vendor h-3 w-3 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    </div>
                `);

                $td.find('div').append($input, $total, $radio);
                $(this).append($td);

                $input.on('input', function() {
                    window.calcCellTotal($(this));
                });
            });

            setTimeout(() => recalcSummaryVendor(colKey), 0);
        }

        $(function() {
            $('#vendorSelect').empty().append('<option></option>');

            $.getJSON('/vendorscs', function(data) {
                vendorMaster = data || [];
                vendorMaster.forEach(v => {
                    $('#vendorSelect').append(new Option(v.vendor_name, v.id));
                });
            });

            $('#vendorSelect').select2({
                width: '100%',
                theme: 'default',
                placeholder: "Select",
                allowClear: true
            });

            $('#vendorSelect').on('select2:select', function(e) {
                const vendorId = String(e.params.data.id);
                const vendor = vendorMaster.find(v => String(v.id) === vendorId);
                if (!vendor) return;

                const currentCount = getVendorColumns().length;
                if (currentCount >= 6) {
                    toastr.warning('Maksimal 6 vendor.');
                    $(this).val(null).trigger('change');
                    return;
                }

                const colKey = nextVendorColKey();

                addHeader(colKey, vendor);
                addPriceCells(colKey);

                vendorCount++;
                $('#emptyMsg').toggle(vendorCount === 0);
                $(this).val(null).trigger('change');
                recalcSummaryVendor(colKey);
            });

            $(document).on('click', '.btn-del', function() {
                const colKey = String($(this).data('col-key'));
                const $header = $('#th-vendor-' + colKey);
                const colIdx = $header.index();

                $header.remove();
                $('#td-sum-' + colKey).remove();

                $('#cvBody tr').each(function() {
                    $(this).children('td').eq(colIdx).remove();
                });

                vendorCount--;
                $('#emptyMsg').toggle(vendorCount === 0);
                recalcAllVendors();
            });

            $(document).on('change', '.pick-vendor', function() {
                recalcAllVendors();
            });
        });
    </script>

    <script>
        $(document).on('keypress', '.qty-input', function(e) {
            const code = e.which || e.keyCode;
            if ([8, 9, 13, 37, 38, 39, 40, 46].includes(code)) return;

            const ch = String.fromCharCode(code);
            if (!/[0-9.,]/.test(ch)) {
                e.preventDefault();
                return;
            }

            const v = $(this).val() || '';
            if ((ch === ',' || ch === '.') && /[.,]/.test(v)) {
                e.preventDefault();
            }
        });

        $(document).on('input', '.qty-input', function() {
            let v = $(this).val() || '';
            v = v.replace(/[^0-9.,]/g, '');
            const firstSepIdx = v.search(/[.,]/);
            if (firstSepIdx !== -1) {
                const head = v.slice(0, firstSepIdx + 1);
                const tail = v.slice(firstSepIdx + 1).replace(/[.,]/g, '');
                v = head + tail;
            }
            $(this).val(v);

            const $row = $(this).closest('tr');
            $row.find('input.price-input').each(function() {
                window.calcCellTotal($(this));
            });
        });

        $(document).on('blur', '.qty-input', function() {
            const num = parseQty($(this).val());
            $(this).val(formatQty2(num));

            const $row = $(this).closest('tr');
            $row.find('input.price-input').each(function() {
                window.calcCellTotal($(this));
            });
        });
    </script>

    <script>
        $(document).on('keypress', '.price-input', function(e) {
            const code = e.which || e.keyCode;
            if ([8, 9, 13, 37, 38, 39, 40, 46].includes(code)) return;

            const ch = String.fromCharCode(code);
            if (!/[0-9.,]/.test(ch)) {
                e.preventDefault();
                return;
            }

            const v = $(this).val() || '';
            if ((ch === ',' || ch === '.') && /[.,]/.test(v)) {
                e.preventDefault();
            }
        });

        $(document).on('input', '.price-input', function() {
            let v = $(this).val() || '';
            v = v.replace(/[^0-9.,]/g, '');
            const firstSep = v.search(/[.,]/);
            if (firstSep !== -1) {
                const head = v.slice(0, firstSep + 1);
                const tail = v.slice(firstSep + 1).replace(/[.,]/g, '');
                v = head + tail;
            }
            $(this).val(v);
        });

        $(document).on('blur', '.price-input', function() {
            const num = parsePrice($(this).val());
            $(this).val(formatPrice2(num));
            window.calcCellTotal($(this));
        });
    </script>

    <script>
        $(function() {
            let taxCache = null;
            let taxTarget = null;
            let taxTargetColKey = null;
            let taxTargetType = null;

            function openTaxModal($input, colKey, type) {
                taxTarget = $input;
                taxTargetColKey = colKey;
                taxTargetType = type;

                const $modal = $('#taxModal');
                $modal.removeClass('hidden');

                if (!taxCache) {
                    $.getJSON('{{ route('taxes.index') }}', function(data) {
                        taxCache = Array.isArray(data) ? data : [];
                        renderTaxTable(taxCache);
                    });
                } else {
                    renderTaxTable(taxCache);
                }

                setTimeout(() => $('#taxSearch').trigger('focus'), 50);
            }

            function closeTaxModal() {
                $('#taxModal').addClass('hidden');
                taxTarget = null;
                taxTargetColKey = null;
                taxTargetType = null;
            }

            function renderTaxTable(rows) {
                const $tbody = $('#taxTableBody');
                $tbody.empty();

                rows.forEach(r => {
                    const tr = $(`
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-2 text-xs">${r.taxid ?? ''}</td>
                            <td class="px-3 py-2 text-xs">${Number(r.taxrate ?? 0).toFixed(2)}</td>
                            <td class="px-3 py-2 text-xs">${r.descr ?? ''}</td>
                            <td class="px-3 py-2 text-right">
                                <button type="button" class="btn-choose-tax rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700"
                                        data-taxid="${r.taxid}" data-taxrate="${r.taxrate}">
                                    Choose
                                </button>
                            </td>
                        </tr>
                    `);
                    $tbody.append(tr);
                });
            }

            $(document).on('click', '.btn-pick-tax', function() {
                const colKey = String($(this).data('col-key'));
                const type = String($(this).data('for'));
                const $cell = $(`#td-sum-${colKey}`);
                const $input = (type === 'ppn') ? $cell.find('.sum-ppn') : $cell.find('.sum-pph');

                openTaxModal($input, colKey, type);
            });

            $('#taxModalClose, #taxModalOverlay').on('click', closeTaxModal);

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') closeTaxModal();
            });

            $('#taxSearch').on('input', function() {
                const q = ($(this).val() || '').toLowerCase();
                if (!taxCache) return;

                const filtered = taxCache.filter(r => {
                    const s1 = String(r.taxid ?? '').toLowerCase();
                    const s2 = String(r.descr ?? '').toLowerCase();
                    const s3 = String(r.taxrate ?? '').toLowerCase();
                    return s1.includes(q) || s2.includes(q) || s3.includes(q);
                });

                renderTaxTable(filtered);
            });

            $(document).on('click', '.btn-choose-tax', function() {
                if (!taxTarget) return;

                const taxid = $(this).data('taxid');
                const rate = Number($(this).data('taxrate') || 0);

                taxTarget.val(rate.toFixed(2));

                const $cell = $(`#td-sum-${taxTargetColKey}`);
                if (taxTargetType === 'ppn') {
                    $cell.find('.sum-ppn-id').val(taxid);
                } else {
                    $cell.find('.sum-pph-id').val(taxid);
                }

                recalcSummaryVendor(String(taxTargetColKey));
                closeTaxModal();
            });
        });
    </script>

    <script>
        function collectVendorsPayload() {
            const vendors = [];

            getVendorColumns().each(function(i) {
                if (vendors.length >= 6) return;

                const $th = $(this);
                const colKey = String($th.data('col-key'));
                const vid = String($th.data('vendor-id'));
                const vcode = String($th.data('vendor-code'));
                const $sum = $(`#td-sum-${colKey}`);

                const total = numFromText($sum.find('.sum-total').text());
                const ppn = Number($sum.find('.sum-ppn').val() || 0);
                const pph = Number($sum.find('.sum-pph').val() || 0);
                const ppnId = $sum.find('.sum-ppn-id').val() || '';
                const pphId = $sum.find('.sum-pph-id').val() || '';
                const tax = total * (ppn / 100) + total * (pph / 100);
                const grand = total + tax;

                const selBase = numFromText($sum.find('.sum-selected-base').text());
                const selTax = selBase * (ppn / 100) + selBase * (pph / 100);
                const selGrand = selBase + selTax;

                vendors.push({
                    col_key: colKey,
                    id: vid,
                    vendorid: vcode,
                    vendorname: String($th.data('vendor-name') || ''),
                    vendoralamat: String($th.data('vendor-addr') || ''),
                    vendortelp: String($th.data('vendor-phone') || ''),
                    vendorcp: String($th.data('vendor-cp') || ''),
                    vendortop: $th.find('select.cara-bayar').val() || '',
                    vendornote: String($(`#vendornote_${colKey}`).val() || ''),
                    total: round2(total),
                    ppn: round2(ppn),
                    pph: round2(pph),
                    taxcode: [ppnId, pphId].filter(Boolean).join('+'),
                    tax: round2(tax),
                    grand: round2(grand),
                    selected_total: round2(selBase),
                    selected_tax: round2(selTax),
                    selected_grand: round2(selGrand),
                });
            });

            return vendors;
        }

        function collectDetailsPayload() {
            const details = [];

            $('#cvBody tr').each(function(rowIdx) {
                const $tr = $(this);
                const qty = parseQty($tr.find('.qty-input').val());
                const uom = $tr.data('uom') || '';
                const invId = $tr.data('inventoryid') || '';
                const invDescr = $tr.data('inventory_descr') || '';
                const lastPrice = Number($tr.data('lastprice') || 0);
                const csNote = String($tr.find('.note-input').val() || '');
                const sppbNo = $tr.data('sppb_no') || '';
                const sppjNo = $tr.data('sppj_no') || '';
                const sppkNo = $tr.data('sppk_no') || '';
                const spptNo = $tr.data('sppt_no') || '';

                const row = {
                    inventoryid: invId,
                    inventory_descr: invDescr,
                    qty: round2(qty),
                    uom: uom,
                    inventory_last_price: round2(lastPrice),
                    csnote_detail: csNote,
                    sppb_no: sppbNo,
                    sppj_no: sppjNo,
                    sppk_no: sppkNo,
                    sppt_no: spptNo,
                    vendor: []
                };

                const pickedColKey = String($tr.find('input.pick-vendor:checked').val() || '');

                getVendorColumns().each(function(i) {
                    if (i >= 6) return;

                    const $th = $(this);
                    const colKey = String($th.data('col-key'));
                    const vendorId = String($th.data('vendor-id'));
                    const vendorIdCode = String($th.data('vendor-code'));

                    const $priceInput = $tr.find(`input.price-input[data-col-key="${colKey}"]`);
                    const price = parsePrice($priceInput.val());
                    const total = qty * price;

                    row.vendor.push({
                        col_key: colKey,
                        id: vendorId,
                        vendorid: vendorIdCode,
                        price: round2(price),
                        total: round2(total),
                        selected: colKey === pickedColKey
                    });
                });

                details.push(row);
            });

            return details;
        }
    </script>

    <script>
        $('#saveBtn').on('click', function(e) {
            e.preventDefault();

            const $vendorCols = getVendorColumns();
            if ($vendorCols.length === 0) {
                toastr.error('Pilih minimal 1 vendor.');
                return;
            }

            let allVendorTotalsZero = true;
            $vendorCols.each(function() {
                const colKey = String($(this).data('col-key'));
                const total = numFromText($(`#td-sum-${colKey} .sum-total`).text());
                if (total > 0) allVendorTotalsZero = false;
            });

            if (allVendorTotalsZero) {
                toastr.error('Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
                return;
            }

            if (!validateQtyLimit()) {
                toastr.error('Ada qty yang melebihi qty awal. Periksa kembali.');
                return;
            }

            if (!validatePaymentTerms()) return;

            const vendors = collectVendorsPayload();
            const details = collectDetailsPayload();

            const fd = new FormData(document.getElementById('csForm'));
            fd.append('vendors', JSON.stringify(vendors));
            fd.append('details', JSON.stringify(details));

            showOverlay('Submitting');

            $.ajax({
                url: "{{ route('cs.save') }}",
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    hideOverlay();
                    toastr.success('CS berhasil disimpan.');
                    window.location.href = "/csjobs";
                },
                error: function(xhr) {
                    hideOverlay();
                    let msg = 'Gagal menyimpan CS.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        });
    </script>

    <script>
        $('#submitBtn').on('click', function(e) {
            e.preventDefault();

            const $vendorCols = getVendorColumns();
            if ($vendorCols.length === 0) {
                toastr.error('Pilih minimal 1 vendor.');
                return;
            }

            let allVendorTotalsZero = true;
            $vendorCols.each(function() {
                const colKey = String($(this).data('col-key'));
                const total = numFromText($(`#td-sum-${colKey} .sum-total`).text());
                if (total > 0) allVendorTotalsZero = false;
            });

            if (allVendorTotalsZero) {
                toastr.error('Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
                return;
            }

            if ($('.pick-vendor:checked').length === 0) {
                toastr.error('Pilih vendor pada minimal satu item.');
                return;
            }

            if (!validateQtyLimit()) {
                toastr.error('Ada qty yang melebihi qty awal. Periksa kembali.');
                return;
            }

            if (!validatePaymentTerms()) return;

            let rowWithoutVendor = false;
            $('#cvBody tr').each(function() {
                let hasPrice = false;
                $(this).find('input.price-input').each(function() {
                    const num = parsePrice($(this).val() || '');
                    if (num > 0) {
                        hasPrice = true;
                        return false;
                    }
                });

                if (hasPrice && $(this).find('.pick-vendor:checked').length === 0) {
                    rowWithoutVendor = true;
                    return false;
                }
            });

            if (rowWithoutVendor) {
                toastr.error('Ada baris yang memiliki harga tetapi belum memilih vendor.');
                return;
            }

            const vendors = collectVendorsPayload();
            const details = collectDetailsPayload();

            const doc = $('input[name="doc"]').val();
            const srcId = $('input[name="src_id"]').val();

            showOverlay('Validating qty');

            $.ajax({
                url: "{{ route('cs.check-qty') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    doc: doc,
                    src_id: srcId,
                    details: JSON.stringify(details),
                },
                success: function(res) {
                    const fd = new FormData(document.getElementById('csForm'));
                    fd.append('vendors', JSON.stringify(vendors));
                    fd.append('details', JSON.stringify(details));

                    showOverlay('Submitting');

                    $.ajax({
                        url: "{{ route('cs.store') }}",
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function(res2) {
                            hideOverlay();
                            toastr.success('CS berhasil disimpan & diajukan.');
                            window.location.href = "/cslist";
                        },
                        error: function(xhr2) {
                            hideOverlay();
                            let msg = 'Gagal menyimpan CS.';
                            if (xhr2.responseJSON && xhr2.responseJSON.message)
                                msg = xhr2.responseJSON.message;

                            toastr.error(msg);
                        }
                    });
                },
                error: function(xhr) {
                    hideOverlay();
                    const res = xhr.responseJSON || {};
                    const msg = res.message || 'Qty tidak valid.';
                    toastr.error(msg);

                    if (Array.isArray(res.errors)) {
                        res.errors.forEach(function(err) {
                            $('#cvBody tr').eq(err.row_index).addClass('bg-red-100');
                        });
                    }
                }
            });
        });
    </script>

    <script>
        function validateQtyLimit() {
            let ok = true;

            $('#cvBody tr').each(function() {
                const $tr = $(this);
                const max = Number($tr.data('original_qty'));
                const $inp = $tr.find('.qty-input');
                const cur = parseQty($inp.val());

                $inp.removeClass('is-invalid');
                $tr.find('.qty-error').remove();

                if (isFinite(max) && cur > max) {
                    ok = false;
                    $inp.addClass('is-invalid');
                    $('<div class="error-feedback qty-error">Qty tidak boleh melebihi ' + formatQty2(max) +
                            '.</div>')
                        .insertAfter($inp);
                }
            });

            return ok;
        }

        $(document).on('blur', '.qty-input', function() {
            const $tr = $(this).closest('tr');
            const max = Number($tr.data('original_qty'));
            const curN = parseQty($(this).val());

            if (isFinite(max) && curN > max) {
                $(this).addClass('is-invalid');
                $tr.find('.qty-error').remove();
                $('<div class="error-feedback qty-error">Qty dikembalikan ke maksimum: ' + formatQty2(max) +
                        '.</div>')
                    .insertAfter($(this));

                $(this).val(formatQty2(max));

                const $row = $tr;
                $row.find('input.price-input').each(function() {
                    window.calcCellTotal($(this));
                });
            } else {
                $(this).removeClass('is-invalid');
                $tr.find('.qty-error').remove();
            }
        });
    </script>

    <script>
        function validatePaymentTerms() {
            let ok = true;

            $('#cvTable thead th[id^="th-vendor-"] select.cara-bayar').removeClass('is-invalid');

            $('#cvTable thead th[id^="th-vendor-"]').each(function() {
                const $th = $(this);
                const $top = $th.find('select.cara-bayar');
                const val = $top.val();

                if (!val) {
                    ok = false;
                    $top.addClass('is-invalid');
                    const th = $th.get(0);
                    if (th && th.scrollIntoView) th.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                    setTimeout(() => $top.trigger('focus'), 150);
                    return false;
                }
            });

            if (!ok) {
                toastr.error('Payment Term (TOP) wajib diisi untuk semua vendor.');
            }
            return ok;
        }

        $(document).on('change', 'select.cara-bayar', function() {
            if ($(this).val()) $(this).removeClass('is-invalid');
        });
    </script>

    <script>
        function openLastPriceModal() {
            $('#lastPriceModal').removeClass('hidden');
        }

        function closeLastPriceModal() {
            $('#lastPriceModal').addClass('hidden');
            $('#lpBody').empty();
            $('#lpEmpty').addClass('hidden');
            $('#lpLoading').addClass('hidden');
            $('#lpTitle').text('');
        }

        $('#lastPriceModalClose, #lastPriceModalOverlay').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeLastPriceModal();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') closeLastPriceModal();
        });

        $(document).on('click', '.btn-lastprice', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const inventoryid = String($(this).data('inventoryid') || '');
            const inventorydescr = String($(this).data('inventorydescr') || '');

            if (!inventoryid) {
                toastr.error('Inventory ID kosong.');
                return;
            }

            $('#lpTitle').text(
                inventoryid + (inventorydescr ? (' — ' + inventorydescr) : '')
            );

            $('#lpBody').empty();
            $('#lpEmpty').addClass('hidden');
            $('#lpLoading').removeClass('hidden');

            openLastPriceModal();

            $.ajax({
                url: "{{ route('cs.lastprice.history.entry') }}",
                method: "GET",
                data: {
                    inventoryid
                },
                success: function(res) {
                    $('#lpLoading').addClass('hidden');

                    const rows = (res && res.data) ? res.data : [];
                    if (!rows.length) {
                        $('#lpEmpty').removeClass('hidden');
                        return;
                    }

                    rows.forEach(r => {
                        const tr = `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${r.ponbr ?? ''}</td>
                                <td class="px-3 py-2">${r.podate ?? ''}</td>
                                <td class="px-3 py-2">${r.csid ?? ''}</td>
                                <td class="px-3 py-2">${r.vendorname ?? ''}</td>
                                <td class="px-3 py-2 text-right font-semibold">${formatNumID(r.unitcost)}</td>
                                <td class="px-3 py-2">${r.purchaser ?? ''}</td>
                            </tr>
                        `;
                        $('#lpBody').append(tr);
                    });
                },
                error: function(xhr) {
                    $('#lpLoading').addClass('hidden');
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ?
                        xhr.responseJSON.message :
                        'Gagal ambil history.';
                    toastr.error(msg);
                }
            });
        });

        document.getElementById('csForm').addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;

            const tag = e.target.tagName;
            const type = (e.target.type || '').toLowerCase();

            if (tag === 'TEXTAREA') return;
            if (type === 'file') return;

            e.preventDefault();

            const inputs = Array.from(
                this.querySelectorAll('input, select, textarea')
            ).filter(el =>
                !el.disabled &&
                el.offsetParent !== null
            );

            const index = inputs.indexOf(document.activeElement);

            if (index > -1 && index + 1 < inputs.length) {
                inputs[index + 1].focus();
            }
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>