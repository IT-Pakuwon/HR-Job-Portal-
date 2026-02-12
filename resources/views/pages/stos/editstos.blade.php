<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:col-span-2 lg:row-span-1">
                <div class="flex flex-col gap-4">
                    <form id="stoForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="flex w-full flex-col gap-8 rounded-xl bg-white p-4 dark:bg-gray-800">
                            <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                                <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Edit ORG Structure
                                </h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-2">
                                    <label for="sto_id"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">STO
                                        ID</label>
                                    <input type="text" id="sto_id" name="sto_id"
                                        class="w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        value="{{ $sto->sto_id }}" readonly>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="sto_date"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Date</label>
                                    <input type="text" id="sto_date" name="sto_date"
                                        class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        value="{{ $sto->sto_date }}" readonly>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-2">
                                    <label for="selectCompany"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <select id="selectCompany"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="cpnyid" required>
                                        @foreach ($companies as $p)
                                            <option value="{{ $p->cpnyid }}"
                                                {{ $p->cpnyid == $sto->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="selectdeptname"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Department</label>
                                    <select id="selectdeptname"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="departementid" required>
                                        @foreach ($departements as $p)
                                            <option value="{{ $p->deptname }}"
                                                {{ $p->deptname == $sto->departementid ? 'selected' : '' }}>
                                                {{ $p->deptname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex w-full flex-col gap-8 rounded-xl bg-white p-4 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex max-h-[125px] flex-col overflow-y-auto pt-6">
                                <div id="attachmentsContainer">
                                    @foreach ($attachment as $attach)
                                        <div class="attachment-row flex items-center gap-2"
                                            data-attachid="{{ $attach->id }}">
                                            <a href="{{ url('/attachments/' . $attach->attachfile) }}" target="_blank"
                                                class="mt-4 w-full border p-3 text-sm">📎
                                                {{ $attach->name }}</a>
                                            <button type="button"
                                                class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                data-id="{{ $attach->id }}">🗑️
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg> Add Attachment
                            </button>
                        </details>
                        <div class="flex w-full justify-end gap-4 pt-4">
                            <button type="button" id="cancelBtn"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                <span id="cancelBtnText">Cancel</span>
                                <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                            <button type="submit" id="submitBtn" form="stoForm"
                                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="submitBtnText">Submit Approval</span>
                                <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-start-2">
                <details class="group w-full rounded-xl bg-white p-4 dark:bg-gray-800" open>
                    <summary
                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                        <span>ORG Chart Visualization</span>
                        <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See details
                            &rarr;</span>
                        <span class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                            details &darr;</span>
                    </summary>
                    <div class="relative mt-4">
                        <div
                            class="chart-container flex h-[500px] w-full items-center justify-center overflow-auto rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-400 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-500">
                        </div>
                    </div>
                </details>
            </div>
        </div>
        <div id="modalForm"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
            <div class="relative w-full max-w-5xl rounded-lg bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <ul class="flex flex-wrap text-center text-xs font-medium" id="tabs">
                        <li class="mr-2">
                            <button type="button"
                                class="tab-button active-tab inline-flex items-center justify-center rounded-t-lg border-b-2 border-transparent p-4 text-sm text-gray-500 transition-colors duration-200 hover:border-gray-300 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
                                onclick="switchTab('view')">
                                <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm0 18a8 8 0 1 1 8-8A8.009 8.009 0 0 1 10 18Zm-3-8a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H8a1 1 0 0 1-1-1Zm0-4a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H8a1 1 0 01-1-1Zm0 8a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H8a1 1 0 01-1-1Z" />
                                </svg>
                                View Employee
                            </button>
                        </li>
                        <li class="mr-2">
                            <button type="button"
                                class="tab-button inline-flex items-center justify-center rounded-t-lg border-b-2 border-transparent p-4 text-sm text-gray-500 transition-colors duration-200 hover:border-gray-300 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
                                onclick="switchTab('employee')">
                                <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm0 18a8 8 0 1 1 8-8A8.009 8.009 0 0 1 10 18Zm-3-8a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H8a1 1 0 01-1-1Zm0-4a1 1 0 0 1 1-1h4a1 1 0 010 2H8a1 1 0 01-1-1Zm0 8a1 1 0 011-1h4a1 1 0 010 2H8a1 1 0 01-1-1Z" />
                                </svg>
                                Add Employee
                            </button>
                        </li>
                        <li class="mr-2">
                            <button type="button"
                                class="tab-button inline-flex items-center justify-center rounded-t-lg border-b-2 border-transparent p-4 text-sm text-gray-500 transition-colors duration-200 hover:border-gray-300 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
                                onclick="switchTab('departement')">
                                <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm0 18a8 8 0 1 1 8-8A8.009 10.009 0 0 1 10 18Zm-3-8a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H8a1 1 0 01-1-1Zm0-4a1 1 0 0 1 1-1h4a1 1 0 010 2H8a1 1 0 01-1-1Zm0 8a1 1 0 011-1h4a1 1 0 010 2H8a1 1 0 01-1-1Z" />
                                </svg>
                                Add Sub Departement
                            </button>
                        </li>
                        <li class="mr-2">
                            <button type="button"
                                class="tab-button inline-flex items-center justify-center rounded-t-lg border-b-2 border-transparent p-4 text-sm text-gray-500 transition-colors duration-200 hover:border-gray-300 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
                                onclick="switchTab('specprofile')">
                                <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300"
                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M10 0a10 10 0 1 0 10 10A10.009 10.009 0 0 0 10 0Zm0 18a8 8 0 1 1 8-8A8.009 10.009 0 0 1 10 18Zm-3-8a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H8a1 1 0 01-1-1Zm0-4a1 1 0 011-1h4a1 1 0 010 2H8a1 1 0 01-1-1Zm0 8a1 1 0 011-1h4a1 1 0 010 2H8a1 1 0 01-1-1Z" />
                                </svg>
                                Add Job Profile & Spec
                            </button>
                        </li>
                    </ul>
                    <button onclick="closeModal()"
                        class="text-lg leading-none text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        &times;
                    </button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto p-4">
                    <div id="tab-view" class="tab-content hidden">
                        {{-- <div class="mb-4 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <h4 class="text-base font-semibold text-gray-800 dark:text-white">
                                Parent Department: <span id="parentDeptLabel"
                                    class="text-indigo-600 dark:text-indigo-400"></span>
                                <button id="btnChangeParentDept"
                                    class="ml-2 inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span>Change Parent</span>
                                </button>
                            </h4>

                            <h4 id="departmentLabel" class="text-base font-semibold text-gray-800 dark:text-white">
                                Department: <span class="text-indigo-600 dark:text-indigo-400"></span>
                                <button id="btnChangeDept"
                                    class="ml-2 inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span>Move All Employee</span>
                                </button>
                            </h4>
                        </div> --}}
                        <div class="flex justify-between">
                            {{-- <h3 class="text-sm font-semibold">Employee List</h3> --}}
                            <div class="mb-4 flex items-center justify-between">
                                <h4 class="text-sm font-semibold">Parent Department: <span id="parentDeptLabel"
                                        class="text-sm font-semibold text-gray-800"></span></h4>
                                <button id="btnChangeParentDept"
                                    class="flex items-center gap-1 rounded px-3 py-1.5 text-xs text-black dark:text-white">
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
                                    class="flex items-center gap-1 rounded px-3 py-1.5 text-xs text-black dark:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    <span>Move All Employee</span>
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                            <table
                                class="min-w-full divide-y divide-gray-200 text-xs text-gray-800 dark:divide-gray-700 dark:text-gray-200">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            No</th>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            Name</th>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            Company</th>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            Position</th>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            Photo</th>
                                        <th scope="col" class="px-4 py-2 text-left font-semibold tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody"
                                    class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="tab-employee" class="tab-content hidden">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Add Employee</h3>
                        <form id="formAddEmployee" method="POST" action="{{ route('orgchart.store') }}"
                            enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="approval_line" id="modalApprovalLine">
                            <input type="hidden" name="sto_id" value="{{ $sto->sto_id }}">

                            <div>
                                <label for="employeeCompany"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select id="employeeCompany"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" id="vacantCheckbox" checked
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                <label for="vacantCheckbox" class="ml-2 text-xs text-gray-700 dark:text-gray-300">Set
                                    as VACANT</label>
                            </div>

                            <input type="hidden" name="full_name" id="hiddenFullName" value="VACANT">

                            <div id="fullNameGroup">
                                <label for="selectFullName"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <select id="selectFullName" name="name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="" disabled selected>Pilih nama karyawan...</option>
                                    @foreach ($users as $p)
                                        <option value="{{ $p->name }}" data-npk="{{ $p->npk }}">
                                            {{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="npk" id="hiddenNpk">
                            </div>

                            <div id="imageGroup">
                                <label for="imageInput"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Image</label>
                                <input type="file" name="image" id="imageInput" accept="image/*"
                                    class="mt-1 block w-full text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white">
                            </div>

                            <div id="qtyGroup">
                                <label for="qty"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Qty</label>
                                <input type="number" name="qty" id="qty"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    value="{{ old('qty', 1) }}" required min="1">
                            </div>

                            <input type="hidden" name="status_talenta" value="Active">

                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Save Employee
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="tab-departement" class="tab-content hidden">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Add Sub Department
                        </h3>
                        <form id="formAddDepartement" method="POST" action="{{ route('orgchart.store') }}"
                            class="space-y-4">
                            @csrf
                            <input type="hidden" name="approval_line" id="modalApprovalLine">
                            <input type="hidden" name="sto_id" value="{{ $sto->sto_id }}">

                            <div>
                                <label for="departement_name"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Sub
                                    Department Name</label>
                                <input type="text" name="departement_name" id="departement_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                            </div>

                            <div>
                                <label for="subgrade_id"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Sub
                                    Grading</label>
                                <input type="hidden" name="subgrade_name" id="subgrade_name">
                                <select name="subgrade_id" id="subgrade_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required onchange="updateSubgradeName(this)">
                                    <option value="" disabled selected>-- Pilih --</option>
                                    @foreach ($subgrading as $p)
                                        <option value="{{ $p->subgrade_id }}">{{ $p->subgrade_id }} -
                                            {{ $p->subgrade_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Save Department
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="tab-specprofile" class="tab-content hidden">
                        <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Add Job Profile &
                            Spec
                        </h3>
                        <form id="formAddSpec" method="POST" action="{{ route('orgchart.store') }}"
                            class="space-y-4">
                            @csrf
                            <input type="hidden" name="approval_line" id="modalApprovalLine">
                            <input type="hidden" name="sto_id" value="{{ $sto->sto_id }}">

                            <div>
                                <label for="job_level"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Position</label>
                                {{-- <select name="job_level" id="job_level"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                                    <option value="" disabled selected>-- Select Position --</option>
                                    @foreach ($subgrading as $p)
                                        <option value="{{ $p->subgrade_id }}">{{ $p->subgrade_id }} -
                                            {{ $p->subgrade_name }}</option>
                                    @endforeach
                                </select> --}}
                                <input type="text" id="position" name="position"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-200 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-400"
                                    readonly>


                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Job
                                    Purpose</label>
                                <div id="jobPurposeList" class="flex flex-col gap-3">
                                    <div class="flex items-center gap-2">
                                        <textarea name="job_purpose[]"
                                            class="flex-grow rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            placeholder="Describe job purpose" required rows="3"></textarea>
                                        <button type="button"
                                            class="removePurpose mt-4 hidden rounded border border-red-600 bg-red-200/30 p-2 text-red-600 transition hover:bg-red-600 hover:text-white">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                <button type="button" id="addJobPurpose"
                                    class="mt-3 inline-flex items-center rounded-md border border-transparent bg-indigo-100 px-3 py-1.5 text-xs font-medium text-indigo-700 transition-colors hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg> Add Purpose
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label for="education_level"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Education
                                        Level</label>
                                    <select name="education_level" id="education_level"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        required>
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
                                    <label for="education_major"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Major</label>
                                    <input type="text" name="education_major" id="education_major"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="e.g., Computer Science" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label for="experience_years"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Experience
                                        (Years)</label>
                                    <input type="number" name="experience_years" id="experience_years"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="e.g., 2" required min="0">
                                </div>
                                <div>
                                    <label for="experience_position"
                                        class="block text-xs font-medium text-gray-700 dark:text-gray-300">Experience
                                        in Position</label>
                                    <input type="text" name="experience_position" id="experience_position"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="e.g., HR Supervisor" required>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Save Job Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="editModal"
                    class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
                    <div class="relative w-full max-w-lg rounded-lg bg-white p-4 shadow-xl dark:bg-gray-800">
                        <button onclick="closeEditModal()"
                            class="absolute right-3 top-3 text-lg leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            &times;
                        </button>
                        <h3 class="mb-6 text-base font-bold text-gray-800 dark:text-white">Edit Employee</h3>
                        <form id="editEmployeeForm" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="id" id="edit_id">

                            <div>
                                <label for="edit_name"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <select name="employee_name" id="edit_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="" disabled selected>-- Select Employee --</option>
                                    @foreach ($users as $p)
                                        <option value="{{ $p->name }}" data-npk="{{ $p->npk }}">
                                            {{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="npk" id="hiddenNpkedit">
                            </div>

                            <div>
                                <label for="edit_company"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="employee_company" id="edit_company"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @foreach ($companies as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="edit_image"
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">New
                                    Image
                                    (optional)</label>
                                <input type="file" name="image" id="edit_image" accept="image/*"
                                    class="mt-1 block w-full text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white">
                            </div>

                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Update Employee
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="modalChangeDept"
                    class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
                    <div class="relative w-full max-w-md rounded-lg bg-white p-4 shadow-xl dark:bg-gray-800">
                        <h3 class="mb-6 text-base font-bold text-gray-800 dark:text-white">Change Department</h3>

                        <div class="mb-4">
                            <label for="selectNewDept"
                                class="block text-xs font-medium text-gray-700 dark:text-gray-300">Select
                                Department</label>
                            <select id="selectNewDept"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @foreach ($subdepartments as $dept)
                                    <option value="{{ $dept->departement_id }}">{{ $dept->departement_name }}
                                        -
                                        {{ $dept->subgrade_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <button id="btnCancelChange"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button id="btnConfirmChange"
                                class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Update
                            </button>
                        </div>
                    </div>
                </div>

                <div id="modalChangeParent"
                    class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
                    <div class="relative w-full max-w-md rounded-lg bg-white p-4 shadow-xl dark:bg-gray-800">
                        <h3 class="mb-6 text-base font-bold text-gray-800 dark:text-white">Change Parent
                            Department
                        </h3>

                        <div class="mb-4">
                            <label for="selectNewParentDept"
                                class="block text-xs font-medium text-gray-700 dark:text-gray-300">Select New
                                Parent Department</label>
                            <select id="selectNewParentDept"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @foreach ($parentdepartments as $dept)
                                    <option value="{{ $dept->departement_id }}">{{ $dept->departement_name }}
                                        -
                                        {{ $dept->subgrade_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <button id="btnCancelChangeParent"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button id="btnConfirmChangeParent"
                                class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Update
                            </button>
                        </div>
                    </div>
                </div>

                <div id="modalJobProfile"
                    class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
                    <div
                        class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white p-4 shadow-xl dark:bg-gray-800">
                        <div
                            class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                                Job Profile - <span id="jobLevelLabel"
                                    class="font-bold text-indigo-600 dark:text-indigo-400"></span>
                            </h3>
                            <button onclick="$('#modalJobProfile').addClass('hidden')"
                                class="text-lg leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                &times;
                            </button>
                        </div>

                        <div
                            class="mb-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                            <table
                                class="min-w-full divide-y divide-gray-200 text-xs text-gray-800 dark:divide-gray-700 dark:text-gray-200">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            No</th>
                                        <th scope="col"
                                            class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                            Job Purpose</th>
                                        <th scope="col" class="px-4 py-2 text-left font-semibold tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="jobProfileBody"
                                    class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                </tbody>
                            </table>
                        </div>

                        <div id="jobSpecInfo" class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="successMessage"
            class="mt-4 hidden rounded-md bg-green-50 p-3 text-xs font-medium text-green-700 shadow-sm">
            STO created successfully!
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
        $(document).ready(function() {
            if ($('#selectdeptname').val() === 'IT') {
                $('#selectdeptname').trigger('change');
            }
            $('#departement_name_select').select2({
                tags: true, // Memungkinkan input baru
                placeholder: "Pilih atau ketik departemen",
                width: '100%'
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#stoForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // $('input[name="attachments[]"]').each(function() {
                //     const files = this.files;
                //     for (let i = 0; i < files.length; i++) {
                //         formData.append('attachments[]', files[i]);
                //     }
                // });

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#submitBtnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                // Ambil id dari data attribute atau hidden input
                var stoId = $('input[name="sto_id"]').val() || "{{ $sto->id ?? '' }}";
                var updateUrl = "{{ url('/stos') }}/" + stoId;

                $.ajax({
                    url: updateUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#stoForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#submitBtnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Sto Submit Successfully!");
                        window.location.href = "/stos";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON
                            .message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error! Please check the input.');
                        }

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#submitBtnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                    }
                });
            });
        });
    </script>

    <script>
        var chart = null;
        $('select[name="departementid"]').on('change', function() {
            const deptname = encodeURIComponent($('#selectdeptname').val());
            // const company = encodeURIComponent($('#selectCompany').val());

            $('.chart-container').html(
                '<div class="text-center text-gray-400 mt-10 animate-pulse">Loading...</div>'
            );

            $.ajax({
                url: `/orgchart/by-dept/${deptname}`,
                method: 'GET',
                success: function(data) {
                    const nodes = data.nodes || [];
                    const connections = data.connections || [];

                    if (!Array.isArray(nodes) || nodes.length === 0) {
                        $('.chart-container').html(
                            '<div class="text-center text-gray-500 mt-10">No data available for this department.</div>'
                        );
                        return;
                    }

                    $('.chart-container').empty(); // Bersihkan chart sebelumnya

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
                        .onNodeClick((d) => openModal(d.data.id))
                        .container('.chart-container')
                        .data(nodes)
                        .expandAll()
                        .connections(connections)
                    // .render()
                    chart.compact(false).render().fit();

                },

                error: function(xhr) {
                    $('.chart-container').empty();
                    if (xhr.status === 404) {
                        $('.chart-container').html(
                            '<div class="text-center text-gray-500 mt-10">Department not found.</div>'
                        );
                    } else {
                        $('.chart-container').html(
                            '<div class="text-center text-red-500 mt-10">Error loading chart data.</div>'
                        );
                    }
                }
            });
        });
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
                                <img src="${emp.image || 'https://cdn-icons-png.flaticon.com/512/149/149071.png'}" class="w-25 h-25 rounded-full mx-auto">
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
                    if (employees.length > 0) {
                        const firstPosition = employees[0].employee_level;
                        $('#position').val(firstPosition);
                    } else {
                        $('#position').val('');
                    }
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

            const deptname = encodeURIComponent($('#selectdeptname').val());
            // const company = encodeURIComponent($('#selectCompany').val());

            $('.chart-container').html(
                '<div class="text-center text-gray-400 mt-10 animate-pulse">Refreshing...</div>'
            );

            $.ajax({
                url: `/orgchart/by-dept/${deptname}`,
                method: 'GET',
                success: function(data) {
                    const nodes = data.nodes || [];
                    const connections = data.connections || [];

                    if (!Array.isArray(nodes) || nodes.length === 0) {
                        $('.chart-container').html(
                            '<div class="text-center text-gray-500 mt-10">No data available for this department.</div>'
                        );
                        return;
                    }

                    $('.chart-container').empty();

                    chart = new d3.OrgChart()
                        .nodeWidth((d) => 300 + (d.data.members?.length || 0) * 10)
                        .nodeHeight((d) => 100 + (d.data.members?.length || 0) * 30)
                        .childrenMargin((d) => 40)
                        .compactMarginBetween((d) => 35)
                        .compactMarginPair((d) => 30)
                        .neighbourMargin((a, b) => 20)
                        .nodeContent(function(d) {
                            const members = d.data.members || [];
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
                        .onNodeClick((d) => openModal(d.data.id))
                        .container('.chart-container')
                        .data(nodes)
                        .expandAll()
                        .connections(connections)
                        .compact(false)
                        .render()
                        .fit();
                },
                error: function(xhr) {
                    $('.chart-container').empty();
                    if (xhr.status === 404) {
                        $('.chart-container').html(
                            '<div class="text-center text-gray-500 mt-10">Department not found.</div>'
                        );
                    } else {
                        $('.chart-container').html(
                            '<div class="text-center text-red-500 mt-10">Error loading chart data.</div>'
                        );
                    }
                }
            });
        }
    </script>




    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" form="stoForm" class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
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

            $(document).on('click', '.removeAttachment2', function() {
                let attachmentId = $(this).data('id'); // Ambil ID attachment
                let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

                // Cek konfirmasi pengguna
                let confirmDelete = confirm('Are you sure you want to remove this attachment?');

                if (confirmDelete) {
                    $.ajax({
                        url: "/stos/remove-attachment/" + attachmentId, // Endpoint ke controller
                        type: "POST",
                        data: {
                            _method: "PUT",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                row.remove(); // Hapus dari tampilan jika berhasil
                                alert("Attachment removed successfully!");
                            } else {
                                alert("Failed to remove attachment.");
                            }
                        },
                        error: function(xhr) {
                            alert("Error! Unable to remove attachment.");
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
                    return false;
                }
            });
        });
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            toggleVacantUI($('#vacantCheckbox').is(':checked'));

            $('#vacantCheckbox').change(function() {
                const isChecked = $(this).is(':checked');
                toggleVacantUI(isChecked);
            });

            function toggleVacantUI(isChecked) {
                if (isChecked) {
                    // Mode VACANT aktif
                    $('#hiddenFullName').val('VACANT').attr('name', 'full_name');
                    $('#selectFullName').removeAttr('name').val('');
                    $('#imageInput').removeAttr('name');
                    $('#fullNameGroup').hide();
                    $('#imageGroup').hide();
                    $('#qtyGroup').show();
                } else {
                    // Mode pilih karyawan
                    $('#selectFullName').attr('name', 'full_name'); // 🟢 tambahkan kembali name
                    $('#hiddenFullName').removeAttr('name'); // 🔴 hilangkan name dari hidden
                    $('#imageInput').attr('name', 'image');
                    $('#fullNameGroup').show();
                    $('#imageGroup').show();
                    $('#qtyGroup').hide();
                    $('#qty').val(1);
                }
            }
        });
    </script>
    <script>
        //    $(function() {
        //         $('#selectFullName').on('change', function() {
        //             var npk = $(this).find(':selected').data('npk') || '';
        //             console.log("Selected NPK:", npk);
        //             $('#hiddenNpk').val(npk);
        //         });
        //     });
        $(function() {
            $('#selectFullName, #edit_name').on('change', function() {
                // Ini untuk select yang berubah
                var selectedOption = $(this).find(':selected');
                var npk = selectedOption.data('npk') || '';
                var value = selectedOption.val() || '';
                console.log("Selected NPK:", npk);
                console.log("Selected Value:", value);

                // Kalau hidden NPK-nya beda, bisa dibuat dinamis:
                if ($(this).attr('id') === 'selectFullName') {
                    $('#hiddenNpk').val(npk);
                }
                if ($(this).attr('id') === 'edit_name') {
                    $('#hiddenNpkedit').val(npk);
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
                        <textarea name="job_purpose[]" class="flex-grow rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" placeholder="Describe job purpose" required></textarea>
                        <button type="button" class="removePurpose bg-red-200/30 mt-4 text-red-600 p-2 rounded border border-red-600 hover:text-white hover:bg-red-600 transition">Delete</button>
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
                        // ✅ Reset inputan
                        form.trigger('reset'); // reset semua input biasa
                        $('#selectFullName').val(null).trigger(
                            'change'); // jika select2 atau select biasa
                        $('#position').val(''); // reset input readonly
                        $('#vacantCheckbox').prop('checked', true).trigger('change'); // default VACANT

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
                                    <button class="btn-delete-jobpurpose bg-red-200/30 text-red-600 p-2 rounded border border-red-600 hover:text-white hover:bg-red-600 transition"
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
    <script>
        $(document).ready(function() {
            $('#selectdeptname').select2({
                placeholder: "Pilih Departement Name...",
                allowClear: true,
                width: 'resolve',
                dropdownAutoWidth: true
            });
            setTimeout(function() {
                $("#selectdeptname").next('.select2-container').css('min-width', '200px');
            }, 0);

            // Aktifkan select2 untuk Company
            $('#selectCompany').select2({
                placeholder: "Pilih Company...",
                allowClear: true,
                width: 'resolve',
                dropdownAutoWidth: true
            });
            setTimeout(function() {
                $("#selectCompany").next('.select2-container').css('min-width', '150px');
            }, 0);
        });
    </script>


</x-app-layout>
