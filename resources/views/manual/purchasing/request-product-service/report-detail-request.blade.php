<div x-data="{
    lang: localStorage.getItem('manual_lang') || 'id',
    setLang(v) { this.lang = v; localStorage.setItem('manual_lang', v); }
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

    <!-- ================= OVERVIEW ================= -->
    <div class="manual-note manual-info">
        <strong>
            <span x-show="lang==='en'">Overview</span>
            <span x-show="lang==='id'">Gambaran Umum</span>
        </strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">
                Report Detail Request is a read-only report page that displays a summary of all
                purchase requests (SPP) submitted by users. The data is generated automatically
                by the system based on existing transactions. No data entry is required on this page.
            </span>
            <span x-show="lang==='id'">
                Report Detail Request adalah halaman laporan yang menampilkan ringkasan semua
                permintaan pembelian (SPP) yang telah diajukan oleh pengguna. Data dihasilkan
                secara otomatis oleh sistem berdasarkan transaksi yang sudah ada.
                Tidak diperlukan input data pada halaman ini.
            </span>
        </p>
    </div>

    <!-- ================= HOW TO USE ================= -->
    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
            <span x-show="lang==='en'">How to Use</span>
            <span x-show="lang==='id'">Cara Menggunakan</span>
        </div>
        <div class="space-y-4 px-6 pb-6">
            <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                <li>
                    <span x-show="lang==='en'">Use the available filters to narrow down the report data by date range, department, or status.</span>
                    <span x-show="lang==='id'">Gunakan filter yang tersedia untuk menyaring data laporan berdasarkan rentang tanggal, departemen, atau status.</span>
                </li>
                <li>
                    <span x-show="lang==='en'">The table displays all purchase request records matching your filter criteria.</span>
                    <span x-show="lang==='id'">Tabel menampilkan semua data permintaan pembelian sesuai filter yang dipilih.</span>
                </li>
                <li>
                    <span x-show="lang==='en'">Data can be exported to Excel for further analysis.</span>
                    <span x-show="lang==='id'">Data dapat diekspor ke Excel untuk analisis lebih lanjut.</span>
                </li>
            </ul>

            <div class="manual-note manual-warning">
                <strong>
                    <span x-show="lang==='en'">Note</span>
                    <span x-show="lang==='id'">Catatan</span>
                </strong>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        This page is view-only. If you need to modify a request, go back to the
                        corresponding SPP form (SPP Barang, SPP Jasa, SPP Kendaraan, or SPP Tenant).
                    </span>
                    <span x-show="lang==='id'">
                        Halaman ini hanya untuk melihat data. Jika perlu mengubah permintaan,
                        kembali ke form SPP yang sesuai (SPP Barang, SPP Jasa, SPP Kendaraan, atau SPP Tenant).
                    </span>
                </p>
            </div>
        </div>
    </div>

</div>
