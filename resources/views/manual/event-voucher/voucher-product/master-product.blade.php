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
                             Master Product is the registry of every voucher and product item used across the
                             Voucher &amp; Product (VPL) module. A product must exist here, with status
                             <strong>Active</strong>, before it can be selected on a Receive, Transfer, Usage, or
                             Settlement document. Each product carries a system-generated Product ID, a type
                             (Voucher or Product), a category, a unit of measure, and — for stock-holding
                             products — the current quantity on hand per warehouse and expiry date.
                         </span>
                         <span x-show="lang==='id'">
                             Master Product adalah data induk untuk setiap voucher dan produk yang digunakan
                             di seluruh modul Voucher &amp; Product (VPL). Sebuah produk harus terdaftar di sini
                             dengan status <strong>Active</strong> sebelum dapat dipilih pada dokumen Receive,
                             Transfer, Usage, maupun Settlement. Setiap produk memiliki Product ID yang dibuat
                             otomatis oleh sistem, tipe (Voucher atau Product), kategori, satuan (UOM), dan —
                             untuk produk yang menyimpan stok — jumlah stok saat ini per gudang dan tanggal
                             kedaluwarsa.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Master Product has no approval workflow — saving the form creates or updates the
                             product record immediately. Approval only applies to the stock-movement documents
                             (Receive, Transfer, Usage, Settlement) that reference a product afterwards.
                         </span>
                         <span x-show="lang==='id'">
                             Master Product tidak memiliki alur approval — menyimpan form akan langsung membuat
                             atau memperbarui data produk. Approval hanya berlaku pada dokumen pergerakan stok
                             (Receive, Transfer, Usage, Settlement) yang menggunakan produk tersebut.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Cards &amp; Filters</span>
                             <span x-show="lang==='id'">1.1 Kartu Status &amp; Filter</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The cards above the table (All, Active, Inactive) filter the list by status.
                                 Below them, the toolbar lets you narrow the list further by Type (Voucher /
                                 Product), Doc ID, Category, Source, or Product Name.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu di atas tabel (All, Active, Inactive) menyaring daftar berdasarkan status.
                                 Di bawahnya, toolbar memungkinkan Anda menyaring lebih lanjut berdasarkan Type
                                 (Voucher / Product), Doc ID, Category, Source, atau Product Name.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Admin users additionally see an <strong>All Product</strong> card, which shows
                                 every product system-wide (ignoring company scoping) together with its own
                                 Company filter — all other cards and tabs stay scoped to your own company.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna admin memiliki tambahan kartu <strong>All Product</strong> yang
                                 menampilkan seluruh produk di semua perusahaan (tanpa batasan scope) beserta
                                 filter Company tersendiri — kartu dan tab lainnya tetap dibatasi hanya untuk
                                 perusahaan Anda.
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
                         <span x-show="lang==='en'">2. Creating &amp; Editing a Product</span>
                         <span x-show="lang==='id'">2. Membuat &amp; Mengedit Produk</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>Add Product</strong> to open the product form. The Product ID is not
                             entered manually — it is generated automatically from the Company and Product Type
                             you select (for example <code>V100001</code> for a Voucher under one company, or
                             <code>P200001</code> for a Product under another).
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>Add Product</strong> untuk membuka form produk. Product ID tidak diisi
                             manual — dibuat otomatis oleh sistem berdasarkan Company dan Product Type yang
                             dipilih (misalnya <code>V100001</code> untuk Voucher di satu perusahaan, atau
                             <code>P200001</code> untuk Product di perusahaan lain).
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>Company</strong> —
                             <span x-show="lang==='en'">The company that owns this product.</span>
                             <span x-show="lang==='id'">Perusahaan pemilik produk ini.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Product Type</span>
                                 <span x-show="lang==='id'">Tipe Produk</span>
                             </strong> —
                             <span x-show="lang==='en'"><strong>Voucher</strong> or <strong>Product</strong>. A product photo is required when the type is Product.</span>
                             <span x-show="lang==='id'"><strong>Voucher</strong> atau <strong>Product</strong>. Foto produk wajib diisi jika tipenya Product.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Product Name, Category, UOM</span>
                                 <span x-show="lang==='id'">Nama Produk, Category, UOM</span>
                             </strong> —
                             <span x-show="lang==='en'">Basic identification; Category options depend on the Product Type chosen.</span>
                             <span x-show="lang==='id'">Identitas dasar produk; pilihan Category mengikuti Product Type yang dipilih.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Nama PT / Nama Tenant / Event</span>
                                 <span x-show="lang==='id'">Nama PT / Nama Tenant / Event</span>
                             </strong> —
                             <span x-show="lang==='en'">The source company and tenant/event this voucher or product is tied to.</span>
                             <span x-show="lang==='id'">Perusahaan sumber serta nama tenant/event yang terkait dengan voucher atau produk ini.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Value</span>
                                 <span x-show="lang==='id'">Value</span>
                             </strong> —
                             <span x-show="lang==='en'">The nominal/unit price used to value stock received for this product.</span>
                             <span x-show="lang==='id'">Nilai nominal/harga satuan yang digunakan untuk menilai stok yang diterima untuk produk ini.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Check Expired Date</span>
                                 <span x-show="lang==='id'">Check Expired Date</span>
                             </strong> —
                             <span x-show="lang==='en'">Tick this if stock of this product must be tracked per expiry date.</span>
                             <span x-show="lang==='id'">Centang jika stok produk ini perlu dipantau berdasarkan tanggal kedaluwarsa.</span>
                         </li>
                         <li>
                             <strong>
                                 <span x-show="lang==='en'">Remarks</span>
                                 <span x-show="lang==='id'">Remarks</span>
                             </strong> —
                             <span x-show="lang==='en'">Optional free-text notes.</span>
                             <span x-show="lang==='id'">Catatan bebas, opsional.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Only the user who created a product (or an admin/full-data-scope user) can edit it.
                             Other users can view the product but the Edit action is not available to them.
                         </span>
                         <span x-show="lang==='id'">
                             Hanya pengguna yang membuat produk tersebut (atau pengguna admin/full-data-scope)
                             yang dapat mengeditnya. Pengguna lain dapat melihat produk tersebut namun aksi
                             Edit tidak tersedia untuk mereka.
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
                         <span x-show="lang==='en'">3. Viewing Stock &amp; Related Transactions</span>
                         <span x-show="lang==='id'">3. Melihat Stok &amp; Transaksi Terkait</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click the Product ID button on any row to open the product detail panel. It shows
                             the current stock breakdown by warehouse and expiry date, the product's
                             attachments, and photo (for Product-type items).
                         </span>
                         <span x-show="lang==='id'">
                             Klik tombol Product ID pada baris mana pun untuk membuka panel detail produk.
                             Panel ini menampilkan rincian stok saat ini per gudang dan tanggal kedaluwarsa,
                             lampiran produk, serta foto (untuk tipe Product).
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             The detail panel also lists any Transfer, Return Transfer, Usage, or Return Usage
                             document that is still <strong>On Progress</strong> and involves this product — so
                             you can see stock that is already committed to a document in flight, even though
                             it has not moved yet.
                         </span>
                         <span x-show="lang==='id'">
                             Panel detail juga menampilkan dokumen Transfer, Return Transfer, Usage, atau Return
                             Usage yang masih berstatus <strong>On Progress</strong> dan melibatkan produk ini —
                             sehingga Anda dapat melihat stok yang sudah terikat pada dokumen yang sedang
                             berjalan, meskipun stoknya belum benar-benar berpindah.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. Activating &amp; Deactivating a Product</span>
                         <span x-show="lang==='id'">4. Mengaktifkan &amp; Menonaktifkan Produk</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Use the <strong>Actions</strong> menu on a row to Deactivate an Active product, or
                             Activate an Inactive one. A deactivated product no longer appears as an option on
                             new Receive/Transfer/Usage documents, but existing documents that already reference
                             it are unaffected.
                         </span>
                         <span x-show="lang==='id'">
                             Gunakan menu <strong>Actions</strong> pada baris untuk menonaktifkan produk Active,
                             atau mengaktifkan kembali produk Inactive. Produk yang dinonaktifkan tidak lagi
                             muncul sebagai pilihan pada dokumen Receive/Transfer/Usage baru, namun dokumen yang
                             sudah ada dan menggunakan produk tersebut tidak terpengaruh.
                         </span>
                     </p>

                     <div class="manual-note manual-important">
                         <span x-show="lang==='en'">
                             A product cannot be deactivated while it still has quantity available in any
                             warehouse. Transfer, Usage, or otherwise bring its stock down to zero first.
                         </span>
                         <span x-show="lang==='id'">
                             Produk tidak dapat dinonaktifkan selama masih memiliki stok tersedia di gudang mana
                             pun. Lakukan Transfer, Usage, atau cara lain untuk menghabiskan stoknya terlebih
                             dahulu.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
