-- =====================================================================
-- New Agreement (PSM/OLA "Pembuatan") — schema + master data
-- Block 1 runs against PGSQL5 (db_das_test: tr_agreement*, ms_agreement_*).
-- Block 2 runs against PGSQL2 (ms_autonbr). Idempotent.
-- =====================================================================

-- ── Block 1 (PGSQL5) ─────────────────────────────────────────────────

-- TrAgreement model/old store already write property_cd, but the column was
-- never added on this DB.
ALTER TABLE tr_agreement ADD COLUMN IF NOT EXISTS property_cd VARCHAR(20);

-- Document checklist master for PSM/OLA Pembuatan (step 2 of the create
-- page). agreementdocument_required is the default; the user can override it
-- per agreement, which is copied into tr_agreement_document on save.
-- ORDER BY below fixes the insert order, and the page lists by id, so id order
-- = display order.
INSERT INTO ms_agreement_document (agreementdocument_id, agreementdocument_descr, agreementdocument_required, status, created_by, created_at)
SELECT v.id, v.descr, v.req, 'A', 'system', now()
FROM (VALUES
    ('MEMO_LOI',               'Memo dan/atau LoI',                                                                           true),
    ('AKTA_PENDIRIAN',         'Akta Pendirian/ Anggaran Dasar Penyesuaian UUPT 40/2007',                                     true),
    ('SK_KEMENKUMHAM_AKTA',    'SK Menteri Hukum & HAM RI atas Akta Pendirian/ Penyesuaian UUPT',                             true),
    ('AKTA_PERUBAHAN',         'Akta Perubahan/susunan Direktur/Komisaris terbaru',                                           true),
    ('SK_KEMENKUMHAM_PERUBAHAN','SK Menteri Hukum & HAM RI atas  Akta Perubahan/susunan Direktur/Komisaris terbaru',          true),
    ('NIB',                    'Nomor Induk Berusaha (NIB) Berbasis Resiko',                                                  true),
    ('PERIZINAN_BERUSAHA',     'Perizinan Berusaha',                                                                          true),
    ('IZIN_LOKASI',            'Izin Lokasi',                                                                                 true),
    ('NPWP',                   'NPWP',                                                                                        true),
    ('LAIN_LAIN',              'lain-lain',                                                                                   false)
) AS v(id, descr, req)
WHERE NOT EXISTS (SELECT 1 FROM ms_agreement_document d WHERE d.agreementdocument_id = v.id)
ORDER BY array_position(ARRAY['MEMO_LOI','AKTA_PENDIRIAN','SK_KEMENKUMHAM_AKTA','AKTA_PERUBAHAN','SK_KEMENKUMHAM_PERUBAHAN','NIB','PERIZINAN_BERUSAHA','IZIN_LOKASI','NPWP','LAIN_LAIN'], v.id::text);


-- Per-agreement "tenant already has this document" tick (the checklist
-- checkbox on the create form writes this). agreementdocument_required stays
-- the master's default, copied as-is and not edited by the user.
ALTER TABLE tr_agreement_document ADD COLUMN IF NOT EXISTS agreementdocument_received BOOLEAN NOT NULL DEFAULT false;


-- ── Block 2 (PGSQL2) ─────────────────────────────────────────────────

-- Autonumber for New Agreement ids: NAG + yymm + 4-digit running number
-- (same shape as Item Request 'SR'). nextAutonbr() also auto-creates a row for
-- any later month, so only the current month is seeded here.
INSERT INTO ms_autonbr (doctype, doctype_descr, year, month, number, status, created_by, created_at)
SELECT 'NAG', 'New Agreement', to_char(now(), 'YYYY'), to_char(now(), 'MM'), 0, 'A', 'system', now()
WHERE NOT EXISTS (
    SELECT 1 FROM ms_autonbr
    WHERE doctype = 'NAG' AND year = to_char(now(), 'YYYY') AND month = to_char(now(), 'MM')
);


-- ── When a checklist document was received (shown in the view modal) ──
-- Stamped when the tick goes on, kept while it stays on, cleared on untick.
-- Rows already ticked get their last change date as the best available value.
ALTER TABLE tr_agreement_document ADD COLUMN IF NOT EXISTS agreementdocument_received_at TIMESTAMP NULL;
UPDATE tr_agreement_document
SET agreementdocument_received_at = COALESCE(updated_at, created_at)
WHERE agreementdocument_received = true AND agreementdocument_received_at IS NULL;

-- ── Process tracking sheet (Create / Cetak / Routing / Kirim) ─────────
-- No tables: the steps are a constant in LegalNewAgreementController and the
-- dates live in tr_agreement_activity (see processesFor()):
--   Create step      -> the agreement's CREATE_PEMBUATAN activity row
--   other steps      -> agreement_activity_type = 'PROCESS:<step id>'
--   working_start_date = IN, working_end_date = OUT, response_descr = notes

-- Create is the sheet's first step and is done the moment the agreement is
-- saved: new agreements stamp both dates; backfill the ones made before that.
UPDATE tr_agreement_activity
SET working_start_date = date_trunc('day', response_date),
    working_end_date   = date_trunc('day', response_date)
WHERE agreement_activity_type = 'CREATE_PEMBUATAN'
  AND working_start_date IS NULL AND working_end_date IS NULL;



-- ── IFCA job status: A pending / P on progress / C completed ──────────
-- Creating a PSM / OLA now sets its job to P (it used to set C); completing the
-- PSM / OLA sets it to C. Jobs left at C by the old rule whose agreement is still
-- being worked (Active / Hold / Escalated) are On Progress, so move them to P.
-- Jobs whose only agreement is Completed stay C. Addendums never touch jobs.
UPDATE staging_contract_agreement s
SET status = 'P', updated_by = 'migration', updated_at = now()
WHERE s.status = 'C' AND s.deleted_at IS NULL
  AND EXISTS (
        SELECT 1 FROM tr_agreement a
        WHERE a.deleted_at IS NULL
          AND a.cpny_id = s.cpny_id AND a.business_id = s.business_id
          AND a.agreement_type IS DISTINCT FROM 'ADDENDUM'
          AND a.agreement_step_id IN ('ACTIVE', 'HOLD', 'ESCALATED')
  );


-- ── Floor LOI / Unit LOI + checklist master changes (2026-10-13) ──────
-- Block 1 (PGSQL5). Floor / Unit come from IFCA and stay untouched; Legal
-- types the floor / unit stated in the LOI into these two new columns.
ALTER TABLE tr_agreement ADD COLUMN IF NOT EXISTS floor_loi VARCHAR(20);
ALTER TABLE tr_agreement ADD COLUMN IF NOT EXISTS unit_loi VARCHAR(20);

-- "Perizinan Berusaha" is now "Sertifikat Standar"; "Izin Lokasi" is dropped
-- (soft-deleted from the master so it leaves the checklist; agreements already
-- holding it keep their saved rows).
UPDATE ms_agreement_document SET agreementdocument_descr = 'Sertifikat Standar', updated_at = now()
WHERE agreementdocument_id = 'PERIZINAN_BERUSAHA';
UPDATE ms_agreement_document SET deleted_at = now(), deleted_by = 'system', status = 'I'
WHERE agreementdocument_id = 'IZIN_LOKASI' AND deleted_at IS NULL;

-- "lain-lain" is no longer a master row: users add free-text documents per
-- agreement instead (tr_agreement_document, agreementdocument_id = 'CUSTOM_...').
-- Agreements that already carry a lain-lain row keep it.
UPDATE ms_agreement_document SET deleted_at = now(), deleted_by = 'system', status = 'I'
WHERE agreementdocument_id = 'LAIN_LAIN' AND deleted_at IS NULL;


-- ── Process tracking: master list of processes (2026-10-13) ───────────
-- Block 1 (PGSQL5). Each row is one Process Name (agreementcategory_type =
-- 'PROCESS') the user can pick under "Add Process" in the view modal. ids
-- match the old fixed step ids so rows already saved as 'PROCESS:<id>' in
-- tr_agreement_activity keep working. DOC_COMPLETENESS (Pengumpulan Dokumen)
-- is added by the system when an agreement becomes Active, so it is not
-- offered in the picker (agreementcategory_system = true).
CREATE TABLE IF NOT EXISTS ms_agreement_category (
    id                        BIGSERIAL PRIMARY KEY,
    agreementcategory_id      VARCHAR(50)  NOT NULL,
    agreementcategory_descr   VARCHAR(255) NOT NULL,
    agreementcategory_type    VARCHAR(20)  NOT NULL,
    agreementcategory_order   INTEGER,
    agreementcategory_system  BOOLEAN NOT NULL DEFAULT false,
    status                    VARCHAR(1)   NOT NULL DEFAULT 'A',
    created_by VARCHAR(50), created_at TIMESTAMP,
    updated_by VARCHAR(50), updated_at TIMESTAMP,
    deleted_by VARCHAR(50), deleted_at TIMESTAMP
);
CREATE UNIQUE INDEX IF NOT EXISTS ux_ms_agreement_category
    ON ms_agreement_category (agreementcategory_type, agreementcategory_id) WHERE deleted_at IS NULL;

INSERT INTO ms_agreement_category (agreementcategory_id, agreementcategory_descr, agreementcategory_type, agreementcategory_order, agreementcategory_system, status, created_by, created_at)
SELECT v.id, v.descr, 'PROCESS', v.ord, v.sys, 'A', 'system', now()
FROM (VALUES
    ('DOC_COMPLETENESS', 'Pengumpulan Dokumen',      0, true),
    ('PRINT',            'Cetak',                    1, false),
    ('ROUTE_MARKETING',  'Marketing/Leasing',        2, false),
    ('ROUTE_IVY',        'Ibu Ivy',                  3, false),
    ('ROUTE_TENANT',     'Tenant',                   4, false),
    ('ROUTE_DIREKTUR_1', 'Direktur 1',               5, false),
    ('ROUTE_DIREKTUR_2', 'Direktur 2',               6, false),
    ('SEND_TENANT',      'Kirim 1 set ke Penyewa',   7, false)
) AS v(id, descr, ord, sys)
WHERE NOT EXISTS (
    SELECT 1 FROM ms_agreement_category c
    WHERE c.agreementcategory_type = 'PROCESS' AND c.agreementcategory_id = v.id AND c.deleted_at IS NULL
);


-- ── Process names: PROCESS vs ROUTING (2026-10-13) ────────────────────
-- Block 1 (PGSQL5). PROCESS = Create, Cetak, Konfirmasi, Review (plus the
-- system Pengumpulan Dokumen); every "send to / routed via" name is type
-- ROUTING. Both types are offered under "Add Process" in the view modal.
UPDATE ms_agreement_category
SET agreementcategory_type = 'ROUTING', updated_by = 'system', updated_at = now()
WHERE agreementcategory_id IN ('ROUTE_MARKETING', 'ROUTE_IVY', 'ROUTE_TENANT', 'ROUTE_DIREKTUR_1', 'ROUTE_DIREKTUR_2', 'SEND_TENANT')
  AND agreementcategory_type = 'PROCESS' AND deleted_at IS NULL;

UPDATE ms_agreement_category SET agreementcategory_order = 1, updated_at = now() WHERE agreementcategory_id = 'PRINT' AND agreementcategory_type = 'PROCESS';

INSERT INTO ms_agreement_category (agreementcategory_id, agreementcategory_descr, agreementcategory_type, agreementcategory_order, agreementcategory_system, status, created_by, created_at)
SELECT v.id, v.descr, 'PROCESS', v.ord, false, 'A', 'system', now()
FROM (VALUES
    ('CREATE',  'Create',      0),
    ('CONFIRM', 'Konfirmasi',  2),
    ('REVIEW',  'Review',      3)
) AS v(id, descr, ord)
WHERE NOT EXISTS (
    SELECT 1 FROM ms_agreement_category c
    WHERE c.agreementcategory_type = 'PROCESS' AND c.agreementcategory_id = v.id AND c.deleted_at IS NULL
);


-- ── "Kelengkapan Dokumen" renamed "Pengumpulan Dokumen" (2026-10-13) ──
-- Block 1 (PGSQL5). Master row (DOC_COMPLETENESS, type PROCESS) plus the name
-- already stored on saved process rows / their timeline entries.
UPDATE ms_agreement_category
SET agreementcategory_descr = 'Pengumpulan Dokumen', updated_by = 'system', updated_at = now()
WHERE agreementcategory_id = 'DOC_COMPLETENESS' AND agreementcategory_type = 'PROCESS' AND deleted_at IS NULL;

UPDATE tr_agreement_activity
SET response_summary = 'Pengumpulan Dokumen'
WHERE agreement_activity_type = 'PROCESS:DOC_COMPLETENESS' AND response_summary = 'Kelengkapan Dokumen';


-- ── "Create" process becomes "Process" (2026-10-13) ───────────────────
-- Block 1 (PGSQL5). The sheet's first system row is now "Process PSM / OLA"
-- (id PROCESS): added with Pengumpulan Dokumen when the agreement is Processed
-- (Draft -> Process, or Create -> Process), Start = the day Process was clicked.
-- It is a system row, so it is not offered under "Add Process".
UPDATE ms_agreement_category
SET agreementcategory_id = 'PROCESS', agreementcategory_descr = 'Process PSM / OLA', agreementcategory_order = 1,
    agreementcategory_system = true, updated_by = 'system', updated_at = now()
WHERE agreementcategory_id = 'CREATE' AND agreementcategory_type = 'PROCESS' AND deleted_at IS NULL;

-- Rows already saved under the old id keep their dates.
UPDATE tr_agreement_activity
SET agreement_activity_type = 'PROCESS:PROCESS', response_summary = 'Process PSM / OLA'
WHERE agreement_activity_type = 'PROCESS:CREATE';


-- ── Routing list revised (2026-10-29) ─────────────────────────────────
-- Block 1 (PGSQL5). Ibu Ivy removed (soft delete), Direktur 3 added, order:
-- Marketing/Leasing 2, Direktur 1 3, Tenant 4, Direktur 2 5, Direktur 3 6, Kirim 1 set 7.
UPDATE ms_agreement_category SET status = 'I', deleted_by = 'system', deleted_at = now(), updated_at = now()
WHERE agreementcategory_id = 'ROUTE_IVY' AND agreementcategory_type = 'ROUTING' AND deleted_at IS NULL;

UPDATE ms_agreement_category c SET agreementcategory_order = v.ord, updated_by = 'system', updated_at = now()
FROM (VALUES ('ROUTE_MARKETING', 2), ('ROUTE_DIREKTUR_1', 3), ('ROUTE_TENANT', 4), ('ROUTE_DIREKTUR_2', 5), ('SEND_TENANT', 7)) AS v(id, ord)
WHERE c.agreementcategory_id = v.id AND c.agreementcategory_type = 'ROUTING' AND c.deleted_at IS NULL;

INSERT INTO ms_agreement_category (agreementcategory_id, agreementcategory_descr, agreementcategory_type, agreementcategory_order, agreementcategory_system, status, created_by, created_at)
SELECT 'ROUTE_DIREKTUR_3', 'Direktur 3', 'ROUTING', 6, false, 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM ms_agreement_category WHERE agreementcategory_id = 'ROUTE_DIREKTUR_3' AND deleted_at IS NULL);
