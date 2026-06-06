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
            <span x-show="lang==='en'">BAST (Berita Acara Serah Terima) is the official handover document that records the physical acceptance of goods or services. It is created after a Purchase Order has been delivered and the items are confirmed received by the department.</span>
            <span x-show="lang==='id'">BAST (Berita Acara Serah Terima) adalah dokumen serah terima resmi yang mencatat penerimaan fisik barang atau jasa. Dokumen ini dibuat setelah Purchase Order dikirimkan dan item dikonfirmasi telah diterima oleh departemen.</span>
        </p>
    </div>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. BAST Dashboard</span><span x-show="lang==='id'">1. Dashboard BAST</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The BAST List page shows summary cards at the top for quick status overview, then a detailed table of all BAST documents.</span>
                    <span x-show="lang==='id'">Halaman BAST List menampilkan kartu ringkasan di bagian atas untuk gambaran status cepat, kemudian tabel detail semua dokumen BAST.</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>BAST Jobs</strong> — <span x-show="lang==='en'">PO deliveries that are ready to be confirmed and need a BAST created</span><span x-show="lang==='id'">Pengiriman PO yang siap dikonfirmasi dan perlu dibuatkan BAST</span></li>
                    <li><strong>On Progress</strong> — <span x-show="lang==='en'">BAST documents currently in the approval process</span><span x-show="lang==='id'">Dokumen BAST yang sedang dalam proses approval</span></li>
                    <li><strong>Completed</strong> — <span x-show="lang==='en'">BAST documents that have been fully approved</span><span x-show="lang==='id'">Dokumen BAST yang telah disetujui sepenuhnya</span></li>
                    <li><strong>Rejected</strong> — <span x-show="lang==='en'">BAST documents that were rejected and may need revision</span><span x-show="lang==='id'">Dokumen BAST yang ditolak dan mungkin perlu direvisi</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Creating a BAST</span><span x-show="lang==='id'">2. Membuat BAST</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Click the <strong>+ Create BAST</strong> button or click on a BAST Job to create a new handover document. The form includes:</span>
                    <span x-show="lang==='id'">Klik tombol <strong>+ Create BAST</strong> atau klik pada BAST Job untuk membuat dokumen serah terima baru. Form mencakup:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong><span x-show="lang==='en'">PO Reference</span><span x-show="lang==='id'">Referensi PO</span></strong> — <span x-show="lang==='en'">Linked Purchase Order</span><span x-show="lang==='id'">Purchase Order yang terkait</span></li>
                    <li><strong><span x-show="lang==='en'">Delivery Date</span><span x-show="lang==='id'">Tanggal Pengiriman</span></strong></li>
                    <li><strong><span x-show="lang==='en'">Items Received</span><span x-show="lang==='id'">Item yang Diterima</span></strong> — <span x-show="lang==='en'">Confirm quantity received per item</span><span x-show="lang==='id'">Konfirmasi jumlah yang diterima per item</span></li>
                    <li><strong>Attachments</strong> — <span x-show="lang==='en'">Supporting documents such as delivery notes</span><span x-show="lang==='id'">Dokumen pendukung seperti surat jalan</span></li>
                </ul>
                <div class="manual-note manual-warning">
                    <strong>⚠️ <span x-show="lang==='en'">Caution</span><span x-show="lang==='id'">Perhatian</span></strong>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">Ensure the quantities received match the actual delivery before submitting. The BAST will trigger the next steps in the payment process once approved.</span>
                        <span x-show="lang==='id'">Pastikan jumlah yang diterima sesuai dengan pengiriman aktual sebelum submit. BAST yang disetujui akan memicu langkah berikutnya dalam proses pembayaran.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
