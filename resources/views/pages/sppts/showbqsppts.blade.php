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
                {{-- <button id="editBtn"
                    class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Edit
                </button> --}}
                @if (!empty($canEdit) && $canEdit)
                    <button id="editBtn"
                        class="inline-flex items-center gap-1 rounded-md bg-gray-500 px-3 py-2 text-sm font-medium text-gray-100 transition-colors hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-100 dark:bg-gray-700/30 dark:text-gray-300 dark:hover:bg-gray-600/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Edit
                    </button>
                @endif

            </div>
        </div>
        <div class="flex w-full flex-col gap-6 xl:flex-col">
            <div class="flex w-full flex-col gap-6 md:h-[35vh] xl:flex-row">
                {{-- Left Card --}}
                <div class="flex flex-1 flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
                            <span class="text-indigo-500">🆔</span>
                            {{ $bq->bqid }}
                        </h1>
                        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-gray-100">
                            <span class="text-indigo-500">🆔</span>
                            {{ $bq->sppjtid }}
                        </h1>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto p-4">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            @php
                                $jobDetails = [
                                    ['label' => 'Company', 'value' => $bq->cpny_id],
                                    ['label' => 'Date', 'value' => date('j F Y', strtotime($bq->created_at))],
                                    [
                                        'label' => 'Created By',
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

                {{-- Right Card (Photo Before) --}}
                <div class="flex flex-1 flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">📸 Photo Before</h2>
                    </header>

                    <div class="flex-1 overflow-y-auto px-4 py-3">
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                            @forelse ($attachment as $at)
                                @php
                                    $year = $at->created_at->year;
                                    $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                    $ext = strtolower(pathinfo($at->attachfile, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                @endphp

                                <div
                                    class="flex flex-col overflow-hidden rounded-lg bg-gray-50 transition-shadow duration-300 hover:shadow-md dark:bg-gray-700">
                                    <a href="{{ $fileUrl }}" target="_blank"
                                        class="group relative block aspect-[4/2] overflow-hidden">
                                        @if ($isImg)
                                            <img src="{{ $fileUrl }}" alt="{{ $at->name }}"
                                                class="h-full w-full rounded-t-lg object-cover transition-transform duration-300 group-hover:scale-105"
                                                loading="lazy" referrerpolicy="no-referrer">
                                        @else
                                            <div
                                                class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-600">
                                                <span class="text-4xl">📄</span>
                                            </div>
                                        @endif
                                        <div
                                            class="absolute inset-0 rounded-t-lg bg-black/0 transition group-hover:bg-black/20">
                                        </div>
                                    </a>

                                    <div class="px-3 py-2">
                                        <div class="truncate text-sm font-medium text-gray-900 dark:text-gray-100"
                                            title="{{ $at->name }}">
                                            {{ $at->name }}
                                        </div>
                                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ $at->created_user }} ·
                                            {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                                    No photos found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex max-h-[50rem] min-h-[12rem] flex-col rounded-2xl shadow dark:bg-gray-800">
                {{-- Header --}}
                <header
                    class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-white px-6 py-4 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <h2 class="text-xl font-semibold">📝 BQ Detail</h2>
                </header>

                {{-- Scrollable Table --}}
                <div class="max-h-[calc(50rem-4rem)] overflow-x-auto overflow-y-auto"> {{-- adjust height --}}
                    <table class="w-full border-collapse text-sm text-gray-700 dark:text-gray-200">
                        <thead class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-700 dark:text-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Line No</th>
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-right">Qty</th>
                                <th class="px-4 py-2 text-left">UoM</th>
                                <th class="px-4 py-2 text-right">Est Mat Price</th>
                                <th class="px-4 py-2 text-right">Total Est Mat</th>
                                <th class="px-4 py-2 text-right">Est Jasa Price</th>
                                <th class="px-4 py-2 text-right">Total Est Jasa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bqdetail as $item)
                                <tr
                                    class="border-t border-gray-200 bg-white hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
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
                window.location.href = "{{ route('bqsppt.edit', $bq->id) }}";
            });
        });
    </script>




</x-app-layout>
