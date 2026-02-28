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
                         <span x-show="lang==='en'">1. Create SPPB</span>
                         <span x-show="lang==='id'">1. Membuat SPPB</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     {{-- ================= OVERVIEW ================= --}}
                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The Create SPPB page is used to submit a new purchase request.
                             Users must complete header information, add item details,
                             select related references via lookup modals,
                             and upload supporting documents before submitting for approval.
                         </span>
                         <span x-show="lang==='id'">
                             Halaman Create SPPB digunakan untuk membuat permintaan pembelian baru.
                             Pengguna harus melengkapi informasi header, menambahkan detail item,
                             memilih referensi melalui lookup modal,
                             serta mengunggah dokumen pendukung sebelum mengajukan approval.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Request Type and COA selection determine the approval workflow of the SPPB document.
                         </span>
                         <span x-show="lang==='id'">
                             Pemilihan Request Type dan COA menentukan alur approval dokumen SPPB.
                         </span>
                     </div>

                     {{-- ================= 1.1 HEADER ================= --}}
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Header Information</span>
                             <span x-show="lang==='id'">1.1 Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must select Company, Business Unit, Department,
                                 Request Type, and Perpost before proceeding to item input.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna wajib memilih Company, Business Unit, Department,
                                 Request Type, dan Perpost sebelum melanjutkan ke pengisian item.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <img src="{{ asset('images/manual/sppb/preview.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <p class="mt-2 text-center text-xs text-gray-500">
                                 Figure 1.1 – Create SPPB Header Section
                             </p>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Business Unit options are filtered based on the selected Company.
                             </span>
                             <span x-show="lang==='id'">
                                 Pilihan Business Unit difilter berdasarkan Company yang dipilih.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the selected Business Unit is correct.
                                 Budget availability is locked and controlled based on the selected Business Unit.
                                 Incorrect selection may result in budget mismatch or approval rejection.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan Business Unit yang dipilih sudah sesuai.
                                 Ketersediaan budget dikunci dan dikontrol berdasarkan Business Unit yang dipilih.
                                 Kesalahan pemilihan dapat menyebabkan ketidaksesuaian budget atau penolakan approval.
                             </span>
                         </div>

                     </section>

                     {{-- ================= 1.2 EMERGENCY & WO ================= --}}
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Emergency & Work Order</span>
                             <span x-show="lang==='id'">1.2 Emergency & Work Order</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may mark the SPPB as Emergency and optionally link
                                 a Work Order (WO) to associate the request with an existing activity.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat menandai SPPB sebagai Emergency dan menghubungkan
                                 Work Order (WO) untuk mengaitkan permintaan dengan aktivitas yang sudah ada.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 If a Work Order (WO) is selected, the system will automatically
                                 carry over the Description and Attachments from the selected WO
                                 into the SPPB form.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika Work Order (WO) dipilih, sistem akan secara otomatis
                                 membawa Description dan Attachment dari WO yang dipilih
                                 ke dalam form SPPB.
                             </span>
                         </div>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Ensure the selected WO is correct before submission,
                                 as the linked data will follow the WO reference.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan WO yang dipilih sudah benar sebelum submit,
                                 karena data yang terhubung akan mengikuti referensi WO tersebut.
                             </span>
                         </div>
                         {{--
                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <img src="{{ asset('images/manual/sppb/create/emergency-wo.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <p class="mt-2 text-center text-xs text-gray-500">
                                 Figure 1.2 – Emergency Flag and WO Selection
                             </p>
                         </div> --}}

                     </section>
                     {{-- ================= 1.3 DETAIL TABLE ================= --}}
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 SPPB Detail Input</span>
                             <span x-show="lang==='id'">1.3 Input Detail SPPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each row in the SPPB Detail table represents one requested item.
                                 Users must complete Product, Quantity, UoM, Location, and COA.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap baris pada tabel SPPB Detail mewakili satu item permintaan.
                                 Pengguna wajib melengkapi Product, Quantity, UoM, Location, dan COA.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <img src="{{ asset('images/manual/sppb/detail-table.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <p class="mt-2 text-center text-xs text-gray-500">
                                 Figure 1.3 – SPPB Detail Table
                             </p>
                         </div>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Inventory Lookup</li>
                             <li>UoM Selection</li>
                             <li>Location & Sub Location Picker</li>
                             <li>COA Selection Modal</li>
                             <li>Add / Remove Row</li>
                         </ul>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 COA and Location are mandatory before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 COA dan Location wajib dipilih sebelum submit.
                             </span>
                         </div>

                     </section>

                     {{-- ================= 1.4 ATTACHMENTS ================= --}}
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 Attachments</span>
                             <span x-show="lang==='id'">1.4 Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users must upload supporting documents related to the purchase request.
                                 Multiple files can be attached.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna wajib mengunggah dokumen pendukung terkait permintaan pembelian.
                                 Beberapa file dapat dilampirkan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Attachments are mandatory (minimum 1 file).
                                 Ensure the attached documents for (SPPB JKT, SPB, WO, or Item Request)
                                 are correct and do not exceed the maximum file size
                                 of 5MB per file.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran wajib diunggah (minimal 1 file).
                                 Pastikan dokumen yang dilampirkan untuk (SPPB JKT, SPB, WO, atau Item Request)
                                 sesuai dan tidak melebihi ukuran maksimal
                                 5MB per file.
                             </span>
                         </div>

                     </section>

                     {{-- ================= 1.5 SUBMIT ================= --}}
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Submit Approval</span>
                             <span x-show="lang==='id'">1.5 Submit Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After all required fields are completed, click "Submit Approval"
                                 to send the SPPB into the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah semua field wajib diisi, klik "Submit Approval"
                                 untuk mengirim SPPB ke proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The system validates all mandatory fields before allowing submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Sistem akan memvalidasi seluruh field wajib sebelum dokumen dapat dikirim.
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
                         <span x-show="lang==='en'">2. Edit SPPB</span>
                         <span x-show="lang==='id'">2. Edit SPPB</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- OVERVIEW -->
                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The Edit SPPB feature allows users to modify an existing SPPB document
                             before final approval. Users may update header information, item details,
                             attachments, or linked Work Order data.
                         </span>
                         <span x-show="lang==='id'">
                             Fitur Edit SPPB memungkinkan pengguna untuk melakukan perubahan
                             pada dokumen SPPB yang sudah dibuat sebelum final approval.
                             Pengguna dapat memperbarui informasi header, detail item,
                             lampiran, atau data Work Order yang terhubung.
                         </span>
                     </p>

                     <!-- INFO BLOCK -->
                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Only SPPB documents in Draft or Rejected status can be edited.
                             Approved documents cannot be modified.
                         </span>
                         <span x-show="lang==='id'">
                             Hanya dokumen SPPB dengan status Draft atau Rejected
                             yang dapat diedit. Dokumen yang sudah Approved tidak dapat diubah.
                         </span>
                     </div>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <img src="{{ asset('images/manual/sppb/edit-list.png') }}"
                             class="rounded-lg border shadow dark:border-gray-800">
                         <p class="mt-2 text-center text-xs text-gray-500">
                             Figure 2.1 – Revise SPPB Section
                         </p>
                     </div>


                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Edit Header Information</span>
                             <span x-show="lang==='id'">2.1 Edit Informasi Header</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may update Company, Business Unit, Department,
                                 Request Type, Perpost, Emergency flag, and Work Order reference.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat memperbarui Company, Business Unit, Department,
                                 Request Type, Perpost, status Emergency, dan referensi Work Order.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Changing the Business Unit will affect budget validation
                                 and may require re-selection of COA.
                             </span>
                             <span x-show="lang==='id'">
                                 Perubahan Business Unit akan mempengaruhi validasi budget
                                 dan mungkin memerlukan pemilihan ulang COA.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <img src="{{ asset('images/manual/sppb/edit-header.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <p class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.1 – Edit SPPB Header Section
                             </p>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Edit Detail Items</span>
                             <span x-show="lang==='id'">2.2 Edit Detail Item</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may modify item quantity, notes, location, COA,
                                 or remove and add new items as needed.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat mengubah quantity, catatan, location, COA,
                                 serta menghapus atau menambahkan item baru sesuai kebutuhan.
                             </span>
                         </p>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Deleted items will be permanently removed after submission.
                                 Ensure all changes are reviewed before submitting for approval.
                             </span>
                             <span x-show="lang==='id'">
                                 Item yang dihapus akan terhapus permanen setelah submit.
                                 Pastikan semua perubahan telah diperiksa sebelum diajukan approval.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <img src="{{ asset('images/manual/sppb/edit-detail.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <p class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.2 – Edit SPPB Detail Table
                             </p>
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
                                 All attachments must comply with document requirements.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat menghapus lampiran yang sudah ada dan
                                 mengunggah file baru. Semua lampiran harus sesuai
                                 dengan ketentuan dokumen.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Minimum 1 attachment is required.
                                 Each file must not exceed 5MB.
                             </span>
                             <span x-show="lang==='id'">
                                 Minimal 1 lampiran wajib diunggah.
                                 Setiap file tidak boleh melebihi 5MB.
                             </span>
                         </div>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <img src="{{ asset('images/manual/sppb/edit-attachment.png') }}"
                                 class="rounded-lg border shadow dark:border-gray-800">
                             <p class="mt-2 text-center text-xs text-gray-500">
                                 Figure 2.3 – Edit Attachments Section
                             </p>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Submit or Cancel Document</span>
                             <span x-show="lang==='id'">2.4 Submit atau Cancel Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing revisions, users may resubmit the document
                                 for approval or cancel the document if no longer required.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah melakukan revisi, pengguna dapat mengajukan kembali
                                 dokumen untuk approval atau membatalkan dokumen
                                 jika sudah tidak diperlukan.
                             </span>
                         </p>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Cancelling a document will stop the approval process permanently.
                             </span>
                             <span x-show="lang==='id'">
                                 Membatalkan dokumen akan menghentikan proses approval secara permanen.
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
                         <span x-show="lang==='en'">3. SPPB List</span>
                         <span x-show="lang==='id'">3. Daftar SPPB</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- OVERVIEW -->
                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The SPPB List page displays all submitted SPPB documents
                             along with their current status and allows users to
                             filter, create, and track document progress.
                         </span>
                         <span x-show="lang==='id'">
                             Halaman Daftar SPPB menampilkan seluruh dokumen SPPB
                             yang telah dibuat beserta status terkini dan memungkinkan
                             pengguna untuk melakukan filter, membuat dokumen baru,
                             serta melakukan tracking progres dokumen.
                         </span>
                     </p>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <img src="{{ asset('images/manual/sppb/list.png') }}"
                             class="rounded-lg border shadow dark:border-gray-800">
                         <p class="mt-2 text-center text-xs text-gray-500">
                             Figure 2.3 – Edit Attachments Section
                         </p>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Status Summary</span>
                             <span x-show="lang==='id'">3.1 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards provide a quick overview of SPPB documents
                                 categorized by their current workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status memberikan ringkasan cepat jumlah dokumen
                                 SPPB berdasarkan status alur proses saat ini.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users may click a status card to filter the list
                                 according to the selected status.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat mengklik kartu status untuk melakukan
                                 filter daftar berdasarkan status yang dipilih.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 SPPB Table</span>
                             <span x-show="lang==='id'">3.2 Tabel SPPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The table displays detailed SPPB information including
                                 Document ID, Date, Company, Department, Request Type,
                                 Description, and Status.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel menampilkan informasi detail SPPB seperti
                                 DocID, Tanggal, Company, Department, Request Type,
                                 Description, dan Status.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The table supports sorting and searching to
                                 quickly locate specific documents.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel mendukung fitur sorting dan pencarian
                                 untuk memudahkan pencarian dokumen tertentu.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Create New SPPB</span>
                             <span x-show="lang==='id'">3.3 Membuat SPPB Baru</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may create a new SPPB document by clicking
                                 the "Create" button located at the top-right section
                                 of the page.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat membuat dokumen SPPB baru dengan
                                 mengklik tombol "Create" yang berada di bagian kanan atas halaman.
                             </span>
                         </p>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Tracking Detail</span>
                             <span x-show="lang==='id'">3.4 Tracking Detail</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The Tracking Detail modal allows users to monitor
                                 the end-to-end process of a document, including
                                 SPPB, CS, PO, and Receipt stages.
                             </span>
                             <span x-show="lang==='id'">
                                 Fitur Tracking Detail memungkinkan pengguna untuk
                                 memantau proses dokumen secara menyeluruh, termasuk
                                 tahap SPPB, CS, PO, hingga Receipt.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Each tab displays header and detail information
                                 related to the selected document stage.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap tab menampilkan informasi header dan detail
                                 sesuai dengan tahapan dokumen yang dipilih.
                             </span>
                         </div>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Tracking data is read-only and cannot be modified
                                 from this page.
                             </span>
                             <span x-show="lang==='id'">
                                 Data tracking bersifat read-only dan tidak dapat
                                 diubah melalui halaman ini.
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

                     <span>
                         <span x-show="lang==='en'">4. Show SPPB</span>
                         <span x-show="lang==='id'">4. Lihat SPPB</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- OVERVIEW -->
                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The Show SPPB page displays complete information of a selected SPPB document,
                             including header data, item details, attachments, approval progress, and comments.
                         </span>
                         <span x-show="lang==='id'">
                             Halaman Show SPPB menampilkan informasi lengkap dari dokumen SPPB yang dipilih,
                             termasuk data header, detail item, lampiran, progres approval, dan komentar.
                         </span>
                     </p>

                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                         <img src="{{ asset('images/manual/sppb/show.png') }}"
                             class="rounded-lg border shadow dark:border-gray-800">
                         <p class="mt-2 text-center text-xs text-gray-500">
                             Figure 4.1 – Show SPPB Details
                         </p>
                     </div>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Approval Actions</span>
                             <span x-show="lang==='id'">4.1 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized users may perform approval actions including Approve,
                                 Revise, or Reject directly from the top section of the page.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna yang berwenang dapat melakukan aksi approval seperti
                                 Approve, Revise, atau Reject langsung dari bagian atas halaman.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Reject and Revise actions require a mandatory reason before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Reject dan Revise wajib mengisi alasan sebelum dikonfirmasi.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 SPPB Header Information</span>
                             <span x-show="lang==='id'">4.2 Informasi Header SPPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The header section contains general document information such as
                                 Company, Department, Date, Request Type, Purpose, and current Status.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian header menampilkan informasi umum dokumen seperti
                                 Company, Department, Tanggal, Request Type, Purpose, dan Status saat ini.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The status badge indicates the current workflow stage of the document.
                             </span>
                             <span x-show="lang==='id'">
                                 Badge status menunjukkan tahapan proses dokumen saat ini.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 Tabs: Attachment, Approval & Comments</span>
                             <span x-show="lang==='id'">4.3 Tab: Attachment, Approval & Komentar</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right panel provides three main tabs:
                                 Attachment, Approval Details, and Comments.
                             </span>
                             <span x-show="lang==='id'">
                                 Panel sebelah kanan menyediakan tiga tab utama:
                                 Attachment, Approval Details, dan Comments.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users may upload additional attachments if permitted.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dapat mengunggah lampiran tambahan jika memiliki izin.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Maximum 10 files per upload. PDF or image format is recommended.
                             </span>
                             <span x-show="lang==='id'">
                                 Maksimal 10 file per upload. Disarankan menggunakan format PDF atau gambar.
                             </span>
                         </div>

                     </section>
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 SPPB Detail</span>
                             <span x-show="lang==='id'">4.4 Detail SPPB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 This section displays item-level information including description,
                                 quantity, unit of measure, location, budget allocation, and
                                 order progress.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian ini menampilkan informasi detail item termasuk deskripsi,
                                 kuantitas, satuan, lokasi, alokasi budget, serta progres pemesanan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Row colors indicate ordering progress:
                                 Blue = Fully Ordered, Yellow = Partially Ordered.
                             </span>
                             <span x-show="lang==='id'">
                                 Warna baris menunjukkan progres pemesanan:
                                 Biru = Fully Ordered, Kuning = Partially Ordered.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 Edit COA</span>
                             <span x-show="lang==='id'">4.5 Ubah COA</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized users (Cost Control Only) may modify COA allocation through the Edit COA
                                 feature.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna (Hanya Cost Control) yang berwenang dapat mengubah alokasi COA melalui fitur
                                 Edit COA.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Ensure the selected Business Unit is correct, as budget validation
                                 is locked based on Business Unit configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Pastikan Business Unit yang dipilih sudah benar, karena validasi
                                 budget terkunci berdasarkan konfigurasi Business Unit.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 COA filtering requires Company, Business Unit, and Financial Department selection.
                             </span>
                             <span x-show="lang==='id'">
                                 Filter COA memerlukan pemilihan Company, Business Unit, dan Department Finance.
                             </span>
                         </div>

                     </section>
                 </div>
             </div>

         </section>

     </div>
