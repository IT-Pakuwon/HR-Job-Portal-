Master evaluasi vendor dari sembilan form Excel (16 November 2026)

Jalankan dengan PHP 8.2 atau lebih baru:

```powershell
php artisan db:seed --class=VendorPerformanceTemplateSeeder --force
```

Seeder memakai koneksi `pgsql` dalam satu transaksi. Data identik dilewati saat
diulang; jika kunci yang sama memiliki isi berbeda, transaksi dibatalkan agar
perubahan master yang sudah ada tidak tertimpa. Seeder tidak dipasang pada
DatabaseSeeder agar impor ini hanya dijalankan secara sengaja.

Pemetaan:

- `template_vpe_id`: kode jenis pekerjaan `VPE-*`; deskripsi mengikuti C5 Excel.
- `ms_template_vendor_perfomance`: satu baris per aspek (45 baris, 9 jenis).
- `ms_template_vendor_perfomance_detail`: subaspek, kriteria, dan catatan/bukti
  lapangan mengikuti kolom B, D, G (118 baris). Urutan ID subaspek mengikuti
  urutan baris, termasuk label `c.` ganda pada form Sliding Door.
- Bobot Excel dikonversi menjadi persen dua desimal: 0.15 menjadi 15.00.
  `template_vpe_aspect_bobot` pada tabel detail menyimpan bobot subaspek.
  Total detail per aspek cocok dengan bobot aspek; total per jenis 100.00.
- `ms_template_vendor_perfomance_score`: skala 1=40, 2=60, 3=80, 4=100,
  5=120 untuk setiap aspek (225 baris).
- `cpny_id` dan `department_id` NULL karena form tidak mengisi scope tersebut.
- Status `A`, created/updated_by `vendor_template_import`.
- ID primary key dibuat otomatis oleh PostgreSQL.

Panduan deskripsi skor dan kategori hasil akhir disimpan di
`vendor_evaluation_templates.json`, karena tiga tabel master saat ini tidak
memiliki kolom untuk teks panduan/rentang kategori. Tidak ada perubahan schema.
Teks kriteria dari sumber dipertahankan apa adanya, termasuk rujukan peraturan.
Tabel transaksi `tr_vendor_perfomance` dan detailnya tidak diisi dari form kosong.

`vendor_evaluation_source.json` menyimpan sel sumber tanpa menjalankan formula.
`scripts/extract_vendor_evaluation.py` mengekstrak ulang file dari Downloads
serta memvalidasi bobot dan skala sebelum membentuk dataset impor.
