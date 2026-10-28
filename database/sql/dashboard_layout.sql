-- Dashboard Setup: how each dashboard's top area is arranged (Main Menu, My Favourites, panel area, Your Progress, To Do List).
-- One row per dashboard (dash_cd = sys_screen.screen_id of application DASHBOARD). No row = the standard arrangement.
-- layout = {"columns":[{"span":9,"blocks":[{"id":"menu","v":true},{"id":"favourites","v":true},{"id":"panels","v":true}]},
--                      {"span":3,"blocks":[{"id":"progress","v":true},{"id":"todo","v":true}]}]}
-- spans of the columns add up to 12; v = shown. Connection: pgsql2 (same as ms_dashboard_menu).
CREATE TABLE IF NOT EXISTS ms_dashboard_layout (
    dash_cd     varchar(30) PRIMARY KEY,
    layout      jsonb       NOT NULL,
    updated_by  varchar(50),
    updated_at  timestamp   NOT NULL DEFAULT now()
);
