# QA Results: sp_process_vpl — Settlement (VPS)

Executed 2026-08-10 against `public.sp_process_vpl` on connection `pgsql5` / database `db_das_test`, per `vpl_sp_qa_5_settlement.md`. Confirmed calling the full 2590-line `sp_process_vpl` (75060 chars source), not `sp_process_vpl_260805` (43046 chars, VPS absent).

## Setup

No pre-existing rows in `tr_vpl_usage`/`tr_vpl_settlement` on this DB, so master-data checks (parts 1-4 of the series) were skipped as prerequisites — confirmed the VPS branch itself never joins `tr_vpl_usage`/`tr_vpl_usage_detail` at all; it reads only `tr_vpl_settlement`/`tr_vpl_settlement_detail`, trusting `d.qty_usage` as entered on the settlement detail row. Built one synthetic Usage doc (`QAVPLSPU01`, 6 lines) for setup fidelity plus 7 synthetic Settlement docs, `cpnyid='AW'`, `whs_id='WHCOLLECTION'`, products `QAVPLSP01`-`QAVPLSP06`, all tagged `QAVPL-SP-2026-08-10`.

## Results

| ID | Case | Result | Notes |
|---|---|---|---|
| SPS-01 | Partial settlement (qty_settlement 40 < qty_usage 100) | PASS | `qty_remain` written back as 60. Ledger `qty=60` (the remain), not 40 (the settled amount) — confirmed `v_qty := r_detail.qty_remain_calc` drives the ledger/balance effect as documented. `ms_vpl_product_bal.period08in=60`, `ms_vpl_product_detail.qty_available=60`. |
| SPS-02 | Full settlement (qty_settlement 50 == qty_usage 50, remain=0) | PASS | `v_row_count` increments *before* the `IF v_qty = 0 THEN CONTINUE` guard, so a document consisting only of fully-settled lines does not trigger `Dokumen settlement % tidak mempunyai detail` — Submit returned OK with zero ledger/balance/detail writes for the line, exactly as the guard is positioned in source (line ~2279 vs ~2384). |
| SPS-03 | qty_settlement (40) > qty_usage (30) | PASS | Raised `Qty settlement tidak boleh lebih besar dari qty usage. Dokumen QAVPLSPS03, line 3, qty usage 30, qty settlement 40.` before any writes — confirmed zero ledger/balance/detail rows created for that product. |
| SPS-04 | qty_settlement < 0 (-5) | PASS | Raised `Qty settlement tidak boleh minus. Dokumen QAVPLSPS04, line 4, qty settlement -5.` before any writes — confirmed zero side effects. |
| SPS-05 | Reject after non-zero-remain Submit | PASS | Submit: ledger `qty=+40`, `period08in=40`, `qty_available=40`. Reject: second ledger row `qty=-40`, `period08out=40`, `qty_available` back to 0. Reversal math nets to zero correctly. |
| SPS-06 | Two settlement docs against the same `usage_id`/`linenbr`, second doc's `qty_usage` not reduced to reflect the first doc's remain | **GAP confirmed** | Doc A (qty_usage=90, settle 30) → remain=60, ledger `+60`. Doc B, same `usage_id`/`linenbr`/product, **still carrying `qty_usage=90`** (settle 15 more) → remain=75, ledger `+75`. Both `CALL`s succeeded — total credited back to stock: `60+75=135` units against a usage line that only ever consumed 90. The net-ledger check keys on `refnbr = p_docid` (each settlement doc's own docid), never on `usage_id`, so nothing in the procedure stops a second (or Nth) settlement document from independently over-crediting the same usage line if its `qty_usage` field isn't kept in sync with prior settlements. This is purely an app/caller responsibility today — no DB-level guard exists. |

**6/6 cases behaved as functionally expected; 1 of those (SPS-06) confirms a real gap** (over-crediting via stale `qty_usage` across multiple settlement docs against one usage line), consistent with what the test plan predicted going in.

## Cleanup

All synthetic rows removed and verified: `tr_vpl_ledger` (5), `ms_vpl_product_bal` (3), `ms_vpl_product_detail` (3), `tr_vpl_settlement_detail` (7), `tr_vpl_settlement` (7), `tr_vpl_usage_detail` (6), `tr_vpl_usage` (1). Post-cleanup count of remaining synthetic rows across all tables: **0**. No real data touched.

## Fix applied (2026-08-10, same session)

Deployed a `CREATE OR REPLACE PROCEDURE` patch to `sp_process_vpl` on `pgsql5`/`db_das_test` closing the SPS-06 gap. In the VPS branch, a Submit now raises `Usage % line % sudah mempunyai settlement aktif pada dokumen lain. Reject/Revise dokumen tersebut terlebih dahulu sebelum membuat settlement baru untuk line yang sama.` if any *other* settlement document still has a live (non-reversed) ledger credit for the same `usage_id` + `linenbr` — mirroring the same-document net-ledger pattern already used by VPR/VPT/VPU, just extended across documents. No table/schema changes; the fix is contained entirely inside the procedure body (two lines added to `DECLARE` were later removed as unused after the check was simplified from a cumulative-sum approach to an existence check).

Re-verified after deploy, all against fresh synthetic data:
- SPS-01, SPS-02, SPS-03, SPS-04, SPS-05: unchanged, still PASS (no regressions).
- SPS-06 doc B: now correctly **blocked** while doc A's credit is active.
- SPS-06 doc B after Rejecting doc A first: **succeeds** (`qty_available=75` net of the true remaining 90-15, since doc A's credit of 60 was fully reversed first) — confirms the legitimate reject-then-resettle workflow still works.

The patched source is version-controlled for the first time at `database/sql/sp_process_vpl.sql` (this procedure previously lived only on the DB with no tracked copy). Keep that file in sync by hand after any future live change until proper migrations exist for it.

**Not changed**: `VplSettlementController::store()` (app/Http/Controllers/VplSettlementController.php:307) still seeds a new settlement detail's `qty_usage` straight from `tr_vpl_usage_detail.qty_usage` rather than the live remaining balance after prior settlements. The controller's own guard (line 235: blocks a second *active* settlement per `usage_id`) prevents the exact SPS-06 shape today, but a stale `qty_usage` could still surface once a settlement is completed and a follow-up settlement is created for the balance. The DB-level fix above is now the real backstop regardless of caller; the controller change is a separate, optional follow-up if the sequential-settlement UX is meant to be first-class.
