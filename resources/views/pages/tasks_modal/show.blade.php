<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="grid">
            <div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    {{-- <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Show Task</h2> --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        
                        <!-- Task Details -->
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="px-6 py-4 border-b">
                                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Task</h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Kolom Kiri -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-white font-bold">Doc No:</label>
                                        <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="taskid" value="{{ $projecttask->taskid }}" readonly>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-white font-bold">Status:</label>                                        
                                        @php
                                            $statusText = [
                                                'H' => 'Hold',
                                                'P' => 'On Progress',
                                                'C' => 'Completed',
                                                'X' => 'Cancel'
                                            ];
                                        @endphp
                                        <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="status" value="{{ $statusText[$projecttask->status] ?? 'Unknown' }}" readonly>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-white font-bold">Start Date:</label>
                                        <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="startdate" value="{{ $projecttask->startdate }}" readonly>
                                    </div>
                                </div>
                        
                                <!-- Kolom Kanan -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-white font-bold">Entry Date:</label>
                                        <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="taskdate" value="{{ $projecttask->taskdate }}" readonly>
                                    </div>                                   
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-white font-bold">Priority:</label>
                                        <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="taskpriority" value="{{ $projecttask->taskpriority }}" readonly>   
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 dark:text-white font-bold">Due Date:</label>
                                        <input type="text" class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="duedate" value="{{ $projecttask->duedate }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-white font-bold">Title:</label>
                                <textarea class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="summary" readonly>{{ $projecttask->summary }}</textarea>                               

                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-white font-bold">Details:</label>
                                <textarea class="w-full px-3 py-2 border rounded-lg bg-gray-200 dark:bg-gray-600" id="description" readonly>{{ $projecttask->description }}</textarea>
                            </div>
                        </div>
                        
            
                        <!-- Task Actions -->
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                                <div class="px-6 py-4 border-b">
                                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Approval</h2>
                                </div>
                        
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white dark:bg-gray-900">
                                        <thead class="bg-gray-100 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-white">Level</th>
                                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-white">User</th>
                                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-600 dark:text-white">Date</th>
                                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-600 dark:text-white">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($t_approval as $p)
                                                <tr class="border-b dark:border-gray-700">
                                                    <td class="px-6 py-4 text-gray-800 dark:text-white">{{ $p->aprvid }}</td>
                                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-300">{{ $p->name }}</td>
                                                    <td class="px-6 py-4 text-right">{{ $p->aprvdateafter }}</td>
                                                    <td class="px-6 py-4 text-center">
                                                        <span class="px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-200 rounded-lg">
                                                            In Progress
                                                        </span>
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
        </div>
    </div>
</x-app-layout>
