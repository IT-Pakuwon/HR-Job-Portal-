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
                             Meeting Room is the module used to reserve internal meeting rooms.
                             It shows room availability on a calendar, lets you book a room for a specific
                             date and time, automatically prevents double-booking of the same room,
                             and can generate a Microsoft Teams link for the meeting when a room's accessory
                             supports it. Any employee with access to the menu can book a room.
                         </span>
                         <span x-show="lang==='id'">
                             Meeting Room adalah modul untuk memesan ruang meeting internal.
                             Modul ini menampilkan ketersediaan ruangan dalam bentuk kalender, memungkinkan Anda
                             memesan ruangan untuk tanggal dan jam tertentu, secara otomatis mencegah bentrok
                             jadwal pada ruangan yang sama, dan dapat membuatkan link Microsoft Teams untuk
                             meeting tersebut jika accessory ruangan mendukungnya. Setiap karyawan yang memiliki
                             akses ke menu ini dapat memesan ruangan.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Use Meeting Room to book a physical room for an internal or external discussion.
                             If you only need a Microsoft Teams link without booking a physical room,
                             use the Meeting Teams feature instead.
                         </span>
                         <span x-show="lang==='id'">
                             Gunakan Meeting Room untuk memesan ruangan fisik untuk diskusi internal maupun eksternal.
                             Jika Anda hanya butuh link Microsoft Teams tanpa memesan ruangan fisik,
                             gunakan fitur Meeting Teams.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Room Access</span>
                             <span x-show="lang==='id'">1.1 Akses Ruangan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Most rooms are public and can be booked by anyone. Some rooms may be
                                 restricted to a specific list of users — if a room is restricted and
                                 you are not on its allowed list, it will not be available for you to book.
                             </span>
                             <span x-show="lang==='id'">
                                 Sebagian besar ruangan bersifat publik dan dapat dipesan oleh siapa saja.
                                 Beberapa ruangan dapat dibatasi hanya untuk daftar user tertentu — jika sebuah
                                 ruangan dibatasi dan Anda tidak termasuk dalam daftar yang diizinkan, ruangan
                                 tersebut tidak akan tersedia untuk Anda pesan.
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
                         <span x-show="lang==='en'">2. Booking a Meeting Room</span>
                         <span x-show="lang==='id'">2. Memesan Ruang Meeting</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The Meeting Room page displays a calendar with each room as a column (resource).
                             Click and drag on an empty time slot for the room you want, or click
                             <strong>Create Meeting</strong>, to open the booking form.
                         </span>
                         <span x-show="lang==='id'">
                             Halaman Meeting Room menampilkan kalender dengan setiap ruangan sebagai kolom (resource).
                             Klik dan seret pada slot waktu kosong untuk ruangan yang diinginkan, atau klik
                             <strong>Create Meeting</strong>, untuk membuka form pemesanan.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Required Information</span>
                             <span x-show="lang==='id'">2.1 Informasi yang Wajib Diisi</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Start &amp; End Date/Time</span>
                                     <span x-show="lang==='id'">Tanggal/Jam Mulai &amp; Selesai</span>
                                 </strong> —
                                 <span x-show="lang==='en'">The meeting's start and end date and time. The end time must be after the start time, and the date cannot be in the past.</span>
                                 <span x-show="lang==='id'">Tanggal dan jam mulai serta selesai meeting. Jam selesai harus lebih besar dari jam mulai, dan tanggalnya tidak boleh tanggal yang sudah lewat.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Room</span>
                                     <span x-show="lang==='id'">Ruangan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Select which active room to book. Restricted rooms will reject the booking if you are not on the allowed list.</span>
                                 <span x-show="lang==='id'">Pilih ruangan aktif yang akan dipesan. Ruangan yang dibatasi akan menolak pemesanan jika Anda tidak termasuk dalam daftar yang diizinkan.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Title &amp; Description</span>
                                     <span x-show="lang==='id'">Judul &amp; Deskripsi</span>
                                 </strong> —
                                 <span x-show="lang==='en'">A short meeting title and a description of the agenda. Both are required.</span>
                                 <span x-show="lang==='id'">Judul singkat meeting dan deskripsi agenda. Keduanya wajib diisi.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Number of Participants</span>
                                     <span x-show="lang==='id'">Jumlah Peserta</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Total participant count for the meeting (minimum 1).</span>
                                 <span x-show="lang==='id'">Total jumlah peserta meeting (minimal 1).</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Internal Participants</span>
                                     <span x-show="lang==='id'">Peserta Internal</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Select internal employees from the user list. They will be added as meeting invitees and receive the invitation email.</span>
                                 <span x-show="lang==='id'">Pilih karyawan internal dari daftar user. Mereka akan ditambahkan sebagai undangan meeting dan menerima email undangan.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">External Participants</span>
                                     <span x-show="lang==='id'">Peserta Eksternal</span>
                                 </strong> —
                                 <span x-show="lang==='en'">If the meeting includes outside guests, enable "External Participant" and add each guest's name, company, and email. Name, company, and email are required for every external row added.</span>
                                 <span x-show="lang==='id'">Jika meeting melibatkan tamu dari luar, aktifkan "External Participant" lalu tambahkan nama, perusahaan, dan email setiap tamu. Nama, perusahaan, dan email wajib diisi untuk setiap baris peserta eksternal yang ditambahkan.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Accessories</span>
                                     <span x-show="lang==='id'">Accessories</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Optional equipment tied to the selected room (e.g. projector, conferencing device). If you choose an accessory configured for Microsoft Teams, the system automatically creates a Teams meeting and attaches the join link.</span>
                                 <span x-show="lang==='id'">Perlengkapan opsional yang terkait dengan ruangan yang dipilih (misalnya proyektor, perangkat conference). Jika Anda memilih accessory yang dikonfigurasi untuk Microsoft Teams, sistem akan otomatis membuat Teams meeting dan menyertakan link join-nya.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 You cannot book a room for a date in the past, and bookings cannot be made
                                 too far in advance — the maximum advance booking window is controlled by
                                 system settings (15 days by default). If your selected date/time overlaps
                                 with an existing booking for the same room, the system will reject the
                                 booking and tell you the conflicting time slot.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda tidak dapat memesan ruangan untuk tanggal yang sudah lewat, dan pemesanan
                                 tidak dapat dilakukan terlalu jauh ke depan — batas maksimal pemesanan diatur
                                 oleh pengaturan sistem (default 15 hari). Jika tanggal/jam yang Anda pilih
                                 bentrok dengan pemesanan lain pada ruangan yang sama, sistem akan menolak
                                 pemesanan dan menampilkan jam yang bentrok tersebut.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Calendar View</span>
                             <span x-show="lang==='id'">2.2 Tampilan Kalender</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each room has its own color on the calendar, making it easy to see at a
                                 glance which rooms are booked at what times. Click any existing event on the
                                 calendar to open its detail view, which shows the title, time, room, PIC,
                                 participant count, description, and the Teams link if one was generated.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap ruangan memiliki warna tersendiri pada kalender, sehingga mudah untuk
                                 melihat sekilas ruangan mana yang sedang dipesan pada jam berapa. Klik event
                                 yang sudah ada pada kalender untuk membuka tampilan detailnya, yang menampilkan
                                 judul, waktu, ruangan, PIC, jumlah peserta, deskripsi, dan link Teams jika ada.
                             </span>
                         </p>

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
                         <span x-show="lang==='en'">3. Viewing &amp; Managing Your Bookings</span>
                         <span x-show="lang==='id'">3. Melihat &amp; Mengelola Pemesanan Anda</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Open any booking from the calendar to see its full detail, including the list
                             of internal and external participants. From the detail view you can edit or
                             cancel the booking, subject to the rules below.
                         </span>
                         <span x-show="lang==='id'">
                             Buka pemesanan mana pun dari kalender untuk melihat detail lengkapnya, termasuk
                             daftar peserta internal dan eksternal. Dari tampilan detail, Anda dapat mengedit
                             atau membatalkan pemesanan tersebut, sesuai aturan di bawah ini.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Edit</span>
                                 <span x-show="lang==='id'">Edit</span>
                             </strong> —
                             <span x-show="lang==='en'">Update the date/time, room, title, description, participants, or accessories. The system re-checks for scheduling conflicts and re-validates the booking window on every edit. If the room or schedule changes and the meeting has a Microsoft Teams link, the Teams meeting is updated or recreated automatically.</span>
                             <span x-show="lang==='id'">Mengubah tanggal/jam, ruangan, judul, deskripsi, peserta, atau accessories. Sistem akan memeriksa ulang bentrok jadwal dan memvalidasi ulang batas waktu pemesanan setiap kali diedit. Jika ruangan atau jadwal berubah dan meeting memiliki link Microsoft Teams, Teams meeting akan diperbarui atau dibuat ulang secara otomatis.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Cancel</span>
                                 <span x-show="lang==='id'">Batalkan</span>
                             </strong> —
                             <span x-show="lang==='en'">Cancels the booking. Cancelling also removes the associated Microsoft Teams meeting (if any) and sends a cancellation email to all participants.</span>
                             <span x-show="lang==='id'">Membatalkan pemesanan. Pembatalan juga akan menghapus Microsoft Teams meeting terkait (jika ada) dan mengirimkan email pembatalan ke semua peserta.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             By default you can only edit or cancel meetings you personally booked.
                             Users with the CS Access role can manage and cancel bookings made by other users
                             as well — useful for front-desk or admin staff coordinating room usage on behalf
                             of others.
                         </span>
                         <span x-show="lang==='id'">
                             Secara default Anda hanya dapat mengedit atau membatalkan meeting yang Anda pesan
                             sendiri. User dengan role CS Access dapat mengelola dan membatalkan pemesanan milik
                             user lain juga — berguna bagi staf front-desk atau admin yang mengoordinasikan
                             penggunaan ruangan atas nama pengguna lain.
                         </span>
                     </div>

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             Cancelling a booking cannot be undone. Make sure you select the correct meeting
                             before confirming.
                         </span>
                         <span x-show="lang==='id'">
                             Pembatalan pemesanan tidak dapat dibatalkan kembali. Pastikan Anda memilih meeting
                             yang benar sebelum mengonfirmasi.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Meeting Teams (Teams-Only Bookings)</span>
                         <span x-show="lang==='id'">4. Meeting Teams (Pemesanan Khusus Teams)</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Meeting Teams is a separate calendar view focused on rooms or resources set up
                             specifically for online meetings. It is used when the goal is simply to generate
                             a Microsoft Teams meeting (with a room/resource reserved for it), rather than
                             organizing a full room booking with a long participant list.
                         </span>
                         <span x-show="lang==='id'">
                             Meeting Teams adalah tampilan kalender terpisah yang berfokus pada ruangan atau
                             resource yang dikonfigurasi khusus untuk meeting online. Fitur ini digunakan ketika
                             tujuannya hanya untuk membuat Microsoft Teams meeting (dengan ruangan/resource yang
                             dipesan untuknya), bukan mengatur pemesanan ruangan lengkap dengan daftar peserta
                             yang panjang.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             The booking form here is simpler than the main Meeting Room form — it only asks
                             for the date/time, resource, title, description, and accessories. Once saved,
                             a Microsoft Teams meeting link and invitation email are generated automatically.
                         </span>
                         <span x-show="lang==='id'">
                             Form pemesanan di sini lebih sederhana dibandingkan form Meeting Room utama —
                             hanya meminta tanggal/jam, resource, judul, deskripsi, dan accessories. Setelah
                             disimpan, link meeting Microsoft Teams dan email undangan akan dibuat secara
                             otomatis.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 5 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">5. TV Room Display</span>
                         <span x-show="lang==='id'">5. Tampilan TV Ruangan</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The TV Room display is a read-only screen meant to be shown on a TV or monitor
                             mounted outside a meeting room. It shows the meeting currently happening in that
                             room right now (if any) and the list of upcoming meetings scheduled for the rest
                             of the day, so people walking by can see at a glance whether the room is in use.
                         </span>
                         <span x-show="lang==='id'">
                             Tampilan TV Ruangan adalah layar read-only yang ditampilkan pada TV atau monitor
                             yang dipasang di luar ruang meeting. Layar ini menampilkan meeting yang sedang
                             berlangsung di ruangan tersebut saat ini (jika ada) dan daftar meeting berikutnya
                             yang dijadwalkan untuk sisa hari itu, sehingga orang yang lewat dapat langsung
                             melihat apakah ruangan sedang digunakan.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             This display does not require login — it is meant to run unattended on a dedicated
                             screen. It cannot be used to create, edit, or cancel bookings; it only shows
                             schedule information for the specific room it is pointed at.
                         </span>
                         <span x-show="lang==='id'">
                             Tampilan ini tidak memerlukan login — dirancang untuk berjalan tanpa pengawasan
                             pada layar khusus. Tampilan ini tidak dapat digunakan untuk membuat, mengedit,
                             atau membatalkan pemesanan; tampilan ini hanya menampilkan informasi jadwal untuk
                             ruangan tertentu yang dituju.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 6 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s6')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">6. Room &amp; Accessory Setup</span>
                         <span x-show="lang==='id'">6. Pengaturan Ruangan &amp; Accessories</span>
                     </span>

                     <span x-text="openSection==='s6' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s6'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The Meeting Room Setup page is where rooms and their accessories are configured.
                             This is administrative configuration rather than day-to-day booking, and is
                             normally handled by GA/IT staff responsible for managing meeting room facilities.
                         </span>
                         <span x-show="lang==='id'">
                             Halaman Meeting Room Setup adalah tempat ruangan dan accessories-nya dikonfigurasi.
                             Ini merupakan konfigurasi administratif, bukan pemesanan sehari-hari, dan biasanya
                             dikelola oleh staf GA/IT yang bertanggung jawab atas fasilitas ruang meeting.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.1 Managing Rooms</span>
                             <span x-show="lang==='id'">6.1 Mengelola Ruangan</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Create / Edit Room</span>
                                     <span x-show="lang==='id'">Buat / Edit Ruangan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Define the room ID, room name, calendar color, and an optional approval user for the room.</span>
                                 <span x-show="lang==='id'">Menentukan ID ruangan, nama ruangan, warna kalender, dan user approval opsional untuk ruangan tersebut.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Active / Inactive Toggle</span>
                                     <span x-show="lang==='id'">Toggle Aktif / Nonaktif</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Inactive rooms no longer appear as a bookable option on the Meeting Room calendar.</span>
                                 <span x-show="lang==='id'">Ruangan yang nonaktif tidak lagi muncul sebagai pilihan yang bisa dipesan pada kalender Meeting Room.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Manage Access</span>
                                     <span x-show="lang==='id'">Manage Access</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Restrict a room to a specific list of users. A room with no users in its access list remains open to everyone; once a user is added, only listed users (plus CS Access users) can book that room.</span>
                                 <span x-show="lang==='id'">Membatasi sebuah ruangan hanya untuk daftar user tertentu. Ruangan tanpa user dalam daftar aksesnya tetap terbuka untuk semua orang; setelah ada user yang ditambahkan, hanya user dalam daftar tersebut (ditambah user CS Access) yang dapat memesan ruangan itu.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.2 Managing Accessories</span>
                             <span x-show="lang==='id'">6.2 Mengelola Accessories</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each accessory is tied to a specific room and can optionally be linked to
                                 a Zoom or Microsoft Teams account. When a user selects an accessory configured
                                 with a Teams account while booking, the system automatically creates the
                                 Teams meeting for that booking. Accessories can also be set Active or Inactive.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap accessory terkait dengan ruangan tertentu dan secara opsional dapat
                                 dihubungkan dengan akun Zoom atau Microsoft Teams. Ketika user memilih accessory
                                 yang dikonfigurasi dengan akun Teams saat memesan, sistem akan otomatis membuat
                                 Teams meeting untuk pemesanan tersebut. Accessories juga dapat diatur statusnya
                                 menjadi Aktif atau Nonaktif.
                             </span>
                         </p>

                     </section>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Changes made here affect what every user sees when booking a room — deactivating
                             a room or accessory, or restricting room access, takes effect immediately on the
                             booking calendar.
                         </span>
                         <span x-show="lang==='id'">
                             Perubahan di sini memengaruhi apa yang dilihat semua user saat memesan ruangan —
                             menonaktifkan ruangan atau accessory, atau membatasi akses ruangan, langsung
                             berlaku pada kalender pemesanan.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
