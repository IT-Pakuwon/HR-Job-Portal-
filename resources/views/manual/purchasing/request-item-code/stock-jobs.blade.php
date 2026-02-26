<div x-data="{
    lang: localStorage.getItem('manual_lang') || 'id',
    setLang(v) {
        this.lang = v;
        localStorage.setItem('manual_lang', v);
    }
}" class="max-w-9xl mx-auto space-y-6 p-6">

    <!-- ================= LANGUAGE TOGGLE ================= -->
    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')"
                :class="lang === 'id' ? 'bg-gray-900 text-white' : 'text-gray-600 dark:text-gray-300'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition">
                ID
            </button>
            <button @click="setLang('en')"
                :class="lang === 'en' ? 'bg-gray-900 text-white' : 'text-gray-600 dark:text-gray-300'"
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
                Seluruh data yang ditampilkan dalam manual ini merupakan data dummy
                yang digunakan hanya sebagai contoh.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 4 ================= -->
    <section class="space-y-10">

        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">1. Stock Job</span>
            <span x-show="lang==='id'">1. Job Stock</span>
        </h2>

        <!-- 1.1 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.1 Stock Job Overview</span>
                <span x-show="lang==='id'">1.1 Gambaran Stock Job</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    Stock Jobs are automatically generated from approved Item Requests
                    with Inventory Type set to <strong>STOCK</strong>.
                </span>
                <span x-show="lang==='id'">
                    Stock Job terbentuk secara otomatis dari Item Request
                    dengan Inventory Type <strong>STOCK</strong>.
                </span>
            </p>

            <div class="manual-note manual-info">
                <span x-show="lang==='en'">
                    Stock Jobs require valid Inventory ID assignment before completion.
                </span>
                <span x-show="lang==='id'">
                    Stock Job memerlukan penentuan Inventory ID sebelum dianggap selesai.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/stock/jobs-overview.png') }}">
                    <figcaption>Figure 1.1 – Stock Jobs Dashboard</figcaption>
                </figure>
            </div>
        </section>

        <!-- 1.2 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.2 Stock Job Status</span>
                <span x-show="lang==='id'">1.2 Status Stock Job</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    Stock Jobs are divided into <strong>JOB</strong> and <strong>DONE</strong>.
                </span>
                <span x-show="lang==='id'">
                    Stock Job terbagi menjadi status <strong>JOB</strong> dan <strong>DONE</strong>.
                </span>
            </p>

            <div class="manual-note manual-warning">
                <span x-show="lang==='en'">
                    JOB means Inventory ID is not yet assigned.
                    DONE means Inventory ID has been assigned.
                </span>
                <span x-show="lang==='id'">
                    JOB berarti Inventory ID belum ditentukan.
                    DONE berarti Inventory ID telah ditentukan.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/stock/status-cards.png') }}">
                    <figcaption>Figure 1.2 – Stock Job Status Cards</figcaption>
                </figure>
            </div>
        </section>

        <!-- 1.3 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.3 Assign Inventory</span>
                <span x-show="lang==='id'">1.3 Assign Inventory</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    If Inventory ID is empty, click 🔎 to assign an existing inventory.
                </span>
                <span x-show="lang==='id'">
                    Jika Inventory ID kosong, klik 🔎 untuk memilih inventory.
                </span>
            </p>

            <div class="manual-note manual-important">
                <span x-show="lang==='en'">
                    After assignment, the job automatically changes to DONE.
                </span>
                <span x-show="lang==='id'">
                    Setelah assignment, status otomatis berubah menjadi DONE.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/stock/assign-inventory.png') }}">
                    <figcaption>Figure 1.3 – Assign Inventory</figcaption>
                </figure>
            </div>
        </section>

        <!-- 1.4 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.4 Create New Inventory</span>
                <span x-show="lang==='id'">1.4 Membuat Inventory Baru</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    If inventory does not exist, use <strong>+ Add Inventory</strong>.
                </span>
                <span x-show="lang==='id'">
                    Jika inventory belum tersedia, gunakan tombol <strong>+ Add Inventory</strong>.
                </span>
            </p>

            <div class="manual-note manual-warning">
                <span x-show="lang==='en'">
                    Classification must follow the official structure.
                </span>
                <span x-show="lang==='id'">
                    Klasifikasi harus mengikuti struktur resmi.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/stock/add-inventory.png') }}">
                    <figcaption>Figure 1.4 – Add Inventory Modal</figcaption>
                </figure>
            </div>
        </section>

        <!-- 1.5 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.5 Inventory Status Control</span>
                <span x-show="lang==='id'">1.5 Kontrol Status Inventory</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    Inventory records may be activated or deactivated via toggle switch.
                </span>
                <span x-show="lang==='id'">
                    Data inventory dapat diaktifkan atau dinonaktifkan melalui toggle.
                </span>
            </p>

            <div class="manual-note manual-warning">
                <span x-show="lang==='en'">
                    Inactive inventory cannot be used for new Stock Jobs.
                </span>
                <span x-show="lang==='id'">
                    Inventory Inactive tidak dapat digunakan untuk Stock Job baru.
                </span>
            </div>
        </section>

    </section>

</div>
