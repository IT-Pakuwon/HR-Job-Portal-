<div x-data="{ lang: localStorage.getItem('manual_lang')||'id', setLang(v){this.lang=v;localStorage.setItem('manual_lang',v);} }" class="max-w-9xl mx-auto space-y-6 p-6">
    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')" :class="lang==='id'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">ID</button>
            <button @click="setLang('en')" :class="lang==='en'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">EN</button>
        </div>
    </div>

    <div class="manual-note manual-info">
        <strong><span x-show="lang==='en'">Overview</span><span x-show="lang==='id'">Gambaran Umum</span></strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">Report Detail WH is a read-only report page that provides a detailed summary of warehouse transactions including stock requests (SPB), receipts, and stock issues across departments and date ranges. Data is generated automatically — no input required.</span>
            <span x-show="lang==='id'">Report Detail WH adalah halaman laporan yang menyajikan ringkasan detail transaksi gudang termasuk permintaan barang (SPB), penerimaan, dan pengeluaran barang antar departemen dan rentang tanggal. Data dihasilkan otomatis oleh sistem — tidak diperlukan input data.</span>
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 font-semibold text-gray-900 dark:text-white"><span x-show="lang==='en'">How to Use</span><span x-show="lang==='id'">Cara Menggunakan</span></div>
        <div class="space-y-4 px-6 pb-6">
            <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                <li><span x-show="lang==='en'">Filter by date range to select the period you want to view.</span><span x-show="lang==='id'">Filter berdasarkan rentang tanggal untuk memilih periode yang ingin dilihat.</span></li>
                <li><span x-show="lang==='en'">Filter by department, company, or transaction type to narrow down results.</span><span x-show="lang==='id'">Filter berdasarkan departemen, company, atau jenis transaksi untuk mempersempit hasil.</span></li>
                <li><span x-show="lang==='en'">Data can be exported to Excel for further analysis or reporting.</span><span x-show="lang==='id'">Data dapat diekspor ke Excel untuk analisis atau pelaporan lebih lanjut.</span></li>
            </ul>
            <div class="manual-note manual-warning">
                <strong><span x-show="lang==='en'">Note</span><span x-show="lang==='id'">Catatan</span></strong>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">This page is view-only. To manage warehouse transactions, use SPB List, Receipt List, or Issue List.</span>
                    <span x-show="lang==='id'">Halaman ini hanya untuk melihat data. Untuk mengelola transaksi gudang, gunakan SPB List, Receipt List, atau Issue List.</span>
                </p>
            </div>
        </div>
    </div>
</div>
