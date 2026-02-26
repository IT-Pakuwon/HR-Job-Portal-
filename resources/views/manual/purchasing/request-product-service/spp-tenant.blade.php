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
                         <span x-show="lang==='en'">1. Create SPPT</span>
                         <span x-show="lang==='id'">2. Membuat SPPT</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Create SPPT Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Umum Create SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create SPPT page allows users to submit a new Service / Product
                                 Purchase Task (SPPT) request. The form consists of header information,
                                 detailed item entries, and mandatory attachments before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create SPPT digunakan untuk membuat pengajuan
                                 Service / Product Purchase Task (SPPT) baru.
                                 Form terdiri dari informasi header, detail item,
                                 dan lampiran wajib sebelum dikirim untuk approval.
                             </span>
                         </p>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Header Information</span>
                             <span x-show="lang==='id'">1.2 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must complete all required fields including:
                                 Company, Business Unit, Department, Request Type,
                                 Perpost (Budget Year), Tenant Information, PIC,
                                 Unit Status, and Cost Allocation (Beban Biaya).
                             </span>
                             <span x-show="lang==='id'">
                                 User wajib mengisi seluruh field yang ditandai wajib,
                                 termasuk Company, Business Unit, Department, Request Type,
                                 Perpost (Tahun Budget), Data Tenant, PIC,
                                 Status Unit, dan Beban Biaya.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Business Unit selection determines available COA and budget validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan Business Unit menentukan COA yang tersedia
                                 serta validasi budget.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/create/header.png') }}">
                                 <figcaption>
                                     Figure 1.1 – SPPT Header Form
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Emergency & Work Order</span>
                             <span x-show="lang==='id'">1.3 Emergency & Work Order</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may mark the SPPT as Emergency and optionally
                                 link an existing Work Order (WO).
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat menandai SPPT sebagai Emergency
                                 dan menghubungkannya dengan Work Order (WO) yang sudah ada.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If a Work Order is selected, related information
                                 such as description and attachments may be inherited.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika Work Order dipilih, informasi terkait seperti
                                 deskripsi dan lampiran dapat ikut terbawa.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/create/emergency-wo.png') }}">
                                 <figcaption>
                                     Figure 1.2 – Emergency & WO Selection
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 SPPT Detail Entry</span>
                             <span x-show="lang==='id'">1.4 Input Detail SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must input at least one item in the SPPT Detail table.
                                 Each row requires Product Name, Quantity, UoM,
                                 Location, and COA selection.
                             </span>
                             <span x-show="lang==='id'">
                                 User wajib mengisi minimal satu item pada tabel SPPT Detail.
                                 Setiap baris membutuhkan Product Name, Quantity, UoM,
                                 Location, dan pemilihan COA.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA selection is filtered based on Company,
                                 Business Unit, Department, and Perpost.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA difilter berdasarkan Company,
                                 Business Unit, Department, dan Perpost.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Budget validation is performed upon COA selection.
                                 Insufficient budget may prevent submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Validasi budget dilakukan saat pemilihan COA.
                                 Budget yang tidak mencukupi dapat menghambat submit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/create/detail-table.png') }}">
                                 <figcaption>
                                     Figure 1.3 – SPPT Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Attachments</span>
                             <span x-show="lang==='id'">1.5 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must upload at least one supporting document
                                 before submitting the SPPT.
                             </span>
                             <span x-show="lang==='id'">
                                 User wajib mengunggah minimal satu dokumen pendukung
                                 sebelum mengirim SPPT.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Attachment is mandatory (minimum 1 file).
                                 Maximum file size: 5MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran wajib minimal 1 file.
                                 Ukuran maksimal 5MB per file.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/create/attachments.png') }}">
                                 <figcaption>
                                     Figure 1.4 – SPPT Attachment Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.6 Submit for Approval</span>
                             <span x-show="lang==='id'">1.6 Submit untuk Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all required fields and attachments,
                                 click "Submit Approval" to initiate the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah seluruh field dan lampiran lengkap,
                                 klik "Submit Approval" untuk memulai proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Once submitted, the document will follow the configured approval hierarchy.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah dikirim, dokumen akan mengikuti
                                 alur approval sesuai konfigurasi sistem.
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
                         <span x-show="lang==='en'">2. Edit SPPT</span>
                         <span x-show="lang==='id'">2. Edit SPPT</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition x-cloak class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Edit SPPT Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Umum Edit SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit SPPT page allows authorized users to modify an existing SPPT
                                 document before final approval. Users may update header information,
                                 detail items, attachments, and related references such as Work Order.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit SPPT memungkinkan user yang berwenang untuk
                                 melakukan perubahan terhadap dokumen SPPT sebelum approval final.
                                 User dapat mengubah informasi header, detail item,
                                 lampiran, serta referensi seperti Work Order.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Edit access is restricted based on document status and workflow authorization.
                             </span>
                             <span x-show="lang==='id'">
                                 Hak edit dibatasi berdasarkan status dokumen
                                 dan otorisasi workflow.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing an SPPT that is already in approval may reset the approval flow.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada SPPT yang sudah masuk proses approval
                                 dapat mereset alur approval.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/edit/overview.png') }}">
                                 <figcaption>
                                     Figure 2.1 – Edit SPPT Page Overview
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Header Modification</span>
                             <span x-show="lang==='id'">2.2 Perubahan Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update Company, Business Unit, Department,
                                 Request Type, Perpost, Tenant, PIC, Unit Status,
                                 Emergency flag, Work Order reference, and Description.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengubah Company, Business Unit, Department,
                                 Request Type, Perpost, Tenant, PIC, Status Unit,
                                 tanda Emergency, referensi Work Order, serta Deskripsi.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Changing Company, Business Unit, Department, or Perpost
                                 may affect available COA and budget validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan Company, Business Unit, Department,
                                 atau Perpost dapat mempengaruhi COA
                                 serta validasi budget yang tersedia.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If Work Order (WO) is modified, related data may need revalidation.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika Work Order (WO) diubah,
                                 data terkait mungkin perlu divalidasi ulang.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/edit/header.png') }}">
                                 <figcaption>
                                     Figure 2.2 – Editable Header Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Edit Detail Items</span>
                             <span x-show="lang==='id'">2.3 Edit Detail Item</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may modify existing detail rows, change product,
                                 quantity, UoM, location, note, and COA.
                                 New rows may also be added, and existing rows can be removed.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengubah baris detail yang ada,
                                 termasuk produk, qty, UoM, lokasi, catatan, dan COA.
                                 User juga dapat menambahkan baris baru
                                 atau menghapus baris yang tidak diperlukan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA selection is dynamically filtered based on
                                 Company, Business Unit, Department, and Perpost.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA difilter secara dinamis berdasarkan
                                 Company, Business Unit, Department, dan Perpost.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Budget validation is re-evaluated when detail items are modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Validasi budget akan dihitung ulang
                                 ketika detail item diubah.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 At least one detail item must remain before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal satu detail item harus tersedia sebelum submit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/edit/detail-table.png') }}">
                                 <figcaption>
                                     Figure 2.3 – Edit Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Manage Attachments</span>
                             <span x-show="lang==='id'">2.4 Kelola Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may remove existing attachments
                                 and upload new supporting documents.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat menghapus lampiran yang sudah ada
                                 dan menambahkan dokumen pendukung baru.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Minimum one attachment is required before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal satu lampiran wajib tersedia sebelum submit.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum file size: 5MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Ukuran maksimal file: 5MB per file.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/edit/attachments.png') }}">
                                 <figcaption>
                                     Figure 2.4 – Attachment Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.5 Submit or Cancel</span>
                             <span x-show="lang==='id'">2.5 Submit atau Cancel</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After modifications are completed, users may
                                 re-submit the SPPT for approval or cancel the document.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah perubahan selesai,
                                 user dapat mengirim ulang SPPT untuk approval
                                 atau membatalkan dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Canceling a document will change its status permanently
                                 and it cannot continue in the approval process.
                             </span>
                             <span x-show="lang==='id'">
                                 Membatalkan dokumen akan mengubah status secara permanen
                                 dan tidak dapat dilanjutkan dalam proses approval.
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
                         <span x-show="lang==='en'">3. List SPPT</span>
                         <span x-show="lang==='id'">3. Daftar SPPT</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 List SPPT Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Umum List SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The List SPPT page displays all SPPT documents based on user access.
                                 Users can monitor document status, filter by workflow stage,
                                 create new SPPT, and access tracking details.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman List SPPT menampilkan seluruh dokumen SPPT
                                 sesuai dengan hak akses user.
                                 User dapat memantau status dokumen,
                                 memfilter berdasarkan tahapan workflow,
                                 membuat SPPT baru, dan melihat detail tracking.
                             </span>
                         </p>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status Summary Cards</span>
                             <span x-show="lang==='id'">3.2 Ringkasan Status Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The top section displays status summary cards,
                                 showing the total number of documents per workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian atas menampilkan kartu ringkasan status,
                                 yang menunjukkan jumlah dokumen berdasarkan status workflow.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>All</strong> – <span x-show="lang==='en'">All SPPT documents</span><span
                                     x-show="lang==='id'">Seluruh dokumen SPPT</span></li>
                             <li><strong>On Progress</strong> – <span x-show="lang==='en'">Documents currently in
                                     approval process</span><span x-show="lang==='id'">Dokumen dalam proses
                                     approval</span></li>
                             <li><strong>Reject</strong> – <span x-show="lang==='en'">Documents rejected during
                                     workflow</span><span x-show="lang==='id'">Dokumen yang ditolak dalam
                                     workflow</span></li>
                             <li><strong>Revise / Draft</strong> – <span x-show="lang==='en'">Documents requiring
                                     revision or still in draft</span><span x-show="lang==='id'">Dokumen revisi atau
                                     masih draft</span></li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Fully approved and completed
                                     documents</span><span x-show="lang==='id'">Dokumen yang sudah selesai dan
                                     approved</span></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Clicking a status card will filter the SPPT list automatically.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan memfilter daftar SPPT secara otomatis.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/list/status-cards.png') }}">
                                 <figcaption>
                                     Figure 3.1 – SPPT Status Summary Cards
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Create New SPPT</span>
                             <span x-show="lang==='id'">3.3 Membuat SPPT Baru</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The “Create” button redirects users to the Create SPPT page
                                 to submit a new SPPT request.
                             </span>
                             <span x-show="lang==='id'">
                                 Tombol “Create” akan mengarahkan user ke halaman Create SPPT
                                 untuk membuat pengajuan SPPT baru.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only authorized users can create new SPPT documents.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya user dengan hak akses yang sesuai
                                 yang dapat membuat SPPT baru.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/list/create-button.png') }}">
                                 <figcaption>
                                     Figure 3.2 – Create SPPT Button
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 SPPT Data Table</span>
                             <span x-show="lang==='id'">3.4 Tabel Data SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The main table displays detailed SPPT information including:
                                 Document ID, Date, Company, Department,
                                 Request Type, Description, and Status.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel utama menampilkan informasi detail SPPT seperti:
                                 DocID, Tanggal, Company, Department,
                                 Request Type, Deskripsi, dan Status.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The table supports searching, sorting, and pagination.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel mendukung fitur pencarian, pengurutan,
                                 dan pagination.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users may click a row or tracking button to open document tracking.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat klik baris atau tombol tracking
                                 untuk membuka detail tracking dokumen.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/list/data-table.png') }}">
                                 <figcaption>
                                     Figure 3.3 – SPPT Data Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 SPPT Tracking Detail</span>
                             <span x-show="lang==='id'">3.5 Detail Tracking SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Tracking Modal provides complete workflow visibility
                                 including SPPT, CS, PO, and BAST progression.
                             </span>
                             <span x-show="lang==='id'">
                                 Tracking Modal menampilkan visibilitas lengkap alur workflow,
                                 termasuk SPPT, CS, PO, dan BAST.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Each tab displays related document headers and detail information.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap tab menampilkan header dan detail dokumen terkait.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Tracking data is read-only and cannot be modified from this view.
                             </span>
                             <span x-show="lang==='id'">
                                 Data tracking bersifat read-only
                                 dan tidak dapat diubah dari halaman ini.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/list/tracking-modal.png') }}">
                                 <figcaption>
                                     Figure 3.4 – SPPT Tracking Modal
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4  ================= -->

         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span x-show="lang==='en'">4. Show SPPT</span>
                     <span x-show="lang==='id'">4. Tampilkan SPPT</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 SPPT Detail Overview</span>
                             <span x-show="lang==='id'">4.1 Gambaran Detail SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show SPPT page provides complete document visibility including
                                 header information, detail lines, attachments, approval workflow,
                                 comments, and related BQ creation.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show SPPT menampilkan informasi lengkap dokumen
                                 termasuk header, detail item, attachment, alur approval,
                                 komentar, serta pembuatan BQ terkait.
                             </span>
                         </p>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Approval Actions</span>
                             <span x-show="lang==='id'">4.2 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized approvers can perform one of the following actions:
                                 Approve, Revise, or Reject the SPPT document.
                             </span>
                             <span x-show="lang==='id'">
                                 Approver yang memiliki hak akses dapat melakukan
                                 Approve, Revise, atau Reject terhadap dokumen SPPT.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> – <span x-show="lang==='en'">Move document to next approval
                                     level</span><span x-show="lang==='id'">Meneruskan ke level approval
                                     berikutnya</span></li>
                             <li><strong>Revise</strong> – <span x-show="lang==='en'">Return document for
                                     correction</span><span x-show="lang==='id'">Mengembalikan dokumen untuk
                                     revisi</span></li>
                             <li><strong>Reject</strong> – <span x-show="lang==='en'">Reject document
                                     permanently</span><span x-show="lang==='id'">Menolak dokumen secara final</span>
                             </li>
                         </ul>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 Approval actions cannot be undone once confirmed.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi approval tidak dapat dibatalkan setelah dikonfirmasi.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/show/approval-actions.png') }}">
                                 <figcaption>
                                     Figure 4.1 – Approval Action Buttons
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 SPPT Header Information</span>
                             <span x-show="lang==='id'">4.3 Informasi Header SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays general document information including:
                                 SPPT ID, Status, Company, Department, Tenant, PIC, and Request Type.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan informasi umum dokumen seperti:
                                 SPPT ID, Status, Company, Department, Tenant, PIC, dan Request Type.
                             </span>
                         </p>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 Users can print the SPPT document in PDF format.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mencetak dokumen SPPT dalam format PDF.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/show/header-info.png') }}">
                                 <figcaption>
                                     Figure 4.2 – SPPT Header Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Tabs: Attachment, Approval & Comments</span>
                             <span x-show="lang==='id'">4.4 Tab: Attachment, Approval & Komentar</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right section contains three tabs:
                                 Attachment, Approval Details, and Comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian kanan terdiri dari tiga tab:
                                 Attachment, Approval Details, dan Comments.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Attachment</strong> – <span x-show="lang==='en'">View and upload supporting
                                     documents</span><span x-show="lang==='id'">Melihat dan upload dokumen
                                     pendukung</span></li>
                             <li><strong>Approval Details</strong> – <span x-show="lang==='en'">Displays approval level
                                     history</span><span x-show="lang==='id'">Menampilkan riwayat approval</span></li>
                             <li><strong>Comments</strong> – <span x-show="lang==='en'">Discussion between users and
                                     approvers</span><span x-show="lang==='id'">Diskusi antara user dan approver</span>
                             </li>
                         </ul>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 Comments are stored chronologically and visible to all related users.
                             </span>
                             <span x-show="lang==='id'">
                                 Komentar tersimpan secara kronologis dan dapat dilihat oleh user terkait.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/show/tabs-section.png') }}">
                                 <figcaption>
                                     Figure 4.3 – Attachment & Approval Tabs
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 SPPT Detail Items</span>
                             <span x-show="lang==='id'">4.5 Detail Item SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The SPPT Detail table displays requested items including
                                 quantity, location, budget allocation, and ordering progress.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel Detail SPPT menampilkan item yang diajukan
                                 termasuk quantity, lokasi, alokasi budget,
                                 dan progres pemesanan.
                             </span>
                         </p>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 Ordering columns show progress from ordered to completed.
                             </span>
                             <span x-show="lang==='id'">
                                 Kolom ordering menunjukkan progres dari ordered hingga completed.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/show/detail-table.png') }}">
                                 <figcaption>
                                     Figure 4.4 – SPPT Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.6 Create BQ & Edit COA</span>
                             <span x-show="lang==='id'">4.6 Create BQ & Edit COA</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If no BQ exists, users can create a new BQ directly from the SPPT.
                                 If BQ already exists, the button will display the BQ ID.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika belum ada BQ, user dapat membuat BQ langsung dari SPPT.
                                 Jika BQ sudah ada, tombol akan menampilkan ID BQ tersebut.
                             </span>
                         </p>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 BQ creation is allowed only after certain approval stages.
                             </span>
                             <span x-show="lang==='id'">
                                 Pembuatan BQ hanya diperbolehkan setelah tahapan approval tertentu.
                             </span>
                         </div>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users with appropriate access may edit COA allocations via the Edit COA modal.
                             </span>
                             <span x-show="lang==='id'">
                                 User dengan hak akses tertentu dapat mengubah alokasi COA melalui modal Edit COA.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppt/show/create-bq-coa.png') }}">
                                 <figcaption>
                                     Figure 4.5 – Create BQ & Edit COA Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                 </div>
             </div>

         </section>

         <!-- ================= SECTION 5  ================= -->

         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span x-show="lang==='en'">5. Create BQ</span>
                     <span x-show="lang==='id'">5. Buat BQ</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.1 Create BQ from SPPT</span>
                             <span x-show="lang==='id'">5.1 Membuat BQ dari SPPT</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create BQ page allows users to import Bill of Quantity (BQ)
                                 data into the system based on an existing SPPT document.
                                 The process is performed via Excel template upload.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create BQ digunakan untuk mengimpor data
                                 Bill of Quantity (BQ) ke dalam sistem berdasarkan
                                 dokumen SPPT yang sudah ada.
                                 Proses dilakukan melalui upload template Excel.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 BQ creation is linked to a specific SPPT document.
                             </span>
                             <span x-show="lang==='id'">
                                 Pembuatan BQ terhubung langsung dengan dokumen SPPT tertentu.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.2 Import BQ Form</span>
                             <span x-show="lang==='id'">5.2 Form Import BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must upload the Excel file using the official BQ template.
                                 The system validates the file before previewing the data.
                             </span>
                             <span x-show="lang==='id'">
                                 User harus mengunggah file Excel menggunakan template BQ resmi.
                                 Sistem akan melakukan validasi sebelum menampilkan preview data.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>SPPT ID</strong> – <span x-show="lang==='en'">Auto-filled reference
                                     document</span><span x-show="lang==='id'">Referensi dokumen otomatis</span></li>
                             <li><strong>Company</strong> – <span x-show="lang==='en'">Company code linked to
                                     SPPT</span><span x-show="lang==='id'">Kode perusahaan dari SPPT</span></li>
                             <li><strong>Department</strong> – <span x-show="lang==='en'">Department from
                                     SPPT</span><span x-show="lang==='id'">Departemen dari SPPT</span></li>
                             <li><strong>Import Excel</strong> – <span x-show="lang==='en'">Upload .xlsx
                                     file</span><span x-show="lang==='id'">Upload file .xlsx</span></li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only Excel files using the official template format are accepted.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya file Excel dengan format template resmi yang diterima.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/import-form.png') }}">
                                 <figcaption>
                                     Figure 5.1 – Import BQ Form
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.3 BQ Preview</span>
                             <span x-show="lang==='id'">5.3 Preview Data BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After importing, the system displays a preview table
                                 containing the BQ line details before final submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah import berhasil, sistem menampilkan tabel preview
                                 berisi detail baris BQ sebelum disimpan secara final.
                             </span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The table includes material and service estimations.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel mencakup estimasi material dan jasa.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Line No</li>
                             <li>Description</li>
                             <li>Quantity</li>
                             <li>UoM</li>
                             <li>Estimated Material Price</li>
                             <li>Total Estimated Material</li>
                             <li>Estimated Jasa Price</li>
                             <li>Total Estimated Jasa</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users should verify quantities and pricing before saving.
                             </span>
                             <span x-show="lang==='id'">
                                 User harus memverifikasi quantity dan harga sebelum menyimpan.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/preview-table.png') }}">
                                 <figcaption>
                                     Figure 5.2 – BQ Preview Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.4 Photo Before Documentation</span>
                             <span x-show="lang==='id'">5.4 Dokumentasi Foto Sebelum Pekerjaan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users are required to upload "Photo Before" documentation
                                 as supporting evidence before submitting the BQ.
                             </span>
                             <span x-show="lang==='id'">
                                 User wajib mengunggah dokumentasi "Foto Before"
                                 sebagai bukti pendukung sebelum submit BQ.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Accepted format: JPG or PNG, maximum 5 MB per image.
                             </span>
                             <span x-show="lang==='id'">
                                 Format yang diterima: JPG atau PNG,
                                 maksimal 5 MB per foto.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Multiple photos can be uploaded.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengunggah lebih dari satu foto.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/photo-before.png') }}">
                                 <figcaption>
                                     Figure 5.3 – Photo Before Upload Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.5 Save BQ</span>
                             <span x-show="lang==='id'">5.5 Simpan BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After verification and attachment upload,
                                 users can save the BQ data into the system.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah verifikasi dan upload dokumen,
                                 user dapat menyimpan data BQ ke dalam sistem.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once saved, the BQ will proceed to the next workflow stage.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disimpan, BQ akan masuk ke tahapan workflow berikutnya.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/save-button.png') }}">
                                 <figcaption>
                                     Figure 5.4 – Save BQ Button
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 6  ================= -->

         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s6')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span x-show="lang==='en'">6. Show BQ</span>
                     <span x-show="lang==='id'">6. Lihat BQ</span>
                     </span>

                     <span x-text="openSection==='s6' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s6'" x-transition class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.1 BQ Detail Overview</span>
                             <span x-show="lang==='id'">6.1 Gambaran Detail BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show BQ page displays complete Bill of Quantity (BQ) information
                                 including header data, attachment photos, and detailed cost estimation lines.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show BQ menampilkan informasi lengkap Bill of Quantity (BQ)
                                 termasuk data header, lampiran foto, dan detail estimasi biaya.
                             </span>
                         </p>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.2 Edit Permission</span>
                             <span x-show="lang==='id'">6.2 Hak Akses Edit</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit button is displayed only if the user has permission
                                 and the document is still within editable workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Tombol Edit hanya ditampilkan jika user memiliki hak akses
                                 dan dokumen masih berada dalam status workflow yang dapat diedit.
                             </span>
                         </p>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 Editing a BQ may reset the approval process depending on workflow configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada BQ dapat mereset proses approval tergantung konfigurasi workflow.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/show/edit-button.png') }}">
                                 <figcaption>
                                     Figure 6.1 – Edit Button Visibility
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.3 BQ Header Information</span>
                             <span x-show="lang==='id'">6.3 Informasi Header BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays general BQ information including:
                                 BQ ID, Related SPPT ID, Company, Creation Date, and Created User.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan informasi umum BQ seperti:
                                 BQ ID, ID SPPT terkait, Company, Tanggal pembuatan, dan User pembuat.
                             </span>
                         </p>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 Users may print the BQ document in PDF format.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mencetak dokumen BQ dalam format PDF.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/show/header-section.png') }}">
                                 <figcaption>
                                     Figure 6.2 – BQ Header Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.4 Photo Before Attachments</span>
                             <span x-show="lang==='id'">6.4 Lampiran Foto Before</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Photo Before section displays image attachments uploaded
                                 as supporting documentation for the BQ.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian Photo Before menampilkan lampiran gambar
                                 sebagai dokumentasi pendukung BQ.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users with edit permission can upload additional attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 User dengan hak edit dapat menambahkan lampiran tambahan.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum 10 files per upload. PDF and image formats are recommended.
                             </span>
                             <span x-show="lang==='id'">
                                 Maksimal 10 file per upload. Format PDF dan gambar direkomendasikan.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/show/photo-before.png') }}">
                                 <figcaption>
                                     Figure 6.3 – Photo Before Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.5 BQ Detail Lines</span>
                             <span x-show="lang==='id'">6.5 Detail Item BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The BQ Detail table displays cost estimation lines including
                                 quantity, unit of measurement, estimated material price,
                                 and estimated service (jasa) price.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel Detail BQ menampilkan estimasi biaya termasuk
                                 quantity, satuan, estimasi harga material,
                                 dan estimasi harga jasa.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Est Mat Price</strong> – <span x-show="lang==='en'">Estimated material unit
                                     price</span><span x-show="lang==='id'">Estimasi harga material per unit</span>
                             </li>
                             <li><strong>Total Est Mat</strong> – <span x-show="lang==='en'">Total estimated material
                                     cost</span><span x-show="lang==='id'">Total estimasi biaya material</span></li>
                             <li><strong>Est Jasa Price</strong> – <span x-show="lang==='en'">Estimated service unit
                                     price</span><span x-show="lang==='id'">Estimasi harga jasa per unit</span></li>
                             <li><strong>Total Est Jasa</strong> – <span x-show="lang==='en'">Total estimated service
                                     cost</span><span x-show="lang==='id'">Total estimasi biaya jasa</span></li>
                         </ul>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 Totals are calculated automatically based on quantity × unit price.
                             </span>
                             <span x-show="lang==='id'">
                                 Total dihitung otomatis berdasarkan quantity × harga satuan.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/show/detail-table.png') }}">
                                 <figcaption>
                                     Figure 6.4 – BQ Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 7  ================= -->

         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s7')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span x-show="lang==='en'">7. Edit BQ</span>
                     <span x-show="lang==='id'">7. Edit BQ</span>
                     </span>

                     <span x-text="openSection==='s7' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s7'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.1 Edit BQ Overview</span>
                             <span x-show="lang==='id'">7.1 Gambaran Edit BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit BQ page allows authorized users to modify
                                 an existing Bill of Quantity before final approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit BQ memungkinkan user berwenang
                                 untuk melakukan perubahan pada Bill of Quantity sebelum approval final.
                             </span>
                         </p>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 Editing a BQ will reset the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada BQ akan mereset alur approval.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.2 Header Information</span>
                             <span x-show="lang==='id'">7.2 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays key BQ information in read-only format,
                                 including BQ ID, related SPPT ID, Company, and Created By.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan informasi utama BQ dalam format read-only,
                                 termasuk BQ ID, SPPT terkait, Company, dan User pembuat.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/header-info.png') }}">
                                 <figcaption>
                                     Figure 7.1 – Edit BQ Header Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.3 Re-Import Excel File</span>
                             <span x-show="lang==='id'">7.3 Import Ulang File Excel</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may upload a revised Excel file to update
                                 BQ detail lines.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengunggah ulang file Excel untuk memperbarui
                                 detail item BQ.
                             </span>
                         </p>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 The system will replace existing detail lines with
                                 the newly imported data.
                             </span>
                             <span x-show="lang==='id'">
                                 Sistem akan mengganti detail item lama dengan data
                                 hasil import terbaru.
                             </span>
                         </div>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 Ensure the Excel format matches the official BQ template.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan format Excel sesuai dengan template BQ resmi.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/import-section.png') }}">
                                 <figcaption>
                                     Figure 7.2 – Import Excel Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.4 BQ Detail Preview</span>
                             <span x-show="lang==='id'">7.4 Preview Detail BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After import, the system displays a preview of
                                 the BQ detail lines before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah import, sistem menampilkan preview
                                 detail item BQ sebelum disubmit.
                             </span>
                         </p>

                         <div class="manual-info manual-note">
                             <span x-show="lang==='en'">
                                 Users should verify quantities, prices, and totals
                                 before submitting for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 User wajib memverifikasi quantity, harga, dan total
                                 sebelum submit approval.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/detail-preview.png') }}">
                                 <figcaption>
                                     Figure 7.3 – BQ Detail Preview Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.5 Attachment Management</span>
                             <span x-show="lang==='id'">7.5 Manajemen Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Photo Before section allows users to manage
                                 both existing and new attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian Photo Before memungkinkan user mengelola
                                 lampiran lama dan menambahkan lampiran baru.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><span x-show="lang==='en'">Existing attachments can be removed.</span><span
                                     x-show="lang==='id'">Lampiran lama dapat dihapus.</span></li>
                             <li><span x-show="lang==='en'">New attachments can be added via Add Photo.</span><span
                                     x-show="lang==='id'">Lampiran baru dapat ditambahkan melalui Add Photo.</span>
                             </li>
                         </ul>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 Maximum file size: 5 MB per photo.
                             </span>
                             <span x-show="lang==='id'">
                                 Ukuran maksimal: 5 MB per foto.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/attachment-section.png') }}">
                                 <figcaption>
                                     Figure 7.4 – Attachment Management Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.6 Submit for Approval</span>
                             <span x-show="lang==='id'">7.6 Submit untuk Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing edits and verifying data,
                                 users must submit the BQ for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah selesai melakukan perubahan dan verifikasi data,
                                 user wajib melakukan submit approval.
                             </span>
                         </p>

                         <div class="manual-warning manual-note">
                             <span x-show="lang==='en'">
                                 Once submitted, the BQ will follow the configured approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, BQ akan mengikuti alur approval sesuai konfigurasi.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/submit-approval.png') }}">
                                 <figcaption>
                                     Figure 7.5 – Submit Approval Button
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
     </div>
