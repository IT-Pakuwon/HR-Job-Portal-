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
                        Training List is where employees browse published training schedules and register
                        for them. A registration always goes through approval, and — if a schedule's seats are
                        full — the registrant is placed on a waiting list and offered a seat automatically if
                        one opens up later.
                    </span>
                    <span x-show="lang==='id'">
                        Training List adalah tempat karyawan melihat jadwal training yang sudah dipublikasikan
                        dan mendaftar. Setiap pendaftaran selalu melalui approval, dan — jika kursi pada
                        sebuah schedule sudah penuh — pendaftar akan dimasukkan ke waiting list dan ditawari
                        kursi secara otomatis apabila kursi tersedia kembali.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Approval starts the moment you register, regardless of whether you got a seat or were
                        placed on the waiting list — being on the waiting list does not pause your approval.
                    </span>
                    <span x-show="lang==='id'">
                        Proses approval dimulai sejak Anda mendaftar, baik Anda mendapat kursi maupun masuk ke
                        waiting list — berada di waiting list tidak menghentikan proses approval Anda.
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
                    <span x-show="lang==='en'">2. Registering for a Training</span>
                    <span x-show="lang==='id'">2. Mendaftar Training</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The browse list only shows schedules that are <strong>Published</strong>, not yet past
                        their registration deadline or session date, and open to your own company. Open a
                        training to see its available schedule(s) and click <strong>Register</strong>.
                    </span>
                    <span x-show="lang==='id'">
                        Daftar yang ditampilkan hanya berisi schedule berstatus <strong>Published</strong>,
                        belum melewati batas pendaftaran atau tanggal sesinya, dan terbuka untuk company Anda.
                        Buka sebuah training untuk melihat schedule yang tersedia lalu klik
                        <strong>Register</strong>.
                    </span>
                </p>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.1 Registering Colleagues Together</span>
                        <span x-show="lang==='id'">2.1 Mendaftarkan Rekan Kerja Sekaligus</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            You can add one or more colleagues from your own department to the same
                            registration. Each participant you submit gets their own independent registration
                            document and approval chain — approving or rejecting one participant does not
                            affect the others in the same batch.
                        </span>
                        <span x-show="lang==='id'">
                            Anda dapat menambahkan satu atau beberapa rekan kerja dari departemen yang sama ke
                            dalam satu pendaftaran. Setiap peserta yang Anda submit akan mendapatkan dokumen
                            pendaftaran dan rantai approval-nya masing-masing — menyetujui atau menolak satu
                            peserta tidak memengaruhi peserta lain dalam batch yang sama.
                        </span>
                    </p>

                    <div class="manual-note manual-warning">
                        <span x-show="lang==='en'">
                            You cannot register the same person twice for the same schedule, and if the
                            training is marked <strong>Mandatory</strong>, a person already registered on
                            another schedule of that same training cannot be added again.
                        </span>
                        <span x-show="lang==='id'">
                            Anda tidak dapat mendaftarkan orang yang sama dua kali pada schedule yang sama, dan
                            jika training tersebut ditandai <strong>Mandatory</strong>, orang yang sudah
                            terdaftar pada schedule lain dari training yang sama tidak dapat didaftarkan lagi.
                        </span>
                    </div>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.2 Seats and the Waiting List</span>
                        <span x-show="lang==='id'">2.2 Kursi dan Waiting List</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Seats are drawn from your own company's quota for that schedule, on a first-come
                            basis. If seats remain, your registration is seated immediately; if not, you are
                            placed on the <strong>Waiting List</strong>. When registering several participants
                            at once and only some seats remain, part of the batch may be seated and the rest
                            waitlisted.
                        </span>
                        <span x-show="lang==='id'">
                            Kursi diambil dari kuota company Anda untuk schedule tersebut, berdasarkan urutan
                            pendaftaran. Jika kursi masih tersedia, pendaftaran Anda langsung mendapat kursi;
                            jika tidak, Anda akan dimasukkan ke <strong>Waiting List</strong>. Saat mendaftarkan
                            beberapa peserta sekaligus dan hanya sebagian kursi yang tersisa, sebagian batch
                            bisa langsung mendapat kursi sementara sisanya masuk waiting list.
                        </span>
                    </p>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            If a seated participant cancels or has their registration declined, the next
                            person on the waiting list is automatically sent an <strong>Offer</strong>. Open
                            your registration from <strong>My Registrations</strong> and click
                            <strong>Accept</strong> or <strong>Decline</strong>. Accepting only secures your
                            seat — if your document was already fully approved while you waited, it stays
                            approved; if not, approval continues as normal.
                        </span>
                        <span x-show="lang==='id'">
                            Jika peserta yang sudah mendapat kursi membatalkan atau pendaftarannya ditolak,
                            orang berikutnya pada waiting list akan otomatis dikirimi <strong>Offer</strong>.
                            Buka pendaftaran Anda dari <strong>My Registrations</strong> lalu klik
                            <strong>Accept</strong> atau <strong>Decline</strong>. Menerima offer hanya
                            mengamankan kursi Anda — jika dokumen Anda sudah fully approved selama menunggu,
                            statusnya tetap approved; jika belum, proses approval berjalan seperti biasa.
                        </span>
                    </p>

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
                    <span x-show="lang==='en'">3. My Registrations & Approval</span>
                    <span x-show="lang==='id'">3. My Registrations & Approval</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The <strong>My Registrations</strong> tab lists every training you have registered
                        for — your own and, if applicable, colleagues you registered — together with their
                        document status and seating status.
                    </span>
                    <span x-show="lang==='id'">
                        Tab <strong>My Registrations</strong> menampilkan seluruh training yang pernah Anda
                        daftarkan — milik Anda sendiri dan, jika ada, rekan kerja yang Anda daftarkan —
                        beserta status dokumen dan status kursinya.
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>Pending</strong> —
                        <span x-show="lang==='en'">Submitted and going through the approval chain.</span>
                        <span x-show="lang==='id'">Sudah disubmit dan sedang melewati rantai approval.</span>
                    </li>
                    <li>
                        <strong>Approved</strong> —
                        <span x-show="lang==='en'">Fully approved. A QR/barcode becomes available for attendance check-in once approval completes.</span>
                        <span x-show="lang==='id'">Sudah disetujui sepenuhnya. QR/barcode untuk check-in kehadiran tersedia setelah approval selesai.</span>
                    </li>
                    <li>
                        <strong>Rejected</strong> —
                        <span x-show="lang==='en'">Declined by an approver; the registration is closed.</span>
                        <span x-show="lang==='id'">Ditolak oleh approver; pendaftaran ditutup.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Waiting List / Offered / Cancelled (seating status)</span>
                            <span x-show="lang==='id'">Waiting List / Offered / Cancelled (status kursi)</span>
                        </strong> —
                        <span x-show="lang==='en'">Shown alongside the document status to reflect whether a seat is actually secured, separate from the approval outcome.</span>
                        <span x-show="lang==='id'">Ditampilkan berdampingan dengan status dokumen untuk menunjukkan apakah kursi benar-benar sudah diamankan, terpisah dari hasil approval.</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click any row to open its detail and see the approval timeline. You can cancel your
                        own registration from here while it is still in progress.
                    </span>
                    <span x-show="lang==='id'">
                        Klik baris mana pun untuk membuka detailnya dan melihat timeline approval. Anda dapat
                        membatalkan pendaftaran Anda sendiri dari sini selama masih berjalan.
                    </span>
                </p>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">3.1 For Approvers</span>
                        <span x-show="lang==='id'">3.1 Untuk Approver</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            The <strong>Pending Approvals</strong> tab lists registrations waiting on your
                            approval step. Open one to review the participant and training details, then
                            <strong>Approve</strong> or <strong>Reject</strong> it. Each participant's document
                            is approved individually.
                        </span>
                        <span x-show="lang==='id'">
                            Tab <strong>Pending Approvals</strong> menampilkan pendaftaran yang menunggu
                            approval Anda. Buka salah satu untuk meninjau detail peserta dan training, lalu
                            <strong>Approve</strong> atau <strong>Reject</strong>. Setiap dokumen peserta
                            disetujui secara individual.
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
                    <span x-show="lang==='en'">4. After the Training: Feedback & Certificate</span>
                    <span x-show="lang==='id'">4. Setelah Training: Feedback & Sertifikat</span>
                </span>

                <span x-text="openSection==='s4' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Once your attendance has been recorded for a session, your Training List dashboard
                        may show a reminder to fill out feedback for it. Open the registration and click
                        <strong>Fill Feedback</strong> to answer the questions set up for that training (these
                        can be rating scales, single-choice questions, or open text) while the feedback window
                        is still open.
                    </span>
                    <span x-show="lang==='id'">
                        Setelah kehadiran Anda tercatat untuk suatu sesi, dashboard Training List Anda dapat
                        menampilkan pengingat untuk mengisi feedback. Buka pendaftaran tersebut lalu klik
                        <strong>Fill Feedback</strong> untuk menjawab pertanyaan yang disiapkan untuk training
                        tersebut (dapat berupa skala rating, pilihan tunggal, atau teks bebas) selama jendela
                        feedback masih terbuka.
                    </span>
                </p>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        You can also download your attendance <strong>Certificate</strong> from the
                        registration detail once attendance has been recorded.
                    </span>
                    <span x-show="lang==='id'">
                        Anda juga dapat mengunduh <strong>Certificate</strong> kehadiran dari detail
                        pendaftaran setelah kehadiran Anda tercatat.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        Attended trainings and any stars earned are also summarized in the "My Trainings &
                        Stars" panel on your profile page.
                    </span>
                    <span x-show="lang==='id'">
                        Training yang sudah diikuti beserta star yang diperoleh juga dirangkum pada panel "My
                        Trainings & Stars" di halaman profil Anda.
                    </span>
                </div>

            </div>
        </div>

    </section>

</div>
