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
                Report Detail CS is a read-only report page that displays a detailed summary of
                all Canvass Sheets (CS) across departments and date ranges. The data is generated
                automatically by the system. No data entry is required on this page.
            </span>
            <span x-show="lang==='id'">
                Report Detail CS adalah halaman laporan yang menampilkan ringkasan detail semua
                Canvass Sheet (CS) lintas departemen dan rentang tanggal. Data dihasilkan secara
                otomatis oleh sistem. Tidak diperlukan input data pada halaman ini.
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
                    <span x-show="lang==='en'">Use the date range filter to select the period you want to view.</span>
                    <span x-show="lang==='id'">Gunakan filter rentang tanggal untuk memilih periode yang ingin Anda lihat.</span>
                </li>
                <li>
                    <span x-show="lang==='en'">Filter by department to narrow down CS records to a specific team.</span>
                    <span x-show="lang==='id'">Filter berdasarkan departemen untuk mempersempit data CS ke tim tertentu.</span>
                </li>
                <li>
                    <span x-show="lang==='en'">The report table displays CS line-level details including items, quantities, and prices.</span>
                    <span x-show="lang==='id'">Tabel laporan menampilkan detail baris CS termasuk item, kuantitas, dan harga.</span>
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
                        This page is view-only. To modify a CS record, go to the CS List page
                        and open the relevant document.
                    </span>
                    <span x-show="lang==='id'">
                        Halaman ini hanya untuk melihat data. Untuk mengubah data CS, buka halaman
                        CS List dan pilih dokumen yang relevan.
                    </span>
                </p>
            </div>
        </div>
    </div>

</div>
