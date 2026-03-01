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
                         <span x-show="lang==='en'">1. Create WO</span>
                         <span x-show="lang==='id'">1. Buat WO</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- ================= SECTION 1 ================= -->
                     <section class="space-y-10">


                         <!-- 1.1 Overview -->
                         <section class="space-y-4">

                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 <span x-show="lang==='en'">1.1 Overview</span>
                                 <span x-show="lang==='id'">1.1 Gambaran Umum</span>
                             </h3>

                             <p class="text-gray-600 dark:text-gray-400">
                                 <span x-show="lang==='en'">
                                     Work Order (WO) is used to request and manage operational or maintenance work
                                     within a company. The WO document contains job classification, location, budget,
                                     and financial account allocation.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Work Order (WO) digunakan untuk mengajukan dan mengelola pekerjaan operasional
                                     atau maintenance dalam perusahaan. Dokumen WO memuat klasifikasi pekerjaan, lokasi,
                                     anggaran, dan alokasi akun keuangan.
                                 </span>
                             </p>

                             <div class="manual-note manual-info">
                                 <span x-show="lang==='en'">
                                     WO must be approved before it can proceed to execution.
                                 </span>
                                 <span x-show="lang==='id'">
                                     WO harus melalui proses approval sebelum dapat diproses lebih lanjut.
                                 </span>
                             </div>
                         </section>

                         <!-- 1.2 Basic Information -->
                         <section class="space-y-4">

                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 <span x-show="lang==='en'">1.2 Basic Information</span>
                                 <span x-show="lang==='id'">1.2 Informasi Dasar</span>
                             </h3>

                             <p class="text-gray-600 dark:text-gray-400">
                                 <span x-show="lang==='en'">
                                     Users must complete the following mandatory fields:
                                 </span>
                                 <span x-show="lang==='id'">
                                     Pengguna wajib mengisi field berikut:
                                 </span>
                             </p>

                             <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                                 <li><strong>Company</strong></li>
                                 <li><strong>Business Unit</strong></li>
                                 <li><strong>Department</strong></li>
                                 <li><strong>WO Type</strong></li>
                                 <li><strong>WO Request</strong></li>
                             </ul>

                             <div class="manual-note manual-warning">
                                 <span x-show="lang==='en'">
                                     Company and Department are automatically filtered based on user authorization.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Company dan Department akan otomatis terfilter sesuai dengan otorisasi user.
                                 </span>
                             </div>

                             <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                                 <figure class="manual-figure">
                                     <img src="{{ asset('images/manual/wo/create-header.png') }}">
                                     <figcaption>
                                         Figure 1.1 – Create WO Header Section
                                     </figcaption>
                                 </figure>
                             </div>


                         </section>

                         <!-- 1.3 Job & Location Selection -->
                         <section class="space-y-4">

                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 <span x-show="lang==='en'">1.3 Job Type & Location Selection</span>
                                 <span x-show="lang==='id'">1.3 Pemilihan Jenis Pekerjaan & Lokasi</span>
                             </h3>

                             <p class="text-gray-600 dark:text-gray-400">
                                 <span x-show="lang==='en'">
                                     Click the 🔎 icon to select Worktype, Sub Worktype, Location,
                                     and Sub Location through the lookup modal.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Klik ikon 🔎 untuk memilih Worktype, Sub Worktype, Location,
                                     dan Sub Location melalui modal lookup.
                                 </span>
                             </p>

                             <div class="manual-note manual-important">
                                 <span x-show="lang==='en'">
                                     Worktype and Location must match operational scope.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Worktype dan Location harus sesuai dengan ruang lingkup pekerjaan.
                                 </span>
                             </div>

                         </section>

                         <!-- 1.4 Budget & COA -->
                         <section class="space-y-4">

                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 <span x-show="lang==='en'">1.4 Budget & COA Selection</span>
                                 <span x-show="lang==='id'">1.4 Pemilihan Budget & COA</span>
                             </h3>

                             <p class="text-gray-600 dark:text-gray-400">
                                 <span x-show="lang==='en'">
                                     Users must select Budget ownership (Pemberi Kerja / Penerima Kerja)
                                     and assign the correct COA using the lookup feature.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Pengguna harus memilih kepemilikan Budget (Pemberi Kerja / Penerima Kerja)
                                     serta menentukan COA yang sesuai melalui fitur lookup.
                                 </span>
                             </p>

                             <div class="manual-note manual-warning">
                                 <span x-show="lang==='en'">
                                     WO cannot be submitted without valid COA selection.
                                 </span>
                                 <span x-show="lang==='id'">
                                     WO tidak dapat disubmit tanpa pemilihan COA yang valid.
                                 </span>
                             </div>

                             <div class="manual-note manual-important">
                                 <span x-show="lang==='en'">
                                     Remaining budget will be validated before approval.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Sisa anggaran akan divalidasi sebelum proses approval.
                                 </span>
                             </div>

                         </section>

                         <!-- 1.5 Description & Attachment -->
                         <section class="space-y-4">

                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 <span x-show="lang==='en'">1.5 Description & Attachments</span>
                                 <span x-show="lang==='id'">1.5 Deskripsi & Lampiran</span>
                             </h3>

                             <p class="text-gray-600 dark:text-gray-400">
                                 <span x-show="lang==='en'">
                                     Provide a clear description of the requested work.
                                     Supporting documents may be uploaded in the Attachments section.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Isi deskripsi pekerjaan secara jelas.
                                     Dokumen pendukung dapat diunggah pada bagian Attachments.
                                 </span>
                             </p>

                             <div class="manual-note manual-info">
                                 <span x-show="lang==='en'">
                                     Maximum 10 files are allowed. PDF or image formats are recommended.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Maksimal 10 file diperbolehkan. Disarankan menggunakan format PDF atau gambar.
                                 </span>
                             </div>
                             <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                                 <figure class="manual-figure">
                                     <img src="{{ asset('images/manual/wo/create-attachment.png') }}">
                                     <figcaption>
                                         Figure 1.2 – Create WO Attachment Section
                                     </figcaption>
                                 </figure>
                             </div>

                         </section>

                         <!-- 1.6 Submit -->
                         <section class="space-y-4">

                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                 <span x-show="lang==='en'">1.6 Submit Work Order</span>
                                 <span x-show="lang==='id'">1.6 Submit Work Order</span>
                             </h3>

                             <p class="text-gray-600 dark:text-gray-400">
                                 <span x-show="lang==='en'">
                                     After completing all required fields, click <strong>Submit Approval</strong>
                                     to send the WO into the approval workflow.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Setelah semua field wajib diisi, klik <strong>Submit Approval</strong>
                                     untuk mengirim WO ke proses approval.
                                 </span>
                             </p>

                             <div class="manual-note manual-important">
                                 <span x-show="lang==='en'">
                                     Once submitted, the WO cannot be edited unless returned for revision.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Setelah disubmit, WO tidak dapat diedit kecuali dikembalikan untuk revisi.
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
                         <span x-show="lang==='en'">2. Edit WO</span>
                         <span x-show="lang==='id'">2. Edit WO</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- ================= SECTION 2 ================= -->


                     <!-- 2.1 Overview -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Overview</span>
                             <span x-show="lang==='id'">2.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Edit WO feature allows users to modify an existing Work Order
                                 before it is fully processed or executed.
                             </span>
                             <span x-show="lang==='id'">
                                 Fitur Edit WO memungkinkan pengguna untuk mengubah Work Order
                                 yang sudah dibuat sebelum diproses atau dieksekusi sepenuhnya.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only WO with editable status (e.g., Draft or Revise) can be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya WO dengan status yang masih dapat diedit (misalnya Draft atau Revise)
                                 yang dapat dilakukan perubahan.
                             </span>
                         </div>
                     </section>

                     <!-- 2.2 Editable Fields -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Editable Fields</span>
                             <span x-show="lang==='id'">2.2 Field yang Dapat Diedit</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update the following fields:
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat memperbarui field berikut:
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Business Unit</li>
                             <li>Department</li>
                             <li>WO Type & WO Request</li>
                             <li>Worktype & Sub Worktype</li>
                             <li>Location & Sub Location</li>
                             <li>Budget & COA</li>
                             <li>Description</li>
                             <li>Attachments</li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Company selection is usually locked and cannot be changed
                                 after WO creation.
                             </span>
                             <span x-show="lang==='id'">
                                 Field Company umumnya tidak dapat diubah
                                 setelah WO dibuat.
                             </span>
                         </div>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/wo/edit-header.png') }}">
                                 <figcaption>
                                     Figure 2.1 – Edit WO Header Section
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- 2.3 Budget & COA Adjustment -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Budget & COA Adjustment</span>
                             <span x-show="lang==='id'">2.3 Penyesuaian Budget & COA</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When modifying Budget ownership or COA,
                                 the system will re-validate remaining budget.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika mengubah Budget atau COA,
                                 sistem akan melakukan validasi ulang terhadap sisa anggaran.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 WO cannot be submitted if the selected COA has insufficient remaining budget.
                             </span>
                             <span x-show="lang==='id'">
                                 WO tidak dapat disubmit apabila COA yang dipilih
                                 tidak memiliki sisa anggaran yang mencukupi.
                             </span>
                         </div>

                     </section>

                     <!-- 2.4 Attachment Management -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Attachment Management</span>
                             <span x-show="lang==='id'">2.4 Pengelolaan Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Existing attachments may be removed and new files can be uploaded.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran yang sudah ada dapat dihapus,
                                 dan file baru dapat ditambahkan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Attachment changes will be recorded in the system audit log.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan lampiran akan tercatat dalam log sistem.
                             </span>
                         </div>

                     </section>

                     <!-- 2.5 Submit Updated WO -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.5 Submit Updated WO</span>
                             <span x-show="lang==='id'">2.5 Submit WO yang Telah Diperbarui</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing revisions, click <strong>Submit Approval</strong>
                                 to resend the WO into the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah revisi selesai, klik <strong>Submit Approval</strong>
                                 untuk mengirim ulang WO ke proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once resubmitted, the WO will follow the approval sequence again.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah dikirim ulang, WO akan mengikuti alur approval dari awal.
                             </span>
                         </div>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/wo/edit-attachment.png') }}">
                                 <figcaption>
                                     Figure 2.2 – Edit WO Attachment Section
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
                         <span x-show="lang==='en'">3. List WO</span>
                         <span x-show="lang==='id'">3. Daftar WO</span>

                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- ================= SECTION 3 ================= -->


                     <!-- 3.1 Overview -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Overview</span>
                             <span x-show="lang==='id'">3.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The WO List page displays all submitted Work Orders,
                                 including their workflow status and processing progress.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar WO menampilkan seluruh Work Order
                                 yang telah dibuat, termasuk status dan progres prosesnya.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users can filter, track, and create new Work Orders directly from this page.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat memfilter, melakukan tracking,
                                 dan membuat Work Order baru langsung dari halaman ini.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/wo/list.png') }}">
                                 <figcaption>
                                     Figure 3.1 – WO List Overview
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- 3.2 Status Cards -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status Cards</span>
                             <span x-show="lang==='id'">3.2 Kartu Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards summarize the total number of WO documents
                                 grouped by workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status menampilkan jumlah total dokumen WO
                                 berdasarkan status proses.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>All</strong> – All WO records</li>
                             <li><strong>On Progress</strong> – WO currently in approval process</li>
                             <li><strong>Reject</strong> – WO rejected by approver</li>
                             <li><strong>Revise / Draft</strong> – WO returned for revision or still draft</li>
                             <li><strong>Completed</strong> – WO fully approved and completed</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Clicking a status card will automatically filter the WO table below.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada kartu status akan memfilter tabel WO di bawahnya secara otomatis.
                             </span>
                         </div>

                     </section>

                     <!-- 3.3 WO Table -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 WO Data Table</span>
                             <span x-show="lang==='id'">3.3 Tabel Data WO</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The table displays detailed information for each Work Order.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel menampilkan informasi detail untuk setiap Work Order.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Document ID</li>
                             <li>Date</li>
                             <li>Company</li>
                             <li>Department</li>
                             <li>Work Type</li>
                             <li>WO Request</li>
                             <li>Budget Use</li>
                             <li>Description</li>
                             <li>Status</li>
                             <li>WO Status</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The table supports searching, sorting, and pagination.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel mendukung fitur pencarian, pengurutan, dan pagination.
                             </span>
                         </div>

                     </section>

                     <!-- 3.4 Create WO -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Create New WO</span>
                             <span x-show="lang==='id'">3.4 Membuat WO Baru</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click the <strong>Create</strong> button to open the WO creation form.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik tombol <strong>Create</strong> untuk membuka form pembuatan WO.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Ensure Business Unit and Budget are selected correctly,
                                 as budget validation is locked based on Business Unit.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan Business Unit dan Budget dipilih dengan benar,
                                 karena validasi anggaran dikunci berdasarkan Business Unit.
                             </span>
                         </div>

                     </section>

                     <!-- 3.5 WO Tracking -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.5 WO Tracking Timeline</span>
                             <span x-show="lang==='id'">3.5 Timeline Tracking WO</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may open the Tracking modal to monitor
                                 approval progress and action history.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat membuka modal Tracking untuk memantau
                                 progres approval dan riwayat tindakan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The timeline shows each approval level,
                                 including timestamps and user actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Timeline menampilkan setiap level approval,
                                 termasuk waktu dan tindakan pengguna.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Tracking information is read-only and cannot be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Informasi tracking bersifat read-only dan tidak dapat diubah.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/wo/tracking-modal.png') }}">
                                 <figcaption>
                                     Figure 3.3 – WO Tracking Timeline
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
                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Show WO Details</span>
                         <span x-show="lang==='id'">4. Tampilkan Detail WO</span>

                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- ================= SECTION 4 ================= -->

                     <!-- 4.1 Overview -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Overview</span>
                             <span x-show="lang==='id'">4.1 Gambaran Umum</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Process WO section allows authorized users to update job execution status.
                                 However, when the logged-in user does not have processing rights,
                                 the system will display the information in Read Only mode.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian Process WO memungkinkan user yang berwenang
                                 untuk memperbarui status pekerjaan.
                                 Namun, apabila user login tidak memiliki hak proses,
                                 sistem akan menampilkan data dalam mode Read Only.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Only the assigned PIC WO or authorized department
                                 may update job processing information.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya PIC WO yang ditunjuk atau departemen terkait
                                 yang dapat memperbarui informasi proses pekerjaan.
                             </span>
                         </div>
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/wo/overview.png') }}">
                                 <figcaption>
                                     Figure 4.1 – Show WO Details Overview
                                 </figcaption>
                             </figure>
                         </div>

                     </section>

                     <!-- 4.2 Approval Actions -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Approval Actions</span>
                             <span x-show="lang==='id'">4.2 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top-right section, approvers may see the following actions:
                             </span>
                             <span x-show="lang==='id'">
                                 Pada bagian kanan atas, approver dapat melihat tombol aksi berikut:
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> – Approve the WO</li>
                             <li><strong>Revise</strong> – Return the WO for revision</li>
                             <li><strong>Reject</strong> – Reject the WO</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Reject and Revise actions require a mandatory comment.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Reject dan Revise wajib disertai dengan alasan atau komentar.
                             </span>
                         </div>

                     </section>

                     <!-- 4.3 WO Information Card -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 WO Information Display</span>
                             <span x-show="lang==='id'">4.3 Tampilan Informasi WO</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The left panel displays complete WO information including:
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kiri menampilkan informasi lengkap WO meliputi:
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Company</li>
                             <li>Department</li>
                             <li>Date</li>
                             <li>Created User</li>
                             <li>WO Type & Request</li>
                             <li>Worktype & Sub Worktype</li>
                             <li>Location & Sub Location</li>
                             <li>Budget Information</li>
                             <li>Purpose / Description</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 This information is always read-only and cannot be modified from this page.
                             </span>
                             <span x-show="lang==='id'">
                                 Informasi ini selalu bersifat read-only dan tidak dapat diubah dari halaman ini.
                             </span>
                         </div>

                     </section>

                     <!-- 4.4 Tabs Section -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Tabs: Attachment, Approval, Comments</span>
                             <span x-show="lang==='id'">4.4 Tab: Attachment, Approval, Comments</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right panel contains tab navigation for:
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kanan menyediakan navigasi tab untuk:
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Attachment</strong> – View and download attached files</li>
                             <li><strong>Approval Details</strong> – View approval history per level</li>
                             <li><strong>Comments</strong> – View discussion or add new comments</li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Approval history is generated automatically by the workflow system
                                 and cannot be edited manually.
                             </span>
                             <span x-show="lang==='id'">
                                 Riwayat approval dihasilkan otomatis oleh sistem workflow
                                 dan tidak dapat diubah secara manual.
                             </span>
                         </div>

                     </section>

                     <!-- 4.5 Read Only Job Process Box -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 Job Process (Read Only Mode)</span>
                             <span x-show="lang==='id'">4.5 Proses Pekerjaan (Mode Read Only)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the logged-in user is not authorized to process the WO,
                                 the system will display the job processing information in read-only format.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika user login tidak memiliki hak proses,
                                 sistem akan menampilkan informasi proses pekerjaan dalam format read-only.
                             </span>
                         </p>

                         <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Status Pekerjaan (On Progress / Completed / Cancel)</li>
                             <li>Assigned Department</li>
                             <li>SPB/SPPBJKT Flag</li>
                             <li>PIC WO Comment</li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 In Read Only mode, no status updates, department changes,
                                 or comments can be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Dalam mode Read Only, tidak dapat dilakukan perubahan status,
                                 perubahan departemen, maupun pengeditan komentar.
                             </span>
                         </div>

                     </section>
                 </div>
             </div>
         </section>
     </div>
