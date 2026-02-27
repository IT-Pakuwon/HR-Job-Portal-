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
         <div class="manual-note manual-important">

             <span x-show="lang==='en'">
                 <strong>Important:</strong><br><br>

                 IM Budget will be automatically created when the <strong>Budget Needed</strong>
                 in the CS document exceeds the available budget.
                 The IM Budget is generated <strong>only after Approval Level 2 approves the CS
                     document</strong>.<br><br>

                 If the approver decides not to create an IM Budget,
                 the CS document cannot proceed further and will not be approved.
                 <strong>Please contact the Cost Control team at each site for further discussion
                     regarding budget availability and validation.</strong><br><br>

                 If the approver agrees to create the IM Budget,
                 the <strong>SPPBJK Created User</strong> will be notified via email
                 to continue and complete the IM Budget process.
             </span>

             <span x-show="lang==='id'">
                 <strong>Penting:</strong><br><br>

                 IM Budget akan dibuat secara otomatis apabila <strong>Budget Needed</strong>
                 pada dokumen CS melebihi budget yang tersedia.
                 IM Budget hanya akan terbentuk <strong>setelah Approval Level 2 menyetujui dokumen CS</strong>.<br><br>

                 Apabila approver tidak menyetujui pembuatan IM Budget,
                 maka dokumen CS tidak dapat dilanjutkan dan tidak akan di-approve.
                 <strong>Silakan menghubungi tim Cost Control di masing-masing site
                     untuk diskusi lebih lanjut terkait ketersediaan dan validasi budget.</strong><br><br>

                 Jika approver menyetujui pembuatan IM Budget,
                 maka <strong>User pembuat SPPBJK</strong> akan menerima notifikasi melalui email
                 untuk melanjutkan dan menyelesaikan proses IM Budget.
             </span>

         </div>

         <!-- ================= SECTION 1 CREATE / IMPORT BUDGET ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s1')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">1. Process IM Budget</span>
                         <span x-show="lang==='id'">1. Proses IM Budget</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Edit IM Budget Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Edit IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit IM Budget page allows authorized users to update
                                 budget request values generated from the approved CS document.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit IM Budget digunakan untuk memperbarui
                                 nilai permintaan anggaran yang dihasilkan dari dokumen CS yang telah disetujui.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 IM Budget detail is system-generated during CS approval
                                 and reflects the financial allocation per COA and Activity.
                             </span>
                             <span x-show="lang==='id'">
                                 Detail IM Budget dihasilkan otomatis saat approval CS
                                 dan merepresentasikan alokasi keuangan per COA dan Activity.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing IM Budget may impact financial validation
                                 and approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan IM Budget dapat mempengaruhi validasi keuangan
                                 dan alur approval.
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
                                 The header section displays document metadata including
                                 Company, Department, Perpost, and Description.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan metadata dokumen termasuk
                                 Company, Department, Perpost, dan Description.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Company, Department, and Perpost fields are locked
                                 and cannot be modified during edit.
                             </span>
                             <span x-show="lang==='id'">
                                 Field Company, Department, dan Perpost dikunci
                                 dan tidak dapat diubah saat proses edit.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/header.png') }}">
                                 <figcaption>
                                     Figure 1.1 – IM Budget Header Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 IM Budget Detail</span>
                             <span x-show="lang==='id'">1.3 Detail IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The detail table displays budget allocation per COA
                                 and Activity including calculated financial values.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel detail menampilkan alokasi anggaran per COA
                                 dan Activity beserta nilai perhitungan keuangan.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>COA (Chart of Account)</li>
                             <li>Activity ID</li>
                             <li>Activity Description</li>
                             <li>Amount Expense (System Calculated)</li>
                             <li>Budget Remain (System Calculated)</li>
                             <li>Budget Needed (System Calculated)</li>
                             <li>Budget Requested (Editable)</li>
                             <li>Note</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only the <strong>Budget Requested</strong> and <strong>Note</strong>
                                 fields are editable.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya field <strong>Budget Requested</strong> dan <strong>Note</strong>
                                 yang dapat diubah.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Amount Expense, Budget Remain, and Budget Needed
                                 are calculated automatically and cannot be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Amount Expense, Budget Remain, dan Budget Needed
                                 dihitung otomatis oleh sistem dan tidak dapat diubah.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/detail-table.png') }}">
                                 <figcaption>
                                     Figure 1.2 – IM Budget Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 Attachments</span>
                             <span x-show="lang==='id'">1.4 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may review existing attachments and upload
                                 additional supporting documents if necessary.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat meninjau lampiran yang ada dan
                                 mengunggah dokumen pendukung tambahan jika diperlukan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Uploaded files must comply with company file size
                                 and format policies.
                             </span>
                             <span x-show="lang==='id'">
                                 File yang diunggah harus sesuai dengan kebijakan
                                 ukuran dan format perusahaan.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/attachments.png') }}">
                                 <figcaption>
                                     Figure 1.3 – IM Budget Attachment Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Submit or Cancel</span>
                             <span x-show="lang==='id'">1.5 Submit atau Cancel</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing updates, users may submit the IM Budget
                                 for approval or cancel the document.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah perubahan selesai, pengguna dapat
                                 melakukan submit untuk approval atau membatalkan dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Submission will trigger budget validation
                                 based on Company and Department configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Submit akan memicu validasi budget
                                 berdasarkan konfigurasi Company dan Department.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Once submitted, editing may be restricted
                                 according to workflow rules.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, hak edit dapat dibatasi
                                 sesuai aturan workflow.
                             </span>
                         </div>

                     </section>
                 </div>
             </div>
         </section>

         <!-- ================= SECTION 2 EDIT BUDGET ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s2')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">2. IM Budget List</span>
                         <span x-show="lang==='id'">2. Daftar IM Budget</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 IM Budget List Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Daftar IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The IM Budget List page provides centralized visibility
                                 of all IM Budget documents based on user access rights.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar IM Budget menampilkan seluruh dokumen
                                 IM Budget berdasarkan hak akses pengguna.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Documents displayed are filtered according to Company
                                 and financial authorization scope.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumen yang ditampilkan difilter berdasarkan Company
                                 dan cakupan otorisasi keuangan.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Status Cards</span>
                             <span x-show="lang==='id'">2.2 Kartu Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards display the total number of IM Budget documents
                                 grouped by workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status menampilkan jumlah dokumen IM Budget
                                 berdasarkan status workflow.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>All</li>
                             <li>Hold / Revise</li>
                             <li>On Progress</li>
                             <li>Reject</li>
                             <li>Cancel</li>
                             <li>Completed</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Clicking a status card will automatically filter
                                 the IM Budget data table.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan otomatis memfilter
                                 tabel data IM Budget.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Status reflects the current financial validation
                                 and approval workflow stage.
                             </span>
                             <span x-show="lang==='id'">
                                 Status menunjukkan tahap validasi keuangan
                                 dan alur approval saat ini.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/status-cards.png') }}">
                                 <figcaption>
                                     Figure 2.1 – IM Budget Status Cards
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 IM Budget Data Table</span>
                             <span x-show="lang==='id'">2.3 Tabel Data IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The table displays IM Budget document information,
                                 including document ID, date, CS reference,
                                 company, requester, and status.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel menampilkan informasi dokumen IM Budget,
                                 termasuk ID dokumen, tanggal, referensi CS,
                                 company, peminta, dan status.
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
                             <li>CSID</li>
                             <li>SPPBJKTID</li>
                             <li>Company</li>
                             <li>User Peminta</li>
                             <li>Status</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Search, sorting, and filtering operate dynamically
                                 without requiring a page refresh.
                             </span>
                             <span x-show="lang==='id'">
                                 Fitur pencarian, pengurutan, dan filter berjalan secara dinamis
                                 tanpa perlu refresh halaman.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only authorized users may access edit
                                 or approval actions from this page.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya pengguna yang berwenang yang dapat mengakses
                                 fitur edit atau approval dari halaman ini.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/data-table.png') }}">
                                 <figcaption>
                                     Figure 2.2 – IM Budget Data Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Document Tracking</span>
                             <span x-show="lang==='id'">2.4 Tracking Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Tracking modal provides a visual timeline
                                 of document workflow progression.
                             </span>
                             <span x-show="lang==='id'">
                                 Modal Tracking menampilkan timeline visual
                                 perkembangan workflow dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The timeline shows each approval level
                                 including timestamps and status updates.
                             </span>
                             <span x-show="lang==='id'">
                                 Timeline menampilkan setiap level approval
                                 termasuk waktu dan perubahan status.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Tracking information is read-only
                                 and cannot be modified by users.
                             </span>
                             <span x-show="lang==='id'">
                                 Informasi tracking bersifat read-only
                                 dan tidak dapat diubah oleh pengguna.
                             </span>
                         </div>
                     </section>
                 </div>
             </div>
         </section>

         <!-- ================= SECTION 3 SHOW BUDGET ================= -->
         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. Show IM Budget</span>
                         <span x-show="lang==='id'">3. Lihat IM Budget</span>

                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Show IM Budget Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Halaman Show IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show IM Budget page displays complete document information,
                                 including header details, approval status, attachments,
                                 comments, and budget breakdown.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show IM Budget menampilkan informasi lengkap dokumen,
                                 termasuk detail header, status approval, lampiran,
                                 komentar, serta rincian anggaran.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This page is accessible to authorized users for review,
                                 validation, and approval actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini dapat diakses oleh pengguna yang berwenang
                                 untuk keperluan review, validasi, dan approval.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/overview.png') }}">
                                 <figcaption>
                                     Figure 3.1 – Show IM Budget Page Overview
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Approval Actions</span>
                             <span x-show="lang==='id'">3.2 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized users may approve, request revision,
                                 or reject the IM Budget document.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna yang berwenang dapat melakukan approve,
                                 revise, atau reject terhadap dokumen IM Budget.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Approve</li>
                             <li>Revise (with mandatory reason)</li>
                             <li>Reject (with mandatory reason)</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Revise and Reject actions require a written reason.
                                 The document workflow will update automatically.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Revise dan Reject wajib disertai alasan tertulis.
                                 Workflow dokumen akan diperbarui secara otomatis.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/budget/button.png') }}">
                                 <figcaption>
                                     Figure 3.2 – Approval Action Buttons
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 IM Budget Header Information</span>
                             <span x-show="lang==='id'">3.3 Informasi Header IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays document identity,
                                 status badge, company, department, related CS,
                                 SPPBJKT reference, and purpose.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan identitas dokumen,
                                 badge status, company, department, referensi CS,
                                 SPPBJKT, serta tujuan pengajuan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Linked CS and SPPBJKT documents can be opened
                                 in a new tab for cross-reference validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumen CS dan SPPBJKT dapat dibuka
                                 di tab baru untuk keperluan validasi silang.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Status badge reflects real-time workflow position.
                             </span>
                             <span x-show="lang==='id'">
                                 Badge status menunjukkan posisi workflow secara real-time.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/header-info.png') }}">
                                 <figcaption>
                                     Figure 3.3 – IM Budget Header Information
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Document Tabs</span>
                             <span x-show="lang==='id'">3.4 Tab Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right-side panel contains three functional tabs:
                                 Attachment, Approval Details, and Comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Panel sisi kanan memiliki tiga tab utama:
                                 Attachment, Approval Details, dan Comments.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Attachment</strong> – View and upload supporting documents</li>
                             <li><strong>Approval Details</strong> – View approval hierarchy and status</li>
                             <li><strong>Comments</strong> – Internal discussion and clarification</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Uploading attachments may be restricted
                                 based on user role and workflow stage.
                             </span>
                             <span x-show="lang==='id'">
                                 Upload lampiran dapat dibatasi
                                 berdasarkan role dan tahap workflow.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/approval-actions.png') }}">
                                 <figcaption>
                                     Figure 3.4 – Attachment, Approval & Comments Tabs
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 IM Budget Detail</span>
                             <span x-show="lang==='id'">3.5 Detail IM Budget</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The IM Budget Detail table shows the complete financial
                                 breakdown including COA, activity, budget remain,
                                 budget needed, and budget requested.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel Detail IM Budget menampilkan rincian keuangan lengkap
                                 termasuk COA, activity, budget remain,
                                 budget needed, dan budget requested.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Budget validation ensures that requested amounts
                                 do not exceed the remaining available budget.
                             </span>
                             <span x-show="lang==='id'">
                                 Validasi anggaran memastikan bahwa jumlah yang diajukan
                                 tidak melebihi sisa anggaran yang tersedia.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Financial approval is based on COA hierarchy
                                 and budget control rules.
                             </span>
                             <span x-show="lang==='id'">
                                 Approval keuangan mengikuti hierarki COA
                                 dan aturan kontrol anggaran.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/imbudget/detail-table-.png') }}">
                                 <figcaption>
                                     Figure 3.5 – IM Budget Detail Table
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                 </div>
             </div>

         </section>

     </div>
