-- =====================================================================
-- Legal Lease (New Agreement / Agreement FU) — sync test2 -> live
-- Target DB : db_iamsystem  (PGSQL2 connection)
-- Source    : db_iamsystem_test2 (snapshot taken 2026-11-17)
--
-- Idempotent: every INSERT is guarded by NOT EXISTS, safe to re-run.
-- Wrapped in a transaction; review the verification SELECTs, then COMMIT.
--
-- Menu tree (application LEGALAPP):
--   LEGAL "Legal Lease" (sort 11)
--     ├─ NEWAGREEMENT "New Agreement"
--     │    ├─ PSMOLA   "PSM / OLA"
--     │    ├─ ADDENDUM "Addendum"
--     │    └─ OTHERS   "Others"
--     └─ LEGALAGREEMENT "Agreement FU"
--
-- Roles:
--   LEGALAGREEMENT  - leaf menus + VIEW/CREATE/EDIT/DELETE on 4 screens
--   LEGALACCESS     - "Access Legal (PIC Legal)": all 6 menus incl. parents,
--                     NO access-right rows (same as test2 — check if intended)
-- =====================================================================
BEGIN;

-- ── 1. Application ───────────────────────────────────────────────────
INSERT INTO sys_application (application_id, application_name, status, created_by, created_at)
SELECT 'LEGALAPP', 'Legal APP', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_application WHERE application_id = 'LEGALAPP');

-- ── 2. Screens ───────────────────────────────────────────────────────
INSERT INTO sys_screen (screen_id, screen_name, application_id, status, created_by, created_at)
SELECT v.screen_id, v.screen_name, 'LEGALAPP', 'A', 'system', now()
FROM (VALUES
    ('LEGALAGREEMENT', 'Legal Agreement'),
    ('PSMOLA',         'New Agreement - PSM / OLA'),
    ('ADDENDUM',       'New Agreement - Addendum'),
    ('OTHERS',         'New Agreement - Others')
) AS v(screen_id, screen_name)
WHERE NOT EXISTS (
    SELECT 1 FROM sys_screen s
    WHERE s.screen_id = v.screen_id AND s.application_id = 'LEGALAPP'
);

-- ── 3. Menus ─────────────────────────────────────────────────────────
INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, screen_id, application_id, status, created_by, created_at)
SELECT v.menu_id, v.parent_menu_id, v.menu_name, v.menu_route,
       'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
       v.sort_order, v.screen_id, 'LEGALAPP', 'A', 'system', now()
FROM (VALUES
    ('LEGAL',          NULL::varchar,  'Legal Lease',   NULL::varchar,                   11, NULL::varchar),
    ('NEWAGREEMENT',   'LEGAL',        'New Agreement', NULL,                             1, NULL),
    ('PSMOLA',         'NEWAGREEMENT', 'PSM / OLA',     'legal-new-agreement.psm-ola',    1, 'PSMOLA'),
    ('ADDENDUM',       'NEWAGREEMENT', 'Addendum',      'legal-new-agreement.addendum',   2, 'ADDENDUM'),
    ('OTHERS',         'NEWAGREEMENT', 'Others',        'legal-new-agreement.others',     3, 'OTHERS'),
    ('LEGALAGREEMENT', 'LEGAL',        'Agreement FU',  'legal-agreement',                2, 'LEGALAGREEMENT')
) AS v(menu_id, parent_menu_id, menu_name, menu_route, sort_order, screen_id)
WHERE NOT EXISTS (
    SELECT 1 FROM sys_menu m
    WHERE m.menu_id = v.menu_id AND m.application_id = 'LEGALAPP'
);

-- ── 4. Roles ─────────────────────────────────────────────────────────
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT v.role_id, v.role_name, 'A', 'system', now()
FROM (VALUES
    ('LEGALAGREEMENT', 'Access Legal Agreement'),
    ('LEGALACCESS',    'Access Legal (PIC Legal)')
) AS v(role_id, role_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_role r WHERE r.role_id = v.role_id);

-- ── 5. Role -> Menu ──────────────────────────────────────────────────
-- LEGALAGREEMENT: leaf menus, parent_menu_id set
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'LEGALAGREEMENT', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.application_id = 'LEGALAPP'
  AND m.menu_id IN ('PSMOLA', 'ADDENDUM', 'OTHERS', 'LEGALAGREEMENT')
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'LEGALAGREEMENT' AND r.menu_id = m.menu_id
  );

-- LEGALACCESS: all 6 menus, parent_menu_id NULL (as on test2)
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'LEGALACCESS', m.menu_id, NULL, 'A', 'system', now()
FROM sys_menu m
WHERE m.application_id = 'LEGALAPP'
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'LEGALACCESS' AND r.menu_id = m.menu_id
  );

-- ── 6. Access rights (LEGALAGREEMENT x 4 screens x 4 actions) ────────
INSERT INTO sys_access_right (role_id, screen_id, application_id, access_name, access_right, status, created_by, created_at)
SELECT 'LEGALAGREEMENT', s.screen_id, 'LEGALAPP', a.access_name, true, 'A', 'system', now()
FROM sys_screen s
CROSS JOIN (VALUES ('VIEW'), ('CREATE'), ('EDIT'), ('DELETE')) AS a(access_name)
WHERE s.application_id = 'LEGALAPP'
  AND s.screen_id IN ('LEGALAGREEMENT', 'PSMOLA', 'ADDENDUM', 'OTHERS')
  AND NOT EXISTS (
      SELECT 1 FROM sys_access_right r
      WHERE r.role_id = 'LEGALAGREEMENT'
        AND r.screen_id = s.screen_id
        AND r.application_id = 'LEGALAPP'
        AND r.access_name = a.access_name
  );

-- ── Verification (expected: 1 / 4 / 6 / 2 roles / 10 / 16) ───────────
SELECT 'application' AS step, count(*) FROM sys_application WHERE application_id = 'LEGALAPP'
UNION ALL SELECT 'screen',       count(*) FROM sys_screen       WHERE application_id = 'LEGALAPP'
UNION ALL SELECT 'menu',         count(*) FROM sys_menu         WHERE application_id = 'LEGALAPP'
UNION ALL SELECT 'role',         count(*) FROM sys_role         WHERE role_id IN ('LEGALAGREEMENT','LEGALACCESS')
UNION ALL SELECT 'role_menu',    count(*) FROM sys_role_menu    WHERE role_id IN ('LEGALAGREEMENT','LEGALACCESS')
UNION ALL SELECT 'access_right', count(*) FROM sys_access_right WHERE application_id = 'LEGALAPP';

COMMIT;  -- change to ROLLBACK to dry-run
