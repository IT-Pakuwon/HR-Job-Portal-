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
                             Ticket Support is the main channel for reporting IT issues
                             — whether it's a broken device, software that suddenly won't open, a network problem,
                             or anything else that's getting in the way of your work.
                             Once you submit a ticket, the IT team will pick it up and follow up directly.
                         </span>
                         <span x-show="lang==='id'">
                             Ticket Support adalah saluran utama untuk melaporkan masalah IT —
                             mulai dari perangkat yang bermasalah, software yang tiba-tiba tidak bisa dibuka,
                             masalah jaringan, atau hal-hal lain yang menghambat pekerjaan.
                             Setelah tiket dibuat, tim IT akan segera menangani dan menindaklanjuti langsung.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             The ticket system applies to all IT-related issues.
                             For access or system permission requests, please use the Access Request menu instead.
                         </span>
                         <span x-show="lang==='id'">
                             Sistem tiket berlaku untuk semua kendala terkait IT.
                             Untuk permintaan akses atau izin sistem, silakan gunakan menu Access Request.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Overview</span>
                             <span x-show="lang==='id'">1.1 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top of the page you'll see status cards showing how many tickets
                                 are in each stage. Clicking a card will filter the list below to show
                                 only tickets with that status.
                             </span>
                             <span x-show="lang==='id'">
                                 Di bagian atas halaman terdapat kartu status yang menunjukkan jumlah tiket
                                 di setiap tahapan. Klik kartu untuk memfilter daftar di bawah sesuai status tersebut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>All</strong> —
                                 <span x-show="lang==='en'">All tickets regardless of status.</span>
                                 <span x-show="lang==='id'">Semua tiket tanpa filter status.</span>
                             </li>
                             <li>
                                 <strong>Created</strong> —
                                 <span x-show="lang==='en'">Ticket just submitted, waiting for IT to respond.</span>
                                 <span x-show="lang==='id'">Tiket baru dibuat, menunggu respon dari IT.</span>
                             </li>
                             <li>
                                 <strong>Response</strong> —
                                 <span x-show="lang==='en'">IT has acknowledged the ticket and is preparing to handle it.</span>
                                 <span x-show="lang==='id'">IT sudah merespons dan sedang mempersiapkan penanganan.</span>
                             </li>
                             <li>
                                 <strong>Process</strong> —
                                 <span x-show="lang==='en'">IT is actively working on the issue.</span>
                                 <span x-show="lang==='id'">IT sedang aktif menangani masalah.</span>
                             </li>
                             <li>
                                 <strong>Pending</strong> —
                                 <span x-show="lang==='en'">The ticket is on hold, usually because IT is waiting for additional information or parts.</span>
                                 <span x-show="lang==='id'">Tiket ditahan sementara, biasanya karena IT menunggu informasi tambahan atau spare part.</span>
                             </li>
                             <li>
                                 <strong>Envision</strong> —
                                 <span x-show="lang==='en'">The issue has been escalated to a vendor or specialist for further assessment (visible to IT team only).</span>
                                 <span x-show="lang==='id'">Masalah diteruskan ke vendor atau spesialis untuk penanganan lebih lanjut (hanya terlihat oleh tim IT).</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">The ticket has been resolved and closed.</span>
                                 <span x-show="lang==='id'">Tiket sudah diselesaikan dan ditutup.</span>
                             </li>
                             <li>
                                 <strong>Cancelled / Closed</strong> —
                                 <span x-show="lang==='en'">The ticket was cancelled before completion.</span>
                                 <span x-show="lang==='id'">Tiket dibatalkan sebelum selesai.</span>
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
                         <span x-show="lang==='en'">2. Create a Ticket</span>
                         <span x-show="lang==='id'">2. Membuat Tiket</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click the <strong>Create</strong> button on the top-right area of the page to open the ticket form.
                             Fill in as much detail as possible — the more information you provide, the faster IT can resolve it.
                         </span>
                         <span x-show="lang==='id'">
                             Klik tombol <strong>Create</strong> di pojok kanan atas halaman untuk membuka form tiket.
                             Isi detail sebanyak mungkin — semakin lengkap informasinya, semakin cepat IT dapat menanganinya.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Required Fields</span>
                             <span x-show="lang==='id'">2.1 Field Wajib Diisi</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Company & Department</strong> —
                                 <span x-show="lang==='en'">Auto-filled based on your account, but double-check before submitting.</span>
                                 <span x-show="lang==='id'">Terisi otomatis dari akun Anda, namun pastikan sudah benar sebelum submit.</span>
                             </li>
                             <li>
                                 <strong>Category</strong> —
                                 <span x-show="lang==='en'">Select the type of issue (e.g., Hardware, Software, Network).</span>
                                 <span x-show="lang==='id'">Pilih jenis masalah (misalnya Hardware, Software, Jaringan).</span>
                             </li>
                             <li>
                                 <strong>Sub Category</strong> —
                                 <span x-show="lang==='en'">A more specific breakdown of the issue within the selected category.</span>
                                 <span x-show="lang==='id'">Rincian lebih spesifik dari masalah dalam kategori yang dipilih.</span>
                             </li>
                             <li>
                                 <strong>Priority</strong> —
                                 <span x-show="lang==='en'">Indicate urgency level. Be honest — selecting High for everything slows down handling for all users.</span>
                                 <span x-show="lang==='id'">Tentukan tingkat urgensi. Gunakan secara bijak — menandai semua sebagai High justru memperlambat penanganan.</span>
                             </li>
                             <li>
                                 <strong>Subject & Description</strong> —
                                 <span x-show="lang==='en'">Write a clear title and explain the problem in detail, including when it started and what you've already tried.</span>
                                 <span x-show="lang==='id'">Tulis judul yang jelas dan jelaskan masalahnya secara detail, termasuk kapan mulai terjadi dan apa yang sudah Anda coba.</span>
                             </li>
                             <li>
                                 <strong>Location</strong> —
                                 <span x-show="lang==='en'">Specify where you're located so IT knows where to come if an on-site visit is needed.</span>
                                 <span x-show="lang==='id'">Cantumkan lokasi Anda agar IT tahu ke mana harus datang jika kunjungan langsung diperlukan.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Attachments are optional but highly recommended.
                                 Screenshots or photos of the error message help IT diagnose the problem much faster.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran bersifat opsional, tapi sangat disarankan.
                                 Screenshot atau foto pesan error sangat membantu IT dalam mendiagnosis masalah lebih cepat.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 After Submitting</span>
                             <span x-show="lang==='id'">2.2 Setelah Submit</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once submitted, your ticket will appear in the list with status <strong>Created</strong>.
                                 You'll also receive a notification or email confirmation.
                                 The IT team will respond as soon as they pick it up.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah submit, tiket Anda akan muncul di daftar dengan status <strong>Created</strong>.
                                 Anda juga akan mendapat notifikasi atau konfirmasi email.
                                 Tim IT akan merespons segera setelah menerima tiket.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 You can monitor the progress of your ticket anytime from the Ticket Support list.
                                 No need to follow up separately unless it's been a while without any update.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda bisa memantau progres tiket kapan saja dari daftar Ticket Support.
                                 Tidak perlu follow up terpisah kecuali sudah cukup lama tanpa ada pembaruan.
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
                         <span x-show="lang==='en'">3. Monitoring Your Tickets</span>
                         <span x-show="lang==='id'">3. Memantau Tiket Anda</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             The ticket list shows all tickets that belong to your Company and Department.
                             You can search by ticket number, description, or filter by status using the cards at the top.
                         </span>
                         <span x-show="lang==='id'">
                             Daftar tiket menampilkan semua tiket yang termasuk dalam Company dan Department Anda.
                             Anda bisa mencari berdasarkan nomor tiket, deskripsi, atau memfilter berdasarkan status di kartu atas.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Opening a Ticket Detail</span>
                             <span x-show="lang==='id'">3.1 Membuka Detail Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click on a ticket row to open the detail page.
                                 Here you'll see the full description, current status, all activity history,
                                 and any comments from the IT team.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada baris tiket untuk membuka halaman detail.
                                 Di sini Anda akan melihat deskripsi lengkap, status terkini,
                                 seluruh riwayat aktivitas, dan komentar dari tim IT.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Adding Comments</span>
                             <span x-show="lang==='id'">3.2 Menambahkan Komentar</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You can add comments to provide additional information or
                                 ask for a status update. The IT team will see your comment
                                 and can reply directly within the ticket.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda bisa menambahkan komentar untuk memberikan informasi tambahan atau
                                 menanyakan perkembangan. Tim IT akan melihat komentar Anda
                                 dan dapat membalas langsung di dalam tiket.
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
                         <span x-show="lang==='en'">4. Reopening a Completed Ticket</span>
                         <span x-show="lang==='id'">4. Membuka Kembali Tiket yang Sudah Selesai</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             If the same issue comes back after a ticket was marked as completed,
                             you don't need to create a new ticket. You can reopen the original one.
                             This helps IT understand that the problem wasn't fully resolved.
                         </span>
                         <span x-show="lang==='id'">
                             Jika masalah yang sama muncul kembali setelah tiket dinyatakan selesai,
                             Anda tidak perlu membuat tiket baru. Anda cukup membuka kembali tiket yang lama.
                             Ini membantu IT memahami bahwa masalah belum sepenuhnya terselesaikan.
                         </span>
                     </p>

                     <div class="manual-note manual-caution">
                         <span x-show="lang==='en'">
                             Only tickets with status <strong>Completed</strong> can be reopened.
                             Include a note explaining what happened after the previous resolution.
                         </span>
                         <span x-show="lang==='id'">
                             Hanya tiket dengan status <strong>Completed</strong> yang bisa dibuka kembali.
                             Sertakan catatan yang menjelaskan apa yang terjadi setelah penyelesaian sebelumnya.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         @if(auth()->user()->isIT())
         <!-- ================= SECTION 5 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">5. For IT Team: Handling a Ticket</span>
                         <span x-show="lang==='id'">5. Untuk Tim IT: Menangani Tiket</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             This section is for IT staff. The steps below describe the standard workflow
                             for handling an incoming ticket from start to close.
                         </span>
                         <span x-show="lang==='id'">
                             Bagian ini diperuntukkan bagi staf IT. Langkah-langkah di bawah menjelaskan
                             alur standar penanganan tiket dari awal hingga selesai.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.1 Respond to a Ticket</span>
                             <span x-show="lang==='id'">5.1 Merespons Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When a new ticket arrives with status <strong>Created</strong>,
                                 click <strong>Response</strong> to acknowledge it and let the user know you're on it.
                                 You can add an initial comment here.
                             </span>
                             <span x-show="lang==='id'">
                                 Ketika tiket baru masuk dengan status <strong>Created</strong>,
                                 klik <strong>Response</strong> untuk mengonfirmasi penerimaan dan
                                 memberitahu pengguna bahwa Anda sudah menanganinya.
                                 Anda bisa menambahkan komentar awal di sini.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.2 Process the Ticket</span>
                             <span x-show="lang==='id'">5.2 Memproses Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click <strong>Process</strong> to move the ticket into active handling.
                                 This tells the user and your team that the issue is currently being worked on.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Process</strong> untuk memindahkan tiket ke tahap penanganan aktif.
                                 Ini memberi tahu pengguna dan tim bahwa masalah sedang dikerjakan.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.3 Put on Pending (if needed)</span>
                             <span x-show="lang==='id'">5.3 Tahan Tiket / Pending (jika diperlukan)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If you're waiting for something — a spare part, a vendor response, or user input —
                                 click <strong>Pending</strong> and include a reason.
                                 This keeps the ticket visible as an open item without implying it's actively being worked on.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika Anda sedang menunggu sesuatu — spare part, respons vendor, atau informasi dari pengguna —
                                 klik <strong>Pending</strong> dan sertakan alasannya.
                                 Tiket tetap terlihat sebagai item terbuka tanpa mengesankan sedang dikerjakan.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.4 Transfer to Another IT</span>
                             <span x-show="lang==='id'">5.4 Transfer ke IT Lain</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the ticket needs to be handled by a different IT team member or team,
                                 use the <strong>Transfer</strong> action. Select the target and add a note
                                 explaining why it's being transferred.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika tiket perlu ditangani oleh anggota atau tim IT yang berbeda,
                                 gunakan aksi <strong>Transfer</strong>. Pilih tujuan transfer dan tambahkan catatan
                                 yang menjelaskan alasan transfer.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Always include a clear reason when transferring.
                                 The receiving IT needs context to continue handling the ticket effectively.
                             </span>
                             <span x-show="lang==='id'">
                                 Selalu cantumkan alasan yang jelas saat mentransfer.
                                 IT penerima membutuhkan konteks untuk melanjutkan penanganan tiket dengan baik.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.5 Complete the Ticket</span>
                             <span x-show="lang==='id'">5.5 Menyelesaikan Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once the issue is resolved, click <strong>Complete</strong>.
                                 Add a resolution summary — what the problem was and what you did to fix it.
                                 This is useful for future reference if the same issue comes up again.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah masalah terselesaikan, klik <strong>Complete</strong>.
                                 Tambahkan ringkasan penyelesaian — apa masalahnya dan apa yang dilakukan untuk memperbaikinya.
                                 Ini berguna sebagai referensi jika masalah serupa muncul lagi.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 A well-written resolution note helps future IT staff solve similar problems faster.
                             </span>
                             <span x-show="lang==='id'">
                                 Catatan penyelesaian yang ditulis dengan baik membantu staf IT di masa depan
                                 menyelesaikan masalah serupa dengan lebih cepat.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

     </div>
