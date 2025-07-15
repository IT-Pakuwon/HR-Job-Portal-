<div class="py-1 w-full max-w-9xl mx-auto">
    <div class="grid">
        <div class="px-2 sm:px-6 lg:px-2 py-1 w-full max-w-9xl mx-auto">
            <div class="gap-1">    
                <div class="flex flex-col xl:flex-col sm:col-span-1 lg:row-span-2 xl:row-span-2 gap-2 w-full overflow-hidden">
                    <div class="flex flex-col  bg-white dark:bg-gray-800 w-full rounded-2xl shadow-sm">
                        <header class="px-6 py-4 flex justify-between items-center  rounded-t-2xl border-b bg-gray-50 border-gray-300/10 dark:border-gray-600">
                            <div class="flex justify-end max-w-5xl gap-2">
                                <h1 class="text-xl font-semibold text-gray-700 dark:text-gray-100">🆔 {{ $jobposting->docid }}</h1>                                  
                            </div>                                
                        </header>
                        <!-- Main Content -->
                        <div class="p-4">
                            <div>
                                <!-- Job Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @php
                                        $jobDetails = [
                                            ['icon' => 'building-2', 'label' => 'Company', 'value' => $jobposting->cpnyid],
                                            ['icon' => 'building', 'label' => 'Department', 'value' => $jobposting->departementid],
                                            ['icon' => 'briefcase', 'label' => 'Job Title', 'value' => $jobposting->job_title],
                                            ['icon' => 'clipboard-list', 'label' => 'Job Type', 'value' => $jobposting->job_type],
                                            ['icon' => 'user-check', 'label' => 'Immediate Superior', 'value' => $jobposting->immediate_superior],
                                            // ['icon' => 'user-check', 'label' => 'Status', 'value' => $jobposting->status],
                                        ];
                                    @endphp
                            
                                    @foreach ($jobDetails as $detail)
                                        <div class="flex items-center gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <i class="lucide lucide-{{ $detail['icon'] }} w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                            <div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $detail['label'] }}</span>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $detail['value'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                            
                                    <!-- Combined Level & State Position -->
                                    <div class="grid grid-cols-2 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center gap-2">
                                            <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                            <div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Level</span>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $jobposting->job_level }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                            <div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">State Position</span>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $jobposting->state_position }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                <!-- Job Numbers -->
                                {{-- <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">Job Numbers</h3>
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
                                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $job['value'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div> --}}
                                <!-- Reason for Vacancy -->
                                {{-- <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Reason for Vacancy</h3>
                                    <p class="text-base text-gray-900 dark:text-gray-100">{{ $jobposting->reason_vacancy }}</p>
                                </div> --}}
                            </div>

                        </div>
                    </div>
                    <div class="flex flex-row w-full gap-2">
                        <div x-data="{ isOpen: true }" class="pb-4 w-1/2">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                    <h2 class=" text-xl  font-semibold text-gray-700 dark:text-gray-100">📝 Job Responsibilities</h2>
                                        <button @click="isOpen = !isOpen" class="text-grey-500 dark:text-grey-200 focus:outline-none flex items-center">
                                                <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                </header>
                                    <div class="p-6">
                                        <ul x-show="isOpen" x-transition.opacity class="space-y-3 text-gray-700 dark:text-gray-300">
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
                        <div x-data="{ isOpen: true }" class="pb-3 w-1/2">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                    <h2 class=" text-xl font-semibold text-gray-700 dark:text-gray-100"> 🎯 Job Qualification</h2>
                                    <button @click="isOpen = !isOpen" class="text-grey-500 dark:text-grey-200 focus:outline-none flex items-center">
                                        <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                    </button>
                                </header>
                                <div class="pl-6 pr-6 pt-2 pb-2">
                                    <ul x-show="isOpen" x-transition.opacity class="space-y-2 text-gray-700 dark:text-gray-300">
                                        <div class="flex flex-row gap-2 mb-2 mt-1">
                                            <li class="flex w-1/2 items-center space-x-2 bg-gray-100 dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm">
                                                <span class="text-indigo-500 dark:text-indigo-400 text-lg">🎓</span>
                                                <span class="font-medium">Education: {{ $jobposting->education }}</span>
                                            </li>
                                            <li class="flex w-1/2 items-center space-x-2 bg-gray-100 dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm">
                                                <span class="text-indigo-500 dark:text-indigo-400 text-lg">💼</span>
                                                <span class="font-medium">Experience: Lebih dari {{ $jobposting->experience_start }} tahun {{ $jobposting->experience_end }} Year</span>
                                            </li>
                                        </div>
                                        @foreach ($jobqua as $jq)
                                        <li class="flex items-start space-x-3 pt-2">
                                            <span class="text-indigo-500 dark:text-indigo-400">•</span>
                                            <span class="font-medium">{{ $jq->job_qualification_descr }}</span>
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
