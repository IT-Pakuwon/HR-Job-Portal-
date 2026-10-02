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
                        All Report is a consolidated reporting dashboard that brings several operational
                        areas into one page, organized into tabs: <strong>Budget</strong>,
                        <strong>PG Card</strong>, <strong>Operation - Isort</strong>,
                        <strong>Parking - Valet</strong>, <strong>Event</strong>, and
                        <strong>Voucher &amp; Product</strong>. An <strong>All</strong> tab shows every
                        section stacked on one scrollable page.
                    </span>
                    <span x-show="lang==='id'">
                        All Report adalah dashboard pelaporan terpadu yang menggabungkan beberapa area
                        operasional dalam satu halaman, terbagi menjadi beberapa tab:
                        <strong>Budget</strong>, <strong>PG Card</strong>,
                        <strong>Operation - Isort</strong>, <strong>Parking - Valet</strong>,
                        <strong>Event</strong>, dan <strong>Voucher &amp; Product</strong>. Tab
                        <strong>All</strong> menampilkan seluruh bagian sekaligus dalam satu halaman yang
                        dapat di-scroll.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        The data behind each tab is company-scoped — you only see figures for the
                        companies your account is allowed to access. If your account is tied to a single
                        company, the company filter is effectively locked to it.
                    </span>
                    <span x-show="lang==='id'">
                        Data di setiap tab bersifat terbatas sesuai perusahaan (company-scoped) — Anda
                        hanya akan melihat angka untuk perusahaan yang diizinkan untuk akun Anda. Jika akun
                        Anda hanya terkait dengan satu perusahaan, filter perusahaan secara otomatis
                        terkunci ke perusahaan tersebut.
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
                    <span x-show="lang==='en'">2. Tabs & Filters</span>
                    <span x-show="lang==='id'">2. Tab & Filter</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.1 Section Tabs</span>
                        <span x-show="lang==='id'">2.1 Tab Bagian</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>Budget</strong> —
                            <span x-show="lang==='en'">Total budget vs. remaining, usage breakdown, monthly absorption trend, and usage tables by department and by activity.</span>
                            <span x-show="lang==='id'">Total budget vs. sisa anggaran, rincian penggunaan, tren penyerapan bulanan, dan tabel penggunaan per departemen serta per aktivitas.</span>
                        </li>
                        <li>
                            <strong>PG Card</strong> —
                            <span x-show="lang==='en'">Coupon figures by mall, plus top customers and top tenants.</span>
                            <span x-show="lang==='id'">Data kupon per mall, serta top customer dan top tenant.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Operation - Isort</span>
                                <span x-show="lang==='id'">Operation - Isort</span>
                            </strong> —
                            <span x-show="lang==='en'">Housekeeping/incident (Isort) activity by type, by department/site, and over time.</span>
                            <span x-show="lang==='id'">Aktivitas housekeeping/insiden (Isort) berdasarkan tipe, departemen/site, dan dari waktu ke waktu.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Parking - Valet</span>
                                <span x-show="lang==='id'">Parking - Valet</span>
                            </strong> —
                            <span x-show="lang==='en'">Valet parking income trend, peak hours, repetitive vehicle numbers, and top transactions.</span>
                            <span x-show="lang==='id'">Tren pendapatan valet parking, jam sibuk, nomor kendaraan yang sering muncul, dan transaksi terbesar.</span>
                        </li>
                        <li>
                            <strong>Event</strong> —
                            <span x-show="lang==='en'">Event activity summary, breakdown by event type, and status per company.</span>
                            <span x-show="lang==='id'">Ringkasan aktivitas event, rincian per tipe event, dan status per perusahaan.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Voucher & Product</span>
                                <span x-show="lang==='id'">Voucher & Product</span>
                            </strong> —
                            <span x-show="lang==='en'">Voucher/product (VPL) overview per company, outgoing voucher leaders, category breakdown, and usage by reason.</span>
                            <span x-show="lang==='id'">Ringkasan voucher/produk (VPL) per perusahaan, voucher dengan pengeluaran terbanyak, rincian per kategori, dan penggunaan berdasarkan alasan.</span>
                        </li>
                    </ul>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.2 Filter Bar</span>
                        <span x-show="lang==='id'">2.2 Filter</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The filter control at the top right lets you choose a company and, where
                            applicable, one or more departments, then pick a date range (with quick presets
                            such as this month or last quarter, or a custom range). All charts and tables on
                            the currently active tab update to match your selection.
                        </span>
                        <span x-show="lang==='id'">
                            Kontrol filter di kanan atas memungkinkan Anda memilih perusahaan dan, jika
                            tersedia, satu atau beberapa departemen, lalu memilih rentang tanggal (dengan
                            preset cepat seperti bulan ini atau kuartal lalu, atau rentang kustom). Seluruh
                            chart dan tabel pada tab yang sedang aktif akan diperbarui sesuai pilihan Anda.
                        </span>
                    </p>

                    <div class="manual-note manual-info">
                        <span x-show="lang==='en'">
                            Each section header shows a <strong>Last Updated</strong> timestamp so you know
                            how fresh the figures are. Insight callouts (the lamp icon on a card) summarize
                            the key takeaway of that chart in plain language.
                        </span>
                        <span x-show="lang==='id'">
                            Setiap judul bagian menampilkan cap waktu <strong>Last Updated</strong> sehingga
                            Anda tahu seberapa baru data yang ditampilkan. Catatan insight (ikon lampu pada
                            kartu) merangkum poin penting dari chart tersebut dalam bahasa yang mudah dipahami.
                        </span>
                    </div>

                </section>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 3 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s3')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">3. Exporting Data</span>
                    <span x-show="lang==='id'">3. Mengekspor Data</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click the <strong>Export</strong> button next to the filter to download the
                        current report as <strong>PDF</strong>, <strong>CSV</strong>, or
                        <strong>XLSX</strong>. The export reflects whatever filters (company, department,
                        date range) and tab are currently applied on screen.
                    </span>
                    <span x-show="lang==='id'">
                        Klik tombol <strong>Export</strong> di sebelah filter untuk mengunduh laporan yang
                        sedang ditampilkan dalam format <strong>PDF</strong>, <strong>CSV</strong>, atau
                        <strong>XLSX</strong>. Hasil export mengikuti filter (perusahaan, departemen,
                        rentang tanggal) dan tab yang sedang aktif di layar.
                    </span>
                </p>

            </div>
        </div>

    </section>

</div>
