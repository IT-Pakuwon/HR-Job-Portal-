<x-app-layout>
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="gap-1">
            <div
                class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-row">
                <div class="flex flex-col gap-6 sm:w-1/2 md:w-full"> {{-- Increased gap for more breathing room --}}

                    <div class="rounded-xl bg-white duration-300 dark:bg-gray-800">
                        {{-- Sharper shadow, rounded-xl, and hover effect --}}
                        <header
                            class="flex items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            {{-- Rounded-t-xl, stronger border, and darker background for header --}}
                            <h1 class="text-sm flex items-center gap-2 font-bold text-gray-800 dark:text-gray-100">
                                {{-- Larger, bolder title --}}
                                    <span
                                    class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-700">
                                    ID
                                </span> {{-- Iconic color for the ID icon --}}
                                {{ $personnel->docid }}
                            </h1>
                            @php
                                // Define the status text
                                $statusText = match ($personnel->status) {
                                    'D' => 'Revise',
                                    'P' => 'On Progress',
                                    'C' => 'Completed',
                                    'X' => 'Cancelled',
                                    'R' => 'Rejected',
                                    default => 'Unknown',
                                };

                                // Define the status badge classes based on the status
                                $statusClasses = '';
                                if ($personnel->status === 'D') {
                                    $statusClasses = 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300';
                                } elseif ($personnel->status === 'P') {
                                    $statusClasses =
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300';
                                } elseif ($personnel->status === 'C') {
                                    $statusClasses =
                                        'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300';
                                } elseif (in_array($personnel->status, ['X', 'R'])) {
                                    $statusClasses = 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300';
                                } else {
                                    $statusClasses = 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300';
                                }
                            @endphp
                            <span
                                class="{{ $statusClasses }} inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold transition-colors duration-200">
                                {{ $statusText }}
                            </span>
                        </header>

                        <div class="space-y-4 p-4"> {{-- Increased padding and consistent vertical spacing --}}

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3"> {{-- Increased gap --}}
                                @php
                                    $jobDetails = [
                                        [
                                            'label' => 'Company',
                                            'value' => $personnel->cpnyid,
                                        ],
                                        [
                                            'label' => 'Department',
                                            'value' => $personnel->departementid,
                                        ],
                                        [
                                            'label' => 'Job Title',
                                            'value' => $personnel->job_title,
                                        ],
                                    ];
                                @endphp
                                @foreach ($jobDetails as $detail)
                                    <div
                                        class="flex flex-row items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4  duration-200 hover:  dark:border-gray-700 dark:bg-gray-800">
                                        {{-- Rounded-lg, subtle background, shadow, and hover effect --}}
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                {{ $detail['label'] }}</p> {{-- Label above value, smaller text --}}
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $detail['value'] }}</p> {{-- Bolder value --}}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2"> {{-- Adjusted to 2 columns on medium screens --}}
                                <div class="flex flex-col gap-6"> {{-- Consistent gap for items within this column --}}
                                    @php
                                        $jobDetail = [
                                            [
                                                'icon' => 'clipboard-list',
                                                'label' => 'Job Type',
                                                'value' => $personnel->job_type,
                                            ],
                                            [
                                                'icon' => 'user-check',
                                                'label' => 'Immediate Superior',
                                                'value' => $personnel->immediate_superior,
                                            ],
                                        ];
                                    @endphp
                                    @foreach ($jobDetail as $details)
                                        <div
                                            class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4  duration-200 hover:  dark:border-gray-700 dark:bg-gray-800">
                                            <i
                                                class="lucide lucide-{{ $details['icon'] }} h-7 w-7 flex-shrink-0 text-indigo-500 dark:text-indigo-400"></i>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    {{ $details['label'] }}</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $details['value'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex flex-col gap-6"> {{-- Consistent gap for items within this column --}}
                                    @php
                                        $jobDetail2 = [
                                            [
                                                'icon' => 'gauge',
                                                'label' => 'Job Level',
                                                'value' => $personnel->job_level,
                                            ],
                                            [
                                                'icon' => 'map-pin',
                                                'label' => 'State Position',
                                                'value' => $personnel->state_position,
                                            ],
                                        ];
                                    @endphp
                                    @foreach ($jobDetail2 as $details)
                                        <div
                                            class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4  duration-200 hover:  dark:border-gray-700 dark:bg-gray-800">
                                            <i
                                                class="lucide lucide-{{ $details['icon'] }} h-7 w-7 flex-shrink-0 text-indigo-500 dark:text-indigo-400"></i>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    {{ $details['label'] }}</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $details['value'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                                {{-- Rounded-xl, stronger shadow --}}
                                <h3
                                    class="mb-4 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                    {{-- Larger, bolder title --}}
                                    <span class="text-emerald-500">📊</span> Job Numbers
                                </h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3"> {{-- Consistent gap --}}
                                    @php
                                        $jobNumbers = [
                                            ['label' => 'Total Required', 'value' => $personnel->required],
                                            ['label' => 'Actual', 'value' => $personnel->actual],
                                            ['label' => 'Actual Number', 'value' => $personnel->total_actual],
                                        ];
                                    @endphp

                                    @foreach ($jobNumbers as $job)
                                        <div
                                            class="flex flex-row items-center justify-between space-y-1 rounded-lg border border-gray-200 bg-white p-2 transition-all duration-200 hover:  dark:border-gray-700 dark:bg-gray-900">
                                            {{-- Flex-col for stacking, space-y, larger padding, rounded-lg, darker bg in dark mode, hover effect --}}
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                {{ $job['label'] }}</span>
                                            <span
                                                class="text-base font-extrabold text-indigo-600 dark:text-indigo-400">{{ $job['value'] }}</span>
                                            {{-- Much larger and bolder value --}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                                {{-- Rounded-xl, stronger shadow --}}
                                <h3
                                    class="mb-3 flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                    {{-- Larger, bolder title --}}
                                    <span class="text-pink-500">🤔</span> Reason for Vacancy
                                </h3>
                                <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                    {{ $personnel->reason_vacancy }}</p> {{-- Adjusted text color and line height --}}
                            </div>

                            <div
                                class="rounded-xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                                {{-- Rounded-xl, stronger shadow --}}
                                <div class="flex flex-col md:flex-row md:items-center md:gap-4">
                                    <h3
                                        class="mb-3 flex flex-shrink-0 items-center gap-2 text-base font-bold text-gray-800 md:mb-0 dark:text-gray-100">
                                        {{-- Larger, bolder title --}}
                                        <span class="text-lg text-purple-500">🏷️</span>
                                        Tags
                                    </h3>
                                    <div x-data="{ isOpen: true }" class="mt-2 flex max-w-full flex-wrap gap-3 md:mt-0">
                                        {{-- Added margin-top for mobile, consistent gap --}}
                                        @foreach ($jobtag as $jt)
                                            <span
                                                class="inline-block cursor-pointer rounded-full bg-purple-100 px-4 py-1.5 text-xs font-semibold text-purple-700 shadow-sm transition-all duration-300 hover:bg-purple-200 hover:  dark:bg-purple-800/30 dark:text-purple-300 dark:hover:bg-purple-800">
                                                {{-- Adjusted colors for tags, hover effects --}}
                                                {{ $jt->job_tags }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div x-data="{ isOpen: true }" class="rounded-xl bg-white dark:bg-gray-800">
                        {{-- Consistent card styling --}}
                        <header
                            class="flex cursor-pointer items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700"
                            @click="isOpen = !isOpen"> {{-- Clickable header for accordion --}}
                            <h2 class="flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                {{-- Larger, bolder title --}}
                                <span class="text-orange-500">📝</span> Job Responsibilities
                            </h2>
                            <button
                                class="rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-600"
                                aria-expanded="true" :aria-expanded="isOpen.toString()"> {{-- Improved button styling, accessibility --}}
                                <svg x-show="isOpen" class="h-5 w-5 transform transition-transform duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg x-show="!isOpen" class="h-5 w-5 transform transition-transform duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                </svg>
                                <span class="sr-only"
                                    x-text="isOpen ? 'Collapse Job Responsibilities' : 'Expand Job Responsibilities'"></span>
                            </button>
                        </header>
                        <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="p-6">
                            {{-- Consistent padding for content --}}
                            <ul
                                class="custom-scrollbar max-h-60 space-y-3 overflow-y-auto pr-3 text-gray-700 dark:text-gray-300">
                                {{-- Added custom-scrollbar class --}}
                                @foreach ($jobres as $jr)
                                    <li class="flex items-start gap-2"> {{-- Changed space-x-3 to gap-2 --}}
                                        <span
                                            class="flex-shrink-0 text-sm leading-none text-indigo-500 dark:text-indigo-400">•</span>
                                        {{-- Larger bullet point, aligned --}}
                                        <span
                                            class="text-sm leading-relaxed">{{ $jr->job_responsibilities_descr }}</span>
                                        {{-- Adjusted text size and line height --}}
                                    </li>
                                @endforeach
                                @if ($jobres->isEmpty())
                                    <li class="py-4 text-center italic text-gray-500 dark:text-gray-400">No job
                                        responsibilities listed.</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <div x-data="{ isOpen: true }"
                        class="rounded-xl bg-white  duration-300 hover:   dark:bg-gray-800">
                        {{-- Consistent card styling --}}
                        <header
                            class="flex cursor-pointer items-center justify-between rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700"
                            @click="isOpen = !isOpen"> {{-- Clickable header for accordion --}}
                            <h2 class="flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                                {{-- Larger, bolder title --}}
                                <span class="text-green-500">🎯</span> Job Qualification
                            </h2>
                            <button
                                class="rounded-full p-2 text-gray-500 transition-colors hover:bg-gray-100 focus:outline-none dark:text-gray-200 dark:hover:bg-gray-600"
                                aria-expanded="true" :aria-expanded="isOpen.toString()">
                                <svg x-show="isOpen" class="h-5 w-5 transform transition-transform duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                                <svg x-show="!isOpen" class="h-5 w-5 transform transition-transform duration-200"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                </svg>
                                <span class="sr-only"
                                    x-text="isOpen ? 'Collapse Job Qualifications' : 'Expand Job Qualifications'"></span>
                            </button>
                        </header>
                        <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="p-6">
                            {{-- Consistent padding for content --}}
                            <ul class="space-y-4 text-gray-700 dark:text-gray-300"> {{-- Increased space-y --}}
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2"> {{-- Changed to grid for better layout of education/experience --}}
                                    <li
                                        class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-100 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                        {{-- Smoother background, better padding, border --}}
                                        <span
                                            class="flex-shrink-0 text-base text-indigo-500 dark:text-indigo-400">🎓</span>
                                        {{-- Larger icon --}}
                                        <span class="font-semibold text-gray-800 dark:text-gray-100">Pendidikan
                                            minimum
                                            <span class="font-bold">{{ $personnel->education }}</span></span>
                                        {{-- Bolder value --}}
                                    </li>
                                    <li
                                        class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-100 px-4 py-3 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                        {{-- Smoother background, better padding, border --}}
                                        <span
                                            class="flex-shrink-0 text-base text-indigo-500 dark:text-indigo-400">💼</span>
                                        {{-- Larger icon --}}
                                        <span class="font-semibold text-gray-800 dark:text-gray-100">Pengalaman
                                            minimum
                                            <span class="font-bold">{{ $personnel->experience_start }}
                                                Tahun</span></span>
                                        {{-- Bolder value --}}
                                    </li>
                                </div>
                                @foreach ($jobqua as $jq)
                                    <li class="flex items-start gap-2 pt-2"> {{-- Adjusted gap and padding-top --}}
                                        <span
                                            class="flex-shrink-0 text-sm leading-none text-indigo-500 dark:text-indigo-400">•</span>
                                        {{-- Larger bullet point, aligned --}}
                                        <span
                                            class="text-sm font-medium leading-relaxed text-gray-700 dark:text-gray-300">{{ $jq->job_qualification_descr }}</span>
                                        {{-- Adjusted text size and line height --}}
                                    </li>
                                @endforeach
                                @if ($jobqua->isEmpty() && !$personnel->education && !$personnel->experience_start)
                                    <li class="py-4 text-center italic text-gray-500 dark:text-gray-400">No job
                                        qualifications listed.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-6 sm:w-1/2 md:w-full">
                    <div x-data="{ activeTab: 'approval' }"
                        class="rounded-xl bg-white    duration-300 hover:   dark:bg-gray-800">
                        <header
                            class="sticky top-0 z-10 flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                            <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                                <button @click="activeTab = 'approval'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'approval',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'approval'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-xs font-medium transition-colors duration-200 focus:outline-none">
                                    Approval Details
                                </button>
                                <button @click="activeTab = 'attachment'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'attachment',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'attachment'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-xs font-medium transition-colors duration-200 focus:outline-none">
                                    Attachment
                                </button>
                                <button @click="activeTab = 'comments'"
                                    :class="{
                                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'comments',
                                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'comments'
                                    }"
                                    class="flex-1 whitespace-nowrap px-4 py-2 text-center text-xs font-medium transition-colors duration-200 focus:outline-none">
                                    Comments
                                </button>
                            </nav>
                        </header>

                        <div class="flex-grow overflow-y-auto rounded-b-xl bg-white p-6 dark:bg-gray-800">

                            <div x-show="activeTab === 'approval'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr
                                            class="border-b border-gray-200 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                            <th class="p-3 text-left font-semibold">Level</th>
                                            <th class="p-3 text-left font-semibold">Name</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                            <th class="p-3 text-left font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approval as $ap)
                                            <tr
                                                class="border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="p-3 text-left text-gray-800 dark:text-gray-200">
                                                    {{ $ap->aprvid }}</td>
                                                <td class="p-3 text-left text-gray-800 dark:text-gray-200">
                                                    {{ $ap->name }}</td>
                                                <td class="p-3 text-left text-gray-700 dark:text-gray-300">
                                                    {{ \Carbon\Carbon::parse($ap->aprvdatebefore)->format('d M Y') }}
                                                </td>
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
                                                    <span
                                                        class="{{ $statusClass }} inline-block rounded-full px-3 py-1 text-xs font-semibold">{{ $statusText }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="activeTab === 'attachment'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <table class="w-full text-xs">
                                    <thead class="text-gray-600 dark:text-gray-300">
                                        <tr class="border-b border-gray-200 dark:border-gray-700">
                                            <th class="p-3 text-left font-semibold">Filename</th>
                                            <th class="p-3 text-left font-semibold">Created By</th>
                                            <th class="p-3 text-left font-semibold">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($attachment as $at)
                                            @php
                                                $year = $at->created_at->year;
                                                $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                            @endphp
                                            <tr
                                                class="border-b border-gray-100 transition-colors last:border-b-0 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                <td class="px-3 py-2">
                                                    <a href="{{ $fileUrl }}" target="_blank"
                                                        class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">📎
                                                        {{ $at->name }}</a>
                                                </td>
                                                <td class="p-3 text-gray-800 dark:text-gray-200">
                                                    {{ $at->created_user }}</td>
                                                <td class="p-3 text-gray-700 dark:text-gray-300">
                                                    {{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if ($attachment->isEmpty())
                                            <tr>
                                                <td colspan="3"
                                                    class="p-4 text-center italic text-gray-500 dark:text-gray-400">
                                                    No attachments found.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div x-show="activeTab === 'comments'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex w-full flex-col justify-center">
                                    <div id="commentList"
                                        class="custom-scrollbar flex max-h-60 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" x-model="newComment" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg border border-transparent bg-gray-100 p-3 text-gray-800 transition-all duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                        <button id="postCommentBtn"
                                            @click="if(newComment.trim()) { comments.push({ text: newComment, user: currentUser }); newComment = ''; }"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-xs font-semibold text-white   transition-all duration-200 hover:bg-indigo-700 hover:  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-70 col-span-full flex flex-col overflow-y-auto rounded-lg border-b bg-white p-6   sm:col-span-6 xl:col-span-12 dark:border-gray-200/10">
                        <div x-data="{ isOpen: true, comments: [], newComment: '', currentUser: 'User1' }" class="mt-4 flex w-full flex-col justify-center">
                            <header class="flex items-center justify-between overflow-y-auto"
                                @click="isOpen = !isOpen">
                                <h2
                                    class="flex items-center gap-2 text-base font-semibold text-gray-700 dark:text-gray-100">
                                    💬 Comments
                                </h2>
                                <button>
                                    <span x-show="isOpen">🔽 See Details</span>
                                    <span x-show="!isOpen">▶️ Closed</span>
                                </button>
                            </header>
                            <div x-show="isOpen" class="overflow-hidden transition-all duration-300">
                                <div x-data="{ comments: [], newComment: '', currentUser: 'User1' }" class="flex w-full flex-col justify-center">
                                    <div id="commentList"
                                        class="custom-scrollbar flex max-h-60 flex-col space-y-4 overflow-y-auto p-4">
                                        <p class="py-4 text-center italic text-gray-500">Loading comments...</p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                                        <input id="commentInput" x-model="newComment" type="text"
                                            placeholder="Write a comment..."
                                            class="flex-1 rounded-lg border border-transparent bg-gray-100 p-3 text-gray-800 transition-all duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400">
                                        <button id="postCommentBtn"
                                            @click="if(newComment.trim()) { comments.push({ text: newComment, user: currentUser }); newComment = ''; }"
                                            class="rounded-lg bg-indigo-600 px-5 py-3 text-xs font-semibold text-white   transition-all duration-200 hover:bg-indigo-700 hover:  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:scale-95 dark:focus:ring-offset-gray-800">
                                            Post 🚀
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center">
        <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
    </div>

    <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6   dark:bg-gray-700">
            <h2 class="mb-4 text-base font-bold text-gray-800 dark:text-white">Reject Task</h2>
            <textarea id="rejectReason"
                class="mt-2 w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Enter rejection reason..."></textarea>

            <div class="mt-6 flex justify-end gap-3">
                <button id="cancelRejectBtn"
                    class="rounded-lg bg-gray-200 px-5 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                    Cancel
                </button>
                <button id="confirmRejectBtn"
                    class="rounded-lg bg-red-600 px-5 py-2 font-medium text-white transition-colors hover:bg-red-700">
                    Reject
                </button>
            </div>
        </div>
    </div>

    <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6   dark:bg-gray-700">
            <h2 class="mb-4 text-base font-bold text-gray-800 dark:text-white">Revise Task</h2>
            <textarea id="reviseReason"
                class="mt-2 w-full rounded-lg border border-gray-300 p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                placeholder="Enter revise reason..."></textarea>

            <div class="mt-6 flex justify-end gap-3">
                <button id="cancelReviseBtn"
                    class="rounded-lg bg-gray-200 px-5 py-2 font-medium text-gray-700 transition-colors hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                    Cancel
                </button>
                <button id="confirmReviseBtn"
                    class="rounded-lg bg-blue-600 px-5 py-2 font-medium text-white transition-colors hover:bg-blue-700">
                    Revise
                </button>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>

    <script>
        $(document).ready(function() {
            let docid = "{{ $personnel->docid }}"; // Ambil task ID dari PHP ke JavaScript
            loadComments(docid);

            // **Fungsi untuk Memuat Komentar**
            function loadComments(docid) {
                console.log("Loading comments for Doc ID:", docid);
                let commentList = $('#commentList');
                commentList.html('<p class="text-gray-500 italic">Loading comments...</p>'); // Loader

                $.ajax({
                    url: `/personnel/${docid}/comments`,
                    type: 'GET',
                    success: function(response) {
                        console.log("Comments Loaded:", response);
                        commentList.empty();

                        if (response.comments.length === 0) {
                            commentList.append(
                                '<p class="text-gray-500 text-sm italic">No comments yet. Be the first to comment!</p>'
                            );
                        } else {
                            response.comments.forEach(comment => {
                                let timeAgo = moment(comment.created_at)
                                    .fromNow(); // Format waktu seperti "4 days ago"

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
                    url: `/personnel/${docid}/comments`,
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
                success: function(response) {
                    if (response.success) {
                        // Update status di UI
                        $("#xstatus").text("Approved")
                            .removeClass()
                            .addClass(
                                "w-full max-w-32 bg-green-300/30 dark:bg-green-300 text-green-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                            );

                        // Tampilkan alert sukses
                        toastr.success("Personnel approved successfully!");
                        window.location.href = "/personnels";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);

                    if (xhr.status === 403) {
                        toastr.error("You are not authorized to approve this personnel.");
                    } else {
                        toastr.error("Error: Unable to approve personnel.");
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
                let docid = "{{ $personnel->docid }}";
                checkApproval(docid, "reject");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Reject
            $(document).on("click", "#cancelRejectBtn", function() {
                $("#rejectTaskModal").addClass("hidden");
            });

            // Saat tombol "Reject" ditekan, proses perubahan status
            $(document).on("click", "#confirmRejectBtn", function() {
                let docid = "{{ $personnel->docid }}"; // Ambil ID tugas dari modal detail
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
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been rejected successfully.");

                            // Update status di modal personnel
                            $("#xstatus").text("Rejected")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();

                            window.location.href = "/personnels";
                        } else {
                            alert("Failed to reject personnel.");
                        }
                    },
                    error: function(xhr) {
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
        $(document).ready(function() {
            // Saat tombol "Revise" ditekan, tampilkan modal Revise di depan
            $(document).on("click", "#reviseBtn", function() {
                $("#reviseReason").val(""); // Reset alasan revise
                // $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                let docid = "{{ $personnel->docid }}";
                checkApproval(docid, "revise");

            });

            // Saat tombol "Cancel" ditekan, tutup modal Revise
            $(document).on("click", "#cancelReviseBtn", function() {
                $("#reviseTaskModal").addClass("hidden");
            });

            // Saat tombol "Revise" ditekan, proses perubahan status
            $(document).on("click", "#confirmReviseBtn", function() {
                let docid = "{{ $personnel->docid }}"; // Ambil ID tugas dari modal detail
                let reviseReason = $("#reviseReason").val().trim();

                if (reviseReason === "") {
                    toastr.error("Please provide a reason for revise.");
                    return;
                }
                let $spinner = $("#loadingSpinnerContainer"); // Ambil elemen spinner        
                // Tampilkan spinner di kanan bawah
                $spinner.fadeIn();

                $.ajax({
                    url: `/personnel/${docid}/revise`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        docid: docid,
                        reason: reviseReason
                    },
                    success: function(response) {
                        if (response.success) {
                            // alert("Task has been reviseed successfully.");

                            // Update status di modal personnel
                            $("#xstatus").text("Revised")
                                .removeClass()
                                .addClass(
                                    "w-full max-w-32 bg-red-300/30 dark:bg-red-300 text-red-600 flex justify-items-center focus:outline-none pointer-events-none border-none font-semibold px-2 py-0.5 rounded"
                                );
                            $spinner.fadeOut();
                            window.location.href = "/personnels";
                        } else {
                            alert("Failed to revise personnel.");
                        }
                    },
                    error: function(xhr) {
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

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function checkApproval(docid, action) {
            console.log(docid, '-', action);
            $.ajax({
                url: `/personnel/${docid}/check-approval/${action}`,
                type: "GET",
                success: function(response) {
                    if (response.canPerformAction) {
                        // Jika user bisa melakukan aksi, tampilkan modal atau langsung proses approval
                        if (action === "reject") {
                            $("#rejectReason").val(""); // Reset alasan reject
                            $("#rejectTaskModal").removeClass("hidden").css("z-index", "60");
                        } else if (action === "revise") {
                            $("#reviseReason").val(""); // Reset alasan revise
                            $("#reviseTaskModal").removeClass("hidden").css("z-index", "60");
                            // } else if (action === "approve") {
                            //     approvePersonnel(docid); // Jika approve, langsung jalankan proses approval
                        }
                    } else {
                        // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                        toastr.error("You are not authorized to " + action + " this personnel.");
                    }
                },
                error: function() {
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
            display: none;
            /* Tersembunyi saat tidak digunakan */
        }

        #loadingSpinnerContainer svg {
            width: 30px;
            height: 30px;
            color: white;
        }
    </style>

    {{-- <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/lucide.min.js"></script> --}}



</x-app-layout>
