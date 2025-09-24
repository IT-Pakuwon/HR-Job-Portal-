<x-app-layout>
    <style>
        /* Overlay full-screen di tengah */
        #loadingSpinnerContainer {
            position: fixed;
            inset: 0;
            /* = top/right/bottom/left: 0 */
            display: none;
            /* ditampilkan via JS .fadeIn() */
            display: grid;
            place-items: center;
            /* center horizontal + vertical */
            background: rgba(17, 24, 39, .55);
            /* #111827 dengan transparansi */
            backdrop-filter: blur(2px);
            /* efek blur background */
            z-index: 2000;
        }

        /* Kartu spinner */
        .loading-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            -radius: 16px;
            background: linear-gradient(180deg, rgba(31, 41, 55, .9), rgba(17, 24, 39, .9));
            : 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        /* Spinner dual ring */
        .loading-spinner {
            width: 54px;
            height: 54px;
            -radius: 50%;
            : 4px solid transparent;
            -top-color: #6366f1;
            /* indigo-500 */
            animation: spin 1s linear infinite;
            position: relative;
        }

        .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            -radius: 50%;
            : 4px solid transparent;
            -left-color: #a5b4fc;
            /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        /* Teks */
        .loading-text {
            color: #e5e7eb;
            /* gray-200 */
            font-weight: 600;
            letter-spacing: .02em;
        }

        /* Dots animasi */
        .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }

        .loading-ellipsis span:nth-child(2) {
            animation-delay: .2s;
        }

        .loading-ellipsis span:nth-child(3) {
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

    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <button onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back
                </button>
            </div>

            <div class="flex gap-3">
                <button id="approveBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Approve
                </button>
                <button id="reviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Revise
                </button>
                <button id="rejectBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-700/30 dark:text-red-300 dark:hover:bg-red-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713-.518 1.972-1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                    </svg>
                    Reject
                </button>
            </div>
        </div>
        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <header
                        class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
                            <span class="text-indigo-500">🆔</span>
                            {{ $cs->csid }}
                        </h1>

                        @php
                            $statusText = match ($cs->status) {
                                'D' => 'Revise',
                                'H' => 'Hold',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($cs->status) {
                                'H' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
                                'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
                                'X', 'R' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                            };
                        @endphp

                        <div class="flex items-center gap-3">
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>

                            <a href="{{ url('/pdf_cs') }}/{{ $cs->id }}" target="_blank">
                                <button
                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Print PDF
                                </button>
                            </a>
                        </div>
                    </header>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">SPPB/J/K/T ID : {{ $docid }}</label>
                    <label class="text-sm text-gray-700 dark:text-gray-300">User : {{ ucwords(strtolower(optional($srcHeader->creator)->name)) }}</label>
                    <label class="text-sm text-gray-700 dark:text-gray-300">Company : {{ $srcHeader->cpny_id }}</label>
                    <label class="text-sm text-gray-700 dark:text-gray-300">Department : {{ $srcHeader->department_id }}</label>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Purchaser : {{ ucwords(strtolower(optional($srcHeader->purchaser)->name)) }}</label>
                    @if($cs->bqid)
                    <label class="text-sm text-gray-700 dark:text-gray-300">BQ ID : {{ $srcHeader->bqid }}</label>
                    @endif
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Keperluan : {{ $srcHeader->keperluan }}</label>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-gray-700 dark:text-gray-300">Note CS :</label>
                    <textarea class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            rows="3" readonly>{{ $cs->csnote }}</textarea>
                </div>
            </div>


            {{-- SPPB Detail table --}}
            <div class="flex w-full flex-col rounded-2xl bg-white dark:bg-gray-800">              
                <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                    <div class="flex w-full flex-col rounded-2xl p-4">
                        <details class="group" open>                     
                        <summary
                            class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                            <span>CS Detail</span>
                            <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                details &rarr;</span>
                            <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                details &darr;</span>
                        </summary>
                        <div class="flex h-auto flex-col justify-start">
                            <div class="overflow-x-auto">
                                <table id="cvTable" class="w-max table-auto whitespace-nowrap border">
                                    <thead>
                                    <tr class="bg-gray-100 align-top">
                                        <th class="w-64 border px-3 py-2">Inventory Descr</th>
                                        <th class="w-20 border px-3 py-2 text-center">Qty</th>
                                        <th class="w-16 border px-3 py-2 text-center">UOM</th>
                                        <th class="w-40 border px-3 py-2 text-center">Note</th>

                                        @foreach($vendors as $v)
                                        <th class="border px-3 py-2 align-top" style="width:15rem;max-width:15rem;">
                                            <div class="text-center font-semibold leading-tight">{{ $v['vendorname'] }}</div>
                                            <div class="text-xs text-gray-500 leading-4 mt-0.5 whitespace-normal break-words">
                                            <div>👤 {{ $v['vendorcp'] ?: '-' }}</div>
                                            <div>☎️ {{ $v['vendortelp'] ?: '-' }}</div>
                                            <div>🏠 {{ $v['vendoralamat'] ?: '-' }}</div>
                                            </div>
                                            @if($v['vendortop'])
                                            <div class="mt-1 flex justify-center">
                                                <span class="rounded border px-2 py-0.5 text-xs">{{ $v['vendortop'] }}</span>
                                            </div>
                                            @endif
                                        </th>
                                        @endforeach
                                    </tr>
                                    </thead>

                                    <tbody id="cvBody">
                                    @foreach ($csdetail as $row)
                                        <tr>
                                        <td class="border px-3 py-2">{{ $row->inventory_descr }}</td>
                                        <td class="border px-3 py-2 text-center">
                                            <input type="text" class="w-24 border rounded px-2 text-right bg-gray-50" value="{{ number_format((float)$row->qty, 2, ',', '.') }}" readonly>
                                        </td>
                                        <td class="border px-3 py-2 text-center">{{ $row->uom }}</td>
                                        <td class="border px-3 py-2 text-left">{{ $row->csnote_detail }}</td>

                                        @foreach ($vendors as $v)
                                            @php
                                            $i   = $v['i'];
                                            $prc = (float)($row->{"vendorprice{$i}"} ?? 0);
                                            $tot = (float)($row->{"vendortotalprice{$i}"} ?? 0);
                                            $sel = (bool)($row->{"vendor{$i}selected"} ?? false);
                                            @endphp
                                            <td class="border px-3 py-2">
                                            <div class="flex flex-col items-center gap-0.5 w-full">
                                                <input type="text" class="w-full border rounded px-1 text-right bg-gray-50"
                                                    value="{{ number_format($prc, 2, ',', '.') }}" readonly>
                                                <small class="text-right w-full text-xs font-bold text-gray-600">{{ number_format($tot, 0, ',', '.') }}</small>
                                                <div class="flex justify-center mt-0.5">
                                                <input type="radio" class="h-3 w-3" {{ $sel ? 'checked' : '' }} disabled>
                                                </div>
                                            </div>
                                            </td>
                                        @endforeach
                                        </tr>
                                    @endforeach
                                    </tbody>

                                    <tfoot>
                                    <tr class="bg-gray-50 align-top">
                                        <td colspan="4" class="border px-3 py-2 text-right font-semibold">Ringkasan</td>

                                        @foreach ($vendors as $v)
                                        @php
                                            $ppn = (float)($v['ppn'] ?? 11);
                                            $pph = (float)($v['pph'] ?? 0);
                                        @endphp
                                        <td class="border px-3 py-2 text-xs space-y-1" style="width:15rem;max-width:15rem;">
                                            <div><span class="font-semibold">Total&nbsp;</span><span>{{ number_format($v['total'], 0, ',', '.') }}</span></div>

                                            <div class="flex flex-wrap items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span>PPN&nbsp;</span>
                                                <input type="text" class="w-14 border rounded px-1 text-right bg-gray-50" value="{{ number_format($ppn, 2, ',', '.') }}" readonly>
                                                <span>%</span>
                                                 <span>PPh&nbsp;</span>
                                                <input type="text" class="w-14 border rounded px-1 text-right bg-gray-50" value="{{ number_format($pph, 2, ',', '.') }}" readonly>
                                                <span>%</span>
                                            </div>                                            
                                            </div>

                                            <div><span class="font-semibold">Grand Total&nbsp;</span><span>{{ number_format($v['grand'], 0, ',', '.') }}</span></div>
                                            <div><span class="font-semibold">G.Total Selected&nbsp;</span><span>{{ number_format($v['selected_grand'] ?: $v['selected_total'], 0, ',', '.') }}</span></div>
                                        </td>
                                        @endforeach
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                             <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {{-- Left: Existing Attachments (from controller) --}}
                        <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            @if(($attachment ?? collect())->count())
                                <details class="group" open>
                                    <summary
                                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                        <span>Attachments {{ $doc }}</span>
                                        <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                        <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                    </summary>

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
                                                @foreach ($attachmentBJKT as $at)
                                                    @php
                                                        $year = $at->created_at->year;
                                                        $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                                    @endphp
                                                    <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                        <td class="p-3">
                                                            <a href="{{ $fileUrl }}" target="_blank"
                                                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                                📎 {{ $at->name }}
                                                            </a>
                                                        </td>
                                                        <td class="p-3">{{ $at->created_user }}</td>
                                                        <td class="p-3">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @else
                                <div class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Attachments {{ $prefix }}</h3>
                                </div>
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Attachment Empty.</p>
                            @endif
                        </div>

                        {{-- Right: New Attachments CS --}}
                        <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between border-b pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Attachments CS</span>
                                    <span class="text-sm font-medium text-gray-500 group-open:hidden">See details →</span>
                                    <span class="hidden text-sm font-medium text-gray-500 group-open:inline">Hide details ↓</span>
                                </summary>
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
                                        @foreach ($attachmentCS as $at)
                                        @php
                                            $year = \Carbon\Carbon::parse($at->created_at)->year;
                                            $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                        @endphp
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                            <td class="p-3"><a href="{{ $fileUrl }}" target="_blank" class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎 {{ $at->name }}</a></td>
                                            <td class="p-3">{{ $at->created_user }}</td>
                                            <td class="p-3">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    </table>
                                </div>
                                </details>

                            {{-- Action buttons keep here or move below both columns as you wish --}}
                            
                        </div>
                    </div>

               
                        </div>
                        </details>
                    </div>
                    </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>


    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Reject</h2>
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
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Revise Task</h2>
            <textarea id="reviseReason" class="mt-2 w-full rounded-lg p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    Revise
                </button>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/plugin/relativeTime.min.js"></script>
    <script>
        dayjs.extend(dayjs_plugin_relativeTime);

        const $spinner = $("#loadingSpinnerContainer");
        $spinner.fadeIn(); // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut(); // sembunyikan saat selesai
    </script>

    <script>
        $(document).ready(function() {
            let sppbid = "{{ $cs->sppbid }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(sppbid);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(sppbid) {
                console.log("Loading comments for Doc ID:", sppbid);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/sppb/${sppbid}/comments`,
                    type: 'GET',
                    success: function(response) {
                        console.log("Comments Loaded:", response);
                        commentList.empty();

                        if (response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>'
                            );
                        } else {
                            response.comments.forEach(comment => {
                                // let timeAgo = moment(comment.created_at)
                                //     .fromNow(); // Format waktu seperti "4 days ago"
                                let timeAgo = dayjs(comment.created_at).fromNow();
                                commentList.append(`
                                        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2        -gray-300 dark:   -gray-700">
                                            <p class="text-sm font-semibold">${comment.username} 
                                                <span class="text-xs text-gray-500">(${timeAgo})</span>
                                            </p>
                                            <p class="text-gray-800 dark:text-gray-200">${comment.message}</p>
                                        </div>
                                `);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching comments:", xhr.responseText);
                        commentList.html('<p class="text-red-500 italic">Failed to load comments.</p>');
                    }
                });
            }

            $(document).on('click', '#postCommentBtn', function(e) {
                e.preventDefault();
                addComment();
            });

            // **Fungsi untuk Menambahkan Komentar**
            function addComment() {
                let input = $('#commentInput').val().trim();

                if (input === "") {
                    alert("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting... 🚀'); // Disable button saat proses

                $.ajax({
                    url: `/sppb/${sppbid}/comments`,
                    type: 'POST',
                    data: {
                        sppbid: sppbid,
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Comment added successfully:', response);

                        if (response.status === "success") {
                            loadComments(sppbid); // **Reload komentar setelah menambahkan**
                            $('#commentInput').val(''); // Kosongkan input setelah sukses
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message :
                            "Unknown Error"));
                    },
                    complete: function() {
                        $('#postCommentBtn').prop('disabled', false).text(
                            'Post 🚀'); // Aktifkan kembali tombol
                    }
                });
            }

            // **Event Listener untuk Tombol "Post"**
            $('#postCommentBtn').click(function() {
                addComment();
            });

            // **Event Listener untuk Enter (Tanpa Shift) di Input**
            $('#commentInput').keypress(function(event) {
                if (event.which === 13 && !event.shiftKey) {
                    event.preventDefault();
                    addComment();
                }
            });
        });
    </script>
    <script>
        $(document).on("click", "#approveBtn", function() {
            let sppbid = "{{ $cs->sppbid }}"; // Ambil Task ID dari modal        
            approveSPPB(sppbid);
        });

        function approveSPPB(sppbid) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/sppb/${sppbid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    sppbid: sppbid
                },
                success: function(response) {
                    if (response.success) {
                        // Update status di UI
                        $("#xstatus").text("Approved")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                            );

                        // Tampilkan alert sukses
                        toastr.success("SPPB approved successfully!");
                        window.location.href = "/sppbs";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this sppb.");
                    } else {
                        toastr.error("Error: Unable to approve sppb.");
                    }
                },
                complete: function() {
                    // Sembunyikan spinner setelah request selesai
                    $spinner.fadeOut();
                }
            });
        }
    </script>


    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                let sppbid = "{{ $cs->sppbid }}";
                checkApproval(sppbid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let sppbid = "{{ $cs->sppbid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/sppb/${sppbid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: sppbid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal sppb
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            window.location.href = "/sppbs";
                        } else {
                            alert("Failed to reject sppb.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject sppb status.");
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
                let sppbid = "{{ $cs->sppbid }}";
                checkApproval(sppbid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let sppbid = "{{ $cs->sppbid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/sppb/${sppbid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: sppbid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal sppb
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            window.location.href = "/sppbs";
                        } else {
                            alert("Failed to revise sppb.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise sppb status.");
                        }
                    },
                });
            });
        });
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function checkApproval(sppbid, action) {
            console.log(sppbid, '-', action);
            $.ajax({
                url: `/sppb/${sppbid}/check-approval/${action}`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {
                        // Jika user bisa melakukan aksi, tampilkan modal atau langsung proses approval
                        if (action === "reject") {
                            $("#rejectReason").val(""); // Reset alasan reject
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                        } else if (action === "revise") {
                            $("#reviseReason").val(""); // Reset alasan revise
                            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                            // } else if (action === "approve") {
                            //     approveSPPB(sppbid); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this sppb.");
                    }
                },
                error: function() {
                    toastr.error("Error checking approval status.");
                }
            });
        }
    </script>




</x-app-layout>
