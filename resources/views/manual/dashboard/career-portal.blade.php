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
                        Career Portal is the recruitment reporting dashboard. It tracks the hiring pipeline
                        from requisition (PRF) to job posting to applicant, covering both candidates who
                        applied to a specific job posting and candidates who registered directly through
                        the public career site without applying to a particular opening.
                    </span>
                    <span x-show="lang==='id'">
                        Career Portal adalah dashboard pelaporan rekrutmen. Halaman ini memantau alur
                        perekrutan mulai dari permintaan karyawan (PRF), job posting, hingga pelamar,
                        mencakup baik kandidat yang melamar pada lowongan tertentu maupun kandidat yang
                        mendaftar langsung melalui situs karier publik tanpa melamar lowongan tertentu.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Use the <strong>Job Applicant</strong> / <strong>Self Applicant</strong> source
                        toggle (where available) to switch between candidates sourced from a job posting
                        and candidates who self-registered. Figures that depend on a specific job (such as
                        the hiring funnel and top job) only apply to Job Applicant data, since self
                        registrations are not tied to one posting until later mapped.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan toggle sumber <strong>Job Applicant</strong> / <strong>Self Applicant</strong>
                        (jika tersedia) untuk beralih antara kandidat yang bersumber dari job posting dan
                        kandidat yang mendaftar sendiri. Angka yang bergantung pada lowongan tertentu
                        (seperti funnel perekrutan dan top job) hanya berlaku untuk data Job Applicant,
                        karena pendaftaran mandiri belum terkait ke satu lowongan sampai dipetakan kemudian.
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
                    <span x-show="lang==='en'">2. Filters</span>
                    <span x-show="lang==='id'">2. Filter</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The filter bar at the top lets you narrow every chart and table on the page down to:
                    </span>
                    <span x-show="lang==='id'">
                        Filter di bagian atas memungkinkan Anda mempersempit seluruh chart dan tabel pada
                        halaman ini berdasarkan:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Company Group / Company / Division / Department</span>
                            <span x-show="lang==='id'">Company Group / Company / Division / Department</span>
                        </strong> —
                        <span x-show="lang==='en'">Narrows to a group of companies, a single company, a division, or a department. The Company Group selector is locked to your own group if your account belongs to one (only an administrator can see and switch between all groups).</span>
                        <span x-show="lang==='id'">Mempersempit ke sekelompok perusahaan, satu perusahaan, divisi, atau departemen tertentu. Selector Company Group akan terkunci ke grup Anda sendiri jika akun Anda tergabung dalam satu grup (hanya administrator yang dapat melihat dan berpindah antar semua grup).</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Location</span>
                            <span x-show="lang==='id'">Location</span>
                        </strong> —
                        <span x-show="lang==='en'">Filters by the job posting's location.</span>
                        <span x-show="lang==='id'">Menyaring berdasarkan lokasi job posting.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Date Range</span>
                            <span x-show="lang==='id'">Date Range</span>
                        </strong> —
                        <span x-show="lang==='en'">Filters candidates by their apply date, using quick presets or a custom range.</span>
                        <span x-show="lang==='id'">Menyaring kandidat berdasarkan tanggal melamar, menggunakan preset cepat atau rentang kustom.</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Reset</strong> to clear all active filters and return to the default,
                        unfiltered view.
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Reset</strong> untuk menghapus semua filter yang aktif dan kembali ke
                        tampilan default tanpa filter.
                    </span>
                </p>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 3 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s3')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">3. Reading the Dashboard</span>
                    <span x-show="lang==='id'">3. Membaca Dashboard</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.1 Requisition & Job Status</span>
                        <span x-show="lang==='id'">3.1 Status PRF & Job Posting</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The top row shows how many Personnel Request Forms (PRF) are on progress,
                            revised, rejected, or completed, and how many resulting job postings are
                            Posted, Unposted, Closed, or on Hold — including the share of requisitions
                            that are already live on the career site.
                        </span>
                        <span x-show="lang==='id'">
                            Baris pertama menampilkan jumlah Personnel Request Form (PRF) yang sedang
                            berjalan, direvisi, ditolak, atau selesai, serta jumlah job posting yang
                            dihasilkan dengan status Posted, Unposted, Closed, atau Hold — termasuk
                            persentase permintaan karyawan yang sudah tayang di situs karier.
                        </span>
                    </p>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.2 Applicant Funnel</span>
                        <span x-show="lang==='id'">3.2 Funnel Pelamar</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            This row totals all candidates (Job Applicant plus Self Applicant), how many
                            were rejected, and how many were joined/hired. A separate hiring funnel chart
                            shows how candidates thin out stage by stage (Applied → HC Review → Interview →
                            Psycho Test → Offering → Hired), and a timing chart shows the average number of
                            days spent between each stage — useful for spotting where the process slows down.
                        </span>
                        <span x-show="lang==='id'">
                            Baris ini menjumlahkan seluruh kandidat (Job Applicant ditambah Self Applicant),
                            berapa yang ditolak, dan berapa yang joined/hired. Chart funnel perekrutan
                            terpisah menunjukkan bagaimana jumlah kandidat menyusut di setiap tahap (Applied
                            → HC Review → Interview → Psycho Test → Offering → Hired), dan chart waktu
                            menunjukkan rata-rata jumlah hari yang dihabiskan antar tahap — berguna untuk
                            melihat di tahap mana proses melambat.
                        </span>
                    </p>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.3 Demographics</span>
                        <span x-show="lang==='id'">3.3 Demografi</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Breaks candidates down by gender, age bracket, education level, residential
                            city (top 10), and hiring source (how they heard about the opening).
                        </span>
                        <span x-show="lang==='id'">
                            Merinci kandidat berdasarkan gender, kelompok usia, tingkat pendidikan, kota
                            domisili (10 teratas), dan sumber informasi lowongan (bagaimana mereka mengetahui
                            lowongan tersebut).
                        </span>
                    </p>

                    <div class="manual-note manual-info">
                        <span x-show="lang==='en'">
                            Some fields (education, hiring source) are often left blank by candidates — the
                            dashboard calls this out directly, and you can export the underlying "unknown"
                            or "others" rows behind the Gender, Education, and Residential City charts as a
                            CSV for follow-up.
                        </span>
                        <span x-show="lang==='id'">
                            Beberapa field (pendidikan, sumber informasi) sering dibiarkan kosong oleh
                            kandidat — dashboard menampilkan hal ini secara langsung, dan Anda dapat
                            mengekspor baris data "unknown" atau "others" di balik chart Gender, Education,
                            dan Residential City sebagai CSV untuk ditindaklanjuti.
                        </span>
                    </div>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.4 Division, Job & Turnaround</span>
                        <span x-show="lang==='id'">3.4 Divisi, Pekerjaan & Turnaround</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Further down you'll find the top 10 divisions by candidates applied, a table of
                            total candidates applied per job, and the PRF-completed-to-job-posted
                            turnaround time, bucketed into ranges (under 3 days, 3–7 days, and so on) so you
                            can see how quickly approved requisitions turn into live postings.
                        </span>
                        <span x-show="lang==='id'">
                            Lebih ke bawah Anda akan menemukan 10 divisi teratas berdasarkan jumlah pelamar,
                            tabel total kandidat yang melamar per pekerjaan, dan waktu turnaround dari PRF
                            selesai hingga job posting tayang, dikelompokkan ke dalam rentang (kurang dari 3
                            hari, 3–7 hari, dan seterusnya) sehingga Anda bisa melihat seberapa cepat
                            permintaan yang disetujui berubah menjadi lowongan yang tayang.
                        </span>
                    </p>

                </section>

            </div>
        </div>

    </section>

</div>
