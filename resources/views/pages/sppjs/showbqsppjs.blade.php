<x-app-layout>
    <style>
        /* This container needs a defined height and overflow-y for the sticky position to work. */
        .table-container {
            height: 400px;
            /* You can adjust this height as needed */
            overflow-y: auto;
            -bottom-left-radius: 1rem;
            -bottom-right-radius: 1rem;
        }

        .sticky-header thead {
            position: sticky;
            top: 0;
            /* Optional: Ensure the header is above the body content when scrolling */
            z-index: 10;
        }
    </style>
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
                <button id="editBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Edit
                </button>

            </div>
        </div>
        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:col-span-1 xl:col-span-1 xl:flex-col">
            <div class="flex w-full flex-col gap-6 lg:row-span-1 xl:row-span-1 xl:flex-row">
                <div class="rounded-xl bg-white duration-300 sm:w-1/2 md:w-full dark:bg-gray-800">
                    <header
                        class="dark: flex items-center justify-between rounded-t-xl border-b border-gray-200 border-gray-700 bg-gray-50 px-6 py-4 dark:bg-gray-700">
                        {{-- Rounded-t-xl, stronger    , and darker background for header --}}
                        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
                            {{-- Larger, bolder title --}}
                            <span class="text-indigo-500">🆔</span> {{-- Iconic color for the ID icon --}}
                            {{ $bq->bqid }}
                        </h1>
                        @php
                            // Define the status text
                            $statusText = match ($bq->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            // Define the status badge classes based on the status
                            $statusClasses = '';
                            if ($bq->status === 'D') {
                                $statusClasses = 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300';
                            } elseif ($bq->status === 'P') {
                                $statusClasses =
                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300';
                            } elseif ($bq->status === 'C') {
                                $statusClasses = 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300';
                            } elseif (in_array($bq->status, ['X', 'R'])) {
                                $statusClasses = 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300';
                            } else {
                                $statusClasses = 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300';
                            }
                        @endphp
                    </header>
                    <!-- Main Content -->
                    <div class="space-y-4 p-4">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            @php
                                $jobDetails = [
                                    [
                                        'label' => 'Company',
                                        'value' => $bq->cpny_id,
                                    ],
                                    [
                                        'label' => 'SPPJ ID',
                                        'value' => $bq->sppjtid,
                                    ],
                                    [
                                        'label' => 'Date',
                                        'value' => date('j F Y', strtotime($bq->created_at)),
                                    ],
                                    [
                                        'label' => 'Creted By',
                                        'value' => ucwords(strtolower(optional($bq->creator)->name)),
                                    ],
                                ];
                            @endphp
                            @foreach ($jobDetails as $detail)
                                <div
                                    class="flex items-center gap-4 rounded-lg border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                    <div>
                                        <p class="text-base font-medium text-gray-900 dark:text-gray-100">
                                            <span
                                                class="mr-1 text-xs text-gray-500 dark:text-gray-400">{{ $detail['label'] }}:</span>
                                            {{ $detail['value'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="flex max-h-96 min-h-[12rem] flex-col gap-4 sm:w-1/2 md:w-full">
                    <div x-data="{ activeTab: 'attachment' }" class="flex flex-1 flex-col">
                        <header
                            class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="flex flex-grow">
                                <button @click="activeTab = 'attachment'"
                                    :class="activeTab === 'attachment'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Attachment
                                </button>
                                <button @click="activeTab = 'approval'"
                                    :class="activeTab === 'approval'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Approval Details
                                </button>
                                <button @click="activeTab = 'comments'"
                                    :class="activeTab === 'comments'
                                        ?
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium transition-colors duration-200">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        {{-- Tabs Content --}}
                        <div class="flex flex-1 flex-col rounded-b-xl bg-white dark:bg-gray-800">
                            {{-- Approval tab --}}
                            <div x-show="activeTab === 'approval'" class="flex-1 transition-all">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr
                                            class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            <th class="p-3 text-left font-semibold">Level</th>
                                            <th class="p-3 text-left font-semibold">Name</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            <th class="p-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approval as $ap)
                                            <tr
                                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="p-3">{{ $ap->aprvid }}</td>
                                                <td class="p-3">{{ $ap->name }}</td>
                                                <td class="p-3">
                                                    {{ \Carbon\Carbon::parse($ap->aprvdatebefore)->format('d M Y') }}
                                                </td>
                                                <td class="p-3">
                                                    @php
                                                        $statusText = '';
                                                        $statusClass = '';
                                                        switch ($ap->status) {
                                                            case 'P':
                                                                $statusText = 'Waiting Approval';
                                                                $statusClass = 'bg-yellow-500 text-white';
                                                                break;
                                                            case 'A':
                                                                $statusText = 'Approved';
                                                                $statusClass = 'bg-green-500 text-white';
                                                                break;
                                                            case 'R':
                                                                $statusText = 'Rejected';
                                                                $statusClass = 'bg-red-500 text-white';
                                                                break;
                                                            case 'D':
                                                                $statusText = 'Revise';
                                                                $statusClass = 'bg-blue-500 text-white';
                                                                break;
                                                            default:
                                                                $statusText = 'Unknown';
                                                                $statusClass = 'bg-gray-500 text-white';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1 text-xs font-semibold">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Attachment tab --}}
                            <div x-show="activeTab === 'attachment'" class="flex-1 transition-all">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($attachment as $at)
                                            @php
                                                $year = $at->created_at->year;
                                                $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                            @endphp
                                            <tr
                                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="p-3">
                                                    <a href="{{ $fileUrl }}" target="_blank"
                                                        class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                        📎 {{ $at->name }}
                                                    </a>
                                                </td>
                                                <td class="p-3">{{ $at->created_user }}</td>
                                                <td class="p-3">
                                                    {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3"
                                                    class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Comments tab --}}
                            <div x-show="activeTab === 'comments'" class="flex-1 transition-all">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex h-full flex-col">
                                    <div id="commentList"
                                        class="custom-scrollbar flex-1 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" x-model="newComment" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg bg-gray-100 p-3 text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                        <button id="postCommentBtn" type="button"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div class="flex max-h-[50rem] min-h-[12rem] flex-col rounded-2xl bg-white dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-xl font-semibold">📝 Budget Detail</h2>
                </header>

                <table class="sticky-header w-full text-sm text-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-700 dark:text-gray-100">
                        <tr>
                            <th class="px-4 py-2">Line No</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2">UoM</th>
                            <th class="px-4 py-2 text-right">Est Mat Price</th>
                            <th class="px-4 py-2 text-right">Total Est Mat</th>
                            <th class="px-4 py-2 text-right">Est Jasa Price</th>
                            <th class="px-4 py-2 text-right">Total Est Jasa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bqdetail as $item)
                            <tr
                                class="border-t border-gray-200 bg-gray-50 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
                                <td class="px-4 py-2">{{ $item->bq_line_no }}</td>
                                <td class="px-4 py-2">{{ $item->bq_descr }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                                </td>
                                <td class="px-4 py-2">{{ $item->uom }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ is_null($item->total_est_material_price) ? '' : number_format((float) $item->total_est_material_price, 2) }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ is_null($item->total_est_jasa_price) ? '' : number_format((float) $item->total_est_jasa_price, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
        $(function() {
            $('#editBtn').on('click', function() {
                // optional: tampilkan overlay sebentar
                $('#loadingSpinnerContainer').fadeIn(120);
                window.location.href = "{{ route('bqsppj.edit', $bq->id) }}";
            });
        });
    </script>




</x-app-layout>
