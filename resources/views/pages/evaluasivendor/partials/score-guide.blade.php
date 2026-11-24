<dialog id="vendor-score-guide" aria-labelledby="vendor-score-guide-title">
    <header class="vendor-guide-header">
        <div><span class="vendor-guide-eyebrow">Evaluasi Kinerja Vendor</span><h2 id="vendor-score-guide-title">Panduan Skor</h2><p>Gunakan kriteria berikut saat memberi nilai pada setiap subaspek.</p></div>
        <button type="button" class="vendor-guide-close" aria-label="Tutup panduan skor">&times;</button>
    </header>
    <div class="vendor-guide-body">
        <section aria-labelledby="vendor-guide-scale-title">
            <h3 id="vendor-guide-scale-title">Skor dan nilai konversi</h3>
            <div class="vendor-guide-table-wrap"><table class="vendor-guide-table">
                <thead><tr><th style="width:58px;white-space:nowrap">Skor</th><th>Predikat &amp; keterangan</th><th style="width:76px;min-width:76px;white-space:nowrap">Nilai</th></tr></thead>
                <tbody>
                    @foreach([
                        [5, 'Sangat Baik', 120, 'Melebihi ekspektasi, kualitas di atas standar, tidak ada temuan, pekerjaan selesai lebih cepat dari target.'],
                        [4, 'Baik', 100, 'Memenuhi seluruh persyaratan kontrak dan spesifikasi dengan baik.'],
                        [3, 'Cukup', 80, 'Memenuhi standar minimum namun terdapat temuan minor atau perbaikan kecil.'],
                        [2, 'Kurang', 60, 'Terdapat beberapa ketidaksesuaian, keterlambatan, atau kualitas pekerjaan di bawah target yang memerlukan tindakan korektif.'],
                        [1, 'Sangat Kurang', 40, 'Gagal memenuhi kontrak, kualitas pekerjaan tidak dapat diterima, terdapat temuan mayor/berulang, atau pelanggaran K3.'],
                    ] as [$score, $label, $value, $description])
                        <tr><td><span class="vendor-guide-score">{{ $score }}</span></td><td><strong>{{ $label }}</strong><p>{{ $description }}</p></td><td class="vendor-guide-number">{{ $value }}</td></tr>
                    @endforeach
                </tbody>
            </table></div>
        </section>
        <section class="vendor-guide-example" aria-labelledby="vendor-guide-formula-title">
            <h3 id="vendor-guide-formula-title">Cara menghitung nilai terbobot</h3>
            <p>Bobot (%) × nilai konversi ÷ 100</p>
            <div>Bobot <strong>25%</strong> dengan skor <strong>5</strong> → 25 × 120 ÷ 100 = <strong>30,00</strong></div>
            <p>Total Nilai KPI adalah jumlah nilai terbobot seluruh subaspek.</p>
        </section>
        <section aria-labelledby="vendor-guide-result-title">
            <h3 id="vendor-guide-result-title">Kategori hasil akhir vendor</h3>
            <div class="vendor-guide-table-wrap"><table class="vendor-guide-table vendor-guide-categories">
                <thead><tr><th>Total Nilai KPI</th><th>Predikat</th></tr></thead>
                <tbody><tr><td>≥ 90</td><td>Sangat Baik</td></tr><tr><td>80 – &lt;90</td><td>Baik</td></tr><tr><td>70 – &lt;80</td><td>Cukup</td></tr><tr><td>60 – &lt;70</td><td>Kurang</td></tr><tr><td>&lt; 60</td><td>Sangat Kurang / Perlu Evaluasi Khusus</td></tr></tbody>
            </table></div>
            <p class="vendor-guide-note">Nilai maksimum 120 jika seluruh subaspek mendapat skor 5 dan total bobot 100%. Kategori Sangat Baik mencakup nilai di atas 100.</p>
        </section>
    </div>
    <footer class="vendor-guide-footer"><button type="button" class="vendor-guide-done">Mengerti</button></footer>
</dialog>
<style>
    .vendor-score-help{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;border:1px solid #b6e1ff;border-radius:999px;background:#e3f3ff;padding:5px 10px;color:#1c71ae;font-size:11px;font-weight:600;cursor:pointer}
    .vendor-score-help:hover{background:#d1ecff}.vendor-score-help svg{width:15px;height:15px;flex-shrink:0}
    #vendor-score-guide{width:760px;max-width:calc(100vw - 32px);max-height:88vh;margin:auto;border:0;border-radius:16px;padding:0;background:#fff;color:#0f172a;box-shadow:0 24px 80px #0f172a40;overflow:auto}
    #vendor-score-guide::backdrop{background:#0f172a80;backdrop-filter:blur(3px)}
    .vendor-guide-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:22px 24px;border-bottom:1px solid #e2e8f0;background:#f8fafc}
    .vendor-guide-eyebrow{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:#56b1f3;font-weight:700}
    .vendor-guide-header h2{margin:4px 0;font-size:22px;font-weight:750}.vendor-guide-header p{margin:0;font-size:12px;color:#64748b}
    .vendor-guide-close{border:0;background:transparent;font-size:26px;line-height:1;color:#64748b;padding:4px;cursor:pointer}
    .vendor-guide-body{display:grid;gap:20px;padding:22px 24px}.vendor-guide-body h3{margin:0 0 10px;font-size:13px;font-weight:700}
    .vendor-guide-table-wrap{overflow-x:auto;border:1px solid #e2e8f0;border-radius:10px}.vendor-guide-table{width:100%;border-collapse:collapse;font-size:12px;text-align:left}
    .vendor-guide-table th{padding:9px 12px;background:#f8fafc;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:.04em}
    .vendor-guide-table td{padding:10px 12px;border-top:1px solid #e2e8f0;vertical-align:top}.vendor-guide-table strong{font-weight:650}.vendor-guide-table p{margin:3px 0 0;font-size:11px;line-height:1.5;color:#64748b}
    .vendor-guide-score{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:#e3f3ff;color:#1c71ae;font-weight:700}.vendor-guide-number{font-weight:700;font-variant-numeric:tabular-nums}
    .vendor-guide-example{border:1px solid #b6e1ff;border-radius:10px;padding:14px 16px;background:#e3f3ff;color:#124d79}.vendor-guide-example p{margin:5px 0;font-size:12px}.vendor-guide-example div{margin:10px 0;font-size:13px;line-height:1.6}
    .vendor-guide-categories td:first-child{width:30%;font-weight:600}.vendor-guide-note{margin:9px 0 0;font-size:11px;line-height:1.6;color:#64748b}
    .vendor-guide-footer{display:flex;justify-content:flex-end;padding:14px 24px;border-top:1px solid #e2e8f0}.vendor-guide-done{border:0;border-radius:8px;padding:8px 18px;background:#3193da;color:#fff;font-size:12px;font-weight:600;cursor:pointer}
    .dark #vendor-score-guide{background:#111827;color:#e2e8f0}.dark .vendor-guide-header,.dark .vendor-guide-table th{background:#1f2937}.dark .vendor-guide-header,.dark .vendor-guide-footer,.dark .vendor-guide-table-wrap,.dark .vendor-guide-table td{border-color:#374151}
    .dark .vendor-guide-header p,.dark .vendor-guide-table p,.dark .vendor-guide-table th,.dark .vendor-guide-note{color:#94a3b8}.dark .vendor-guide-example{background:#0b324f;color:#d1ecff;border-color:#1c71ae}
    @media(max-width:640px){.vendor-guide-header,.vendor-guide-body{padding:16px}.vendor-guide-table th,.vendor-guide-table td{padding:8px}.vendor-guide-header h2{font-size:20px}}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dialog = document.getElementById('vendor-score-guide');
        document.querySelectorAll('[data-open-score-guide]').forEach(button => button.addEventListener('click', () => dialog.showModal()));
        dialog.querySelectorAll('.vendor-guide-close, .vendor-guide-done').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', function (event) {
            const box = dialog.getBoundingClientRect();
            if (event.target === dialog && (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom)) dialog.close();
        });
    });
</script>
