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
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                {{-- ====== EDIT FORM ====== --}}
                <form id="imbudgetForm" class="flex flex-col gap-4" enctype="multipart/form-data"
                    action="{{ route('imbudgets.update', $hash) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="w-full rounded-xl bg-white p-6 dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">
                                <span
                                    class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                    ID
                                </span>
                                {{ $imbudget->imbudgetid }} -
                                <a href="{{ url('/showcs/' . $eidcs) }}" target="_blank"
                                    class="w-full rounded-lg border border-gray-300 bg-indigo-50 p-2.5 font-semibold text-indigo-700 hover:underline dark:border-gray-600 dark:bg-gray-700 dark:text-indigo-400">
                                    {{ $imbudget->csid }}
                                </a>
                            </h2>
                        </div>

                        {{-- ===== Header fields ===== --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

                            {{-- Company --}}
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Company
                                </label>

                                <select disabled
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300">
                                    <option>{{ $imbudget->cpny_id }}</option>
                                </select>

                                {{-- hidden agar tetap terkirim --}}
                                <input type="hidden" name="cpnyid" value="{{ $imbudget->cpny_id }}">
                            </div>

                            {{-- Department --}}
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Department
                                </label>

                                <select disabled
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300">
                                    <option>{{ $imbudget->department_id }}</option>
                                </select>

                                <input type="hidden" name="departementid" value="{{ $imbudget->department_id }}">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Perpost
                                </label>

                                <input type="text" disabled
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 shadow-sm dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                    value="{{ $imbudget->budget_perpost }}">

                                <input type="hidden" name="perpost" value="{{ $imbudget->budget_perpost }}">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea rows="3"
                                    name="imbudgetnote"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:outline-none focus:ring-0 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                >{{ $imbudget->imbudgetnote }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-2xl p-4">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>📝 IMBudget Detail</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                        details &rarr;</span>
                                    <span
                                        class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                        details &darr;</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-10 border p-3 text-center">No</th>
                                                    <th class="w-20 border p-3 text-center">COA</th>
                                                    <th class="w-24 border p-3 text-center">Activity</th>
                                                    <th class="w-[16%] border p-3">Activity Descr</th>
                                                    <th class="w-24 border p-3 text-right">Amount Expense</th>
                                                    <th class="w-24 border p-3 text-right">Budget Remain</th>
                                                    <th class="w-24 border p-3 text-right">Budget Needed</th>
                                                    <th class="w-24 border p-3 text-right">Budget Requested</th>
                                                    <th class="w-[14%] border p-3">Note</th>
                                                </tr>
                                            </thead>

                                            <tbody id="imbudgetTable">
                                                @php $rowNo = 0; @endphp
                                                @forelse ($imbudgetdetail as $d)
                                                    @php
                                                        $rowNo++;
                                                        $fmt = fn($v) => is_null($v)
                                                            ? ''
                                                            : number_format((float) $v, 2, ',', '.');
                                                    @endphp
                                                    <tr class="imbudget-row" data-detail-id="{{ $d->id }}">
                                                        <td class="border p-3 text-center">{{ $rowNo }}</td>

                                                        {{-- id detail untuk update --}}
                                                        <input type="hidden" name="detail_id[]"
                                                            value="{{ $d->id }}" />

                                                        <td class="border p-3 text-center">
                                                            <input type="text"
                                                                class="w-full border-none bg-transparent text-center"
                                                                value="{{ $d->budget_account_id }}" readonly>
                                                            <input type="hidden" name="budget_account_id[]"
                                                                value="{{ $d->budget_account_id }}">
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <input type="text"
                                                                class="w-full border-none bg-transparent text-center"
                                                                value="{{ $d->budget_activity_id }}" readonly>
                                                            <input type="hidden" name="budget_activity_id[]"
                                                                value="{{ $d->budget_activity_id }}">
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text"
                                                                class="w-full border-none bg-transparent"
                                                                value="{{ $d->budget_activity_descr }}" readonly>
                                                            <input type="hidden" name="budget_activity_descr[]"
                                                                value="{{ $d->budget_activity_descr }}">
                                                        </td>

                                                        {{-- angka hasil agregasi/perhitungan (readonly) --}}
                                                        <td class="border p-3 text-right">
                                                            <input type="text"
                                                                class="amountExpenseField w-full border-none bg-transparent text-right"
                                                                value="{{ $fmt($d->amount_expense ?? 0) }}" readonly>
                                                            <input type="hidden" name="amount_expense[]"
                                                                value="{{ (float) ($d->amount_expense ?? 0) }}">
                                                        </td>
                                                        <td class="border p-3 text-right">
                                                            <input type="text"
                                                                class="budgetRemainField w-full border-none bg-transparent text-right"
                                                                value="{{ $fmt($d->budget_remain ?? 0) }}" readonly>
                                                            <input type="hidden" name="budget_remain[]"
                                                                value="{{ (float) ($d->budget_remain ?? 0) }}">
                                                        </td>
                                                        <td class="border p-3 text-right">
                                                            <input type="text"
                                                                class="budgetNeededField w-full border-none bg-transparent text-right"
                                                                value="{{ $fmt($d->budget_needed ?? 0) }}" readonly>
                                                            <input type="hidden" name="budget_needed[]"
                                                                value="{{ (float) ($d->budget_needed ?? 0) }}">
                                                        </td>

                                                        {{-- editable --}}
                                                        <td class="border p-3 text-right">
                                                            @php
                                                                $req = $d->budget_requested ?? 0;
                                                                // tampil pakai koma
                                                                $reqDisp = number_format((float) $req, 2, ',', '.');
                                                            @endphp
                                                            <input type="text" name="budget_requested[]"
                                                                class="reqField w-full rounded-md border px-2 py-1 text-right"
                                                                value="{{ $reqDisp }}" inputmode="decimal"
                                                                autocomplete="off" placeholder="0,00">
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" name="note[]"
                                                                class="w-full rounded-md border px-2 py-1"
                                                                value="{{ $d->note }}">
                                                        </td>

                                                    </tr>
                                                @empty
                                                    <tr class="imbudget-row">
                                                        <td colspan="14"
                                                            class="border p-4 text-center text-sm text-gray-500">
                                                            No budget detail. (Generated from CS when approving.)
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>


                    {{-- ===== Attachments (optional ditampilkan sesuai kebutuhan) ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                📎</div>
                                            <div class="min-w-0">
                                                @if ($att->url)
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                                        {{ $att->display_name }}
                                                    </a>
                                                @else
                                                    <span
                                                        class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                        {{ $att->display_name }} (no link)
                                                    </span>
                                                @endif
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ strtoupper($att->extention ?? '-') }}
                                                    @if (!empty($att->size))
                                                        • {{ number_format($att->size / 1024, 0) }} KB
                                                    @endif
                                                    @if (!empty($att->created_at))
                                                        •
                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                            aria-label="Remove attachment">
                                            🗑️
                                        </button>
                                    </div>
                                @empty
                                    <div
                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No existing attachments.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Upload baru --}}
                            <div id="attachmentsContainer" class="mt-6">
                                <div class="attachment-row flex items-center gap-2">
                                    <input type="file" name="attachments[]"
                                        class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                    <button type="button"
                                        class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Attachment
                            </button>
                        </details>

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

                            <!-- Cancel Button-->
                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <button id="cancelBtn"
                                    class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                    <span id="cancelText">Cancel</span>
                                    <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                        </path>
                                    </svg>
                                </button>
                                <button type="submit" id="submitBtn"
                                    class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
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
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
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


        });
    </script>
    <script>
        // --- helper angka: "1.234,56" -> 1234.56
        function toNumber(str) {
            if (!str) return 0;
            let s = String(str).trim();
            // hapus spasi
            s = s.replace(/\s+/g, '');
            // kalau ada baik titik maupun koma, anggap koma adalah desimal (format id)
            const hasComma = s.indexOf(',') !== -1;
            const hasDot = s.indexOf('.') !== -1;
            if (hasComma && hasDot) {
                // hilangkan semua titik (thousand), koma -> titik
                s = s.replace(/\./g, '').replace(/,/g, '.');
            } else if (hasComma) {
                s = s.replace(/,/g, '.');
            }
            return isFinite(s) ? parseFloat(s) : 0;
        }

        $('#imbudgetForm').on('submit', function(e) {
            e.preventDefault();

            // RESET error
            $('#imbudgetTable .is-invalid').removeClass('is-invalid');
            $('#imbudgetTable .error-feedback').remove();

            // Minimal 1 baris "aktif" (punya COA) dan budget_requested >= 0
            let anyActive = false;
            let anyInvalid = false;

            $('#imbudgetTable tr.imbudget-row').each(function() {
                const $tr = $(this);
                const $coa = $tr.find('input[name="budget_account_id[]"]');
                const $reqVis = $tr.find('input[name="budget_requested[]"]');

                const hasCoa = ($coa.val() || '').trim() !== '';

                // normalisasi input req (ganti tampilan -> hidden numeric)
                const reqNum = toNumber($reqVis.val());
                $reqVis.val($reqVis.val().replace(/\./g, '').replace(/,/g,
                    ',')); // biar tampilan tetap id (opsional)

                if (hasCoa) anyActive = true;

                if (hasCoa && reqNum < 0) {
                    $reqVis.addClass('is-invalid').after(
                        '<small class="error-feedback">Budget Requested tidak boleh negatif.</small>');
                    anyInvalid = true;
                }
            });

            if (!anyActive) {
                toastr.error('Minimal ada 1 baris detail dengan COA terisi.');
                return;
            }
            if (anyInvalid) {
                toastr.error('Mohon periksa Budget Requested yang tidak valid.');
                const $first = $('#imbudgetTable .is-invalid').first();
                if ($first.length) $('html,body').animate({
                    scrollTop: $first.offset().top - 120
                }, 300);
                return;
            }

            // lock UI + submit AJAX seperti sebelumnya...
            $('#submitBtn, #cancelBtn').prop('disabled', true);
            $('#btnText').text('Processing...');
            showOverlay('Submitting');

            const form = document.getElementById('imbudgetForm');
            const formData = new FormData(form);
            formData.set('_method', 'PUT');

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    toastr.success(res.message || "IMBudget updated successfully!");
                    window.location.href = "/imbudgets";
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let msg = 'Mohon periksa input:<br>';
                        Object.keys(errors).forEach(k => {
                            msg += `- ${errors[k].join(', ')}<br>`;
                        });
                        toastr.error(msg);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error! Please check the input.');
                    }
                },
                complete: function() {
                    $('#submitBtn, #cancelBtn').prop('disabled', false);
                    $('#btnText').text('Submit Approval');
                    hideOverlay();
                }
            });
        });
    </script>

    <script>
        /** Format ke gaya Indonesia: 1.234,56 (2 desimal) */
        function formatID(num) {
            if (num === null || num === undefined || isNaN(num)) return '';
            const fixed = Number(num).toFixed(2); // "1234.56"
            const [intStr, decStr] = fixed.split('.');
            const intFmt = intStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return intFmt + ',' + decStr;
        }

        /** Validasi + sanitasi input angka/desimal (mendukung "." dan ",") */
        function sanitizeNumericInput(raw) {
            if (!raw) return '';
            let v = String(raw);

            // buang semua karakter selain digit, titik, koma
            v = v.replace(/[^0-9.,]/g, '');

            // rapikan separator beruntun ",,.." atau "..,,"
            v = v.replace(/,{2,}/g, ',').replace(/\.{2,}/g, '.');

            // kalau ada koma & titik sekaligus, biarkan dulu; blur akan normalkan.
            return v;
        }

        $(document).ready(function() {
            const $req = $('#imbudgetTable').find('input[name="budget_requested[]"]');

            // Tambah atribut bantu (opsional, untuk UX)
            $req.attr({
                'autocomplete': 'off',
                'inputmode': 'decimal',
                'placeholder': '0,00',
                'aria-label': 'Budget Requested (angka & desimal)'
            });

            // Saat mengetik: tolak karakter non angka/desimal
            $(document).on('input', 'input[name="budget_requested[]"]', function() {
                const cur = $(this).val();
                const clean = sanitizeNumericInput(cur);
                if (cur !== clean) $(this).val(clean);
            });

            // Blok beberapa key yang mengganggu (mis. minus, huruf e)
            $(document).on('keydown', 'input[name="budget_requested[]"]', function(e) {
                const allowedControl = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End', 'Tab'
                ];
                if (allowedControl.includes(e.key) || (e.ctrlKey || e.metaKey)) return;

                // izinkan digit, koma, titik
                const isDigit = e.key >= '0' && e.key <= '9';
                const isComma = e.key === ',';
                const isDot = e.key === '.';

                if (!(isDigit || isComma || isDot)) {
                    e.preventDefault();
                }
            });

            // Saat paste: sanitasi cepat setelah paste
            $(document).on('paste', 'input[name="budget_requested[]"]', function() {
                const el = this;
                setTimeout(() => {
                    el.value = sanitizeNumericInput(el.value);
                }, 0);
            });

            // Saat blur: normalisasi → ke angka → format Indonesia 2 desimal
            $(document).on('blur', 'input[name="budget_requested[]"]', function() {
                const v = $(this).val().trim();
                if (v === '') return;

                const num = toNumber(v); // fungsi milikmu (sudah ada di view)
                if (!isFinite(num) || num < 0) {
                    // tandai invalid dan kasih pesan
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.error-feedback').length) {
                        $(this).after('<small class="error-feedback">Hanya angka & desimal (≥ 0).</small>');
                    }
                    return;
                }

                // valid → hapus error & format ID
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').remove();
                $(this).val(formatID(num));
            });
        });
    </script>

    <script>
        $(document).on('click', '.removeAttachment2', function() {
            const $btn = $(this);
            const $row = $btn.closest('.attachment-row');
            const attachmentId = $row.data('id');

            if (!attachmentId) {
                toastr.error('Attachment ID tidak ditemukan.');
                return;
            }

            if (!confirm('Are you sure you want to remove this attachment?')) return;

            // lock UI kecil pada tombol
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Removing...
            `);

            $.ajax({
                    url: "/remove-attachment/" + attachmentId,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        // animasi keluar biar halus
                        $row.slideUp(180, function() {
                            $(this).remove();
                        });
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(res?.message || 'Failed to remove attachment.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    toastr.error('Error! Unable to remove attachment.');
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });

        // ===== Cancel Button =====
        $('#cancelBtn').click(function() {
            const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");
            if (confirmed) {
                $('#cancelBtn').prop('disabled', true);
                $('#cancelText').text('Cancelling...');
                $('#cancelSpinner').removeClass('hidden');
                window.location.href = "{{ route('imbudgets') }}";
            }
        });
    </script>



    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
