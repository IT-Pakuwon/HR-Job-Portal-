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
                         <span x-show="lang==='en'">1. Overview</span>
                         <span x-show="lang==='id'">1. Gambaran Umum</span>
                     </span>

                     <span x-text="openSection==='s1' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Receive Product is where incoming voucher or product stock is formally recorded
                             before it can be used anywhere else in the VPL module. A Receive document lists the
                             products, quantities, expiry dates, and destination warehouse for a single intake —
                             for example a batch of vouchers received from a Media Promo or Rental source. Stock
                             is only added to the warehouse once the document is fully approved.
                         </span>
                         <span x-show="lang==='id'">
                             Receive Product adalah tempat pencatatan resmi stok voucher atau produk yang masuk
                             sebelum dapat digunakan di bagian lain modul VPL. Satu dokumen Receive mencatat
                             produk, jumlah, tanggal kedaluwarsa, dan gudang tujuan untuk satu kali penerimaan —
                             misalnya satu batch voucher yang diterima dari sumber Media Promo atau Rental. Stok
                             baru ditambahkan ke gudang setelah dokumen disetujui sepenuhnya.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Overview</span>
                             <span x-show="lang==='id'">1.1 Ringkasan Status</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>On Progress</strong> —
                                 <span x-show="lang==='en'">Submitted and going through the approval chain.</span>
                                 <span x-show="lang==='id'">Sudah disubmit dan sedang melalui rantai approval.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">Fully approved — the stock has been posted into the destination warehouse.</span>
                                 <span x-show="lang==='id'">Sudah disetujui sepenuhnya — stok telah diposting ke gudang tujuan.</span>
                             </li>
                             <li>
                                 <strong>Hold / Revise</strong> —
                                 <span x-show="lang==='en'">An approver sent it back for correction. The creator can edit and re-submit, or cancel it.</span>
                                 <span x-show="lang==='id'">Dikembalikan oleh approver untuk diperbaiki. Pembuat dokumen dapat mengedit dan submit ulang, atau membatalkannya.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> —
                                 <span x-show="lang==='en'">Rejected by an approver; the document is closed.</span>
                                 <span x-show="lang==='id'">Ditolak oleh approver; dokumen ditutup.</span>
                             </li>
                             <li>
                                 <strong>Cancelled</strong> —
                                 <span x-show="lang==='en'">Cancelled by its creator before any approval step was approved.</span>
                                 <span x-show="lang==='id'">Dibatalkan oleh pembuatnya sebelum ada langkah approval yang disetujui.</span>
                             </li>
                         </ul>

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
                         <span x-show="lang==='en'">2. Submitting a Receive Request</span>
                         <span x-show="lang==='id'">2. Mengajukan Permintaan Receive</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>New Receive</strong> to open the form. Once submitted, the document
                             immediately enters the approval chain — there is no draft mode.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>New Receive</strong> untuk membuka form. Setelah disubmit, dokumen
                             langsung masuk ke rantai approval — tidak ada mode draft.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Company / Department</span>
                                 <span x-show="lang==='id'">Company / Department</span>
                             </strong> —
                             <span x-show="lang==='en'">The company and department recording this receipt. Access to this combination is validated before submission.</span>
                             <span x-show="lang==='id'">Perusahaan dan departemen yang mencatat penerimaan ini. Akses terhadap kombinasi ini divalidasi sebelum disubmit.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Voucher / Product Type</span>
                                 <span x-show="lang==='id'">Voucher / Product Type</span>
                             </strong> —
                             <span x-show="lang==='en'">Whether this document receives Voucher or Product stock.</span>
                             <span x-show="lang==='id'">Apakah dokumen ini menerima stok Voucher atau Product.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Source of Receive</span>
                                 <span x-show="lang==='id'">Source of Receive</span>
                             </strong> —
                             <span x-show="lang==='en'">Where the stock is coming from (e.g. Media Promo, Event, Promotion Levy, Rental) — this also decides the approval chain.</span>
                             <span x-show="lang==='id'">Dari mana stok ini berasal (misalnya Media Promo, Event, Promotion Levy, Rental) — ini juga menentukan rantai approval.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Department of Receive</span>
                                 <span x-show="lang==='id'">Department of Receive</span>
                             </strong> —
                             <span x-show="lang==='en'">Which business line the stock belongs to: Casual Leasing, Leasing, or Promotion.</span>
                             <span x-show="lang==='id'">Lini bisnis pemilik stok tersebut: Casual Leasing, Leasing, atau Promotion.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Remark</span>
                                 <span x-show="lang==='id'">Remark</span>
                             </strong> —
                             <span x-show="lang==='en'">Required explanation of the receipt.</span>
                             <span x-show="lang==='id'">Penjelasan penerimaan, wajib diisi.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Detail Lines</span>
                                 <span x-show="lang==='id'">Detail Lines</span>
                             </strong> —
                             <span x-show="lang==='en'">Add one row per product: Product, Qty, Expired Date, and destination Warehouse. At least one valid line is required.</span>
                             <span x-show="lang==='id'">Tambahkan satu baris per produk: Product, Qty, Expired Date, dan Warehouse tujuan. Minimal satu baris yang valid wajib diisi.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Remark, at least one valid product line, and an attachment are all required — the
                             form cannot be submitted without them. The Expired Date on any line cannot be set
                             to a date before today.
                         </span>
                         <span x-show="lang==='id'">
                             Remark, minimal satu baris produk yang valid, dan lampiran semuanya wajib diisi —
                             form tidak dapat disubmit tanpa itu. Expired Date pada baris mana pun tidak boleh
                             diisi dengan tanggal sebelum hari ini.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. Approval &amp; Status Tracking</span>
                         <span x-show="lang==='id'">3. Approval &amp; Memantau Status</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Open the document from the list to see its approval timeline and leave or read
                             messages with the approver. When the document reaches your approval level, you can
                             <strong>Approve</strong>, <strong>Revise</strong> (returns it to Hold with a required
                             comment), or <strong>Reject</strong> (closes it, comment required).
                         </span>
                         <span x-show="lang==='id'">
                             Buka dokumen dari daftar untuk melihat timeline approval serta mengirim atau membaca
                             pesan dengan approver. Saat dokumen mencapai level approval Anda, Anda dapat memilih
                             <strong>Approve</strong>, <strong>Revise</strong> (mengembalikan ke status Hold
                             dengan komentar wajib), atau <strong>Reject</strong> (menutup dokumen, komentar
                             wajib).
                         </span>
                     </p>

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             Stock is only added to the destination warehouse once the document reaches
                             <strong>Completed</strong> (the last required approval). Nothing is posted while a
                             document is still On Progress.
                         </span>
                         <span x-show="lang==='id'">
                             Stok baru ditambahkan ke gudang tujuan setelah dokumen mencapai status
                             <strong>Completed</strong> (approval terakhir yang disyaratkan). Belum ada stok yang
                             terposting selama dokumen masih berstatus On Progress.
                         </span>
                     </div>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             As the creator, you may <strong>Cancel</strong> the document while it is On Progress
                             and no approval step has been approved yet, or at any time while it is on Hold. You
                             may <strong>Edit</strong> and re-submit only while it is on Hold.
                         </span>
                         <span x-show="lang==='id'">
                             Sebagai pembuat dokumen, Anda dapat melakukan <strong>Cancel</strong> selama dokumen
                             masih On Progress dan belum ada langkah approval yang disetujui, atau kapan pun
                             selama berstatus Hold. Anda hanya dapat melakukan <strong>Edit</strong> dan submit
                             ulang selama dokumen berstatus Hold.
                         </span>
                     </p>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Attachments &amp; Printing</span>
                         <span x-show="lang==='id'">4. Lampiran &amp; Mencetak</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The creator can add further attachments from the document detail page, for any
                             status except Cancelled, Rejected, or Hold. Click <strong>Print PDF</strong> to
                             generate a printable copy of the document, including its approval signatures.
                         </span>
                         <span x-show="lang==='id'">
                             Pembuat dokumen dapat menambahkan lampiran lain dari halaman detail dokumen, untuk
                             semua status kecuali Cancelled, Rejected, atau Hold. Klik <strong>Print PDF</strong>
                             untuk menghasilkan salinan dokumen yang dapat dicetak, termasuk tanda tangan
                             approval-nya.
                         </span>
                     </p>

                 </div>
             </div>

         </section>

     </div>
