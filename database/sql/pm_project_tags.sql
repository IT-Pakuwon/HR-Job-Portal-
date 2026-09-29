-- =====================================================================
-- Project Management — Tags on Projects too (portfolio Kanban cards),
-- reusing the same shared master list as Task tags (ms_task_tag, from
-- pm_task_tags.sql) rather than a second master. Run against PGSQL5.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_tag (
    id          BIGSERIAL PRIMARY KEY,
    project_id  VARCHAR(20) NOT NULL REFERENCES ms_project(project_id),
    tag_id      VARCHAR(30) NOT NULL REFERENCES ms_task_tag(tag_id),
    status      VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_project_tag UNIQUE (project_id, tag_id)
);

CREATE INDEX IF NOT EXISTS idx_tr_project_tag_project ON tr_project_tag(project_id);
CREATE INDEX IF NOT EXISTS idx_tr_project_tag_tag ON tr_project_tag(tag_id);
