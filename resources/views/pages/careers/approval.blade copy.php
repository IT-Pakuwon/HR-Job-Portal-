<!-- Informasi Dasar -->
<div class="space-y-2 text-sm text-gray-800 dark:text-gray-200">
    <div><strong>Company</strong>: {{ $career->cpnyid ?? '-' }}</div>
    <div><strong>Job Title</strong>: {{ $career->job_title ?? '-' }}</div>
    <div><strong>Name Applicant</strong>: {{ $career->fullname ?? '-' }}</div>
</div>

<!-- Step Table -->
<div class="overflow-auto">
    <!-- Step Table Tabs -->
    <div x-data="{ subtab: 'step' }" class="w-full space-y-4">
        <!-- Sub Tab Header -->
        <div class="flex space-x-2 border-b border-gray-300 dark:border-gray-600 mb-2">
            <button @click="subtab = 'step'" 
                    :class="subtab === 'step' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-sm font-medium focus:outline-none">
                Step
            </button>
            <button @click="subtab = 'schedule'" 
                    :class="subtab === 'schedule' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-sm font-medium focus:outline-none">
                Schedule
            </button>
            <button @click="subtab = 'checklist'" 
                    :class="subtab === 'checklist' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-sm font-medium focus:outline-none">
                Checklist
            </button>
            <button @click="subtab = 'assessment'" 
                    :class="subtab === 'assessment' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-sm font-medium focus:outline-none">
                Assessment
            </button>
        </div>

        <!-- Sub Tab Content -->
        <div>
            <!-- STEP TAB -->
            <div x-show="subtab === 'step'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">            
                <div class="flex flex-col justify-center w-full mt-2 border-b dark:border-gray-200/10 overflow-y-auto h-70">
                    <header class="flex justify-between items-center px-6 pt-4 bg-white dark:bg-gray-700">
                        <h2 class="font-semibold text-xl text-gray-600 dark:text-gray-100"> 🚀 Approval</h2>
                        <div class="flex gap-2">
                            <div class="flex items-center gap-1 px-2 py-2 bg-green-500/15 text-green-700 text-sm font-medium rounded-md hover:bg-green-600 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                </svg>                       
                                <button id="approveBtn" class="focus:outline-none">Approve</button>
                            </div>
                            <div class="flex items-center gap-1 px-2 bg-red-500/15 text-red-700 text-sm font-medium rounded-md hover:bg-red-600 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                                </svg>                        
                                <button id="rejectBtn" class="focus:outline-none">Reject</button>                        
                            </div>                           
                        </div>
                    </header>
                    <div class="px-4 pt-4 overflow-x-auto">
                        <table class="w-full text-sm mb-4">
                            <thead>
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <th class="p-3 text-left">No</th>
                                    <th class="p-3 text-left">Activity</th>
                                    <th class="p-3 text-left">User</th>
                                    <th class="p-3 text-left">Date</th>
                                    <th class="p-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobapplystep as $step)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <td class="p-3 text-left">{{$step->step_order }}</td>
                                    <td class="p-3 text-left">{{ $step->step_id }}</td>
                                    <td class="p-3 text-left">{{ $step->aprvusername }}</td>
                                    <td class="p-3 text-left">{{ $step->aprvuserdate }}</td>
                                    <td class="p-3 text-left">
                                        @php
                                            $statusText = '';
                                            $statusClass = '';
                                            switch ($step->status) {
                                                case 'P': $statusText = 'Waiting Approval'; $statusClass = 'bg-yellow-500 text-white'; break;
                                                case 'A': $statusText = 'Approved'; $statusClass = 'bg-green-500 text-white'; break;
                                                case 'R': $statusText = 'Rejected'; $statusClass = 'bg-red-500 text-white'; break;
                                                case 'D': $statusText = 'Revise'; $statusClass = 'bg-blue-500 text-white'; break;
                                                default: $statusText = 'Unknown'; $statusClass = 'bg-gray-500 text-white';
                                            }
                                        @endphp
                                        <span class="px-3 py-1 rounded-md {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>  
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SCHEDULE TAB -->
            <div x-show="subtab === 'schedule'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                <ul class="list-disc list-inside text-sm text-gray-800 dark:text-gray-200 space-y-1">
                    <li>Schedule item 1</li>
                    <li>Schedule item 2</li>
                    <li>Schedule item 3</li>
                </ul>
            </div>

            <!-- CHECKLIST TAB -->
            <div x-show="subtab === 'checklist'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                <table class="min-w-full text-sm border border-gray-300 dark:border-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        <tr>
                            <th class="border px-3 py-2">Approve User</th>
                            <th class="border px-3 py-2">Checklist Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-3 py-2">User A</td>
                            <td class="border px-3 py-2">Checklist 1</td>
                        </tr>
                        <tr>
                            <td class="border px-3 py-2">User B</td>
                            <td class="border px-3 py-2">Checklist 2</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ASSESSMENT TAB -->
            <div x-show="subtab === 'assessment'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                <table class="min-w-full text-sm border border-gray-300 dark:border-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        <tr>
                            <th class="border px-3 py-2">Approve Date</th>
                            <th class="border px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-3 py-2">2025-04-22</td>
                            <td class="border px-3 py-2">Approved</td>
                        </tr>
                        <tr>
                            <td class="border px-3 py-2">2025-04-23</td>
                            <td class="border px-3 py-2">Pending</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div id="loadingSpinnerContainer" class="flex justify-center items-center h-16">
        <svg class="animate-spin h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div>
    <div id="rejectTaskModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Reject Task</h2>
            <textarea id="rejectReason" class="w-full mt-2 p-3 border rounded-lg focus:outline-none dark:bg-gray-800 dark:text-white"
                    placeholder="Enter rejection reason..."></textarea>
    
            <div class="mt-4 flex justify-between">
                <button id="cancelRejectBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmRejectBtn" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                    Reject
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on("click", "#approveBtn", function () {
        let docid = "{{ $personnel->docid }}"; // Ambil Task ID dari modal        
        approvePersonnel(docid);
    });

    function approvePersonnel(docid) {
        let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
        
        // Tampilkan spinner di kanan bawah
        $spinner.fadeIn();

        $.ajax({
            url: `/personnel/${docid}/approve`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                docid: docid
            },
            success: function (response) {
                if (response.success) {
                    // Update status di UI
                    $("#xstatus").text("Approved")
                        .removeClass()
                        .addClass("w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded");

                    // Tampilkan alert sukses
                    toastr.success("Personnel approved successfully!");
                    window.location.href = "/personnels";
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);

                if (xhr.status === 403) {
                    toastr.error("You are not authorized to approve this personnel.");
                } else {
                    toastr.error("Error: Unable to approve personnel.");
                }
            },
            complete: function () {
                // Sembunyikan spinner setelah request selesai
                $spinner.fadeOut();
            }
        });
    }
</script>


<script>
    $(document).ready(function () {
        // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
        $(document).on("click", "#rejectBtn", function () {
            $("#rejectReason").val("");  // Reset alasan reject
            // $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
            let docid = "{{ $personnel->docid }}";            
            checkApproval(docid, "reject");
           
        });

        // Saat tombol "Cancel" ditekan, tutup modal Reject
        $(document).on("click", "#cancelRejectBtn", function () {
            $("#rejectTaskModal").addClass("hidden");
        });

        // Saat tombol "Reject" ditekan, proses perubahan status
        $(document).on("click", "#confirmRejectBtn", function () {
            let docid = "{{ $personnel->docid }}";  // Ambil ID tugas dari modal detail
            let rejectReason = $("#rejectReason").val().trim();          

            if (rejectReason === "") {             
                toastr.error("Please provide a reason for rejection.");
                return;
            }

            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/personnel/${docid}/reject`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid,
                    reason: rejectReason
                },
                success: function (response) {
                    if (response.success) {
                        // alert("Task has been rejected successfully.");

                        // Update status di modal personnel
                        $("#xstatus").text("Rejected")
                            .removeClass()
                            .addClass("w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded");
                        $spinner.fadeOut();
                        
                        window.location.href = "/personnels";
                    } else {
                        alert("Failed to reject personnel.");
                    }
                },            
                error: function (xhr) {
                    console.error(xhr.responseText);
        
                    if (xhr.status === 403) {
                        alert("You Can't Rejected!"); // Popup jika user tidak berhak
                    } else {
                        alert("Error: Unable to reject personnel status.");
                    }
                },
            });
        });
    });
</script>
