-- =====================================================================
-- Project Management — Task Tags. Run against the PGSQL5 connection
-- (same connection as tr_project_task).
--
-- ms_task_tag is the shared "master" list (same idiom as
-- ms_project_status): typing a new tag on a Task both tags that Task and
-- registers the tag for every other Task's picker to reuse, exact typed
-- text preserved (matched/deduped by a normalized tag_id).
-- tr_project_task_tag is the many-to-many join — a Tag can be reused
-- across many Tasks, a Task can carry many Tags.
-- =====================================================================

CREATE TABLE IF NOT EXISTS ms_task_tag (
    id           BIGSERIAL PRIMARY KEY,
    tag_id       VARCHAR(30)  NOT NULL UNIQUE,
    tag_name     VARCHAR(100) NOT NULL,
    color        VARCHAR(20),
    status       VARCHAR(5)   DEFAULT 'A',
    created_by   VARCHAR(50),
    created_at   TIMESTAMP WITHOUT TIME ZONE,
    updated_by   VARCHAR(50),
    updated_at   TIMESTAMP WITHOUT TIME ZONE
);

CREATE TABLE IF NOT EXISTS tr_project_task_tag (
    id       BIGSERIAL PRIMARY KEY,
    task_id  VARCHAR(20) NOT NULL REFERENCES tr_project_task(task_id),
    tag_id   VARCHAR(30) NOT NULL REFERENCES ms_task_tag(tag_id),
    status   VARCHAR(5)  DEFAULT 'A',
    CONSTRAINT uq_tr_project_task_tag UNIQUE (task_id, tag_id)
);

CREATE INDEX IF NOT EXISTS idx_tr_project_task_tag_task ON tr_project_task_tag(task_id);
CREATE INDEX IF NOT EXISTS idx_tr_project_task_tag_tag ON tr_project_task_tag(tag_id);
