@php $noBack = true; @endphp
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

        <div
            class="flex h-full flex-col rounded-xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

            <!-- TITLE -->
            <div class="mb-4 shrink-0">
                <h1 class="text-lg font-bold text-gray-900 md:text-lg dark:text-gray-100">Application Modules</h1>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">Select a module to continue</p>
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
                    ' group relative cursor-pointer rounded-xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md flex flex-col items-center justify-center text-center ';
                $icon = 'mb-2 text-lg';
                $label = 'text-xs font-semibold text-gray-800';
                $badge =
                    ' absolute top-2 right-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white shadow ';
            @endphp
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4">

                {{-- ================= Recruitment (WITH SUB MENU) ================= --}}
                <div class="group relative">
                    <div
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['recruitment'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white shadow">
                                {{ $counts['recruitment'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">👥</div>
                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">Recruitment</div>
                    </div>

                    {{-- SUB MENU --}}
                    <div
                        class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                        <a href="{{ route('personnels') }}"
                            class="block px-4 py-3 text-xs text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                            PRF
                        </a>

                        <a href="{{ route('jobapplicant') }}"
                            class="block px-4 py-3 text-xs text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                            Applicant Portal
                        </a>
                    </div>
                </div>

                {{-- ================= Applicants ================= --}}
                <a href="{{ route('applicants') }}"
                    class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                    @if ($counts['applicants'] > 0)
                        <span
                            class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white shadow">
                            {{ $counts['applicants'] }}
                        </span>
                    @endif
                    <div class="mb-2 text-lg">🧾</div>
                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">Applicants</div>
                </a>

                {{-- ================= Purchase (WITH SUB MENU) ================= --}}
                <div class="group relative">
                    <div
                        class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                        @if ($counts['purchase'] > 0)
                            <span
                                class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white shadow">
                                {{ $counts['purchase'] }}
                            </span>
                        @endif
                        <div class="mb-2 text-lg">🛒</div>
                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">Purchase</div>
                    </div>

                    {{-- SUB MENU --}}
                    <div
                        class="pointer-events-none absolute left-1/2 top-[105%] z-50 w-44 -translate-x-1/2 scale-95 rounded-xl border border-gray-200 bg-white opacity-0 shadow-lg transition-all duration-200 group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 dark:border-gray-600 dark:bg-gray-700">

                        <a href="{{ route('polist') }}"
                            class="block px-4 py-3 text-xs text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                            PO List
                        </a>

                        <a href="{{ route('receiptlist') }}"
                            class="block px-4 py-3 text-xs text-gray-800 hover:bg-gray-100 dark:text-gray-100 dark:hover:bg-gray-600">
                            Receipt List
                        </a>
                    </div>
                </div>

                {{-- ================= Other Modules ================= --}}
                <a href="{{ route('wos') }}"
                    class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                    <div class="mb-2 text-lg">🛠️</div>
                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">Work Order</div>
                </a>

                <a href="{{ route('spbs') }}"
                    class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                    @if ($counts['warehouse'] > 0)
                        <span
                            class="absolute right-2 top-2 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-semibold text-white shadow">
                            {{ $counts['warehouse'] }}
                        </span>
                    @endif
                    <div class="mb-2 text-lg">📦</div>
                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">Warehouse</div>
                </a>

                <a href="{{ route('bastlist') }}"
                    class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                    <div class="mb-2 text-lg">📑</div>
                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">BAST</div>
                </a>

                <a href="{{ route('rfcalist') }}"
                    class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                    <div class="mb-2 text-lg">💵</div>
                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">RFCA</div>
                </a>

                <a href="{{ route('calrlist') }}"
                    class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-5 text-center transition-all duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700">
                    <div class="mb-2 text-lg">📝</div>
                    <div class="text-xs font-semibold text-gray-800 dark:text-gray-100">CALR</div>
                </a>

            </div>
        </div>




        {{-- ================= RIGHT : TODO + NOTIFICATIONS ================= --}}
        <div class="grid h-full grid-rows-2 gap-4 md:gap-6 lg:grid-rows-2 xl:grid-rows-2">

            {{-- ================= WEEKLY TODO ================= --}}
``            <div x-data="todoApp()"
                class="flex flex-col overflow-hidden rounded-xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                <!-- HEADER -->
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Weekly To-Do</h2>
                    <button @click="showModal = true"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                        Create Task
                    </button>
                </div>

                <!-- WEEKLY CALENDAR -->
                <div class="mb-6 flex justify-between gap-3">
                    <template x-for="day in days" :key="day.key">
                        <button @click="selectedDay = day.key"
                            class="flex flex-1 flex-col items-center rounded-xl py-3 transition"
                            :class="selectedDay === day.key ?
                                'bg-indigo-600 text-white' :
                                'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'">
                            <span class="text-xs dark:text-gray-200" x-text="day.label"></span>
                            <span class="text-sm font-semibold dark:text-gray-100" x-text="day.date"></span>
                        </button>
                    </template>
                </div>

                <!-- TASK LIST -->
                <div class="flex-1 space-y-4 overflow-y-auto pr-2">
                    <template x-for="task in filteredTasks()" :key="task.id">
                        <div class="rounded-xl p-4"
                            :class="task.completed ? 'bg-gray-100 dark:bg-gray-700' : 'bg-gray-50 dark:bg-gray-600'">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" x-model="task.completed" class="mt-1">

                                <div class="flex-1">
                                    <!-- TITLE + BADGES -->
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium"
                                            :class="task.completed ?
                                                'line-through text-gray-400 dark:text-gray-400' :
                                                'text-gray-900 dark:text-gray-100'"
                                            x-text="task.title"></p>

                                        <!-- OVERDUE -->
                                        <template x-if="isOverdue(task)">
                                            <span
                                                class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-700 dark:text-red-100">
                                                Overdue
                                            </span>
                                        </template>

                                        <!-- TODAY -->
                                        <template x-if="!isOverdue(task) && isToday(task)">
                                            <span
                                                class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-700 dark:text-indigo-100">
                                                Today
                                            </span>
                                        </template>
                                    </div>

                                    <!-- META -->
                                    <div class="mt-1 space-y-1 text-xs"
                                        :class="task.completed ? 'text-gray-500 dark:text-gray-300' :
                                            'text-gray-500 dark:text-gray-300'">
                                        <p>Deadline: <span x-text="task.deadline"></span></p>

                                        <template x-if="task.type === 'meeting'">
                                            <p>📍 Location <span x-text="task.location"></span></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredTasks().length === 0">
                        <p class="text-center text-xs text-gray-400 dark:text-gray-300">
                            No tasks for this day
                        </p>
                    </template>
                </div>

                <!-- CREATE TASK MODAL -->
                <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div @click.stop class="w-full max-w-lg rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">

                        <h3 class="mb-6 text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Create New Task
                        </h3>

                        <div class="space-y-4">
                            <input type="text" x-model="newTask.title" placeholder="Task name"
                                class="w-full rounded-xl border px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">

                            <input type="date" x-model="newTask.deadline"
                                class="w-full rounded-xl border px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button @click="showModal = false"
                                class="rounded-lg px-4 py-2 text-gray-600 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                Cancel
                            </button>

                            <button @click="addTask()"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                                Create
                            </button>
                        </div>

                    </div>
                </div>
            </div>``



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
                    <div class="flex flex-1 items-center justify-center text-xs text-gray-400">
                        No pending notifications
                    </div>
                </template>

                <div class="flex-1 overflow-y-auto pr-2">
                    <ul class="space-y-3 text-xs">

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

                                        <p class="text-xs text-gray-500 dark:text-gray-300"
                                            x-text="item.status === 'waiting'
                                   ? 'Waiting approval'
                                   : 'Revision needed'">
                                        </p>
                                    </div>

                                    <!-- RIGHT -->
                                    <div class="flex flex-col items-end gap-1 text-right">

                                        <!-- STATUS BADGE -->
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium"
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


</x-app-layout>
