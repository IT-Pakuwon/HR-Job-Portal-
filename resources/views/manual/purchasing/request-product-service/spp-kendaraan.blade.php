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
                         <span x-show="lang==='en'">1. Create SPPK</span>
                         <span x-show="lang==='id'">2. Membuat SPPK</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- ===================================================== -->
                     <!-- 1.1 Overview -->
                     <!-- ===================================================== -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create SPPK page is used to submit a new Spare Part & Service
                                 Purchase Request (SPPK). Users must complete header information,
                                 vehicle data (if applicable), item details, and attachments
                                 before submitting for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create SPPK digunakan untuk mengajukan permintaan
                                 pembelian Spare Part & Jasa (SPPK). User wajib melengkapi
                                 informasi header, data kendaraan (jika ada), detail item,
                                 dan lampiran sebelum mengajukan approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 All fields marked with <strong>required (req)</strong> must be completed before
                                 submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Semua field yang ditandai <strong>required (req)</strong> wajib diisi sebelum submit.
                             </span>
                         </div>


                     </section>

                     <!-- ===================================================== -->
                     <!-- 1.2 Header Information -->
                     <!-- ===================================================== -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Header Information</span>
                             <span x-show="lang==='id'">1.2 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must complete the main header fields before adding detail items.
                             </span>
                             <span x-show="lang==='id'">
                                 User harus melengkapi field header utama sebelum menambahkan detail item.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Company</strong></li>
                             <li><strong>Business Unit</strong></li>
                             <li><strong>Department</strong></li>
                             <li><strong>Request Type</strong> (selected via lookup modal)</li>
                             <li><strong>Perpost (Year)</strong></li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Request Type selection determines the workflow approval routing.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan Request Type menentukan alur workflow approval.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/create-header.png') }}">
                                 <figcaption>
                                     Figure 1.2 – Header Information
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- ===================================================== -->
                     <!-- 1.3 Vehicle Information -->
                     <!-- ===================================================== -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Vehicle Information</span>
                             <span x-show="lang==='id'">1.3 Informasi Kendaraan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the request is related to a vehicle, select the vehicle number (No. Polisi)
                                 using the lookup button. Vehicle owner and name will be filled automatically.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika permintaan berkaitan dengan kendaraan, pilih No. Polisi
                                 menggunakan tombol lookup. Data pemilik dan nama kendaraan akan terisi otomatis.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 KM (kilometer) must reflect the latest vehicle condition.
                             </span>
                             <span x-show="lang==='id'">
                                 KM harus diisi sesuai kondisi kendaraan terakhir.
                             </span>
                         </div>
                     </section>

                     <!-- ===================================================== -->
                     <!-- 1.4 Emergency & Description -->
                     <!-- ===================================================== -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 Emergency & Description</span>
                             <span x-show="lang==='id'">1.4 Emergency & Deskripsi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may mark the SPPK as Emergency if the request requires urgent handling.
                                 A clear description must be provided.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat menandai SPPK sebagai Emergency jika membutuhkan penanganan segera.
                                 Deskripsi yang jelas wajib diisi.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Emergency requests may follow a different or accelerated approval flow.
                             </span>
                             <span x-show="lang==='id'">
                                 Permintaan Emergency dapat mengikuti alur approval yang berbeda atau dipercepat.
                             </span>
                         </div>


                     </section>

                     <!-- ===================================================== -->
                     <!-- 1.5 SPPK Detail Items -->
                     <!-- ===================================================== -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 SPPK Detail Items</span>
                             <span x-show="lang==='id'">1.5 Detail Item SPPK</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must add one or more detail rows by clicking “Add Row”.
                                 Each row requires product, quantity, UoM, location, and COA.
                             </span>
                             <span x-show="lang==='id'">
                                 User harus menambahkan minimal satu baris detail dengan klik “Add Row”.
                                 Setiap baris membutuhkan produk, qty, UoM, location, dan COA.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA selection will validate available budget before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA akan memvalidasi ketersediaan budget sebelum submit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/create-detail.png') }}">
                                 <figcaption>
                                     Figure 1.5 – SPPK Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- ===================================================== -->
                     <!-- 1.6 Attachments & Submission -->
                     <!-- ===================================================== -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.6 Attachments & Submission</span>
                             <span x-show="lang==='id'">1.6 Lampiran & Submit</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may upload supporting documents such as quotations,
                                 photos, or technical references before submitting.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengunggah dokumen pendukung seperti quotation,
                                 foto, atau referensi teknis sebelum submit.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Click “Submit Approval” to send the SPPK into workflow approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik “Submit Approval” untuk mengirim SPPK ke proses approval.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once submitted, the document status will change to Waiting Approval
                                 and editing may be restricted depending on workflow rules.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, status dokumen akan berubah menjadi Waiting Approval
                                 dan hak edit dapat dibatasi sesuai aturan workflow.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/create-attachement.png') }}">
                                 <figcaption>
                                     Figure 1.6 – Attachments & Submit
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
                         <span x-show="lang==='en'">2. Edit SPPK</span>
                         <span x-show="lang==='id'">2. Edit SPPK</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition x-cloak class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit SPPK page allows authorized users to modify an existing SPPK
                                 before the approval process is completed.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit SPPK memungkinkan user yang berwenang untuk melakukan
                                 perubahan pada SPPK sebelum proses approval selesai.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing access depends on workflow status and user authorization level.
                             </span>
                             <span x-show="lang==='id'">
                                 Hak edit tergantung pada status workflow dan level otorisasi user.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If the document is in <strong>Waiting Approval (first level)</strong>,
                                 the SPPK may still be edited depending on company policy.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika dokumen berada pada status <strong>Waiting Approval (level pertama)</strong>,
                                 SPPK masih dapat diedit sesuai kebijakan perusahaan.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Header Information</span>
                             <span x-show="lang==='id'">2.2 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update Company, Business Unit, Department,
                                 Request Type, and Budget Year (Perpost) before re-submitting.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengubah Company, Business Unit, Department,
                                 Request Type, dan Tahun Budget (Perpost) sebelum submit ulang.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Business Unit selection determines budget locking and COA validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan Business Unit menentukan penguncian budget dan validasi COA.
                             </span>
                         </div>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/edit-header.png') }}">
                                 <figcaption>
                                     Figure 2.1 – Edit Header Fields
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Vehicle & Emergency</span>
                             <span x-show="lang==='id'">2.3 Kendaraan & Emergency</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Vehicle data such as No. Polisi and KM may be updated.
                                 Emergency flag can also be modified if necessary.
                             </span>
                             <span x-show="lang==='id'">
                                 Data kendaraan seperti No. Polisi dan KM dapat diperbarui.
                                 Status Emergency juga dapat diubah jika diperlukan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Emergency requests may trigger accelerated or alternative approval routing.
                             </span>
                             <span x-show="lang==='id'">
                                 Permintaan Emergency dapat memicu alur approval yang dipercepat atau berbeda.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Detail Item Editing</span>
                             <span x-show="lang==='id'">2.4 Edit Detail Item</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may modify product, quantity, UoM, location,
                                 sub-location, COA, and notes.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengubah produk, quantity, UoM, lokasi,
                                 sub-lokasi, COA, dan catatan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Any modification to detail items may trigger re-validation
                                 of available budget.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada detail item dapat memicu validasi ulang
                                 terhadap ketersediaan budget.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing SPPK detail will reset the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan pada detail SPPK akan mereset alur approval.
                             </span>
                         </div>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/edit-detail.png') }}">
                                 <figcaption>
                                     Figure 2.2 – Edit Detail Item
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.5 Attachments & Re-Submission</span>
                             <span x-show="lang==='id'">2.5 Lampiran & Submit Ulang</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may add or remove attachments before re-submitting
                                 the document for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat menambahkan atau menghapus lampiran
                                 sebelum mengirim ulang dokumen untuk approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Click “Submit Approval” to restart the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik “Submit Approval” untuk memulai kembali alur approval.
                             </span>
                         </div>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/edit-attachment.png') }}">
                                 <figcaption>
                                     Figure 2.3 – Edit Attachment
                                 </figcaption>
                             </figure>
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
                         <span x-show="lang==='en'">3. List SPPK</span>
                         <span x-show="lang==='id'">3. Daftar SPPK</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The List SPPK page displays all submitted SPPK documents.
                                 Users can monitor status, filter documents, create new requests,
                                 and access tracking details.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman List SPPK menampilkan seluruh dokumen SPPK yang telah dibuat.
                                 User dapat memantau status, melakukan filter, membuat permintaan baru,
                                 dan melihat detail tracking dokumen.
                             </span>
                         </p>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/list-sppk.png') }}">
                                 <figcaption>
                                     Figure 3.1 – List SPPK Page
                                 </figcaption>
                             </figure>
                         </div>
                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status Summary</span>
                             <span x-show="lang==='id'">3.2 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top of the page, summary cards display the total number
                                 of documents based on their current workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Pada bagian atas halaman terdapat kartu ringkasan
                                 yang menampilkan jumlah dokumen berdasarkan status workflow.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>All</strong> – <span x-show="lang==='en'">All documents</span><span
                                     x-show="lang==='id'">Semua dokumen</span></li>
                             <li><strong>On Progress</strong> – <span x-show="lang==='en'">Documents currently in
                                     approval process</span><span x-show="lang==='id'">Dokumen dalam proses
                                     approval</span></li>
                             <li><strong>Reject</strong> – <span x-show="lang==='en'">Rejected documents</span><span
                                     x-show="lang==='id'">Dokumen yang ditolak</span></li>
                             <li><strong>Revise / Draft</strong> – <span x-show="lang==='en'">Documents pending
                                     revision</span><span x-show="lang==='id'">Dokumen dalam status draft/revisi</span>
                             </li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Fully approved
                                     documents</span><span x-show="lang==='id'">Dokumen yang sudah selesai
                                     approval</span></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Clicking a status card will filter the table below.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan memfilter tabel di bawahnya.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status Summary</span>
                             <span x-show="lang==='id'">3.2 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top of the page, summary cards display the total number
                                 of documents based on their current workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Pada bagian atas halaman terdapat kartu ringkasan
                                 yang menampilkan jumlah dokumen berdasarkan status workflow.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>All</strong> – <span x-show="lang==='en'">All documents</span><span
                                     x-show="lang==='id'">Semua dokumen</span></li>
                             <li><strong>On Progress</strong> – <span x-show="lang==='en'">Documents currently in
                                     approval process</span><span x-show="lang==='id'">Dokumen dalam proses
                                     approval</span></li>
                             <li><strong>Reject</strong> – <span x-show="lang==='en'">Rejected documents</span><span
                                     x-show="lang==='id'">Dokumen yang ditolak</span></li>
                             <li><strong>Revise / Draft</strong> – <span x-show="lang==='en'">Documents pending
                                     revision</span><span x-show="lang==='id'">Dokumen dalam status draft/revisi</span>
                             </li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Fully approved
                                     documents</span><span x-show="lang==='id'">Dokumen yang sudah selesai
                                     approval</span></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Clicking a status card will filter the table below.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan memfilter tabel di bawahnya.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Create New SPPK</span>
                             <span x-show="lang==='id'">3.3 Membuat SPPK Baru</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click the <strong>Create</strong> button to open the Create SPPK page.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik tombol <strong>Create</strong> untuk membuka halaman Create SPPK.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Access to create SPPK depends on user role and authorization.
                             </span>
                             <span x-show="lang==='id'">
                                 Akses pembuatan SPPK tergantung pada role dan otorisasi user.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 Tracking Detail</span>
                             <span x-show="lang==='id'">3.5 Detail Tracking</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users can open the Tracking Modal to view the full lifecycle
                                 of the document.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat membuka Tracking Modal untuk melihat
                                 seluruh siklus hidup dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The tracking view contains multiple tabs:
                             </span>
                             <span x-show="lang==='id'">
                                 Tampilan tracking memiliki beberapa tab:
                             </span>
                         </div>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>SPPJ</strong> – <span x-show="lang==='en'">Original request
                                     details</span><span x-show="lang==='id'">Detail permintaan awal</span></li>
                             <li><strong>CS</strong> – <span x-show="lang==='en'">Comparative Sheet
                                     information</span><span x-show="lang==='id'">Informasi Comparative Sheet</span>
                             </li>
                             <li><strong>SPK</strong> – <span x-show="lang==='en'">Purchase Order / Work
                                     Order</span><span x-show="lang==='id'">Surat Perintah Kerja / PO</span></li>
                             <li><strong>BAST</strong> – <span x-show="lang==='en'">Handover document</span><span
                                     x-show="lang==='id'">Berita Acara Serah Terima</span></li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Tracking data is view-only and cannot be edited.
                             </span>
                             <span x-show="lang==='id'">
                                 Data tracking bersifat view-only dan tidak dapat diedit.
                             </span>
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

                     <span x-show="lang==='en'">4. Show SPPK</span>
                     <span x-show="lang==='id'">4. Tampilkan SPPK</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Overview</span>
                             <span x-show="lang==='id'">4.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show SPPK page displays complete document information,
                                 including header data, detail items, attachments,
                                 approval status, and workflow actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show SPPK menampilkan informasi lengkap dokumen,
                                 termasuk data header, detail item, lampiran,
                                 status approval, dan aksi workflow.
                             </span>
                         </p>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/sppk/show.png') }}">
                                 <figcaption>
                                     Figure 4.1 – SPPK Detail View
                                 </figcaption>
                             </figure>
                         </div>
                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Overview</span>
                             <span x-show="lang==='id'">4.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show SPPK page displays complete document information,
                                 including header data, detail items, attachments,
                                 approval status, and workflow actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show SPPK menampilkan informasi lengkap dokumen,
                                 termasuk data header, detail item, lampiran,
                                 status approval, dan aksi workflow.
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
                                 Authorized approvers can perform workflow actions:
                                 Approve, Revise, or Reject.
                             </span>
                             <span x-show="lang==='id'">
                                 Approver yang berwenang dapat melakukan aksi workflow:
                                 Approve, Revise, atau Reject.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> –
                                 <span x-show="lang==='en'">Move document to next approval level</span>
                                 <span x-show="lang==='id'">Melanjutkan dokumen ke level approval berikutnya</span>
                             </li>
                             <li><strong>Revise</strong> –
                                 <span x-show="lang==='en'">Return document for correction</span>
                                 <span x-show="lang==='id'">Mengembalikan dokumen untuk perbaikan</span>
                             </li>
                             <li><strong>Reject</strong> –
                                 <span x-show="lang==='en'">Fully reject the document</span>
                                 <span x-show="lang==='id'">Menolak dokumen secara keseluruhan</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Approve, Revise, and Reject actions require a mandatory reason.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Approve, Revise, dan Reject memerlukan alasan (reason) wajib.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 SPPK Information</span>
                             <span x-show="lang==='id'">4.3 Informasi SPPK</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 This section displays document identity and key information
                                 such as Company, Department, Request Type, Vehicle,
                                 KM, and Purpose.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian ini menampilkan identitas dokumen dan informasi utama
                                 seperti Company, Department, Request Type, Kendaraan,
                                 KM, dan Purpose.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The status badge indicates current workflow position.
                             </span>
                             <span x-show="lang==='id'">
                                 Badge status menunjukkan posisi workflow saat ini.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Attachment & Approval Tabs</span>
                             <span x-show="lang==='id'">4.4 Tab Lampiran & Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right panel contains three tabs:
                                 Attachment, Approval Details, and Comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kanan memiliki tiga tab:
                                 Attachment, Approval Details, dan Comments.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Attachment</strong> –
                                 <span x-show="lang==='en'">View and upload supporting files</span>
                                 <span x-show="lang==='id'">Melihat dan mengunggah file pendukung</span>
                             </li>
                             <li><strong>Approval Details</strong> –
                                 <span x-show="lang==='en'">Display approval level history</span>
                                 <span x-show="lang==='id'">Menampilkan riwayat level approval</span>
                             </li>
                             <li><strong>Comments</strong> –
                                 <span x-show="lang==='en'">Workflow communication between users</span>
                                 <span x-show="lang==='id'">Komunikasi antar user dalam workflow</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Attachment upload may be restricted based on status and user role.
                             </span>
                             <span x-show="lang==='id'">
                                 Upload lampiran dapat dibatasi berdasarkan status dan role user.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 SPPK Detail Items</span>
                             <span x-show="lang==='id'">4.5 Detail Item SPPK</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The detail table displays requested items,
                                 quantities, location, budget allocation,
                                 and ordering progress.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel detail menampilkan item yang diminta,
                                 quantity, lokasi, alokasi budget,
                                 serta progres pemesanan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Budget Department, Account, and Business Unit
                                 determine financial validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Budget Department, Account, dan Business Unit
                                 menentukan validasi keuangan.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.6 Edit COA</span>
                             <span x-show="lang==='id'">4.6 Edit COA</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users with special authorization may edit
                                 COA allocation before final processing.
                             </span>
                             <span x-show="lang==='id'">
                                 User dengan otorisasi khusus dapat mengubah
                                 alokasi COA sebelum proses final.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 COA selection is filtered by Company,
                                 Business Unit, Department Finance, and Budget Year.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemilihan COA difilter berdasarkan Company,
                                 Business Unit, Department Finance, dan Tahun Budget.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Changing COA may trigger budget re-validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan COA dapat memicu validasi ulang budget.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.7 Reject & Revise Modal</span>
                             <span x-show="lang==='id'">4.7 Modal Reject & Revise</span>
                         </h3>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Reject and Revise actions require a written reason
                                 before confirmation.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Reject dan Revise mewajibkan pengisian alasan
                                 sebelum konfirmasi.
                             </span>
                         </div>

                     </section>
                 </div>
             </div>

         </section>



     </div>
