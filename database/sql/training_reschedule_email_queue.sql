-- =====================================================================
-- Training reschedule email queue. The SMTP relay rejects bursts with
-- "421 4.7.0 Too many messages", so reschedule() no longer mails
-- everyone inline: it enqueues one row per registrant here and the
-- training:send-reschedule-emails command (every 5 min) sends up to 15
-- per run, one schedule at a time.
-- status: P = pending, S = sent, F = failed (gave up after 3 attempts).
-- Run on pgsql5 (db_das).
-- =====================================================================

CREATE TABLE IF NOT EXISTS tr_lnd_reschedule_email_queue (
    id bigserial PRIMARY KEY,
    schedule_id varchar(30) NOT NULL,
    training_regist_id varchar(30) NOT NULL,
    old_date date,
    new_date date NOT NULL,
    time_label varchar(20),
    status char(1) NOT NULL DEFAULT 'P',
    attempts integer NOT NULL DEFAULT 0,
    last_error text,
    sent_at timestamp,
    created_by varchar(50),
    created_at timestamp,
    updated_at timestamp
);

CREATE INDEX IF NOT EXISTS idx_reschedule_email_queue_pending
    ON tr_lnd_reschedule_email_queue (status, schedule_id, id);
