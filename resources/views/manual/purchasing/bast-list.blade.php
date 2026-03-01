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
                         <span x-show="lang==='en'">1. Create BAST</span>
                         <span x-show="lang==='id'">1. Buat BAST</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- 1.1 Overview -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create BAST page is used to generate a BAST (Berita Acara Serah Terima)
                                 document based on an approved PO Term.
                                 This document confirms that work progress has been completed
                                 according to the agreed terms and is ready for further processing.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create BAST digunakan untuk membuat dokumen BAST (Berita Acara Serah Terima)
                                 berdasarkan PO Term yang telah disetujui.
                                 Dokumen ini menjadi bukti bahwa progress pekerjaan telah selesai
                                 sesuai dengan terms yang disepakati dan siap untuk proses selanjutnya.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 BAST can only be created for valid and approved PO Terms.
                             </span>
                             <span x-show="lang==='id'">
                                 BAST hanya dapat dibuat untuk PO Term yang valid dan telah disetujui.
                             </span>
                         </div>
                     </section>

                     <!-- 1.2 Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Header Information</span>
                             <span x-show="lang==='id'">1.2 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 This section displays read-only information retrieved from the related PO Term.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian ini menampilkan informasi read-only yang diambil dari PO Term terkait.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>PO Number</strong></li>
                             <li><strong>Company</strong></li>
                             <li><strong>Department</strong></li>
                             <li><strong>Start Date</strong></li>
                             <li><strong>End Date</strong></li>
                             <li><strong>User Peminta</strong></li>
                             <li><strong>CS ID</strong></li>
                             <li><strong>SPPB/J/K/T</strong></li>
                             <li><strong>Vendor</strong></li>
                             <li><strong>Terms Name</strong></li>
                             <li><strong>Progress (%)</strong></li>
                             <li><strong>Payment (%)</strong></li>
                             <li><strong>Keperluan</strong></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Progress and Payment percentages are automatically calculated
                                 based on the defined Term configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Persentase Progress dan Payment dihitung otomatis
                                 berdasarkan konfigurasi Term yang telah ditentukan.
                             </span>
                         </div>
                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bast/create-header.png') }}">
                                 <figcaption>
                                     Figure 1.1 – Create Header Fields
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- 1.3 Location Selection -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Location & Sub Location</span>
                             <span x-show="lang==='id'">1.3 Lokasi & Sub Lokasi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must select the project Location and Sub Location
                                 before submitting the BAST document.
                             </span>
                             <span x-show="lang==='id'">
                                 User wajib memilih Location dan Sub Location proyek
                                 sebelum mengirim dokumen BAST.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Location</strong> –
                                 <span x-show="lang==='en'">Main project area.</span>
                                 <span x-show="lang==='id'">Area utama proyek.</span>
                             </li>
                             <li>
                                 <strong>Sub Location</strong> –
                                 <span x-show="lang==='en'">Specific working section inside the location.</span>
                                 <span x-show="lang==='id'">Bagian spesifik di dalam lokasi.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Location selection is mandatory.
                                 The BAST cannot be submitted without valid Location and Sub Location.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan Location wajib dilakukan.
                                 BAST tidak dapat disubmit tanpa Location dan Sub Location yang valid.
                             </span>
                         </div>

                     </section>
                     <!-- 2.4 Photo Before -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 Photo Before</span>
                             <span x-show="lang==='id'">1.4 Foto Before</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Photo Before is retrieved from the related BQ (Bill of Quantity)
                                 and cannot be modified in the Edit BAST page.
                             </span>
                             <span x-show="lang==='id'">
                                 Foto Before diambil dari BQ (Bill of Quantity) terkait
                                 dan tidak dapat diubah pada halaman Edit BAST.
                             </span>
                         </p>
                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bast/create-before.png') }}">
                                 <figcaption>
                                     Figure 1.2 – Create Photo Before Fields
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 2.4 Photo Before -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Photo After</span>
                             <span x-show="lang==='id'">1.5 Foto After</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may:
                                 <ul class="mt-2 list-disc pl-6">
                                     <li>Add new photos</li>
                                 </ul>
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat:
                                 <ul class="mt-2 list-disc pl-6">
                                     <li>Menambahkan foto baru</li>
                                 </ul>
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Accepted formats: JPG/PNG with maximum 5 MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Format yang diterima: JPG/PNG dengan maksimum 5 MB per file.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bast/create-after.png') }}">
                                 <figcaption>
                                     Figure 1.3 – Create Photo After Fields
                                 </figcaption>
                             </figure>
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
                         <span x-show="lang==='en'">2. Edit BAST </span>
                         <span x-show="lang==='id'">2. Edit BAST</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- 2.1 Overview -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit BAST page allows users to modify an existing BAST document
                                 before it is fully approved.
                                 Users may update location data, add or remove photos,
                                 and upload additional attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit BAST memungkinkan user untuk mengubah dokumen BAST
                                 yang sudah dibuat sebelum disetujui sepenuhnya.
                                 User dapat memperbarui lokasi, menambah atau menghapus foto,
                                 serta mengunggah lampiran tambahan.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Editing is only allowed when the BAST status permits modification
                                 (e.g., Draft or Revise).
                             </span>
                             <span x-show="lang==='id'">
                                 Pengeditan hanya diperbolehkan apabila status BAST masih mengizinkan perubahan
                                 (misalnya Draft atau Revise).
                             </span>
                         </div>
                     </section>

                     <!-- 2.2 Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Header Information (Read Only)</span>
                             <span x-show="lang==='id'">2.2 Informasi Header (Read Only)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays BAST and PO reference information.
                                 These fields cannot be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan informasi referensi BAST dan PO.
                                 Field ini tidak dapat diubah.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>BAST ID</strong></li>
                             <li><strong>PO Number</strong></li>
                             <li><strong>Company</strong></li>
                             <li><strong>Department</strong></li>
                             <li><strong>Start Date</strong></li>
                             <li><strong>End Date</strong></li>
                             <li><strong>User Peminta</strong></li>
                             <li><strong>CS ID</strong></li>
                             <li><strong>SPPB/J/K/T</strong></li>
                             <li><strong>Vendor</strong></li>
                             <li><strong>Progress %</strong></li>
                             <li><strong>Payment %</strong></li>
                             <li><strong>Keperluan</strong></li>
                         </ul>
                     </section>

                     <!-- 2.3 Edit Location -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Update Location</span>
                             <span x-show="lang==='id'">2.3 Perbarui Lokasi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update the Location and Sub Location
                                 by clicking the <strong>Change</strong> button.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat memperbarui Location dan Sub Location
                                 dengan menekan tombol <strong>Change</strong>.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Location and Sub Location must be valid before updating the BAST.
                             </span>
                             <span x-show="lang==='id'">
                                 Location dan Sub Location harus valid sebelum melakukan update BAST.
                             </span>
                         </div>
                     </section>

                     <!-- 2.4 Photo Before -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Photo Before</span>
                             <span x-show="lang==='id'">2.4 Foto Before</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Photo Before is retrieved from the related BQ (Bill of Quantity)
                                 and cannot be modified in the Edit BAST page.
                             </span>
                             <span x-show="lang==='id'">
                                 Foto Before diambil dari BQ (Bill of Quantity) terkait
                                 dan tidak dapat diubah pada halaman Edit BAST.
                             </span>
                         </p>
                     </section>

                     <!-- 2.5 Photo After -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.5 Photo After</span>
                             <span x-show="lang==='id'">2.5 Foto After</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may:
                                 <ul class="mt-2 list-disc pl-6">
                                     <li>View existing Photo After</li>
                                     <li>Add new photos</li>
                                     <li>Remove uploaded photos (if permitted)</li>
                                 </ul>
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat:
                                 <ul class="mt-2 list-disc pl-6">
                                     <li>Melihat Foto After yang sudah ada</li>
                                     <li>Menambahkan foto baru</li>
                                     <li>Menghapus foto (jika diizinkan)</li>
                                 </ul>
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Accepted formats: JPG/PNG with maximum 5 MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Format yang diterima: JPG/PNG dengan maksimum 5 MB per file.
                             </span>
                         </div>
                     </section>

                     <!-- 2.6 Attachments -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.6 BAST Attachments</span>
                             <span x-show="lang==='id'">2.6 Lampiran BAST</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users can review existing attachments and upload new supporting documents.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat melihat lampiran yang sudah ada dan mengunggah dokumen pendukung tambahan.
                             </span>
                         </p>
                     </section>

                     <!-- 2.7 Update Submission -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.7 Update Submission</span>
                             <span x-show="lang==='id'">2.7 Submit Update</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all changes, click <strong>Update</strong>
                                 to save modifications and continue the approval process.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah selesai melakukan perubahan, klik <strong>Update</strong>
                                 untuk menyimpan perubahan dan melanjutkan proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Any update may reset or re-trigger approval workflow depending on system configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap perubahan dapat memicu ulang proses approval
                                 tergantung konfigurasi sistem.
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
                         <span x-show="lang==='en'">3. List BAST</span>
                         <span x-show="lang==='id'">3. Lihat BAST</span>

                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- 3.1 Overview -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The BAST List page displays all BAST documents available to the user.
                                 Users can monitor document status, filter by workflow stage,
                                 and open specific BAST records for review or action.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar BAST menampilkan seluruh dokumen BAST yang tersedia untuk user.
                                 User dapat memantau status dokumen, melakukan filter berdasarkan tahap workflow,
                                 serta membuka dokumen BAST tertentu untuk ditinjau atau diproses.
                             </span>
                         </p>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bast/job-list.png') }}">
                                 <figcaption>
                                     Figure 3.1 – BAST List Page
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- 3.2 Status Summary Cards -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status Summary Cards</span>
                             <span x-show="lang==='id'">3.2 Kartu Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top of the page, summary cards display the number of BAST documents
                                 grouped by status. Clicking a card will filter the table accordingly.
                             </span>
                             <span x-show="lang==='id'">
                                 Di bagian atas halaman terdapat kartu ringkasan yang menampilkan jumlah dokumen BAST
                                 berdasarkan status. Klik pada kartu untuk memfilter tabel sesuai kategori tersebut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Bast Jobs</strong> –
                                 <span x-show="lang==='en'">Tasks assigned to the current user.</span>
                                 <span x-show="lang==='id'">Tugas BAST yang ditugaskan kepada user.</span>
                             </li>
                             <li><strong>On Progress</strong> –
                                 <span x-show="lang==='en'">Documents currently in approval process.</span>
                                 <span x-show="lang==='id'">Dokumen yang sedang dalam proses approval.</span>
                             </li>
                             <li><strong>Rejected</strong> –
                                 <span x-show="lang==='en'">Documents rejected during approval.</span>
                                 <span x-show="lang==='id'">Dokumen yang ditolak dalam proses approval.</span>
                             </li>
                             <li><strong>Revise</strong> –
                                 <span x-show="lang==='en'">Documents requiring revision.</span>
                                 <span x-show="lang==='id'">Dokumen yang memerlukan revisi.</span>
                             </li>
                             <li><strong>Completed</strong> –
                                 <span x-show="lang==='en'">Fully approved BAST documents.</span>
                                 <span x-show="lang==='id'">Dokumen BAST yang telah selesai dan disetujui
                                     sepenuhnya.</span>
                             </li>
                             <li><strong>All</strong> –
                                 <span x-show="lang==='en'">Displays all BAST records without filtering.</span>
                                 <span x-show="lang==='id'">Menampilkan seluruh dokumen BAST tanpa filter.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The numbers shown on each card are automatically calculated by the system.
                             </span>
                             <span x-show="lang==='id'">
                                 Jumlah yang ditampilkan pada setiap kartu dihitung secara otomatis oleh sistem.
                             </span>
                         </div>
                     </section>

                     <!-- 3.3 BAST Table -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 BAST Data Table</span>
                             <span x-show="lang==='id'">3.3 Tabel Data BAST</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The BAST table displays detailed information for each document.
                                 Users can sort, search, and open individual records.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel BAST menampilkan informasi detail setiap dokumen.
                                 User dapat melakukan pencarian, pengurutan, dan membuka dokumen tertentu.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Access to specific BAST records depends on user role,
                                 company, and department authorization.
                             </span>
                             <span x-show="lang==='id'">
                                 Akses terhadap dokumen BAST tertentu bergantung pada role user,
                                 company, dan otorisasi department.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bast/list.png') }}">
                                 <figcaption>
                                     Figure 3.2 – BAST List Page
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 3.4 Workflow Behavior -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Workflow Behavior</span>
                             <span x-show="lang==='id'">3.4 Perilaku Workflow</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The BAST list dynamically updates based on workflow actions such as
                                 approval, rejection, revision, or completion.
                             </span>
                             <span x-show="lang==='id'">
                                 Daftar BAST akan diperbarui secara dinamis berdasarkan tindakan workflow
                                 seperti approval, reject, revisi, atau penyelesaian.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 A BAST document may move between categories depending on its latest approval status.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumen BAST dapat berpindah kategori sesuai dengan status approval terakhir.
                             </span>
                         </div>
                     </section>
                 </div>
             </div>

         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Show BAST Details</span>
                         <span x-show="lang==='id'">4. Tampilkan Detail BAST</span>

                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- 4.1 Overview -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Overview</span>
                             <span x-show="lang==='id'">4.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show BAST page displays complete information of a selected BAST document,
                                 including financial data, approval status, attachments, comments,
                                 vendor rating, and photo documentation.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Detail BAST menampilkan informasi lengkap dari dokumen BAST yang dipilih,
                                 termasuk data finansial, status approval, lampiran, komentar,
                                 penilaian vendor, dan dokumentasi foto.
                             </span>
                         </p>
                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bast/overview.png') }}">
                                 <figcaption>
                                     Figure 4.1 – Show BAST Page Overview
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 4.2 Status & Action Buttons -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Status & Approval Actions</span>
                             <span x-show="lang==='id'">4.2 Status & Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The document status is displayed at the top-right of the page.
                                 Authorized users may perform workflow actions:
                             </span>
                             <span x-show="lang==='id'">
                                 Status dokumen ditampilkan di bagian kanan atas halaman.
                                 User yang memiliki otorisasi dapat melakukan aksi workflow:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong></li>
                             <li><strong>Revise</strong></li>
                             <li><strong>Reject</strong></li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Approval actions follow the configured approval hierarchy.
                                 A document cannot move to the next level unless the current level is completed.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi approval mengikuti hierarki approval yang telah dikonfigurasi.
                                 Dokumen tidak dapat berpindah ke level berikutnya sebelum level saat ini selesai.
                             </span>
                         </div>
                     </section>

                     <!-- 4.3 BAST Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 BAST Information</span>
                             <span x-show="lang==='id'">4.3 Informasi BAST</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The left panel displays detailed BAST information:
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kiri menampilkan detail informasi BAST:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>BAST ID & Date</li>
                             <li>PO Number</li>
                             <li>Company & Department</li>
                             <li>Requester & Vendor</li>
                             <li>CS ID & SPPB/J/K/T Reference</li>
                             <li>BQ ID</li>
                             <li>BAST Amount</li>
                             <li>Progress % & Payment %</li>
                             <li>Start Date, End Date, Handover Date</li>
                             <li>Penalty Information</li>
                             <li>Realization Amount</li>
                         </ul>
                     </section>

                     <!-- 4.4 Attachments, Approval & Comments -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Attachments, Approval & Comments</span>
                             <span x-show="lang==='id'">4.4 Lampiran, Approval & Komentar</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right panel contains three tabs:
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kanan terdiri dari tiga tab:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Attachment</strong> –
                                 <span x-show="lang==='en'">View and upload supporting documents.</span>
                                 <span x-show="lang==='id'">Melihat dan mengunggah dokumen pendukung.</span>
                             </li>
                             <li>
                                 <strong>Approval Details</strong> –
                                 <span x-show="lang==='en'">Displays approval level history and status.</span>
                                 <span x-show="lang==='id'">Menampilkan riwayat level approval dan statusnya.</span>
                             </li>
                             <li>
                                 <strong>Comments</strong> –
                                 <span x-show="lang==='en'">Internal discussion related to the BAST.</span>
                                 <span x-show="lang==='id'">Diskusi internal terkait dokumen BAST.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Attachment uploads may be restricted based on document status and user role.
                             </span>
                             <span x-show="lang==='id'">
                                 Upload lampiran dapat dibatasi berdasarkan status dokumen dan role user.
                             </span>
                         </div>
                     </section>

                     <!-- 4.5 Vendor Rating -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 Vendor Rating</span>
                             <span x-show="lang==='id'">4.5 Penilaian Vendor</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Vendor Rating section displays evaluation criteria,
                                 score values, star visualization, and legend classification.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian Penilaian Vendor menampilkan kriteria evaluasi,
                                 nilai skor, visualisasi bintang, dan klasifikasi legenda.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Vendor rating contributes to overall vendor performance evaluation.
                             </span>
                             <span x-show="lang==='id'">
                                 Penilaian vendor berkontribusi terhadap evaluasi performa vendor secara keseluruhan.
                             </span>
                         </div>
                     </section>

                     <!-- 4.6 Photo Documentation -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.6 Photo Documentation</span>
                             <span x-show="lang==='id'">4.6 Dokumentasi Foto</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Photo Before</strong> –
                                 <span x-show="lang==='en'">Retrieved from related BQ document.</span>
                                 <span x-show="lang==='id'">Diambil dari dokumen BQ terkait.</span>
                             </li>
                             <li>
                                 <strong>Photo After</strong> –
                                 <span x-show="lang==='en'">Uploaded as part of BAST completion evidence.</span>
                                 <span x-show="lang==='id'">Diunggah sebagai bukti penyelesaian pekerjaan.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Photo documentation serves as official evidence of work completion.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumentasi foto menjadi bukti resmi penyelesaian pekerjaan.
                             </span>
                         </div>
                     </section>
                 </div>
             </div>
         </section>
     </div>
