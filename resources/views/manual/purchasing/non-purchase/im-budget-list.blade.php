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
            <span x-show="lang==='en'">IM Budget List (Non Purchase) displays the list of internal memo budget requests that are not linked to a standard purchasing flow. These are used for direct budget transfers or internal cost allocations that do not go through the regular procurement process.</span>
            <span x-show="lang==='id'">IM Budget List (Non Purchase) menampilkan daftar permintaan budget internal memo yang tidak terkait dengan alur pembelian standar. Ini digunakan untuk transfer budget langsung atau alokasi biaya internal yang tidak melalui proses pengadaan reguler.</span>
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 font-semibold text-gray-900 dark:text-white"><span x-show="lang==='en'">How to Use</span><span x-show="lang==='id'">Cara Menggunakan</span></div>
        <div class="space-y-4 px-6 pb-6">
            <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                <li><span x-show="lang==='en'">The list shows all IM Budget requests with their status (On Progress / Completed / Rejected).</span><span x-show="lang==='id'">Daftar menampilkan semua permintaan IM Budget beserta statusnya (On Progress / Completed / Rejected).</span></li>
                <li><span x-show="lang==='en'">Use the filter options to narrow down by company, department, or date range.</span><span x-show="lang==='id'">Gunakan pilihan filter untuk mempersempit berdasarkan company, departemen, atau rentang tanggal.</span></li>
                <li><span x-show="lang==='en'">Click on any record to view the full IM Budget detail and approval history.</span><span x-show="lang==='id'">Klik pada record mana pun untuk melihat detail IM Budget lengkap dan riwayat approval.</span></li>
            </ul>
        </div>
    </div>
</div>
