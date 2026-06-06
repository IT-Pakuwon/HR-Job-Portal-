<div x-data="{
    lang: localStorage.getItem('manual_lang') || 'id',
    openSection: 's1',
    setLang(v) { this.lang = v; localStorage.setItem('manual_lang', v); },
    toggle(section) { this.openSection = this.openSection === section ? null : section; }
}" class="max-w-9xl mx-auto space-y-6 p-6">

    <!-- ================= LANGUAGE TOGGLE ================= -->
    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')"
                :class="lang === 'id' ? 'bg-gray-900 text-white' : 'text-gray-600 dark:text-gray-300'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition">ID</button>
            <button @click="setLang('en')"
                :class="lang === 'en' ? 'bg-gray-900 text-white' : 'text-gray-600 dark:text-gray-300'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition">EN</button>
        </div>
    </div>

    <!-- ================= DATA DISCLAIMER ================= -->
    <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            <span x-show="lang === 'en'">Information</span>
            <span x-show="lang === 'id'">Informasi</span>
        </h3>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <span x-show="lang === 'en'">All data shown in this manual are dummy data used for illustration purposes only.</span>
            <span x-show="lang === 'id'">Seluruh data yang ditampilkan dalam manual ini merupakan data dummy yang digunakan hanya sebagai contoh.</span>
        </p>
    </div>

    <!-- ================= OVERVIEW ================= -->
    <div class="manual-note manual-info">
        <strong>
            <span x-show="lang==='en'">Overview</span>
            <span x-show="lang==='id'">Gambaran Umum</span>
        </strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                Last Order is a reference page that shows the most recent purchase order prices
                for each item. Users can use this page to check the last known unit cost of an item
                before creating a new canvass sheet, helping to set realistic price benchmarks.
            </span>
            <span x-show="lang==='id'">
                Last Order adalah halaman referensi yang menampilkan harga purchase order terbaru
                untuk setiap item. Pengguna dapat menggunakan halaman ini untuk melihat harga satuan
                terakhir dari suatu barang sebelum membuat canvass sheet baru, membantu menentukan
                acuan harga yang realistis.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">1. Last Order Inventory</span>
                    <span x-show="lang==='id'">1. Last Order Inventory</span>
                </span>
                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The <strong>Last Order Inventory</strong> tab shows the most recent purchase history
                        for stock and non-stock items. This is useful as a price reference when purchasing
                        the same item again.
                    </span>
                    <span x-show="lang==='id'">
                        Tab <strong>Last Order Inventory</strong> menampilkan riwayat pembelian terbaru
                        untuk item stock dan non-stock. Ini berguna sebagai acuan harga saat membeli
                        item yang sama kembali.
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>PONbr</strong> —
                        <span x-show="lang==='en'">Purchase Order number from the last transaction</span>
                        <span x-show="lang==='id'">Nomor Purchase Order dari transaksi terakhir</span>
                    </li>
                    <li>
                        <strong>PO Date</strong> —
                        <span x-show="lang==='en'">Date of the last purchase order</span>
                        <span x-show="lang==='id'">Tanggal purchase order terakhir</span>
                    </li>
                    <li>
                        <strong>CS ID</strong> —
                        <span x-show="lang==='en'">Canvass Sheet linked to that purchase</span>
                        <span x-show="lang==='id'">Canvass Sheet yang terkait dengan pembelian tersebut</span>
                    </li>
                    <li>
                        <strong>Vendor</strong> —
                        <span x-show="lang==='en'">Supplier used for the last purchase</span>
                        <span x-show="lang==='id'">Supplier yang digunakan pada pembelian terakhir</span>
                    </li>
                    <li>
                        <strong>Inventory ID & Description</strong> —
                        <span x-show="lang==='en'">Item code and name</span>
                        <span x-show="lang==='id'">Kode dan nama item</span>
                    </li>
                    <li>
                        <strong>Unit Cost</strong> —
                        <span x-show="lang==='en'">Last purchase price per unit</span>
                        <span x-show="lang==='id'">Harga beli terakhir per satuan</span>
                    </li>
                    <li>
                        <strong>Purchaser</strong> —
                        <span x-show="lang==='en'">Staff who handled the purchase</span>
                        <span x-show="lang==='id'">Staff yang menangani pembelian</span>
                    </li>
                </ul>

            </div>
        </div>
    </section>

    <!-- ================= SECTION 2 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">2. Last Order BQ</span>
                    <span x-show="lang==='id'">2. Last Order BQ</span>
                </span>
                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The <strong>Last Order BQ</strong> tab shows the most recent purchase history
                        for Bill of Quantity (BQ) items — goods or services that are priced and
                        quoted through a BQ canvass process. Use this as a reference when creating
                        a new BQ canvass for similar items.
                    </span>
                    <span x-show="lang==='id'">
                        Tab <strong>Last Order BQ</strong> menampilkan riwayat pembelian terbaru
                        untuk item Bill of Quantity (BQ) — barang atau jasa yang dihargai dan
                        dikuotasi melalui proses canvass BQ. Gunakan ini sebagai referensi saat
                        membuat canvass BQ baru untuk item serupa.
                    </span>
                </p>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click the tab buttons at the top right of the page to switch between
                        <strong>Last Order Inventory</strong> and <strong>Last Order BQ</strong>.
                    </span>
                    <span x-show="lang==='id'">
                        Klik tombol tab di kanan atas halaman untuk beralih antara
                        <strong>Last Order Inventory</strong> dan <strong>Last Order BQ</strong>.
                    </span>
                </p>

            </div>
        </div>
    </section>

</div>
