@props(['tr_approval'])


<div class="col-span-12 col-span-full rounded-2xl bg-white p-6 dark:bg-gray-800">
    <!-- Tabs -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="-mb-px flex flex-wrap text-center text-sm font-medium" id="approvalTabs" role="tablist">
            <li class="mr-2">
                <button
                    class="inline-block rounded-t-lg border-b-2 border-blue-600 p-4 text-blue-600 dark:border-blue-400 dark:text-blue-400"
                    id="tab-waiting" type="button" role="tab" aria-controls="waiting" aria-selected="true"
                    onclick="switchTab('waiting')">Waiting Approval</button>
            </li>
            <li class="mr-2">
                <button
                    class="inline-block rounded-t-lg border-b-2 border-transparent p-4 hover:border-gray-300 hover:text-gray-600 dark:hover:text-gray-300"
                    id="tab-approved" type="button" role="tab" aria-controls="approved" aria-selected="false"
                    onclick="switchTab('approved')">Approval</button>
            </li>
        </ul>
    </div>
    <!-- Tab Content -->
    <div id="tab-content-waiting" class="tab-content">
        <div class="flex flex-col justify-between gap-4 sm:flex-row">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">📝 Waiting Approval</h1>
                <p class="text-m ml-8 mt-2 dark:text-white">See what's your task for today!</p>
            </div>
            <div class="flex gap-2">
                <input id="waitingSearch" type="text" placeholder="Search..."
                    class="rounded-md border bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:bg-gray-700 dark:text-white" />
            </div>
        </div>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
            <table class="w-full min-w-full rounded" id="waitingTable">
                <thead class="bg-gray-200 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 text-center uppercase">DocID</th>
                        <th class="px-4 py-2 text-center uppercase">Date</th>
                        <th class="px-4 py-2 text-center uppercase">Company</th>
                        <th class="px-4 py-2 text-left uppercase">Department</th>
                        <th class="px-4 py-2 text-center uppercase">Info</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-800 dark:text-gray-300">
                    <!-- Data will be loaded by JS -->
                </tbody>
            </table>
            <div class="mt-2 flex items-center justify-between">
                <button id="waitingPrev"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Previous</button>
                <span id="waitingPaginationInfo" class="text-sm text-gray-700 dark:text-white">Page 1 of 1</span>
                <button id="waitingNext"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Next</button>
            </div>
            </tbody>
            </table>
        </div>
    </div>
    <div id="tab-content-approved" class="tab-content hidden">
        <div class="flex flex-col justify-between gap-4 sm:flex-row">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">✅ Approval</h1>
                <p class="text-m ml-8 mt-2 dark:text-white">See your approved tasks!</p>
            </div>
            <div class="flex gap-2">
                <input id="approvedSearch" type="text" placeholder="Search..."
                    class="rounded-md border bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:bg-gray-700 dark:text-white" />
            </div>
        </div>
        <div class="mt-4 overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
            <table class="w-full min-w-full rounded" id="approvedTable">
                <thead class="bg-gray-200 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 text-center uppercase">DocID</th>
                        <th class="px-4 py-2 text-center uppercase">Date</th>
                        <th class="px-4 py-2 text-center uppercase">Company</th>
                        <th class="px-4 py-2 text-left uppercase">Department</th>
                        <th class="max-w-xs px-4 py-2 text-center uppercase">Info</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-800 dark:text-gray-300">
                    <!-- Data will be loaded by JS -->
                </tbody>
            </table>
            <div class="mt-2 flex items-center justify-between">
                <button id="approvedPrev"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Previous</button>
                <span id="approvedPaginationInfo" class="text-sm text-gray-700 dark:text-white">Page 1 of 1</span>
                <button id="approvedNext"
                    class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Next</button>
            </div>
            </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        if (tab === 'waiting') {
            document.getElementById('tab-waiting').classList.add('text-blue-600', 'border-blue-600',
                'dark:text-blue-400', 'dark:border-blue-400');
            document.getElementById('tab-approved').classList.remove('text-blue-600', 'border-blue-600',
                'dark:text-blue-400', 'dark:border-blue-400');
            document.getElementById('tab-content-waiting').classList.remove('hidden');
            document.getElementById('tab-content-approved').classList.add('hidden');
        } else {
            document.getElementById('tab-waiting').classList.remove('text-blue-600', 'border-blue-600',
                'dark:text-blue-400', 'dark:border-blue-400');
            document.getElementById('tab-approved').classList.add('text-blue-600', 'border-blue-600',
                'dark:text-blue-400', 'dark:border-blue-400');
            document.getElementById('tab-content-waiting').classList.add('hidden');
            document.getElementById('tab-content-approved').classList.remove('hidden');
        }
    }

    // Search & Pagination logic
    function renderTable(data, tbodySelector, page, perPage, searchValue) {
        const tbody = document.querySelector(tbodySelector);
        tbody.innerHTML = '';
        let filtered = data;
        if (searchValue) {
            const val = searchValue.toLowerCase();
            filtered = data.filter(ap =>
                (ap.docid && ap.docid.toLowerCase().includes(val)) ||
                (ap.docdate && ap.docdate.toLowerCase().includes(val)) ||
                (ap.cpnyid && ap.cpnyid.toLowerCase().includes(val)) ||
                (ap.departementid && ap.departementid.toLowerCase().includes(val)) ||
                (ap.infohd && ap.infohd.toLowerCase().includes(val))
            );
        }
        const total = filtered.length;
        const start = (page - 1) * perPage;
        const end = start + perPage;
        const paged = filtered.slice(start, end);
        if (paged.length > 0) {
            paged.forEach(ap => {
                tbody.innerHTML += `
                    <tr class="border-b border-gray-200 transition hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                        <td class="whitespace-nowrap p-3 text-center">
                            <a href="${ap.url}/${ap.id}" target="_blank" class="rounded-md bg-blue-500 px-3 py-1 text-white transition hover:bg-blue-600">${ap.docid}</a>
                        </td>
<td class="px-4 py-2 text-center break-words whitespace-normal max-w-xs">${ap.docdate ?? '-'}</td>
<td class="px-4 py-2 text-center break-words whitespace-normal max-w-xs">${ap.cpnyid ?? '-'}</td>
<td class="px-4 py-2 text-left break-words whitespace-normal max-w-xs">${ap.departementid ?? '-'}</td>
<td class="px-4 py-2 text-left break-words whitespace-normal max-w-xs">${ap.infohd ?? '-'}</td>    
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No data found.</td></tr>';
        }
        return {
            total,
            filteredCount: filtered.length
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        let waitingData = [];
        let approvedData = [];
        let waitingPage = 1;
        let approvedPage = 1;
        const perPage = 10;
        let waitingSearch = '';
        let approvedSearch = '';

        function updateWaitingTable() {
            const {
                total,
                filteredCount
            } = renderTable(waitingData, '#waitingTable tbody', waitingPage, perPage, waitingSearch);
            document.getElementById('waitingPaginationInfo').innerText =
                `Page ${waitingPage} of ${Math.ceil(filteredCount/perPage)||1}`;
        }

        function updateApprovedTable() {
            const {
                total,
                filteredCount
            } = renderTable(approvedData, '#approvedTable tbody', approvedPage, perPage, approvedSearch);
            document.getElementById('approvedPaginationInfo').innerText =
                `Page ${approvedPage} of ${Math.ceil(filteredCount/perPage)||1}`;
        }

        fetch('/waitingjson')
            .then(response => response.json())
            .then(data => {
                waitingData = data.data || [];
                updateWaitingTable();
            });
        fetch('/approvejson')
            .then(response => response.json())
            .then(data => {
                approvedData = data.data || [];
                updateApprovedTable();
            });

        document.getElementById('waitingSearch').addEventListener('input', function(e) {
            waitingSearch = e.target.value;
            waitingPage = 1;
            updateWaitingTable();
        });
        document.getElementById('approvedSearch').addEventListener('input', function(e) {
            approvedSearch = e.target.value;
            approvedPage = 1;
            updateApprovedTable();
        });

        document.getElementById('waitingPrev').addEventListener('click', function() {
            if (waitingPage > 1) {
                waitingPage--;
                updateWaitingTable();
            }
        });
        document.getElementById('waitingNext').addEventListener('click', function() {
            const filteredCount = renderTable(waitingData, '#waitingTable tbody', 1, waitingData.length,
                waitingSearch).filteredCount;
            if (waitingPage < Math.ceil(filteredCount / perPage)) {
                waitingPage++;
                updateWaitingTable();
            }
        });
        document.getElementById('approvedPrev').addEventListener('click', function() {
            if (approvedPage > 1) {
                approvedPage--;
                updateApprovedTable();
            }
        });
        document.getElementById('approvedNext').addEventListener('click', function() {
            const filteredCount = renderTable(approvedData, '#approvedTable tbody', 1, approvedData
                .length, approvedSearch).filteredCount;
            if (approvedPage < Math.ceil(filteredCount / perPage)) {
                approvedPage++;
                updateApprovedTable();
            }
        });
    });
</script>
