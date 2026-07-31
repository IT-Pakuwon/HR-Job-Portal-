<div x-data="{
    lang: localStorage.getItem('manual_lang')||'id',
    openSection: 's1',
    setLang(v){this.lang=v;localStorage.setItem('manual_lang',v);},
    toggle(section){this.openSection=this.openSection===section?null:section;}
}" class="max-w-9xl mx-auto space-y-6 p-6">

    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800">
            <button @click="setLang('id')" :class="lang==='id'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">ID</button>
            <button @click="setLang('en')" :class="lang==='en'?'bg-gray-900 text-white':'text-gray-600 dark:text-gray-300'" class="rounded-md px-4 py-1.5 text-sm font-medium transition">EN</button>
        </div>
    </div>

    <div class="rounded-xl border border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300"><span x-show="lang==='en'">Information</span><span x-show="lang==='id'">Informasi</span></h3>
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">All data shown in this manual are dummy data used for illustration purposes only.</span>
            <span x-show="lang==='id'">Seluruh data yang ditampilkan dalam manual ini merupakan data dummy yang digunakan hanya sebagai contoh.</span>
        </p>
    </div>

    <div class="manual-note manual-info">
        <strong><span x-show="lang==='en'">Overview</span><span x-show="lang==='id'">Gambaran Umum</span></strong>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
            <span x-show="lang==='en'">Self Register Applicant is a list of external candidates who have registered themselves through the public job portal — without being directly invited by HR. HR reviews these self-registered candidates and can assign them to an open job posting when there is a suitable match.</span>
            <span x-show="lang==='id'">Self Register Applicant adalah daftar kandidat eksternal yang mendaftar sendiri melalui portal lowongan kerja publik — tanpa diundang langsung oleh HR. HR meninjau kandidat yang mendaftar sendiri ini dan dapat menugaskan mereka ke lowongan kerja yang terbuka jika ada kesesuaian yang sesuai.</span>
        </p>
    </div>

    {{-- Section 1: Viewing the List --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. Viewing the Self Register List</span><span x-show="lang==='id'">1. Melihat Daftar Self Register</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The list shows all self-registered applicants with their current status:</span>
                    <span x-show="lang==='id'">Daftar menampilkan semua pelamar yang mendaftar sendiri beserta status mereka saat ini:</span>
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-2"><span x-show="lang==='en'">Status</span><span x-show="lang==='id'">Status</span></th>
                                <th class="px-4 py-2"><span x-show="lang==='en'">Meaning</span><span x-show="lang==='id'">Arti</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-900 dark:text-gray-300">Hold</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Self-registered but not yet assigned to any job posting</span><span x-show="lang==='id'">Telah mendaftar sendiri tetapi belum ditugaskan ke lowongan kerja mana pun</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">In Process</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Assigned to a job posting and currently going through recruitment steps</span><span x-show="lang==='id'">Ditugaskan ke lowongan kerja dan sedang melewati tahap rekrutmen</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Rejected</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Applicant was not selected</span><span x-show="lang==='id'">Pelamar tidak terpilih</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Completed</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Applicant accepted the offer and joined the company</span><span x-show="lang==='id'">Pelamar menerima tawaran dan bergabung dengan perusahaan</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Each row shows: Registration Date, Full Name, Education, Last Working Company, and Status. Click on an applicant to open their profile.</span>
                    <span x-show="lang==='id'">Setiap baris menampilkan: Tanggal Pendaftaran, Nama Lengkap, Pendidikan, Perusahaan Terakhir, dan Status. Klik pelamar untuk membuka profilnya.</span>
                </p>
            </div>
        </div>
    </section>

    {{-- Section 2: Mapping to a Job Posting --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Assigning to a Job Posting</span><span x-show="lang==='id'">2. Menugaskan ke Lowongan Kerja</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">A self-registered applicant with <strong>Hold</strong> status can be assigned (mapped) to an open job posting when HR finds a suitable position for them. To assign:</span>
                    <span x-show="lang==='id'">Pelamar yang mendaftar sendiri dengan status <strong>Hold</strong> dapat ditugaskan (dipetakan) ke lowongan kerja yang terbuka jika HR menemukan posisi yang sesuai. Untuk menugaskan:</span>
                </p>
                <ol class="list-decimal space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><span x-show="lang==='en'">Open the applicant profile from the Self Register list.</span><span x-show="lang==='id'">Buka profil pelamar dari daftar Self Register.</span></li>
                    <li><span x-show="lang==='en'">Review the applicant's background, education, and uploaded CV.</span><span x-show="lang==='id'">Tinjau latar belakang, pendidikan, dan CV yang diunggah pelamar.</span></li>
                    <li><span x-show="lang==='en'">Click <strong>Assign to Job</strong> (or the mapping button) to open the assignment form.</span><span x-show="lang==='id'">Klik <strong>Assign to Job</strong> (atau tombol mapping) untuk membuka formulir penugasan.</span></li>
                    <li><span x-show="lang==='en'">Select the appropriate open job posting from the dropdown.</span><span x-show="lang==='id'">Pilih lowongan kerja yang terbuka dari dropdown.</span></li>
                    <li><span x-show="lang==='en'">Save — the applicant will appear in the Applicant Portal for that job and begin the recruitment steps.</span><span x-show="lang==='id'">Simpan — pelamar akan muncul di Applicant Portal untuk lowongan tersebut dan mulai menjalani tahap rekrutmen.</span></li>
                </ol>
                <div class="manual-note manual-warning mt-2">
                    <span x-show="lang==='en'">Once an applicant is assigned to a job, their status changes to <strong>In Process</strong> and they follow the same recruitment flow as regular applicants in the Applicant Portal.</span>
                    <span x-show="lang==='id'">Setelah pelamar ditugaskan ke suatu pekerjaan, statusnya berubah menjadi <strong>In Process</strong> dan mereka mengikuti alur rekrutmen yang sama seperti pelamar reguler di Applicant Portal.</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3: Cancel Assignment --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s3')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">3. Cancelling an Assignment</span><span x-show="lang==='id'">3. Membatalkan Penugasan</span></span>
                <span x-text="openSection==='s3'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s3'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">If an applicant was assigned to a job posting by mistake or is no longer suitable, HR can cancel the assignment. Open the applicant profile and click <strong>Cancel Assignment</strong>. The applicant's status will revert to <strong>Hold</strong> and they can be reassigned to a different job if needed.</span>
                    <span x-show="lang==='id'">Jika pelamar ditugaskan ke lowongan kerja secara tidak sengaja atau sudah tidak sesuai, HR dapat membatalkan penugasan tersebut. Buka profil pelamar dan klik <strong>Cancel Assignment</strong>. Status pelamar akan kembali ke <strong>Hold</strong> dan dapat ditugaskan ulang ke pekerjaan lain jika diperlukan.</span>
                </p>
            </div>
        </div>
    </section>

    {{-- Section 4: Tagging --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s4')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">4. Tagging an Applicant</span><span x-show="lang==='id'">4. Menandai (Tag) Pelamar</span></span>
                <span x-text="openSection==='s4'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s4'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The <strong>Tag</strong> feature lets HR label self-registered applicants for easier filtering and future reference — without having to assign them to a job posting immediately. Tags help organize a large pool of candidates by skill category, interest area, or priority level.</span>
                    <span x-show="lang==='id'">Fitur <strong>Tag</strong> memungkinkan HR memberi label pada pelamar yang mendaftar sendiri untuk memudahkan penyaringan dan referensi di masa mendatang — tanpa harus langsung menugaskan mereka ke lowongan kerja. Tag membantu mengorganisasi banyak kandidat berdasarkan kategori keahlian, bidang minat, atau tingkat prioritas.</span>
                </p>
                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span x-show="lang==='en'">How to Tag an Applicant:</span>
                        <span x-show="lang==='id'">Cara Memberi Tag pada Pelamar:</span>
                    </p>
                    <ol class="list-decimal space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li><span x-show="lang==='en'">Open the applicant profile from the Self Register list.</span><span x-show="lang==='id'">Buka profil pelamar dari daftar Self Register.</span></li>
                        <li><span x-show="lang==='en'">Click the <strong>Tag</strong> button on the profile page.</span><span x-show="lang==='id'">Klik tombol <strong>Tag</strong> di halaman profil.</span></li>
                        <li><span x-show="lang==='en'">Select one or more tags from the available list, or type to create a new tag.</span><span x-show="lang==='id'">Pilih satu atau lebih tag dari daftar yang tersedia, atau ketik untuk membuat tag baru.</span></li>
                        <li><span x-show="lang==='en'">Save — the selected tags will appear on the applicant card in the list.</span><span x-show="lang==='id'">Simpan — tag yang dipilih akan muncul pada kartu pelamar di daftar.</span></li>
                    </ol>
                </div>
                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">Tags can be added or removed at any time. Use the filter on the Self Register list page to quickly find applicants by their assigned tags.</span>
                    <span x-show="lang==='id'">Tag dapat ditambahkan atau dihapus kapan saja. Gunakan filter di halaman daftar Self Register untuk menemukan pelamar berdasarkan tag yang diberikan dengan cepat.</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 5: Remapping --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s5')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">5. Remapping to a Different Position</span><span x-show="lang==='id'">5. Remapping ke Posisi Berbeda</span></span>
                <span x-text="openSection==='s5'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s5'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">If a self-registered applicant is already <strong>In Process</strong> for a job but is found to be a better fit for a different open vacancy, HR can use <strong>Remap</strong> to move them to the new position without losing their profile data.</span>
                    <span x-show="lang==='id'">Jika pelamar yang mendaftar sendiri sudah dalam status <strong>In Process</strong> untuk suatu pekerjaan namun ternyata lebih cocok untuk lowongan yang berbeda, HR dapat menggunakan fitur <strong>Remap</strong> untuk memindahkan mereka ke posisi baru tanpa kehilangan data profil mereka.</span>
                </p>
                <div class="manual-note manual-warning">
                    <span x-show="lang==='en'">Remapping resets the recruitment progress to the first step (HR Check) under the new position. Previous step records are retained but will no longer be active.</span>
                    <span x-show="lang==='id'">Remapping akan mereset progres rekrutmen ke tahap pertama (HR Check) di bawah posisi baru. Catatan tahap sebelumnya tetap tersimpan namun tidak lagi aktif.</span>
                </div>
                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span x-show="lang==='en'">How to Remap:</span>
                        <span x-show="lang==='id'">Cara Melakukan Remap:</span>
                    </p>
                    <ol class="list-decimal space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li><span x-show="lang==='en'">Open the applicant profile (status must be <strong>In Process</strong>).</span><span x-show="lang==='id'">Buka profil pelamar (status harus <strong>In Process</strong>).</span></li>
                        <li><span x-show="lang==='en'">Click the <strong>Remap</strong> button on the profile page.</span><span x-show="lang==='id'">Klik tombol <strong>Remap</strong> di halaman profil.</span></li>
                        <li><span x-show="lang==='en'">Select the new target job vacancy from the dialog.</span><span x-show="lang==='id'">Pilih lowongan pekerjaan tujuan yang baru dari dialog yang muncul.</span></li>
                        <li><span x-show="lang==='en'">Confirm — the applicant moves to the new vacancy and restarts from the HR Check step.</span><span x-show="lang==='id'">Konfirmasi — pelamar dipindahkan ke lowongan baru dan memulai ulang dari tahap HR Check.</span></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 6: Rejecting --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s6')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">6. Rejecting a Self-Registered Applicant</span><span x-show="lang==='id'">6. Menolak Pelamar Self Register</span></span>
                <span x-text="openSection==='s6'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s6'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">If a self-registered applicant does not meet the minimum requirements and HR decides not to proceed with them, the applicant can be <strong>Rejected</strong>. This closes their profile without assigning them to any job posting.</span>
                    <span x-show="lang==='id'">Jika pelamar yang mendaftar sendiri tidak memenuhi persyaratan minimum dan HR memutuskan untuk tidak melanjutkan prosesnya, pelamar tersebut dapat <strong>Ditolak</strong>. Ini menutup profil mereka tanpa menugaskan mereka ke lowongan kerja mana pun.</span>
                </p>
                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span x-show="lang==='en'">How to Reject:</span>
                        <span x-show="lang==='id'">Cara Menolak Pelamar:</span>
                    </p>
                    <ol class="list-decimal space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                        <li><span x-show="lang==='en'">Open the applicant profile from the Self Register list.</span><span x-show="lang==='id'">Buka profil pelamar dari daftar Self Register.</span></li>
                        <li><span x-show="lang==='en'">Click the <strong>Reject</strong> button on the profile page.</span><span x-show="lang==='id'">Klik tombol <strong>Reject</strong> di halaman profil.</span></li>
                        <li><span x-show="lang==='en'">Optionally provide a rejection reason in the confirmation dialog.</span><span x-show="lang==='id'">Opsional: berikan alasan penolakan di dialog konfirmasi.</span></li>
                        <li><span x-show="lang==='en'">Confirm — the applicant status changes to <strong>Rejected</strong> and they will no longer appear in the active candidate pool.</span><span x-show="lang==='id'">Konfirmasi — status pelamar berubah menjadi <strong>Rejected</strong> dan mereka tidak akan lagi muncul di kumpulan kandidat aktif.</span></li>
                    </ol>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase">
                            <tr>
                                <th class="px-4 py-2"><span x-show="lang==='en'">Current Status</span><span x-show="lang==='id'">Status Saat Ini</span></th>
                                <th class="px-4 py-2"><span x-show="lang==='en'">Can be Rejected?</span><span x-show="lang==='id'">Dapat Ditolak?</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-900 dark:text-gray-300">Hold</span></td>
                                <td class="px-4 py-2 text-green-600 font-semibold"><span x-show="lang==='en'">Yes</span><span x-show="lang==='id'">Ya</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">In Process</span></td>
                                <td class="px-4 py-2 text-green-600 font-semibold"><span x-show="lang==='en'">Yes</span><span x-show="lang==='id'">Ya</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Completed</span></td>
                                <td class="px-4 py-2 text-red-600 font-semibold"><span x-show="lang==='en'">No</span><span x-show="lang==='id'">Tidak</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="manual-note manual-info">
                    <span x-show="lang==='en'">Rejected applicants are still visible in the list under the <strong>Rejected</strong> status filter. Their profile data is preserved for record-keeping.</span>
                    <span x-show="lang==='id'">Pelamar yang ditolak masih terlihat di daftar di bawah filter status <strong>Rejected</strong>. Data profil mereka tetap disimpan untuk keperluan pencatatan.</span>
                </div>
            </div>
        </div>
    </section>

</div>
