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
                CS Jobs is the main dashboard for the Canvass Sheet workflow. It shows a summary
                of all canvass sheet tasks assigned to you or your team, grouped by their current status.
                Use this page to monitor workload and quickly access CS records that need action.
            </span>
            <span x-show="lang==='id'">
                CS Jobs adalah dashboard utama untuk alur kerja Canvass Sheet. Halaman ini menampilkan
                ringkasan semua tugas canvass sheet yang ditugaskan kepada Anda atau tim Anda,
                dikelompokkan berdasarkan status saat ini. Gunakan halaman ini untuk memantau
                beban kerja dan mengakses CS yang memerlukan tindakan.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">1. Dashboard Summary Cards</span>
                    <span x-show="lang==='id'">1. Kartu Ringkasan Dashboard</span>
                </span>
                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        At the top of the page, several summary cards show the total count of CS records
                        in each category. Click on a card to filter the table below to show only those records.
                    </span>
                    <span x-show="lang==='id'">
                        Di bagian atas halaman, beberapa kartu ringkasan menampilkan jumlah total CS
                        di setiap kategori. Klik kartu untuk memfilter tabel di bawah agar hanya
                        menampilkan record dari kategori tersebut.
                    </span>
                </p>

                <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>CS Jobs</strong> —
                        <span x-show="lang==='en'">CS records assigned to you that are still in progress</span>
                        <span x-show="lang==='id'">CS yang ditugaskan kepada Anda dan masih dalam proses pengerjaan</span>
                    </li>
                    <li>
                        <strong>CS Reuse</strong> —
                        <span x-show="lang==='en'">CS records that are being reused or revised from a previous canvass</span>
                        <span x-show="lang==='id'">CS yang sedang digunakan ulang atau direvisi dari canvass sebelumnya</span>
                    </li>
                    <li>
                        <strong>All CS Jobs</strong> —
                        <span x-show="lang==='en'">All CS records across all purchasers visible to you</span>
                        <span x-show="lang==='id'">Semua CS dari semua purchaser yang dapat Anda lihat</span>
                    </li>
                    <li>
                        <strong>SPPBJKT In Progress</strong> —
                        <span x-show="lang==='en'">Purchase requests (SPP) that already have a CS created and are currently being processed</span>
                        <span x-show="lang==='id'">Permintaan pembelian (SPP) yang sudah memiliki CS dan sedang diproses</span>
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
                    <span x-show="lang==='en'">2. CS Jobs Table</span>
                    <span x-show="lang==='id'">2. Tabel CS Jobs</span>
                </span>
                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Below the summary cards, a table lists the CS records matching the active filter.
                        Each row shows key information about the canvass sheet:
                    </span>
                    <span x-show="lang==='id'">
                        Di bawah kartu ringkasan, tabel menampilkan daftar CS sesuai filter yang aktif.
                        Setiap baris menampilkan informasi utama dari canvass sheet:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>CS ID</strong> —
                        <span x-show="lang==='en'">Unique Canvass Sheet number</span>
                        <span x-show="lang==='id'">Nomor Canvass Sheet unik</span>
                    </li>
                    <li>
                        <strong>CS Date</strong> —
                        <span x-show="lang==='en'">Date the CS was created</span>
                        <span x-show="lang==='id'">Tanggal CS dibuat</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Source Document</span>
                            <span x-show="lang==='id'">Dokumen Sumber</span>
                        </strong> —
                        <span x-show="lang==='en'">The SPP reference that triggered this CS</span>
                        <span x-show="lang==='id'">Referensi SPP yang memicu pembuatan CS ini</span>
                    </li>
                    <li>
                        <strong>Purchaser</strong> —
                        <span x-show="lang==='en'">Staff assigned to handle this CS</span>
                        <span x-show="lang==='id'">Staff yang ditugaskan untuk menangani CS ini</span>
                    </li>
                    <li>
                        <strong>Status</strong> —
                        <span x-show="lang==='en'">Current status of the CS</span>
                        <span x-show="lang==='id'">Status CS saat ini</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click on any row to open the CS detail page and continue working on the canvass.
                    </span>
                    <span x-show="lang==='id'">
                        Klik pada baris mana pun untuk membuka halaman detail CS dan melanjutkan pengerjaan canvass.
                    </span>
                </p>

            </div>
        </div>
    </section>

</div>
