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
                             Voucher Taxi is the formal process for requesting a taxi voucher for business travel.
                             You submit a booking request specifying where you're going and why, it goes through
                             department approval, and once approved the General Affair (GA) team processes the
                             actual transportation cost. A printable voucher (PDF) is available once the request
                             is approved.
                         </span>
                         <span x-show="lang==='id'">
                             Voucher Taxi adalah proses resmi untuk mengajukan voucher taksi untuk keperluan
                             perjalanan dinas. Anda mengajukan permintaan booking dengan menyebutkan tujuan dan
                             alasannya, permintaan tersebut melewati approval departemen, dan setelah disetujui
                             tim General Affair (GA) memproses biaya transportasi sebenarnya. Voucher dalam
                             format PDF yang bisa dicetak tersedia setelah permintaan disetujui.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Use Voucher Taxi when you need taxi transportation for a business trip and the
                             cost needs to be charged to the company. This is not for personal car/driver
                             bookings — for that, use Booking Car instead.
                         </span>
                         <span x-show="lang==='id'">
                             Gunakan Voucher Taxi ketika Anda membutuhkan transportasi taksi untuk perjalanan
                             dinas dan biayanya perlu dibebankan ke perusahaan. Fitur ini bukan untuk
                             pemesanan mobil/sopir pribadi — untuk itu gunakan Booking Car.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 End-to-End Flow</span>
                             <span x-show="lang==='id'">1.1 Alur Proses Lengkap</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The page is split into a calendar view (showing scheduled trips) and a
                                 request queue list. The overall flow is as follows.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini terbagi menjadi tampilan kalender (menampilkan jadwal perjalanan)
                                 dan daftar antrean permintaan. Alur prosesnya adalah sebagai berikut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <span x-show="lang==='en'">Employee submits a Voucher Taxi request (<strong>New Booking</strong>).</span>
                                 <span x-show="lang==='id'">Karyawan mengajukan permintaan Voucher Taxi (<strong>New Booking</strong>).</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">The request enters the department approval chain.</span>
                                 <span x-show="lang==='id'">Permintaan masuk ke rantai approval departemen.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">Once fully approved, the request becomes <strong>Completed</strong> and is ready for GA to process.</span>
                                 <span x-show="lang==='id'">Setelah disetujui sepenuhnya, status permintaan menjadi <strong>Completed</strong> dan siap diproses GA.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">GA records the actual transportation cost (<strong>Process</strong>), closing the financial loop.</span>
                                 <span x-show="lang==='id'">GA mencatat biaya transportasi aktual (<strong>Process</strong>), menutup proses finansialnya.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">At any point after approval, the voucher can be printed/downloaded as a PDF.</span>
                                 <span x-show="lang==='id'">Setelah disetujui, voucher dapat dicetak/diunduh dalam format PDF kapan saja.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Status Overview</span>
                             <span x-show="lang==='id'">1.2 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Filter buttons above the request list let you quickly narrow the queue
                                 by status.
                             </span>
                             <span x-show="lang==='id'">
                                 Tombol filter di atas daftar permintaan memudahkan Anda menyaring antrean
                                 berdasarkan status.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Waiting Approval</strong> —
                                 <span x-show="lang==='en'">Request has been submitted and is currently going through the approval chain.</span>
                                 <span x-show="lang==='id'">Permintaan sudah disubmit dan sedang melewati rantai approval.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">An approver sent the request back for correction. The requester can edit and re-submit.</span>
                                 <span x-show="lang==='id'">Approver mengembalikan permintaan untuk diperbaiki. Pemohon dapat mengedit dan submit ulang.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> —
                                 <span x-show="lang==='en'">The request was rejected by an approver, with a reason recorded.</span>
                                 <span x-show="lang==='id'">Permintaan ditolak oleh approver, dengan alasan yang tercatat.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">All approval levels have approved. The voucher is valid and ready for GA processing.</span>
                                 <span x-show="lang==='id'">Semua level approval sudah menyetujui. Voucher sah dan siap diproses GA.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Waiting Process</span>
                                     <span x-show="lang==='id'">Menunggu Proses</span>
                                 </strong> —
                                 <span x-show="lang==='en'">(GA only) Completed vouchers that still need actual cost to be recorded.</span>
                                 <span x-show="lang==='id'">(Khusus GA) Voucher berstatus Completed yang masih perlu dicatat biaya aktualnya.</span>
                             </li>
                             <li>
                                 <strong>Cancelled</strong> —
                                 <span x-show="lang==='en'">The request was cancelled by its creator before approval was completed.</span>
                                 <span x-show="lang==='id'">Permintaan dibatalkan oleh pembuatnya sebelum approval selesai.</span>
                             </li>
                         </ul>

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
                         <span x-show="lang==='en'">2. Submitting a Voucher Taxi Request</span>
                         <span x-show="lang==='id'">2. Mengajukan Permintaan Voucher Taxi</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>New Booking</strong> to open the request form. The form is grouped
                             into three sections: Basic Information, Trip Information, and Finance Information.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>New Booking</strong> untuk membuka form permintaan. Form terbagi
                             menjadi tiga bagian: Basic Information, Trip Information, dan Finance Information.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Basic Information</span>
                             <span x-show="lang==='id'">2.1 Informasi Dasar</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Company</strong> /
                                 <strong>Department</strong> —
                                 <span x-show="lang==='en'">Auto-filled when you only belong to one company/department. If you belong to more than one, select the correct one for this request.</span>
                                 <span x-show="lang==='id'">Terisi otomatis jika Anda hanya terdaftar di satu company/department. Jika lebih dari satu, pilih yang sesuai untuk permintaan ini.</span>
                             </li>
                             <li>
                                 <strong>Requester</strong> —
                                 <span x-show="lang==='en'">Always your own account name; this field cannot be changed.</span>
                                 <span x-show="lang==='id'">Selalu nama akun Anda sendiri; field ini tidak dapat diubah.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Date Used</span>
                                     <span x-show="lang==='id'">Tanggal Penggunaan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">The date the taxi will actually be used.</span>
                                 <span x-show="lang==='id'">Tanggal taksi akan benar-benar digunakan.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Trip Information</span>
                             <span x-show="lang==='id'">2.2 Informasi Perjalanan</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Trip Type</span>
                                     <span x-show="lang==='id'">Jenis Perjalanan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Choose <strong>Return</strong> (round trip) or <strong>One Way</strong>.</span>
                                 <span x-show="lang==='id'">Pilih <strong>Return</strong> (pulang-pergi) atau <strong>One Way</strong> (sekali jalan).</span>
                             </li>
                             <li>
                                 <strong>Origin</strong> /
                                 <strong>
                                     <span x-show="lang==='en'">Destination</span>
                                     <span x-show="lang==='id'">Tujuan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Free-text fields for where the trip starts and ends.</span>
                                 <span x-show="lang==='id'">Field teks bebas untuk titik awal dan akhir perjalanan.</span>
                             </li>
                             <li>
                                 <strong>Purpose</strong> —
                                 <span x-show="lang==='en'">Select a purpose category from the list (managed in the Voucher Taxi Setup, see Section 5).</span>
                                 <span x-show="lang==='id'">Pilih kategori tujuan dari daftar (dikelola di Voucher Taxi Setup, lihat Bagian 5).</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Purpose Description</span>
                                     <span x-show="lang==='id'">Deskripsi Tujuan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Free-text explanation of why the trip is needed.</span>
                                 <span x-show="lang==='id'">Penjelasan bebas mengenai alasan perjalanan tersebut diperlukan.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Finance Information</span>
                             <span x-show="lang==='id'">2.3 Informasi Keuangan</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Company Expense</span>
                                     <span x-show="lang==='id'">Beban Perusahaan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">The company that the cost should be charged to. Defaults to your own company, but can differ.</span>
                                 <span x-show="lang==='id'">Perusahaan yang akan menanggung biaya. Defaultnya adalah perusahaan Anda, namun dapat berbeda.</span>
                             </li>
                             <li>
                                 <strong>Topup</strong> —
                                 <span x-show="lang==='en'">The employee whose taxi balance/card will be topped up for this trip.</span>
                                 <span x-show="lang==='id'">Karyawan yang saldo/kartu taksinya akan di-topup untuk perjalanan ini.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 All fields marked with <strong>*</strong> are required. The request cannot be
                                 submitted until Company, Department, Date Used, Trip Type, Origin, Destination,
                                 Purpose, Purpose Description, Company Expense, and Topup are all filled in.
                             </span>
                             <span x-show="lang==='id'">
                                 Semua field dengan tanda <strong>*</strong> wajib diisi. Permintaan tidak dapat
                                 disubmit sebelum Company, Department, Date Used, Trip Type, Origin, Destination,
                                 Purpose, Purpose Description, Company Expense, dan Topup terisi semua.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.4 No Draft Mode</span>
                             <span x-show="lang==='id'">2.4 Tidak Ada Mode Draft</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Unlike some other modules, Voucher Taxi does not support saving a request as
                                 a draft. Clicking <strong>Submit Request</strong> immediately sends the request
                                 into the approval chain.
                             </span>
                             <span x-show="lang==='id'">
                                 Berbeda dengan beberapa modul lain, Voucher Taxi tidak mendukung penyimpanan
                                 sebagai draft. Mengklik <strong>Submit Request</strong> langsung mengirim
                                 permintaan ke rantai approval.
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
                         <span x-show="lang==='en'">3. Approval Flow & Tracking Status</span>
                         <span x-show="lang==='id'">3. Alur Approval & Memantau Status</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click any request row in the queue to open its detail view, which shows full
                             trip information, current status, and the approval flow timeline.
                         </span>
                         <span x-show="lang==='id'">
                             Klik baris permintaan mana pun di antrean untuk membuka tampilan detailnya,
                             yang menampilkan informasi perjalanan lengkap, status saat ini, dan timeline
                             alur approval.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Who Approves</span>
                             <span x-show="lang==='id'">3.1 Siapa yang Menyetujui</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Approval levels and approvers are determined by the company/department
                                 approval setup for the Voucher Taxi document type. A request may pass through
                                 one or more approval levels before reaching <strong>Completed</strong> status.
                             </span>
                             <span x-show="lang==='id'">
                                 Level approval dan approver ditentukan oleh pengaturan approval
                                 company/department untuk jenis dokumen Voucher Taxi. Sebuah permintaan dapat
                                 melewati satu atau lebih level approval sebelum mencapai status
                                 <strong>Completed</strong>.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 For Approvers: Reviewing a Request</span>
                             <span x-show="lang==='id'">3.2 Untuk Approver: Meninjau Permintaan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When a request reaches your approval level, open its detail view and review
                                 the trip information, then take one of the following actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Ketika permintaan mencapai level approval Anda, buka tampilan detailnya dan
                                 tinjau informasi perjalanannya, lalu ambil salah satu tindakan berikut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Approve</strong> —
                                 <span x-show="lang==='en'">The request is valid and you authorize it to move to the next level (or to Completed if you are the last approver).</span>
                                 <span x-show="lang==='id'">Permintaan valid dan Anda mengizinkannya lanjut ke level berikutnya (atau menjadi Completed jika Anda approver terakhir).</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">Something needs to be corrected. A reason is required; the request returns to the requester with status Revise.</span>
                                 <span x-show="lang==='id'">Ada yang perlu diperbaiki. Alasan wajib diisi; permintaan dikembalikan ke pemohon dengan status Revise.</span>
                             </li>
                             <li>
                                 <strong>Reject</strong> —
                                 <span x-show="lang==='en'">The request is not approved. A reason is required so the requester knows why.</span>
                                 <span x-show="lang==='id'">Permintaan tidak disetujui. Alasan wajib diisi agar pemohon mengetahui penyebabnya.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Handling a Revision</span>
                             <span x-show="lang==='id'">3.3 Menangani Revisi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If your request comes back with status <strong>Revise</strong>, open the
                                 detail view to read the approver's reason (shown in the Revision Reason box),
                                 click <strong>Edit Voucher</strong>, make the necessary changes, and click
                                 <strong>Save Changes</strong> to re-submit it into the approval chain.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika permintaan Anda dikembalikan dengan status <strong>Revise</strong>, buka
                                 tampilan detail untuk membaca alasan dari approver (ditampilkan pada kotak
                                 Revision Reason), klik <strong>Edit Voucher</strong>, lakukan perubahan yang
                                 diperlukan, lalu klik <strong>Save Changes</strong> untuk mengirim ulang ke
                                 rantai approval.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Only the original requester can edit or cancel a request, and only while it
                                 is in <strong>Revise</strong> status. From the detail view you can also click
                                 <strong>Cancel Request</strong> to withdraw it entirely.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya pemohon asli yang dapat mengedit atau membatalkan permintaan, dan hanya
                                 selama statusnya <strong>Revise</strong>. Dari tampilan detail Anda juga bisa
                                 klik <strong>Cancel Request</strong> untuk membatalkannya sepenuhnya.
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
                         <span x-show="lang==='en'">4. Printing the Voucher</span>
                         <span x-show="lang==='id'">4. Mencetak Voucher</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Once you open a request's detail view, click <strong>Print PDF</strong> to
                             generate a printable Voucher Taxi document. It opens in a new tab as a downloadable
                             PDF and includes the document number, trip details, company information, current
                             status, and the approval signatures/timeline.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah membuka tampilan detail sebuah permintaan, klik <strong>Print PDF</strong>
                             untuk membuat dokumen Voucher Taxi yang dapat dicetak. Dokumen akan terbuka di
                             tab baru sebagai PDF yang dapat diunduh, berisi nomor dokumen, detail perjalanan,
                             informasi perusahaan, status saat ini, dan tanda tangan/timeline approval.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             The PDF can be generated at any status, but it is normally used as proof of an
                             approved trip — print it once the voucher reaches <strong>Completed</strong> status
                             so it reflects the final approved details.
                         </span>
                         <span x-show="lang==='id'">
                             PDF dapat dibuat pada status apa pun, namun biasanya digunakan sebagai bukti
                             perjalanan yang telah disetujui — cetak setelah voucher mencapai status
                             <strong>Completed</strong> agar mencerminkan detail final yang sudah disetujui.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         @if(auth()->user()->hasRole('GAACCESS'))
         <!-- ================= SECTION 5 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">5. For GA Team: Processing & Setup</span>
                         <span x-show="lang==='id'">5. Untuk Tim GA: Memproses & Pengaturan</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Users with the GA Access role see every Voucher Taxi request that is part of
                             their approval chain, plus all requests within their own company/department.
                             They also get access to the <strong>Process</strong> action and the
                             <strong>Voucher Taxi Setup</strong> page.
                         </span>
                         <span x-show="lang==='id'">
                             Pengguna dengan role GA Access dapat melihat semua permintaan Voucher Taxi yang
                             merupakan bagian dari rantai approval mereka, ditambah seluruh permintaan dalam
                             company/department mereka sendiri. Mereka juga mendapat akses ke aksi
                             <strong>Process</strong> dan halaman <strong>Voucher Taxi Setup</strong>.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.1 Processing an Approved Voucher</span>
                             <span x-show="lang==='id'">5.1 Memproses Voucher yang Disetujui</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Filter the request queue by <strong>Waiting Process</strong> to find vouchers
                                 with status <strong>Completed</strong> that still need their actual cost
                                 recorded. Open the request and click <strong>Process</strong>.
                             </span>
                             <span x-show="lang==='id'">
                                 Filter antrean permintaan dengan <strong>Waiting Process</strong> untuk
                                 menemukan voucher berstatus <strong>Completed</strong> yang masih perlu
                                 dicatat biaya aktualnya. Buka permintaan dan klik <strong>Process</strong>.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Actual Budget</span>
                                     <span x-show="lang==='id'">Biaya Aktual</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Required. Enter the real amount paid for the taxi trip.</span>
                                 <span x-show="lang==='id'">Wajib diisi. Masukkan jumlah biaya taksi yang sebenarnya dibayarkan.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Update Expense Owner</span>
                                     <span x-show="lang==='id'">Update Pemilik Beban</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Optional checkbox. Enable it if the cost should be re-charged to a different company, department, or employee than originally requested, then fill in the new Company, Department, and Employee.</span>
                                 <span x-show="lang==='id'">Checkbox opsional. Aktifkan jika biaya perlu dibebankan ke company, department, atau karyawan yang berbeda dari permintaan awal, lalu isi Company, Department, dan Employee yang baru.</span>
                             </li>
                         </ul>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click <strong>Save Process</strong> to finalize. The voucher status changes to
                                 <strong>Processed</strong>, and the recorded actual cost is shown in the
                                 detail view under Actual Expense.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Save Process</strong> untuk menyelesaikan. Status voucher berubah
                                 menjadi <strong>Processed</strong>, dan biaya aktual yang tercatat ditampilkan
                                 di tampilan detail pada bagian Actual Expense.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.2 Voucher Taxi Setup (Purpose Categories)</span>
                             <span x-show="lang==='id'">5.2 Voucher Taxi Setup (Kategori Tujuan)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click <strong>Voucher Taxi Setup</strong> from the main page to manage the
                                 list of purpose categories that requesters choose from on the Purpose field.
                                 Click <strong>Add Category</strong> to create a new one, specifying Category
                                 ID, Category Name, and Group, then activate or deactivate existing categories
                                 as needed.
                             </span>
                             <span x-show="lang==='en'">
                                 Klik <strong>Voucher Taxi Setup</strong> dari halaman utama untuk mengelola
                                 daftar kategori tujuan yang dipilih pemohon pada field Purpose. Klik
                                 <strong>Add Category</strong> untuk membuat kategori baru dengan mengisi
                                 Category ID, Category Name, dan Group, lalu aktifkan atau nonaktifkan
                                 kategori yang sudah ada sesuai kebutuhan.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only categories with Active status will appear as selectable Purpose options
                                 on the request form. Deactivating a category does not affect existing
                                 requests that already used it.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya kategori berstatus Active yang akan muncul sebagai pilihan Purpose pada
                                 form permintaan. Menonaktifkan kategori tidak memengaruhi permintaan yang
                                 sudah ada dan sebelumnya menggunakan kategori tersebut.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

     </div>
