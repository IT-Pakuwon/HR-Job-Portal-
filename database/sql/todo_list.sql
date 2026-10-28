-- =====================================================================
-- Dashboard "To Do List" — personal tasks per user, with a week calendar.
-- Run against the PGSQL5 connection (db_das_test). Idempotent.
-- due_date is optional (NULL = no date, shown only under "All").
-- =====================================================================
CREATE TABLE IF NOT EXISTS tr_todo (
    id          BIGSERIAL PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL,
    title       VARCHAR(255) NOT NULL,
    due_date    DATE,
    is_done     BOOLEAN NOT NULL DEFAULT FALSE,
    done_at     TIMESTAMP WITHOUT TIME ZONE,
    created_at  TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    updated_at  TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX IF NOT EXISTS idx_tr_todo_user_due ON tr_todo (username, due_date);
