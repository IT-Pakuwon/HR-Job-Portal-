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
                             Settlement Product reconciles a completed <strong>Usage</strong> document — it
                             records how much of the quantity that was handed out is actually accounted for
                             (settled), leaving the rest as an outstanding remainder. A Settlement always refers
                             to exactly one Usage document, and a Usage document can only have one active
                             Settlement at a time.
                         </span>
                         <span x-show="lang==='id'">
                             Settlement Product merekonsiliasi dokumen <strong>Usage</strong> yang sudah
                             Completed — mencatat berapa banyak dari jumlah yang dikeluarkan benar-benar
                             dipertanggungjawabkan (settled), dengan sisanya menjadi outstanding. Satu Settlement
                             selalu mengacu pada tepat satu dokumen Usage, dan satu dokumen Usage hanya boleh
                             memiliki satu Settlement aktif pada satu waktu.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Settlement does not apply to documents created by the CUSTOMERSERVICE department —
                             those are tracked differently and never appear in the Job List described below.
                         </span>
                         <span x-show="lang==='id'">
                             Settlement tidak berlaku untuk dokumen yang dibuat oleh departemen CUSTOMERSERVICE —
                             dokumen tersebut dipantau dengan cara berbeda dan tidak akan muncul pada Job List
                             yang dijelaskan di bawah ini.
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
                                 <span x-show="lang==='en'">Submitted and going through approval.</span>
                                 <span x-show="lang==='id'">Sudah disubmit dan sedang dalam approval.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">Fully approved — the settled quantities are posted to the ledger.</span>
                                 <span x-show="lang==='id'">Sudah disetujui sepenuhnya — jumlah yang disettle terposting ke ledger.</span>
                             </li>
                             <li>
                                 <strong>Hold / Revise</strong> —
                                 <span x-show="lang==='en'">Returned by an approver for correction.</span>
                                 <span x-show="lang==='id'">Dikembalikan oleh approver untuk diperbaiki.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> /
                                 <strong>Cancelled</strong> —
                                 <span x-show="lang==='en'">Closed; the Usage document becomes eligible for a new Settlement again.</span>
                                 <span x-show="lang==='id'">Ditutup; dokumen Usage kembali memenuhi syarat untuk dibuatkan Settlement baru.</span>
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
                         <span x-show="lang==='en'">2. Job List &amp; Creating a Settlement</span>
                         <span x-show="lang==='id'">2. Job List &amp; Membuat Settlement</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The <strong>Job List</strong> tab lists every Completed Usage document in your
                             company/department that does not yet have an active Settlement. Click
                             <strong>Settle</strong> on a row to start a new Settlement pre-filled with that
                             document's Company, Department, and Type — or click <strong>New Settlement</strong>
                             and pick the Usage document manually.
                         </span>
                         <span x-show="lang==='id'">
                             Tab <strong>Job List</strong> menampilkan setiap dokumen Usage berstatus Completed
                             di company/department Anda yang belum memiliki Settlement aktif. Klik
                             <strong>Settle</strong> pada baris untuk memulai Settlement baru yang sudah terisi
                             Company, Department, dan Type dari dokumen tersebut — atau klik
                             <strong>New Settlement</strong> dan pilih dokumen Usage secara manual.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Reference Usage Doc</span>
                                 <span x-show="lang==='id'">Reference Usage Doc</span>
                             </strong> —
                             <span x-show="lang==='en'">The Completed Usage document being settled. It must not already have another active Settlement.</span>
                             <span x-show="lang==='id'">Dokumen Usage berstatus Completed yang akan disettle. Dokumen tersebut tidak boleh sudah memiliki Settlement aktif lain.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Remark</span>
                                 <span x-show="lang==='id'">Remark</span>
                             </strong> —
                             <span x-show="lang==='en'">Required explanation of the settlement.</span>
                             <span x-show="lang==='id'">Penjelasan settlement, wajib diisi.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Settlement Lines</span>
                                 <span x-show="lang==='id'">Settlement Lines</span>
                             </strong> —
                             <span x-show="lang==='en'">One line per detail of the referenced Usage document, each with a <strong>Qty Settlement</strong> (how much of that line is being accounted for now) and an optional line remark. Qty Settlement cannot be negative and cannot exceed the line's remaining quantity (quantity used minus any quantity already returned).</span>
                             <span x-show="lang==='id'">Satu baris per detail dari dokumen Usage yang direferensikan, masing-masing dengan <strong>Qty Settlement</strong> (berapa banyak dari baris tersebut yang dipertanggungjawabkan sekarang) dan catatan baris opsional. Qty Settlement tidak boleh negatif dan tidak boleh melebihi sisa jumlah baris tersebut (jumlah yang digunakan dikurangi jumlah yang sudah dikembalikan).</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Remark, at least one settlement line, and an attachment are all required — the form
                             cannot be submitted without them.
                         </span>
                         <span x-show="lang==='id'">
                             Remark, minimal satu baris settlement, dan lampiran semuanya wajib diisi — form
                             tidak dapat disubmit tanpa itu.
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
                             Open the document from the list to see its approval timeline. When it reaches your
                             approval level, you can <strong>Approve</strong>, <strong>Revise</strong> (comment
                             required), or <strong>Reject</strong> (comment required) — the same approve/reject/
                             revise pattern used across Receive, Transfer, and Usage documents.
                         </span>
                         <span x-show="lang==='id'">
                             Buka dokumen dari daftar untuk melihat timeline approval-nya. Saat dokumen mencapai
                             level approval Anda, Anda dapat memilih <strong>Approve</strong>,
                             <strong>Revise</strong> (komentar wajib), atau <strong>Reject</strong> (komentar
                             wajib) — pola approve/reject/revise yang sama seperti pada dokumen Receive,
                             Transfer, dan Usage.
                         </span>
                     </p>

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             The settled quantities are only posted to the ledger once the Settlement reaches
                             <strong>Completed</strong>. As the creator, you may Cancel while On Progress (before
                             any approval) or while on Hold, and Edit only while on Hold.
                         </span>
                         <span x-show="lang==='id'">
                             Jumlah yang disettle baru terposting ke ledger setelah Settlement mencapai status
                             <strong>Completed</strong>. Sebagai pembuat dokumen, Anda dapat Cancel selama
                             berstatus On Progress (sebelum ada approval) atau selama Hold, dan hanya dapat Edit
                             selama berstatus Hold.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
