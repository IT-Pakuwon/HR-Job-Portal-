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
                                <td class="px-4 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">Hold</span></td>
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

</div>
