     <div x-data="{
         lang: localStorage.getItem('manual_lang') || 'id',
         openSection: 's1',

         setLang(v) {
             this.lang = v;
             localStorage.setItem('manual_lang', v);
         },

         toggle(section) {
             this.openSection = this.openSection === section ? null : section;
         }
     }" class="max-w-9xl mx-auto space-y-6 p-6">

         <!-- ================= LANGUAGE TOGGLE ================= -->
         <div class="flex justify-end">
             <div
                 class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
                 <button @click="setLang('id')"
                     :class="lang === 'id'
                         ?
                         'bg-gray-900 text-white' :
                         'text-gray-600 dark:text-gray-300'"
                     class="rounded-md px-4 py-1.5 text-sm font-medium transition">
                     ID
                 </button>
                 <button @click="setLang('en')"
                     :class="lang === 'en'
                         ?
                         'bg-gray-900 text-white' :
                         'text-gray-600 dark:text-gray-300'"
                     class="rounded-md px-4 py-1.5 text-sm font-medium transition">
                     EN
                 </button>
             </div>
         </div>

         <!-- ================= DATA DISCLAIMER ================= -->
         <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">

             <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                 <span x-show="lang === 'en'">Information</span>
                 <span x-show="lang === 'id'">Informasi</span>
             </h3>

             <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                 <span x-show="lang === 'en'">
                     All data shown in this manual (screenshots, numbers, names, and documents)
                     are dummy data used for illustration purposes only.
                 </span>
                 <span x-show="lang === 'id'">
                     Seluruh data yang ditampilkan dalam manual ini (screenshot, angka, nama, dan dokumen)
                     merupakan data dummy yang digunakan hanya sebagai contoh.
                 </span>
             </p>

         </div>

         <!-- ================= SECTION 1 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s1')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">1. Overview</span>
                         <span x-show="lang==='id'">1. Gambaran Umum</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Event Calendar is where casual leasing exhibitions, promotion events, and mall
                             sales promotions are scheduled against the available event locations. The
                             calendar shows every location on its own row so you can see at a glance which
                             spots are booked, on which dates, and by whom.
                         </span>
                         <span x-show="lang==='id'">
                             Event Calendar adalah tempat untuk menjadwalkan casual leasing exhibition,
                             promotion event, dan mall sales promotion pada lokasi event yang tersedia.
                             Kalender menampilkan setiap lokasi pada barisnya masing-masing sehingga Anda
                             dapat melihat sekilas lokasi mana yang sudah dibooking, pada tanggal berapa,
                             dan oleh siapa.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Reading the Calendar</span>
                             <span x-show="lang==='id'">1.1 Membaca Kalender</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <span x-show="lang==='en'">Rows are grouped by Company and Department, then by Location.</span>
                                 <span x-show="lang==='id'">Baris dikelompokkan berdasarkan Company dan Department, lalu Location.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">Columns are the days of the selected month, grouped into weeks. Weekends and national holidays are shaded so they stand out.</span>
                                 <span x-show="lang==='id'">Kolom menunjukkan tanggal pada bulan yang dipilih, dikelompokkan per minggu. Akhir pekan dan hari libur nasional ditandai dengan warna agar mudah terlihat.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">Use the arrow buttons and the Today button above the calendar to move between months.</span>
                                 <span x-show="lang==='id'">Gunakan tombol panah dan tombol Today di atas kalender untuk berpindah antar bulan.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">Only companies and departments you have access to are shown. If nothing appears, ask an admin to set up a location for your company and department.</span>
                                 <span x-show="lang==='id'">Hanya company dan department yang menjadi hak akses Anda yang ditampilkan. Jika tidak ada yang muncul, minta admin untuk membuat lokasi untuk company dan department Anda.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Event Status Colors</span>
                             <span x-show="lang==='id'">1.2 Warna Status Event</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each event bar is colored based on its status, shown in the legend at the
                                 top-right of the page:
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap bar event diberi warna sesuai statusnya, seperti pada legenda di
                                 pojok kanan atas halaman:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Booked</strong> —
                                 <span x-show="lang==='en'">the location has been reserved for this event, but it isn't confirmed yet.</span>
                                 <span x-show="lang==='id'">lokasi sudah dipesan untuk event ini, namun belum dikonfirmasi.</span>
                             </li>
                             <li>
                                 <strong>Confirmed</strong> —
                                 <span x-show="lang==='en'">the event is confirmed to proceed.</span>
                                 <span x-show="lang==='id'">event sudah dipastikan akan berlangsung.</span>
                             </li>
                             <li>
                                 <strong>Paid</strong> —
                                 <span x-show="lang==='en'">the event's contract payment has been settled.</span>
                                 <span x-show="lang==='id'">pembayaran kontrak untuk event ini sudah diselesaikan.</span>
                             </li>
                         </ul>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each bar also shows the event ID, the event name, and a small avatar for the
                                 person who created it. Hover over a bar to see a quick preview before opening it.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap bar juga menampilkan ID event, nama event, dan avatar kecil dari orang
                                 yang membuat event tersebut. Arahkan kursor ke bar untuk melihat pratinjau
                                 singkat sebelum membukanya.
                             </span>
                         </p>

                     </section>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 2 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s2')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">2. Creating an Event</span>
                         <span x-show="lang==='id'">2. Membuat Event</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             There are two ways to open the Create Event form — if you have permission
                             to create events, you'll see both:
                         </span>
                         <span x-show="lang==='id'">
                             Ada dua cara untuk membuka form Create Event — jika Anda memiliki hak untuk
                             membuat event, kedua cara ini akan tersedia:
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <span x-show="lang==='en'">Click the <strong>New Event</strong> button at the top-right of the page, or</span>
                             <span x-show="lang==='id'">Klik tombol <strong>New Event</strong> di pojok kanan atas halaman, atau</span>
                         </li>
                         <li>
                             <span x-show="lang==='en'">Click and drag across the date cells on a location's row to pre-fill that location and date range on the form.</span>
                             <span x-show="lang==='id'">Klik dan seret (drag) pada sel tanggal di baris sebuah lokasi untuk mengisi otomatis lokasi dan rentang tanggal pada form.</span>
                         </li>
                     </ul>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Event Fields</span>
                             <span x-show="lang==='id'">2.1 Field Event</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Event Name</strong> —
                                 <span x-show="lang==='en'">required. The name of the event.</span>
                                 <span x-show="lang==='id'">wajib diisi. Nama event.</span>
                             </li>
                             <li>
                                 <strong>Tenant / Event Company Name</strong> —
                                 <span x-show="lang==='en'">the brand or tenant running the event, if applicable.</span>
                                 <span x-show="lang==='id'">brand atau tenant yang menjalankan event, jika ada.</span>
                             </li>
                             <li>
                                 <strong>Location</strong> —
                                 <span x-show="lang==='en'">required. Only locations you have access to appear here.</span>
                                 <span x-show="lang==='id'">wajib diisi. Hanya lokasi yang menjadi hak akses Anda yang muncul di sini.</span>
                             </li>
                             <li>
                                 <strong>Event Type</strong> —
                                 <span x-show="lang==='en'">required. Casual Leasing Exhibition, Promotion Event, or Mall Sales Promotion.</span>
                                 <span x-show="lang==='id'">wajib diisi. Casual Leasing Exhibition, Promotion Event, atau Mall Sales Promotion.</span>
                             </li>
                             <li>
                                 <strong>Event Status</strong> —
                                 <span x-show="lang==='en'">required. Booked, Confirmed, or Paid — see the status colors above.</span>
                                 <span x-show="lang==='id'">wajib diisi. Booked, Confirmed, atau Paid — lihat warna status di atas.</span>
                             </li>
                             <li>
                                 <strong>Start Date / End Date</strong> —
                                 <span x-show="lang==='en'">required. The end date cannot be earlier than the start date.</span>
                                 <span x-show="lang==='id'">wajib diisi. Tanggal selesai tidak boleh lebih awal dari tanggal mulai.</span>
                             </li>
                             <li>
                                 <strong>PIC Event / PIC External</strong> —
                                 <span x-show="lang==='en'">the internal person(s) in charge (you can select more than one), plus an optional external PIC name and phone number.</span>
                                 <span x-show="lang==='id'">PIC internal (dapat memilih lebih dari satu), ditambah nama dan nomor telepon PIC eksternal (opsional).</span>
                             </li>
                             <li>
                                 <strong>Total Contract</strong> —
                                 <span x-show="lang==='en'">the contract value for this event, if applicable.</span>
                                 <span x-show="lang==='id'">nilai kontrak untuk event ini, jika ada.</span>
                             </li>
                             <li>
                                 <strong>Description</strong> —
                                 <span x-show="lang==='en'">any additional notes about the event.</span>
                                 <span x-show="lang==='id'">catatan tambahan mengenai event.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 A location can only host up to 5 active events with overlapping dates at
                                 the same time. If you try to schedule a 6th overlapping event on the same
                                 location, the form will show an error and you'll need to pick a different
                                 date or location.
                             </span>
                             <span x-show="lang==='id'">
                                 Satu lokasi hanya dapat menampung maksimal 5 event aktif dengan tanggal
                                 yang saling tumpang tindih dalam waktu yang sama. Jika Anda mencoba
                                 menjadwalkan event ke-6 yang tumpang tindih pada lokasi yang sama, form
                                 akan menampilkan error dan Anda perlu memilih tanggal atau lokasi lain.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Events are saved immediately once you submit — there is no approval step.
                                 Make sure the details are correct before saving.
                             </span>
                             <span x-show="lang==='id'">
                                 Event akan langsung tersimpan setelah Anda submit — tidak ada proses
                                 approval. Pastikan detail sudah benar sebelum menyimpan.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. Viewing, Editing, and Deleting an Event</span>
                         <span x-show="lang==='id'">3. Melihat, Mengubah, dan Menghapus Event</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Viewing an Event</span>
                             <span x-show="lang==='id'">3.1 Melihat Detail Event</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click on any event bar to open its details in a read-only view. From there,
                                 click <strong>Edit</strong> to make changes, if you have permission to do so.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada bar event mana pun untuk membuka detailnya dalam tampilan
                                 read-only. Dari sana, klik <strong>Edit</strong> untuk melakukan perubahan,
                                 jika Anda memiliki hak untuk itu.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Who Can Edit or Delete</span>
                             <span x-show="lang==='id'">3.2 Siapa yang Dapat Mengubah atau Menghapus</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <span x-show="lang==='en'">You can edit or delete an event you created yourself.</span>
                                 <span x-show="lang==='id'">Anda dapat mengubah atau menghapus event yang Anda buat sendiri.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">Admins and General Managers can edit or delete any event within their own company.</span>
                                 <span x-show="lang==='id'">Admin dan General Manager dapat mengubah atau menghapus event apa pun di dalam company mereka.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Deleting an event removes it from the calendar. This cannot be undone from
                                 the calendar page — please double-check before deleting.
                             </span>
                             <span x-show="lang==='id'">
                                 Menghapus event akan menghilangkannya dari kalender. Aksi ini tidak dapat
                                 dibatalkan dari halaman kalender — pastikan sudah yakin sebelum menghapus.
                             </span>
                         </div>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Dates cannot be changed by dragging an event bar on the calendar — to move
                                 an event to a different date, open it and use the Edit form.
                             </span>
                             <span x-show="lang==='id'">
                                 Tanggal tidak dapat diubah dengan menyeret (drag) bar event pada kalender —
                                 untuk memindahkan event ke tanggal lain, buka event tersebut dan gunakan
                                 form Edit.
                             </span>
                         </p>

                     </section>

                 </div>
             </div>

         </section>

         @if(auth()->check() && in_array('admin', auth()->user()->roles(), true))
         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. For Admins: Setting Up Locations</span>
                         <span x-show="lang==='id'">4. Untuk Admin: Mengatur Lokasi</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click the <strong>Setup</strong> button at the top-right of the calendar page to
                             manage event locations — the rows that appear on the calendar. From there you
                             can add a new location, edit an existing one, or deactivate a location so it no
                             longer appears in the calendar.
                         </span>
                         <span x-show="lang==='id'">
                             Klik tombol <strong>Setup</strong> di pojok kanan atas halaman kalender untuk
                             mengelola lokasi event — baris-baris yang muncul pada kalender. Dari sana Anda
                             dapat menambahkan lokasi baru, mengubah lokasi yang sudah ada, atau
                             menonaktifkan lokasi agar tidak lagi muncul di kalender.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Each location is tied to a company and one or more departments — this is what
                             controls which users can see and book that location. If a user reports an empty
                             calendar, check that a location has been set up for their company and department.
                         </span>
                         <span x-show="lang==='id'">
                             Setiap lokasi terikat pada satu company dan satu atau beberapa department — hal
                             ini yang menentukan pengguna mana yang dapat melihat dan membooking lokasi
                             tersebut. Jika ada pengguna melaporkan kalender kosong, periksa apakah lokasi
                             sudah dibuat untuk company dan department mereka.
                         </span>
                     </div>

                 </div>
             </div>

         </section>
         @endif

     </div>
