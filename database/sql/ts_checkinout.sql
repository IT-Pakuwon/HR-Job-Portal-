-- Checker TS: Check In / Out Posko FCC log for SIK / SIMKB permits (MySQL, db_tenancy = connection mysql5). Idempotent.
CREATE TABLE IF NOT EXISTS tr_ts_checkinout (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    doctype      VARCHAR(10)  NOT NULL,                    -- SIK | SIMKB
    ref_id       BIGINT UNSIGNED NOT NULL,                 -- trsik.id / trsimkb.id
    ref_nbr      VARCHAR(20)  NOT NULL,                    -- siknbr / simkbnbr
    action       VARCHAR(3)   NOT NULL,                    -- IN | OUT
    checked_by   VARCHAR(50)  NOT NULL,
    checked_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes        VARCHAR(255) NULL,
    KEY ix_tr_ts_checkinout_ref (doctype, ref_id, checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
