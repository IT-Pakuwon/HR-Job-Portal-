-- =====================================================================
-- Project Task statuses become genuinely per-project — ms_task_status was
-- meant to be scoped by project_id (see project_management.sql) but the
-- live table never got that column, leaving every Project sharing one
-- global pool of 4 statuses via the tr_project_task_status junction.
-- Renaming/deleting a status on one Project silently affected every other
-- Project. This gives Project the same honest twin-table pattern Team
-- already has (ms_team_task_status, see team_task_management.sql).
--
-- ms_task_status / tr_project_task_status are intentionally left in place
-- (unused after this) rather than dropped — safer/reversible, can be
-- cleaned up later once this table has run in production for a while.
-- =====================================================================

CREATE TABLE IF NOT EXISTS ms_project_task_status (
    id           BIGSERIAL PRIMARY KEY,
    status_id    VARCHAR(20)  NOT NULL,
    project_id   VARCHAR(20)  NOT NULL REFERENCES ms_project(project_id),
    status_name  VARCHAR(100) NOT NULL,
    color        VARCHAR(20),
    sort_order   INTEGER      DEFAULT 0,
    status       VARCHAR(5)   DEFAULT 'A',
    created_by   VARCHAR(50),
    created_at   TIMESTAMP WITHOUT TIME ZONE,
    updated_by   VARCHAR(50),
    updated_at   TIMESTAMP WITHOUT TIME ZONE,
    deleted_by   VARCHAR(50),
    deleted_at   TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_ms_project_task_status_project_status UNIQUE (project_id, status_id)
);

-- Carry over every existing project's currently-enabled statuses so their
-- existing tasks keep a valid column to point at.
INSERT INTO ms_project_task_status (status_id, project_id, status_name, color, sort_order, status, created_by, created_at)
SELECT j.status_id, j.project_id, m.status_name, m.color, m.sort_order, j.status, j.created_by, j.created_at
FROM tr_project_task_status j
JOIN ms_task_status m ON m.status_id = j.status_id
WHERE NOT EXISTS (
    SELECT 1 FROM ms_project_task_status x WHERE x.project_id = j.project_id AND x.status_id = j.status_id
);

ALTER TABLE tr_project_task DROP CONSTRAINT IF EXISTS tr_project_task_status_id_fkey;
ALTER TABLE tr_project_task ADD CONSTRAINT fk_tr_project_task_status
    FOREIGN KEY (project_id, status_id) REFERENCES ms_project_task_status(project_id, status_id);
