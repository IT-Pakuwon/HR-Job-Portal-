# QA Results: sp_process_vpl — Usage (VPU)

Executed 2026-08-10 against `public.sp_process_vpl` on `pgsql5` / `db_das_test`. Object identity confirmed before running any case: `sp_process_vpl` = 2590 lines (full, has VPU branch), `sp_process_vpl_260805` = 1481 lines (no VPU) — all calls below used `sp_process_vpl` explicitly.

Setup: synthetic stock established via `CALL sp_process_vpl('VPR', ..., 'Submit', ...)` on isolated product/warehouse pairs (`QVU0001..QVU0004` / `QVUWH1..QVUWH4`, company `AW`), kept in their own namespace deliberately so this pass wouldn't collide with the in-progress `vpl_sp_qa_2_receive.md` leftovers (`QAVPLSPR*`) found on the same tables at start. Docs tagged `QAVPL-SP-2026-08-10` in the remark field.

| ID | Result | Expected | Notes |
|---|---|---|---|
| SPU-01 | PASS | Ledger qty negative, `transaction_source='Usage'`, balance/detail updated, `purpose_id` carried onto ledger | qty_available 200→170; ledger row `qty=-30, purpose_id='TESTPURPOSE1', transaction_activity='Submit'`; `period08in=200` (from setup receive), `period08out=30`. Confirms VPU is the only branch writing non-NULL `purpose_id` — cross-checked against the pre-existing `QAVPLSPR01` ledger row (Receive) which has `purpose_id=null`. |
| SPU-02 | PASS | `Stock tidak ditemukan atau tidak mencukupi untuk Usage` raised, full rollback | Submitted qty 99999 against 170 available → exact exception message raised; qty_available unchanged at 170; zero ledger rows created for the doc. |
| SPU-03 | PASS | Second Submit blocked by net-ledger check | 1st submit OK (available 170→160). 2nd submit on same doc/line raised `Line usage sudah pernah Submit dan belum dibalikkan. ... net ledger -10`; available stayed 160; only one ledger row exists. |
| SPU-04 | PASS | Reject returns stock to pre-Submit value | Submit: available 160→140, ledger `qty=-20`. Reject: available 140→160 (back to pre-Submit), ledger `qty=+20, transaction_activity='Reject'`. |
| SPU-05 | PASS | Defensive `INSERT` fires when `ms_vpl_product_detail` row is missing at Reject time | Submit (available 50→35), then row deleted directly from `ms_vpl_product_detail`, then Reject. The `UPDATE` found no row → defensive branch inserted a new row with `qty_available=15` (a fresh `id`, confirming `INSERT` not `UPDATE` fired) — exactly `v_qty`, the reversed amount. |
| SPU-06 | PASS (design-consistency note, not a bug) | Both doctypes net to zero on `ms_vpl_product_bal` after a matched Submit+Reject pair | Usage (qty 20, `QVU0003`/`QVUWH3`): `period08in` 50→50→70 (Submit doesn't touch `in`, Reject added 20), `period08out` 0→20→20 (Submit added 20, Reject doesn't touch `out`); net delta over the pair = 0. Receive (qty 20, `QVU0004`/`QVUWH4`): `period08in` 50→70→70 (Submit added 20), `period08out` 0→0→20 (Reject added 20); net delta = 0 too. Ledger signs are consistent across doctypes regardless of internal implementation (`+`=stock-increasing event, `-`=stock-decreasing, for both VPR and VPU): `Usage Submit=-20/Reject=+20`, `Receive Submit=+20/Reject=-20`. The only real difference is internal: VPR flips the sign into `v_qty` itself before the ledger insert and then wraps it back in `ABS(v_qty)` for the balance columns; VPU keeps `v_qty` unsigned throughout (via `ABS()` at assignment time) and applies the sign only inline in the ledger `INSERT`'s `CASE`. Net observable behavior is identical — flagging as the design-consistency note the plan asked for, since a report author who assumed one single sign-convention *pattern* (not outcome) across doctypes would be reading the wrong column literally, even though the numbers come out right either way. |

## Incidental observation (not a proc bug)

Mid-run, the `QAVPLSPR*` synthetic Receive rows left over from a prior/concurrent `vpl_sp_qa_2_receive.md` pass disappeared from the DB between two of our queries — not caused by this run (our deletes were scoped to `QVUS%`/`QVUSETUP%` only, verified by row-count diff). Consistent with another session cleaning up Part 2 concurrently on the same shared `db_das_test` instance. Worth knowing if Part 2's own results are being compiled around the same time — its synthetic data no longer exists to inspect.

## Cleanup

All synthetic rows deleted post-run: `tr_vpl_usage`/`tr_vpl_usage_detail` (6 docs/lines, `QVUS%`), `tr_vpl_receive`/`tr_vpl_receive_detail` (5 docs/lines, `QVUSETUP%`), `tr_vpl_ledger` (14 rows), `ms_vpl_product_bal` (4 rows), `ms_vpl_product_detail` (4 rows) — all under the `QVU%` product namespace. Verified zero rows remain post-cleanup. No real data touched.

## Summary

6/6 PASS. No functional gaps found in the VPU branch itself — every guard (stock-sufficiency check, double-submit block, reject/revise reversal, defensive re-insert) fired exactly as the proc body and plan predicted. SPU-06 surfaces a documentation-worthy internal inconsistency in *how* the sign math is written (not in what it produces) between VPR and VPU.
