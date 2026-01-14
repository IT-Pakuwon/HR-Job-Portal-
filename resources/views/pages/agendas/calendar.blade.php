<div x-data="calendarApp()" x-init="init()"
    class="rounded-xl bg-white p-4 text-gray-900 dark:bg-gray-800 dark:text-white">

    <!-- Header & View Switch -->
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-lg font-bold tracking-wide" x-text="currentViewTitle"></h2>
        <nav class="space-x-2">
            <button @click="changeView('yearly')"
                :class="view === 'yearly' ? activeBtnClass : inactiveBtnClass">Year</button>
            <button @click="changeView('month')"
                :class="view === 'month' ? activeBtnClass : inactiveBtnClass">Month</button>
            <button @click="changeView('week')"
                :class="view === 'week' ? activeBtnClass : inactiveBtnClass">Week</button>
            <button @click="changeView('day')"
                :class="view === 'day' ? activeBtnClass : inactiveBtnClass">Day</button>
        </nav>
    </div>

    <!-- Navigation Buttons for Yearly and Monthly -->
    <div x-show="view === 'yearly' || view === 'month'" x-transition
        class="mb-6 flex items-center justify-between px-2">
        <button @click="view === 'yearly' ? prevYear() : prevMonth()" class="btn-nav">← Previous</button>
        <div class="text-base font-semibold" x-text="view === 'yearly' ? year : monthYearTitle"></div>
        <button @click="view === 'yearly' ? nextYear() : nextMonth()" class="btn-nav">Next →</button>
    </div>

    <!-- Calendar Container -->
    <div class="grid gap-6 xl:grid-cols-4">

        <!-- Main Calendar View -->
        <main
            class="overflow-auto rounded-lg border border-gray-300 bg-gray-50 p-4 shadow-sm xl:col-span-3 dark:border-gray-700 dark:bg-gray-900">

            <!-- Yearly View -->
            <div x-show="view === 'yearly'" x-transition class="grid grid-cols-3 gap-6">
                <template x-for="m in 12" :key="m">
                    <section
                        class="hover: cursor-pointer rounded-lg border bg-white p-4 shadow transition duration-300 dark:bg-gray-800"
                        @click="selectDayYearly(year, m - 1, 1)">
                        <header class="mb-2 text-center text-sm font-semibold text-indigo-600 dark:text-indigo-400"
                            x-text="monthNames[m - 1]"></header>
                        <div
                            class="mb-2 grid select-none grid-cols-7 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <template x-for="d in weekDays" :key="d">
                                <div x-text="d"></div>
                            </template>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs">
                            <template x-for="blank in blanksYearly(m - 1)" :key="'blank-' + blank">
                                <div>&nbsp;</div>
                            </template>
                            <template x-for="day in daysInMonthYearly(m - 1)" :key="day">
                                <div class="select-none rounded py-1 transition"
                                    :class="{
                                        'bg-indigo-600 text-white font-semibold  ': isTodayYearly(year, m - 1,
                                            day),
                                        'hover:bg-indigo-100 dark:hover:bg-indigo-700 cursor-pointer': !isTodayYearly(
                                            year, m - 1, day)
                                    }"
                                    x-text="day" @click.stop="selectDayYearly(year, m - 1, day)"></div>
                            </template>
                        </div>
                    </section>
                </template>
            </div>

            <!-- Monthly View -->
            <div x-show="view === 'month'" x-transition>
                <div
                    class="mb-4 grid select-none grid-cols-7 text-center text-sm font-semibold text-gray-600 dark:text-gray-400">
                    <template x-for="day in weekDays" :key="day">
                        <div x-text="day"></div>
                    </template>
                </div>

                <div class="grid grid-cols-7 gap-3 text-center">
                    <template x-for="blank in blanks" :key="'blank-' + blank">
                        <div class="py-5">&nbsp;</div>
                    </template>

                    <template x-for="day in days" :key="day">
                        <div x-init="$el.dataset.month = month;
                        $el.dataset.year = year" @click="selectDay(day)"
                            class="relative cursor-pointer select-none rounded py-4 text-sm transition"
                            :class="{
                                'bg-indigo-600 text-white font-bold  ': isToday(day),
                                'hover:bg-indigo-100 dark:hover:bg-indigo-700': !isToday(day),
                                'ring-2 ring-indigo-400 ring-offset-2': selectedDate === formatDateString(day, +$el
                                    .dataset.month, +$el.dataset.year)
                            }">
                            <span x-text="day"></span>
                            <span class="absolute bottom-2 right-2 h-3 w-3 rounded-full bg-red-500"
                                x-show="events[formatDateString(day, +$el.dataset.month, +$el.dataset.year)]?.length > 0"></span>
                        </div>
                    </template>

                </div>
            </div>

            <!-- Week View -->
            <div x-show="view === 'week'" x-transition>
                <!-- Week Navigation -->
                <div class="mb-4 flex items-center justify-between px-4 py-2">
                    <button @click="prevWeek()" class="btn-nav text-xs">← Previous Week</button>
                    <div class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                        x-text="`${formatDayWithDate(weekRange[0])} - ${formatDayWithDate(weekRange[6])}`"></div>
                    <button @click="nextWeek()" class="btn-nav text-xs">Next Week →</button>
                </div>

                <!-- Week Header -->
                <div
                    class="grid grid-cols-8 border-b bg-gray-100 text-center text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <div class="p-2">Hour</div>
                    <template x-for="(day, index) in weekRange" :key="index">
                        <div class="p-2" x-text="formatDayWithDate(day)"></div>
                    </template>
                </div>

                <!-- Week Hours -->
                <div class="h-[500px] select-none overflow-y-auto">
                    <template x-for="hour in 24" :key="hour">
                        <div class="grid grid-cols-8 border-t text-xs">
                            <div class="select-none p-2 text-right text-gray-500 dark:text-gray-400">
                                <span x-text="`${String(hour).padStart(2, '0')}:00`"></span>
                            </div>
                            <template x-for="(day, index) in weekRange" :key="index">
                                <div class="relative min-h-[60px] border-l p-1">
                                    <template x-for="event in eventsForDayHour(day, hour)" :key="event.id">
                                        <div class="mb-1 w-full rounded bg-indigo-300 px-1 py-0.5 text-[11px] shadow">
                                            <div x-text="event.title"></div>
                                            <div class="text-[10px] text-gray-600"
                                                x-text="formatEventTime(event.startdate, event.enddate)"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Day View -->
            <div x-show="view === 'day'" x-transition style="height: 500px; overflow-y:auto; user-select:none;">
                <div class="grid grid-cols-1 text-xs text-gray-600 dark:text-gray-300">
                    <template x-for="hour in 24" :key="hour">
                        <div class="grid grid-cols-[60px_1fr] border-b border-gray-200 py-3 dark:border-gray-700">
                            <div class="select-none pr-3 text-right text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="`${String(hour).padStart(2,'0')}:00`"></span>
                            </div>
                            <div>
                                <template x-for="event in eventsForHour(hour)" :key="event.id">
                                    <div class="mb-1 w-fit rounded bg-indigo-300 px-2 py-1 text-xs shadow">
                                        <div x-text="event.title"></div>
                                        <div class="text-[9px] text-gray-700"
                                            x-text="formatEventTime(event.startdate, event.enddate)"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </main>

        <!-- Sidebar - Agenda -->
        <aside class="rounded-lg bg-white p-4 shadow xl:col-span-1 dark:bg-gray-800">
            <h3 class="mb-3 border-b border-gray-300 pb-1 text-base font-semibold dark:border-gray-700">Events on <span
                    x-text="selectedDate"></span></h3>
            <ul class="max-h-[400px] space-y-3 overflow-auto">
                <template x-if="events[selectedDate] && events[selectedDate].length > 0">
                    <template x-for="event in events[selectedDate]" :key="event.id">
                        <li class="rounded border bg-indigo-50 p-3 shadow-sm dark:bg-indigo-900">
                            <div class="font-semibold text-indigo-700 dark:text-indigo-300" x-text="event.title"></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400"
                                x-text="formatEventTime(event.startdate, event.enddate)"></div>
                        </li>
                    </template>
                </template>
                <template x-if="!events[selectedDate] || events[selectedDate].length === 0">
                    <li class="italic text-gray-500 dark:text-gray-400">No events scheduled for this day.</li>
                </template>
            </ul>
        </aside>

    </div>
</div>

<script>
    function calendarApp() {
        return {
            view: 'yearly',
            year: new Date().getFullYear(),
            month: new Date().getMonth(),
            selectedDate: '',
            events: {},
            monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            weekDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],

            // Button classes for active/inactive
            activeBtnClass: 'px-3 py-1 rounded-md bg-indigo-600 text-white font-semibold  ',
            inactiveBtnClass: 'px-3 py-1 rounded-md text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-700 cursor-pointer',

            init() {
                this.selectedDate = this.formatDateString(new Date().getDate());
                this.loadMonthDays();
                this.loadEvents();
                this.initWeekRange();
            },

            changeView(newView) {
                this.view = newView;

                if (newView === 'month') {
                    if (this.month === null || this.year === null) {
                        this.month = new Date().getMonth();
                        this.year = new Date().getFullYear();
                    }
                    this.loadMonthDays();
                }

                if (newView === 'yearly') {
                    this.year = new Date().getFullYear();
                }

                if (newView === 'week') {
                    this.initWeekRange();
                }
            },

            // Yearly view helpers
            blanksYearly(monthIndex) {
                let firstDay = new Date(this.year, monthIndex, 1).getDay();
                return Array(firstDay).fill('');
            },
            daysInMonthYearly(monthIndex) {
                let days = new Date(this.year, monthIndex + 1, 0).getDate();
                return Array.from({
                    length: days
                }, (_, i) => i + 1);
            },
            isTodayYearly(y, m, d) {
                let today = new Date();
                return y === today.getFullYear() && m === today.getMonth() && d === today.getDate();
            },
            selectDayYearly(y, m, d) {
                this.year = y;
                this.month = m;
                this.selectedDate = this.formatDateString(d, m, y);
                this.changeView('month');
                this.loadMonthDays();
            },

            // Monthly view helpers
            blanks: [],
            days: [],
            loadMonthDays() {
                let firstDay = new Date(this.year, this.month, 1).getDay();
                this.blanks = Array(firstDay).fill('');
                let daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
                this.days = Array.from({
                    length: daysInMonth
                }, (_, i) => i + 1);
            },
            isToday(day) {
                let today = new Date();
                return this.year === today.getFullYear() && this.month === today.getMonth() && day === today.getDate();
            },
            selectDay(day) {
                this.selectedDate = this.formatDateString(day);
                // this.changeView('day');
            },
            formatDateString(day, month = this.month, year = this.year) {
                let mm = (month + 1).toString().padStart(2, '0');
                let dd = day.toString().padStart(2, '0');
                return `${year}-${mm}-${dd}`;
            },

            get currentViewTitle() {
                if (this.view === 'yearly') return `Yearly View: ${this.year}`;
                if (this.view === 'month') return `${this.monthNames[this.month]} ${this.year}`;
                if (this.view === 'week') return `Week View`;
                if (this.view === 'day') return `Day View: ${this.selectedDate}`;
                return '';
            },
            get monthYearTitle() {
                return `${this.monthNames[this.month]} ${this.year}`;
            },
            nextYear() {
                this.year++;
            },
            prevYear() {
                this.year--;
            },
            nextMonth() {
                if (this.month === 11) {
                    this.month = 0;
                    this.year++;
                } else {
                    this.month++;
                }
                this.loadMonthDays();
            },
            prevMonth() {
                if (this.month === 0) {
                    this.month = 11;
                    this.year--;
                } else {
                    this.month--;
                }
                this.loadMonthDays();
            },

            // Week view setup
            weekRange: [],
            initWeekRange() {
                let today = new Date();
                let dayOfWeek = today.getDay();
                this.weekRange = [];
                for (let i = 0; i < 7; i++) {
                    let d = new Date(today);
                    d.setDate(today.getDate() - dayOfWeek + i);
                    this.weekRange.push(d);
                }
            },
            eventsForDayHour(day, hour) {
                let dateStr = day.toISOString().split('T')[0];
                if (!this.events[dateStr]) return [];
                return this.events[dateStr].filter(e => {
                    let startHour = new Date(e.startdate).getHours();
                    let endHour = new Date(e.enddate).getHours();
                    return hour >= startHour && hour <= endHour;
                });
            },
            formatDayWithDate(day) {
                let options = {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric'
                };
                return day.toLocaleDateString(undefined, options);
            },
            prevWeek() {
                this.shiftWeek(-7);
            },
            nextWeek() {
                this.shiftWeek(7);
            },
            shiftWeek(days) {
                this.weekRange = this.weekRange.map(d => {
                    let newD = new Date(d);
                    newD.setDate(newD.getDate() + days);
                    return newD;
                });
            },

            // Day view helpers
            eventsForHour(hour) {
                if (!this.selectedDate || !this.events[this.selectedDate]) return [];
                return this.events[this.selectedDate].filter(event => {
                    let start = new Date(event.startdate).getHours();
                    let end = new Date(event.enddate).getHours();
                    return hour >= start && hour <= end;
                });
            },
            formatEventTime(start, end) {
                let s = new Date(start);
                let e = new Date(end);
                return `${s.getHours()}:${s.getMinutes().toString().padStart(2, '0')} - ${e.getHours()}:${e.getMinutes().toString().padStart(2, '0')}`;
            },
            loadEvents() {
                let self = this;
                const monthParam = this.month + 1; // karena this.month = 0-11
                const yearParam = this.year;

                $.ajax({
                    url: `/api/agendas/month?month=${monthParam}&year=${yearParam}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log('Data agenda dari API:', data);
                        self.events = {};

                        if (!Array.isArray(data)) {
                            console.error('Response data bukan array!');
                            return;
                        }

                        data.forEach(event => {
                            if (!event.startdate) return;
                            let dateKey = event.startdate.split('T')[0];
                            if (!self.events[dateKey]) {
                                self.events[dateKey] = [];
                            }
                            self.events[dateKey].push(event);
                        });

                        console.log('Events terproses:', self.events);
                    },
                    error: function(xhr) {
                        console.error('Gagal load events:', xhr.responseText);
                    }
                });
            },

        }
    }
</script>

<style>
    .btn-nav {
        @apply px-3 py-1 rounded-md bg-indigo-100 dark:bg-indigo-700 text-indigo-700 dark:text-indigo-100 font-semibold shadow-sm hover:bg-indigo-200 dark:hover:bg-indigo-600 transition cursor-pointer;
    }

    .btn-nav:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.6);
    }
</style>

<script>
    $(document).ready(function() {
        $('#addAgendaBtn').click(function() {
            $('#agendaForm')[0].reset();
            $('#agendaModal').removeClass('hidden');
        });

        $('#closeModal').click(function() {
            $('#agendaModal').addClass('hidden');
        });

        $('.select2').select2({
            placeholder: "Select Participants",
            allowClear: true,
            width: '100%'
        });

    });
</script>

<script>
    function editAgenda(agendaId) {
        $.get(`/api/agendas/${agendaId}`, function(event) {
            $('#agendaModal').removeClass('hidden');
            $('#agendaForm')[0].reset();

            // Set Data
            $('#agenda_id').val(event.id);
            $('#cpnyid').val(event.cpnyid);
            $('#departementid').val(event.departementid);
            $('#title').val(event.title);
            $('#description').val(event.description || '');
            $('#startdate').val(event.startdate.replace(" ", "T"));
            $('#enddate').val(event.enddate.replace(" ", "T"));

            // Set participants jika ada
            if (event.participant) {
                let participantArray = event.participant.split(',');
                $('#participant').val(participantArray).trigger('change');
            }

            // Ubah teks tombol
            $('#submitAgenda').text("Update Agenda");

            // Ubah action form menjadi update
            $('#agendaForm').attr('data-action', 'update');
        }).fail(function() {
            alert("Gagal mengambil data agenda.");
        });
    }
</script>

<script>
    $('#agendaForm').submit(function(e) {
        e.preventDefault();

        let formData = new FormData();
        formData.append('cpnyid', $('#cpnyid').val());
        formData.append('departementid', $('#departementid').val());
        formData.append('title', $('#title').val());
        formData.append('description', $('#description').val());
        formData.append('startdate', $('#startdate').val());
        formData.append('enddate', $('#enddate').val());

        // Tambahkan participants
        let participants = $('#participant').val();
        if (participants) {
            participants.forEach(participant => {
                formData.append('participant[]', participant);
            });
        }

        // Cek apakah form dalam mode edit atau tambah
        let action = $('#agendaForm').attr('data-action');
        let url = action === 'update' ?
            `/api/agendas/${$('#agenda_id').val()}` // Update jika edit
            :
            "{{ route('agendas.store') }}"; // Tambah jika baru

        // let method = action === 'update' ? "PUT" : "POST";
        let method = action === 'update' ? "POST" : "POST"; // tetap POST semua
        if (action === 'update') {
            formData.append('_method', 'PUT'); // spoof method Laravel
        }


        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert("Agenda berhasil disimpan!");
                $('#agendaModal').addClass('hidden');
                location.reload(); // Refresh daftar agenda
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
</script>
