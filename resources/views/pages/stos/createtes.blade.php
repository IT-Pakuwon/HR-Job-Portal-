@@ -1,311 +1,376 @@
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

                                <!-- Modal dengan Tab -->



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
                            <div class="w-1/8 flex flex-col justify-start" ">
                                        <button type="submit" id="submitBtn" class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
                                            <span id="btnText">Submit Approval</span>
                                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r=" 10"
                                stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                </button>
                            </div>
                            <div class="w-1/8 flex flex-col justify-start">
                                <button type="submit" id="#"
                                    class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
                                    <span id="btnText">Cancel Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
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
                </form>
            </div>
            <div id="modalForm"
                class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50 backdrop-blur-md">
                <div class="relative w-full max-w-5xl rounded-lg bg-white p-4">
                    <div class="flex justify-end p-1">
                        <button onclick="closeModal()" class="text-sm text-gray-500">close</button>
                    </div>
                    <!-- Tab -->
                    <div class="mb-4 border-b border-gray-200">
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
                        </ul>
                    </div>

                    <!-- Tab Content: View Employee -->
                    <div id="tab-view" class="tab-content hidden">
                        <h3 class="mb-4 text-sm font-semibold">Employee List</h3>
                        <table class="w-full border text-xs text-black">
                            <thead class="bg-gray-300/10">
                                <tr class="text-left">
                                    <th class="border px-2 py-1">No</th>
                                    <th class="border px-2 py-1">Name</th>
                                    <th class="border px-2 py-1">Company</th>
                                    <th class="border px-2 py-1">Position</th>
                                    <th class="border px-2 py-1">Photo</th>
                                    <th class="border px-2 py-1">Action</th>
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
                                    <span class="ml-2 text-xs text-gray-700">Set as Vacant</span>
                                </label>
                            </div>

                            <!-- Hidden input untuk simpan full_name saat Vacant -->
                            <input type="hidden" name="full_name" id="hiddenFullName" value="Vacant">

                            <div class="mb-4" id="fullNameGroup">
                                <label class="block text-xs font-medium text-gray-700">Name</label>
                                <select id="full_name"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2">
                                    @foreach ($users as $p)
                                        <option value="{{ $p->name }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700">Position</label>
                                <select
                                    class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                    name="job_position" required>
                                    @foreach ($joblevel as $p)
                                        <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                            {{-- <div class="mb-4">
                                    <label class="block text-xs font-medium text-gray-700">Departement</label>
                                    <select id="departement_name_select" name="departement_name" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                                        @foreach ($departements as $dept)
                                            <option value="{{ $dept->deptname }}">{{ $dept->deptname }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}

                            <div class="mt-4">
                                <button type="submit"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Simpan</button>
                            </div>
                        </form>
                    </div>
                    <div id="editModal"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black bg-opacity-30">
                        <div class="relative w-full max-w-lg rounded-lg bg-white p-6">
                            <button onclick="closeEditModal()"
                                class="absolute right-2 top-2 text-lg text-gray-500">&times;</button>
                            <h3 class="mb-4 text-sm font-bold">Edit Employee</h3>
                            <form id="editEmployeeForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" id="edit_id">

                                <div class="mb-3">
                                    <label>Name</label>
                                    <input type="text" name="employee_name" id="edit_name"
                                        class="w-full rounded border p-2">
                                </div>
                                <div class="mb-3">
                                    <label>Company</label>
                                    {{-- <input type="text" name="employee_company" id="edit_company" class="w-full border p-2 rounded"> --}}
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
                                <div class="mb-3">
                                    <label>Position</label>
                                    {{-- <input type="text" name="employee_position" id="edit_position" class="w-full border p-2 rounded"> --}}
                                    <select
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="employee_position" id="edit_position">
                                        @foreach ($joblevel as $p)
                                            <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>New Image (optional)</label>
                                    <input type="file" name="image" class="w-full">
                                </div>
                                <button type="submit"
                                    class="rounded bg-blue-600 px-4 py-2 text-white">Update</button>
                            </form>
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

        d3.json("{{ route('orgchart.json') }}").then((data) => {
            chart = new d3.OrgChart()
                .nodeWidth((d) => {
                    return 300 + (d.data.members?.length || 0) * 10; // tambah lebar sesuai jumlah anggota
                })
                .nodeHeight((d) => {
                    return 100 + (d.data.members?.length || 0) * 25; // tambah tinggi per anggota
                })

                .childrenMargin((d) => 40)
                .compactMarginBetween((d) => 35)
                .compactMarginPair((d) => 30)
                .neighbourMargin((a, b) => 20)
                .nodeContent(function(d) {
                    const members = d.data.members || [];
                    return `

                    <div style='width:${d.width}px;height:${d.height}px;padding-top:25px;padding-left:1px;padding-right:1px'>
                        <div style="
                            background-color:#fff;

@@ -321,46 +386,45 @@
                                <strong>Employee:</strong>
                                <div style="margin-top:10px;">
                                    ${members.map(m => `
                                                                                                                                                                                                                                                                                <div style="display:flex;align-items:center;margin-bottom:6px;">
                                                                                                                                                                                                                                                                                    <img src="${m.image}" style="width:20px;height:20px;border-radius:50%;margin-right:8px;" />
                                                                                                                                                                                                                                                                                    <span style="font-size:12px;">${m.name} (${m.company} - ${m.position})</span>
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
                .render();
        });

        function openModal(id) {
            alert('Clicked node ID: ' + id); // ganti ini untuk buka modal
        }
    </script>


    <script>
        function openModal(approvalLineId) {
            // Set hidden input untuk semua form
            document.querySelectorAll('input[name="approval_line"]').forEach(el => el.value = approvalLineId);

            // Ambil data employee berdasarkan departemen
            $.ajax({
                url: `{{ url('/orgchart/employee/by-dept') }}/${approvalLineId}`,
                method: 'GET',
                success: function(response) {
                    let html = '';
                    response.forEach((emp, index) => {
                        html += `

                        <tr>
                            <td class="border   px-2 py-1">${index + 1}</td>
                            <td class="border   px-2 py-1">${emp.employee_name}</td>

@@ -370,287 +434,339 @@ function openModal(approvalLineId) {
                                ${emp.image ? `<img src="${emp.image}" class="w-15 h-15 rounded-full mx-auto">` : '-'}
                            </td>
                            <td class="border   px-2 py-1 text-center">
                                <div class="inline-flex gap-2">
                                    <!-- Edit Button -->
                                    <button
                                        class="btn-edit flex items-center gap-1 rounded bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-2 text-xs transition"
                                        title="Edit"
                                        data-id="${emp.id}"
                                        data-name="${emp.employee_name}"
                                        data-company="${emp.employee_company}"
                                        data-position="${emp.employee_position}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
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
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
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

                    // Tampilkan modal dan langsung buka tab View Employee
                    switchTab('view');
                    document.getElementById('modalForm').classList.remove('hidden');
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




        function refreshChart() {
            d3.json("{{ route('orgchart.json') }}").then((data) => {
                chart.data(data).render(); // update chart dengan data baru
            });
        }
    </script>

    <script>
        // function switchTab(tab) {
        //     document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('text-blue-600',
        //         'border-blue-600'));
        //     document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));

        //     document.getElementById(`tab-${tab}`).classList.remove('hidden');
        //     const activeBtn = [...document.querySelectorAll('.tab-button')].find(btn => btn.textContent.toLowerCase() ===
        //         tab);
        //     if (activeBtn) activeBtn.classList.add('text-blue-600', 'border-blue-600');
        // }

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
                    // alert('Departement berhasil disimpan!');
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
                    $('#hiddenFullName').val('Vacant').attr('name', 'full_name');
                    $('#fullNameGroup').hide();
                    $('#imageGroup').hide();
                    $('#qtyGroup').show();


                    $('#full_name').removeAttr('name');
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
            $('#edit_name').val($(this).data('name'));
            $('#edit_company').val($(this).data('company'));
            $('#edit_position').val($(this).data('position'));
            $('#editModal').removeClass('hidden');































        });

        function closeEditModal() {
            $('#editModal').addClass('hidden');
            $('#editEmployeeForm')[0].reset();
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
    </script>
    <script>
        $(document).on('click', '.btn-delete', function() {
            const id = $('#edit_id').val();

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
