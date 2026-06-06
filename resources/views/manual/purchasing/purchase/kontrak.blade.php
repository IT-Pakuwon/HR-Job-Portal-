<div x-data="{
    lang: localStorage.getItem('manual_lang') || 'id',
    openSection: 's1',
    setLang(v) { this.lang = v; localStorage.setItem('manual_lang', v); },
    toggle(section) { this.openSection = this.openSection === section ? null : section; }
}" class="max-w-9xl mx-auto space-y-6 p-6">

    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')" :class="lang==='id'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">ID</button>
            <button @click="setLang('en')" :class="lang==='en'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">EN</button>
        </div>
    </div>

    <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            <span x-show="lang==='en'">Information</span><span x-show="lang==='id'">Informasi</span>
        </h3>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">All data shown in this manual are dummy data used for illustration purposes only.</span>
            <span x-show="lang==='id'">Seluruh data yang ditampilkan dalam manual ini merupakan data dummy yang digunakan hanya sebagai contoh.</span>
        </p>
    </div>

    <div class="manual-note manual-info">
        <strong><span x-show="lang==='en'">Overview</span><span x-show="lang==='id'">Gambaran Umum</span></strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">Kontrak is used to manage long-term contract agreements with vendors. A contract is typically created from a completed Canvass Sheet and goes through an approval workflow before becoming active.</span>
            <span x-show="lang==='id'">Kontrak digunakan untuk mengelola perjanjian kontrak jangka panjang dengan vendor. Kontrak biasanya dibuat dari Canvass Sheet yang sudah selesai dan melalui proses approval sebelum menjadi aktif.</span>
        </p>
    </div>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. Viewing Contracts</span><span x-show="lang==='id'">1. Melihat Daftar Kontrak</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The Kontrak page shows all contracts related to your account. Use the tabs to switch between views:</span>
                    <span x-show="lang==='id'">Halaman Kontrak menampilkan semua kontrak yang terkait dengan akun Anda. Gunakan tab untuk beralih antara tampilan:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>My Kontrak</strong> — <span x-show="lang==='en'">Contracts you created, with full filter options</span><span x-show="lang==='id'">Kontrak yang Anda buat, dengan pilihan filter lengkap</span></li>
                    <li><strong>All Kontrak</strong> — <span x-show="lang==='en'">All contracts visible to you across the company</span><span x-show="lang==='id'">Semua kontrak yang dapat Anda lihat di seluruh perusahaan</span></li>
                </ul>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><span x-show="lang==='en'">Status Labels</span><span x-show="lang==='id'">Keterangan Status</span></h3>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>Unsend</strong> — <span x-show="lang==='en'">Contract is drafted but not yet submitted for approval</span><span x-show="lang==='id'">Kontrak sudah dibuat tapi belum diajukan untuk approval</span></li>
                    <li><strong>On Progress</strong> — <span x-show="lang==='en'">Contract is currently in the approval process</span><span x-show="lang==='id'">Kontrak sedang dalam proses approval</span></li>
                    <li><strong>Completed</strong> — <span x-show="lang==='en'">Contract has been fully approved and is active</span><span x-show="lang==='id'">Kontrak telah disetujui sepenuhnya dan aktif</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Creating a Contract</span><span x-show="lang==='id'">2. Membuat Kontrak</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">A contract is created from a completed Canvass Sheet. The system automatically carries over the vendor, items, and price information from the CS into the contract form.</span>
                    <span x-show="lang==='id'">Kontrak dibuat dari Canvass Sheet yang sudah selesai. Sistem secara otomatis memindahkan informasi vendor, item, dan harga dari CS ke dalam form kontrak.</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>Company</strong></li>
                    <li><strong>Vendor</strong></li>
                    <li><strong>Category</strong> — <span x-show="lang==='en'">Contract category (e.g. goods, services)</span><span x-show="lang==='id'">Kategori kontrak (misalnya barang, jasa)</span></li>
                    <li><strong><span x-show="lang==='en'">Contract Period</span><span x-show="lang==='id'">Periode Kontrak</span></strong> — <span x-show="lang==='en'">Start and end date of the contract</span><span x-show="lang==='id'">Tanggal mulai dan akhir kontrak</span></li>
                    <li><strong><span x-show="lang==='en'">Items & Pricing</span><span x-show="lang==='id'">Item & Harga</span></strong> — <span x-show="lang==='en'">Agreed items and unit prices</span><span x-show="lang==='id'">Item dan harga satuan yang disepakati</span></li>
                    <li><strong>Attachments</strong> — <span x-show="lang==='en'">Supporting documents (signed contract, etc.)</span><span x-show="lang==='id'">Dokumen pendukung (kontrak yang ditandatangani, dll.)</span></li>
                </ul>
                <div class="manual-note manual-warning">
                    <strong>⚠️ <span x-show="lang==='en'">Caution</span><span x-show="lang==='id'">Perhatian</span></strong>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">After submitting, the contract will enter the approval workflow. Ensure all information is correct before submitting, as changes may require resubmission.</span>
                        <span x-show="lang==='id'">Setelah diajukan, kontrak akan masuk ke alur approval. Pastikan semua informasi sudah benar sebelum submit, karena perubahan mungkin memerlukan pengajuan ulang.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
