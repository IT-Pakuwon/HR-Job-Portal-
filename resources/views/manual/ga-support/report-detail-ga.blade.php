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
                             Report Detail GA is a consolidated reporting hub for General Affair (GA) activities.
                             Instead of opening each GA module separately, you can review usage data for
                             Meeting Room, Meeting Teams/Zoom, Operational Car, Voucher Taxi, Free Parking, and
                             Car Expense from a single page. The page is read-only — it does not create, approve,
                             or modify any transaction; it only displays and exports data that already exists
                             in the respective modules.
                         </span>
                         <span x-show="lang==='id'">
                             Report Detail GA adalah halaman laporan terpusat untuk aktivitas General Affair (GA).
                             Daripada membuka setiap modul GA secara terpisah, Anda dapat meninjau data penggunaan
                             Meeting Room, Meeting Teams/Zoom, Operational Car, Voucher Taxi, Free Parking, dan
                             Car Expense dari satu halaman. Halaman ini bersifat read-only — tidak dapat membuat,
                             menyetujui, atau mengubah transaksi apa pun; hanya menampilkan dan mengekspor data
                             yang sudah ada di masing-masing modul.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Use Report Detail GA when you need a recap, audit trail, or printable/exportable
                             summary of GA activity for a period — for example for monthly reporting, cost
                             review, or utilization analysis. To submit or process a transaction, go to the
                             relevant module (e.g. Booking Car, Voucher Taxi) instead.
                         </span>
                         <span x-show="lang==='id'">
                             Gunakan Report Detail GA ketika Anda memerlukan rekap, jejak audit, atau ringkasan
                             yang dapat dicetak/diekspor dari aktivitas GA untuk suatu periode — misalnya untuk
                             laporan bulanan, review biaya, atau analisis pemakaian. Untuk mengajukan atau
                             memproses transaksi, gunakan modul terkait (misalnya Booking Car, Voucher Taxi).
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Who Can Access Which Report</span>
                             <span x-show="lang==='id'">1.1 Siapa yang Dapat Mengakses Laporan Apa</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Not every report type is visible to every user. The report cards shown at the
                                 top of the page depend on your role/access:
                             </span>
                             <span x-show="lang==='id'">
                                 Tidak semua jenis laporan terlihat oleh semua pengguna. Kartu laporan yang
                                 ditampilkan di bagian atas halaman tergantung pada role/akses Anda:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Meeting Room</strong> —
                                 <span x-show="lang==='en'">visible to users with CS Access (Customer Service / room admin access).</span>
                                 <span x-show="lang==='id'">terlihat untuk pengguna dengan CS Access (akses admin Customer Service / ruang meeting).</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Meeting Teams / Zoom</span>
                                     <span x-show="lang==='id'">Meeting Teams / Zoom</span>
                                 </strong> —
                                 <span x-show="lang==='en'">visible to users with Admin access.</span>
                                 <span x-show="lang==='id'">terlihat untuk pengguna dengan akses Admin.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Operational Car, Voucher Taxi, Free Parking, Car Expense</span>
                                     <span x-show="lang==='id'">Operational Car, Voucher Taxi, Free Parking, Car Expense</span>
                                 </strong> —
                                 <span x-show="lang==='en'">visible to users with GA Access.</span>
                                 <span x-show="lang==='id'">terlihat untuk pengguna dengan akses GA.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If a report card you expect to see is missing, you most likely don't have the
                                 corresponding access role. Contact your administrator or IT if you believe this
                                 is incorrect.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika kartu laporan yang Anda harapkan tidak muncul, kemungkinan besar Anda belum
                                 memiliki role akses yang sesuai. Hubungi administrator atau IT jika menurut Anda
                                 ini tidak seharusnya terjadi.
                             </span>
                         </div>

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
                         <span x-show="lang==='en'">2. Navigating Between Report Types</span>
                         <span x-show="lang==='id'">2. Berpindah Antar Jenis Laporan</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             At the top of the page you'll see a row of report cards, one per GA sub-module
                             you have access to (Meeting Room, Meeting Teams/Zoom, Operational Car,
                             Voucher Taxi, Free Parking, Car Expense). Click a card to switch the report
                             shown below — only one report is displayed at a time.
                         </span>
                         <span x-show="lang==='id'">
                             Di bagian atas halaman terdapat deretan kartu laporan, satu untuk setiap
                             sub-modul GA yang dapat Anda akses (Meeting Room, Meeting Teams/Zoom,
                             Operational Car, Voucher Taxi, Free Parking, Car Expense). Klik salah satu kartu
                             untuk mengganti laporan yang ditampilkan di bawahnya — hanya satu laporan yang
                             ditampilkan dalam satu waktu.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>Meeting Room</strong> —
                             <span x-show="lang==='en'">room booking and usage (physical rooms only).</span>
                             <span x-show="lang==='id'">booking dan penggunaan ruang meeting (ruang fisik saja).</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Meeting Teams / Zoom</span>
                                 <span x-show="lang==='id'">Meeting Teams / Zoom</span>
                             </strong> —
                             <span x-show="lang==='en'">online meeting activity (Teams Only / Zoom Only rooms).</span>
                             <span x-show="lang==='id'">aktivitas meeting online (ruang Teams Only / Zoom Only).</span>
                         </li>
                         <li>
                             <strong>Operational Car</strong> —
                             <span x-show="lang==='en'">vehicle booking and usage (also referred to as Booking Car in the system).</span>
                             <span x-show="lang==='id'">booking dan penggunaan kendaraan operasional (di sistem juga disebut Booking Car).</span>
                         </li>
                         <li>
                             <strong>Voucher Taxi</strong> —
                             <span x-show="lang==='en'">taxi voucher usage and trips.</span>
                             <span x-show="lang==='id'">penggunaan voucher taksi dan perjalanan.</span>
                         </li>
                         <li>
                             <strong>Free Parking</strong> —
                             <span x-show="lang==='en'">parking registration and access.</span>
                             <span x-show="lang==='id'">registrasi dan akses parkir.</span>
                         </li>
                         <li>
                             <strong>Car Expense</strong> —
                             <span x-show="lang==='en'">vehicle cost and expense records.</span>
                             <span x-show="lang==='id'">data biaya dan pengeluaran kendaraan.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             The page remembers which report you opened only for your current visit — when you
                             reload, it falls back to the default report for your role
                             (Operational Car for GA Access, Meeting Teams/Zoom for Admin, Meeting Room otherwise).
                         </span>
                         <span x-show="lang==='id'">
                             Halaman ini hanya mengingat laporan yang sedang Anda buka untuk kunjungan saat ini —
                             saat Anda memuat ulang, halaman akan kembali ke laporan default sesuai role Anda
                             (Operational Car untuk akses GA, Meeting Teams/Zoom untuk Admin, Meeting Room untuk lainnya).
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. Filtering and Reading Each Report</span>
                         <span x-show="lang==='id'">3. Memfilter dan Membaca Setiap Laporan</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Every report has a filter panel above its table. Set the filters you need, then
                             click <strong>Apply</strong> to reload the table, or <strong>Reset</strong> to clear
                             all filters back to default. The table itself supports paging and column sorting,
                             but does not have a free-text search box — filtering must be done through the
                             filter panel fields.
                         </span>
                         <span x-show="lang==='id'">
                             Setiap laporan memiliki panel filter di atas tabelnya. Atur filter yang dibutuhkan,
                             lalu klik <strong>Apply</strong> untuk memuat ulang tabel, atau <strong>Reset</strong>
                             untuk mengembalikan semua filter ke kondisi awal. Tabel mendukung paging dan
                             pengurutan kolom, namun tidak memiliki kotak pencarian bebas — pemfilteran harus
                             dilakukan melalui field pada panel filter.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Meeting Room</span>
                             <span x-show="lang==='id'">3.1 Meeting Room</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Filters: Date From, Date To, Room, Requester, Status (Active / Cancelled).</span>
                             <span x-show="lang==='id'">Filter: Date From, Date To, Room, Requester, Status (Active / Cancelled).</span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Columns: Doc ID, Date, Start, End, Room, Accessories, Title, Requester, Department, Participants, Type (Internal/External), External Company, Duration, Status.</span>
                             <span x-show="lang==='id'">Kolom: Doc ID, Date, Start, End, Room, Accessories, Title, Requester, Department, Participants, Type (Internal/External), External Company, Duration, Status.</span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Meeting Teams / Zoom</span>
                             <span x-show="lang==='id'">3.2 Meeting Teams / Zoom</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Filters: Date From, Date To, Requester, Platform (Zoom / Teams), Status (Active / Cancelled).</span>
                             <span x-show="lang==='id'">Filter: Date From, Date To, Requester, Platform (Zoom / Teams), Status (Active / Cancelled).</span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Columns: Doc ID, Date, Start, End, Platform, Title, Requester, Department, Type, Duration, Status.</span>
                             <span x-show="lang==='id'">Kolom: Doc ID, Date, Start, End, Platform, Title, Requester, Department, Type, Duration, Status.</span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Operational Car</span>
                             <span x-show="lang==='id'">3.3 Operational Car</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Filters: Date From, Date To, Requester, Status (On Progress, Completed, Revise, Rejected, Cancelled), Driver, Vehicle.</span>
                             <span x-show="lang==='id'">Filter: Date From, Date To, Requester, Status (On Progress, Completed, Revise, Rejected, Cancelled), Driver, Vehicle.</span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Columns: Doc ID, Booking Date, Start, End, Requester, Department, Purpose, Route (origin → destination), Passenger, Driver, Vehicle, Duration, Status.</span>
                             <span x-show="lang==='id'">Kolom: Doc ID, Booking Date, Start, End, Requester, Department, Purpose, Route (asal → tujuan), Passenger, Driver, Vehicle, Duration, Status.</span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Voucher Taxi</span>
                             <span x-show="lang==='id'">3.4 Voucher Taxi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Filters: Date From, Date To, Requester, Type Trip (One Way / Return Trip), Status (Pending, Completed, Revise, Rejected, Cancelled).</span>
                             <span x-show="lang==='id'">Filter: Date From, Date To, Requester, Type Trip (One Way / Return Trip), Status (Pending, Completed, Revise, Rejected, Cancelled).</span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Columns: Doc ID, Date, Created User, Requester, Department, Company, Origin, Destination, Purpose, Type Trip, Actual Budget, Status.</span>
                             <span x-show="lang==='id'">Kolom: Doc ID, Date, Created User, Requester, Department, Company, Origin, Destination, Purpose, Type Trip, Actual Budget, Status.</span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 Free Parking</span>
                             <span x-show="lang==='id'">3.5 Free Parking</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Filters: Date From, Date To, Name/Employee, Parking Type, Worker Type, Status (On Progress, Completed, Revise, Rejected, Cancelled, Active).</span>
                             <span x-show="lang==='id'">Filter: Date From, Date To, Name/Employee, Parking Type, Worker Type, Status (On Progress, Completed, Revise, Rejected, Cancelled, Active).</span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Columns: Doc ID, Reg. Date, Name, Company, Department, License Plate, Vehicle Type, Parking Type, Worker Type, Start Date, End Date, Status.</span>
                             <span x-show="lang==='id'">Kolom: Doc ID, Reg. Date, Name, Company, Department, License Plate, Vehicle Type, Parking Type, Worker Type, Start Date, End Date, Status.</span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.6 Car Expense</span>
                             <span x-show="lang==='id'">3.6 Car Expense</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Filters: Date From, Date To, Vehicle, Driver.</span>
                             <span x-show="lang==='id'">Filter: Date From, Date To, Vehicle, Driver.</span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">Columns: Ref No, Date, Vehicle, Driver, Cost Type, Description, Qty, Amount.</span>
                             <span x-show="lang==='id'">Kolom: Ref No, Date, Vehicle, Driver, Cost Type, Description, Qty, Amount.</span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Car Expense does not have a Status filter or column — every recorded expense
                                 entry is shown.
                             </span>
                             <span x-show="lang==='id'">
                                 Car Expense tidak memiliki filter atau kolom Status — setiap data biaya yang
                                 tercatat akan ditampilkan.
                             </span>
                         </div>

                     </section>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Date From / Date To filter by the document or transaction date of each module
                             (e.g. meeting date, booking date, voucher date). Leaving both empty returns all
                             available records, which can be slow to load on a large date range — narrow the
                             range when possible.
                         </span>
                         <span x-show="lang==='id'">
                             Date From / Date To memfilter berdasarkan tanggal dokumen atau transaksi masing-masing
                             modul (misalnya tanggal meeting, tanggal booking, tanggal voucher). Jika dikosongkan,
                             seluruh data yang tersedia akan ditampilkan, yang dapat memperlambat proses pemuatan
                             pada rentang tanggal yang besar — persempit rentang tanggal bila memungkinkan.
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
                         <span x-show="lang==='en'">4. Exporting and Printing</span>
                         <span x-show="lang==='id'">4. Mengekspor dan Mencetak</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Every report has an <strong>Export</strong> button in its filter panel. Exporting
                             always applies whatever filters are currently set — clear or adjust the filters
                             first if you want the export to match what you see on screen.
                         </span>
                         <span x-show="lang==='id'">
                             Setiap laporan memiliki tombol <strong>Export</strong> pada panel filternya.
                             Proses export selalu menggunakan filter yang sedang aktif — sesuaikan atau kosongkan
                             filter terlebih dahulu jika Anda ingin hasil export sesuai dengan apa yang terlihat
                             di layar.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Meeting Room — Excel and PDF</span>
                                 <span x-show="lang==='id'">Meeting Room — Excel dan PDF</span>
                             </strong> —
                             <span x-show="lang==='en'">this is the only report with two separate buttons, Excel and PDF. The PDF is a print-friendly landscape layout meant for sharing or filing.</span>
                             <span x-show="lang==='id'">ini satu-satunya laporan dengan dua tombol terpisah, Excel dan PDF. PDF menggunakan layout landscape yang cocok untuk dicetak atau dibagikan.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Meeting Teams/Zoom, Operational Car, Voucher Taxi, Free Parking, Car Expense — Excel only</span>
                                 <span x-show="lang==='id'">Meeting Teams/Zoom, Operational Car, Voucher Taxi, Free Parking, Car Expense — hanya Excel</span>
                             </strong> —
                             <span x-show="lang==='en'">these reports have a single Export button that downloads an Excel (.xlsx) file. There is no PDF option for these report types.</span>
                             <span x-show="lang==='id'">laporan-laporan ini hanya memiliki satu tombol Export yang mengunduh file Excel (.xlsx). Tidak tersedia opsi PDF untuk jenis laporan ini.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Exported files download directly through your browser. If nothing happens after
                             clicking Export, check your browser's pop-up/download blocker settings.
                         </span>
                         <span x-show="lang==='id'">
                             File hasil export akan terunduh langsung melalui browser Anda. Jika tidak ada yang
                             terjadi setelah mengklik Export, periksa pengaturan pop-up/download blocker pada
                             browser Anda.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
