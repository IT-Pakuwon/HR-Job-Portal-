@props(['tr_approval'])

<div class="col-span-full flex h-[45vh] flex-col rounded-xl bg-white p-4 sm:col-span-12 xl:col-span-7 dark:bg-gray-800">


    <!-- Table Container -->
    <div x-data="pagination()" x-init="init()" class="overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <div>
                <h1 class="text-2xl font-bold dark:text-white">📝 To Do List</h1>
                <p class="text-m ml-8 mt-2 dark:text-white">See what's your task for today!</p>
            </div>
            
            <!-- Search Input -->
            {{-- <div class="flex gap-2">
                <input x-ref="search" type="text" placeholder="Search task..."
                    @input="searchValue = $refs.search.value.toLowerCase(); currentPage = 1; paginate();"
                    class="rounded-md border bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:bg-gray-700 dark:text-white">
            </div> --}}
            <div class="flex gap-2">
            <a href="{{ route('waitingapproval') }}"
                class="w-full max-w-xs text-center text-sm font-medium text-blue-600 hover:text-blue-800">
                See More
            </a>
        </div>

            
        </div>
        <table class="mt-4 w-full rounded">
            <thead class="bg-gray-200 text-xs text-gray-700 dark:bg-gray-700 dark:text-white">
                <tr>
                    <th @click="sortTable('docid')" class="cursor-pointer select-none px-4 py-2 text-left uppercase"
                        :class="getSortClass('docid')">
                        <span x-html="getSortLabel('docid', 'DocID')"></span>
                    </th>
                    <th @click="sortTable('docdate')" class="cursor-pointer select-none px-4 py-2 text-left uppercase"
                        :class="getSortClass('docdate')">
                        <span x-html="getSortLabel('docdate', 'Date')"></span>
                    </th>
                    <th @click="sortTable('cpnyid')" class="cursor-pointer select-none px-4 py-2 text-center uppercase"
                        :class="getSortClass('cpnyid')">
                        <span x-html="getSortLabel('cpnyid', 'Company')"></span>
                    </th>
                    <th @click="sortTable('departementid')"
                        class="cursor-pointer select-none px-4 py-2 text-right uppercase"
                        :class="getSortClass('departementid')">
                        <span x-html="getSortLabel('departementid', 'Department')"></span>
                    </th>
                    <th @click="sortTable('infohd')" class="cursor-pointer select-none px-4 py-2 text-right uppercase"
                        :class="getSortClass('infohd')">
                        <span x-html="getSortLabel('infohd', 'Info')"></span>
                    </th>
                </tr>
            </thead>
            <tbody id="taskTable" class="text-sm text-gray-800 dark:text-gray-300">
                @foreach ($tr_approval as $ap)
                    <tr
                        class="border-b border-gray-200 transition hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                        <td class="whitespace-nowrap p-3 text-left">
                            <a href="{{ url($ap->url . '/' . $ap->id) }}" target="_blank"
                                class="rounded-md bg-blue-500 px-3 py-1 text-white transition hover:bg-blue-600">
                                {{ $ap->docid }}
                            </a>
                        </td>
                        <td class="whitespace-nowrap p-3 text-left">{{ $ap->docdate ?? '-' }}</td>
                        <td class="whitespace-nowrap p-3 text-center">{{ $ap->cpnyid ?? '-' }}</td>
                        <td class="whitespace-nowrap p-3 text-right">{{ $ap->departementid ?? '-' }}</td>
                        <td class="whitespace-nowrap p-3 text-right">{{ $ap->infohd ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex items-center justify-between p-4">
            <button @click="prevPage" :disabled="currentPage === 1"
                class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                Previous
            </button>
            <span class="text-sm text-gray-700 dark:text-white">
                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
            </span>
            <button @click="nextPage" :disabled="currentPage === totalPages"
                class="rounded-md bg-gray-200 px-3 py-2 text-gray-700 transition hover:bg-gray-300 disabled:opacity-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                Next
            </button>
        </div>
    </div>
</div>

<!-- Alpine.js Logic -->
<script>
    function pagination() {
        return {
            currentPage: 1,
            perPage: 5,
            totalPages: 1,
            searchValue: '',
            sortKey: '',
            sortAsc: true,
            data: [],

            init() {
                const rows = document.querySelectorAll('#taskTable tr');
                this.data = [...rows].map(row => {
                    const cells = row.querySelectorAll('td');
                    return {
                        element: row,
                        docid: cells[0]?.innerText.trim().toLowerCase(),
                        docdate: cells[1]?.innerText.trim().toLowerCase(),
                        cpnyid: cells[2]?.innerText.trim().toLowerCase(),
                        departementid: cells[3]?.innerText.trim().toLowerCase(),
                        infohd: cells[4]?.innerText.trim().toLowerCase(),
                        rawText: row.textContent.toLowerCase()
                    };
                });
                this.paginate();
            },

            getFilteredData() {
                let filtered = this.data;

                if (this.searchValue) {
                    filtered = filtered.filter(item => item.rawText.includes(this.searchValue));
                }

                if (this.sortKey) {
                    filtered.sort((a, b) => {
                        const valA = a[this.sortKey];
                        const valB = b[this.sortKey];
                        if (valA < valB) return this.sortAsc ? -1 : 1;
                        if (valA > valB) return this.sortAsc ? 1 : -1;
                        return 0;
                    });
                }

                return filtered;
            },

            paginate() {
                const filtered = this.getFilteredData();
                this.totalPages = Math.ceil(filtered.length / this.perPage) || 1;

                this.data.forEach(item => item.element.style.display = 'none');

                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;

                filtered.slice(start, end).forEach(item => {
                    item.element.style.display = '';
                });
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.paginate();
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.paginate();
                }
            },

            sortTable(key) {
                if (this.sortKey === key) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortKey = key;
                    this.sortAsc = true;
                }
                this.paginate();
            },

            getSortClass(key) {
                if (this.sortKey !== key) return '';
                return 'text-sm font-semibold text-gray-600 dark:text-gray-400';
            },

            getSortLabel(key, label) {
                if (this.sortKey === key) {
                    const arrow = this.sortAsc ? '▲' : '▼';
                    return `${label} <span class='ml-1'>${arrow}</span>`;
                }
                return label;
            }
        };
    }
</script>
