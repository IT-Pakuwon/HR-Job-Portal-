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
                        Dashboard is the landing page you see right after logging in. Its content is not
                        the same for everyone — it is built around the "homepage" configured for your
                        account, so the widgets you see reflect your role (for example Approval, IT,
                        Warehouse, Cost Control, Purchasing, HR, General Affair, Vendor Product
                        Collection/Promotion/Loyalty, Finance, Treasury, Corporate Teknik, or Recruitment).
                        Most users land on the <strong>Approval Dashboard</strong>, described in this manual.
                    </span>
                    <span x-show="lang==='id'">
                        Dashboard adalah halaman utama yang muncul setelah Anda login. Isinya tidak sama
                        untuk setiap pengguna — halaman ini dibangun berdasarkan "homepage" yang
                        dikonfigurasi untuk akun Anda, sehingga widget yang ditampilkan mengikuti peran
                        Anda (misalnya Approval, IT, Warehouse, Cost Control, Purchasing, HR, General
                        Affair, Vendor Product Collection/Promotion/Loyalty, Finance, Treasury, Corporate
                        Teknik, atau Recruitment). Sebagian besar pengguna akan melihat
                        <strong>Approval Dashboard</strong>, yang dijelaskan dalam manual ini.
                    </span>
                </p>

                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">
                        If your Dashboard looks different from the screens described here, it simply
                        means your account's homepage is set to a different module. The general behavior
                        (stat tiles, filters, lists) follows the same pattern across all variants.
                    </span>
                    <span x-show="lang==='id'">
                        Jika tampilan Dashboard Anda berbeda dari yang dijelaskan di sini, itu berarti
                        homepage akun Anda diatur ke modul yang berbeda. Perilaku umumnya (kartu statistik,
                        filter, daftar) mengikuti pola yang sama di semua varian.
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
                    <span x-show="lang==='en'">2. Approval Snapshot & Favourite Shortcuts</span>
                    <span x-show="lang==='id'">2. Ringkasan Approval & Shortcut Favorit</span>
                </span>

                <span x-text="openSection==='s2' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.1 Stat Tiles</span>
                        <span x-show="lang==='id'">2.1 Kartu Statistik</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            At the top of the Approval Dashboard you see three live counters:
                        </span>
                        <span x-show="lang==='id'">
                            Di bagian atas Approval Dashboard Anda akan melihat tiga penghitung langsung:
                        </span>
                    </p>

                    <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Waiting Approval</span>
                                <span x-show="lang==='id'">Waiting Approval</span>
                            </strong> —
                            <span x-show="lang==='en'">Documents currently waiting for your approval action.</span>
                            <span x-show="lang==='id'">Dokumen yang sedang menunggu tindakan approval dari Anda.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Waiting &gt; 3 Days</span>
                                <span x-show="lang==='id'">Waiting &gt; 3 Days</span>
                            </strong> —
                            <span x-show="lang==='en'">Of those, how many have been pending for more than 3 days — a quick way to spot aging documents.</span>
                            <span x-show="lang==='id'">Dari jumlah di atas, berapa banyak yang sudah pending lebih dari 3 hari — cara cepat untuk melihat dokumen yang mulai menumpuk.</span>
                        </li>
                        <li>
                            <strong>
                                <span x-show="lang==='en'">Approved Today</span>
                                <span x-show="lang==='id'">Approved Today</span>
                            </strong> —
                            <span x-show="lang==='en'">How many documents you have approved so far today.</span>
                            <span x-show="lang==='id'">Berapa banyak dokumen yang sudah Anda approve hari ini.</span>
                        </li>
                    </ul>

                    <div class="manual-note manual-info">
                        <span x-show="lang==='en'">
                            These counters refresh automatically — the countdown next to
                            <strong>Next Refresh</strong> shows when the next auto-update happens. You can
                            also click <strong>Refresh</strong> any time to update them immediately.
                        </span>
                        <span x-show="lang==='id'">
                            Ketiga kartu ini diperbarui secara otomatis — hitung mundur di samping
                            <strong>Next Refresh</strong> menunjukkan kapan pembaruan otomatis berikutnya
                            terjadi. Anda juga bisa klik <strong>Refresh</strong> kapan saja untuk memperbarui
                            secara langsung.
                        </span>
                    </div>

                </section>

                <section class="space-y-4">

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-show="lang==='en'">2.2 Favourite Shortcuts</span>
                        <span x-show="lang==='id'">2.2 Shortcut Favorit</span>
                    </h3>

                    <p class="text-gray-600 dark:text-gray-400">
                        <span x-show="lang==='en'">
                            Below the stat tiles is a row of shortcut icons to the menus you use most.
                            Star a menu from the sidebar to pin it here, and drag the icons to reorder them.
                            Only menus you already have access to can appear as a favourite.
                        </span>
                        <span x-show="lang==='id'">
                            Di bawah kartu statistik terdapat baris ikon shortcut menuju menu yang paling
                            sering Anda gunakan. Beri tanda bintang pada menu dari sidebar untuk
                            menyematkannya di sini, dan geser ikon untuk mengatur ulang urutannya. Hanya
                            menu yang sudah bisa Anda akses yang dapat dijadikan favorit.
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
                    <span x-show="lang==='en'">3. Waiting Approval & Approval History</span>
                    <span x-show="lang==='id'">3. Waiting Approval & Riwayat Approval</span>
                </span>

                <span x-text="openSection==='s3' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The main panel has two tabs: <strong>Waiting Approval</strong> (documents still
                        pending your action) and <strong>Approval History</strong> (documents you have
                        already acted on). Use the controls above the list to narrow down what you see:
                    </span>
                    <span x-show="lang==='id'">
                        Panel utama memiliki dua tab: <strong>Waiting Approval</strong> (dokumen yang masih
                        menunggu tindakan Anda) dan <strong>Approval History</strong> (dokumen yang sudah
                        Anda proses). Gunakan kontrol di atas daftar untuk mempersempit tampilan:
                    </span>
                </p>

                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Doctype</span>
                            <span x-show="lang==='id'">Doctype</span>
                        </strong> —
                        <span x-show="lang==='en'">Filter the list to a specific document type, or leave it on "All Doctype".</span>
                        <span x-show="lang==='id'">Menyaring daftar ke jenis dokumen tertentu, atau biarkan pada "All Doctype".</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Search document</span>
                            <span x-show="lang==='id'">Search document</span>
                        </strong> —
                        <span x-show="lang==='en'">Find a document by its number or description.</span>
                        <span x-show="lang==='id'">Mencari dokumen berdasarkan nomor atau deskripsinya.</span>
                    </li>
                    <li>
                        <strong>
                            <span x-show="lang==='en'">Show Entries</span>
                            <span x-show="lang==='id'">Show Entries</span>
                        </strong> —
                        <span x-show="lang==='en'">How many rows are shown per page (10/25/50/100).</span>
                        <span x-show="lang==='id'">Jumlah baris yang ditampilkan per halaman (10/25/50/100).</span>
                    </li>
                </ul>

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        Click <strong>Filter</strong> to apply the selections above, or
                        <strong>Open All</strong> to open every currently-listed waiting document at once
                        so you can review them back to back.
                    </span>
                    <span x-show="lang==='id'">
                        Klik <strong>Filter</strong> untuk menerapkan pilihan di atas, atau
                        <strong>Open All</strong> untuk membuka seluruh dokumen waiting yang sedang
                        ditampilkan sekaligus, sehingga Anda bisa meninjaunya satu per satu secara berurutan.
                    </span>
                </p>

                <div class="manual-note manual-warning">
                    <span x-show="lang==='en'">
                        <strong>Open All</strong> opens one browser tab per document in the current list —
                        with a large result set and a page size of 100, this can open many tabs at once.
                        Narrow the list with the Doctype filter or a smaller page size first if you only
                        need a subset.
                    </span>
                    <span x-show="lang==='id'">
                        <strong>Open All</strong> membuka satu tab browser untuk setiap dokumen pada daftar
                        yang sedang tampil — jika hasilnya banyak dan page size diatur ke 100, ini bisa
                        membuka banyak tab sekaligus. Persempit daftar dengan filter Doctype atau page size
                        yang lebih kecil terlebih dahulu jika Anda hanya butuh sebagian.
                    </span>
                </div>

            </div>
        </div>

    </section>

    <!-- ================= SECTION 4 ================= -->
    <section class="space-y-6">

        <div class="rounded-xl border border-gray-200 dark:border-gray-700">

            <button @click="toggle('s4')"
                class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                <span>
                    <span x-show="lang==='en'">4. Update Notifications</span>
                    <span x-show="lang==='id'">4. Update Notification</span>
                </span>

                <span x-text="openSection==='s4' ? '−' : '+'"></span>
            </button>

            <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">
                        The bell icon next to Refresh opens the <strong>Update Notification</strong> panel,
                        which lists system announcements such as new features and scheduled maintenance.
                        A red badge on the bell shows how many updates you have not read yet, and a toast
                        pop-up may appear in the corner of the screen when a new update is published while
                        you are using the app.
                    </span>
                    <span x-show="lang==='id'">
                        Ikon lonceng di sebelah Refresh membuka panel <strong>Update Notification</strong>,
                        yang menampilkan pengumuman sistem seperti fitur baru dan jadwal maintenance.
                        Lencana merah pada ikon lonceng menunjukkan jumlah update yang belum Anda baca, dan
                        sebuah toast pop-up dapat muncul di sudut layar saat ada update baru yang
                        dipublikasikan ketika Anda sedang menggunakan aplikasi.
                    </span>
                </p>

            </div>
        </div>

    </section>

</div>
