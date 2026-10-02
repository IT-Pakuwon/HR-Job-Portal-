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
                        Catalog Chart is a gallery of reusable chart and card templates (KPI/stat cards,
                        line, area, bar, donut/pie, combo, gauge, waterfall, funnel, scatter, treemap,
                        heatmap, radar, candlestick, sankey, tables, and more). Each template is previewed
                        with sample data so you can see how it looks before using it elsewhere. This page
                        is primarily a design/reference tool rather than a live business report.
                    </span>
                    <span x-show="lang==='id'">
                        Catalog Chart adalah galeri template chart dan card yang dapat digunakan ulang (KPI/
                        stat card, line, area, bar, donut/pie, combo, gauge, waterfall, funnel, scatter,
                        treemap, heatmap, radar, candlestick, sankey, tabel, dan lainnya). Setiap template
                        ditampilkan dengan data contoh sehingga Anda bisa melihat tampilannya sebelum
                        digunakan di tempat lain. Halaman ini terutama berfungsi sebagai alat referensi/desain,
                        bukan laporan bisnis yang menampilkan data aktual.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Every number and label on this page is sample/dummy data baked into the template —
                        it does not reflect real figures from any module.
                    </span>
                    <span x-show="lang==='id'">
                        Setiap angka dan label pada halaman ini adalah data contoh/dummy yang sudah
                        tertanam pada template — tidak mencerminkan angka sebenarnya dari modul mana pun.
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
                    <span x-show="lang==='en'">2. BigQuery Explorer</span>
                    <span x-show="lang==='id'">2. BigQuery Explorer</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>BigQuery Explorer</strong> to browse the datasets and tables
                        available in the connected BigQuery project. Click a dataset to see its tables,
                        then click a table to see its column names, data types, and modes — useful when
                        you are planning what data to plug into a new chart.
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>BigQuery Explorer</strong> untuk menjelajahi dataset dan tabel yang
                        tersedia pada project BigQuery yang terhubung. Klik sebuah dataset untuk melihat
                        tabel-tabelnya, lalu klik sebuah tabel untuk melihat nama kolom, tipe data, dan
                        mode-nya — berguna saat Anda merencanakan data apa yang akan digunakan pada chart baru.
                    </span>
                </p>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 3 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s3')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">3. Customize Drag Dashboard</span>
                    <span x-show="lang==='id'">3. Customize Drag Dashboard</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Customize Drag Dashboard</strong> to open the dashboard editor, where
                        you can drag chart cards onto a canvas to lay out your own custom dashboard using
                        the same templates shown in the catalog. Use <strong>Save</strong> to store your
                        layout, and the <strong>Catalog</strong> link in the editor's top bar to return
                        here.
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Customize Drag Dashboard</strong> untuk membuka editor dashboard, di
                        mana Anda dapat menyeret kartu chart ke sebuah canvas untuk menyusun dashboard
                        kustom Anda sendiri menggunakan template yang sama seperti pada katalog. Gunakan
                        <strong>Save</strong> untuk menyimpan layout Anda, dan tautan <strong>Catalog</strong>
                        pada bagian atas editor untuk kembali ke halaman ini.
                    </span>
                </p>

                <div class="manual-note manual-warning">
                    <span x-show="lang==='en'">
                        A saved layout is stored only in your current browser (not on the server), so it
                        will not appear if you switch browsers or devices, and clearing your browser data
                        will remove it.
                    </span>
                    <span x-show="lang==='id'">
                        Layout yang disimpan hanya tersimpan di browser yang sedang Anda gunakan (bukan di
                        server), sehingga tidak akan muncul jika Anda berpindah browser atau perangkat, dan
                        akan hilang jika Anda membersihkan data browser.
                    </span>
                </div>

            </div>
        </div>

    </section>

</div>
