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
