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
                             Transfer Product moves voucher or product stock between warehouses within the same
                             company — typically from the department's central/collection warehouse out to the
                             warehouse it actually hands stock out from, or back again. There are two transfer
                             types: <strong>Transfer</strong> (central → department) and
                             <strong>Return Transfer</strong> (department → central), which references the
                             original Transfer document being reversed.
                         </span>
                         <span x-show="lang==='id'">
                             Transfer Product memindahkan stok voucher atau produk antar gudang dalam satu
                             perusahaan — biasanya dari gudang pusat/koleksi milik departemen ke gudang tempat
                             stok tersebut benar-benar dikeluarkan, atau sebaliknya. Terdapat dua jenis transfer:
                             <strong>Transfer</strong> (pusat → departemen) dan <strong>Return Transfer</strong>
                             (departemen → pusat), yang mengacu pada dokumen Transfer asal yang dibalik.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             From/To Warehouse are not chosen freely — they are resolved automatically from the
                             Company, Department, and Voucher/Product Type you select, based on each
                             department's registered warehouses.
                         </span>
                         <span x-show="lang==='id'">
                             From/To Warehouse tidak dipilih bebas — keduanya ditentukan otomatis berdasarkan
                             Company, Department, dan Voucher/Product Type yang dipilih, sesuai gudang yang
                             terdaftar untuk departemen tersebut.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Overview</span>
                             <span x-show="lang==='id'">1.1 Ringkasan Status</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>On Progress</strong> —
                                 <span x-show="lang==='en'">Submitted and going through the approval chain; the stock being transferred is held (reserved) in the source warehouse.</span>
                                 <span x-show="lang==='id'">Sudah disubmit dan sedang melalui rantai approval; stok yang ditransfer ditahan (reserved) di gudang asal.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">Fully approved — stock has moved from the source warehouse to the destination warehouse.</span>
                                 <span x-show="lang==='id'">Sudah disetujui sepenuhnya — stok telah berpindah dari gudang asal ke gudang tujuan.</span>
                             </li>
                             <li>
                                 <strong>Hold / Revise</strong> —
                                 <span x-show="lang==='en'">Returned by an approver for correction.</span>
                                 <span x-show="lang==='id'">Dikembalikan oleh approver untuk diperbaiki.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> /
                                 <strong>Cancelled</strong> —
                                 <span x-show="lang==='en'">Closed without the stock moving.</span>
                                 <span x-show="lang==='id'">Ditutup tanpa perpindahan stok.</span>
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
                         <span x-show="lang==='en'">2. Submitting a Transfer</span>
                         <span x-show="lang==='id'">2. Mengajukan Transfer</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>New Transfer</strong> to open the form. Submitting immediately enters
                             the approval chain — there is no draft mode.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>New Transfer</strong> untuk membuka form. Setelah disubmit, dokumen
                             langsung masuk ke rantai approval — tidak ada mode draft.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Company / Department</span>
                                 <span x-show="lang==='id'">Company / Department</span>
                             </strong> —
                             <span x-show="lang==='en'">The company and department performing the transfer.</span>
                             <span x-show="lang==='id'">Perusahaan dan departemen yang melakukan transfer.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">V/P Type</span>
                                 <span x-show="lang==='id'">V/P Type</span>
                             </strong> —
                             <span x-show="lang==='en'">Voucher or Product.</span>
                             <span x-show="lang==='id'">Voucher atau Product.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Transfer Type</span>
                                 <span x-show="lang==='id'">Transfer Type</span>
                             </strong> —
                             <span x-show="lang==='en'"><strong>Transfer (Central → Dept)</strong> for a normal issue, or <strong>Return Transfer (Dept → Central)</strong> to send unused stock back.</span>
                             <span x-show="lang==='id'"><strong>Transfer (Central → Dept)</strong> untuk pengeluaran stok normal, atau <strong>Return Transfer (Dept → Central)</strong> untuk mengembalikan stok yang tidak terpakai.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Reference Transfer</span>
                                 <span x-show="lang==='id'">Reference Transfer</span>
                             </strong> —
                             <span x-show="lang==='en'">Required for a Return Transfer — the original Transfer document being returned. Return quantity cannot exceed what that document still has outstanding.</span>
                             <span x-show="lang==='id'">Wajib diisi untuk Return Transfer — dokumen Transfer asal yang dikembalikan. Qty return tidak boleh melebihi sisa yang masih bisa dikembalikan dari dokumen tersebut.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Remark</span>
                                 <span x-show="lang==='id'">Remark</span>
                             </strong> —
                             <span x-show="lang==='en'">Required explanation of the transfer.</span>
                             <span x-show="lang==='id'">Penjelasan transfer, wajib diisi.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Transfer Details</span>
                                 <span x-show="lang==='id'">Transfer Details</span>
                             </strong> —
                             <span x-show="lang==='en'">One row per product, showing From WHS, Product, Available Qty, Expired Date, Qty Transfer, and To WHS. Qty Transfer cannot exceed the available quantity shown.</span>
                             <span x-show="lang==='id'">Satu baris per produk, menampilkan From WHS, Product, Available Qty, Expired Date, Qty Transfer, dan To WHS. Qty Transfer tidak boleh melebihi jumlah yang tersedia.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             If your department has no registered transfer warehouse for the selected company
                             and type, a warning is shown and the form cannot be submitted. Remark, at least one
                             valid product line, and an attachment are all required.
                         </span>
                         <span x-show="lang==='id'">
                             Jika departemen Anda belum memiliki gudang transfer yang terdaftar untuk company
                             dan type yang dipilih, peringatan akan muncul dan form tidak dapat disubmit.
                             Remark, minimal satu baris produk yang valid, dan lampiran semuanya wajib diisi.
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
                         <span x-show="lang==='en'">3. Stock Hold &amp; Approval Flow</span>
                         <span x-show="lang==='id'">3. Penahanan Stok &amp; Alur Approval</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             As soon as a Transfer is submitted, the quantity on each line is held (reserved) in
                             the source warehouse, so the same stock cannot be oversold to another document
                             while this one is still pending. The hold is released automatically once the
                             document is Completed, Rejected, or Cancelled.
                         </span>
                         <span x-show="lang==='id'">
                             Begitu Transfer disubmit, jumlah pada setiap baris langsung ditahan (reserved) di
                             gudang asal, sehingga stok yang sama tidak bisa terjual berlebih ke dokumen lain
                             selama dokumen ini masih pending. Penahanan ini dilepas otomatis setelah dokumen
                             berstatus Completed, Rejected, atau Cancelled.
                         </span>
                     </p>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             At each approval step, the system re-checks that the source warehouse still has
                             enough stock before letting the approval go through — <strong>Approve</strong>,
                             <strong>Revise</strong> (comment required), and <strong>Reject</strong> (comment
                             required) work the same way as other VPL documents.
                         </span>
                         <span x-show="lang==='id'">
                             Pada setiap langkah approval, sistem memeriksa ulang apakah gudang asal masih
                             memiliki stok yang cukup sebelum approval dapat diproses — <strong>Approve</strong>,
                             <strong>Revise</strong> (komentar wajib), dan <strong>Reject</strong> (komentar
                             wajib) bekerja sama seperti dokumen VPL lainnya.
                         </span>
                     </p>

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             The stock only actually moves between warehouses when the document reaches
                             <strong>Completed</strong>. As the creator, you may Cancel while On Progress (before
                             any approval) or while on Hold, and Edit only while on Hold.
                         </span>
                         <span x-show="lang==='id'">
                             Stok baru benar-benar berpindah antar gudang setelah dokumen mencapai status
                             <strong>Completed</strong>. Sebagai pembuat dokumen, Anda dapat Cancel selama
                             berstatus On Progress (sebelum ada approval) atau selama Hold, dan hanya dapat Edit
                             selama berstatus Hold.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
