<!-- Informasi Dasar -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-lg text-gray-800 dark:text-gray-200">
    <div><strong>Doc ID</strong>: {{ $career->docid ?? '-' }} / {{ $career->apply_date ?? '-' }}</div>
    <div><strong>Company</strong>: {{ $career->cpnyid ?? '-' }}</div>
    <div><strong>Job Title</strong>: {{ $career->job_title ?? '-' }}</div>
    <div><strong>Name Applicant</strong>: {{ $career->fullname ?? '-' }}</div>
</div>
<br>
<hr>
<!-- Step Table -->
<div class="overflow-auto">
    <!-- Step Table Tabs -->
    <div x-data="{ subtab: 'step' }" class="w-full space-y-4">
        <!-- Sub Tab Header -->
        <div class="flex space-x-2 border-b border-gray-300 dark:border-gray-600 mb-2">
            <button @click="subtab = 'step'" 
                    :class="subtab === 'step' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Step
            </button>
            <button @click="subtab = 'schedule'" 
                    :class="subtab === 'schedule' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Schedule
            </button>
            <button @click="subtab = 'checklist'" 
                    :class="subtab === 'checklist' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Checklist
            </button>
            <button @click="subtab = 'assessment'" 
                    :class="subtab === 'assessment' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Assessment HC
            </button>
            <button @click="subtab = 'assessmentuser'" 
                    :class="subtab === 'assessmentuser' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Assessment User
            </button>
            {{-- <button @click="subtab = 'psychotest'" 
                    :class="subtab === 'psychotest' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Phsyco Test
            </button> --}}
            <button @click="subtab = 'payroll'" 
                    :class="subtab === 'payroll' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Payroll
            </button>
            <button @click="subtab = 'join'" 
                    :class="subtab === 'join' ? 'border-b-2 border-indigo-500 text-indigo-600' : 'text-gray-600 dark:text-gray-300'" 
                    class="px-3 py-1 text-lg font-medium focus:outline-none">
                Join
            </button>
        </div>

        <!-- Sub Tab Content -->
        <div>
            <!-- STEP TAB -->
            <div x-show="subtab === 'step'" x-transition class="bg-white dark:bg-gray-800">    
                <div class="flex flex-col md:flex-row gap-6 border-b dark:border-gray-200/10 overflow-y-auto mt-2">
                    <div class="w-full md:w-2/3 bg-white dark:bg-gray-800 ">
                        {{-- <header class="flex justify-between items-center px-6 pt-4 bg-white dark:bg-gray-700">
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
                        </header> --}}
                        <div class=" overflow-x-auto">
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
                                    @php $firstPendingShown = false; @endphp

                                    @foreach ($jobapplystep as $step)
                                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <td class="p-3 text-left">{{ $step->step_order }}</td>
                                            <td class="p-3 text-left">{{ $step->step_descr }}</td>
                                            <td class="p-3 text-left">{{ $step->aprvusername }}</td>
                                            <td class="p-3 text-left">{{ $step->aprvuserdate }}</td>
                                            <td class="p-3 text-left">
                                                @if ($step->status === 'P' && !$firstPendingShown)
                                                    @php $firstPendingShown = true; @endphp
                                                    <div class="flex gap-2">
                                                        {{-- <button id="approveBtn" class="bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">Approve</button>
                                                        <button id="rejectBtn" class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1 rounded">Reject</button> --}}
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
                                                @elseif ($step->status === 'A')
                                                    <span class="px-3 py-1 rounded-md bg-green-500 text-white">Approved</span>
                                                @elseif ($step->status === 'R')
                                                    <span class="px-3 py-1 rounded-md bg-red-500 text-white">Rejected</span>
                                                @elseif ($step->status === 'D')
                                                    <span class="px-3 py-1 rounded-md bg-blue-500 text-white">Revise</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach                                  
                                </tbody>
                                                           
                            </table>
                        </div>                    
                    </div>               
                    <div class="w-full md:w-1/3 bg-white dark:bg-gray-800 ">
                        <div x-data="{ isOpen: true, comments: [], newComment: '', currentUser: 'User1' }" class="flex flex-col justify-center w-full mt-4">
                            <header class="flex justify-between items-center px-5 pt-4 pb-2 overflow-y-auto" @click="isOpen = !isOpen">
                                <h2 class="font-semibold text-xl text-gray-700 dark:text-gray-100 flex items-center gap-2">
                                    💬 Comments
                                </h2>
                                <button>
                                    <span x-show="isOpen">🔽 Closed</span>
                                    <span x-show="!isOpen">▶️ See Details</span>
                                </button>
                            </header>
                            <div x-show="isOpen" class="overflow-hidden transition-all duration-300">
                                <div id="commentList" class="p-4 h-auto space-y-3 flex flex-col">
                                    <template x-for="(comment, index) in comments" :key="index">
                                        <div :class="comment.user === currentUser ? 'self-end bg-indigo-500 text-white' : 'self-start bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200'" class="p-3 rounded-lg max-w-xs shadow-md">
                                            <p class="text-sm"><strong x-text="comment.user"></strong>: <span x-text="comment.text"></span></p>
                                        </div>
                                    </template>
                                    <p x-show="comments.length === 0" class="text-gray-500 italic animate-pulse">No comments yet...</p>
                                </div>
                                <div class="flex items-center p-3 border-t border-gray-200 dark:border-gray-700 gap-2">
                                    <input 
                                        id="commentInput" 
                                        x-model="newComment" 
                                        type="text" 
                                        placeholder="Write a comment..." 
                                        class="flex-1 p-3 rounded-lg bg-gray-100 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-400 focus:outline-none transition-all duration-200">
                                    <button id="postCommentBtn"
                                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg active:scale-95">Post 🚀</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SCHEDULE TAB -->
            <div x-show="subtab === 'schedule'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.schedule')
            </div>

            <!-- CHECKLIST TAB -->
            <div x-show="subtab === 'checklist'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.checklist')
            </div>

            <!-- ASSESSMENT TAB HC-->
            <div x-show="subtab === 'assessment'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.assessmenthc')
            </div>
             <!-- ASSESSMENT TAB USER -->
            <div x-show="subtab === 'assessmentuser'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.assessmentuser')
            </div>
            <div x-show="subtab === 'psychotest'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.psychotest')
            </div>
            <div x-show="subtab === 'payroll'" x-transition class="bg-white dark:bg-gray-800 p-4 w-full rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.payroll')
            </div>
            <div x-show="subtab === 'join'" x-transition class="bg-white dark:bg-gray-800 p-4 rounded border border-gray-300 dark:border-gray-600">
                @include('pages.careers.join')
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>
  <!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
<script>
    $(document).ready(function () {
        let docid = "{{ $career->docid }}"; // Ambil task ID dari PHP ke JavaScript
        loadComments(docid);

        // **Fungsi untuk Memuat Komentar**
        function loadComments(docid) {
            console.log("Loading comments for Doc ID:", docid);
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

            $.ajax({
                url: `/career/${docid}/comments`,
                type: 'GET',
                success: function (response) {
                    console.log("Comments Loaded:", response);
                    commentList.empty();

                    if (response.comments.length === 0) {
                        commentList.append('<p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>');
                    } else {
                        response.comments.forEach(comment => {
                            let timeAgo = moment(comment.created_at).fromNow(); // Format waktu seperti "4 days ago"

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
                error: function (xhr) {
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
                url: `/career/${docid}/comments`,
                type: 'POST',
                data: {
                    docid: docid,
                    comment: input,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    console.log('Comment added successfully:', response);

                    if (response.status === "success") {
                        loadComments(docid); // **Reload komentar setelah menambahkan**
                        $('#commentInput').val(''); // Kosongkan input setelah sukses
                    }
                },
                error: function (xhr) {
                    console.error("Error adding comment:", xhr);
                    alert("Error: " + (xhr.responseJSON ? xhr.responseJSON.message : "Unknown Error"));
                },
                complete: function () {
                    $('#postCommentBtn').prop('disabled', false).text('Post'); // Aktifkan kembali tombol
                }
            });
        }

        // **Event Listener untuk Tombol "Post"**
        $('#postCommentBtn').click(function () {
            addComment();
        });

        // **Event Listener untuk Enter (Tanpa Shift) di Input**
        $('#commentInput').keypress(function (event) {
            if (event.which === 13 && !event.shiftKey) { 
                event.preventDefault(); 
                addComment();
            }
        });
    });
</script>
<script>
    $(document).on("click", "#approveBtn", function () {
        let docid = "{{ $career->docid }}"; // Ambil Task ID dari modal        
        approveCareer(docid);
    });

    function approveCareer(docid) {
        let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner
        
        // Tampilkan spinner di kanan bawah
        $spinner.fadeIn();

        $.ajax({
            url: `/career/${docid}/approve`,
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
                    toastr.success("Career approved successfully!");
                    // window.location.href = "/careers";
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);

                if (xhr.status === 403) {
                    toastr.error("You are not authorized to approve this career.");
                } else {
                    toastr.error("Error: Unable to approve career.");
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
            let docid = "{{ $career->docid }}";            
            // checkApproval(docid, "reject");

            $.get(`/career/${docid}/check-reject-permission`, function (res) {
                if (res.canReject) {
                    checkApproval(docid, "reject"); // lanjut cek approval umum
                } else {
                    toastr.warning("You are not allowed to reject at this step.");
                }
            }).fail(function () {
                toastr.error("Failed to verify reject permission.");
            });
           
        });

        // Saat tombol "Cancel" ditekan, tutup modal Reject
        $(document).on("click", "#cancelRejectBtn", function () {
            $("#rejectTaskModal").addClass("hidden");
        });

        // Saat tombol "Reject" ditekan, proses perubahan status
        $(document).on("click", "#confirmRejectBtn", function () {
            let docid = "{{ $career->docid }}";  // Ambil ID tugas dari modal detail
            let rejectReason = $("#rejectReason").val().trim();          

            if (rejectReason === "") {             
                toastr.error("Please provide a reason for rejection.");
                return;
            }

            let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
            // Tampilkan spinner di kanan bawah
            $spinner.fadeIn();

            $.ajax({
                url: `/career/${docid}/reject`,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid,
                    reason: rejectReason
                },
                success: function (response) {
                    if (response.success) {
                        // alert("Task has been rejected successfully.");

                        // Update status di modal career
                        $("#xstatus").text("Rejected")
                            .removeClass()
                            .addClass("w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded");
                        $spinner.fadeOut();
                        
                        // window.location.href = "/careers";
                        location.reload();
                    } else {
                        alert("Failed to reject career.");
                    }
                },            
                error: function (xhr) {
                    console.error(xhr.responseText);
        
                    if (xhr.status === 403) {
                        alert("You Can't Rejected!"); // Popup jika user tidak berhak
                    } else {
                        alert("Error: Unable to reject career status.");
                    }
                },
            });
        });
    });
</script>
<script>
    function checkApproval(docid, action) {
        console.log(docid,'-',action);
        $.ajax({
            url: `/career/${docid}/check-approval/${action}`,
            type: "GET",
            success: function (response) {
                if (response.canPerformAction) {
                    // Jika user bisa melakukan aksi, tampilkan modal atau langsung proses approval
                    if (action === "reject") {
                        $("#rejectReason").val("");  // Reset alasan reject
                        $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                    } else if (action === "revise") {
                        $("#reviseReason").val("");  // Reset alasan revise
                        $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                    // } else if (action === "approve") {
                    //     approvePersonnel(docid); // Jika approve, langsung jalankan proses approval
                    }
                } else {
                    // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                    toastr.error("You are not authorized to " + action + " this career.");
                }
            },
            error: function () {
                toastr.error("Error checking approval status.");
            }
        });
    }

</script>
<style>
    /* Styling untuk loading spinner di kanan bawah */
    #loadingSpinnerContainer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.7);
        padding: 10px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        z-index: 1000;
        display: none; /* Tersembunyi saat tidak digunakan */
    }

    #loadingSpinnerContainer svg {
        width: 30px;
        height: 30px;
        color: white;
    }
</style>


