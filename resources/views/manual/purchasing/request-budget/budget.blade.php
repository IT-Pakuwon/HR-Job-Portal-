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

         <!-- ================= CAUTION CONTENT ================= -->
         <div class="manual-note manual-important">
             <strong>
                 <span x-show="lang==='en'">Caution:</span>
                 <span x-show="lang==='id'">Perhatian:</span>
             </strong>

             <p class="mt-2 text-gray-600 dark:text-gray-400">
                 <span x-show="lang==='en'">
                     The approved 2026 Budget will be imported by the Cost Control team at each site.
                     Please contact the respective Cost Control representative if any budget item is missing
                     or requires clarification.
                 </span>
                 <span x-show="lang==='id'">
                     Budget 2026 yang telah disetujui akan diimpor oleh tim Cost Control
                     di masing-masing site. Harap menghubungi Cost Control terkait apabila terdapat
                     anggaran yang belum tersedia atau memerlukan klarifikasi.
                 </span>
             </p>
         </div>
         <!-- ================= SECTION 1 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s1')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">1. Create / Import Budget</span>
                         <span x-show="lang==='id'">1. Membuat / Import Budget</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="manual-info manual-note text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             This section allows users to upload budget data using an Excel template.
                             The system provides a structured import process including preview validation
                             before submitting for approval.
                         </span>
                         <span x-show="lang==='id'">
                             Bagian ini digunakan untuk mengunggah data budget menggunakan template Excel.
                             Sistem menyediakan proses import yang terstruktur termasuk preview data
                             sebelum diajukan untuk approval.
                         </span>
                     </p>

                     <!-- ================= IMPORT FORM ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Import Budget Form</span>
                         <span x-show="lang==='id'">Form Import Budget</span>
                     </h3>
                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Before uploading the file, users must select the organizational information
                             to determine where the budget belongs. If the user has access to more than one
                             company or department, please ensure the correct selection is made before proceeding.
                         </span>
                         <span x-show="lang==='id'">
                             Sebelum mengunggah file, pengguna harus memilih informasi organisasi
                             untuk menentukan lokasi anggaran. Jika pengguna memiliki lebih dari satu
                             perusahaan atau departemen, pastikan memilih data yang sesuai sebelum melanjutkan.
                         </span>
                     </p>
                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Company</li>
                         <li>Business Unit</li>
                         <li>Department</li>
                         <li>Import Excel File</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/import-form.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 1.1 – Import Budget Form
                             </figcaption>
                         </figure>
                     </div>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Users can download the official budget template using the
                             <strong>Template Budget</strong> button.
                             Only files that follow this format can be processed by the system.
                         </span>
                         <span x-show="lang==='id'">
                             Pengguna dapat mengunduh template resmi melalui tombol
                             <strong>Template Budget</strong>.
                             Hanya file dengan format ini yang dapat diproses oleh sistem.
                         </span>
                     </p>

                     <!-- ================= PREVIEW SECTION ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Preview Budget Data</span>
                         <span x-show="lang==='id'">Preview Data Budget</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             After uploading the Excel file, the system will display a preview table.
                             This preview allows users to verify data accuracy before submitting.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah file diunggah, sistem akan menampilkan tabel preview.
                             Preview ini membantu pengguna memverifikasi data sebelum submit.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Perpost</li>
                         <li>Company & Business Unit</li>
                         <li>Department</li>
                         <li>Account & Activity</li>
                         <li>Quantity & Unit Price</li>
                         <li>Total Budget</li>
                         <li>Monthly Period Budget (01 – 12)</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/preview.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 1.2 – Budget Preview Table
                             </figcaption>
                         </figure>
                     </div>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The table footer automatically calculates total quantity,
                             average unit price, total budget, and monthly totals.
                         </span>
                         <span x-show="lang==='id'">
                             Footer tabel akan otomatis menghitung total quantity,
                             rata-rata unit price, total budget, dan total per periode.
                         </span>
                     </p>

                     <!-- ================= ATTACHMENT & SUBMIT ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Attachments & Submission</span>
                         <span x-show="lang==='id'">Lampiran & Pengajuan</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Before submitting, users may add supporting documents as attachments.
                             Multiple files can be uploaded and removed dynamically.
                         </span>
                         <span x-show="lang==='id'">
                             Sebelum submit, pengguna dapat menambahkan dokumen pendukung sebagai lampiran.
                             Beberapa file dapat ditambahkan atau dihapus secara dinamis.
                         </span>
                     </p>


                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/attachment.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 1.3 – Attachment Section
                             </figcaption>
                         </figure>
                     </div>

                     <div class="manual-note manual-warning">

                         <strong>⚠️ Caution</strong>

                         <div class="mt-2">
                             <span x-show="lang==='en'">
                                 Please attach budget documents that have been fully approved.
                                 Maximum file size per attachment is 5 MB.
                             </span>

                             <span x-show="lang==='id'">
                                 Pastikan melampirkan dokumen budget yang telah fully approved.
                                 Ukuran maksimum setiap file adalah 5 MB.
                             </span>
                         </div>

                     </div>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Clicking <strong>Submit Approval</strong> will send the imported
                             budget data into the approval workflow. The system will display
                             a loading indicator during processing.
                         </span>
                         <span x-show="lang==='id'">
                             Tombol <strong>Submit Approval</strong> akan mengirim data budget
                             ke proses approval. Sistem akan menampilkan loading selama proses berlangsung.
                         </span>
                     </p>
                 </div>
             </div>

         </section>

         <!-- ================= SECTION 2 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s2')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">2. Edit Budget</span>
                         <span x-show="lang==='id'">2. Edit Budget</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="manual-note manual-info text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             <strong> Revised budgets can be filtered by selecting the "Revise" status in the filter
                                 menu.</strong> Edit Budget feature allows users to revise an existing budget
                             by re-importing an updated Excel file. The system maintains the
                             original organizational structure while enabling controlled updates
                             before re-submitting for approval.
                         </span>
                         <span x-show="lang==='id'">
                             <strong> Budget yang telah direvisi dapat difilter dengan memilih status "Revise" pada menu
                                 filter.</strong> Fitur Edit Budget memungkinkan pengguna melakukan revisi terhadap
                             budget yang sudah ada dengan cara mengunggah ulang file Excel terbaru.
                             Sistem akan mempertahankan struktur organisasi sebelumnya dan
                             memungkinkan perubahan sebelum diajukan kembali untuk approval.
                         </span>
                     </p>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/revise.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.1 – List Revised Budgets (Revise Status Filter)
                             </figcaption>
                         </figure>
                     </div>

                     <!-- ================= EDIT IMPORT FORM ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Edit Import Form</span>
                         <span x-show="lang==='id'">Form Edit Import</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             When editing, Company, Business Unit, and Department fields are
                             automatically pre-filled based on the selected budget document.
                         </span>
                         <span x-show="lang==='id'">
                             Saat melakukan edit, field Company, Business Unit, dan Department
                             akan otomatis terisi sesuai data budget yang dipilih.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Company (pre-selected)</li>
                         <li>Business Unit (pre-selected)</li>
                         <li>Department (pre-selected)</li>
                         <li>Upload Updated Excel File</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/edit-import-form.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.1 – Edit Budget Import Form
                             </figcaption>
                         </figure>

                     </div>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Users must use the official template when updating budget data.
                             The import process will generate a preview before changes are applied.
                         </span>
                         <span x-show="lang==='id'">
                             Pengguna wajib menggunakan template resmi saat melakukan update data budget.
                             Proses import akan menampilkan preview sebelum perubahan diterapkan.
                         </span>
                     </p>

                     <!-- ================= EDIT PREVIEW TABLE ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Budget Details Table</span>
                         <span x-show="lang==='id'">Tabel Detail Budget</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The table displays either existing budget data or newly imported
                             preview data. If a new file is uploaded, a preview label will appear.
                         </span>
                         <span x-show="lang==='id'">
                             Tabel akan menampilkan data budget yang sudah ada atau preview
                             hasil import terbaru. Jika file baru diunggah, label preview akan muncul.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Perpost</li>
                         <li>Company & Business Unit</li>
                         <li>Department</li>
                         <li>Account & Activity</li>
                         <li>Description & Detail</li>
                         <li>Qty & Unit Price</li>
                         <li>Total Budget</li>
                         <li>Monthly Period Budget (01 – 12)</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/edit-preview-table.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.2 – Budget Details Table (Edit Mode)
                             </figcaption>
                         </figure>
                     </div>

                     <!-- ================= ATTACHMENT MANAGEMENT ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Attachment Management</span>
                         <span x-show="lang==='id'">Pengelolaan Lampiran</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Existing attachments will be displayed in the edit page.
                             Users can remove previous files or upload additional supporting documents.
                         </span>
                         <span x-show="lang==='id'">
                             Lampiran yang sudah ada akan ditampilkan pada halaman edit.
                             Pengguna dapat menghapus file lama atau menambahkan dokumen pendukung baru.
                         </span>
                     </p>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/edit-attachments.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.3 – Attachment Section (Edit Budget)
                             </figcaption>
                         </figure>
                     </div>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Removing an attachment will trigger a confirmation process.
                             The system updates the attachment list instantly without refreshing the page.
                         </span>
                         <span x-show="lang==='id'">
                             Menghapus lampiran akan memunculkan konfirmasi terlebih dahulu.
                             Sistem akan memperbarui daftar lampiran secara langsung tanpa refresh halaman.
                         </span>
                     </p>

                     <!-- ================= SUBMIT APPROVAL ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Submit Edited Budget</span>
                         <span x-show="lang==='id'">Submit Budget Revisi</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             After reviewing the changes, click <strong>Submit Approval</strong>
                             to send the revised budget back into the approval workflow.
                             A processing indicator will appear during submission.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah memastikan perubahan sudah benar, klik
                             <strong>Submit Approval</strong> untuk mengirim budget revisi
                             kembali ke proses approval. Indikator loading akan muncul
                             selama proses berlangsung.
                         </span>
                     </p>
                 </div>
             </div>
         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. Show Budget</span>
                         <span x-show="lang==='id'">3. Lihat Budget</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="manual-info manual-note text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The Show Budget page provides a complete overview of the selected budget,
                             including approval workflow, attachments, comments, and detailed budget allocation.
                         </span>
                         <span x-show="lang==='id'">
                             Halaman Show Budget menampilkan informasi lengkap mengenai budget yang dipilih,
                             termasuk proses approval, lampiran, komentar, dan detail alokasi budget.
                         </span>
                     </p>

                     <!-- ================= HEADER & ACTION BUTTONS ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Approval Actions</span>
                         <span x-show="lang==='id'">Aksi Approval</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Authorized users may perform approval actions directly from this page.
                         </span>
                         <span x-show="lang==='id'">
                             Pengguna yang memiliki otorisasi dapat melakukan aksi approval langsung dari halaman ini.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Approve — approve the budget request</li>
                         <li>Revise — request revision with a reason</li>
                         <li>Reject — reject the budget submission</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/button.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 3.1 – Approval Action Buttons
                             </figcaption>
                         </figure>

                     </div>

                     <!-- ================= BUDGET SUMMARY CARD ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Budget Summary Information</span>
                         <span x-show="lang==='id'">Informasi Ringkasan Budget</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The summary card displays key information about the budget document,
                             including status, creator, organizational structure, and total budget value.
                         </span>
                         <span x-show="lang==='id'">
                             Kartu ringkasan menampilkan informasi utama mengenai dokumen budget,
                             termasuk status, pembuat, struktur organisasi, dan total nilai budget.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Budget ID</li>
                         <li>Status Indicator</li>
                         <li>Company</li>
                         <li>Business Unit</li>
                         <li>Department</li>
                         <li>Total Budget</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/summary.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 3.2 – Budget Summary Card
                             </figcaption>
                         </figure>

                     </div>

                     <!-- ================= ATTACHMENT / APPROVAL / COMMENTS TABS ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Tabs Section</span>
                         <span x-show="lang==='id'">Bagian Tab</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The right panel contains three main tabs used for collaboration and tracking:
                         </span>
                         <span x-show="lang==='id'">
                             Panel kanan memiliki tiga tab utama untuk kolaborasi dan monitoring:
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Attachment — upload and view supporting files</li>
                         <li>Approval Details — view approval history and levels</li>
                         <li>Comments — discussion between users</li>
                     </ul>

                     {{-- <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <img src="{{ asset('images/manual/budget/show-tabs.png') }}"
                             class="rounded-lg border shadow dark:border-gray-800">
                         <p class="mt-2 text-center text-xs text-gray-500">
                             Figure 3.3 – Attachment, Approval & Comments Tabs
                         </p>
                     </div> --}}

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Attachments can be uploaded dynamically without refreshing the page.
                             Approval history is loaded automatically from the workflow system.
                         </span>
                         <span x-show="lang==='id'">
                             Lampiran dapat diunggah secara dinamis tanpa reload halaman.
                             Riwayat approval akan ditampilkan otomatis dari sistem workflow.
                         </span>
                     </p>

                     <!-- ================= BUDGET DETAIL TABLE ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">Budget Detail Table</span>
                         <span x-show="lang==='id'">Tabel Detail Budget</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The budget detail table shows account allocation per activity
                             including monthly budget distribution.
                         </span>
                         <span x-show="lang==='id'">
                             Tabel detail budget menampilkan alokasi account per aktivitas
                             termasuk distribusi budget per bulan.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Account</li>
                         <li>Description & Detail</li>
                         <li>Total Budget</li>
                         <li>Monthly Budget (January – December)</li>
                     </ul>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <figure class="manual-figure">
                             <img src="{{ asset('images/manual/budget/detail-budget.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <figcaption class="mt-2 text-center text-xs text-gray-500">
                                 Figure 3.4 – Budget Detail Table
                             </figcaption>
                         </figure>
                     </div>

                     <!-- ================= VIEW CONTROLS ================= -->
                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                         <span x-show="lang==='en'">View Controls</span>
                         <span x-show="lang==='id'">Kontrol Tampilan</span>
                     </h3>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Users can adjust how the data is displayed using the control buttons above the table.
                         </span>
                         <span x-show="lang==='id'">
                             Pengguna dapat mengatur tampilan data menggunakan tombol kontrol di atas tabel.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Export Excel — download budget detail to Excel file</li>
                         <li>In Million — switch value display to million format</li>
                         <li>View Full — show additional columns such as Activity ID, Qty, and Unit Price</li>
                     </ul>

                     {{-- <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <img src="{{ asset('images/manual/budget/show-controls.png') }}"
                             class="rounded-lg border shadow dark:border-gray-800">
                         <p class="mt-2 text-center text-xs text-gray-500">
                             Figure 3.5 – Table Control Buttons
                         </p>
                     </div> --}}

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The system automatically recalculates totals and adjusts formatting
                             when switching between display modes.
                         </span>
                         <span x-show="lang==='id'">
                             Sistem akan otomatis menghitung ulang total dan menyesuaikan format
                             saat pengguna mengganti mode tampilan.
                         </span>
                     </p>
                 </div>
             </div>

         </section>

     </div>
