<x-app-layout>
    <div class="max-w-9xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6">
            <form id="stoForm" class="flex flex-col rounded-xl bg-white shadow-lg dark:bg-gray-800"
                enctype="multipart/form-data">
                @csrf
                {{-- Form Header with Title and Filters --}}
                <div
                    class="flex flex-col gap-4 rounded-t-xl border-b border-gray-200 bg-gray-50 p-6 lg:flex-row lg:items-center lg:justify-between dark:border-gray-700 dark:bg-gray-700">
                    <h2 class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-gray-100">
                        <span class="text-blue-500">🏢</span> Organization Structure by Department
                    </h2>
                    <div class="flex flex-col items-start gap-4 md:flex-row md:items-end"> {{-- Filters container --}}
                        <div class="flex items-center gap-2">
                            <label for="selectCompany"
                                class="mb-1 block text-lg font-semibold text-gray-700 dark:text-gray-300">Company:</label>
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
                                class="mb-1 block text-lg font-semibold text-gray-700 dark:text-gray-300">Department:</label>
                            <select id="selectdeptname"
                                class="w-full min-w-[150px] rounded-lg border border-gray-300 bg-white p-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                name="company_filter">
                                <option value="">All</option>
                                @foreach ($departements as $p)
                                    <option value="{{ $p->deptname }}" {{ $p->deptname == 'IT' ? 'selected' : '' }}>
                                        {{ $p->deptname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- <div class="flex items-center gap-2">
                            <label for="selectdeptname"
                                class="mb-1 block text-lg font-semibold text-gray-700 dark:text-gray-300">Department:</label>
                            <select id="selectdeptname"
                                class="w-full min-w-[200px] rounded-lg border border-gray-300 bg-white p-2.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                                name="departementid" required>
                                @foreach ($departements as $p)
                                    <option value="{{ $p->deptname }}" {{ $p->deptname == 'IT' ? 'selected' : '' }}>
                                        {{ $p->deptname }}
                                    </option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>
                </div>

                {{-- Chart and Export Button Section --}}
                <div class="flex w-full flex-col rounded-b-xl bg-white p-6 dark:bg-gray-800"> {{-- Removed 'shadow' from here --}}
                    <div class="mb-6 flex justify-end"> {{-- Aligns button to the right --}}
                        <button type="button"
                            class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            onclick="chart.exportImg({full:true})">Export Image Full</button>
                    </div>
                    <div class="chart-container w-full" style="width: 100%;"></div>
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
                    <ul class="-mb-px flex flex-wrap text-center text-sm font-medium" id="tabs">
                        <li class="mr-2">
                            <button type="button"
                                class="tab-button inline-flex items-center rounded-t-lg border-b-2 border-blue-600 px-4 py-2 text-base font-semibold text-blue-600 transition-colors duration-200 dark:border-blue-500 dark:text-blue-400"
                                onclick="switchTab('view')">View Employee</button>
                        </li>
                        {{-- Add other tabs here if needed --}}
                    </ul>
                    <button onclick="closeModal()" class="text-xl text-gray-500 transition-colors hover:text-red-600">
                        &times; {{-- Close icon --}}
                    </button>
                </div>

                {{-- Tab Content: View Employee --}}
                <div id="tab-view" class="tab-content"> {{-- Removed 'hidden' as it will be controlled by JS --}}
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">Employee List</h3>
                        <h4 id="departmentLabel" class="text-lg font-semibold text-gray-800 dark:text-gray-200">
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
                                        class="py-4 text-center text-sm italic text-gray-500 dark:text-gray-400">Loading
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
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                                Job Profile - <span id="jobLevelLabel"
                                    class="font-bold text-blue-600 dark:text-blue-400"></span>
                            </h3>
                            <button onclick="$('#modalJobProfile').addClass('hidden')"
                                class="text-xl text-gray-500 transition-colors hover:text-red-600">&times;</button>
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
                                                class="py-4 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                                Loading job purposes...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="jobSpecInfo"
                            class="space-y-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-700 shadow-inner dark:bg-gray-700 dark:text-gray-200">
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
                            .toUpperCase();
                        $('#departmentLabel').text(`Department: ${capitalizedDeptName}`);


                        let html = '';
                        employees.forEach((emp, index) => {
                            html += `
                        <tr>
                            <td class="border border-black px-2 py-1">${index + 1}</td>
                            <td class="border border-black px-2 py-1">${emp.employee_name}</td>
                            <td class="border border-black px-2 py-1">${emp.employee_company}</td>
                            <td class="border border-black px-2 py-1">${emp.employee_level}</td>
                            <td class="border border-black px-2 py-1 text-center">
                                <img src="${emp.image || 'https://cdn-icons-png.flaticon.com/512/149/149071.png'}" class="w-15 h-15 rounded-full mx-auto">
                            </td>
                            <td class="border border-black px-2 py-1 text-center">
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
                            .nodeContent(function(d) {
                                const members = d.data.members || [];
                                const level = d.depth;
                                // const bgColor = level === 0 ? '#e3f2fd' : level === 1 ? '#e8f5e9' : level === 2 ? '#fff3e0' : level === 3 ? '#fce4ec' : '#f5f5f5';
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
                            .data(nodes) // ✅ ini yang benar
                            .expandAll() // ⛔ HARUS setelah .data([...])
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
                                <td class="border border-black px-2 py-1">${i + 1}</td>                                
                                <td class="border border-black px-2 py-1">${p.job_purpose || ''}</td>                                                                                    
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
        <script>
            function exportOrgChartImage() {
                if (chart) {
                    chart.exportImg(); // ✅ panggil method bawaan d3-org-chart
                } else {
                    alert("Chart belum dimuat!");
                }
            }
        </script>

        <script src="https://unpkg.com/html2canvas@1.1.4/dist/html2canvas.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
        <script>
            function downloadPdf(chart) {
                chart.exportImg({
                    save: false,
                    full: true,
                    onLoad: (base64) => {
                        var pdf = new jspdf.jsPDF();
                        var img = new Image();
                        img.src = base64;
                        img.onload = function() {
                            pdf.addImage(
                                img,
                                'JPEG',
                                5,
                                5,
                                595 / 3,
                                ((img.height / img.width) * 595) / 3
                            );
                            pdf.save('chart.pdf');
                        };
                    },
                });
            }
        </script>



</x-app-layout>
