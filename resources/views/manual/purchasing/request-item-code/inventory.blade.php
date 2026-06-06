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
                The Inventory page allows users to browse the company's item catalogue, including both
                stock and non-stock items. Users can also check current stock levels at specific
                business units. This page is used as a reference when creating purchase requests.
            </span>
            <span x-show="lang==='id'">
                Halaman Inventory memungkinkan pengguna untuk menelusuri katalog barang perusahaan,
                termasuk item stock dan non-stock. Pengguna juga dapat melihat jumlah stok yang tersedia
                di business unit tertentu. Halaman ini digunakan sebagai referensi saat membuat
                permintaan pembelian.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">1. Filtering the Inventory List</span>
                    <span x-show="lang==='id'">1. Filter Daftar Inventory</span>
                </span>
                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Use the filter buttons and selectors at the top of the page to narrow down the inventory list.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan tombol filter dan selector di bagian atas halaman untuk menyaring daftar inventory.
                    </span>
                </p>

                <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Stock</strong> —
                        <span x-show="lang==='en'">Show only stock items (goods stored in the warehouse)</span>
                        <span x-show="lang==='id'">Tampilkan hanya item stock (barang yang disimpan di gudang)</span>
                    </li>
                    <li>
                        <strong>Non-Stock</strong> —
                        <span x-show="lang==='en'">Show only non-stock items (items purchased directly without warehouse storage)</span>
                        <span x-show="lang==='id'">Tampilkan hanya item non-stock (barang yang dibeli langsung tanpa disimpan di gudang)</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Company & Business Unit</span>
                            <span x-show="lang==='id'">Company & Business Unit</span>
                        </strong> —
                        <span x-show="lang==='en'">Select your company and business unit to check stock availability at that location</span>
                        <span x-show="lang==='id'">Pilih company dan business unit untuk melihat ketersediaan stok di lokasi tersebut</span>
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
                    <span x-show="lang==='en'">2. Inventory Table Columns</span>
                    <span x-show="lang==='id'">2. Kolom Tabel Inventory</span>
                </span>
                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Inventory ID</strong> —
                        <span x-show="lang==='en'">Unique item code in the system</span>
                        <span x-show="lang==='id'">Kode item unik dalam sistem</span>
                    </li>
                    <li>
                        <strong>Description</strong> —
                        <span x-show="lang==='en'">Full name / description of the item</span>
                        <span x-show="lang==='id'">Nama lengkap / deskripsi barang</span>
                    </li>
                    <li>
                        <strong>Item Type</strong> —
                        <span x-show="lang==='en'">Stock or Non-Stock classification</span>
                        <span x-show="lang==='id'">Klasifikasi Stock atau Non-Stock</span>
                    </li>
                    <li>
                        <strong>Unit</strong> —
                        <span x-show="lang==='en'">Unit of measurement for the item</span>
                        <span x-show="lang==='id'">Satuan pengukuran barang</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Stock (quantity)</span>
                            <span x-show="lang==='id'">Stok (jumlah)</span>
                        </strong> —
                        <span x-show="lang==='en'">Current available quantity at the selected business unit (shown for Stock items only)</span>
                        <span x-show="lang==='id'">Jumlah stok yang tersedia di business unit yang dipilih (ditampilkan hanya untuk item Stock)</span>
                    </li>
                    <li>
                        <strong>Status</strong> —
                        <span x-show="lang==='en'">Active or inactive status of the item</span>
                        <span x-show="lang==='id'">Status aktif atau tidak aktif dari item</span>
                    </li>
                </ul>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        The stock column only appears when the <strong>Stock</strong> filter is active and a
                        <strong>Business Unit</strong> is selected. Without a business unit selection,
                        the stock quantity will not be displayed.
                    </span>
                    <span x-show="lang==='id'">
                        Kolom stok hanya muncul ketika filter <strong>Stock</strong> aktif dan
                        <strong>Business Unit</strong> sudah dipilih. Tanpa pemilihan business unit,
                        jumlah stok tidak akan ditampilkan.
                    </span>
                </div>

            </div>
        </div>
    </section>

</div>
