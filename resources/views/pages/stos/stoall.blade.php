<x-app-layout>
    {{-- <style>
        #chartLegend {
  position: absolute;
  right: 36px;
  bottom: 36px;
  z-index: 10;
}

    </style> --}}
    <div class="max-w-9xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6">
            <form id="stoForm" class="flex flex-col rounded-xl bg-white dark:bg-gray-800" enctype="multipart/form-data">
                @csrf
                {{-- Form Header with Title and Filters --}}
                <div
                    class="flex flex-col gap-4 rounded-t-xl border-b border-gray-200 bg-gray-50 p-4 lg:flex-row lg:items-center lg:justify-between dark:border-gray-700 dark:bg-gray-700">
                    <h2 class="flex items-center gap-2 text-base font-bold text-gray-800 dark:text-gray-100">
                        <span class="text-blue-500">🏢</span> Organization Structure by Department
                    </h2>
                    <div class="flex flex-col items-start gap-4 md:flex-row md:items-end"> {{-- Filters container --}}
                        <div class="flex items-center gap-2">
                            <label for="selectCompany"
                                class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-100">Company:</label>
                            <select id="selectCompany"
                                class="w-full min-w-[150px] rounded-lg border border-gray-300 bg-white p-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                name="company_filter">
                                <option value="">All</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c->cpnyid }}">{{ $c->cpnyid }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <label for="selectdeptname"
                                class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-100">Department:</label>
                            <select id="selectdeptname"
                                class="w-full min-w-[200px] rounded-lg border border-gray-300 bg-white p-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                name="departementid" required>
                                <option value="">All</option>
                                @foreach ($departements as $p)
                                    <option value="{{ $p->deptname }}" {{ $p->deptname == 'IT' ? 'selected' : '' }}>
                                        {{ $p->deptname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Chart and Export Button Section --}}
                <div class="flex w-full flex-col rounded-b-xl bg-white p-4 dark:bg-gray-800"> {{-- Removed 'shadow' from here --}}
                    <div class="mb-6 flex justify-end"> {{-- Aligns button to the right --}}
                        <button type="button"
                            class="hover: inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-md transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            onclick="chart.exportImg({full:true})">Export Image Full</button>
                    </div>
                    {{-- <button onclick="chart.exportImg({full:true})">Export Full</button> --}}

                    {{-- <div id="chartExportArea" style="position:relative;"> --}}
                    <div id="chartExportArea" class="min-h[1100px] relative bg-gray-50 pb-[200px] dark:bg-gray-700">
                        {{-- <div class="chart-container w-full" style="width:100%; min-height:420px;"></div> --}}
                        <div class="chart-container w-full" style="width: 100%;"></div>
                        <!-- Legend di pojok kanan bawah -->
                        {{-- <div id="chartLegend2" style="position:absolute; left:32px; bottom:32px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #cefefe; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Crew</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #c6d4df; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Staff</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #bdb9c9; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Senior Staff</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #e6d0dd; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Supervisor</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #97d077; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Assistant Manager / Chief</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #effbfe; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Head of Dept</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #c7ffbb; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Head of Division</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="display: inline-block; width: 28px; height: 18px; background: #ddebf6; border-radius: 4px; border:1px solid #ccc"></span>
                                <span>Executive</span>
                            </div>
                        </div> --}}
                        {{-- <div id="chartLegend"
                            style="
                            position: absolute;
                            right: 16px;
                            bottom: 16px;
                            z-index: 10;
                            display: flex;
                            flex-direction: column;
                            gap: 8px;
                            padding: 16px;
                            border-radius: 12px;
                            background: #fff;
                            box-shadow: 0 2px 8px #0001;
                            width: 260px;
                            border: 1px solid #eee;
                        ">
                        </div> --}}
                    </div>


                </div>
            </form>

            {{-- Success Message --}}
            <div id="successMessage"
                class="mt-4 hidden rounded-lg bg-green-50 p-3 font-bold text-green-600 shadow-md dark:bg-green-900 dark:text-green-200">
                Sto Created Successfully!
            </div>

        </div>

        {{-- Modal Form (View Employee) --}}
        <div id="modalForm"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4 transition-opacity duration-300">
            <div class="relative w-full max-w-5xl rounded-xl bg-white p-8 shadow-xl dark:bg-gray-700">
                {{-- Modal Header with Tabs and Close Button --}}
                <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-600">
                    <ul class="-mb-px flex flex-wrap text-center text-xs font-medium" id="tabs">
                        <li class="mr-2">
                            <button type="button"
                                class="tab-button inline-flex items-center rounded-t-lg border-b-2 border-blue-600 px-4 py-2 text-sm font-semibold text-blue-600 transition-colors duration-200 dark:border-blue-500 dark:text-blue-400"
                                onclick="switchTab('view')">View Employee</button>
                        </li>
                        {{-- Add other tabs here if needed --}}
                    </ul>
                    <button onclick="closeModal()" class="text-base text-gray-500 transition-colors hover:text-red-600">
                        &times; {{-- Close icon --}}
                    </button>
                </div>

                {{-- Tab Content: View Employee --}}
                <div id="tab-view" class="tab-content"> {{-- Removed 'hidden' as it will be controlled by JS --}}
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white">Employee List</h3>
                        <h4 id="departmentLabel" class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Dept: </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="employeeTable">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                        No</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                        Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                        Company</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                        Position</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                        Photo</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"
                                id="employeeTableBody">
                                {{-- Employee data will be loaded here by JS --}}
                                <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td colspan="6"
                                        class="py-4 text-center text-xs italic text-gray-500 dark:text-gray-400">Loading
                                        employees...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Nested Modal: Job Profile --}}
                <div id="modalJobProfile"
                    class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4 transition-opacity duration-300">
                    <div
                        class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-8 shadow-xl dark:bg-gray-700">
                        <div
                            class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-600">
                            <h3 class="text-base font-bold text-gray-800 dark:text-white">
                                Job Profile - <span id="jobLevelLabel"
                                    class="font-bold text-blue-600 dark:text-blue-400"></span>
                            </h3>
                            <button onclick="$('#modalJobProfile').addClass('hidden')"
                                class="text-base text-gray-500 transition-colors hover:text-red-600">&times;</button>
                        </div>

                        <div class="mb-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                No</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Job Purpose</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"
                                        id="jobProfileBody">
                                        {{-- Job Purpose details will be injected here by JS --}}
                                        <tr
                                            class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td colspan="2"
                                                class="py-4 text-center text-xs italic text-gray-500 dark:text-gray-400">
                                                Loading job purposes...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="jobSpecInfo"
                            class="space-y-4 rounded-lg bg-gray-50 p-4 text-xs text-gray-700 shadow-inner dark:bg-gray-700 dark:text-gray-200">
                            {{-- Job Spec details will be injected here by JS --}}
                            <p class="text-center italic text-gray-500 dark:text-gray-400">Job specification details
                                will appear here.</p>
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
                    placeholder: "Select",
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
                    '<div class="text-center text-gray-400 mt-10 animate-pulse">Loading...</div>'
                );

                $.ajax({
                    url: `/orgchart/by-dept/${deptname}?company=${company}`,
                    method: 'GET',
                    success: function(data) {
                        const nodes = data.nodes || [];
                        const connections = data.connections || [];

                        // // ==== GENERATE LEGEND ====
                        // let legendArr = [];
                        // nodes.forEach(d => {
                        //     (d.members || []).forEach(m => {
                        //         legendArr.push({
                        //             department: d.name,
                        //             company: m.company
                        //         });
                        //     });
                        // });

                        // let legendMap = {};
                        // legendArr.forEach(row => {
                        //     let key = row.department + '-' + row.company;
                        //     if (!legendMap[key]) {
                        //         legendMap[key] = { department: row.department, company: row.company, count: 0 };
                        //     }
                        //     legendMap[key].count++;
                        // });

                        // let legendList = Object.values(legendMap);

                        // let legendHTML = `
                // <div class="p-2 rounded border bg-white shadow" style="display:inline-block; min-width:260px">
                //     <div class="font-bold mb-1">Legend:</div>
                //     <table class="text-xs">
                //     <thead>
                //         <tr>
                //         <th class="pr-4">Department</th>
                //         <th class="pr-4">Company</th>
                //         <th>Jumlah</th>
                //         </tr>
                //     </thead>
                //     <tbody>
                //         ${legendList.map(item => `
                        //         <tr>
                        //             <td class="pr-4">${item.department}</td>
                        //             <td class="pr-4">${item.company}</td>
                        //             <td>${item.count}</td>
                        //         </tr>
                        //         `).join('')}
                //     </tbody>
                //     </table>
                // </div>
                // `;

                        // $('#chartLegend').html(legendHTML);
                        // // ==== END LEGEND ====
                        // ==== LEGEND PER COMPANY ====
                        let companyMap = {};
                        nodes.forEach(d => {
                            (d.members || []).forEach(m => {
                                if (!companyMap[m.company]) {
                                    companyMap[m.company] = 0;
                                }
                                companyMap[m.company]++;
                            });
                        });

                        let legendCompany = Object.entries(companyMap).map(([company, count]) => ({
                            company,
                            count
                        }));

                        let legendHTML = `
                        <div class="p-2 rounded border bg-white shadow" style="display:inline-block; min-width:160px">
                            <div class="font-bold mb-1">Legend (Company):</div>
                            <table class="text-xs">
                            <thead>
                                <tr>
                                <th class="pr-4">Company</th>
                                <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${legendCompany.map(item => `
                                                                                                                                                                                                                                                                                                                                        <tr>
                                                                                                                                                                                                                                                                                                                                            <td class="pr-4">${item.company}</td>
                                                                                                                                                                                                                                                                                                                                            <td>${item.count}</td>
                                                                                                                                                                                                                                                                                                                                        </tr>
                                                                                                                                                                                                                                                                                                                                        `).join('')}
                            </tbody>
                            </table>
                        </div>
                        `;

                        $('#chartLegend').html(legendHTML);
                        // ==== END LEGEND ====

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
                            .childrenMargin((d) => 60)
                            .compactMarginBetween((d) => 35)
                            .compactMarginPair((d) => 30)
                            .neighbourMargin((a, b) => 20)
                            .nodeContent(function(d) {
                                const members = d.data.members || [];
                                const level = d.depth;
                                const bgColor = d.data.bgColor || '#f5f5f5';

                                console.log('Level:', level); // Debugging line
                                console.log('Node Width:', d.width, 'Height:', d
                                    .height); // Debugging line
                                console.log('Node Data:', d.data); // Debugging line
                                return `
                                    <div style='width:${d.width}px;height:${d.height}px;padding-top:25px;padding-left:25px;padding-right:10px'>
                                        <div style="
                                            background-color:${bgColor};
                                            width:${d.width - 50}px;
                                            height:${d.height - 25}px;
                                            border-radius:20px;
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
                                                                                                                                                                                                                                                                                                                                                                <div style="display:flex;align-items:center;margin-bottom:2px;">
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
                            .linkUpdate((d, i, arr) => {
                                d3.select(arr[i])
                                    .attr('stroke-width', 2) // tebal garis parent-child
                                    .attr('stroke', '#374151'); // opsional: warna
                            })
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
            $(document).ready(function() {
                $('#selectdeptname').select2({
                    placeholder: "Select",
                    allowClear: true,
                    width: 'resolve',
                    dropdownAutoWidth: true
                });
                setTimeout(function() {
                    $("#selectdeptname").next('.select2-container').css('min-width', '200px');
                }, 0);

                // Aktifkan select2 untuk Company
                $('#selectCompany').select2({
                    placeholder: "Select",
                    allowClear: true,
                    width: 'resolve',
                    dropdownAutoWidth: true
                });
                setTimeout(function() {
                    $("#selectCompany").next('.select2-container').css('min-width', '150px');
                }, 0);
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
                        $('#jobLevelLabel').text(spec.job_level || '');


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


        <script src="https://unpkg.com/html2canvas@1.1.4/dist/html2canvas.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>


        <script>
            function exportChartWithLegend() {
                const exportArea = document.getElementById('chartExportArea');
                const images = exportArea.querySelectorAll('img');
                let loaded = 0;
                if (images.length === 0) {
                    doExport();
                    return;
                }
                images.forEach(img => {
                    if (img.complete) {
                        loaded++;
                        if (loaded === images.length) doExport();
                    } else {
                        img.onload = () => {
                            loaded++;
                            if (loaded === images.length) doExport();
                        }
                        img.onerror = () => {
                            loaded++;
                            if (loaded === images.length) doExport();
                        }
                    }
                });

                function doExport() {
                    html2canvas(exportArea, {
                        backgroundColor: '#fff',
                        scale: 2,
                        useCORS: true
                    }).then(function(canvas) {
                        const link = document.createElement('a');
                        link.href = canvas.toDataURL('image/png');
                        link.download = 'orgchart-export.png';
                        link.click();
                    });
                }
            }
        </script>

</x-app-layout>
