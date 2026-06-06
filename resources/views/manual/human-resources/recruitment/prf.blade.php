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
            <span x-show="lang==='en'">PRF (Personnel Requisition Form) is used to formally request a new position or replacement in your department. Once a PRF is approved, HR can create a job posting and begin the recruitment process for that position.</span>
            <span x-show="lang==='id'">PRF (Personnel Requisition Form) digunakan untuk mengajukan permintaan posisi baru atau penggantian karyawan di departemen Anda secara resmi. Setelah PRF disetujui, HR dapat membuat lowongan kerja dan memulai proses rekrutmen untuk posisi tersebut.</span>
        </p>
    </div>

    {{-- Section 1: Dashboard --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s1')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">1. PRF Dashboard</span><span x-show="lang==='id'">1. Dashboard PRF</span></span>
                <span x-text="openSection==='s1'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s1'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">The PRF list page shows all requisition forms with their current status. Status cards at the top allow quick filtering:</span>
                    <span x-show="lang==='id'">Halaman daftar PRF menampilkan semua formulir permintaan beserta statusnya. Kartu status di bagian atas memudahkan pemfilteran cepat:</span>
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
                                <td class="px-4 py-2"><span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">On Progress</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">PRF has been submitted and is awaiting approval</span><span x-show="lang==='id'">PRF telah diajukan dan menunggu persetujuan</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">Revise</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">Approver requested changes — needs to be updated and resubmitted</span><span x-show="lang==='id'">Approver meminta perubahan — perlu diperbarui dan diajukan kembali</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Reject</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">PRF was rejected by the approver</span><span x-show="lang==='id'">PRF ditolak oleh approver</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2"><span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Completed</span></td>
                                <td class="px-4 py-2"><span x-show="lang==='en'">PRF is approved — HR can create a job posting from this PRF</span><span x-show="lang==='id'">PRF disetujui — HR dapat membuat lowongan kerja dari PRF ini</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: Create PRF --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s2')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">2. Creating a PRF</span><span x-show="lang==='id'">2. Membuat PRF</span></span>
                <span x-text="openSection==='s2'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s2'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Click <strong>+ New PRF</strong> to open the creation form. Fill in the following details:</span>
                    <span x-show="lang==='id'">Klik <strong>+ New PRF</strong> untuk membuka formulir pembuatan. Isi detail berikut:</span>
                </p>
                <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                    <li><strong>Company</strong> — <span x-show="lang==='en'">Select the company entity for this requisition</span><span x-show="lang==='id'">Pilih entitas perusahaan untuk permintaan ini</span></li>
                    <li><strong>Department</strong> — <span x-show="lang==='en'">The department that needs the new position</span><span x-show="lang==='id'">Departemen yang membutuhkan posisi baru</span></li>
                    <li><strong>Division</strong> — <span x-show="lang==='en'">Organizational division under the department</span><span x-show="lang==='id'">Divisi organisasi di bawah departemen</span></li>
                    <li><strong>Location</strong> — <span x-show="lang==='en'">Work location or site for the position</span><span x-show="lang==='id'">Lokasi kerja atau site untuk posisi ini</span></li>
                    <li><strong>Job Title</strong> — <span x-show="lang==='en'">The position title being requested</span><span x-show="lang==='id'">Jabatan yang diminta</span></li>
                    <li><strong>Job Level</strong> — <span x-show="lang==='en'">Seniority level of the position</span><span x-show="lang==='id'">Tingkat senioritas posisi</span></li>
                    <li><strong>Immediate Superior</strong> — <span x-show="lang==='en'">The direct reporting manager for this position</span><span x-show="lang==='id'">Atasan langsung untuk posisi ini</span></li>
                    <li><strong>Job Type</strong> — <span x-show="lang==='en'">Employment type (permanent, contract, etc.)</span><span x-show="lang==='id'">Jenis pekerjaan (tetap, kontrak, dll.)</span></li>
                    <li><strong>Reason for Vacancy</strong> — <span x-show="lang==='en'">Why this position is needed (new position, replacement, expansion)</span><span x-show="lang==='id'">Alasan posisi ini dibutuhkan (posisi baru, penggantian, ekspansi)</span></li>
                    <li><strong>Required Count</strong> — <span x-show="lang==='en'">Number of people needed for this position</span><span x-show="lang==='id'">Jumlah orang yang dibutuhkan untuk posisi ini</span></li>
                    <li><strong>Education Requirements</strong> — <span x-show="lang==='en'">Minimum education level required</span><span x-show="lang==='id'">Tingkat pendidikan minimum yang diperlukan</span></li>
                    <li><strong>Experience</strong> — <span x-show="lang==='en'">Years of work experience required</span><span x-show="lang==='id'">Tahun pengalaman kerja yang diperlukan</span></li>
                </ul>
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">After filling in all fields, click <strong>Submit</strong> to send the PRF for approval. The PRF status will change to <em>On Progress</em>.</span>
                    <span x-show="lang==='id'">Setelah mengisi semua field, klik <strong>Submit</strong> untuk mengirim PRF untuk disetujui. Status PRF akan berubah menjadi <em>On Progress</em>.</span>
                </p>
            </div>
        </div>
    </section>

    {{-- Section 3: Approval Flow --}}
    <section class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700">
            <button @click="toggle('s3')" class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">
                <span><span x-show="lang==='en'">3. Approval Flow</span><span x-show="lang==='id'">3. Alur Persetujuan</span></span>
                <span x-text="openSection==='s3'?'−':'+'"></span>
            </button>
            <div x-show="openSection==='s3'" x-transition class="space-y-4 px-6 pb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    <span x-show="lang==='en'">Once submitted, the PRF goes through an approval process. The possible outcomes are:</span>
                    <span x-show="lang==='id'">Setelah diajukan, PRF akan melalui proses persetujuan. Kemungkinan hasilnya adalah:</span>
                </p>
                <ul class="list-disc space-y-3 pl-6 text-gray-600 dark:text-gray-400">
                    <li>
                        <span x-show="lang==='en'"><strong>Approved (Completed)</strong> — The PRF is accepted. HR will then create a job posting based on the approved PRF and begin recruiting candidates.</span>
                        <span x-show="lang==='id'"><strong>Approved (Completed)</strong> — PRF diterima. HR kemudian akan membuat lowongan kerja berdasarkan PRF yang disetujui dan mulai merekrut kandidat.</span>
                    </li>
                    <li>
                        <span x-show="lang==='en'"><strong>Revise</strong> — The approver has requested changes. Open the PRF, make the required updates, and resubmit.</span>
                        <span x-show="lang==='id'"><strong>Revise</strong> — Approver meminta perubahan. Buka PRF, lakukan pembaruan yang diperlukan, dan ajukan kembali.</span>
                    </li>
                    <li>
                        <span x-show="lang==='en'"><strong>Rejected</strong> — The PRF has been declined. Open the PRF detail to see the reason for rejection.</span>
                        <span x-show="lang==='id'"><strong>Rejected</strong> — PRF ditolak. Buka detail PRF untuk melihat alasan penolakan.</span>
                    </li>
                </ul>
                <div class="manual-note manual-info mt-4">
                    <span x-show="lang==='en'">Once a PRF reaches <strong>Completed</strong> status and HR creates the job posting, the PRF will show a <strong>Job Posted</strong> indicator to let you know that recruitment is active for that position.</span>
                    <span x-show="lang==='id'">Setelah PRF mencapai status <strong>Completed</strong> dan HR membuat lowongan kerja, PRF akan menampilkan indikator <strong>Job Posted</strong> untuk memberi tahu Anda bahwa rekrutmen aktif untuk posisi tersebut.</span>
                </div>
            </div>
        </div>
    </section>

</div>
