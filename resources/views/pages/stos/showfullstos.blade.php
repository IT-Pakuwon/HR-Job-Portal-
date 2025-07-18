<x-app-layout>
    <div class="max-w-9xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid">
            <div class="mx-auto w-full px-2 py-1 sm:px-6 lg:px-2">
                <div class="gap-1">
                    <div
                        class="flex w-full flex-col gap-2 overflow-hidden sm:col-span-1 lg:row-span-1 xl:row-span-1 xl:flex-row">
                        <div class="flex flex-row gap-4 sm:w-1/2 md:w-full">
                            <div class="flex w-full flex-col rounded-2xl bg-white shadow-sm dark:bg-gray-800">

                                <!-- Main Content -->
                                <div>
                                    {{-- <div class="chart-container h-[80vh]" style="width: 100%;"></div> --}}
                                    <div class="chart-container"></div>
                                    <div id="modalForm"
                                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50 backdrop-blur-md">
                                        <div class="relative w-full max-w-xl rounded-lg bg-white p-4">
                                            <div class="border-gray-200s mb-4 flex justify-between border-b">
                                                <ul class="text-md flex flex-wrap text-center font-medium"
                                                    id="tabs">
                                                    <li class="">
                                                        <button type="button"
                                                            class="tab-button py-2 font-semibold text-blue-600"
                                                            onclick="switchTab('view')">View Employee</button>
                                                    </li>
                                                </ul>
                                                <button onclick="closeModal()"
                                                    class="text-lg text-gray-500">close</button>

                                            </div>

                                            <!-- Tab Content: View Employee -->
                                            <div id="tab-view" class="tab-content hidden">
                                                <div class="flex justify-between">
                                                    <h3 class="mb-4 text-lg font-semibold">Employee List</h3>
                                                    <h4 id="departmentLabel" class="mb-4 text-lg font-semibold">
                                                    </h4>
                                                </div>

                                                <table
                                                    class="w-full border border-black bg-gray-300/10 text-sm text-black">
                                                    <thead>
                                                        <tr class="text-left">
                                                            <th class="border border-black px-2 py-1">No</th>
                                                            <th class="border border-black px-2 py-1">Name</th>
                                                            <th class="border border-black px-2 py-1">Company</th>
                                                            <th class="border border-black px-2 py-1">Jabatan</th>
                                                            <th class="border border-black px-2 py-1">Foto</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="employeeTableBody">

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
            <div id="loadingSpinnerContainer" class="flex h-16 items-center justify-center">
                <svg class="h-10 w-10 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>

            <div id="rejectTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-700">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Reject Task</h2>
                    <textarea id="rejectReason"
                        class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                        placeholder="Enter rejection reason..."></textarea>

                    <div class="mt-4 flex justify-between">
                        <button id="cancelRejectBtn"
                            class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                            Cancel
                        </button>
                        <button id="confirmRejectBtn"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                            Reject
                        </button>
                    </div>
                </div>
            </div>
            <div id="reviseTaskModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-700">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Revise Task</h2>
                    <textarea id="reviseReason"
                        class="mt-2 w-full rounded-lg border p-3 focus:outline-none dark:bg-gray-800 dark:text-white"
                        placeholder="Enter revise reason..."></textarea>

                    <div class="mt-4 flex justify-between">
                        <button id="cancelReviseBtn"
                            class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                            Cancel
                        </button>
                        <button id="confirmReviseBtn"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white hover:bg-red-600">
                            Revise
                        </button>
                    </div>
                </div>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

            <!-- Toastr CSS -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
            <!-- Toastr JS -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
            <script>
                function checkApproval(docid, action) {
                    console.log(docid, '-', action);
                    $.ajax({
                        url: `/sto/${docid}/check-approval/${action}`,
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
                                    //     approveSto(docid); // Jika approve, langsung jalankan proses approval
                                }
                            } else {
                                // Jika user tidak boleh melakukan aksi, tampilkan popup toastr
                                toastr.error("You are not authorized to " + action + " this sto.");
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

            <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/lucide.min.js"></script>

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
                    alert('Clicked node ID: ' + id); // ganti ini untuk buka modal
                }
            </script>


            <script>
                function openModal(id) {
                    currentDeptId = id;
                    document.querySelectorAll('input[name="approval_line"]').forEach(el => el.value = id);

                    $.ajax({
                        url: `{{ url('/orgchart/employee/by-dept') }}/${id}`,
                        method: 'GET',
                        success: function(response) {
                            const employees = response.employees || [];
                            const deptName = response.departement_name || '-';

                            // Set label di atas tabel
                            const capitalizedDeptName = deptName.charAt(0).toUpperCase() + deptName.slice(1)
                                .toLowerCase();
                            $('#departmentLabel').text(`Dept: ${capitalizedDeptName}`);

                            let html = '';
                            employees.forEach((emp, index) => {
                                html += `
                                    <tr>
                                        <td class="border border-black px-2 py-1">${index + 1}</td>
                                        <td class="border border-black px-2 py-1">${emp.employee_name}</td>
                                        <td class="border border-black px-2 py-1">${emp.employee_company}</td>
                                        <td class="border border-black px-2 py-1">${emp.employee_level}</td>
                                        <td class="border border-black px-2 py-1 text-center">
                                            ${emp.image ? `<img src="${emp.image}" class="w-15 h-15 rounded-full mx-auto">` : '-'}
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
                    const formData = form.serialize();

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: formData,
                        success: function(response) {
                            closeModal(); // tutup modal
                            refreshChart(); // reload chart
                            // alert('Data berhasil disimpan!');
                            toastr.success("Add Vacant Successfully!");
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






</x-app-layout>
