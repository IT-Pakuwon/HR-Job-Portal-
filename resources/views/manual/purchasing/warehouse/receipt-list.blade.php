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
                         <span x-show="lang==='en'">1. Create Receipt</span>
                         <span x-show="lang==='id'">1. Buat Receipt</span>
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
                                 The Create Receipt page is used to record goods received
                                 from a Purchase Order (PO). Users input the received quantity
                                 per item based on the remaining quantity available.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Create Receipt digunakan untuk mencatat barang yang diterima
                                 berdasarkan Purchase Order (PO). User mengisi jumlah yang diterima
                                 per item sesuai dengan quantity sisa yang tersedia.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Receipt quantity cannot exceed the Remaining quantity.
                             </span>
                             <span x-show="lang==='id'">
                                 Quantity Receipt tidak boleh melebihi quantity Remaining.
                             </span>
                         </div>
                     </section>

                     <!-- 1.2 Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Header Information</span>
                             <span x-show="lang==='id'">1.2 Informasi Header</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>PO Nbr</strong> – <span x-show="lang==='en'">Purchase Order number
                                     reference</span><span x-show="lang==='id'">Nomor referensi Purchase Order</span>
                             </li>
                             <li><strong>PO Date</strong> – <span x-show="lang==='en'">Purchase Order date</span><span
                                     x-show="lang==='id'">Tanggal Purchase Order</span></li>
                             <li><strong>SPPB/J/K/T</strong> – <span x-show="lang==='en'">Related SPPB
                                     reference</span><span x-show="lang==='id'">Referensi SPPB terkait</span></li>
                             <li><strong>User Peminta</strong> – <span x-show="lang==='en'">Requesting user</span><span
                                     x-show="lang==='id'">User peminta barang</span></li>
                             <li><strong>Vendor</strong> – <span x-show="lang==='en'">Supplier name</span><span
                                     x-show="lang==='id'">Nama vendor</span></li>
                             <li><strong>Company / Department</strong> – <span x-show="lang==='en'">Organizational
                                     information</span><span x-show="lang==='id'">Informasi perusahaan dan
                                     department</span></li>
                             <li><strong>Receipt Note</strong> – <span x-show="lang==='en'">Optional general
                                     note</span><span x-show="lang==='id'">Catatan umum receipt (opsional)</span></li>
                         </ul>
                     </section>

                     <!-- 1.3 Receipt Detail -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.3 Receipt Detail</span>
                             <span x-show="lang==='id'">1.3 Detail Receipt</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each PO line displays its purchasing and receiving status.
                                 Users must input Qty Receipt per item.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap baris PO menampilkan status pembelian dan penerimaan.
                                 User wajib mengisi Qty Receipt per item.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>PO Qty</strong> – <span x-show="lang==='en'">Ordered quantity</span><span
                                     x-show="lang==='id'">Quantity yang dipesan</span></li>
                             <li><strong>Received</strong> – <span x-show="lang==='en'">Total previously
                                     received</span><span x-show="lang==='id'">Total yang sudah diterima
                                     sebelumnya</span></li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Quantity already
                                     completed/closed</span><span x-show="lang==='id'">Quantity yang sudah
                                     diselesaikan</span></li>
                             <li><strong>Returned</strong> – <span x-show="lang==='en'">Returned quantity</span><span
                                     x-show="lang==='id'">Quantity yang dikembalikan</span></li>
                             <li><strong>Remaining</strong> – <span x-show="lang==='en'">Available quantity to
                                     receive</span><span x-show="lang==='id'">Sisa quantity yang dapat diterima</span>
                             </li>
                             <li><strong>Qty Receipt</strong> – <span x-show="lang==='en'">Input quantity for this
                                     receipt</span><span x-show="lang==='id'">Jumlah yang diterima pada transaksi
                                     ini</span></li>
                             <li><strong>Site</strong> – <span x-show="lang==='en'">Destination storage site</span><span
                                     x-show="lang==='id'">Site tujuan penyimpanan</span></li>
                             <li><strong>Detail Note</strong> – <span x-show="lang==='en'">Optional item
                                     note</span><span x-show="lang==='id'">Catatan per item (opsional)</span></li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Remaining = PO Qty − Net Received − Completed − Returned.
                             </span>
                             <span x-show="lang==='id'">
                                 Remaining = PO Qty − Net Received − Completed − Returned.
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
                                 Users may upload supporting documents such as
                                 delivery notes, invoices, or receiving photos.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mengunggah dokumen pendukung seperti
                                 surat jalan, invoice, atau foto penerimaan barang.
                             </span>
                         </p>
                     </section>

                     <!-- 1.5 Submit Process -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.5 Submit Receipt</span>
                             <span x-show="lang==='id'">1.5 Submit Receipt</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing all required fields,
                                 click "Submit Receipt" to record the transaction.
                                 The system will update stock and PO remaining quantities.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah semua field wajib diisi,
                                 klik "Submit Receipt" untuk mencatat transaksi.
                                 Sistem akan memperbarui stok dan quantity sisa PO.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once submitted, the receipt cannot be edited directly.
                                 Any correction must follow the return or adjustment procedure.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, receipt tidak dapat diedit langsung.
                                 Koreksi harus melalui prosedur return atau adjustment.
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
                         <span x-show="lang==='en'">2. Edit Receipt</span>
                         <span x-show="lang==='id'">2. Edit Receipt</span>
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
                                 The Edit Receipt page allows users to revise a receipt document
                                 that has been returned with status <strong>Revise</strong>.
                                 Only quantity and attachments can be modified.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Edit Receipt digunakan untuk merevisi dokumen receipt
                                 yang dikembalikan dengan status <strong>Revise</strong>.
                                 Hanya quantity dan attachment yang dapat diubah.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Edit is only allowed when receipt status is Revise.
                             </span>
                             <span x-show="lang==='id'">
                                 Edit hanya diperbolehkan ketika status receipt adalah Revise.
                             </span>
                         </div>
                     </section>

                     <!-- 2.2 Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Header Information (Read Only)</span>
                             <span x-show="lang==='id'">2.2 Informasi Header (Read Only)</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Receipt Nbr</strong> – <span x-show="lang==='en'">Receipt document
                                     number</span><span x-show="lang==='id'">Nomor dokumen receipt</span></li>
                             <li><strong>Receipt Date</strong> – <span x-show="lang==='en'">Original receipt
                                     date</span><span x-show="lang==='id'">Tanggal receipt</span></li>
                             <li><strong>PO Nbr</strong> – <span x-show="lang==='en'">Related Purchase
                                     Order</span><span x-show="lang==='id'">Purchase Order terkait</span></li>
                             <li><strong>Company</strong> – <span x-show="lang==='en'">Company code</span><span
                                     x-show="lang==='id'">Kode perusahaan</span></li>
                             <li><strong>Receipt Type</strong> –
                                 <span x-show="lang==='en'">
                                     Determines whether the transaction is a normal receipt or return.
                                 </span>
                                 <span x-show="lang==='id'">
                                     Menentukan apakah transaksi adalah receipt normal atau return.
                                 </span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Header information cannot be modified during revision.
                             </span>
                             <span x-show="lang==='id'">
                                 Informasi header tidak dapat diubah saat revisi.
                             </span>
                         </div>
                     </section>

                     <!-- 2.3 Editable Detail -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Editable Detail</span>
                             <span x-show="lang==='id'">2.3 Detail yang Dapat Diubah</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users may adjust the quantity per line and site location
                                 depending on receipt type.
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat menyesuaikan quantity per baris dan site
                                 sesuai dengan tipe receipt.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Qty Received Edit</strong> –
                                 <span x-show="lang==='en'">Editable when receipt type = Normal</span>
                                 <span x-show="lang==='id'">Dapat diubah jika tipe receipt = Normal</span>
                             </li>
                             <li>
                                 <strong>Qty Return Edit</strong> –
                                 <span x-show="lang==='en'">Editable when receipt type = Return</span>
                                 <span x-show="lang==='id'">Dapat diubah jika tipe receipt = Return</span>
                             </li>
                             <li>
                                 <strong>Site</strong> –
                                 <span x-show="lang==='en'">Can be adjusted if required</span>
                                 <span x-show="lang==='id'">Dapat disesuaikan jika diperlukan</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Edited quantity must follow validation rules and cannot violate stock or PO limits.
                             </span>
                             <span x-show="lang==='id'">
                                 Quantity yang diedit harus mengikuti aturan validasi dan tidak boleh melanggar batas
                                 stok atau PO.
                             </span>
                         </div>
                     </section>

                     <!-- 2.4 Attachment Handling -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 Attachment Management</span>
                             <span x-show="lang==='id'">2.4 Pengelolaan Lampiran</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Existing attachments are displayed and may be removed.
                                 Users may upload new supporting documents if needed.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran yang sudah ada ditampilkan dan dapat dihapus.
                                 User juga dapat menambahkan dokumen pendukung baru.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 All attachment changes will be recorded during submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Semua perubahan lampiran akan tercatat saat submission.
                             </span>
                         </div>
                     </section>

                     <!-- 2.5 Submit Revision -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.5 Submit Approval</span>
                             <span x-show="lang==='id'">2.5 Submit Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing the revision, click "Submit Approval"
                                 to resend the receipt into the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah revisi selesai, klik "Submit Approval"
                                 untuk mengirim ulang receipt ke proses approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once submitted, the receipt status will change and cannot be edited again
                                 unless returned for revision.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, status receipt akan berubah dan tidak dapat diedit lagi
                                 kecuali dikembalikan untuk revisi.
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
                         <span x-show="lang==='en'">3. List Receipts</span>
                         <span x-show="lang==='id'">3. Daftar Receipt</span>

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
                                 The Receipt List page displays all receipt documents
                                 including Purchase Receipts and Return STTB.
                                 Users can monitor document status, filter data,
                                 and access receipt details.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman List Receipt menampilkan seluruh dokumen receipt
                                 termasuk Purchase Receipt dan Return STTB.
                                 User dapat memonitor status dokumen, melakukan filter,
                                 dan mengakses detail receipt.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/receipt/list/overview.png') }}">
                                 <figcaption>
                                     Figure 3.1 – Receipt List Overview
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 3.2 Status Cards -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Status & Scope Filters</span>
                             <span x-show="lang==='id'">3.2 Filter Status & Scope</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The status cards at the top of the page allow users
                                 to filter receipt documents by category and workflow status.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status di bagian atas halaman digunakan untuk
                                 memfilter dokumen receipt berdasarkan kategori dan status workflow.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Purchase Receipt</strong> – <span x-show="lang==='en'">Normal receipt
                                     transactions</span><span x-show="lang==='id'">Transaksi receipt normal</span></li>
                             <li><strong>Return STTB</strong> – <span x-show="lang==='en'">Return receipt
                                     transactions</span><span x-show="lang==='id'">Transaksi return receipt</span></li>
                             <li><strong>On Progress</strong> – <span x-show="lang==='en'">Documents under
                                     approval</span><span x-show="lang==='id'">Dokumen dalam proses approval</span>
                             </li>
                             <li><strong>Rejected</strong> – <span x-show="lang==='en'">Rejected documents</span><span
                                     x-show="lang==='id'">Dokumen ditolak</span></li>
                             <li><strong>Revise</strong> – <span x-show="lang==='en'">Returned for revision</span><span
                                     x-show="lang==='id'">Dikembalikan untuk revisi</span></li>
                             <li><strong>Completed</strong> – <span x-show="lang==='en'">Fully approved
                                     receipts</span><span x-show="lang==='id'">Receipt yang sudah selesai
                                     approval</span></li>
                             <li><strong>All</strong> – <span x-show="lang==='en'">Display all receipts</span><span
                                     x-show="lang==='id'">Menampilkan semua receipt</span></li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Clicking a status card dynamically filters the receipt table below.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik kartu status untuk memfilter tabel receipt di bawah secara otomatis.
                             </span>
                         </div>
                     </section>

                     <!-- 3.3 Receipt Table -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Receipt Table</span>
                             <span x-show="lang==='id'">3.3 Tabel Receipt</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The receipt table displays detailed information
                                 for each receipt document.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel receipt menampilkan informasi detail
                                 untuk setiap dokumen receipt.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Receipt Number</strong></li>
                             <li><strong>Receipt Date</strong></li>
                             <li><strong>PO Number</strong></li>
                             <li><strong>Vendor</strong></li>
                             <li><strong>Company / Department</strong></li>
                             <li><strong>Receipt Type</strong></li>
                             <li><strong>Status</strong></li>
                             <li><strong>Action</strong> – <span x-show="lang==='en'">View / Edit (if
                                     Revise)</span><span x-show="lang==='id'">View / Edit (jika Revise)</span></li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only receipts with status <strong>Revise</strong>
                                 can be edited. Other statuses are read-only.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya receipt dengan status <strong>Revise</strong>
                                 yang dapat diedit. Status lainnya bersifat read-only.
                             </span>
                         </div>
                     </section>

                     <!-- 3.4 Workflow Visibility -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.4 Workflow Visibility</span>
                             <span x-show="lang==='id'">3.4 Visibilitas Workflow</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Receipt status reflects its current position
                                 in the approval workflow.
                             </span>
                             <span x-show="lang==='id'">
                                 Status receipt mencerminkan posisi dokumen
                                 dalam alur approval.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Completed receipts will automatically update stock quantities
                                 and affect inventory balance.
                             </span>
                             <span x-show="lang==='id'">
                                 Receipt yang berstatus Completed akan secara otomatis
                                 memperbarui stok dan mempengaruhi saldo inventory.
                             </span>
                         </div>
                     </section>
                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Show Receipt Details</span>
                         <span x-show="lang==='id'">4. Tampilkan Detail Receipt</span>

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
                                 The Receipt Detail page displays complete information about a receipt document,
                                 including header information, receipt lines, attachments,
                                 approval history, and workflow actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman Detail Receipt menampilkan informasi lengkap mengenai dokumen receipt,
                                 termasuk informasi header, detail item, lampiran,
                                 riwayat approval, dan aksi workflow.
                             </span>
                         </p>

                         <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                             <figure class="manual-figure">
                                 <img src="{{ asset('images/manual/receipt/detail/overview.png') }}">
                                 <figcaption>
                                     Figure 4.1 – Receipt Detail Page
                                 </figcaption>
                             </figure>
                         </div>
                     </section>

                     <!-- 4.2 Header Information -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Receipt Information</span>
                             <span x-show="lang==='id'">4.2 Informasi Receipt</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Receipt Number</strong></li>
                             <li><strong>Status Badge</strong></li>
                             <li><strong>Receipt Date</strong></li>
                             <li><strong>Receipt Type</strong> (Purchase / Return)</li>
                             <li><strong>PO Number</strong> (clickable link)</li>
                             <li><strong>Company & Department</strong></li>
                             <li><strong>Requester</strong></li>
                             <li><strong>Vendor</strong></li>
                             <li><strong>CS & SPPB Reference</strong> (clickable)</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Related documents such as PO, CS, and SPPB can be opened directly
                                 by clicking the reference number.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumen terkait seperti PO, CS, dan SPPB dapat dibuka langsung
                                 dengan mengklik nomor referensinya.
                             </span>
                         </div>
                     </section>

                     <!-- 4.3 Approval Actions -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.3 Approval Actions</span>
                             <span x-show="lang==='id'">4.3 Aksi Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Authorized approvers may perform the following actions:
                             </span>
                             <span x-show="lang==='id'">
                                 Approver yang berwenang dapat melakukan aksi berikut:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li><strong>Approve</strong> – <span x-show="lang==='en'">Approve the receipt and move to
                                     next level</span><span x-show="lang==='id'">Menyetujui receipt dan lanjut ke level
                                     berikutnya</span></li>
                             <li><strong>Revise</strong> – <span x-show="lang==='en'">Return to creator for
                                     correction</span><span x-show="lang==='id'">Mengembalikan ke pembuat untuk
                                     revisi</span></li>
                             <li><strong>Reject</strong> – <span x-show="lang==='en'">Reject and stop
                                     workflow</span><span x-show="lang==='id'">Menolak dan menghentikan proses</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Revise and Reject actions require a mandatory reason before submission.
                             </span>
                             <span x-show="lang==='id'">
                                 Aksi Revise dan Reject wajib mengisi alasan sebelum disubmit.
                             </span>
                         </div>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 When status becomes <strong>Completed</strong>,
                                 stock quantities are officially updated in the inventory system.
                             </span>
                             <span x-show="lang==='id'">
                                 Ketika status menjadi <strong>Completed</strong>,
                                 stok akan diperbarui secara resmi pada sistem inventory.
                             </span>
                         </div>
                     </section>

                     <!-- 4.4 Tabs Section -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.4 Tabs: Attachment, Approval & Comments</span>
                             <span x-show="lang==='id'">4.4 Tab: Attachment, Approval & Comments</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Attachment</strong> –
                                 <span x-show="lang==='en'">View and upload supporting files (if allowed)</span>
                                 <span x-show="lang==='id'">Melihat dan mengunggah dokumen pendukung (jika
                                     diizinkan)</span>
                             </li>
                             <li>
                                 <strong>Approval Details</strong> –
                                 <span x-show="lang==='en'">Display approval level, approver name, date, and
                                     status</span>
                                 <span x-show="lang==='id'">Menampilkan level approval, nama approver, tanggal, dan
                                     status</span>
                             </li>
                             <li>
                                 <strong>Comments</strong> –
                                 <span x-show="lang==='en'">Internal discussion related to this receipt</span>
                                 <span x-show="lang==='id'">Diskusi internal terkait receipt ini</span>
                             </li>
                         </ul>
                     </section>

                     <!-- 4.5 Receipt Detail Table -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.5 Receipt Line Details</span>
                             <span x-show="lang==='id'">4.5 Detail Item Receipt</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The detail table displays item-level receipt information.
                             </span>
                             <span x-show="lang==='id'">
                                 Tabel detail menampilkan informasi receipt per item.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>Inventory ID</li>
                             <li>Description</li>
                             <li>Qty Ordered</li>
                             <li>Qty Received</li>
                             <li>Qty Returned</li>
                             <li>Unit of Measure (UoM)</li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 For Return Receipt, the Qty Returned column will be updated.
                                 For Purchase Receipt, Qty Received will increase stock.
                             </span>
                             <span x-show="lang==='id'">
                                 Untuk Return Receipt, kolom Qty Returned akan bertambah.
                                 Untuk Purchase Receipt, Qty Received akan menambah stok.
                             </span>
                         </div>
                     </section>

                     <!-- 4.6 Print PDF -->
                     <section class="space-y-4">
                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.6 Print Document</span>
                             <span x-show="lang==='id'">4.6 Cetak Dokumen</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Users can print receipt documents via the Print PDF dropdown.
                                 Available formats may include STTB/SPB and BPG (Non Stock).
                             </span>
                             <span x-show="lang==='id'">
                                 User dapat mencetak dokumen receipt melalui dropdown Print PDF.
                                 Format yang tersedia dapat berupa STTB/SPB dan BPG (Non Stock).
                             </span>
                         </p>
                     </section>

                 </div>
             </div>

         </section>


         <!-- ================= SECTION 5 ================= -->
         <section class="space-y-6">
             <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">5. Create Return</span>
                         <span x-show="lang==='id'">5. Buat Return</span>

                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <!-- ================= SECTION 4 ================= -->
                     <section class="space-y-6">
                         <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                             <button @click="toggle('s4')"
                                 class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                                 <span>
                                     <span x-show="lang==='en'">4. Show Receipt Details</span>
                                     <span x-show="lang==='id'">4. Tampilkan Detail Receipt</span>

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
                                             The Receipt Detail page displays complete information about a receipt
                                             document,
                                             including header information, receipt lines, attachments,
                                             approval history, and workflow actions.
                                         </span>
                                         <span x-show="lang==='id'">
                                             Halaman Detail Receipt menampilkan informasi lengkap mengenai dokumen
                                             receipt,
                                             termasuk informasi header, detail item, lampiran,
                                             riwayat approval, dan aksi workflow.
                                         </span>
                                     </p>

                                     <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                                         <figure class="manual-figure">
                                             <img src="{{ asset('images/manual/receipt/detail/overview.png') }}">
                                             <figcaption>
                                                 Figure 4.1 – Receipt Detail Page
                                             </figcaption>
                                         </figure>
                                     </div>
                                 </section>

                                 <!-- 4.2 Header Information -->
                                 <section class="space-y-4">
                                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                         <span x-show="lang==='en'">4.2 Receipt Information</span>
                                         <span x-show="lang==='id'">4.2 Informasi Receipt</span>
                                     </h3>

                                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                                         <li><strong>Receipt Number</strong></li>
                                         <li><strong>Status Badge</strong></li>
                                         <li><strong>Receipt Date</strong></li>
                                         <li><strong>Receipt Type</strong> (Purchase / Return)</li>
                                         <li><strong>PO Number</strong> (clickable link)</li>
                                         <li><strong>Company & Department</strong></li>
                                         <li><strong>Requester</strong></li>
                                         <li><strong>Vendor</strong></li>
                                         <li><strong>CS & SPPB Reference</strong> (clickable)</li>
                                     </ul>

                                     <div class="manual-note manual-info">
                                         <span x-show="lang==='en'">
                                             Related documents such as PO, CS, and SPPB can be opened directly
                                             by clicking the reference number.
                                         </span>
                                         <span x-show="lang==='id'">
                                             Dokumen terkait seperti PO, CS, dan SPPB dapat dibuka langsung
                                             dengan mengklik nomor referensinya.
                                         </span>
                                     </div>
                                 </section>

                                 <!-- 4.3 Approval Actions -->
                                 <section class="space-y-4">
                                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                         <span x-show="lang==='en'">4.3 Approval Actions</span>
                                         <span x-show="lang==='id'">4.3 Aksi Approval</span>
                                     </h3>

                                     <p class="text-gray-600 dark:text-gray-400">
                                         <span x-show="lang==='en'">
                                             Authorized approvers may perform the following actions:
                                         </span>
                                         <span x-show="lang==='id'">
                                             Approver yang berwenang dapat melakukan aksi berikut:
                                         </span>
                                     </p>

                                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                                         <li><strong>Approve</strong> – <span x-show="lang==='en'">Approve the receipt
                                                 and move to
                                                 next level</span><span x-show="lang==='id'">Menyetujui receipt dan
                                                 lanjut ke level
                                                 berikutnya</span></li>
                                         <li><strong>Revise</strong> – <span x-show="lang==='en'">Return to creator for
                                                 correction</span><span x-show="lang==='id'">Mengembalikan ke pembuat
                                                 untuk
                                                 revisi</span></li>
                                         <li><strong>Reject</strong> – <span x-show="lang==='en'">Reject and stop
                                                 workflow</span><span x-show="lang==='id'">Menolak dan menghentikan
                                                 proses</span>
                                         </li>
                                     </ul>

                                     <div class="manual-note manual-warning">
                                         <span x-show="lang==='en'">
                                             Revise and Reject actions require a mandatory reason before submission.
                                         </span>
                                         <span x-show="lang==='id'">
                                             Aksi Revise dan Reject wajib mengisi alasan sebelum disubmit.
                                         </span>
                                     </div>

                                     <div class="manual-note manual-important">
                                         <span x-show="lang==='en'">
                                             When status becomes <strong>Completed</strong>,
                                             stock quantities are officially updated in the inventory system.
                                         </span>
                                         <span x-show="lang==='id'">
                                             Ketika status menjadi <strong>Completed</strong>,
                                             stok akan diperbarui secara resmi pada sistem inventory.
                                         </span>
                                     </div>
                                 </section>

                                 <!-- 4.4 Tabs Section -->
                                 <section class="space-y-4">
                                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                         <span x-show="lang==='en'">4.4 Tabs: Attachment, Approval & Comments</span>
                                         <span x-show="lang==='id'">4.4 Tab: Attachment, Approval & Comments</span>
                                     </h3>

                                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                                         <li>
                                             <strong>Attachment</strong> –
                                             <span x-show="lang==='en'">View and upload supporting files (if
                                                 allowed)</span>
                                             <span x-show="lang==='id'">Melihat dan mengunggah dokumen pendukung (jika
                                                 diizinkan)</span>
                                         </li>
                                         <li>
                                             <strong>Approval Details</strong> –
                                             <span x-show="lang==='en'">Display approval level, approver name, date,
                                                 and
                                                 status</span>
                                             <span x-show="lang==='id'">Menampilkan level approval, nama approver,
                                                 tanggal, dan
                                                 status</span>
                                         </li>
                                         <li>
                                             <strong>Comments</strong> –
                                             <span x-show="lang==='en'">Internal discussion related to this
                                                 receipt</span>
                                             <span x-show="lang==='id'">Diskusi internal terkait receipt ini</span>
                                         </li>
                                     </ul>
                                 </section>

                                 <!-- 4.5 Receipt Detail Table -->
                                 <section class="space-y-4">
                                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                         <span x-show="lang==='en'">4.5 Receipt Line Details</span>
                                         <span x-show="lang==='id'">4.5 Detail Item Receipt</span>
                                     </h3>

                                     <p class="text-gray-600 dark:text-gray-400">
                                         <span x-show="lang==='en'">
                                             The detail table displays item-level receipt information.
                                         </span>
                                         <span x-show="lang==='id'">
                                             Tabel detail menampilkan informasi receipt per item.
                                         </span>
                                     </p>

                                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                                         <li>Inventory ID</li>
                                         <li>Description</li>
                                         <li>Qty Ordered</li>
                                         <li>Qty Received</li>
                                         <li>Qty Returned</li>
                                         <li>Unit of Measure (UoM)</li>
                                     </ul>

                                     <div class="manual-note manual-info">
                                         <span x-show="lang==='en'">
                                             For Return Receipt, the Qty Returned column will be updated.
                                             For Purchase Receipt, Qty Received will increase stock.
                                         </span>
                                         <span x-show="lang==='id'">
                                             Untuk Return Receipt, kolom Qty Returned akan bertambah.
                                             Untuk Purchase Receipt, Qty Received akan menambah stok.
                                         </span>
                                     </div>
                                 </section>

                                 <!-- 4.6 Print PDF -->
                                 <section class="space-y-4">
                                     <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                         <span x-show="lang==='en'">4.6 Print Document</span>
                                         <span x-show="lang==='id'">4.6 Cetak Dokumen</span>
                                     </h3>

                                     <p class="text-gray-600 dark:text-gray-400">
                                         <span x-show="lang==='en'">
                                             Users can print receipt documents via the Print PDF dropdown.
                                             Available formats may include STTB/SPB and BPG (Non Stock).
                                         </span>
                                         <span x-show="lang==='id'">
                                             User dapat mencetak dokumen receipt melalui dropdown Print PDF.
                                             Format yang tersedia dapat berupa STTB/SPB dan BPG (Non Stock).
                                         </span>
                                     </p>
                                 </section>

                             </div>
                         </div>

                     </section>
                 </div>
             </div>

         </section>

     </div>
