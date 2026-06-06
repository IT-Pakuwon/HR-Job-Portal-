<div x-data="{
    lang: localStorage.getItem('manual_lang')||'id',
    openSection: 's1',
    setLang(v){this.lang=v;localStorage.setItem('manual_lang',v);},
    toggle(section){this.openSection=this.openSection===section?null:section;}
}" class="max-w-9xl mx-auto space-y-6 p-6">

    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')" :class="lang==='id'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">ID</button>
            <button @click="setLang('en')" :class="lang==='en'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">EN</button>
        </div>
    </div>

    <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300"><span x-show="lang==='en'">Information</span><span x-show="lang==='id'">Informasi</span></h3>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">All data shown in this manual are dummy data used for illustration purposes only.</span>
            <span x-show="lang==='id'">Seluruh data yang ditampilkan dalam manual ini merupakan data dummy yang digunakan hanya sebagai contoh.</span>
        </p>
    </div>

    <div class="manual-note manual-info">
        <strong><span x-show="lang==='en'">Overview</span><span x-show="lang==='id'">Gambaran Umum</span></strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">CALR (Completion Acceptance Letter Request) is the final document issued after an RFCA has been completed and approved. It serves as the official letter confirming that all contractual obligations have been fulfilled.</span>
            <span x-show="lang==='id'">CALR (Completion Acceptance Letter Request) adalah dokumen akhir yang diterbitkan setelah RFCA selesai dan disetujui. Dokumen ini berfungsi sebagai surat resmi yang mengkonfirmasi bahwa semua kewajiban kontraktual telah terpenuhi.</span>
        </p>
    </div>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. Viewing CALR List</span><span x-show="lang==='id'">1. Melihat Daftar CALR</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The CALR List page displays all CALR documents. Each row shows key information about the completion letter:</span>
                    <span x-show="lang==='id'">Halaman CALR List menampilkan semua dokumen CALR. Setiap baris menampilkan informasi utama tentang surat penyelesaian:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>CALR ID</strong> — <span x-show="lang==='en'">Unique document number</span><span x-show="lang==='id'">Nomor dokumen unik</span></li>
                    <li><strong>RFCA Reference</strong> — <span x-show="lang==='en'">The RFCA document this CALR was created from</span><span x-show="lang==='id'">Dokumen RFCA asal CALR ini dibuat</span></li>
                    <li><strong>Vendor</strong></li>
                    <li><strong>Date</strong> — <span x-show="lang==='en'">Date the CALR was issued</span><span x-show="lang==='id'">Tanggal CALR diterbitkan</span></li>
                    <li><strong>Status</strong> — <span x-show="lang==='en'">On Progress / Completed</span><span x-show="lang==='id'">On Progress / Completed</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Creating a CALR</span><span x-show="lang==='id'">2. Membuat CALR</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">A CALR is created from a completed RFCA. Open the relevant RFCA record and use the <strong>Create CALR</strong> action when all RFCA steps are done.</span>
                    <span x-show="lang==='id'">CALR dibuat dari RFCA yang sudah selesai. Buka record RFCA yang relevan dan gunakan aksi <strong>Create CALR</strong> ketika semua tahap RFCA sudah selesai.</span>
                </p>
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The CALR document can be printed as an official PDF letter once approved.</span>
                    <span x-show="lang==='id'">Dokumen CALR dapat dicetak sebagai surat PDF resmi setelah disetujui.</span>
                </p>
            </div>
        </div>
    </section>

</div>
