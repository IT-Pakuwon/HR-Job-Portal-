<div x-data="{
        open: null,
        page: 1,
        perPage: 10,
        totalItems: 19,
        get totalPages() { return Math.ceil(this.totalItems / this.perPage); },
        visible(i) { return Math.ceil(i / this.perPage) === this.page; },
        goToPage(p) {
            if (p < 1 || p > this.totalPages) return;
            this.page = p;
            this.open = null;
            document.getElementById('faq-top')?.scrollIntoView({ behavior: 'smooth' });
        }
    }" class="max-w-9xl mx-auto space-y-6 p-2">
    <!-- HEADER -->
    <div id="faq-top" class="mb-10">
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
        <div class="faq-card" x-show="visible(1)">
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
        <div class="faq-card" x-show="visible(2)">
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
        <div class="faq-card" x-show="visible(3)">
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
        <div class="faq-card" x-show="visible(4)">
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
        <div class="faq-card" x-show="visible(5)">
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
        <div class="faq-card" x-show="visible(6)">
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
        <div class="faq-card" x-show="visible(7)">
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
        <div class="faq-card" x-show="visible(8)">
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
        <div class="faq-card" x-show="visible(9)">
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
        <div class="faq-card" x-show="visible(10)">
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
        </div>

        {{-- 11 --}}
        <div class="faq-card" x-show="visible(11)">
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
        <div class="faq-card" x-show="visible(12)">
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
        <div class="faq-card" x-show="visible(13)">
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
        <div class="faq-card" x-show="visible(14)">
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
        <div class="faq-card" x-show="visible(15)">
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

        {{-- 16 --}}
        <div class="faq-card" x-show="visible(16)">
            <button @click="open === 16 ? open = null : open = 16" class="faq-question">
                16. Apakah pembuatan Event di Event Calendar perlu approval?
            </button>
            <div x-show="open === 16" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    No. An event is saved immediately once you submit the form — there is no approval step.
                    Please make sure the details (dates, location, status) are correct before saving.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Tidak. Event akan langsung tersimpan setelah form di-submit — tidak ada proses approval.
                    Pastikan detail (tanggal, lokasi, status) sudah benar sebelum menyimpan.
                </p>
            </div>
        </div>

        {{-- 17 --}}
        <div class="faq-card" x-show="visible(17)">
            <button @click="open === 17 ? open = null : open = 17" class="faq-question">
                17. Kenapa lokasi event yang saya pilih tidak bisa dibooking?
            </button>
            <div x-show="open === 17" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    A single location can only hold up to 5 active events with overlapping dates at the same
                    time. If the location already has 5 overlapping events, please choose a different date
                    or location.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Satu lokasi hanya dapat menampung maksimal 5 event aktif dengan tanggal yang saling
                    tumpang tindih dalam waktu yang sama. Jika lokasi tersebut sudah memiliki 5 event yang
                    tumpang tindih, silakan pilih tanggal atau lokasi lain.
                </p>
            </div>
        </div>

        {{-- 18 --}}
        <div class="faq-card" x-show="visible(18)">
            <button @click="open === 18 ? open = null : open = 18" class="faq-question">
                18. Apa perbedaan Ticket Support di menu Operation Teknik dengan IT Support?
            </button>
            <div x-show="open === 18" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    IT Support handles computer, software, and network issues. Operation Teknik Ticket
                    Support handles Engineering, Building Service, Front Office, and Berita Acara (BA)
                    matters. Its workflow also differs: once the assigned technician marks the ticket as
                    Complete, it moves to Awaiting Approval and only becomes Completed after the assigned
                    approver(s) approve it.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    IT Support menangani kendala komputer, software, dan jaringan. Ticket Support pada menu
                    Operation Teknik menangani hal-hal Engineering, Building Service, Front Office, dan
                    Berita Acara (BA). Alur kerjanya juga berbeda: setelah teknisi yang ditugaskan menandai
                    tiket sebagai Complete, tiket berpindah ke status Awaiting Approval dan baru menjadi
                    Completed setelah disetujui oleh approver yang ditugaskan.
                </p>
            </div>
        </div>

        {{-- 19 --}}
        <div class="faq-card" x-show="visible(19)">
            <button @click="open === 19 ? open = null : open = 19" class="faq-question">
                19. Berapa lama saya bisa reopen tiket Operation Teknik yang sudah Completed?
            </button>
            <div x-show="open === 19" x-collapse class="faq-answer">
                <p><strong>EN:</strong>
                    As the requester, you can reopen a completed ticket yourself within 7 days after it was
                    completed. After that window, please create a new ticket instead.
                </p>

                <p class="mt-2"><strong>ID:</strong>
                    Sebagai requester, Anda dapat membuka kembali tiket yang sudah selesai dalam waktu 7 hari
                    setelah tiket tersebut selesai. Setelah lewat batas waktu tersebut, silakan buat tiket baru.
                </p>
            </div>
        </div>

    </div>

    <!-- PAGINATION -->
    <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700"
        x-show="totalPages > 1">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Page <span x-text="page"></span> of <span x-text="totalPages"></span>
        </p>
        <div class="flex items-center gap-1">
            <button @click="goToPage(page - 1)" :disabled="page === 1"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Prev
            </button>
            <template x-for="p in totalPages" :key="p">
                <button @click="goToPage(p)"
                    :class="p === page ? 'bg-gray-900 text-white dark:bg-blue-600' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="h-8 w-8 rounded-lg text-sm font-medium transition" x-text="p"></button>
            </template>
            <button @click="goToPage(page + 1)" :disabled="page === totalPages"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Next
            </button>
        </div>
    </div>
</div>
