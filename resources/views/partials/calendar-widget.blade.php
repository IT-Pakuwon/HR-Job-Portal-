<meta name="csrf-token" content="{{ csrf_token() }}">

<div x-data="calendarApp()" x-init="init()"
    class="flex h-[50vh] w-full flex-col overflow-hidden rounded-2xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="mb-3 flex items-center justify-between">

        <!-- LEFT: Title + Nav -->
        <div class="flex items-center gap-2">
            <h2 class="text-base font-medium text-gray-900 dark:text-white">
                Calendar
            </h2>

            <!-- FullCalendar nav -->
            <div id="calendar-nav" class="ml-2 flex items-center gap-1"></div>
        </div>

        <!-- RIGHT: Status + Action -->
        <div class="flex items-center justify-between gap-3">

            <!-- LEFT: Google Status -->
            <div class="flex items-center gap-2">
                <template x-if="googleConnected">
                    <div
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Google connected

                        <button @click="disconnectGoogle()"
                            class="ml-1 rounded-full px-1 text-emerald-600 hover:bg-emerald-100">
                            ✕
                        </button>
                    </div>
                </template>

                <template x-if="!googleConnected">
                    <div
                        class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                        Google disconnected
                    </div>
                </template>
            </div>

            <!-- RIGHT: Primary Action -->
            <button @click="showModal = true"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-1 text-xs font-semibold text-white shadow-sm hover:bg-gray-700 active:scale-[0.98]">
                <span class="text-[11px]">＋</span>
                task
            </button>
        </div>

    </div>




    <!-- CALENDAR -->
    <div id="calendar"
        class="h-full overflow-hidden rounded-xl dark:[&_.fc-theme-standard_td]:border-gray-700 dark:[&_.fc-theme-standard_th]:border-gray-700">
    </div>

    <!-- MODAL -->
    <div x-show="showModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 backdrop-blur-md">

        <div @click.stop class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">

            <!-- MODAL HEADER -->
            <div class="mb-6">
                <h3 x-text="editingTaskId ? 'Edit task' : 'New task'"></h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    This task will sync with Google Calendar
                </p>
            </div>

            <!-- FORM -->
            <div class="space-y-5">

                <!-- TITLE -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">
                        Title
                    </label>
                    <input x-model="newTask.title"
                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <!-- DATE -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">
                        Date
                    </label>
                    <input type="date" x-model="newTask.deadline"
                        class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <!-- TIME -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="newTask.all_day" id="allDay"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                    <label for="allDay" class="text-xs text-gray-600 dark:text-gray-400">
                        All-day event
                    </label>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-medium text-gray-500">
                        Time
                    </label>

                    <div class="grid grid-cols-3 gap-3">
                        <input type="time" x-model="newTask.start_time" @change="autoCalculateEnd()"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                        <input type="time" x-model="newTask.end_time" @focus="manualEndTime = true"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                        <select x-model="duration" @change="applyDuration()"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Duration</option>
                            <option value="30">30m</option>
                            <option value="60">1h</option>
                            <option value="90">1.5h</option>
                            <option value="120">2h</option>
                        </select>
                    </div>
                </div>

                <!-- META -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <input x-model="newTask.location" placeholder="Location"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                    <input x-model="newTask.link" placeholder="Meeting link"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">
                        Description
                    </label>
                    <textarea x-model="newTask.description" rows="3" placeholder="Optional details…"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="mt-6 flex justify-end gap-3 border-t pt-4 dark:border-gray-700">
                <button @click="showModal=false"
                    class="rounded-lg px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                    Cancel
                </button>
                <button @click="submitTask()" :disabled="!newTask.title || !newTask.deadline || !newTask.description"
                    class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Submit
                </button>


            </div>
        </div>
    </div>

    <!-- EVENT DETAIL POPUP -->
    <div x-show="selectedEvent" x-cloak x-transition.opacity @click.self="selectedEvent = null"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div @click.stop x-transition.scale
            class="w-full max-w-xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">

            <!-- HEADER -->
            <div class="flex items-start justify-between px-7 py-6">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"
                        x-text="selectedEvent?.title || 'Untitled event'"></h3>
                    <p class="text-xs text-gray-500">Event details</p>
                </div>

                <button @click="selectedEvent = null"
                    class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    ✕
                </button>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800"></div>

            <!-- CONTENT -->
            <div class="space-y-8 px-7 py-6">

                <!-- DESCRIPTION -->
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        Description
                    </p>
                    <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300"
                        x-text="selectedEvent?.extendedProps?.description || 'No description provided'"></p>
                </div>

                <!-- META GRID -->
                <div class="grid grid-cols-2 gap-6">

                    <!-- DATE -->
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-lg dark:bg-gray-800">
                            📅
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Date
                            </p>
                            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200"
                                x-text="
                                selectedEvent?.start
                                    ? selectedEvent.start.toLocaleDateString()
                                    : 'Empty'
                            ">
                            </p>
                        </div>
                    </div>

                    <!-- TIME -->
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-lg dark:bg-gray-800">
                            🕒
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Time
                            </p>
                            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200"
                                x-text="
                                selectedEvent?.start
                                    ? selectedEvent.start.toLocaleTimeString()
                                    : 'Empty'
                            ">
                            </p>
                        </div>
                    </div>

                    <!-- LOCATION -->
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-lg dark:bg-gray-800">
                            📍
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Location
                            </p>
                            <p class="mt-1 text-sm text-gray-800 dark:text-gray-200"
                                x-text="selectedEvent?.extendedProps?.location || 'Empty'"></p>
                        </div>
                    </div>

                    <!-- MEETING LINK -->
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-lg dark:bg-gray-800">
                            🔗
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                Meeting
                            </p>

                            <template x-if="selectedEvent?.extendedProps?.meeting_link">
                                <a :href="selectedEvent.extendedProps.meeting_link" target="_blank"
                                    class="mt-1 block text-sm font-medium text-indigo-600 hover:underline">
                                    Open meeting
                                </a>
                            </template>

                            <template x-if="!selectedEvent?.extendedProps?.meeting_link">
                                <p class="mt-1 text-sm text-gray-500">Empty</p>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <!-- FOOTER -->
            <div
                class="flex items-center justify-between gap-3 border-t border-gray-100 bg-gray-50 px-7 py-5 dark:border-gray-800 dark:bg-gray-950">
                <button @click="selectedEvent = null"
                    class="rounded-xl px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                    Close
                </button>

                <div class="flex gap-4">
                    {{-- <template x-if="!selectedEvent?.extendedProps?.fromGoogle">
                        <button @click="openEdit(selectedEvent)"
                            class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Edit
                        </button>
                    </template> --}}
                    <button @click="deleteEvent(selectedEvent)"
                        class="rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">
                        Delete
                    </button>

                </div>

            </div>

        </div>
    </div>


    <!-- TOAST -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed left-1/2 top-6 z-50 -translate-x-1/2">
        <div
            class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 shadow-[0_10px_25px_-10px_rgba(0,0,0,0.25)] backdrop-blur-md dark:bg-gray-900">
            <!-- ACCENT -->
            <div class="h-4 w-1 rounded-full"
                :class="{
                    'bg-green-500': toast.type === 'success',
                    'bg-red-500': toast.type === 'error',
                    'bg-gray-400': toast.type === 'info'
                }">
            </div>

            <!-- MESSAGE -->
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.message"></p>

            <!-- CLOSE -->
            <button @click="toast.show = false"
                class="ml-2 rounded-md p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                ✕
            </button>
        </div>
    </div>
</div>
<script>
    function calendarApp() {
        return {
            calendar: null,
            showModal: false,
            googleConnected: false,
            duration: '',
            manualEndTime: false,
            selectedEvent: null,
            hasFocusedMonth: false,

            editingTaskId: null,

            toast: {
                show: false,
                message: '',
                type: 'info',
            },

            newTask: {
                title: '',
                description: '', // ✅ ADD
                deadline: '',
                start_time: '',
                end_time: '',
                location: '',
                link: '',
                all_day: false, // ✅ ADD
            },


            async init() {
                await this.checkGoogle();
                this.initCalendar();
            },

            showToast(message, type = 'info') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;

                setTimeout(() => {
                    this.toast.show = false;
                }, 2500);
            },


            async checkGoogle() {
                try {
                    const res = await fetch('/api/google/calendar/status', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })

                    if (!res.ok) {
                        const text = await res.text()
                        console.error('Google status API error:', res.status, text)
                        this.googleConnected = false
                        return
                    }

                    const data = await res.json()
                    this.googleConnected = !!data.connected

                } catch (err) {
                    console.error('Google status fetch failed:', err)
                    this.googleConnected = false
                }
            },

            async disconnectGoogle() {
                if (!confirm('Disconnect Google Calendar? Your tasks will stay.')) return;

                const res = await fetch('/google/calendar/disconnect', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (res.ok) {
                    this.googleConnected = false;
                    this.showToast('Google Calendar disconnected');
                } else {
                    this.showToast('Failed to disconnect Google');
                }
            },



            initCalendar() {
                const now = new Date();
                this.calendar = new FullCalendar.Calendar(
                    document.getElementById('calendar'), {
                        // initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                        initialView: 'listWeek',

                        // ✅ OPEN ON TODAY
                        initialDate: now,

                        height: '100%',
                        editable: true,
                        selectable: true,
                        eventResizableFromStart: true,
                        expandRows: true,
                        handleWindowResize: true,

                        // ✅ FOCUS ON CURRENT TIME
                        nowIndicator: true,
                        scrollTime: now.toTimeString().slice(0, 8),

                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                        },

                        datesSet: (info) => {
                            if (
                                info.view.type === 'dayGridMonth' &&
                                !this.hasFocusedMonth
                            ) {
                                this.hasFocusedMonth = true;
                                this.calendar.gotoDate(new Date());
                            }
                        },



                        select: (info) => {
                            this.newTask.deadline = info.startStr.slice(0, 10);
                            this.newTask.start_time = info.startStr.slice(11, 16);
                            this.newTask.end_time = info.endStr?.slice(11, 16);
                            this.showModal = true;
                        },

                        eventDrop: info => this.syncMove(info),
                        eventResize: info => this.syncMove(info),

                        eventClick: (info) => {
                            if (info.event.extendedProps.fromGoogle) {
                                this.showToast('Google events are read-only');
                                return;
                            }

                            this.selectedEvent = info.event;
                        },


                        events: async (_, successCallback, failureCallback) => {
                            try {
                                const res = await fetch('/api/google/calendar/events', {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                })

                                // ❌ backend error (500, 401, 403, etc)
                                if (!res.ok) {
                                    const text = await res.text()
                                    console.error('Calendar API error:', res.status, text)
                                    failureCallback(text)
                                    return
                                }

                                // Read raw text first (VERY IMPORTANT)
                                const text = await res.text()
                                console.log('RAW RESPONSE:', text)

                                const events = JSON.parse(text)
                                console.log('EVENTS FROM BACKEND:', events)

                                successCallback(
                                    events.map(e => ({
                                        id: e.id,
                                        title: e.fromGoogle ? `${e.title} (Google)` : e.title,
                                        start: e.start,
                                        end: e.end ?? null,
                                        allDay: e.allDay ?? false,
                                        editable: !e.fromGoogle,

                                        extendedProps: {
                                            description: e.description ?? '', // ✅ ADD
                                            location: e.location ?? '',
                                        },

                                        backgroundColor: e.fromGoogle ? '#EEF2FF' : '#ECFDF5',
                                        borderColor: e.fromGoogle ? '#6366F1' : '#10B981',
                                        textColor: e.fromGoogle ? '#1E3A8A' : '#065F46',
                                    }))
                                )


                            } catch (err) {
                                console.error('Calendar fetch failed:', err)
                                failureCallback(err)
                            }
                        },


                    });

                this.calendar.render();
            },

            async syncMove(info) {
                const res = await fetch(`/api/tasks/${info.event.id}/move`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        start: info.event.start,
                        end: info.event.end
                    })
                });

                if (!res.ok) info.revert();
            },

            autoCalculateEnd() {
                if (this.manualEndTime || !this.newTask.start_time || !this.duration) return;
                const [h, m] = this.newTask.start_time.split(':').map(Number);
                const d = new Date();
                d.setHours(h, m);
                d.setMinutes(d.getMinutes() + Number(this.duration));
                this.newTask.end_time = d.toTimeString().slice(0, 5);
            },

            applyDuration() {
                this.manualEndTime = false;
                this.autoCalculateEnd();
            },

            openEdit(event) {
                this.newTask = {
                    title: event.title.replace(' (Google)', ''),
                    description: event.extendedProps.description ?? '',
                    deadline: event.start.toISOString().slice(0, 10),
                    start_time: event.allDay ? '' : event.start.toTimeString().slice(0, 5),
                    end_time: event.end && !event.allDay ?
                        event.end.toTimeString().slice(0, 5) : '',
                    location: event.extendedProps.location ?? '',
                    link: event.extendedProps.meeting_link ?? '',
                    all_day: event.allDay,
                };

                this.editingTaskId = event.id;
                this.showModal = true;
                this.selectedEvent = null;
            },

            validateTask() {
                // title
                if (!this.newTask.title.trim()) {
                    this.showToast('Title is required', 'error');
                    return false;
                }

                // date
                if (!this.newTask.deadline) {
                    this.showToast('Date is required', 'error');
                    return false;
                }

                // time (only if not all-day)
                if (!this.newTask.all_day) {
                    if (!this.newTask.start_time) {
                        this.showToast('Start time is required', 'error');
                        return false;
                    }

                    if (!this.newTask.end_time) {
                        this.showToast('End time is required', 'error');
                        return false;
                    }
                }

                // description
                if (!this.newTask.description.trim()) {
                    this.showToast('Description is required', 'error');
                    return false;
                }

                return true;
            },
            async submitTask() {
                if (!this.validateTask()) {
                    return; // ❌ stop submit
                }

                if (this.editingTaskId) {
                    await this.updateTask();
                } else {
                    await this.addTask();
                }
            },


            async updateTask() {
                try {
                    const payload = {
                        ...this.newTask,
                        start_time: this.newTask.all_day ? null : this.newTask.start_time,
                        end_time: this.newTask.all_day ? null : this.newTask.end_time,
                    };

                    const res = await fetch(`/api/tasks/${this.editingTaskId}`, {
                        method: 'PUT',
                        credentials: 'same-origin', // 🔥 REQUIRED
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    });


                    if (!res.ok) {
                        this.showToast('Failed to update task', 'error');
                        return;
                    }

                    this.calendar.refetchEvents();
                    this.showModal = false;
                    this.editingTaskId = null;

                    this.showToast('Task updated successfully', 'success');
                } catch (err) {
                    console.error(err);
                    this.showToast('Unexpected error occurred', 'error');
                }
            },

            async addTask() {
                try {
                    const payload = {
                        ...this.newTask,

                        // ✅ handle all-day events properly
                        start_time: this.newTask.all_day ? null : this.newTask.start_time,
                        end_time: this.newTask.all_day ? null : this.newTask.end_time,
                    };

                    // 1️⃣ Save to your app DB
                    const res = await // ALWAYS this
                    fetch('/tasks', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });


                    // ❌ backend failed
                    if (!res.ok) {
                        const text = await res.text();
                        console.error('Create task failed:', text);
                        this.showToast('Failed to create task');
                        return;
                    }

                    // 2️⃣ Sync to Google only if connected
                    if (this.googleConnected) {
                        const googleRes = await fetch('/google/calendar/event', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        if (!googleRes.ok) {
                            console.warn('Google sync failed');
                            this.showToast('Task created, but Google sync failed');
                        }
                    }

                    // 3️⃣ Refresh UI
                    this.calendar.refetchEvents();
                    this.showModal = false;

                    // 4️⃣ ✅ SUCCESS TOAST
                    this.showToast('Task created successfully');

                    // optional: reset form
                    this.newTask = {
                        title: '',
                        description: '',
                        deadline: '',
                        start_time: '',
                        end_time: '',
                        location: '',
                        link: '',
                        all_day: false,
                    };

                } catch (err) {
                    console.error(err);
                    this.showToast('Unexpected error occurred');
                }
            },

            async deleteEvent(event) {
                if (!confirm('Delete this task?')) return;

                const xsrfToken = decodeURIComponent(
                    document.cookie
                    .split('; ')
                    .find(row => row.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1] || ''
                );

                const res = await fetch(`/api/tasks/${event.id}`, {
                    method: 'DELETE',
                    credentials: 'include', // 🔥 REQUIRED for Sanctum
                    headers: {
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': xsrfToken, // 🔥 REQUIRED
                    }
                });

                if (!res.ok) {
                    this.showToast('Failed to delete task', 'error');
                    return;
                }

                event.remove();
                this.selectedEvent = null;
                this.showToast('Task deleted', 'success');
            }



        }
    }
</script>
