# QA Results: sp_process_vpl — Receive (VPR)

Executed 2026-08-10, branch `JP-backend`, against `pgsql5` / `db_das_test`. Plan: `vpl_sp_qa_2_receive.md`. 9/9 PASS.

Synthetic fixtures: product `QAVPLSP001` (cpnyid `AW`), warehouse `QAVPLSPWH1` (cpnyid `AW`, vp_type `V`), receive docs `QAVPLSPR01`/`03`/`06`/`07`/`09`, all tagged `QAVPL-SP-2026-08-10`. All synthetic rows (master + ledger/bal/detail created during testing) deleted after the run; verified zero residual rows post-cleanup.

| ID | Result | Expected | Notes |
|---|---|---|---|
| SPR-01 | PASS | Ledger `+qty`, status A, source Receive; bal `periodNNin` created; detail `qty_available` created | Submit qty=100 → ledger qty=100 status=A source=Receive; bal period08in=100 (row created); detail qty_available=100 (row created) |
| SPR-02 | PASS | Second Submit raises "sudah pernah Submit", zero side effects | Raised `Line sudah pernah Submit dan belum dibalikkan... net ledger 100.`; ledger count 1→1, bal period08in 100→100, detail qty 100→100 (unchanged) |
| SPR-03 | PASS | Mid-loop failure rolls back whole CALL, not partial | 2-line doc, line2 blank whs_id → raised `Warehouse ID kosong pada dokumen QAVPLSPR03, line 2.`; 0 ledger rows for the doc; shared bal/detail rows (same product/whs as SPR-01) unchanged at 100/100 — line 1's would-be insert/update was fully rolled back |
| SPR-04 | PASS | Reject: ledger `qty=-qty`, `periodNNout` bumped, detail decremented to pre-Submit value | Reject QAVPLSPR01 → ledger qty=-100, transaction_activity=Reject; bal period08in=100 period08out=100; detail qty_available 100→0 (back to pre-Submit) |
| SPR-05 | PASS | Second Reject raises "sudah tidak aktif" | Raised `Line tidak dapat di-reject. Penerimaan sudah tidak aktif...`; ledger count unchanged (2→2) |
| SPR-06 | PASS | Revise: same numeric effect as Reject but `transaction_activity='Revise'` literally | Submit qty=30 then Revise → second ledger row qty=-30, transaction_activity=`Revise` (not `Reject`) |
| SPR-07 | PASS | Reversal driving stock negative raises "Stock menjadi minus", full rollback | Submit qty=50 (detail→50), simulated partial consumption via direct decrement to 10 (standing in for a separate Usage doc — VPU itself is covered in `vpl_sp_qa_4_usage.md`), then Reject → raised `Stock menjadi minus...`; ledger count unchanged, bal period08out unchanged (130→130), detail qty_available stayed at 10 (not driven negative) |
| SPR-08 | PASS | Blank `p_docid`, blank `p_cpny_id`, invalid `p_activity` each raise their specific message, no side effects | `Document ID tidak boleh kosong.` / `Company ID tidak boleh kosong.` / `Activity Approve tidak valid. Gunakan Submit, Reject, atau Revise.` — all three distinct; ledger count for the probe doc unchanged (1→1) across all three calls |
| SPR-09 | PASS | Backdated `receive_date` (month 3) lands in `period03in`, not current calendar month | receive_date=2026-03-15, Submit → period03in 0→40, period08in unchanged (180→180), ledger `perpost`=`202603` matching the document's own date, not today's (`202608`) |

## Observations (not gaps, informational)

- SPR-03 and SPR-07 both confirm the procedure's rollback is transaction-wide per `CALL` — no partial commits observed anywhere in the VPR branch, consistent with plain PL/pgSQL exception propagation (no nested block swallows the exception).
- The `ms_vpl_product_bal` row is keyed by `(year, cpnyid, product_id, expired_date, whs_id)` — **not** by month — so a single row accumulates all 12 months' in/out columns. SPR-09's fixture reused the same product/expired_date/whs as earlier cases and landed in the same bal row, which was sufficient to prove correct month-bucketing (period03in moved, period08in didn't) without needing an isolated row.
- SPR-07 used a direct `ms_vpl_product_detail.qty_available` decrement to stand in for a real Usage doc's consumption, since `sp_process_vpl`'s VPU branch is exercised separately in `vpl_sp_qa_4_usage.md`. This is functionally equivalent for the purpose of testing the Reject-side negative-stock guard (Activity 3 in the VPR branch), since that guard only reads the current `qty_available` value regardless of how it got there.

## No gaps found in the VPR branch itself

All 9 cases behaved exactly per the procedure's own logic (read directly from `pg_get_functiondef`, lines 75–716 of the VPR branch). Master-data-level gaps (missing UNIQUE constraints, no `status='A'` check, etc.) are tracked separately in `vpl_sp_qa_1_master.md`, not re-tested here.
