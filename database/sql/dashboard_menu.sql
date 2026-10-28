-- =====================================================================
-- Dashboard "Menu" tile bar — configurable per dashboard.
-- Run against the PGSQL2 connection (same DB as sys_screen). Idempotent.
--   ms_dashboard_menu     : catalog of tiles (defined once, reused anywhere)
--   ms_dashboard_menu_map : which tiles each dashboard (sys_screen.screen_id) shows, in order
-- A tile is an external link (external_url) or opens an in-page panel (panel_key =
-- a key of DashboardMenu::templatePanels(), code in components/panel/<key>.blade.php).
-- Whoever is assigned to the dashboard sees the tile; role_id is no longer written.
-- =====================================================================
CREATE TABLE IF NOT EXISTS ms_dashboard_menu (
    menu_key    VARCHAR(30)  PRIMARY KEY,
    menu_name   VARCHAR(100) NOT NULL,
    menu_desc   VARCHAR(255),
    icon_path   TEXT,
    route_name  VARCHAR(100),
    external_url VARCHAR(1000),
    panel_key   VARCHAR(30),
    role_id     VARCHAR(50),
    status      VARCHAR(5) DEFAULT 'A',
    created_by  VARCHAR(50),
    created_at  TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    updated_by  VARCHAR(50),
    updated_at  TIMESTAMP WITHOUT TIME ZONE
);

CREATE TABLE IF NOT EXISTS ms_dashboard_menu_map (
    dash_cd     VARCHAR(30) NOT NULL,
    menu_key    VARCHAR(30) NOT NULL REFERENCES ms_dashboard_menu (menu_key),
    sort_no     INT NOT NULL DEFAULT 0,
    status      VARCHAR(5) DEFAULT 'A',
    created_by  VARCHAR(50),
    created_at  TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    PRIMARY KEY (dash_cd, menu_key)
);

INSERT INTO ms_dashboard_menu (menu_key, menu_name, menu_desc, icon_path, route_name, panel_key, created_by)
VALUES
 ('APPROVAL', 'Approval Dashboard', 'Documents waiting for your approval',
  'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  NULL, 'approval', 'system')
ON CONFLICT (menu_key) DO NOTHING;
-- ---------------------------------------------------------------------
-- v2: a map row may also point straight at a sidebar item (sys_menu.menu_id)
-- instead of a catalog tile. Name/icon/route come from sys_menu and the tile
-- is shown only to users whose roles grant that menu (sys_role_menu) —
-- exactly the sidebar's own visibility rule. Exactly one of menu_key /
-- sys_menu_id is set per row. menu_desc is an optional per-dashboard subtitle.
-- ---------------------------------------------------------------------
ALTER TABLE ms_dashboard_menu_map ADD COLUMN IF NOT EXISTS id BIGSERIAL;
ALTER TABLE ms_dashboard_menu_map ADD COLUMN IF NOT EXISTS sys_menu_id VARCHAR(100);
ALTER TABLE ms_dashboard_menu_map ADD COLUMN IF NOT EXISTS menu_desc VARCHAR(255);
ALTER TABLE ms_dashboard_menu_map DROP CONSTRAINT IF EXISTS ms_dashboard_menu_map_pkey;
ALTER TABLE ms_dashboard_menu_map ADD PRIMARY KEY (id);
ALTER TABLE ms_dashboard_menu_map ALTER COLUMN menu_key DROP NOT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS ux_dash_menu_map_key ON ms_dashboard_menu_map (dash_cd, menu_key) WHERE menu_key IS NOT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS ux_dash_menu_map_sys ON ms_dashboard_menu_map (dash_cd, sys_menu_id) WHERE sys_menu_id IS NOT NULL;
DO $$ BEGIN
    ALTER TABLE ms_dashboard_menu_map ADD CONSTRAINT ck_dash_menu_map_one CHECK ((menu_key IS NULL) <> (sys_menu_id IS NULL));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- v3: custom tiles may open an external https URL (new tab) instead of a route/panel.
-- (also in the CREATE TABLE above for fresh installs; this line upgrades databases created before v3)
ALTER TABLE ms_dashboard_menu ADD COLUMN IF NOT EXISTS external_url VARCHAR(1000);

-- v4: DASHOM's Mailbox tile is the real sidebar item (sys_menu 'MAILBOX'), not a custom tile.
INSERT INTO ms_dashboard_menu_map (dash_cd, sys_menu_id, menu_desc, sort_no, created_by)
SELECT 'DASHOM', 'MAILBOX', 'Read and send email', 1, 'system'
WHERE NOT EXISTS (SELECT 1 FROM ms_dashboard_menu_map WHERE dash_cd = 'DASHOM' AND sys_menu_id = 'MAILBOX');
INSERT INTO ms_dashboard_menu_map (dash_cd, menu_key, sort_no, created_by)
SELECT 'DASHOM', 'APPROVAL', 2, 'system'
WHERE NOT EXISTS (SELECT 1 FROM ms_dashboard_menu_map WHERE dash_cd = 'DASHOM' AND menu_key = 'APPROVAL');
DELETE FROM ms_dashboard_menu_map WHERE menu_key = 'MAILBOX';
DELETE FROM ms_dashboard_menu WHERE menu_key = 'MAILBOX';

-- v5: every dashboard owns one in-page panel (its main tabbed card). Assigning that panel's tile to the
-- dashboard keeps the card hidden until the tile (or a KPI card) is clicked. 'APPROVAL' (panel 'approval')
-- already exists and serves both DASHAPPROVAL and the embedded panel on DASHOM.
INSERT INTO ms_dashboard_menu (menu_key, menu_name, menu_desc, icon_path, route_name, panel_key, created_by)
SELECT v.menu_key, v.menu_name, v.menu_desc,
       'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
       NULL, v.panel_key, 'system'
FROM (VALUES
 ('PNL_IT',        'IT Dashboard',          'Ticketing, access requests and recommendations', 'it'),
 ('PNL_WH',        'Warehouse Dashboard',   'Warehouse lists and approvals',                  'warehouse'),
 ('PNL_OPR',       'Operational Dashboard', 'Operational lists and approvals',                'operational'),
 ('PNL_CC',        'Cost Control Dashboard','Cost control lists and approvals',               'costcontrol'),
 ('PNL_PURCH',     'Purchasing Dashboard',  'Purchasing lists and approvals',                 'purchasing'),
 ('PNL_HR',        'HR Dashboard',          'HR lists and approvals',                         'hr'),
 ('PNL_GA',        'GA Dashboard',          'General affairs lists and approvals',            'ga'),
 ('PNL_VPCOLL',    'VP Collection Dashboard','VP collection lists and approvals',             'vpcollection'),
 ('PNL_VPPRMTN',   'VP Promotion Dashboard','VP promotion lists and approvals',               'vppromotion'),
 ('PNL_VPLOYALTY', 'VP Loyalty Dashboard',  'VP loyalty lists and approvals',                 'vployalty'),
 ('PNL_FIN',       'Finance Dashboard',     'Finance lists and approvals',                    'finance'),
 ('PNL_TREAS',     'Treasury Dashboard',    'Treasury lists and approvals',                   'treasury'),
 ('PNL_CORPTEK',   'Corporate Teknik Dashboard','Corporate teknik lists and approvals',       'corporateteknik'),
 ('PNL_RECRUIT',   'Recruitment Dashboard', 'Recruitment lists and approvals',                'recruitment')
) AS v(menu_key, menu_name, menu_desc, panel_key)
ON CONFLICT (menu_key) DO NOTHING;

-- v6: a panel tile may be "open by default": that panel is already open whenever the dashboard loads.
ALTER TABLE ms_dashboard_menu_map ADD COLUMN IF NOT EXISTS is_default BOOLEAN NOT NULL DEFAULT FALSE;

-- v7: a user can pin a panel tile to open it by default for themself. A row overrides the admin default
-- (ms_dashboard_menu_map.is_default) for that user; open_default = FALSE means "don't open it for me".
CREATE TABLE IF NOT EXISTS ms_dashboard_menu_user_pref (
    username     VARCHAR(50) NOT NULL,
    dash_cd      VARCHAR(30) NOT NULL,
    panel_key    VARCHAR(30) NOT NULL,
    open_default BOOLEAN NOT NULL,
    updated_at   TIMESTAMP WITHOUT TIME ZONE DEFAULT now(),
    PRIMARY KEY (username, dash_cd, panel_key)
);
