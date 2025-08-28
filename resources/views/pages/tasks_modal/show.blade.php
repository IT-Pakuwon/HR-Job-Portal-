<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid">
            <div class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl rounded-lg bg-white p-6 dark:bg-gray-800">
                    {{-- <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Show Task</h2> --}}
                    <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">

                        <!-- Task Details -->
                        <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
                            <div class="border-b px-6 py-4">
                                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Task</h2>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Kolom Kiri -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block font-bold text-gray-700 dark:text-white">Doc No:</label>
                                        <input type="text"
                                            class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600"
                                            id="taskid" value="{{ $projecttask->taskid }}" readonly>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block font-bold text-gray-700 dark:text-white">Status:</label>
                                        @php
                                            $statusText = [
                                                'H' => 'Hold',
                                                'P' => 'On Progress',
                                                'C' => 'Completed',
                                                'X' => 'Cancel',
                                            ];
                                        @endphp
                                        <input type="text"
                                            class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600"
                                            id="status" value="{{ $statusText[$projecttask->status] ?? 'Unknown' }}"
                                            readonly>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block font-bold text-gray-700 dark:text-white">Start Date:</label>
                                        <input type="text"
                                            class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600"
                                            id="startdate" value="{{ $projecttask->startdate }}" readonly>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div>
                                    <div class="mb-4">
                                        <label class="block font-bold text-gray-700 dark:text-white">Entry Date:</label>
                                        <input type="text"
                                            class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600"
                                            id="taskdate" value="{{ $projecttask->taskdate }}" readonly>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block font-bold text-gray-700 dark:text-white">Priority:</label>
                                        <input type="text"
                                            class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600"
                                            id="taskpriority" value="{{ $projecttask->taskpriority }}" readonly>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block font-bold text-gray-700 dark:text-white">Due Date:</label>
                                        <input type="text"
                                            class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600"
                                            id="duedate" value="{{ $projecttask->duedate }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block font-bold text-gray-700 dark:text-white">Title:</label>
                                <textarea class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600" id="summary" readonly>{{ $projecttask->summary }}</textarea>

                            </div>
                            <div class="mb-4">
                                <label class="block font-bold text-gray-700 dark:text-white">Details:</label>
                                <textarea class="w-full rounded-lg border bg-gray-200 px-3 py-2 dark:bg-gray-600" id="description" readonly>{{ $projecttask->description }}</textarea>
                            </div>
                        </div>


                        <!-- Task Actions -->
                        <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
                            <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
                                <div class="border-b px-6 py-4">
                                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Approval</h2>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white dark:bg-gray-900">
                                        <thead class="bg-gray-100 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-white">
                                                    Level</th>
                                                <th
                                                    class="px-6 py-3 text-left text-sm font-medium text-gray-600 dark:text-white">
                                                    User</th>
                                                <th
                                                    class="px-6 py-3 text-center text-sm font-medium text-gray-600 dark:text-white">
                                                    Date</th>
                                                <th
                                                    class="px-6 py-3 text-right text-sm font-medium text-gray-600 dark:text-white">
                                                    Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($t_approval as $p)
                                                <tr class="border-b dark:border-gray-700">
                                                    <td class="px-6 py-4 text-gray-800 dark:text-white">
                                                        {{ $p->aprvid }}</td>
                                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-300">
                                                        {{ $p->name }}</td>
                                                    <td class="px-6 py-4 text-right">{{ $p->aprvdateafter }}</td>
                                                    <td class="px-6 py-4 text-center">
                                                        <span
                                                            class="rounded-lg bg-yellow-200 px-3 py-1 text-xs font-semibold text-yellow-700">
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
