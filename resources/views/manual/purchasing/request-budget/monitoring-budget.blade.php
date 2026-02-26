<div x-data="{
    lang: localStorage.getItem('manual_lang') || 'id',
    setLang(v) {
        this.lang = v;
        localStorage.setItem('manual_lang', v);
    }
}" class="max-w-9xl mx-auto space-y-12 p-6">

    <!-- ================= LANGUAGE TOGGLE ================= -->
    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
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

    <!-- ================= SECTION 1 FILTER ================= -->
    <section class="space-y-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">1. Filter Section</span>
            <span x-show="lang==='id'">1. Bagian Filter</span>
        </h2>

        <p class="text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                The filter section allows you to narrow down budget data by:
            </span>
            <span x-show="lang==='id'">
                Bagian filter digunakan untuk menyaring data budget berdasarkan:
            </span>
        </p>

        <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
            <li>Year (Tahun)</li>
            <li>Company</li>
            <li>Business Unit</li>
            <li>Department</li>
        </ul>

        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
            <figure class="manual-figure">
                <img src="{{ asset('images/manual/budget-monitor/filter-section.png') }}"
                    class="rounded-lg border shadow dark:border-gray-800">
                <figcaption class="mt-2 text-center text-xs text-gray-500">
                    Figure 1.1 – Filter Section
                </figcaption>
            </figure>

        </div>

        <p class="text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                When Company is selected, Business Unit will load automatically.
                When Business Unit is selected, Department will load automatically.
            </span>
            <span x-show="lang==='id'">
                Ketika Company dipilih, Business Unit akan muncul otomatis.
                Ketika Business Unit dipilih, Department akan muncul otomatis.
            </span>
        </p>
    </section>

    <!-- ================= SECTION 2 MASTER BUDGET ================= -->
    <section class="space-y-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">2. Master Budget</span>
            <span x-show="lang==='id'">2. Master Budget</span>
        </h2>

        <p class="text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                Master Budget displays summarized budget per COA and Activity.
            </span>
            <span x-show="lang==='id'">
                Master Budget menampilkan ringkasan budget per COA dan Activity.
            </span>
        </p>

        <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
            <li>COA (Account)</li>
            <li>Activity</li>
            <li>Description</li>
            <li>Budget</li>
            <li>Additional</li>
            <li>Reserved</li>
            <li>Used</li>
        </ul>

        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
            <figure class="manual-figure">
                <img src="{{ asset('images/manual/budget-monitor/master-budget.png') }}"
                    class="rounded-lg border shadow dark:border-gray-800">
                <figcaption class="mt-2 text-center text-xs text-gray-500">
                    Figure 2.1 – Master Budget Table
                    </>
            </figure>

        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">Summary Section</span>
            <span x-show="lang==='id'">Bagian Summary</span>
        </h3>

        <p class="text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                The summary box below the table displays total:
                Budget, Additional, Reserved, and Used.
            </span>
            <span x-show="lang==='id'">
                Bagian summary di bawah tabel menampilkan total:
                Budget, Additional, Reserved, dan Used.
            </span>
        </p>

        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
            <figure class="manual-figure">
                <img src="{{ asset('images/manual/budget-monitor/master-summary.png') }}"
                    class="rounded-lg border shadow dark:border-gray-800">
                <figcaption class="mt-2 text-center text-xs text-gray-500">
                    Figure 2.2 – Master Budget Summary
                </figcaption>
            </figure>

        </div>
    </section>

    <!-- ================= SECTION 3 TRX BUDGET ================= -->
    <section class="space-y-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">3. Transaction Budget (Trx Budget)</span>
            <span x-show="lang==='id'">3. Transaksi Budget (Trx Budget)</span>
        </h2>

        <p class="text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                Trx Budget displays detailed transaction records that affect the budget.
            </span>
            <span x-show="lang==='id'">
                Trx Budget menampilkan detail transaksi yang mempengaruhi budget.
            </span>
        </p>

        <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
            <li>Ref No</li>
            <li>Date</li>
            <li>Account</li>
            <li>Activity</li>
            <li>Description</li>
            <li>Flow</li>
            <li>Source</li>
            <li>Amount</li>
        </ul>

        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
            <figure class="manual-figure">
                <img src="{{ asset('images/manual/budget-monitor/trx-budget.png') }}"
                    class="rounded-lg border shadow dark:border-gray-800">
                <figcaption class="mt-2 text-center text-xs text-gray-500">
                    Figure 3.1 – Trx Budget Table
                </figcaption>
            </figure>

        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">Total Amount</span>
            <span x-show="lang==='id'">Total Amount</span>
        </h3>

        <p class="text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                The total amount displayed below the table represents
                the sum of all filtered transactions.
            </span>
            <span x-show="lang==='id'">
                Total amount di bawah tabel merupakan total transaksi
                sesuai filter yang dipilih.
            </span>
        </p>

        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
            <figure class="manual-figure">
                <img src="{{ asset('images/manual/budget-monitor/trx-summary.png') }}"
                    class="rounded-lg border shadow dark:border-gray-800">
                <figcaption class="mt-2 text-center text-xs text-gray-500">
                    Figure 3.2 – Trx Budget Summary
                    </>
            </figure>

        </div>
    </section>

</div>
