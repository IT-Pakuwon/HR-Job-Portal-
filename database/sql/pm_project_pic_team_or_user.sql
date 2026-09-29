-- =====================================================================
-- Project Management — a Project's PIC can now be a whole Team OR an
-- individual person, not person-only. Reuses the exact fav_type/ref_id
-- idiom already used by tr_favorite (TEAM|PROJECT -> here TEAM|USER)
-- instead of inventing a new shape. Run against the PGSQL5 connection.
-- =====================================================================

ALTER TABLE tr_project_pic ADD COLUMN IF NOT EXISTS pic_type VARCHAR(10) DEFAULT 'USER';
ALTER TABLE tr_project_pic ADD COLUMN IF NOT EXISTS ref_id VARCHAR(50);

-- Every existing row is a person (ref_id = the old username column).
UPDATE tr_project_pic SET ref_id = username, pic_type = 'USER' WHERE ref_id IS NULL;

ALTER TABLE tr_project_pic ALTER COLUMN ref_id SET NOT NULL;
ALTER TABLE tr_project_pic DROP COLUMN IF EXISTS username;
ALTER TABLE tr_project_pic ADD CONSTRAINT uq_tr_project_pic_project_ref UNIQUE (project_id, pic_type, ref_id);
