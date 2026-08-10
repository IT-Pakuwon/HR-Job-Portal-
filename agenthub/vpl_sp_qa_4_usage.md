# QA Plan: sp_process_vpl — Usage (VPU)

Generated 2026-08-10, branch `JP-backend`. Part 4 of 5 in the `sp_process_vpl` verification series (see also: `vpl_sp_qa_1_master.md`, `vpl_sp_qa_2_receive.md`, `vpl_sp_qa_3_transfer.md`, `vpl_sp_qa_5_settlement.md`).

## Why this exists

Same rationale as the Receive/Transfer plans — `public.sp_process_vpl` on `pgsql5`/`db_das_test`, not the app controller, is what writes `tr_vpl_ledger`/`ms_vpl_product_bal` for Usage documents. This file verifies the proc's VPU branch specifically.

**Important — object identity check first**: the VPU branch exists **only** in the full `sp_process_vpl` (2590 lines). The `sp_process_vpl_260805` copy (1481 lines) dropped VPU entirely — calling it with doctype `'VPU'` there hits the fallback `RAISE EXCEPTION 'Doctype % belum tersedia'`. Before running any case, confirm you are calling `public.sp_process_vpl`, not `sp_process_vpl_260805`.

**No trigger or `pg_cron` job invokes this procedure** (confirmed 2026-08-10). Every case requires manually running:

```sql
CALL sp_process_vpl('VPU', :usage_id, :cpny_id, :activity, :user);
```

## Setup

Establish stock in a synthetic warehouse via a Receive doc processed through `vpl_sp_qa_2_receive.md`. Create 1-2 `tr_vpl_usage` + `tr_vpl_usage_detail` docs, each line with a `purpose_id` set, tagged `QAVPL-SP-2026-08-10`.

## Cases

| ID | Case | Expected |
|---|---|---|
| SPU-01 | Submit | Ledger row `qty` negative (stock leaving), `transaction_source='Usage'`; `ms_vpl_product_bal.periodNNout` bumped; `ms_vpl_product_detail.qty_available` decreased. Confirm `purpose_id` is carried onto the ledger row — VPU is the only branch where `purpose_id` is non-NULL (VPR/VPT always pass `NULL`). |
| SPU-02 | Qty exceeds available stock | Submit with `qty_usage` greater than `ms_vpl_product_detail.qty_available` | `Stock tidak ditemukan atau tidak mencukupi untuk Usage` raised, full rollback. |
| SPU-03 | Double Submit | Submit the same line twice without a Reject/Revise between | Blocked by the net-ledger check (`Line usage sudah pernah Submit dan belum dibalikkan`), same pattern as Receive. |
| SPU-04 | Reject/Revise after Submit | Stock returned to `ms_vpl_product_detail.qty_available`. |
| SPU-05 | Defensive re-insert on missing detail row | Delete (or otherwise cause absence of) the `ms_vpl_product_detail` row between Submit and Reject, then Reject | Confirm the proc's defensive branch (`INSERT ... qty_available = v_qty` when the UPDATE finds no row) fires instead of erroring — read the VPU branch's "Reject/Revise" `ms_vpl_product_detail` block for this fallback path. |
| SPU-06 | Sign-convention cross-check against Receive | Submit then fully Reject a Usage line, and separately Submit then fully Reject a Receive line for the same qty | Confirm both net to zero on `ms_vpl_product_bal` even though VPU uses signed `v_qty` directly for `periodNNin`/`periodNNout` while VPR wraps everything in `ABS(v_qty)` with the in/out column choice doing the sign work instead. Flag as a design-consistency note (not necessarily a bug) if a report built assuming one universal sign convention across doctypes would get this wrong. |

## Cleanup

Delete all synthetic usage docs/details and every ledger/balance/detail row created during this pass. Do not touch real data.

## Reporting

PASS/FAIL/GAP table per case, same style as `vpl_voucher_qa_results.md`.
