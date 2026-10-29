-- Plaud panel: which Plaud account feeds the panel, per dashboard and per company (pgsql2, next to ms_dashboard_menu).
-- A user sees the recordings of the accounts mapped to THEIR companies on that dashboard (see App\Services\Dashboard\PlaudScope).
-- cpny_id '*' = every company; plaud_account_id 0 = every Plaud account (also future ones).
-- The account itself (ms_plaud_account) lives on pgsql5 with the Plaud recordings, so plaud_account_id is not a foreign key.
CREATE TABLE IF NOT EXISTS ms_dashboard_plaud (
    id               BIGSERIAL PRIMARY KEY,
    dash_cd          VARCHAR(30) NOT NULL,
    cpny_id          VARCHAR(30) NOT NULL,
    plaud_account_id BIGINT      NOT NULL,
    status           VARCHAR(1)  NOT NULL DEFAULT 'A',
    created_by       VARCHAR(50),
    created_at       TIMESTAMP,
    updated_by       VARCHAR(50),
    updated_at       TIMESTAMP,
    -- a company may have several Plaud accounts on one dashboard; the same pair twice makes no sense
    CONSTRAINT uq_ms_dashboard_plaud UNIQUE (dash_cd, cpny_id, plaud_account_id)
);

-- Databases created from the first version of this file had UNIQUE (dash_cd, cpny_id) = one account per company. Upgrade:
ALTER TABLE ms_dashboard_plaud DROP CONSTRAINT IF EXISTS uq_ms_dashboard_plaud;
ALTER TABLE ms_dashboard_plaud ADD CONSTRAINT uq_ms_dashboard_plaud UNIQUE (dash_cd, cpny_id, plaud_account_id);
