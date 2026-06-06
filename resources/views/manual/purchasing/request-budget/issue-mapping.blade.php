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

    <!-- ================= OVERVIEW ================= -->
    <div class="manual-note manual-info">
        <strong>
            <span x-show="lang==='en'">Overview</span>
            <span x-show="lang==='id'">Gambaran Umum</span>
        </strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                Issue Mapping is used to review warehouse stock issues and link them to the
                correct budget accounts. Users can monitor the status of each issue record
                and update it as the review progresses.
            </span>
            <span x-show="lang==='id'">
                Issue Mapping digunakan untuk mereview pengeluaran barang dari gudang (stock issue)
                dan menghubungkannya ke akun budget yang sesuai. Pengguna dapat memantau status
                setiap record dan memperbaruinya seiring proses review berjalan.
            </span>
        </p>
    </div>

    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span>
                    <span x-show="lang==='en'">1. Viewing the Issue Mapping List</span>
                    <span x-show="lang==='id'">1. Melihat Daftar Issue Mapping</span>
                </span>
                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The Issue Mapping page displays all warehouse stock issues available for your company.
                        Each row in the table represents one issue document with the following information:
                    </span>
                    <span x-show="lang==='id'">
                        Halaman Issue Mapping menampilkan semua stock issue gudang yang tersedia untuk company Anda.
                        Setiap baris dalam tabel mewakili satu dokumen issue dengan informasi berikut:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Cpny</strong> —
                        <span x-show="lang==='en'">Company associated with the issue</span>
                        <span x-show="lang==='id'">Company yang terkait dengan issue</span>
                    </li>
                    <li>
                        <strong>Issue ID</strong> —
                        <span x-show="lang==='en'">Unique stock issue document number</span>
                        <span x-show="lang==='id'">Nomor dokumen stock issue yang unik</span>
                    </li>
                    <li>
                        <strong>Issue Date</strong> —
                        <span x-show="lang==='en'">Date the stock issue was created</span>
                        <span x-show="lang==='id'">Tanggal stock issue dibuat</span>
                    </li>
                    <li>
                        <strong>Reference No</strong> —
                        <span x-show="lang==='en'">Reference number of the related document</span>
                        <span x-show="lang==='id'">Nomor referensi dokumen yang terkait</span>
                    </li>
                    <li>
                        <strong>SPB ID</strong> —
                        <span x-show="lang==='en'">Related Surat Permintaan Barang number</span>
                        <span x-show="lang==='id'">Nomor Surat Permintaan Barang yang terkait</span>
                    </li>
                    <li>
                        <strong>WO ID</strong> —
                        <span x-show="lang==='en'">Related Work Order number</span>
                        <span x-show="lang==='id'">Nomor Work Order yang terkait</span>
                    </li>
                    <li>
                        <strong>Status</strong> —
                        <span x-show="lang==='en'">Current mapping status of the issue</span>
                        <span x-show="lang==='id'">Status mapping issue saat ini</span>
                    </li>
                </ul>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span x-show="lang==='en'">Status Labels</span>
                    <span x-show="lang==='id'">Keterangan Status</span>
                </h3>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Waiting Review</strong> —
                        <span x-show="lang==='en'">Issue received and waiting to be reviewed</span>
                        <span x-show="lang==='id'">Issue diterima dan menunggu untuk direview</span>
                    </li>
                    <li>
                        <strong>Review</strong> —
                        <span x-show="lang==='en'">Issue is currently under review</span>
                        <span x-show="lang==='id'">Issue sedang dalam proses review</span>
                    </li>
                    <li>
                        <strong>Completed</strong> —
                        <span x-show="lang==='en'">Mapping has been completed</span>
                        <span x-show="lang==='id'">Mapping telah selesai dikerjakan</span>
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
                    <span x-show="lang==='en'">2. Filter & Search</span>
                    <span x-show="lang==='id'">2. Filter & Pencarian</span>
                </span>
                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Use the filter and search bar to find specific issue records quickly.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan filter dan kolom pencarian untuk menemukan record issue tertentu dengan cepat.
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Filter Status</span>
                            <span x-show="lang==='id'">Filter Status</span>
                        </strong> —
                        <span x-show="lang==='en'">Show only records with a specific status (All / Waiting Review / Review / Completed)</span>
                        <span x-show="lang==='id'">Tampilkan hanya record dengan status tertentu (All / Waiting Review / Review / Completed)</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Search</span>
                            <span x-show="lang==='id'">Pencarian</span>
                        </strong> —
                        <span x-show="lang==='en'">Type a keyword such as Company, Issue ID, Reference No, SPB ID, WO ID, or requester name</span>
                        <span x-show="lang==='id'">Ketik kata kunci seperti Company, Issue ID, Reference No, SPB ID, WO ID, atau nama peminta</span>
                    </li>
                    <li>
                        <strong>Apply</strong> —
                        <span x-show="lang==='en'">Click to apply the selected filters and search keyword</span>
                        <span x-show="lang==='id'">Klik untuk menerapkan filter dan kata kunci pencarian</span>
                    </li>
                    <li>
                        <strong>Reload</strong> —
                        <span x-show="lang==='en'">Refresh the table to load the latest issue data</span>
                        <span x-show="lang==='id'">Refresh tabel untuk memuat data issue terbaru</span>
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
                    <span x-show="lang==='en'">3. Reviewing an Issue</span>
                    <span x-show="lang==='id'">3. Mereview Issue</span>
                </span>
                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="manual-note manual-info text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click the 🔍 icon on any row to open the Detail & Mapping panel for that issue.
                    </span>
                    <span x-show="lang==='id'">
                        Klik ikon 🔍 pada baris mana pun untuk membuka panel Detail & Mapping dari issue tersebut.
                    </span>
                </p>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span x-show="lang==='en'">3.1 Issue Header</span>
                    <span x-show="lang==='id'">3.1 Header Issue</span>
                </h3>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The top section displays a summary of the issue document including Company,
                        Issue ID, Issue Date, Description, Reference No, SPB ID, WO ID, Department,
                        and Requester. These fields are read-only.
                    </span>
                    <span x-show="lang==='id'">
                        Bagian atas menampilkan ringkasan dokumen issue termasuk Company,
                        Issue ID, Issue Date, Deskripsi, Reference No, SPB ID, WO ID, Departemen,
                        dan Peminta. Field ini bersifat read-only.
                    </span>
                </p>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span x-show="lang==='en'">3.2 Review Note & Status Update</span>
                    <span x-show="lang==='id'">3.2 Catatan Review & Perbarui Status</span>
                </h3>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Users can add a review note and change the status of the issue mapping.
                        Update the status to <strong>Review</strong> while working on it,
                        and to <strong>Completed</strong> once the mapping is finished.
                    </span>
                    <span x-show="lang==='id'">
                        Pengguna dapat menambahkan catatan review dan mengubah status mapping issue.
                        Ubah status ke <strong>Review</strong> saat sedang dikerjakan,
                        dan ke <strong>Completed</strong> setelah mapping selesai.
                    </span>
                </p>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span x-show="lang==='en'">3.3 Issue Line Items</span>
                    <span x-show="lang==='id'">3.3 Baris Detail Issue</span>
                </h3>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The detail table lists each item in the stock issue. Each line can be reviewed
                        and its account mapping fields can be filled in accordingly.
                        After all lines are completed, click <strong>Save Mapping</strong> to save.
                    </span>
                    <span x-show="lang==='id'">
                        Tabel detail menampilkan setiap barang dalam stock issue. Setiap baris dapat direview
                        dan field pemetaan akun-nya dapat diisi sesuai kebutuhan.
                        Setelah semua baris selesai, klik <strong>Save Mapping</strong> untuk menyimpan.
                    </span>
                </p>

                <div class="manual-note manual-warning">
                    <strong>⚠️
                        <span x-show="lang==='en'">Caution</span>
                        <span x-show="lang==='id'">Perhatian</span>
                    </strong>
                    <div class="mt-2 text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Make sure all line items have been reviewed before setting the status to Completed.
                            Once completed, the data will be submitted for further processing.
                        </span>
                        <span x-show="lang==='id'">
                            Pastikan semua baris telah direview sebelum mengubah status menjadi Completed.
                            Setelah selesai, data akan dikirimkan untuk diproses lebih lanjut.
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>
