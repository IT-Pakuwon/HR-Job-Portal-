-- =====================================================================
-- Chat messages of the Project module: Team and Project "Message" tabs,
-- Project Task and Team Task chats. They used to share db_iamsystem's
-- tr_message with every other module.
--
-- doctype / refnbr: PRJ + project_id, TEAM + team_id,
--                   TSK + tr_project_task.task_id,
--                   TTK + tr_team_task.task_id
-- Same columns as tr_message, so App\Models\TrProjectMessage can be used
-- wherever TrMessage is. Files shared in chat stay in tr_attachment.
--
-- Run against the PGSQL5 connection (db_das) BEFORE deploying the code
-- that reads it.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_message (
    id            BIGSERIAL PRIMARY KEY,
    refnbr        VARCHAR(20)  NOT NULL,
    doctype       VARCHAR(10)  NOT NULL,
    message_date  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now(),
    message_type  VARCHAR(10),
    cpny_id       VARCHAR(10),
    department_id VARCHAR(25),
    username      VARCHAR(50),
    name          VARCHAR(255),
    message       TEXT,
    status        VARCHAR(5)   NOT NULL DEFAULT 'A',
    created_by    VARCHAR(50),
    created_at    TIMESTAMP WITHOUT TIME ZONE,
    updated_by    VARCHAR(50),
    updated_at    TIMESTAMP WITHOUT TIME ZONE,
    deleted_by    VARCHAR(50),
    deleted_at    TIMESTAMP WITHOUT TIME ZONE
);

-- One thread (chat tab, card comment counts, history).
CREATE INDEX IF NOT EXISTS idx_tr_project_message_thread ON tr_project_message (doctype, refnbr, message_date DESC);
-- Recent messages of a doctype (bell notifications).
CREATE INDEX IF NOT EXISTS idx_tr_project_message_recent ON tr_project_message (doctype, message_date DESC);
