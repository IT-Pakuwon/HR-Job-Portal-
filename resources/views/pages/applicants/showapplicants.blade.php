<x-app-layout> 
    <div class="py-1 w-full max-w-9xl mx-auto">
        <div class="grid">
            <div class="px-2 sm:px-6 lg:px-2 py-1 w-full max-w-9xl mx-auto">
                <div class="gap">    
                    <div class="flex flex-col xl:flex-row sm:col-span-1 lg:row-span-2 xl:row-span-2 gap-2 w-full overflow-hidden">
                        <div class="flex flex-col w-full">
                            {{-- Personal Information --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <div class="flex gap-10">                                            
                                            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">🆔{{ $applicant->applicant_id }}</h2>
                                            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Personal Information</h2>
                                        </div>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                                                    <div class="grid grid-cols-2 gap-6">
                                                      <div class="flex items-center justify-center p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                        {{-- <img src="#" alt="Applicant Photo" class="w-50 h-50 rounded-full object-cover">      --}}
                                                        {{-- <img src="{{ $photo }}" alt="Applicant Photo" class="w-50 h-50 rounded-full object-cover">               --}}
                                                        <img src="{{ $photo }}" alt="Applicant Photo"
                                                              onerror="this.onerror=null;this.src='{{ asset('images/sample.png') }}';"
                                                              class="w-50 h-50 rounded-full object-cover">
                                                      </div>
                                                      <div class="grid grid-row-2 gap-6">
                                                        <div class="flex items-center gap-2 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                          <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                          <div>
                                                              <span class="text-xs text-gray-500 dark:text-gray-400">Full Name</span>
                                                              <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->full_name }}</p>
                                                          </div>
                                                      </div>
                                                      <div class="flex items-center gap-2 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"">
                                                          <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                          <div>
                                                              <span class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                                                              <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->email_address }}</p>
                                                          </div>
                                                      </div>
                                                      </div>
          
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-6">
                                                        <div class="grid grid-cols-2 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Birth Place</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->birth_place }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">DOB</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->date_of_birth }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Gender</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->gender }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Blood Type</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->blood_type }}</p>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div> 
                                                    <div class="grid grid-cols-1 gap-6">
                                                        <div class="grid grid-cols-3 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Age</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->age }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Height</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->height }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Weight</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->weight }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-6">
                                                        <div class="grid grid-cols-2 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Citizenship</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->citizenship }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">KTP ID</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->ktp_id }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-2 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Marital Status</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->martial_status }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Religion</span>
                                                                    <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->religion }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                        <div class="flex items-center gap-2">
                                                            <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">Address</span>
                                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->id_address }} {{ $applicant->domicile_address }} {{ $applicant->domicile_city }}</p>
                                                            </div>
                                                        </div>
                                                    </div> 
                                                    <div class="grid grid-cols-1 gap-6">
                                                        <div class="grid grid-cols-4 gap-4 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Facebook</span>
                                                                    <p class="text-base font-medium word-break: break-all  text-gray-900 dark:text-gray-100">{{ $applicant->sosmed_facebook_account }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Instagram</span>
                                                                    <p class="text-base font-medium word-break: break-all  text-gray-900 dark:text-gray-100">{{ $applicant->sosmed_instagram_account }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Twitter</span>
                                                                    <p class="text-base font-medium word-break: break-all text-gray-900 dark:text-gray-100">{{ $applicant->sosmed_x_account }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                                <div>
                                                                    <span class="text-xs text-gray-500 dark:text-gray-400">LinkedIn</span>
                                                                    <p class="text-base font-medium word-break: break-all text-gray-900 dark:text-gray-100">{{ $applicant->sosmed_linkedin_account }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="mx-6">
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <label for="" class="font-semibold text-lg">Reference Information</label>
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm my-4">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Name</th>
                                                        <th class="px-4 py-2 border">Division</th>  
                                                        <th class="px-4 py-2 border">Contact</th>                      
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">                                                     
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $applicant->reference_name }}</td>
                                                          <td class="px-4 py-2 border">{{ $applicant->reference_division }}</td> 
                                                          <td class="px-4 py-2 border">{{ $applicant->reference_contact_number }}</td> 
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Education</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Name</th>
                                                        <th class="px-4 py-2 border">Type</th>
                                                        <th class="px-4 py-2 border">Start</th>
                                                        <th class="px-4 py-2 border">End</th>
                                                        <th class="px-4 py-2 border">Score</th>                                              
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                      @foreach ($applicant_education as $education)
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $education->education_name }}</td>
                                                          <td class="px-4 py-2 border">{{ $education->education_type }}</td>
                                                          <td class="px-4 py-2 border">{{ $education->start_year }}</td>
                                                          <td class="px-4 py-2 border">{{ $education->end_year }}</td>
                                                          <td class="px-4 py-2 border">{{ $education->education_score }}</td>
                                                          
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Work Experience</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Name</th>
                                                        <th class="px-4 py-2 border">Job Title</th>
                                                        <th class="px-4 py-2 border">Start</th>
                                                        <th class="px-4 py-2 border">End</th>
                                                        <th class="px-4 py-2 border">Superior Name</th>    
                                                        <th class="px-4 py-2 border">Reasor For Leasing</th>                                          
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                      @foreach ($applicant_working as $working)
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $working->company_name }}</td>
                                                          <td class="px-4 py-2 border">{{ $working->job_title }}</td>
                                                          <td class="px-4 py-2 border">{{ $working->start_date }}</td>
                                                          <td class="px-4 py-2 border">{{ $working->end_date }}</td>
                                                          <td class="px-4 py-2 border">{{ $working->superior_name }}</td>
                                                          <td class="px-4 py-2 border">{{ $working->reason_for_leaving }}</td>
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Skill</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Description</th>                                                                                                                                   
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                      @foreach ($applicant_skill as $skill)
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $skill->skill_descr }}</td>                                                                                                       
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Languange</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Name</th>
                                                        <th class="px-4 py-2 border">Score</th>                                                                             
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                      @foreach ($applicant_language as $language)
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $language->language_descr }}</td>
                                                          <td class="px-4 py-2 border">{{ $language->language_score }}</td>                                                
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Certificate</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Name</th>
                                                        <th class="px-4 py-2 border">Type</th>    
                                                        <th class="px-4 py-2 border">Start</th>
                                                        <th class="px-4 py-2 border">End</th>                                                                         
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                      @foreach ($applicant_course as $course)
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $course->course_name }}</td>
                                                          <td class="px-4 py-2 border">{{ $course->course_type }}</td>
                                                          <td class="px-4 py-2 border">{{ $course->start_year }}</td>
                                                          <td class="px-4 py-2 border">{{ $course->end_year }}</td>                                                 
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                  <!-- Header -->
                                  <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Strengths & Weaknesses</h2>
                                    <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                      <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                    </button>
                                  </header>
                              
                                  <!-- Tabel -->
                                  <div class="p-6">
                                    <div x-show="isOpen" x-transition.opacity>
                                      <div class="overflow-x-auto">
                                        <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                              <tr>
                                                <th class="px-4 py-2 border">Type</th>
                                                <th class="px-4 py-2 border">Description</th>                     
                                              </tr>
                                            </thead>
                                            <tbody class="text-gray-700 dark:text-gray-300">
                                              @foreach ($applicant_sw as $sw)
                                                <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                  <td class="px-4 py-2 border">{{ $sw->sw_type }}</td>
                                                  <td class="px-4 py-2 border">{{ $sw->sw_descr }}</td> 
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

                        <div class="flex flex-col w-full">
                            {{-- Expectation --}}
                            <div x-data="{ isOpen: true }" class="pb-4">
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Detail Information</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                        <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                                                    <div class="grid grid-cols-2 gap-6">
                                                        
                                                        <div class="flex items-center gap-2 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">Last Salary</span>
                                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">Rp. {{ $applicant->existing_last_thp }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"">
                                                            <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">Expected Salary</span>
                                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">Rp. {{ $applicant->expected_thp }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-6">
                                                        <div class="flex items-center gap-2 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                            <i class="lucide lucide-bar-chart-2 w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">Expectations</span>
                                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->expectations }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2 p-3 bg-gray-200/10 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"">
                                                            <i class="lucide lucide-map-pin w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                                                            <div>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">Career Achievement</span>
                                                                <p class="text-base font-medium text-gray-900 dark:text-gray-100">{{ $applicant->applicant_achievement }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                          </div>
                                        </div>
                                    </div>
                                    <hr class="mx-6">
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <label for="" class="font-semibold text-lg">Attachment</label>
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm my-4">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Name</th>
                                                        <th class="px-4 py-2 border">File</th>                       
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">                                                     
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="px-4 py-2 border">CV</td>
                                                            <td class="px-4 py-2 border">
                                                                {{-- 📁 <a href="#" target="_blank" class="text-blue-600 hover:underline">Download</a> --}}
                                                                📁 <a href="{{ $cv }}" target="_blank" class="text-blue-600 hover:underline">Download</a>
                                                            </td> 
                                                        </tr> 
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                            <td class="px-4 py-2 border">Cover Letter</td>
                                                            <td class="px-4 py-2 border">
                                                                {{-- 📁 <a href="#" target="_blank" class="text-blue-600 hover:underline">Download</a> --}}
                                                                📁 <a href="{{ $coverletter }}" target="_blank" class="text-blue-600 hover:underline">Download</a>
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                    <!-- Header -->
                                    <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Family</h2>
                                        <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                            <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                        </button>
                                    </header>
                                    <!-- Tabel -->
                                    <div class="p-6">
                                        <div x-show="isOpen" x-transition.opacity>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                                      <tr>
                                                        <th class="px-4 py-2 border">Nama</th>
                                                        <th class="px-4 py-2 border">Hubungan</th>
                                                        <th class="px-4 py-2 border">Jenis Kelamin</th>
                                                        <th class="px-4 py-2 border">Tanggal Lahir</th>
                                                        <th class="px-4 py-2 border">Pendidikan</th>
                                                        <th class="px-4 py-2 border">Pekerjaan</th>
                                                      </tr>
                                                    </thead>
                                                    <tbody class="text-gray-700 dark:text-gray-300">
                                                      @foreach ($applicant_family as $family)
                                                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                          <td class="px-4 py-2 border">{{ $family->family_name }}</td>
                                                          <td class="px-4 py-2 border">{{ $family->family_type }}</td>
                                                          <td class="px-4 py-2 border">{{ $family->family_gender }}</td>
                                                          <td class="px-4 py-2 border">{{ $family->family_birt_of_date }}</td>
                                                          <td class="px-4 py-2 border">{{ $family->family_education }}</td>
                                                          <td class="px-4 py-2 border">{{ $family->family_profession }}</td>
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                  <!-- Header -->
                                  <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Marital Status & Children</h2>
                                    <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                      <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                    </button>
                                  </header>
                              
                                  <!-- Tabel -->
                                  <div class="p-6">
                                    <div x-show="isOpen" x-transition.opacity>
                                      <div class="overflow-x-auto">
                                        <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                          <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                            <tr>
                                              <th class="px-4 py-2 border">Nama</th>
                                              <th class="px-4 py-2 border">Hubungan</th>
                                              <th class="px-4 py-2 border">Jenis Kelamin</th>
                                              <th class="px-4 py-2 border">Tanggal Lahir</th>
                                              <th class="px-4 py-2 border">Pendidikan</th>
                                              <th class="px-4 py-2 border">Pekerjaan</th>
                                            </tr>
                                          </thead>
                                          <tbody class="text-gray-700 dark:text-gray-300">
                                            @foreach ($applicant_marital as $family)
                                              <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                <td class="px-4 py-2 border">{{ $family->core_family_name }}</td>
                                                <td class="px-4 py-2 border">{{ $family->core_family_type }}</td>
                                                <td class="px-4 py-2 border">{{ $family->core_family_gender }}</td>
                                                <td class="px-4 py-2 border">{{ $family->core_family_birt_of_date }}</td>
                                                <td class="px-4 py-2 border">{{ $family->core_family_education }}</td>
                                                <td class="px-4 py-2 border">{{ $family->core_family_profession }}</td>
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                  <!-- Header -->
                                  <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Emergency Contact</h2>
                                    <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                      <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                    </button>
                                  </header>
                              
                                  <!-- Tabel -->
                                  <div class="p-6">
                                    <div x-show="isOpen" x-transition.opacity>
                                      <div class="overflow-x-auto">
                                        <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                          <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                            <tr>
                                              <th class="px-4 py-2 border">Name</th>
                                              <th class="px-4 py-2 border">Relation</th>  
                                              <th class="px-4 py-2 border">Phone</th>                      
                                            </tr>
                                          </thead>
                                          <tbody class="text-gray-700 dark:text-gray-300">                                           
                                              <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                <td class="px-4 py-2 border">{{ $applicant->urgent_contact_name }}</td>
                                                <td class="px-4 py-2 border">{{ $applicant->urgent_contact_relation }}</td> 
                                                <td class="px-4 py-2 border">{{ $applicant->urgent_phone }}</td> 
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
                                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
                                  <!-- Header -->
                                  <header class="flex justify-between items-center px-6 py-4 border-b border-gray-300/10 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100">Relative Information</h2>
                                    <button @click="isOpen = !isOpen" class="text-gray-500 dark:text-gray-200 focus:outline-none flex items-center">
                                      <span x-text="isOpen ? 'Closed' : 'See Details'"></span>
                                    </button>
                                  </header>
                              
                                  <!-- Tabel -->
                                  <div class="p-6">
                                    <div x-show="isOpen" x-transition.opacity>
                                      <div class="overflow-x-auto">
                                        <table class="min-w-full border border-gray-300 dark:border-gray-700 text-sm">
                                          <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                            <tr>
                                              <th class="px-4 py-2 border">Name</th>
                                              <th class="px-4 py-2 border">Division</th>  
                                              <th class="px-4 py-2 border">Status</th>                      
                                            </tr>
                                          </thead>
                                          <tbody class="text-gray-700 dark:text-gray-300">                                           
                                              <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                                <td class="px-4 py-2 border">{{ $applicant->relative_work_name }}</td>
                                                <td class="px-4 py-2 border">{{ $applicant->relative_work_division }}</td> 
                                                <td class="px-4 py-2 border">{{ $applicant->relative_work_status }}</td> 
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
