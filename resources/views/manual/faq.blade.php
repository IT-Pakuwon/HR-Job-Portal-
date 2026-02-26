<div x-data="{ open: null }" class="max-w-9xl mx-auto space-y-6 p-2">
    <!-- HEADER -->
    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-gray-800 dark:text-white">
            Frequently Asked Questions
        </h1>
        <p class="mt-2 text-gray-500 dark:text-gray-400">
            Common questions regarding the system.
        </p>
    </div>

    <!-- FAQ LIST -->
    <div class="space-y-3">

        {{-- 1 --}}
        <div class="faq-card">
            <button @click="open === 1 ? open = null : open = 1" class="faq-question">
                1. Credential yang digunakan? Jika belum punya?
            </button>
            <div x-show="open === 1" x-collapse class="faq-answer">
                <p><strong>EN:</strong> Use your DAS (Digital Approval System) account credentials.
                    If you do not yet have access, please create Access Request by DAS (Digital Approval System) before
                    you use the system.</p>

                <p class="mt-2"><strong>ID:</strong> Gunakan kredensial akun yang terdaftar pada DAS (Digital Approval
                    System).
                    Jika belum memiliki akses, silakan ajukan permintaan akses melalui DAS (Digital Approval System)
                    sebelum anda menggunakan sistem.</p>
            </div>
        </div>

        {{-- 2 --}}
        <div class="faq-card">
            <button @click="open === 2 ? open = null : open = 2" class="faq-question">
                2. Jika muncul approval belum di-set IT?
            </button>
            <div x-show="open === 2" x-collapse class="faq-answer">
                <p><strong>EN:</strong> If an approval flow appears incomplete or not configured,
                    please coordinate with IT to ensure the approval hierarchy is properly assigned.</p>

                <p class="mt-2"><strong>ID:</strong> Jika approval belum terkonfigurasi,
                    silakan koordinasikan dengan IT untuk memastikan alur persetujuan sudah diatur dengan benar.</p>
            </div>
        </div>


        {{-- 3 --}}
        <div class="faq-card">
            <button @click="open === 3 ? open = null : open = 3" class="faq-question">
                3. Jika ada approval yang ingin diubah?
            </button>
            <div x-show="open === 3" x-collapse class="faq-answer">
                <p><strong>EN:</strong> Approval structure changes must be submitted to IT officially.
                    Modification will be applied after validation and confirmation.</p>

                <p class="mt-2"><strong>ID:</strong> Perubahan struktur approval harus diajukan secara resmi ke IT.
                    Perubahan akan dilakukan setelah validasi dan konfirmasi.</p>
            </div>
        </div>

        {{-- 4 --}}
        <div class="faq-card">
            <button @click="open === 4 ? open = null : open = 4" class="faq-question">
                4. Jika ada pertanyaan mengenai budget yang tidak ada / project dadakan?
            </button>
            <div x-show="open === 4" x-collapse class="faq-answer">
                <p><strong>EN:</strong> Please contact the respective Cost Control team.
                    For urgent or new projects without allocated budget, official budget revision or approval is
                    required.</p>

                <p class="mt-2"><strong>ID:</strong> Silakan hubungi tim Cost Control terkait.
                    Untuk proyek mendadak atau budget yang belum tersedia, diperlukan proses revisi atau persetujuan
                    tambahan.</p>
            </div>
        </div>

        {{-- 5 --}}
        <div class="faq-card">
            <button @click="open === 5 ? open = null : open = 5" class="faq-question">
                5. Jika kode barang tidak ada?
            </button>
            <div x-show="open === 5" x-collapse class="faq-answer">
                <p><strong>EN:</strong>Please ask Purchasing team before, then ubmit a new item code request to the
                    Procurement or Warehouse
                    for verification and registration before processing the transaction.</p>

                <p class="mt-2"><strong>ID:</strong> Silahkan menghubungi tim Purchasing terlebih dahulu, kemudian
                    ajukan permintaan pembuatan kode barang baru ke tim Procurement
                    atau Warehouse untuk verifikasi dan registrasi sebelum transaksi diproses.</p>
            </div>
        </div>

        {{-- 6 --}}
        <div class="faq-card">
            <button @click="open === 6 ? open = null : open = 6" class="faq-question">
                6. Jika tidak tahu WO menggunakan budget siapa?
            </button>
            <div x-show="open === 6" x-collapse class="faq-answer">
                <p><strong>EN:</strong> Coordinate with your Department Head or Cost Control and Engineering team
                    to confirm the appropriate budget owner before submitting the Work Order.</p>

                <p class="mt-2"><strong>ID:</strong> Koordinasikan dengan Kepala Departemen atau Cost Control dan tim
                    Engineering
                    untuk memastikan pemilik anggaran yang tepat sebelum mengajukan Work Order.</p>
            </div>
        </div>

        {{-- 7 --}}
        <div class="faq-card">
            <button @click="open === 7 ? open = null : open = 7" class="faq-question">
                7. Jika ingin mengecek SPPBJK/SPB di mana?
            </button>
            <div x-show="open === 7" x-collapse class="faq-answer">
                <p><strong>EN:</strong> You can monitor SPPBJK/SPB status in the respective transaction tracking menu
                    within the list dashboard.</p>

                <p class="mt-2"><strong>ID:</strong> Status SPPBJK/SPB dapat dicek melalui menu monitoring transaksi
                    pada list dashboard.</p>
            </div>
        </div>

    </div>
</div>
