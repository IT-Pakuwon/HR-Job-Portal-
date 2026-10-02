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
            <span x-show="lang==='en'">RFP/RFCA List (Non Purchase) shows payment requests and completion acceptance documents for transactions outside the standard purchasing flow — such as reimbursements, direct costs, or internal service charges. These follow the same approval workflow as their purchasing counterparts but are linked to non-procurement sources.</span>
            <span x-show="lang==='id'">RFP/RFCA List (Non Purchase) menampilkan permintaan pembayaran dan dokumen penerimaan penyelesaian untuk transaksi di luar alur pembelian standar — seperti reimbursement, biaya langsung, atau biaya layanan internal. Dokumen ini mengikuti alur approval yang sama seperti pembelian biasa, tetapi terkait dengan sumber non-pengadaan.</span>
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 font-semibold text-gray-900 dark:text-white"><span x-show="lang==='en'">How to Use</span><span x-show="lang==='id'">Cara Menggunakan</span></div>
        <div class="space-y-4 px-6 pb-6">
            <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                <li><span x-show="lang==='en'">The list shows all Non-Purchase RFP and RFCA documents with their current status.</span><span x-show="lang==='id'">Daftar menampilkan semua dokumen RFP dan RFCA Non-Purchase beserta status saat ini.</span></li>
                <li><span x-show="lang==='en'">Use the status cards to quickly filter by On Progress, Revise/Draft, or Completed.</span><span x-show="lang==='id'">Gunakan kartu status untuk memfilter dengan cepat berdasarkan On Progress, Revise/Draft, atau Completed.</span></li>
                <li><span x-show="lang==='en'">Click on a record to open the full detail, view the approval progress, and complete your step if assigned.</span><span x-show="lang==='id'">Klik pada record untuk membuka detail lengkap, melihat progres approval, dan menyelesaikan tahap Anda jika ditugaskan.</span></li>
            </ul>
        </div>
    </div>
</div>
