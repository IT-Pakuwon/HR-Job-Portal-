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
    </style>
    <style>
        .req::after {
            content: " *";
            color: #dc2626;
            font-weight: 700;
        }
    </style>
    <style>
        .vendor-title {
            white-space: normal;
            /* boleh turun baris */
            overflow-wrap: anywhere;
            /* pecah kata sangat panjang */
            word-break: break-word;
            /* jaga pecah kata yang wajar */
            line-height: 1.1;
            /* rapatkan sedikit */
        }
    </style>
    <style>
        .tax-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
            align-items: center
        }

        .tax-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem
        }

        .tax-input {
            width: 3.75rem;
            text-align: right;
            padding: .125rem .25rem
        }

        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            font-size: 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #fff
        }

        .icon-btn:hover {
            background: #f3f4f6
        }

        .summary-label {
            font-size: 2rem;
            /* sedikit lebih besar dari  text-sm  */
            font-weight: 600;
            color: #374151;
            /* gray-700 */
        }

        .summary-value {
            font-size: 2rem;
            /* font lebih besar utk nominal */
            font-weight: 700;
            color: #111827;
            /* gray-900 */
        }

        .select-container .select-selection--single {
            height: 42px;
            border-radius: 0.5rem;
        }

        .select-container--default .select-selection--single .select-selection__rendered {
            line-height: 42px;
            padding-left: .75rem;
        }

        .select-container--default .select-selection--single .select-selection__arrow {
            height: 42px;
            right: .5rem;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="csForm" class="flex flex-col gap-6" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="doc" value="{{ $doc }}">
                    <input type="hidden" name="src_id" value="{{ $src_id }}">
                    <input type="hidden" name="sppbjktid" value="{{ $docno }}">
                    <input type="hidden" name="cpny_id" value="{{ $header->cpny_id }}">
                    <input type="hidden" name="department_id" value="{{ $header->department_id }}">
                    <input type="hidden" name="bqid" value="{{ $header->bqid ?? '' }}">
                    <input type="hidden" name="user_peminta" value="{{ optional($header->creator)->name }}">
                    <input type="hidden" name="assigndate" value="{{ $header->assigndate ?? '' }}">

                    <!-- Create CS Header -->
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <!-- Header -->
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white"><span
                                    class="text-indigo-500">🆔</span>
                                {{ $cs->csid }}</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                            <!-- LEFT SIDE: auto grid of fields -->
                            <div class="grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-4">

                                <!-- SPPB/J/K/T -->
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">SPPB/J/K/T
                                        ID</label>
                                    @if (!empty($sourceShowUrl))
                                        <a href="{{ $sourceShowUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-indigo-600 underline hover:text-indigo-800 dark:border-gray-600 dark:bg-gray-700 dark:text-indigo-300">
                                            {{ $docno }}
                                        </a>
                                    @else
                                        <input type="text" value="{{ $docno }}" readonly
                                            class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                    @endif
                                    {{-- <input type="text" value="{{ $docno }}" readonly
                                        class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2  text-sm  dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" /> --}}
                                </div>

                                <!-- User -->
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">User</label>
                                    <input type="text"
                                        value="{{ ucwords(strtolower(optional($header->creator)->name)) }}" readonly
                                        class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                </div>

                                <!-- Company -->
                                <div>
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Company</label>
                                    <input type="text" value="{{ $header->cpny_id }}" readonly
                                        class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                </div>

                                <!-- Department -->
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600 dark:text-gray-400">Department</label>
                                    <input type="text" value="{{ $header->department_id }}" readonly
                                        class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                </div>

                                <!-- Purchaser -->
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600 dark:text-gray-400">Purchaser</label>
                                    <input type="text"
                                        value="{{ ucwords(strtolower(optional($header->purchaser)->name)) }}" readonly
                                        class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                </div>

                                <!-- BQ ID -->
                                <div class="flex flex-row justify-between gap-2">
                                    @if (in_array($doc, ['SPPJ', 'SPPT']))
                                        <div class="flex-1">
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">BQ
                                                ID</label>
                                            @if (!empty($bqShowUrl) && !empty($header->bqid))
                                                <a href="{{ $bqShowUrl }}" target="_blank" rel="noopener noreferrer"
                                                    class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-indigo-600 underline hover:text-indigo-800 dark:border-gray-600 dark:bg-gray-700 dark:text-indigo-300">
                                                    {{ $header->bqid }}
                                                </a>
                                            @else
                                                <input type="text" value="{{ $header->bqid ?? '' }}" readonly
                                                    class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                            @endif
                                            {{-- <input type="text" value="{{ $header->bqid }}" readonly
                                                class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2  text-sm  dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" /> --}}
                                        </div>

                                        <div class="flex-1">
                                            @php
                                                // $eid = CS hash id (sudah dikirim dari controller)
                                                $csidForBQ = $eid ?? null;
                                            @endphp

                                            {{-- Kondisi: jika BQ SUDAH ADA → tombol Open BQ, kalau BELUM → tombol Create BQ --}}
                                            @if ($bq && $bq_eid)
                                                <a href="{{ route('bqcs.edit', $bq_eid) }}"
                                                    class="mt-7 inline-flex w-full items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-700">
                                                    Open BQ CS
                                                </a>
                                            @elseif ($csidForBQ)
                                                <a href="{{ route('bqcs.createFromCS', $csidForBQ) }}"
                                                    class="mt-7 inline-flex w-full items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-blue-700">
                                                    Create BQ CS
                                                </a>
                                            @else
                                                <button type="button" title="Simpan CS dulu, baru buat BQ"
                                                    class="mt-8 inline-flex w-full cursor-not-allowed items-center gap-2 rounded-lg bg-gray-400 px-4 py-2 text-center text-sm font-semibold text-white">
                                                    Create BQ CS
                                                </button>
                                            @endif
                                        </div>

                                    @endif
                                </div>



                                <!-- Purpose -->
                                {{-- <div>
                                    <label
                                        class="req  text-sm  font-medium text-gray-600 dark:text-gray-400">Purpose</label>
                                    <input type="text" value="{{ $header->keperluan }}" readonly
                                        class="mt-1 w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2  text-sm  dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                </div> --}}

                                <!-- Vendor -->
                                <div class="col-span-2 flex flex-col gap-2">
                                    <label class="req text-sm font-medium text-gray-600 dark:text-gray-400">Select
                                        Vendor</label>
                                    <select id="vendorSelect"
                                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="">Select</option>
                                    </select>

                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:col-span-1 md:grid-cols-2">
                                <!-- RIGHT SIDE: NOTE -->
                                <div>
                                    <label
                                        class="req text-sm font-medium text-gray-600 dark:text-gray-400">Purpose</label>
                                    <input type="text" value="{{ $header->keperluan }}" readonly
                                        class="h-35 w-full rounded-md border border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Note CS</label>
                                    <textarea name="csnote" id="csnote"
                                        class="h-35 w-full rounded-md border border-gray-300 bg-white p-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">{{ $cs->csnote }}</textarea>
                                </div>


                            </div>



                        </div>
                    </div>

                    <!-- CS Detail -->
                    <div class="flex w-full flex-col rounded-xl bg-white shadow-md dark:bg-gray-800">
                        <div class="p-4">
                            <div
                                class="border-b border-gray-200 pb-4 text-sm font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                CS Detail
                            </div>
                            <div class="mt-4 overflow-x-auto">
                                <table id="cvTable"
                                    class="w-max table-auto border text-sm text-gray-700 dark:text-gray-200">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-gray-700">
                                            <th class="w-64 border px-3 py-2">Inventory Descr</th>
                                            <th class="w-20 border px-3 py-2 text-center">Qty</th>
                                            <th class="w-20 border px-3 py-2 text-center">UOM</th>
                                            <th class="w-40 border px-3 py-2 text-center">Note</th>
                                            <th class="w-28 border px-3 py-2 text-center">Last Price</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cvBody">
                                        @foreach ($items as $row)
                                            <tr data-inventoryid="{{ $row->inventoryid ?? '' }}"
                                                data-inventory_descr="{{ $row->inventory_descr }}"
                                                data-uom="{{ $row->uom }}"
                                                data-lastprice="{{ $row->last_unitcost ?? 0 }}"
                                                data-original_qty="{{ (float) $row->qty }}"
                                                data-note="{{ $row->csnote_detail ?? '' }}">
                                                <td class="border px-3 py-2">{{ $row->inventory_descr }}</td>
                                                <td class="border px-3 py-2 text-center">
                                                    <input type="text"
                                                        class="qty-input w-full rounded-md border border-gray-400 px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                                        value="{{ number_format((float) $row->qty, 2, ',', '') }}"
                                                        inputmode="decimal" autocomplete="off" placeholder="0,00"
                                                        aria-label="Qty">
                                                </td>
                                                <td class="border px-3 py-2 text-center">{{ $row->uom }}</td>
                                                {{-- <td class="border px-3 py-2 text-center">{{ $row->csnote_detail }}</td> --}}
                                                <td class="border px-3 py-2 text-center">
                                                    <textarea
                                                        class="note-input w-full resize-none rounded-md border border-gray-400 px-2 py-1 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                                        rows="2" autocomplete="off" placeholder="Add note..." aria-label="Note">{{ $row->csnote_detail ?? '' }}</textarea>
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
                                            {{-- vendor cells via JS --}}
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>


                    <!-- Attachments -->
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Existing Attachments -->
                        <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div
                                class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Attachments
                                    {{ $doc }}</h3>
                            </div>

                            @if (($attachment ?? collect())->count())
                                <div class="mt-4 overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="text-gray-600 dark:text-gray-300">
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <th class="p-3 text-left font-semibold">Filename</th>
                                                <th class="p-3 text-left font-semibold">Created By</th>
                                                <th class="p-3 text-left font-semibold">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- @foreach ($attachment as $at)
                                                @php
                                                    $year = $at->created_at->year;
                                                    $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                                @endphp
                                                <tr
                                                    class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                    <td class="px-3 py-2">
                                                        <a href="{{ $fileUrl }}" target="_blank"
                                                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                            📎 {{ $at->name }}
                                                        </a>
                                                    </td>
                                                    <td class="px-3 py-2">{{ $at->created_user }}</td>
                                                    <td class="px-3 py-2">
                                                        {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach --}}
                                            @foreach ($attachment as $at)
                                                <tr
                                                    class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                    <td class="px-3 py-2">
                                                        @if ($at->url)
                                                            <a href="{{ $at->url }}" target="_blank"
                                                                class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                                📎 {{ $at->display_name }}
                                                            </a>
                                                        @else
                                                            <span
                                                                class="flex items-center gap-2 text-gray-500 dark:text-gray-300"
                                                                title="Signed URL tidak tersedia/expired">
                                                                📎 {{ $at->display_name }} <em class="text-sm">(no
                                                                    link)</em>
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2">{{ $at->created_by }}</td>
                                                    <td class="px-3 py-2">
                                                        {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Attachment Empty.</p>
                            @endif
                        </div>


                        <!-- New Attachments -->
                        <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Attachments CS</span>
                                    <span
                                        class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                        details &rarr;</span>
                                    <span
                                        class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                        details &darr;</span>
                                </summary>
                                <div class="flex h-auto flex-col justify-start">
                                    <div id="attachmentsContainer">
                                        {{-- @foreach ($attachmentCS as $attach)
                                             @php
                                                $year = $attach->created_at->year;
                                                $fileUrl = url('/attachments/' . $year . '/' . $attach->attachfile);
                                            @endphp
                                            <div class="attachment-row flex items-center gap-2"
                                                data-attachid="{{ $attach->id }}">
                                                <a href="{{ $fileUrl }}" target="_blank" class="mt-4 w-full border p-3 text-sm">📎
                                                    {{ $attach->name }}</a>
                                                <button type="button"
                                                    class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                    data-id="{{ $attach->id }}">🗑️
                                                </button>
                                            </div>
                                        @endforeach --}}
                                        @foreach ($attachmentCS as $attach)
                                            <div class="attachment-row flex items-center gap-2"
                                                data-attachid="{{ $attach->id }}">
                                                @if ($attach->url)
                                                    <a href="{{ $attach->url }}" target="_blank"
                                                        class="mt-4 w-full border p-3 text-sm">
                                                        📎 {{ $attach->display_name }}
                                                    </a>
                                                @else
                                                    <div class="mt-4 w-full border p-3 text-sm text-gray-500 dark:text-gray-300"
                                                        title="Signed URL tidak tersedia/expired">
                                                        📎 {{ $attach->display_name }} <em class="text-sm">(no
                                                            link)</em>
                                                    </div>
                                                @endif
                                                <button type="button"
                                                    class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                    data-id="{{ $attach->id }}">🗑️
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="button" id="addAttachment"
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg> Add Attachment
                                </button>
                            </details>
                            <!-- Action Buttons -->
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
                                    <!-- Cancel -->
                                    <button id="cancelBtn"
                                        class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                        <span id="cancelText">Cancel</span>
                                        <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                            </path>
                                        </svg>
                                    </button>

                                    <!-- Save -->
                                    <button type="button" id="saveBtn"
                                        class="<span id= mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-md bg-green-600 px-4 py-2 text-white md:w-auto"saveText">Save
                                        CS</span>
                                        <svg id="saveSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                            </path>
                                        </svg>
                                    </button>
                                    <!-- Submit Approval -->
                                    <button type="submit" id="submitBtn"
                                        class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                        <span id="btnText">Submit Approval</span>
                                        <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                            </path>
                                        </svg>
                                    </button>

                                    <div class="flex justify-start md:justify-end">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAX PICKER MODAL -->
            <div id="taxModal" class="fixed inset-0 z-[3000] hidden">
                <div id="taxModalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div
                    class="absolute left-1/2 top-1/2 w-[90vw] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b px-4 py-3 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pilih Pajak</h3>
                        <button id="taxModalClose"
                            class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                    </div>
                    <div class="p-4">
                        <div class="mb-3 flex items-center gap-2">
                            <input id="taxSearch" type="text" placeholder="Cari taxid/descr..."
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        </div>
                        <div class="max-h-[55vh] overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-sm font-semibold uppercase tracking-wider">
                                            Tax ID</th>
                                        <th class="px-3 py-2 text-left text-sm font-semibold uppercase tracking-wider">
                                            Rate (%)</th>
                                        <th class="px-3 py-2 text-left text-sm font-semibold uppercase tracking-wider">
                                            Description</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody id="taxTableBody"
                                    class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <!-- rows by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /TAX PICKER MODAL -->

            <!-- MISMATCH POPUP -->
            <div id="bqcsMismatchModal" class="fixed inset-0 z-[3500] hidden">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                <div
                    class="absolute left-1/2 top-1/2 w-[92vw] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white p-4 shadow-xl dark:bg-gray-800">
                    <div class="mb-3 flex items-center justify-between border-b pb-2 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Tidak bisa Submit —
                            Perbedaan Nilai BQ vs CS</h3>
                        <button id="bqcsMismatchClose"
                            class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                    </div>
                    <p class="mb-3 text-sm text-gray-700 dark:text-gray-300">
                        Terdapat vendor dengan nilai berbeda antara <b>(BQ: Total BQ)</b> dan <b>(CS: Total CS)</b>.
                        Periksa tabel di bawah ini:
                    </p>
                    <div class="max-h-[60vh] overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">Vendor</th>
                                    <th class="px-3 py-2 text-right font-semibold">Total BQ </th>
                                    <th class="px-3 py-2 text-right font-semibold">Total CS</th>
                                    <th class="px-3 py-2 text-right font-semibold">Selisih</th>
                                </tr>
                            </thead>
                            <tbody id="bqcsMismatchBody"
                                class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right">
                        <button id="bqcsMismatchOk"
                            class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">OK</button>
                    </div>
                </div>
            </div>

            <div id="lastPriceModal" class="fixed inset-0 z-[4000] hidden">
                <div id="lastPriceModalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                <div
                    class="absolute left-1/2 top-1/2 w-[92vw] max-w-4xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b px-4 py-3 dark:border-gray-700">
                        <div class="flex flex-col">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Last Price History</h3>
                            {{-- <div id="lpTitle" class=" text-sm  text-gray-500 dark:text-gray-300"></div> --}}
                            <h3 id="lpTitle" class="text-sm font-semibold text-gray-800 dark:text-gray-100"></h3>
                        </div>
                        <button type="button" id="lastPriceModalClose"
                            class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">✖</button>
                    </div>

                    <div class="p-4">
                        <div id="lpLoading" class="mb-3 hidden text-sm text-gray-600 dark:text-gray-300">
                            Loading...
                        </div>

                        <div class="max-h-[60vh] overflow-auto rounded border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
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
                                    <!-- rows by JS -->
                                </tbody>
                            </table>
                        </div>

                        <div id="lpEmpty" class="mt-3 hidden text-sm text-gray-500 dark:text-gray-300">
                            No history found.
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
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>


    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }

            $(document).on('click', '.removeAttachment2', function() {
                let attachmentId = $(this).data('id'); // Ambil ID attachment
                let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

                // Cek konfirmasi pengguna
                let confirmDelete = confirm('Are you sure you want to remove this attachment?');

                if (confirmDelete) {
                    $.ajax({
                        url: "/remove-attachment/" + attachmentId, // Endpoint ke controller
                        type: "POST",
                        data: {
                            _method: "PUT",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                row.remove(); // Hapus dari tampilan jika berhasil
                                alert("Attachment removed successfully!");
                            } else {
                                alert("Failed to remove attachment.");
                            }
                        },
                        error: function(xhr) {
                            alert("Error! Unable to remove attachment.");
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
                    return false;
                }
            });
        });
    </script>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function() {
            /* --- stubs agar tidak undefined bila dipanggil lebih awal --- */
            if (typeof window.calcCellTotal !== 'function') {
                window.calcCellTotal = function() {};
            }
            if (typeof window.recalcSummaryVendor !== 'function') {
                window.recalcSummaryVendor = function() {};
            }

            /* ========== 1) Master Vendor ke <select> ========== */
            $('#vendorSelect').empty().append('<option></option>');
            let vendorMaster = [];
            $.getJSON('/vendorscs', function(data) {
                vendorMaster = data || [];
                vendorMaster.forEach(v => $('#vendorSelect').append(new Option(v.vendor_name, v.id)));
            });

            /* ========== 2) Select2 ========== */
            $('#vendorSelect').select2({
                width: '100%',
                theme: 'default',
                placeholder: 'Select',
                allowClear: true
            });

            /* (opsional) tombol add vendor */
            $('#btnAddVendor').on('click', function() {
                $('#vendorSelect').val(null).trigger('change');
                $('#vendorSelect').select2('open');
            });

            /* ========== 3) Tambah kolom saat vendor dipilih ========== */
            let vendorCount = 0;

            //vendor TOP
            const TOPS = @json($tops->map(fn($t) => ['id' => $t->topid, 'name' => $t->top_name]));
            // siapkan HTML option sekali saja
            const TOPS_OPTIONS_HTML =
                '<option value="" disabled selected>Select TOP</option>' +
                TOPS.map(t => `<option value="${_.escape(String(t.id))}">${_.escape(t.name)}</option>`).join('');

            $('#vendorSelect').on('select2:select', function(e) {
                const pkId = String(e.params.data.id);
                const v = vendorMaster.find(x => String(x.id) === pkId);
                if (!v) return;

                const colKey = String(v.vendor_id); // kunci kolom adalah vendor_id (kode)

                // limit
                if ($('#cvTable thead th[id^="th-vendor-"]').length >= 6) {
                    toastr.warning('Maksimal 6 vendor.');
                    $(this).val(null).trigger('change');
                    return;
                }
                // cegah duplikat
                if ($('#th-vendor-' + CSS.escape(colKey)).length) {
                    toastr.warning('Vendor sudah ada.');
                    $(this).val(null).trigger('change');
                    // return;
                }

                addHeader(colKey, v);
                addPriceCells(colKey);

                vendorCount++;
                $('#emptyMsg').toggle(vendorCount === 0);
                $(this).val(null).trigger('change');
            });

            /* ========== 4) Header vendor + summary cell ========== */
            function addHeader(idKey, v) {
                const colWidth = '20rem';
                const $th = $(`
            <th id="th-vendor-${idKey}"
                class="relative border px-3 py-2 align-top w-72 max-w-xs sm:w-80 sm:max-w-sm md:w-96 md:max-w-md lg:w-[20rem]"
                data-vendor-id="${_.escape(idKey)}"
                data-vendor-code="${_.escape(v.vendor_id)}"
                data-vendor-name="${_.escape(v.vendor_name)}"
                data-vendor-addr="${_.escape(v.vendor_addr1 ?? '')}"
                data-vendor-phone="${_.escape(v.phone_number ?? '')}"
                data-vendor-cp="${_.escape(v.contact_person ?? '')}">
                <div class="flex flex-col text-left  text-sm ">

                    <!-- Vendor Name + Info Icon -->
                    <div class="flex items-center gap-1 font-bold text-gray-800 dark:text-gray-100 break-words">
                        <span>${v.vendor_name}</span>

                        <!-- INFO ICON -->
                        <div class="relative group inline-block cursor-default">
                            <div class="flex h-4 w-4 items-center justify-center 
                                    rounded-full bg-gray-200 text-gray-700 text-[10px]
                                    dark:bg-gray-700 dark:text-gray-200 cursor-default">
                                i
                            </div>

                            <!-- TOOLTIP -->
                            <div class="pointer-events-none absolute left-1/2 top-full z-50 mt-2 
                                        w-64 -translate-x-1/2 rounded-md bg-gray-900 p-3  text-sm  text-gray-200 
                                        shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible 
                                        transition-opacity duration-200">

                                <div class="font-semibold text-white mb-1">${v.vendor_name}</div>

                                <div class="space-y-1 text-gray-300 leading-4">
                                    <div>✉️ ${v.contact_person ?? '-'}</div>
                                    <div>☎️ ${v.phone_number ?? '-'}</div>
                                    <div>🏠 ${v.vendor_addr1 ?? '-'}</div>
                                </div>

                                <!-- Arrow -->
                                <div class="absolute -top-1 left-1/2 h-2 w-2 -translate-x-1/2 rotate-45 bg-gray-900"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Term -->
                    <div class="flex items-center gap-2 mt-1">
                        <span class=" text-sm  font-semibold text-gray-600 dark:text-gray-300">Payment Term:</span>

                        <select name="cara_bayar_${idKey}" 
                            class="cara-bayar w-40 rounded-full border border-gray-300 bg-white px-3 py-1 
                                 text-sm  font-medium shadow-sm focus:border-indigo-500 focus:ring 
                                focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                            ${TOPS_OPTIONS_HTML}
                        </select>
                    </div>
                    <!-- Vendor Note -->
                    <div class="mt-2">                        
                        <textarea
                            name="vendornote_${idKey}"
                            class="vendornote mt-1 w-full rounded-md border border-gray-300 bg-white px-2 py-2  text-sm  text-gray-900 shadow-sm
                                focus:border-indigo-500 focus:ring focus:ring-indigo-500/50
                                dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            rows="2"
                            placeholder="Vendor Note"></textarea>
                    </div>


                </div>

                <button type="button" class="btn-del absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600  text-sm  text-white shadow hover:bg-red-700" data-id="${idKey}">✕</button>
            </th>
            `);
                $('#cvTable thead tr').append($th);

                const $sumTd = $(`
            <td id="td-sum-${idKey}" class="border px-3 py-2  text-sm  align-top" style="width:${colWidth};max-width:${colWidth};">
                <div class="flex flex-col gap-2 text-gray-700 dark:text-gray-200">
                <div><span class="font-semibold">Total:</span> <span class="sum-total">0</span></div>
                <div class="flex justify-between gap-2">
                    <div class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 dark:bg-gray-700">
                    <span class=" text-sm  font-medium">PPN</span>
                    <input type="number" class="sum-ppn tax-input w-16 rounded border border-gray-300 px-1 text-right  text-sm  focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" value="11.00" step="0.01" min="0">
                    <button type="button" class="btn-pick-tax rounded bg-indigo-100 px-1  text-sm  text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-800 dark:text-white dark:hover:bg-indigo-700" data-for="ppn" data-vendor="${idKey}" title="Pilih PPN">🔍</button>
                    <input type="hidden" class="sum-ppn-id" value="">
                    </div>
                    <div class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 dark:bg-gray-700">
                    <span class=" text-sm  font-medium">PPh</span>
                    <input type="number" class="sum-pph tax-input w-16 rounded border border-gray-300 px-1 text-right  text-sm  focus:border-indigo-500 focus:ring focus:ring-indigo-500/50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" value="0" step="0.01" min="0">
                    <button type="button" class="btn-pick-tax rounded bg-indigo-100 px-1  text-sm  text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-800 dark:text-white dark:hover:bg-indigo-700" data-for="pph" data-vendor="${idKey}" title="Pilih PPh">🔍</button>
                    <input type="hidden" class="sum-pph-id" value="">
                    </div>
                </div>
                <div><span class="font-semibold">Grand Total:</span> <span class="sum-grand">0</span></div>
                <div><span class="font-semibold">G.Total Selected:</span><span class="sum-selected" data-raw="0">0</span></div>
                </div>
            </td>
            `);
                $('#summaryRow').append($sumTd);

                // perubahan pajak -> recalc
                $sumTd.find('.sum-ppn, .sum-pph').on('input', function() {
                    recalcSummaryVendor(String(idKey));
                });
            }

            /* ========== 5) Tambah cell harga tiap baris ========== */
            function addPriceCells(idKey) {
                $('#cvBody tr').each(function(rowIdx) {
                    const $input = $(`
                <input type="text" class="price-input  w-full rounded-md border border-gray-400 px-2 py-1 text-right shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    data-row="${rowIdx}" data-vendor="${idKey}"
                    value="0,00" inputmode="decimal" autocomplete="off" placeholder="0,00">
            `);
                    const $td = $(
                        `<td class="border px-3 py-2"><div class="flex flex-col items-center gap-0.5 w-full"></div></td>`
                    );
                    const $total = $(
                        `<small class="total-label text-right  text-sm  dark:text-gray-300 font-bold text-gray-600">0</small>`
                    );
                    const $radio = $(`
                <div class="flex justify-center mt-0.5">
                <input type="radio" name="selected_vendor_${rowIdx}" value="${idKey}" class="pick-vendor h-3 w-3 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                </div>
            `);
                    $td.find('div').append($input, $total, $radio);
                    $(this).append($td);

                    // bind hitung ulang
                    $input.on('input', function() {
                        calcCellTotal($(this));
                    });
                });
            }

            /* ========== 6) PRELOAD dari controller ========== */
            if (typeof VENDORS_USED !== 'undefined' && Array.isArray(VENDORS_USED)) {
                VENDORS_USED.forEach(v => {
                    const colKey = String(v.vendor_id);
                    addHeader(colKey, v);
                    addPriceCells(colKey);
                });

                // set TOP & pajak + angka ringkasan awal
                VENDORS_USED.forEach(v => {
                    const id = String(v.vendor_id);
                    const $th = $(`#th-vendor-${CSS.escape(id)}`);
                    const $sum = $(`#td-sum-${CSS.escape(id)}`);
                    if ($th.length) $th.find('select.cara-bayar').val(v.top || '30D');
                    if ($sum.length) {
                        $sum.find('.sum-ppn').val((v.ppn ?? 11).toFixed(2));
                        $sum.find('.sum-pph').val((v.pph ?? 0).toFixed(2));
                        $sum.find('.sum-ppn-id').val(v.ppn_id || '');
                        $sum.find('.sum-pph-id').val(v.pph_id || '');
                        if (v.total != null) $sum.find('.sum-total').text((+v.total).toLocaleString(
                            'id-ID'));
                        if (v.grand != null) $sum.find('.sum-grand').text((+v.grand).toLocaleString(
                            'id-ID'));
                        // if (v.sel_total != null) $sum.find('.sum-selected').text((+v.sel_total)
                        //     .toLocaleString('id-ID'));
                        if (v.sel_total != null) {
                            const selTotal = +v.sel_total || 0; // NET (tanpa pajak) dari server
                            const ppn = +(v.ppn ?? 11) / 100;
                            const pph = +(v.pph ?? 0) / 100;
                            const selGrand = selTotal * (1 + ppn + pph); // GROSS utk tampilan
                            $sum.find('.sum-selected')
                                .attr('data-raw', String(selTotal)) // simpan NET untuk payload
                                .text(selGrand.toLocaleString('id-ID'));
                        }
                    }
                });
            }

            if (typeof DETAIL_MATRIX !== 'undefined' && Array.isArray(DETAIL_MATRIX)) {
                $('#cvBody tr').each(function(rowIdx) {
                    const rowMap = DETAIL_MATRIX[rowIdx] || {};
                    Object.keys(rowMap).forEach(vcode => {
                        const cell = rowMap[vcode];
                        const $price = $(
                            `#cvBody tr:eq(${rowIdx}) input.price-input[data-vendor="${CSS.escape(vcode)}"]`
                        );
                        if (!$price.length) return;
                        $price.val(formatPrice2(cell.price ?? 0));
                        $price.closest('td').find('.total-label').text(((cell.total ?? 0))
                            .toLocaleString('id-ID'));
                        if (cell.selected) {
                            $price.closest('td').find('input.pick-vendor').prop('checked', true);
                        }
                    });
                });
            }

            // recalc awal semua vendor
            $('#cvTable thead th[id^="th-vendor-"]').each(function() {
                const vid = String($(this).data('vendor-id'));
                recalcSummaryVendor(vid);
            });

            /* ========== 7) Definisi fungsi GLOBAL ========== */
            // -> jangan pakai let/const supaya jadi binding global (dipakai file lain yang memanggil langsung)
            calcCellTotal = function($input) {
                const $tr = $input.closest('tr');
                const qty = parseQty($tr.find('.qty-input').val());
                const price = parsePrice($input.val());
                const total = qty * price;
                $input.closest('td').find('.total-label').text(total.toLocaleString('id-ID'));
                const vid = String($input.data('vendor'));
                recalcSummaryVendor(vid);
            };

            recalcSummaryVendor = function(vendorId) {
                const key = String(vendorId);
                let total = 0;

                $(`input.price-input[data-vendor="${CSS.escape(key)}"]`).each(function() {
                    const price = parsePrice($(this).val());
                    const qty = parseQty($(this).closest('tr').find('.qty-input').val());
                    total += qty * price;
                });

                const $sumCell = $(`#td-sum-${CSS.escape(key)}`);
                $sumCell.find('.sum-total').text((+total || 0).toLocaleString('id-ID'));

                const ppn = Number($sumCell.find('.sum-ppn').val() || 0) / 100;
                const pph = Number($sumCell.find('.sum-pph').val() || 0) / 100;
                const grand = total * (1 + ppn + pph);
                $sumCell.find('.sum-grand').text((+grand || 0).toLocaleString('id-ID'));

                let selTotal = 0;
                $('#cvBody tr').each(function() {
                    const picked = String($(this).find('input.pick-vendor:checked').val() || '');
                    if (picked === key) {
                        const lbl = $(this).find(`input.price-input[data-vendor="${CSS.escape(key)}"]`)
                            .closest('td').find('.total-label');
                        selTotal += Number((lbl.text() || '0').replace(/[^0-9]/g, ''));
                    }
                });
                // $sumCell.find('.sum-selected').text((+selTotal || 0).toLocaleString('id-ID'));
                // tampilkan TERMASUK pajak (seperti Grand Total)
                const selGrand = selTotal * (1 + ppn + pph);
                const $sel = $sumCell.find('.sum-selected');
                $sel.text((+selGrand || 0).toLocaleString('id-ID'));
                // simpan raw (tanpa pajak) agar payload tidak double count
                $sel.attr('data-raw', String(selTotal || 0));
            };

            /* ========== 8) Hapus kolom vendor ========== */
            $(document).on('click', '.btn-del', function() {
                const id = String($(this).data('id')); // vendor_id
                const $header = $('#th-vendor-' + CSS.escape(id));
                const colIdx = $header.index();

                $header.remove();
                $('#td-sum-' + CSS.escape(id)).remove();
                $('#cvBody tr').each(function() {
                    $(this).children('td').eq(colIdx).remove();
                });

                // recalc sisa vendor
                $('#cvTable thead th[id^="th-vendor-"]').each(function() {
                    recalcSummaryVendor(String($(this).data('vendor-id')));
                });

                vendorCount--;
                $('#emptyMsg').toggle(vendorCount === 0);
            });

            /* ========== 9) Radio change -> recalc selected ========== */
            $(document).on('change', '.pick-vendor', function() {
                recalcSummaryVendor(String($(this).val()));
                recalcAllVendors();
            });
        });
    </script>



    <script>
        // Izinkan: digit, koma, titik, dan tombol kontrol
        $(document).on('keypress', '.qty-input', function(e) {
            const code = e.which || e.keyCode;
            // kontrol: backspace, tab, enter, delete, panah
            if ([8, 9, 13, 37, 38, 39, 40, 46].includes(code)) return;

            const ch = String.fromCharCode(code);
            if (!/[0-9.,]/.test(ch)) {
                e.preventDefault();
                return;
            }

            const v = $(this).val() || '';
            // cegah lebih dari satu pemisah desimal total (koma/titik)
            if ((ch === ',' || ch === '.') && /[.,]/.test(v)) {
                e.preventDefault();
            }
        });


        // Sanitasi saat user mengetik (hapus karakter asing)
        $(document).on('input', '.qty-input', function() {
            // sanitasi yang sudah kamu punya...
            let v = $(this).val() || '';
            v = v.replace(/[^0-9.,]/g, '');
            const firstSepIdx = v.search(/[.,]/);
            if (firstSepIdx !== -1) {
                const head = v.slice(0, firstSepIdx + 1);
                const tail = v.slice(firstSepIdx + 1).replace(/[.,]/g, '');
                v = head + tail;
            }
            $(this).val(v);

            // 🔁 hitung ulang semua price di baris ini
            const $row = $(this).closest('tr');
            $row.find('input.price-input').each(function() {
                // calcCellTotal($(this));
                window.calcCellTotal($(this));
            });
        });



        // Pada blur → format ke 2 desimal dengan koma
        $(document).on('blur', '.qty-input', function() {
            const num = parseQty($(this).val());
            $(this).val(formatQty2(num));
            // trigger recalculation baris yang terkait (pakai harga yang sudah ada)
            const $row = $(this).closest('tr');
            // Jika ada input harga di baris ini, recal semua vendor di baris
            $row.find('input.price-input').each(function() {
                // calcCellTotal($(this));
                window.calcCellTotal($(this));
            });
        });
    </script>
    <script>
        // Ubah "1.234,56" / "1234,56" / "1234.56" → 1234.56 (Number)
        function parseQty(val) {
            if (typeof val !== 'string') val = String(val ?? '');
            val = val.trim();

            // Buang semua selain digit dan pemisah . atau ,
            val = val.replace(/[^0-9.,]/g, '');

            // Jika ada kedua pemisah, ambil yang terakhir sebagai desimal
            const lastComma = val.lastIndexOf(',');
            const lastDot = val.lastIndexOf('.');
            let decimalSep = (lastComma > lastDot) ? ',' : '.';

            // Hilangkan pemisah ribuan (apa pun sebelum decimalSep)
            if (decimalSep === ',') {
                val = val.replace(/\./g, ''); // titik jadi ribuan → buang
                val = val.replace(',', '.'); // koma desimal → titik
            } else {
                val = val.replace(/,/g, ''); // koma ribuan → buang
                // titik sudah desimal → biarkan
            }

            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        // Tampilkan Number → "xx,yy" (2 desimal, koma)
        function formatQty2(val) {
            const n = isNaN(val) ? 0 : Number(val);
            return n.toFixed(2).replace('.', ',');
        }
    </script>

    <script>
        // Parse string "1.234,56" / "1,234.56" → Number 1234.56
        function parsePrice(val) {
            if (typeof val !== 'string') val = String(val ?? '');
            val = val.trim();

            // buang karakter non digit/pemisah
            val = val.replace(/[^0-9.,]/g, '');

            // tentukan pemisah desimal dengan posisi terakhir
            const lastComma = val.lastIndexOf(',');
            const lastDot = val.lastIndexOf('.');
            const decimalSep = (lastComma > lastDot) ? ',' : '.';

            if (decimalSep === ',') {
                // titik = ribuan → buang; koma = desimal → ganti titik
                val = val.replace(/\./g, '').replace(',', '.');
            } else {
                // koma = ribuan → buang; titik = desimal → biarkan
                val = val.replace(/,/g, '');
            }

            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        // Format Number → "1.234,56" (2 desimal, locale id-ID)
        function formatPrice2(n) {
            const num = isNaN(n) ? 0 : Number(n);
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
    </script>

    <script>
        // keypress: batasi karakter
        $(document).on('keypress', '.price-input', function(e) {
            const code = e.which || e.keyCode;
            // kontrol
            if ([8, 9, 13, 37, 38, 39, 40, 46].includes(code)) return;
            const ch = String.fromCharCode(code);
            if (!/[0-9.,]/.test(ch)) {
                e.preventDefault();
                return;
            }

            const v = $(this).val() || '';
            if ((ch === ',' || ch === '.') && /[.,]/.test(v)) {
                e.preventDefault(); // hanya boleh satu pemisah
            }
        });

        // input: sanitasi agar hanya 1 pemisah
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

        // blur: format 2 desimal + ribuan
        $(document).on('blur', '.price-input', function() {
            const num = parsePrice($(this).val());
            $(this).val(formatPrice2(num)); // contoh: 1234.5 → 1.234,50
            // hitung ulang total sel
            calcCellTotal($(this));
        });
    </script>

    <script>
        $(function() {
            // ======== TAX PICKER (Fix) =========
            let taxCache = null; // cache data pajak
            let taxTargetInput = null; // jQuery object input .sum-ppn / .sum-pph
            let taxTargetVendorId = null; // vendor_id (kolom)
            let taxTargetType = null; // 'ppn' | 'pph'

            function openTaxModal($input, vendorId, type) {
                taxTargetInput = $input;
                taxTargetVendorId = String(vendorId);
                taxTargetType = String(type);

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
                taxTargetInput = null;
                taxTargetVendorId = null;
                taxTargetType = null;
            }

            function renderTaxTable(rows) {
                const $tbody = $('#taxTableBody');
                $tbody.empty();
                rows.forEach(r => {
                    $tbody.append(`
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-3 py-2  text-sm ">${r.taxid ?? ''}</td>
                <td class="px-3 py-2  text-sm ">${Number(r.taxrate ?? 0).toFixed(2)}</td>
                <td class="px-3 py-2  text-sm ">${r.descr ?? ''}</td>
                <td class="px-3 py-2 text-right">
                    <button type="button" class="btn-choose-tax rounded bg-indigo-600 px-3 py-1  text-sm  font-semibold text-white hover:bg-indigo-700"
                    data-taxid="${r.taxid}" data-taxrate="${r.taxrate}">
                    Choose
                    </button>
                </td>
                </tr>
            `);
                });
            }

            // Buka modal saat klik kaca pembesar
            $(document).on('click', '.btn-pick-tax', function() {
                const vendorId = $(this).data('vendor'); // vendor_id (kolom)
                const type = $(this).data('for'); // 'ppn' | 'pph'
                const $cell = $(`#td-sum-${CSS.escape(String(vendorId))}`);
                const $input = (type === 'ppn') ? $cell.find('.sum-ppn') : $cell.find('.sum-pph');
                openTaxModal($input, vendorId, type);
            });

            // Tutup modal
            $('#taxModalClose, #taxModalOverlay').on('click', closeTaxModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') closeTaxModal();
            });

            // Cari di modal
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

            // Pilih pajak → set nilai + hidden taxid + recalc
            $(document).on('click', '.btn-choose-tax', function() {
                if (!taxTargetInput) return;

                const taxid = $(this).data('taxid');
                const rate = Number($(this).data('taxrate') || 0);

                // set nilai ke input & trigger input event (agar handler existing jalan)
                taxTargetInput.val(rate.toFixed(2)).trigger('input');

                // set hidden id sesuai tipe
                const $cell = $(`#td-sum-${CSS.escape(String(taxTargetVendorId))}`);
                if (taxTargetType === 'ppn') {
                    $cell.find('.sum-ppn-id').val(taxid);
                } else {
                    $cell.find('.sum-pph-id').val(taxid);
                }

                // pastikan summary vendor dihitung ulang
                if (typeof recalcSummaryVendor === 'function') {
                    recalcSummaryVendor(String(taxTargetVendorId));
                }

                closeTaxModal();
            });
        });
    </script>

    {{-- 1) suntik payload dari controller --}}
    <script>
        const VENDORS_USED =
            @json($vendorsUsed ?? []); // [{ vendor_id, vendor_name, vendor_addr1, phone_number, contact_person, top, ppn, pph, ppn_id, pph_id, total, grand, sel_total }]
        const DETAIL_MATRIX = @json($detailVendorMatrix ?? []); // array per baris: { [vendor_id]: { price, total, selected } }
    </script>

    <script>
        $('#saveBtn').on('click', function(e) {
            e.preventDefault();

            $('#cvTable thead th[id^="th-vendor-"]').each(function() {
                recalcSummaryVendor(String($(this).data('vendor-id')));
            });

            if (!validateQtyLimit()) {
                toastr.error('Ada qty yang melebihi qty awal. Periksa kembali.');
                return;
            }

            // ==== VALIDASI: minimal 1 vendor kolom ====
            const $vendorCols = $('#cvTable thead th[id^="th-vendor-"]');
            if ($vendorCols.length === 0) {
                toastr.error('Pilih minimal 1 vendor.');
                return;
            }

            if (!validatePaymentTerms()) return;

            // ==== VALIDASI: total per-vendor tidak semuanya 0 ====
            let allVendorTotalsZero = true;
            $vendorCols.each(function() {
                const vid = String($(this).data('vendor-id'));
                // const total = numFromText($(`#td-sum-${vid} .sum-total`).text());
                const total = numFromText($(`#td-sum-${CSS.escape(vid)} .sum-total`).text());
                if (total > 0) allVendorTotalsZero = false;
            });
            if (allVendorTotalsZero) {
                toastr.error('Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
                return;
            }

            // Kumpulkan vendor summary (urut sesuai posisi kolom)
            const vendors = [];
            $('#cvTable thead th[id^="th-vendor-"]').each(function(i) {
                if (vendors.length >= 6) return; // hard limit 6
                const $th = $(this);
                const vid = String($th.data('vendor-id'));
                const vcode = String($th.data('vendor-code'));

                // const $sum = $(`#td-sum-${vid}`);
                const $sum = $(`#td-sum-${CSS.escape(vid)}`);
                const total = numFromText($sum.find('.sum-total').text());
                const ppn = Number($sum.find('.sum-ppn').val() || 0);
                const pph = Number($sum.find('.sum-pph').val() || 0);
                const ppnId = $sum.find('.sum-ppn-id').val() || '';
                const pphId = $sum.find('.sum-pph-id').val() || '';
                const tax = total * (ppn / 100) + total * (pph / 100);
                const grand = total + tax;
                // const selTotal = numFromText($sum.find('.sum-selected').text());
                let selTotal = Number($sum.find('.sum-selected').attr('data-raw') || 0);
                if (!selTotal) {
                    // fallback aman kalau attr belum ada (mis. data lama)
                    let tmp = 0;
                    $('#cvBody tr').each(function() {
                        const picked = String($(this).find('input.pick-vendor:checked').val() ||
                            '');
                        if (picked === vid) {
                            const lbl = $(this)
                                .find(`input.price-input[data-vendor="${CSS.escape(vid)}"]`)
                                .closest('td').find('.total-label');
                            tmp += Number((lbl.text() || '0').replace(/[^0-9]/g, ''));
                        }
                    });
                    selTotal = tmp;
                }

                const selTax = selTotal * (ppn / 100) + selTotal * (pph / 100);
                const selGrand = selTotal + selTax;

                vendors.push({
                    id: vid,
                    vendorid: vcode,
                    vendorname: String($th.data('vendor-name') || ''),
                    vendoralamat: String($th.data('vendor-addr') || ''),
                    vendortelp: String($th.data('vendor-phone') || ''),
                    vendorcp: String($th.data('vendor-cp') || ''),
                    vendortop: $th.find('select.cara-bayar').val() || '',
                    vendornote: String($th.find('textarea.vendornote').val() || ''),

                    total: round2(total),
                    ppn: round2(ppn),
                    pph: round2(pph),
                    taxcode: [ppnId, pphId].filter(Boolean).join('+'),
                    tax: round2(tax),
                    grand: round2(grand),

                    selected_total: round2(selTotal),
                    selected_tax: round2(selTax),
                    selected_grand: round2(selGrand),
                });
            });

            // Kumpulkan detail baris
            const details = [];
            $('#cvBody tr').each(function(rowIdx) {
                const $tr = $(this);
                const qty = parseQty($tr.find('.qty-input').val());
                const uom = $tr.data('uom') || '';
                const invId = $tr.data('inventoryid') || '';
                const invDescr = $tr.data('inventory_descr') || '';
                const lastPrice = Number($tr.data('lastprice') || 0);
                const csNote = String($tr.find('.note-input').val() || '');

                const row = {
                    inventoryid: invId,
                    inventory_descr: invDescr,
                    qty: round2(qty),
                    uom: uom,
                    inventory_last_price: round2(lastPrice),
                    csnote_detail: csNote,
                    vendor: []
                };

                const picked = String($tr.find('input.pick-vendor:checked').val() || '');

                $('#cvTable thead th[id^="th-vendor-"]').each(function(i) {
                    if (i >= 6) return;
                    const vendorId = String($(this).data('vendor-id'));
                    const vendorIdCode = String($(this).data('vendor-code'));
                    const $priceInput = $tr.find(`input.price-input[data-vendor="${vendorId}"]`);
                    const price = parsePrice($priceInput.val());
                    const total = qty * price;

                    row.vendor.push({
                        id: vendorId,
                        vendorid: vendorIdCode,
                        price: round2(price),
                        total: round2(total),
                        selected: vendorId === picked
                    });
                });

                details.push(row);
            });

            // FormData dari form yang benar
            const fd = new FormData(document.getElementById('csForm'));
            fd.append('vendors', JSON.stringify(vendors));
            fd.append('details', JSON.stringify(details));
            fd.append('action', 'save');

            showOverlay('Submitting');

            $.ajax({
                url: "{{ route('csjobs.update', $cs->csid) }}",
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                beforeSend: function(xhr) {
                    fd.append('_method', 'PUT');
                },
                success: function(res) {
                    hideOverlay();
                    toastr.success('CS berhasil disimpan.');
                    // window.location.href = "/csjobs";
                    window.location.href = res.redirect ?? window.location.href;
                },
                error: function(xhr) {
                    hideOverlay();
                    let msg = 'Gagal menyimpan CS.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toastr.error(msg);
                }
            });
        });


        // helpers number
        function numFromText(t) {
            t = String(t || '');
            t = t.replace(/\./g, '').replace(',', '.').replace(/[^0-9.-]/g, '');
            const n = parseFloat(t);
            return isNaN(n) ? 0 : n;
        }

        function round2(n) {
            return Math.round((+n + Number.EPSILON) * 100) / 100;
        }
    </script>

    <script>
        $('#submitBtn').on('click', function(e) {
            e.preventDefault();

            // ===== VALIDASI FRONTEND =====
            if (!validateQtyLimit()) {
                toastr.error('Ada qty yang melebihi qty awal. Periksa kembali.');
                return;
            }

            if (!validateBQvsCS()) {
                return; // modal mismatch akan muncul
            }

            if (!validatePaymentTerms()) return;

            // ==== VALIDASI: minimal 1 vendor kolom ====
            const $vendorCols = $('#cvTable thead th[id^="th-vendor-"]');
            if ($vendorCols.length === 0) {
                toastr.error('Pilih minimal 1 vendor.');
                return;
            }

            // ==== VALIDASI total vendor !== 0 ====
            let allVendorTotalsZero = true;
            $vendorCols.each(function() {
                const vid = String($(this).data('vendor-id'));
                const total = numFromText($(`#td-sum-${CSS.escape(vid)} .sum-total`).text());
                if (total > 0) allVendorTotalsZero = false;
            });

            if (allVendorTotalsZero) {
                toastr.error('Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
                return;
            }

            // ==== NEW: minimal ada vendor dipilih ====
            if ($('.pick-vendor:checked').length === 0) {
                toastr.error('Pilih vendor pada minimal satu item.');
                return;
            }

            // ==== NEW: baris harga > 0 wajib pilih vendor ====
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

            // ==========================================
            // KUMPULKAN DATA vendors & details
            // ==========================================
            const vendors = [];
            $('#cvTable thead th[id^="th-vendor-"]').each(function(i) {
                if (vendors.length >= 6) return;

                const $th = $(this);
                const vid = String($th.data('vendor-id'));
                const vcode = String($th.data('vendor-code'));

                const $sum = $(`#td-sum-${CSS.escape(vid)}`);
                const total = numFromText($sum.find('.sum-total').text());

                const ppn = Number($sum.find('.sum-ppn').val() || 0);
                const pph = Number($sum.find('.sum-pph').val() || 0);
                const ppnId = $sum.find('.sum-ppn-id').val() || '';
                const pphId = $sum.find('.sum-pph-id').val() || '';

                const tax = total * (ppn / 100) + total * (pph / 100);
                const grand = total + tax;

                let selTotal = Number($sum.find('.sum-selected').attr('data-raw') || 0);
                if (!selTotal) {
                    // fallback (untuk data lama)
                    let tmp = 0;
                    $('#cvBody tr').each(function() {
                        const picked = String($(this).find('.pick-vendor:checked').val() || '');
                        if (picked === vid) {
                            const lbl = $(this)
                                .find(`input.price-input[data-vendor="${CSS.escape(vid)}"]`)
                                .closest('td').find('.total-label');
                            tmp += Number((lbl.text() || '').replace(/[^0-9]/g, ''));
                        }
                    });
                    selTotal = tmp;
                }

                const selTax = selTotal * (ppn / 100) + selTotal * (pph / 100);
                const selGrand = selTotal + selTax;

                vendors.push({
                    id: vid,
                    vendorid: vcode,
                    vendorname: String($th.data('vendor-name') || ''),
                    vendoralamat: String($th.data('vendor-addr') || ''),
                    vendortelp: String($th.data('vendor-phone') || ''),
                    vendorcp: String($th.data('vendor-cp') || ''),
                    vendortop: $th.find('select.cara-bayar').val() || '',
                    vendornote: String($th.find('textarea.vendornote').val() || ''),

                    total: round2(total),
                    ppn: round2(ppn),
                    pph: round2(pph),
                    taxcode: [ppnId, pphId].filter(Boolean).join('+'),
                    tax: round2(tax),
                    grand: round2(grand),

                    selected_total: round2(selTotal),
                    selected_tax: round2(selTax),
                    selected_grand: round2(selGrand),
                });
            });

            // ==========================================
            // DETAIL
            // ==========================================
            const details = [];
            $('#cvBody tr').each(function(rowIdx) {
                const $tr = $(this);
                const qty = parseQty($tr.find('.qty-input').val());
                const uom = $tr.data('uom') || '';
                const invId = $tr.data('inventoryid') || '';
                const invDescr = $tr.data('inventory_descr') || '';
                const lastPrice = Number($tr.data('lastprice') || 0);
                const csNote = String($tr.data('note') || '');

                const row = {
                    inventoryid: invId,
                    inventory_descr: invDescr,
                    qty: round2(qty),
                    uom,
                    inventory_last_price: round2(lastPrice),
                    csnote_detail: csNote,
                    vendor: []
                };

                const picked = String($tr.find('.pick-vendor:checked').val() || '');

                $('#cvTable thead th[id^="th-vendor-"]').each(function(i) {
                    if (i >= 6) return;

                    const vendorId = String($(this).data('vendor-id'));
                    const vendorIdCode = String($(this).data('vendor-code'));

                    const price = parsePrice(
                        $tr.find(`input.price-input[data-vendor="${vendorId}"]`).val()
                    );

                    const total = qty * price;

                    row.vendor.push({
                        id: vendorId,
                        vendorid: vendorIdCode,
                        price: round2(price),
                        total: round2(total),
                        selected: vendorId === picked
                    });
                });

                details.push(row);
            });

            // ==========================================
            // CEK QTY DULU (PENTING!)
            // ==========================================
            const doc = $('input[name="doc"]').val();
            const src_id = $('input[name="src_id"]').val();
            // const src_id = $('input[name="sppbjktid"]').val();
            console.log('Checking qty for doc', doc, 'src_id', src_id);
            showOverlay('Validating qty...');

            $.ajax({
                url: "{{ route('cs.check-qty') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    doc,
                    src_id,
                    details: JSON.stringify(details)
                },
                success: function(res) {

                    // ================================
                    // LOL0S → SUBMIT EDIT CS
                    // ================================
                    const fd = new FormData(document.getElementById('csForm'));
                    fd.append('vendors', JSON.stringify(vendors));
                    fd.append('details', JSON.stringify(details));
                    fd.append('action', 'submit');
                    fd.append('_method', 'PUT');

                    showOverlay('Submitting...');

                    $.ajax({
                        url: "{{ route('csjobs.update', $cs->csid) }}",
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,

                        success: function() {
                            hideOverlay();
                            toastr.success('CS berhasil disubmit.');
                            window.location.href = "/cslist";
                        },

                        error: function(xhr) {
                            hideOverlay();
                            toastr.error(xhr.responseJSON?.message ||
                                'Gagal menyimpan CS.');
                        }
                    });
                },

                error: function(xhr) {
                    hideOverlay();
                    const res = xhr.responseJSON || {};
                    toastr.error(res.message || 'Qty tidak valid.');

                    if (Array.isArray(res.errors)) {
                        res.errors.forEach(err => {
                            $('#cvBody tr').eq(err.row_index).addClass('bg-red-100');
                        });
                    }
                }
            });
        });

        // HELPERS
        function numFromText(t) {
            t = String(t || '').replace(/\./g, '').replace(',', '.').replace(/[^0-9.-]/g, '');
            const n = parseFloat(t);
            return isNaN(n) ? 0 : n;
        }

        function round2(n) {
            return Math.round((+n + Number.EPSILON) * 100) / 100;
        }
    </script>


    <script>
        // validasi: qty edit tidak boleh > qty awal
        function validateQtyLimit() {
            let ok = true;

            $('#cvBody tr').each(function() {
                const $tr = $(this);
                const max = Number($tr.data('original_qty')); // qty awal dari server
                const $inp = $tr.find('.qty-input');
                const cur = parseQty($inp.val());

                // reset state
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

        // saat user keluar dari field qty → auto-koreksi ke max jika melebihi
        $(document).on('blur', '.qty-input', function() {
            const $tr = $(this).closest('tr');
            const max = Number($tr.data('original_qty'));
            const curN = parseQty($(this).val());

            if (isFinite(max) && curN > max) {
                $(this).addClass('is-invalid');
                // tampilkan/refresh pesan error
                $tr.find('.qty-error').remove();
                $('<div class="error-feedback qty-error">Qty dikembalikan ke maksimum: ' + formatQty2(max) +
                        '.</div>')
                    .insertAfter($(this));

                // kembalikan ke max dan format
                $(this).val(formatQty2(max));

                // trigger hitung ulang total per vendor di baris ini
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
        const CS_VENDOR_TOTALS = @json($csVendorTotals ?? []);
        const BQ_VENDOR_TOTALS = @json($bqVendorTotals ?? []);
        const BQ_EXISTS = @json(!!($bq ?? null));
    </script>

    <script>
        (function() {
            // toleransi perbandingan (mis. 1 rupiah)
            const EPS = 1;

            function fmtIDR(n) {
                return (Number(n) || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Render isi modal mismatch
            function showMismatchTable(rows) {
                const $tbody = $('#bqcsMismatchBody').empty();
                rows.forEach(r => {
                    const cls = 'text-red-600 dark:text-red-400 font-semibold';
                    $tbody.append(`
                <tr>
                <td class="px-3 py-2">${_.escape(r.vendor_label)}</td>
                <td class="px-3 py-2 text-right ${cls}">${fmtIDR(r.bq)}</td>
                <td class="px-3 py-2 text-right ${cls}">${fmtIDR(r.cs)}</td>
                <td class="px-3 py-2 text-right ${cls}">${fmtIDR(r.diff)}</td>
                </tr>
            `);
                });
                $('#bqcsMismatchModal').removeClass('hidden');
            }

            $('#bqcsMismatchClose, #bqcsMismatchOk').on('click', function() {
                $('#bqcsMismatchModal').addClass('hidden');
            });

            // validasi utama — dipanggil sebelum submit
            window.validateBQvsCS = function() {
                // kalau bukan SPPJ/SPPT, aturan ini optional — kalau kamu mau hanya berlaku utk dok tsb:
                const docType = "{{ $doc }}";
                const requiresBQ = (docType === 'SPPJ' || docType === 'SPPT');

                if (requiresBQ && !BQ_EXISTS) {
                    toastr.error('BQ belum dibuat untuk dokumen ini. Buat/isi BQ terlebih dahulu sebelum submit.');
                    return false;
                }

                // jika tidak ada data perbandingan, anggap lolos
                if (!requiresBQ) return true;
                if (!CS_VENDOR_TOTALS || !BQ_VENDOR_TOTALS) return true;

                const mismatches = [];

                // loop index 1..6
                for (let i = 1; i <= 6; i++) {
                    const csRow = CS_VENDOR_TOTALS[i];
                    const bqRow = BQ_VENDOR_TOTALS[i];

                    // Jika vendor tidak ada di keduanya, lewati
                    if (!csRow && !bqRow) continue;

                    const vendorName = (csRow?.vendorname || csRow?.vendorid || `Vendor ${i}`);
                    const csTotal = Number(csRow?.total_cs || 0);
                    const bqSum = Number(bqRow?.sum_bq || 0);

                    // Kalau dua-duanya 0, anggap cocok
                    const diff = Math.abs(bqSum - csTotal);
                    if (diff > EPS) {
                        mismatches.push({
                            idx: i,
                            vendor_label: vendorName,
                            bq: bqSum,
                            cs: csTotal,
                            diff: bqSum - csTotal
                        });
                    }
                }

                if (mismatches.length > 0) {
                    showMismatchTable(mismatches);
                    return false;
                }

                return true;
            };
        })();
    </script>
    <script>
        function recalcAllVendors() {
            $('#cvTable thead th[id^="th-vendor-"]').each(function() {
                const vid = String($(this).data('vendor-id'));
                recalcSummaryVendor(vid);
            });
        }
    </script>

    <script>
        window.htmlEscape = function(s) {
            s = String(s ?? '');
            return s.replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [m]));
        };
    </script>

    <script>
        function validatePaymentTerms() {
            let ok = true;

            // reset state lama
            $('#cvTable thead th[id^="th-vendor-"] select.cara-bayar').removeClass('is-invalid');

            $('#cvTable thead th[id^="th-vendor-"]').each(function() {
                const $th = $(this);
                const $top = $th.find('select.cara-bayar');
                const val = $top.val();

                if (!val) {
                    ok = false;
                    $top.addClass('is-invalid'); // tampilkan border merah
                    // scroll ke kolom vendor yang belum diisi
                    const th = $th.get(0);
                    if (th && th.scrollIntoView) th.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                    // fokuskan ke select agar langsung bisa dipilih
                    setTimeout(() => $top.trigger('focus'), 150);
                    // break dari .each
                    return false;
                }
            });

            if (!ok) {
                toastr.error('Payment Term (TOP) wajib diisi untuk semua vendor.');
            }
            return ok;
        }

        // hilangkan merah ketika user memilih nilai
        $(document).on('change', 'select.cara-bayar', function() {
            if ($(this).val()) $(this).removeClass('is-invalid');
        });
    </script>

    <script>
        $('#cancelBtn').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Cancel CS?',
                text: 'CS akan dibatalkan (Cancel). Proses ini tidak bisa dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                // optional: minta alasan cancel
                Swal.fire({
                    title: 'Reason (optional)',
                    input: 'text',
                    inputPlaceholder: 'Tulis alasan cancel...',
                    showCancelButton: true,
                    confirmButtonText: 'Submit Cancel',
                    cancelButtonText: 'Back'
                }).then((r2) => {
                    if (!r2.isConfirmed) return;

                    const reason = (r2.value || '').trim();

                    showOverlay('Cancelling...');

                    $.ajax({
                        url: "{{ route('csjobs.cancel', $cs->csid) }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT',
                            reason: reason
                        },
                        success: function(res) {
                            hideOverlay();
                            toastr.success(res.message || 'CS berhasil dicancel.');
                            window.location.href = res.redirect || '/cslist';
                        },
                        error: function(xhr) {
                            hideOverlay();
                            toastr.error(xhr.responseJSON?.message ||
                                'Gagal cancel CS.');
                        }
                    });
                });
            });
        });
    </script>

    <script>
        function formatNumID(n) {
            n = Number(n || 0);
            return n.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

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

        // $('#lastPriceModalClose, #lastPriceModalOverlay').on('click', closeLastPriceModal);
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
            if (e.key === 'Enter') {
                e.preventDefault();

                const inputs = Array.from(
                    this.querySelectorAll('input, select, textarea')
                ).filter(el => !el.disabled && el.tabIndex !== -1);

                const index = inputs.indexOf(document.activeElement);
                if (index > -1 && index + 1 < inputs.length) {
                    inputs[index + 1].focus();
                }
            }
        });
    </script>





    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4/lodash.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</x-app-layout>
