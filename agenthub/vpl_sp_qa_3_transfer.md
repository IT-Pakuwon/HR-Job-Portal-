# QA Plan: sp_process_vpl — Transfer (VPT)

Generated 2026-08-10, branch `JP-backend`. Part 3 of 5 in the `sp_process_vpl` verification series (see also: `vpl_sp_qa_1_master.md`, `vpl_sp_qa_2_receive.md`, `vpl_sp_qa_4_usage.md`, `vpl_sp_qa_5_settlement.md`).

## Why this exists

Same rationale as the Receive plan: the controller never writes ledger/balance itself (confirmed by the 2026-08-07 QA pass) — `public.sp_process_vpl` on `pgsql5`/`db_das_test` does. This file verifies the proc's VPT branch specifically.

**No trigger or `pg_cron` job invokes this procedure** (confirmed 2026-08-10). Every case requires manually running:

```sql
CALL sp_process_vpl('VPT', :transfer_id, :cpny_id, :activity, :user);
```

## Why VPT needs its own careful pass

VPT is structurally different from every other branch: it's the **only** one using dynamic SQL (`EXECUTE FORMAT('... period%1$sin ... period%1$sout ...', LPAD(v_month::TEXT,2,'0')) USING ...`) to update the month column, instead of the static `CASE WHEN v_month = N THEN ...` pattern used in VPR/VPU/VPS. It's also the only branch that touches **two** warehouses (and therefore two `ms_vpl_product_bal`/`ms_vpl_product_detail` rows) per call. Not a security concern (`v_month` comes from `EXTRACT(MONTH FROM date)`, never user input) but it is the most structurally fragile part of the whole procedure and deserves more scrutiny than a single happy-path test.

## Setup

Two synthetic warehouses (source + destination). Establish real stock in the source warehouse via a synthetic Receive doc processed through the Receive QA pass (`vpl_sp_qa_2_receive.md`) — Transfer needs real stock to move, not seeded balances. Create 1-2 `tr_vpl_transfer` + `tr_vpl_transfer_detail` docs, tagged `QAVPL-SP-2026-08-10`.

## Cases

| ID | Case | Expected |
|---|---|---|
| SPT-01 | Submit | Two `tr_vpl_ledger` rows: `Transfer Out` on `from_whs_id` with negative qty, `Transfer In` on `to_whs_id` with positive qty. `ms_vpl_product_bal` rows for **both** warehouses period-bucketed correctly. |
| SPT-02 | Month-column substitution across the year | Repeat SPT-01 with `transfer_date` in at least 3 different months (e.g. Jan, Jun, Dec) | Confirm the dynamic `EXECUTE FORMAT` correctly targets `period01`, `period06`, `period12` respectively for both warehouses — this is the one code path where a typo in the format string or an off-by-one in `LPAD` would silently write to the wrong column. |
| SPT-03 | Destination already has an active ledger entry for the same key | Attempt Submit when `to_whs_id` already has an unreversed ledger row for the same product/expiry/linenbr | `Warehouse tujuan line transfer sudah memiliki ledger aktif` raised. |
| SPT-04 | Insufficient stock at source | Submit a transfer qty greater than what's available at `from_whs_id` | `Stock warehouse asal tidak mencukupi` raised, full rollback (both warehouses, both ledger inserts). |
| SPT-05 | Reject/Revise after Submit | Reverses both warehouses' ledger, balance, and detail rows — confirm stock returns exactly to source and is fully removed from destination, no residual at destination. |
| SPT-06 | Same warehouse as source and destination | `from_whs_id == to_whs_id` | Explicit guard rejects (`Warehouse asal dan tujuan tidak boleh sama`) before any writes. |
| SPT-07 | Concurrent-call race on the two `INSERT ... WHERE NOT EXISTS` balance/detail bootstrap statements (~proc lines 1106-1222) | Issue two concurrent `CALL`s for the same new product/warehouse/year key (different sessions) | Confirm `FOR UPDATE` locking on the header row is sufficient to serialize these, or document if a duplicate-row race is possible — this is the branch most exposed to it since it bootstraps rows for two warehouses per call. |

## Cleanup

Delete all synthetic transfer docs/details and every ledger/balance/detail row created (both warehouses) during this pass. Do not touch real data.

## Reporting

PASS/FAIL/GAP table per case, same style as `vpl_voucher_qa_results.md`.
