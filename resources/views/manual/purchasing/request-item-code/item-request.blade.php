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
                         <span x-show="lang==='en'">1. Create Item Request</span>
                         <span x-show="lang==='id'">1. Buat Permintaan Item</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Create Item Request Overview</span>
                             <span x-show="lang==='id'">1.1 Gambaran Create Item Request</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Create Item Request page allows users to submit a new inventory request
                                 for STOCK or NON-STOCK items that are not yet registered in the system.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create Item Request digunakan untuk mengajukan permintaan item inventori
                                 baik STOCK maupun NON-STOCK yang belum terdaftar di sistem.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Submitted requests will follow the approval workflow
                                 based on Company and Department authorization.
                             </span>
                             <span x-show="lang==='id'">
                                 Permintaan yang diajukan akan mengikuti alur approval
                                 sesuai otorisasi Company dan Department.
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
                                 Users must complete the following mandatory fields before submitting:
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna wajib melengkapi field berikut sebelum melakukan submit:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Company</li>
                             <li>Department</li>
                             <li>Inventory Type (STOCK / NON-STOCK)</li>
                             <li>Inventory Description</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the Inventory Description is clear and detailed,
                                 as it will be used for item master validation.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan Inventory Description jelas dan detail,
                                 karena akan digunakan untuk validasi master item.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/create-header.png') }}">
                                 <figcaption>
                                     Figure 1.2 – Item Request Header Fields
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Inventory Type Selection</span>
                             <span x-show="lang==='id'">1.3 Pemilihan Inventory Type</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must select the appropriate inventory type:
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna harus memilih tipe inventori yang sesuai:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>STOCK</strong> – Item will be managed in warehouse inventory</li>
                             <li><strong>NON-STOCK</strong> – Item will not be stored as warehouse inventory</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Incorrect inventory type selection may affect procurement
                                 and inventory control processes.
                             </span>
                             <span x-show="lang==='id'">
                                 Kesalahan pemilihan tipe inventori dapat mempengaruhi proses
                                 pengadaan dan kontrol persediaan.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 Attachments</span>
                             <span x-show="lang==='id'">1.4 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At least one attachment is required before submission.
                                 Supporting documents may include specifications,
                                 product images, or technical references.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal satu lampiran wajib diunggah sebelum submit.
                                 Dokumen pendukung dapat berupa spesifikasi,
                                 gambar produk, atau referensi teknis.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Submission will be rejected automatically if no attachment is uploaded.
                             </span>
                             <span x-show="lang==='id'">
                                 Sistem akan menolak submit apabila tidak terdapat lampiran.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Multiple attachments can be added using the "Add Attachment" button.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat menambahkan lebih dari satu lampiran
                                 dengan tombol "Add Attachment".
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/create-attachment.png') }}">
                                 <figcaption>
                                     Figure 1.3 – Attachment Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Submit Item Request</span>
                             <span x-show="lang==='id'">1.5 Submit Item Request</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all required fields and uploading attachments,
                                 users may submit the Item Request for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah seluruh field dan lampiran terisi,
                                 pengguna dapat melakukan submit untuk proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once submitted, the document cannot be modified
                                 unless returned for revision.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, dokumen tidak dapat diubah
                                 kecuali dikembalikan untuk revisi.
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
                         <span x-show="lang==='en'">2. Edit Item Request</span>
                         <span x-show="lang==='id'">2. Edit Permintaan Item</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Edit Item Request Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Edit Item Request</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit Item Request page allows users to modify an existing
                                 Item Request before re-submitting it for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit Item Request digunakan untuk mengubah dokumen
                                 Item Request sebelum diajukan kembali untuk approval.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing is only permitted while the document status is Draft or Revise.
                             </span>
                             <span x-show="lang==='id'">
                                 Proses edit hanya diperbolehkan jika status dokumen masih Draft atau Revise.
                             </span>
                         </div>


                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Edit Item Request Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Edit Item Request</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit Item Request page allows users to modify an existing
                                 Item Request before re-submitting it for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit Item Request digunakan untuk mengubah dokumen
                                 Item Request sebelum diajukan kembali untuk approval.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Editing is only permitted while the document status is Draft or Revise.
                             </span>
                             <span x-show="lang==='id'">
                                 Proses edit hanya diperbolehkan jika status dokumen masih Draft atau Revise.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/edit-header.png') }}">
                                 <figcaption>
                                     Figure 2.1 – Edit Item Request Page Overview
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
                                 Users may remove existing attachments and upload new supporting documents.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat menghapus lampiran yang sudah ada dan
                                 mengunggah dokumen pendukung baru.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 At least one attachment must remain before re-submitting the document.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal harus terdapat satu lampiran sebelum dokumen disubmit ulang.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Uploaded attachments should support item specification,
                                 technical details, or product references.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran yang diunggah sebaiknya mendukung spesifikasi item,
                                 detail teknis, atau referensi produk.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/edit-attachment.png') }}">
                                 <figcaption>
                                     Figure 2.3 – Manage Attachments Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Re-Submit or Cancel</span>
                             <span x-show="lang==='id'">2.4 Submit Ulang atau Cancel</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing the necessary updates,
                                 users may re-submit the Item Request for approval
                                 or cancel the document if required.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah seluruh perubahan selesai,
                                 pengguna dapat melakukan submit ulang untuk approval
                                 atau membatalkan dokumen jika diperlukan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once re-submitted, the document will follow
                                 the approval workflow and cannot be edited
                                 unless returned again for revision.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit ulang, dokumen akan mengikuti
                                 alur approval dan tidak dapat diedit
                                 kecuali dikembalikan kembali untuk revisi.
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
                         <span x-show="lang==='en'">3. Show Item Request</span>
                         <span x-show="lang==='id'">3. Tampilan Permintaan Barang</span>

                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Item Request Detail Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Detail Item Request</span>
                         </h3>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/list.pn g') }}">
                                 <figcaption>
                                     Figure 3.1 – Item Request List Page
                                 </figcaption>
                             </figure>
                         </div>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show Item Request page displays complete information of the selected
                                 Item Request document, including company, department, request details,
                                 inventory type, attachments, approval history, and comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show Item Request menampilkan informasi lengkap dari dokumen
                                 Item Request yang dipilih, termasuk company, department,
                                 detail permintaan, tipe inventory, lampiran, riwayat approval, dan komentar.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This page is used by approvers to review and decide whether the document
                                 should be Approved, Revised, or Rejected.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini digunakan oleh approver untuk melakukan review dan menentukan
                                 apakah dokumen akan di-Approve, Revise, atau Reject.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Item Request Detail Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Detail Item Request</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Show Item Request page displays complete information of the selected
                                 Item Request document, including company, department, request details,
                                 inventory type, attachments, approval history, and comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show Item Request menampilkan informasi lengkap dari dokumen
                                 Item Request yang dipilih, termasuk company, department,
                                 detail permintaan, tipe inventory, lampiran, riwayat approval, dan komentar.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This page is used by approvers to review and decide whether the document
                                 should be Approved, Revised, or Rejected.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini digunakan oleh approver untuk melakukan review dan menentukan
                                 apakah dokumen akan di-Approve, Revise, atau Reject.
                             </span>
                         </div>

                         {{-- <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/show/overview.png') }}">
                                 <figcaption>
                                     Figure 3.1 – Item Request Detail Page
                                 </figcaption>
                             </figure>
                         </div> --}}
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/overview.png') }}">
                                 <figcaption>
                                     Figure 3.1 – Item Request Detail Page
                                 </figcaption>
                             </figure>
                         </div>


                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Item Request Information</span>
                             <span x-show="lang==='id'">3.2 Informasi Item Request</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The left information card displays the main document data
                                 such as Company, Department, Date, Created User,
                                 Inventory Type, and Request Description.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu informasi sebelah kiri menampilkan data utama dokumen
                                 seperti Company, Department, Tanggal, User Pembuat,
                                 Tipe Inventory, dan Deskripsi Permintaan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the Business Unit (Company & Department)
                                 is correct before approving, because budget validation
                                 is locked based on Business Unit.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan Business Unit (Company & Department)
                                 sudah benar sebelum melakukan approval,
                                 karena validasi budget terkunci berdasarkan Business Unit.
                             </span>
                         </div>

                         {{-- <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/show/info-card.png') }}">
                                 <figcaption>
                                     Figure 3.2 – Item Request Information Card
                                 </figcaption>
                             </figure>
                         </div> --}}

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Approval Actions</span>
                             <span x-show="lang==='id'">3.3 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized users can take action using the buttons:
                                 <strong>Approve</strong>, <strong>Revise</strong>, or <strong>Reject</strong>.
                             </span>
                             <span x-show="lang==='id'">
                                 User yang memiliki otorisasi dapat melakukan aksi melalui tombol:
                                 <strong>Approve</strong>, <strong>Revise</strong>, atau <strong>Reject</strong>.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Reject and Revise actions require a mandatory reason
                                 before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Reject dan Revise wajib mengisi alasan
                                 sebelum dikirim.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Once approved at the final level,
                                 the Item Request cannot be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disetujui pada level final,
                                 Item Request tidak dapat diubah kembali.
                             </span>
                         </div>

                         {{-- <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/show/action-buttons.png') }}">
                                 <figcaption>
                                     Figure 3.3 – Approval Action Buttons
                                 </figcaption>
                             </figure>
                         </div> --}}

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Attachment Management</span>
                             <span x-show="lang==='id'">3.4 Manajemen Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Attachment tab displays uploaded supporting documents.
                                 Users with permission may upload additional attachments.
                             </span>
                             <span x-show="lang==='id'">
                                 Tab Attachment menampilkan dokumen pendukung yang telah diunggah.
                                 User yang memiliki akses dapat menambahkan lampiran tambahan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum 10 files per upload.
                                 PDF and image formats are recommended.
                             </span>
                             <span x-show="lang==='id'">
                                 Maksimal 10 file per upload.
                                 Format PDF dan gambar direkomendasikan.
                             </span>
                         </div>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 For SPPBJK, SPB, and WO Item Request,
                                 attachment is mandatory (minimum 1 file).
                             </span>
                             <span x-show="lang==='id'">
                                 Untuk SPPBJK, SPB, dan WO Item Request,
                                 lampiran wajib minimal 1 file.
                             </span>
                         </div>
                         {{--
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/show/attachment-tab.png') }}">
                                 <figcaption>
                                     Figure 3.4 – Attachment Tab
                                 </figcaption>
                             </figure>
                         </div> --}}

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 Approval History & Comments</span>
                             <span x-show="lang==='id'">3.5 Riwayat Approval & Komentar</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Approval Details tab displays the approval workflow history,
                                 including approval level, approver name, date, and status.
                             </span>
                             <span x-show="lang==='id'">
                                 Tab Approval Details menampilkan riwayat alur approval,
                                 termasuk level approval, nama approver, tanggal, dan status.
                             </span>
                         </p>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Comments tab allows communication between requester and approvers.
                             </span>
                             <span x-show="lang==='id'">
                                 Tab Comments memungkinkan komunikasi antara requester dan approver.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 All approval actions and comments are recorded for audit trail purposes.
                             </span>
                             <span x-show="lang==='id'">
                                 Seluruh aksi approval dan komentar tercatat
                                 sebagai bagian dari audit trail.
                             </span>
                         </div>

                         {{-- <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/itemreq/show/approval-comments.png') }}">
                                 <figcaption>
                                     Figure 3.5 – Approval & Comments Tab
                                 </figcaption>
                             </figure>
                         </div> --}}

                     </section>
                 </div>
             </div>

         </section>
     </div>
