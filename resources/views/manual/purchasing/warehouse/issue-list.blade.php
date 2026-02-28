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
                         <span x-show="lang==='en'">1. Edit Issue</span>
                         <span x-show="lang==='id'">1. Edit Issue</span>
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
                                 The Edit Issue page allows users to modify an existing Issue document.
                                 Editable fields include issue quantities, detail notes, site selection,
                                 and attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit Issue digunakan untuk mengubah dokumen Issue yang sudah dibuat.
                                 Field yang dapat diubah meliputi quantity issue, catatan per detail,
                                 pemilihan site, dan lampiran.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/issue/edit/overview.png') }}">
                                 <figcaption>
                                     Figure 1.1 – Edit Issue Page
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 1.2 Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Issue Information</span>
                             <span x-show="lang==='id'">1.2 Informasi Issue</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section displays basic information of the Issue document.
                                 Most fields are read-only.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan informasi dasar dokumen Issue.
                                 Sebagian besar field bersifat read-only.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Issue ID</strong></li>
                             <li><strong>Issue Date</strong></li>
                             <li><strong>SPB ID</strong></li>
                             <li><strong>Company</strong></li>
                             <li><strong>Issue Note</strong> (Editable)</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Issue Note can be updated to provide additional explanation
                                 before resubmitting for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Issue Note dapat diperbarui untuk memberikan penjelasan tambahan
                                 sebelum diajukan kembali untuk approval.
                             </span>
                         </div>
                     </section>

                     <!-- 1.3 Issue Detail -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Issue Detail</span>
                             <span x-show="lang==='id'">1.3 Detail Issue</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may adjust issue quantities per line and update detail notes.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat menyesuaikan quantity issue per baris dan memperbarui catatan detail.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Line</strong> – <span x-show="lang==='en'">Sequential item number</span><span
                                     x-show="lang==='id'">Nomor urut item</span></li>
                             <li><strong>Inventory ID</strong></li>
                             <li><strong>Description</strong></li>
                             <li><strong>Qty (Current)</strong> – <span x-show="lang==='en'">Previously issued
                                     quantity</span><span x-show="lang==='id'">Quantity yang sudah di-issue</span></li>
                             <li><strong>Qty Edit</strong> – <span x-show="lang==='en'">New quantity value</span><span
                                     x-show="lang==='id'">Quantity baru yang diinput</span></li>
                             <li><strong>Detail Note</strong> – <span x-show="lang==='en'">Optional per-line
                                     note</span><span x-show="lang==='id'">Catatan per baris (opsional)</span></li>
                             <li><strong>Site</strong> – <span x-show="lang==='en'">Site destination</span><span
                                     x-show="lang==='id'">Lokasi site tujuan</span></li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Qty Edit should not exceed available stock.
                                 The system will validate stock availability during submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Qty Edit tidak boleh melebihi stok yang tersedia.
                                 Sistem akan melakukan validasi saat submit.
                             </span>
                         </div>
                     </section>

                     <!-- 1.4 Attachments -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 Attachments</span>
                             <span x-show="lang==='id'">1.4 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Existing attachments are displayed in the list.
                                 Users may remove attachments or upload new supporting files.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran yang sudah ada akan ditampilkan pada daftar.
                                 User dapat menghapus atau menambahkan lampiran baru.
                             </span>
                         </p>
                     </section>

                     <!-- 1.5 Submit Approval -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Submit Approval</span>
                             <span x-show="lang==='id'">1.5 Submit Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After editing, click <strong>Submit Approval</strong>
                                 to continue the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah melakukan perubahan, klik <strong>Submit Approval</strong>
                                 untuk melanjutkan proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once approved, the issue transaction will reduce inventory stock
                                 according to the final approved quantities.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disetujui, transaksi issue akan mengurangi stok inventory
                                 sesuai quantity final yang di-approve.
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
                         <span x-show="lang==='en'">2. List Issue </span>
                         <span x-show="lang==='id'">2. Daftar Issue</span>
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
                                 The Issue List page displays all Issue documents
                                 based on workflow status and user access.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar Issue menampilkan seluruh dokumen Issue
                                 berdasarkan status workflow dan hak akses user.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/issue/list/overview.png') }}">
                                 <figcaption>
                                     Figure 2.1 – Issue List Page
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 2.2 Status Cards -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Status Filter Cards</span>
                             <span x-show="lang==='id'">2.2 Kartu Filter Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards provide a quick summary and filtering mechanism.
                                 Clicking a card will filter the Issue table accordingly.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status menyediakan ringkasan dan mekanisme filter cepat.
                                 Klik kartu untuk memfilter tabel Issue sesuai status.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Return Issue</strong> – <span x-show="lang==='en'">Issues related to return
                                     transactions</span><span x-show="lang==='id'">Issue terkait transaksi
                                     return</span></li>
                             <li><strong>All</strong> – <span x-show="lang==='en'">Display all Issue
                                     documents</span><span x-show="lang==='id'">Menampilkan seluruh dokumen
                                     Issue</span></li>
                             <li><strong>On Progress</strong> – <span x-show="lang==='en'">Documents currently in
                                     approval process</span><span x-show="lang==='id'">Dokumen dalam proses
                                     approval</span></li>
                             <li><strong>Revise</strong> – <span x-show="lang==='en'">Documents returned for
                                     revision</span><span x-show="lang==='id'">Dokumen yang perlu direvisi</span></li>
                             <li><strong>Rejected</strong> – <span x-show="lang==='en'">Rejected Issue
                                     documents</span><span x-show="lang==='id'">Dokumen Issue yang ditolak</span></li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Fully approved and finalized
                                     documents</span><span x-show="lang==='id'">Dokumen yang sudah selesai dan
                                     disetujui</span></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The number displayed on each card represents the total documents
                                 within that specific status.
                             </span>
                             <span x-show="lang==='id'">
                                 Angka pada setiap kartu menunjukkan total dokumen
                                 dalam status tersebut.
                             </span>
                         </div>
                     </section>

                     <!-- 2.3 Issue Table -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Issue Table</span>
                             <span x-show="lang==='id'">2.3 Tabel Issue</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Issue table displays detailed information for each document.
                                 The table supports sorting, searching, and pagination.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel Issue menampilkan informasi detail setiap dokumen.
                                 Tabel mendukung fitur sorting, pencarian, dan pagination.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only authorized users can open or modify specific Issue documents
                                 depending on their role and approval level.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya user yang berwenang yang dapat membuka atau mengubah
                                 dokumen Issue tertentu sesuai role dan level approval.
                             </span>
                         </div>
                     </section>

                     <!-- 2.4 Workflow Behavior -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Workflow Behavior</span>
                             <span x-show="lang==='id'">2.4 Perilaku Workflow</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <span x-show="lang==='en'">
                                     Clicking a row opens the Issue Detail page.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Klik baris akan membuka halaman Detail Issue.
                                 </span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">
                                     Status changes automatically after approval actions.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Status akan berubah otomatis setelah aksi approval.
                                 </span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">
                                     Completed Issues reduce inventory stock permanently.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Issue yang Completed akan mengurangi stok inventory secara permanen.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once an Issue reaches Completed status,
                                 inventory stock is officially deducted from the selected site.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah Issue berstatus Completed,
                                 stok inventory akan resmi dikurangi dari site yang dipilih.
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
                         <span x-show="lang==='en'">3. Show Issue</span>
                         <span x-show="lang==='id'">3. Lihat Issue</span>

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
                                 The Show Issue page displays complete information about a specific Issue document,
                                 including header data, approval status, attachments, comments, and item details.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show Issue menampilkan informasi lengkap mengenai satu dokumen Issue,
                                 termasuk data header, status approval, lampiran, komentar, dan detail item.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This page is read-only. Users cannot modify data unless they have approval authority.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini bersifat read-only. User tidak dapat mengubah data kecuali memiliki
                                 otorisasi approval.
                             </span>
                         </div>
                     </section>

                     <!-- 3.2 Action Buttons -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Approval Actions</span>
                             <span x-show="lang==='id'">3.2 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the user has approval authority, the following action buttons will appear:
                             </span>
                             <span x-show="lang==='id'">
                                 Jika user memiliki hak approval, maka tombol berikut akan muncul:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> –
                                 <span x-show="lang==='en'">Approve the Issue document.</span>
                                 <span x-show="lang==='id'">Menyetujui dokumen Issue.</span>
                             </li>
                             <li><strong>Revise</strong> –
                                 <span x-show="lang==='en'">Send back to requester for revision.</span>
                                 <span x-show="lang==='id'">Mengembalikan ke peminta untuk revisi.</span>
                             </li>
                             <li><strong>Reject</strong> –
                                 <span x-show="lang==='en'">Reject the Issue with mandatory reason.</span>
                                 <span x-show="lang==='id'">Menolak dokumen Issue dengan alasan wajib.</span>
                             </li>
                             <li><strong>Print PDF</strong> –
                                 <span x-show="lang==='en'">Generate printable PDF version.</span>
                                 <span x-show="lang==='id'">Mencetak versi PDF dokumen.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Reject and Revise actions require a mandatory reason before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Reject dan Revise wajib mengisi alasan sebelum dikirim.
                             </span>
                         </div>
                     </section>

                     <!-- 3.3 Issue Information Card -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Issue Information</span>
                             <span x-show="lang==='id'">3.3 Informasi Issue</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The left panel displays Issue header information.
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kiri menampilkan informasi header Issue.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Issue ID</strong></li>
                             <li><strong>Status Badge</strong> (Pending / Approved / Rejected / Completed / Canceled)
                             </li>
                             <li><strong>Issue Date</strong></li>
                             <li><strong>Type</strong> (Issue / Return Issue)</li>
                             <li><strong>SPB ID</strong> (Clickable if available)</li>
                             <li><strong>Company</strong></li>
                             <li><strong>Department</strong></li>
                             <li><strong>Requester</strong></li>
                             <li><strong>Issue Note</strong> (if provided)</li>
                         </ul>
                     </section>

                     <!-- 3.4 Tabs Section -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Detail Tabs</span>
                             <span x-show="lang==='id'">3.4 Tab Detail</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right panel contains three main tabs:
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kanan memiliki tiga tab utama:
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
                                 <span x-show="lang==='en'">Displays approval level, approver name, date, and
                                     status.</span>
                                 <span x-show="lang==='id'">Menampilkan level approval, nama approver, tanggal, dan
                                     status.</span>
                             </li>
                             <li>
                                 <strong>Comments</strong> –
                                 <span x-show="lang==='en'">Internal discussion between users and approvers.</span>
                                 <span x-show="lang==='id'">Diskusi internal antara user dan approver.</span>
                             </li>
                         </ul>
                     </section>

                     <!-- 3.5 Issue Detail Table -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 Issue Detail Table</span>
                             <span x-show="lang==='id'">3.5 Tabel Detail Issue</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Issue Detail table shows all issued items.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel Detail Issue menampilkan seluruh item yang di-issue.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>No</strong></li>
                             <li><strong>Inventory ID</strong></li>
                             <li><strong>Description</strong></li>
                             <li><strong>Qty SPB</strong> – Quantity requested in SPB</li>
                             <li><strong>UoM</strong></li>
                             <li><strong>Issue Qty</strong> – Actual issued quantity</li>
                             <li><strong>Return Qty</strong> – Total returned quantity</li>
                             <li><strong>Site</strong></li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Return Qty indicates items already returned from this Issue.
                                 Remaining return quantity can be processed via "Create Return (Issue)".
                             </span>
                             <span x-show="lang==='id'">
                                 Return Qty menunjukkan jumlah item yang sudah direturn dari Issue ini.
                                 Sisa return dapat diproses melalui menu "Create Return (Issue)".
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
                         <span x-show="lang==='en'">4. Create Return Issue</span>
                         <span x-show="lang==='id'">4. Buat Return Issue</span>

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
                                 The Create Return (Issue) page is used to generate a return transaction
                                 for items that were previously issued.
                                 This process increases stock back to inventory.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create Return (Issue) digunakan untuk membuat transaksi return
                                 atas item yang sebelumnya sudah di-issue.
                                 Proses ini akan menambah kembali stok ke inventory.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/issue/return/overview.png') }}">
                                 <figcaption>
                                     Figure 4.1 – Create Return (Issue) Page
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 4.2 Reference Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Reference Issue Information</span>
                             <span x-show="lang==='id'">4.2 Informasi Referensi Issue</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 This section displays reference data from the original Issue document.
                                 All fields are read-only.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian ini menampilkan data referensi dari dokumen Issue asli.
                                 Semua field bersifat read-only.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Issue Number (Reference)</strong></li>
                             <li><strong>Issue Date</strong></li>
                             <li><strong>Issue Type</strong></li>
                             <li><strong>SPB ID</strong></li>
                             <li><strong>Company</strong></li>
                             <li><strong>Department</strong></li>
                             <li><strong>Requested By</strong></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The return document is directly linked to the original Issue.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumen return terhubung langsung dengan dokumen Issue asal.
                             </span>
                         </div>
                     </section>

                     <!-- 4.3 Return Detail -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 Return Detail</span>
                             <span x-show="lang==='id'">4.3 Detail Return</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must enter the quantity to return for each item.
                                 The system validates against the remaining allowable quantity.
                             </span>
                             <span x-show="lang==='id'">
                                 User harus mengisi quantity yang akan direturn untuk setiap item.
                                 Sistem akan memvalidasi berdasarkan sisa quantity yang diperbolehkan.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Inventory ID</strong></li>
                             <li><strong>Description</strong></li>
                             <li><strong>Site</strong></li>
                             <li><strong>Location / Sub Location</strong></li>
                             <li><strong>Qty Sisa Return</strong> –
                                 <span x-show="lang==='en'">
                                     Remaining return quantity (Issue Qty – Previous Returns)
                                 </span>
                                 <span x-show="lang==='id'">
                                     Sisa quantity return (Qty Issue – Total Return sebelumnya)
                                 </span>
                             </li>
                             <li><strong>Qty Return</strong> –
                                 <span x-show="lang==='en'">
                                     Input field for new return quantity
                                 </span>
                                 <span x-show="lang==='id'">
                                     Field input quantity return baru
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Qty Return must not exceed Qty Sisa Return.
                                 The system will reject the submission if the value exceeds the limit.
                             </span>
                             <span x-show="lang==='id'">
                                 Qty Return tidak boleh melebihi Qty Sisa Return.
                                 Sistem akan menolak jika melebihi batas.
                             </span>
                         </div>
                     </section>

                     <!-- 4.4 Attachments -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Attachments</span>
                             <span x-show="lang==='id'">4.4 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may upload supporting documents such as return notes,
                                 internal approval letters, or stock adjustment documentation.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengunggah dokumen pendukung seperti berita acara return,
                                 surat persetujuan internal, atau dokumen penyesuaian stok.
                             </span>
                         </p>
                     </section>

                     <!-- 4.5 Submit Return -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 Submit Return</span>
                             <span x-show="lang==='id'">4.5 Submit Return</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After entering all return quantities, click <strong>Submit Return</strong>
                                 to create the return document and continue the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah mengisi seluruh quantity return, klik <strong>Submit Return</strong>
                                 untuk membuat dokumen return dan melanjutkan proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once approved, the return transaction will increase inventory stock
                                 at the original site and location.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disetujui, transaksi return akan menambah stok inventory
                                 pada site dan location asal.
                             </span>
                         </div>
                     </section>
                 </div>
             </div>

         </section>

     </div>
