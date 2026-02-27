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
        {{-- 8 --}}
        <div class="faq-card">
            <button @click="open === 8 ? open = null : open = 8" class="faq-question">
                8. Jika budget yang sudah di-submit salah, bolehkah import ulang file yang sama?
            </button>
            <div x-show="open === 8" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    Do not re-import the same budget file after submission, as the system will process it again
                    and may cause duplicate data. Please revise the existing data instead.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Jangan meng-import ulang file budget yang sama setelah di-submit,
                    karena sistem akan memproses ulang dan menyebabkan data menjadi double.
                    Silakan lakukan revisi pada data yang sudah ada.
                </p>
            </div>
        </div>

        {{-- 9 --}}
        <div class="faq-card">
            <button @click="open === 9 ? open = null : open = 9" class="faq-question">
                9. Jika ada penambahan row budget baru, apakah harus import semua data lagi?
            </button>
            <div x-show="open === 9" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    If existing rows (e.g., 1–10) have already been updated and there are additional rows (e.g., 11–12),
                    please import only the newly added rows.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Jika baris sebelumnya (misalnya 1–10) sudah diperbarui dan terdapat penambahan baris baru
                    (misalnya 11–12), maka harap hanya meng-import baris yang baru ditambahkan saja.
                </p>
            </div>
        </div>

        {{-- 10 --}}
        <div class="faq-card">
            <button @click="open === 10 ? open = null : open = 10" class="faq-question">
                10. Mengapa file budget tidak dapat di-import?
            </button>
            <div x-show="open === 10" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    Import may fail if the Excel template format or field type has been modified.
                    Please use a new system-provided template and do not change the predefined column formats.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Proses import dapat gagal jika format template Excel atau tipe field telah diubah.
                    Silakan gunakan template baru yang disediakan sistem dan jangan mengubah format kolom yang telah
                    ditentukan.
                </p>
            </div>
        </div> {{-- 11 --}}
        <div class="faq-card">
            <button @click="open === 11 ? open = null : open = 11" class="faq-question">
                11. BQ SPPJ atau SPPT dibuat di mana?
            </button>
            <div x-show="open === 11" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    BQ for SPPJ or SPPT can only be created after the SPPJ/SPPT has been submitted.
                    Approval Level 1 cannot approve the document if the BQ has not been created.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    BQ untuk SPPJ atau SPPT dapat dibuat setelah SPPJ/SPPT di-submit.
                    Approval Level 1 tidak dapat melakukan approval apabila BQ belum dibuat.
                </p>
            </div>
        </div>

        {{-- 12 --}}
        <div class="faq-card">
            <button @click="open === 12 ? open = null : open = 12" class="faq-question">
                12. Apakah CS bisa di-approve jika budget kurang?
            </button>
            <div x-show="open === 12" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    CS cannot be fully approved if the budget is insufficient.
                    Approval Level 2 will not be able to approve the transaction.
                    An IM Budget must be created if approval is still required.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    CS tidak dapat di-approve secara penuh jika budget tidak mencukupi.
                    Approval Level 2 tidak dapat melakukan approval.
                    Jika tetap ingin diproses, maka harus membuat IM Budget terlebih dahulu.
                </p>
            </div>
        </div>

        {{-- 13 --}}
        <div class="faq-card">
            <button @click="open === 13 ? open = null : open = 13" class="faq-question">
                13. Apakah BQ bisa di-edit?
            </button>
            <div x-show="open === 13" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    BQ can be edited only while it is still in "Waiting Approval" status.
                    Once the approval process has started, the BQ can no longer be modified.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    BQ dapat di-edit selama masih dalam status "Waiting Approval".
                    Jika proses approval sudah berjalan, maka BQ tidak dapat diubah kembali.
                </p>
            </div>
        </div>

        {{-- 14 --}}
        <div class="faq-card">
            <button @click="open === 14 ? open = null : open = 14" class="faq-question">
                14. Apakah attachment bisa dihapus setelah di-upload?
            </button>
            <div x-show="open === 14" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    Currently, attachments cannot be deleted after being uploaded.
                    Please contact the IT team for further assistance.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Saat ini attachment belum dapat dihapus setelah di-upload.
                    Mohon menghubungi tim IT untuk bantuan lebih lanjut.
                </p>
            </div>
        </div>

        {{-- 15 --}}
        <div class="faq-card">
            <button @click="open === 15 ? open = null : open = 15" class="faq-question">
                15. Jika membuat PRF namun belum ada approval?
            </button>
            <div x-show="open === 15" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    Please coordinate with HR to confirm the approval line.
                    Once confirmed, IT will configure the approval setup in the system.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Harap menghubungi HR untuk memastikan line approval terlebih dahulu.
                    Setelah dikonfirmasi, IT akan melakukan pengaturan approval di sistem.
                </p>
            </div>
        </div>

    </div>
</div>
