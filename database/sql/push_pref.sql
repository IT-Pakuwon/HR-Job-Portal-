-- =====================================================================
-- Per-user My APP notification settings (which kinds to push, quiet hours)
-- Run against the PGSQL2 connection (same as sys_push_subscription).
-- No row = defaults (everything on, no quiet hours).
-- =====================================================================
CREATE TABLE IF NOT EXISTS sys_push_pref (
    username     VARCHAR(100) PRIMARY KEY,
    approvals    BOOLEAN      NOT NULL DEFAULT TRUE,   -- documents waiting for my approval
    updates      BOOLEAN      NOT NULL DEFAULT TRUE,   -- status changes on my documents / tickets / requests
    comments     BOOLEAN      NOT NULL DEFAULT TRUE,   -- comments and @mentions
    others       BOOLEAN      NOT NULL DEFAULT TRUE,   -- reminders, trainings, emails, agreements
    quiet_on     BOOLEAN      NOT NULL DEFAULT FALSE,
    quiet_from   CHAR(5)      NOT NULL DEFAULT '22:00', -- HH:MM, in the user's own time zone
    quiet_to     CHAR(5)      NOT NULL DEFAULT '07:00',
    tz           VARCHAR(64)  NULL,                     -- IANA zone reported by the phone
    created_at   TIMESTAMP    NULL,
    updated_at   TIMESTAMP    NULL
);
