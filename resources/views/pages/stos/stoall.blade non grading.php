<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 py-1 lg:px-2">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="stoForm" class="flex flex-col" enctype="multipart/form-data">
                            @csrf
                            <div class="flex justify-between rounded-t-2xl border-b bg-gray-50 p-4 dark:border-gray-600">
                                <h2 class="text-base font-bold">Organization Structure by Department</h2>
                                <div class="flex items-center gap-2">
                                    <label class="mb-1 block w-40 text-base font-semibold">Company</label>
                                    <select id="selectCompany"
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="company_filter">
                                        <option value="">All</option>
                                        @foreach ($companies as $c)
                                            <option value="{{ $c->cpnyid }}">{{ $c->cpnyid }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-2">
                                    <label class="mb-1 block w-40 text-base font-semibold">Department</label>
                                    <select id="selectdeptname"
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="departementid" required>                                   
                                        @foreach ($departements as $p)
                                            <option value="{{ $p->deptname }}" {{ $p->deptname == 'IT' ? 'selected' : '' }}>
                                                {{ $p->deptname }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                           
                            <div
                                class="flex w-full flex-col rounded-b-2xl border-b bg-white p-4 shadow-sm dark:bg-gray-800">
                                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row"> 
                                    {{-- <button class="rounded-lg bg-indigo-500 px-5 py-2 text-white" onclick="chart.exportImg()">Export Image</button> --}}
                                    <button type="button" class="rounded-lg bg-indigo-500 px-5 py-2 text-white" onclick="exportOrgChartImage()">Export Image</button>

                                </div>
                                <div class="chart-container h-[80vh]" style="width: 100%;"></div>
                                
                            </div>

                        </form>
                    </div>
                    <div id="modalForm"
                        class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-500/10 bg-opacity-50 backdrop-blur-md">
                        <div class="relative w-full max-w-5xl rounded-lg bg-white p-4">
                            <div class="border-gray-200s mb-4 flex justify-between border-b">
                                <ul class="  flex flex-wrap text-center text-xs font-medium" id="tabs">
                                    <li class="mr-2">
                                        <button type="button"
                                            class="tab-button border-blue-600 px-4 py-2 text-sm text-blue-600"
                                            onclick="switchTab('view')">View Employee</button>
                                    </li>                                    
                                </ul>
                                <button onclick="closeModal()" class="text-sm text-gray-500">close</button>

                            </div>

                            <!-- Tab Content: View Employee -->
                            <div id="tab-view" class="tab-content hidden">
                                <div class="flex justify-between">
                                    <h3 class="text-sm font-semibold">Employee List</h3>
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 id="departmentLabel" class="text-sm font-semibold text-gray-800">
                                            Dept: <!-- Dynamic text will be inserted via JS -->
                                        </h4>                                       
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

                            <div id="modalJobProfile" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-500/10 backdrop-blur-md">
                                <div class="w-full max-w-4xl rounded-lg bg-white p-6    overflow-y-auto max-h-[90vh]">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-sm font-semibold">
                                            Job Profile - <span id="jobLevelLabel" class="text-blue-600 font-semibold"></span>
                                        </h3>
                                        <button onclick="$('#modalJobProfile').addClass('hidden')" class="text-gray-600 hover:text-red-600 text-base">&times;</button>
                                    </div>

                                    <div class="mb-4">
                                        <table class="w-full border   text-xs">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="border   px-2 py-1">No</th>                                           
                                                    <th class="border   px-2 py-1">Job Purpose</th>   
                                                </tr>
                                            </thead>
                                            <tbody id="jobProfileBody"></tbody>
                                        </table>
                                    </div>

                                    <div id="jobSpecInfo" class="text-xs text-gray-700 space-y-2">
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
        var chart = null;
        $('select[name="departementid"], #selectCompany').on('change', function() {
            const deptname = encodeURIComponent($('#selectdeptname').val());
            const company = encodeURIComponent($('#selectCompany').val());
      
            $('.chart-container').html(
                '<div class="text-center text-gray-400 mt-10 animate-pulse">Loading...</div>');

            $.ajax({
                url: `/orgchart/by-dept/${deptname}?company=${company}`,
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
                        .nodeContent(function (d) {
                            const members = d.data.members || [];
                            const level = d.depth;
                            const bgColor = level === 0 ? '#e3f2fd' : level === 1 ? '#e8f5e9' : level === 2 ? '#fff3e0' : level === 3 ? '#fce4ec' : '#f5f5f5';

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
                                        <div style="font-size:18px;color:#08011E;margin-bottom:5px">${d.data.name}</div>                           
                                        <div style="font-size:12px;color:#333">                                    
                                            <div style="margin-top:10px;">
                                                ${members.map(m => `
                                                    <div style="display:flex;align-items:center;margin-bottom:6px;">
                                                        <img src="${m.image}" style="width:30px;height:30px;border-radius:50%;margin-right:8px;" />
                                                        <span style="font-size:12px; color:${m.name.toUpperCase() === 'VACANT' ? 'red' : '#000'};">
                                                            ${m.name} (${m.company} - ${m.position})
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
                        .data(nodes)              // ✅ ini yang benar
                        .expandAll()              // ⛔ HARUS setelah .data([...])
                        .render();

                    // Tambahkan garis tambahan
                    chart.connections(connections).render();

                    
                },

                error: function(xhr) {
                    $('.chart-container').empty(); // ❗bersihkan chart sebelumnya

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
        $(document).ready(function() {
           
            $('#selectdeptname').select2({
                placeholder: "Pilih Departement Name...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

     <script>   
        $(document).ready(function () {
            // Tombol Add Purpose
            $('#addJobPurpose').on('click', function () {
                $('#jobPurposeList').append(`
                    <div class="flex gap-2">
                        <textarea name="job_purpose[]" class="w-full border rounded p-2" placeholder="Deskripsikan tujuan pekerjaan" required></textarea>
                        <button type="button" class="removePurpose text-red-600">🗑️</button>
                    </div>
                `);
                toggleRemoveButtons();
            });

            // Tombol Remove Purpose
            $(document).on('click', '.removePurpose', function () {
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
        $(document).on('click', '.btn-profile', function () {
            const empId = $(this).data('id');

            $.ajax({
                url: `/orgchart/job-profile/${empId}`,
                method: 'GET',
                success: function (res) {
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
                    $('#jobLevelLabel').text(spec.job_level || '');


                    $('#jobSpecInfo').html(`
                        <h4 class="font-semibold">Job Spec Detail:</h4>                       
                        <p><strong>Education:</strong> ${spec.education_min || ''} - ${spec.education_jurusan || ''}</p>
                        <p><strong>Experience:</strong> ${spec.experience_min || ''} years as ${spec.experience_position || ''}</p>
                    `);

                    $('#modalJobProfile').removeClass('hidden');
                },
                error: function () {
                    toastr.error('Gagal memuat job profile.');
                }
            });
        });

    </script>
    <script>
        function exportOrgChartImage() {
            if (chart) {
                chart.exportImg(); // ✅ panggil method bawaan d3-org-chart
            } else {
                alert("Chart belum dimuat!");
            }
        }
    </script>



</x-app-layout>
