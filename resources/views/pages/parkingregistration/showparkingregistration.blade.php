<x-app-layout>

    <div class="max-w-9xl mx-auto p-2">
        <x-approval-actions
            :status="$parkingRegistration->status"
            :is-approver="$isApprover"
            :edit-url="url('/editparkingregistration/' . $hash)"
        />

        <div class="flex w-full flex-col gap-6 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-col">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">                
                {{-- Left card Parking Registration Info --}}
                <div class="flex flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <header
                        class="sticky top-0 z-10 flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-[8px] dark:border-gray-700 dark:bg-gray-700">
                        <h1 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                            <span
                                class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-sm font-semibold text-purple-700">
                                ID
                            </span>
                            {{ $parkingRegistration->docid }}
                        </h1>

                        @php
                            $statusText = match ($parkingRegistration->status) {
                                'D' => 'Revise',
                                'P' => 'On Progress',
                                'C' => 'Completed',
                                'X' => 'Cancelled',
                                'R' => 'Rejected',
                                default => 'Unknown',
                            };

                            $statusClasses = match ($parkingRegistration->status) {
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
                        </div>
                    </header>

                    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-[8px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">

                            @php
                                $row = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                                $label = 'flex items-center gap-2 text-gray-500 sm:min-w-40';
                                $value = 'break-words font-medium text-gray-900 dark:text-gray-300 sm:flex-1';

                                $fields = [
                                    [
                                        'icon' => 'building-office',
                                        'label' => 'Company',
                                        'value' => $parkingRegistration->cpny_id,
                                    ],
                                    [
                                        'icon' => 'squares-2x2',
                                        'label' => 'Department',
                                        'value' => $parkingRegistration->department_id,
                                    ],
                                    [
                                        'icon' => 'calendar',
                                        'label' => 'Registration Date',
                                        'value' => $parkingRegistration->parking_regist_date
                                            ? \Carbon\Carbon::parse($parkingRegistration->parking_regist_date)->format('j F Y')
                                            : '-',
                                    ],
                                    [
                                        'icon' => 'user-circle',
                                        'label' => 'Created User',
                                        'value' => ucwords(strtolower(optional($parkingRegistration->creator)->name ?? $parkingRegistration->created_by)),
                                    ],
                                    [
                                        'icon' => 'map-pin',
                                        'label' => 'Site Parking',
                                        'value' => $siteParkingName ?: $parkingRegistration->site_id_parking,
                                    ],
                                    [
                                        'icon' => 'calendar-days',
                                        'label' => 'Periode',
                                        'value' => $parkingRegistration->perpost,
                                    ],
                                    [
                                        'icon' => 'truck',
                                        'label' => 'Parking Type',
                                        'value' => $parkingTypeName ?: $parkingRegistration->parking_type,
                                    ],
                                    [
                                        'icon' => 'users',
                                        'label' => 'Worker Type',
                                        'value' => $workerTypeName ?: $parkingRegistration->worker_type,
                                    ],
                                ];
                            @endphp

                            @foreach ($fields as $f)
                                <div class="{{ $row }}">
                                    <div class="{{ $label }}">
                                        <x-dynamic-component :component="'heroicon-o-' . $f['icon']" class="h-5 w-5 text-gray-400" />
                                        <span>{{ $f['label'] }}</span>
                                    </div>
                                    <span class="{{ $value }}">{{ $f['value'] ?: '-' }}</span>
                                </div>
                            @endforeach

                            <div class="col-span-2 flex flex-col gap-3 sm:flex-row">
                                <div class="flex flex-1 items-center gap-2 rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                                    <x-heroicon-o-clipboard-document-check class="h-5 w-5 text-gray-400" />
                                    <div class="flex flex-col">
                                        <span class="text-gray-500">Info</span>
                                        <span class="break-words font-medium text-gray-900 dark:text-gray-300">
                                            {{ $parkingRegistration->info ?: '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Right card (Tabs) --}}
                <div class="flex flex-col overflow-y-auto rounded-xl bg-white dark:bg-gray-800">
                    <div x-data="{ activeTab: 'attachment' }" class="flex max-h-[100%] flex-1 flex-col">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
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
                        <div class="flex flex-1 flex-col">
                            {{-- Approval tab --}}
                            <div x-show="activeTab === 'approval'" class="flex-1 overflow-y-auto px-4">
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
                                    <tbody id="approval-table-body">
                                    </tbody>


                                </table>
                            </div>
                            {{-- Attachment tab --}}
                            <div x-show="activeTab === 'attachment'" class="flex-1 overflow-y-auto px-4">
                                <table class="w-full text-sm">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>

                                    <tbody id="parkingregistrationAttachmentTbody"></tbody>

                                </table>
                                {{-- Upload attachment (multi) --}}
                                @if ($canUpload)
                                    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                                        <form id="parkingregistrationAttachmentUploadForm" enctype="multipart/form-data">
                                            @csrf
                                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                                <div class="flex-1">
                                                    <label for="parkingregistrationAttachFiles"
                                                        class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                        Upload Attachments
                                                    </label>
                                                    <div class="flex items-center gap-3">
                                                        <input type="hidden" name="cpny_id" value="{{ $parkingRegistration->cpny_id }}">
                                                        <input type="hidden" name="department_id" value="{{ $parkingRegistration->department_id }}">
                                                        <input type="file" id="parkingregistrationAttachFiles"
                                                            name="attachments[]" multiple
                                                            class="block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-[7px] text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                                                        <button type="button" id="btnUploadSppbAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                            Upload
                                                        </button>
                                                        <button type="button" id="btnResetSppbAttachment"
                                                            class="inline-flex h-[36px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                            Reset
                                                        </button>
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                        Max 10 files, PDF / Image preferred.
                                                    </p>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Comments tab --}}
                            <div x-show="activeTab === 'comments'" class="flex-1 overflow-y-auto px-4">
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
            {{-- Detail Parking Registration --}}
            <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                    <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                        Parking Registration Detail
                    </h2>
                </div>

                @php
                    $isChangeNopol = strtoupper((string) $parkingRegistration->parking_type) === 'CHANGENOPOL';
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2">No</th>
                                <th class="px-3 py-2">Name</th>

                                @if ($isChangeNopol)
                                    <th class="px-3 py-2">No Polisi Lama</th>
                                    <th class="px-3 py-2">Jenis Lama</th>
                                    <th class="px-3 py-2">No Polisi Baru</th>
                                    <th class="px-3 py-2">Jenis Kendaraan Baru</th>
                                @else
                                    <th class="px-3 py-2">No Polisi</th>
                                    <th class="px-3 py-2">Jenis Kendaraan</th>
                                @endif

                                <th class="px-3 py-2">Start Date</th>
                                <th class="px-3 py-2">End Date</th>
                                <th class="px-3 py-2">STNK</th>
                                <th class="px-3 py-2">ID Card</th>
                                <th class="px-3 py-2">Bukti Bayar</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($parkingRegistrationDetail as $i => $d)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                    <td class="px-3 py-2">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 font-semibold">{{ $d->nama ?: '-' }}</td>

                                    @if ($isChangeNopol)
                                        <td class="px-3 py-2">
                                            {{ $d->nopol_lama ?: '-' }}
                                        </td>

                                        <td class="px-3 py-2">
                                            {{ $d->jenis_lama ?: '-' }}
                                        </td>

                                        <td class="px-3 py-2 font-semibold text-indigo-600">
                                            {{ $d->nopol ?: '-' }}
                                        </td>

                                        <td class="px-3 py-2 font-semibold text-indigo-600">
                                            {{ $d->jenis_kendaraan ?: '-' }}
                                        </td>
                                    @else
                                        <td class="px-3 py-2">
                                            {{ $d->nopol ?: '-' }}
                                        </td>

                                        <td class="px-3 py-2">
                                            {{ $d->jenis_kendaraan ?: '-' }}
                                        </td>
                                    @endif

                                    <td class="px-3 py-2">
                                        {{ $d->startdate ? \Carbon\Carbon::parse($d->startdate)->format('d M Y') : '-' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        {{ $d->enddate ? \Carbon\Carbon::parse($d->enddate)->format('d M Y') : '-' }}
                                    </td>

                                    <td class="px-3 py-2">
                                        @if ($d->attach_stnk_url)
                                            <a href="{{ $d->attach_stnk_url }}" target="_blank"
                                                class="font-medium text-indigo-600 hover:underline">
                                                📎 View
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-3 py-2">
                                        @if ($d->attach_idcard_url)
                                            <a href="{{ $d->attach_idcard_url }}" target="_blank"
                                                class="font-medium text-indigo-600 hover:underline">
                                                📎 View
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="px-3 py-2">
                                        @if ($d->attach_bukti_bayar_url)
                                            <a href="{{ $d->attach_bukti_bayar_url }}" target="_blank"
                                                class="font-medium text-indigo-600 hover:underline">
                                                📎 View
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isChangeNopol ? 11 : 9 }}" class="p-4 text-center italic text-gray-500">
                                        No detail found.
                                    </td>
                                </tr>
                            @endforelse
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

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
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
        <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Revise Task</h2>
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
            const docid = "{{ $parkingRegistration->docid }}";
            const doctype = "PKR";

            loadComments(docid, doctype);

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
                                '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                            );
                            return;
                        }

                        response.comments.forEach(comment => {
                            const timeStr = comment.message_date ?? comment.created_at;
                            const timeAgo = timeStr ? dayjs(timeStr).fromNow() : '';

                            commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                                    <p class="text-sm font-semibold">
                                        ${comment.username}
                                        <span class="text-sm text-gray-500">(${timeAgo})</span>
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
                    url: `/comments/${doctype}/${docid}`,
                    type: 'POST',
                    data: {
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            loadComments(docid, doctype);
                            $('#commentInput').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error adding comment:", xhr);
                        alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message : "Unknown Error"));
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


    <script>
        $(document).on("click", "#approveBtn", function() {
            let docid = "{{ $parkingRegistration->docid }}"; // Ambil Task ID dari modal
            approveParking(docid);
        });

        function approveParking(docid) {
            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner

            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/parkingregistration/${docid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid
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
                        toastr.success("Parking approved successfully!");
                        // window.location.href = "/parkingregistration";
                        closeOrRedirect("/parkingregistration");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this Parking.");
                    } else {
                        toastr.error("Error: Unable to approve Parking.");
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
                let docid = "{{ $parkingRegistration->docid }}";
                checkApproval(docid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let docid = "{{ $parkingRegistration->docid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    toastr.error("Please provide a reason for rejection.");
                    return;
                }

                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/parkingregistration/${docid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal parkingregistration
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Parking Rejected successfully!");
                            // window.location.href = "/parkingregistration";
                            closeOrRedirect("/parkingregistration");
                        } else {
                            alert("Failed to reject parkingregistration.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject parkingregistration status.");
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
                let docid = "{{ $parkingRegistration->docid }}";
                checkApproval(docid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let docid = "{{ $parkingRegistration->docid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/parkingregistration/${docid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal parkingregistration
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none    -none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            toastr.success("Parking Revised successfully!");
                            // window.location.href = "/parkingregistration";
                            closeOrRedirect("/parkingregistration");
                        } else {
                            alert("Failed to revise parkingregistration.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise parkingregistration status.");
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
        function checkApproval(docid, action) {
            $.ajax({
                url: `/approval/${docid}/check/${action}?doctype=PKR`,
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
                        toastr.error("You are not authorized to " + action + " this Parking.");
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
            const listUrl = @json(route('attachments.list', ['doctype' => 'PKR', 'refnbr' => $parkingRegistration->docid]));
            const uploadUrl = @json(route('attachments.upload', ['doctype' => 'PKR', 'refnbr' => $parkingRegistration->docid]));

            function $tbody() {
                return $('#parkingregistrationAttachmentTbody');
            }

            function renderAttachmentRows(rows) {
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
                    const fileName = at.name || at.display_name || at.attachment_name || '(no name)';
                    const createdBy = at.created_user ?? at.created_by ?? '-';
                    const dateStr = at.created_at ? dayjs(at.created_at).format('DD MMM YYYY HH:mm:ss') : '-';

                    const linkHtml = at.url
                        ? `<a href="${at.url}" target="_blank"
                                class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                📎 ${fileName}
                        </a>`
                        : `<span class="text-gray-700 dark:text-gray-300">📎 ${fileName}</span>
                        <span class="ml-2 text-sm text-red-500">(link unavailable)</span>`;

                    $tb.append(`
                        <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                            <td class="px-3 py-2">${linkHtml}</td>
                            <td class="px-3 py-2">${createdBy}</td>
                            <td class="px-3 py-2">${dateStr}</td>
                        </tr>
                    `);
                });
            }

            function refreshAttachments() {
                const $tb = $tbody();

                $tb.html(`
                    <tr>
                        <td colspan="3" class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                            Loading attachments...
                        </td>
                    </tr>
                `);

                return $.get(listUrl)
                    .done(function(res) {
                        if (res.success) {
                            renderAttachmentRows(res.attachments || []);
                        } else {
                            toastr.error(res.message || 'Failed to load attachments.');
                        }
                    })
                    .fail(function(xhr) {
                        console.error(xhr.responseText);
                        toastr.error('Failed to load attachments.');
                    });
            }

            // load awal
            refreshAttachments();

            $('#btnUploadSppbAttachment').on('click', function() {
                const $form = $('#parkingregistrationAttachmentUploadForm')[0];
                const files = $('#parkingregistrationAttachFiles')[0].files;

                if (!files || !files.length) {
                    toastr.warning('Please choose at least one file.');
                    return;
                }

                const fd = new FormData($form);

                $('#btnUploadSppbAttachment').prop('disabled', true).text('Uploading...');

                if (typeof showOverlay === 'function') {
                    showOverlay('Uploading');
                }

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res?.message || 'Upload failed.');
                            return;
                        }

                        toastr.success('Upload success.');
                        $('#parkingregistrationAttachFiles').val('');

                        // PENTING: ambil ulang attachment terbaru dari server
                        refreshAttachments();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        toastr.error(xhr.responseJSON?.message || 'Upload failed.');
                    },
                    complete: function() {
                        $('#btnUploadSppbAttachment').prop('disabled', false).text('Upload');

                        if (typeof hideOverlay === 'function') {
                            hideOverlay();
                        }
                    }
                });
            });

            $('#btnResetSppbAttachment').on('click', function() {
                $('#parkingregistrationAttachFiles').val('');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const docid = "{{ $parkingRegistration->docid }}";
            const doctype = "PKR";

            loadApproval(docid, doctype);
        });

        function getApprovalStatusLabel(status) {
            let statusText = "";
            let statusClass = "";

            switch (String(status || '').toUpperCase()) {
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

                case "C":
                    statusText = "Completed";
                    statusClass = "bg-green-600 text-white";
                    break;

                default:
                    statusText = status || "Unknown";
                    statusClass = "bg-gray-500 text-white";
            }

            return `<span class="${statusClass} inline-block rounded-full px-3 py-1 text-sm font-semibold">${statusText}</span>`;
        }

        function loadApproval(refnbr, doctype) {
            fetch(`/approval/${refnbr}/${doctype}`)
                .then(response => response.json())
                .then(res => {
                    const tbody = document.querySelector("#approval-table-body");

                    if (!tbody) {
                        console.error("approval-table-body not found");
                        return;
                    }

                    tbody.innerHTML = "";

                    if (!res.data || res.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="p-4 text-center italic text-gray-500">
                                    No approval data found.
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    res.data.forEach(row => {
                        const statusLabel = getApprovalStatusLabel(row.status);

                        tbody.innerHTML += `
                            <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-3 py-2">${row.aprv_leveling ?? '-'}</td>
                                <td class="px-3 py-2">${row.aprv_name ?? '-'}</td>
                                <td class="px-3 py-2">
                                    ${row.aprv_dateafter ? dayjs(row.aprv_dateafter).format('DD MMM YYYY HH:mm:ss') : ''}
                                </td>
                                <td class="px-3 py-2">${statusLabel}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error("Approval fetch failed →", err));
        }
    </script>
    <script>
        function closeOrRedirect(fallbackUrl = '/parkingregistration') {
            // coba tutup tab (berhasil kalau tab dibuka via window.open/target=_blank)
            window.close();

            // fallback kalau browser blok close
            setTimeout(() => {
                // kalau masih belum tertutup, redirect saja
                window.location.href = fallbackUrl;
            }, 300);
        }
    </script>



</x-app-layout>
