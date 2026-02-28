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
                         <span x-show="lang==='en'">1. Create SPPJ</span>
                         <span x-show="lang==='id'">1. Membuat SPPJ</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Create SPPJ Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Create SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create SPPJ page allows users to submit a Service or Contract Request
                                 including header data, item details, budget allocation, and mandatory attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create SPPJ digunakan untuk mengajukan permintaan Jasa atau Kontrak
                                 yang mencakup data header, detail item, alokasi budget, serta lampiran wajib.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Request Type and COA selection determine the approval workflow of the SPPJ document.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan Request Type dan COA menentukan alur approval dokumen SPPJ.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Header Information</span>
                             <span x-show="lang==='id'">1.2 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must complete Company, Business Unit, Department,
                                 Request Type, Perpost, and Description before proceeding.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna wajib mengisi Company, Business Unit, Department,
                                 Request Type, Perpost, dan Description sebelum melanjutkan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the selected Business Unit is correct.
                                 Budget validation and COA filtering are locked based on the selected Business Unit.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan Business Unit yang dipilih sudah benar.
                                 Validasi budget dan filter COA terkunci sesuai Business Unit yang dipilih.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/create-header.png') }}">
                                 <figcaption>
                                     Figure 1.1 – SPPJ Header Information
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Emergency & Work Order</span>
                             <span x-show="lang==='id'">1.4 Emergency & Work Order</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may mark the SPPJ as Emergency and optionally link an existing Work Order (WO).
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat menandai SPPJ sebagai Emergency dan menghubungkannya dengan Work Order
                                 (WO).
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If a WO is selected, related information such as description
                                 and supporting documents may be inherited.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika WO dipilih, informasi seperti deskripsi dan dokumen pendukung dapat ikut terbawa.
                             </span>
                         </div>

                         {{-- <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/create/emergency-wo.png') }}">
                                 <figcaption>
                                     Figure 1.2 – Emergency Flag & WO Selection
                                 </figcaption>
                             </figure>
                         </div> --}}

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 SPPJ Detail</span>
                             <span x-show="lang==='id'">1.4 Detail SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must add at least one item including Product, Quantity,
                                 Unit of Measure, Location, and COA allocation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna wajib menambahkan minimal satu item yang mencakup Product,
                                 Quantity, UoM, Location, dan alokasi COA.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA selection is filtered based on Company, Business Unit,
                                 Financial Department, and Perpost.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA difilter berdasarkan Company, Business Unit,
                                 Department Finance, dan Perpost.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/create-detail.png') }}">
                                 <figcaption>
                                     Figure 1.3 – SPPJ Detail Entry Table
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
                                 Attachment requirements vary depending on the selected BQ Type (Jasa or Kontrak).
                             </span>
                             <span x-show="lang==='id'">
                                 Ketentuan lampiran berbeda tergantung BQ Type yang dipilih (Jasa atau Kontrak).
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 At least one attachment is mandatory before submitting SPPJ.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal 1 lampiran wajib diunggah sebelum submit SPPJ.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum file size is 5MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Ukuran maksimal file adalah 5MB per file.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/create-attachment.png') }}">
                                 <figcaption>
                                     Figure 1.4 – Attachment Section (Jasa & Kontrak Mode)
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.6 Submit SPPJ</span>
                             <span x-show="lang==='id'">1.6 Submit SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all required fields and attachments,
                                 users may submit the SPPJ for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah seluruh field dan lampiran wajib terisi,
                                 pengguna dapat melakukan submit untuk proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Submission will be rejected automatically if:
                                 - No attachment is uploaded
                                 - Mandatory contract documents are missing
                                 - Budget validation fails due to Business Unit mismatch
                             </span>
                             <span x-show="lang==='id'">
                                 Sistem akan menolak submit jika:
                                 - Tidak ada lampiran yang diunggah
                                 - Dokumen kontrak mandatory belum lengkap
                                 - Validasi budget gagal karena Business Unit tidak sesuai
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
                         <span x-show="lang==='en'">2. Edit SPPJ</span>
                         <span x-show="lang==='id'">2. Edit SPPJ</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>



                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Edit SPPJ Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Edit SPPJ</span>
                         </h3>



                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit SPPJ page allows users to modify an existing SPPJ document
                                 before re-submitting it for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit SPPJ digunakan untuk mengubah dokumen SPPJ
                                 sebelum diajukan kembali untuk proses approval.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/edit-list.png') }}">
                                 <figcaption>
                                     Figure 2.1 – Edit SPPJ Page Overview
                                 </figcaption>
                             </figure>
                         </div>
                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing is only allowed while the document status is Draft or Revise.
                             </span>
                             <span x-show="lang==='id'">
                                 Proses edit hanya diperbolehkan jika status dokumen masih Draft atau Revise.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Re-submitting the document will restart the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Submit ulang dokumen akan mereset alur approval.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Edit Header Information</span>
                             <span x-show="lang==='id'">2.2 Edit Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update Business Unit, Request Type, BQ Type,
                                 Description, and Emergency flag.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat memperbarui Business Unit, Request Type,
                                 BQ Type, Description, serta status Emergency.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Changing the Business Unit may affect budget validation
                                 and COA availability. Ensure the selected Business Unit is correct.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan Business Unit dapat mempengaruhi validasi budget
                                 dan ketersediaan COA. Pastikan Business Unit sudah benar.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/edit-header.png') }}">
                                 <figcaption>
                                     Figure 2.2 – Edit Header Fields
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Edit SPPJ Detail</span>
                             <span x-show="lang==='id'">2.3 Edit Detail SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may modify item quantity, replace products,
                                 update location, or change COA allocation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat mengubah quantity, mengganti produk,
                                 memperbarui lokasi, atau mengubah alokasi COA.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Deleting a detail row will permanently remove the item
                                 from the document upon submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Menghapus baris detail akan menghilangkan item tersebut
                                 secara permanen setelah dokumen disubmit.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/edit-detail.png') }}">
                                 <figcaption>
                                     Figure 2.3 – Edit SPPJ Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Manage Attachments</span>
                             <span x-show="lang==='id'">2.3 Kelola Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may remove existing attachments and upload new files.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat menghapus lampiran yang ada dan mengunggah file baru.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 At least one attachment must remain before submitting.
                                 Maximum file size is 5MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal harus terdapat 1 lampiran sebelum submit.
                                 Ukuran maksimal file adalah 5MB per file.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 For BQ Type "Kontrak", mandatory contract documents
                                 must remain uploaded.
                             </span>
                             <span x-show="lang==='id'">
                                 Untuk BQ Type "Kontrak", dokumen kontrak yang bertanda
                                 Mandatory wajib tetap terunggah.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/edit-attachment.png') }}">
                                 <figcaption>
                                     Figure 2.4 – Edit Attachment Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Submit or Cancel Document</span>
                             <span x-show="lang==='id'">2.4 Submit atau Cancel Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all required updates, users may resubmit
                                 the document for approval or cancel the document if necessary.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah seluruh perubahan selesai, pengguna dapat
                                 melakukan submit ulang untuk approval atau melakukan cancel dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once submitted, the document will follow the approval workflow
                                 and cannot be edited unless returned.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, dokumen akan mengikuti alur approval
                                 dan tidak dapat diedit kecuali dikembalikan.
                             </span>
                         </div>

                     </section>
                 </div>


         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. List SPPJ</span>
                         <span x-show="lang==='id'">3. Daftar SPPJ</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 List SPPJ Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Daftar SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The List SPPJ page provides a centralized view of all SPPJ documents
                                 based on the user’s access rights.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar SPPJ menampilkan seluruh dokumen SPPJ
                                 berdasarkan hak akses pengguna.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only documents aligned with the user’s Company,
                                 Business Unit, and Department are visible.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya dokumen yang sesuai dengan Company,
                                 Business Unit, dan Department pengguna yang ditampilkan.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">


                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status Overview</span>
                             <span x-show="lang==='id'">3.2 Ringkasan Status</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>All</li>
                             <li>On Progress</li>
                             <li>Reject</li>
                             <li>Revise / Draft</li>
                             <li>Completed</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Clicking a status card will automatically filter
                                 the SPPJ list according to the selected status.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan otomatis memfilter
                                 daftar SPPJ sesuai status yang dipilih.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/status-overview.png') }}">
                                 <figcaption>
                                     Figure 3.1 – SPPJ Status Overview Cards
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 SPPJ Data Table</span>
                             <span x-show="lang==='id'">3.3 Tabel Data SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The table displays detailed information of all SPPJ documents.
                                 Users may search, sort, and filter data dynamically.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel menampilkan detail seluruh dokumen SPPJ.
                                 Pengguna dapat melakukan pencarian, pengurutan, dan filter data secara dinamis.
                             </span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Columns displayed include:
                             </span>
                             <span x-show="lang==='id'">
                                 Kolom yang ditampilkan meliputi:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>DocID</li>
                             <li>Date</li>
                             <li>Company</li>
                             <li>Department</li>
                             <li>BQ Type</li>
                             <li>Request Type</li>
                             <li>Description</li>
                             <li>Status</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only documents accessible to the user’s Business Unit
                                 and Department will be displayed.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya dokumen yang sesuai dengan Business Unit
                                 dan Department pengguna yang akan ditampilkan.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/data-table.png') }}">
                                 <figcaption>
                                     Figure 3.2 – SPPJ Data Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Create New SPPJ</span>
                             <span x-show="lang==='id'">3.4 Membuat SPPJ Baru</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may create a new SPPJ by clicking the "Create" button
                                 located at the top-right of the page.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat membuat SPPJ baru dengan menekan tombol "Create"
                                 yang berada di bagian kanan atas halaman.
                             </span>
                         </p>


                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 Tracking Detail</span>
                             <span x-show="lang==='id'">3.5 Detail Tracking</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Tracking modal provides end-to-end document visibility,
                                 showing the workflow from SPPJ to CS, SPK, and BAST.
                             </span>
                             <span x-show="lang==='id'">
                                 Modal Tracking menampilkan alur dokumen secara menyeluruh,
                                 mulai dari SPPJ hingga CS, SPK, dan BAST.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>SPPJ</li>
                             <li>CS</li>
                             <li>SPK</li>
                             <li>BAST</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Each tab dynamically loads document details
                                 related to the selected SPPJ.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap tab akan memuat detail dokumen secara dinamis
                                 berdasarkan SPPJ yang dipilih.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/tracking-modal.png') }}">
                                 <figcaption>
                                     Figure 3.4 – SPPJ Tracking Modal
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

                     <span x-show="lang==='en'">4. Show SPPJ</span>
                     <span x-show="lang==='id'">4. Tampilkan SPPJ</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- OVERVIEW -->

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Show SPPJ Overview</span>
                             <span x-show="lang==='id'">4.1 Gambaran Show SPPJ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show SPPJ page provides complete visibility of the document,
                                 including header information, workflow status, item details,
                                 attachments, and approval history.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show SPPJ menampilkan informasi lengkap dokumen,
                                 termasuk header, status workflow, detail item,
                                 lampiran, serta riwayat approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This page is used for document monitoring, approval processing,
                                 and audit tracking.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini digunakan untuk monitoring dokumen,
                                 proses approval, dan kebutuhan audit.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Budget allocation and COA are locked based on the selected Business Unit.
                                 Changes require authorized financial access.
                             </span>
                             <span x-show="lang==='id'">
                                 Alokasi budget dan COA dikunci berdasarkan Business Unit yang dipilih.
                                 Perubahan memerlukan otorisasi keuangan.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/show/overview.png') }}">
                                 <figcaption>
                                     Figure 4.1 – Show SPPJ Overview Page
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Approval Actions</span>
                             <span x-show="lang==='id'">4.2 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized users may perform approval actions:
                                 Approve, Revise, or Reject the document.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna yang berwenang dapat melakukan aksi:
                                 Approve, Revise, atau Reject dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Approval decisions will immediately update the workflow status
                                 and cannot be reversed without administrative intervention.
                             </span>
                             <span x-show="lang==='id'">
                                 Keputusan approval akan langsung memperbarui status workflow
                                 dan tidak dapat dibatalkan tanpa intervensi administrator.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 Document Information</span>
                             <span x-show="lang==='id'">4.3 Informasi Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays document metadata such as
                                 Company, Department, Request Type, BQ Type, Status,
                                 and creation details.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan metadata dokumen seperti
                                 Company, Department, Request Type, BQ Type, Status,
                                 dan informasi pembuat.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Status badges are color-coded to reflect
                                 the current workflow state.
                             </span>
                             <span x-show="lang==='id'">
                                 Badge status menggunakan warna berbeda
                                 untuk menunjukkan kondisi workflow saat ini.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Attachment & Approval Details</span>
                             <span x-show="lang==='id'">4.4 Lampiran & Detail Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right-side tabs provide structured access to:
                                 Attachment files, Approval history, and Comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Tab di sisi kanan menyediakan akses terstruktur ke:
                                 Lampiran, Riwayat Approval, dan Komentar.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Attachments are limited to maximum 10 files per upload.
                                 File types such as PDF and image formats are recommended.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran dibatasi maksimal 10 file per upload.
                                 Format file PDF dan gambar direkomendasikan.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 SPPJ Detail & Budget Allocation</span>
                             <span x-show="lang==='id'">4.5 Detail SPPJ & Alokasi Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The bottom section displays all item details,
                                 including quantity, location, COA allocation,
                                 and budget department.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian bawah menampilkan seluruh detail item,
                                 termasuk quantity, lokasi, alokasi COA,
                                 dan budget department.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Budget allocation is locked based on the selected Business Unit.
                                 Changes must follow financial authorization policies.
                             </span>
                             <span x-show="lang==='id'">
                                 Alokasi budget dikunci berdasarkan Business Unit yang dipilih.
                                 Perubahan harus mengikuti kebijakan otorisasi keuangan.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.6 Edit COA (Authorized Users)</span>
                             <span x-show="lang==='id'">4.6 Edit COA (Pengguna Berwenang)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users with financial control access may modify
                                 COA allocation using the Edit COA feature.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dengan akses kontrol keuangan dapat mengubah
                                 alokasi COA melalui fitur Edit COA.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA selection must match Company, Business Unit,
                                 Department Financial, and Perpost validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA harus sesuai dengan validasi
                                 Company, Business Unit, Department Financial, dan Perpost.
                             </span>
                         </div>
                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.7 Create or View BQ</span>
                             <span x-show="lang==='id'">4.7 Buat atau Lihat BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create BQ button allows users to generate
                                 a Bill of Quantity (BQ) based on the selected SPPJ.
                             </span>
                             <span x-show="lang==='id'">
                                 Tombol Create BQ digunakan untuk membuat
                                 Bill of Quantity (BQ) berdasarkan SPPJ yang dipilih.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If a BQ already exists, the button will display
                                 the BQ ID and redirect to the BQ detail page.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika BQ sudah dibuat, tombol akan menampilkan
                                 ID BQ dan mengarahkan ke halaman detail BQ.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 BQ Type depends on the selected SPPJ type:
                                 <br>• Jasa → Standard BQ
                                 <br>• Kontrak → Contract BQ
                             </span>
                             <span x-show="lang==='id'">
                                 Tipe BQ mengikuti tipe SPPJ yang dipilih:
                                 <br>• Jasa → BQ Standar
                                 <br>• Kontrak → BQ Kontrak
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once BQ is created, item quantities and budget allocations
                                 will be referenced in the procurement process.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah BQ dibuat, quantity dan alokasi budget
                                 akan digunakan dalam proses procurement selanjutnya.
                             </span>
                         </div>

                         <!-- FIGURE -->
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/bq-viewbutton.png') }}">
                                 <figcaption>
                                     Figure 4.7 – Create / View BQ Button
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
                             <span x-show="lang==='en'">5.2 Import BQ (Excel)</span>
                             <span x-show="lang==='id'">5.2 Import BQ (Excel)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may upload a standardized Excel template
                                 to import BQ line items into the system.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat mengunggah template Excel standar
                                 untuk mengimpor detail BQ ke dalam sistem.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only the official Template BQ file format is supported.
                                 Editing column structure may cause import failure.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya format resmi Template BQ yang diperbolehkan.
                                 Perubahan struktur kolom dapat menyebabkan kegagalan import.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure quantities, prices, and UoM values are correctly formatted
                                 before submitting.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan quantity, harga, dan UoM sudah diformat dengan benar
                                 sebelum melakukan submit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/create/import-bq.png') }}">
                                 <figcaption>
                                     Figure 5.2 – Import BQ Excel Form
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.3 Preview & Photo Before</span>
                             <span x-show="lang==='id'">5.3 Preview & Foto Before</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After import, the system displays a preview table
                                 to verify line details before final submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah import, sistem menampilkan tabel preview
                                 untuk verifikasi detail sebelum submit final.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Photo Before documentation is mandatory
                                 for contract or work validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumentasi Foto Before bersifat wajib
                                 untuk validasi pekerjaan atau kontrak.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum 5MB per photo. JPG/PNG recommended.
                             </span>
                             <span x-show="lang==='id'">
                                 Maksimal 5MB per foto. Disarankan format JPG/PNG.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/create/preview-photo.png') }}">
                                 <figcaption>
                                     Figure 5.3 – BQ Preview & Photo Upload
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.4 Create BQ Kontrak</span>
                             <span x-show="lang==='id'">5.4 Buat BQ Kontrak</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 For Contract-based SPPJ, BQ must be created
                                 using predefined Contract Categories.
                             </span>
                             <span x-show="lang==='id'">
                                 Untuk SPPJ tipe Kontrak, BQ dibuat
                                 menggunakan Category Kontrak yang telah ditentukan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Selecting a category automatically loads
                                 predefined contract items into the detail table.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan category akan otomatis memuat
                                 item kontrak ke dalam tabel detail.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Users are required to input quantity values
                                 before saving the contract BQ.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna wajib mengisi quantity
                                 sebelum menyimpan BQ kontrak.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/bq-kontrak.png') }}">
                                 <figcaption>
                                     Figure 5.4 – Create BQ Kontrak Page
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.5 Select Kontrak Category</span>
                             <span x-show="lang==='id'">5.5 Pilih Category Kontrak</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The category modal allows users to search,
                                 filter, and select contract categories.
                             </span>
                             <span x-show="lang==='id'">
                                 Modal category memungkinkan pengguna
                                 mencari dan memilih kategori kontrak.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only active and authorized contract categories
                                 are available for selection.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya kategori kontrak yang aktif dan berwenang
                                 yang tersedia untuk dipilih.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/kontrak-category.png') }}">
                                 <figcaption>
                                     Figure 5.5 – Kontrak Category Selection Modal
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
                             <span x-show="lang==='en'">6.1 Show BQ Overview</span>
                             <span x-show="lang==='id'">6.1 Gambaran Show BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show BQ page provides detailed information regarding
                                 cost estimation, item breakdown, documentation,
                                 and approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show BQ menampilkan detail estimasi biaya,
                                 rincian item, dokumentasi,
                                 serta alur approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This page is used for financial validation,
                                 review, approval, revision, or rejection of the BQ.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini digunakan untuk validasi keuangan,
                                 review, approval, revisi, atau penolakan BQ.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 All total calculations are generated automatically by the system
                                 and cannot be manually altered.
                             </span>
                             <span x-show="lang==='id'">
                                 Seluruh perhitungan total dihasilkan otomatis oleh sistem
                                 dan tidak dapat diubah secara manual.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppj/bq-overview.png') }}">
                                 <figcaption>
                                     Figure 6.1 – Show BQ Page Overview
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.2 BQ Header Information</span>
                             <span x-show="lang==='id'">6.2 Informasi Header BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays BQ ID, SPPJ ID, Company,
                                 Creation Date, and Creator information.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan ID BQ, ID SPPJ, Company,
                                 tanggal pembuatan, dan user pembuat.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 BQ data is system-generated and cannot be manually altered
                                 unless edit permission is granted.
                             </span>
                             <span x-show="lang==='id'">
                                 Data BQ dihasilkan oleh sistem dan tidak dapat diubah
                                 kecuali memiliki hak edit.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.3 Photo Before & Attachments</span>
                             <span x-show="lang==='id'">6.3 Foto Before & Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 This section displays uploaded documentation photos and supporting files.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian ini menampilkan foto dokumentasi dan file pendukung.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum 10 files per upload. Recommended formats: PDF / Image.
                             </span>
                             <span x-show="lang==='id'">
                                 Maksimal 10 file per upload. Disarankan format PDF / Image.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Attachments are stored securely and linked to the BQ record.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran disimpan secara aman dan terhubung langsung ke record BQ.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">6.4 BQ Detail Table</span>
                             <span x-show="lang==='id'">6.4 Tabel Detail BQ</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Displays all line items including quantity,
                                 material estimation, service estimation, and totals.
                             </span>
                             <span x-show="lang==='id'">
                                 Menampilkan seluruh detail item termasuk quantity,
                                 estimasi material, estimasi jasa, dan total.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 All calculation values are generated automatically by the system.
                                 Manual modification is not permitted at this stage.
                             </span>
                             <span x-show="lang==='id'">
                                 Seluruh nilai perhitungan dihasilkan otomatis oleh sistem.
                                 Perubahan manual tidak diperbolehkan pada tahap ini.
                             </span>
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
                                 an existing BQ before final approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit BQ memungkinkan pengguna berwenang
                                 untuk melakukan perubahan sebelum approval final.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 BQ can still be edited while the first approval level
                                 status is <strong>Waiting Approval</strong>.
                             </span>
                             <span x-show="lang==='id'">
                                 BQ masih dapat diedit selama status approval level pertama
                                 adalah <strong>Waiting Approval</strong>.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing a BQ will reset the approval workflow
                                 back to the first approval level.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada BQ akan mereset alur approval
                                 kembali ke level pertama.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/overview.png') }}">
                                 <figcaption>
                                     Figure 7.1 – Edit BQ Page Overview
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.2 Edit BQ Kontrak – Direct Update</span>
                             <span x-show="lang==='id'">7.2 Edit BQ Kontrak – Update Langsung</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 By default, the system allows direct quantity updates
                                 without changing the contract category.
                             </span>
                             <span x-show="lang==='id'">
                                 Secara default, sistem memungkinkan update quantity
                                 tanpa mengganti category kontrak.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Mode: <strong>Update Qty (Direct)</strong>
                                 modifies existing BQ detail records.
                             </span>
                             <span x-show="lang==='id'">
                                 Mode: <strong>Update Qty (Direct)</strong>
                                 akan mengubah record detail BQ yang sudah ada.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only quantity values are editable in this mode.
                             </span>
                             <span x-show="lang==='id'">
                                 Pada mode ini, hanya nilai quantity yang dapat diubah.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/direct-mode.png') }}">
                                 <figcaption>
                                     Figure 7.2 – Direct Quantity Update Mode
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.3 Change Contract Category</span>
                             <span x-show="lang==='id'">7.3 Ganti Category Kontrak</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the contract category is changed,
                                 the system will reload detail items from TEMP.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika category kontrak diganti,
                                 sistem akan memuat ulang detail dari tabel TEMP.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 This action replaces all existing BQ detail lines.
                             </span>
                             <span x-show="lang==='id'">
                                 Tindakan ini akan mengganti seluruh detail BQ yang ada.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the selected category matches the Business Unit configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan category yang dipilih sesuai dengan konfigurasi Business Unit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/category-change.png') }}">
                                 <figcaption>
                                     Figure 7.3 – Change Contract Category Modal
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.4 Re-Import BQ (Excel)</span>
                             <span x-show="lang==='id'">7.4 Import Ulang BQ (Excel)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may re-import Excel to update BQ details.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat melakukan import ulang file Excel
                                 untuk memperbarui detail BQ.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Importing a new file will overwrite existing BQ details.
                             </span>
                             <span x-show="lang==='id'">
                                 Import file baru akan menimpa detail BQ yang sudah ada.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the file structure follows the official BQ template.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan struktur file mengikuti template resmi BQ.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/reimport.png') }}">
                                 <figcaption>
                                     Figure 7.4 – Re-Import Excel for Edit
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.5 Photo Before (Mandatory)</span>
                             <span x-show="lang==='id'">7.5 Foto Before (Wajib)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Updated BQ requires valid Photo Before documentation.
                             </span>
                             <span x-show="lang==='id'">
                                 BQ yang diperbarui wajib memiliki dokumentasi Foto Before yang valid.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Attachment wajib minimal 1 file.
                             </span>
                             <span x-show="lang==='id'">
                                 Attachment wajib minimal 1 file.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum file size: 5MB per file (JPG/PNG).
                             </span>
                             <span x-show="lang==='id'">
                                 Maksimal ukuran file: 5MB per file (JPG/PNG).
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/photo-before.png') }}">
                                 <figcaption>
                                     Figure 7.5 – Photo Before Upload Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">7.6 Submit Approval</span>
                             <span x-show="lang==='id'">7.6 Submit Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing edits, users must re-submit the BQ for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah perubahan selesai, user wajib melakukan submit ulang untuk approval.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Submission triggers the approval workflow from the first level.
                             </span>
                             <span x-show="lang==='id'">
                                 Submit ulang akan memulai ulang alur approval dari level pertama.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Approval will validate Business Unit budget alignment.
                             </span>
                             <span x-show="lang==='id'">
                                 Approval akan memvalidasi kesesuaian budget berdasarkan Business Unit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/bq/edit/submit-approval.png') }}">
                                 <figcaption>
                                     Figure 7.6 – Submit Approval After Edit
                                 </figcaption>
                             </figure>
                         </div>

                     </section>


                 </div>
             </div>

         </section>

     </div>
