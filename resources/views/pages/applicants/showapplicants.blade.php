<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-2 py-1 sm:px-6 lg:px-2">
                <div class="gap">
                    <div
                        class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-2 xl:row-span-2 xl:flex-row">
                        <div class="flex w-full flex-col">
                            {{-- Personal Information --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <div class="flex gap-10">
                                            <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">
                                                🆔{{ $applicant->applicant_id }}</h2>
                                            <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">
                                                Personal
                                                Information</h2>
                                        </div>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <div class="grid grid-cols-1 gap-6 md:grid-cols-1">
                                                    <div class="grid grid-cols-2 gap-6">
                                                        <div
                                                            class="flex items-center justify-center rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            {{-- <img src="#" alt="Applicant Photo" class="w-50 h-50 rounded-full object-cover">      --}}
                                                            {{-- <img src="{{ $photo }}" alt="Applicant Photo" class="w-50 h-50 rounded-full object-cover">               --}}
                                                            <img src="{{ $photo }}" alt="Applicant Photo"
                                                                onerror="this.onerror=null;this.src='{{ asset('images/sample.png') }}';"
                                                                class="w-50 h-50 rounded-full object-cover">
                                                        </div>
                                                        <div class="grid-row-2 grid gap-6">
                                                            <div
                                                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Full
                                                                        Name</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->full_name }}</p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800"">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Email</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->email_address }}</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="grid grid-cols-2 gap-6">
                                                        <div
                                                            class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Birth
                                                                        Place</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->birth_place }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">DOB</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->date_of_birth }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Gender</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->gender }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Blood
                                                                        Type</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->blood_type }}</p>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-6">
                                                        <div
                                                            class="grid grid-cols-3 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Age</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->age }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Height</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->height }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Weight</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->weight }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-6">
                                                        <div
                                                            class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Citizenship</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->citizenship }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">KTP
                                                                        ID</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->ktp_id }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Marital
                                                                        Status</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->martial_status }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Religion</span>
                                                                    <p
                                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->religion }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="grid grid-cols-1 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-sm text-gray-500 dark:text-gray-400">Address</span>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->id_address }}
                                                                    {{ $applicant->domicile_address }}
                                                                    {{ $applicant->domicile_city }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-6">
                                                        <div
                                                            class="grid grid-cols-4 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Facebook</span>
                                                                    <p
                                                                        class="word-break: break-all text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->sosmed_facebook_account }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Instagram</span>
                                                                    <p
                                                                        class="word-break: break-all text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->sosmed_instagram_account }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">Twitter</span>
                                                                    <p
                                                                        class="word-break: break-all text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->sosmed_x_account }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i
                                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span
                                                                        class="text-sm text-gray-500 dark:text-gray-400">LinkedIn</span>
                                                                    <p
                                                                        class="word-break: break-all text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ $applicant->sosmed_linkedin_account }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="mx-6">
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <label for="" class="text-sm font-semibold">Reference
                                                    Information</label>
                                                <table
                                                    class="my-4 min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Division</th>
                                                            <th class="border px-4 py-2">Contact</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        <tr
                                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->reference_name }}</td>
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->reference_division }}</td>
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->reference_contact_number }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            {{-- Education Information --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Education
                                        </h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Type</th>
                                                            <th class="border px-4 py-2">Start</th>
                                                            <th class="border px-4 py-2">End</th>
                                                            <th class="border px-4 py-2">Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_education as $education)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $education->education_name }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $education->education_type }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $education->start_year }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $education->end_year }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $education->education_score }}</td>

                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Work Experienced --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Work
                                            Experience</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Job Title</th>
                                                            <th class="border px-4 py-2">Start</th>
                                                            <th class="border px-4 py-2">End</th>
                                                            <th class="border px-4 py-2">Superior Name</th>
                                                            <th class="border px-4 py-2">Reason For Leaving </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_working as $working)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $working->company_name }}</td>
                                                                <td class="border px-4 py-2">{{ $working->job_title }}
                                                                </td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $working->start_date }}</td>
                                                                <td class="border px-4 py-2">{{ $working->end_date }}
                                                                </td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $working->superior_name }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $working->reason_for_leaving }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Skill --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Skill</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_skill as $skill)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">{{ $skill->skill_descr }}
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
                            {{-- Language --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Languange
                                        </h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_language as $language)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $language->language_descr }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $language->language_score }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Certificate --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">
                                            Certificate
                                        </h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Type</th>
                                                            <th class="border px-4 py-2">Start</th>
                                                            <th class="border px-4 py-2">End</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_course as $course)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $course->course_name }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $course->course_type }}</td>
                                                                <td class="border px-4 py-2">{{ $course->start_year }}
                                                                </td>
                                                                <td class="border px-4 py-2">{{ $course->end_year }}
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
                            {{-- Strengths & Weaknesses --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Strengths
                                            &
                                            Weaknesses</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>

                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Type</th>
                                                            <th class="border px-4 py-2">Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_sw as $sw)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">{{ $sw->sw_type }}</td>
                                                                <td class="border px-4 py-2">{{ $sw->sw_descr }}</td>
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

                        <div class="flex w-full flex-col">
                            {{-- Expectation --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Detail
                                            Information</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <div class="grid grid-cols-1 gap-6 md:grid-cols-1">
                                                    <div class="grid grid-cols-2 gap-6">

                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-sm text-gray-500 dark:text-gray-400">Last
                                                                    Salary</span>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    Rp. {{ $applicant->existing_last_thp }}</p>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800"">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-sm text-gray-500 dark:text-gray-400">Expected
                                                                    Salary</span>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    Rp. {{ $applicant->expected_thp }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-6">
                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-sm text-gray-500 dark:text-gray-400">Expectations</span>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->expectations }}</p>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800"">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-sm text-gray-500 dark:text-gray-400">Career
                                                                    Achievement</span>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->applicant_achievement }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="mx-6">
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <label for="" class="text-sm font-semibold">Attachment</label>
                                                <table
                                                    class="my-4 min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">File</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        <tr
                                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="border px-4 py-2">CV</td>
                                                            <td class="border px-4 py-2">
                                                                {{-- 📁 <a href="#" target="_blank" class="text-blue-600 hover:underline">Download</a> --}}
                                                                📁 <a href="{{ $cv }}" target="_blank"
                                                                    class="text-blue-600 hover:underline">Download</a>
                                                            </td>
                                                        </tr>
                                                        <tr
                                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="border px-4 py-2">Cover Letter</td>
                                                            <td class="border px-4 py-2">
                                                                {{-- 📁 <a href="#" target="_blank" class="text-blue-600 hover:underline">Download</a> --}}
                                                                📁 <a href="{{ $coverletter }}" target="_blank"
                                                                    class="text-blue-600 hover:underline">Download</a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Family Information --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Family
                                        </h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Nama</th>
                                                            <th class="border px-4 py-2">Hubungan</th>
                                                            <th class="border px-4 py-2">Jenis Kelamin</th>
                                                            <th class="border px-4 py-2">Tanggal Lahir</th>
                                                            <th class="border px-4 py-2">Pendidikan</th>
                                                            <th class="border px-4 py-2">Pekerjaan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_family as $family)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->family_name }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->family_type }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->family_gender }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->family_birt_of_date }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->family_education }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->family_profession }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Marital Status --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Marital
                                            Status & Children</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>

                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Nama</th>
                                                            <th class="border px-4 py-2">Hubungan</th>
                                                            <th class="border px-4 py-2">Jenis Kelamin</th>
                                                            <th class="border px-4 py-2">Tanggal Lahir</th>
                                                            <th class="border px-4 py-2">Pendidikan</th>
                                                            <th class="border px-4 py-2">Pekerjaan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        @foreach ($applicant_marital as $family)
                                                            <tr
                                                                class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->core_family_name }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->core_family_type }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->core_family_gender }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->core_family_birt_of_date }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->core_family_education }}</td>
                                                                <td class="border px-4 py-2">
                                                                    {{ $family->core_family_profession }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Emergency Contact --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Emergency
                                            Contact</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>

                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Relation</th>
                                                            <th class="border px-4 py-2">Phone</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        <tr
                                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->urgent_contact_name }}</td>
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->urgent_contact_relation }}</td>
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->urgent_phone }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Relative Information --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="overflow-hidden rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                    <!-- Header -->
                                    <header
                                        class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-2 dark:border-gray-700 dark:bg-gray-700">
                                        <h2 class="text-base font-semibold text-gray-700 dark:text-gray-100">Relative
                                            Information</h2>
                                        <button @click="isOpen = !isOpen"
                                            class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>

                                    <!-- Tabel -->
                                    <div class="p-4">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table
                                                    class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                    <thead
                                                        class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                        <tr>
                                                            <th class="border px-4 py-2">Name</th>
                                                            <th class="border px-4 py-2">Division</th>
                                                            <th class="border px-4 py-2">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                        <tr
                                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->relative_work_name }}</td>
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->relative_work_division }}</td>
                                                            <td class="border px-4 py-2">
                                                                {{ $applicant->relative_work_status }}</td>
                                                        </tr>
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
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <script>
                lucide.createIcons();
            </script>


</x-app-layout>
