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
         <!-- ================= SECTION – PURCHASING ASSIGNMENT ================= -->
         <section class="space-y-10">

             <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                 <span x-show="lang==='en'"> Purchasing Assignment & Transfer Jobs</span>
                 <span x-show="lang==='id'"> Pengaturan & Transfer Pekerjaan Purchasing</span>
             </h2>

             <!-- 1. OVERVIEW -->
             <section class="space-y-4">
                 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                     <span x-show="lang==='en'">1. Overview</span>
                     <span x-show="lang==='id'">1. Gambaran Umum</span>
                 </h3>

                 <p class="text-gray-600 dark:text-gray-400">
                     <span x-show="lang==='en'">
                         Purchasing Assignment module is used to assign procurement
                         documents (SPPB / SPPJ / SPPK / SPPT) to specific purchasing users.
                     </span>
                     <span x-show="lang==='id'">
                         Modul Purchasing Assignment digunakan untuk meng-assign
                         dokumen procurement (SPPB / SPPJ / SPPK / SPPT)
                         kepada user purchasing tertentu.
                     </span>
                 </p>

                 <div class="manual-note manual-info">
                     <span x-show="lang==='en'">
                         Only documents eligible for purchasing process
                         will appear in this module.
                     </span>
                     <span x-show="lang==='id'">
                         Hanya dokumen yang memenuhi syarat proses purchasing
                         yang akan muncul di modul ini.
                     </span>
                 </div>
             </section>

             <!-- 2. FILTER AREA -->
             <section class="space-y-4">
                 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                     2. Document Filter
                 </h3>

                 <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                     <li>
                         <strong>Doc Type Filter</strong>
                         <ul class="list-disc pl-6">
                             <li>All</li>
                             <li>SPPB</li>
                             <li>SPPJ</li>
                             <li>SPPK</li>
                             <li>SPPT</li>
                         </ul>
                     </li>
                 </ul>

                 <div class="manual-note manual-important">
                     Filtering affects both Assign List and Transfer Jobs tabs.
                 </div>
             </section>

             <!-- 3. TAB – ASSIGN LIST -->
             <section class="space-y-6">

                 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                     3. Assign List Tab
                 </h3>

                 <p class="text-gray-600 dark:text-gray-400">
                     <span x-show="lang==='en'">
                         This tab displays documents that have not yet been assigned
                         to a purchasing user.
                     </span>
                     <span x-show="lang==='id'">
                         Tab ini menampilkan dokumen yang belum di-assign
                         ke user purchasing.
                     </span>
                 </p>

                 <!-- Columns -->
                 <div class="space-y-3">
                     <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                         Table Columns
                     </h4>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Checkbox Selector</li>
                         <li>Doc ID</li>
                         <li>Assign Purchasing (Dropdown / User)</li>
                         <li>Date</li>
                         <li>Company</li>
                         <li>Created By</li>
                         <li>Department</li>
                         <li>Description</li>
                     </ul>
                 </div>

                 <!-- Rules -->
                 <div class="space-y-3">
                     <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                         Assignment Rules
                     </h4>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>At least one document must be selected</li>
                         <li>Purchasing user must be selected before submit</li>
                         <li>Assignment cannot be empty</li>
                         <li>Only active purchasing users are selectable</li>
                     </ul>
                 </div>

                 <div class="manual-note manual-warning">
                     Once assigned, document will move out from Assign List
                     and appear under assigned purchasing responsibility.
                 </div>
             </section>

             <!-- 4. TAB – TRANSFER JOBS -->
             <section class="space-y-6">

                 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                     4. Transfer Jobs Tab
                 </h3>

                 <p class="text-gray-600 dark:text-gray-400">
                     <span x-show="lang==='en'">
                         This tab allows reassigning documents from one purchasing user
                         to another.
                     </span>
                     <span x-show="lang==='id'">
                         Tab ini digunakan untuk memindahkan assignment
                         dari satu purchasing ke purchasing lainnya.
                     </span>
                 </p>

                 <!-- Columns -->
                 <div class="space-y-3">
                     <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                         Table Columns
                     </h4>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>Checkbox Selector</li>
                         <li>Doc ID</li>
                         <li>Assign Purchasing (Current)</li>
                         <li>Assign Purchasing New</li>
                         <li>Date</li>
                         <li>Company</li>
                         <li>Created By</li>
                         <li>Department</li>
                         <li>Description</li>
                     </ul>
                 </div>

                 <!-- Rules -->
                 <div class="space-y-3">
                     <h4 class="font-semibold text-gray-800 dark:text-gray-200">
                         Transfer Rules
                     </h4>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>At least one document must be selected</li>
                         <li>New purchasing user must be selected</li>
                         <li>New user cannot be same as current user</li>
                         <li>Transfer is recorded for audit tracking</li>
                     </ul>
                 </div>

                 <div class="manual-note manual-important">
                     Transfer does not reset document progress.
                     It only changes responsibility.
                 </div>
             </section>

             <!-- 5. SYSTEM CONTROL LOGIC -->
             <section class="space-y-4">

                 <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                     5. System Control Logic
                 </h3>

                 <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                     <li>Assign button only visible in Assign tab</li>
                     <li>Transfer button only visible in Transfer tab</li>
                     <li>Bulk selection supported</li>
                     <li>System validates before processing</li>
                 </ul>

                 <div class="manual-note manual-important">
                     All assignment and transfer actions are logged
                     for traceability and accountability.
                 </div>

             </section>

         </section>

     </div>
