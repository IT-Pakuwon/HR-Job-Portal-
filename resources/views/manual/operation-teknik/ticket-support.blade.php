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
                             Ticket Support under Operation Teknik is the channel for reporting Engineering,
                             Building Service, and Front Office issues — a broken facility, a maintenance need,
                             or anything that requires the technical operation team's attention.
                             Once you submit a ticket, the assigned technician will respond and handle it through
                             to completion.
                         </span>
                         <span x-show="lang==='id'">
                             Ticket Support pada menu Operation Teknik adalah saluran untuk melaporkan kendala
                             Engineering, Building Service, dan Front Office — mulai dari fasilitas yang bermasalah,
                             kebutuhan perawatan, atau hal lain yang memerlukan penanganan tim operasional teknik.
                             Setelah tiket dibuat, teknisi yang ditugaskan akan merespons dan menanganinya
                             hingga selesai.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             This menu is separate from IT Support &gt; Ticket Support. Use this menu for
                             Engineering, Building Service, Front Office, and Berita Acara (BA) matters —
                             use IT Support for computer, software, and network issues instead.
                         </span>
                         <span x-show="lang==='id'">
                             Menu ini terpisah dari IT Support &gt; Ticket Support. Gunakan menu ini untuk hal-hal
                             Engineering, Building Service, Front Office, dan Berita Acara (BA) —
                             gunakan IT Support untuk kendala komputer, software, dan jaringan.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Overview</span>
                             <span x-show="lang==='id'">1.1 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 At the top of the page you'll see status cards showing how many tickets are in
                                 each stage. Clicking a card filters the list below to show only tickets with
                                 that status.
                             </span>
                             <span x-show="lang==='id'">
                                 Di bagian atas halaman terdapat kartu status yang menunjukkan jumlah tiket
                                 di setiap tahapan. Klik kartu untuk memfilter daftar di bawah sesuai status tersebut.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Created</strong> —
                                 <span x-show="lang==='en'">Ticket just submitted, waiting for the technical team to respond.</span>
                                 <span x-show="lang==='id'">Tiket baru dibuat, menunggu respon dari tim teknik.</span>
                             </li>
                             <li>
                                 <strong>Response</strong> —
                                 <span x-show="lang==='en'">A PIC and priority have been assigned; the team is preparing to handle it.</span>
                                 <span x-show="lang==='id'">PIC dan prioritas sudah ditentukan; tim sedang mempersiapkan penanganan.</span>
                             </li>
                             <li>
                                 <strong>Process</strong> —
                                 <span x-show="lang==='en'">The technician is actively working on the issue.</span>
                                 <span x-show="lang==='id'">Teknisi sedang aktif menangani masalah.</span>
                             </li>
                             <li>
                                 <strong>Pending</strong> —
                                 <span x-show="lang==='en'">The ticket is on hold, usually while waiting for spare parts, a vendor, or more information.</span>
                                 <span x-show="lang==='id'">Tiket ditahan sementara, biasanya menunggu spare part, vendor, atau informasi tambahan.</span>
                             </li>
                             <li>
                                 <strong>Awaiting Approval</strong> —
                                 <span x-show="lang==='en'">The technician has finished the work and submitted it for document approval before the ticket can be closed.</span>
                                 <span x-show="lang==='id'">Teknisi sudah menyelesaikan pekerjaan dan mengajukan persetujuan dokumen sebelum tiket dapat ditutup.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">The approval is fully signed off and the ticket is closed.</span>
                                 <span x-show="lang==='id'">Persetujuan sudah lengkap dan tiket ditutup.</span>
                             </li>
                             <li>
                                 <strong>Revised</strong> —
                                 <span x-show="lang==='en'">An approver sent the completion back for correction; the requester needs to update and resubmit the ticket.</span>
                                 <span x-show="lang==='id'">Approver mengembalikan penyelesaian untuk diperbaiki; requester perlu memperbarui dan mengajukan ulang tiket.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> —
                                 <span x-show="lang==='en'">The completion was rejected by an approver. This is final — no further action can be taken on the ticket.</span>
                                 <span x-show="lang==='id'">Penyelesaian ditolak oleh approver. Status ini final — tidak ada aksi lanjutan yang dapat dilakukan pada tiket.</span>
                             </li>
                             <li>
                                 <strong>Reopen</strong> —
                                 <span x-show="lang==='en'">A completed or cancelled ticket was reopened because the issue recurred.</span>
                                 <span x-show="lang==='id'">Tiket yang sudah selesai atau dibatalkan dibuka kembali karena masalah muncul lagi.</span>
                             </li>
                             <li>
                                 <strong>Cancel</strong> —
                                 <span x-show="lang==='en'">The ticket was cancelled before completion.</span>
                                 <span x-show="lang==='id'">Tiket dibatalkan sebelum selesai.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.2 Calendar View and Table View</span>
                             <span x-show="lang==='id'">1.2 Tampilan Kalender dan Tabel</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The page opens in <strong>Calendar View</strong> by default, showing tickets laid
                                 out across the month. Switch to <strong>Table View</strong> for a sortable,
                                 searchable list instead — useful when you need to filter by status, category,
                                 ticket type, company, or a specific date range and export the result to Excel.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman ini secara default terbuka dalam <strong>Calendar View</strong>, menampilkan
                                 tiket dalam tampilan bulanan. Beralih ke <strong>Table View</strong> untuk tampilan
                                 daftar yang dapat diurutkan dan dicari — berguna saat Anda perlu memfilter
                                 berdasarkan status, kategori, tipe tiket, company, atau rentang tanggal tertentu
                                 dan mengekspor hasilnya ke Excel.
                             </span>
                         </p>

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
                             Click the <strong>Create</strong> button on the top-right of the page to open the
                             ticket form. Fill in as much detail as possible — the more information you provide,
                             the faster the technical team can resolve it.
                         </span>
                         <span x-show="lang==='id'">
                             Klik tombol <strong>Create</strong> di pojok kanan atas halaman untuk membuka form
                             tiket. Isi detail sebanyak mungkin — semakin lengkap informasinya, semakin cepat
                             tim teknik dapat menanganinya.
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
                                 <span x-show="lang==='en'">Auto-filled based on your account.</span>
                                 <span x-show="lang==='id'">Terisi otomatis berdasarkan akun Anda.</span>
                             </li>
                             <li>
                                 <strong>Ticket Type</strong> —
                                 <span x-show="lang==='en'">Choose Engineering, Building Service, Front Office, or the matching Berita Acara (BA) type if the report needs an official BA document.</span>
                                 <span x-show="lang==='id'">Pilih Engineering, Building Service, Front Office, atau tipe Berita Acara (BA) yang sesuai jika laporan memerlukan dokumen BA resmi.</span>
                             </li>
                             <li>
                                 <strong>Ticket Date</strong> —
                                 <span x-show="lang==='en'">Defaults to today; you may adjust it if needed.</span>
                                 <span x-show="lang==='id'">Default hari ini; dapat diubah jika diperlukan.</span>
                             </li>
                             <li>
                                 <strong>Category & Sub Category</strong> —
                                 <span x-show="lang==='en'">Select the type of issue, then the more specific breakdown that appears based on your category.</span>
                                 <span x-show="lang==='id'">Pilih jenis masalah, lalu rincian yang lebih spesifik yang muncul sesuai kategori tersebut.</span>
                             </li>
                             <li>
                                 <strong>Location</strong> —
                                 <span x-show="lang==='en'">Select the site. For Berita Acara (BA) tickets, a Sub Location is also required.</span>
                                 <span x-show="lang==='id'">Pilih site. Untuk tiket Berita Acara (BA), Sub Location juga wajib diisi.</span>
                             </li>
                             <li>
                                 <strong>Issue Summary & Description</strong> —
                                 <span x-show="lang==='en'">Write a clear summary and explain the problem in detail — the more specific, the better.</span>
                                 <span x-show="lang==='id'">Tulis ringkasan yang jelas dan jelaskan masalahnya secara detail — semakin spesifik semakin baik.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Priority is set to Medium by default when you create a ticket. The actual
                                 priority and response target are determined by the technical team once
                                 they respond to your ticket.
                             </span>
                             <span x-show="lang==='id'">
                                 Priority secara default bernilai Medium saat tiket dibuat. Priority sebenarnya
                                 dan target respon akan ditentukan oleh tim teknik saat mereka merespons tiket Anda.
                             </span>
                         </div>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Attachments are optional but recommended. You may attach photos, PDFs,
                                 spreadsheets, or drawing files (jpg, png, pdf, xlsx, doc, dwg, dxf) up to 5MB each.
                             </span>
                             <span x-show="lang==='id'">
                                 Lampiran bersifat opsional namun disarankan. Anda dapat melampirkan foto, PDF,
                                 spreadsheet, atau file gambar teknik (jpg, png, pdf, xlsx, doc, dwg, dxf)
                                 maksimal 5MB per file.
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
                                 Once submitted, your ticket appears in the list with status <strong>Created</strong>.
                                 You can monitor its progress anytime from this page — no need to follow up
                                 separately unless it's been a while without any update.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah submit, tiket Anda akan muncul di daftar dengan status <strong>Created</strong>.
                                 Anda bisa memantau progresnya kapan saja dari halaman ini — tidak perlu follow up
                                 terpisah kecuali sudah cukup lama tanpa ada pembaruan.
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
                         <span x-show="lang==='en'">3. Monitoring Your Tickets</span>
                         <span x-show="lang==='id'">3. Memantau Tiket Anda</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Opening a Ticket Detail</span>
                             <span x-show="lang==='id'">3.1 Membuka Detail Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Click on a ticket to open the detail page. The
                                 <strong>Tracking</strong> tab shows the full activity timeline, the
                                 <strong>Discussion</strong> tab is where you and the technical team can chat
                                 about the ticket (you can @mention people involved), and the
                                 <strong>Approval</strong> tab shows the approval status once the ticket has
                                 been marked complete.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik pada sebuah tiket untuk membuka halaman detail. Tab
                                 <strong>Tracking</strong> menampilkan riwayat aktivitas lengkap, tab
                                 <strong>Discussion</strong> adalah tempat Anda dan tim teknik berdiskusi
                                 mengenai tiket (Anda bisa @mention orang yang terlibat), dan tab
                                 <strong>Approval</strong> menampilkan status persetujuan setelah tiket
                                 ditandai selesai.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.2 Approving a Completed Ticket</span>
                             <span x-show="lang==='id'">3.2 Menyetujui Tiket yang Sudah Selesai Dikerjakan</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When a technician finishes the work, the ticket moves to
                                 <strong>Awaiting Approval</strong> instead of closing right away. If you are
                                 listed as an approver for that ticket, it will appear in your
                                 <strong>Pending My Approval</strong> panel. Open the ticket and choose:
                             </span>
                             <span x-show="lang==='id'">
                                 Ketika teknisi selesai mengerjakan, tiket berpindah ke status
                                 <strong>Awaiting Approval</strong>, bukan langsung tertutup. Jika Anda terdaftar
                                 sebagai approver untuk tiket tersebut, tiket akan muncul di panel
                                 <strong>Pending My Approval</strong> Anda. Buka tiket tersebut lalu pilih:
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Approve</strong> —
                                 <span x-show="lang==='en'">confirms the work is acceptable. Once every approval level has approved, the ticket becomes Completed and the requester is notified.</span>
                                 <span x-show="lang==='id'">mengonfirmasi bahwa pekerjaan sudah sesuai. Setelah seluruh level approval menyetujui, tiket berubah menjadi Completed dan requester akan diberi notifikasi.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">sends the ticket back to the requester with a note, so they can update and resubmit it.</span>
                                 <span x-show="lang==='id'">mengembalikan tiket ke requester disertai catatan, agar dapat diperbarui dan diajukan ulang.</span>
                             </li>
                             <li>
                                 <strong>Reject</strong> —
                                 <span x-show="lang==='en'">declines the ticket with a reason. This is final and cannot be undone.</span>
                                 <span x-show="lang==='id'">menolak tiket disertai alasan. Aksi ini bersifat final dan tidak dapat dibatalkan.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Reject is final — once a ticket is rejected, no further action can be taken on it.
                                 Use Revise instead if the work simply needs correction.
                             </span>
                             <span x-show="lang==='id'">
                                 Reject bersifat final — setelah tiket ditolak, tidak ada aksi lanjutan yang
                                 dapat dilakukan. Gunakan Revise jika pekerjaan hanya perlu diperbaiki.
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
                         <span x-show="lang==='en'">4. Reopening a Ticket</span>
                         <span x-show="lang==='id'">4. Membuka Kembali Tiket</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             If the same issue comes back after a ticket was completed or cancelled, you don't
                             need to create a new ticket. Open the original ticket and click
                             <strong>Reopen</strong>, then explain what happened.
                         </span>
                         <span x-show="lang==='id'">
                             Jika masalah yang sama muncul kembali setelah tiket selesai atau dibatalkan, Anda
                             tidak perlu membuat tiket baru. Buka tiket yang lama dan klik
                             <strong>Reopen</strong>, lalu jelaskan apa yang terjadi.
                         </span>
                     </p>

                     <div class="manual-note manual-caution">
                         <span x-show="lang==='en'">
                             As the requester, you can only reopen a completed ticket yourself within
                             <strong>7 days</strong> after it was completed. After that window, please create a
                             new ticket instead.
                         </span>
                         <span x-show="lang==='id'">
                             Sebagai requester, Anda hanya dapat membuka kembali tiket yang sudah selesai dalam
                             waktu <strong>7 hari</strong> setelah tiket tersebut selesai. Setelah lewat batas
                             waktu tersebut, silakan buat tiket baru.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         @if(auth()->user()->hasRole('OPRTEKNIKENG') || auth()->user()->hasRole('OPRTEKNIKBS') || auth()->user()->hasRole('OPRTEKNIKFO') || auth()->user()->hasRole('MGROPRTEKNIKACCESS'))
         <!-- ================= SECTION 5 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">5. For Technical Team: Handling a Ticket</span>
                         <span x-show="lang==='id'">5. Untuk Tim Teknik: Menangani Tiket</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             This section is for Engineering, Building Service, and Front Office staff. The
                             steps below describe the standard workflow for handling an incoming ticket from
                             start to close.
                         </span>
                         <span x-show="lang==='id'">
                             Bagian ini diperuntukkan bagi staf Engineering, Building Service, dan Front Office.
                             Langkah-langkah di bawah menjelaskan alur standar penanganan tiket dari awal
                             hingga selesai.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.1 Respond to a Ticket</span>
                             <span x-show="lang==='id'">5.1 Merespons Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 When a new ticket arrives with status <strong>Created</strong>, click
                                 <strong>Response</strong>. Assign a PIC, set the actual priority, and add a
                                 response description. You may also define a working schedule (start and end date).
                             </span>
                             <span x-show="lang==='id'">
                                 Ketika tiket baru masuk dengan status <strong>Created</strong>, klik
                                 <strong>Response</strong>. Tentukan PIC, atur priority sebenarnya, dan
                                 tambahkan deskripsi respon. Anda juga dapat menentukan jadwal pengerjaan
                                 (tanggal mulai dan selesai).
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
                                 Click <strong>Process</strong> to move the ticket into active handling. This
                                 tells the requester the issue is currently being worked on.
                             </span>
                             <span x-show="lang==='id'">
                                 Klik <strong>Process</strong> untuk memindahkan tiket ke tahap penanganan aktif.
                                 Ini memberi tahu requester bahwa masalah sedang dikerjakan.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Berita Acara (BA) Engineering tickets skip this step — a PIC moves straight
                                 from Response to Pending or Complete.
                             </span>
                             <span x-show="lang==='id'">
                                 Tiket Berita Acara (BA) Engineering melewati tahap ini — PIC langsung berpindah
                                 dari Response ke Pending atau Complete.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.3 Put on Pending (if needed)</span>
                             <span x-show="lang==='id'">5.3 Tahan Tiket / Pending (jika diperlukan)</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If you're waiting for something — a spare part, a vendor, or more information —
                                 click <strong>Pending</strong> and state the reason.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika Anda sedang menunggu sesuatu — spare part, vendor, atau informasi tambahan —
                                 klik <strong>Pending</strong> dan sertakan alasannya.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.4 Transfer the Ticket</span>
                             <span x-show="lang==='id'">5.4 Transfer Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 If the ticket needs to be reassigned to a different category, sub category, or
                                 PIC, use the <strong>Transfer</strong> action and include a note explaining why.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika tiket perlu dialihkan ke category, sub category, atau PIC yang berbeda,
                                 gunakan aksi <strong>Transfer</strong> dan sertakan catatan alasan pengalihan.
                             </span>
                         </p>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.5 Complete the Ticket</span>
                             <span x-show="lang==='id'">5.5 Menyelesaikan Tiket</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once the work is done, click <strong>Complete</strong> and write a solution
                                 description explaining what was done. Unlike IT Support tickets, this does not
                                 close the ticket immediately — it moves the ticket to
                                 <strong>Awaiting Approval</strong> and sends it to the assigned approver(s).
                                 The ticket only becomes Completed after every approval level signs off.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah pekerjaan selesai, klik <strong>Complete</strong> dan tulis deskripsi
                                 solusi yang menjelaskan apa yang dilakukan. Berbeda dengan tiket IT Support,
                                 aksi ini tidak langsung menutup tiket — tiket akan berpindah ke status
                                 <strong>Awaiting Approval</strong> dan dikirim ke approver yang ditugaskan.
                                 Tiket baru menjadi Completed setelah seluruh level approval menyetujui.
                             </span>
                         </p>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Write a clear solution description — if the approver sends it back for
                                 revision, you and the requester will need to go through the process again.
                             </span>
                             <span x-show="lang==='id'">
                                 Tulis deskripsi solusi yang jelas — jika approver mengembalikan untuk revisi,
                                 Anda dan requester perlu mengulang proses tersebut.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

     </div>
