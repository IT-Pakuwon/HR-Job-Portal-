<div x-data="{
    lang: localStorage.getItem('manual_lang') || 'id',
    openSection: 's1',

    setLang(v) {
        this.lang = v;
        localStorage.setItem('manual_lang', v);
    },

    toggle(section) {
        this.openSection = this.openSection === section ? null : section;
    }
}" class="max-w-9xl mx-auto space-y-6 p-6">

    <!-- ================= LANGUAGE TOGGLE ================= -->
    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')"
                :class="lang === 'id'
                    ?
                    'bg-gray-900 text-white' :
                    'text-gray-600 dark:text-gray-300'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition">
                ID
            </button>
            <button @click="setLang('en')"
                :class="lang === 'en'
                    ?
                    'bg-gray-900 text-white' :
                    'text-gray-600 dark:text-gray-300'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition">
                EN
            </button>
        </div>
    </div>

    <!-- ================= DATA DISCLAIMER ================= -->
    <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">

        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            <span x-show="lang === 'en'">Information</span>
            <span x-show="lang === 'id'">Informasi</span>
        </h3>

        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <span x-show="lang === 'en'">
                All data shown in this manual (screenshots, numbers, names, and documents)
                are dummy data used for illustration purposes only.
            </span>
            <span x-show="lang === 'id'">
                Seluruh data yang ditampilkan dalam manual ini (screenshot, angka, nama, dan dokumen)
                merupakan data dummy yang digunakan hanya sebagai contoh.
            </span>
        </p>

    </div>


    <!-- ================= SECTION 1 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s1')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">1. Create Issue</span>
                    <span x-show="lang==='id'">1. Buat Issue</span>
                </span>

                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">
                <!-- ================= SECTION S1 ================= -->

                <!-- 1.1 Overview -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.1 Overview</span>
                        <span x-show="lang==='id'">1.1 Gambaran Umum</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The Create Issue page allows users to process material issuance
                            based on an approved SPB document.
                            Users define issued quantities, site destination,
                            and optional notes before submitting for approval.
                        </span>
                        <span x-show="lang==='id'">
                            Halaman Create Issue digunakan untuk memproses pengeluaran barang
                            berdasarkan dokumen SPB yang telah disetujui.
                            User menentukan jumlah yang di-issue, site tujuan,
                            dan catatan sebelum dikirim untuk approval.
                        </span>
                    </p>

                    <div class="manual-note manual-info">
                        <span x-show="lang==='en'">
                            Issue can only be created from an approved SPB.
                        </span>
                        <span x-show="lang==='id'">
                            Issue hanya dapat dibuat dari SPB yang sudah disetujui.
                        </span>
                    </div>
                </section>

                <!-- 1.2 Header Information -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.2 Header Information</span>
                        <span x-show="lang==='id'">1.2 Informasi Header</span>
                    </h3>

                    <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                        <li><strong>SPB ID</strong> – <span x-show="lang==='en'">Reference document number</span><span
                                x-show="lang==='id'">Nomor referensi dokumen</span></li>
                        <li><strong>SPB Date</strong> – <span x-show="lang==='en'">Original SPB date</span><span
                                x-show="lang==='id'">Tanggal SPB</span></li>
                        <li><strong>Company</strong> – <span x-show="lang==='en'">Company code</span><span
                                x-show="lang==='id'">Kode perusahaan</span></li>
                        <li><strong>Department</strong> – <span x-show="lang==='en'">Requesting department</span><span
                                x-show="lang==='id'">Department pemohon</span></li>
                        <li><strong>Keperluan</strong> – <span x-show="lang==='en'">Purpose description</span><span
                                x-show="lang==='id'">Deskripsi kebutuhan</span></li>
                        <li><strong>Issue Note</strong> – <span x-show="lang==='en'">Optional issue remark</span><span
                                x-show="lang==='id'">Catatan issue (opsional)</span></li>
                    </ul>
                </section>

                <!-- 1.3 Issue Detail Table -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.3 Issue Detail Table</span>
                        <span x-show="lang==='id'">1.3 Tabel Detail Issue</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Each SPB detail item is displayed with stock information,
                            original quantity, remaining quantity (open),
                            and input fields for issuing quantity.
                        </span>
                        <span x-show="lang==='id'">
                            Setiap detail SPB ditampilkan dengan informasi stok,
                            quantity awal, quantity sisa (open),
                            dan kolom input untuk quantity issue.
                        </span>
                    </p>

                    <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                        <li><strong>Stock</strong> – <span x-show="lang==='en'">Current available stock</span><span
                                x-show="lang==='id'">Stok tersedia saat ini</span></li>
                        <li><strong>Qty (Open)</strong> – <span x-show="lang==='en'">Remaining quantity not yet
                                issued</span><span x-show="lang==='id'">Sisa quantity yang belum di-issue</span></li>
                        <li><strong>Qty Issue *</strong> – <span x-show="lang==='en'">Quantity to issue
                                (mandatory)</span><span x-show="lang==='id'">Jumlah yang akan di-issue (wajib)</span>
                        </li>
                        <li><strong>Site *</strong> – <span x-show="lang==='en'">Destination site
                                (mandatory)</span><span x-show="lang==='id'">Site tujuan (wajib)</span></li>
                        <li><strong>Detail Note</strong> – <span x-show="lang==='en'">Optional per-line
                                remark</span><span x-show="lang==='id'">Catatan per baris (opsional)</span></li>
                    </ul>

                    <div class="manual-note manual-warning">
                        <span x-show="lang==='en'">
                            Issued quantity cannot exceed both remaining quantity (Open)
                            and available stock.
                        </span>
                        <span x-show="lang==='id'">
                            Quantity issue tidak boleh melebihi quantity sisa (Open)
                            maupun stok yang tersedia.
                        </span>
                    </div>
                </section>

                <!-- 1.4 Attachments -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.4 Attachments</span>
                        <span x-show="lang==='id'">1.4 Lampiran</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Users may upload supporting documents
                            such as delivery notes or photos.
                        </span>
                        <span x-show="lang==='id'">
                            User dapat mengunggah dokumen pendukung
                            seperti surat jalan atau foto dokumentasi.
                        </span>
                    </p>
                </section>

                <!-- 1.5 Submission -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.5 Submit for Approval</span>
                        <span x-show="lang==='id'">1.5 Submit untuk Approval</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            After completing all required fields,
                            click "Submit Approval" to send the Issue document
                            into the approval workflow.
                        </span>
                        <span x-show="lang==='id'">
                            Setelah semua field wajib diisi,
                            klik "Submit Approval" untuk mengirim dokumen Issue
                            ke proses workflow approval.
                        </span>
                    </p>

                    <div class="manual-note manual-important">
                        <span x-show="lang==='en'">
                            Once submitted, the document status will change
                            to On Progress and can no longer be modified.
                        </span>
                        <span x-show="lang==='id'">
                            Setelah disubmit, status dokumen berubah menjadi On Progress
                            dan tidak dapat diubah lagi.
                        </span>
                    </div>
                </section>
            </div>
        </div>
    </section>

    <!-- ================= SECTION 2 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s2')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">2. Create SPPB - SPB</span>
                    <span x-show="lang==='id'">2. Buat SPPB - SPB</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">
                <!-- 2.1 Overview -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.1 Overview</span>
                        <span x-show="lang==='id'">2.1 Gambaran Umum</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The Create SPPB page allows users to generate an SPPB document
                            based on an existing SPB. This process records material movement
                            and reduces the remaining (open) quantity of the SPB.
                        </span>
                        <span x-show="lang==='id'">
                            Halaman Create SPPB digunakan untuk membuat dokumen SPPB
                            berdasarkan SPB yang sudah ada. Proses ini mencatat pergerakan barang
                            dan mengurangi quantity sisa (open) pada SPB.
                        </span>
                    </p>

                    <div class="manual-note manual-info">
                        <span x-show="lang==='en'">
                            SPPB can only be created if the SPB still has remaining (Open) quantity.
                        </span>
                        <span x-show="lang==='id'">
                            SPPB hanya dapat dibuat jika SPB masih memiliki quantity sisa (Open).
                        </span>
                    </div>
                </section>

                <!-- 2.2 Header Information -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.2 Header Information</span>
                        <span x-show="lang==='id'">2.2 Informasi Header</span>
                    </h3>

                    <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                        <li><strong>SPB ID</strong> – <span x-show="lang==='en'">Reference document number</span><span
                                x-show="lang==='id'">Nomor referensi SPB</span></li>
                        <li><strong>SPB Date – User</strong> – <span x-show="lang==='en'">Original SPB date and
                                creator</span><span x-show="lang==='id'">Tanggal dan pembuat SPB</span></li>
                        <li><strong>Company – Department</strong> – <span x-show="lang==='en'">Company and requesting
                                department</span><span x-show="lang==='id'">Perusahaan dan department pemohon</span>
                        </li>
                        <li><strong>Department</strong> – <span x-show="lang==='en'">Processing department
                                (editable)</span><span x-show="lang==='id'">Department pemroses (dapat dipilih)</span>
                        </li>
                        <li><strong>Keperluan</strong> – <span x-show="lang==='en'">Purpose description
                                (read-only)</span><span x-show="lang==='id'">Deskripsi kebutuhan (read-only)</span>
                        </li>
                        <li><strong>SPPB Note</strong> – <span x-show="lang==='en'">Optional header note</span><span
                                x-show="lang==='id'">Catatan header SPPB (opsional)</span></li>
                    </ul>
                </section>

                <!-- 2.3 SPPB Detail Table -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.3 SPPB Detail Table</span>
                        <span x-show="lang==='id'">2.3 Tabel Detail SPPB</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Each SPB detail item is displayed with stock information,
                            original quantity, and remaining quantity (Open).
                            Users input the quantity to process under Qty SPPB.
                        </span>
                        <span x-show="lang==='id'">
                            Setiap detail SPB ditampilkan dengan informasi stok,
                            quantity awal, dan quantity sisa (Open).
                            User mengisi jumlah yang akan diproses pada kolom Qty SPPB.
                        </span>
                    </p>

                    <ul class="list-disc space-y-1 pl-6 text-gray-600 dark:text-gray-400">
                        <li><strong>Stock</strong> – <span x-show="lang==='en'">Current available stock</span><span
                                x-show="lang==='id'">Stok tersedia saat ini</span></li>
                        <li><strong>Qty</strong> – <span x-show="lang==='en'">Original requested quantity</span><span
                                x-show="lang==='id'">Quantity awal yang diminta</span></li>
                        <li><strong>Qty (Open)</strong> – <span x-show="lang==='en'">Remaining quantity not yet
                                processed</span><span x-show="lang==='id'">Sisa quantity yang belum diproses</span>
                        </li>
                        <li><strong>Qty SPPB</strong> – <span x-show="lang==='en'">Quantity to be processed in this
                                SPPB</span><span x-show="lang==='id'">Jumlah yang diproses pada SPPB ini</span></li>
                        <li><strong>Site</strong> – <span x-show="lang==='en'">Destination site</span><span
                                x-show="lang==='id'">Site tujuan</span></li>
                        <li><strong>Detail Note</strong> – <span x-show="lang==='en'">Optional per-item
                                note</span><span x-show="lang==='id'">Catatan per item (opsional)</span></li>
                    </ul>

                    <div class="manual-note manual-warning">
                        <span x-show="lang==='en'">
                            Qty SPPB must not exceed the remaining (Open) quantity.
                        </span>
                        <span x-show="lang==='id'">
                            Qty SPPB tidak boleh melebihi quantity sisa (Open).
                        </span>
                    </div>
                </section>

                <!-- 2.4 Attachments -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.4 Attachments</span>
                        <span x-show="lang==='id'">2.4 Lampiran</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Users may attach supporting documents
                            such as delivery evidence or supporting forms.
                        </span>
                        <span x-show="lang==='id'">
                            User dapat menambahkan dokumen pendukung
                            seperti bukti serah terima atau dokumen pendukung lainnya.
                        </span>
                    </p>
                </section>

                <!-- 2.5 Submit Process -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.5 Submit for Approval</span>
                        <span x-show="lang==='id'">2.5 Submit untuk Approval</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            After completing all required fields,
                            click "Submit Approval" to send the SPPB
                            into the approval workflow.
                        </span>
                        <span x-show="lang==='id'">
                            Setelah semua field yang diperlukan diisi,
                            klik "Submit Approval" untuk mengirim SPPB
                            ke proses approval.
                        </span>
                    </p>

                    <div class="manual-note manual-important">
                        <span x-show="lang==='en'">
                            Once submitted, the SPPB status will change
                            and the document can no longer be edited.
                        </span>
                        <span x-show="lang==='id'">
                            Setelah disubmit, status SPPB akan berubah
                            dan dokumen tidak dapat diedit kembali.
                        </span>
                    </div>
                </section>

            </div>
        </div>
    </section>



    <!-- ================= SECTION 3 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s3')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">3. List SPB Jobs</span>
                    <span x-show="lang==='id'">3. Daftar SPB Jobs</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <!-- 3.1 Overview -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.1 Overview</span>
                        <span x-show="lang==='id'">3.1 Gambaran Umum</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The Issue Dashboard displays SPB documents grouped by processing scope.
                            Users can filter jobs based on their current status and proceed
                            to Issue or SPPB processing.
                        </span>
                        <span x-show="lang==='id'">
                            Dashboard Issue menampilkan dokumen SPB yang dikelompokkan berdasarkan scope proses.
                            User dapat memfilter pekerjaan berdasarkan status saat ini
                            dan melanjutkan ke proses Issue atau SPPB.
                        </span>
                    </p>
                </section>

                <!-- 3.2 Scope Cards -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.2 Scope Categories</span>
                        <span x-show="lang==='id'">3.2 Kategori Scope</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>SPB Open</strong> –
                            <span x-show="lang==='en'">
                                Newly approved SPB with full remaining quantity.
                            </span>
                            <span x-show="lang==='id'">
                                SPB yang baru disetujui dan masih memiliki quantity penuh.
                            </span>
                        </li>

                        <li>
                            <strong>Issue Partial</strong> –
                            <span x-show="lang==='en'">
                                SPB that has been partially issued and still has remaining quantity.
                            </span>
                            <span x-show="lang==='id'">
                                SPB yang sudah di-issue sebagian dan masih memiliki sisa quantity.
                            </span>
                        </li>

                        <li>
                            <strong>SPB to SPPB</strong> –
                            <span x-show="lang==='en'">
                                SPB ready to be processed into SPPB.
                            </span>
                            <span x-show="lang==='id'">
                                SPB yang siap diproses menjadi SPPB.
                            </span>
                        </li>

                        <li>
                            <strong>Issue On Progress</strong> –
                            <span x-show="lang==='en'">
                                Issue documents currently in approval workflow.
                            </span>
                            <span x-show="lang==='id'">
                                Dokumen Issue yang sedang dalam proses approval.
                            </span>
                        </li>

                        <li>
                            <strong>SPPB On Progress</strong> –
                            <span x-show="lang==='en'">
                                SPPB documents currently under approval process.
                            </span>
                            <span x-show="lang==='id'">
                                Dokumen SPPB yang sedang dalam proses approval.
                            </span>
                        </li>
                    </ul>

                    <div class="manual-note manual-info">
                        <span x-show="lang==='en'">
                            The number displayed on each card represents the total documents in that category.
                        </span>
                        <span x-show="lang==='id'">
                            Angka pada setiap kartu menunjukkan total dokumen dalam kategori tersebut.
                        </span>
                    </div>
                </section>

                <!-- 3.3 Issue Table -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.3 Issue Table</span>
                        <span x-show="lang==='id'">3.3 Tabel Issue</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            After selecting a scope category, the table displays
                            relevant SPB or Issue records.
                        </span>
                        <span x-show="lang==='id'">
                            Setelah memilih kategori scope, tabel akan menampilkan
                            daftar SPB atau Issue yang sesuai.
                        </span>
                    </p>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Users can open a document to proceed with Issue creation
                            or SPPB processing depending on the selected scope.
                        </span>
                        <span x-show="lang==='id'">
                            User dapat membuka dokumen untuk melanjutkan pembuatan Issue
                            atau pemrosesan SPPB sesuai scope yang dipilih.
                        </span>
                    </p>
                </section>

                <!-- 3.4 Process Flow Logic -->
                <section class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.4 Processing Logic</span>
                        <span x-show="lang==='id'">3.4 Logika Proses</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <span x-show="lang==='en'">
                                If SPB Open → User may create Issue or SPPB.
                            </span>
                            <span x-show="lang==='id'">
                                Jika SPB Open → User dapat membuat Issue atau SPPB.
                            </span>
                        </li>

                        <li>
                            <span x-show="lang==='en'">
                                If Issue Partial → Remaining quantity can still be processed.
                            </span>
                            <span x-show="lang==='id'">
                                Jika Issue Partial → Quantity sisa masih dapat diproses.
                            </span>
                        </li>

                        <li>
                            <span x-show="lang==='en'">
                                If On Progress → Document is locked until approval is completed.
                            </span>
                            <span x-show="lang==='id'">
                                Jika On Progress → Dokumen terkunci sampai proses approval selesai.
                            </span>
                        </li>
                    </ul>

                    <div class="manual-note manual-warning">
                        <span x-show="lang==='en'">
                            Documents under approval cannot be edited or reprocessed.
                        </span>
                        <span x-show="lang==='id'">
                            Dokumen yang sedang dalam approval tidak dapat diedit atau diproses ulang.
                        </span>
                    </div>
                </section>
            </div>
        </div>
    </section>
</div>
