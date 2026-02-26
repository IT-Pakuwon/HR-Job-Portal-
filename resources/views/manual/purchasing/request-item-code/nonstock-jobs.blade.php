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
                Seluruh data yang ditampilkan dalam manual ini (screenshot, angka, nama, dan dokumen)
                merupakan data dummy yang digunakan hanya sebagai contoh.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 3 ================= -->
    <section class="space-y-10">

        <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">1. Non Stock Job</span>
            <span x-show="lang==='id'">1. Job Non Stock</span>
        </h2>

        <!-- 1.1 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.1 Nonstock Job Overview</span>
                <span x-show="lang==='id'">1.1 Gambaran Nonstock Job</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    Nonstock Jobs are automatically generated from approved Item Requests
                    with Inventory Type set to <strong>NON STOCK</strong>.
                    These jobs require inventory master creation or inventory assignment.
                </span>
                <span x-show="lang==='id'">
                    Nonstock Jobs terbentuk secara otomatis dari Item Request yang telah disetujui
                    dengan Inventory Type <strong>NON STOCK</strong>.
                    Job ini memerlukan pembuatan master inventory atau assignment inventory.
                </span>
            </p>

            <div class="manual-note manual-info">
                <span x-show="lang==='en'">
                    Nonstock Jobs represent pending inventory master actions.
                </span>
                <span x-show="lang==='id'">
                    Nonstock Jobs merepresentasikan proses pembuatan atau penentuan master inventory.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/nonstock/jobs-overview.png') }}">
                    <figcaption>Figure 1.1 – Nonstock Jobs Dashboard</figcaption>
                </figure>
            </div>
        </section>

        <!-- 1.2 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.2 Status Cards</span>
                <span x-show="lang==='id'">1.2 Kartu Status</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    The status cards summarize Nonstock Jobs and Inventory records.
                    Users may filter data by clicking each card.
                </span>
                <span x-show="lang==='id'">
                    Kartu status menampilkan ringkasan Nonstock Jobs dan Inventory.
                    Pengguna dapat memfilter data dengan memilih kartu yang tersedia.
                </span>
            </p>

            <div class="manual-note manual-warning">
                <span x-show="lang==='en'">
                    Selecting <strong>Inventory Nonstock</strong> switches the view
                    from Jobs table to Inventory Master table.
                </span>
                <span x-show="lang==='id'">
                    Memilih <strong>Inventory Nonstock</strong> akan mengubah tampilan
                    ke tabel Master Inventory.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/nonstock/status-cards.png') }}">
                    <figcaption>Figure 3.2 – Nonstock Status Cards</figcaption>
                </figure>
            </div>
        </section>

        <!-- 1.3 -->
        <section class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-show="lang==='en'">1.3 Assign Inventory to Job</span>
                <span x-show="lang==='id'">1.3 Assign Inventory ke Job</span>
            </h3>

            <p class="text-gray-600 dark:text-gray-400">
                <span x-show="lang==='en'">
                    If the Inventory ID field is empty, users must assign an existing inventory
                    by clicking the 🔍 button.
                </span>
                <span x-show="lang==='id'">
                    Jika kolom Inventory ID kosong, pengguna harus memilih inventory
                    dengan menekan tombol 🔍.
                </span>
            </p>

            <div class="manual-note manual-important">
                <span x-show="lang==='en'">
                    Once an Inventory ID is assigned, the job status will change to DONE.
                </span>
                <span x-show="lang==='id'">
                    Setelah Inventory ID ditentukan, status job berubah menjadi DONE.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/nonstock/assign-inventory.png') }}">
                    <figcaption>Figure 1.3 – Assign Inventory to Nonstock Job</figcaption>
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
                    If the required inventory does not exist,
                    users may create a new Inventory Master using
                    <strong>+ Add Inventory</strong>.
                </span>
                <span x-show="lang==='id'">
                    Jika inventory belum tersedia,
                    pengguna dapat membuat Master Inventory baru melalui
                    <strong>+ Add Inventory</strong>.
                </span>
            </p>

            <div class="manual-note manual-warning">
                <span x-show="lang==='en'">
                    Inventory classification must follow the official structure.
                </span>
                <span x-show="lang==='id'">
                    Klasifikasi inventory harus mengikuti struktur resmi.
                </span>
            </div>

            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                <figure class="manual-figure">
                    <img src="{{ asset('images/manual/nonstock/add-inventory.png') }}">
                    <figcaption>Figure 1.4 – Add Inventory Modal</figcaption>
                </figure>
            </div>
        </section>

    </section>
</div>
