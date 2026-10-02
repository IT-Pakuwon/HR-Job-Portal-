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
                        Report Detail FA (Fixed Asset) lists every goods receipt (STTB) line item whose
                        inventory sub-type is <strong>FIXED ASSET</strong> and whose receipt has been
                        completed. It is a read-only report used to track which fixed-asset items have been
                        received against a Purchase Order and whether each line was received in full or only
                        partially.
                    </span>
                    <span x-show="lang==='id'">
                        Report Detail FA (Fixed Asset) menampilkan setiap baris item penerimaan barang (STTB)
                        yang inventory sub-type-nya <strong>FIXED ASSET</strong> dan penerimaannya sudah
                        selesai (completed). Laporan ini bersifat read-only dan digunakan untuk memantau item
                        fixed asset mana saja yang sudah diterima terhadap sebuah Purchase Order, serta apakah
                        setiap baris diterima penuh atau hanya sebagian.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        The data you see is scoped to the companies tied to your account, unless your role has
                        full data access across all companies.
                    </span>
                    <span x-show="lang==='id'">
                        Data yang ditampilkan dibatasi pada perusahaan yang terkait dengan akun Anda, kecuali
                        peran Anda memiliki akses data penuh ke seluruh perusahaan.
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
                    <span x-show="lang==='en'">2. Filtering the Report</span>
                    <span x-show="lang==='id'">2. Memfilter Laporan</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Use the filter panel above the table to narrow the results, then click
                        <strong>Apply</strong>. Click <strong>Reset</strong> to clear every filter back to the
                        full list.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan panel filter di atas tabel untuk mempersempit hasil, lalu klik
                        <strong>Apply</strong>. Klik <strong>Reset</strong> untuk menghapus seluruh filter dan
                        kembali ke daftar lengkap.
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Date From / Date To</span>
                            <span x-show="lang==='id'">Date From / Date To</span>
                        </strong> —
                        <span x-show="lang==='en'">Filters by the receipt (STTB) date.</span>
                        <span x-show="lang==='id'">Menyaring berdasarkan tanggal penerimaan (STTB).</span>
                    </li>
                    <li>
                        <strong>STTB</strong> —
                        <span x-show="lang==='en'">The goods receipt document number.</span>
                        <span x-show="lang==='id'">Nomor dokumen penerimaan barang.</span>
                    </li>
                    <li>
                        <strong>PO</strong> —
                        <span x-show="lang==='en'">The Purchase Order number the receipt was made against.</span>
                        <span x-show="lang==='id'">Nomor Purchase Order yang menjadi dasar penerimaan.</span>
                    </li>
                    <li>
                        <strong>SPPB</strong> —
                        <span x-show="lang==='en'">The purchase request document number tied to the receipt.</span>
                        <span x-show="lang==='id'">Nomor dokumen permintaan pembelian yang terkait dengan penerimaan.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Department</span>
                            <span x-show="lang==='id'">Department</span>
                        </strong> —
                        <span x-show="lang==='en'">The requesting department on the receipt.</span>
                        <span x-show="lang==='id'">Departemen pemohon pada dokumen penerimaan.</span>
                    </li>
                    <li>
                        <strong>Vendor</strong> —
                        <span x-show="lang==='en'">Matches either the vendor code or vendor name.</span>
                        <span x-show="lang==='id'">Mencocokkan kode vendor maupun nama vendor.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Inventory Code / Name</span>
                            <span x-show="lang==='id'">Inventory Code / Name</span>
                        </strong> —
                        <span x-show="lang==='en'">Matches either the item's inventory code or its description.</span>
                        <span x-show="lang==='id'">Mencocokkan kode inventory item maupun deskripsinya.</span>
                    </li>
                    <li>
                        <strong>Status</strong> —
                        <span x-show="lang==='en'"><strong>Full Received</strong> (quantity received equals quantity ordered) or <strong>Partial Received</strong> (less than ordered has been received so far).</span>
                        <span x-show="lang==='id'"><strong>Full Received</strong> (jumlah diterima sama dengan jumlah dipesan) atau <strong>Partial Received</strong> (jumlah yang diterima sejauh ini masih kurang dari yang dipesan).</span>
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
                    <span x-show="lang==='en'">3. Reading the Table & Exporting</span>
                    <span x-show="lang==='id'">3. Membaca Tabel & Mengekspor</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Each row is one fixed-asset line item from a completed receipt, showing the receipt
                        number and date, receipt type, PO and reference receipt numbers, company, SPPB,
                        department, requester, vendor, the item's sub-type/category/code/name, quantity
                        ordered vs. quantity received, unit of measure, and the computed receiving status.
                    </span>
                    <span x-show="lang==='id'">
                        Setiap baris adalah satu item fixed asset dari sebuah penerimaan yang sudah selesai,
                        menampilkan nomor dan tanggal penerimaan, tipe penerimaan, nomor PO dan referensi
                        penerimaan, company, SPPB, departemen, pemohon, vendor, sub-type/kategori/kode/nama
                        item, jumlah dipesan vs. jumlah diterima, satuan, dan status penerimaan yang dihitung
                        otomatis.
                    </span>
                </p>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Export</strong> to download the currently filtered result set as an
                        Excel file.
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Export</strong> untuk mengunduh hasil yang sedang difilter sebagai file
                        Excel.
                    </span>
                </p>

                <div class="manual-note manual-warning">
                    <span x-show="lang==='en'">
                        Only receipts with a fully completed status are included — receipts still in progress,
                        and non-fixed-asset items, never appear on this report.
                    </span>
                    <span x-show="lang==='id'">
                        Hanya penerimaan dengan status sudah selesai sepenuhnya yang ditampilkan — penerimaan
                        yang masih berjalan, serta item non-fixed-asset, tidak akan pernah muncul pada laporan
                        ini.
                    </span>
                </div>

            </div>
        </div>

    </section>

</div>
