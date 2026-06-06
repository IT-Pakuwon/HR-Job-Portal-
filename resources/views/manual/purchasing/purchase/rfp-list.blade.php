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
            <span x-show="lang==='en'">RFP (Request for Payment) is used to submit a payment request after goods or services have been delivered and the BAST has been completed. The RFP goes through an approval workflow before payment is processed.</span>
            <span x-show="lang==='id'">RFP (Request for Payment) digunakan untuk mengajukan permintaan pembayaran setelah barang atau jasa telah diterima dan BAST telah diselesaikan. RFP melalui alur approval sebelum pembayaran diproses.</span>
        </p>
    </div>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. RFP Status Dashboard</span><span x-show="lang==='id'">1. Dashboard Status RFP</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The RFP List page shows summary cards at the top that display the count of RFPs in each status. Click a card to filter the list below.</span>
                    <span x-show="lang==='id'">Halaman RFP List menampilkan kartu ringkasan di bagian atas yang menunjukkan jumlah RFP di setiap status. Klik kartu untuk memfilter daftar di bawah.</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>All</strong> — <span x-show="lang==='en'">All RFP documents</span><span x-show="lang==='id'">Semua dokumen RFP</span></li>
                    <li><strong>On Progress</strong> — <span x-show="lang==='en'">RFP currently in the approval process</span><span x-show="lang==='id'">RFP yang sedang dalam proses approval</span></li>
                    <li><strong>Revise / Draft</strong> — <span x-show="lang==='en'">RFP that has been revised or is still in draft</span><span x-show="lang==='id'">RFP yang telah direvisi atau masih dalam status draft</span></li>
                    <li><strong>Completed</strong> — <span x-show="lang==='en'">RFP that has been fully approved and payment processed</span><span x-show="lang==='id'">RFP yang telah disetujui sepenuhnya dan pembayaran diproses</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Submitting an RFP</span><span x-show="lang==='id'">2. Mengajukan RFP</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">An RFP is created from a completed BAST. The system carries over the PO and delivery information automatically. Fill in the additional details required for payment:</span>
                    <span x-show="lang==='id'">RFP dibuat dari BAST yang sudah selesai. Sistem membawa informasi PO dan pengiriman secara otomatis. Isi detail tambahan yang diperlukan untuk pembayaran:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong><span x-show="lang==='en'">Invoice Number & Date</span><span x-show="lang==='id'">Nomor & Tanggal Invoice</span></strong></li>
                    <li><strong><span x-show="lang==='en'">Payment Amount</span><span x-show="lang==='id'">Jumlah Pembayaran</span></strong></li>
                    <li><strong><span x-show="lang==='en'">Bank Account</span><span x-show="lang==='id'">Rekening Bank</span></strong></li>
                    <li><strong>Attachments</strong> — <span x-show="lang==='en'">Invoice, BAST, and any supporting documents</span><span x-show="lang==='id'">Invoice, BAST, dan dokumen pendukung lainnya</span></li>
                </ul>
                <div class="manual-note manual-warning">
                    <strong>⚠️ <span x-show="lang==='en'">Caution</span><span x-show="lang==='id'">Perhatian</span></strong>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">Ensure all attached documents are complete and accurate before submitting. Incomplete submissions may delay payment processing.</span>
                        <span x-show="lang==='id'">Pastikan semua dokumen yang dilampirkan lengkap dan akurat sebelum submit. Pengajuan yang tidak lengkap dapat menunda proses pembayaran.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
