-- =====================================================================
-- Project Management — PIC becomes multi-person (was a single
-- ms_project.pic_username column, added in pm_project_pic.sql). Run
-- against the PGSQL5 connection (same connection as ms_project).
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_pic (
    id          BIGSERIAL PRIMARY KEY,
    project_id  VARCHAR(20) NOT NULL REFERENCES ms_project(project_id),
    username    VARCHAR(50) NOT NULL,
    added_by    VARCHAR(50),
    added_at    TIMESTAMP WITHOUT TIME ZONE,
    status      VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_project_pic_project_username UNIQUE (project_id, username)
);

CREATE INDEX IF NOT EXISTS idx_tr_project_pic_project ON tr_project_pic(project_id);

-- Carry over any single PIC already set before dropping the old column.
INSERT INTO tr_project_pic (project_id, username, added_by, added_at, status)
SELECT project_id, pic_username, 'system', now(), 'A'
FROM ms_project
WHERE pic_username IS NOT NULL
ON CONFLICT (project_id, username) DO NOTHING;

ALTER TABLE ms_project DROP COLUMN IF EXISTS pic_username;
