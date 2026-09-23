-- SPM-01 fix: DB-level uniqueness on the business keys the proc's
-- UPDATE-then-INSERT bootstrap pattern relies on. NULLS NOT DISTINCT is
-- required (PG15+) since NULL expired_date is a valid, distinct grouping
-- key (confirmed SPM-03) and plain UNIQUE would not dedupe two NULL rows.

ALTER TABLE ms_vpl_product_bal
    ADD CONSTRAINT uq_ms_vpl_product_bal_key
    UNIQUE NULLS NOT DISTINCT (year, cpnyid, product_id, expired_date, whs_id);

ALTER TABLE ms_vpl_product_detail
    ADD CONSTRAINT uq_ms_vpl_product_detail_key
    UNIQUE NULLS NOT DISTINCT (cpnyid, product_id, expired_date, whs_id);
