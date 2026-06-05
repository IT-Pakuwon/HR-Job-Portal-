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
                             Access Request is the formal process for requesting system access or permissions.
                             Instead of contacting IT directly, you submit a structured request here,
                             it goes through department approval, and then IT processes the actual access grant.
                             This keeps things traceable and ensures every access is properly authorized.
                         </span>
                         <span x-show="lang==='id'">
                             Access Request adalah proses resmi untuk meminta akses atau izin ke dalam sistem.
                             Daripada menghubungi IT secara langsung, Anda mengajukan permintaan terstruktur di sini,
                             permintaan melewati approval departemen, lalu IT memproses pemberian akses.
                             Cara ini menjaga keterlacakan dan memastikan setiap akses sudah diotorisasi dengan benar.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             Use Access Request when you need to be granted access to a specific system,
                             application, or menu that you currently cannot open.
                             For hardware or device issues, use Ticket Support instead.
                         </span>
                         <span x-show="lang==='id'">
                             Gunakan Access Request ketika Anda perlu mendapatkan akses ke sistem,
                             aplikasi, atau menu tertentu yang saat ini tidak bisa dibuka.
                             Untuk masalah hardware atau perangkat, gunakan Ticket Support.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Overview</span>
                             <span x-show="lang==='id'">1.1 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards at the top of the page give a quick summary of
                                 all access requests by their current stage.
                                 Click any card to filter the list below.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status di bagian atas halaman memberikan ringkasan cepat
                                 semua permintaan akses berdasarkan tahap saat ini.
                                 Klik kartu mana saja untuk memfilter daftar di bawah.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>All</strong> —
                                 <span x-show="lang==='en'">All access requests regardless of status.</span>
                                 <span x-show="lang==='id'">Semua permintaan akses tanpa filter status.</span>
                             </li>
                             <li>
                                 <strong>On Progress</strong> —
                                 <span x-show="lang==='en'">Request is submitted and currently going through the approval or processing stage.</span>
                                 <span x-show="lang==='id'">Permintaan sudah dikirim dan sedang dalam tahap approval atau proses.</span>
                             </li>
                             <li>
                                 <strong>Approved (IT Processing)</strong> —
                                 <span x-show="lang==='en'">Management has approved the request. IT is now setting up the actual access.</span>
                                 <span x-show="lang==='id'">Manajemen sudah menyetujui permintaan. IT sedang menyiapkan akses.</span>
                             </li>
                             <li>
                                 <strong>Revise / Draft</strong> —
                                 <span x-show="lang==='en'">The request was returned for revision, or is still saved as a draft and hasn't been submitted yet.</span>
                                 <span x-show="lang==='id'">Permintaan dikembalikan untuk revisi, atau masih tersimpan sebagai draft dan belum disubmit.</span>
                             </li>
                             <li>
                                 <strong>Reject</strong> —
                                 <span x-show="lang==='en'">The request was rejected. Open the detail page to see the reason.</span>
                                 <span x-show="lang==='id'">Permintaan ditolak. Buka halaman detail untuk melihat alasannya.</span>
                             </li>
                             <li>
                                 <strong>Finished</strong> —
                                 <span x-show="lang==='en'">Access has been fully granted and the request is closed.</span>
                                 <span x-show="lang==='id'">Akses sudah sepenuhnya diberikan dan permintaan telah ditutup.</span>
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
                         <span x-show="lang==='en'">2. Submitting an Access Request</span>
                         <span x-show="lang==='id'">2. Mengajukan Permintaan Akses</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click <strong>Create</strong> to open the access request form.
                             Select the system or application you need access to,
                             specify the access type, and briefly explain why you need it.
                         </span>
                         <span x-show="lang==='id'">
                             Klik <strong>Create</strong> untuk membuka form permintaan akses.
                             Pilih sistem atau aplikasi yang butuh diakses,
                             tentukan jenis aksesnya, dan jelaskan secara singkat mengapa Anda membutuhkannya.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.1 Required Information</span>
                             <span x-show="lang==='id'">2.1 Informasi yang Wajib Diisi</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>Company & Department</strong> —
                                 <span x-show="lang==='en'">Auto-filled from your account. Confirm before submitting.</span>
                                 <span x-show="lang==='id'">Terisi otomatis dari akun Anda. Konfirmasi sebelum submit.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Category / Access Type</span>
                                     <span x-show="lang==='id'">Kategori / Jenis Akses</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Select what kind of access you're requesting (e.g., new account, additional menu, role change).</span>
                                 <span x-show="lang==='id'">Pilih jenis akses yang diminta (misalnya akun baru, tambahan menu, perubahan role).</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Target System / Application</span>
                                     <span x-show="lang==='id'">Sistem / Aplikasi yang Dituju</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Specify clearly which system or module you need access to.</span>
                                 <span x-show="lang==='id'">Sebutkan dengan jelas sistem atau modul mana yang perlu diakses.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Justification</span>
                                     <span x-show="lang==='id'">Justifikasi</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Briefly explain why the access is needed and how it relates to your job function.</span>
                                 <span x-show="lang==='id'">Jelaskan secara singkat mengapa akses dibutuhkan dan kaitannya dengan fungsi pekerjaan Anda.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Be specific about what you need. Vague requests like "access to all menus"
                                 will likely be sent back for clarification, which delays the process.
                             </span>
                             <span x-show="lang==='id'">
                                 Jelaskan secara spesifik apa yang Anda butuhkan. Permintaan yang tidak jelas
                                 seperti "akses ke semua menu" kemungkinan besar akan dikembalikan untuk klarifikasi,
                                 yang membuat prosesnya menjadi lebih lama.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.2 Adding Detail Items</span>
                             <span x-show="lang==='id'">2.2 Menambahkan Detail Item</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You may add multiple access items in a single request if they are related
                                 and part of the same business need. Each item should specify
                                 the system, access level, and reason.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda dapat menambahkan beberapa item akses dalam satu permintaan
                                 jika semuanya terkait dan bagian dari kebutuhan bisnis yang sama.
                                 Setiap item harus mencantumkan sistem, level akses, dan alasannya.
                             </span>
                         </p>

                         <div class="manual-note manual-info">
                             <span x-show="lang==='en'">
                                 Grouping related access items in one request is preferred over
                                 submitting separate requests for each item.
                                 It makes the review process easier for both approvers and IT.
                             </span>
                             <span x-show="lang==='id'">
                                 Menggabungkan item akses yang terkait dalam satu permintaan lebih disukai
                                 daripada mengajukan permintaan terpisah untuk setiap item.
                                 Ini memudahkan proses review bagi approver maupun IT.
                             </span>
                         </div>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">2.3 Submitting vs. Saving as Draft</span>
                             <span x-show="lang==='id'">2.3 Submit vs. Simpan sebagai Draft</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 You can save the form as a <strong>Draft</strong> if you're not ready to submit yet —
                                 for example, if you need to gather more information first.
                                 Drafts will appear in the list with <strong>Revise / Draft</strong> status.
                                 When you're ready, open the draft and click <strong>Submit</strong>.
                             </span>
                             <span x-show="lang==='id'">
                                 Anda bisa menyimpan form sebagai <strong>Draft</strong> jika belum siap untuk submit —
                                 misalnya, jika Anda masih perlu mengumpulkan informasi tambahan.
                                 Draft akan muncul di daftar dengan status <strong>Revise / Draft</strong>.
                                 Saat sudah siap, buka draft tersebut dan klik <strong>Submit</strong>.
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
                         <span x-show="lang==='en'">3. Tracking Your Request</span>
                         <span x-show="lang==='id'">3. Memantau Permintaan Anda</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             After submitting, your request will move to <strong>On Progress</strong> status
                             and enter the approval chain. You can check its progress anytime
                             by clicking on the request row to open the detail page.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah submit, permintaan Anda akan berpindah ke status <strong>On Progress</strong>
                             dan masuk ke rantai approval. Anda bisa memantau progresnya kapan saja
                             dengan mengklik baris permintaan untuk membuka halaman detail.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">3.1 Approval Timeline</span>
                             <span x-show="lang==='id'">3.1 Timeline Approval</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 The detail page shows a full approval timeline — who has approved,
                                 who is currently pending, and any comments left along the way.
                                 This gives you a clear picture of where your request stands without
                                 needing to ask anyone.
                             </span>
                             <span x-show="lang==='id'">
                                 Halaman detail menampilkan timeline approval secara lengkap — siapa yang sudah menyetujui,
                                 siapa yang masih pending, dan komentar-komentar yang ditinggalkan selama proses.
                                 Ini memberi gambaran jelas di mana permintaan Anda berada tanpa perlu bertanya ke siapa pun.
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
                                 If your request is returned with status <strong>Revise</strong>,
                                 open the detail page and read the approver's comment carefully.
                                 Make the necessary changes and re-submit when ready.
                             </span>
                             <span x-show="lang==='id'">
                                 Jika permintaan Anda dikembalikan dengan status <strong>Revise</strong>,
                                 buka halaman detail dan baca komentar approver dengan saksama.
                                 Lakukan perubahan yang diperlukan dan submit kembali saat sudah siap.
                             </span>
                         </p>

                         <div class="manual-note manual-caution">
                             <span x-show="lang==='en'">
                                 Only documents in <strong>Revise</strong> or <strong>Draft</strong> status can be edited.
                                 Once submitted and in progress, the document is locked for editing.
                             </span>
                             <span x-show="lang==='id'">
                                 Hanya dokumen dengan status <strong>Revise</strong> atau <strong>Draft</strong>
                                 yang bisa diedit. Setelah disubmit dan sedang diproses, dokumen dikunci untuk diedit.
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
                         <span x-show="lang==='en'">4. For Approvers: Reviewing a Request</span>
                         <span x-show="lang==='id'">4. Untuk Approver: Meninjau Permintaan</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             When a request reaches your approval level, you'll receive a notification.
                             Open the detail page and review what access is being requested and why.
                             Then take one of the following actions.
                         </span>
                         <span x-show="lang==='id'">
                             Ketika permintaan mencapai level approval Anda, Anda akan mendapat notifikasi.
                             Buka halaman detail dan tinjau akses apa yang diminta dan alasannya.
                             Kemudian ambil salah satu tindakan berikut.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>Approve</strong> —
                             <span x-show="lang==='en'">The request is valid and you authorize it to proceed.</span>
                             <span x-show="lang==='id'">Permintaan sudah valid dan Anda mengizinkannya untuk dilanjutkan.</span>
                         </li>
                         <li>
                             <strong>Revise</strong> —
                             <span x-show="lang==='en'">Something needs to be corrected or clarified. Add a note explaining what.</span>
                             <span x-show="lang==='id'">Ada sesuatu yang perlu diperbaiki atau diklarifikasi. Tambahkan catatan tentang apa yang perlu diubah.</span>
                         </li>
                         <li>
                             <strong>Reject</strong> —
                             <span x-show="lang==='en'">The request is not approved. Always include a reason so the requester knows why.</span>
                             <span x-show="lang==='id'">Permintaan tidak disetujui. Selalu sertakan alasan agar pemohon memahami penyebabnya.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Approval decisions cannot be undone after confirmation.
                             Make sure you've reviewed all the request details before acting.
                         </span>
                         <span x-show="lang==='id'">
                             Keputusan approval tidak dapat dibatalkan setelah dikonfirmasi.
                             Pastikan Anda sudah meninjau semua detail permintaan sebelum mengambil tindakan.
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
                         <span x-show="lang==='en'">5. For IT Team: Processing the Access</span>
                         <span x-show="lang==='id'">5. Untuk Tim IT: Memproses Akses</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Once all approvals are completed, the request moves to
                             <strong>Approved (IT Processing)</strong> status and lands in the IT queue.
                             Open the request, review what access needs to be granted,
                             and carry out the configuration in the relevant system.
                         </span>
                         <span x-show="lang==='id'">
                             Setelah semua approval selesai, permintaan berpindah ke status
                             <strong>Approved (IT Processing)</strong> dan masuk ke antrean IT.
                             Buka permintaan, tinjau akses apa yang perlu diberikan,
                             dan lakukan konfigurasi di sistem yang relevan.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">5.1 Marking as Finished</span>
                             <span x-show="lang==='id'">5.1 Menandai sebagai Selesai</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 After completing the access setup, click <strong>Done</strong> to close the request.
                                 Add a brief note confirming what was done — for example, which role was assigned
                                 or which menu was enabled. This creates a clear audit trail.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah menyelesaikan pengaturan akses, klik <strong>Done</strong> untuk menutup permintaan.
                                 Tambahkan catatan singkat yang mengonfirmasi apa yang sudah dilakukan — misalnya,
                                 role apa yang diberikan atau menu apa yang diaktifkan. Ini menciptakan jejak audit yang jelas.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once marked as Finished, the request is locked and cannot be reopened.
                                 If the user reports that access is still missing after the request is closed,
                                 they will need to submit a new Access Request.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah ditandai sebagai Finished, permintaan dikunci dan tidak bisa dibuka kembali.
                                 Jika pengguna melaporkan akses masih kurang setelah permintaan ditutup,
                                 mereka perlu mengajukan Access Request baru.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

     </div>
