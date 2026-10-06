-- =====================================================================
-- New Agreement / Agreement FU — application, menus, screens, role, access
-- Run against the PGSQL2 connection (sys_* tables live there). Idempotent.
--
-- Menu tree (application LEGALAPP):
--   LEGAL  "Legal"
--     ├─ NEWAGREEMENT "New Agreement"   (group, no route)
--     │    ├─ PSMOLA    "PSM / OLA"
--     │    ├─ ADDENDUM  "Addendum"
--     │    └─ OTHERS    "Others"
--     └─ LEGALAGREEMENT "Agreement FU"   (existing /legal-agreement page, screen LEGALAGREEMENT)
--
-- Role: LEGALAGREEMENT, with VIEW/CREATE/EDIT/DELETE on every screen below
-- (routes gate on access:<SCREEN>,<ACTION> via AccessRightMiddleware).
-- LEGALAGREEMENT screen itself is the pre-existing /legal-agreement page.
-- =====================================================================

-- ── Application ──────────────────────────────────────────────────────
INSERT INTO sys_application (application_id, application_name, status, created_by, created_at)
SELECT 'LEGALAPP', 'Legal APP', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_application WHERE application_id = 'LEGALAPP');

-- ── Role ─────────────────────────────────────────────────────────────
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT 'LEGALAGREEMENT', 'Access Legal Agreement', 'A', 'system', now()
WHERE NOT EXISTS (SELECT 1 FROM sys_role WHERE role_id = 'LEGALAGREEMENT');

-- ── Screens ──────────────────────────────────────────────────────────
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

-- ── Menus ────────────────────────────────────────────────────────────
INSERT INTO sys_menu (menu_id, parent_menu_id, menu_name, menu_route, menu_icon, menu_sort_order, screen_id, application_id, status, created_by, created_at)
SELECT v.menu_id, v.parent_menu_id, v.menu_name, v.menu_route,
       'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
       v.sort_order, v.screen_id, 'LEGALAPP', 'A', 'system', now()
FROM (VALUES
    ('LEGAL',        NULL::varchar,   'Legal',         NULL::varchar,                    10, NULL::varchar),
    ('NEWAGREEMENT', 'LEGAL',         'New Agreement', NULL,                              1, NULL),
    ('PSMOLA',       'NEWAGREEMENT',  'PSM / OLA',     'legal-new-agreement.psm-ola',     1, 'PSMOLA'),
    ('ADDENDUM',     'NEWAGREEMENT',  'Addendum',      'legal-new-agreement.addendum',    2, 'ADDENDUM'),
    ('OTHERS',       'NEWAGREEMENT',  'Others',        'legal-new-agreement.others',      3, 'OTHERS'),
    ('LEGALAGREEMENT','LEGAL',         'Agreement FU',  'legal-agreement',                 2, 'LEGALAGREEMENT')
) AS v(menu_id, parent_menu_id, menu_name, menu_route, sort_order, screen_id)
WHERE NOT EXISTS (
    SELECT 1 FROM sys_menu m
    WHERE m.menu_id = v.menu_id AND m.application_id = 'LEGALAPP'
);

-- ── Role -> menu (leaves; parents are resolved by the sidebar provider) ──
INSERT INTO sys_role_menu (role_id, menu_id, parent_menu_id, status, created_by, created_at)
SELECT 'LEGALAGREEMENT', m.menu_id, m.parent_menu_id, 'A', 'system', now()
FROM sys_menu m
WHERE m.application_id = 'LEGALAPP'
  AND m.menu_id IN ('PSMOLA', 'ADDENDUM', 'OTHERS', 'LEGALAGREEMENT')
  AND NOT EXISTS (
      SELECT 1 FROM sys_role_menu r
      WHERE r.role_id = 'LEGALAGREEMENT' AND r.menu_id = m.menu_id
  );

-- ── Role access rights (role x screen x action) ──────────────────────
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
        AND r.access_name = a.access_name
  );

-- ── PIC pool roles (PGSQL2) ──────────────────────────────────────────
-- Marker roles only (no menus/screens): PSM/OLA's PIC Legal and PIC Leasing
-- pickers list the users holding these. Assign them to users in User Role.
INSERT INTO sys_role (role_id, role_name, status, created_by, created_at)
SELECT v.role_id, v.role_name, 'A', 'system', now()
FROM (VALUES
    ('LEGALACCESS',   'Access Legal (PIC Legal)'),
    ('LEASINGACCESS', 'Access Leasing (PIC Leasing)')
) AS v(role_id, role_name)
WHERE NOT EXISTS (SELECT 1 FROM sys_role r WHERE r.role_id = v.role_id);
