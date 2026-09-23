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
                             Car Expense is where General Affair records the operational costs of company vehicles —
                             for example fuel, toll, and parking. Each entry is logged against a specific vehicle
                             (Nopol) and driver, with a cost type, description, quantity, and amount.
                             There is no approval workflow: once a record is saved it is immediately final and
                             visible in the list, so this page is best used as a running expense log rather than
                             a request that needs sign-off.
                         </span>
                         <span x-show="lang==='id'">
                             Car Expense adalah tempat General Affair mencatat biaya operasional kendaraan
                             perusahaan — misalnya bahan bakar, tol, dan parkir. Setiap entri dicatat berdasarkan
                             kendaraan (Nopol) dan driver tertentu, lengkap dengan jenis biaya, deskripsi, kuantitas,
                             dan nominal. Tidak ada proses approval di modul ini: begitu sebuah entri disimpan,
                             entri tersebut langsung final dan tampil di daftar, sehingga halaman ini lebih tepat
                             digunakan sebagai catatan biaya berjalan, bukan sebagai permintaan yang perlu disetujui.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Access to this page is restricted to users with General Affair access (GA role).
                             If you cannot see the Car Expense menu, contact your administrator.
                         </span>
                         <span x-show="lang==='id'">
                             Akses ke halaman ini terbatas untuk pengguna dengan akses General Affair (role GA).
                             Jika Anda tidak melihat menu Car Expense, hubungi administrator Anda.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Cost Type Summary Cards</span>
                             <span x-show="lang==='id'">1.1 Kartu Ringkasan Jenis Biaya</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top of the page, summary cards show the total number of records for
                                 <strong>All</strong> entries plus one card per active cost type (e.g. Fuel, Toll,
                                 Parking — depending on what cost types are configured). Click a card to filter
                                 the table below to that cost type; click <strong>All</strong> to clear the filter.
                             </span>
                             <span x-show="lang==='id'">
                                 Di bagian atas halaman, kartu ringkasan menampilkan jumlah total entri untuk
                                 <strong>All</strong> serta satu kartu untuk setiap jenis biaya yang aktif (misalnya
                                 Bahan Bakar, Tol, Parkir — tergantung jenis biaya yang dikonfigurasi). Klik salah
                                 satu kartu untuk memfilter tabel di bawah berdasarkan jenis biaya tersebut; klik
                                 <strong>All</strong> untuk menghapus filter.
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
                         <span x-show="lang==='en'">2. Recording a Single Expense</span>
                         <span x-show="lang==='id'">2. Mencatat Satu Entri Biaya</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>Create</strong> at the top right of the table to open the
                             "Create Car Expense" form and record a new entry.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>Create</strong> di kanan atas tabel untuk membuka form
                             "Create Car Expense" dan mencatat entri baru.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Required Fields</span>
                             <span x-show="lang==='id'">2.1 Kolom yang Wajib Diisi</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Date</strong> —
                                 <span x-show="lang==='en'">The date the expense occurred.</span>
                                 <span x-show="lang==='id'">Tanggal biaya tersebut terjadi.</span>
                             </li>
                             <li>
                                 <strong>Cost Type</strong> —
                                 <span x-show="lang==='en'">The category of the expense (e.g. fuel, toll, parking), taken from the active "CAR COST" category list.</span>
                                 <span x-show="lang==='id'">Kategori biaya (misalnya bahan bakar, tol, parkir), diambil dari daftar kategori "CAR COST" yang aktif.</span>
                             </li>
                             <li>
                                 <strong>Nopol</strong> —
                                 <span x-show="lang==='en'">The vehicle's police number, selected from the list of active company vehicles. The vehicle name and brand are shown alongside the plate number to help you pick the right one.</span>
                                 <span x-show="lang==='id'">Nomor polisi kendaraan, dipilih dari daftar kendaraan perusahaan yang aktif. Nama dan merek kendaraan ditampilkan di samping nomor polisi untuk membantu Anda memilih kendaraan yang tepat.</span>
                             </li>
                             <li>
                                 <strong>Driver</strong> —
                                 <span x-show="lang==='en'">The driver associated with the trip or usage, selected from the list of active operational drivers.</span>
                                 <span x-show="lang==='id'">Driver yang terkait dengan perjalanan atau penggunaan kendaraan, dipilih dari daftar driver operasional yang aktif.</span>
                             </li>
                             <li>
                                 <strong>Description</strong> —
                                 <span x-show="lang==='en'">A free-text note describing the expense (e.g. "Fuel top-up for Jakarta–Bandung trip").</span>
                                 <span x-show="lang==='id'">Catatan bebas yang menjelaskan biaya tersebut (misalnya "Isi BBM untuk perjalanan Jakarta–Bandung").</span>
                             </li>
                             <li>
                                 <strong>Qty</strong> —
                                 <span x-show="lang==='en'">Quantity related to the expense (e.g. liters of fuel, number of toll passes). Must be a number of at least 1.</span>
                                 <span x-show="lang==='id'">Kuantitas terkait biaya (misalnya jumlah liter BBM, jumlah kali bayar tol). Harus berupa angka minimal 1.</span>
                             </li>
                             <li>
                                 <strong>Amount (IDR)</strong> —
                                 <span x-show="lang==='en'">The cost amount in Rupiah. The field automatically formats the number with thousand separators as you type.</span>
                                 <span x-show="lang==='id'">Nominal biaya dalam Rupiah. Kolom ini otomatis memformat angka dengan pemisah ribuan saat Anda mengetik.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 A reference number (e.g. <strong>CEX2606001</strong>) is generated automatically
                                 when you save — you do not need to enter it yourself.
                             </span>
                             <span x-show="lang==='id'">
                                 Nomor referensi (misalnya <strong>CEX2606001</strong>) dibuat secara otomatis
                                 saat Anda menyimpan — Anda tidak perlu mengisinya sendiri.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Attachments</span>
                             <span x-show="lang==='id'">2.2 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You may optionally attach supporting documents such as a fuel or toll receipt
                                 (PDF, DOCX, XLSX, PNG, or JPG, up to 5 MB per file) before saving. Attachments
                                 can also be added or removed later from the detail view.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda dapat melampirkan dokumen pendukung seperti struk BBM atau tol
                                 (PDF, DOCX, XLSX, PNG, atau JPG, maksimal 5 MB per file) sebelum menyimpan.
                                 Lampiran juga bisa ditambahkan atau dihapus kemudian dari halaman detail.
                             </span>
                         </p>

                     </section>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             There is no draft option — clicking <strong>Save</strong> immediately creates the
                             expense record. Double-check the vehicle, driver, and amount before saving.
                         </span>
                         <span x-show="lang==='id'">
                             Tidak ada opsi draft — klik <strong>Save</strong> akan langsung membuat entri biaya.
                             Periksa kembali kendaraan, driver, dan nominal sebelum menyimpan.
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
                         <span x-show="lang==='en'">3. Tracking and Managing Entries</span>
                         <span x-show="lang==='id'">3. Memantau dan Mengelola Entri</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             All recorded expenses appear in the table below the summary cards, sorted by the
                             newest reference number first. Click <strong>View</strong> on any row to open the
                             full detail of that entry.
                         </span>
                         <span x-show="lang==='id'">
                             Semua biaya yang tercatat muncul di tabel di bawah kartu ringkasan, diurutkan dari
                             nomor referensi terbaru. Klik <strong>View</strong> pada baris mana pun untuk membuka
                             detail lengkap entri tersebut.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Filtering the List</span>
                             <span x-show="lang==='id'">3.1 Memfilter Daftar</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Nopol</strong> —
                                 <span x-show="lang==='en'">Filter the list to a specific vehicle.</span>
                                 <span x-show="lang==='id'">Memfilter daftar untuk kendaraan tertentu.</span>
                             </li>
                             <li>
                                 <strong>From / To</strong> —
                                 <span x-show="lang==='en'">Filter by expense date range.</span>
                                 <span x-show="lang==='id'">Memfilter berdasarkan rentang tanggal biaya.</span>
                             </li>
                             <li>
                                 <strong>Reset</strong> —
                                 <span x-show="lang==='en'">Clears the Nopol and date filters.</span>
                                 <span x-show="lang==='id'">Menghapus filter Nopol dan tanggal.</span>
                             </li>
                         </ul>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You can also type in the table's search box to find entries by reference number,
                                 Nopol, driver name, or description, and combine this with the cost type cards
                                 from Section 1.1.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda juga dapat mengetik pada kotak pencarian tabel untuk mencari entri berdasarkan
                                 nomor referensi, Nopol, nama driver, atau deskripsi, dan menggabungkannya dengan
                                 kartu jenis biaya pada Bagian 1.1.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Viewing, Editing, and Deleting</span>
                             <span x-show="lang==='id'">3.2 Melihat, Mengedit, dan Menghapus</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The detail view shows all fields of the entry along with who created it and when,
                                 plus its attachments. From there you can:
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman detail menampilkan semua kolom entri beserta siapa yang membuatnya dan
                                 kapan, serta lampirannya. Dari sana Anda dapat:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Upload</strong> —
                                 <span x-show="lang==='en'">Add more supporting attachments to the entry.</span>
                                 <span x-show="lang==='id'">Menambahkan lampiran pendukung lain ke entri tersebut.</span>
                             </li>
                             <li>
                                 <strong>Edit</strong> —
                                 <span x-show="lang==='en'">Open the entry in edit mode to correct any of its fields (date, cost type, Nopol, driver, description, qty, or amount).</span>
                                 <span x-show="lang==='id'">Membuka entri dalam mode edit untuk memperbaiki kolom apa pun (tanggal, jenis biaya, Nopol, driver, deskripsi, qty, atau nominal).</span>
                             </li>
                             <li>
                                 <strong>Delete</strong> —
                                 <span x-show="lang==='en'">Remove the entry from the active list.</span>
                                 <span x-show="lang==='id'">Menghapus entri dari daftar aktif.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Deleting an entry asks for confirmation and cannot be undone from the screen.
                                 Make sure you are deleting the correct record before confirming.
                             </span>
                             <span x-show="lang==='id'">
                                 Menghapus entri akan meminta konfirmasi dan tidak dapat dibatalkan dari layar.
                                 Pastikan Anda menghapus entri yang benar sebelum mengonfirmasi.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Exporting the List</span>
                             <span x-show="lang==='id'">3.3 Mengekspor Daftar</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Use the <strong>Excel</strong> or <strong>CSV</strong> buttons above the table to
                                 export the currently visible page of results for reporting purposes.
                             </span>
                             <span x-show="lang==='id'">
                                 Gunakan tombol <strong>Excel</strong> atau <strong>CSV</strong> di atas tabel untuk
                                 mengekspor data yang sedang tampil pada halaman tabel untuk keperluan pelaporan.
                             </span>
                         </p>

                     </section>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Bulk Import from Excel</span>
                         <span x-show="lang==='id'">4. Import Massal dari Excel</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             If you have many expenses to record at once (for example a month's worth of fuel
                             receipts), you can import them from an Excel file instead of entering them one by one.
                         </span>
                         <span x-show="lang==='id'">
                             Jika Anda memiliki banyak biaya yang perlu dicatat sekaligus (misalnya kumpulan struk
                             BBM selama sebulan), Anda dapat mengimpornya dari file Excel daripada memasukkannya
                             satu per satu.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Step 1 — Download the Template</span>
                             <span x-show="lang==='id'">4.1 Langkah 1 — Unduh Template</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click <strong>Template</strong> at the top of the page to download the official
                                 import template. Fill in one row per expense, following the template's columns
                                 (date, Nopol, driver, cost type, description, quantity, and amount).
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Template</strong> di bagian atas halaman untuk mengunduh template
                                 import resmi. Isi satu baris untuk setiap biaya, sesuai kolom pada template
                                 (tanggal, Nopol, driver, jenis biaya, deskripsi, kuantitas, dan nominal).
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Step 2 — Upload and Preview</span>
                             <span x-show="lang==='id'">4.2 Langkah 2 — Unggah dan Pratinjau</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click <strong>Import</strong>, then choose or drag-and-drop your completed Excel
                                 file (.xlsx or .xls, max 5 MB). The system parses the file and shows a preview
                                 table of every row that will be created. If any row has a problem — a missing
                                 date, an invalid cost type, a non-numeric quantity, and so on — the errors are
                                 listed with their row numbers so you can fix the file and re-upload.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Import</strong>, lalu pilih atau drag-and-drop file Excel yang sudah
                                 diisi (.xlsx atau .xls, maksimal 5 MB). Sistem akan membaca file tersebut dan
                                 menampilkan tabel pratinjau dari setiap baris yang akan dibuat. Jika ada baris yang
                                 bermasalah — tanggal kosong, jenis biaya tidak valid, kuantitas bukan angka, dan
                                 sebagainya — kesalahan tersebut akan ditampilkan beserta nomor barisnya agar Anda
                                 bisa memperbaiki file dan mengunggah ulang.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 An import is all-or-nothing: if even one row has an error, none of the rows are
                                 imported until the file is corrected.
                             </span>
                             <span x-show="lang==='id'">
                                 Proses import bersifat all-or-nothing: jika ada satu baris saja yang error, tidak
                                 ada baris yang akan diimpor sampai file tersebut diperbaiki.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 Step 3 — Supporting Documents (Optional)</span>
                             <span x-show="lang==='id'">4.3 Langkah 3 — Dokumen Pendukung (Opsional)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once the preview looks correct, you may optionally attach supporting documents
                                 (PDF, PNG, or JPG/JPEG, max 5 MB each). These files will be attached to
                                 <strong>every</strong> record created by this import — they are not matched to
                                 individual rows.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah pratinjau terlihat benar, Anda dapat melampirkan dokumen pendukung
                                 (PDF, PNG, atau JPG/JPEG, maksimal 5 MB per file). File ini akan dilampirkan ke
                                 <strong>setiap</strong> entri yang dibuat oleh proses import ini — bukan dicocokkan
                                 per baris.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Click <strong>Import</strong> in the footer to finish. Each row becomes its own
                                 expense record with its own automatically generated reference number.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Import</strong> pada bagian bawah untuk menyelesaikan. Setiap baris
                                 akan menjadi entri biaya tersendiri dengan nomor referensi yang dibuat otomatis.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>

     </div>
