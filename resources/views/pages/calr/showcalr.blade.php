<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- === Styles (fixed CSS typos) === --}}
    <style>
        /* Overlay full-screen */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            display: none;
            /* akan ditampilkan via JS */
            background: rgba(17, 24, 39, .55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        /* Kartu spinner di tengah */
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

        /* Spinner dual ring */
        #loadingSpinnerContainer .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;
            /* indigo-500 */
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
            /* indigo-200 */
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


    @php
        $statusText = match ($calr->status) {
            'P' => 'Pending',
            'A' => 'Approved',
            'R' => 'Rejected',
            'C' => 'Completed',
            'X' => 'Canceled',
            default => 'Unknown',
        };
        $statusClasses = match ($calr->status) {
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            'A' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'C' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
            'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };
        $nf0 = fn($n) => number_format((float) $n, 0, ',', '.');
        $nf2 = fn($n) => number_format((float) $n, 2, ',', '.');
    @endphp

    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>

            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-xs font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Revise
                </button>
                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-xs font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Reject
                </button>
            </div>
        </div>

        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="flex flex-col gap-6 sm:w-1/2 md:w-full xl:flex-row">
                {{-- Left card (Calr Info) --}}
                <div class="rounded-xl bg-white duration-300 sm:w-1/2 md:w-full dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">ID</span>
                            {{ $calr->calrid }}
                        </h1>

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>


                            <a href="{{ url('/pdf_calr') }}/{{ $hash }}" target="_blank">
                                <button
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-xs font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>

                        </div>
                    </header>

                    @php
                        $fmtDate = function ($v) {
                            return $v ? \Carbon\Carbon::parse($v)->format('d M Y') : '-';
                        };
                        $fmtMoney = function ($v) {
                            if (is_null($v) || $v === '') {
                                return '-';
                            }
                            $num = (float) $v;
                            $sign = $num < 0 ? '-' : '';
                            $num = abs($num);
                            return $sign . number_format($num, 0, ',', '.');
                        };
                    @endphp

                    <div class="flex flex-1 flex-col overflow-y-auto p-4">

                        @php
                            // Reusable layout classes
                            $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                            $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                            $fields = [
                                [
                                    'icon' => 'calendar-days',
                                    'label' => 'CALR Date',
                                    'value' => $fmtDate($calr->calrdate),
                                ],
                                [
                                    'icon' => 'identification',
                                    'label' => 'RFCA ID',
                                    'value' =>
                                        !empty($rfcaUrl) && !empty($calr->rfcaid)
                                            ? '<a href="' .
                                                $rfcaUrl .
                                                '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' .
                                                e($calr->rfcaid) .
                                                '</a>'
                                            : $calr->rfcaid ?? '-',
                                ],

                                [
                                    'icon' => 'tag',
                                    'label' => 'RFCA Type',
                                    'value' => $calr->rfca_type ?? '-',
                                ],
                                [
                                    'icon' => 'hashtag',
                                    'label' => 'PO Nbr',
                                    'value' => !empty($poUrl)
                                        ? '<a href="' .
                                            $poUrl .
                                            '" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">' .
                                            e($calr->ponbr) .
                                            '</a>'
                                        : $calr->ponbr ?? '-',
                                ],
                                [
                                    'icon' => 'building-office',
                                    'label' => 'Company',
                                    'value' => $calr->cpny_id ?? '-',
                                ],
                                [
                                    'icon' => 'squares-2x2',
                                    'label' => 'Department',
                                    'value' => $calr->department_id ?? '-',
                                ],
                                [
                                    'icon' => 'user',
                                    'label' => 'Requester',
                                    'value' => $calr->user_peminta ?? '-',
                                ],
                                [
                                    'icon' => 'building-storefront',
                                    'label' => 'Vendor',
                                    'value' => $calr->vendorname ?? '-',
                                ],
                                [
                                    'icon' => 'document-duplicate',
                                    'label' => 'CS ID',
                                    'value' =>
                                        !empty($csUrl) && !empty($calr->csid)
                                            ? '<a href="' .
                                                $csUrl .
                                                '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                                e($calr->csid) .
                                                '</a>'
                                            : $calr->csid ?? '-',
                                ],
                                [
                                    'icon' => 'document-text',
                                    'label' => 'SPPB/J/K/T',
                                    'value' =>
                                        !empty($sppbUrl) && !empty($calr->sppbjktid)
                                            ? '<a href="' .
                                                $sppbUrl .
                                                '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">' .
                                                e($calr->sppbjktid) .
                                                '</a>'
                                            : $calr->sppbjktid ?? '-',
                                ],
                                [
                                    'icon' => 'clipboard-document-list',
                                    'label' => 'Purpose',
                                    'value' => $calr->keperluan ?? '-',
                                ],

                                // Financials
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'RFCA Amount',
                                    'value' => 'Rp ' . $fmtMoney($calr->rfca_amount),
                                ],
                                [
                                    'icon' => 'currency-dollar',
                                    'label' => 'CALR Amount',
                                    'value' => 'Rp ' . $fmtMoney($calr->calr_amount),
                                ],
                                [
                                    'icon' => 'scale',
                                    'label' => 'Balance Amount',
                                    'value' => 'Rp ' . $fmtMoney($calr->balance_amount),
                                ],

                                // Audit info
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Created By',
                                    'value' => $calr->created_by ?? '-',
                                ],
                                [
                                    'icon' => 'user-circle',
                                    'label' => 'Updated By',
                                    'value' => $calr->updated_by ?? '-',
                                ],
                            ];
                        @endphp

                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-xs sm:grid-cols-2">
                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{!! $value !!}">{!! $f['value'] !!}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Right card (Tabs) --}}
                <div class="flex flex-col gap-4 rounded-xl duration-300 sm:w-1/2 md:w-full">
                    <div class="flex flex-1 flex-col rounded-xl bg-white dark:bg-gray-800">
                        <div x-data="{ activeTab: 'attachment' }" class="flex flex-1 flex-col">
                            <header
                                class="sticky top-0 z-10 flex items-center rounded-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                                <nav class="flex flex-grow">
                                    <button @click="activeTab = 'attachment'"
                                        :class="activeTab === 'attachment' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-xs font-medium">Attachment
                                    </button>
                                    <button @click="activeTab = 'approval'"
                                        :class="activeTab === 'approval'
                                            ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-xs font-medium transition-colors duration-200">
                                        Approval Details
                                    </button>

                                    <button @click="activeTab = 'comments'"
                                        :class="activeTab === 'comments' ?
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                        class="flex-1 px-4 py-2 text-center text-xs font-medium">Comments</button>
                                </nav>
                            </header>

                            <div class="flex flex-1 flex-col">
                                <div x-show="activeTab === 'approval'" class="flex-1 p-4 transition-all">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr
                                                class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                <th class="p-3 text-left font-semibold">Level</th>
                                                <th class="p-3 text-left font-semibold">Name</th>
                                                <th class="p-3 text-left font-semibold">Date</th>
                                                <th class="p-3 text-left font-semibold">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="approval-table-body">
                                        </tbody>

                                    </table>
                                </div>

                                {{-- Attachment Tab --}}
                                <div x-show="activeTab === 'attachment'"
                                    class="flex h-full flex-1 flex-col transition-all">
                                    <div class="flex-1 overflow-auto rounded-lg">
                                        <table class="w-full text-xs">
                                            <thead class="text-gray-600 dark:text-gray-300">
                                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                                    <th class="p-3 text-left font-semibold">Filename</th>
                                                    <th class="p-3 text-left font-semibold">Created By</th>
                                                    <th class="p-3 text-left font-semibold">Date</th>
                                                </tr>
                                            </thead>

                                            <tbody id="rcpAttachmentTbody"></tbody>
                                        </table>
                                        @if ($canUpload)
                                            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                                <form id="rcpAttachmentUploadForm" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                        <div class="flex-1">
                                                            <label for="rcpAttachFiles"
                                                                class="mb-2 block text-xs font-semibold text-gray-800 dark:text-gray-200">
                                                                Upload Attachments
                                                            </label>
                                                            <div class="flex items-center gap-3">
                                                                <input type="hidden" name="cpnyid"
                                                                    value="{{ $calr->cpny_id }}">
                                                                <input type="hidden" name="departementid"
                                                                    value="{{ $calr->department_id }}">
                                                                <input type="file" id="rcpAttachFiles"
                                                                    name="attachments[]" multiple
                                                                    class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-xs text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                                <button type="button" id="btnUploadSppbAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                                    Upload
                                                                </button>
                                                                <button type="button" id="btnResetSppbAttachment"
                                                                    class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                                    Reset
                                                                </button>
                                                            </div>
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                Max 10 files, PDF / Image preferred.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Comments Tab --}}
                                <div x-show="activeTab === 'comments'" class="flex-1 transition-all">
                                    <div class="flex h-full flex-col">
                                        <div id="commentList"
                                            class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                            <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                            <input id="commentInput" type="text" placeholder="Write a comment..."
                                                class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                            <button id="postCommentBtn" type="button"
                                                class="rounded-lg bg-indigo-600 px-5 py-3 text-xs font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                Post 🚀
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

            </div>


            {{-- CALR / PO Detail (TrPOdetail) --}}
            <div class="mt-6 rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                <div class="mb-4 border-b border-gray-200 pb-3 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">
                        PO Detail
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Inventory</th>
                                <th class="px-3 py-2 text-right font-semibold">Qty</th>
                                <th class="px-3 py-2 text-left font-semibold">UOM</th>
                                <th class="px-3 py-2 text-right font-semibold">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($details as $d)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-2">{{ $d->inventory_descr }}</td>
                                    <td class="px-3 py-2 text-right">
                                        {{ number_format((float) $d->qty, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2">{{ $d->uom }}</td>
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format((float) $d->totalcost, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                        No PO detail found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>




        </div>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Reject</h2>
            <textarea id="rejectReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter rejection reason..."></textarea>
            <div class="mt-4 flex justify-between">
                <button id="cancelRejectBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmRejectBtn" class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                    Reject
                </button>
            </div>
        </div>
    </div>
    <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Task</h2>
            <textarea id="reviseReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>
            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-xs font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    Revise
                </button>
            </div>
        </div>
    </div>



    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">Processing<span
                    class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span></div>
        </div>
    </div>

    {{-- dayjs & toastr --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>

    {{-- Comments --}}
    <script>
        $(document).ready(function() {
            const calrid = "{{ $calr->calrid }}";
            const doctype = "CA";

            loadComments(calrid, doctype);

            function loadComments(refnbr, doctype) {
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>');

                $.ajax({
                    url: `/comments/${doctype}/${refnbr}`,
                    type: 'GET',
                    success: function(response) {
                        commentList.empty();

                        if (!response.comments || response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            // fallback jika data lama masih punya created_at
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class="text-xs font-semibold">
                                        ${comment.username}
                                        <span class="text-xs text-gray-500">(${timeAgo})</span>
                                    </p>
                                    <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                </div>
                            `);
                        });
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            function addComment() {
                let input = $('#commentInput').val().trim();
                if (input === "") {
                    alert("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀');

                $.ajax({
                    url: `/comments/${doctype}/${calrid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(calrid, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                            "Unknown Error"));
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text('Post 🚀');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });


        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Approve button -> cek authorize -> buka modal + load ratings
        $(document).on("click", "#approveBtn", function() {
            const calrid = "{{ $calr->calrid }}";
            const $spinner = $("#loadingSpinnerContainer");
            $spinner.fadeIn();

            let authorized = false;

            $.ajax({
                    url: `/approval/${encodeURIComponent(calrid)}/check/approve?doctype=CA`,
                    type: "GET"
                })
                .done(function(resp) {
                    authorized = !!(resp && resp.canPerformAction);
                    if (!authorized) toastr.error("You are not authorized to approve this Calr.");
                })
                .fail(function() {
                    toastr.error("Error checking approval status.");
                })
                .always(function() {
                    // setelah spinner hilang, kalau authorized → buka modal & load ratings
                    $spinner.fadeOut(150, async function() {
                        if (authorized) {
                            openRatingModal();
                            // tampilkan skeleton sementara
                            $ratingTbody.html(
                                `<tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Loading ratings…</td></tr>`
                            );
                            try {
                                await loadRatings(calrid);
                            } catch (_) {
                                /* error sudah ditangani di loadRatings */
                            }
                        }
                    });
                });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                let calrid = "{{ $calr->calrid }}";
                checkApproval(calrid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let calrid = "{{ $calr->calrid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/calr/${calrid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: calrid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal calr
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Calr Rejected successfully!");
                            window.location.href = "/calrlist";
                        } else {
                            alert("Failed to reject calr.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject calr status.");
                        }
                    },
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Saat tombol "Revise" ditekan, tampilkan modal Revise di depan
            $(document).on("click", "#reviseBtn", function() {
                $("#reviseReason").val(""); // Reset alasan revise
                // $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                let calrid = "{{ $calr->calrid }}";
                checkApproval(calrid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let calrid = "{{ $calr->calrid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/calr/${calrid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: calrid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal calr
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Calr Revised successfully!");
                            window.location.href = "/calrlist";
                        } else {
                            alert("Failed to revise calr.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise calr status.");
                        }
                    },
                });
            });
        });
    </script>

    <script>
        function checkApproval(calrid, action) {
            $.ajax({
                url: `/approval/${calrid}/check/${action}?doctype=CA`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {

                        if (action === "reject") {
                            $("#rejectReason").val("");
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");

                        } else if (action === "revise") {
                            $("#reviseReason").val("");
                            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                        }

                    } else {
                        toastr.error("You are not authorized to " + action + " this Calr.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>

    <script>
        $(function() {
            const listUrl = @json(route('attachments.list', ['doctype' => 'CA', 'refnbr' => $calr->calrid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'CA', 'refnbr' => $calr->calrid]));

            function $tbody() {
                return $('#rcpAttachmentTbody');
            } // <tbody id="rcpAttachmentTbody">

            function renderSppbAttachmentRows(rows) {
                const $tb = $tbody().empty();

                if (!rows || !rows.length) {
                    $tb.append(`
                <tr>
                <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                    No attachments found.
                </td>
                </tr>
            `);
                    return;
                }

                rows.forEach(at => {
                    const fileName = at.name || at.display_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') :
                        '-';
                    const linkHtml = at.url ?
                        `<a href="${at.url}" target="_blank"
                    class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 ${fileName}</a>` :
                        `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span>
                <span class="ml-2 text-xs text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                <td class="p-3">${linkHtml}</td>
                <td class="p-3">${createdBy}</td>
                <td class="p-3">${dateStr}</td>
                </tr>
            `);
                });
            }

            function refreshSppbAttachments() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderSppbAttachmentRows(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            // optional: load saat tab dibuka / page load
            refreshSppbAttachments();

            $('#btnUploadSppbAttachment').on('click', function() {
                const $form = $('#rcpAttachmentUploadForm')[0];
                const files = $('#rcpAttachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);
                if (typeof showOverlay === 'function') showOverlay('Uploading');

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }
                        toastr.success('Upload success.');
                        $('#rcpAttachFiles').val('');
                        // back-end sudah mengembalikan list terbaru
                        renderSppbAttachmentRows(res.attachments || []);
                    },
                    error: function(xhr) {
                        if (typeof hideOverlay === 'function') hideOverlay();
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    }
                });
            });

            $('#btnResetSppbAttachment').on('click', function() {
                $('#rcpAttachFiles').val('');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const calrid = "{{ $calr->calrid }}"; // contoh: PB2501010001
            const doctype = "CA";

            loadApproval(calrid, doctype);
        });

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");
                    tbody.innerHTML = ""; // reset

                    res.data.forEach(row => {
                        const statusLabel = getStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="p-3">${row.aprv_leveling}</td>
                                <td class="p-3">${row.aprv_name}</td>
                                <td class="p-3">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                </td>
                                <td class="p-3">${statusLabel}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error("Approval fetch failed →", err));
        }

        function formatDate(dateString) {
            if (!dateString) return "-";
            const d = new Date(dateString);
            const options = {
                year: "numeric",
                month: "short",
                day: "numeric"
            };
            return d.toLocaleDateString("en-US", options);
        }

        function getStatusLabel(status) {
            let statusText = "";
            let statusClass = "";

            switch (status) {
                case "P":
                    statusText = "Waiting Approval";
                    statusClass = "bg-yellow-500 text-white";
                    break;
                case "A":
                    statusText = "Approved";
                    statusClass = "bg-green-500 text-white";
                    break;
                case "R":
                    statusText = "Rejected";
                    statusClass = "bg-red-500 text-white";
                    break;
                case "D":
                    statusText = "Revise";
                    statusClass = "bg-blue-500 text-white";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "bg-gray-500 text-white";
            }

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-xs font-semibold">${statusText}</span>`;
        }
    </script>


    {{-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('printMenuBtn');
            const menu = document.getElementById('printMenu');

            const open = () => {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            };
            const close = () => {
                menu.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            };

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.contains('hidden') ? open() : close();
            });
            document.addEventListener('click', (e) => {
                if (!menu.classList.contains('hidden') && !menu.contains(e.target) && e.target !== btn)
                    close();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });
        });
    </script> --}}




</x-app-layout>
