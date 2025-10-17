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
                                    <div class="chart-container"></div>
                                    <div id="modalForm"
                                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50 backdrop-blur-md">
                                        <div class="relative w-[95vw] max-w-6xl rounded-lg bg-white p-6 md:w-auto">
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

                                            <div id="tab-view" class="tab-content hidden">
                                                <div class="flex justify-between">
                                                    <h3 class="mb-4 text-lg font-semibold">Employee List</h3>
                                                    <h4 id="departmentLabel" class="mb-4 text-lg font-semibold">
                                                    </h4>
                                                </div>
                                                <div class="overflow-y-auto" style="max-height: 500px;">
                                                    <table class="w-full border bg-gray-300/10 text-sm text-black">
                                                        <thead>
                                                            <tr class="text-left">
                                                                <th class="border px-2 py-1">No</th>
                                                                <th class="border px-2 py-1">Name</th>
                                                                <th class="border px-2 py-1">Company</th>
                                                                <th class="border px-2 py-1">Jabatan</th>
                                                                <th class="border px-2 py-1">Foto</th>
                                                                <th class="border px-2 py-1">Action</th>
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
                <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
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
                <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-700">
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

            <div id="modalJobProfile"
                class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/40 backdrop-blur-sm">
                <div
                    class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
                    <div
                        class="mb-4 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
                            Job Profile <span id="jobLevelLabel"
                                class="font-bold text-indigo-600 dark:text-indigo-400"></span>
                        </h3>
                        <button onclick="$('#modalJobProfile').addClass('hidden')"
                            class="text-2xl leading-none text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            &times;
                        </button>
                    </div>

                    <div class="mb-6 overflow-x-auto rounded-lg border border-gray-200 shadow-sm dark:border-gray-700">
                        <table
                            class="min-w-full divide-y divide-gray-200 text-sm text-gray-800 dark:divide-gray-700 dark:text-gray-200">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                        No</th>
                                    <th scope="col"
                                        class="border-r border-gray-200 px-4 py-2 text-left font-semibold tracking-wider dark:border-gray-600">
                                        Job Purpose</th>

                                </tr>
                            </thead>
                            <tbody id="jobProfileBody"
                                class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            </tbody>
                        </table>
                    </div>

                    <div id="jobSpecInfo" class="space-y-3 text-base text-gray-700 dark:text-gray-300">
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

                d3.json("{{ route('orgchartShow.json', ['sto' => $sto->id]) }}").then((res) => {
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
                                            ? `<div style="font-size:18px;color:#08011E;margin-bottom:5px;"><strong>${d.data.name} ${d.data.position}</strong></div>`
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
                        // .disableZoom()
                        .linkUpdate((d, i, arr) => {
                            d3.select(arr[i])
                                .attr('stroke-width', 2) // tebal garis parent-child
                                .attr('stroke', '#374151'); // opsional: warna
                        })
                        .render();

                    chart.connections(connections).render();
                    chart.expandAll().fit()
                    setTimeout(() => {
                        d3.select(".chart-container svg")
                            .on("wheel.zoom", null)
                            .on("mousedown.zoom", null)
                            .on("touchstart.zoom", null)
                            .on("touchmove.zoom", null)
                            .on("touchend.zoom", null)
                            .on("dblclick.zoom", null);
                    }, 100);

                });

                function openModal(id) {
                    alert('Clicked node ID: ' + id); // ganti ini untuk buka modal
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
                    d3.json("{{ route('orgchartShow.json', ['sto' => $sto->id]) }}").then((data) => {
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





</x-app-layout>
