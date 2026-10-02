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
        <div
            class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
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
                    <span x-show="lang==='en'">1. Overview & Monitoring Dashboard</span>
                    <span x-show="lang==='id'">1. Gambaran Umum & Dashboard Monitoring</span>
                </span>

                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Monitoring Permit (Permit & Compliance Monitoring) is the module used to record and
                        track the company's permits, licenses, and certificates — for example operational
                        permits, legal contracts, or other documents issued by an authority — together with
                        their validity period. The module keeps an activity log for each permit, sends
                        reminders before a permit expires, and lets General Affair (GA) produce a document
                        handover report once a batch of permits is completed.
                    </span>
                    <span x-show="lang==='id'">
                        Monitoring Permit (Permit & Compliance Monitoring) adalah modul untuk mencatat dan
                        memantau perizinan, lisensi, dan sertifikat perusahaan — misalnya izin operasional,
                        kontrak legal, atau dokumen lain yang diterbitkan oleh instansi tertentu — beserta masa
                        berlakunya. Modul ini menyimpan log aktivitas untuk setiap perizinan, mengirimkan
                        pengingat sebelum izin berakhir, dan memungkinkan General Affair (GA) membuat laporan
                        serah terima dokumen setelah sekumpulan perizinan selesai diproses.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        What you see on this page depends on your access: users with GA access can see and
                        manage every permit within the companies they are assigned to, while users with
                        permit-input access (without GA access) only see the permits for their own
                        department(s) and can only update the item list on those permits.
                    </span>
                    <span x-show="lang==='id'">
                        Tampilan pada halaman ini tergantung pada akses Anda: pengguna dengan akses GA dapat
                        melihat dan mengelola seluruh perizinan pada perusahaan yang menjadi tanggung jawabnya,
                        sedangkan pengguna dengan akses input perizinan (tanpa akses GA) hanya dapat melihat
                        perizinan milik departemennya sendiri dan hanya dapat memperbarui daftar item pada
                        perizinan tersebut.
                    </span>
                </div>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.1 Status Cards</span>
                        <span x-show="lang==='id'">1.1 Kartu Status</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The cards at the top of the page summarize the permits you can see and act as
                            quick filters for the table below. Click a card to filter the list to that status.
                        </span>
                        <span x-show="lang==='id'">
                            Kartu-kartu di bagian atas halaman merangkum perizinan yang dapat Anda lihat dan
                            berfungsi sebagai filter cepat untuk tabel di bawahnya. Klik sebuah kartu untuk
                            menyaring daftar sesuai status tersebut.
                        </span>
                    </p>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>All Permits</strong> —
                            <span x-show="lang==='en'">Every permit you have access to.</span>
                            <span x-show="lang==='id'">Seluruh perizinan yang dapat Anda akses.</span>
                        </li>
                        <li>
                            <strong>Active</strong> —
                            <span x-show="lang==='en'">Not yet completed/rejected/cancelled, and either has no expiry date or has not reached its end date.</span>
                            <span x-show="lang==='id'">Belum completed/rejected/cancelled, dan tidak memiliki tanggal berakhir atau belum mencapai tanggal berakhirnya.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Expiring ≤ 30 Days / 30-60 Days / 60-90 Days / ≥ 90 Days</span>
                                <span x-show="lang==='id'">Expiring ≤ 30 Hari / 30-60 Hari / 60-90 Hari / ≥ 90 Hari</span>
                            </strong> —
                            <span x-show="lang==='en'">Permits with an expiry date, grouped by how many days are left until they expire.</span>
                            <span x-show="lang==='id'">Perizinan yang memiliki tanggal berakhir, dikelompokkan berdasarkan sisa hari menuju masa berakhirnya.</span>
                        </li>
                        <li>
                            <strong>Expired</strong> —
                            <span x-show="lang==='en'">The end date has already passed and the permit is still not completed/rejected/cancelled.</span>
                            <span x-show="lang==='id'">Tanggal berakhir sudah terlewati dan perizinan masih belum completed/rejected/cancelled.</span>
                        </li>
                        <li>
                            <strong>Completed</strong> —
                            <span x-show="lang==='en'">Fully processed and closed.</span>
                            <span x-show="lang==='id'">Sudah diproses sepenuhnya dan ditutup.</span>
                        </li>
                    </ul>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">1.2 Filtering & Searching the List</span>
                        <span x-show="lang==='id'">1.2 Memfilter & Mencari Daftar</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Besides the status cards, you can narrow the list further using the dropdowns
                            above the table: Expiry Year, Expiry Month, Category, and Site. Use the search box
                            to look up a permit by its ID, title, description, category, status, or month/year
                            of its start or end date. Click <strong>Reset</strong> to clear all filters.
                        </span>
                        <span x-show="lang==='id'">
                            Selain kartu status, Anda dapat menyaring daftar lebih lanjut menggunakan dropdown
                            di atas tabel: Expiry Year, Expiry Month, Category, dan Site. Gunakan kotak
                            pencarian untuk mencari perizinan berdasarkan ID, judul, deskripsi, kategori,
                            status, atau bulan/tahun dari tanggal mulai atau berakhirnya. Klik
                            <strong>Reset</strong> untuk menghapus semua filter.
                        </span>
                    </p>

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
                    <span x-show="lang==='en'">2. Creating a Permit Record</span>
                    <span x-show="lang==='id'">2. Membuat Data Perizinan</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Users with GA access can click <strong>Create</strong> to register a new permit.
                        There is no approval chain on this form — once submitted, the record is saved directly
                        with status <strong>On Progress</strong> and an email is sent to the requester.
                    </span>
                    <span x-show="lang==='id'">
                        Pengguna dengan akses GA dapat mengklik <strong>Create</strong> untuk mendaftarkan
                        perizinan baru. Form ini tidak melalui rantai approval — setelah disubmit, data
                        langsung tersimpan dengan status <strong>On Progress</strong> dan email dikirimkan
                        kepada pemohon.
                    </span>
                </p>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.1 Basic Information</span>
                        <span x-show="lang==='id'">2.1 Informasi Dasar</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>Company</strong> / <strong>Site</strong> /
                            <strong><span x-show="lang==='en'">Department</span><span x-show="lang==='id'">Departemen</span></strong> —
                            <span x-show="lang==='en'">Selected from the companies, sites, and departments that are active for your account.</span>
                            <span x-show="lang==='id'">Dipilih dari perusahaan, site, dan departemen yang aktif untuk akun Anda.</span>
                        </li>
                        <li>
                            <strong>Category</strong> —
                            <span x-show="lang==='en'">The permit category set up by GA (e.g. business license, environmental permit).</span>
                            <span x-show="lang==='id'">Kategori perizinan yang diatur oleh GA (misalnya izin usaha, izin lingkungan).</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Title & Description</span>
                                <span x-show="lang==='id'">Judul & Deskripsi</span>
                            </strong> —
                            <span x-show="lang==='en'">The permit's name and a free-text description of what it covers.</span>
                            <span x-show="lang==='id'">Nama perizinan dan deskripsi bebas mengenai cakupannya.</span>
                        </li>
                    </ul>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.2 Validity & Reminder</span>
                        <span x-show="lang==='id'">2.2 Masa Berlaku & Pengingat</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Start Date</span>
                                <span x-show="lang==='id'">Tanggal Mulai</span>
                            </strong> —
                            <span x-show="lang==='en'">When the permit takes effect.</span>
                            <span x-show="lang==='id'">Kapan perizinan mulai berlaku.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Has Expiry Date</span>
                                <span x-show="lang==='id'">Memiliki Tanggal Berakhir</span>
                            </strong> —
                            <span x-show="lang==='en'">Toggle on if the permit expires. When it does, the End Date field becomes required and an End Date in the past relative to Start Date is not allowed.</span>
                            <span x-show="lang==='id'">Aktifkan jika perizinan memiliki masa berlaku. Jika aktif, kolom End Date wajib diisi dan tidak boleh lebih awal dari Start Date.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Reminder Before End</span>
                                <span x-show="lang==='id'">Pengingat Sebelum Berakhir</span>
                            </strong> —
                            <span x-show="lang==='en'">How many days before expiry (120, 90, 60, or 30) the renewal reminder email should go out. Only applies to permits that have an expiry date.</span>
                            <span x-show="lang==='id'">Berapa hari sebelum masa berakhir (120, 90, 60, atau 30) email pengingat perpanjangan akan dikirim. Hanya berlaku untuk perizinan yang memiliki tanggal berakhir.</span>
                        </li>
                    </ul>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.3 Processing Details & Items</span>
                        <span x-show="lang==='id'">2.3 Detail Proses & Item</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Application Handling Method, Issuing Authority, Submission Channel, Contract/Legal No., Issue Date</span>
                                <span x-show="lang==='id'">Application Handling Method, Issuing Authority, Submission Channel, No. Kontrak/Legal, Issue Date</span>
                            </strong> —
                            <span x-show="lang==='en'">Optional reference fields describing how and where the permit was processed.</span>
                            <span x-show="lang==='id'">Kolom referensi opsional yang menjelaskan bagaimana dan di mana perizinan diproses.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Approval Notification Users / Requesting Department Users</span>
                                <span x-show="lang==='id'">User Notifikasi Approval / User Departemen Pemohon</span>
                            </strong> —
                            <span x-show="lang==='en'">The users who should be kept informed about this permit (used for CC on outgoing emails).</span>
                            <span x-show="lang==='id'">Pengguna yang perlu mendapat informasi mengenai perizinan ini (digunakan sebagai CC pada email yang dikirim).</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Item & Quantity</span>
                                <span x-show="lang==='id'">Item & Qty</span>
                            </strong> —
                            <span x-show="lang==='en'">Click <strong>Add Item</strong> to list one or more items or documents covered by this permit, each with its quantity.</span>
                            <span x-show="lang==='id'">Klik <strong>Add Item</strong> untuk mendaftarkan satu atau beberapa item/dokumen yang tercakup dalam perizinan ini beserta jumlahnya.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Attachments</span>
                                <span x-show="lang==='id'">Lampiran</span>
                            </strong> —
                            <span x-show="lang==='en'">Supporting files for the permit (max 5 MB per file).</span>
                            <span x-show="lang==='id'">File pendukung untuk perizinan ini (maksimal 5 MB per file).</span>
                        </li>
                    </ul>

                    <div class="manual-note manual-warning">
                        <span x-show="lang==='en'">
                            Company, Site, Department, Category, Title, Start Date, Reminder, at least one
                            approval/requester user, and at least one item with its quantity are required
                            before the form can be submitted.
                        </span>
                        <span x-show="lang==='id'">
                            Company, Site, Department, Category, Title, Start Date, Reminder, minimal satu user
                            approval/pemohon, dan minimal satu item beserta jumlahnya wajib diisi sebelum form
                            dapat disubmit.
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
                    <span x-show="lang==='en'">3. Updating Status, Items, and Renewing a Permit</span>
                    <span x-show="lang==='id'">3. Memperbarui Status, Item, dan Memperpanjang Perizinan</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.1 Activity Log & Status</span>
                        <span x-show="lang==='id'">3.1 Log Aktivitas & Status</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Open a permit's detail to see its full processing timeline. Add a new activity
                            entry with a description and a processing status: <strong>Waiting</strong>,
                            <strong>Process</strong>, <strong>Rejected</strong>, <strong>Cancelled</strong>, or
                            <strong>Done</strong>. Choosing Done, Rejected, or Cancelled also updates the
                            permit's overall status to Completed, Rejected, or Cancelled respectively; Waiting
                            and Process only add a note to the timeline without closing the permit. Each
                            activity can include its own attachments.
                        </span>
                        <span x-show="lang==='id'">
                            Buka detail sebuah perizinan untuk melihat timeline proses secara lengkap. Tambahkan
                            entri aktivitas baru dengan deskripsi dan status proses: <strong>Waiting</strong>,
                            <strong>Process</strong>, <strong>Rejected</strong>, <strong>Cancelled</strong>, atau
                            <strong>Done</strong>. Memilih Done, Rejected, atau Cancelled juga akan memperbarui
                            status keseluruhan perizinan menjadi Completed, Rejected, atau Cancelled; Waiting dan
                            Process hanya menambahkan catatan pada timeline tanpa menutup perizinan. Setiap
                            aktivitas dapat menyertakan lampirannya sendiri.
                        </span>
                    </p>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.2 Updating the Item List</span>
                        <span x-show="lang==='id'">3.2 Memperbarui Daftar Item</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Users with permit-input access (and without GA access) can update the item/quantity
                            list on a permit from its detail view, as long as the permit has not yet reached
                            status <strong>Completed</strong>. This replaces the full item list with whatever
                            is submitted.
                        </span>
                        <span x-show="lang==='id'">
                            Pengguna dengan akses input perizinan (dan tanpa akses GA) dapat memperbarui daftar
                            item/qty pada sebuah perizinan dari halaman detailnya, selama perizinan tersebut
                            belum berstatus <strong>Completed</strong>. Pembaruan ini akan mengganti seluruh
                            daftar item dengan data yang disubmit.
                        </span>
                    </p>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.3 Renewing a Permit</span>
                        <span x-show="lang==='id'">3.3 Memperpanjang Perizinan</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Before a permit expires, GA can click <strong>Renew</strong> on its detail page.
                            This creates a brand-new permit record — with a new document number and its own
                            renewal sequence — that copies the original's company, site, department, category,
                            and item list, but leaves the Start Date and End Date empty for GA to fill in once
                            the renewed permit is issued. The new record starts again at status
                            <strong>On Progress</strong>.
                        </span>
                        <span x-show="lang==='id'">
                            Sebelum sebuah perizinan berakhir, GA dapat mengklik <strong>Renew</strong> pada
                            halaman detailnya. Tindakan ini membuat data perizinan baru — dengan nomor dokumen
                            baru dan urutan perpanjangannya sendiri — yang menyalin company, site, departemen,
                            kategori, dan daftar item dari perizinan asal, namun mengosongkan Start Date dan End
                            Date agar diisi GA setelah perizinan hasil perpanjangan terbit. Data baru ini dimulai
                            kembali dengan status <strong>On Progress</strong>.
                        </span>
                    </p>

                    <div class="manual-note manual-important">
                        <span x-show="lang==='en'">
                            A permit can only be renewed once at a time — if an active (not Rejected/Cancelled)
                            renewal already exists for it, Renew is blocked until that renewal is resolved.
                        </span>
                        <span x-show="lang==='id'">
                            Sebuah perizinan hanya dapat diperpanjang satu kali dalam satu waktu — jika sudah
                            ada perpanjangan yang aktif (bukan Rejected/Cancelled) untuknya, tombol Renew akan
                            diblokir sampai perpanjangan tersebut selesai diproses.
                        </span>
                    </div>

                </section>

            </div>
        </div>

    </section>

    @if(auth()->user()->hasRole('GAACCESS'))
    <!-- ================= SECTION 4 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s4')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">4. For GA: Generating the Document Handover Report (Berita Acara)</span>
                    <span x-show="lang==='id'">4. Untuk GA: Membuat Berita Acara Serah Terima Dokumen</span>
                </span>

                <span x-text="openSection==='s4' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        When one or more permits reach status <strong>Completed</strong>, GA can select them
                        using the checkboxes in the rightmost column of the table and click
                        <strong>Generate Berita Acara</strong> to produce a downloadable Word document
                        formally recording the handover of the original permit documents.
                    </span>
                    <span x-show="lang==='id'">
                        Ketika satu atau beberapa perizinan sudah berstatus <strong>Completed</strong>, GA
                        dapat memilihnya menggunakan checkbox pada kolom paling kanan tabel lalu mengklik
                        <strong>Generate Berita Acara</strong> untuk menghasilkan dokumen Word yang dapat
                        diunduh, mencatat serah terima dokumen perizinan asli secara resmi.
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <span x-show="lang==='en'">Only permits with status <strong>Completed</strong> can be selected, and all selected permits must belong to the same company.</span>
                        <span x-show="lang==='id'">Hanya perizinan berstatus <strong>Completed</strong> yang dapat dipilih, dan seluruh perizinan yang dipilih harus berasal dari company yang sama.</span>
                    </li>
                    <li>
                        <span x-show="lang==='en'">You may add free-text notes describing any other documents included in the handover.</span>
                        <span x-show="lang==='id'">Anda dapat menambahkan catatan bebas yang menjelaskan dokumen lain yang turut diserahterimakan.</span>
                    </li>
                    <li>
                        <span x-show="lang==='en'">The generated document lists each selected permit's type, number, issuing authority, and validity period, with the company's logo and signature blocks.</span>
                        <span x-show="lang==='id'">Dokumen yang dihasilkan memuat jenis, nomor, instansi penerbit, dan masa berlaku setiap perizinan yang dipilih, lengkap dengan logo perusahaan dan kolom tanda tangan.</span>
                    </li>
                </ul>

            </div>
        </div>

    </section>
    @endif

</div>
