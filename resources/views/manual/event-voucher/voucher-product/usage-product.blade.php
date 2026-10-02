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
                             Usage Product records voucher or product stock being handed out or redeemed — for
                             example vouchers given away at a promotion event, or loyalty vouchers redeemed by a
                             customer. There are two usage types: <strong>Usage</strong> (stock goes out) and
                             <strong>Return Usage</strong> (unused stock from a prior Usage document comes back),
                             which references the original Usage document.
                         </span>
                         <span x-show="lang==='id'">
                             Usage Product mencatat stok voucher atau produk yang dikeluarkan atau diredeem —
                             misalnya voucher yang dibagikan pada acara promosi, atau voucher loyalty yang
                             diredeem oleh pelanggan. Terdapat dua jenis usage: <strong>Usage</strong> (stok
                             keluar) dan <strong>Return Usage</strong> (stok yang tidak terpakai dari dokumen
                             Usage sebelumnya dikembalikan), yang mengacu pada dokumen Usage asal.
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
                                 <span x-show="lang==='en'">Submitted and going through approval; for a Usage document, the quantity is held in the warehouse so it can't be double-promised elsewhere.</span>
                                 <span x-show="lang==='id'">Sudah disubmit dan sedang dalam approval; untuk dokumen Usage, jumlahnya ditahan di gudang agar tidak terpakai ganda di tempat lain.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">Fully approved — the stock movement is posted to the warehouse and ledger, and the document becomes eligible for Settlement (Usage) if applicable.</span>
                                 <span x-show="lang==='id'">Sudah disetujui sepenuhnya — pergerakan stok terposting ke gudang dan ledger, dan dokumen menjadi memenuhi syarat untuk Settlement (untuk Usage) jika berlaku.</span>
                             </li>
                             <li>
                                 <strong>Hold / Revise</strong> —
                                 <span x-show="lang==='en'">Returned by an approver for correction.</span>
                                 <span x-show="lang==='id'">Dikembalikan oleh approver untuk diperbaiki.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> /
                                 <strong>Cancelled</strong> —
                                 <span x-show="lang==='en'">Closed without affecting stock.</span>
                                 <span x-show="lang==='id'">Ditutup tanpa memengaruhi stok.</span>
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
                         <span x-show="lang==='en'">2. Submitting a Usage Request</span>
                         <span x-show="lang==='id'">2. Mengajukan Permintaan Usage</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>New Usage</strong> to open the form. Submitting immediately enters the
                             approval chain — there is no draft mode.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>New Usage</strong> untuk membuka form. Setelah disubmit, dokumen
                             langsung masuk ke rantai approval — tidak ada mode draft.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Company / Department / V-P Type</span>
                                 <span x-show="lang==='id'">Company / Department / V-P Type</span>
                             </strong> —
                             <span x-show="lang==='en'">Who is using the stock, and whether it's Voucher or Product.</span>
                             <span x-show="lang==='id'">Siapa yang menggunakan stok ini, dan apakah berupa Voucher atau Product.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Usage Type</span>
                                 <span x-show="lang==='id'">Usage Type</span>
                             </strong> —
                             <span x-show="lang==='en'"><strong>Usage</strong> or <strong>Return Usage</strong>. Return requires a Reference Usage Doc, and is blocked while that document has a Settlement still in progress.</span>
                             <span x-show="lang==='id'"><strong>Usage</strong> atau <strong>Return Usage</strong>. Return mewajibkan Reference Usage Doc, dan tidak dapat diajukan selama dokumen tersebut masih memiliki Settlement yang sedang berjalan.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Event Date</span>
                                 <span x-show="lang==='id'">Event Date</span>
                             </strong> —
                             <span x-show="lang==='en'">Required for a new Usage document, except for the CUSTOMERSERVICE department. It must be today or a future date — it cannot be backdated.</span>
                             <span x-show="lang==='id'">Wajib diisi untuk dokumen Usage baru, kecuali untuk departemen CUSTOMERSERVICE. Tanggalnya harus hari ini atau di masa depan — tidak boleh mundur.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Usage Date</span>
                                 <span x-show="lang==='id'">Usage Date</span>
                             </strong> —
                             <span x-show="lang==='en'">Only shown for the CUSTOMERSERVICE department, which may backdate a Usage or Return up to 3 days (H-3) to log stock that was actually handed out earlier.</span>
                             <span x-show="lang==='id'">Hanya tersedia untuk departemen CUSTOMERSERVICE, yang dapat memundurkan tanggal Usage atau Return hingga 3 hari (H-3) untuk mencatat stok yang sebenarnya sudah dikeluarkan sebelumnya.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Warehouse</span>
                                 <span x-show="lang==='id'">Warehouse</span>
                             </strong> —
                             <span x-show="lang==='en'">The warehouse the stock is drawn from, resolved after Company, Department, and Type are selected.</span>
                             <span x-show="lang==='id'">Gudang asal stok, ditentukan setelah Company, Department, dan Type dipilih.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Remark</span>
                                 <span x-show="lang==='id'">Remark</span>
                             </strong> —
                             <span x-show="lang==='en'">Required explanation of the usage.</span>
                             <span x-show="lang==='id'">Penjelasan usage, wajib diisi.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Detail Lines</span>
                                 <span x-show="lang==='id'">Detail Lines</span>
                             </strong> —
                             <span x-show="lang==='en'">One row per product with its Qty and a required <strong>Purpose</strong> (and optional purpose remark). For Usage, the Qty cannot exceed stock pickable in that warehouse; for Return, it cannot exceed what the referenced document still has outstanding.</span>
                             <span x-show="lang==='id'">Satu baris per produk dengan Qty dan <strong>Purpose</strong> yang wajib diisi (serta catatan purpose opsional). Untuk Usage, Qty tidak boleh melebihi stok yang tersedia di gudang tersebut; untuk Return, tidak boleh melebihi sisa yang masih bisa dikembalikan dari dokumen asal.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Remark, Purpose on every line, and an attachment are all required — the form
                             cannot be submitted without them.
                         </span>
                         <span x-show="lang==='id'">
                             Remark, Purpose pada setiap baris, dan lampiran semuanya wajib diisi — form tidak
                             dapat disubmit tanpa itu.
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
                             Submitting a Usage document holds (reserves) the quantity on each line in its
                             warehouse, so it cannot be claimed twice while the document is still pending. A
                             Return Usage does the opposite — it releases a hold instead of adding one.
                         </span>
                         <span x-show="lang==='id'">
                             Mengajukan dokumen Usage akan menahan (reserve) jumlah pada setiap baris di gudang
                             tersebut, sehingga tidak dapat diklaim dua kali selama dokumen masih pending. Return
                             Usage bekerja sebaliknya — melepaskan penahanan, bukan menambahkannya.
                         </span>
                     </p>

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             At the final approval step for a Usage document, the system re-checks warehouse
                             stock before letting it complete. <strong>Approve</strong>, <strong>Revise</strong>
                             (comment required), and <strong>Reject</strong> (comment required) work the same way
                             as other VPL documents.
                         </span>
                         <span x-show="lang==='id'">
                             Pada langkah approval terakhir untuk dokumen Usage, sistem memeriksa ulang stok
                             gudang sebelum dokumen dapat diselesaikan. <strong>Approve</strong>,
                             <strong>Revise</strong> (komentar wajib), dan <strong>Reject</strong> (komentar
                             wajib) bekerja sama seperti dokumen VPL lainnya.
                         </span>
                     </p>

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             Once a Usage document is Completed, it may need to go through <strong>Settlement
                             Product</strong> afterwards to reconcile how much of the used quantity was actually
                             accounted for. As the creator, you may Cancel while On Progress (before any
                             approval) or while on Hold, and Edit only while on Hold.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah dokumen Usage berstatus Completed, dokumen tersebut mungkin perlu diproses
                             lebih lanjut melalui <strong>Settlement Product</strong> untuk merekonsiliasi berapa
                             banyak dari jumlah yang digunakan benar-benar dipertanggungjawabkan. Sebagai pembuat
                             dokumen, Anda dapat Cancel selama berstatus On Progress (sebelum ada approval) atau
                             selama Hold, dan hanya dapat Edit selama berstatus Hold.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
