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
            <span x-show="lang==='en'">The Applicant Portal is where HR and hiring managers track all candidates who have applied to open job postings. Each applicant is guided through a series of recruitment steps — from initial HR screening through interviews, psycho test, and finally a job offer.</span>
            <span x-show="lang==='id'">Applicant Portal adalah tempat HR dan hiring manager melacak semua kandidat yang telah melamar ke lowongan kerja yang terbuka. Setiap pelamar dipandu melalui serangkaian tahap rekrutmen — mulai dari screening awal HR, wawancara, tes psikologi, hingga penawaran kerja.</span>
        </p>
    </div>

    {{-- Section 1: Applicant List --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. Viewing the Applicant List</span><span x-show="lang==='id'">1. Melihat Daftar Pelamar</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The portal lists all applicants grouped by their current status. Use the status cards to filter:</span>
                    <span x-show="lang==='id'">Portal menampilkan daftar semua pelamar yang dikelompokkan berdasarkan status mereka saat ini. Gunakan kartu status untuk memfilter:</span>
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
                                <td class="px-4 py-2"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">Unchecked</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">New application — not yet reviewed by HR</span><span x-show="lang==='id'">Lamaran baru — belum ditinjau oleh HR</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Checked</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Application reviewed and currently being processed through recruitment steps</span><span x-show="lang==='id'">Lamaran ditinjau dan sedang diproses melalui tahap rekrutmen</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Rejected</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Applicant was not selected during the recruitment process</span><span x-show="lang==='id'">Pelamar tidak terpilih selama proses rekrutmen</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Completed</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Applicant accepted the offer and joined the company</span><span x-show="lang==='id'">Pelamar menerima tawaran dan bergabung dengan perusahaan</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Each applicant row shows: Application Date, Full Name, Education, Last Working Company, Match Score, Current Step, and Status. Click on an applicant to open their full profile.</span>
                    <span x-show="lang==='id'">Setiap baris pelamar menampilkan: Tanggal Lamaran, Nama Lengkap, Pendidikan, Perusahaan Terakhir, Skor Kesesuaian, Tahap Saat Ini, dan Status. Klik pelamar untuk membuka profil lengkapnya.</span>
                </p>
            </div>
        </div>
    </section>

    {{-- Section 2: Recruitment Steps --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Recruitment Steps</span><span x-show="lang==='id'">2. Tahapan Rekrutmen</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Each applicant goes through the following recruitment stages in sequence:</span>
                    <span x-show="lang==='id'">Setiap pelamar melewati tahapan rekrutmen berikut secara berurutan:</span>
                </p>
                <ol class="space-y-3 pl-2 text-gray-600 dark:text-gray-400">
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">1</span>
                        <div>
                            <strong><span x-show="lang==='en'">HR Check</span><span x-show="lang==='id'">Pengecekan HR</span></strong>
                            <p class="text-sm"><span x-show="lang==='en'">HR reviews the application to determine if the candidate meets the basic requirements.</span><span x-show="lang==='id'">HR meninjau lamaran untuk menentukan apakah kandidat memenuhi persyaratan dasar.</span></p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">2</span>
                        <div>
                            <strong><span x-show="lang==='en'">User/Manager Review</span><span x-show="lang==='id'">Tinjauan User/Manager</span></strong>
                            <p class="text-sm"><span x-show="lang==='en'">The hiring manager reviews the shortlisted candidates from HR.</span><span x-show="lang==='id'">Hiring manager meninjau kandidat yang diseleksi oleh HR.</span></p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">3</span>
                        <div>
                            <strong><span x-show="lang==='en'">HR Interview</span><span x-show="lang==='id'">Wawancara HR</span></strong>
                            <p class="text-sm"><span x-show="lang==='en'">Schedule and conduct the interview with HR. Assessment scores are recorded after the interview.</span><span x-show="lang==='id'">Jadwalkan dan lakukan wawancara bersama HR. Skor penilaian dicatat setelah wawancara.</span></p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">4</span>
                        <div>
                            <strong><span x-show="lang==='en'">User Interview</span><span x-show="lang==='id'">Wawancara User</span></strong>
                            <p class="text-sm"><span x-show="lang==='en'">Schedule and conduct the interview with the hiring manager or department head.</span><span x-show="lang==='id'">Jadwalkan dan lakukan wawancara bersama hiring manager atau kepala departemen.</span></p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">5</span>
                        <div>
                            <strong><span x-show="lang==='en'">Psycho Test</span><span x-show="lang==='id'">Tes Psikologi</span></strong>
                            <p class="text-sm"><span x-show="lang==='en'">Schedule the psychological test and record the results once completed.</span><span x-show="lang==='id'">Jadwalkan tes psikologi dan catat hasilnya setelah selesai.</span></p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">6</span>
                        <div>
                            <strong><span x-show="lang==='en'">Offering</span><span x-show="lang==='id'">Penawaran</span></strong>
                            <p class="text-sm"><span x-show="lang==='en'">Generate and send the job offer letter to the selected candidate. Once accepted, the applicant status moves to Completed.</span><span x-show="lang==='id'">Buat dan kirimkan surat penawaran kerja kepada kandidat yang dipilih. Setelah diterima, status pelamar berubah menjadi Completed.</span></p>
                        </div>
                    </li>
                </ol>
                <div class="manual-note manual-info mt-2">
                    <span x-show="lang==='en'">At any step, an applicant can be <strong>rejected</strong> if they do not meet the requirements. Open the applicant profile and use the available action to update the status.</span>
                    <span x-show="lang==='id'">Di setiap tahap, pelamar dapat <strong>ditolak</strong> jika tidak memenuhi persyaratan. Buka profil pelamar dan gunakan tindakan yang tersedia untuk memperbarui statusnya.</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3: Applicant Profile --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s3')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">3. Applicant Profile</span><span x-show="lang==='id'">3. Profil Pelamar</span></span>
                <span x-text="openSection==='s3'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s3'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Clicking on an applicant opens their full profile, which includes:</span>
                    <span x-show="lang==='id'">Mengklik pelamar akan membuka profil lengkap mereka, yang mencakup:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><span x-show="lang==='en'">Personal information (name, photo, contact details, marital status)</span><span x-show="lang==='id'">Informasi pribadi (nama, foto, kontak, status pernikahan)</span></li>
                    <li><span x-show="lang==='en'">Education history</span><span x-show="lang==='id'">Riwayat pendidikan</span></li>
                    <li><span x-show="lang==='en'">Work experience</span><span x-show="lang==='id'">Pengalaman kerja</span></li>
                    <li><span x-show="lang==='en'">Skills and languages</span><span x-show="lang==='id'">Keahlian dan kemampuan bahasa</span></li>
                    <li><span x-show="lang==='en'">Attachments (CV, cover letter — downloadable)</span><span x-show="lang==='id'">Lampiran (CV, surat lamaran — dapat diunduh)</span></li>
                    <li><span x-show="lang==='en'">Current recruitment step and progress tracker</span><span x-show="lang==='id'">Tahap rekrutmen saat ini dan pelacak progres</span></li>
                    <li><span x-show="lang==='en'">Assessment scores from each interview stage</span><span x-show="lang==='id'">Skor penilaian dari setiap tahap wawancara</span></li>
                </ul>
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">From the profile page, you can take actions for the current recruitment step (schedule interview, record results, generate offer letter).</span>
                    <span x-show="lang==='id'">Dari halaman profil, Anda dapat mengambil tindakan untuk tahap rekrutmen saat ini (jadwalkan wawancara, catat hasil, buat surat penawaran).</span>
                </p>
            </div>
        </div>
    </section>

</div>
