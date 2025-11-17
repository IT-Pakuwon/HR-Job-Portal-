           <div class="max-w-9xl mx-auto w-full p-4">
               <div
                   class="flex w-full flex-col gap-4 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-row">

                   {{-- Main Job Posting Card (remains the same) --}}
                   <div class="flex flex-col gap-4 rounded-xl bg-white duration-300 sm:w-1/2 md:w-full dark:bg-gray-800">
                       <header
                           class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                           <h1 class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-gray-100">
                               <span
                                   class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">
                                   ID
                               </span> {{ $jobposting->docid }}
                           </h1>
                           {{-- Status badge can be added here if desired --}}
                       </header>

                       <div class="p-4">
                           <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                               @php
                                   $jobDetails = [
                                       ['label' => 'Company', 'value' => $jobposting->cpnyid],
                                       ['label' => 'Department', 'value' => $jobposting->departementid],
                                       ['label' => 'Job Title', 'value' => $jobposting->job_title],
                                       ['label' => 'Job Type', 'value' => $jobposting->job_type],
                                       [
                                           'label' => 'Immediate Superior',
                                           'value' => $jobposting->immediate_superior,
                                       ],
                                   ];
                               @endphp

                               @foreach ($jobDetails as $detail)
                                   <div
                                       class="hover: flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 duration-200 dark:border-gray-700 dark:bg-gray-700/50">

                                       <div>
                                           <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                               {{ $detail['label'] }}
                                           </p>
                                           <p class="text-base text-gray-900 dark:text-gray-100">
                                               {{ $detail['value'] }}</p>
                                       </div>
                                   </div>
                               @endforeach

                               <div
                                   class="hover: grid grid-cols-1 gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 duration-200 sm:grid-cols-2 dark:border-gray-700 dark:bg-gray-700/50">
                                   <div class="flex items-center gap-2">
                                       <div>
                                           <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Job Level</p>
                                           <p class="text-base text-gray-900 dark:text-gray-100">
                                               {{ $jobposting->job_level }}</p>
                                       </div>
                                   </div>
                                   <div class="flex items-center gap-2">
                                       <div>
                                           <p class="text-xs font-medium text-gray-500 dark:text-gray-400">State
                                               Position</p>
                                           <p class="text-base text-gray-900 dark:text-gray-100">
                                               {{ $jobposting->state_position }}</p>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
                   <div
                       class="flex flex-col gap-4 rounded-xl bg-white duration-300 sm:w-1/2 md:w-full dark:bg-gray-800">
                       <div x-data="{ activeTab: 'Responsibilities' }" class="rounded-xl bg-white duration-300 dark:bg-gray-800">
                           <header
                               class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                               <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                                   <button @click="activeTab = 'Responsibilities'"
                                       :class="{
                                           'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Responsibilities',
                                           'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Responsibilities'
                                       }"
                                       class="flex-1 whitespace-nowrap px-4 py-1 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                       Job Responsibilities
                                   </button>
                                   <button @click="activeTab = 'Qualification'"
                                       :class="{
                                           'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Qualification',
                                           'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Qualification'
                                       }"
                                       class="flex-1 whitespace-nowrap px-4 py-1 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                       Job Qualification
                                   </button>
                               </nav>
                           </header>

                           <div class="flex-grow overflow-y-auto rounded-b-xl bg-white p-4 dark:bg-gray-800">
                               <div x-show="activeTab === 'Responsibilities'"
                                   x-transition:enter="transition ease-out duration-300"
                                   x-transition:enter-start="opacity-0 translate-y-2"
                                   x-transition:enter-end="opacity-100 translate-y-0"
                                   x-transition:leave="transition ease-in duration-200"
                                   x-transition:leave-start="opacity-100 translate-y-0"
                                   x-transition:leave-end="opacity-0 translate-y-2"
                                   class="max-h-[300px] overflow-y-auto">
                                   <ul class="overflow-y-auto pr-3 text-gray-700 dark:text-gray-300">
                                       @foreach ($jobres as $jr)
                                           <li class="flex items-start gap-2">
                                               <span
                                                   class="flex-shrink-0 text-lg leading-none text-indigo-500 dark:text-indigo-400">•</span>
                                               <span
                                                   class="text-base leading-relaxed">{{ $jr->job_responsibilities_descr }}</span>
                                           </li>
                                       @endforeach
                                       @if ($jobres->isEmpty())
                                           <li class="py-4 text-center italic text-gray-500 dark:text-gray-400">No job
                                               responsibilities listed.</li>
                                       @endif
                                   </ul>
                               </div>

                               <div x-show="activeTab === 'Qualification'"
                                   x-transition:enter="transition ease-out duration-300"
                                   x-transition:enter-start="opacity-0 translate-y-2"
                                   x-transition:enter-end="opacity-100 translate-y-0"
                                   x-transition:leave="transition ease-in duration-200"
                                   x-transition:leave-start="opacity-100 translate-y-0"
                                   x-transition:leave-end="opacity-0 translate-y-2"
                                   class="max-h-[300px] overflow-y-auto">
                                   <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                                       <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                           <li
                                               class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-100 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                               <span
                                                   class="flex-shrink-0 text-xl text-indigo-500 dark:text-indigo-400">🎓</span>
                                               <span class="font-semibold text-gray-800 dark:text-gray-100">
                                                   Education: <span
                                                       class="font-bold">{{ $jobposting->education }}</span>
                                               </span>
                                           </li>
                                           <li
                                               class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-100 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                               <span
                                                   class="flex-shrink-0 text-xl text-indigo-500 dark:text-indigo-400">💼</span>
                                               <span class="font-semibold text-gray-800 dark:text-gray-100">
                                                   Experience: {{ $jobposting->experience_start }} -
                                                   {{ $jobposting->experience_end }}
                                                   Years
                                               </span>
                                           </li>
                                       </div>
                                       @foreach ($jobqua as $jq)
                                           <li class="flex items-start gap-2 pt-2">
                                               <span
                                                   class="flex-shrink-0 text-lg leading-none text-indigo-500 dark:text-indigo-400">•</span>
                                               <span
                                                   class="text-base font-medium leading-relaxed text-gray-700 dark:text-gray-300">{{ $jq->job_qualification_descr }}</span>
                                           </li>
                                       @endforeach
                                       @if ($jobqua->isEmpty() && !$jobposting->education && !$jobposting->experience_start)
                                           <li class="py-4 text-center italic text-gray-500 dark:text-gray-400">No job
                                               qualifications listed.</li>
                                       @endif
                                   </ul>
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
