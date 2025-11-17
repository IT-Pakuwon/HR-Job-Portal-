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
        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="flex flex-col gap-6 sm:w-1/2 md:w-full xl:flex-row">
                {{-- Left Card --}}
                <div class="rounded-xl bg-white duration-300 sm:w-1/2 md:w-full dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $bq->bqid }}
                        </h1>
                        <h1 class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $bq->sppjtid }}
                        </h1>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto p-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm sm:grid-cols-2">

                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                                $fields = [
                                    [
                                        'icon' => 'building-office',
                                        'label' => 'Company',
                                        'value' => $bq->cpny_id,
                                    ],
                                    [
                                        'icon' => 'calendar',
                                        'label' => 'Date',
                                        'value' => date('j F Y', strtotime($bq->created_at)),
                                    ],
                                    [
                                        'icon' => 'user-circle',
                                        'label' => 'Created User',
                                        'value' => ucwords(strtolower(optional($bq->creator)->name)),
                                    ],
                                ];
                            @endphp

                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>

                                    <span class="{{ $value }}">{{ $f['value'] }}</span>
                                </div>
                            @endforeach

                        </div>
                    </div>

                </div>

                {{-- Right Card (Photo Before) --}}
                <div class="flex flex-col gap-4 bg-white sm:w-1/2 md:w-full dark:bg-gray-800">
                    <header
                        class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">📸 Photo Before</h2>
                    </header>

                    <div class="flex-1 overflow-y-auto px-4 py-3">
                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                            @forelse ($attachment as $at)
                                @php
                                    $year = $at->created_at->year;
                                    $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                    $ext = strtolower(pathinfo($at->attachfile, PATHINFO_EXTENSION));
                                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                @endphp

                                <div
                                    class="group relative flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-gray-500 dark:border-gray-700 dark:bg-gray-800">

                                    {{-- Thumbnail kecil --}}
                                    <a href="{{ $fileUrl }}" target="_blank"
                                        class="relative block aspect-square overflow-hidden">
                                        @if ($isImg)
                                            <img src="{{ $fileUrl }}" alt="{{ $at->name }}"
                                                class="h-full w-full object-cover transition group-hover:scale-105"
                                                loading="lazy" referrerpolicy="no-referrer">
                                        @else
                                            <div
                                                class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                                                <span class="text-2xl">📄</span>
                                            </div>
                                        @endif

                                        {{-- Hover overlay --}}
                                        <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20">
                                        </div>

                                        {{-- Action icons kecil --}}
                                        <div
                                            class="absolute right-1 top-1 flex gap-1 opacity-0 transition group-hover:opacity-100">
                                            <a href="{{ $fileUrl }}" target="_blank"
                                                class="rounded bg-white p-1 text-xs shadow hover:bg-gray-100 dark:bg-gray-700">🔍</a>
                                            <a href="{{ $fileUrl }}" download
                                                class="rounded bg-white p-1 text-xs shadow hover:bg-gray-100 dark:bg-gray-700">⬇️</a>
                                        </div>
                                    </a>

                                    {{-- Info ringkas --}}
                                    <div class="px-2 py-1">
                                        <div class="truncate text-xs font-medium text-gray-900 dark:text-gray-100"
                                            title="{{ $at->name }}">
                                            {{ $at->name }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                                    No attachments found.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex max-h-[50rem] min-h-[12rem] w-full flex-col rounded-2xl bg-white dark:bg-gray-800">
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
