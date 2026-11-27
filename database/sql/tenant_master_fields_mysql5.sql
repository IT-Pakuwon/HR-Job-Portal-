-- Master Tenant extra fields (mysql5 / tenancy DB). Run on dbtenancytest first, then prod. Safe to re-run.
-- Run after tenant_warning_mysql5.sql. All new columns are optional, so existing rows and the WPS app are unaffected.
ALTER TABLE mstenant
  ADD COLUMN IF NOT EXISTS unittype       varchar(30)   NULL AFTER unit,            -- "Type": Occupied / Fit Out / Vacant
  ADD COLUMN IF NOT EXISTS typestatus     varchar(50)   NULL AFTER unittype,        -- "Type Status": Tenant Upcoming / Renovasi / Pullout / ...
  ADD COLUMN IF NOT EXISTS periode        char(7)       NULL AFTER typestatus,      -- YYYY-MM
  ADD COLUMN IF NOT EXISTS unitsize       decimal(12,2) NULL AFTER periode,         -- m2
  ADD COLUMN IF NOT EXISTS brandorigin    varchar(100)  NULL AFTER unitsize,
  ADD COLUMN IF NOT EXISTS brandtype      varchar(30)   NULL AFTER brandorigin,     -- Local / International
  ADD COLUMN IF NOT EXISTS contractstatus varchar(30)   NULL AFTER brandtype,       -- Active / Pending / Expired
  ADD COLUMN IF NOT EXISTS perusahaan     varchar(255)  NULL AFTER contractstatus;

-- "Type" choices are mscategory rows with type = 'Tenant Type'. mstenant.unittype stores the name.
ALTER TABLE mstenant MODIFY COLUMN unittype varchar(100) NULL;

-- Keep the name 'Vacant' as it is: choosing it sets the tenant's status to Vacant.
INSERT INTO mscategory (name, type, status, created_user, created_datetime, lastupdate_user, lastupdate_datetime)
SELECT s.name, 'Tenant Type', 'A', 'system', NOW(), 'system', NOW()
FROM (
  SELECT 1 AS seq, 'Occupied' AS name
  UNION ALL SELECT 2, 'Fit Out'
  UNION ALL SELECT 3, 'Vacant'
) s
WHERE NOT EXISTS (SELECT 1 FROM mscategory c WHERE c.type = 'Tenant Type' AND c.name = s.name)
ORDER BY s.seq;

-- Tenants already marked Vacant show it in "Type" too.
UPDATE mstenant SET unittype = 'Vacant' WHERE status = 'V' AND unittype IS NULL;

-- A tenant can have several categories (mscategory rows with type = 'Tenant').
CREATE TABLE IF NOT EXISTS mstenantcategory (
  tenantid   bigint unsigned NOT NULL,
  categoryid bigint unsigned NOT NULL,
  PRIMARY KEY (tenantid, categoryid),
  KEY idx_category (categoryid)
);

-- Starter categories. Add the rest in Organization > Category with type "Tenant".
INSERT INTO mscategory (name, type, status, created_user, created_datetime, lastupdate_user, lastupdate_datetime)
SELECT s.name, 'Tenant', 'A', 'system', NOW(), 'system', NOW()
FROM (
  SELECT 'Bag and Shoes' AS name
  UNION ALL SELECT 'Electronics and Gadgets'
  UNION ALL SELECT 'Fashion'
  UNION ALL SELECT 'Food & Beverages'
  UNION ALL SELECT 'Home & Furnishing'
  UNION ALL SELECT 'Services'
) s
WHERE NOT EXISTS (SELECT 1 FROM mscategory c WHERE c.type = 'Tenant' AND c.name = s.name);
