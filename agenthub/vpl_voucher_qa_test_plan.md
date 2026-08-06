# QA Test Plan: VPL Voucher Module (Master → Receive → Transfer → Usage → Settlement)

Generated 2026-08-06 against the current working tree (branch `JP-backend`), grounded in actual controller/model code (not the original feature spec) via 5 parallel full-file reads. This plan comes **before** the reporting/ledger work mentioned in this session — it's meant to establish a known-good/known-bad baseline for the module before that report is built on top of it.

Scope: `VplMsProductController`, `VplWarehouseSetupController`, `VplReceiveController`, `VplTransferController`, `VplUsageController`, `VplSettlementController`, the shared `ApprovalController` engine, and the corresponding `Ms Vpl*`/`Trx Vpl*` models. See [[project_vpl_table_mapping]] memory for the old→new table-name history if you run into legacy table names anywhere.

---

## 0. Architecture map — read this first, applies to every section below

| Module | Access right | URL prefix | Doctype literal | Status codes |
|---|---|---|---|---|
| Master Product / Warehouse Setup | `MASTERVP` | `/msproduct` | *(no approval workflow)* | `A`/`X` (Active/Inactive) |
| Receive | `RECEIVEDVP` | `/requestvp` | `VPR` | `P` On Progress · `C` Completed · `R` Rejected · `D` Hold/Revise · `X` Cancelled |
| Transfer | `TRANSFERVP` | `/transfervp` | `VPT` | same as above |
| Usage | `USAGEVP` | `/usagevp` | `VPU` | same as above |
| Settlement | `SETTLEMENTVP` | `/settlementvp` | `VPS` | same as above |

Facts confirmed **independently by multiple agents reading different controllers** — treat these as high-confidence, not single-source guesses:

1. **Approval engine is the modern one everywhere**: `TrApproval`/`MsApproval` on connection **`pgsql2`**, table `tr_approval`/`ms_approval`. The legacy `T_approval`/mysql2 engine ([[project_manage_approval]]) is **not** used by any VPL controller — confirmed by direct model inspection in Receive, Transfer, Usage, and Settlement.
2. **Transactional documents live on `pgsql5`** (`tr_vpl_receive`, `tr_vpl_transfer`, `tr_vpl_usage`, `tr_vpl_settlement` + their `_detail` tables) — a different physical connection than the approval tables. Several controllers have explicit code comments about this (can't JOIN across connections), and it creates real cross-connection commit-ordering risks flagged per-module below (RCV-32, TRF-30/34/35).
3. **The only stock table any controller actually mutates is `ms_vpl_product_detail`** (per product + expiry + warehouse row, fields `qty_available`/`qty_reserved`). **`ms_vpl_product_bal` (period-bucketed balance) and `trx_vpl_ledger` (movement ledger) are never written by Receive, Transfer, Usage, or Settlement** — confirmed by independent repo-wide grep from 3 separate agents. Settlement's `approve()` even has an inline comment attributing stock/ledger/period-balance updates to an external DB stored procedure that isn't part of this Laravel codebase at all.
   **This is the single most important fact for QA before the reporting/ledger work starts**: if the upcoming report ledger is meant to read `ms_vpl_product_bal`/`trx_vpl_ledger`, it will read stale/empty data unless something outside this repo (a DB trigger or stored procedure) is populating them. **Verify directly against the live DB** whether these tables get populated at all right now — don't trust either "yes" or "no" until checked (see ARC-01/ARC-02 below).
4. **Approve/Reject/Revise routes are gated only by `<MODULE>,VIEW`** in all four transactional modules, not a dedicated approval right. The real authorization is `ApprovalController::assertUserCanAct()` matching the acting username (case-insensitive, `;`/`,`-split) against the **currently active** step's `aprv_username` list. This means every module needs two distinct negative tests: (a) wrong access right entirely → blocked by route middleware, and (b) correct `VIEW` access but not the approver → blocked only by the inner check. Don't collapse these into one test.
5. **Status lifecycle is consistent across Receive/Transfer/Usage/Settlement**: `P` (submitted, approval pending) → `C` (Completed) / `R` (Rejected) / `D` (Hold — sent back by an approver via Revise, resubmittable by the creator) / `X` (Cancelled). Cancel is only allowed by the document's creator, and only while status is `D`, or `P` with zero approved steps so far.
6. **Detail-line validation is uniformly weak on the create/store side** across all 5 controllers — missing/invalid lines are silently `continue`-skipped rather than rejected with a specific error, and several controllers have no minimum-line-count guard on `update()` even though `store()` has one. This pattern repeats often enough below that it's worth testing deliberately in each module rather than assuming one pass covers all.
7. **Stock-sufficiency is checked late, not at store() time**, in Transfer and Usage — a document can be created (and stock reserved) for a quantity that already exceeds what's available; the actual block happens at `approve()`. This is worth testing explicitly since it means a requester gets no early feedback.

### Architecture verification tasks (run once, before the module sections)

| ID | Type | Case | Steps | Expected |
|---|---|---|---|---|
| ARC-01 | Informational | Does approving *any* VPL document ever populate `ms_vpl_product_bal`? | Complete one Receive, one Transfer, one Usage doc (see module sections), then query `ms_vpl_product_bal` for the touched product/warehouse before and after each | Per code: **no controller writes it**. Confirm empirically whether some other mechanism (DB trigger/stored proc) does. Report actual finding either way — this blocks/unblocks the upcoming reporting work. |
| ARC-02 | Informational | Does approving *any* VPL document ever populate `trx_vpl_ledger`? | Same as ARC-01, query `trx_vpl_ledger` for `refnbr` = each completed doc's ID | Per code: **no controller writes it**. Confirm empirically. |
| ARC-03 | Negative | Cross-connection commit-order failure on final approval (Receive) | Force `insertMsProductDetail()` to fail mid-loop on the last approval step (e.g. temporarily break a `whs_id` FK) | Per code analysis: the `pgsql2` approval chain can close (fully approved, no pending steps) **before** the `pgsql5` stock-credit transaction runs; if that fails, the document is left permanently `status='P'` with no pending approver and no stock credited — a stuck, un-completable document. High-value to actually reproduce once, not just read about. |

---

## 1. Global test environment & preconditions

### 1.1 Synthetic data strategy — read before creating anything

This module moves real inventory quantities (`ms_vpl_product_detail.qty_available`/`qty_reserved`). Unlike the Training/Ticket QA passes in this folder, **cleanup here can't just soft-delete or roll back rows — it has to not corrupt real stock balances in the first place.** The safe approach, matching the synthetic-data convention already used for `zztest_trn_*`/`ZZTEST` in [[project_training_registration]]:

1. Create a small set of **dedicated synthetic master data** used by nothing else: 1–2 synthetic products (`product_id` will auto-generate, but set `product_name` to a tagged value like `QAVPL Test Voucher` / `QAVPL Test Product`), 2 synthetic warehouses (source + destination), and the warehouse-department mappings needed for each module's activity type. Use company `AW` (already in the `COMPANY_PREFIX` map — see MST-08 gap below — so ID generation behaves normally) with a department reserved for QA.
2. **Every stock quantity used in this QA pass originates from a Receive document created during this QA pass**, not from seeding `ms_vpl_product_detail` directly. This means the module execution order below (Master → Receive → Transfer/Usage → Settlement) mirrors the real document chain, and cleanup can fully reverse everything by deleting the synthetic product/warehouse rows entirely — no real balance ever needs to be "subtracted back."
3. Tag every document's remark/description field with `QAVPL-2026-08-06` (swap in the actual run date) so cleanup can find everything by search, same convention as `agenthub/eng_ticket_qa_test_plan.md`.
4. Keep a running list of every generated doc ID (`VPR...`, `VPT...`, `VPU...`, `VPS...`) and every synthetic `product_id`/`whs_id` as you go.

### 1.2 Roles / test accounts needed

| Role | Purpose |
|---|---|
| `MASTERVP` VIEW-only, CREATE-only, EDIT-only (3 separate users) | Exercises the CREATE/EDIT access-right asymmetry gaps found in Master (MST-17, AGE-06) |
| `VPACCESS` role holder | Bypasses company scoping on Master list/export/doc-id lookup — needed to confirm scoping actually holds for everyone else |
| `RECEIVEDVP` VIEW/CREATE/EDIT/DELETE (per-tier users, or one user with all four for functional tests + one VIEW-only user for the "weak approve gate" tests) | Receive lifecycle |
| `TRANSFERVP` VIEW/CREATE/EDIT/DELETE (same pattern) | Transfer lifecycle |
| `USAGEVP` VIEW/CREATE/EDIT/DELETE (same pattern) | Usage lifecycle |
| `SETTLEMENTVP` VIEW/CREATE/EDIT/DELETE (same pattern) | Settlement lifecycle |
| Two-level approver chain for at least one doctype (recommend Receive `VPR`) | Out-of-turn-approver negative tests need a real level-1/level-2 split |
| A user with `VIEW` access to a module but **not** listed as approver on any pending step | The "correct access right, wrong approver" negative test that's distinct from a plain 403 |
| A user with zero access rights to a given module | Plain route-middleware 403 baseline |

### 1.3 Master data checklist (must exist before the corresponding section is runnable)

| Data | Needed for | Notes |
|---|---|---|
| `ms_category`: `doctype='VPL'`, `categoryid='type'`, `groups='TYPE'`/`'SOURCE'`, `status='A'` | Master Product category/source pickers | Not enforced server-side on save (gap #3) — still needed for the UI to render |
| `ms_base_uom` active rows | Master Product UOM picker | Same — not enforced server-side |
| `ms_category`: `doctype='VPR'`, `categoryid='condition'`, `category_name` ∈ `{Voucher, Product}`, `status='A'` | Receive — **hard 422 if missing** |
| `ms_category`: `doctype='VPT'`, `categoryid='condition'`, `category_name` ∈ `{Transfer Voucher, Transfer Product, Return Voucher, Return Product}`, `status='A'` | Transfer — **hard 422 if missing** |
| `ms_category`: `doctype='VPU'`, `categoryid='condition'`, `category_name` ∈ `{Usage Product, Usage Voucher, Return Usage Product, Return Usage Voucher}`, `status='A'`; also `categoryid='type'`, `groups='PURPOSE'` | Usage — **hard 422 if missing** |
| `ms_category`: `doctype='VPS'`, `categoryid='condition'`, `category_name` ∈ `{Settlement Voucher, Settlement Product}`, `status='A'` | Settlement — **hard 422 if missing** |
| `ms_approval` (pgsql2) rows for `aprv_doctype` ∈ `{VPR, VPT, VPU, VPS}` matching the synthetic `cpnyid`/`department`, `status='A'`, condition matching each `category_name` above (or `aprv_type='Normal'` fallback) | All 4 approval flows — **hard 422 (or worse — an orphaned committed doc, see gaps) if missing** |
| `ms_vpl_warehouse_dept`: `activity_type='RECEIVE'` (Receive), `'TRANSFER'` + `'TRANSFER_RECEIVE'` (Transfer, both directions), `'USAGE'` (Usage) — each `status='A'`, matching `vp_type` | Populates the warehouse ajax pickers; without these the dropdowns return empty and no detail line can be built |
| Synthetic `ms_vpl_warehouse` rows (source + destination), synthetic `ms_vpl_product` rows (one `V`, one `P`) | All 4 transactional modules |

### 1.4 Recommended execution order

Run the sections in the order below — it mirrors the real stock lifecycle and lets each later section reuse a document/stock state produced by an earlier one, rather than requiring separately seeded data:

**Master (create synthetic product + warehouses) → Receive (creates real stock via a Completed doc) → Transfer & Usage (consume that stock; run Usage with varied-expiry batches for FEFO coverage) → Settlement (settles a Completed Usage doc) → Cross-cutting/Access → Architecture verification (ARC-01/02) → Cleanup.**

---

## 2. Master Product & Warehouse Setup (`MASTERVP`)

Scope: `VplMsProductController.php` (724 lines) — product CRUD, product detail/stock lines, GCS attachments, aging setup, product target date, category/source lookups, export; `VplWarehouseSetupController.php` (228 lines) — warehouse + warehouse-department setup.

Module-specific preconditions: a `Usercpny` row (`status='A'`) linking the test user to at least one `cpny_id`; a second test user holding role `VPACCESS` for the cross-company-bypass tests; `product_id` auto-generates as `{product_type}{cpnyPrefix}{00001}` where `cpnyPrefix` comes from a **hardcoded map** `AW=1, EP=2, PSA=3, GPS=4` (anything else falls back to `'0'` — see MST-08). GCS is live for photo/attachment uploads — expect test files to actually land in the bucket.

### 2a. Master Product

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| MST-01 | Positive | Create Voucher product | `cpnyid=AW`, `product_name`, `product_type=V`, no photo | 200, `product_id` auto `V1{00001}`, `status='A'` |
| MST-02 | Positive | Create Product-type item with photo | `product_type=P`, attach jpg/png ≤5MB | 200, photo uploaded to GCS, `product_id` `P1{00001}` |
| MST-03 | Negative | Product-type without photo, none existing | `product_type=P`, omit `product_photo`, `key_id` empty | 422 "Product photo is required for Product type." |
| MST-04 | Negative | Missing required field | Omit `product_name`/`cpnyid`/`product_type` | 422 |
| MST-05 | Negative | Invalid `product_type` | `product_type=X` | 422 (`in:V,P`) |
| MST-06 | Negative | Oversized/wrong-type photo | 10MB file, or `.gif` | 422 (`mimes:jpg,jpeg,png|max:5120`) |
| MST-07 | Negative | `cpnyid` outside the user's `Usercpny` scope | Non-`VPACCESS` user submits `cpnyid` they have no company access for | **Currently succeeds** — `save_product()` never checks `cpnyIds()`; only listing/export are scoped. Real access-control gap, not just a validation nit. |
| MST-08 | Negative | `cpnyid` not in the hardcoded prefix map | `cpnyid` outside `{AW,EP,PSA,GPS}` | **Currently succeeds**, prefix silently defaults to `'0'` — two different unlisted companies each creating their first product independently collide on the same `product_id` (e.g. both `V000001`). Check whether a DB unique constraint on `product_id` exists to catch this. |
| MST-09 | Negative | Unvalidated `product_category`/`product_source_type` | Values not present in `ms_category` | **Currently succeeds** — no FK/enum check server-side |
| MST-10 | Negative | Unvalidated `product_uom` | UOM not in `MsBaseUom` active list | **Currently succeeds** — stored uppercased/trimmed, no existence check |
| MST-11 | Negative | Unvalidated `status` on edit | `status=ZZZ` | **Currently succeeds** — no `in:A,X` rule anywhere in `save_product()`'s validation |
| MST-12 | Positive | Edit product | PUT valid field changes via `key_id` | 200, fields updated |
| MST-13 | Positive | Edit — replace photo | Upload new photo on existing product | New GCS object created; **old photo is never deleted** — orphaned GCS storage growth |
| MST-14 | Positive | Deactivate product, zero stock | Sum of `qty_available` across active detail rows = 0 | 200, `status='X'` |
| MST-15 | Negative | Deactivate product with stock on hand | Sum of `qty_available` > 0 | 422 "Cannot deactivate. This product still has {N} qty in stock." |
| MST-16 | Positive | Activate an inactive product | `status='X'` → activate | 200, `status='A'` — **no precondition check at all**, unlike deactivate |
| MST-17 | Negative | CREATE/EDIT access-right mismatch | CREATE-only user calls `save_product` with a `key_id` (i.e. edits an existing product) | **Succeeds** — `save_product` handles both create and update but is gated only by `MASTERVP,CREATE`; conversely an EDIT-only user can fetch `edit_product` but gets 403 trying to actually submit `save_product`. Real two-way access-right mismatch. |
| MST-18 | Negative | Unauthorized access | Hit any `/msproduct/*` route without the corresponding `MASTERVP` tier | 403 |
| MST-19 | Negative | Non-ajax direct request | Call `edit_product`/`viewproductJson`/`getDocIds` without XHR header | 404 (`abort_unless($request->ajax(), 404)`) |

### 2b. Product detail lines / stock (`saveProductDetail`, `viewproduct(Json)`, `getProductDetails`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| PRD-01 | Positive | Add one stock line | `addmore[0] = {qty, expired_date, source_whs}` | 200, `MsVplProductDetail` row created, `status='A'` |
| PRD-02 | Positive | Add multiple lines in one call | 3 `addmore[]` entries | All 3 created inside one `pgsql5` transaction |
| PRD-03 | Negative | Missing/empty `addmore` payload | Omit `addmore` entirely | **No `validate()` call exists at all** in `saveProductDetail()` — confirm actual failure mode (likely a generic 500 caught by try/catch, not a clean 422) |
| PRD-04 | Negative | Negative/non-numeric `qty` | `qty=-5` or `"abc"` | **Currently accepted** — no validation |
| PRD-05 | Negative | `whs_id` not in `ms_vpl_warehouse` | Garbage warehouse code | **Currently accepted** — no FK existence check |
| PRD-06 | Negative | Wrong-company/inactive/wrong-type warehouse in the picker | The warehouse dropdown on `viewproduct` loads `MsVplWarehouse::all()` with **no** `status`/`cpnyid`/`vp_type` filter | Stock can be recorded against an inactive, wrong-company, or wrong-type warehouse — flag as gap in both data-sourcing and server-side check |
| PRD-07 | Positive | View product stock listing | `viewproductJson`/`viewproduct` | Returns only `status='A'` rows, ordered by `expired_date` |

### 2c. Attachments (`saveProductAttach`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| ATT-01 | Positive | Upload attachment | Valid file, `product_id`, `cpnyid` | 200, `TrAttachment` row (`doctype='VPLPROD'`), file in GCS |
| ATT-02 | Negative | No file | Omit `attachment` | 422 "No file uploaded" |
| ATT-03 | Negative | Disallowed type/oversized file | `.exe`, or a 500MB file | **Currently accepted** — no `mimes`/`max` rule anywhere in `saveProductAttach()` or `TrAttachmentController::uploadInternal()`, unlike the product-photo path which does validate |
| ATT-04 | Negative | Missing `product_id` | Omit it | 500 "Upload failed" (`InvalidArgumentException` from empty `refnbr`) |

### 2d. Aging setup (`setupaging`/`save_aging`/`edit_aging`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| AGE-01 | Positive | Create aging bucket | `age_descr`, `start_age`, `end_age`, `order_age` | 200, `status='A'` forced |
| AGE-02 | Negative | Empty POST | No fields | **No `validate()` call at all** in `save_aging()` — confirm actual behavior on a fully empty submission |
| AGE-03 | Negative | `start_age` > `end_age` | e.g. `60`/`30` | **Currently accepted** — no range check |
| AGE-04 | Negative | Overlapping age ranges | `0-30` then `15-45` | **Currently accepted** — no overlap detection |
| AGE-05 | Negative | Unvalidated `status` on edit | `status=ZZZ` | **Currently accepted** |
| AGE-06 | Negative | CREATE/EDIT access-right mismatch | Same pattern as MST-17 | `save_aging` is CREATE-only-gated with no separate update route, `edit_aging` fetch is EDIT-gated — same two-way mismatch |
| AGE-07 | Negative | Unauthorized access | No `MASTERVP` access | 403 |

### 2e. Product target date (`producttarget`/`getProductDetails`/`updateTargetDate`) — highest-value section in Master

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TGT-01 | Positive | List target-date view | `GET producttarget` (ajax) | Datatable, scoped by `cpnyIds()` unless `VPACCESS` |
| TGT-02 | Positive | Fetch per-product detail rows | `getProductDetails($product_id)` | All `MsVplProductDetail` rows (any status), per line, with `expired_date`/`target_date`/`whs_id`/`qty_available` |
| TGT-03 | **Negative — likely the most consequential gap in this module** | Update target date is a blanket per-product overwrite, not per-line | `updateTargetDate()` validates only `target_date` + `product_id`, then runs `MsVplProductDetail::where('product_id', ...)->update(['target_date' => ...])` with **no** scoping by `expired_date`/`whs_id` | Setting a target date for what the UI presents as one specific stock line silently overwrites `target_date` on **every** detail row for that product, across every warehouse and expiry batch. Read side is per-line, write side is per-product — verify and flag prominently. |
| TGT-04 | Negative | `product_id` matches zero rows | Nonexistent/mistyped `product_id` | **Silent no-op** — still returns 200 "Target date updated" |
| TGT-05 | Negative | Missing/invalid `target_date` | Omit, or non-date string | 422 (`required|date`) |

### 2f. Warehouse setup

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| WHS-01 | Positive | Create warehouse | `cpnyid`, `whs_id`, `vp_type` | 200, `status='A'`, `whs_id` uppercased+trimmed |
| WHS-02 | Negative | Empty POST | No fields | **No `validate()` call at all** in `saveWarehouse()` |
| WHS-03 | Negative | Duplicate `(cpnyid, whs_id, vp_type)` | Same combo twice | **Currently accepted** — no uniqueness check (a V+P pair sharing one `whs_id` is by-design; a true full duplicate is not guarded against) |
| WHS-04 | Negative | Unvalidated `vp_type` | `vp_type=FOOBAR` | **Currently accepted** — no `in:V,P` rule |
| WHS-05 | Positive | Edit warehouse | PUT `cpnyid`/`vp_type` changes | 200 — `whs_id` itself is not editable on the update branch; confirm intentional |
| WHS-06 | Negative | Deactivate a warehouse still holding stock | Toggle inactive on a warehouse with rows in `ms_vpl_product_detail` | **Currently accepted** — no referential-integrity check, unlike Master Product's deactivate which correctly blocks on stock-on-hand |
| WHS-07 | Negative | Toggle with `activate` param omitted | `PUT toggle` with no `activate` key | `$request->boolean('activate')` defaults `false` → **silently deactivates** rather than erroring |
| WHS-08 | Negative | Unauthorized access | No `MASTERVP` tier | 403 |

### 2g. Warehouse-department setup

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| WHD-01 | Positive | Create dept assignment, specific `vp_type` | Full payload, `vp_type=V` (or `P`) | 200, one row |
| WHD-02 | Positive | Create dept assignment, `vp_type` omitted ("All") | Same, no `vp_type` | 200, **two** rows created (`V` and `P`) |
| WHD-03 | Negative | Duplicate "All" create | Create the same `(cpnyid, whs_id, activity_type, department_id)` "All" combo twice | **Currently succeeds twice** — the duplicate-counterpart guard (`deptCounterpartExists()`) exists only on the **edit** branch, not create |
| WHD-04 | Negative | Edit existing `vp_type='P'` row, switch to "All" | Edit a `P` row with `vp_type` omitted | Row is **forced back to `'V'`** regardless of its original type, then a `P` counterpart conditionally created — looks like a copy-paste assumption that "All" always starts from a `V` row; verify and flag |
| WHD-05 | Negative | Missing required fields | Omit `whs_id`/`department_id` | **Currently accepted** — no `validate()` call |
| WHD-06 | Negative | Nonexistent `department_id` | Garbage value | **Currently accepted** — no FK check |
| WHD-07 | Positive | Toggle active/inactive | `PUT toggle` | 200 |
| WHD-08 | Negative | Toggle with `activate` omitted | Same as WHS-07 | Defaults to deactivate |
| WHD-09 | Positive | Filtered listing | `warehouseDeptJson` with filters | Only matching rows |
| WHD-10 | Negative | Unauthorized access | No `MASTERVP` tier | 403 |

### 2h. Category/source lookups & export

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| LKP-01 | Positive | Category dropdown | `GET get-category` | Only `doctype='VPL', groups='TYPE', status='A'` |
| LKP-02 | Positive | Source dropdown | `GET get-source` | Only `groups='SOURCE'`, `status='A'` |
| LKP-03 | Negative | Non-ajax request | Direct browser GET | 404 |
| LKP-04 | Positive | Doc-ID typeahead | `GET get-doc-ids?q=V1` | ≤50 matches, scoped by `cpnyIds()` unless `VPACCESS` |
| LKP-05 | Positive | Export, no filters | `GET export` | XLSX, scoped by `cpnyIds()` |
| LKP-06 | Positive | Export with filters | Combined filters | Filtered rows only |
| LKP-07 | Negative | Export scoping bypass attempt | Non-`VPACCESS` user, no client-side override param exists | Still correctly scoped server-side |

### Master module — known gaps

1. Two-way CREATE/EDIT access-right mismatch on both `save_product` (MST-17) and `save_aging` (AGE-06) — the single save-and-update endpoint is gated on CREATE only, while the fetch-for-edit endpoint is gated on EDIT only.
2. `save_product()` never checks the submitting user's `Usercpny` scope (MST-07) — a company-restricted user can create/edit products for companies they can't otherwise see.
3. Hardcoded, incomplete `COMPANY_PREFIX` map causes `product_id` collisions across unlisted companies (MST-08).
4. `product_category`/`product_source_type`/`product_uom`/`status` all unvalidated against their lookup tables or enum on save (MST-09/10/11).
5. `saveProductDetail()` and `save_aging()`/`saveWarehouse()`/`saveWarehouseDept()` have **zero** request validation (PRD-03, AGE-02, WHS-02, WHD-05).
6. Warehouse picker for adding stock is unfiltered by status/company/type (PRD-06).
7. Attachment upload has no file-type/size restriction, unlike product photo (ATT-03).
8. `updateTargetDate()` is a blanket per-product overwrite despite the read side being per-line (TGT-03) — likely the most consequential functional gap in this module.
9. Warehouse/warehouse-dept deactivate have no referential-integrity checks, unlike product deactivate (WHS-06).
10. `toggle*` endpoints default to "deactivate" when the `activate` param is missing (WHS-07/WHD-08).
11. Old GCS product photo is never deleted on replace (MST-13).

---

## 3. Receive Voucher (`RECEIVEDVP`, doctype `VPR`, `/requestvp`)

Module-specific preconditions: active product in the target `cpnyid`; active `MsVplWarehouseDept` row (`activity_type='RECEIVE'`, matching `vp_type`); the `VPR`/`condition` category row (Voucher/Product); an `ms_approval` rule for `VPR` matching the doc's `cpnyid`+`department` — **missing this aborts `store()`/`update()` with 422 "Approval line belum di-setup, Please contact IT!"**.

### 3a. Create / detail lines / attachments

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| RCV-01 | Positive | Valid store, one detail line | Valid `cpnyid`/`department`/`vp_type`, `receive_remark` set, one valid `addmore[0]` line | `TrxVplReceive` created `status='P'`, `receive_id` = `VPR{YY}{M}{NNNN}`, one detail row, `TrApproval` rows generated, first approver emailed |
| RCV-02 | Negative | Missing remark | `receive_remark` empty | 422 "Remark is required." |
| RCV-03 | Negative | No department/company access | Non-admin user with no `Usercpny`/`Userdept` for the target | 403 |
| RCV-04 | Negative | Missing category condition master data | No active `condition` category row for the `vp_type` | 422 "Category condition not found for {TYPE}. Please contact IT!" |
| RCV-05 | Negative | Zero valid detail lines | `addmore` omitted, or every line missing required fields | Invalid lines silently `continue`-skipped with **no per-line error**; if resulting line count is 0, whole submission rolls back with "Please add at least one valid product line before submitting." — but a request with 3 garbage + 1 valid line saves successfully with only 1 line and no warning about the dropped ones |
| RCV-06 | Negative | No matching approval rule | No `MsApproval` row for the doc's cpny/dept/`VPR` | Whole transaction (header+details+attachments) rolls back — verify no orphan rows persist |
| RCV-07 | Negative | Expiry date omitted for exp-tracked product | No `expired_date` regardless of `product_check_exp` | **Currently succeeds** — defaults silently to `1900-01-01` |
| RCV-08 | Negative | Attachment with disallowed extension/size | `.exe` or oversized file | **Currently succeeds** — `saveAttachments()` only checks `isValid()`, no mime/size limit |
| RCV-09 | Negative | `receive_type`/`source_receive_dept` omitted | Raw POST bypassing the form | **Currently succeeds** — required only client-side |
| RCV-10 | Negative | Nonexistent `source_receive_id` | Bogus FK value | **Currently succeeds** — no existence check |

### 3b. Edit / update by status

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| RCV-11 | Positive | Update while status D, by creator | Creator resubmits a Hold doc | Lines merged (same product+expiry+whs increments `qty_receive`, else new row), linenbr renumbered, fresh approval chain, `status→P` |
| RCV-12 | Negative | Update by non-creator | Different user | 403 |
| RCV-13 | Negative | Update while status P/C/R/X | Not `D` | 422 "This document cannot be edited in its current status." |
| RCV-14 | Negative | Update with zero lines after deleting all | Creator deletes all lines via `deleteDetail`, then updates with no `addmore` | **Currently succeeds** — unlike `store()`, `update()` has no minimum-line-count guard; doc resubmits with zero detail lines |
| RCV-15 | Negative | Missing remark on update | Blank | 422, same as store |
| RCV-16 | Negative | Update after approval rule removed | `MsApproval` rule deactivated between edits | Transaction rolls back, doc stays `D` with old lines intact |

### 3c. Cancel by status

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| RCV-17 | Positive | Cancel while D | Creator cancels a Hold doc | `status→X`, residual `P` approval rows forced to `X` |
| RCV-18 | Positive | Cancel while P, nothing approved yet | Creator cancels before any approval | Allowed |
| RCV-19 | Negative | Cancel while P after first approval | At least one step already `A` | 403 |
| RCV-20 | Negative | Cancel by non-creator | Any other user | 403 |
| RCV-21 | Negative | Cancel while C/R/X | Terminal statuses | 403 |

### 3d. Approve / Reject / Revise

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| RCV-22 | Positive | Approve intermediate step | Approver on active step, more steps pending | Step `→A`, next step activated, doc stays `P` |
| RCV-23 | Positive | Approve final step | Last pending step | `status→C`, `insertMsProductDetail()` credits `qty_available` on each detail's target row (upsert by product+expiry+warehouse) |
| RCV-24 | Negative | Approve by non-approver | `VIEW`-only user, username not on the active step | 403 "You can't approve." — route middleware alone would NOT have blocked this |
| RCV-25 | Negative | Out-of-turn approver | Level-2 approver acts while level-1 still pending | 403 |
| RCV-26 | Negative | Approve/Reject/Revise on a terminal doc | Doc already C/R/X | "No pending approval step." |
| RCV-27 | Positive | Reject | Valid approver, message set | Step `→R`, remaining steps forced `X`, doc `status→R`, requester notified |
| RCV-28 | Negative | Reject without message | Empty message | 422 "Reason is required." — checked **before** the approver check, so a non-approver also gets this first |
| RCV-29 | Positive | Revise | Valid approver, message set | Step `→D`, doc `status→D`, requester notified with a revise-specific suffix |
| RCV-30 | Negative | Revise without message | Empty | 422 |
| RCV-31 | Negative | Double-approve / replay | Approve the same step twice quickly | Second call should hit "no pending step" or "can't approve" — verify no double stock-credit |
| RCV-32 | Negative | Stock-credit failure mid-loop on final approval | Simulate a failure inside `insertMsProductDetail()`'s loop | Per code analysis: the `pgsql2` approval commit can precede the `pgsql5` stock-credit transaction — a failure here can leave the doc permanently stuck `status='P'` with a fully-closed approval chain and no stock credited. **High-priority reproduction target** — see ARC-03. |

### 3e. Stock balance & ledger impact

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| RCV-33 | Positive | Stock credited on completion | Complete a receive with one existing-combo line and one new-combo line | Existing row's `qty_available` incremented in place; new row created with `qty_available=qty_receive`, `qty_reserved=0` |
| RCV-34 | Negative | `ms_vpl_product_bal` untouched | Inspect the table after RCV-33 | Confirms ARC-01 for this module specifically |
| RCV-35 | Negative | `trx_vpl_ledger` untouched | Inspect the table after RCV-33 | Confirms ARC-02 for this module specifically |

### 3f. Ajax endpoints

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| RCV-36 | Positive | `getProducts` scoping | `cpnyid`, `vp_type='voucher'` | Correct `cpnyid`+`product_type='V'`+`status='A'` filter |
| RCV-37 | Negative | `getProducts` with unmapped `vp_type` | `vp_type='bogus'` | Raw string passed through as filter — likely empty result, no error |
| RCV-38 | Positive | `getWarehouse` scoping | `cpnyid`, `department`, `vp_type` | Filtered by `activity_type='RECEIVE'` + all three params |
| RCV-39 | Positive | `getTenants` active-only | `cpnyid` | Distinct active tenants only |
| RCV-40 | Negative | `getProductDetails` unscoped by company | `product_id` from a different `cpnyid` | **Currently succeeds** — no `cpnyid` check, minor info-leak of `product_check_exp` |

### Receive module — known gaps

1. No stock ledger entry ever written on completion (RCV-35).
2. `ms_vpl_product_bal` never updated on completion (RCV-34).
3. `update()` has no minimum-line-count guard, unlike `store()` (RCV-14).
4. Invalid detail lines are silently dropped with no per-line error, in both store and update (RCV-05).
5. `receive_type`/`source_receive_dept`/`source_receive_id` have no server-side validation (RCV-09/10).
6. No attachment file-type/size validation (RCV-08).
7. `expired_date` not required even for exp-tracked products (RCV-07).
8. Approve/Reject/Revise sit behind `VIEW`, not a dedicated right — protection is entirely the inner approver check (RCV-24).
9. Cross-connection commit-order risk on final approval — see ARC-03/RCV-32.
10. `getProductDetails` ajax has no company scoping (RCV-40).

---

## 4. Transfer Voucher (`TRANSFERVP`, doctype `VPT`, `/transfervp`)

Module-specific preconditions: source warehouse must have a stock row with `qty_available - qty_reserved > 0`; destination department/warehouse must have an active `ms_vpl_warehouse_dept` row (`TRANSFER` for source side, `TRANSFER_RECEIVE` for destination side); the `VPT`/`condition` category rows for all 4 combinations (`Transfer Voucher/Product`, `Return Voucher/Product`); matching approval rules. For Return Transfer (`transfertype=ReturnTf`), a **Completed** prior Transfer doc in the same scope is required as `ref_transfer_id`.

### 4a. Create (store)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-01 | Positive | Valid Transfer, sufficient stock | Valid header + one line, real available stock | 200, `transfer_id = VPT{yy}{mm}{seq}`, status `P`, source stock reserved |
| TRF-02 | Negative | Missing remark | Blank | 422 |
| TRF-03 | Negative | ReturnTf without `ref_transfer_id` | `transfertype=ReturnTf`, no ref | 422 "Reference Transfer ID is required for Return Transfer." |
| TRF-04 | Negative | No department/company access | Non-admin, no scope match | 403 |
| TRF-05 | Negative | Missing approval category | No active condition row for the combo | 422 |
| TRF-06 | Negative | Zero valid detail lines | All lines invalid | 422 "Please add at least one valid product line before submitting." |
| TRF-07 | **Negative (gap)** | Same-warehouse transfer | `from_whs_id === to_whs_id` via direct POST | **Currently succeeds** — no equality check anywhere server-side; the UI only excludes the source from the `to` picker for plain `Transfer`, not for `ReturnTf` at all |
| TRF-08 | **Negative (gap)** | Qty exceeds available stock at store() time | `qty_transfer` > true available | **Currently succeeds at store()** — no sufficiency check in the create loop; `reserveDetail()` has no upper bound, so `qty_reserved` can exceed `qty_available`. Real block only happens at `approve()`. |
| TRF-09 | **Negative (gap)** | Missing `from_whs_id` on a line | Line has valid product/to_whs/qty but no `from_whs_id` | **Currently succeeds** — not in the required-field check; row saved with `from_whs_id=null`, reservation silently no-ops, and approval later fails with a misleading "insufficient stock" rather than a clear "missing warehouse" error |
| TRF-10 | Positive | Multiple lines, mixed valid/invalid | 3 lines, 1 invalid | Only valid lines persisted, sequential `linenbr` |
| TRF-11 | Positive | Attachment upload on create | Valid files | Stored, `Attachment` rows created |
| TRF-12 | Negative | Approval-rule engine failure mid-transaction | Force `generateForDocument()` to throw | Entire transaction rolls back, no orphan rows |

### 4b. Edit / update

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-13 | Positive | Update while D, by creator | Resubmit | `status→P`, fresh approval chain |
| TRF-14 | Negative | Update by non-creator | Different user | 403 |
| TRF-15 | Negative | Update when status ≠ D | P/C/R/X | 422 |
| TRF-16 | Positive | Add a genuinely new line on update | New product/expiry/to_whs combo | New row, full qty reserved |
| TRF-17 | Positive | Bump an existing line's qty on update | Matching product+expiry+to_whs | Existing row's qty incremented by the **delta only**, only the delta reserved |
| TRF-18 | **Negative (gap, high risk)** | Resubmit after Revise, re-sending an already-persisted line | Doc revised (all reservations released), `update()` re-includes an existing line in `addmore` | If resent: qty gets `+=`'d on top of the current value, silently doubling the transferred quantity. If not resent: that line's reservation is never restored, permanently drifting reservation accounting low. **Test both frontend behaviors and report which actually occurs.** |
| TRF-19 | Negative | Update without remark | Blank | 422 |
| TRF-20 | Negative | Update, approval-category lookup fails | Missing condition row | 422, changes rolled back |

### 4c. Cancel

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-21 | Positive | Cancel while D | Creator | `status→X`, all reservations released, pending approval rows forced `X` |
| TRF-22 | Positive | Cancel while P, nothing approved | Creator | Allowed |
| TRF-23 | Negative | Cancel while P after one approval | Any approval already `A` | 403 |
| TRF-24 | Negative | Cancel a Completed doc | `status=C` | 403 — no reversal mechanism exists at all for a completed transfer; confirm this is by design |
| TRF-25 | Negative | Cancel by non-creator | Different user | 403 |
| TRF-26 | Positive | Cancel does NOT touch `qty_available` | Cancel a P/D doc, inspect balances | Only `qty_reserved` moves on cancel; `qty_available` only changes at approval completion |

### 4d. Approve / Reject / Revise

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-27 | Positive | Approve final step | Correct approver, sufficient stock | `status→C`; source `qty_available -= qty`, `qty_reserved -= qty` (floored at 0); destination row incremented or created |
| TRF-28 | Positive | Approve intermediate step | Multi-level chain, more steps pending | `completed:false`, next step activated, header stays `P` |
| TRF-29 | Negative | Insufficient stock at approval time | Source stock dropped below `qty_transfer` since submission | 422 "Approval failed! {product} (Expired: {date}) has insufficient stock." — the real enforcement point, not `store()` |
| TRF-30 | **Negative (concurrency, needs hands-on verification)** | Two concurrent final-step approvals | Simulate near-simultaneous `approve()` calls | Deduction re-checks under row lock inside the transaction (documented as closing a race window); verify the second concurrent request actually fails cleanly rather than double-deducting — flag as needing real concurrency testing, not just static reading |
| TRF-31 | Negative | Reject by non-approver | `VIEW`-only, not on the active step | 403 "You can't reject." |
| TRF-32 | Negative | Out-of-turn approver | Level-2 before level-1 | 403 |
| TRF-33 | Negative | Reject without message | Blank | 422 |
| TRF-34 | Positive | Reject | Correct approver, message set | `status→R`, reservations released — **but this release is written on `pgsql5` from inside a `pgsql2` transaction**, so the code comment claiming it "rolls back on failure" doesn't actually hold across connections; worth a forced-failure test |
| TRF-35 | Positive | Revise | Correct approver, message set | `status→D`, reservations released (same cross-connection caveat as TRF-34) |
| TRF-36 | Negative | Revise/Reject without message | Blank | 422 |
| TRF-37 | Negative | Act on a terminal doc | Already C/X/R | "No pending approval step." |

### 4e. Stock balance impact (both warehouses) — highest priority in this module

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-38 | Positive | Source decrement on completion | Note balance before/after final approval | Decremented exactly by `qty_transfer` |
| TRF-39 | Positive | Destination increment, existing row | Destination already has that product+expiry+warehouse | `qty_available += qty_transfer` on the existing row |
| TRF-40 | Positive | Destination increment, no existing row | Never held that combo before | New row created, `cpnyid` copied from the transfer header (not derived from the destination warehouse itself — check whether cross-company transfer is even reachable) |
| TRF-41 | **Negative (gap)** | Mid-loop failure across multiple lines | Force a later line in a multi-line transfer to fail the in-loop recheck | Should roll back all lines' saves atomically since they share one transaction — confirm this holds |
| TRF-42 | Positive | Reserved-counter round-trip | Straight-through create→approve, no revise cycle | `qty_reserved` nets back to pre-transfer baseline after completion |

### 4f. `getRefOptions` / return-reference linkage

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-43 | Positive | Ref options list Completed transfers only | `getRefOptions`, `transfertype=ReturnTf` | Only `status='C'` docs, scoped to matching cpny/dept/vp_type |
| TRF-44 | Negative | Ref options for plain Transfer | `transfertype=Transfer` | Empty immediately |
| TRF-45 | **Negative (gap)** | Product list for ReturnTf ignores selected `from_whs_id` | Pick a `ref_transfer_id`, call `getTransferProducts` | The ReturnTf branch never applies `from_whs_id` — returned `whs_id` is hard-coded to the *original* transfer's `to_whs_id`, which may not match what the user actually picked |
| TRF-46 | **Negative (gap)** | Return qty not bounded by original transfer qty | Submit a ReturnTf qty greater than what the referenced transfer originally sent | **Currently succeeds** — no cross-check against the referenced document's original quantity, only real warehouse stock sufficiency is checked at approval |

### 4g. From/To warehouse ajax filters

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| TRF-47 | Positive | `getFromWhs`, Transfer | `activity_type='TRANSFER'` | Correct filter |
| TRF-48 | Positive | `getFromWhs`, ReturnTf | `activity_type='TRANSFER_RECEIVE'` | Correct filter — stock is sourced from the dept's own receiving warehouse for a return |
| TRF-49 | Positive | `getToWhs`, Transfer, excludes source | `from_whs_id=X` | Candidates exclude `X` |
| TRF-50 | **Negative (gap)** | `getToWhs`, ReturnTf, does NOT exclude source | `from_whs_id=X`, `transfertype=ReturnTf` | Candidates **include** `X` — combined with TRF-07, makes a same-warehouse Return even easier to construct via the normal UI, not just direct POST |

### FEFO / batch handling note

Transfer tracks `expired_date` and orders the picker by nearest-expiry-first for **display only** — there is no server-side FEFO enforcement; a user can manually transfer a later-expiring batch while an earlier one still has stock. Confirm this is accepted behavior (a gap, not necessarily a bug) — it's the same conclusion USG-18 reaches for Usage's FEFO picker with expired batches.

### Transfer module — known gaps

1. No `from_whs_id != to_whs_id` check anywhere server-side (TRF-07), and the UI-level exclusion doesn't even apply to Return Transfer (TRF-50).
2. No stock-sufficiency check until `approve()` — `reserveDetail()` has no upper bound, so `qty_reserved` can exceed `qty_available` (TRF-08).
3. `from_whs_id` is not in the required-field check, producing a stalled document with a misleading error later (TRF-09).
4. Revise→Update cycle can silently double a line's qty or permanently under-reserve it, depending on whether the frontend resends existing lines (TRF-18) — needs a live test to determine actual behavior.
5. `getTransferProducts()`'s ReturnTf branch ignores the selected `from_whs_id` entirely (TRF-45).
6. Return quantity isn't bounded by the referenced original transfer's quantity (TRF-46).
7. `ms_vpl_product_bal`/`trx_vpl_ledger` never written (confirms ARC-01/02 for this module).
8. Reject/Revise release reservations on `pgsql5` from inside a `pgsql2` transaction — the "rolls back together" claim in the code comment doesn't actually hold across connections (TRF-34/35).
9. Approve/Reject/Revise gated only by `VIEW` (TRF-31/32), same pattern as every other transactional module.
10. Cancelling a Completed transfer has no code path at all (TRF-24) — confirm intended, not a missing feature.

---

## 5. Usage Voucher (`USAGEVP`, doctype `VPU`, `/usagevp`)

Module-specific preconditions: `ms_approval` rules for all 4 condition names (`Usage Product`, `Usage Voucher`, `Return Usage Product`, `Return Usage Voucher`); matching category rows; an active `ms_vpl_warehouse_dept` (`activity_type='USAGE'`); **stock batches with deliberately varied expiry dates** at the test warehouse to make FEFO meaningful, including one already-expired batch; a Completed Usage doc with no settlement yet, for Return-reference testing.

Note: `TrxVplUsageDetailTemp` (the temp-table model this task originally suspected might leak orphaned rows) is confirmed **dead code** — nothing in `VplUsageController` reads or writes it. Drop that concern from the plan; it doesn't apply.

### 5a. Create / store

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-01 | Positive | Valid Usage doc, single product/batch | Valid header, one FEFO-derived line | `usage_id = VPU{YYMM}{seq}`, status `P`, stock reserved, approval rows created |
| USG-02 | Negative | Missing remark | Blank | 422 |
| USG-03 | Negative | Return without `ref_usage_id` | `usagetype=Return`, no ref | 422 "Reference Usage Doc is required for Return." |
| USG-04 | Negative | No matching category condition | Deactivate the relevant condition row | 422 |
| USG-05 | **Negative (gap)** | No approval rule configured | No `ms_approval` row for the scope | **Doc + details + reservation are already committed** in a `pgsql5` transaction *before* `generateForDocument()` runs; the missing-rule abort throws outside the try/catch, so the client gets a raw error page (not the controller's normal JSON) while the doc is left stuck `status='P'`, reserved, with zero approval rows. Recoverable via cancel (no approval yet), but the failure mode is inconsistent — flag. |
| USG-06 | Positive | CUSTOMERSERVICE backdate within H-3 | `department=CUSTOMERSERVICE`, `usage_date` = today−2 | 200, date honored |
| USG-07 | Negative | CUSTOMERSERVICE backdate beyond H-3 | today−4 | 422 "Usage Date must be within H-3 to today." |
| USG-08 | Negative | Non-CUSTOMERSERVICE dept sends a backdate | Any other dept, `usage_date` = today−2 | **Currently succeeds but is silently overridden** — `usage_date` is forced to today regardless of what was submitted; confirm this silent override is intended |
| USG-09 | Negative | Line missing a required field | One complete line, one missing `whs_id` | The incomplete line is silently skipped, no warning |
| USG-10 | Negative | No detail lines at all | `addmore` empty/omitted | **Currently succeeds** — header created with zero lines, status `P`, approval generated anyway |
| USG-11 | Negative | Line qty exceeds available stock | Bypass the FEFO preview, post an oversized qty directly | **Currently succeeds at store()** — no sufficiency check until `approve()` |

### 5b. FEFO stock picking (`pickFefoStock`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-12 | Positive | Oldest-expiry-first split across two batches | Batch A (sooner expiry, qty 5), Batch B (later, qty 10); request qty 8 | Breakdown `[A:5, B:3]`, ordered by expiry |
| USG-13 | Positive | Single batch covers full qty | Request ≤ nearest batch's pickable qty | One-row breakdown |
| USG-14 | Negative | Insufficient total stock | Request qty > sum of all batches' pickable qty | 422 "Insufficient stock for {product}. Short by {N}." — no partial breakdown returned |
| USG-15 | Negative | qty = 0 or negative | `qty=0` | 422 |
| USG-16 | Negative | Product not found for company/type | Wrong product/cpny/vp_type combo | 404 |
| USG-17 | Negative | Batch fully reserved by other pending docs | `qty_available=5, qty_reserved=5` | That batch contributes 0, FEFO soft-skips to the next batch instead of erroring — verify this is intended |
| USG-18 | **Negative (gap)** | Expired batch included in FEFO pick | An already-expired batch has the earliest expiry among candidates | **Currently succeeds** — no `WHERE expired_date >= today` filter and `product_check_exp` is never consulted here; expired stock is picked first as if usable |
| USG-19 | **Negative (gap)** | Manual override of the FEFO suggestion | Edit the "Received" qty below/above the suggested amount in the preview table before submit | Server accepts whatever is in `addmore[]` with no re-validation that it matches what FEFO would have produced or that it stays within the batch's true `qty_available` — client-side `max` attribute is the only cap. Real bad-actor/tampering path to test directly with a forged POST. |

### 5c. Return-reference flow

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-20 | Positive | Valid return against a Completed Usage doc | `getReturnRefOptions`→pick doc→`getReturnRefDetails`→submit qty ≤ remaining | 200, Return doc created `status=P` |
| USG-21 | Negative | Return qty exceeds remaining returnable qty | qty > `qty_usage - qty_settlement` | 422 |
| USG-22 | Negative | Referenced Usage already settled | A `tr_vpl_settlement` row exists for it with status P/D/C | 422 "Cannot return: the referenced Usage document has already been settled." |
| USG-23 | Negative | Referenced doc already fully returned | Every line's `qty_settlement == qty_usage` | Still listed in the ref-options dropdown (only filters by status/scope, not remaining qty), but `getReturnRefDetails` returns zero lines — UX gap, not a hard block |
| USG-24 | Negative | Nonexistent `ref_usage_id`, with lines submitted | Bogus ref, at least one line | Blocked indirectly — `$remaining` resolves to 0, so the qty-exceeds-remaining check catches it |
| USG-25 | **Negative (gap)** | Nonexistent `ref_usage_id`, zero lines submitted | Bogus ref, no `addmore` | **Currently succeeds** — the per-line validation loop (which is the only place `ref_usage_id` gets indirectly checked) never runs when there are no lines; a Return doc referencing a non-existent parent gets created |
| USG-26 | **Negative (gap)** | Return references a Usage doc from a different department/company | Craft a direct POST bypassing the scoped dropdown | **Currently succeeds** — `store()` never re-validates that the referenced doc's cpny/dept/vp_type match the new Return doc's; only settlement status and per-line remaining qty are checked |
| USG-27 | Positive | Return completion restocks and caps the origin line | Approve a Return to completion | `finalizeStock()`: target batch `qty_available += qty`, `qty_reserved += qty`; origin line's `qty_settlement += qty` so it can't be returned twice |

### 5d. Detail line / attachment handling

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-28 | Positive | Delete a detail line pre-approval | `deleteDetail` on a `P`-status doc's line | Reservation released, row deleted |
| USG-29 | Negative | Delete a non-existent detail id | Bad id | 404 |
| USG-30 | **Negative (gap, significant)** | Delete a line with no ownership/status check | Any `USAGEVP,DELETE` user deletes a line on **someone else's, or an already-Completed**, document | **Currently succeeds** — `deleteDetail()` checks neither `created_user` nor document status, unlike cancel/edit. Deleting a line from a Completed doc after `finalizeStock()` already ran desyncs the stock reservation math with no error. |
| USG-31 | Negative | Attachment delete, same gap | Same pattern for `deleteAttachment` | Same finding — no ownership/status check |

### 5e. Edit / update (resubmit)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-32 | Positive | Edit while D, by creator | Add a new line, resubmit | New lines appended, reserved, fresh approval chain, `status→P` |
| USG-33 | Negative | Update missing remark | Blank | 422 |
| USG-34 | Negative | Update a Return doc whose origin got settled meanwhile | Settlement created against `ref_usage_id` between edits | 422 |
| USG-35 | **Negative (gap)** | No returnable-qty guard on update() | Add a line via `addmore` on resubmit whose qty exceeds the origin's remaining qty | **Currently succeeds** — the equivalent guard exists only in `store()`, not `update()` |
| USG-36 | **Negative (gap)** | `update()`'s insert loop has no DB transaction | Force a failure partway through a multi-line update | Unlike `store()`, no transaction wraps this loop — a mid-loop failure can leave partially-created, partially-reserved rows |
| USG-37 | **Negative (gap, significant)** | Edit a non-D-status doc via direct POST | Bypass the client's `can_edit` gate, POST `update()` on a C/R/X doc directly | **Currently succeeds** — `update()` has no server-side status check at all and unconditionally forces `status='P'`; only the UI flag prevents this normally |

### 5f. Cancel

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-38 | Positive | Cancel while D | Creator | Reservation fully released, `status→X` |
| USG-39 | Positive | Cancel while P, nothing approved | Creator | Allowed |
| USG-40 | Negative | Cancel while P after one approval | Any step already `A` | 403 |
| USG-41 | Negative | Cancel by non-creator | Different user, even with EDIT access | 403 |
| USG-42 | Negative | Cancel an already C/X/R doc | Terminal status | 403 |
| USG-43 | Negative | Cancel nonexistent id | Bad id | 404 |

### 5g. Approve / Reject / Revise

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| USG-44 | Positive | Approve final step, single approver | Sufficient stock | `status→C`, `finalizeStock()` deducts `qty_available`/`qty_reserved` |
| USG-45 | Positive | Approve intermediate step | Multi-level chain | Next level activated, header stays `P` |
| USG-46 | Negative | Insufficient stock at approval time | Stock dropped since submission | 422 "Approval failed! {product} (Expired: {date}) has insufficient stock." — checked for `usagetype='Usage'` only; confirm no equivalent invariant is skipped for `Return` |
| USG-47 | Negative | Approve by `VIEW`-only user, not an approver | Direct POST | 403 "You can't approve." |
| USG-48 | Negative | Out-of-turn approver | Level-2 before level-1 | 403 — note the side effect that even a rejected out-of-turn attempt can "activate" the correct step as a byproduct |
| USG-49 | Negative | Non-approver reject/revise | Same as USG-47 | 403 |
| USG-50 | Negative | Reject/Revise without message | Blank | 422 |
| USG-51 | Positive | Reject releases stock hold | Correct approver | `status→R`, reservation released |
| USG-52 | Positive | Revise releases stock hold | Correct approver | `status→D`, reservation released, pending steps closed |
| USG-53 | **Negative (gap, needs a concurrency test)** | Pending Return transiently inflates apparent free stock | Create a Return (qty 5) against a batch with `qty_available=10, qty_reserved=8` (2 truly free), then check `getUsageProducts`/`pickFefoStock` for that batch **before** the Return is approved | Per the reservation-delta math (`reserveDetail` decreases `qty_reserved` at Return *creation*, `finalizeStock` only increases `qty_available` at Return *approval*), `qty_pickable` may read higher than the true free stock during the pending window, letting a concurrent Usage request over-pick. Not confirmed by test data yet — worth deliberately reproducing. |

### Usage module — known gaps

1. Missing-approval-rule failure leaves a committed, reserved, orphaned document with an inconsistent error response (USG-05).
2. `deleteDetail`/`deleteAttachment` have no ownership or status check at all (USG-30/31).
3. `update()` has no server-side status gate — can resurrect a Completed/Cancelled/Rejected doc back to `P` via direct POST (USG-37).
4. FEFO picking includes already-expired batches with no filter or warning (USG-18).
5. No server-side re-validation that submitted lines match the FEFO breakdown or true batch availability at store/update time (USG-19).
6. `ref_usage_id` is never validated for existence or cross-department/company match when zero lines are submitted (USG-25/26).
7. `update()` is missing the returnable-qty guard that `store()` has (USG-35).
8. `update()`'s insert loop has no transaction wrapper, unlike `store()` (USG-36).
9. Pending Returns can transiently inflate apparent free stock for concurrent Usage requests (USG-53).
10. `ms_vpl_product_bal`/`trx_vpl_ledger` never written (confirms ARC-01/02).

---

## 6. Settlement Voucher (`SETTLEMENTVP`, doctype `VPS`, `/settlementvp`)

Module-specific preconditions: at least one **Completed** Usage doc with no active settlement and known per-line `qty_usage`/`qty_return_usage`; the `VPS`/`condition` category rows (`Settlement Voucher`/`Settlement Product`); matching approval rules. There is intentionally no `deleteDetail` route for Settlement — its detail table is fully derived from the selected Usage doc's lines (every usage line always renders with a settle-down-to-zero cell), not an add/remove UI, confirmed against the Blade view.

### 6a. Create (store)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| STL-01 | Positive | Create from a Completed, unsettled Usage doc | Valid `usage_id`, remark, one line within bounds | `status='P'`, docid `VPS{YYMM}{seq}`, usage-detail `qty_settlement`/`qty_remain` updated |
| STL-02 | Negative | Missing `usage_id` | Omit | 422 |
| STL-03 | Negative | Missing remark | Blank | 422 |
| STL-04 | Negative | Usage doc not Completed | `usage_id` status P/D/R/X, direct POST bypassing the picker | 422 "Referenced Usage document must be Completed." |
| STL-05 | Negative | No department/company access | Non-admin, no scope match | 403 |
| STL-06 | Negative | Double settlement (doc-level) | Create settlement A (any of P/D/C) for usage X, then attempt settlement B for the same X | 422 "This Usage document already has a settlement." — must hold whether A is P, D, or C |
| STL-07 | Positive | Re-settle after original was Rejected/Cancelled | Settlement A on usage X → R or X, attempt settlement B | **Currently succeeds** — `R`/`X` are excluded from the active-settlement-status check; confirm intended |
| STL-08 | **Negative (gap)** | Duplicate line within one request | `lines[]` contains the same `usage_detail_id` twice, each individually within bounds but summing over remaining | **Currently succeeds** — no dedup or cumulative check; two detail rows get written, `usage_detail.qty_settlement` reflects only the last line processed, leaving totals out of sync |
| STL-09 | Negative | `qty_settlement` out of bounds | Negative, or > remaining | 422 |
| STL-10 | Positive | Partial settlement | Subset of lines, or a line at qty 0 | Succeeds, `qty_remain` reflects the unsettled portion |
| STL-11 | **Negative (gap — one-shot design issue)** | Settle the remainder of a doc that already has a Completed partial settlement | Settlement A partially settles usage X and completes; attempt settlement B for the remainder | **Currently fails with "already has a settlement"** — the unsettled remainder becomes permanently unreachable through this UI once any settlement on that Usage doc reaches Completed. Confirm this is intended, not a design gap the report should account for. |
| STL-12 | Negative | Missing approval-condition category | No active condition row for the vp_type | 422, no orphan rows (rejected pre-commit) |
| STL-13 | **Negative (gap)** | No matching approval rule (category exists, `ms_approval` doesn't) | Category present, no rule/fallback | Header+detail rows already committed **before** `generateForDocument()` runs — result is an orphaned `status='P'` settlement with zero approval steps, stuck forever (though still cancellable since cancel only requires "no approved step") |

### 6b. Approve / Reject / Revise

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| STL-14 | Positive | Approve, correct in-turn approver | Active step's approver | If last step: `status→C`; else next level activated |
| STL-15 | Negative | Out-of-turn approver | Level-2 before level-1 | 403 |
| STL-16 | Negative | Non-approver, `VIEW`-only, direct POST | Right access, wrong approver | 403 — confirms route middleware alone isn't the real gate here either |
| STL-17 | **Informational — cannot verify from code** | Stock/ledger impact on approval completion | Approve to Completion, inspect `ms_vpl_product_bal`/`trx_vpl_ledger`/`trx_vpl_adjustment` before/after | Controller touches none of these — an inline comment attributes this to an external DB stored procedure not present in this repo. **Must be checked directly against the live DB** — this is the same question as ARC-01/02 but specifically for Settlement, which is the module most likely to be the write-point if one exists anywhere. |
| STL-18 | Negative | Reject without reason | Blank | 422 |
| STL-19 | Positive | Reject by correct approver | Message set | `status→R`, usage-detail rolled back to unsettled (re-check via STL-07-style test) |
| STL-20 | Positive | Revise by correct approver | Message set | `status→D`, usage-detail rolled back but settlement doc itself stays until `update()` — brief window where `qty_remain` on the usage side is inconsistent with the still-existing settlement doc; minor, not a hard bug |

### 6c. Edit / update / cancel

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| STL-21 | Positive | Update (resubmit) a Hold doc, by creator | Edit lines/remark | `status→P`, fresh approval chain |
| STL-22 | **Negative (gap, high priority)** | Update a non-Hold or non-owned doc via direct POST | POST `update` against a Completed, Rejected, or someone-else's On-Progress settlement | **Currently succeeds** — `update()` has zero status or ownership check, unconditionally overwrites lines and forces `status='P'`, re-triggering approval on an already-closed or foreign document |
| STL-23 | Negative | Cancel, not creator | Non-creator | 403 |
| STL-24 | Negative | Cancel, wrong status | C/R/X, or P with an already-approved step | 403 |
| STL-25 | Positive | Cancel, valid | Creator, D or unapproved-P | `status→X`, usage-detail rolled back, pending approval rows closed, usage doc becomes settleable again |
| STL-26 | **Negative (gap)** | Invalid/nonexistent `id` on approve/reject/revise/update/sendMessage | POST against a nonexistent settlement id | **Currently a 500 (fatal error)** — no null-check on `find($id)` in these 5 methods; only `cancel()` guards against it |

### 6d. Ajax endpoints

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| STL-27 | Positive | `getSettleableUsageOptions` scoping | Wrong vp_type/company/department | Excluded correctly |
| STL-28 | Negative | Same endpoints without department access | User lacking company/department access calls directly | Empty array returned, not an error — verify it fails closed (no data leak) |

### Settlement module — known gaps

1. Partial settlement is effectively one-shot: once any settlement on a Usage doc reaches Completed, the doc is permanently excluded from further settlement even if qty remains unsettled (STL-11).
2. No dedup of settlement lines within one request — the same usage-detail line can be double-settled inside a single document (STL-08).
3. `update()` has no status or ownership guard at all — can be used to reopen a Completed/Rejected/foreign settlement (STL-22).
4. Missing null-checks on `find($id)` in update/approve/reject/revise/sendMessage → 500 instead of 404 (STL-26).
5. Orphaned settlement possible if the approval rule/category is missing — commit happens before the approval-generation check (STL-13).
6. Stock/ledger/period-balance effect of settlement approval is entirely outside this codebase — must be verified against the live DB, not assumed either way (STL-17, ties into ARC-01/02).
7. Approve/Reject/Revise gated only by `VIEW` (STL-16), same pattern as every other transactional module.

---

## 7. Cross-cutting / access control

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| ACC-01 | Negative | Every VIEW-gated route hit without the module's VIEW right | All 5 modules | 403 |
| ACC-02 | Negative | Every CREATE/EDIT/DELETE-gated route hit without the matching right | All 5 modules | 403 (except the Master/Aging CREATE↔EDIT mismatches already called out as gaps, MST-17/AGE-06 — those are the one place this doesn't hold cleanly) |
| ACC-03 | Negative | Approve/Reject/Revise on Receive/Transfer/Usage/Settlement, held only by `VIEW` access, no route-level approve-specific right | User with the module's `VIEW` right, not an approver, direct POST | 403 via the inner `assertUserCanAct()` check in every case — confirm this holds consistently across all 4 modules, since it's the sole real gate everywhere it applies |
| ACC-04 | Positive | Menu visibility matches role's screen access | Compare sidebar for a role with vs. without each `*VP` right | Menu items appear/disappear correctly |
| ACC-05 | Negative | Notification recipient with no email on file | Trigger an approve/reject/revise notification for a user with no email | Confirm no exception; check whether an in-app `TrMessage` row is still written (pattern established in the Training module QA — same class of check applies here) |
| ACC-06 | Positive | `VPACCESS` role bypasses company scoping consistently | Compare a `VPACCESS` user's visibility vs. a scoped user's, across all 5 modules' list/export endpoints | Full-access role sees everything everywhere it's checked; confirm no module was missed |

---

## 8. Consolidated summary of known gaps (raise with dev/product before sign-off — not necessarily bugs to fix blindly)

**Architecture-level (affects all 4 transactional modules):**
1. `ms_vpl_product_bal` and `trx_vpl_ledger` are never written by any VPL controller in this codebase (ARC-01/02, RCV-34/35, TRF gap 7, USG gap 10, STL-17) — verify against the live DB whether anything else populates them before the upcoming report ledger work assumes either table is current.
2. Approve/Reject/Revise are gated only by each module's `VIEW` access right, not a dedicated approval right, across Receive/Transfer/Usage/Settlement — the only real protection is `ApprovalController::assertUserCanAct()`'s username match against the active step.
3. Cross-connection (`pgsql2` approval vs `pgsql5` documents) commit-ordering risk on final approval — demonstrated most clearly in Receive (RCV-32/ARC-03) and in Transfer's reject/revise reservation release (TRF gap 8); worth checking whether it also applies structurally to Usage/Settlement's completion path.
4. Detail-line validation on create is uniformly weak — invalid lines are silently dropped rather than rejected with a specific error, in Receive, Transfer, and Usage.
5. Several `update()` paths lack guards their sibling `store()` has: minimum line count (Receive), transaction wrapping (Usage), returnable-qty bound (Usage), status/ownership check entirely (Usage, Settlement).

**Master Product / Warehouse Setup:** see section 2's "known gaps" — most notable is `updateTargetDate()`'s per-product blanket overwrite (TGT-03) and the CREATE/EDIT access-right mismatch on the combined save-and-update endpoints (MST-17, AGE-06).

**Receive:** see section 3's "known gaps" — most notable is the missing stock ledger/period-balance writes and the cross-connection stuck-document risk on final approval (RCV-32).

**Transfer:** see section 4's "known gaps" — most notable is the total absence of a same-warehouse check (TRF-07/50) and the Revise→Update reservation-accounting risk (TRF-18).

**Usage:** see section 5's "known gaps" — most notable is `deleteDetail`/`deleteAttachment` having no ownership/status check at all (USG-30/31) and `update()` being able to resurrect a terminal-status document (USG-37).

**Settlement:** see section 6's "known gaps" — most notable is the one-shot partial-settlement design (STL-11) and `update()` having zero status/ownership guard (STL-22).

---

## 9. Cleanup — delete ALL QA test data

Because this module moves real inventory quantities, cleanup here is different from the Training/Ticket QA passes in this folder: **don't seed or touch real products/warehouses at all.** If section 1.1's synthetic-data approach was followed (dedicated synthetic products + warehouses, all real stock originating from a QA-created Receive doc), cleanup is a straightforward cascade delete with no balance math required — deleting the synthetic product/warehouse rows removes any corrupted balance along with them, since nothing else references that product/warehouse.

**Step 1** — Collect every synthetic doc ID created during QA (`VPR...`, `VPT...`, `VPU...`, `VPS...`), plus the synthetic `product_id`(s) and `whs_id`(s), from your running list kept during section 1.1.

**Step 2** — Run via `php artisan tinker`, confirming each model's connection first (`TrxVpl*`/`MsVpl*Detail` on `pgsql5`, `TrApproval` on `pgsql2`):

```php
$receiveIds    = ['VPR260800001', /* ... */];
$transferIds   = ['VPT260800001', /* ... */];
$usageIds      = ['VPU260800001', /* ... */];
$settlementIds = ['VPS260800001', /* ... */];
$allDocIds     = array_merge($receiveIds, $transferIds, $usageIds, $settlementIds);
$qaProductIds  = ['V1000XX', 'P1000XX']; // synthetic products only
$qaWhsIds      = ['QAWHS1', 'QAWHS2'];   // synthetic warehouses only

// Approval workflow rows (pgsql2) — confirm table/column names against
// App\Models\TrApproval / TrApprovalHistory before running.
App\Models\TrApproval::whereIn('refnbr', $allDocIds)->delete();
// If a TrApprovalHistory model/table exists for this engine, clear it too.

// Messages / comments tied to any of the QA docs
App\Models\TrMessage::whereIn('refnbr', $allDocIds)->delete();

// Attachments — confirm actual `doctype` values used per module before running
// (Master Product confirmed 'VPLPROD'; Receive/Transfer/Usage/Settlement doctype
// strings were not independently confirmed by this research pass — check via
// `TrAttachment::whereIn('docid', $allDocIds)->get()` first, then decide whether
// to flip status='X' (GCS-file convention, see eng_ticket plan) or hard-delete.
App\Models\TrAttachment::whereIn('docid', $allDocIds)->update(['status' => 'X']);

// Transactional documents (pgsql5) — details first, then headers
App\Models\TrxVplSettlementDetail::whereIn('settlement_id', $settlementIds)->delete();
App\Models\TrxVplSettlement::whereIn('settlement_id', $settlementIds)->delete();
App\Models\TrxVplUsageDetail::whereIn('usage_id', $usageIds)->delete();
App\Models\TrxVplUsage::whereIn('usage_id', $usageIds)->delete();
App\Models\TrxVplTransferDetail::whereIn('transfer_id', $transferIds)->delete();
App\Models\TrxVplTransfer::whereIn('transfer_id', $transferIds)->delete();
App\Models\TrxVplReceiveDetail::whereIn('receive_id', $receiveIds)->delete();
App\Models\TrxVplReceive::whereIn('receive_id', $receiveIds)->delete();

// Synthetic stock/master data — safe to hard-delete since nothing real
// references these IDs (per the section 1.1 isolation strategy)
App\Models\MsVplProductDetail::whereIn('product_id', $qaProductIds)->delete();
App\Models\MsVplProduct::whereIn('product_id', $qaProductIds)->delete();
App\Models\MsVplWarehouseDept::whereIn('whs_id', $qaWhsIds)->delete();
App\Models\MsVplWarehouse::whereIn('whs_id', $qaWhsIds)->delete();
```

**Step 3** — Sanity checks:
- Search each module's list view for the QA tag (`QAVPL-2026-08-06`) in remarks — result count must be 0.
- Confirm `ms_vpl_product` / `ms_vpl_warehouse` no longer contain the synthetic IDs.
- **Confirm no real product's `ms_vpl_product_detail.qty_available`/`qty_reserved` was ever touched** — this should hold automatically if section 1.1's isolation was followed, but verify explicitly since a mistake here corrupts real inventory reporting, not just test data.

**Step 4** — If any real approver inboxes/emails received live notifications during testing, send a short courtesy follow-up noting they were QA test messages.

---

## 10. Ready-to-use execution prompt

Paste the block below into a fresh Claude Code session (or reuse this one) to actually run the plan against this codebase:

```
Execute the QA test plan at agenthub/vpl_voucher_qa_test_plan.md for the VPL Voucher
module (Master, Receive, Transfer, Usage, Settlement).

Rules:
1. Follow section 1.1's synthetic-data strategy exactly: create dedicated synthetic
   products and warehouses tagged QAVPL-<today's date>, and let every stock quantity
   used in this pass originate from a Receive document created during the pass itself
   (do not seed ms_vpl_product_detail directly, and never touch a real product's or
   warehouse's balance). Keep a running list of every synthetic doc ID / product_id /
   whs_id as you go — cleanup in section 9 depends on it.
2. Run section 0's ARC-01/ARC-02/ARC-03 architecture-verification cases FIRST, since
   several later sections' "known gap" callouts assume their outcome. Report what you
   actually find in the live DB, not what the code implies.
3. Work through sections in the order given: Master → Receive → Transfer → Usage →
   Settlement → Cross-cutting/Access. Before running each transactional module's
   section, verify its ms_category condition rows and ms_approval rules exist for the
   synthetic company/department (section 1.3) — if any are missing, set them up
   yourself (synthetic-scoped) rather than skipping the section.
4. For each test ID, report PASS / FAIL / BLOCKED with the actual observed
   response/state — especially for anything marked "Negative (gap)" or "Informational,"
   since those were derived from reading the code, not from a feature spec, and are the
   most likely to reveal real bugs or confirm/refute the plan's assumptions.
5. Do not modify application code while testing unless you find and confirm an actual
   bug that blocks further testing (not a "gap" the plan already flags as ambiguous) —
   in that case stop, report the bug with file:line, and ask before fixing.
6. Run section 9's cleanup only after every case is signed off, and only ever delete
   the synthetic data your own run created — never touch pre-existing real VPL data.
7. At the end, produce: a consolidated results table (mirroring the format used in
   agenthub/training_module_qa_test_plan.md's and agenthub/eng_ticket_qa_test_plan.md's
   execution-results sections), a FAILs list with file:line for each, and explicit
   answers to ARC-01/ARC-02 (does anything actually populate ms_vpl_product_bal /
   trx_vpl_ledger right now, yes or no, checked directly against the DB) since that
   result should inform the upcoming report-ledger work this QA pass is meant to
   precede.
```
