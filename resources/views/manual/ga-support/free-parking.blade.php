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
                             Free Parking is the menu used to register a vehicle for a parking access card.
                             Submit a registration with the vehicle and owner details, it goes through the
                             approval chain for your company and department, and once fully approved the
                             vehicle is recorded as an active parking pass. Use this menu for a brand new
                             vehicle, a temporary vehicle, renewing an expiring card, replacing a lost or
                             damaged card, or changing the registered plate number.
                         </span>
                         <span x-show="lang==='id'">
                             Free Parking adalah menu yang digunakan untuk mendaftarkan kendaraan agar
                             mendapatkan kartu akses parkir. Anda mengajukan pendaftaran beserta data
                             kendaraan dan pemiliknya, permintaan melewati rantai approval sesuai company
                             dan department Anda, dan setelah disetujui sepenuhnya kendaraan tercatat
                             sebagai kartu parkir aktif. Gunakan menu ini untuk kendaraan baru, kendaraan
                             sementara, perpanjangan kartu yang akan habis masa berlakunya, penggantian
                             kartu yang hilang atau rusak, atau perubahan nomor polisi yang terdaftar.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             "Free Parking" is the menu name shown in the sidebar. The underlying process is
                             called Parking Registration (document code PKR) and the document numbers you
                             will see follow that format.
                         </span>
                         <span x-show="lang==='id'">
                             "Free Parking" adalah nama menu yang tampil di sidebar. Proses di baliknya
                             disebut Parking Registration (kode dokumen PKR) dan nomor dokumen yang akan
                             Anda lihat mengikuti format tersebut.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Registration Types</span>
                             <span x-show="lang==='id'">1.1 Jenis Registrasi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When creating a registration, you choose a Parking Type that describes what
                                 you are requesting:
                             </span>
                             <span x-show="lang==='id'">
                                 Saat membuat registrasi, Anda memilih Parking Type yang menjelaskan
                                 permintaan Anda:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>New Request</strong> —
                                 <span x-show="lang==='en'">Register a vehicle that does not yet have a parking card.</span>
                                 <span x-show="lang==='id'">Mendaftarkan kendaraan yang belum memiliki kartu parkir.</span>
                             </li>
                             <li>
                                 <strong>Temp Request</strong> —
                                 <span x-show="lang==='en'">Register a vehicle for temporary/limited-period parking access.</span>
                                 <span x-show="lang==='id'">Mendaftarkan kendaraan untuk akses parkir sementara/periode terbatas.</span>
                             </li>
                             <li>
                                 <strong>Renewal</strong> —
                                 <span x-show="lang==='en'">Extend an existing, already-active parking registration for another period.</span>
                                 <span x-show="lang==='id'">Memperpanjang registrasi parkir yang sudah aktif untuk periode berikutnya.</span>
                             </li>
                             <li>
                                 <strong>Change Card</strong> —
                                 <span x-show="lang==='en'">Request a replacement card for an existing active vehicle (e.g., lost or damaged card).</span>
                                 <span x-show="lang==='id'">Meminta kartu pengganti untuk kendaraan aktif yang sudah terdaftar (misalnya kartu hilang atau rusak).</span>
                             </li>
                             <li>
                                 <strong>Change Plate Number</strong> —
                                 <span x-show="lang==='en'">Update the registered plate number of an existing active vehicle.</span>
                                 <span x-show="lang==='id'">Memperbarui nomor polisi kendaraan aktif yang sudah terdaftar.</span>
                             </li>
                         </ul>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You also choose a Worker Type — typically <strong>Employee</strong> for staff-owned
                                 vehicles, <strong>Operational Vehicle</strong> for company vehicles taken from the
                                 vehicle master list, or another worker category depending on what is configured for
                                 your site.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda juga memilih Worker Type — umumnya <strong>Employee</strong> untuk kendaraan
                                 milik karyawan, <strong>Operational Vehicle</strong> untuk kendaraan perusahaan yang
                                 diambil dari daftar master kendaraan, atau kategori worker lain sesuai konfigurasi
                                 site Anda.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Status Overview</span>
                             <span x-show="lang==='id'">1.2 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards at the top of the page summarize your registrations by stage.
                                 Click any card to filter the list below.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status di bagian atas halaman merangkum registrasi Anda berdasarkan
                                 tahapnya. Klik kartu mana saja untuk memfilter daftar di bawah.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>All</strong> —
                                 <span x-show="lang==='en'">All parking registrations regardless of status.</span>
                                 <span x-show="lang==='id'">Semua registrasi parkir tanpa filter status.</span>
                             </li>
                             <li>
                                 <strong>On Progress</strong> —
                                 <span x-show="lang==='en'">The registration has been submitted and is currently going through approval.</span>
                                 <span x-show="lang==='id'">Registrasi sudah disubmit dan sedang dalam proses approval.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">The registration was returned by an approver for correction and can be edited again.</span>
                                 <span x-show="lang==='id'">Registrasi dikembalikan oleh approver untuk diperbaiki dan dapat diedit kembali.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> —
                                 <span x-show="lang==='en'">The registration was rejected. Open the detail page to see the reason.</span>
                                 <span x-show="lang==='id'">Registrasi ditolak. Buka halaman detail untuk melihat alasannya.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">All approvals are done. The vehicle is now recorded as an active parking pass.</span>
                                 <span x-show="lang==='id'">Semua approval sudah selesai. Kendaraan kini tercatat sebagai kartu parkir aktif.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Users with parking master access also see two extra cards — <strong>All Parking</strong>
                                 (every registration across the company, not just your own department) and
                                 <strong>List Kendaraan</strong> (the master list of registered vehicles). See section 4.
                             </span>
                             <span x-show="lang==='id'">
                                 Pengguna dengan akses master parkir juga melihat dua kartu tambahan —
                                 <strong>All Parking</strong> (seluruh registrasi se-company, bukan hanya department
                                 Anda) dan <strong>List Kendaraan</strong> (daftar master kendaraan yang terdaftar).
                                 Lihat bagian 4.
                             </span>
                         </div>

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
                         <span x-show="lang==='en'">2. Submitting a Registration</span>
                         <span x-show="lang==='id'">2. Mengajukan Registrasi</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>Create</strong> to open the registration form. Fill in the header
                             information, choose the Parking Type and Worker Type, then add one or more
                             vehicles in the detail list before submitting.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>Create</strong> untuk membuka form registrasi. Isi informasi header,
                             pilih Parking Type dan Worker Type, lalu tambahkan satu atau beberapa kendaraan
                             di daftar detail sebelum melakukan submit.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Header Information</span>
                             <span x-show="lang==='id'">2.1 Informasi Header</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Company & Department</strong> —
                                 <span x-show="lang==='en'">Selected from your assigned companies and departments.</span>
                                 <span x-show="lang==='id'">Dipilih dari company dan department yang terdaftar pada akun Anda.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Site Parking</span>
                                     <span x-show="lang==='id'">Site Parking</span>
                                 </strong> —
                                 <span x-show="lang==='en'">The location/site where the parking access applies.</span>
                                 <span x-show="lang==='id'">Lokasi/site tempat akses parkir berlaku.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Parking Type</span>
                                     <span x-show="lang==='id'">Parking Type</span>
                                 </strong> —
                                 <span x-show="lang==='en'">New Request, Temp Request, Renewal, Change Card, or Change Plate Number. See section 1.1.</span>
                                 <span x-show="lang==='id'">New Request, Temp Request, Renewal, Change Card, atau Change Plate Number. Lihat bagian 1.1.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Worker Type</span>
                                     <span x-show="lang==='id'">Worker Type</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Employee, Operational Vehicle, or another category configured for the site.</span>
                                 <span x-show="lang==='id'">Employee, Operational Vehicle, atau kategori lain sesuai konfigurasi site.</span>
                             </li>
                             <li>
                                 <strong>Perpost</strong> —
                                 <span x-show="lang==='en'">The registration period/year. For Employee registrations, the card's start and end dates are set automatically from this period.</span>
                                 <span x-show="lang==='id'">Periode/tahun registrasi. Untuk registrasi Employee, tanggal mulai dan berakhir kartu diisi otomatis berdasarkan periode ini.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Adding Vehicles & Owners</span>
                             <span x-show="lang==='id'">2.2 Menambahkan Kendaraan & Pemilik</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You can add multiple vehicles to a single registration. Each row requires:
                             </span>
                             <span x-show="lang==='id'">
                                 Anda dapat menambahkan beberapa kendaraan dalam satu registrasi. Setiap baris
                                 membutuhkan:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Owner / Name</span>
                                     <span x-show="lang==='id'">Pemilik / Nama</span>
                                 </strong> —
                                 <span x-show="lang==='en'">For Employee worker type, search and pick the employee. For Operational Vehicle, search and pick the vehicle from the master vehicle list. For Renewal, Change Card, and Change Plate Number, search and pick the existing active parking record to update.</span>
                                 <span x-show="lang==='id'">Untuk worker type Employee, cari dan pilih karyawan. Untuk Operational Vehicle, cari dan pilih kendaraan dari daftar master kendaraan. Untuk Renewal, Change Card, dan Change Plate Number, cari dan pilih data parkir aktif yang sudah ada untuk diperbarui.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Plate Number</span>
                                     <span x-show="lang==='id'">Nomor Polisi</span>
                                 </strong> —
                                 <span x-show="lang==='en'">The vehicle's plate number. For Change Plate Number, you enter both the old and the new plate number.</span>
                                 <span x-show="lang==='id'">Nomor polisi kendaraan. Untuk Change Plate Number, Anda mengisi nomor polisi lama dan nomor polisi baru.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Vehicle Type</span>
                                     <span x-show="lang==='id'">Jenis Kendaraan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Motor or Mobil (car).</span>
                                 <span x-show="lang==='id'">Motor atau Mobil.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Attachments (STNK / ID Card / Proof of Payment)</span>
                                     <span x-show="lang==='id'">Lampiran (STNK / ID Card / Bukti Bayar)</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Upload the vehicle registration (STNK), ID card, and/or payment proof for that row. Which attachments are required depends on the selected Parking Type and Worker Type combination — the form will mark required uploads automatically.</span>
                                 <span x-show="lang==='id'">Unggah STNK, ID card, dan/atau bukti bayar untuk baris tersebut. Lampiran mana yang wajib bergantung pada kombinasi Parking Type dan Worker Type yang dipilih — form akan menandai unggahan yang wajib secara otomatis.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Required attachments vary by registration type. If a file you expect to upload
                                 is not shown as required, it simply isn't needed for that combination of
                                 Parking Type and Worker Type.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran yang wajib berbeda-beda sesuai jenis registrasi. Jika file yang Anda
                                 kira perlu diunggah tidak ditandai wajib, berarti memang tidak dibutuhkan untuk
                                 kombinasi Parking Type dan Worker Type tersebut.
                             </span>
                         </div>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 You may also attach general supporting documents at the header level
                                 (not tied to a specific vehicle row).
                             </span>
                             <span x-show="lang==='id'">
                                 Anda juga dapat melampirkan dokumen pendukung umum di level header
                                 (tidak terikat pada baris kendaraan tertentu).
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Submitting</span>
                             <span x-show="lang==='id'">2.3 Submit</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once the form is complete, submit the registration. It is assigned a document
                                 number (e.g. PKR&hellip;), moves to <strong>On Progress</strong> status, and enters
                                 the approval chain for your company/department/site.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah form lengkap, submit registrasi. Sistem akan memberikan nomor dokumen
                                 (misalnya PKR&hellip;), status berubah menjadi <strong>On Progress</strong>, dan
                                 masuk ke rantai approval sesuai company/department/site Anda.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 For New Request and Temp Request, the vehicle is immediately added to the
                                 vehicle master list with a Pending status while it awaits approval.
                                 For Renewal, Change Card, and Change Plate Number, the existing active record
                                 is temporarily set to Pending until the approval finishes. If the request is
                                 rejected, these pending records are removed or reverted automatically.
                             </span>
                             <span x-show="lang==='id'">
                                 Untuk New Request dan Temp Request, kendaraan langsung ditambahkan ke master
                                 kendaraan dengan status Pending selama menunggu approval. Untuk Renewal,
                                 Change Card, dan Change Plate Number, data aktif yang sudah ada akan
                                 sementara diubah menjadi Pending sampai approval selesai. Jika permintaan
                                 ditolak, data pending tersebut otomatis dihapus atau dikembalikan.
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
                         <span x-show="lang==='en'">3. Approval, Tracking & Revisions</span>
                         <span x-show="lang==='id'">3. Approval, Pemantauan & Revisi</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click a row in the list to open the registration's detail page. The detail page
                             shows the current status, the vehicle list, attachments, and the approval
                             timeline for the document.
                         </span>
                         <span x-show="lang==='id'">
                             Klik salah satu baris di daftar untuk membuka halaman detail registrasi. Halaman
                             detail menampilkan status saat ini, daftar kendaraan, lampiran, dan timeline
                             approval dokumen tersebut.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 For Approvers</span>
                             <span x-show="lang==='id'">3.1 Untuk Approver</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If a registration is waiting on your approval level, open its detail page and
                                 take one of the following actions.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika registrasi sedang menunggu approval pada level Anda, buka halaman
                                 detailnya dan ambil salah satu tindakan berikut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Approve</strong> —
                                 <span x-show="lang==='en'">The registration is valid and you authorize it to proceed to the next approver, or to completion if you are the last approver.</span>
                                 <span x-show="lang==='id'">Registrasi sudah valid dan Anda mengizinkannya lanjut ke approver berikutnya, atau langsung selesai jika Anda adalah approver terakhir.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">Something needs to be corrected. The document returns to the requester with status Revise so they can edit and resubmit it.</span>
                                 <span x-show="lang==='id'">Ada yang perlu diperbaiki. Dokumen dikembalikan ke pemohon dengan status Revise agar bisa diedit dan disubmit ulang.</span>
                             </li>
                             <li>
                                 <strong>Reject</strong> —
                                 <span x-show="lang==='en'">The registration is not approved. Always include a reason — it is recorded as a comment on the document.</span>
                                 <span x-show="lang==='id'">Registrasi tidak disetujui. Selalu sertakan alasan — alasan tersebut disimpan sebagai komentar pada dokumen.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Approval decisions cannot be undone once confirmed. Review the vehicle details
                                 and attachments carefully before approving, revising, or rejecting.
                             </span>
                             <span x-show="lang==='id'">
                                 Keputusan approval tidak dapat dibatalkan setelah dikonfirmasi. Periksa detail
                                 kendaraan dan lampiran dengan saksama sebelum approve, revise, atau reject.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Handling a Revision</span>
                             <span x-show="lang==='id'">3.2 Menangani Revisi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When a registration comes back with status <strong>Revise</strong>, open it and
                                 use the edit form to correct the vehicle data or attachments, then submit again.
                                 Submitting a revised registration restarts the approval chain from the
                                 beginning.
                             </span>
                             <span x-show="lang==='id'">
                                 Saat registrasi kembali dengan status <strong>Revise</strong>, buka dokumennya
                                 dan gunakan form edit untuk memperbaiki data kendaraan atau lampiran, lalu
                                 submit kembali. Submit ulang registrasi yang direvisi akan memulai kembali
                                 rantai approval dari awal.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Only documents with status <strong>Revise</strong> can be edited. Once a
                                 registration is On Progress, Completed, or Cancelled, it is locked and cannot
                                 be edited.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya dokumen dengan status <strong>Revise</strong> yang bisa diedit. Setelah
                                 registrasi berstatus On Progress, Completed, atau Cancelled, dokumen dikunci
                                 dan tidak bisa diedit.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.3 Cancelling a Registration</span>
                             <span x-show="lang==='id'">3.3 Membatalkan Registrasi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The original requester can cancel their own registration while it is still in
                                 <strong>On Progress</strong> or <strong>Revise</strong> status. A cancelled
                                 registration cannot be reopened — submit a new one if the parking access is
                                 still needed.
                             </span>
                             <span x-show="lang==='id'">
                                 Pemohon asli dapat membatalkan registrasi miliknya selama statusnya masih
                                 <strong>On Progress</strong> atau <strong>Revise</strong>. Registrasi yang sudah
                                 dibatalkan tidak dapat dibuka kembali — ajukan registrasi baru jika akses
                                 parkir masih dibutuhkan.
                             </span>
                         </p>

                     </section>

                 </div>
             </div>

         </section>

         @if(auth()->user()->hasRole('GAACCESS'))
         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. For GA/Admin: Card Number & Vehicle Master</span>
                         <span x-show="lang==='id'">4. Untuk GA/Admin: Nomor Kartu & Master Kendaraan</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Staff with parking master access can open <strong>Master Kendaraan</strong> (also
                             reachable via the <strong>List Kendaraan</strong> status card) to manage every
                             registered vehicle across the company, regardless of department. This is where
                             the physical parking card is actually assigned after a registration is approved.
                         </span>
                         <span x-show="lang==='id'">
                             Staf dengan akses master parkir dapat membuka <strong>Master Kendaraan</strong>
                             (juga dapat diakses melalui kartu status <strong>List Kendaraan</strong>) untuk
                             mengelola seluruh kendaraan terdaftar se-company, terlepas dari department-nya.
                             Di sinilah kartu parkir fisik sebenarnya diberikan setelah registrasi disetujui.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 Assigning the Card Number</span>
                             <span x-show="lang==='id'">4.1 Memberikan Nomor Kartu</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once a registration reaches <strong>Completed</strong> status, the vehicle
                                 appears in the master list as active but without a card number yet. Open the
                                 vehicle row and use the <strong>No Kartu</strong> action to enter the physical
                                 parking card number that was issued. This is the step that finalizes the
                                 physical card handover after approval.
                             </span>
                             <span x-show="lang==='en'">
                                 Setelah registrasi mencapai status <strong>Completed</strong>, kendaraan akan
                                 muncul di master list dengan status aktif namun belum memiliki nomor kartu.
                                 Buka baris kendaraan tersebut dan gunakan aksi <strong>No Kartu</strong> untuk
                                 mengisi nomor kartu parkir fisik yang sudah diterbitkan. Ini adalah langkah
                                 yang menyelesaikan serah terima kartu fisik setelah approval.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Activating / Deactivating a Vehicle</span>
                             <span x-show="lang==='id'">4.2 Mengaktifkan / Menonaktifkan Kendaraan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Each vehicle in the master list has a status toggle switch. Use it to activate
                                 or deactivate a vehicle's parking access directly — for example, to suspend
                                 access without waiting for a new registration document.
                             </span>
                             <span x-show="lang==='id'">
                                 Setiap kendaraan di master list memiliki switch status. Gunakan untuk
                                 mengaktifkan atau menonaktifkan akses parkir kendaraan secara langsung —
                                 misalnya untuk menangguhkan akses tanpa perlu menunggu dokumen registrasi baru.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 The Master Kendaraan list can also be filtered by Site Parking, Parking Type,
                                 Worker Type, Vehicle Type, and Department to help locate a specific vehicle
                                 quickly.
                             </span>
                             <span x-show="lang==='id'">
                                 Daftar Master Kendaraan juga dapat difilter berdasarkan Site Parking, Parking
                                 Type, Worker Type, Jenis Kendaraan, dan Department untuk membantu menemukan
                                 kendaraan tertentu dengan cepat.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

     </div>
