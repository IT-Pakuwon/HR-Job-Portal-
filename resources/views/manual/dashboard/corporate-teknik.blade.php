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
                        Corporate Teknik is a reporting dashboard that tracks Support Ticket and
                        Berita Acara activity raised through the Corporate Teknik (engineering / building
                        service / fit out) module. It gives a single view of how many requests come in,
                        how they are categorized, which status they are in, and which equipment or topic
                        is requested most.
                    </span>
                    <span x-show="lang==='id'">
                        Corporate Teknik adalah dashboard pelaporan yang memantau aktivitas Support Ticket
                        dan Berita Acara yang diajukan melalui modul Corporate Teknik (engineering /
                        building service / fit out). Dashboard ini memberikan satu tampilan untuk melihat
                        berapa banyak permintaan yang masuk, bagaimana kategorinya, status apa yang sedang
                        berjalan, dan peralatan atau topik apa yang paling sering diminta.
                    </span>
                </p>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 2 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s2')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">2. Filters</span>
                    <span x-show="lang==='id'">2. Filter</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The filter bar at the top of the page controls every chart and the table below it:
                    </span>
                    <span x-show="lang==='id'">
                        Filter di bagian atas halaman mengontrol seluruh chart dan tabel di bawahnya:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Document Type</span>
                            <span x-show="lang==='id'">Document Type</span>
                        </strong> —
                        <span x-show="lang==='en'">Choose which group of documents to report on: Support Ticket (All, Engineering, Building Service, Fit Out) or Berita Acara (All, ENG, Request For Approval, Document Approval).</span>
                        <span x-show="lang==='id'">Memilih kelompok dokumen yang ingin dilaporkan: Support Ticket (All, Engineering, Building Service, Fit Out) atau Berita Acara (All, ENG, Request For Approval, Document Approval).</span>
                    </li>
                    <li>
                        <strong>Company</strong> —
                        <span x-show="lang==='en'">Narrows the report to a specific company; locked to your own company/companies if your account is restricted.</span>
                        <span x-show="lang==='id'">Mempersempit laporan ke perusahaan tertentu; terkunci ke perusahaan Anda jika akun Anda dibatasi.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Date Range</span>
                            <span x-show="lang==='id'">Date Range</span>
                        </strong> —
                        <span x-show="lang==='en'">Filters by the document's date, with quick presets or a custom range.</span>
                        <span x-show="lang==='id'">Menyaring berdasarkan tanggal dokumen, dengan preset cepat atau rentang kustom.</span>
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
                    <span x-show="lang==='en'">3. Reading the Dashboard</span>
                    <span x-show="lang==='id'">3. Membaca Dashboard</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Stat Cards</span>
                            <span x-show="lang==='id'">Stat Cards</span>
                        </strong> —
                        <span x-show="lang==='en'">Total, Completed, On Progress, and Completion Rate for the selected period and document type.</span>
                        <span x-show="lang==='id'">Total, Completed, On Progress, dan Completion Rate untuk periode dan document type yang dipilih.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Ticket Categories by Business Unit</span>
                            <span x-show="lang==='id'">Ticket Categories by Business Unit</span>
                        </strong> —
                        <span x-show="lang==='en'">Chart showing which categories of request come from which business unit.</span>
                        <span x-show="lang==='id'">Chart yang menampilkan kategori permintaan berdasarkan masing-masing business unit.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Key Highlights</span>
                            <span x-show="lang==='id'">Key Highlights</span>
                        </strong> —
                        <span x-show="lang==='en'">A short written summary of the standout points behind the numbers on this page.</span>
                        <span x-show="lang==='id'">Ringkasan tertulis singkat mengenai poin-poin penting di balik angka pada halaman ini.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Status by Category</span>
                            <span x-show="lang==='id'">Status by Category</span>
                        </strong> —
                        <span x-show="lang==='en'">Breaks down each category by its current status (e.g. on progress vs. completed).</span>
                        <span x-show="lang==='id'">Merinci setiap kategori berdasarkan status terkininya (misalnya on progress vs. completed).</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Top Request Topic</span>
                            <span x-show="lang==='id'">Top Request Topic</span>
                        </strong> —
                        <span x-show="lang==='en'">The equipment or request topics raised most often.</span>
                        <span x-show="lang==='id'">Peralatan atau topik permintaan yang paling sering diajukan.</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The <strong>Ticket List</strong> table at the bottom lists the individual documents
                        behind the charts above. Use the search box to find a document, sort any column by
                        clicking its header, and open a document directly from the <strong>Operation</strong>
                        column.
                    </span>
                    <span x-show="lang==='id'">
                        Tabel <strong>Ticket List</strong> di bagian bawah menampilkan dokumen-dokumen
                        individual di balik chart di atas. Gunakan kotak pencarian untuk menemukan dokumen,
                        urutkan kolom apa pun dengan mengklik judul kolomnya, dan buka dokumen langsung dari
                        kolom <strong>Operation</strong>.
                    </span>
                </p>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 4 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s4')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">4. Exporting Data</span>
                    <span x-show="lang==='id'">4. Mengekspor Data</span>
                </span>

                <span x-text="openSection==='s4' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Export</strong> to download the current report as
                        <strong>Export PDF</strong> or <strong>Export Excel</strong>. The export reflects
                        the Document Type, Company, and Date Range currently applied.
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Export</strong> untuk mengunduh laporan yang sedang ditampilkan sebagai
                        <strong>Export PDF</strong> atau <strong>Export Excel</strong>. Hasil export
                        mengikuti Document Type, Company, dan Date Range yang sedang diterapkan.
                    </span>
                </p>

            </div>
        </div>

    </section>

</div>
