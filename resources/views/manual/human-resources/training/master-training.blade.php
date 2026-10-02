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
                        Master Training is where the HR/Learning & Development (L&D) team maintains the
                        training catalog — the list of training programs the company offers — and schedules
                        the dated sessions (batches) that employees can later register for in
                        <strong>Training List</strong>. It also contains the supporting master data (Training
                        Places and Training Categories) used throughout the training module.
                    </span>
                    <span x-show="lang==='id'">
                        Master Training adalah tempat tim HR/Learning & Development (L&D) mengelola katalog
                        training — daftar program training yang ditawarkan perusahaan — serta menjadwalkan
                        sesi bertanggal (batch) yang nantinya dapat didaftari karyawan melalui
                        <strong>Training List</strong>. Halaman ini juga memuat data master pendukung (Training
                        Places dan Training Categories) yang digunakan di seluruh modul training.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Think of it as two layers: a <strong>Training</strong> row is the program itself
                        (e.g. "Fire Safety Awareness"), while each training can have one or more
                        <strong>Schedules</strong> — concrete dates, places, and seat quotas — that employees
                        actually register against.
                    </span>
                    <span x-show="lang==='id'">
                        Anggap sebagai dua lapisan: satu baris <strong>Training</strong> adalah program itu
                        sendiri (misalnya "Fire Safety Awareness"), sedangkan setiap training dapat memiliki
                        satu atau beberapa <strong>Schedule</strong> — tanggal, tempat, dan kuota kursi yang
                        konkret — yang benar-benar didaftari oleh karyawan.
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
                    <span x-show="lang==='en'">2. Managing the Training Catalog</span>
                    <span x-show="lang==='id'">2. Mengelola Katalog Training</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Add Training</strong> to create a new program, or use the row actions to
                        edit an existing one. Each training has the following fields:
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Add Training</strong> untuk membuat program baru, atau gunakan aksi pada
                        baris tabel untuk mengedit program yang sudah ada. Setiap training memiliki kolom
                        berikut:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Training Name</span>
                            <span x-show="lang==='id'">Nama Training</span>
                        </strong> —
                        <span x-show="lang==='en'">The name of the program.</span>
                        <span x-show="lang==='id'">Nama program training.</span>
                    </li>
                    <li>
                        <strong>Category</strong> —
                        <span x-show="lang==='en'">Picked from the Training Categories set up in the Setup page (section 4).</span>
                        <span x-show="lang==='id'">Dipilih dari Training Categories yang diatur pada halaman Setup (bagian 4).</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Training Type</span>
                            <span x-show="lang==='id'">Jenis Training</span>
                        </strong> —
                        <span x-show="lang==='en'"><strong>Internal</strong> (delivered by an in-house speaker) or <strong>External</strong> (delivered by an outside provider).</span>
                        <span x-show="lang==='id'"><strong>Internal</strong> (dibawakan oleh pembicara internal) atau <strong>External</strong> (dibawakan oleh penyedia dari luar).</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Mandatory</span>
                            <span x-show="lang==='id'">Wajib</span>
                        </strong> —
                        <span x-show="lang==='en'">When checked, an employee who registers for one schedule of this training is blocked from registering for any other schedule of the same training — it's a single, one-time program for them.</span>
                        <span x-show="lang==='id'">Jika dicentang, karyawan yang sudah mendaftar pada salah satu schedule training ini tidak dapat mendaftar lagi pada schedule lain dari training yang sama — program ini hanya perlu diikuti satu kali.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Description</span>
                            <span x-show="lang==='id'">Deskripsi</span>
                        </strong> —
                        <span x-show="lang==='en'">Free-text details shown to employees browsing the training.</span>
                        <span x-show="lang==='id'">Keterangan bebas yang ditampilkan kepada karyawan saat melihat training tersebut.</span>
                    </li>
                </ul>

                <div class="manual-note manual-warning">
                    <span x-show="lang==='en'">
                        A training can only be deactivated once every one of its schedules has reached status
                        <strong>Closed</strong> or <strong>Cancelled</strong> — if any schedule is still Draft
                        or Published, deactivation is blocked.
                    </span>
                    <span x-show="lang==='id'">
                        Sebuah training hanya dapat dinonaktifkan setelah seluruh schedule-nya berstatus
                        <strong>Closed</strong> atau <strong>Cancelled</strong> — jika masih ada schedule
                        berstatus Draft atau Published, penonaktifan akan diblokir.
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
                    <span x-show="lang==='en'">3. Scheduling Sessions (Batches)</span>
                    <span x-show="lang==='id'">3. Menjadwalkan Sesi (Batch)</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Open a training row and go to its <strong>Sessions</strong> tab to add dated
                        schedules. Click <strong>Add Schedule</strong> to create a new batch, which can include
                        one or more specific dates sharing the same target audience and speaker setup.
                    </span>
                    <span x-show="lang==='id'">
                        Buka sebuah baris training lalu masuk ke tab <strong>Sessions</strong> untuk
                        menambahkan jadwal bertanggal. Klik <strong>Add Schedule</strong> untuk membuat batch
                        baru, yang dapat mencakup satu atau beberapa tanggal yang berbagi target peserta dan
                        susunan pembicara yang sama.
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Batch Name & Poster</span>
                            <span x-show="lang==='id'">Nama Batch & Poster</span>
                        </strong> —
                        <span x-show="lang==='en'">A name for this batch, with an optional poster image shown to employees.</span>
                        <span x-show="lang==='id'">Nama untuk batch ini, dengan poster opsional yang ditampilkan kepada karyawan.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Target Job Level</span>
                            <span x-show="lang==='id'">Target Job Level</span>
                        </strong> —
                        <span x-show="lang==='en'">One or more job-level groups this batch is intended for. Only employees resolving to one of the selected levels can register; employees whose level cannot be determined are still allowed through.</span>
                        <span x-show="lang==='id'">Satu atau beberapa kelompok job level yang menjadi sasaran batch ini. Hanya karyawan yang sesuai dengan salah satu level terpilih yang dapat mendaftar; karyawan yang levelnya tidak dapat ditentukan tetap diperbolehkan mendaftar.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Speaker</span>
                            <span x-show="lang==='id'">Pembicara</span>
                        </strong> —
                        <span x-show="lang==='en'">Pick an internal speaker from the employee list, or toggle External Speaker to type a speaker name that is not in the system.</span>
                        <span x-show="lang==='id'">Pilih pembicara internal dari daftar karyawan, atau aktifkan External Speaker untuk mengetikkan nama pembicara yang tidak terdaftar di sistem.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Date, Place, Registration Deadline</span>
                            <span x-show="lang==='id'">Tanggal, Tempat, Batas Pendaftaran</span>
                        </strong> —
                        <span x-show="lang==='en'">Add one row per session date within the batch, each with its own place (from Training Places) and registration deadline.</span>
                        <span x-show="lang==='id'">Tambahkan satu baris untuk setiap tanggal sesi dalam batch, masing-masing dengan tempat (dari Training Places) dan batas pendaftarannya sendiri.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Quota per Company</span>
                            <span x-show="lang==='id'">Kuota per Company</span>
                        </strong> —
                        <span x-show="lang==='en'">How many seats of this schedule are reserved for each company. Registrations draw from and consume their own company's quota.</span>
                        <span x-show="lang==='id'">Berapa banyak kursi pada schedule ini yang dialokasikan untuk masing-masing company. Pendaftaran akan mengambil dan mengurangi kuota dari company peserta itu sendiri.</span>
                    </li>
                </ul>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.1 Schedule Status</span>
                        <span x-show="lang==='id'">3.1 Status Schedule</span>
                    </h3>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>Draft</strong> —
                            <span x-show="lang==='en'">Created but not yet visible to employees. Can be edited freely or cancelled.</span>
                            <span x-show="lang==='id'">Sudah dibuat namun belum terlihat oleh karyawan. Masih dapat diedit bebas atau dibatalkan.</span>
                        </li>
                        <li>
                            <strong>Published</strong> —
                            <span x-show="lang==='en'">Visible and open for registration in Training List until its registration deadline or schedule date. Can move to Closed or Cancelled.</span>
                            <span x-show="lang==='id'">Terlihat dan terbuka untuk pendaftaran di Training List sampai batas pendaftaran atau tanggal sesinya. Dapat berubah menjadi Closed atau Cancelled.</span>
                        </li>
                        <li>
                            <strong>Closed</strong> —
                            <span x-show="lang==='en'">Registration has ended; this is also the status used for attendance taking. Final — cannot change further.</span>
                            <span x-show="lang==='id'">Pendaftaran sudah berakhir; status ini juga digunakan untuk pencatatan kehadiran. Final — tidak dapat diubah lagi.</span>
                        </li>
                        <li>
                            <strong>Cancelled</strong> —
                            <span x-show="lang==='en'">The schedule will not run. Final — cannot change further.</span>
                            <span x-show="lang==='id'">Schedule tidak akan dilaksanakan. Final — tidak dapat diubah lagi.</span>
                        </li>
                    </ul>

                    <div class="manual-note manual-important">
                        <span x-show="lang==='en'">
                            A schedule must be set to <strong>Published</strong> before employees can see and
                            register for it in Training List. Reschedule is available to move a batch's dates
                            if needed before it is Closed.
                        </span>
                        <span x-show="lang==='id'">
                            Sebuah schedule harus diubah menjadi <strong>Published</strong> terlebih dahulu
                            agar dapat dilihat dan didaftari karyawan di Training List. Fitur Reschedule
                            tersedia untuk memindahkan tanggal batch bila diperlukan sebelum berstatus Closed.
                        </span>
                    </div>

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
                    <span x-show="lang==='en'">4. Setup: Training Places & Categories</span>
                    <span x-show="lang==='id'">4. Setup: Training Places & Categories</span>
                </span>

                <span x-text="openSection==='s4' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Setup</strong> on the Master Training page to manage the reference data
                        used when creating trainings and schedules. It has two tabs:
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Setup</strong> pada halaman Master Training untuk mengelola data
                        referensi yang digunakan saat membuat training dan schedule. Terdapat dua tab:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Training Places</strong> —
                        <span x-show="lang==='en'">The list of venues (name and address) selectable in a schedule's Place field.</span>
                        <span x-show="lang==='id'">Daftar lokasi (nama dan alamat) yang dapat dipilih pada kolom Place di sebuah schedule.</span>
                    </li>
                    <li>
                        <strong>Training Categories</strong> —
                        <span x-show="lang==='en'">The list of categories selectable in a training's Category field.</span>
                        <span x-show="lang==='id'">Daftar kategori yang dapat dipilih pada kolom Category di sebuah training.</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Use <strong>Add</strong> on either tab to create a new entry, and the row actions to
                        edit or deactivate (set status Inactive) an existing one.
                    </span>
                    <span x-show="lang==='id'">
                        Gunakan <strong>Add</strong> pada masing-masing tab untuk membuat data baru, dan aksi
                        pada baris tabel untuk mengedit atau menonaktifkan (ubah status menjadi Inactive) data
                        yang sudah ada.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Deactivating a place or category only removes it from selection in new
                        trainings/schedules — it does not change records already saved.
                    </span>
                    <span x-show="lang==='id'">
                        Menonaktifkan place atau category hanya menghilangkannya dari pilihan pada
                        training/schedule baru — tidak mengubah data yang sudah tersimpan sebelumnya.
                    </span>
                </div>

            </div>
        </div>

    </section>

</div>
