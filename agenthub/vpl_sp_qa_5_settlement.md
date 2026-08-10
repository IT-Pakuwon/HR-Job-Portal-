# QA Plan: sp_process_vpl — Settlement (VPS)

Generated 2026-08-10, branch `JP-backend`. Part 5 of 5 in the `sp_process_vpl` verification series (see also: `vpl_sp_qa_1_master.md`, `vpl_sp_qa_2_receive.md`, `vpl_sp_qa_3_transfer.md`, `vpl_sp_qa_4_usage.md`).

## Why this exists

Same rationale as the other plans in this series — `public.sp_process_vpl` on `pgsql5`/`db_das_test` is the real write-point for `tr_vpl_ledger`/`ms_vpl_product_bal`, not the app controller. This file verifies the proc's VPS branch specifically, which is the most logically involved of the four doctypes (it computes a running "remain" quantity rather than a flat qty).

**Important — object identity check first**: like VPU, the VPS branch exists **only** in the full `sp_process_vpl` (2590 lines). `sp_process_vpl_260805` dropped it entirely. Confirm you are calling `public.sp_process_vpl` before running any case.

**No trigger or `pg_cron` job invokes this procedure** (confirmed 2026-08-10). Every case requires manually running:

```sql
CALL sp_process_vpl('VPS', :settlement_id, :cpny_id, :activity, :user);
```

## Setup

One completed synthetic Usage doc (from `vpl_sp_qa_4_usage.md`, or a fresh one) with a known `qty_usage` per line, so Settlement has something real to settle against. Create 1-2 `tr_vpl_settlement` + `tr_vpl_settlement_detail` docs tagged `QAVPL-SP-2026-08-10`, varying `qty_settlement` relative to `qty_usage` across cases (full, partial, zero-remain).

## Cases

| ID | Case | Expected |
|---|---|---|
| SPS-01 | Partial settlement (`qty_settlement < qty_usage`) | `tr_vpl_settlement_detail.qty_remain` is written back as `qty_usage - qty_settlement`. **Read the proc body carefully here**: on Submit, `v_qty := r_detail.qty_remain_calc` — the ledger/balance effect is driven by the REMAIN amount, not `qty_settlement` itself. Confirm the resulting ledger qty matches the remain, not the settled amount. |
| SPS-02 | Full settlement (`qty_settlement == qty_usage`, remain = 0) | The `IF v_qty = 0 THEN CONTINUE` guard skips ledger/balance writes for that line entirely. Confirm this doesn't get miscounted by the `v_row_count` check at the end (i.e. a document consisting only of fully-settled lines shouldn't incorrectly raise "tidak mempunyai detail"). |
| SPS-03 | `qty_settlement > qty_usage` | `Qty settlement tidak boleh lebih besar dari qty usage` raised before any writes. |
| SPS-04 | `qty_settlement < 0` | `Qty settlement tidak boleh minus` raised. |
| SPS-05 | Reject/Revise after a non-zero-remain Submit | Reversal math (`v_qty` negated) restores balance/detail correctly. |
| SPS-06 | Multiple Settlement docs against the same `usage_id`, each further reducing remain | Confirm no cross-document double-counting — the net-ledger guard keys on the *settlement* doc's own `refnbr`/`linenbr`, not the underlying `usage_id`, so verify sequential settlements against one usage line behave correctly and don't let the same remain get consumed twice. |

## Cleanup

Delete all synthetic settlement docs/details and every ledger/balance/detail row created during this pass (and the supporting Usage doc if created fresh for this file). Do not touch real data.

## Reporting

PASS/FAIL/GAP table per case, same style as `vpl_voucher_qa_results.md`.
