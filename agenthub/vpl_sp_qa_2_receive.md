# QA Plan: sp_process_vpl — Receive (VPR)

Generated 2026-08-10, branch `JP-backend`. Part 2 of 5 in the `sp_process_vpl` verification series (see also: `vpl_sp_qa_1_master.md`, `vpl_sp_qa_3_transfer.md`, `vpl_sp_qa_4_usage.md`, `vpl_sp_qa_5_settlement.md`).

## Why this exists

The controller-level QA pass (`agenthub/vpl_voucher_qa_test_plan.md` / `vpl_voucher_qa_results.md`, 2026-08-07, 250/251 PASS) already confirmed Receive's controller never writes `tr_vpl_ledger`/`ms_vpl_product_bal` itself (ARC-01/ARC-02) — that write is delegated entirely to `public.sp_process_vpl` on connection `pgsql5` / database `db_das_test`. This file verifies the proc's own VPR branch directly, since that behavior has never actually been exercised by QA before now.

**No trigger or `pg_cron` job invokes this procedure** (confirmed 2026-08-10 against `pg_trigger` and the absence of a `cron.job` table). Every case below requires manually running:

```sql
CALL sp_process_vpl('VPR', :receive_id, :cpny_id, :activity, :user);
```

`:activity` ∈ `'Submit' | 'Reject' | 'Revise'` (case-insensitive — the proc normalizes via `UPPER(TRIM(...))`).

## Setup

Create 1-2 synthetic `tr_vpl_receive` + `tr_vpl_receive_detail` docs (2+ lines each), tagged `QAVPL-SP-2026-08-10`. Include at least one pair of lines sharing the same `product_id`/`expired_date`/`whs_id` combination on the same document, to exercise the `linenbr`-based locking the proc's own comments call out as necessary (`tr_vpl_ledger` keys on `linenbr` specifically because product/expiry/warehouse can repeat within a document).

## Cases

| ID | Case | Expected |
|---|---|---|
| SPR-01 | Submit, single line | One `tr_vpl_ledger` row, `qty = +qty_receive`, `status='A'`, `transaction_source='Receive'`; `ms_vpl_product_bal` `periodNNin` (N = receive_date's month) increases by the qty, row created if missing; `ms_vpl_product_detail.qty_available` increases by the same amount, row created if missing. |
| SPR-02 | Submit twice, no Reject/Revise between | Second call raises `Line sudah pernah Submit dan belum dibalikkan` (net-ledger check via `SUM(qty) WHERE status='A'`). Confirm zero side effects from the failed call. |
| SPR-03 | Rollback atomicity on a mid-loop failure | With a multi-line document, force one line to fail validation (e.g. blank `whs_id` on line 2) while line 1 is otherwise valid — confirm the `CALL` fails as a whole and line 1's ledger/balance/detail changes are rolled back too, not partially committed. |
| SPR-04 | Reject after Submit | Ledger row `qty = -qty_receive`; `periodNNout` bumped; `ms_vpl_product_detail.qty_available` decremented back to pre-Submit value. |
| SPR-05 | Reject twice | Second Reject raises `Penerimaan sudah tidak aktif` (net ledger already ≤ 0). |
| SPR-06 | Revise after Submit | Same numeric effect as Reject, but confirm the ledger row's `transaction_activity` is literally `'Revise'`, not `'Reject'` — needed if the report distinguishes the two. |
| SPR-07 | Stock forced negative | Reject/Revise a line whose stock has already been partly consumed by a separate (synthetic) Usage doc, so the reversal would drive `qty_available` below 0 | `Stock menjadi minus` raised, full rollback of that `CALL`. |
| SPR-08 | Parameter validation | Call with blank `p_docid`, blank `p_cpny_id`, and an unrecognized `p_activity` (e.g. `'Approve'`) as three separate calls | Each raises its specific `RAISE EXCEPTION` message with no side effects. |
| SPR-09 | Month bucketing correctness | Backdate a synthetic `receive_date` into a different month (e.g. month 3) and Submit | Balance lands in `period03in`, not the current calendar month. |

## Cleanup

Delete all synthetic docs/details and every `tr_vpl_ledger` / `ms_vpl_product_bal` / `ms_vpl_product_detail` row created during this pass (search by the `QAVPL-SP-2026-08-10` tag and by the synthetic `product_id`/`whs_id` values). Do not touch real data.

## Reporting

PASS/FAIL/GAP table per case, same style as `vpl_voucher_qa_results.md`.
