-- PostgreSQL, connection "pgsql" (same database as ms_sub_location).
-- Remembers "this tenant IS that Sub Location" when the names differ (Setup > Locations > From Tenant).
-- tenant_key = store name in lower case without spaces / punctuation; floor_code = LG, GF, 1, ... ('' = no floor).
CREATE TABLE IF NOT EXISTS ms_tenant_sub_location (
    id              bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    siteid          varchar(10)  NOT NULL,
    tenant_key      varchar(255) NOT NULL,
    floor_code      varchar(50)  NOT NULL DEFAULT '',
    sub_location_id varchar(20)  NOT NULL,
    created_by      varchar(50),
    created_at      timestamp without time zone,
    CONSTRAINT uq_ms_tenant_sub_location UNIQUE (siteid, tenant_key, floor_code)
);
