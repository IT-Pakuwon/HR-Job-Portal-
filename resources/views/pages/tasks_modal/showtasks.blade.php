<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-4">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-6">
                    <div
                        class="shadow-xs col-span-full flex flex-col rounded-xl bg-white sm:col-span-6 xl:col-span-6 dark:bg-gray-800">
                        <header class="border-b border-gray-100 px-5 py-4 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">{{ $task->taskid }}</h2>
                        </header>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div><strong class="text-gray-700 dark:text-gray-300">Company:</strong> <span
                                        id="company">{{ $task->cpnyid }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">Department:</strong> <span
                                        id="department">{{ $task->departementid }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">Summary:</strong> <span
                                        id="summary">{{ $task->summary }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">Type:</strong> <span
                                        id="tasktype">{{ $task->tasktype }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">Priority:</strong> <span
                                        id="taskpriority">{{ $task->taskpriority }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">participant:</strong> <span
                                        id="participant">{{ $task->participant }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">startdate:</strong> <span
                                        id="startdate">{{ $task->startdate }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">Description:</strong> <span
                                        id="description">{{ $task->description }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">duedate:</strong> <span
                                        id="duedate">{{ $task->duedate }}</span></div>
                                <div><strong class="text-gray-700 dark:text-gray-300">status:</strong> <span
                                        id="status">{{ $task->status }}</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan (4/12) - Approval -->
                    <div
                        class="shadow-xs col-span-full flex flex-col rounded-xl bg-white sm:col-span-6 xl:col-span-6 dark:bg-gray-800">
                        <header class="border-b border-gray-100 px-5 py-4 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Approval</h2>
                        </header>
                        <div class="flex gap-2">
                            <div
                                class="flex items-center gap-1 rounded-md border border-green-700 bg-green-500/15 px-2 text-sm font-medium text-green-700 transition hover:bg-green-600 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                </svg>
                                <button id="approveBtn" class="focus:outline-none">Approve</button>
                            </div>
                            <div
                                class="flex items-center gap-1 rounded-md border border-gray-700 bg-gray-500/15 px-2 text-sm font-medium transition hover:bg-gray-600 hover:text-white dark:text-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <button id="reviseBtn" class="focus:outline-none">Revise</button>
                            </div>
                            <div
                                class="flex items-center gap-1 rounded-md border border-red-700 bg-red-500/15 px-2 text-sm font-medium text-red-700 transition hover:bg-red-600 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                                </svg>
                                <button id="rejectBtn" class="focus:outline-none">Reject</button>
                            </div>

                        </div>
                        <div class="p-6">
                            <table id="approvalTable"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="border p-3 text-left">Level</th>
                                        <th class="border p-3 text-left">Name</th>
                                        <th class="border p-3 text-left">Date</th>
                                        <th class="border p-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($approval as $ap)
                                        <tr class="border-b">
                                            <td class="border p-3 text-left">{{ $ap->aprvid }}</td>
                                            <td class="border p-3 text-left">{{ $ap->name }}</td>
                                            <td class="border p-3 text-left">{{ $ap->aprvdatebefore }}</td>
                                            <td class="border p-3 text-left">
                                                @php
                                                    $statusText = '';
                                                    $statusClass = '';

                                                    switch ($ap->status) {
                                                        case 'P':
                                                            $statusText = 'Waiting Approval';
                                                            $statusClass = 'bg-yellow-200 text-yellow-700';
                                                            break;
                                                        case 'A':
                                                            $statusText = 'Approved';
                                                            $statusClass = 'bg-green-200 text-green-700';
                                                            break;
                                                        case 'R':
                                                            $statusText = 'Rejected';
                                                            $statusClass = 'bg-red-200 text-red-700';
                                                            break;
                                                        case 'D':
                                                            $statusText = 'Reuse';
                                                            $statusClass = 'bg-blue-200 text-blue-700';
                                                            break;
                                                        default:
                                                            $statusText = 'Unknown';
                                                            $statusClass = 'bg-gray-200 text-gray-700';
                                                    }
                                                @endphp
                                                <span
                                                    class="{{ $statusClass }} rounded px-3 py-1">{{ $statusText }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                    </div>

                </div>
            </div>
            <div class="max-w-9xl mx-auto w-full px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid grid-cols-12 gap-6">
                    <div
                        class="shadow-xs col-span-full flex flex-col rounded-xl bg-white sm:col-span-6 xl:col-span-5 dark:bg-gray-800">
                        <header class="border-b border-gray-100 px-5 py-4 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Attachment</h2>
                        </header>
                        <div class="p-6">
                            <table class="w-full rounded-lg border border-gray-300 dark:border-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="border p-3 text-left">Filename</th>
                                        <th class="border p-3 text-left">Created By</th>
                                        <th class="border p-3 text-left">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attachment as $at)
                                        @php
                                            $year = $at->created_at->year;
                                            $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                        @endphp
                                        <tr class="border-b">
                                            <td class="border p-3">
                                                <a href="{{ $fileUrl }}" target="_blank"
                                                    class="flex items-center gap-2 text-blue-600 hover:underline dark:text-blue-400">
                                                    📎 {{ $at->name }}
                                                </a>
                                            </td>
                                            <td class="border p-3">{{ $at->created_user }}</td>
                                            <td class="border p-3">
                                                {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div
                        class="shadow-xs col-span-full flex flex-col rounded-xl bg-white sm:col-span-6 xl:col-span-7 dark:bg-gray-800">
                        <header class="border-b border-gray-100 px-5 py-4 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Comment</h2>
                        </header>
                        <div class="p-6">
                            <div id="commentList"
                                class="h-35 space-y-2 overflow-y-auto border-b bg-white p-4 dark:bg-gray-200/5">
                                <p class="italic text-gray-500">Loading comments...</p>
                            </div>

                            <div
                                class="flex items-center gap-2 border-t border-gray-200 bg-white p-2 dark:border-gray-300 dark:bg-gray-200/5">
                                <input id="commentInput" type="text" placeholder="Write a comment..."
                                    class="flex-1 rounded bg-gray-100 p-2 text-sm focus:outline-none dark:bg-gray-700 dark:text-white">
                                <button id="postCommentBtn"
                                    class="text-m rounded bg-indigo-500 px-4 py-2.5 text-white">Post</button>
                            </div>
                        </div>


                    </div>

                </div>
            </div>
        </div>
    </div>
    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
            <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Reject Task</h2>

            <label for="rejectReason" class="text-sm text-gray-600 dark:text-white">Reason for Rejection:</label>
            <textarea id="rejectReason"
                class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
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

            <label for="reviseReason" class="text-sm text-gray-600 dark:text-white">Reason for Revise:</label>
            <textarea id="reviseReason"
                class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn" class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                    Revise
                </button>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            let docid = "{{ $task->docid }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(docid);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(docid) {
                console.log("Loading comments for Doc ID:", docid);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/task/${docid}/comments`,
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
                                let timeAgo = moment(comment.created_at)
                                    .fromNow(); // Format waktu seperti "4 days ago"

                                commentList.append(`
                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2 border border-gray-300 dark:border-gray-700">
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

            // **Fungsi untuk Menambahkan Komentar**
            function addComment() {
                let input = $('#commentInput').val().trim();

                if (input === "") {
                    alert("Please enter a comment.");
                    return;
                }

                $('#postCommentBtn').prop('disabled', true).text('Posting...'); // Disable button saat proses

                $.ajax({
                    url: `/task/${docid}/comments`,
                    type: 'POST',
                    data: {
                        docid: docid,
                        comment: input,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Comment added successfully:', response);

                        if (response.status === "success") {
                            loadComments(docid); // **Reload komentar setelah menambahkan**
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
                            'Post'); // Aktifkan kembali tombol
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
            let docid = "{{ $task->docid }}"; // Ambil Task ID dari modal
            let $btn = $(this); // Ambil tombol yang diklik

            // Ubah teks tombol menjadi "Loading..." dengan spinner
            $btn.html(
                '<svg class="animate-spin h-5 w-5 mr-1 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Processing...'
            ).prop("disabled", true);

            // Kirim AJAX Request ke Controller untuk mengupdate status task
            $.ajax({
                url: `/task/${docid}/approve`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid
                },
                success: function(response) {
                    if (response.success) {
                        // Ubah tampilan status di modal menjadi "Completed"
                        $("#xstatus").text("Completed")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                            );

                        // Tampilkan alert sukses
                        alert("Prf approved successfully!");
                        window.location.href = "/tasks";

                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        alert("You Can't Approve!"); // Popup jika user tidak berhak
                    } else {
                        alert("Error: Unable to update task status.");
                    }
                },
                complete: function() {
                    // Kembalikan tombol ke kondisi semula setelah selesai
                    $btn.html("Approve").prop("disabled", false);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
            $(document).on("click", "#rejectBtn", function() {
                $("#rejectReason").val(""); // Reset alasan reject
                $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                // $("#taskDetailModal").css("z-index", "50"); // Pastikan modal task tetap di belakang
            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let docid = "{{ $task->docid }}"; // Ambil ID tugas dari modal detail
                let rejectReason = $("#rejectReason").val().trim();

                if (rejectReason === "") {
                    alert("Please provide a reason for rejection.");
                    return;
                }

                $.ajax({
                    url: `/task/${docid}/reject`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: rejectReason
                    },
                    success: function(response) {
                        if (response.success) {
                            alert("Task has been rejected successfully.");

                            // Update status di modal task
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );

                            // Tutup modal Reject & Task setelah reject berhasil
                            $("#rejectTaskModal").addClass("hidden");
                            $("#taskDetailModal").addClass("hidden");

                            window.location.href = "/tasks";
                        } else {
                            alert("Failed to reject task.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Rejected!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to reject task status.");
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
                $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                // $("#taskDetailModal").css("z-index", "50"); // Pastikan modal task tetap di belakang
            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let docid = "{{ $task->docid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    alert("Please provide a reason for revise.");
                    return;
                }

                $.ajax({
                    url: `/task/${docid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            alert("Task has been reviseed successfully.");

                            // Update status di modal task
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );

                            // Tutup modal Revise & Task setelah revise berhasil
                            $("#reviseTaskModal").addClass("hidden");
                            $("#taskDetailModal").addClass("hidden");
                            window.location.href = "/tasks";
                        } else {
                            alert("Failed to revise task.");
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);

                        if (xhr.status === 403) {
                            alert("You Can't Revised!"); // Popup jika user tidak berhak
                        } else {
                            alert("Error: Unable to revise task status.");
                        }
                    },
                });
            });
        });
    </script>





</x-app-layout>
