<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
    <div class="mx-auto bg-white py-[64px] rounded shadow h-full space-y-6 px-[64px]">
        <!-- Progress Bar -->
        <div class="flex justify-between items-center text-sm font-medium">
          <div class="step-indicator text-blue-600">1. Personal Information & Contact</div>
          <div class="step-indicator text-gray-400">2. Personal Info</div>
          <div class="step-indicator text-gray-400">3. Education & Work Experience</div>
          <div class="step-indicator text-gray-400">4. Application Question</div>
          <div class="step-indicator text-gray-400">5. Review</div>
        </div>
    
        <hr class="border-t border-gray-300" />
        <form id="applicantform" class="space-y-6">
            @csrf
            <div class="step" id="step-1">
                <div class="flex flex-col gap-6">
                    <h2 class="text-xl font-semibold">Personal Information</h2>                 
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                        <div>
                        <label class="block font-medium mb-2">Full Name</label>
                        <input name="full_name" type="text" class="w-full border rounded px-4 py-2" placeholder="Nama Lengkap" required/>
                        </div>
                        <div>
                        <label class="block font-medium mb-2">Nick Name</label>
                        <input name="nick_name" type="text" class="w-full border rounded px-4 py-2" placeholder="Nama Panggilan" required />
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Gender</label>
                            <select name="gender" class="w-full border rounded px-4 py-2" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>                         
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-medium mb-2">Birth Place</label>
                            <input name="birth_place" type="text" class="w-full border rounded px-4 py-2" placeholder="Tempat Lahir" required/>
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Date of Birth</label>
                            <input name="date_of_birth" type="date" class="w-full border rounded px-4 py-2" placeholder="Tanggal Lahir" required/>
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Age</label>
                            <input name="age" type="number" class="number-only w-full border rounded px-4 py-2" placeholder="Usia" required/>
                        </div>
                    </div>       
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-medium mb-2">Height (cm)</label>
                            <input name="height" type="number" class="number-only w-full border rounded px-4 py-2" placeholder="Tinggi Badan" required/>
                        </div>
                        <div>
                        <label class="block font-medium mb-2">Weight (kg)</label>
                        <input name="weight" type="number" class="number-only w-full border rounded px-4 py-2" placeholder="Berat Badan" required/>
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Blood Type</label>
                            <select name="blood_type" class="w-full border rounded px-4 py-2" required>
                              <option value="">Select Blood Type</option>
                              <option value="A">A</option>
                              <option value="AB">AB</option>
                              <option value="B">B</option>      
                              <option value="O">O</option>                           
                              <option value="Other">Other</option>         
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium mb-2">Citizenship</label>
                            <select name="citizenship" class="w-full border rounded px-4 py-2" required>
                              <option value="">Select Citizenship</option>
                              <option value="WNI">WNI (Warga Negara Indonesia)</option>
                              <option value="WNA">WNA (Warga Negara Asing)</option>                           
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium mb-2">KTP ID</label>
                            <input name="ktp_id" type="text" class="number-only w-full border rounded px-4 py-2" placeholder="KTP ID" required />
                        </div>   
                    </div> 
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium mb-2">Marital Status</label>
                            <select name="martial_status" class="w-full border rounded px-4 py-2" required>
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Merried">Merried</option>
                            <option value="Divorced">Divorced</option>                     
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium mb-2">Religion</label>
                            <select name="religion" class="w-full border rounded px-4 py-2" required>
                            <option value="">Select Religion</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Khatolik">Khatolik</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Hindu">Hindu</option>
                            </select>
                        </div>    
                    </div> 
                    <div>
                        <label class="block font-medium mb-2">ID Address</label>
                        <input name="id_address" type="text" class="w-full border rounded px-4 py-2" placeholder="Alamat Sesuai KTP" required />
                        <input name="domicile_address" type="text" class="w-full border rounded px-4 py-2 mt-2" placeholder="Alamat Domisili" required/>
                        <input name="domicile_city" type="text" class="w-full border rounded px-4 py-2 mt-2" placeholder="Kota / Kabupaten" required/>                    
                    </div>
                    <hr class="my-4">
                    <h2 class="text-xl font-semibold">Contact Information</h2>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div>
                        <label class="block font-medium mb-2">Phone Number</label>
                        <input name="phone_number" type="text" class="number-only w-full border rounded px-4 py-2" placeholder="No Telepon" required/>
                        </div>
                        <div>
                        <label class="block font-medium mb-2">Mobile Phone</label>
                        <input name="mobile_phone" type="text" class="number-only w-full border rounded px-4 py-2" placeholder="No Handphone" required/>
                        </div>
                    </div>
                    <div>
                        <label class="block font-medium mb-2">Email Address</label>
                        <input name="email_address" type="email" class="w-full border rounded px-4 py-2" placeholder="Email" required/>
                    </div>
                </div>
            </div>
            <div class="step hidden" id="step-2">
                <div class="flex flex-col gap-6">
                    <h2 class="text-xl font-semibold">Family Background</h2>
                    <div class="h-auto flex flex-col justify-start">                                           
                        <div class="overflow-y-auto"> 
                            <table class="w-full mt-3 mb-4">
                                <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="p-3 border text-center w-12">No</th>                                                           
                                        <th class="p-3 border-t border-l">Name</th>
                                        <th class="p-3 border-t border-l">Type</th>
                                        <th class="p-3 border-t border-l">Gender</th>
                                        <th class="p-3 border-t border-l">Date of Birth</th> 
                                        <th class="p-3 border-t border-l">Education</th>   
                                        <th class="p-3 border-t border-l">Profession</th>                                                         
                                        <th class="p-3 border-t border-r text-center w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="familyTable">
                                    <tr class="family-row">
                                        <td class="p-3  border text-center">1</td> 
                                        <td class="p-3 border">
                                            <input type="text" name="family_name[]" placeholder="Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td>   
                                        <td class="p-3 border">
                                            <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="family_type[]" required>
                                                <option value="" disabled selected>Select</option>
                                                <option value="Ayah">Ayah</option>
                                                <option value="Ibu">Ibu</option>
                                                <option value="Kakak">Kakak</option>  
                                                <option value="Adik">Adik</option>
                                            </select>
                                        </td>
                                        <td class="p-3 border">
                                            <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="family_gender[]" required>
                                                <option value="" disabled selected>Select Gender</option>                                            
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>  
                                            </select>                                                             
                                        </td> 
                                        <td class="p-3 border">
                                            <input type="date" name="family_birt_of_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td>
                                        <td class="p-3 border">
                                            <select name="family_education[]" class="w-full border rounded px-4 py-2" required>
                                                <option value="" disabled selected>Select Education</option>
                                                <option value="SD">Sekolah Dasar (SD)</option>
                                                <option value="SMP">Sekolah Menengah Pertama (SMP)</option>
                                                <option value="SMA">Sekolah Menengah Atas (SMA)</option>
                                                <option value="D1">Diploma I (D1)</option>
                                                <option value="D2">Diploma II (D2)</option>
                                                <option value="D3">Diploma III (D3)</option>
                                                <option value="D3">Diploma IV (D4)</option>
                                                <option value="S1">Sarjana (S1)</option>
                                                <option value="S2">Master (S2)</option>
                                              </select>
                                        </td>
                                        <td class="p-3 border">                                                               
                                            <input type="text" name="family_profession[]" placeholder="Family Profession" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                                        </td>                                                            
                                        <td class="p-3  border-t border-r border-b text-center">
                                            <button type="button" class="removeFamily bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="addFamily"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            Add Column
                        </button>
                    </div>
                    <hr class="my-4">
                    <h2 class="text-xl font-semibold">Marital Status and Children</h2>
                    <div class="h-auto flex flex-col justify-start">                                           
                        <div class="overflow-y-auto"> 
                            <table class="w-full mt-3 mb-4">
                                <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="p-3 border text-center w-12">No</th>                                                           
                                        <th class="p-3 border-t border-l">Name</th>
                                        <th class="p-3 border-t border-l">Type</th>
                                        <th class="p-3 border-t border-l">Gender</th>
                                        <th class="p-3 border-t border-l">Date of Birth</th> 
                                        <th class="p-3 border-t border-l">Education</th>   
                                        <th class="p-3 border-t border-l">Profession</th>                                                         
                                        <th class="p-3 border-t border-r text-center w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="corefamilyTable">
                                    <tr class="corefamily-row">
                                        <td class="p-3  border text-center">1</td> 
                                        <td class="p-3 border">
                                            <input type="text" name="core_family_name[]" placeholder="Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" >                                                                
                                        </td>   
                                        <td class="p-3 border">
                                            <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="core_family_type[]" >
                                                <option value="" disabled selected>Select Type</option>
                                                <option value="Suami">Suami</option>
                                                <option value="Istri">Istri</option>
                                                <option value="Anak">Anak</option>  
                                            </select>
                                        </td>
                                        <td class="p-3 border">
                                            <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="core_family_gender[]" >
                                                <option value="" disabled selected>Select Gender</option>                                            
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>  
                                            </select>                                                             
                                        </td> 
                                        <td class="p-3 border">
                                            <input type="date" name="core_family_birt_of_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" >                                                                
                                        </td>
                                        <td class="p-3 border">
                                            <select name="core_family_education[]" class="w-full border rounded px-4 py-2" >
                                                <option value="" disabled selected>Select Education</option>
                                                <option value="SD">Sekolah Dasar (SD)</option>
                                                <option value="SMP">Sekolah Menengah Pertama (SMP)</option>
                                                <option value="SMA">Sekolah Menengah Atas (SMA)</option>
                                                <option value="D1">Diploma I (D1)</option>
                                                <option value="D2">Diploma II (D2)</option>
                                                <option value="D3">Diploma III (D3)</option>
                                                <option value="D3">Diploma IV (D4)</option>
                                                <option value="S1">Sarjana (S1)</option>
                                                <option value="S2">Master (S2)</option>
                                            </select>
                                        </td>
                                        <td class="p-3 border">                                                               
                                            <input type="text" name="core_family_profession[]" placeholder="Family Profession" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" >
                                        </td>                                                            
                                        <td class="p-3  border-t border-r border-b text-center">
                                            <button type="button" class="removeCoreFamily bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="addCoreFamily"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            Add Column
                        </button>
                    </div>    
                </div>
            </div>
             <!-- Step 3 -->
            <div class="step hidden" id="step-3">
                <div class="flex flex-col gap-6">
                    <h2 class="text-xl font-semibold">Education</h2>
                    <div class="h-auto flex flex-col justify-start">                                           
                        <div class="overflow-y-auto"> 
                            <table class="w-full mt-3 mb-4">
                                <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="p-3 border text-center w-12">No</th>                                                           
                                        <th class="p-3 border-t border-l">Name</th>
                                        <th class="p-3 border-t border-l">Background</th>
                                        <th class="p-3 border-t border-l">Start Year</th>
                                        <th class="p-3 border-t border-l">End Year</th> 
                                        <th class="p-3 border-t border-l">Score</th>                                                                                          
                                        <th class="p-3 border-t border-r text-center w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="educationTable">
                                    <tr class="education-row">
                                        <td class="p-3  border text-center">1</td> 
                                        <td class="p-3 border">
                                            <input type="text" name="education_name[]" placeholder="Education Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td>   
                                        <td class="p-3 border">
                                            <select name="education_type[]" class="w-full border rounded px-4 py-2" required>
                                                <option value="" disabled selected>Select Education</option>
                                                <option value="SD">Sekolah Dasar (SD)</option>
                                                <option value="SMP">Sekolah Menengah Pertama (SMP)</option>
                                                <option value="SMA">Sekolah Menengah Atas (SMA)</option>
                                                <option value="D1">Diploma I (D1)</option>
                                                <option value="D2">Diploma II (D2)</option>
                                                <option value="D3">Diploma III (D3)</option>
                                                <option value="D3">Diploma IV (D4)</option>
                                                <option value="S1">Sarjana (S1)</option>
                                                <option value="S2">Master (S2)</option>
                                            </select>
                                        </td>
                                        <td class="p-3 border">
                                            <input type="text" name="start_year[]" placeholder="Start Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td> 
                                        <td class="p-3 border">
                                            <input type="text" name="end_year[]" placeholder="End Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td>
                                        <td class="p-3 border">
                                            <input type="text" name="education_score[]" placeholder="Education Score" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                                        </td>                                                                                             
                                        <td class="p-3  border-t border-r border-b text-center">
                                            <button type="button" class="removeEducation bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="addEducation"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            Add Column
                        </button>
                    </div>  
                    <hr class="my-4">
                    <h2 class="text-xl font-semibold">Work Experience</h2>
                    <div class="h-auto flex flex-col justify-start">                                           
                        <div class="overflow-y-auto"> 
                            <table class="w-full mt-3 mb-4">
                                <thead class="bg-gray-100/10">
                                    <tr>
                                        <th class="p-3 border text-center w-12">No</th>                                                           
                                        <th class="p-3 border-t border-l">Company Name</th>
                                        <th class="p-3 border-t border-l">Job Title</th>
                                        <th class="p-3 border-t border-l">Start Date</th>
                                        <th class="p-3 border-t border-l">End Date</th> 
                                        <th class="p-3 border-t border-l">Superior Name</th>   
                                        <th class="p-3 border-t border-l">Reason Leaving</th>                                                         
                                        <th class="p-3 border-t border-r text-center w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="responsibilitiesTable">
                                    <tr class="responsibilities-row">
                                        <td class="p-3  border text-center">1</td> 
                                        <td class="p-3 border">
                                            <input type="text" name="company_name[]" placeholder="Company Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td>   
                                        <td class="p-3 border">
                                            <input type="text" name="job_title[]" placeholder="Job Title" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                                        </td>
                                        <td class="p-3 border">
                                            <input type="date" name="start_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td> 
                                        <td class="p-3 border">
                                            <input type="date" name="end_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                        </td>
                                        <td class="p-3 border">
                                            <input type="text" name="superior_name[]" placeholder="Superior Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                                        </td>
                                        <td class="p-3 border">                                                               
                                            <input type="text" name="reason_for_leaving[]" placeholder="Reason For Leaving" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                                        </td>                                                            
                                        <td class="p-3  border-t border-r border-b text-center">
                                            <button type="button" class="removeResponsibilities bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="addResponsibilities"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            Add Column
                        </button>
                    </div>          
                </div>
            </div>

          <!-- Step 3 -->
          <div class="step hidden" id="step-4">
            <div class="flex flex-col gap-6">
                <h2 class="text-xl font-semibold">Language</h2>
                <div class="h-auto flex flex-col justify-start">                                           
                    <div class="overflow-y-auto"> 
                        <table class="w-full mt-3 mb-4">
                            <thead class="bg-gray-100/10">
                                <tr>
                                    <th class="p-3 border text-center w-12">No</th>             
                                    <th class="p-3 border-t border-l">Description</th>       
                                    <th class="p-3 border-t border-l">Score</th>                                                                                
                                    <th class="p-3 border-t border-r text-center w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="languageTable">
                                <tr class="language-row">
                                    <td class="p-3  border text-center">1</td>                                       
                                    <td class="p-3 border">
                                        <textarea name="language_descr[]" placeholder="Language Description"  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
                                    </td>     
                                    <td class="p-3 border">
                                        <input type="text" name="language_score[]" placeholder="Language Score" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                                    </td>                                                                             
                                    <td class="p-3  border-t border-r border-b text-center">
                                        <button type="button" class="removeLanguage bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="addLanguage"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add Column
                    </button>
                </div> 
                <hr class="my-4">
                <h2 class="text-xl font-semibold">Course</h2>
                <div class="h-auto flex flex-col justify-start">                                           
                    <div class="overflow-y-auto"> 
                        <table class="w-full mt-3 mb-4">
                            <thead class="bg-gray-100/10">
                                <tr>
                                    <th class="p-3 border text-center w-12">No</th>                                                           
                                    <th class="p-3 border-t border-l">Name</th>
                                    <th class="p-3 border-t border-l">Type</th>
                                    <th class="p-3 border-t border-l">Start Year</th>
                                    <th class="p-3 border-t border-l">End Year</th>                                                                                                                              
                                    <th class="p-3 border-t border-r text-center w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="courseTable">
                                <tr class="course-row">
                                    <td class="p-3  border text-center">1</td> 
                                    <td class="p-3 border">
                                        <input type="text" name="course_name[]" placeholder="Course Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                    </td>   
                                    <td class="p-3 border">
                                        <select name="course_type[]" class="w-full border rounded px-4 py-2" required>
                                            <option value="" disabled selected>Select Course</option>
                                            <option value="Seminar">Seminar</option>
                                            <option value="Sertifikat">Sertifikat</option>                                           
                                          </select>
                                    </td>
                                    <td class="p-3 border">
                                        <input type="text" name="start_year[]" placeholder="Start Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                    </td> 
                                    <td class="p-3 border">
                                        <input type="text" name="end_year[]" placeholder="End Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                                    </td>                                                                                                                               
                                    <td class="p-3  border-t border-r border-b text-center">
                                        <button type="button" class="removeCourse bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="addCourse"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add Column
                    </button>
                </div>   
                <hr class="my-4"> 
                <h2 class="text-xl font-semibold">Strengths & Weaknesses</h2>
                <div class="h-auto flex flex-col justify-start">                                           
                    <div class="overflow-y-auto"> 
                        <table class="w-full mt-3 mb-4">
                            <thead class="bg-gray-100/10">
                                <tr>
                                    <th class="p-3 border text-center w-12">No</th>                                                           
                                    <th class="p-3 border-t border-l">Type</th>
                                    <th class="p-3 border-t border-l">Description</th>                                                                                       
                                    <th class="p-3 border-t border-r text-center w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="appswTable">
                                <tr class="appsw-row">
                                    <td class="p-3  border text-center">1</td> 
                                    <td class="p-3 border">
                                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="sw_type[]" >
                                            <option value="" disabled selected>Select Option</option>                                            
                                            <option value="Strengths">Strengths</option>
                                            <option value="Weaknesses">Weaknesses</option>  
                                        </select>   
                                    </td>   
                                    <td class="p-3 border">
                                        <textarea name="sw_descr[]" placeholder="Description"  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
                                    </td>                                                                                  
                                    <td class="p-3  border-t border-r border-b text-center">
                                        <button type="button" class="removeAppSW bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="addAppSW"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add Column
                    </button>
                </div> 
                <hr class="my-4">
                <h2 class="text-xl font-semibold">Skills</h2>
                <div class="h-auto flex flex-col justify-start">                                           
                    <div class="overflow-y-auto"> 
                        <table class="w-full mt-3 mb-4">
                            <thead class="bg-gray-100/10">
                                <tr>
                                    <th class="p-3 border text-center w-12">No</th>  
                                    <th class="p-3 border-t border-l">Description</th>                                                                                       
                                    <th class="p-3 border-t border-r text-center w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="skillTable">
                                <tr class="skill-row">
                                    <td class="p-3  border text-center">1</td> 
                                    <td class="p-3 border">
                                        <textarea name="skill_descr[]" placeholder="Description"  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
                                    </td>                                                                                  
                                    <td class="p-3  border-t border-r border-b text-center">
                                        <button type="button" class="removeSkill bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="addSkill"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add Column
                    </button>
                </div> 
            </div>
          </div>

          <div class="step hidden" id="step-5">
            <div class="flex flex-col gap-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium mb-2">Existing / Last Salary Gross / THP</label>
                        <input name="existing_last_thp" type="text" class="number-only w-full border rounded px-4 py-2" placeholder="Existing / Last Salary Gross / THP" required/>
                    </div>
                    <div>
                        <label class="block font-medium mb-2">Expected Salary Gross / THP</label>
                        <input name="expected_thp" type="text" class="number-only w-full border rounded px-4 py-2" placeholder="Expected Salary Gross / THP" required/>
                    </div>
                </div>
                <div>
                    <label class="block font-medium mb-2">Describe your expectations if accepted</label>
                        <textarea name="expectations" class="w-full border rounded px-4 py-2" rows="3" placeholder="Describe your expectations if accepted" required></textarea>
                </div>
                <div>
                    <label class="block font-medium mb-2">Urgent Contact Person</label>
                    <!-- Contact Person 1 -->
                    <div class="mb-4">                        
                        <input name="urgent_contact_name" type="text" class="w-full border rounded px-4 py-2 mt-1" placeholder="Nama Kontak Darurat" required/>
                        <input name="urgent_phone" type="text" class="w-full border rounded px-4 py-2 mt-2" placeholder="No Kontak Darurat" required/>
                    </div>    
                    <div>
                        <label class="block font-medium mb-2">Urgent Contact Relation</label>
                        <select name="urgent_contact_relation" class="w-full border rounded px-4 py-2" required>
                            <option value="">Select Contact</option>
                            <option value="Ayah">Ayah</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Saudara Kandung">Saudara Kandung</option>      
                            <option value="Teman">Teman</option>   
                            <option value="Pasangan">Pasangan</option>   
                            <option value="Other">Other</option>         
                        </select>
                    </div>               
                </div> 
                <div>
                    <label class="block font-medium mb-2">Upload Your CV (PDF max 10MB)</label>
                    <input name="upload_cv" type="file" accept="application/pdf" class="w-full border rounded px-4 py-2 file:bg-blue-100 file:border-0 file:rounded file:px-4 file:py-2" required/>
                </div>
                <div>
                    <label class="block font-medium mb-2">Upload Cover Latter (PDF max 10MB)</label>
                    <input name="upload_coverletter" type="file" accept="application/pdf" class="w-full border rounded px-4 py-2 file:bg-blue-100 file:border-0 file:rounded file:px-4 file:py-2" required/>
                </div>
                <div>
                    <label class="block font-medium mb-2">Upload Your Photo</label>
                    <input name="upload_photo" type="file" class="w-full border rounded px-4 py-2 file:bg-blue-100 file:border-0 file:rounded file:px-4 file:py-2" required/>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-medium mb-2">Facebook Account</label>
                        <input name="sosmed_facebook_account" type="text" class="w-full border rounded px-4 py-2" placeholder="Facebook Account" required />
                    </div>
                    <div>
                        <label class="block font-medium mb-2">Instagram Account</label>
                        <input name="sosmed_instagram_account" type="text" class="w-full border rounded px-4 py-2" placeholder="Instagram Account" required />
                    </div>
                    <div>
                        <label class="block font-medium mb-2">X Account</label>
                        <input name="sosmed_x_account" type="text" class="w-full border rounded px-4 py-2" placeholder="X Account" required />
                    </div>
                    <div>
                        <label class="block font-medium mb-2">LinkedIn Account</label>
                        <input name="sosmed_linkedin_account" type="text" class="w-full border rounded px-4 py-2" placeholder="LinkedIn Account" required />
                    </div>
                </div>

                <div>
                    <label class="block font-medium mb-2">Career Achievement</label>
                    <input name="career_achievement" type="text" class="w-full border rounded px-4 py-2" placeholder="Career Achievement" required />
                </div>
                <div>
                    <label class="block font-medium mb-2">Do you have any relative work in Pakuwon Group ?</label>
                    <select name="relative_work_status" class="w-full border rounded px-4 py-2" >
                      <option value="">Select Answer</option>
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-2">Relative Name & Devision</label>
                    <div class="mb-4">                        
                        <input name="relative_work_name" type="text" class="w-full border rounded px-4 py-2 mt-1" placeholder="Relative Name" />
                        <input name="relative_work_division" type="text" class="w-full border rounded px-4 py-2 mt-2" placeholder="Relative Devision" />
                    </div>                                  
                </div>  
                <div>
                    <label class="block font-medium mb-2">Reference</label>
                    <div class="mb-4">                        
                        <input name="reference_name" type="text" class="w-full border rounded px-4 py-2 mt-1" placeholder="Reference Name" />
                        <input name="reference_division" type="text" class="w-full border rounded px-4 py-2 mt-2" placeholder="Reference Devision" />
                        <input name="reference_contact_number" type="text" class="number-only w-full border rounded px-4 py-2 mt-2" placeholder="Contact Number" />
                    </div>                                  
                </div>  
                <div>
                    <label class="block font-semibold mb-2 text-lg">Have you ever applied or work at this company before ?</label>
                    <select name="apply_status" class="w-full border rounded px-4 py-2" required>
                    <option value="">Select Answer</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-2">Are you currently applying/being processed at another company?</label>
                    <select name="apply_other_on_progress" class="w-full border rounded px-4 py-2" required>
                      <option value="">Select Answer</option>
                      <option value="1">Yes, (if yes, please mention it)</option>
                      <option value="0">No</option>
                    </select>
                    <input name="apply_other_on_progress_descr" type="text" class="w-full border rounded px-4 py-2 mt-1" placeholder="Please Mention it" />
                </div>

                
                
            </div>
          </div>
    
          <!-- Navigation Buttons -->
          <div class="flex justify-between pt-4">
            <button type="button" id="prevBtn" class="bg-gray-200 px-4 py-2 rounded text-gray-700 hidden">Back</button>
            <button type="button" id="nextBtn" class="bg-blue-600 text-white px-4 py-2 rounded">Next</button>
            {{-- <button type="submit" id="submitBtn" class="bg-green-600 text-white px-4 py-2 rounded hidden">Submit</button> --}}
            <button type="submit" id="submitBtn" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-blue-700 border-blue-700 hover:bg-blue-700 hover:text-white hover:border-blue-700 bg-blue-200/10 p-2 ">
                <span id="btnText">Submit Application</span>
                <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </button> 
          </div>
        </form>
      </div>
    
      <script>
        const steps = document.querySelectorAll('.step');
        const indicators = document.querySelectorAll('.step-indicator');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');
        let currentStep = 0;
    
        function showStep(index) {
          steps.forEach((step, i) => {
            step.classList.toggle('hidden', i !== index);
            indicators[i].classList.toggle('text-blue-600', i === index);
            indicators[i].classList.toggle('text-gray-400', i !== index);
          });
    
          prevBtn.classList.toggle('hidden', index === 0);
          nextBtn.classList.toggle('hidden', index === steps.length - 1);
          submitBtn.classList.toggle('hidden', index !== steps.length - 1);
        }
    
        nextBtn.addEventListener('click', () => {
          if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
          }
        });
    
        prevBtn.addEventListener('click', () => {
          if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
          }
        });
        // Initial state
        showStep(currentStep);
      </script>


<script>
    $(document).ready(function () {
        $('#applicantform').submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            // Tampilkan Loading, Disable Button
            $('#submitBtn').attr('disabled', true); // Disable tombol
            $('#btnText').text('Processing...'); // Ubah teks tombol
            $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

            $.ajax({
                url: "{{ route('applicants.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,               
                success: function (response) {
                    $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                    $('#applicantform')[0].reset(); // Reset form setelah submit

                    // Reset Tombol ke Semula
                    $('#submitBtn').attr('disabled', false);
                    $('#btnText').text('Submit Approval');
                    $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                    toastr.success("Applicant Submit Successfully!");
                    window.location.href = "/dashboard";
                },
                error: function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        alert('Error! Please check the input.');
                    }

                    // Reset Tombol ke Semula
                    $('#submitBtn').attr('disabled', false);
                    $('#btnText').text('Submit Approval');
                    $('#loadingSpinner').addClass('hidden');
                }
            });
        });
    });


</script> 

<script>
    $(document).ready(function () {
        let responsibilityCount = 1;

        // Fungsi untuk Menambah Baris Responsibility
        $('#addResponsibilities').click(function () {
            responsibilityCount++;
            $('#responsibilitiesTable').append(`
                <tr class="responsibilities-row">
                    <td class="p-3 border text-center">${responsibilityCount}</td>                   
                    <td class="p-3 border">
                        <input type="text" name="company_name[]" placeholder="Company Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>   
                    <td class="p-3 border">
                        <input type="text" name="job_title[]" placeholder="Job Title" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td>
                    <td class="p-3 border">
                        <input type="date" name="start_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td> 
                    <td class="p-3 border">
                        <input type="date" name="end_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>
                    <td class="p-3 border">
                        <input type="text" name="superior_name[]" placeholder="Superior Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td>
                    <td class="p-3 border">                                                               
                        <input type="text" name="reason_for_leaving[]" placeholder="Reason For Leaving" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td>                   
                    <td class="p-3 border text-center">
                        <button type="button" class="removeResponsibilities  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Responsibility
        $(document).on('click', '.removeResponsibilities', function () {
            $(this).closest('.responsibilities-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            responsibilityCount = 0;
            $('#responsibilitiesTable tr').each(function () {
                responsibilityCount++;
                $(this).find('td:first').text(responsibilityCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.responsibilities-row').length > 1) {
                $('.removeResponsibilities').removeClass('hidden');
            } else {
                $('.removeResponsibilities').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let familyCount = 1;

        // Fungsi untuk Menambah Baris Family
        $('#addFamily').click(function () {
            familyCount++;
            $('#familyTable').append(`
                <tr class="family-row">
                    <td class="p-3 border text-center">${familyCount}</td>                   
                     <td class="p-3 border">
                        <input type="text" name="family_name[]" placeholder="Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>   
                    <td class="p-3 border">
                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="family_type[]" required>
                            <option value="" disabled selected>Select Type</option>
                            <option value="Ayah">Ayah</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Kakak">Kakak</option>  
                            <option value="Adik">Adik</option>
                        </select>
                    </td>
                    <td class="p-3 border">
                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="family_gender[]" required>
                            <option value="" disabled selected>Select Gender</option>                                            
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>  
                        </select>                                                             
                    </td> 
                    <td class="p-3 border">
                        <input type="date" name="family_birt_of_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>
                    <td class="p-3 border">
                        <select name="family_education[]" class="w-full border rounded px-4 py-2" required>
                            <option value="" disabled selected>Select Education</option>
                            <option value="SD">Sekolah Dasar (SD)</option>
                            <option value="SMP">Sekolah Menengah Pertama (SMP)</option>
                            <option value="SMA">Sekolah Menengah Atas (SMA)</option>
                            <option value="D1">Diploma I (D1)</option>
                            <option value="D2">Diploma II (D2)</option>
                            <option value="D3">Diploma III (D3)</option>
                            <option value="D3">Diploma IV (D4)</option>
                            <option value="S1">Sarjana (S1)</option>
                            <option value="S2">Master (S2)</option>
                            </select>
                    </td>
                    <td class="p-3 border">                                                               
                        <input type="text" name="family_profession[]" placeholder="Family Profession" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td> 
                    <td class="p-3 border text-center">
                        <button type="button" class="removeFamily  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Family
        $(document).on('click', '.removeFamily', function () {
            $(this).closest('.family-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            familyCount = 0;
            $('#familyTable tr').each(function () {
                familyCount++;
                $(this).find('td:first').text(familyCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.family-row').length > 1) {
                $('.removeFamily').removeClass('hidden');
            } else {
                $('.removeFamily').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let corefamilyCount = 1;

        // Fungsi untuk Menambah Baris CoreFamily
        $('#addCoreFamily').click(function () {
            corefamilyCount++;
            $('#corefamilyTable').append(`
                <tr class="corefamily-row">
                    <td class="p-3 border text-center">${corefamilyCount}</td>                   
                    <td class="p-3 border">
                        <input type="text" name="core_family_name[]" placeholder="Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" >                                                                
                    </td>   
                    <td class="p-3 border">
                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="core_family_type[]" >
                            <option value="" disabled selected>Select Type</option>
                            <option value="Suami">Suami</option>
                            <option value="Istri">Istri</option>
                            <option value="Anak">Anak</option>  
                        </select>
                    </td>
                    <td class="p-3 border">
                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="core_family_gender[]" >
                            <option value="" disabled selected>Select Gender</option>                                            
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>  
                        </select>                                                             
                    </td> 
                    <td class="p-3 border">
                        <input type="date" name="core_family_birt_of_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" >                                                                
                    </td>
                    <td class="p-3 border">
                        <select name="core_family_education[]" class="w-full border rounded px-4 py-2" >
                            <option value="" disabled selected>Select Education</option>
                            <option value="SD">Sekolah Dasar (SD)</option>
                            <option value="SMP">Sekolah Menengah Pertama (SMP)</option>
                            <option value="SMA">Sekolah Menengah Atas (SMA)</option>
                            <option value="D1">Diploma I (D1)</option>
                            <option value="D2">Diploma II (D2)</option>
                            <option value="D3">Diploma III (D3)</option>
                            <option value="D3">Diploma IV (D4)</option>
                            <option value="S1">Sarjana (S1)</option>
                            <option value="S2">Master (S2)</option>
                            </select>
                    </td>
                    <td class="p-3 border">                                                               
                        <input type="text" name="core_family_profession[]" placeholder="Family Profession" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" >
                    </td>  
                    <td class="p-3 border text-center">
                        <button type="button" class="removeCoreFamily  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris CoreFamily
        $(document).on('click', '.removeCoreFamily', function () {
            $(this).closest('.corefamily-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            corefamilyCount = 0;
            $('#corefamilyTable tr').each(function () {
                corefamilyCount++;
                $(this).find('td:first').text(corefamilyCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.corefamily-row').length > 1) {
                $('.removeCoreFamily').removeClass('hidden');
            } else {
                $('.removeCoreFamily').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let appswCount = 1;

        // Fungsi untuk Menambah Baris Responsibility
        $('#addAppSW').click(function () {
            appswCount++;
            $('#appswTable').append(`
                <tr class="appsw-row">
                    <td class="p-3 border text-center">${appswCount}</td>                   
                    <td class="p-3 border">
                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="sw_type[]" >
                            <option value="" disabled selected>Select Option</option>                                            
                            <option value="Strengths">Strengths</option>
                            <option value="Weaknesses">Weaknesses</option>  
                        </select>   
                    </td>   
                    <td class="p-3 border">
                        <textarea name="sw_descr[]" placeholder="Description"  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
                    </td>            
                    <td class="p-3 border text-center">
                        <button type="button" class="removeAppSW  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Responsibility
        $(document).on('click', '.removeAppSW', function () {
            $(this).closest('.appsw-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            appswCount = 0;
            $('#appswTable tr').each(function () {
                appswCount++;
                $(this).find('td:first').text(appswCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.appsw-row').length > 1) {
                $('.removeAppSW').removeClass('hidden');
            } else {
                $('.removeAppSW').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let educationCount = 1;

        // Fungsi untuk Menambah Baris Education
        $('#addEducation').click(function () {
            educationCount++;
            $('#educationTable').append(`
                <tr class="education-row">
                    <td class="p-3 border text-center">${educationCount}</td>                   
                    <td class="p-3 border">
                        <input type="text" name="education_name[]" placeholder="Education Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>   
                    <td class="p-3 border">
                        <select name="education_type[]" class="w-full border rounded px-4 py-2" required>
                            <option value="" disabled selected>Select Education</option>
                            <option value="SD">Sekolah Dasar (SD)</option>
                            <option value="SMP">Sekolah Menengah Pertama (SMP)</option>
                            <option value="SMA">Sekolah Menengah Atas (SMA)</option>
                            <option value="D1">Diploma I (D1)</option>
                            <option value="D2">Diploma II (D2)</option>
                            <option value="D3">Diploma III (D3)</option>
                            <option value="D3">Diploma IV (D4)</option>
                            <option value="S1">Sarjana (S1)</option>
                            <option value="S2">Master (S2)</option>
                            </select>
                    </td>
                    <td class="p-3 border">
                        <input type="text" name="start_year[]" placeholder="Start Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td> 
                    <td class="p-3 border">
                        <input type="text" name="end_year[]" placeholder="End Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>
                    <td class="p-3 border">
                        <input type="text" name="education_score[]" placeholder="Education Score" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td>                 
                    <td class="p-3 border text-center">
                        <button type="button" class="removeEducation  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Education
        $(document).on('click', '.removeEducation', function () {
            $(this).closest('.education-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            educationCount = 0;
            $('#educationTable tr').each(function () {
                educationCount++;
                $(this).find('td:first').text(educationCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.education-row').length > 1) {
                $('.removeEducation').removeClass('hidden');
            } else {
                $('.removeEducation').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let languageCount = 1;

        // Fungsi untuk Menambah Baris Responsibility
        $('#addLanguage').click(function () {
            languageCount++;
            $('#languageTable').append(`
                <tr class="language-row">
                    <td class="p-3 border text-center">${languageCount}</td>                   
                    <td class="p-3 border">
                        <textarea name="language_descr[]" placeholder="Language Description"  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
                    </td>     
                    <td class="p-3 border">
                        <input type="text" name="language_score[]" placeholder="Language Score" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td>           
                    <td class="p-3 border text-center">
                        <button type="button" class="removeLanguage  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Responsibility
        $(document).on('click', '.removeLanguage', function () {
            $(this).closest('.language-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            languageCount = 0;
            $('#languageTable tr').each(function () {
                languageCount++;
                $(this).find('td:first').text(languageCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.language-row').length > 1) {
                $('.removeLanguage').removeClass('hidden');
            } else {
                $('.removeLanguage').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let courseCount = 1;

        // Fungsi untuk Menambah Baris Course
        $('#addCourse').click(function () {
            courseCount++;
            $('#courseTable').append(`
                <tr class="course-row">
                    <td class="p-3 border text-center">${courseCount}</td>                   
                    <td class="p-3 border">
                        <input type="text" name="course_name[]" placeholder="Course Name" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>   
                    <td class="p-3 border">
                        <select name="course_type[]" class="w-full border rounded px-4 py-2" required>
                            <option value="" disabled selected>Select Course</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Sertifikat">Sertifikat</option>                                           
                            </select>
                    </td>
                    <td class="p-3 border">
                        <input type="text" name="start_year[]" placeholder="Start Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td> 
                    <td class="p-3 border">
                        <input type="text" name="end_year[]" placeholder="End Year" maxlength="4" class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>             
                    <td class="p-3 border text-center">
                        <button type="button" class="removeCourse  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Course
        $(document).on('click', '.removeCourse', function () {
            $(this).closest('.course-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            courseCount = 0;
            $('#courseTable tr').each(function () {
                courseCount++;
                $(this).find('td:first').text(courseCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.course-row').length > 1) {
                $('.removeCourse').removeClass('hidden');
            } else {
                $('.removeCourse').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        let skillCount = 1;

        // Fungsi untuk Menambah Baris Responsibility
        $('#addSkill').click(function () {
            skillCount++;
            $('#skillTable').append(`
                <tr class="skill-row">
                    <td class="p-3 border text-center">${skillCount}</td>  
                    <td class="p-3 border">
                        <textarea name="skill_descr[]" placeholder="Description"  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
                    </td>            
                    <td class="p-3 border text-center">
                        <button type="button" class="removeSkill  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
            `);
            updateRemoveButtons();
        });

        // Fungsi untuk Menghapus Baris Responsibility
        $(document).on('click', '.removeSkill', function () {
            $(this).closest('.skill-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            skillCount = 0;
            $('#skillTable tr').each(function () {
                skillCount++;
                $(this).find('td:first').text(skillCount);
            });
        }

        // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
        function updateRemoveButtons() {
            if ($('.skill-row').length > 1) {
                $('.removeSkill').removeClass('hidden');
            } else {
                $('.removeSkill').addClass('hidden');
            }
        }

        updateRemoveButtons();
        
    });

</script>

<script>
    $(document).ready(function () {
        // Cegah input selain angka saat mengetik
        $('.number-only').on('keypress', function (event) {
            let charCode = event.which ? event.which : event.keyCode;
            if (charCode < 48 || charCode > 57) {
                event.preventDefault();
            }
        });

        // Hapus karakter selain angka jika sudah terlanjur masuk
        $('.number-only').on('input', function () {
            let value = $(this).val();
            $(this).val(value.replace(/[^0-9]/g, ''));
        });
    });
</script>
    
 <!-- Toastr CSS -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
 <!-- Toastr JS -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
