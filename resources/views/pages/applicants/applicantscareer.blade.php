        <div class="max-w-9xl mx-auto w-full px-2 py-1 sm:px-6 lg:px-2">
            <div class="gap">
                <div
                    class="flex w-full flex-col gap-4 overflow-hidden sm:col-span-1 lg:row-span-2 xl:row-span-2 xl:flex-row">
                    <div class="flex w-full flex-col">
                        {{-- Personal Information --}}
                        <div x-data="{ isOpen: true }" class="pb-4">
                            <div class="overflow-hidden rounded-2xl bg-white dark:bg-gray-800">
                                <header
                                    class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                                    {{-- <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">
                                               🆔{{ $applicant->applicant_id }}</h2> --}}
                                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">
                                        Personal
                                        Information</h2>
                                    <form id="applicantprofile" class="flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="applicant_id"
                                            value="{{ $applicant->applicant_id ?? '' }}">
                                        <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                                        <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                                        <input type="hidden" name="departementid"
                                            value="{{ $career->departementid ?? '' }}">
                                        <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 rounded-md bg-gray-800 px-4 py-2 text-white transition hover:bg-gray-700">
                                            Preview
                                        </button>
                                    </form>
                                </header>
                                <div class="p-4">
                                    <div x-show="isOpen" x-transition.opacity>
                                        <div class="overflow-x-auto">
                                            <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div
                                                        class="flex items-center justify-center rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                        {{-- <img src="#" alt="Applicant Photo" class="w-50 h-50 rounded-full object-cover">      --}}
                                                        <img src="{{ $photo }}" alt="Applicant Photo"
                                                            onerror="this.onerror=null;this.src='{{ asset('images/sample.png') }}';"
                                                            class="w-50 h-50 rounded-full object-cover">
                                                    </div>
                                                    <div class="grid-row-2 grid gap-4">
                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Full
                                                                    Name</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->full_name }}</p>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800"">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->email_address }}</p>
                                                            </div>
                                                        </div>
                                                        <!-- Phone Number -->
                                                        <div
                                                            class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <i
                                                                class="lucide lucide-phone h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Phone
                                                                    Number</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->phone_number }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <!-- Mobile Number -->
                                                        <div
                                                            class="mt-2 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                            <i
                                                                class="lucide lucide-smartphone h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Mobile
                                                                    Number</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->mobile_phone }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div
                                                        class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Birth
                                                                    Place</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->birth_place }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">DOB</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ \Carbon\Carbon::parse($applicant->date_of_birth)->translatedFormat('d F Y') }}</p>
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
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Gender</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->gender }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Blood
                                                                    Type</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->blood_type }}</p>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-1 gap-4">
                                                    <div
                                                        class="grid grid-cols-3 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Age</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->age }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Height</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->height }} cm</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Weight</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->weight }} kg</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div
                                                        class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Citizenship</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->citizenship }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">KTP
                                                                    ID</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
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
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Marital
                                                                    Status</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->martial_status }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Religion</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->religion }}</p>
                                                            </div>
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
                                                                class="text-xs text-gray-500 dark:text-gray-400">Domicile
                                                                Address</span>
                                                            <p class="text-xs italic text-gray-400">Listed on
                                                                official ID (KTP).</p>
                                                            <p
                                                                class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $applicant->id_address }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <i
                                                            class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                        <div>
                                                            <span
                                                                class="text-xs text-gray-500 dark:text-gray-400">Residential
                                                                Address</span>
                                                            <p class="text-xs italic text-gray-400">Current
                                                                residential address.</p>
                                                            <p
                                                                class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $applicant->domicile_address }}
                                                                {{ $applicant->domicile_city }}</p>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="grid grid-cols-1 gap-4">
                                                    <div
                                                        class="grid grid-cols-2 gap-4 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Facebook</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->sosmed_facebook_account }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Instagram</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->sosmed_instagram_account }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">Twitter</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->sosmed_x_account }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <i
                                                                class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span
                                                                    class="text-xs text-gray-500 dark:text-gray-400">LinkedIn</span>
                                                                <p
                                                                    class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $applicant->sosmed_linkedin_account }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="mx-6 dark:border-gray-500">
                                <div class="p-4">
                                    <div x-show="isOpen" x-transition.opacity>
                                        <div class="overflow-x-auto">
                                            <label for="" class="text-lg font-semibold">Reference
                                                Information</label>
                                            <table
                                                class="my-4 min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                                <thead
                                                    class="bg-gray-100 text-center text-gray-700 dark:bg-gray-800 dark:text-gray-700">
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
                                                            {{ $applicant->reference_name }}
                                                        </td>
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
                    </div>

                    <div class="flex w-full flex-col">
                        <div x-data="{ activeTab: 'Education' }" class="rounded-xl bg-white dark:bg-gray-800">

                            <header
                                class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                                <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                                    <button @click="activeTab = 'Education'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Education',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Education'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Education
                                    </button>
                                    <button @click="activeTab = 'WorkExperience'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab
                                            === 'WorkExperience',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'WorkExperience'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Work Experience
                                    </button>
                                    <button @click="activeTab = 'Skill'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Skill',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Skill'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Skill & Languange
                                    </button>
                                    <button @click="activeTab = 'Certificate'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Certificate',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Certificate'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Certificate
                                    </button>
                                    <button @click="activeTab = 'sdanw'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'sdanw',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'sdanw'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Strengths & Weaknesses
                                    </button>

                                </nav>
                            </header>

                            <div class="flex-grow overflow-y-auto rounded-b-xl bg-white p-4 dark:bg-gray-800">
                                <div x-show="activeTab === 'Education'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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
                                                        {{ $education->start_year }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $education->end_year }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $education->education_score }}</td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div x-show="activeTab === 'WorkExperience'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
                                            <tr>
                                                <th class="border px-4 py-2">Name</th>
                                                <th class="border px-4 py-2">Job Title</th>
                                                <th class="border px-4 py-2">Start</th>
                                                <th class="border px-4 py-2">End</th>
                                                <th class="border px-4 py-2">Superior Name</th>
                                                <th class="border px-4 py-2">Reasor For Leasing</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-700 dark:text-gray-300">
                                            @foreach ($applicant_working as $working)
                                                <tr
                                                    class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                    <td class="border px-4 py-2">
                                                        {{ $working->company_name }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $working->job_title }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $working->start_date }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $working->end_date }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $working->superior_name }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $working->reason_for_leaving }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div x-show="activeTab === 'Skill'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <div>
                                        <div>
                                            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Skill
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
                                                                class="font-light odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                                <td class="border px-4 py-2">
                                                                    {{ $skill->skill_descr }}
                                                                </td>

                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                        </div>
                                        <hr class="py-4">
                                        <div>
                                            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">
                                                Languange
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

                                <div x-show="activeTab === 'Certificate'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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
                                                        {{ $course->course_name }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $course->course_type }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $course->start_year }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $course->end_year }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div x-show="activeTab === 'sdanw'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
                                            <tr>
                                                <th class="border px-4 py-2">Type</th>
                                                <th class="border px-4 py-2">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-700 dark:text-gray-300">
                                            @foreach ($applicant_sw as $sw)
                                                <tr
                                                    class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                    <td class="border px-4 py-2">{{ $sw->sw_type }}
                                                    </td>
                                                    <td class="border px-4 py-2">{{ $sw->sw_descr }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div x-data="{ activeTab: 'Family' }" class="rounded-xl bg-white dark:bg-gray-800">

                            <header
                                class="flex items-center rounded-t-xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                                <nav class="-mb-px flex flex-grow"> {{-- Added -mb-px to negative margin to overlap border --}}
                                    <button @click="activeTab = 'Family'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Family',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Family'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Family Information
                                    </button>
                                    <button @click="activeTab = 'MaritalStatus'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab
                                            === 'MaritalStatus',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'MaritalStatus'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Marital Status
                                    </button>
                                    <button @click="activeTab = 'Emergency'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Emergency',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Skill'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Emergency Contact
                                    </button>
                                    <button @click="activeTab = 'Relative'"
                                        :class="{
                                            'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'Relative',
                                            'border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 dark:text-gray-300 dark:hover:text-gray-100 dark:hover:border-gray-600': activeTab !== 'Relative'
                                        }"
                                        class="flex-1 whitespace-nowrap px-4 py-2 text-center text-sm font-medium transition-colors duration-200 focus:outline-none">
                                        Relative Information
                                    </button>
                                </nav>
                            </header>

                            <div class="flex-grow overflow-y-auto rounded-b-xl bg-white p-4 dark:bg-gray-800">
                                <div x-show="activeTab === 'Family'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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
                                                        {{ $family->family_name }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $family->family_type }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        {{ $family->family_gender }}
                                                    </td>
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

                                <div x-show="activeTab === 'MaritalStatus'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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

                                <div x-show="activeTab === 'Emergency'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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
                                                    {{ $applicant->urgent_phone }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div x-show="activeTab === 'Relative'"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-2">
                                    <table class="min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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
                        <div class="overflow-hidden rounded-2xl bg-white dark:bg-gray-800">
                            <header
                                class="flex items-center justify-between border-b border-gray-300/10 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700">
                                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Detail
                                    Information</h2>
                                <button @click="isOpen = !isOpen"
                                    class="flex items-center text-gray-500 focus:outline-none dark:text-gray-200">
                                    <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                </button>
                            </header>
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
                                        <div class="grid grid-cols-2 gap-4">

                                            <div
                                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                <i
                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Last
                                                        Salary</span>
                                                    <p
                                                        class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                        {{-- Rp. {{ $applicant->existing_last_thp }} --}}
                                                        Rp. {{ isset($applicant->existing_last_thp) ? number_format((int)$applicant->existing_last_thp, 0, ',', '.') : '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800"">
                                                <i
                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Expected
                                                        Salary</span>
                                                    <p
                                                        class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                        {{-- Rp. {{ $applicant->expected_thp }} --}}
                                                        Rp. {{ isset($applicant->expected_thp) ? number_format((int)$applicant->expected_thp, 0, ',', '.') : '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div
                                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800">
                                                <i
                                                    class="lucide lucide-bar-chart-2 h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400">Expectations</span>
                                                    <p
                                                        class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                        {{-- {{ $applicant->expectations }} --}}
                                                        Rp. {{ isset($applicant->expectations) ? number_format((int)$applicant->expectations, 0, ',', '.') : '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-200/10 p-3 dark:border-gray-700 dark:bg-gray-800"">
                                                <i
                                                    class="lucide lucide-map-pin h-6 w-6 text-gray-600 dark:text-gray-300"></i>
                                                <div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Career
                                                        Achievement</span>
                                                    <p
                                                        class="w-full break-all text-base font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $applicant->applicant_achievement }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mx-6 dark:border-gray-500">
                            <div class="p-4">
                                <div class="overflow-x-auto">
                                    <label for="" class="text-lg font-semibold">Attachment</label>
                                    <table class="my-4 min-w-full border border-gray-300 text-sm dark:border-gray-700">
                                        <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-700">
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
                                                    📁 @if ($cv)
                                                        <a href="{{ $cv }}" target="_blank"
                                                            class="text-blue-600 hover:underline">Download
                                                            CV</a>
                                                    @else
                                                        <span class="italic text-gray-400">No cover
                                                            letter</span>
                                                    @endif

                                                </td>
                                            </tr>
                                            {{-- <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                      <td class="px-4 py-2 border">Cover Letter</td>
                                                      <td class="px-4 py-2 border">                                                         
                                                          📁 @if ($coverletter)
                                                                  <a href="{{ $coverletter }}" target="_blank" class="text-blue-600 hover:underline">Download</a>
                                                              @else
                                                                  <span class="text-gray-400 italic">No cover letter</span>
                                                              @endif
                                                      </td> 
                                                  </tr>                                                        --}}
                                        </tbody>
                                    </table>
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

                <script>
                    $('#applicantprofile').on('submit', function(e) {
                        e.preventDefault();
                        var form = $(this);

                        $.ajax({
                            url: "{{ route('applicantprofile.pdf') }}",
                            method: 'POST',
                            data: form.serialize(),
                            xhrFields: {
                                responseType: 'blob'
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(blob, status, xhr) {
                                // Cek apakah response berupa PDF atau error JSON
                                var contentType = xhr.getResponseHeader('Content-Type');
                                if (contentType && contentType.indexOf('application/pdf') !== -1) {
                                    const url = window.URL.createObjectURL(blob);
                                    window.open(url, '_blank');
                                } else {
                                    // Jika error JSON
                                    var reader = new FileReader();
                                    reader.onload = function() {
                                        var resp = JSON.parse(reader.result);
                                        alert(resp.message || 'Gagal generate PDF');
                                    };
                                    reader.readAsText(blob);
                                }
                            },
                            error: function(xhr) {
                                alert('Gagal generate PDF. Pastikan data sudah lengkap.');
                            }
                        });
                    });
                </script>

            </div>
        </div>
