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
                         <span x-show="lang==='en'">1. Create SPB</span>
                         <span x-show="lang==='id'">1. Buat SPB</span>
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
                                 The SPB page allows users to create and monitor
                                 Surat Perintah Bayar (SPB) documents.
                                 Users can track document status, approval progress,
                                 and execution flow from this page.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman SPB digunakan untuk membuat dan memonitor
                                 dokumen Surat Perintah Bayar (SPB).
                                 User dapat melihat status dokumen, progres approval,
                                 dan alur proses dari halaman ini.
                             </span>
                         </p>

                     </section>

                     <!-- 1.2 Status Cards -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Status Cards</span>
                             <span x-show="lang==='id'">1.2 Kartu Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards summarize SPB documents based on workflow status.
                                 Clicking a card will filter the table automatically.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status menampilkan ringkasan dokumen SPB
                                 berdasarkan status workflow.
                                 Klik pada kartu akan memfilter tabel secara otomatis.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>All</strong> – Display all SPB documents</li>
                             <li><strong>On Progress</strong> – Documents currently in approval</li>
                             <li><strong>Reject</strong> – Documents rejected</li>
                             <li><strong>Revise / Draft</strong> – Documents needing revision</li>
                             <li><strong>Completed</strong> – Fully approved documents</li>
                             <li><strong>SPB Tracking</strong> – View SPB process timeline</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Status reflects approval workflow,
                                 not payment completion status.
                             </span>
                             <span x-show="lang==='id'">
                                 Status mencerminkan workflow approval,
                                 bukan status pembayaran.
                             </span>
                         </div>

                     </section>

                     <!-- 1.3 Create SPB -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Creating a New SPB</span>
                             <span x-show="lang==='id'">1.3 Membuat SPB Baru</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 To create a new SPB document, click the
                                 <strong>Create</strong> button located at the top-right
                                 of the page.
                             </span>
                             <span x-show="lang==='id'">
                                 Untuk membuat dokumen SPB baru,
                                 klik tombol <strong>Create</strong>
                                 di bagian kanan atas halaman.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Ensure all required information such as Company,
                                 Department, Budget, and supporting documents
                                 are prepared before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan seluruh informasi wajib seperti Company,
                                 Department, Budget, dan dokumen pendukung
                                 telah disiapkan sebelum submit.
                             </span>
                         </div>

                     </section>

                     <!-- 1.4 SPB Tracking -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 SPB Tracking</span>
                             <span x-show="lang==='id'">1.4 Tracking SPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The SPB Tracking feature allows users to view
                                 the document journey in timeline format,
                                 including approval stages and progress updates.
                             </span>
                             <span x-show="lang==='id'">
                                 Fitur SPB Tracking memungkinkan user melihat
                                 perjalanan dokumen dalam bentuk timeline,
                                 termasuk tahapan approval dan progres proses.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Use the navigation buttons (Prev / Next)
                                 to scroll through the timeline.
                             </span>
                             <span x-show="lang==='id'">
                                 Gunakan tombol navigasi (Prev / Next)
                                 untuk melihat timeline secara berurutan.
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
                         <span x-show="lang==='en'">2. Edit SPB</span>
                         <span x-show="lang==='id'">2. Edit SPB</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- 1. Overview -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1. Overview</span>
                             <span x-show="lang==='id'">1. Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit SPPB page allows authorized users to modify
                                 an existing SPPB document before final approval.
                                 Users can update header information, item details,
                                 budget allocation, and attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit SPPB memungkinkan user yang berwenang
                                 untuk melakukan perubahan pada dokumen SPPB
                                 sebelum approval final.
                                 User dapat memperbarui informasi header, detail item,
                                 alokasi budget, serta lampiran.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing an SPPB document will reset the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada dokumen SPPB akan mereset alur approval.
                             </span>
                         </div>

                     </section>

                     <!-- 2. Header Information -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2. Header Information</span>
                             <span x-show="lang==='id'">2. Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update the following header fields:
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat memperbarui informasi berikut pada bagian header:
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Company</strong></li>
                             <li><strong>Business Unit</strong></li>
                             <li><strong>Department</strong></li>
                             <li><strong>Jenis Pekerjaan (Worktype & Sub Worktype)</strong></li>
                             <li><strong>Perpost (Budget Year)</strong></li>
                             <li><strong>WO ID</strong> (optional, if applicable)</li>
                             <li><strong>Description</strong></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If WO ID is selected, related description and attachment
                                 may be automatically carried over.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika WO ID dipilih, deskripsi dan attachment terkait
                                 dapat terbawa secara otomatis.
                             </span>
                         </div>

                     </section>

                     <!-- 3. SPPB Detail -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3. SPPB Detail</span>
                             <span x-show="lang==='id'">3. Detail SPPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The detail section contains itemized product entries.
                                 Users may add, modify, or remove rows as needed.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian detail berisi daftar item produk.
                                 User dapat menambah, mengubah, atau menghapus baris item.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Product Name</strong> – Selected from Inventory lookup</li>
                             <li><strong>Quantity (Qty)</strong></li>
                             <li><strong>UoM</strong> – Unit of Measurement</li>
                             <li><strong>Site ID</strong></li>
                             <li><strong>Note</strong></li>
                             <li><strong>Location & Sub Location</strong></li>
                             <li><strong>COA (Chart of Account)</strong></li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 COA selection must align with Company, Department,
                                 and Perpost configuration to ensure budget validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA harus sesuai dengan konfigurasi Company,
                                 Department, dan Perpost untuk memastikan validasi budget.
                             </span>
                         </div>

                     </section>

                     <!-- 4. Attachments -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4. Attachments</span>
                             <span x-show="lang==='id'">4. Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may review existing attachments,
                                 remove them, or upload new supporting documents.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat melihat lampiran yang sudah ada,
                                 menghapusnya, atau mengunggah dokumen pendukung baru.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure all required supporting documents
                                 are attached before submitting for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan seluruh dokumen pendukung yang diperlukan
                                 telah dilampirkan sebelum submit approval.
                             </span>
                         </div>

                     </section>

                     <!-- 5. Submission -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5. Submit & Cancel</span>
                             <span x-show="lang==='id'">5. Submit & Cancel</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all required fields,
                                 click <strong>Submit Approval</strong> to continue
                                 the approval process.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah seluruh field wajib diisi,
                                 klik <strong>Submit Approval</strong>
                                 untuk melanjutkan proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Submitting the document will restart
                                 the approval workflow from the first level.
                             </span>
                             <span x-show="lang==='id'">
                                 Submit dokumen akan memulai kembali
                                 alur approval dari level pertama.
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
                         <span x-show="lang==='en'">3. List WO</span>
                         <span x-show="lang==='id'">3. Daftar WO</span>

                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- 2.1 Overview -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The SPB List page displays all SPB documents
                                 based on user access rights. Users can filter,
                                 monitor, and track document progress from this page.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar SPB menampilkan seluruh dokumen SPB
                                 sesuai dengan hak akses user.
                                 User dapat memfilter, memonitor, dan melacak progres dokumen dari halaman ini.
                             </span>
                         </p>

                     </section>

                     <!-- 2.2 Status Cards -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Status Cards</span>
                             <span x-show="lang==='id'">2.2 Kartu Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards summarize the number of SPB documents
                                 grouped by workflow status. Clicking a card
                                 automatically filters the table below.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status menampilkan jumlah dokumen SPB
                                 berdasarkan status workflow. Klik kartu
                                 untuk memfilter tabel secara otomatis.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>All</strong> – <span x-show="lang==='en'">All SPB documents</span><span
                                     x-show="lang==='id'">Semua dokumen SPB</span></li>
                             <li><strong>On Progress</strong> – <span x-show="lang==='en'">Documents currently in
                                     approval process</span><span x-show="lang==='id'">Dokumen dalam proses
                                     approval</span></li>
                             <li><strong>Reject</strong> – <span x-show="lang==='en'">Rejected documents</span><span
                                     x-show="lang==='id'">Dokumen ditolak</span></li>
                             <li><strong>Revise / Draft</strong> – <span x-show="lang==='en'">Documents requiring
                                     revision</span><span x-show="lang==='id'">Dokumen perlu revisi</span></li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Fully approved
                                     documents</span><span x-show="lang==='id'">Dokumen selesai approval</span></li>
                             <li><strong>SPB Tracking</strong> – <span x-show="lang==='en'">View approval
                                     timeline</span><span x-show="lang==='id'">Melihat timeline approval</span></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The numbers displayed on each card represent
                                 real-time document totals.
                             </span>
                             <span x-show="lang==='id'">
                                 Angka pada setiap kartu menunjukkan
                                 total dokumen secara real-time.
                             </span>
                         </div>

                     </section>

                     <!-- 2.3 SPB Table -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 SPB Table</span>
                             <span x-show="lang==='id'">2.3 Tabel SPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The SPB table displays detailed document information,
                                 including document number, date, department,
                                 status, and available actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel SPB menampilkan detail informasi dokumen,
                                 termasuk nomor dokumen, tanggal, department,
                                 status, dan aksi yang tersedia.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Available actions (Edit / View / Cancel)
                                 depend on document status and user authorization.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi yang tersedia (Edit / View / Cancel)
                                 tergantung pada status dokumen dan hak akses user.
                             </span>
                         </div>

                     </section>

                     <!-- 2.4 SPB Tracking -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 SPB Tracking Modal</span>
                             <span x-show="lang==='id'">2.4 Modal Tracking SPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The SPB Tracking feature provides a visual timeline
                                 of the document approval process.
                                 Users can review each approval level,
                                 timestamps, and approval decisions.
                             </span>
                             <span x-show="lang==='id'">
                                 Fitur Tracking SPB menampilkan timeline visual
                                 proses approval dokumen.
                                 User dapat melihat setiap level approval,
                                 waktu proses, dan keputusan approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Tracking is read-only and cannot be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Tracking bersifat read-only dan tidak dapat diubah.
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
                         <span x-show="lang==='en'">4. Show WO Details</span>
                         <span x-show="lang==='id'">4. Tampilkan Detail WO</span>

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
                                 The Show SPB page displays complete document information,
                                 including header data, detail items, attachments,
                                 approval history, comments, and available actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show SPB menampilkan informasi lengkap dokumen,
                                 termasuk data header, detail item, attachment,
                                 riwayat approval, komentar, dan aksi yang tersedia.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Available actions depend on document status and user role.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi yang tersedia tergantung pada status dokumen dan role user.
                             </span>
                         </div>
                     </section>

                     <!-- 4.2 Approval Action Buttons -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Approval Actions</span>
                             <span x-show="lang==='id'">4.2 Aksi Approval</span>
                         </h3>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> –
                                 <span x-show="lang==='en'">Approve the document to the next level.</span>
                                 <span x-show="lang==='id'">Menyetujui dokumen ke level berikutnya.</span>
                             </li>
                             <li><strong>Revise</strong> –
                                 <span x-show="lang==='en'">Return document to requester for revision.</span>
                                 <span x-show="lang==='id'">Mengembalikan dokumen ke pembuat untuk revisi.</span>
                             </li>
                             <li><strong>Reject</strong> –
                                 <span x-show="lang==='en'">Reject the document permanently.</span>
                                 <span x-show="lang==='id'">Menolak dokumen secara permanen.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Reject and Revise actions require a mandatory reason.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Reject dan Revise wajib mengisi alasan.
                             </span>
                         </div>
                     </section>

                     <!-- 4.3 SPB Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 SPB Header Information</span>
                             <span x-show="lang==='id'">4.3 Informasi Header SPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Displays key document information including:
                                 SPB ID, Status, Company, Department,
                                 Date, Creator, Worktype, WO reference, and Description.
                             </span>
                             <span x-show="lang==='id'">
                                 Menampilkan informasi utama dokumen seperti:
                                 ID SPB, Status, Company, Department,
                                 Tanggal, Pembuat, Jenis Pekerjaan, referensi WO, dan Deskripsi.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Document status determines workflow availability.
                             </span>
                             <span x-show="lang==='id'">
                                 Status dokumen menentukan ketersediaan proses workflow.
                             </span>
                         </div>
                     </section>

                     <!-- 4.4 Attachment / Approval / Comments Tabs -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Tabs Section</span>
                             <span x-show="lang==='id'">4.4 Bagian Tab</span>
                         </h3>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Attachment</strong> –
                                 <span x-show="lang==='en'">View and upload supporting documents.</span>
                                 <span x-show="lang==='id'">Melihat dan mengunggah dokumen pendukung.</span>
                             </li>
                             <li><strong>Approval Details</strong> –
                                 <span x-show="lang==='en'">View approval level, approver name, date, and
                                     status.</span>
                                 <span x-show="lang==='id'">Melihat level approval, nama approver, tanggal, dan
                                     status.</span>
                             </li>
                             <li><strong>Comments</strong> –
                                 <span x-show="lang==='en'">Discussion between requester and approvers.</span>
                                 <span x-show="lang==='id'">Diskusi antara pembuat dan approver.</span>
                             </li>
                         </ul>
                     </section>

                     <!-- 4.5 SPB Detail Table -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 SPB Detail Table</span>
                             <span x-show="lang==='id'">4.5 Tabel Detail SPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Displays detailed item information including:
                                 product description, quantity, location,
                                 budget allocation, issued quantity,
                                 SPPB quantity, and remaining quantity.
                             </span>
                             <span x-show="lang==='id'">
                                 Menampilkan detail item seperti:
                                 deskripsi barang, jumlah, lokasi,
                                 alokasi budget, quantity issued,
                                 quantity SPPB, dan sisa quantity.
                             </span>
                         </p>
                     </section>

                     <!-- 4.6 Edit COA (Cost Control Only) -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.6 Edit COA (Cost Control Access)</span>
                             <span x-show="lang==='id'">4.6 Edit COA (Akses Cost Control)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Cost Control users can modify the COA (Chart of Account)
                                 and Activity allocation for each detail item.
                             </span>
                             <span x-show="lang==='id'">
                                 User Cost Control dapat mengubah COA (Chart of Account)
                                 dan alokasi Activity untuk setiap detail item.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA changes impact budget allocation and must follow financial policy.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan COA berdampak pada alokasi budget dan harus mengikuti kebijakan keuangan.
                             </span>
                         </div>
                     </section>

                 </div>
             </div>

         </section>

     </div>
