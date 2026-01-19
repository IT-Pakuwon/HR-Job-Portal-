<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-2 py-1 sm:px-6 lg:px-2">
                <div class="gap-1">
                    <div
                        class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-2 xl:row-span-2 xl:flex-col">
                        <div class="flex w-full flex-col rounded-xl bg-white shadow-sm dark:bg-gray-800">
                            <header
                                class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-600">
                                <div class="flex max-w-5xl justify-end gap-2">
                                    <h1 class="text-base font-semibold text-gray-700 dark:text-gray-100">🆔
                                        {{ $jobposting->docid }}</h1>
                                </div>
                            </header>
                            <!-- Main Content -->
                            <div class="p-4">
                                <div>
                                    <!-- Job Details -->
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                        @php
                                            $jobDetails = [
                                                [
                                                    'icon' => 'building-2',
                                                    'label' => 'Company',
                                                    'value' => $jobposting->cpnyid,
                                                ],
                                                [
                                                    'icon' => 'building',
                                                    'label' => 'Department',
                                                    'value' => $jobposting->departementid,
                                                ],
                                                [
                                                    'icon' => 'briefcase',
                                                    'label' => 'Job Title',
                                                    'value' => $jobposting->job_title,
                                                ],
                                                [
                                                    'icon' => 'clipboard-list',
                                                    'label' => 'Job Type',
                                                    'value' => $jobposting->job_type,
                                                ],
                                                [
                                                    'icon' => 'user-check',
                                                    'label' => 'Immediate Superior',
                                                    'value' => $jobposting->immediate_superior,
                                                ],
                                                // ['icon' => 'user-check', 'label' => 'Status', 'value' => $jobposting->status],
                                            ];
                                        @endphp

                                        @foreach ($jobDetails as $detail)
                                            <div
                                                class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                <i
                                                    class="lucide lucide-{{ $detail['icon'] }} h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400">{{ $detail['label'] }}</span>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $detail['value'] }}</p>
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Combined Level & State Position -->
                                        <div
                                            class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                            <div class="flex items-center gap-2">
                                                <i
                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Level</span>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $jobposting->job_level }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i
                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">State
                                                        Position</span>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $jobposting->state_position }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Job Numbers -->
                                    {{-- <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">Job Numbers</h3>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            @php
                                                $jobNumbers = [
                                                    ['label' => 'Total Required', 'value' => $jobposting->required],
                                                    ['label' => 'Actual', 'value' => $jobposting->actual],
                                                    ['label' => 'Actual Number', 'value' => $jobposting->total_actual],
                                                ];
                                            @endphp
                                
                                            @foreach ($jobNumbers as $job)
                                                <div class="flex flex-col items-center p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $job['label'] }}</span>
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $job['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div> --}}
                                    <!-- Reason for Vacancy -->
                                    {{-- <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Reason for Vacancy</h3>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $jobposting->reason_vacancy }}</p>
                                    </div> --}}
                                </div>

                            </div>
                        </div>
                        <div class="flex w-full flex-row gap-2">
                            <div x-data="{ isOpen: true }" class="w-1/2">
                                <div class="max-h-96 min-h-[12rem] rounded-xl dark:bg-gray-800">
                                    <header
                                        class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">📝 Job
                                            Responsibilities</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="text-grey-500 dark:text-grey-200 flex items-center focus:outline-none">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <div class="flex-grow overflow-y-auto rounded-b-2xl bg-white p-4 px-4">
                                        <ul x-show="isOpen" x-transition.opacity
                                            class="space-y-2 text-gray-700 dark:text-gray-300">
                                            @foreach ($jobres as $jr)
                                                <li class="flex items-start space-x-3">
                                                    <span class="text-indigo-500 dark:text-indigo-400">•</span>
                                                    <span>{{ $jr->job_responsibilities_descr }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div x-data="{ isOpen: true }" class="w-1/2">
                                <div class="max-h-96 min-h-[12rem] rounded-xl dark:bg-gray-800">
                                    <header
                                        class="flex items-center justify-between rounded-t-2xl border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100"> 🎯 Job
                                            Qualification</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="text-grey-500 dark:text-grey-200 flex items-center focus:outline-none">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <div class="flex-grow overflow-y-auto rounded-b-2xl bg-white p-4 px-4">
                                        <ul x-show="isOpen" x-transition.opacity
                                            class="space-y-2 text-gray-700 dark:text-gray-300">
                                            <div class="flex flex-row gap-2">
                                                <li
                                                    class="flex w-1/2 items-center space-x-2 rounded-lg bg-gray-100 px-4 py-2 shadow-sm dark:bg-gray-800">
                                                    <span class="text-sm text-indigo-500 dark:text-indigo-400">🎓</span>
                                                    <span class="font-medium">Education:
                                                        {{ $jobposting->education }}</span>
                                                </li>
                                                <li
                                                    class="flex w-1/2 items-center space-x-2 rounded-lg bg-gray-100 px-4 py-2 shadow-sm dark:bg-gray-800">
                                                    <span class="text-sm text-indigo-500 dark:text-indigo-400">💼</span>
                                                    <span class="font-medium">Experience: Lebih dari
                                                        {{ $jobposting->experience_start }} tahun
                                                        {{ $jobposting->experience_end }} Year</span>
                                                </li>
                                            </div>
                                            @foreach ($jobqua as $jq)
                                                <li class="flex items-start space-x-3">
                                                    <span class="text-indigo-500 dark:text-indigo-400">•</span>
                                                    <span>{{ $jq->job_qualification_descr }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <script>
                lucide.createIcons();
            </script>

</x-app-layout>
