<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">      
        <div class="grid grid-cols-12 gap-6">   
            {{-- <x-agendas.agendas-calendar /> --}}
    <div x-data="calendarApp()" x-init="init()" class="flex flex-col col-span-full sm:col-span-6 xl:col-span-12 bg-white dark:bg-gray-800 shadow-xs rounded-xl p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white" x-text="currentViewTitle"></h2>
            <div class="flex gap-2">
                <button id="addAgendaBtn" class="px-3 py-1 rounded bg-blue-200 dark:bg-blue-700 text-blue-700/60 dark:text-white hover:bg-blue-300 dark:hover:bg-blue-600 transition">Create</button>
                <button @click="changeView('month')" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700/60 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition">Month</button>
                <button @click="changeView('week')" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700/60 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition">Week</button>
                <button @click="changeView('day')" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 text-gray-700/60 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition">Day</button>
            </div>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 flex-grow">
            <div class="xl:col-span-3 border-r pr-6 h-full overflow-auto flex-grow">
                <!-- Month View -->
                <div x-show="view === 'month'">
                    <div class="grid grid-cols-7 text-center text-lg font-semibold text-gray-600 dark:text-gray-400 mb-4">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>
                    <div class="grid grid-cols-7 text-center gap-2">
                        <template x-for="blank in blanks">
                            <div class="py-3"></div>
                        </template>
                        {{-- <template x-for="day in days">
                            <div @click="selectDay(day)" class="py-4 text-lg cursor-pointer rounded transition"
                                :class="{'bg-violet-500 text-white font-bold shadow-lg': isToday(day), 'hover:bg-gray-200 dark:hover:bg-gray-700': !isToday(day)}">
                                <span x-text="day"></span>
                            </div>
                        </template> --}}
                        <template x-for="day in days">
                            <div @click="selectDay(day)" 
                                 class="relative py-4 text-lg cursor-pointer rounded transition"
                                 :class="{
                                    'bg-violet-500 text-white font-bold shadow-lg': isToday(day),
                                    'hover:bg-gray-200 dark:hover:bg-gray-700': !isToday(day)
                                 }">
                                 
                                <span x-text="day"></span>
                                
                                <!-- Bulatan merah di kanan bawah jika ada event -->
                                <span 
                                    class="absolute bottom-1 right-1 w-2 h-2 bg-red-500 rounded-full"
                                    x-show="events[formatDateString(day)]?.length > 0">
                                </span>
                            </div>
                        </template>
                        
                    </div>
                </div>
                <!-- Week View -->
                <div x-show="view === 'week'">
                    <div class="flex justify-between text-center text-lg font-semibold text-gray-600 dark:text-gray-400 mb-4">
                        <template x-for="day in weekDays">
                            <div class="w-1/7 py-2" x-text="day"></div>
                        </template>
                    </div>
                    <div class="flex gap-2">
                        <template x-for="day in weekRange">
                            <div class="w-1/7 p-4 bg-gray-100 dark:bg-gray-700 rounded cursor-pointer" @click="selectDay(day)">
                                <span x-text="day"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <!-- Day View -->
                <div x-show="view === 'day'" class="text-center">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4" x-text="selectedDate || 'Select a day'"></h3>
                    {{-- <p class="text-gray-600 dark:text-gray-300">Events for this day:</p> --}}
                    <div class="text-gray-600 dark:text-gray-300 text-sm text-left space-y-2" x-html="dailyEventSummary"></div>


                </div>
            </div>
            <!-- Event Details (Takes up 1/4 of space) -->
            <div class="xl:col-span-1 xl:row-span-1 h-full">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-">Agenda 📝</h3>
                <p class="text-s text-gray-500 dark:text-white mb-4">View your agenda!</p>
                <div class="border p-4 rounded-lg bg-white dark:bg-gray-700 h-80 min-h-0 overflow-auto">
                    <h4 class="text-lg text-gray-800 dark:text-white font-semibold mb-2" x-text="selectedDate || 'Select a date'"></h4>
                    <div class="divide-y divide-gray-300 dark:divide-gray-600">
                        <template x-for="event in events[selectedDate] || []">
                            <div class="flex items-center justify-between py-3">
                                {{-- <div class="flex items-center space-x-3">
                                    <input type="checkbox" x-model="event.completed"
                                        class="w-5 h-5 indigo-green-500 border-gray-300 rounded focus:ring-0">
                                    <span class="text-gray-900 dark:text-white"
                                        :class="{'line-through text-gray-500': event.completed}"
                                        x-text="event.name"></span>
                                </div> --}}
                                {{-- <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500 dark:text-gray-300" x-text="event.dateLabel"></span>
                                    <a :href="event.url" target="_blank"
                                       class="px-3 py-1 text-sm font-medium bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition duration-200">
                                        View Details
                                    </a>
                                </div> --}}
                            </div>
                        </template>
                        <p x-show="!(events[selectedDate] || []).length" class="text-lg text-gray-400 text-center py-4">
                            <div id="agendaList">
                                <p class="text-lg text-gray-400 text-center py-4">Loading...</p>
                            </div>
                            
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="agendaModal" class="fixed inset-0 flex hidden items-center justify-center bg-black/50 z-50">
        <div class="dark:bg-gray-700 dark:text-white flex flex-col p-6 bg-white shadow-md rounded-lg w-1/3 max-w-md">
            <div class="border-b border-gray-500 dark:border-gray-500 mb-4">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Create Agenda</h2>
            </div>
    
            <form id="agendaForm" class="grid gap-4">
                <input type="hidden" id="agenda_id">

                <!-- Company & Department -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-semibold">Company</label>
                        <select id="cpnyid" class="w-full p-2 text-sm border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600" name="cpnyid" required>
                            @foreach($usercpny as $p)
                                <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">Department</label>
                        <select id="departementid" class="w-full p-2 text-sm border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600" name="departementid" required>
                            @foreach($userdept as $p)
                                <option value="{{ $p->deptname }}" {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>{{ $p->deptname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
    
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold">Title</label>
                    <input type="text" id="title" class="w-full p-2 text-sm border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600" name="title" required>
                </div>  
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold">Description</label>
                    <textarea id="description" class="w-full p-3 text-lg border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600" name="description" ></textarea>
                </div>                 
                <div>
                    <label class="block text-sm font-semibold">Participant</label>
                    <select id="participant" name="participant[]" class="w-full p-3 border rounded-lg select2" multiple >
                        @foreach($userlist as $p)
                            <option value="{{ $p->username }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>                
    
                <!-- Start Date & Due Date -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-semibold">Start Date & Time</label>
                        <input type="datetime-local" id="startdate" name="start_date" class="w-full p-2 text-sm border rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold">End Date & Time</label>
                        <input type="datetime-local" id="enddate" name="end_date" class="w-full p-2 text-sm border rounded-lg" required>
                    </div>
                </div>
                
    
                <!-- Buttons -->
                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" id="closeModal" class="bg-red-600 text-white px-3 py-2 text-sm rounded">Cancel</button>
                    <button type="submit" class="bg-green-600 text-white px-3 py-2 text-sm rounded">Submit</button>
                </div>
            </form>
        </div>
    </div>
    
    
    <script>        
        function calendarApp() {
            return {
                view: 'month',
                month: new Date().getMonth(),
                year: new Date().getFullYear(),
                selectedDate: null,
                events: {},
                eventsByDate: {}, // key: 'YYYY-MM-DD', value: array of events

                init() {
                    this.loadMonthAgendas(); // Load agenda saat inisialisasi
                },
                formatDateString(day) {
                    const month = (this.month + 1).toString().padStart(2, '0');
                    const dayStr = day.toString().padStart(2, '0');
                    return `${this.year}-${month}-${dayStr}`;
                },


                get currentViewTitle() {
                    return this.view.charAt(0).toUpperCase() + this.view.slice(1) + " View";
                },
                get blanks() {
                    return new Array(new Date(this.year, this.month, 1).getDay());
                },
                get days() {
                    return [...Array(new Date(this.year, this.month + 1, 0).getDate()).keys()].map(i => i + 1);
                },
                        get weekDays() {
                            return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        },
                        get weekRange() {
                            let today = new Date();
                            let start = new Date(today.setDate(today.getDate() - today.getDay()));
                            return [...Array(7).keys()].map(i => start.getDate() + i);
                        },
                isToday(day) {
                    const today = new Date();
                    return today.getDate() === day && today.getMonth() === this.month && today.getFullYear() === this.year;
                },

                selectDay(day) {
                    // let dateObj = new Date(this.year, this.month, day);
                    let dateObj = new Date(Date.UTC(this.year, this.month, day));
                    this.selectedDate = dateObj.toISOString().split('T')[0]; // Format ke YYYY-MM-DD
                    
                    // Memanggil loadAgendas untuk memuat data agenda
                    this.loadAgendas(this.selectedDate);
                },
                changeView(viewType) {
                            this.view = viewType;
                        },

                get dailyEventSummary() {
                    if (!this.selectedDate || !this.events[this.selectedDate]) return 'Tidak ada agenda hari ini';

                    return this.events[this.selectedDate].map(event => {
                        const startDate = new Date(event.startdate);
                        const endDate = new Date(event.enddate);

                        const timeRange = `${startDate.toLocaleTimeString('id-ID', {
                            hour: '2-digit', minute: '2-digit'
                        })} - ${endDate.toLocaleTimeString('id-ID', {
                            hour: '2-digit', minute: '2-digit'
                        })}`;

                        const participants = event.participant?.split(',').join(', ') || 'Tidak ada peserta';

                        return `
                            <div class="mb-2 text-left">
                                <div class="font-semibold text-blue-600">${event.title}</div>
                                <div class="text-sm text-gray-500">⏰ ${timeRange}</div>
                                <div class="text-sm text-gray-500">📄 ${event.description || 'Tidak ada deskripsi'}</div>
                                <div class="text-sm text-gray-500">👥 ${participants}</div>
                            </div>
                        `;
                    }).join('');
                },

                loadAgendas(selectedDate) {
                    fetch(`/api/agendas/today?date=${selectedDate}`)
                        .then(response => response.json())
                        .then(data => {
                            this.events[selectedDate] = data; 
                            let agendaList = "";
                            if (data.length > 0) {
                                data.forEach(event => {
                                    let startDateObj = new Date(event.startdate);
                                    let endDateObj = new Date(event.enddate);

                                    let startDateFormatted = startDateObj.toLocaleDateString('id-ID', {
                                        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
                                    });

                                    let startTime = startDateObj.toLocaleTimeString('id-ID', {
                                        hour: '2-digit', minute: '2-digit'
                                    });

                                    let endTime = endDateObj.toLocaleTimeString('id-ID', {
                                        hour: '2-digit', minute: '2-digit'
                                    });

                                    let formattedDateTime;

                                    if (startDateObj.toDateString() === endDateObj.toDateString()) {
                                        // Jika start dan end di tanggal yang sama, tampilkan hanya satu tanggal
                                        formattedDateTime = `${startDateFormatted} pukul ${startTime} - ${endTime}`;
                                    } else {
                                        // Jika berbeda, tampilkan lengkap
                                        let endDateFormatted = endDateObj.toLocaleDateString('id-ID', {
                                            weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
                                        });
                                        formattedDateTime = `${startDateFormatted} pukul ${startTime} - ${endDateFormatted} pukul ${endTime}`;
                                    }
                                    agendaList += `<div class="p-4 border rounded-lg mb-2 bg-white dark:bg-gray-700">
                                        <h4 class="text-lg font-semibold cursor-pointer text-blue-600 hover:underline"
                                                onclick="editAgenda(${event.id})">
                                                ${event.title}
                                        </h4>                          
                                        <p class="text-sm text-gray-500">📅 ${formattedDateTime}</p>
                                    </div>`;
                                });
                            } else {
                                agendaList = `<p class="text-lg text-gray-400 text-center py-4">No events on ${selectedDate}</p>`;
                            }
                            document.getElementById('agendaList').innerHTML = agendaList;
                        })
                        .catch(error => console.error("Error fetching agendas:", error));
                },

                loadMonthAgendas() {
                    const year = this.year;
                    const month = (this.month + 1).toString().padStart(2, '0');
                    fetch(`/api/agendas/month?year=${year}&month=${month}`)
                        .then(response => response.json())
                        .then(data => {
                            // Reset event agar tidak tumpuk
                            this.events = {};

                            data.forEach(event => {
                                const dateKey = event.startdate.split(' ')[0]; // Ambil 'YYYY-MM-DD'
                                if (!this.events[dateKey]) {
                                    this.events[dateKey] = [];
                                }
                                this.events[dateKey].push(event);
                            });
                        })
                        .catch(error => {
                            console.error("Gagal memuat agenda bulanan:", error);
                        });
                },

               
            };
            
        }

    </script>
    <script>
        $(document).ready(function () {
            $('#addAgendaBtn').click(function () {
                $('#agendaForm')[0].reset();
                $('#agendaModal').removeClass('hidden');
            });

            $('#closeModal').click(function () {
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
            $.get(`/api/agendas/${agendaId}`, function (event) {
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
            }).fail(function () {
                alert("Gagal mengambil data agenda.");
            });
        }
    </script>

    <script>
        $('#agendaForm').submit(function (e) {
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
            let url = action === 'update'
                ? `/api/agendas/${$('#agenda_id').val()}` // Update jika edit
                : "{{ route('agendas.store') }}"; // Tambah jika baru

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
                success: function (response) {
                    alert("Agenda berhasil disimpan!");
                    $('#agendaModal').addClass('hidden');
                    location.reload(); // Refresh daftar agenda
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            });
        });
    </script>

    
         


</div>

</div>
</x-app-layout>
