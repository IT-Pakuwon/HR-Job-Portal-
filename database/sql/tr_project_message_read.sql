-- =====================================================================
-- How far each user has read each Project module chat thread, for the
-- unread badges on the Task Management sidebar, Message tab and chat list.
--
-- doctype / refnbr: same as tr_project_message (PRJ, TEAM, TSK, TTK).
-- The row doctype '*' / refnbr '*' is the user's starting point: it is
-- created the first time their badges load, so older messages don't all
-- show up as unread.
--
-- Run against the PGSQL5 connection (db_das). Until it exists the badges
-- stay hidden; the rest of the module is unaffected.
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_project_message_read (
    id            BIGSERIAL PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    doctype       VARCHAR(10)  NOT NULL,
    refnbr        VARCHAR(20)  NOT NULL,
    last_read_id  BIGINT       NOT NULL DEFAULT 0,
    updated_at    TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_tr_project_message_read UNIQUE (username, doctype, refnbr)
);
