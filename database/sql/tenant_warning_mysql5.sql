-- Tenant Warning + allowed documents (mysql5 / tenancy DB). Run on dbtenancytest first, then prod.
-- allow_simkb_in / _out / _inout map to mssimkbtype Masuk / Keluar / Masuk & Keluar (independent). Defaults keep every existing tenant fully allowed.
ALTER TABLE mstenant
  ADD COLUMN is_warned      tinyint(1)   NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN warning_reason varchar(500) NULL     AFTER is_warned,
  ADD COLUMN allow_sik      tinyint(1)   NOT NULL DEFAULT 1 AFTER warning_reason,
  ADD COLUMN allow_sia      tinyint(1)   NOT NULL DEFAULT 1 AFTER allow_sik,
  ADD COLUMN allow_simkb_in    tinyint(1) NOT NULL DEFAULT 1 AFTER allow_sia,
  ADD COLUMN allow_simkb_out   tinyint(1) NOT NULL DEFAULT 1 AFTER allow_simkb_in,
  ADD COLUMN allow_simkb_inout tinyint(1) NOT NULL DEFAULT 1 AFTER allow_simkb_out;

CREATE TABLE IF NOT EXISTS trtenantwarning (
  id                bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tenantid          bigint NOT NULL,
  reason            varchar(500) NOT NULL,
  status            char(1) NOT NULL DEFAULT 'A',   -- A = active, C = cleared
  issued_user       varchar(50) NOT NULL,
  issued_datetime   datetime NOT NULL,
  cleared_user      varchar(50) NULL,
  cleared_datetime  datetime NULL,
  clear_reason      varchar(500) NULL,
  KEY idx_tenant (tenantid, status)
);
