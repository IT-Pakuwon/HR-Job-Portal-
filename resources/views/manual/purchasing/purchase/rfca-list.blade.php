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
            <span x-show="lang==='en'">RFCA (Request for Final Completion Acceptance) is a document that formally closes out a completed contract or PO. It confirms that all deliverables have been met and triggers the final payment steps. The RFCA process goes through multiple approval steps before it is finalized.</span>
            <span x-show="lang==='id'">RFCA (Request for Final Completion Acceptance) adalah dokumen yang secara resmi menutup kontrak atau PO yang telah selesai. Dokumen ini mengkonfirmasi bahwa semua deliverable telah terpenuhi dan memicu langkah pembayaran akhir. Proses RFCA melalui beberapa tahap approval sebelum diselesaikan.</span>
        </p>
    </div>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. RFCA List Overview</span><span x-show="lang==='id'">1. Tampilan Daftar RFCA</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The RFCA List page shows all RFCA documents with their current status. Use the status cards at the top to filter the list:</span>
                    <span x-show="lang==='id'">Halaman RFCA List menampilkan semua dokumen RFCA beserta status saat ini. Gunakan kartu status di bagian atas untuk memfilter daftar:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>RFCA Jobs</strong> — <span x-show="lang==='en'">New RFCAs that have not yet been processed</span><span x-show="lang==='id'">RFCA baru yang belum diproses</span></li>
                    <li><strong>On Progress</strong> — <span x-show="lang==='en'">RFCA currently going through approval steps</span><span x-show="lang==='id'">RFCA yang sedang melalui tahap approval</span></li>
                    <li><strong>Completed</strong> — <span x-show="lang==='en'">RFCA that have been fully approved and closed</span><span x-show="lang==='id'">RFCA yang telah disetujui sepenuhnya dan ditutup</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. RFCA Approval Steps</span><span x-show="lang==='id'">2. Tahap Approval RFCA</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Each RFCA goes through a series of steps that must be completed in sequence. Open an RFCA record to view its progress and complete your assigned step.</span>
                    <span x-show="lang==='id'">Setiap RFCA melalui serangkaian tahap yang harus diselesaikan secara berurutan. Buka record RFCA untuk melihat progresnya dan menyelesaikan tahap yang ditugaskan kepada Anda.</span>
                </p>
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Once all steps are completed, the RFCA is marked as Completed and a CALR can be generated from it.</span>
                    <span x-show="lang==='id'">Setelah semua tahap selesai, RFCA ditandai sebagai Completed dan CALR dapat dibuat darinya.</span>
                </p>
            </div>
        </div>
    </section>

</div>
