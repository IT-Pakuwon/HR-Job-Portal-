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
                             IT Recommendation is the place where users can request a formal assessment
                             from the IT team regarding hardware or software needs.
                             Think of it as asking IT: "We need this device or tool — is it the right one,
                             and can we proceed?" IT will review the request, fill in their recommendation,
                             and then pass it through management approval.
                         </span>
                         <span x-show="lang==='id'">
                             IT Recommendation adalah tempat di mana pengguna dapat mengajukan permintaan
                             penilaian resmi dari tim IT terkait kebutuhan hardware atau software.
                             Bayangkan seperti bertanya ke IT: "Kami butuh perangkat atau tools ini —
                             apakah sudah tepat, dan boleh kami lanjutkan?" IT akan meninjau permintaan,
                             mengisi rekomendasi mereka, lalu diteruskan ke approval manajemen.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             IT Recommendation is typically used when a department plans to procure
                             hardware or software that requires IT verification before purchase.
                         </span>
                         <span x-show="lang==='id'">
                             IT Recommendation umumnya digunakan saat departemen berencana mengadakan
                             hardware atau software yang memerlukan verifikasi IT sebelum pembelian.
                         </span>
                     </div>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">1.1 Status Overview</span>
                             <span x-show="lang==='id'">1.1 Ringkasan Status</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Status cards at the top of the page give you a quick count of
                                 recommendations grouped by their current state.
                                 Clicking a card filters the list below.
                             </span>
                             <span x-show="lang==='id'">
                                 Kartu status di bagian atas halaman menampilkan jumlah rekomendasi
                                 berdasarkan kondisi terkini.
                                 Klik kartu untuk memfilter daftar di bawahnya.
                             </span>
                         </p>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>All</strong> —
                                 <span x-show="lang==='en'">All IT Recommendation records.</span>
                                 <span x-show="lang==='id'">Semua data IT Recommendation.</span>
                             </li>
                             <li>
                                 <strong>Waiting IT</strong> —
                                 <span x-show="lang==='en'">Request submitted, IT team is reviewing and filling in the recommendation.</span>
                                 <span x-show="lang==='id'">Permintaan sudah dikirim, tim IT sedang meninjau dan mengisi rekomendasi.</span>
                             </li>
                             <li>
                                 <strong>Waiting Approval</strong> —
                                 <span x-show="lang==='en'">IT has filled in the recommendation and submitted it for management approval.</span>
                                 <span x-show="lang==='id'">IT sudah mengisi rekomendasi dan mengirimkannya untuk approval manajemen.</span>
                             </li>
                             <li>
                                 <strong>Revise</strong> —
                                 <span x-show="lang==='en'">The approver returned the document with a request to revise certain details.</span>
                                 <span x-show="lang==='id'">Approver mengembalikan dokumen dengan permintaan untuk merevisi beberapa detail.</span>
                             </li>
                             <li>
                                 <strong>Rejected</strong> —
                                 <span x-show="lang==='en'">The request was rejected. The rejection reason will be visible in the document detail.</span>
                                 <span x-show="lang==='id'">Permintaan ditolak. Alasan penolakan bisa dilihat di halaman detail dokumen.</span>
                             </li>
                             <li>
                                 <strong>Completed</strong> —
                                 <span x-show="lang==='en'">The recommendation has been fully approved. You may proceed with the procurement process.</span>
                                 <span x-show="lang==='id'">Rekomendasi sudah disetujui penuh. Anda dapat melanjutkan ke proses pengadaan.</span>
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
                         <span x-show="lang==='en'">2. Submitting a Request</span>
                         <span x-show="lang==='id'">2. Mengajukan Permintaan</span>
                     </span>

                     <span x-text="openSection==='s2' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s2'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click the <strong>Create</strong> button to open the request form.
                             Fill in the details of what hardware or software you need,
                             including the business justification — why you need it and how it will be used.
                         </span>
                         <span x-show="lang==='id'">
                             Klik tombol <strong>Create</strong> untuk membuka form permintaan.
                             Isi detail hardware atau software yang dibutuhkan,
                             termasuk justifikasi bisnis — mengapa Anda membutuhkannya dan bagaimana akan digunakan.
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
                                 <span x-show="lang==='en'">Filled automatically from your account.</span>
                                 <span x-show="lang==='id'">Terisi otomatis dari akun Anda.</span>
                             </li>
                             <li>
                                 <strong>Request Type</strong> —
                                 <span x-show="lang==='en'">Whether this is a new procurement, replacement, or additional unit.</span>
                                 <span x-show="lang==='id'">Apakah ini pengadaan baru, penggantian, atau tambahan unit.</span>
                             </li>
                             <li>
                                 <strong>Item / Description</strong> —
                                 <span x-show="lang==='en'">Name and specifications of the hardware or software you're requesting.</span>
                                 <span x-show="lang==='id'">Nama dan spesifikasi hardware atau software yang diminta.</span>
                             </li>
                             <li>
                                 <strong>Quantity</strong> —
                                 <span x-show="lang==='en'">How many units are needed.</span>
                                 <span x-show="lang==='id'">Jumlah unit yang dibutuhkan.</span>
                             </li>
                             <li>
                                 <strong>Justification / Purpose</strong> —
                                 <span x-show="lang==='en'">Clearly explain why this is needed. The more specific, the easier for IT and management to evaluate.</span>
                                 <span x-show="lang==='id'">Jelaskan dengan jelas mengapa hal ini dibutuhkan. Semakin spesifik, semakin mudah bagi IT dan manajemen untuk menilai.</span>
                             </li>
                         </ul>

                         <div class="manual-note manual-warning">
                             <span x-show="lang==='en'">
                                 Supporting documents (quotes, product brochures, etc.) can be attached
                                 and will help speed up the IT review process.
                             </span>
                             <span x-show="lang==='id'">
                                 Dokumen pendukung (penawaran, brosur produk, dll.) dapat dilampirkan
                                 dan akan mempercepat proses review oleh IT.
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
                                 Once submitted, the document will appear in the list with status
                                 <strong>Waiting IT</strong>. The IT team will then assess your request
                                 and fill in their official recommendation.
                                 You'll be notified when the status changes.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, dokumen akan muncul di daftar dengan status
                                 <strong>Waiting IT</strong>. Tim IT kemudian akan menilai permintaan Anda
                                 dan mengisi rekomendasi resmi mereka.
                                 Anda akan mendapat notifikasi ketika statusnya berubah.
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
                         <span x-show="lang==='en'">3. Viewing the Detail & Approval Progress</span>
                         <span x-show="lang==='id'">3. Melihat Detail & Progres Approval</span>
                     </span>

                     <span x-text="openSection==='s3' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s3'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             Click on any row in the list to open the detail page.
                             Here you can see the full content of your request, the IT team's recommendation,
                             and the approval history including who approved, who is still pending,
                             and any comments left by the approvers.
                         </span>
                         <span x-show="lang==='id'">
                             Klik baris mana saja di daftar untuk membuka halaman detail.
                             Di sini Anda bisa melihat isi lengkap permintaan, rekomendasi dari tim IT,
                             dan riwayat approval termasuk siapa yang sudah menyetujui, siapa yang masih pending,
                             serta komentar yang ditinggalkan oleh para approver.
                         </span>
                     </p>

                     <div class="manual-note manual-info">
                         <span x-show="lang==='en'">
                             If the document is in <strong>Revise</strong> status, check the approver's comments first
                             before making changes. The reason for revision is usually noted there.
                         </span>
                         <span x-show="lang==='id'">
                             Jika dokumen berstatus <strong>Revise</strong>, periksa komentar approver terlebih dahulu
                             sebelum melakukan perubahan. Alasan revisi biasanya tercatat di sana.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

         @if(auth()->user()->isIT())
         <!-- ================= SECTION 4 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s4')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">4. For IT Team: Filling the Recommendation</span>
                         <span x-show="lang==='id'">4. Untuk Tim IT: Mengisi Rekomendasi</span>
                     </span>

                     <span x-text="openSection==='s4' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s4'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             When a new request lands in your queue with status <strong>Waiting IT</strong>,
                             open the detail page and review what the requester is asking for.
                             Then fill in the IT recommendation section with your technical assessment.
                         </span>
                         <span x-show="lang==='id'">
                             Ketika permintaan baru masuk ke antrean Anda dengan status <strong>Waiting IT</strong>,
                             buka halaman detail dan tinjau apa yang diminta.
                             Kemudian isi bagian rekomendasi IT dengan penilaian teknis Anda.
                         </span>
                     </p>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.1 What to Fill In</span>
                             <span x-show="lang==='id'">4.1 Apa yang Perlu Diisi</span>
                         </h3>

                         <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Technical Assessment</span>
                                     <span x-show="lang==='id'">Penilaian Teknis</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Is the requested spec appropriate? Are there better or more compatible alternatives?</span>
                                 <span x-show="lang==='id'">Apakah spesifikasi yang diminta sudah sesuai? Ada alternatif yang lebih baik atau kompatibel?</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Recommendation</span>
                                     <span x-show="lang==='id'">Rekomendasi</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Whether you recommend proceeding, modifying the request, or not proceeding.</span>
                                 <span x-show="lang==='id'">Apakah Anda merekomendasikan untuk dilanjutkan, dimodifikasi, atau tidak dilanjutkan.</span>
                             </li>
                             <li>
                                 <strong>
                                     <span x-show="lang==='en'">Notes</span>
                                     <span x-show="lang==='id'">Catatan</span>
                                 </strong> —
                                 <span x-show="lang==='en'">Any relevant technical notes, compatibility concerns, or installation requirements.</span>
                                 <span x-show="lang==='id'">Catatan teknis relevan, masalah kompatibilitas, atau kebutuhan instalasi.</span>
                             </li>
                         </ul>

                     </section>

                     <section class="space-y-4">

                         <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                             <span x-show="lang==='en'">4.2 Submitting the Recommendation</span>
                             <span x-show="lang==='id'">4.2 Mengirim Rekomendasi</span>
                         </h3>

                         <p class="text-gray-600 dark:text-gray-400">
                             <span x-show="lang==='en'">
                                 Once you've completed the recommendation, click <strong>Submit</strong>.
                                 The document will move to <strong>Waiting Approval</strong> status
                                 and will be sent to the approval chain for management sign-off.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah rekomendasi selesai diisi, klik <strong>Submit</strong>.
                                 Dokumen akan berpindah ke status <strong>Waiting Approval</strong>
                                 dan diteruskan ke rantai approval untuk persetujuan manajemen.
                             </span>
                         </p>

                         <div class="manual-note manual-important">
                             <span x-show="lang==='en'">
                                 Once submitted, the IT recommendation content cannot be modified
                                 unless the document is returned for revision by an approver.
                             </span>
                             <span x-show="lang==='id'">
                                 Setelah disubmit, isi rekomendasi IT tidak dapat diubah
                                 kecuali dokumen dikembalikan untuk revisi oleh approver.
                             </span>
                         </div>

                     </section>

                 </div>
             </div>

         </section>
         @endif

         <!-- ================= SECTION 5 ================= -->
         <section class="space-y-6">

             <div class="rounded-xl border border-gray-200 dark:border-gray-700">

                 <button @click="toggle('s5')"
                     class="flex w-full items-center justify-between px-6 py-4 text-left font-semibold">

                     <span>
                         <span x-show="lang==='en'">5. For Approvers: Reviewing a Recommendation</span>
                         <span x-show="lang==='id'">5. Untuk Approver: Meninjau Rekomendasi</span>
                     </span>

                     <span x-text="openSection==='s5' ? '−' : '+'"></span>
                 </button>

                 <div x-show="openSection==='s5'" x-transition class="space-y-6 px-6 pb-6">

                     <p class="text-gray-600 dark:text-gray-400">
                         <span x-show="lang==='en'">
                             When a recommendation reaches your approval level, open the document detail
                             and review both the requester's justification and the IT team's assessment.
                             Then take one of the following actions.
                         </span>
                         <span x-show="lang==='id'">
                             Ketika rekomendasi mencapai level approval Anda, buka detail dokumen
                             dan tinjau justifikasi pemohon serta penilaian tim IT.
                             Kemudian ambil salah satu tindakan berikut.
                         </span>
                     </p>

                     <ul class="list-disc space-y-2 pl-6 text-gray-600 dark:text-gray-400">
                         <li>
                             <strong>Approve</strong> —
                             <span x-show="lang==='en'">Confirm you agree with the recommendation and allow the process to continue.</span>
                             <span x-show="lang==='id'">Konfirmasi bahwa Anda setuju dengan rekomendasi dan proses dapat dilanjutkan.</span>
                         </li>
                         <li>
                             <strong>Revise</strong> —
                             <span x-show="lang==='en'">Return the document with specific notes on what needs to be changed.</span>
                             <span x-show="lang==='id'">Kembalikan dokumen dengan catatan spesifik tentang apa yang perlu diubah.</span>
                         </li>
                         <li>
                             <strong>Reject</strong> —
                             <span x-show="lang==='en'">Decline the request. A reason must be provided so the requester understands why.</span>
                             <span x-show="lang==='id'">Tolak permintaan. Alasan wajib diberikan agar pemohon memahami alasannya.</span>
                         </li>
                     </ul>

                     <div class="manual-note manual-warning">
                         <span x-show="lang==='en'">
                             Revise and Reject actions require a written reason before confirming.
                             This is important so the IT team or requester knows exactly what to address.
                         </span>
                         <span x-show="lang==='id'">
                             Aksi Revise dan Reject wajib disertai alasan tertulis sebelum dikonfirmasi.
                             Ini penting agar tim IT atau pemohon tahu persis apa yang perlu ditangani.
                         </span>
                     </div>

                 </div>
             </div>

         </section>

     </div>
