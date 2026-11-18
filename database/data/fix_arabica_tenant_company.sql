-- Jalankan pada database PostgreSQL aplikasi yang memuat tenant ARABICA.
-- Batasi koreksi ke record yang dilaporkan; aman dijalankan ulang.
BEGIN;

UPDATE ms_tenant
SET cpny_id = 'AW', updated_at = CURRENT_TIMESTAMP
WHERE id = 2796
  AND unit_id = 'MGC000GF000004'
  AND store_name = 'ARABICA'
  AND cpny_id = U&'\00C0W';

SELECT id, unit_id, cpny_id, store_name, floor_id, store_no, status
FROM ms_tenant
WHERE id = 2796 AND unit_id = 'MGC000GF000004';

COMMIT;
