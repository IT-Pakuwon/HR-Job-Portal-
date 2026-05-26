<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 py-1 lg:px-2">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="stoForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div
                                class="flex w-full w-full flex-col rounded-xl border-b bg-white p-6 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="mb-2 text-base font-bold">Create STO</h2>
                                </div>
                                <div
                                    class="mt-2 mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <div class="flex items-center gap-4">
                                        <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">STO
                                            ID</label>
                                        <input type="text" name="sto_id"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            value="{{ $sto->sto_id }}" readonly>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Date</label>
                                        <input type="text" name="sto_date"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            value="{{ $sto->sto_date }}" readonly>
                                    </div>
                                </div>
                                <div
                                    class="mt-2 mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Company</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="cpnyid" required>
                                            @foreach ($usercpny as $p)
                                                <option value="{{ $p->cpnyid }}"
                                                    {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                                    {{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Department</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="departementid" required>
                                            @foreach ($userdept as $p)
                                                <option value="{{ $p->deptname }}"
                                                    {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>
                                                    {{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="chart-container" style="width: 100%; height: 800px;"></div>



                            </div>

                    </div>


                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-1/2 w-full flex-col border-b p-4">
                            <details class="group mb-4" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-sm font-semibold">Attachments</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>
                                <div class="flex h-auto flex-col justify-start">
                                    <div id="attachmentsContainer">
                                        <div class="attachment-row flex items-center gap-2">
                                            <input type="file" name="attachments[]"
                                                class="mt-4 w-full border p-3 text-sm">
                                            <button type="button"
                                                class="removeAttachment mt-4 hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" id="addAttachment"
                                        class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg> Add Attachment
                                    </button>
                                </div>
                            </details>
                        </div>
                        <div class="flex h-auto w-full flex-row justify-end gap-4 pl-4 pr-4">
                            <div class="w-1/8 flex flex-col justify-start">
                                <button type="button" id="cancelBtn"
                                    class="flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                                    <span id="btnText">Cancel Approval</span>
                                    <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="w-1/8 flex flex-col justify-start">
                                <button type="submit" id="submitBtn"
                                    class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r=" 10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
            <div id="modalForm"
                class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50   ">
                <div class="relative w-full max-w-5xl rounded-lg bg-white p-4">
                    <div class="border-gray-200s mb-4 flex justify-between border-b">
                        <ul class="-mb-px flex flex-wrap text-center text-xs font-medium" id="tabs">
                            <li class="mr-2">
                                <button type="button"
                                    class="tab-button border-blue-600 px-4 py-2 text-sm text-blue-600"
                                    onclick="switchTab('view')">View Employee</button>
                            </li>
                            <li class="mr-2">
                                <button type="button"
                                    class="tab-button px-4 py-2 text-sm text-gray-600 hover:border-blue-600 hover:text-blue-600"
                                    onclick="switchTab('employee')">Add Employee</button>
                            </li>
                            <li class="mr-2">
                                <button type="button"
                                    class="tab-button px-4 py-2 text-sm text-gray-600 hover:border-blue-600 hover:text-blue-600"
                                    onclick="switchTab('departement')">Add Sub Departement</button>
                            </li>
                            <li class="mr-2">
                                <button type="button"
                                    class="tab-button px-4 py-2 text-sm text-gray-600 hover:border-blue-600 hover:text-blue-600"
                                    onclick="switchTab('specprofile')">Add Job Profile & Spec</button>
                            </li>
                        </ul>
                        <button onclick="closeModal()" class="text-sm text-gray-500">close</button>

                    </div>

                    <!-- Tab Content: View Employee -->
                    <div id="tab-view" class="tab-content hidden">
                        <div class="flex justify-between">
                            {{-- <h3 class="text-sm font-semibold">Employee List</h3> --}}
                            <div class="mb-4 flex items-center justify-between">
                                <h4 class="text-sm font-semibold">Parent Department: <span id="parentDeptLabel"
                                        class="text-sm font-semibold text-gray-800"></span></h4>
                                <button id="btnChangeParentDept"
                                    class="flex items-center gap-1 rounded px-3 py-1.5 text-xs text-black">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span>Change Parent</span>
                                </button>
                            </div>

                            <div class="mb-4 flex items-center justify-between">
                                <h4 id="departmentLabel" class="text-sm font-semibold text-gray-800">
                                    Dept: <!-- Dynamic text will be inserted via JS -->
                                </h4>
                                <button id="btnChangeDept"
                                    class="flex items-center gap-1 rounded px-3 py-1.5 text-xs text-black">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span>Move All Employee</span>
                                </button>
                            </div>
                        </div>
                        <table class="w-full border   text-xs text-black">
                            <thead class="bg-gray-300/10">
                                <tr class="text-left">
                                    <th class="border   px-2 py-1">No</th>
                                    <th class="border   px-2 py-1">Name</th>
                                    <th class="border   px-2 py-1">Company</th>
                                    <th class="border   px-2 py-1">Position</th>
                                    <th class="border   px-2 py-1">Photo</th>
                                    <th class="border   px-2 py-1">Action</th>
                                </tr>
                            </thead>
                            <tbody id="employeeTableBody">

                            </tbody>
                        </table>
                    </div>

                    <!-- Tab Content: Employee -->
                    <div id="tab-employee" class="tab-content">
                        <h3 class="mb-4 text-sm font-semibold">Add Employee</h3>
                        <form id="formAddEmployee" method="POST" action="{{ route('orgchart.store') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="approval_line" id="modalApprovalLine">
                            {{-- <input type="hidden" name="full_name" value="Vacant">        --}}
                            <input type="hidden" name="sto_id" value="{{ $sto->sto_id }}">
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Company</label>
                                <select
                                    class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                    name="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="vacantCheckbox" class="form-checkbox text-blue-600">
                                    <span class="ml-2 text-xs text-gray-700">Set as VACANT</span>
                                </label>
                            </div>

                            <!-- Hidden input untuk simpan full_name saat VACANT -->
                            <input type="hidden" name="full_name" id="hiddenFullName" value="VACANT">

                            <div class="mb-4" id="fullNameGroup">
                                <label class="block text-xs font-medium text-gray-700">Name</label>
                                <select id="selectFullName" name="full_name"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2">
                                    <option value="" disabled selected>Pilih nama karyawan...</option>
                                    @foreach ($users as $p)
                                        <option value="{{ $p->name }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Position</label>
                                <select
                                    class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                    name="job_position" required>
                                    @foreach ($joblevel as $p)
                                        <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="mb-4" id="imageGroup">
                                <label class="block text-xs font-medium text-gray-700">Image</label>
                                <input type="file" name="image" id="imageInput" accept="image/*"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2">
                            </div>
                            <div class="mb-4" id="qtyGroup">
                                <label class="block text-xs font-medium text-gray-700">Qty</label>
                                <input type="number" name="qty" id="qty"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2"
                                    value="{{ old('qty', 1) }}" required>
                            </div>
                            <input type="hidden" name="status_talenta" value="Active">
                            <div class="mt-4">
                                <button type="submit"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Content: Departement -->
                    <div id="tab-departement" class="tab-content hidden">
                        <h3 class="mb-4 text-sm font-semibold">Add Sub Departement</h3>
                        <form id="formAddDepartement" method="POST" action="{{ route('orgchart.store') }}">
                            @csrf
                            <input type="hidden" name="approval_line" id="modalApprovalLine">
                            <input type="hidden" name="sto_id" value="{{ $sto->sto_id }}">
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Sub Departement</label>
                                <input type="text" name="departement_name"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Sub Grading</label>
                                <input type="hidden" name="subgrade_name" id="subgrade_name">
                                <select name="subgrade_id"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2" required
                                    onchange="updateSubgradeName(this)">
                                    <option value="" disabled selected>-- Pilih --</option>
                                    @foreach ($subgrading as $p)
                                        <option value="{{ $p->subgrade_id }}">{{ $p->subgrade_id }} -
                                            {{ $p->subgrade_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <button type="submit"
                                    class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div id="tab-specprofile" class="tab-content hidden">
                        <h3 class="mb-4 text-sm font-semibold">Add Job Profile & Spec</h3>
                        <form id="formAddSpec" method="POST" action="{{ route('orgchart.store') }}">
                            @csrf
                            <input type="hidden" name="approval_line" id="modalApprovalLine">
                            <input type="hidden" name="sto_id" value="{{ $sto->sto_id }}">
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Position</label>
                                <select name="job_level"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2" required>
                                    @foreach ($subgrading as $p)
                                        <option value="{{ $p->subgrade_id }}">{{ $p->subgrade_id }} -
                                            {{ $p->subgrade_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Job Purpose</label>
                                <div id="jobPurposeList" class="flex flex-col gap-2">
                                    <!-- Baris pertama -->
                                    <div class="flex gap-2">
                                        <textarea name="job_purpose[]" class="w-full rounded border p-2" placeholder="Deskripsikan tujuan pekerjaan"
                                            required></textarea>
                                        <button type="button" class="removePurpose hidden text-red-600">🗑️</button>
                                    </div>
                                </div>
                                <button type="button" id="addJobPurpose"
                                    class="mt-2 rounded border border-blue-600 px-3 py-1 text-blue-600 hover:bg-blue-100">
                                    + Add Purpose
                                </button>
                            </div>

                            <div class="mb-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Education Level</label>
                                    <select name="education_level"
                                        class="mt-1 block w-full rounded-md border border-gray-300 p-2" required>
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA / SMK">SMA / SMK</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Jurusan</label>
                                    <input type="text" name="education_major"
                                        class="mt-1 block w-full rounded-md border border-gray-300 p-2"
                                        placeholder="Masukkan jurusan" required>
                                </div>
                            </div>

                            <div class="mb-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Experience (Years)</label>
                                    <input type="number" name="experience_years"
                                        class="mt-1 block w-full rounded-md border border-gray-300 p-2"
                                        placeholder="Contoh: 2" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Experience in
                                        Position</label>
                                    <input type="text" name="experience_position"
                                        class="mt-1 block w-full rounded-md border border-gray-300 p-2"
                                        placeholder="Contoh: Supervisor HR" required>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit"
                                    class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div id="editModal"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10   ">
                        <div class="relative w-full max-w-lg rounded-lg bg-white p-6">
                            <button onclick="closeEditModal()"
                                class="absolute right-2 top-2 text-lg text-gray-500">&times;</button>
                            <h3 class="mb-4 text-sm font-bold">Edit Employee</h3>
                            <form id="editEmployeeForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" id="edit_id">

                                <div class="mb-3">
                                    <label>Name</label>
                                    <select name="employee_name" id="edit_name" class="select2 w-full">
                                        <option value="" disabled selected>-- Select Employee --</option>
                                        @foreach ($users as $p)
                                            <option value="{{ $p->name }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label>Company</label>
                                    <select
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="employee_company" id="edit_company">
                                        @foreach ($usercpny as $p)
                                            <option value="{{ $p->cpnyid }}"
                                                {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                                {{ $p->cpnyid }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="mb-3">
                                    <label>Position</label>
                                    <select
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="employee_level" id="edit_position">
                                        @foreach ($joblevel as $p)
                                            <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
                                <div class="mb-3">
                                    <label>New Image (optional)</label>
                                    <input type="file" name="image" class="w-full">
                                </div>
                                <button type="submit"
                                    class="rounded bg-blue-600 px-4 py-2 text-white">Update</button>
                            </form>
                        </div>
                    </div>

                    <div id="modalChangeDept"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50   ">
                        <div class="w-full max-w-md rounded-lg bg-white p-6  ">
                            <h3 class="mb-4 text-sm font-semibold">Change Department</h3>

                            <label class="mb-2 block text-xs font-medium text-gray-700">Select Department</label>
                            <select id="selectNewDept" class="mb-4 w-full rounded border p-2">
                                @foreach ($subdepartments as $dept)
                                    <option value="{{ $dept->departement_id }}">{{ $dept->departement_name }} -
                                        {{ $dept->subgrade_name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="flex justify-end space-x-2">
                                <button id="btnCancelChange"
                                    class="rounded bg-gray-300 px-4 py-2 hover:bg-gray-400">Cancel</button>
                                <button id="btnConfirmChange"
                                    class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Update</button>
                            </div>
                        </div>
                    </div>

                    <div id="modalChangeParent"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50   ">
                        <div class="w-full max-w-md rounded-lg bg-white p-6  ">
                            <h3 class="mb-4 text-sm font-semibold">Change Parent Department</h3>

                            <label class="mb-2 block text-xs font-medium text-gray-700">Select New Parent
                                Department</label>
                            <select id="selectNewParentDept" class="mb-4 w-full rounded border p-2">
                                @foreach ($parentdepartments as $dept)
                                    <option value="{{ $dept->departement_id }}">{{ $dept->departement_name }} -
                                        {{ $dept->subgrade_name }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="flex justify-end space-x-2">
                                <button id="btnCancelChangeParent"
                                    class="rounded bg-gray-300 px-4 py-2 hover:bg-gray-400">Cancel</button>
                                <button id="btnConfirmChangeParent"
                                    class="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700">Update</button>
                            </div>
                        </div>
                    </div>


                    <div id="modalJobProfile"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10   ">
                        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white p-6   ">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-semibold">
                                    Job Profile - <span id="jobLevelLabel" class="font-semibold text-blue-600"></span>
                                </h3>
                                <button onclick="$('#modalJobProfile').addClass('hidden')"
                                    class="text-base text-gray-600 hover:text-red-600">&times;</button>
                            </div>

                            <div class="mb-4">
                                <table class="w-full border   text-xs">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="border   px-2 py-1">No</th>
                                            <th class="border   px-2 py-1">Job Purpose</th>
                                            <th class="border   px-2 py-1">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jobProfileBody"></tbody>
                                </table>
                            </div>

                            <div id="jobSpecInfo" class="space-y-2 text-xs text-gray-700">
                                <!-- Job Spec details will be injected here -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
                Sto Created Successfully!
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- D3 Org Chart Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>


    <!-- Tambahkan di bagian <head> atau sebelum script -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#departement_name_select').select2({
                tags: true, // Memungkinkan input baru
                placeholder: "Pilih atau ketik departemen",
                width: '100%'
            });
        });
    </script>


    <script>
        var chart = null;

        d3.json("{{ route('orgchart.json') }}").then((res) => {
            const data = res.nodes; // ⬅️ Ambil 'nodes' dari response
            const connections = res.connections || []; // ⬅️ Ambil 'connections' tambahan

            chart = new d3.OrgChart()
                .nodeWidth((d) => {
                    return 300 + (d.data.members?.length || 0) * 10;
                })
                .nodeHeight((d) => {
                    return 100 + (d.data.members?.length || 0) * 30;
                })
                .childrenMargin((d) => 40)
                .compactMarginBetween((d) => 35)
                .compactMarginPair((d) => 30)
                .neighbourMargin((a, b) => 20)
                .nodeContent(function(d) {
                    const members = d.data.members || [];
                    const level = d.depth;
                    const bgColor = d.data.bgColor || '#f5f5f5';


                    return `
                        <div style='width:${d.width}px;height:${d.height}px;padding-top:25px;padding-left:1px;padding-right:1px'>
                            <div style="
                                background-color:${bgColor};
                                width:${d.width - 2}px;
                                height:${d.height - 25}px;
                                border-radius:10px;
                                border:1px solid #E4E2E9;
                                padding:15px;
                                overflow:visible;
                            ">
                                ${d.data.position
                                ? `<div style="font-size:18px;color:#08011E;margin-bottom:5px">${d.data.name} ${d.data.position}</div>`
                                : `<div style="font-size:18px;color:#08011E;text-align:center;margin-top:10px;">${d.data.name}</div>`
                                }

                                <div style="font-size:12px;color:#333">
                                    <div style="margin-top:10px;">
                                        ${members.map(m => `
                                                <div style="display:flex;align-items:center;margin-bottom:6px;">
                                                    <img src="${m.image}" style="width:30px;height:30px;border-radius:50%;margin-right:8px;" />
                                                    <span style="font-size:12px; color:${m.name.toUpperCase() === 'VACANT' ? 'red' : '#000'};">
                                                        ${m.name} (${m.company})
                                                    </span>
                                                </div>
                                            `).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .onNodeClick((d) => {
                    openModal(d.data.id);
                })
                .container('.chart-container')
                .data(data)
                .expandAll()
                .render();

            chart.connections(connections).render();

        });

        function openModal(id) {
            alert('Clicked node ID: ' + id);
        }
    </script>

    <script>
        function openModal(id) {
            currentDeptId = id;
            currentDeptId_parent = id;
            document.querySelectorAll('input[name="approval_line"]').forEach(el => el.value = id);

            // Ambil detail parent department (untuk label)
            $.ajax({
                url: `/departement/detail/${id}`, // pastikan route ini aktif
                method: 'GET',
                success: function(res) {
                    console.log(res)
                    const parentName = res.data.parent_name ?? 'No Parent';
                    $('#parentDeptLabel').text(parentName);
                    currentParentId = res.data.parent_id;

                },
                error: function() {
                    $('#parentDeptLabel').text('Unknown');
                }
            });

            $.ajax({
                url: `{{ url('/orgchart/employee/by-dept') }}/${id}`,
                method: 'GET',
                success: function(response) {
                    const employees = response.employees || [];
                    const deptName = response.departement_name || '-';

                    // Set label di atas tabel
                    const capitalizedDeptName = deptName.charAt(0).toUpperCase() + deptName.slice(1)
                        .toUpperCase();
                    $('#departmentLabel').text(`Department: ${capitalizedDeptName}`);


                    let html = '';
                    employees.forEach((emp, index) => {
                        html += `
                        <tr>
                            <td class="border   px-2 py-1">${index + 1}</td>
                            <td class="border   px-2 py-1">${emp.employee_name}</td>
                            <td class="border   px-2 py-1">${emp.employee_company}</td>
                            <td class="border   px-2 py-1">${emp.employee_level}</td>
                            <td class="border   px-2 py-1 text-center">
                                <img src="${emp.image || 'https://cdn-icons-png.flaticon.com/512/149/149071.png'}" class="w-15 h-15 rounded-full mx-auto">
                            </td>
                            <td class="border   px-2 py-1 text-center">
                                <div class="inline-flex gap-2">
                                    <!-- Job Profile Button -->
                                    <button
                                        class="btn-profile flex items-center gap-1 rounded bg-sky-500 hover:bg-sky-600 text-white px-2.5 py-2 text-xs transition"
                                        title="Job Profile"
                                        data-id="${emp.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                        <span>Job Profile</span>
                                    </button>
                                    <!-- Edit Button -->
                                    <button
                                        class="btn-edit flex items-center gap-1 rounded bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-2 text-xs transition"
                                        title="Edit"
                                        data-id="${emp.id}"
                                        data-name="${emp.employee_name}"
                                        data-company="${emp.employee_company}"
                                        data-position="${emp.employee_level}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        class="btn-delete flex items-center gap-1 rounded bg-rose-500 hover:bg-rose-600 text-white px-2.5 py-2 text-xs transition"
                                        title="Delete"
                                        data-id="${emp.id}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>

                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    `;
                    });


                    $('#employeeTableBody').html(html);
                    switchTab('view');
                    $('#modalForm').removeClass('hidden');
                },
                error: function(xhr) {
                    alert('Gagal memuat employee!');
                    console.error(xhr);
                }
            });
        }

        function closeModal() {
            document.getElementById('modalForm').classList.add('hidden');
            document.getElementById('formAddEmployee').reset();
        }
    </script>

    <script>
        $('#formAddEmployee').submit(function(e) {
            e.preventDefault(); // cegah submit default

            const form = $(this);
            const url = form.attr('action');
            const formData = new FormData(form[0]); // ✅ penting agar file ikut terkirim

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                processData: false, // WAJIB false
                contentType: false, // WAJIB false
                success: function(response) {
                    closeModal(); // tutup modal
                    refreshChart(); // reload chart
                    toastr.success("Add Employee Successfully!");
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Gagal menyimpan data!');
                }
            });
        });
    </script>



    <script>
        function switchTab(tab) {
            // Reset all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'font-bold');
                btn.classList.add('text-gray-600');
            });

            // Hide all tab content (if applicable)
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected tab content (if applicable)
            const selectedTab = document.getElementById(`tab-${tab}`);
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
            }

            // Find and style the clicked button
            const activeBtn = document.querySelector(`[onclick="switchTab('${tab}')"]`);
            if (activeBtn) {
                activeBtn.classList.remove('text-gray-600');
                activeBtn.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'font-bold');
            }
        }
    </script>

    <script>
        $('#formAddDepartement').submit(function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function(response) {
                    closeModal();
                    refreshChart();
                    toastr.success("Add Sub Departement Successfully!");
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('Gagal menyimpan departement!');
                }
            });
        });
    </script>

    <script>
        function updateSubgradeName(selectElement) {
            const selectedText = selectElement.options[selectElement.selectedIndex].text;
            $('#subgrade_name').val(selectedText);
        }

        function refreshChart() {
            console.log("🌀 Memanggil refreshChart()");
            d3.json("{{ route('orgchart.json') }}")
                .then((res) => {
                    const data = res.nodes;
                    const connections = res.connections || [];

                    // Kosongkan dulu chart-container (HARUS)
                    document.querySelector('.chart-container').innerHTML = '';

                    // Buat ulang chart
                    chart = new d3.OrgChart()
                        .nodeWidth((d) => 300 + (d.data.members?.length || 0) * 10)
                        .nodeHeight((d) => 100 + (d.data.members?.length || 0) * 30)
                        .childrenMargin((d) => 40)
                        .compactMarginBetween((d) => 35)
                        .compactMarginPair((d) => 30)
                        .neighbourMargin((a, b) => 20)
                        .nodeContent(function(d) {
                            const members = d.data.members || [];
                            const level = d.depth;
                            const bgColor = d.data.bgColor || '#f5f5f5';


                            return `
                                <div style='width:${d.width}px;height:${d.height}px;padding-top:25px;padding-left:1px;padding-right:1px'>
                                    <div style="
                                        background-color:${bgColor};
                                        width:${d.width - 2}px;
                                        height:${d.height - 25}px;
                                        border-radius:10px;
                                        border:1px solid #E4E2E9;
                                        padding:15px;
                                        overflow:visible;
                                    ">
                                        <div style="font-size:18px;color:#08011E;margin-bottom:5px">${d.data.name}  ${d.data.position}</div>
                                        <div style="font-size:12px;color:#333">
                                            <div style="margin-top:10px;">
                                                ${members.map(m => `
                                                        <div style="display:flex;align-items:center;margin-bottom:6px;">
                                                            <img src="${m.image}" style="width:30px;height:30px;border-radius:50%;margin-right:8px;" />
                                                            <span style="font-size:12px; color:${m.name.toUpperCase() === 'VACANT' ? 'red' : '#000'};">
                                                                ${m.name} (${m.company})
                                                            </span>
                                                        </div>
                                                    `).join('')}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        })
                        .onNodeClick((d) => openModal(d.data.id))
                        .container('.chart-container')
                        .data(data)
                        .expandAll()
                        .render();

                    chart.connections(connections).render();
                })
                .catch((err) => {
                    console.error('❌ Gagal render ulang chart:', err);
                });
        }
    </script>


    <script>
        $(document).ready(function() {
            $('#stoForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: "{{ route('stos.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#stoForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Sto Submit Successfully!");
                        window.location.href = "/stos";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            // alert('Error! Please check the input.');
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
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-sm border rounded mt-4">
                    <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }
        });
    </script>


    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#qtyGroup').hide(); // sembunyikan saat awal

            // Default name setting
            $('#full_name').attr('name', 'full_name');
            $('#hiddenFullName').removeAttr('name');

            $('#vacantCheckbox').change(function() {
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    $('#hiddenFullName').val('VACANT').attr('name', 'full_name');
                    $('#fullNameGroup').hide();
                    $('#imageGroup').hide();
                    $('#qtyGroup').show();

                    $('#full_name').removeAttr('name');
                    $('#selectFullName').removeAttr('name').val(null).trigger('change');
                    $('#imageInput').removeAttr('name');
                } else {
                    $('#fullNameGroup').show();
                    $('#imageGroup').show();
                    $('#qtyGroup').hide();

                    $('#full_name').attr('name', 'full_name');
                    $('#imageInput').attr('name', 'image');
                    $('#hiddenFullName').removeAttr('name');

                    // ✅ Kembalikan qty ke 1
                    $('#qty').val(1);
                }
            });
        });
    </script>

    <script>
        // Open Edit Modal
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $('#edit_id').val(id);
            $('#edit_name').val($(this).data('name')).trigger('change');
            $('#edit_company').val($(this).data('company'));
            $('#edit_position').val($(this).data('position'));
            $('#editModal').removeClass('hidden');
        });

        function closeEditModal() {
            $('#editModal').addClass('hidden');
            $('#editEmployeeForm')[0].reset();
            $('#edit_name').val(null).trigger('change');
        }

        // Submit Update
        $('#editEmployeeForm').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = $('#edit_id').val();

            $.ajax({
                url: `/orgchart/employee/update/${id}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    closeEditModal();
                    toastr.success('Employee updated!');
                    refreshChart();
                    openModal($('#modalApprovalLine').val());
                },
                error: function() {
                    toastr.error('Gagal update employee.');
                }
            });
        });

        $(document).ready(function() {
            $('#edit_name').select2({
                dropdownParent: $('#editModal'),
                width: '100%'
            });
        });
    </script>
    <script>
        $(document).on('click', '.btn-delete', function() {
            // const id = $('#edit_id').val();
            const id = $(this).data('id');
            if (confirm('Yakin ingin menghapus employee ini?')) {
                $.ajax({
                    url: `/orgchart/employee/delete/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        toastr.success('Employee deleted.');
                        refreshChart();
                        openModal($('#modalApprovalLine').val());
                    },
                    error: function() {
                        toastr.error('Gagal delete.');
                    }
                });
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#selectFullName').select2({
                placeholder: "Pilih nama karyawan...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        let currentDeptId = null;

        // Saat tombol Change ditekan
        $('#btnChangeDept').on('click', function() {
            $('#modalChangeDept').removeClass('hidden');
        });

        // Cancel modal
        $('#btnCancelChange').on('click', function() {
            $('#modalChangeDept').addClass('hidden');
        });

        // Saat klik Update
        $('#btnConfirmChange').on('click', function() {
            const newDeptId = $('#selectNewDept').val();

            if (!newDeptId || !currentDeptId || newDeptId === currentDeptId) {
                alert('Please select a different department.');
                return;
            }

            $.ajax({
                url: `{{ route('orgchart.change-dept') }}`,
                method: 'POST',
                data: {
                    _token: `{{ csrf_token() }}`,
                    old_dept_id: currentDeptId,
                    new_dept_id: newDeptId
                },
                success: function(res) {
                    // alert(res.message || 'Update sukses');
                    toastr.success("Sub Department Change Successfully!");

                    $('#modalChangeDept').addClass('hidden');
                    openModal(newDeptId); // reload data employee
                    refreshChart();
                },
                error: function(xhr) {
                    alert('Gagal update departemen.');
                    console.error(xhr);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Tombol Add Purpose
            $('#addJobPurpose').on('click', function() {
                $('#jobPurposeList').append(`
                    <div class="flex gap-2">
                        <textarea name="job_purpose[]" class="w-full border rounded p-2" placeholder="Deskripsikan tujuan pekerjaan" required></textarea>
                        <button type="button" class="removePurpose text-red-600">🗑️</button>
                    </div>
                `);
                toggleRemoveButtons();
            });

            // Tombol Remove Purpose
            $(document).on('click', '.removePurpose', function() {
                $(this).closest('div').remove();
                toggleRemoveButtons();
            });

            function toggleRemoveButtons() {
                const items = $('#jobPurposeList .removePurpose');
                if (items.length > 1) {
                    items.removeClass('hidden');
                } else {
                    items.addClass('hidden');
                }
            }
        });
    </script>
    <script>
        $('#formAddSpec').submit(function(e) {
            e.preventDefault();

            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize(); // karena tidak ada file

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function(response) {
                    if (response.success && response.type === 'job_spec') {
                        closeModal(); // sembunyikan modal
                        refreshChart(); // refresh chart
                        toastr.success("Job Spec Saved Successfully!");
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let message = Object.values(errors).map(e => e.join(', ')).join('\n');
                        toastr.error(message);
                    } else {
                        toastr.error("Gagal menyimpan Job Spec.");
                    }
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '.btn-profile', function() {
            const empId = $(this).data('id');

            $.ajax({
                url: `/orgchart/job-profile/${empId}`,
                method: 'GET',
                success: function(res) {
                    const profiles = res.profiles || [];
                    const spec = res.spec || {};

                    let rows = '';
                    profiles.forEach((p, i) => {
                        rows += `
                            <tr>
                                <td class="border   px-2 py-1">${i + 1}</td>
                                <td class="border   px-2 py-1">${p.job_purpose || ''}</td>
                                <td class="border   px-2 py-1 text-center">
                                    <button class="btn-delete-jobpurpose bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-700"
                                        data-id="${p.id}">
                                        🗑️ Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#jobProfileBody').html(rows);
                    $('#jobLevelLabel').text(spec.subgrade_name || '');

                    $('#jobSpecInfo').html(`
                        <h4 class="font-semibold">Job Spec Detail:</h4>
                        <p><strong>Education:</strong> ${spec.education_min || ''} - ${spec.education_jurusan || ''}</p>
                        <p><strong>Experience:</strong> ${spec.experience_min || ''} years as ${spec.experience_position || ''}</p>
                    `);

                    $('#modalJobProfile').removeClass('hidden');
                },
                error: function() {
                    toastr.error('Gagal memuat job profile.');
                }
            });
        });
    </script>
    <script>
        $(document).on('click', '.btn-delete-jobpurpose', function() {
            const id = $(this).data('id');
            if (confirm('Yakin ingin menghapus Job Purpose ini?')) {
                $.ajax({
                    url: `/orgchart/job-profile/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: `{{ csrf_token() }}`
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Deleted');
                        $('.btn-profile[data-id]').trigger('click'); // reload modal content
                    },
                    error: function() {
                        toastr.error('Gagal menghapus Job Purpose.');
                    }
                });
            }
        });
    </script>
    <script>
        $('#cancelBtn').click(function() {
            const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");

            if (confirmed) {
                $('#cancelBtn').attr('disabled', true);
                $('#cancelText').text('Cancelling...');
                $('#cancelSpinner').removeClass('hidden');

                // Redirect to /news
                window.location.href = "{{ route('stos') }}";
            }
        });
    </script>
    <script>
        let currentDeptId_parent = null;
        let currentParentId = null;

        $(document).on('click', '#btnChangeParentDept', function() {
            $('#modalChangeParent').removeClass('hidden');
        });

        $('#btnCancelChangeParent').click(() => {
            $('#modalChangeParent').addClass('hidden');
        });

        $('#btnConfirmChangeParent').click(() => {
            const newParentId = $('#selectNewParentDept').val();
            console.log('newParentId', newParentId)
            console.log('currentParentId', currentParentId)
            console.log('currentDeptId_parent', currentDeptId_parent)
            if (!newParentId || newParentId === currentParentId) {
                alert('Select a different parent department');
                return;
            }

            $.ajax({
                url: `{{ route('orgchart.change-parent') }}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    dept_id: currentDeptId_parent,
                    new_parent_id: newParentId
                },
                success: function(res) {
                    toastr.success(res.message || 'Parent updated');
                    $('#modalChangeParent').addClass('hidden');
                    refreshChart();
                    openModal(currentDeptId_parent);
                },
                error: function(xhr) {
                    toastr.error('Failed to update parent department.');
                }
            });
        });
    </script>

</x-app-layout>
