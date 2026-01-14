<<<<<<< HEAD
<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <div id="loadingOverlay" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
        <div class="flex flex-col items-center">
            <svg class="animate-spin h-12 w-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <p class="text-white mt-2 text-sm font-semibold">Processing...</p>
        </div>
    </div>
    <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto">        
        <div class="grid">
            <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-12 gap-6">    
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-6 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                        <header class="px-6 py-4 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $personnel->docid }}</h2>
                        </header>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Company</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="company">{{ $personnel->cpnyid }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Department</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="department">{{ $personnel->departementid }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Job Title</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="job_title">{{ $personnel->job_title }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Level</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="level">{{ $personnel->job_level }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Immediate Superior</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="superior">{{ $personnel->immediate_superior }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">State Position</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="state_position">{{ $personnel->state_position }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Job Type</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="job_type">{{ $personnel->job_type }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Reason for Vacancy</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="reason_vacancy">{{ $personnel->reason_vacancy }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Total Number Required</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="total_required">{{ $personnel->required }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Actual</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="actual">{{ $personnel->actual }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">The Actual Number</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" id="actual_number">{{ $personnel->total_actual }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Kanan (4/12) - Approval -->
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-6 bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
                        <header class="flex justify-between items-center px-6 py-4 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="font-semibold text-sm text-gray-800 dark:text-gray-100">Approval</h2>
                            <div class="flex gap-2">
                                <div class="flex items-center gap-1 px-2 bg-green-500/15 text-green-700 text-xs font-medium rounded-md border border-green-700 hover:bg-green-600 hover:text-white transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                    </svg>                       
                                    <button id="approveBtn" class="focus:outline-none">Approve</button>
                                </div>
                                <div class="flex items-center gap-1 px-2  bg-red-500/15 text-red-700 text-xs font-medium rounded-md border border-red-700 hover:bg-red-600 hover:text-white transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                                    </svg>                        
                                    <button id="rejectBtn" class="focus:outline-none">Reject</button>                        
                                </div>
                                <div class="flex items-center gap-1 px-2 bg-gray-500/15 dark:text-gray-200 text-xs font-medium rounded-md border border-gray-700 hover:bg-gray-600 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>                    
                                    <button id="reviseBtn" class="focus:outline-none">Revise</button> 
                                </div>
                            </div>
                        </header>
                        <div class="p-6 overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <th class="p-3 text-left">Level</th>
                                        <th class="p-3 text-left">Name</th>
                                        <th class="p-3 text-left">Date</th>
                                        <th class="p-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                                    @foreach ($approval as $ap)
                                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <td class="p-3 text-left">{{ $ap->aprvid }}</td>
                                        <td class="p-3 text-left">{{ $ap->name }}</td>
                                        <td class="p-3 text-left">{{ $ap->aprvdatebefore }}</td>
                                        <td class="p-3 text-left">
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
                                            <span class="px-3 py-1 rounded-md {{ $statusClass }}">{{ $statusText }}</span>
                                        </td>  
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>    
            <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-12 gap-6">    
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Job Responsibilities</h2>
                        </header>
                        <div class="p-6">
                            <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="p-3 border text-center w-16">No</th>
                                        <th class="p-3 border text-left">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jobres as $jr)
                                    <tr class="border-b">
                                        <td class="p-3 border text-center">{{ $jr->no_job_responsiblities }}</td>
                                        <td class="p-3 border">{{ $jr->job_responsibilities_descr }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>            
                </div>
            </div>       
            <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-12 gap-6">    
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Job Qualification</h2>
                        </header>
                        <div class="p-6">
                            <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="p-3 border text-center w-16">No</th>
                                        <th class="p-3 border text-left">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jobqua as $jq)
                                    <tr class="border-b">
                                        <td class="p-3 border text-center">{{ $jq->no_job_qualification }}</td>
                                        <td class="p-3 border">{{ $jq->job_qualification_descr }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>            
                </div>
            </div>     
            <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-12 gap-6">    
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-5 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Attachment</h2>
                        </header>
                        <div class="p-6">
                            <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="p-3 border text-left">Filename</th>
                                        <th class="p-3 border text-left">Created By</th>
                                        <th class="p-3 border text-left">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attachment as $at)
                                    @php
                                        $year = ($at->created_at)->year;
                                        $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                    @endphp
                                    <tr class="border-b">
                                        <td class="p-3 border">
                                            <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-2">
                                                📎 {{ $at->name }}
                                            </a>
                                        </td>
                                        <td class="p-3 border">{{ $at->created_user }}</td>
                                        <td class="p-3 border">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>                                      
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-7 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Comment</h2>
                        </header>                        
                        <div class="p-6">
                            <div id="commentList" class="p-4 h-35 overflow-y-auto border-b bg-white dark:bg-gray-200/5 space-y-2">
                                <p class="text-gray-500 italic">Loading comments...</p>
                            </div>
                            
                            <div class="flex items-center border-t border-gray-200 dark:border-gray-300 p-2 bg-white dark:bg-gray-200/5 gap-2">
                                <input id="commentInput" type="text" placeholder="Write a comment..."
                                    class="flex-1 p-2 rounded focus:outline-none text-xs bg-gray-100 dark:bg-gray-700 dark:text-white">
                                <button id="postCommentBtn" class="bg-indigo-500 text-white px-4 py-2.5 rounded text-m">Post</button>
                            </div>
                        </div>
                        
                        
                    </div>

                </div>
            </div>       
        </div>
    </div>
    <div id="rejectTaskModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg   w-full max-w-md">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">Reject Task</h2>
    
            <label for="rejectReason" class="text-gray-600 dark:text-white text-xs">Reason for Rejection:</label>
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
    <div id="reviseTaskModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg   w-full max-w-md">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">Revise Task</h2>
    
            <label for="reviseReason" class="text-gray-600 dark:text-white text-xs">Reason for Revise:</label>
            <textarea id="reviseReason" class="w-full mt-2 p-3 border rounded-lg focus:outline-none dark:bg-gray-800 dark:text-white"
                      placeholder="Enter revise reason..."></textarea>
    
            <div class="mt-4 flex justify-between">
                <button id="cancelReviseBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
                <button id="confirmReviseBtn" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                    Revise
                </button>
            </div>
        </div>
=======
<x-app-layout> 
    <div class="py-4 w-full max-w-9xl mx-auto">
        <div class="grid">
            <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-12 gap-6">    
                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-6 bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                        
                        <header class="px-6 py-4 flex justify-between items-center border-b border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                            <h1 class="text-lg font-semibold text-gray-700 dark:text-gray-100">{{ $personnels->docid }}</h1>
                        <!-- Header -->
                        <div x-data="{ open: false }">
                            <!-- Button to Open Modal -->
                            <button @click="open = true" class="px-4 py-2 bg-blue-500 text-white rounded">Open Modal</button>
                        
                            <!-- Modal -->
                            <div x-show="open" 
                            x-transition:enter="transform transition ease-in-out duration-300"
                            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                            x-transition:leave="transform transition ease-in-out duration-300"
                            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                            class="fixed top-0 right-0 w-full h-full md:w-1/3 bg-white   p-6 z-50">
                        
                                
                                <button @click="open = false" class="absolute top-4 right-4 text-gray-500">✖</button>
                        
                                <!-- Your Content -->
                                {{-- Approval --}}
                                <div class="flex flex-col justify-center w-full mt-6 dark:bg-gray-800 overflow-hidden">
                                    <header class="flex justify-between items-center px-6 pt-4 bg-white dark:bg-gray-700">
                                        <h2 class="font-semibold text-lg text-gray-500 dark:text-gray-100">Approval</h2>
                                        <div class="flex gap-2">
                                            <div class="flex items-center gap-1 px-2 py-2 bg-green-500/15 text-green-700 text-xs font-medium rounded-md hover:bg-green-600 hover:text-white transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
                                                </svg>                       
                                                <button id="approveTaskBtn" class="focus:outline-none">Approve</button>
                                            </div>
                                            <div class="flex items-center gap-1 px-2 bg-red-500/15 text-red-700 text-xs font-medium rounded-md hover:bg-red-600 hover:text-white transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.498 15.25H4.372c-1.026 0-1.945-.694-2.054-1.715a12.137 12.137 0 0 1-.068-1.285c0-2.848.992-5.464 2.649-7.521C5.287 4.247 5.886 4 6.504 4h4.016a4.5 4.5 0 0 1 1.423.23l3.114 1.04a4.5 4.5 0 0 0 1.423.23h1.294M7.498 15.25c.618 0 .991.724.725 1.282A7.471 7.471 0 0 0 7.5 19.75 2.25 2.25 0 0 0 9.75 22a.75.75 0 0 0 .75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 0 0 2.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384m-10.253 1.5H9.7m8.075-9.75c.01.05.027.1.05.148.593 1.2.925 2.55.925 3.977 0 1.487-.36 2.89-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398-.306.774-1.086 1.227-1.918 1.227h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 0 0 .303-.54" />
                                                </svg>                        
                                                <button id="rejectTaskBtn" class="focus:outline-none">Reject</button>                        
                                            </div>
                                            <div class="flex items-center gap-1 px-2 bg-red-500/15 text-red-700 text-xs font-medium rounded-md hover:bg-red-600 hover:text-white transition">
                                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>                    
                                                <button id="rejectTaskBtn" class="focus:outline-none">Revise</button>                        
                                            </div>
                                        </div>
                                    </header>
                                    <div class="px-4 pt-4 overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="text-gray-700 dark:text-gray-300">
                                                    <th class="p-3 text-left">Level</th>
                                                    <th class="p-3 text-left">Name</th>
                                                    <th class="p-3 text-left">Date</th>
                                                    <th class="p-3 text-left">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($approval as $ap)
                                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                                    <td class="p-3 text-left">{{ $ap->aprvid }}</td>
                                                    <td class="p-3 text-left">{{ $ap->name }}</td>
                                                    <td class="p-3 text-left">{{ $ap->aprvdatebefore }}</td>
                                                    <td class="p-3 text-left">
                                                        @php
                                                            $statusText = '';
                                                            $statusClass = '';
                                                            switch ($ap->status) {
                                                                case 'P': $statusText = 'Waiting Approval'; $statusClass = 'bg-yellow-500 text-white'; break;
                                                                case 'A': $statusText = 'Approved'; $statusClass = 'bg-green-500 text-white'; break;
                                                                case 'R': $statusText = 'Rejected'; $statusClass = 'bg-red-500 text-white'; break;
                                                                case 'D': $statusText = 'Reuse'; $statusClass = 'bg-blue-500 text-white'; break;
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
                                {{-- Attachment --}}

                                <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                                    <div class="grid gap-6">    
                                                <div class="grid grid-cols-12 gap-6">    
                                                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                                                        <header class="flex justify-between items-center px-5 py-4">
                                                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Attachment</h2>
                                                        </header>
                                                        <div class="p-6">
                                                            <div x-show="isOpen" class="overflow-x-auto transition-all duration-300 ease-in-out">
                                                                <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                                                    <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                                        <tr>
                                                                            <th class="p-3 border text-left">Filename</th>
                                                                            <th class="p-3 border text-left">Created By</th>
                                                                            <th class="p-3 border text-left">Date</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                                                                        @foreach ($attachment as $at)
                                                                        @php
                                                                            $year = ($at->created_at)->year;
                                                                            $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                                                        @endphp
                                                                        <tr class="border-b">
                                                                            <td class="p-3 border">
                                                                                <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-2">
                                                                                    📎 {{ $at->name }}
                                                                                </a>
                                                                            </td>
                                                                            <td class="p-3 border">{{ $at->created_user }}</td>
                                                                            <td class="p-3 border">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>         
                                        <div x-data="{ isOpen: false }" class="pb-3">
                                            <div class="overflow-hidden">
                                                <div class="grid grid-cols-6 gap-6">    
                                                    <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                                                        <header class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700">
                                                            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Comment</h2>
                                                            <button @click="isOpen = !isOpen" class="text-gray-600 dark:text-white focus:outline-none">
                                                                <span x-show="!isOpen">See Details</span>
                                                                <span x-show="isOpen">Close</span>
                                                            </button>
                                                        </header>
                                                        <div>
                                                            <div x-show="isOpen" class="overflow-x-auto transition-all duration-300 ease-in-out">
                                                                <div id="commentList" class="p-4 h-70 overflow-y-auto border-b bg-white dark:bg-gray-200/5 space-y-2">
                                                                    <p class="text-gray-500 italic">Loading comments...</p>
                                                                </div>
                                                                <div class="flex items-center border-t border-gray-200 dark:border-gray-300 p-2 bg-white dark:bg-gray-200/5 gap-2">
                                                                    <input id="commentInput" type="text" placeholder="Write a comment..."
                                                                        class="flex-1 p-2 rounded focus:outline-none text-xs bg-gray-100 dark:bg-gray-700 dark:text-white">
                                                                    <button id="postCommentBtn" class="bg-indigo-500 text-white px-4 py-2.5 rounded text-m">Post</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>            
                                        </div>   
                                    </div>   
                                </div>   
                            </div>
                        
                            <!-- Background Overlay -->
                            <div x-show="open" @click="open = false" class="fixed inset-0 bg-gray-500/10 bg-opacity-100" x-transition></div>
                        </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Company:</span>
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-100" id="company">{{ $personnels->cpnyid }}</span>
                            </div>
                        </header>
                    
                        <!-- Main Content -->
                        <div class="p-6 space-y-6">
                            <!-- Job Details -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-1">
                                <div class="flex items-center gap-3">
                                    <i class="lucide lucide-building-2 w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Department</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $personnels->departementid }}</p>
                                    </div>
                                </div>
                            
                                <div class="flex items-center gap-3">
                                    <i class="lucide lucide-briefcase w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Job Title</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $personnels->job_title }}</p>
                                    </div>
                                </div>
                            
                                <div class="flex items-center gap-3">
                                    <i class="lucide lucide-clipboard-list w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Job Type</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $personnels->job_type }}</p>
                                    </div>
                                </div>
                            
                                <div class="flex items-center gap-3">
                                    <i class="lucide lucide-bar-chart-2 w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Level</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $personnels->job_level }}</p>
                                    </div>
                                </div>
                            
                                <div class="flex items-center gap-3">
                                    <i class="lucide lucide-map-pin w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">State Position</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $personnels->state_position }}</p>
                                    </div>
                                </div>
                            
                                <div class="flex items-center gap-3">
                                    <i class="lucide lucide-user-check w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                                    <div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Immediate Superior</span>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $personnels->immediate_superior }}</p>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Job Numbers -->
                            <div class="grid grid-cols-3 gap-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Required</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $personnels->required }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Actual</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $personnels->actual }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Actual Number</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $personnels->total_actual }}</span>
                                </div>
                            </div>
                    
                            <!-- Reason for Vacancy -->
                            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300 font-semibold">
                                    Reason for Vacancy
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-2">{{ $personnels->reason_vacancy }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kanan (4/12) - Approval -->
                </div>
            </div>    
            <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-2 gap-6">    
                    <div x-data="{ isOpen: false }" class="pb-3">
                        <div class="overflow-hidden">
                            <div class="grid grid-cols-12 gap-6">    
                                <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                                    <header class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700">
                                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Job Responsibilities</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-600 dark:text-white focus:outline-none">
                                            <span x-show="!isOpen">See Details</span>
                                            <span x-show="isOpen">Close</span>
                                        </button>
                                    </header>
                                    <div class="p-6">
                                        <div x-show="isOpen" class="overflow-x-auto transition-all duration-300 ease-in-out">
                                            <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    <tr>
                                                        <th class="p-3 border text-center w-16">No</th>
                                                        <th class="p-3 border text-left">Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                                                    @foreach ($jobres as $jr)
                                                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                                                        <td class="p-3 border text-center">{{ $jr->no_job_responsiblities }}</td>
                                                        <td class="p-3 border">{{ $jr->job_responsibilities_descr }}</td>
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
                    <div x-data="{ isOpen: false }" class="pb-3">
                        <div class="overflow-hidden">
                            <div class="grid grid-cols-6 gap-6">    
                                <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                                    <header class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700">
                                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Job Qualification</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-600 dark:text-white focus:outline-none">
                                            <span x-show="!isOpen">See Details</span>
                                            <span x-show="isOpen">Close</span>
                                        </button>
                                    </header>
                                    <div class="p-6">
                                        <div x-show="isOpen" class="overflow-x-auto transition-all duration-300 ease-in-out">
                                            <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    <tr>
                                                        <th class="p-3 border text-center w-16">No</th>
                                                        <th class="p-3 border text-left">Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                                                    @foreach ($jobqua as $jq)
                                                    <tr class="border-b">
                                                        <td class="p-3 border text-center">{{ $jq->no_job_qualification }}</td>
                                                        <td class="p-3 border">{{ $jq->job_qualification_descr }}</td>
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
                </div>   
            </div>
            {{-- <div class="px-4 sm:px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="grid grid-cols-2 gap-6">    
                    <div x-data="{ isOpen: false }" class="pb-3">
                        <div class="overflow-hidden">
                            <div class="grid grid-cols-12 gap-6">    
                                <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                                    <header class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700">
                                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Attachment</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-600 dark:text-white focus:outline-none">
                                            <span x-show="!isOpen">See Details</span>
                                            <span x-show="isOpen">Close</span>
                                        </button>
                                    </header>
                                    <div class="p-6">
                                        <div x-show="isOpen" class="overflow-x-auto transition-all duration-300 ease-in-out">
                                            <table class="w-full border border-gray-300 dark:border-gray-700 rounded-lg">
                                                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    <tr>
                                                        <th class="p-3 border text-left">Filename</th>
                                                        <th class="p-3 border text-left">Created By</th>
                                                        <th class="p-3 border text-left">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-300 dark:divide-gray-700">
                                                    @foreach ($attachment as $at)
                                                    @php
                                                        $year = ($at->created_at)->year;
                                                        $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                                    @endphp
                                                    <tr class="border-b">
                                                        <td class="p-3 border">
                                                            <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-2">
                                                                📎 {{ $at->name }}
                                                            </a>
                                                        </td>
                                                        <td class="p-3 border">{{ $at->created_user }}</td>
                                                        <td class="p-3 border">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
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
                    <div x-data="{ isOpen: false }" class="pb-3">
                        <div class="overflow-hidden">
                            <div class="grid grid-cols-6 gap-6">    
                                <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-white-800 rounded-xl overflow-hidden">
                                    <header class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700">
                                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Comment</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-600 dark:text-white focus:outline-none">
                                            <span x-show="!isOpen">See Details</span>
                                            <span x-show="isOpen">Close</span>
                                        </button>
                                    </header>
                                    <div>
                                        <div x-show="isOpen" class="overflow-x-auto transition-all duration-300 ease-in-out">
                                            <div id="commentList" class="p-4 h-70 overflow-y-auto border-b bg-white dark:bg-gray-200/5 space-y-2">
                                                <p class="text-gray-500 italic">Loading comments...</p>
                                            </div>
                                            <div class="flex items-center border-t border-gray-200 dark:border-gray-300 p-2 bg-white dark:bg-gray-200/5 gap-2">
                                                <input id="commentInput" type="text" placeholder="Write a comment..."
                                                    class="flex-1 p-2 rounded focus:outline-none text-xs bg-gray-100 dark:bg-gray-700 dark:text-white">
                                                <button id="postCommentBtn" class="bg-indigo-500 text-white px-4 py-2.5 rounded text-m">Post</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>            
                    </div>   
                </div>   
            </div>     --}}
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
    </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function () {
<<<<<<< HEAD
        let docid = "{{ $personnel->docid }}"; // Ambil personnel ID dari PHP ke JavaScript
        loadComments(docid);

        // **Fungsi untuk Memuat Komentar**
        function loadComments(docid) {
            console.log("Loading comments for Doc ID:", docid);
=======
        let taskId = "{{ $personnels->docid }}"; // Ambil task ID dari PHP ke JavaScript
        loadComments(taskId);

        // **Fungsi untuk Memuat Komentar**
        function loadComments(taskId) {
            console.log("Loading comments for Task ID:", taskId);
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
            let commentList = $('#commentList');
            commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

            $.ajax({
<<<<<<< HEAD
                url: `/personnel/${docid}/comments`,
=======
                url: `/personnels/${taskId}/comments`,
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
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
                                    <p class="text-xs font-semibold">${comment.username} 
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
<<<<<<< HEAD
                url: `/personnel/${docid}/comments`,
                type: 'POST',
                data: {
                    docid: docid,
=======
                url: `/personnels/${taskId}/comments`,
                type: 'POST',
                data: {
                    task_id: taskId,
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
                    comment: input,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    console.log('Comment added successfully:', response);

                    if (response.status === "success") {
<<<<<<< HEAD
                        loadComments(docid); // **Reload komentar setelah menambahkan**
=======
                        loadComments(taskId); // **Reload komentar setelah menambahkan**
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
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
<<<<<<< HEAD
{{-- <script>
    $(document).on("click", "#approveBtn", function () {
        let docid = "{{ $personnel->docid }}"; // Ambil Task ID dari modal
=======
<script>
    $(document).on("click", "#approveBtn", function () {
        let docid = "{{$personnel->docid }}"; // Ambil Task ID dari modal
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
        let $btn = $(this); // Ambil tombol yang diklik

        // Ubah teks tombol menjadi "Loading..." dengan spinner
        $btn.html('<svg class="animate-spin h-5 w-5 mr-1 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Processing...').prop("disabled", true);

        // Kirim AJAX Request ke Controller untuk mengupdate status personnel
        $.ajax({
<<<<<<< HEAD
            url: `/personnel/${docid}/approve`,
=======
            url: /personnel/${docid}/approve,
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                docid: docid
            },
            success: function (response) {
                if (response.success) {
                    // Ubah tampilan status di modal menjadi "Completed"
                    $("#xstatus").text("Completed")
                        .removeClass()
                        .addClass("w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded");

                    // Tampilkan alert sukses
<<<<<<< HEAD
                    alert("Prf approved successfully!");
=======
                    alert("personnels approved successfully!");
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
                    window.location.href = "/personnels";
                  
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
    
                if (xhr.status === 403) {
                    alert("You Can't Approve!"); // Popup jika user tidak berhak
                } else {
                    alert("Error: Unable to update personnel status.");
                }
            },
            complete: function () {
                // Kembalikan tombol ke kondisi semula setelah selesai
                $btn.html("Approve").prop("disabled", false);
            }
        });
    });
<<<<<<< HEAD
</script> --}}
{{-- <script>
    $(document).on("click", "#approveBtn", function () {
        let docid = "{{ $personnel->docid }}"; // Ambil personnel ID dari PHP ke JavaScript
        let $btn = $(this); // Simpan referensi tombol

        // Tampilkan Full Page Loader
        $("#loadingOverlay").removeClass("hidden");

        // Kirim AJAX Request ke Controller
        $.ajax({
            url: `/personnel/${docid}/approve`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                docid: docid
            },
            success: function (response) {
                if (response.success) {
                    alert("PRF approved successfully!");
                    window.location.href = "/personnels"; // Redirect setelah approve
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                if (xhr.status === 403) {
                    alert("You can't approve!");
                } else {
                    alert("Error: Unable to update personnel status.");
                }
            },
            complete: function () {
                // Sembunyikan Full Page Loader setelah selesai
                $("#loadingOverlay").addClass("hidden");
            }
        });
    });
</script> --}}
<script>
    $(document).on("click", "#approveBtn", function () {
        let docid = "{{ $personnel->docid }}"; // Ambil personnel ID dari PHP ke JavaScript

        // Tampilkan Full Page Loader
        $("#loadingOverlay").removeClass("hidden");
        disableButtons(true);
        // Kirim AJAX Request ke Controller
        $.ajax({
            url: `/personnel/${docid}/approve`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                docid: docid
            },
            success: function (response) {
                if (response.success) {
                    toastr.success("PRF approved successfully!", "Success");
                    setTimeout(() => { window.location.href = "/personnels"; }, 2000);
                } else {
                    toastr.warning(response.message, "Warning");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                if (xhr.status === 403) {
                    toastr.error("You can't approve!", "Access Denied");
                } else {
                    toastr.error("Error: Unable to update personnel status.", "Error");
                }
            },
            complete: function () {
                // Sembunyikan Full Page Loader setelah selesai
                $("#loadingOverlay").addClass("hidden");
                disableButtons(false);
            }
        });
    });

    
=======
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
</script>

<script>
    $(document).ready(function () {
        // Saat tombol "Reject" ditekan, tampilkan modal Reject di depan
        $(document).on("click", "#rejectBtn", function () {
            $("#rejectReason").val("");  // Reset alasan reject
            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
            // $("#personnelDetailModal").css("z-index", "50"); // Pastikan modal personnel tetap di belakang
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
                alert("Please provide a reason for rejection.");
                return;
            }

            $.ajax({
<<<<<<< HEAD
                url: `/personnel/${docid}/reject`,
=======
                url: /personnel/${docid}/reject,
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
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
<script>
    $(document).ready(function () {
        // Saat tombol "Revise" ditekan, tampilkan modal Revise di depan
        $(document).on("click", "#reviseBtn", function () {
            $("#reviseReason").val("");  // Reset alasan revise
            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
         
        });

        // Saat tombol "Cancel" ditekan, tutup modal Revise
        $(document).on("click", "#cancelReviseBtn", function () {
            $("#reviseTaskModal").addClass("hidden");
        });

        // Saat tombol "Revise" ditekan, proses perubahan status
        $(document).on("click", "#confirmReviseBtn", function () {
            let docid = "{{ $personnel->docid }}";  // Ambil ID tugas dari modal detail
            let reviseReason = $("#reviseReason").val().trim();

            if (reviseReason === "") {
                alert("Please provide a reason for revise.");
                return;
            }

            $.ajax({
<<<<<<< HEAD
                url: `/personnel/${docid}/revise`,
=======
                url: /personnel/${docid}/revise,
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    docid: docid,
                    reason: reviseReason
                },
                success: function (response) {
                    if (response.success) {
                        // alert("Task has been reviseed successfully.");

                        // Update status di modal personnel
                        $("#xstatus").text("Revised")
                            .removeClass()
                            .addClass("w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded");
                     
                        window.location.href = "/personnels";
                    } else {
                        alert("Failed to revise personnel.");
                    }
                },            
                error: function (xhr) {
                    console.error(xhr.responseText);
        
                    if (xhr.status === 403) {
                        alert("You Can't Revised!"); // Popup jika user tidak berhak
                    } else {
                        alert("Error: Unable to revise personnel status.");
                    }
                },
            });
        });
    });
</script>

<<<<<<< HEAD

<script>
    // Toastr Configuration
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "3000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    function disableButtons(disable) {
        if (disable) {
            $("#loadingOverlay").removeClass("hidden");
            $("button").prop("disabled", true); // Menonaktifkan semua tombol
        } else {
            $("#loadingOverlay").addClass("hidden");
            $("button").prop("disabled", false); // Mengaktifkan kembali semua tombol
        }
    }
</script>

=======
>>>>>>> c1b12ab62fa10e4769165b3842b9bc34ba409777
    
</x-app-layout>
