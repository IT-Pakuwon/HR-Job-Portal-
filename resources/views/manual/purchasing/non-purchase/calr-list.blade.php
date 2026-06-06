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
            <span x-show="lang==='en'">CALR List (Non Purchase) shows all Completion Acceptance Letter Requests that are generated from Non-Purchase RFCA documents. These serve as the official closure letters for non-procurement transactions once all required approvals are completed.</span>
            <span x-show="lang==='id'">CALR List (Non Purchase) menampilkan semua Completion Acceptance Letter Request yang dihasilkan dari dokumen RFCA Non-Purchase. Dokumen ini berfungsi sebagai surat penutup resmi untuk transaksi non-pengadaan setelah semua approval yang diperlukan selesai.</span>
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 font-semibold text-gray-900 dark:text-white"><span x-show="lang==='en'">How to Use</span><span x-show="lang==='id'">Cara Menggunakan</span></div>
        <div class="space-y-4 px-6 pb-6">
            <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                <li><span x-show="lang==='en'">The list shows all Non-Purchase CALR documents with their status (On Progress / Completed).</span><span x-show="lang==='id'">Daftar menampilkan semua dokumen CALR Non-Purchase beserta statusnya (On Progress / Completed).</span></li>
                <li><span x-show="lang==='en'">Click on any record to view the full CALR detail and print the official document.</span><span x-show="lang==='id'">Klik pada record mana pun untuk melihat detail CALR lengkap dan mencetak dokumen resminya.</span></li>
                <li><span x-show="lang==='en'">A CALR is created automatically from a completed Non-Purchase RFCA — you do not need to create it manually.</span><span x-show="lang==='id'">CALR dibuat secara otomatis dari RFCA Non-Purchase yang sudah selesai — Anda tidak perlu membuatnya secara manual.</span></li>
            </ul>
        </div>
    </div>
</div>
