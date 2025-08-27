<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'manpowers' ? 'HR' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-2 py-0 sm:px-6 lg:px-2">
        <!-- Dashboard actions -->
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <!-- Breadcrumb dengan Dropdown -->

        <div class="grid grid-cols-12 gap-4">
            <style>
                .no-border {
                    border: none !important;
                }

                .grid {
                    width: 100%;
                }

                select,
                textarea,
                input {
                    width: 100%;
                    /* Make all input elements take full width */
                }

                table.dataTable {
                    width: 100% !important;
                }

                .dataTables_wrapper {
                    width: 100%;
                }

                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }

                #manpowersTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    /* Aligns items to the left */
                    align-items: center;
                    /* Vertically aligns items */
                }

                #manpowersTable_filter label {
                    margin-right: 2px;
                }

                #manpowersTable_filter input {
                    width: 200px;
                    /* Adjust the width of the input box */
                }


                #manpowersTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }

                /* Prevent text from wrapping */
                #manpowersTable td {
                    white-space: nowrap;
                    /* Prevent text from wrapping */
                    overflow: hidden;
                    /* Hide overflow content */
                    text-overflow: ellipsis;
                    /* Display ellipsis ("...") for overflowing content */
                }

                /* Optional: Adjust the width for table cells */
                #manpowersTable th,
                #manpowersTable td {
                    padding: 10px;
                    /* Adjust padding for better appearance */
                    max-width: 200px;
                    /* You can set a maximum width to control overflow */
                }


                #manpowersTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #manpowersTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #manpowersTable_length select option {
                    padding: 5px;
                    /* Mengatur jarak antar opsi */
                }

                #manpowersTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    margin-top: 10px;
                    margin-bottom: 10px;

                }

                #manpowersTable tbody tr td {
                    padding: 8px 8px;
                    /* Adjust padding for uniform height */
                    line-height: 2;
                    /* Optional, for better text alignment */
                }

                #manpowersTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #manpowersTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #manpowersTable tbody tr:hover td {
                    /* color: black; */
                }
            </style>
            <style>
                /* ✅ Custom Switch Button */
                .switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }

                .switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }

                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 34px;
                }

                .slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }

                input:checked+.slider {
                    background-color: #4CAF50;
                }

                input:checked+.slider:before {
                    transform: translateX(18px);
                }

                /* ✅ Memperkecil Lebar Kolom Actions */
                #manpowersTable th:nth-child(1),
                #manpowersTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #manpowersTable th:nth-child(4),
                #manpowersTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            <div class="col-span-12 lg:col-span-9">
                <div class="chart-container" style="width: 100%; height: 800px;"></div>




                <!-- Modal dengan Tab -->
                <div id="modalForm"
                    class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black bg-opacity-30">
                    <div class="relative w-full max-w-xl rounded-lg bg-white p-6">
                        <button onclick="closeModal()"
                            class="absolute right-2 top-2 text-2xl text-gray-500">&times;</button>

                        <!-- Tab -->
                        <div class="mb-4 border-b border-gray-200">
                            <ul class="-mb-px flex flex-wrap text-center text-sm font-medium" id="tabs">
                                <li class="mr-2">
                                    <button type="button"
                                        class="tab-button border-b-2 border-blue-600 px-4 py-2 text-blue-600"
                                        onclick="switchTab('view')">View Employee</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button"
                                        class="tab-button px-4 py-2 text-gray-600 hover:border-blue-600 hover:text-blue-600"
                                        onclick="switchTab('employee')">Add Employee</button>
                                </li>
                                <li class="mr-2">
                                    <button type="button"
                                        class="tab-button px-4 py-2 text-gray-600 hover:border-blue-600 hover:text-blue-600"
                                        onclick="switchTab('departement')">Add Departement</button>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Content: View Employee -->
                        <div id="tab-view" class="tab-content hidden">
                            <h3 class="mb-4 text-lg font-semibold">Employee List</h3>
                            <table class="w-full border border-gray-200 bg-blue-300 text-sm text-black">
                                <thead>
                                    <tr class="text-left">
                                        <th class="border border-gray-200 px-2 py-1">No</th>
                                        <th class="border border-gray-200 px-2 py-1">Name</th>
                                        <th class="border border-gray-200 px-2 py-1">Company</th>
                                        <th class="border border-gray-200 px-2 py-1">Jabatan</th>
                                        <th class="border border-gray-200 px-2 py-1">Foto</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody">

                                </tbody>
                            </table>
                        </div>

                        <!-- Tab Content: Employee -->
                        <div id="tab-employee" class="tab-content">
                            <h3 class="mb-4 text-lg font-semibold">Add Employee</h3>
                            <form id="formAddEmployee" method="POST" action="{{ route('orgchart.store') }}">
                                @csrf
                                <input type="hidden" name="approval_line" id="modalApprovalLine">
                                <input type="hidden" name="full_name" value="Vacant">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Company</label>
                                    <select
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="cpnyid" required>
                                        @foreach ($companies as $p)
                                            <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Position</label>
                                    <select
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="job_position" required>
                                        @foreach ($joblevel as $p)
                                            <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Qty</label>
                                    <input type="number" name="qty"
                                        class="mt-1 block w-full rounded-md border border-gray-300 p-2" value="1"
                                        required>
                                </div>
                                {{-- <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">URL Foto</label>
                                    <input type="url" name="avatar_local" placeholder="https://..." class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                </div> --}}
                                <input type="hidden" name="status_talenta" value="Active">
                                <div class="mt-4">
                                    <button type="submit"
                                        class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Simpan</button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab Content: Departement -->
                        <div id="tab-departement" class="tab-content hidden">
                            <h3 class="mb-4 text-lg font-semibold">Add Departement</h3>
                            <form id="formAddDepartement" method="POST" action="{{ route('orgchart.store') }}">
                                @csrf
                                <input type="hidden" name="approval_line" id="modalApprovalLine">
                                {{-- <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Departement</label>
                                    <input type="text" name="departement_name" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                                </div> --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Departement</label>
                                    <select id="departement_name_select" name="departement_name"
                                        class="mt-1 block w-full rounded-md border border-gray-300 p-2" required>
                                        @foreach ($departements as $dept)
                                            <option value="{{ $dept->deptname }}">{{ $dept->deptname }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mt-4">
                                    <button type="submit"
                                        class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-3">
                <div class="rounded bg-white p-4 shadow">
                    <h3 class="mb-4 text-lg font-semibold">Header Info</h3>
                    <form id="headerForm" method="POST" action="#">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">STO ID</label>
                            <input type="text" name="sto_id"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2"
                                placeholder="STO ID" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="sto_date"
                                class="mt-1 block w-full rounded-md border border-gray-300 p-2"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Created</label>
                            <input type="text" name="user"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2"
                                placeholder="Created" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <input type="text" name="status"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-100 p-2"
                                placeholder="Status" readonly>
                        </div>
                        <div class="mt-6 flex justify-between gap-2">
                            <button type="submit" name="action" value="draft"
                                class="w-1/2 rounded bg-gray-600 py-2 text-white hover:bg-gray-700">
                                Simpan Draft
                            </button>
                            <button type="submit" name="action" value="submit"
                                class="w-1/2 rounded bg-blue-600 py-2 text-white hover:bg-blue-700">
                                Submit
                            </button>
                        </div>
                    </form>
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
                                    width:${d.width - 2}px;
                                    height:${d.height - 25}px;
                                    border-radius:10px;
                                    border:1px solid #E4E2E9;
                                    padding:15px;
                                    overflow:visible;
                                ">
                                    <div style="font-size:18px;color:#08011E;margin-bottom:5px">${d.data.name}</div>                           
                                    <div style="font-size:12px;color:#333">
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
                                    <td class="border border-gray-200 px-2 py-1">${index + 1}</td>
                                    <td class="border border-gray-200 px-2 py-1">${emp.employee_name}</td>
                                    <td class="border border-gray-200 px-2 py-1">${emp.employee_company}</td>
                                    <td class="border border-gray-200 px-2 py-1">${emp.employee_position}</td>
                                    <td class="border border-gray-200 px-2 py-1 text-center">
                                        ${emp.image ? `<img src="${emp.image}" class="w-15 h-15 rounded-full mx-auto">` : '-'}
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
                    const formData = form.serialize();

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: formData,
                        success: function(response) {
                            closeModal(); // tutup modal
                            refreshChart(); // reload chart
                            alert('Data berhasil disimpan!');
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
                function switchTab(tab) {
                    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('text-blue-600',
                        'border-blue-600'));
                    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));

                    document.getElementById(`tab-${tab}`).classList.remove('hidden');
                    const activeBtn = [...document.querySelectorAll('.tab-button')].find(btn => btn.textContent.toLowerCase() ===
                        tab);
                    if (activeBtn) activeBtn.classList.add('text-blue-600', 'border-blue-600');
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
                            alert('Departement berhasil disimpan!');
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('Gagal menyimpan departement!');
                        }
                    });
                });
            </script>

            <script>
                $('#headerForm').submit(function(e) {
                    e.preventDefault();

                    let form = $(this);
                    let formData = form.serialize();
                    let actionType = form.find('button[type=submit][clicked=true]').val();

                    $.ajax({
                        type: 'POST',
                        url: "{{ route('sto.storehd') }}",
                        data: formData + '&action=' + actionType,
                        success: function(response) {
                            alert(response.message);
                            // bisa refresh atau redirect jika perlu
                        },
                        error: function(xhr) {
                            alert('Gagal menyimpan!');
                            console.error(xhr);
                        }
                    });
                });

                // Tangkap tombol mana yang diklik
                $('#headerForm button[type=submit]').click(function() {
                    $('#headerForm button[type=submit]').removeAttr('clicked');
                    $(this).attr('clicked', 'true');
                });
            </script>






        </div>
    </div>
</x-app-layout>
