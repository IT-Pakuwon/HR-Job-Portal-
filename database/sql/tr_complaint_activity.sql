-- Complaint Jobs activity log (mysql5 / tenant DB, next to tr_complaint).
-- One row per step: Created, Process (P), Transfer to WO (W), Complete (D), Cancel (X), Upload Attachment.
-- `status` = the complaint status that step led to (C/P/W/D/X). The header (tr_complaint.status) only holds the current one.
CREATE TABLE IF NOT EXISTS tr_complaint_activity (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    complaintnbr     VARCHAR(30)  NOT NULL,
    siteid           VARCHAR(20)  NULL,
    activitytype     VARCHAR(50)  NOT NULL,
    status           CHAR(1)      NULL,
    activity         VARCHAR(255) NULL,
    notes            VARCHAR(1000) NULL,
    created_user     VARCHAR(50)  NULL,
    created_name     VARCHAR(100) NULL,
    created_datetime DATETIME     NULL,
    KEY idx_complaint (complaintnbr, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill: Created row for every existing complaint.
INSERT INTO tr_complaint_activity (complaintnbr, siteid, activitytype, status, activity, created_user, created_name, created_datetime)
SELECT c.complaintnbr, c.siteid, 'Created', 'C', 'Complaint created', c.created_user, c.created_user, c.complaintdate
FROM tr_complaint c
WHERE NOT EXISTS (SELECT 1 FROM tr_complaint_activity a WHERE a.complaintnbr = c.complaintnbr AND a.activitytype = 'Created');

-- Backfill: attachment uploads previously logged in tractivity.
INSERT INTO tr_complaint_activity (complaintnbr, siteid, activitytype, status, activity, notes, created_user, created_name, created_datetime)
SELECT t.refnbr, t.siteid, 'Upload Attachment', c.status, t.activity, t.notes, t.created_user, t.name, t.created_datetime
FROM tractivity t JOIN tr_complaint c ON c.complaintnbr = t.refnbr
WHERE t.doctype = 'CMP' AND t.status = 'A'
  AND NOT EXISTS (SELECT 1 FROM tr_complaint_activity a WHERE a.complaintnbr = t.refnbr AND a.activitytype = 'Upload Attachment' AND a.created_datetime = t.created_datetime);

-- Activity date / description (the date the step happened; Process may be backdated by the user) and the complaint's resolved date.
-- notes -> activity_description; activity_date backfilled from created_datetime.
ALTER TABLE tr_complaint_activity CHANGE notes activity_description VARCHAR(1000) NULL, ADD COLUMN activity_date DATETIME NULL AFTER status;
UPDATE tr_complaint_activity SET activity_date = created_datetime WHERE activity_date IS NULL;
ALTER TABLE tr_complaint ADD COLUMN resolved_date DATETIME NULL;
UPDATE tr_complaint c JOIN tr_complaint_activity a ON a.complaintnbr = c.complaintnbr AND a.status = 'D' SET c.resolved_date = a.activity_date WHERE c.resolved_date IS NULL;
