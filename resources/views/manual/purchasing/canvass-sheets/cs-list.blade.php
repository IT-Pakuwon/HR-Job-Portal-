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
                CS List shows all Canvass Sheets you have submitted. It provides a full history of
                your CS records with their current status, and allows you to open and review
                each individual CS document.
            </span>
            <span x-show="lang==='id'">
                CS List menampilkan semua Canvass Sheet yang telah Anda ajukan. Halaman ini
                memberikan riwayat lengkap seluruh CS Anda beserta status saat ini, dan memungkinkan
                Anda membuka serta mereview setiap dokumen CS.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">1. Status Filter Cards</span>
                    <span x-show="lang==='id'">1. Kartu Filter Status</span>
                </span>
                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click one of the status cards at the top to filter the list by that status.
                        Each card shows the total count for that category.
                    </span>
                    <span x-show="lang==='id'">
                        Klik salah satu kartu status di bagian atas untuk memfilter daftar berdasarkan
                        status tersebut. Setiap kartu menampilkan jumlah total untuk kategori itu.
                    </span>
                </p>

                <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>My CS</strong> —
                        <span x-show="lang==='en'">CS documents you created that are still active</span>
                        <span x-show="lang==='id'">Dokumen CS yang Anda buat dan masih aktif</span>
                    </li>
                    <li>
                        <strong>On Progress</strong> —
                        <span x-show="lang==='en'">CS currently being processed (e.g. under approval or in review)</span>
                        <span x-show="lang==='id'">CS yang sedang diproses (misalnya dalam proses approval atau review)</span>
                    </li>
                    <li>
                        <strong>Reject</strong> —
                        <span x-show="lang==='en'">CS that have been rejected and may require revision</span>
                        <span x-show="lang==='id'">CS yang telah ditolak dan mungkin perlu direvisi</span>
                    </li>
                    <li>
                        <strong>Completed</strong> —
                        <span x-show="lang==='en'">CS that have been fully approved and completed</span>
                        <span x-show="lang==='id'">CS yang telah disetujui sepenuhnya dan selesai</span>
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
                    <span x-show="lang==='en'">2. CS List Table</span>
                    <span x-show="lang==='id'">2. Tabel CS List</span>
                </span>
                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The table below the status cards lists all CS records matching the selected filter.
                        Key information shown per row:
                    </span>
                    <span x-show="lang==='id'">
                        Tabel di bawah kartu status menampilkan semua CS sesuai filter yang dipilih.
                        Informasi utama yang ditampilkan per baris:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>CS ID</strong> —
                        <span x-show="lang==='en'">Canvass Sheet document number</span>
                        <span x-show="lang==='id'">Nomor dokumen Canvass Sheet</span>
                    </li>
                    <li>
                        <strong>CS Date</strong> —
                        <span x-show="lang==='en'">Date the CS was submitted</span>
                        <span x-show="lang==='id'">Tanggal CS diajukan</span>
                    </li>
                    <li>
                        <strong>Company</strong> —
                        <span x-show="lang==='en'">Company the CS belongs to</span>
                        <span x-show="lang==='id'">Company dari CS ini</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Source Document</span>
                            <span x-show="lang==='id'">Dokumen Sumber</span>
                        </strong> —
                        <span x-show="lang==='en'">The SPP reference linked to this CS</span>
                        <span x-show="lang==='id'">Referensi SPP yang terkait dengan CS ini</span>
                    </li>
                    <li>
                        <strong>Status</strong> —
                        <span x-show="lang==='en'">Current CS status</span>
                        <span x-show="lang==='id'">Status CS saat ini</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click on a CS row to open the full detail view of that document.
                    </span>
                    <span x-show="lang==='id'">
                        Klik pada baris CS untuk membuka tampilan detail lengkap dari dokumen tersebut.
                    </span>
                </p>

            </div>
        </div>
    </section>

</div>
