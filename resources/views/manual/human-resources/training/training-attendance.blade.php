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
                    <span x-show="lang==='en'">1. Overview</span>
                    <span x-show="lang==='id'">1. Gambaran Umum</span>
                </span>

                <span x-text="openSection==='s1' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s1'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Training Attendance is where HR/L&D selects a scheduled training event, checks
                        participants in (by scanning a barcode/QR or manually), reviews or corrects who
                        attended after the fact, manages the post-training feedback window and its results,
                        and runs attendance reports across trainings.
                    </span>
                    <span x-show="lang==='id'">
                        Training Attendance adalah tempat HR/L&D memilih sebuah sesi training terjadwal,
                        melakukan check-in peserta (dengan memindai barcode/QR atau secara manual), meninjau
                        atau mengoreksi data kehadiran setelahnya, mengelola jendela feedback pasca-training
                        beserta hasilnya, dan menjalankan laporan kehadiran lintas training.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Only schedules with status <strong>Published</strong> or <strong>Closed</strong> appear
                        in the event list — a schedule that is still Draft has no registrants yet, so there is
                        nothing to take attendance for.
                    </span>
                    <span x-show="lang==='id'">
                        Hanya schedule berstatus <strong>Published</strong> atau <strong>Closed</strong> yang
                        muncul pada daftar event — schedule yang masih Draft belum memiliki pendaftar, sehingga
                        belum ada yang perlu dicatat kehadirannya.
                    </span>
                </div>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 2 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s2')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">2. Checking In Participants</span>
                    <span x-show="lang==='id'">2. Check-in Peserta</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Select an event from the list to open its <strong>Roster</strong> — the participants
                        who hold an approved, seated registration for that session (waitlisted or
                        not-yet-approved registrations don't appear here).
                    </span>
                    <span x-show="lang==='id'">
                        Pilih sebuah event dari daftar untuk membuka <strong>Roster</strong>-nya — peserta
                        yang memiliki pendaftaran approved dan sudah mendapat kursi untuk sesi tersebut
                        (pendaftaran yang masih waiting list atau belum approved tidak akan muncul di sini).
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Scan Barcode/QR</span>
                            <span x-show="lang==='id'">Scan Barcode/QR</span>
                        </strong> —
                        <span x-show="lang==='en'">Accepts the participant's own per-training code, their personal account badge, or the QR on their profile page (a vCard that also carries their badge code) — any of the three marks them present for the selected event.</span>
                        <span x-show="lang==='id'">Dapat memindai kode khusus training milik peserta, badge akun pribadi mereka, atau QR pada halaman profil mereka (vCard yang juga memuat kode badge) — ketiganya dapat menandai peserta hadir untuk event yang dipilih.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Mark Attend manually</span>
                            <span x-show="lang==='id'">Mark Attend manual</span>
                        </strong> —
                        <span x-show="lang==='en'">Click a roster row directly if scanning isn't practical.</span>
                        <span x-show="lang==='id'">Klik langsung baris pada roster apabila scanning tidak memungkinkan.</span>
                    </li>
                </ul>

                <div class="manual-note manual-warning">
                    <span x-show="lang==='en'">
                        Check-in only works within the event's attendance window (around its scheduled date);
                        outside that window, scanning or marking attendance is rejected as "not yet valid or
                        expired."
                    </span>
                    <span x-show="lang==='id'">
                        Check-in hanya berfungsi dalam jendela waktu kehadiran event tersebut (di sekitar
                        tanggal terjadwalnya); di luar jendela waktu itu, scan atau penandaan kehadiran akan
                        ditolak sebagai "belum berlaku atau sudah kedaluwarsa."
                    </span>
                </div>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 3 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s3')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">3. After the Event: Corrections, Exports & Feedback</span>
                    <span x-show="lang==='id'">3. Setelah Event: Koreksi, Export & Feedback</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.1 After-Event List & Undoing a Check-in</span>
                        <span x-show="lang==='id'">3.1 Daftar Setelah Event & Membatalkan Check-in</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Open an event's <strong>After Event</strong> view to see everyone who was actually
                            checked in, along with a full scan history per person (including any voided
                            scans). HR admin users can <strong>Undo</strong> a mis-scan here, which clears the
                            check-in and voids the log entry so the person can be re-scanned correctly.
                        </span>
                        <span x-show="lang==='id'">
                            Buka tampilan <strong>After Event</strong> pada sebuah event untuk melihat siapa
                            saja yang benar-benar tercatat hadir, lengkap dengan riwayat scan per orang
                            (termasuk scan yang sudah dibatalkan). Pengguna admin HR dapat mengklik
                            <strong>Undo</strong> di sini untuk membatalkan scan yang salah, yang akan
                            menghapus catatan kehadiran dan membatalkan entri log-nya agar orang tersebut bisa
                            di-scan ulang dengan benar.
                        </span>
                    </p>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.2 Exporting Attendance</span>
                        <span x-show="lang==='id'">3.2 Mengekspor Kehadiran</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            From an event, export the list of checked-in participants to <strong>Excel</strong>,
                            <strong>CSV</strong>, or a printable <strong>PDF</strong> attendance sheet.
                        </span>
                        <span x-show="lang==='id'">
                            Dari sebuah event, ekspor daftar peserta yang sudah check-in ke
                            <strong>Excel</strong>, <strong>CSV</strong>, atau lembar kehadiran
                            <strong>PDF</strong> yang dapat dicetak.
                        </span>
                    </p>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.3 Feedback Window & Results</span>
                        <span x-show="lang==='id'">3.3 Jendela Feedback & Hasilnya</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            HR admin users can <strong>Open Feedback</strong> for an event so attendees can
                            submit their feedback, and <strong>Close Feedback</strong> once collection should
                            stop (it can be re-opened again later if needed). The <strong>Feedback Results</strong>
                            view shows, per question: the average and rating distribution for rating
                            questions, option counts for single-choice questions, and the raw list of comments
                            for open-text questions — along with how many attendees have responded. Results can
                            be exported to Excel as one row per respondent.
                        </span>
                        <span x-show="lang==='id'">
                            Pengguna admin HR dapat mengklik <strong>Open Feedback</strong> pada sebuah event
                            agar peserta dapat mengisi feedback, dan <strong>Close Feedback</strong> saat
                            pengumpulan harus dihentikan (dapat dibuka kembali nanti bila diperlukan). Tampilan
                            <strong>Feedback Results</strong> menampilkan, per pertanyaan: rata-rata dan
                            distribusi untuk pertanyaan rating, jumlah per pilihan untuk pertanyaan pilihan
                            tunggal, dan daftar komentar mentah untuk pertanyaan teks bebas — beserta jumlah
                            peserta yang sudah merespons. Hasil dapat diekspor ke Excel, satu baris per
                            responden.
                        </span>
                    </p>

                </section>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 4 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s4')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">4. Training Report</span>
                    <span x-show="lang==='id'">4. Training Report</span>
                </span>

                <span x-text="openSection==='s4' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The <strong>Training Report</strong> tab summarizes attendance across trainings rather
                        than within a single event. Filter by date range, training, company, and department
                        (scoped to what you are allowed to see), then review:
                    </span>
                    <span x-show="lang==='id'">
                        Tab <strong>Training Report</strong> merangkum kehadiran lintas training, bukan hanya
                        satu event. Filter berdasarkan rentang tanggal, training, company, dan departemen
                        (dibatasi sesuai akses Anda), lalu tinjau:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Summary totals</span>
                            <span x-show="lang==='id'">Total ringkasan</span>
                        </strong> —
                        <span x-show="lang==='en'">Total schedules held and total attendance in the selected range, with a breakdown by company and by department.</span>
                        <span x-show="lang==='id'">Total schedule yang berlangsung dan total kehadiran pada rentang yang dipilih, dengan rincian per company dan per departemen.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Employee drill-down</span>
                            <span x-show="lang==='id'">Rincian per karyawan</span>
                        </strong> —
                        <span x-show="lang==='en'">A searchable list of employees with how many distinct trainings and sessions they attended and the stars they earned; expand a row to see each training they went to.</span>
                        <span x-show="lang==='id'">Daftar karyawan yang dapat dicari, menampilkan berapa training dan sesi berbeda yang mereka ikuti serta star yang diperoleh; perluas sebuah baris untuk melihat setiap training yang mereka ikuti.</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Use <strong>Export</strong> to download the employee table (with whatever filters and
                        search term are currently applied) to Excel.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan <strong>Export</strong> untuk mengunduh tabel karyawan (dengan filter dan kata
                        kunci pencarian yang sedang diterapkan) ke Excel.
                    </span>
                </p>

            </div>
        </div>

    </section>

</div>
