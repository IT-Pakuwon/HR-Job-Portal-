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
                             Booking Car is the module used to request an operational vehicle and driver
                             for a business trip. You submit a request with the purpose, schedule, and route,
                             it goes through department approval, General Affair (GA) assigns the driver and
                             vehicle (or hands the trip to a taxi/other arrangement), and the finished booking
                             can be printed as a travel document.
                         </span>
                         <span x-show="lang==='id'">
                             Booking Car adalah modul untuk mengajukan permintaan kendaraan operasional
                             beserta pengemudi untuk keperluan perjalanan dinas. Anda mengajukan permintaan
                             dengan tujuan, jadwal, dan rute perjalanan, permintaan melewati approval departemen,
                             lalu General Affair (GA) menugaskan driver dan kendaraan (atau menangani perjalanan
                             dengan cara lain, misalnya taksi), dan booking yang sudah selesai diproses
                             dapat dicetak sebagai dokumen perjalanan.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Use Booking Car when you need an operational car and driver for an official trip.
                             The calendar on the main page shows all bookings (yours, or your company/department's)
                             so you can also check vehicle availability before submitting a new request.
                         </span>
                         <span x-show="lang==='id'">
                             Gunakan Booking Car ketika Anda membutuhkan kendaraan operasional dan pengemudi
                             untuk perjalanan dinas resmi. Kalender pada halaman utama menampilkan seluruh booking
                             (milik Anda, atau perusahaan/departemen Anda) sehingga Anda juga bisa memeriksa
                             ketersediaan kendaraan sebelum mengajukan permintaan baru.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 End-to-End Flow</span>
                             <span x-show="lang==='id'">1.1 Alur Proses Keseluruhan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 A booking request moves through the following stages:
                             </span>
                             <span x-show="lang==='id'">
                                 Sebuah permintaan booking melewati tahapan berikut:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <span x-show="lang==='en'">Requester submits the booking form (purpose, schedule, route, passengers).</span>
                                 <span x-show="lang==='id'">Pemohon mengajukan form booking (tujuan, jadwal, rute, penumpang).</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">The request enters the approval chain configured for the company/department.</span>
                                 <span x-show="lang==='id'">Permintaan masuk ke rantai approval yang dikonfigurasi untuk perusahaan/departemen tersebut.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">Once fully approved, the request lands in GA's queue for processing.</span>
                                 <span x-show="lang==='id'">Setelah disetujui sepenuhnya, permintaan masuk ke antrean GA untuk diproses.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">GA assigns a driver and vehicle, or marks a final travel status (e.g. handled by taxi, cancelled), then locks the booking.</span>
                                 <span x-show="lang==='id'">GA menugaskan driver dan kendaraan, atau menandai status perjalanan akhir (misalnya ditangani taksi, dibatalkan), lalu mengunci booking.</span>
                             </li>
                             <li>
                                 <span x-show="lang==='en'">The requester can print the booking document (PDF) once it has been processed.</span>
                                 <span x-show="lang==='id'">Pemohon dapat mencetak dokumen booking (PDF) setelah booking diproses.</span>
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
                                 The filter buttons above the booking list let you narrow the list by status.
                                 The calendar legend uses the same color coding.
                             </span>
                             <span x-show="lang==='id'">
                                 Tombol filter di atas daftar booking digunakan untuk menyaring daftar berdasarkan status.
                                 Legenda kalender menggunakan kode warna yang sama.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Pending</strong> —
                                 <span x-show="lang==='en'">Submitted and currently going through approval.</span>
                                 <span x-show="lang==='id'">Sudah disubmit dan sedang dalam proses approval.</span>
                             </li>
                             <li>
                                 <strong>Approved</strong> —
                                 <span x-show="lang==='en'">All approvals are complete. The booking is waiting to be processed by GA.</span>
                                 <span x-show="lang==='id'">Semua approval sudah selesai. Booking menunggu diproses oleh GA.</span>
                             </li>
                             <li>
                                 <strong>Waiting Process</strong> —
                                 <span x-show="lang==='en'">(GA view only) Approved bookings that GA has not yet processed.</span>
                                 <span x-show="lang==='id'">(Tampilan GA) Booking yang sudah approved namun belum diproses oleh GA.</span>
                             </li>
                             <li>
                                 <strong>Processed</strong> —
                                 <span x-show="lang==='en'">GA has assigned a driver/vehicle or recorded a travel status for the booking.</span>
                                 <span x-show="lang==='id'">GA sudah menugaskan driver/kendaraan atau mencatat status perjalanan untuk booking ini.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">An approver sent the request back for correction. It can be edited and re-submitted, or cancelled.</span>
                                 <span x-show="lang==='id'">Approver mengembalikan permintaan untuk diperbaiki. Bisa diedit dan disubmit ulang, atau dibatalkan.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> —
                                 <span x-show="lang==='en'">The request was rejected and the document is closed.</span>
                                 <span x-show="lang==='id'">Permintaan ditolak dan dokumen ditutup.</span>
                             </li>
                             <li>
                                 <strong>Cancelled</strong> —
                                 <span x-show="lang==='en'">The requester cancelled a Revise document before re-submitting it.</span>
                                 <span x-show="lang==='id'">Pemohon membatalkan dokumen berstatus Revise sebelum disubmit ulang.</span>
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
                         <span x-show="lang==='en'">2. Submitting a Car Booking Request</span>
                         <span x-show="lang==='id'">2. Mengajukan Permintaan Booking Car</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>New Booking</strong> on the Booking Car page to open the request form.
                             Fill in the basic information, schedule, route, and purpose, then submit.
                             There is no draft mode for this form — once you submit, the request immediately
                             enters the approval chain.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>New Booking</strong> pada halaman Booking Car untuk membuka form permintaan.
                             Isi informasi dasar, jadwal, rute, dan tujuan perjalanan, lalu submit.
                             Form ini tidak memiliki mode draft — setelah disubmit, permintaan langsung
                             masuk ke rantai approval.
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
                                 <strong>
                                     <span x-show="lang==='en'">Department</span>
                                     <span x-show="lang==='id'">Departemen</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Selected from the companies/departments tied to your account.</span>
                                 <span x-show="lang==='id'">Dipilih dari perusahaan/departemen yang terkait dengan akun Anda.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Requester</span>
                                     <span x-show="lang==='id'">Pemohon</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Auto-filled from your account and read-only.</span>
                                 <span x-show="lang==='id'">Terisi otomatis dari akun Anda dan tidak dapat diubah.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Total Passenger</span>
                                     <span x-show="lang==='id'">Total Penumpang</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Number of people who will use the vehicle.</span>
                                 <span x-show="lang==='id'">Jumlah orang yang akan menggunakan kendaraan.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Schedule & Route</span>
                             <span x-show="lang==='id'">2.2 Jadwal & Rute</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Booking Date, Start Time, End Time</span>
                                     <span x-show="lang==='id'">Tanggal Booking, Jam Mulai, Jam Selesai</span>
                                 </strong> —
                                 <span x-show="lang==='en'">When the vehicle is needed.</span>
                                 <span x-show="lang==='id'">Kapan kendaraan dibutuhkan.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Route (Pickup / Destination)</span>
                                     <span x-show="lang==='id'">Rute (Lokasi Jemput / Tujuan)</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Click <strong>Add Route</strong> to add one or more legs of the trip. Each leg needs a pickup and a destination point.</span>
                                 <span x-show="lang==='id'">Klik <strong>Add Route</strong> untuk menambahkan satu atau beberapa tahap perjalanan. Setiap tahap membutuhkan titik jemput dan tujuan.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 You can add multiple route legs for a multi-stop trip (e.g. office → client A → client B → office).
                             </span>
                             <span x-show="lang==='id'">
                                 Anda dapat menambahkan beberapa tahap rute untuk perjalanan dengan banyak titik henti
                                 (misalnya kantor → klien A → klien B → kantor).
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Purpose Information</span>
                             <span x-show="lang==='id'">2.3 Informasi Tujuan</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Company Expense</span>
                                     <span x-show="lang==='id'">Beban Biaya Perusahaan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Which company the trip cost should be charged to. This also determines part of the approval routing.</span>
                                 <span x-show="lang==='id'">Perusahaan mana yang menanggung biaya perjalanan. Ini juga menentukan sebagian alur approval.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">User Request</span>
                                     <span x-show="lang==='id'">Penumpang Utama</span>
                                 </strong> —
                                 <span x-show="lang==='en'">The person on whose behalf the trip is requested, selected from the active employee list.</span>
                                 <span x-show="lang==='id'">Orang yang menjadi alasan utama pengajuan perjalanan, dipilih dari daftar karyawan aktif.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Purpose</span>
                                     <span x-show="lang==='id'">Tujuan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">A predefined trip category (e.g. meeting, site visit) set up by GA.</span>
                                 <span x-show="lang==='id'">Kategori perjalanan yang sudah ditentukan (misalnya meeting, kunjungan site) yang diatur oleh GA.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Purpose Description</span>
                                     <span x-show="lang==='id'">Deskripsi Tujuan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">A free-text explanation of the trip's purpose.</span>
                                 <span x-show="lang==='id'">Penjelasan bebas mengenai tujuan perjalanan.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 All fields marked with * are required. The form cannot be submitted until the
                                 company, department, passenger count, schedule, at least one route, company expense,
                                 user request, purpose, and purpose description are filled in.
                             </span>
                             <span x-show="lang==='id'">
                                 Semua kolom bertanda * wajib diisi. Form tidak dapat disubmit sebelum perusahaan,
                                 departemen, jumlah penumpang, jadwal, minimal satu rute, beban biaya perusahaan,
                                 penumpang utama, tujuan, dan deskripsi tujuan terisi.
                             </span>
                         </div>

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
                             After submitting, the request status becomes <strong>Pending</strong> and enters
                             the approval chain configured for the company and department. Open the booking
                             from the list or calendar to see the full approval timeline — who has approved,
                             who is pending, and any comments left along the way.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah disubmit, status permintaan menjadi <strong>Pending</strong> dan masuk
                             ke rantai approval yang dikonfigurasi untuk perusahaan dan departemen tersebut.
                             Buka booking dari daftar atau kalender untuk melihat timeline approval secara lengkap —
                             siapa yang sudah menyetujui, siapa yang masih pending, dan komentar yang ditinggalkan.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Email Notifications</span>
                             <span x-show="lang==='id'">3.1 Notifikasi Email</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The system sends email notifications at each stage: the next approver is
                                 notified when a request reaches their level, and the requester is notified
                                 when the request is approved, rejected, or returned for revision. Once GA
                                 finishes processing the booking, the requester receives an email with the
                                 driver, handphone, and vehicle plate number assigned — or, if the trip could
                                 not proceed as planned, the final travel status GA recorded instead.
                             </span>
                             <span x-show="lang==='id'">
                                 Sistem mengirimkan notifikasi email pada setiap tahap: approver berikutnya
                                 diberi tahu saat permintaan mencapai levelnya, dan pemohon diberi tahu saat
                                 permintaan disetujui, ditolak, atau dikembalikan untuk revisi. Setelah GA selesai
                                 memproses booking, pemohon menerima email berisi driver, nomor handphone, dan
                                 nomor polisi kendaraan yang ditugaskan — atau, jika perjalanan tidak dapat
                                 berjalan sesuai rencana, status perjalanan akhir yang dicatat GA.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Handling a Revision</span>
                             <span x-show="lang==='id'">3.2 Menangani Revisi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If your request is returned with status <strong>Revise</strong>, open the
                                 detail and read the approver's comment in the tracking timeline. Click
                                 <strong>Edit Booking</strong> to correct the details and re-submit — this
                                 regenerates the approval chain from the start. If the trip is no longer
                                 needed, click <strong>Cancel Request</strong> instead.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika permintaan Anda dikembalikan dengan status <strong>Revise</strong>,
                                 buka detail dan baca komentar approver pada timeline tracking. Klik
                                 <strong>Edit Booking</strong> untuk memperbaiki detail lalu submit ulang —
                                 ini akan membuat ulang rantai approval dari awal. Jika perjalanan sudah
                                 tidak diperlukan, klik <strong>Cancel Request</strong>.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Only documents in <strong>Revise</strong> status, and only the original
                                 requester, can edit or cancel a booking. Once approved or further along,
                                 the document can no longer be edited or cancelled this way.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya dokumen berstatus <strong>Revise</strong>, dan hanya pemohon aslinya,
                                 yang dapat mengedit atau membatalkan booking. Setelah disetujui atau berada
                                 di tahap selanjutnya, dokumen tidak bisa lagi diedit atau dibatalkan dengan cara ini.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 For Approvers: Reviewing a Request</span>
                             <span x-show="lang==='id'">3.3 Untuk Approver: Meninjau Permintaan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When a booking reaches your approval level, open the detail page to review
                                 the schedule, route, and purpose, then take one of the following actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Ketika booking mencapai level approval Anda, buka halaman detail untuk
                                 meninjau jadwal, rute, dan tujuan perjalanan, lalu ambil salah satu tindakan berikut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Approve</strong> —
                                 <span x-show="lang==='en'">The request is valid; it moves to the next approval level or, if you are the last approver, to GA for processing.</span>
                                 <span x-show="lang==='id'">Permintaan valid; berpindah ke level approval berikutnya, atau ke GA untuk diproses jika Anda adalah approver terakhir.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">Something needs to be corrected. A comment is required and is shown to the requester.</span>
                                 <span x-show="lang==='id'">Ada yang perlu diperbaiki. Komentar wajib diisi dan akan ditampilkan kepada pemohon.</span>
                             </li>
                             <li>
                                 <strong>Reject</strong> —
                                 <span x-show="lang==='en'">The request is not approved and the document is closed. A comment explaining the reason is required.</span>
                                 <span x-show="lang==='id'">Permintaan tidak disetujui dan dokumen ditutup. Komentar yang menjelaskan alasan wajib diisi.</span>
                             </li>
                         </ul>

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
                         <span x-show="lang==='en'">4. Printing the Booking Document</span>
                         <span x-show="lang==='id'">4. Mencetak Dokumen Booking</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Open the booking detail and click <strong>Print PDF</strong> to generate the
                             printable travel document. It opens in a new tab and includes the trip purpose,
                             schedule, route, the assigned driver and vehicle (when applicable), and the
                             approval signatures recorded for the request.
                         </span>
                         <span x-show="lang==='id'">
                             Buka detail booking lalu klik <strong>Print PDF</strong> untuk menghasilkan
                             dokumen perjalanan yang dapat dicetak. Dokumen terbuka di tab baru dan berisi
                             tujuan perjalanan, jadwal, rute, driver dan kendaraan yang ditugaskan (jika ada),
                             serta tanda tangan approval yang tercatat untuk permintaan tersebut.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             The PDF can be generated at any stage of the booking — its content (status,
                             driver/vehicle, approval signatures) reflects whatever has been recorded so far.
                             For the document to show driver and vehicle details, GA must have processed
                             the booking first.
                         </span>
                         <span x-show="lang==='id'">
                             PDF dapat dihasilkan pada tahap apa pun dari booking — isinya (status,
                             driver/kendaraan, tanda tangan approval) mengikuti apa yang sudah tercatat
                             sejauh itu. Agar dokumen menampilkan detail driver dan kendaraan, GA harus
                             sudah memproses booking tersebut terlebih dahulu.
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
                         <span x-show="lang==='en'">5. For GA: Processing a Booking</span>
                         <span x-show="lang==='id'">5. Untuk GA: Memproses Booking</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Once a booking is fully approved (status <strong>Approved</strong>), it appears
                             in the <strong>Waiting Process</strong> filter for GA users. Open the booking and
                             click <strong>Process</strong> to assign a driver and vehicle, or to record a final
                             travel status.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah booking disetujui sepenuhnya (status <strong>Approved</strong>), booking
                             akan muncul pada filter <strong>Waiting Process</strong> untuk pengguna GA. Buka
                             booking tersebut lalu klik <strong>Process</strong> untuk menugaskan driver dan
                             kendaraan, atau mencatat status perjalanan akhir.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.1 Assigning Driver & Vehicle</span>
                             <span x-show="lang==='id'">5.1 Menugaskan Driver & Kendaraan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You may adjust the route if needed, then select a <strong>Driver</strong> from
                                 the driver master list (the handphone number is filled in automatically) and a
                                 <strong>Vehicle</strong> from the operational vehicle master list (the plate
                                 number is filled in automatically).
                             </span>
                             <span x-show="lang==='id'">
                                 Anda dapat menyesuaikan rute jika diperlukan, lalu pilih <strong>Driver</strong>
                                 dari daftar master driver (nomor handphone terisi otomatis) dan
                                 <strong>Vehicle</strong> dari daftar master kendaraan operasional (nomor polisi
                                 terisi otomatis).
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.2 Recording a Travel Status Instead</span>
                             <span x-show="lang==='id'">5.2 Mencatat Status Perjalanan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the trip cannot be handled with an operational vehicle (for example, it will
                                 be handled by taxi, or the trip is cancelled), enable
                                 <strong>Set Status Perjalanan</strong> and choose the appropriate status instead
                                 of assigning a driver/vehicle. The requester is notified by email of this status.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika perjalanan tidak dapat ditangani dengan kendaraan operasional (misalnya
                                 akan ditangani dengan taksi, atau perjalanan dibatalkan), aktifkan
                                 <strong>Set Status Perjalanan</strong> dan pilih status yang sesuai sebagai
                                 pengganti penugasan driver/kendaraan. Pemohon akan diberi tahu melalui email
                                 mengenai status ini.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.3 Save vs. Submit & Lock</span>
                             <span x-show="lang==='id'">5.3 Save vs. Submit & Lock</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Save</strong> —
                                 <span x-show="lang==='en'">Stores the driver/vehicle or travel status without locking the booking, so it can still be updated later.</span>
                                 <span x-show="lang==='id'">Menyimpan driver/kendaraan atau status perjalanan tanpa mengunci booking, sehingga masih bisa diperbarui nanti.</span>
                             </li>
                             <li>
                                 <strong>Submit & Lock</strong> —
                                 <span x-show="lang==='en'">Finalizes the processing. The booking is locked and triggers the notification email to the requester.</span>
                                 <span x-show="lang==='id'">Menyelesaikan proses. Booking dikunci dan memicu email notifikasi kepada pemohon.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 A booking that has not been locked is automatically locked using whatever
                                 was last saved once 3 days (H+3) have passed since its final approval.
                                 Process bookings promptly to make sure the correct driver and vehicle are recorded.
                             </span>
                             <span x-show="lang==='id'">
                                 Booking yang belum dikunci akan otomatis terkunci menggunakan data terakhir
                                 yang disimpan setelah 3 hari (H+3) sejak approval terakhirnya. Proses booking
                                 secepatnya agar driver dan kendaraan yang tercatat sudah benar.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.4 Changing the Company Expense</span>
                             <span x-show="lang==='id'">5.4 Mengubah Beban Biaya Perusahaan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 While a booking is still <strong>Pending</strong>, GA can click
                                 <strong>Change Expense</strong> on the detail page to move the cost to a
                                 different company. This regenerates the condition-based approval steps tied
                                 to that company while keeping the approval history already recorded.
                             </span>
                             <span x-show="lang==='id'">
                                 Selama booking masih berstatus <strong>Pending</strong>, GA dapat mengklik
                                 <strong>Change Expense</strong> pada halaman detail untuk memindahkan beban
                                 biaya ke perusahaan lain. Ini akan membuat ulang langkah approval berbasis
                                 kondisi yang terkait perusahaan tersebut, sambil tetap menyimpan riwayat
                                 approval yang sudah ada.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.5 Booking Car Setup (Master Data)</span>
                             <span x-show="lang==='id'">5.5 Booking Car Setup (Data Master)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click <strong>Booking Car Setup</strong> on the main page to manage the master
                                 data used throughout this module. It is organized into three tabs:
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Booking Car Setup</strong> pada halaman utama untuk mengelola
                                 data master yang digunakan di seluruh modul ini. Halaman ini terbagi menjadi
                                 tiga tab:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Vehicle Master</strong> —
                                 <span x-show="lang==='en'">The list of operational vehicles available for assignment, used by the Process form's Vehicle field.</span>
                                 <span x-show="lang==='id'">Daftar kendaraan operasional yang tersedia untuk ditugaskan, digunakan oleh kolom Vehicle pada form Process.</span>
                             </li>
                             <li>
                                 <strong>Driver Master</strong> —
                                 <span x-show="lang==='en'">The list of drivers available for assignment, used by the Process form's Driver field.</span>
                                 <span x-show="lang==='id'">Daftar driver yang tersedia untuk ditugaskan, digunakan oleh kolom Driver pada form Process.</span>
                             </li>
                             <li>
                                 <strong>Category</strong> —
                                 <span x-show="lang==='en'">The trip Purpose options and the Status Perjalanan options shown in the booking and process forms.</span>
                                 <span x-show="lang==='id'">Pilihan Purpose perjalanan dan pilihan Status Perjalanan yang ditampilkan pada form booking dan process.</span>
                             </li>
                         </ul>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Use the <strong>Add</strong> button on each tab to create a new entry, and use
                                 the row actions to edit or change the active/inactive status of existing entries.
                             </span>
                             <span x-show="lang==='id'">
                                 Gunakan tombol <strong>Add</strong> pada setiap tab untuk membuat data baru,
                                 dan gunakan aksi pada baris tabel untuk mengedit atau mengubah status
                                 aktif/nonaktif data yang sudah ada.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Booking Car Setup is only accessible to users with GA access. Deactivating a
                                 vehicle, driver, or category removes it from selection in new bookings, but
                                 does not change records already saved on existing bookings.
                             </span>
                             <span x-show="lang==='id'">
                                 Booking Car Setup hanya dapat diakses oleh pengguna dengan akses GA.
                                 Menonaktifkan kendaraan, driver, atau kategori akan menghilangkannya dari
                                 pilihan pada booking baru, namun tidak mengubah data yang sudah tersimpan
                                 pada booking yang sudah ada.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

     </div>
