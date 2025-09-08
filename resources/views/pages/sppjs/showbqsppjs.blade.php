<x-app-layout>
    <style>
        /* This container needs a defined height and overflow-y for the sticky position to work. */
        .table-container {
            height: 400px;
            /* You can adjust this height as needed */
            overflow-y: auto;
            border-bottom-left-radius: 1rem;
            border-bottom-right-radius: 1rem;
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
            inset: 0;                       /* = top/right/bottom/left: 0 */
            display: none;                  /* ditampilkan via JS .fadeIn() */
            display: grid;
            place-items: center;            /* center horizontal + vertical */
            background: rgba(17,24,39,.55); /* #111827 dengan transparansi */
            backdrop-filter: blur(2px);     /* efek blur background */
            z-index: 2000;
        }

        /* Kartu spinner */
        .loading-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 18px 22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31,41,55,.9), rgba(17,24,39,.9));
            border: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 10px 30px rgba(0,0,0,.35), inset 0 0 0 1px rgba(255,255,255,.04);
        }

        /* Spinner dual ring */
        .loading-spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: #6366f1;      /* indigo-500 */
            animation: spin 1s linear infinite;
            position: relative;
        }
        .loading-spinner::after {
            content: "";
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-left-color: #a5b4fc;     /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        /* Teks */
        .loading-text {
            color: #e5e7eb;                 /* gray-200 */
            font-weight: 600;
            letter-spacing: .02em;
        }

        /* Dots animasi */
        .loading-ellipsis span {
            display: inline-block;
            animation: blink 1.4s infinite both;
        }
        .loading-ellipsis span:nth-child(2) { animation-delay: .2s; }
        .loading-ellipsis span:nth-child(3) { animation-delay: .4s; }

        @keyframes spin        { to { transform: rotate(360deg); } }
        @keyframes spinReverse { to { transform: rotate(-360deg);} }
        @keyframes blink {
            0%   { opacity:.3; transform: translateY(0); }
            20%  { opacity:1;  transform: translateY(-2px); }
            100% { opacity:.3; transform: translateY(0); }
        }
        </style>

    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <div>             
            </div>

            <div class="flex gap-3">
                <button id="editBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-green-100 px-3 py-2 text-sm font-medium text-green-700 transition-colors hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-700/30 dark:text-green-300 dark:hover:bg-green-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                    </svg>
                    Edit
                </button>
                
            </div>
        </div>
        <div class="flex w-full flex-row gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:col-span-1 xl:flex-col">
            <div class="flex w-full flex-row gap-6">
                <div class="flex max-h-96 min-h-[12rem] flex-col gap-6 rounded-2xl sm:w-1/2 md:w-full">
                    <div class="flex h-full flex-col rounded-2xl bg-white dark:bg-gray-800">
                        <header
                            class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            {{-- Rounded-t-xl, stronger border, and darker background for header --}}
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
                                    $statusClasses =
                                        'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300';
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
                                        class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
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
                </div>

                <div class="flex flex-col gap-4 sm:w-1/2 md:w-full">
                    <div x-data="{ activeTab: 'attachment' }" class="rounded-xl bg-white duration-300 dark:bg-gray-800">
                        <header
                            class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                                <button @click="activeTab = 'attachment'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'attachment',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'attachment'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                    Photo Before
                                </button>                                
                            </nav>
                        </header>

                        <div class="max-h-96 min-h-[12rem] flex-grow overflow-y-auto rounded-b-xl bg-white px-6 py-2 dark:bg-gray-800">      
                            <div x-show="activeTab === 'attachment'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    @forelse ($attachment as $at)
                                        @php
                                            $year    = $at->created_at->year;
                                            $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                            $ext     = strtolower(pathinfo($at->attachfile, PATHINFO_EXTENSION));
                                            $isImg   = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                        @endphp

                                        <div class="flex flex-col">
                                            <a href="{{ $fileUrl }}" target="_blank"
                                            class="group relative block aspect-[4/3] overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                                @if ($isImg)
                                                    <img src="{{ $fileUrl }}" alt="{{ $at->name }}"
                                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                        loading="lazy" referrerpolicy="no-referrer">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center bg-gray-50 dark:bg-gray-700">
                                                        <span class="text-4xl">📄</span>
                                                    </div>
                                                @endif
                                                <div class="pointer-events-none absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>
                                            </a>

                                            <div class="mt-2 flex items-center justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-medium text-gray-900 dark:text-gray-100"
                                                        title="{{ $at->name }}">{{ $at->name }}</div>
                                                    <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $at->created_user }} · {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                    </div>
                                                </div>                                               
                                            </div>
                                        </div>
                                    @empty
                                        <p class="col-span-full text-center italic text-gray-500 dark:text-gray-400">
                                            No attachments found.
                                        </p>
                                    @endforelse
                                </div>

                            </div>

                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex max-h-[50rem] min-h-[12rem] flex-col rounded-2xl dark:bg-gray-800">
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-xl font-semibold">📝 Budget Detail</h2>
                </header>
                <div class="table-container flex-grow">
                    <table class="sticky-header w-full text-sm dark:text-gray-200">
                        <thead>
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
                                <tr class="border-t bg-gray-50 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                     <td class="px-4 py-2">{{ $item->bq_line_no }}</td>
                                    <td class="px-4 py-2">{{ $item->bq_descr }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ is_null($item->qty) ? '' : number_format((float)$item->qty, 2) }}
                                    </td>
                                    <td class="px-4 py-2">{{ $item->uom }}</td>
                                    <td class="px-4 py-2 text-right">
                                        {{ is_null($item->est_material_price) ? '' : number_format((float)$item->est_material_price, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ is_null($item->total_est_material_price) ? '' : number_format((float)$item->total_est_material_price, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ is_null($item->est_jasa_price) ? '' : number_format((float)$item->est_jasa_price, 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        {{ is_null($item->total_est_jasa_price) ? '' : number_format((float)$item->total_est_jasa_price, 2) }}
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
        $spinner.fadeIn();   // tampilkan saat mulai proses
        // ...
        $spinner.fadeOut();  // sembunyikan saat selesai
    </script>
    <script>
        $(function () {
            $('#editBtn').on('click', function () {
            // optional: tampilkan overlay sebentar
            $('#loadingSpinnerContainer').fadeIn(120);
            window.location.href = "{{ route('bqsppj.edit', $bq->id) }}";
            });
        });
    </script>

   


</x-app-layout>
