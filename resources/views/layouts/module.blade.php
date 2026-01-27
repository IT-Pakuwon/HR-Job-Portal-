@php $noBack = true; @endphp
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
{{-- ================= ALPINE LOGIC ================= --}}
<script>
    function todoApp() {
        return {
            showModal: false,
            selectedDay: 'thu',

            days: [{
                    key: 'mon',
                    label: 'Mon',
                    date: 15
                },
                {
                    key: 'tue',
                    label: 'Tue',
                    date: 16
                },
                {
                    key: 'wed',
                    label: 'Wed',
                    date: 17
                },
                {
                    key: 'thu',
                    label: 'Thu',
                    date: 18
                },
                {
                    key: 'fri',
                    label: 'Fri',
                    date: 19
                },
                {
                    key: 'sat',
                    label: 'Sat',
                    date: 20
                },
                {
                    key: 'sun',
                    label: 'Sun',
                    date: 21
                },
            ],

            // MOCK TASKS
            tasks: [{
                    id: 1,
                    title: 'Review PRF submissions',
                    deadline: '2025-12-16', // OVERDUE
                    type: 'task',
                    location: '',
                    day: 'tue',
                    completed: false,
                },
                {
                    id: 2,
                    title: 'HR Weekly Meeting',
                    deadline: '2025-12-18', // TODAY
                    type: 'meeting',
                    location: 'Meeting Room B',
                    day: 'thu',
                    completed: false,
                },
                {
                    id: 3,
                    title: 'Warehouse stock check',
                    deadline: '2025-12-19',
                    type: 'task',
                    location: '',
                    day: 'fri',
                    completed: true,
                },
            ],

            newTask: {
                title: '',
                deadline: '',
            },

            today() {
                return new Date().toISOString().split('T')[0]
            },

            isToday(task) {
                return task.deadline === this.today()
            },

            isOverdue(task) {
                return !task.completed && task.deadline < this.today()
            },

            filteredTasks() {
                return this.tasks.filter(task => {
                    if (this.isOverdue(task)) return true
                    return task.day === this.selectedDay
                })
            },

            addTask() {
                this.tasks.push({
                    id: Date.now(),
                    title: this.newTask.title,
                    deadline: this.newTask.deadline,
                    type: 'task',
                    location: '',
                    day: this.selectedDay,
                    completed: false,
                })

                this.newTask = {
                    title: '',
                    deadline: ''
                }
                this.showModal = false
            }
        }
    }
</script>


<x-app-layout>

    <!-- HEADER -->
    <x-app.header variant="v2" />

    <!-- MAIN WRAPPER (LOCKED SCREEN, NO SCROLL) -->
    <div
        class="grid h-screen w-full grid-cols-1 gap-6 px-4 py-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2">

        {{-- ================= LEFT : APPLICATION MODULES ================= --}}

        <div class="grid h-full grid-rows-2 gap-4 md:gap-6 lg:grid-rows-2 xl:grid-rows-2">
            <div class="flex flex-col gap-4 rounded-xl border bg-white p-4 dark:border-gray-600 dark:bg-gray-800">


                <!-- TITLE -->
                <div class="shrink-0">
                    <h1 class="text-lg font-bold text-gray-900 md:text-lg dark:text-gray-100">Application Modules
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Select a module to continue</p>
                </div>

                @php
                    /* MOCK COUNTS */ $counts = [
                        'recruitment' => 3,
                        'applicants' => 5,
                        'purchase' => 2,
                        'warehouse' => 1,
                        'request' => 0,
                        'workorder' => 0,
                        'bast' => 0,
                        'rfca' => 0,
                        'calr' => 0,
                    ];
                    $card =
                        ' group relative cursor-pointer rounded-xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm flex flex-col items-center justify-center text-center ';
                    $icon = 'mb-2 text-lg';
                    $label = ' text-sm  font-semibold text-gray-800';
                    $badge =
                        ' absolute top-2 right-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5  text-sm  font-semibold text-white shadow ';
                @endphp
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">

                    {{-- ================= Recruitment (WITH SUB MENU) ================= --}}
                    <div class="group relative">
                        <div
                            class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            @if ($counts['recruitment'] > 0)
                                <span
                                    class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                    {{ $counts['recruitment'] }}
                                </span>
                            @endif
                            <div class="mb-2 text-lg">👥</div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Recruitment</div>
                        </div>

                        {{-- SUB MENU --}}
                        <div
                            class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                            <a href="{{ route('personnels') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                PRF
                            </a>

                            <a href="{{ route('jobapplicant') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                Applicant Portal
                            </a>
                        </div>
                    </div>

                    {{-- ================= Applicants ================= --}}
                    <a href="{{ route('applicants') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['applicants'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                {{ $counts['applicants'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">🧾</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Applicants</div>
                    </a>

                    {{-- ================= Purchase (WITH SUB MENU) ================= --}}
                    <div class="group relative">
                        <div
                            class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                            @if ($counts['purchase'] > 0)
                                <span
                                    class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                    {{ $counts['purchase'] }}
                                </span>
                            @endif
                            <div class="mb-2 text-lg">🛒</div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Purchase</div>
                        </div>

                        {{-- SUB MENU --}}
                        <div
                            class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                            <a href="{{ route('polist') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                PO List
                            </a>

                            <a href="{{ route('receiptlist') }}"
                                class="block px-4 py-3 text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                                Receipt List
                            </a>
                        </div>
                    </div>

                    {{-- ================= Other Modules ================= --}}
                    <a href="{{ route('wos') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">🛠️</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Work Order</div>
                    </a>

                    <a href="{{ route('spbs') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['warehouse'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-sm font-semibold text-white shadow">
                                {{ $counts['warehouse'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">📦</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">Warehouse</div>
                    </a>

                    <a href="{{ route('bastlist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">📑</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">BAST</div>
                    </a>

                    <a href="{{ route('rfcalist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">💵</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">RFCA</div>
                    </a>

                    <a href="{{ route('calrlist') }}"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">
                        <div class="mb-2 text-lg">📝</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">CALR</div>
                    </a>

                    <a href="https://mail3.pakuwon.com/" target="#"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">

                        <!-- Email Icon -->
                        <svg class="h-8 w-8 text-indigo-500 transition group-hover:scale-110" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75m19.5 0L12 13.5 2.25 6.75" />
                        </svg>

                        <span class="mt-3 text-sm font-medium text-gray-700">
                            Email
                        </span>
                    </a>

                    <a href="https://pakuwon.isort.id/login" target="_blank"
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-sm dark:border-gray-600 dark:bg-gray-700">

                        <!-- Reporting / Analytics Icon -->
                        <svg class="h-8 w-8 text-indigo-500 transition group-hover:scale-110" fill="none"
                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3v18h18M7.5 15v-6m4.5 6V6m4.5 9v-3" />
                        </svg>

                        <span class="mt-3 text-sm font-medium text-gray-700">
                            ISort
                        </span>
                    </a>


                </div>
            </div>
            <!-- ================= Today’s Summary ================= -->
            <div class="rounded-xl border bg-white p-4 dark:border-gray-600 dark:bg-gray-800">

                <!-- Header -->
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Today’s Summary
                        </h3>
                        <p class="text-sm text-gray-500">
                            Choose up to 5 analytics cards to build your daily dashboard.
                        </p>
                        <p id="dashboardCardCounter" class="mt-1 text-xs text-gray-400">
                            Selected: 0 / 5
                        </p>
                    </div>

                    <button id="btnAddDashboardCard"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        + Add Card
                    </button>
                </div>

                <!-- Dashboard Cards -->
                <div id="dashboardCardContainer" class="grid min-h-[120px] grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="text-sm text-gray-400">
                        No analytics added yet.
                    </div>
                </div>
            </div>

            <!-- ================= Add Analytics Card Modal ================= -->
            <div id="addDashboardCardModal" class="fixed inset-0 z-50 hidden">
                <div class="flex min-h-screen items-center justify-center bg-black/40 p-4">
                    <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-lg">

                        <!-- Modal Header -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold">Add Analytics Card</h3>
                            <p class="text-sm text-gray-500">
                                Select up to 5 cards to customize your dashboard.
                            </p>
                        </div>

                        <!-- Card List -->
                        <div id="dashboardCardList" class="space-y-3"></div>

                        <!-- Footer -->
                        <div class="mt-6 flex justify-end">
                            <button id="closeDashboardCardModal"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm hover:bg-gray-200">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {{-- ================= RIGHT : TODO + NOTIFICATIONS ================= --}}
        <div class="grid h-full grid-rows-2 gap-4 md:gap-6 lg:grid-rows-2 xl:grid-rows-2">

            <div x-data="todoApp()" x-init="init()"
                class="flex flex-col overflow-hidden rounded-xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                <!-- HEADER -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Weekly To-Do
                    </h2>

                    <div class="flex items-center gap-2">
                        <template x-if="!googleConnected">
                            <a href="/auth/google/calendar"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                Connect Google Calendar
                            </a>
                        </template>

                        <template x-if="googleConnected">
                            <span class="rounded-lg bg-green-100 px-3 py-2 text-sm text-green-700">
                                ✓ Google Connected
                            </span>
                        </template>

                        <button @click="showModal = true"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white">
                            Create Task
                        </button>
                    </div>
                </div>

                <!-- WEEK SELECTOR -->
                <div class="mb-6 flex justify-between gap-3">
                    <template x-for="day in days" :key="day.date">
                        <button @click="selectedDay = day.date"
                            class="flex flex-1 flex-col items-center rounded-xl py-3 transition"
                            :class="selectedDay === day.date ?
                                'bg-indigo-600 text-white' :
                                'bg-gray-100 dark:bg-gray-700'">
                            <span class="text-sm" x-text="day.label"></span>
                            <span class="text-sm font-semibold" x-text="day.day"></span>
                        </button>
                    </template>
                </div>

                <!-- TASK LIST -->
                <div class="flex-1 space-y-4 overflow-y-auto pr-2">
                    <template x-for="task in filteredTasks()" :key="task.id">
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-700">
                            <div class="flex items-start gap-3">

                                <input type="checkbox" x-model="task.completed" :disabled="task.fromGoogle"
                                    class="mt-1">

                                <div class="flex-1">
                                    <!-- TITLE + BADGE -->
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium"
                                            :class="task.completed ?
                                                'line-through text-gray-400' :
                                                'text-gray-900 dark:text-gray-100'"
                                            x-text="task.title"></p>

                                        <span x-show="task.fromGoogle"
                                            class="rounded-full bg-blue-100 px-2 py-0.5 text-sm text-blue-700">
                                            Google
                                        </span>
                                    </div>

                                    <!-- META -->
                                    <div class="mt-1 space-y-1 text-sm text-gray-500 dark:text-gray-300">
                                        <p>Date: <span x-text="task.date"></span></p>

                                        <template x-if="task.time">
                                            <p>⏰ <span x-text="task.time"></span></p>
                                        </template>

                                        <template x-if="task.location">
                                            <p>📍 <span x-text="task.location"></span></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredTasks().length === 0">
                        <p class="text-center text-sm text-gray-400">
                            No tasks for this day
                        </p>
                    </template>
                </div>

                <!-- CREATE TASK MODAL -->
                <div x-show="showModal" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div @click.stop class="w-full max-w-lg rounded-xl bg-white p-6 dark:bg-gray-800">

                        <h3 class="mb-6 text-sm font-semibold">
                            Create New Task
                        </h3>

                        <div class="space-y-4">
                            <input type="text" x-model="newTask.title" placeholder="Task name"
                                class="w-full rounded-xl border px-4 py-2">

                            <input type="date" x-model="newTask.deadline"
                                class="w-full rounded-xl border px-4 py-2">
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button @click="showModal = false" class="rounded-lg px-4 py-2 text-gray-600">
                                Cancel
                            </button>

                            <button @click="addTask()" class="rounded-lg bg-indigo-600 px-4 py-2 text-white">
                                Create
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Notifications -->
            <div x-data="{
                notifications: [{
                        id: 1,
                        title: 'PO #2304',
                        status: 'waiting',
                        icon: '🛒',
                        createdAt: '2025-12-18',
                        url: '#'
                    },
                    {
                        id: 2,
                        title: 'PO #2298',
                        status: 'revised',
                        icon: '🛒',
                        createdAt: '2025-12-15',
                        url: '#'
                    },
                    {
                        id: 3,
                        title: 'SPP Barang #118',
                        status: 'waiting',
                        icon: '📝',
                        createdAt: '2025-12-17',
                        url: '#'
                    }
                ],
            
                openNotification(id) {
                    this.notifications = this.notifications.filter(n => n.id !== id)
                },
            
                formatDate(date) {
                    return new Date(date).toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: '2-digit'
                    })
                }
            }"
                class="flex flex-col overflow-hidden rounded-xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                <h2 class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    Notifications
                </h2>

                <!-- EMPTY STATE -->
                <template x-if="notifications.length === 0">
                    <div class="flex flex-1 items-center justify-center text-sm text-gray-400">
                        No pending notifications
                    </div>
                </template>

                <div class="flex-1 overflow-y-auto pr-2">
                    <ul class="space-y-3 text-sm">

                        <template x-for="item in notifications" :key="item.id">
                            <li>
                                <a href="#" @click.prevent="openNotification(item.id)"
                                    class="flex items-start gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600">

                                    <!-- ICON -->
                                    <div class="text-sm">
                                        <span x-text="item.icon"></span>
                                    </div>

                                    <!-- CONTENT -->
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800 dark:text-gray-100" x-text="item.title">
                                        </p>

                                        <p class="text-sm text-gray-500 dark:text-gray-300"
                                            x-text="item.status === 'waiting'
                                   ? 'Waiting approval'
                                   : 'Revision needed'">
                                        </p>
                                    </div>

                                    <!-- RIGHT -->
                                    <div class="flex flex-col items-end gap-1 text-right">

                                        <!-- STATUS BADGE -->
                                        <span class="rounded-full px-2 py-0.5 text-sm font-medium"
                                            :class="item.status === 'waiting' ?
                                                'bg-yellow-100 text-yellow-700' :
                                                'bg-red-100 text-red-700'"
                                            x-text="item.status">
                                        </span>

                                        <!-- UNREAD SINCE -->
                                        <span class="text-[10px] text-gray-400"
                                            x-text="'Unread since ' + formatDate(item.createdAt)">
                                        </span>
                                    </div>

                                </a>
                            </li>
                        </template>

                    </ul>
                </div>
            </div>




        </div>



    </div>

    <script>
        function todoApp() {
            return {
                /* ================= STATE ================= */
                showModal: false,
                selectedDay: null,

                googleConnected: new URLSearchParams(window.location.search)
                    .get('google') === 'connected',

                days: [],
                tasks: [],

                newTask: {
                    title: '',
                    deadline: '',
                },

                /* ================= INIT ================= */
                init() {
                    this.generateWeek(new Date(), true);

                    if (this.googleConnected) {
                        this.loadGoogleEvents();
                    }
                },

                /* ================= WEEK (SAFE) ================= */
                generateWeek(baseDate = new Date(), force = false) {
                    // Find Monday
                    const monday = new Date(baseDate);
                    const day = monday.getDay();
                    const diff = day === 0 ? -6 : 1 - day;
                    monday.setDate(monday.getDate() + diff);

                    this.days = Array.from({
                        length: 7
                    }).map((_, i) => {
                        const d = new Date(monday);
                        d.setDate(monday.getDate() + i);

                        return {
                            label: d.toLocaleDateString('en-US', {
                                weekday: 'short'
                            }),
                            date: d.toISOString().split('T')[0],
                            day: d.getDate(),
                        };
                    });

                    // ✅ Only set selectedDay ONCE
                    if (!this.selectedDay || force) {
                        this.selectedDay = this.days[0].date;
                    }
                },

                /* ================= HELPERS ================= */
                formatTime(dateStr) {
                    if (!dateStr || dateStr.length === 10) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                filteredTasks() {
                    return this.tasks.filter(t => t.date === this.selectedDay);
                },

                /* ================= GOOGLE → WEB ================= */
                async loadGoogleEvents() {
                    try {
                        const res = await fetch('/google/calendar/events');
                        if (!res.ok) return;

                        const events = await res.json();

                        events.forEach(ev => {
                            const date = ev.start.substring(0, 10);

                            // ❗ Prevent duplicates
                            if (this.tasks.find(t => t.id === ev.id)) return;

                            this.tasks.push({
                                id: ev.id,
                                title: ev.title || '(No title)',
                                date,
                                time: this.formatTime(ev.start),
                                location: ev.location || '',
                                fromGoogle: true,
                                completed: false,
                            });
                        });
                    } catch (e) {
                        console.error('Failed to load Google events', e);
                    }
                },

                /* ================= WEB → GOOGLE ================= */
                async addTask() {
                    if (!this.newTask.title || !this.newTask.deadline) return;

                    const task = {
                        title: this.newTask.title,
                        deadline: this.newTask.deadline,
                    };

                    if (this.googleConnected) {
                        await this.syncToGoogle(task);
                    }

                    this.tasks.push({
                        id: Date.now(),
                        title: task.title,
                        date: task.deadline,
                        fromGoogle: false,
                        completed: false,
                    });

                    this.newTask = {
                        title: '',
                        deadline: ''
                    };
                    this.showModal = false;
                },

                async syncToGoogle(task) {
                    try {
                        const res = await fetch('/google/calendar/event', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(task)
                        });

                        if (!res.ok) {
                            const err = await res.json();
                            alert(err.error ?? 'Google Calendar sync failed');
                        }
                    } catch (e) {
                        console.error('Google sync failed', e);
                    }
                }
            }
        }

        const ctx = document.getElementById('opsSummaryChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{
                    label: 'Completed Tasks',
                    data: [18, 22, 20, 17, 19],
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                    barThickness: 16
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.raw} tasks`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: false
                    }
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {

            // ================= Card Registry =================
            const DASHBOARD_CARDS = [{
                    id: 'po_status',
                    title: 'PO Status',
                    description: 'Waiting vs Approved',
                    chartType: 'pie'
                },
                {
                    id: 'weekly_orders',
                    title: 'Weekly Orders',
                    description: 'Orders per day',
                    chartType: 'bar'
                },
                {
                    id: 'pending_tasks',
                    title: 'Pending Tasks',
                    description: 'Today pending tasks',
                    chartType: 'bar'
                },
                {
                    id: 'notification_summary',
                    title: 'Notifications',
                    description: 'Unread notifications',
                    chartType: 'line'
                }
            ];

            const MAX_DASHBOARD_CARDS = 5;
            let selectedDashboardCards = [];
            const dashboardCharts = {};

            // ================= Counter =================
            function updateDashboardCounter() {
                $('#dashboardCardCounter').text(
                    `Selected: ${selectedDashboardCards.length} / ${MAX_DASHBOARD_CARDS}`
                );
            }

            // ================= Modal Open / Close =================
            $('#btnAddDashboardCard').on('click', function() {
                renderDashboardCardList();
                $('#addDashboardCardModal').removeClass('hidden');
            });

            $('#closeDashboardCardModal').on('click', function() {
                $('#addDashboardCardModal').addClass('hidden');
            });

            // ================= Render Modal Card List =================
            function renderDashboardCardList() {
                const $list = $('#dashboardCardList');
                $list.empty();

                DASHBOARD_CARDS.forEach(card => {
                    const isAdded = selectedDashboardCards.includes(card.id);
                    const isDisabled = !isAdded && selectedDashboardCards.length >= MAX_DASHBOARD_CARDS;

                    $list.append(`
                <div class="flex items-center justify-between rounded-lg border p-3">
                    <div>
                        <div class="font-medium">${card.title}</div>
                        <div class="text-sm text-gray-500">${card.description}</div>
                    </div>

                    <button
                        class="btnAddCard rounded-md px-3 py-1.5 text-sm
                        ${isAdded ? 'bg-gray-300 text-gray-600' : 'bg-indigo-600 text-white hover:bg-indigo-700'}
                        ${isDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                        data-id="${card.id}"
                        ${isAdded || isDisabled ? 'disabled' : ''}>
                        ${isAdded ? 'Added' : 'Add'}
                    </button>
                </div>
            `);
                });
            }

            // ================= Add Card =================
            $(document).on('click', '.btnAddCard', function() {
                const cardId = $(this).data('id');

                if (
                    selectedDashboardCards.length >= MAX_DASHBOARD_CARDS ||
                    selectedDashboardCards.includes(cardId)
                ) return;

                selectedDashboardCards.push(cardId);

                renderDashboardCardList();
                renderDashboardCards();
                updateDashboardCounter();
            });

            // ================= Render Dashboard Cards =================
            function renderDashboardCards() {
                const $container = $('#dashboardCardContainer');
                $container.empty();

                if (!selectedDashboardCards.length) {
                    $container.append(`<div class="text-sm text-gray-400">No analytics added yet.</div>`);
                    return;
                }

                selectedDashboardCards.forEach(cardId => {
                    const card = DASHBOARD_CARDS.find(c => c.id === cardId);
                    if (!card) return;

                    const canvasId = `chart_${card.id}`;

                    $container.append(`
                <div class="rounded-lg border bg-white p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="font-medium">${card.title}</h4>
                        <button class="btnRemoveCard text-sm text-red-500" data-id="${card.id}">
                            Remove
                        </button>
                    </div>
                    <div class="relative h-48">
                        <canvas id="${canvasId}"></canvas>
                    </div>
                </div>
            `);

                    renderChart(card, canvasId);
                });
            }

            // ================= Remove Card =================
            $(document).on('click', '.btnRemoveCard', function() {
                const cardId = $(this).data('id');
                const canvasId = `chart_${cardId}`;

                if (dashboardCharts[canvasId]) {
                    dashboardCharts[canvasId].destroy();
                    delete dashboardCharts[canvasId];
                }

                selectedDashboardCards = selectedDashboardCards.filter(id => id !== cardId);

                renderDashboardCards();
                updateDashboardCounter();
            });

            // ================= Chart Data =================
            function getChartData(cardId) {
                switch (cardId) {
                    case 'po_status':
                        return {
                            labels: ['Waiting', 'Approved'], data: [12, 8]
                        };
                    case 'weekly_orders':
                        return {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], data: [5, 9, 7, 12, 6]
                        };
                    case 'pending_tasks':
                        return {
                            labels: ['Morning', 'Afternoon', 'Evening'], data: [3, 4, 2]
                        };
                    case 'notification_summary':
                        return {
                            labels: ['Unread', 'Read'], data: [6, 14]
                        };
                    default:
                        return {
                            labels: [], data: []
                        };
                }
            }

            function renderChart(card, canvasId) {
                const ctx = document.getElementById(canvasId);
                if (!ctx) return;

                if (dashboardCharts[canvasId]) {
                    dashboardCharts[canvasId].destroy();
                }

                const chartData = getChartData(card.id);

                dashboardCharts[canvasId] = new Chart(ctx, {
                    type: card.chartType,
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: ['#6366F1', '#22C55E', '#F59E0B', '#EF4444', '#3B82F6']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: card.chartType !== 'bar'
                            }
                        },
                        scales: card.chartType === 'bar' ?
                            {
                                y: {
                                    beginAtZero: true
                                }
                            } :
                            {}
                    }
                });
            }

            // ================= INIT =================
            updateDashboardCounter();

        });
    </script>






</x-app-layout>
