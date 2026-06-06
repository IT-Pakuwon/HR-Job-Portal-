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
                The Vendor page displays the company's approved vendor/supplier directory.
                Users can use this page to look up vendor contact details before selecting a
                supplier in the canvass sheet process.
            </span>
            <span x-show="lang==='id'">
                Halaman Vendor menampilkan direktori vendor/supplier yang telah disetujui perusahaan.
                Pengguna dapat menggunakan halaman ini untuk melihat detail kontak vendor sebelum
                memilih supplier dalam proses canvass sheet.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">1. Vendor List Table</span>
                    <span x-show="lang==='id'">1. Tabel Daftar Vendor</span>
                </span>
                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The vendor list shows all registered suppliers available in the system.
                        Each row contains the following information:
                    </span>
                    <span x-show="lang==='id'">
                        Daftar vendor menampilkan semua supplier yang terdaftar dalam sistem.
                        Setiap baris berisi informasi berikut:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Vendor ID</strong> —
                        <span x-show="lang==='en'">Unique vendor code in the system</span>
                        <span x-show="lang==='id'">Kode vendor unik dalam sistem</span>
                    </li>
                    <li>
                        <strong>Vendor Name</strong> —
                        <span x-show="lang==='en'">Full company name of the vendor</span>
                        <span x-show="lang==='id'">Nama lengkap perusahaan vendor</span>
                    </li>
                    <li>
                        <strong>Email</strong> —
                        <span x-show="lang==='en'">Primary email address of the vendor</span>
                        <span x-show="lang==='id'">Alamat email utama vendor</span>
                    </li>
                    <li>
                        <strong>Contact Person</strong> —
                        <span x-show="lang==='en'">Name of the person to contact at this vendor</span>
                        <span x-show="lang==='id'">Nama kontak yang dapat dihubungi di vendor ini</span>
                    </li>
                    <li>
                        <strong>Phone</strong> —
                        <span x-show="lang==='en'">Vendor's phone number</span>
                        <span x-show="lang==='id'">Nomor telepon vendor</span>
                    </li>
                    <li>
                        <strong>Status</strong> —
                        <span x-show="lang==='en'">Active or inactive status of the vendor</span>
                        <span x-show="lang==='id'">Status aktif atau tidak aktif vendor</span>
                    </li>
                </ul>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Use the search bar at the top of the table to quickly find a vendor
                        by name, Vendor ID, or contact information.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan kolom pencarian di atas tabel untuk menemukan vendor dengan cepat
                        berdasarkan nama, Vendor ID, atau informasi kontak.
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
                    <span x-show="lang==='en'">2. Vendor Status</span>
                    <span x-show="lang==='id'">2. Status Vendor</span>
                </span>
                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Active</strong> —
                        <span x-show="lang==='en'">Vendor is currently active and can be selected in canvass sheets</span>
                        <span x-show="lang==='id'">Vendor aktif dan dapat dipilih dalam canvass sheet</span>
                    </li>
                    <li>
                        <strong>Inactive</strong> —
                        <span x-show="lang==='en'">Vendor is no longer active and should not be used for new purchases</span>
                        <span x-show="lang==='id'">Vendor tidak lagi aktif dan tidak boleh digunakan untuk pembelian baru</span>
                    </li>
                </ul>

                <div class="manual-note manual-warning">
                    <strong>
                        <span x-show="lang==='en'">Note</span>
                        <span x-show="lang==='id'">Catatan</span>
                    </strong>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Vendor data is managed by the Purchasing Admin. If a vendor is missing or
                            has incorrect information, please contact the Purchasing team.
                        </span>
                        <span x-show="lang==='id'">
                            Data vendor dikelola oleh Admin Purchasing. Jika ada vendor yang tidak tersedia
                            atau memiliki informasi yang tidak sesuai, hubungi tim Purchasing.
                        </span>
                    </p>
                </div>

            </div>
        </div>
    </section>

</div>
