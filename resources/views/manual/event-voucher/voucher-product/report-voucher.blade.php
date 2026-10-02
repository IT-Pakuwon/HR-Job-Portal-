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
                             Report Voucher is the reporting hub for the whole VPL module. Its reports are
                             organized into three groups, selectable from the top navigation, each containing
                             one or more report tabs. Every report is scoped to a single company and reporting
                             period (month/year) that you choose, and can be exported to Excel.
                         </span>
                         <span x-show="lang==='id'">
                             Report Voucher adalah pusat laporan untuk seluruh modul VPL. Laporan-laporannya
                             dikelompokkan menjadi tiga grup yang dapat dipilih dari navigasi atas, masing-masing
                             berisi satu atau beberapa tab laporan. Setiap laporan dibatasi pada satu perusahaan
                             dan periode (bulan/tahun) yang Anda pilih, dan dapat diekspor ke Excel.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             You can only generate reports for companies you have access to. Switching the
                             Company or Period filter and clicking into a tab refreshes that tab's data for the
                             new selection.
                         </span>
                         <span x-show="lang==='id'">
                             Anda hanya dapat membuat laporan untuk perusahaan yang menjadi akses Anda. Mengganti
                             filter Company atau Period lalu membuka sebuah tab akan memuat ulang data tab
                             tersebut sesuai pilihan baru.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 2 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s2')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">2. Voucher Stock Reports</span>
                         <span x-show="lang==='id'">2. Voucher Stock Reports</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             This group covers the main monthly stock reports for vouchers, each one a
                             Company + Month/Year view.
                         </span>
                         <span x-show="lang==='id'">
                             Grup ini berisi laporan stok bulanan utama untuk voucher, masing-masing berupa
                             tampilan Company + Month/Year.
                         </span>
                     </p>

                     <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Stock Voucher</span>
                                 <span x-show="lang==='id'">Stock Voucher</span>
                             </strong> —
                             <span x-show="lang==='en'">The main stock movement report ("Laporan Stock Voucher"): Beginning balance, In, Out, and Ending balance per product and expiry batch for the selected month. In covers Receive and Return Transfer; Out covers Transfer, Usage, and Return Usage; Settlement does not move stock and is excluded.</span>
                             <span x-show="lang==='id'">Laporan pergerakan stok utama ("Laporan Stock Voucher"): saldo Awal, In, Out, dan saldo Akhir per produk dan batch kedaluwarsa untuk bulan yang dipilih. In mencakup Receive dan Return Transfer; Out mencakup Transfer, Usage, dan Return Usage; Settlement tidak memindahkan stok sehingga tidak disertakan.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Stock &amp; Aging Summary</span>
                                 <span x-show="lang==='id'">Stock &amp; Aging Summary</span>
                             </strong> —
                             <span x-show="lang==='en'">Stock grouped by aging bucket, together with a breakdown of voucher sources (e.g. Promotion, Leasing) received and a breakdown of how vouchers were used in the period (Loyalty, Promotion, Entertainment, Internal Use).</span>
                             <span x-show="lang==='id'">Stok dikelompokkan per bucket aging, dilengkapi rincian sumber voucher (misalnya Promotion, Leasing) yang diterima serta rincian penggunaan voucher pada periode tersebut (Loyalty, Promotion, Entertainment, Internal Use).</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Stock Out Voucher</span>
                                 <span x-show="lang==='id'">Stock Out Voucher</span>
                             </strong> —
                             <span x-show="lang==='en'">Beginning/In/Out/Retur/Ending plus the nominal value, purpose, and remarks per tenant, scoped to one warehouse that you pick (Promotion or Loyalty).</span>
                             <span x-show="lang==='id'">Saldo Awal/In/Out/Retur/Akhir beserta nilai nominal, purpose, dan remarks per tenant, dibatasi pada satu gudang yang Anda pilih (Promotion atau Loyalty).</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Loyalty Usage Rate</span>
                                 <span x-show="lang==='id'">Loyalty Usage Rate</span>
                             </strong> —
                             <span x-show="lang==='en'">Usage and ending stock specifically at the Loyalty warehouse, for tracking how much of the loyalty voucher allocation has been redeemed.</span>
                             <span x-show="lang==='id'">Usage dan saldo akhir khusus di gudang Loyalty, untuk memantau seberapa banyak alokasi voucher loyalty yang sudah diredeem.</span>
                         </li>
                     </ul>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 3 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s3')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">3. Ledger &amp; Stock Detail / Product Stock Reports</span>
                         <span x-show="lang==='id'">3. Ledger &amp; Stock Detail / Product Stock Reports</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             These tabs give you a closer, line-level look at stock and movements rather than a
                             monthly summary.
                         </span>
                         <span x-show="lang==='id'">
                             Tab-tab ini memberikan tampilan yang lebih rinci (per baris) atas stok dan
                             pergerakan, berbeda dari ringkasan bulanan.
                         </span>
                     </p>

                     <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Voucher &amp; Product Stock</span>
                                 <span x-show="lang==='id'">Voucher &amp; Product Stock</span>
                             </strong> —
                             <span x-show="lang==='en'">A searchable table of current stock balances, filterable by Product ID, Product Name, or Warehouse.</span>
                             <span x-show="lang==='id'">Tabel saldo stok saat ini yang dapat dicari, dapat difilter berdasarkan Product ID, Product Name, atau Warehouse.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">In &amp; Out Voucher Product</span>
                                 <span x-show="lang==='id'">In &amp; Out Voucher Product</span>
                             </strong> —
                             <span x-show="lang==='en'">A raw ledger browser showing every individual movement (Receive, Transfer In, Transfer Out, Usage, Return) for the period, filterable by Ref No, Product Name, Type, or a reference document number.</span>
                             <span x-show="lang==='id'">Daftar ledger mentah yang menampilkan setiap pergerakan individual (Receive, Transfer In, Transfer Out, Usage, Return) untuk periode tersebut, dapat difilter berdasarkan Ref No, Product Name, Type, atau nomor dokumen referensi.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Summary Group</span>
                                 <span x-show="lang==='id'">Summary Group</span>
                             </strong> —
                             <span x-show="lang==='en'">Beginning/In/Transfer/Out-by-purpose/Ending per product and expiry batch, split by the department that made each movement.</span>
                             <span x-show="lang==='id'">Saldo Awal/In/Transfer/Out-per-purpose/Akhir per produk dan batch kedaluwarsa, dipisahkan menurut departemen yang melakukan masing-masing pergerakan.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Product Report</span>
                                 <span x-show="lang==='id'">Product Report</span>
                             </strong> —
                             <span x-show="lang==='en'">One row per product and expiry batch with Beginning/In/Out/Ending quantities, plus who requested each Out movement ("Laporan Stok Product").</span>
                             <span x-show="lang==='id'">Satu baris per produk dan batch kedaluwarsa dengan jumlah Awal/In/Out/Akhir, beserta siapa yang mengajukan setiap pergerakan Out ("Laporan Stok Product").</span>
                         </li>
                     </ul>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Filters &amp; Export</span>
                         <span x-show="lang==='id'">4. Filter &amp; Export</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Most tabs share the same filter bar: a <strong>Company</strong> dropdown (limited to
                             companies you have access to) and <strong>Month</strong> / <strong>Year</strong>
                             dropdowns for the reporting period. Tabs that need it, such as Stock Out Voucher,
                             add their own extra selector (e.g. the Promotion/Loyalty warehouse).
                         </span>
                         <span x-show="lang==='id'">
                             Sebagian besar tab memiliki filter yang sama: dropdown <strong>Company</strong>
                             (terbatas pada perusahaan yang menjadi akses Anda) serta dropdown
                             <strong>Month</strong> / <strong>Year</strong> untuk periode laporan. Tab yang
                             memerlukan filter tambahan, seperti Stock Out Voucher, memiliki selektor
                             tambahannya sendiri (misalnya gudang Promotion/Loyalty).
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Click <strong>Export Excel</strong> on any tab to download that report, with its
                             current filters applied, as an .xlsx file.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>Export Excel</strong> pada tab mana pun untuk mengunduh laporan
                             tersebut, dengan filter yang sedang aktif, sebagai file .xlsx.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
