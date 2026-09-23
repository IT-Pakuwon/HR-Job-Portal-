# QA Plan: sp_process_vpl — Master Data Invariants

Generated 2026-08-10, branch `JP-backend`. Part 1 of 5 in the `sp_process_vpl` verification series (see also: `vpl_sp_qa_2_receive.md`, `vpl_sp_qa_3_transfer.md`, `vpl_sp_qa_4_usage.md`, `vpl_sp_qa_5_settlement.md`).

## Why this exists

The controller-level QA pass (`agenthub/vpl_voucher_qa_test_plan.md` / `vpl_voucher_qa_results.md`, executed 2026-08-07, 250/251 PASS) already fully covers Master/Receive/Transfer/Usage/Settlement at the app layer. **Do not re-run that pass or repeat its cases.**

Since then, the decision has been made to rely on `public.sp_process_vpl` (on connection `pgsql5`, database `db_das_test`) as the real write-point for `tr_vpl_ledger` and `ms_vpl_product_bal` for the upcoming report-ledger feature. Two versions were found on that DB: the full `sp_process_vpl` (2590 lines, covers VPR/VPT/VPU/VPS) and a stripped `sp_process_vpl_260805` copy (1481 lines, VPR/VPT only, missing VPU/VPS entirely). **The full `sp_process_vpl` is the one being adopted.**

**Critical fact confirmed 2026-08-10**: there is no DB trigger and no `pg_cron` job calling this procedure — checked directly against `pg_trigger` and for the `cron.job` table (doesn't exist on this instance). The only existing ledger row (`VPR266007`, 2026-07-26) was written by a human (`created_user = resianjani`), meaning it is invoked manually today. Every QA case in this series must `CALL sp_process_vpl(...)` explicitly — nothing will fire it for you.

## Scope of this file

This file is about master-data invariants the procedure body implicitly assumes but never checks — not a repeat of the already-passed `MST-*`/`WHS-*`/`WHD-*` controller cases. The proc does raw keyed SQL (`product_id` + `expired_date` + `whs_id` + `cpnyid` + `year`) with no visible FK/unique enforcement inside the procedure body itself.

## Cases

| ID | Case | Steps | Expected / what to check |
|---|---|---|---|
| SPM-01 | Uniqueness constraints on balance/detail keys | Check live schema: does `ms_vpl_product_bal(year, cpnyid, product_id, expired_date, whs_id)` and `ms_vpl_product_detail(cpnyid, product_id, expired_date, whs_id)` have a DB-level UNIQUE constraint? | If absent, the proc's `UPDATE ... IF NOT FOUND THEN INSERT` pattern is racy under concurrent `CALL`s and can silently create duplicate rows that split future `SUM`/`UPDATE` matches. Flag as a gap if missing. |
| SPM-02 | Deactivated product/warehouse still accumulates stock | Create synthetic product/warehouse, set `status='X'` (inactive) on the master row, then run a Receive+Submit through the proc against it | Proc has no `status='A'` check anywhere in its body — confirm inactive master records can still silently accrue ledger/balance rows. Flag as a gap if so (report data would then include inventory tied to "deleted" masters). |
| SPM-03 | NULL `expired_date` handled as a valid grouping key | Create a synthetic voucher product with NULL `expired_date`, run Receive Submit | Proc uses `IS NOT DISTINCT FROM` throughout — confirm balance/detail rows key correctly off NULL rather than being dropped or merged with unrelated rows. |
| SPM-04 | `p_cpny_id` correctness has no cross-check for VPT | Read the VPT branch of the proc body — VPR has an explicit `IF v_cpnyid IS DISTINCT FROM p_cpny_id THEN RAISE EXCEPTION` guard; confirm VPT's header lookup (`WHERE h.transfer_id = p_docid AND h.cpnyid = p_cpny_id`) achieves the same protection implicitly via the WHERE clause (wrong cpnyid → NOT FOUND → exception) rather than a dedicated mismatch message. Not a functional bug, but confirm the caller always has the correct `p_cpny_id` available before calling — trace how the (currently manual) caller determines this value. | Document whatever the current calling convention is, since nothing in the DB enforces it. |

## Setup / cleanup discipline

Use synthetic master data tagged `QAVPL-SP-2026-08-10` (product_name / description), matching the cleanup convention from the original QA pass — synthetic product+warehouse rows only, no seeding of `ms_vpl_product_detail` directly. Delete all synthetic rows (master + any ledger/balance/detail rows created while testing) at the end. Do not touch real data.

## Reporting

Report as a PASS/FAIL/GAP table in the same style as `vpl_voucher_qa_results.md`.
