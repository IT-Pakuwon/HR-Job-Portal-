-- Checker TS: which post made each Check In / Out (POSKO = Posko FCC, SECURITY). MySQL, db_tenancy = connection mysql5. Idempotent.
-- Existing rows were all made at Posko FCC, hence the default.
SET @has := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tr_ts_checkinout' AND COLUMN_NAME = 'post');
SET @ddl := IF(@has = 0, "ALTER TABLE tr_ts_checkinout ADD COLUMN post VARCHAR(10) NOT NULL DEFAULT 'POSKO' AFTER action, ADD KEY ix_tr_ts_checkinout_post (doctype, ref_id, post, checked_at)", 'SELECT 1');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
