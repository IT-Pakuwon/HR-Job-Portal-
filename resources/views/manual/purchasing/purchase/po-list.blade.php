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
                         <span x-show="lang==='en'">1. List PO</span>
                         <span x-show="lang==='id'">1. Daftar PO</span>
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
                                 The Purchase Order (PO) List page displays all PO documents
                                 available to the user based on role and access rights.
                                 Users can view, filter, and open PO records for further processing.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Daftar Purchase Order (PO) menampilkan seluruh dokumen PO
                                 yang tersedia sesuai dengan role dan hak akses user.
                                 User dapat melihat, memfilter, dan membuka dokumen PO untuk diproses lebih lanjut.
                             </span>
                         </p>
                     </section>

                     <!-- 1.2 PO Tabs -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 PO Tabs (My PO & All PO)</span>
                             <span x-show="lang==='id'">1.2 Tab PO (My PO & All PO)</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>My PO</strong> –
                                 <span x-show="lang==='en'">
                                     Displays POs created or assigned to the current user.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Menampilkan PO yang dibuat atau ditugaskan kepada user saat ini.
                                 </span>
                             </li>
                             <li>
                                 <strong>All PO</strong> –
                                 <span x-show="lang==='en'">
                                     Displays all accessible PO documents based on user authorization.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Menampilkan seluruh PO yang dapat diakses berdasarkan otorisasi user.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The "My PO" tab may not be available for Finance users,
                                 depending on system configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Tab "My PO" mungkin tidak tersedia untuk user Finance,
                                 tergantung pada konfigurasi sistem.
                             </span>
                         </div>
                     </section>

                     <!-- 1.3 Filters -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Filters</span>
                             <span x-show="lang==='id'">1.3 Filter</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users can refine the PO list using the available filters:
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mempersempit daftar PO menggunakan filter yang tersedia:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Company</strong> –
                                 <span x-show="lang==='en'">
                                     Filter PO by company.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Memfilter PO berdasarkan company.
                                 </span>
                             </li>
                             <li>
                                 <strong>Status</strong> (My PO only) –
                                 <span x-show="lang==='en'">
                                     Filter PO based on workflow status.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Memfilter PO berdasarkan status workflow.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Status filter is only available in the "My PO" tab.
                             </span>
                             <span x-show="lang==='id'">
                                 Filter status hanya tersedia pada tab "My PO".
                             </span>
                         </div>
                     </section>

                     <!-- 1.4 PO Status Definition -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.4 PO Status Definition</span>
                             <span x-show="lang==='id'">1.4 Definisi Status PO</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>H – Unsend</strong>
                                 <span x-show="lang==='en'">PO has not been sent to vendor.</span>
                                 <span x-show="lang==='id'">PO belum dikirim ke vendor.</span>
                             </li>
                             <li><strong>P – Purchase</strong>
                                 <span x-show="lang==='en'">PO has been sent and is active.</span>
                                 <span x-show="lang==='id'">PO telah dikirim dan aktif.</span>
                             </li>
                             <li><strong>O – Partial</strong>
                                 <span x-show="lang==='en'">Partially delivered or partially received.</span>
                                 <span x-show="lang==='id'">Sebagian telah dikirim atau diterima.</span>
                             </li>
                             <li><strong>C – Completed</strong>
                                 <span x-show="lang==='en'">PO fully completed.</span>
                                 <span x-show="lang==='id'">PO telah selesai sepenuhnya.</span>
                             </li>
                             <li><strong>X – Canceled</strong>
                                 <span x-show="lang==='en'">PO has been canceled.</span>
                                 <span x-show="lang==='id'">PO telah dibatalkan.</span>
                             </li>
                             <li><strong>D – Reuse</strong>
                                 <span x-show="lang==='en'">PO reused or duplicated.</span>
                                 <span x-show="lang==='id'">PO digunakan kembali atau diduplikasi.</span>
                             </li>
                         </ul>
                     </section>

                     <!-- 1.5 PO Table Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 PO Table Information</span>
                             <span x-show="lang==='id'">1.5 Informasi Tabel PO</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The PO table displays key information for each record:
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel PO menampilkan informasi utama untuk setiap dokumen:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>PO Number</li>
                             <li>PO Date</li>
                             <li>Company</li>
                             <li>PO Type</li>
                             <li>Vendor</li>
                             <li>Delivery Date</li>
                             <li>Purpose</li>
                             <li>Grand Total</li>
                             <li>Created By</li>
                             <li>Status</li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Access to open or modify a PO depends on user role,
                                 company authorization, and document status.
                             </span>
                             <span x-show="lang==='id'">
                                 Akses untuk membuka atau memodifikasi PO bergantung pada role user,
                                 otorisasi company, dan status dokumen.
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
                         <span x-show="lang==='en'">2. Show PO Details</span>
                         <span x-show="lang==='id'">2. Tampilkan Detail PO</span>
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
                                 The Show PO page displays detailed Purchase Order (PO/SPK) information,
                                 including status, financial summary, attachment, comments,
                                 item details, and receipt tracking.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Show PO menampilkan detail lengkap dokumen Purchase Order (PO/SPK),
                                 termasuk status, ringkasan finansial, attachment, komentar,
                                 detail item, serta tracking penerimaan barang.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Editing capability depends on PO status and ownership.
                                 Once processed, most fields become read-only.
                             </span>
                             <span x-show="lang==='id'">
                                 Hak edit bergantung pada status PO dan kepemilikan dokumen.
                                 Setelah diproses, sebagian besar field menjadi read-only.
                             </span>
                         </div>
                     </section>

                     <!-- 2.2 Status-Based Behavior -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Status-Based Behavior</span>
                             <span x-show="lang==='id'">2.2 Perilaku Berdasarkan Status</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>H – Hold</strong>
                                 <span x-show="lang==='en'">
                                     PO is still editable by the owner. Delivery date, SPK working configuration,
                                     and attachments can be modified.
                                 </span>
                                 <span x-show="lang==='id'">
                                     PO masih dapat diedit oleh pembuatnya. Delivery date,
                                     konfigurasi kerja SPK, dan attachment masih bisa diubah.
                                 </span>
                             </li>

                             <li>
                                 <strong>P – Purchase Order</strong>
                                 <span x-show="lang==='en'">
                                     PO has been processed. Most fields become read-only.
                                     Delivery date may no longer be editable.
                                 </span>
                                 <span x-show="lang==='id'">
                                     PO telah diproses. Sebagian besar field menjadi read-only.
                                     Delivery date biasanya sudah tidak dapat diubah.
                                 </span>
                             </li>

                             <li>
                                 <strong>O – Partial Release</strong>
                                 <span x-show="lang==='en'">
                                     Partial receipt has been recorded. PO is locked structurally.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Sudah ada penerimaan sebagian. Struktur PO terkunci.
                                 </span>
                             </li>

                             <li>
                                 <strong>C – Completed</strong>
                                 <span x-show="lang==='en'">
                                     All items have been received. PO becomes fully read-only.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Semua item telah diterima. PO sepenuhnya read-only.
                                 </span>
                             </li>

                             <li>
                                 <strong>X – Canceled</strong>
                                 <span x-show="lang==='en'">
                                     PO is canceled and cannot be modified.
                                 </span>
                                 <span x-show="lang==='id'">
                                     PO telah dibatalkan dan tidak dapat dimodifikasi.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once receipt (STTB) exists, item structure and quantity
                                 can no longer be edited to maintain audit integrity.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika sudah terdapat receipt (STTB), struktur item dan quantity
                                 tidak dapat diubah demi menjaga integritas audit.
                             </span>
                         </div>
                     </section>

                     <!-- 2.3 Available Actions -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Available Actions</span>
                             <span x-show="lang==='id'">2.3 Aksi yang Tersedia</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Submit</strong> –
                                 <span x-show="lang==='en'">
                                     Available when status = Hold. Moves PO to active process.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Tersedia saat status = Hold. Mengirim PO ke proses aktif.
                                 </span>
                             </li>

                             <li>
                                 <strong>Cancel</strong> –
                                 <span x-show="lang==='en'">
                                     Cancels PO with mandatory reason input.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Membatalkan PO dengan alasan wajib diisi.
                                 </span>
                             </li>

                             <li>
                                 <strong>Reuse</strong> –
                                 <span x-show="lang==='en'">
                                     Duplicate or reuse PO structure for new process.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Menggunakan ulang struktur PO untuk proses baru.
                                 </span>
                             </li>

                             <li>
                                 <strong>Completed</strong> –
                                 <span x-show="lang==='en'">
                                     Available if receipt exists and quantity received > 0.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Tersedia jika sudah ada receipt dan qty received > 0.
                                 </span>
                             </li>

                             <li>
                                 <strong>Print PDF</strong> –
                                 <span x-show="lang==='en'">
                                     Generate official PO/SPK PDF document.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Mencetak dokumen resmi PO/SPK dalam format PDF.
                                 </span>
                             </li>
                         </ul>
                     </section>

                     <!-- 2.4 PO Detail & Receipt Tracking -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 PO Detail & STTB Tracking</span>
                             <span x-show="lang==='id'">2.4 Detail PO & Tracking STTB</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The lower section contains two tabs:
                                 PO Detail and STTB Tracking.
                             </span>
                             <span x-show="lang==='id'">
                                 Bagian bawah terdiri dari dua tab:
                                 PO Detail dan STTB Tracking.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>PO Detail</strong> –
                                 <span x-show="lang==='en'">
                                     Displays item list, budget mapping, quantity, unit cost,
                                     tax amount, total cost, and quantity received.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Menampilkan daftar item, mapping budget, quantity,
                                     unit cost, pajak, total cost, dan qty received.
                                 </span>
                             </li>

                             <li>
                                 <strong>STTB Tracking</strong> –
                                 <span x-show="lang==='en'">
                                     Shows receipt history including receipt number,
                                     date, quantity received, return quantity, and status.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Menampilkan riwayat penerimaan termasuk nomor receipt,
                                     tanggal, qty diterima, qty return, dan status.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Receipt history cannot be modified from PO screen.
                                 Changes must be performed via Receipt module.
                             </span>
                             <span x-show="lang==='id'">
                                 Riwayat receipt tidak dapat diubah dari halaman PO.
                                 Perubahan harus dilakukan melalui modul Receipt.
                             </span>
                         </div>
                     </section>

                     <!-- 2.5 Attachment & Comments -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.5 Attachment & Comments</span>
                             <span x-show="lang==='id'">2.5 Attachment & Komentar</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Attachment</strong> –
                                 <span x-show="lang==='en'">
                                     Files can only be uploaded when PO status = Hold.
                                 </span>
                                 <span x-show="lang==='id'">
                                     File hanya dapat diunggah saat status PO = Hold.
                                 </span>
                             </li>

                             <li>
                                 <strong>Comments</strong> –
                                 <span x-show="lang==='en'">
                                     Used for communication and internal discussion.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Digunakan untuk komunikasi dan diskusi internal.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 After PO is submitted and processed, attachment upload
                                 may be restricted depending on system configuration.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah PO disubmit dan diproses, upload attachment
                                 dapat dibatasi sesuai konfigurasi sistem.
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
                         <span x-show="lang==='en'">3. Process PO</span>
                         <span x-show="lang==='id'">3. Proses PO</span>

                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">
                     <!-- 3.1 MODE CONCEPT -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Right Panel Behavior</span>
                             <span x-show="lang==='id'">3.1 Perilaku Panel Kanan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The right panel inside Show PO operates in two modes:
                                 Editable Mode (Process Mode) and Read-Only Mode.
                                 The system automatically determines which mode is active.
                             </span>
                             <span x-show="lang==='id'">
                                 Panel kanan pada halaman Show PO memiliki dua mode:
                                 Mode Edit (Mode Proses) dan Mode Baca.
                                 Sistem secara otomatis menentukan mode yang aktif.
                             </span>
                         </p>
                     </section>

                     <!-- 3.2 WHEN EDITABLE -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 When Editable Mode is Active</span>
                             <span x-show="lang==='id'">3.2 Kapan Mode Edit Aktif</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Status = <strong>H (Hold)</strong></li>
                             <li>User is document <strong>Owner</strong></li>
                             <li>No structural lock from receipt impact</li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 If status changes to P (Purchase) or above,
                                 Editable Mode is permanently disabled.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika status berubah menjadi P (Purchase) atau lebih,
                                 Mode Edit akan otomatis dinonaktifkan.
                             </span>
                         </div>
                     </section>

                     <!-- 3.3 PO INPUT RULES -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             PO – Input Rules
                         </h3>
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 PO Input Rules</span>
                             <span x-show="lang==='id'">3.3 Aturan Input PO</span>
                         </h3>
                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Delivery Date</strong>
                                 <ul class="list-disc pl-6">
                                     <li>Required before submit</li>
                                     <li>Cannot be past date (recommended rule)</li>
                                     <li>Locked after status = P</li>
                                 </ul>
                             </li>

                             <li>
                                 <strong>Term of Payment</strong>
                                 <ul class="list-disc pl-6">
                                     <li>System reference from master TOP</li>
                                     <li>Not editable from this panel</li>
                                 </ul>
                             </li>
                         </ul>
                     </section>

                     <!-- 3.4 SPK INPUT RULES -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 SPK – Input Rules (Mandatory Before Submit) </span>
                             <span x-show="lang==='id'">3.4 Aturan Input SPK (Wajib Sebelum Submit)</span>
                         </h3>
                         <div class="manual-note manual-warning">
                             SPK requires complete working configuration before submission.
                         </div>

                         <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">

                             <li>
                                 <strong>Working Day Rule</strong>
                                 <ul class="list-disc pl-6">
                                     <li>Include or Exclude weekends</li>
                                     <li>Affects auto calculation of total days</li>
                                 </ul>
                             </li>

                             <li>
                                 <strong>Work Date From / To</strong>
                                 <ul class="list-disc pl-6">
                                     <li>Both fields required</li>
                                     <li>Date To must be ≥ Date From</li>
                                     <li>System auto-calculates total working days</li>
                                 </ul>
                             </li>

                             <li>
                                 <strong>Working Time</strong>
                                 <ul class="list-disc pl-6">
                                     <li>Time From and Time To required</li>
                                     <li>Or enable 24h option</li>
                                 </ul>
                             </li>

                             <li>
                                 <strong>Manpower</strong>
                                 <ul class="list-disc pl-6">
                                     <li>Must be numeric</li>
                                     <li>Cannot be negative</li>
                                 </ul>
                             </li>

                             <li>
                                 <strong>Warranty</strong>
                                 <ul class="list-disc pl-6">
                                     <li>Text input</li>
                                     <li>Recommended format: “30 Days” / “6 Months”</li>
                                 </ul>
                             </li>

                             <li>
                                 <strong>PIC Name & Phone</strong>
                                 <ul class="list-disc pl-6">
                                     <li>PIC Name required</li>
                                     <li>Phone optional but recommended</li>
                                 </ul>
                             </li>

                         </ul>

                         <div class="manual-note manual-important">
                             If any mandatory SPK field is empty,
                             system should prevent Submit action.
                         </div>
                     </section>


                     <!-- 3.6 SYSTEM LOCKING LOGIC -->
                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 System Locking Matrix</span>
                             <span x-show="lang==='id'">3.4 Matriks Kunci Sistem</span>
                         </h3>


                         <table class="w-full border border-gray-200 text-sm dark:border-gray-700">
                             <thead class="bg-gray-100 dark:bg-gray-700">
                                 <tr>
                                     <th class="p-2 text-left">Status</th>
                                     <th class="p-2 text-left">Owner</th>
                                     <th class="p-2 text-left">Editable?</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr class="border-t">
                                     <td class="p-2">H</td>
                                     <td class="p-2">Yes</td>
                                     <td class="p-2">Yes</td>
                                 </tr>
                                 <tr class="border-t">
                                     <td class="p-2">H</td>
                                     <td class="p-2">No</td>
                                     <td class="p-2">No</td>
                                 </tr>
                                 <tr class="border-t">
                                     <td class="p-2">P / O / C / X</td>
                                     <td class="p-2">Any</td>
                                     <td class="p-2">No</td>
                                 </tr>
                             </tbody>
                         </table>

                         <div class="manual-note manual-important">
                             System enforces automatic UI switching to protect financial
                             and operational integrity.
                         </div>
                     </section>

                 </div>
             </div>

         </section>

     </div>
