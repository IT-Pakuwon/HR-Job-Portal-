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
                         <span x-show="lang==='en'">1. List WO Jobs</span>
                         <span x-show="lang==='id'">1. Daftar WO Jobs</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- ================= SECTION S1 ================= -->


                     <!-- S1.1 Overview -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The WO Jobs List page displays all Work Orders that have entered
                                 the job execution phase.
                                 This page is mainly used by PIC WO and operational departments
                                 to monitor work progress.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman WO Jobs List menampilkan seluruh Work Order
                                 yang telah masuk ke tahap pelaksanaan pekerjaan.
                                 Halaman ini digunakan oleh PIC WO dan departemen operasional
                                 untuk memantau progres pekerjaan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Only WO that have passed approval stage will appear in this list.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya WO yang telah melewati tahap approval
                                 yang akan muncul dalam daftar ini.
                             </span>
                         </div>

                     </section>

                     <!-- S1.2 Status Cards -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Job Status Cards</span>
                             <span x-show="lang==='id'">1.2 Kartu Status Pekerjaan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards summarize WO based on job execution status.
                                 Users may filter the table by clicking one of the cards.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status menampilkan ringkasan WO berdasarkan
                                 status pelaksanaan pekerjaan.
                                 Pengguna dapat memfilter tabel dengan memilih kartu yang tersedia.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>WO List</strong> – All WO in job phase</li>
                             <li><strong>On Progress</strong> – Jobs currently being executed</li>
                             <li><strong>Cancel</strong> – Jobs cancelled during execution</li>
                             <li><strong>Completed</strong> – Jobs marked as completed</li>
                             <li><strong>All</strong> – Display all statuses</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Clicking a status card will dynamically filter the table below.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan memfilter tabel di bawahnya secara otomatis.
                             </span>
                         </div>

                     </section>

                     <!-- S1.3 WO Jobs Table -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 WO Jobs Data Table</span>
                             <span x-show="lang==='id'">1.3 Tabel Data WO Jobs</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The WO Jobs table provides detailed information for each job record.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel WO Jobs menampilkan informasi detail untuk setiap pekerjaan.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Document ID</li>
                             <li>Date</li>
                             <li>Company</li>
                             <li>Department</li>
                             <li>Work Type</li>
                             <li>WO Request</li>
                             <li>Description</li>
                             <li>Status Pekerjaan</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The table supports searching, sorting, and pagination
                                 for easier monitoring.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel mendukung fitur pencarian, pengurutan,
                                 dan pagination untuk memudahkan monitoring.
                             </span>
                         </div>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Status Pekerjaan reflects operational progress,
                                 not approval workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Status Pekerjaan mencerminkan progres operasional,
                                 bukan status workflow approval.
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
                         <span x-show="lang==='en'">2. Process WO</span>
                         <span x-show="lang==='id'">2. Proses WO</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             All created Work Orders (WO) will automatically appear in the WO List.
                             This allows users to proceed with the creation of
                             <strong>SPPBJKT</strong> or <strong>SPB</strong> when required.
                         </span>
                         <span x-show="lang==='id'">
                             Seluruh Work Order (WO) yang telah dibuat akan otomatis muncul
                             pada WO List.
                             Hal ini memungkinkan user untuk melanjutkan proses pembuatan
                             <strong>SPPBJKT</strong> atau <strong>SPB</strong> apabila diperlukan.
                         </span>
                     </div>

                     <!-- 2.1 Approval Action Buttons -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Approval Actions</span>
                             <span x-show="lang==='id'">2.1 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users with approval authority may take action using
                                 the available buttons at the top of the page.
                             </span>
                             <span x-show="lang==='id'">
                                 User dengan hak approval dapat melakukan tindakan
                                 melalui tombol yang tersedia di bagian atas halaman.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> – Approve the Work Order</li>
                             <li><strong>Revise</strong> – Request revision with reason</li>
                             <li><strong>Reject</strong> – Reject the Work Order with explanation</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Revise and Reject actions require a mandatory comment before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Revise dan Reject wajib disertai alasan sebelum dikirim.
                             </span>
                         </div>

                     </section>

                     <!-- 2.2 WO Information Panel -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Work Order Information</span>
                             <span x-show="lang==='id'">2.2 Informasi Work Order</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The left panel displays detailed information including:
                                 Company, Department, WO Type, Work Type, Budget usage,
                                 PIC Request, Location, and Purpose.
                             </span>
                             <span x-show="lang==='id'">
                                 Panel sebelah kiri menampilkan detail Work Order seperti:
                                 Company, Department, WO Type, Jenis Pekerjaan, Budget,
                                 PIC Request, Location, dan Purpose.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 All information in this section is read-only and cannot be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Seluruh informasi pada bagian ini bersifat read-only dan tidak dapat diubah.
                             </span>
                         </div>

                     </section>

                     <!-- 2.3 Attachment, Approval & Comments Tabs -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Supporting Tabs</span>
                             <span x-show="lang==='id'">2.3 Tab Pendukung</span>
                         </h3>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 When creating <strong>SPPBJKT</strong> or <strong>SPB</strong>,
                                 if the <strong>WO ID</strong> field is filled,
                                 the WO Description and all related Attachments
                                 will automatically be carried over to the new document.
                             </span>
                             <span x-show="lang==='id'">
                                 Saat membuat <strong>SPPBJKT</strong> atau <strong>SPB</strong>,
                                 apabila field <strong>WO ID</strong> diisi,
                                 maka Deskripsi WO dan seluruh Attachment terkait
                                 akan otomatis terbawa ke dokumen yang dibuat.
                             </span>
                         </div>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Attachment</strong> – View and upload supporting documents</li>
                             <li><strong>Approval Details</strong> – View approval history and status</li>
                             <li><strong>Comments</strong> – View and add discussion notes</li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Attachments are limited to 10 files per upload.
                                 PDF and image formats are recommended.
                             </span>
                             <span x-show="lang==='id'">
                                 Upload attachment dibatasi maksimal 10 file per upload.
                                 Format PDF dan gambar direkomendasikan.
                             </span>
                         </div>

                     </section>

                     <!-- 2.4 Process Work Order (Editable Mode) -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Process Work Order</span>
                             <span x-show="lang==='id'">2.4 Proses Work Order</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 This section is visible only to authorized users
                                 (PIC WO or assigned department).
                                 Users may update job execution details here.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian ini hanya tampil untuk user yang berwenang
                                 (PIC WO atau departemen terkait).
                                 User dapat memperbarui detail pelaksanaan pekerjaan di sini.
                             </span>
                         </p>

                         <h4 class="font-semibold text-gray-800 dark:text-white">
                             <span x-show="lang==='en'">Editable Fields:</span>
                             <span x-show="lang==='id'">Field yang Dapat Diubah:</span>
                         </h4>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Status Pekerjaan</strong> (On Progress / Cancel / Completed)</li>
                             <li><strong>SPB / SPPBJKT</strong> flag (checkbox if required)</li>
                             <li><strong>Department</strong> (Processing department)</li>
                             <li><strong>Comment</strong> (Operational notes)</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Status Pekerjaan represents operational progress,
                                 not approval workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Status Pekerjaan mencerminkan progres operasional,
                                 bukan status approval workflow.
                             </span>
                         </div>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once marked as Completed or Cancelled, the job is considered finished or terminated
                                 and cannot be reverted without authorization.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika status sudah Completed atau Batal,
                                 pekerjaan dianggap selesai atau dihentikan
                                 dan tidak dapat diubah kembali tanpa otorisasi.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>
         </section>


     </div>
