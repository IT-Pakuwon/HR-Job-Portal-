<?php

namespace App\Console\Commands;

use App\Services\AiSearch\SchemaSynchronizer;
use Illuminate\Console\Command;
use Throwable;

class AiSearchSyncSchema extends Command
{
    protected $signature = 'ai-search:sync-schema {--source= : ID sumber di ai_data_sources}';

    protected $description = 'Sinkronkan metadata tabel, kolom, dan foreign key ke katalog AI Search';

    public function handle(SchemaSynchronizer $synchronizer): int
    {
        $source = (string) $this->option('source');
        if (!ctype_digit($source) || (int) $source < 1) {
            $this->error('Isi ID sumber yang valid, misalnya --source=3.');
            return self::FAILURE;
        }

        $this->info("Membaca struktur sumber ID {$source}...");
        try {
            $counts = $synchronizer->sync((int) $source);
        } catch (Throwable $exception) {
            // Do not expose connection credentials or database query bindings.
            $this->error('Sinkronisasi gagal. Periksa sumber, koneksi Laravel, izin baca metadata, dan tabel katalog pgsql9.');
            $this->line('Jenis error: '.class_basename($exception));
            return self::FAILURE;
        }

        $this->info("Selesai: {$counts['tables']} tabel/view, {$counts['columns']} kolom, {$counts['relations']} relasi.");
        $this->line("Foreign key yang dilewati (gabungan atau di luar schema): {$counts['skipped_relations']}.");
        $this->line('Metadata baru tetap nonaktif. Periksa katalog dan izin sebelum mengaktifkan pencarian.');
        return self::SUCCESS;
    }
}
