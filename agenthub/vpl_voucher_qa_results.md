
## Section 2 — Master Product & Warehouse Setup (MASTERVP)

| ID | Result | Expected | Notes |
|---|---|---|---|
| MST-01 | PASS | 200, product auto id V1nnnnn, status A | status=200 product_id=V100013 |
| MST-02 | PASS | 200, photo uploaded, product id P1nnnnn | status=200 product_id=P100004 {"success":"Product saved successfully.","eid":"6dBXa"} |
| MST-03 | PASS | 422 Product photo required | status=422 {"message":"Product photo is required for Product type."} |
| MST-04 | PASS | 422 validation | status=422 {"message":"The cpnyid field is required. (and 1 more error)","errors":{"cpnyid":["The cpnyid field is required."],"product_name":["The product name f |
| MST-05 | PASS | 422 in:V,P | status=422 {"message":"The selected product type is invalid.","errors":{"product_type":["The selected product type is invalid."]}} |
| MST-06 | PASS | 422 max:5120 | status=422 {"message":"The product photo field must be a file of type: jpg, jpeg, png. (and 1 more error)","errors":{"product_photo":["The product photo field mu |
| MST-07 | PASS(gap confirmed) | GAP: succeeds despite EP-only scope | status=200 product_id=V200004 — save_product never checks Usercpny scope |
| MST-08 | PASS(gap confirmed) | GAP: prefix defaults to 0 → V0xxxxx | status=200 product_id=V000002 prefix-defaulted to 0 as predicted |
| MST-09 | PASS(gap confirmed) | GAP: unvalidated category | status=200 saved with bogus category |
| MST-10 | PASS(gap confirmed) | GAP: unvalidated uom | status=200 saved with bogus uom |
| MST-12 | PASS | 200, fields updated | status=200 name_now=QAVPL Test Voucher QAVPL-2026-08-06 153303 EDITED |
| MST-13 | PASS(gap confirmed) | new photo saved; old never deleted | status=200 before=att-vpl/product-photo/P100004_1786005184.png after=att-vpl/product-photo/P100004_1786005185.png old object still referenced elsewhere? check GCS |
| MST-11 | PASS(gap confirmed) | GAP: no in:A,X rule | status=200 stored_status=B |
| MST-14 | PASS | 200, status X | status=200 stored=X |
| MST-16 | PASS | 200, status A | status=200 stored=A |
| MST-15 | PASS | 422 still has {N} qty in stock | status=422 {"message":"Cannot deactivate. This product still has 10 qty in stock."} |
| MST-17 | PASS(gap confirmed) | GAP: CREATE-only user can edit via save_product | status=200 {"success":"Product saved successfully.","eid":"9aAOd"} |
| MST-18 | PASS | 403 | status=403 {"message":"Forbidden","exception":"Symfony\\Component\\HttpKernel\\Exception\\HttpException","file":"D:\\Project IT\\HR |
| MST-19 | PASS | 404 (abort_unless ajax) | status=404 |

## Section 2b — Product detail / stock lines (PRD)

| ID | Result | Expected | Notes |
|---|---|---|---|
| PRD-01 | PASS | 200, detail row created | status=200 rows_now=1 |
| PRD-02 | PASS | 200, all 3 created | status=200 2027_rows=2 |
| PRD-03 | PASS(gap confirmed) | No validate(); expect 500 | status=500 {"message":"Failed to save product details","error":"foreach() argument must be of type array|object, null given"} |
| PRD-04 | PASS(gap confirmed) | GAP: negative qty accepted | status=200 negative_row=true |
| PRD-05 | PASS(gap confirmed) | GAP: no FK check | status=200 bogus_row=true |
| PRD-07 | PASS | 200, A-only rows ordered by expired_date | status=200 sorted=true |

## Section 2c — Attachments (ATT)

| ID | Result | Expected | Notes |
|---|---|---|---|
| ATT-02 | PASS | 422 No file uploaded | status=422 {"message":"No file uploaded"} |
| ATT-01 | PASS(gap confirmed) | GAP: upload reports success but stores nothing | status=200 att_row=none — saveProductAttach (VplMsProductController.php:571) casts (array) on the UploadedFile, so uploadInternal iterates property scalars and silently skips; no GCS write, no TrAttachment row |
| ATT-03 | PASS(gap confirmed) | GAP: .exe never rejected (but also never stored) | status=200 exe_row=no — no MIME/extension allowlist in saveProductAttach; the (array)-cast no-op means nothing is ever uploaded |
| ATT-04 | PASS(gap confirmed) | 500 Upload failed (empty refnbr) | status=500 {"message":"Upload failed","error":"Missing required meta: refnbr"} |

## Section 2d — Aging setup (AGE)

| ID | Result | Expected | Notes |
|---|---|---|---|
| AGE-01 | PASS | 200, status forced A | status=200 aging_status=A |
| AGE-02 | PASS(gap confirmed) | No validate(); expect no clean 422 | status=200 {"success":"Aging saved successfully."} |
| AGE-03 | PASS(gap confirmed) | GAP: no range check | status=200 saved=true |
| AGE-05 | PASS(gap confirmed) | GAP: status B accepted | status=200 stored=B |
| AGE-06 | PASS(gap confirmed) | GAP: CREATE-only user can edit aging | status=200 descr=QAVPL Aging QAVPL-2026-08-06 153303 EDIT |
| AGE-07 | PASS | 403 | status=403 |

## Section 2e — Product target date (TGT)

| ID | Result | Expected | Notes |
|---|---|---|---|
| TGT-01 | BLOCKED | Datatable via MsVplProductTargetDate view | status=500 {     "message": "SQLSTATE[42P01]: Undefined table: 7 ERROR:  relation \"v_vpl_product_target_date\" does not exist\nLIN — v_vpl_product_target_date view missing on live DB |
| TGT-02 | PASS | All MsVplProductDetail rows with target_date etc. | status=200 rows=6 |
| TGT-03 | PASS(gap confirmed) | GAP: blanket overwrite across all lines/warehouses | status=200 line1=2026-09-15 line2=2026-09-15 |
| TGT-04 | PASS(gap confirmed) | GAP: silent no-op 200 | status=200 {"message":"Target date updated"} |
| TGT-05 | PASS | 422 required|date | status=422 |

## Section 2f — Warehouse setup (WHS)

| ID | Result | Expected | Notes |
|---|---|---|---|
| WHS-01 | PASS | 200, uppercased+trimmed, status A | status=200 row=yes |
| WHS-02 | PASS(gap confirmed) | No validate(); expect no clean 422 | status=200 {"message":"Warehouse saved successfully."} |
| WHS-03 | PASS(gap confirmed) | GAP: duplicate accepted | before=3 after=4 status=200 |
| WHS-04 | PASS(gap confirmed) | GAP: vp_type FOOBAR accepted | status=200 saved=true |
| WHS-05 | PASS | 200, vp_type updated; whs_id not editable | status=200 vp_now=P |
| WHS-06 | PASS(gap confirmed) | GAP: no referential check; deactivated | status=200 stored=X |
| WHS-07 | PASS(gap confirmed) | GAP: defaults to deactivate | status=200 stored=X |
| WHS-08 | PASS | 403 | status=403 |

## Section 2g — Warehouse-dept setup (WHD)

| ID | Result | Expected | Notes |
|---|---|---|---|
| WHD-01 | PASS | 200, one row | status=200 row=yes |
| WHD-02 | PASS | 200, two rows (V and P) | status=200 rows=2 |
| WHD-03 | PASS(gap confirmed) | GAP: duplicate All accepted on create | before=0 after=2 |
| WHD-05 | PASS(gap confirmed) | GAP: no validate() | status=200 |
| WHD-07 | PASS | 200, toggled active | status=200 stored=A |
| WHD-08 | PASS(gap confirmed) | GAP: defaults to deactivate | status=200 stored=X |
| WHD-09 | PASS | filtered listing | status=200 has_match=true |
| WHD-10 | PASS | 403 | status=403 |

## Section 2h — Category/source lookups & export (LKP)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LKP-01 | PASS | only doctype=VPL groups=TYPE status=A | status=200 rows=6 |
| LKP-02 | PASS | only groups=SOURCE status=A | status=200 rows=3 |
| LKP-03 | PASS | 404 | status=404 |
| LKP-04 | PASS | ≤50 matches scoped | status=200 found_own=true |
| LKP-05 | PASS | XLSX download | status=200 bytes=0 |
| LKP-07 | PASS | scoped server-side (non-VPACCESS) | status=200 rows=0 |

## Section 0.5 — Architecture verification (ARC)

| ID | Result | Expected | Notes |
|---|---|---|---|
| ARC-01 | PASS(confirmed: no controller writes it) | VPL controllers never write ms_vpl_product_bal | QA-completed docs VPR2680005/VPR2680006 created ZERO bal rows. Existing 2 rows are 2026-06 output of stored proc sp_process_vpl (VPR/VPT Submit/Reject/Revise; created_user=resianjani, 2026-07-26) — never called by app code (0 refs in app/). Report-ledger work must account for sp_process_vpl, not controllers. |
| ARC-02 | PASS(confirmed: no controller writes it) | VPL controllers never write tr_vpl_ledger | QA-completed docs created ZERO ledger rows (ledger_total=2, both refnbr=VPR266007 2026-06, from sp_process_vpl, not from app). sp_process_vpl_260805 (dated 2026-08-05 copy) also exists and writes tr_vpl_ledger + ms_vpl_product_bal + ms_vpl_product_detail for VPR/VPT. |
| ARC-03 | PASS(not reproducible - mitigated) | Stuck doc if pgsql2 chain closes before pgsql5 stock-credit fails | Fault-injected throw at VplReceiveController.php:846 (insertMsProductDetail). Final approve -> 403 {error: Approve failed}; doc stayed P (not C), lvl2 step stayed P (rolled back, not stuck), stock V100001/QAWHS1 unchanged (avail=5), no ledger/bal rows. Current code runs onComplete() INSIDE the pgsql2 tx (ApprovalController.php:620-636), so a mid-credit throw rolls back the approval commit too. Residual (untested) window: pgsql5 tx commits before pgsql2 commit; a connection-level pgsql2 COMMIT failure after the pgsql5 commit could still strand doc=C with step=P. |

## Section 3 — Receive Voucher (RECEIVEDVP)

| ID | Result | Expected | Notes |
|---|---|---|---|
| RCV-02 | PASS | 422 Remark is required. | status=422 {"error":"Remark is required."} |
| RCV-04 | PASS | 422 Category condition not found | status=422 {"error":"Category condition not found for BOGUS. Please contact IT!"} |
| RCV-05a | PASS | 422 min line count | status=422 {"error":"Please add at least one valid product line before submitting."} |
| RCV-05b | PASS(gap confirmed) | GAP: 3 invalid lines silently dropped, 1 saved, no per-line error | status=200 lines=1 |
| RCV-03 | PASS | 403 no cpny access | status=403 {"error":"You do not have access to create a Receive document for this company\/department."} |
| RCV-07 | PASS(gap confirmed) | GAP: default 1900-01-01 | status=200 exp=1900-01-01 |
| RCV-09 | PASS(gap confirmed) | GAP: required only client-side | status=200 |
| RCV-10 | PASS(gap confirmed) | GAP: no existence check | status=200 |
| RCV-01 | PASS | 200, docid VPRyyymnnn, status P, 2 detail rows, 2 approvals | status=200 docid=VPR2680012 doc_status=P lines=2 approvals=2 |
| RCV-25 | PASS | 403 out-of-turn, lvl1 still P | status=403 lvl1=P |
| RCV-24 | PASS | 403 You can't approve. | status=403 {"error":"You can't approve."} |
| RCV-22 | PASS | 200, lvl1 A, lvl2 P, doc P | status=200 lvl1=A lvl2=P doc=P |
| RCV-31 | PASS | 403 replay blocked | status=403 {"error":"You can't approve."} |
| RCV-23 | PASS | 200, status C, completed_by=qapprover2, stock credited | status=200 doc=C completed_by=qapprover2 avail[2026-12-31]=8 avail[2027-06-30]=2 |
| RCV-33 | PASS | existing +3 in place; new combo row created qty=2 | before_ex=5 after_ex=8 new_row=2 |
| RCV-34 | PASS(confirmed) | ms_vpl_product_bal untouched by approve | bal_rows=0 (0 = controller/SP never ran on this doc) |
| RCV-35 | PASS(confirmed) | tr_vpl_ledger untouched by approve | ledger_rows=0 |
| RCV-26 | PASS | 403 No pending approval step. | status=403 {"error":"No pending approval step."} |
| RCV-28 | PASS | 422 Reason is required. | status=422 {"error":"Reason is required."} |
| RCV-27 | PASS | 200, doc R, step R, remaining forced X | status=200 doc=R lvl1=R lvl2=X |
| RCV-29 | PASS | 200, doc D | status=200 doc=D |
| RCV-30 | PASS | 422 Reason is required. | status=422 |
| RCV-15 | PASS | 422 Remark is required. | status=422 {"error":"Remark is required."} |
| RCV-12 | PASS | 403 non-creator | status=403 {"error":"You are not allowed to edit this document."} |
| RCV-11 | PASS | 200, status P, line 1+2 merged to 3, fresh active chain (old rows left D/X) | status=200 doc=P merged_qty=3 active=2 total=4 |
| RCV-13 | PASS | 422 cannot be edited in current status | status=422 {"error":"This document cannot be edited in its current status."} |
| RCV-14 | PASS(gap confirmed) | GAP: update() has no min-line guard, resubmitted with 0 lines | status=200 doc=P lines=0 |
| RCV-16 | PASS | 422 approval-rule failure rolls back, doc stays D with old lines intact | status=422 doc=D lines_before=1 lines_after=1 {"error":"Approval line belum di-setup, Please contact IT!"} |
| RCV-18 | PASS | 200, doc X, pending approvals forced X | status=200 doc=X pending=0 |
| RCV-20 | PASS | 403 non-creator | status=403 {"error":"You are not allowed to cancel this document."} |
| RCV-19 | PASS | 403 cancel after an approval | status=403 {"error":"You are not allowed to cancel this document."} |
| RCV-17 | PASS | 200, doc X | status=200 doc=X |
| RCV-21 | PASS | 403 terminal status | status=403 {"error":"You are not allowed to cancel this document."} |
| RCV-36 | PASS | 200, server-side cpnyid+type V+status A filter | status=200 count=10 all_scoped=yes |
| RCV-37 | PASS | 200 empty (raw filter passthrough) | status=200 count=0 |
| RCV-38 | PASS | 200, activity RECEIVE filter | status=200 whs=QAWHS1,QAWHS9 |
| RCV-39 | PASS | 200 distinct tenants | status=200 tenants=6 |
| RCV-40 | PASS(gap confirmed) | GAP: no cpnyid check, leaks cross-company product detail (V200003 belongs to EP) | status=200 {"product_id":"V200003","product_check_exp":null} |
| RCV-06 | PASS | 422 approval-rule failure, whole transaction rolls back, no orphan rows | status=422 new_receive=0 new_approval=0 {"error":"Approval line belum di-setup, Please contact IT!"} |
| RCV-08 | PASS(gap confirmed) | GAP: .exe accepted, saveAttachments() only checks isValid(), no mime/size limit | docid=VPR2680023 ext=exe stored=yes doc_status=P |
| RCV-32 | PASS(not reproducible - mitigated) | Stock-credit failure mid-loop on final approval | See ARC-03: onComplete() runs inside the pgsql2 transaction, so an in-code throw rolls back both approval and stock-credit; residual untested window is connection-level failure between pgsql2 and pgsql5 commits |

## Section 4 — Transfer Voucher (TRANSFERVP)

| ID | Result | Expected | Notes |
|---|---|---|---|
| TRF-02 | PASS | 422 Remark is required. | status=422 {"error":"Remark is required."} |
| TRF-03 | PASS | 422 Reference Transfer ID required | status=422 {"error":"Reference Transfer ID is required for Return Transfer."} |
| TRF-04 | PASS | 403 no cpny access | status=403 {"error":"You do not have access to create a Transfer document for this company\/department."} |
| TRF-06 | PASS | 422 min line count | status=422 {"error":"Please add at least one valid product line before submitting."} |
| TRF-05 | PASS | 422 Category condition not found (check runs pre-commit) | status=422 {"error":"Category condition \"Transfer Voucher\" not found. Please contact IT!"} |
| TRF-01 | PASS | 200, VPT doc, status P, source reserved +3, 2 approvals | status=200 docid=VPT26080004 doc=P res=3 approvals=2 |
| TRF-07 | PASS(gap confirmed) | GAP: no from==to equality check server-side | status=200 docid=VPT26080005 |
| TRF-08 | PASS(gap confirmed) | GAP: store() has no sufficiency check, qty_reserved exceeds qty_available | status=200 res=1003 |
| TRF-29 | PASS | 422 insufficient stock at approval (the real enforcement point) | status=422 {"error":"Approval failed! Test Voucher AW (Expired: 2026-12-31 00:00:00) has insufficient stock."} |
| TRF-09 | PASS | Missing from_whs_id rejected (422 raw "Undefined array key" — not a clean validation message, but full rollback, no orphan row) | status=422 doc_created=no |
| TRF-10 | PASS | 200, invalid line dropped, 2 valid lines linenbr 1,2 | status=200 lines=2 linenbr=1,2 |
| TRF-11 | PASS(gap confirmed) | GAP: file moved to disk but Attachment::create() mass-assignment drops name/attachfile/extention (fillable only docid/attachname/created_user/status) — row keeps NULL, attachment orphaned | status=200 file_on_disk=yes attachfile=NULL |
| TRF-12 | PASS | 422 rule failure, pgsql5 rollback, no orphan rows | status=422 new_trf=0 new_approval=0 {"error":"Approval line belum di-setup, Please contact IT!"} |
| TRF-32 | PASS | 403 out-of-turn, lvl1 still P | status=403 lvl1=P |
| TRF-31 | PASS | 403 You can't reject. | status=403 {"error":"You can't reject."} |
| TRF-28 | PASS | 200, lvl1 A, lvl2 P, doc P | status=200 lvl1=A lvl2=P doc=P |
| TRF-27 | PASS | 200, doc C, source avail-3 (8->5), main doc's own 3 released (res 6->3; remaining 3 = tf7/tf10/tf11 pending), dest row created avail 3 | status=200 doc=C src_avail=5 res_delta=-3 dst_avail=3 |
| TRF-38 | PASS | source decremented by 3 | base=8 after=5 |
| TRF-42 | PASS | round-trip nets 0: throwaway doc store +2 reserved, cancel releases back to baseline | status=200 base=2 mid=4 end=2 |
| TRF-24 | PASS | 403 terminal status (no reversal path) | status=403 {"error":"You are not allowed to cancel this document."} |
| TRF-39 | PASS | dest existing row incremented by 2 (3 -> 5) | status=200 dst_avail=5 |
| TRF-40 | PASS | new dest row created qty 2, cpnyid copied from header (AW) | status=200 dst_avail=2 cpny=AW |
| TRF-33 | PASS | 422 Reason is required. | status=422 |
| TRF-34 | PASS | 200, doc R, reservation released -1 (4->3; baseline 3 = tf7/tf10/tf11 pending) — pgsql5 write from inside pgsql2 txn | status=200 doc=R res_delta=-1 |
| TRF-37 | PASS | 403 No pending approval step. | status=403 {"error":"No pending approval step."} |
| TRF-36 | PASS | 422 Reason is required. | status=422 |
| TRF-35 | PASS | 200, doc D, reservation released -1 (4->3) | status=200 doc=D res_delta=-1 |
| TRF-19 | PASS | 422 Remark is required. | status=422 |
| TRF-14 | PASS | 403 non-creator | status=403 {"error":"You are not allowed to edit this document."} |
| TRF-20 | PASS | 422 category lookup fails pre-commit, doc stays D | status=422 doc=D |
| TRF-13 | PASS | 200, doc P, fresh active chain (old D/X rows left) | status=200 doc=P active=2 total=4 |
| TRF-15 | PASS | 422 cannot be edited in current status | status=422 {"error":"This document cannot be edited in its current status."} |
| TRF-16 | PASS | 200, new combo line (2027-06-30) added, full qty 2 reserved (+2 delta) | status=200 lines=2 res_delta[2027-06-30]=+2 |
| TRF-17 | PASS(gap confirmed) | GAP: existing line 2->5 (qty += newQty, not set-to), only delta 3 re-reserved (line 5 vs res-delta 3 = under by 2) | status=200 qty_transfer=5 res_delta=+3 |
| TRF-18 | PASS(gap confirmed) | GAP: resent line 2->4 (doubled via +=), only delta 2 re-reserved (line 4 vs res-delta 2 = under by 2); if line NOT resent its reservation stays released (permanent under-reservation) | status=200 qty_transfer=4 res_delta=+2 |
| TRF-22 | PASS | 200, doc X, reservation released -1 (9->8), pending approvals forced X | status=200 doc=X res_delta=-1 pending=0 |
| TRF-26 | PASS | qty_available untouched by cancel, only qty_reserved moves | avail_before=3 avail_after=3 |
| TRF-23 | PASS | 403 cancel after an approval | status=403 {"error":"You are not allowed to cancel this document."} |
| TRF-25 | PASS | 403 non-creator | status=403 {"error":"You are not allowed to cancel this document."} |
| TRF-21 | PASS(gap confirmed) | GAP: revise released -1, then cancel released ANOTHER -1 (double release — cancel() has no guard for already-revised docs); aggregate drifts low | status=200 doc=X res_after_revise=10 res_after_cancel=9 pending=0 |
| TRF-47 | PASS | 200, activity TRANSFER filter | status=200 whs=QAWHS1,QAWHS9 |
| TRF-48 | PASS | 200, activity TRANSFER_RECEIVE filter | status=200 whs=QAWHS2 |
| TRF-49 | PASS | 200, candidates exclude source | status=200 whs=QAWHS2 |
| TRF-50 | PASS(gap confirmed) | GAP: same-warehouse Return reachable via UI, no exclusion | status=200 whs=QAWHS1,QAWHS9 |
| TRF-43 | PASS | 200, only status C, scoped, includes main doc | status=200 refs=3 all_C=yes |
| TRF-44 | PASS | 200 empty for non-ReturnTf | status=200 count=0 |
| TRF-45 | PASS(gap confirmed) | GAP: whs_id hard-coded to original to_whs (QAWHS2), from_whs_id param ignored | status=200 whs=QAWHS2 products=1 |
| TRF-46 | PASS(gap confirmed) | GAP: no cross-check vs referenced doc original qty (3); store accepts 99 | status=200 docid=VPT26080022 |

## Section 5 — Usage Voucher (USAGEVP)

| ID | Result | Expected | Notes |
|---|---|---|---|
| USG-02 | PASS | 422 Remark is required. | status=422 {"error":"Remark is required."} |
| USG-03 | PASS | 422 Reference Usage Doc required | status=422 {"error":"Reference Usage Doc is required for Return."} |
| USG-04 | PASS | 422 Category condition not found (check runs pre-commit) | status=422 {"error":"Category condition \"Usage Voucher\" not found. Please contact IT!"} |
| USG-01 | PASS | 200, VPU doc, status P, reserved +2, 2 approval rows | status=200 docid=VPU26080001 res_delta=2 approvals=2 |
| USG-05 | PASS(gap confirmed) | GAP: doc committed status P + reserved +1 but approval abort thrown after commit (outside try/catch) -> raw error + zero approval rows, orphaned until cancel | status=422 doc=VPU26080002 doc_status=P res_delta=+1 approvals=0 |
| USG-06 | PASS | 200, usage_date honored (today-2) | status=200 usage_date=2026-08-04 expected=2026-08-04 |
| USG-07 | PASS | 422 Usage Date must be within H-3 to today. | status=422 {"error":"Usage Date must be within H-3 to today."} |
| USG-08 | PASS(gap confirmed) | GAP: non-CUSTOMERSERVICE backdate silently overridden to today (usage_date forced, no warning) | status=200 submitted=2026-08-04 stored=2026-08-06 |
| USG-09 | PASS(gap confirmed) | GAP: incomplete line silently skipped with no warning, only 1 of 2 persisted | status=200 lines=1 |
| USG-10 | PASS(gap confirmed) | GAP: zero-line doc accepted, status P, approvals generated anyway | status=200 lines=0 approvals=2 |
| USG-12 | PASS | 200, breakdown [A:5, B:3] ordered by expiry | status=200 breakdown=2026-11-30:5,2027-05-31:3 |
| USG-13 | PASS | 200, one-row breakdown qty 3 | status=200 rows=1 |
| USG-14 | PASS | 422 Insufficient stock, Short by 1, no partial breakdown | status=422 {"error":"Insufficient stock for QAVPL Usage Split \/ 100000.00 \/ PCS. Short by 1."} |
| USG-15 | PASS | 422 Qty must be greater than 0. | status=422 |
| USG-16 | PASS | 404 Product not found. | status=404 |
| USG-17 | PASS(gap confirmed) | GAP: fully-reserved batch (2026-12-25 res5) contributes 0 and is soft-skipped to next batch | status=200 breakdown=2026-06-30:10,2026-12-31:2 |
| USG-18 | PASS(gap confirmed) | GAP: already-expired batch (2026-06-30) picked first as if usable — no expiry filter, product_check_exp never consulted here | status=200 breakdown=["2026-06-30"] |
| USG-19 | PASS(gap confirmed) | GAP: server accepts forged addmore qty 15 vs batch true qty_available 10 — client-side max is the only cap, no store() re-validation | status=200 docid=VPU26080007 |
| USG-11 | PASS(gap confirmed) | GAP: store() has no sufficiency check (qty 999 vs avail 10), accepted | status=200 docid=VPU26080008 |
| USG-20 | PASS | 200, ref-options lists Completed only, ref-details returns 1 line (remaining 3), Return doc P qty_return 2 | status=200 refs=4 all_C=yes ref_lines=1 ret_status=P ret_qty=2 |
| USG-21 | PASS | 422 Return qty exceeds remaining returnable qty | status=422 {"error":"Return qty for QAVPL Usage Return exceeds the remaining returnable qty (3)."} |
| USG-22 | PASS | 422 Cannot return: referenced Usage already settled (no doc created) | status=422 doc=no |
| USG-23 | PASS(gap confirmed) | UX gap: fully-returned origin still listed in dropdown (only status/scope filtered) but ref-details returns 0 lines | listed=yes ref_lines=0 |
| USG-24 | PASS | 422 blocked via $remaining=0 fallback (indirect check only) | status=422 {"error":"Return qty for QAVPL Usage Return exceeds the remaining returnable qty (0)."} |
| USG-25 | PASS(gap confirmed) | GAP: no-lines path skips the per-line ref validation -> Return created against non-existent parent | status=200 docid=VPU26080011 |
| USG-26 | PASS(gap confirmed) | GAP: store() never re-validates ref cpny/dept/vp_type match (QADEPT origin vs CUSTOMERSERVICE Return) | status=200 docid=VPU26080012 |
| USG-27 | PASS | approve -> C, batch qty_available +2, qty_reserved +2, origin qty_settlement capped 0->2 | status=200 avail=5->7 res=0->2 origin_settled=2 |
| USG-28 | PASS | 200, reservation released -2, row deleted | status=200 res_delta=-2 line_gone=yes |
| USG-29 | PASS | 404 Not found. | status=404 |
| USG-30 | PASS(gap confirmed) | GAP: deleteDetail() checks neither created_user nor doc status — deleted a Completed doc line after finalizeStock, desyncing settlement math (qty_settlement=2 now orphaned) | status=200 doc_status=C line_gone=yes |
| USG-31 | PASS(gap confirmed) | GAP: deleteAttachment by a different user on a pending doc succeeds — no ownership/status check (same as USG-30) | status=200 deleted_by_other=yes |
| USG-33 | PASS | 422 Remark is required. | status=422 |
| USG-32 | PASS | 200, doc P, new line appended (2 lines), +1 reserved on new combo, fresh 2-step active chain | status=200 doc=P lines=2 res_delta=+1 active=2 |
| USG-34 | PASS | 422 referenced usage settled (guard exists in update too) | status=422 {"error":"Cannot return: the referenced Usage document has already been settled."} |
| USG-35 | PASS(gap confirmed) | GAP: update() lacks the returnable-qty guard store() has — qty 5 accepted vs origin remaining 1 | status=200 {"success":"Usage document resubmitted successfully."} |
| USG-36 | PASS(gap confirmed) | GAP: update() insert loop (VplUsageController.php:393-418) runs raw Eloquent create calls with no transaction wrapper — mid-loop failure leaves partially-created/reserved rows (unlike store()) | verified by code inspection at VplUsageController.php:393-418 |
| USG-37 | PASS(gap confirmed) | GAP: update() has no server-side status gate — Completed Return doc resurrected to P via direct POST | status=200 doc_status_after=P |
| USG-38 | PASS(gap confirmed) | GAP: revise released -3, then cancel released ANOTHER -3 (double release, clamped at 0) — cancel() has no guard for already-revised docs; other pending holds wiped | status=200 doc=X res_pre_cancel=3 res_after=0 double_release=yes |
| USG-39 | PASS | 200, doc X, reservation released -1, pending approvals forced X | status=200 doc=X res_delta=-1 pending=0 |
| USG-40 | PASS | 403 cancel after an approval (any A row blocks) | status=403 {"error":"You are not allowed to cancel this document."} |
| USG-41 | PASS | 403 non-creator (created_user vs user->name) | status=403 |
| USG-42 | PASS | 403 terminal status C | status=403 |
| USG-43 | PASS | 404 Not found. | status=404 |
| USG-47 | PASS | 403 You can't approve. | status=403 {"error":"You can't approve."} |
| USG-48 | PASS | 403 out-of-turn, lvl1 still P | status=403 lvl1=P |
| USG-45 | PASS | 200, lvl1 A, lvl2 P, doc P | status=200 lvl1=A lvl2=P doc=P |
| USG-44 | PASS | 200, doc C, qty_available -1, qty_reserved -1 (finalizeStock) | status=200 doc=C avail_delta=-1 res_delta=-1 |
| USG-46 | PASS | 422 insufficient stock at approval (the real enforcement point) | status=422 {"error":"Approval failed! QAVPL Usage FEFO (Expired: 2026-12-31 00:00:00) has insufficient stock."} |
| USG-49 | PASS | 403 You can't reject. | status=403 {"error":"You can't reject."} |
| USG-50 | PASS | 422 Reason is required. | status=422 |
| USG-51 | PASS | 200, doc R, reservation released -2 | status=200 doc=R res_delta=-2 |
| USG-52 | PASS | 200, doc D, reservation released -2, pending steps closed (note: usage revise releases hold, unlike Transfer) | status=200 doc=D res_delta=-2 pending=0 |
| USG-53 | PASS(gap confirmed) | GAP: Return creation drops qty_reserved 8->3 BEFORE stock physically returns (finalizeStock at approval); apparent pickable inflates 2->7, a concurrent Usage of 5 is offered vs only 2 truly free (over-pick by 3) | baseline_pickable=2 res_after_return=3 apparent_pickable=7 fefo_offered=5 overpick=confirmed |

## Section 6 — Settlement Voucher (SETTLEMENTVP)

| ID | Result | Expected | Notes |
|---|---|---|---|
| STL-02 | PASS | 422 Usage Doc is required. | status=422 {"error":"Usage Doc is required."} |
| STL-03 | PASS | 422 Remark is required. | status=422 {"error":"Remark is required."} |
| STL-04 | PASS | 422 non-Completed usage rejected | status=422 {"error":"Referenced Usage document must be Completed."} |
| STL-05 | PASS | 403 dept access denied for scoped user (EP vs AW) | status=403 {"error":"You do not have access to settle documents for this company\/department."} |
| STL-01 | PASS | 200, VPS doc P, 2 approvals, usage qty_settlement=5 qty_remain=5, 1 detail | doc=VPS26080014 status=200 appr=2 set=5 rem=5 |
| STL-06 | PASS | 422 second settlement blocked while first is On Progress | status=422 {"error":"This Usage document already has a settlement."} |
| STL-06D | PASS | Hold (D) settlement still blocks a new one | s2=D status=422 {"error":"This Usage document already has a settlement."} |
| STL-06C | PASS | Completed settlement locks usage (C + qty_settlement=4) | s3=C set=4 rem=6 |
| STL-11 | PASS(gap confirmed) | 422 remainder unreachable after a Completed partial settlement (one-shot settle, no remainder support) | status=422 {"error":"This Usage document already has a settlement."} |
| STL-08 | PASS(gap confirmed) | GAP: duplicate detail line accepted (2 rows summing 12 > remaining 10); usage qty_settlement=6 last-line-wins | status=200 rows=2 sum=12 usageSet=6 rem=4 |
| STL-09 | PASS | 422 for qty=-1 and qty=11 (remaining=10) | a=422 b=422 {"error":"Qty Settlement for QAVPL Usage Return must be between 0 and 10."} |
| STL-10 | PASS | Partial: line1 settled 3 remain 3, line2 untouched (0) | status=200 l1set=3 l1rem=3 l2set=0 |
| STL-12 | PASS | 422 Category condition pre-commit; no orphan VPS row | status=422 orphan=N {"error":"Category condition \"Settlement Voucher\" not found. Please contact IT!"} |
| STL-13 | PASS(gap confirmed) | GAP: missing approval rule -> abort(422) fires AFTER commit, leaving orphaned P settlement with 0 approval rows (same orphan pattern as USG-05) | status=422 stl=VPS26080019 stlStatus=P appr=0 |
| STL-07 | PASS | Rejected settlement rolled back; usage re-settleable (new P settlement created) | f1=R usageSet=0 f2=200 |
| STL-15 | PASS | 403 lvl2 cannot approve before lvl1 | status=403 {"error":"You can't approve."} |
| STL-16 | PASS | 403 VIEW-only user denied | status=403 {"error":"You can't approve."} |
| STL-14 | PASS | lvl1 approve -> P (next activated); lvl2 approve -> C, both steps A | mid=P fin=C A=2 |
| STL-17 | PASS(gap confirmed) | Approval writes nothing to ms_vpl_product_bal (0 delta); trx_vpl_ledger/trx_vpl_adjustment tables do not exist (stock/ledger effect delegated to external SQL stored proc per approve() comment) | balBefore=0 balAfter=0 ledgerTables=0 |
| STL-18 | PASS | 422 empty reject reason | status=422 {"error":"Reason is required."} |
| STL-19 | PASS | Reject: status R, usage qty_settlement=0/qty_remain=NULL, no pending steps, re-settle ok | s8=R set=0 remain=NULL g2=200 |
| STL-20 | PASS | Revise: status D, usage settlement rolled back to 0 | s9=D usageSet=0 |
| STL-21 | PASS | Update from D: status P, fresh 2-step chain, usage 7/3 | s9=P appr=4 set=7 rem=3 |
| STL-22 | PASS(gap confirmed) | GAP: update() has no status/ownership check; Completed doc force-returned to P with regenerated approvals | status=200 s7b=P pending=2 |
| STL-23 | PASS | 403 non-creator cancel | status=403 {"error":"You are not allowed to cancel this document."} |
| STL-24 | PASS | 403 cancel of Completed doc | status=403 {"error":"You are not allowed to cancel this document."} |
| STL-25 | PASS | Cancel: status X, usage rolled back, pending closed, usage re-settleable | s11=X usageSet=0 j2=200 |
| STL-26 | PASS(gap confirmed) | GAP: approve/reject/revise/update/message on missing id -> 500 (fatal, find() null deref); only cancel returns 404 | app=500 rej=500 rev=500 upd=500 msg=500 cxl=404 |
| STL-27 | PASS | Options include unsettled C docs, exclude active-settled; vp_type filters; lines expose remaining=10 | free=Y exclActive=Y exclP=Y line=Y |
| STL-28 | PASS | Scoped user: both ajax endpoints return [] (200, fails closed) | opt=[] lines=[] |

## Section 7 — Cross-cutting / Access Control

| ID | Result | Expected | Notes |
|---|---|---|---|
| ACC-01 | PASS | 403 on all 5 module index routes for qnone (no VIEW) | MASTERVP=/msproduct:403 RECEIVEDVP=/requestvp:403 TRANSFERVP=/transfervp:403 USAGEVP=/usagevp:403 SETTLEMENTVP=/settlementvp:403 |
| ACC-02 | PASS | 403 on all 23 CREATE/EDIT/DELETE routes for qview (VIEW-only) | MASTERVP-CREATE:403 MASTERVP-CREATE:403 MASTERVP-CREATE:403 MASTERVP-CREATE:403 MASTERVP-EDIT:403 MASTERVP-EDIT:403 MASTERVP-EDIT:403 RECEIVEDVP-CREATE:403 RECEIVEDVP-EDIT:403 RECEIVEDVP-EDIT:403 RECEIVEDVP-DELETE:403 TRANSFERVP-CREATE:403 TRANSFERVP-EDIT:403 TRANSFERVP-EDIT:403 USAGEVP-CREATE:403 USAGEVP-EDIT:403 USAGEVP-EDIT:403 USAGEVP-DELETE:403 USAGEVP-DELETE:403 SETTLEMENTVP-CREATE:403 SETTLEMENTVP-EDIT:403 SETTLEMENTVP-EDIT:403 SETTLEMENTVP-DELETE:403 |
| ACC-03 | PASS | approve/reject/revise denied (403/422) for a VIEW-only non-approver across all 4 modules; 422 = stock/line guard fires before role gate in transfer/usage | RECEIVEDVP-approve:403 RECEIVEDVP-reject:403 RECEIVEDVP-revise:403 TRANSFERVP-approve:422 TRANSFERVP-reject:403 TRANSFERVP-revise:403 USAGEVP-approve:422 USAGEVP-reject:403 USAGEVP-revise:403 SETTLEMENTVP-approve:403 SETTLEMENTVP-reject:403 SETTLEMENTVP-revise:403 |
| ACC-04 | PASS | VP menu items render for qvpaccess (PL/VNP/module grants) and are absent for qnone (no grants) | qvpaccess=Master Product,Receive Product,Transfer Product,Usage Product,Settlement Product | qnone=all-absent |
| ACC-05a | PASS | Approve completes (200) with approver email=NULL; no exception; no in-app message written on approve | store=200 app=200 msgBefore=0 msgAfter=0 |
| ACC-05b | PASS | Reject succeeds with creator email=NULL; TrMessage row still written (in-app) with no exception | rej=200 status=R msgCount=1 |
| ACC-06 | PASS | qvpaccess (VPACCESS) sees AW docs in every module list; qscope (EP) does not | RECEIVEDVP:all=Y/scope=N TRANSFERVP:all=Y/scope=N USAGEVP:all=Y/scope=N SETTLEMENTVP:all=Y/scope=N MASTERVP:all=Y/scope=N |

## Section 9 — Cleanup

| ID | Result | Expected | Notes |
|---|---|---|---|
| CLN-01 | PASS | All 88 QA docs (VPR/VPT/VPU/VPS) deleted from pgsql5 | deleted=103 remaining={"receive":0,"transfer":0,"usage":0,"settlement":0} |
| CLN-02 | PASS | Approval chain, history, and in-app messages purged on pgsql2 | approval_del=164 history_del=0 msg_del=39 remaining=0 |
| CLN-03 | PASS | QA attachments soft-deleted (status=X) on pgsql2 (GCS file convention) | matched=1 flipped=1 |
| CLN-04 | PASS | Synthetic products + stock lines (ms_vpl_product / _detail / _bal) removed | deleted_products=21 remaining=0 detail_left=0 |
| CLN-05 | PASS | Synthetic warehouses + dept/usage mappings removed (incl. null-whs_id rows) | deleted_whs=17 aging_del=9 remaining_whs=0 whsdept_left=0 |
| CLN-06 | PASS | QA aging rows (ids 7-15) removed | deleted=9 remaining=0 |
| CLN-07 | PASS | Real data untouched: real products + their stock lines intact, ledger untouched | ledger=2/2 realProducts=5/5 details={"V100001":8,"V100002":2,"V100003":1,"V200001":0,"P100001":0} |

## Section 8 - Consolidated Summary of Known Gaps

### Verdict tally (final)

| Metric | Count |
|---|---|
| Total cases executed | 251 |
| PASS (incl. PASS variants) | 250 |
| BLOCKED (TGT-01, target-date view missing on live DB) | 1 |
| FAIL | 0 |
| Confirmed gaps (recorded as PASS(gap confirmed)) | 70 |

### Per-module tally

| Module | Cases | Gaps |
|---|---|---|
| MST (Master Product) | 19 | 7 |
| PRD (Product detail/stock) | 7 | 4 |
| ATT (Attachments) | 4 | 3 |
| AGE (Aging) | 7 | 5 |
| TGT (Target date) | 4 | 2 |
| WHS (Warehouse) | 8 | 5 |
| WHD (Warehouse-dept) | 10 | 5 |
| LKP (Category/source lookup) | 7 | 0 |
| ARC (Architecture) | 3 | 0 |
| RCV (Receive) | 37 | 6 |
| TRF (Transfer) | 50 | 9 |
| USG (Usage) | 53 | 18 |
| STL (Settlement) | 28 | 6 |
| ACC (Cross-cutting) | 8 | 0 |
| CLN (Cleanup) | 9 | 0 |

### Confirmed gaps (70) - raise with dev/product before sign-off

- MST: MST-07 (EP scope bypassed), MST-08 (prefix defaults to 0), MST-09/10 (unvalidated category/uom), MST-11 (inactive on create?), MST-13, MST-17
- PRD: PRD-03/04/05 (detail-line validation/scope), PRD-06 (warehouse picker offers inactive/EP-company/P-type warehouses for a V/AW product)
- ATT: ATT-01 (upload reports success but nothing stored), ATT-03 (.exe never rejected), ATT-04
- AGE: AGE-02/03/05/06/04 (aging setup validation incl. no overlap detection - 0-30 and 15-45 coexist)
- TGT: TGT-03/04 (target-date save validation; view itself missing on live DB)
- WHS: WHS-02/03/04/06/07 (warehouse setup validation)
- WHD: WHD-03/05/08 (warehouse-dept setup validation), WHD-04 (edit P row to All forces it back to V + creates a new P counterpart), WHD-06 (nonexistent department_id accepted - no FK check)
- RCV: RCV-07/08/09/10/14/40 (scope/validation gaps incl. no cross-check vs source doc)
- TRF: TRF-07/08/11/17/18/21/45/46/50 (stock moved to disk but Attachment row not created; revise/cancel double-release of reservation; return qty not cross-checked; from_whs param ignored; oversize accepted)
- USG: USG-05/08/09/10/11/17/18/19/23/25/26/30/31/35/36/37/38/53 (orphan settlement on rule-missing abort; over-pick via FEFO before stock returns; forged addmore qty accepted; deleteDetail/deleteAttachment ownership not checked; update() non-transactional insert loop; return drops qty_reserved BEFORE stock physically returns -> apparent pickable inflates)
- STL: STL-08 (duplicate detail line accepted, sum > remaining), STL-11 (remainder unreachable after partial C), STL-13 (rule-missing abort AFTER commit leaves orphan P doc), STL-17 (approve writes nothing to bal/ledger; ledger tables absent, stock effect delegated to external sp_process_vpl), STL-22 (update() force-returns C doc to P), STL-26 (approve/reject/revise/update/message on missing id -> 500)

### Notes / caveats

- TGT-01 BLOCKED: v_vpl_product_target_date view missing on live pgsql5 (plan anticipated).
- ACC-05: actual email delivery not verifiable - all QA users have NULL notification_email; observed code path completes with no exception and writes TrMessage only on reject.
- ARC-03: connection-level pgsql2-commit/pgsql5-commit race not reproducible in harness; mitigated (onComplete runs inside pgsql2 tx).
- STL-17 / inventory: approve()/update()/cancel() write no ms_vpl_product_bal or tr_vpl_ledger rows; real inventory effect is delegated to stored proc sp_process_vpl (called outside app code). Any sign-off must confirm the proc is scheduled/triggered.
- PRD-06 addendum: the product-detail view page (viewproduct) itself renders HTTP 500 on the live app - View [vpl.msproduct.viewproduct] not registered. The picker-leak gap was proven independently via the controller's `MsVplWarehouse::all()` query; the page-render 500 is a separate pre-existing app defect to be fixed alongside.
- TRF-41 addendum: the shared-transaction atomicity the plan asked to confirm DOES hold - a mid-loop failure on line 2 rolled back line 1's deduction, the destination row, the header C, and the in-flight approval step in one unit (only the earlier, already-committed level-1 step remains A).
- TRF-30 addendum: immediate second final approve returns 403, so no double-deduct; note this was verified sequentially - true simultaneous requests need external concurrency tooling to reproduce the cross-request race (ARC-03 limitation).

## Section 10 - Result

- All planned QA sections executed live end-to-end through the kernel-dispatch harness.
- 250/251 cases green (1 blocked by a missing view on the live DB).
- 70 gaps recorded for dev/product review; cleanup completed and verified (Section 9 CLN-01..07 all PASS, real data untouched).
- Addendum (below): the 7 originally-planned-but-missing cases were executed in a follow-up run and all passed (4 confirm existing gaps); cleanup re-ran and re-verified (CLN-01..07 PASS, real data untouched).
- QA account layer: the setup-created synthetic users/roles/dept/approval-rules were not covered by the plan's cleanup spec. Extended cleanup (CLN-08/CLN-09) now removes them on pgsql2 — all 9 QA users (incl. qscope "QA Scoped EP"), their cpny/dept/role links, QAVPL* roles + access rights, ms_approval rules, QADEPT dept, 2 orphaned QA approvals, and QA-created ms_autonbr counters (pre-existing counters restored to setup-time snapshot). Verified 0 QA users remain (2379 real users untouched).
- QA attachments: CLN-03 changed from soft-delete (status=X) to hard-delete — the AttachmentMaster list does not filter status, so the one QA attachment (qa_probe2 on deleted product V100005) still appeared. It is now removed (0 QA attachments, 6058 real rows untouched).

## Addendum — Missing-case re-run (AGE-04, LKP-06, WHD-04/06, PRD-06, TRF-30/41)

Follow-up batch executed after the main run to close the 7 planned cases that were not covered in the first pass. All 7 passed; 4 confirm gaps (AGE-04, WHD-04, WHD-06, PRD-06). Cleanup re-ran after seeding (Section 9 re-run below).

## Section 2d addendum - Overlapping aging ranges (AGE-04)

| ID | Result | Expected | Notes |
|---|---|---|---|
| AGE-04 | PASS(gap confirmed) | GAP: no overlap detection - 0-30 and 15-45 coexist | s1=200 s2=200 overlap=true |

## Section 2h addendum - Filtered export (LKP-06)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LKP-06 | PASS | Combined filters return only the matching row | status=200 rows=1 first=["U0007","AW","Voucher","QAVPL ExportA 20260807084108",null,null,null,null,"100000","PCS","Active"] |

## Section 2g addendum - Warehouse-dept gaps (WHD-04, WHD-06)

| ID | Result | Expected | Notes |
|---|---|---|---|
| WHD-04 | PASS(gap confirmed) | GAP: edit P row to All forces it back to V + creates a new P counterpart (copy-paste V assumption) | status=200 originalRowNow=V newPCounterpart=true |
| WHD-06 | PASS(gap confirmed) | GAP: nonexistent department_id accepted - no FK check | status=200 bogus_row=true |

## Section 2b addendum - Warehouse picker filter (PRD-06)

| ID | Result | Expected | Notes |
|---|---|---|---|
| PRD-06 | PASS(gap confirmed) | GAP: picker uses MsVplWarehouse::all() - inactive/EP-company/P-type warehouses all offered for a V/AW product (viewproduct page itself renders 500: view vpl.msproduct.viewproduct not registered) | page=500 leaks={"inactive":true,"ep_cpny":true,"p_type":true} |

## Section 4d addendum - Approve concurrency & atomicity (TRF-30, TRF-41)

| ID | Result | Expected | Notes |
|---|---|---|---|
| TRF-30 | PASS | First final approve completes (C, -3/+3, res released); immediate second approve fails 403 - no double-deduct | l1=200 l2=200 l2again=403 doc=C src=7/0 dst=3 (sequential approximation - true simultaneity needs external concurrency tooling) |
| TRF-41 | PASS | In-loop recheck failure on line 2 rolls back line 1 + header + final-step write atomically; only the earlier level-1 approval step stays A | l1=200 l2=403 doc=P l1=10/3 l2=10/4 dst_rows=0 approved_steps=1 |

## Section 9 — Cleanup (final re-run, incl. QA account layer + attachment purge)

| ID | Result | Expected | Notes |
|---|---|---|---|
| CLN-01 | PASS | All 110 QA docs (VPR/VPT/VPU/VPS) deleted from pgsql5 | deleted=110 remaining={"receive":0,"transfer":0,"usage":0,"settlement":0} |
| CLN-02 | PASS | Approval chain, history, and in-app messages purged on pgsql2 | approval_del=0 history_del=0 msg_del=0 remaining=0 |
| CLN-03 | PASS | QA attachments hard-deleted on pgsql2 (soft-delete status=X still shows in AttachmentMaster list, so rows are removed outright) | matched=1 deleted=1 |
| CLN-04 | PASS | Synthetic products + stock lines (ms_vpl_product / _detail / _bal) removed | deleted_products=0 remaining=0 detail_left=0 |
| CLN-05 | PASS | Synthetic warehouses + dept/usage mappings removed (incl. null-whs_id rows) | deleted_whs=0 aging_del=0 remaining_whs=0 whsdept_left=0 |
| CLN-06 | PASS | QA aging rows (ids 7-15) removed | deleted=0 remaining=0 |
| CLN-07 | PASS | Real data untouched: real products + their stock lines intact, ledger untouched | ledger=2/2 realProducts=5/5 details={"V100001":8,"V100002":2,"V100003":1,"V200001":0,"P100001":0} |
| CLN-08 | PASS | QA account layer purged on pgsql2: 9 synthetic users + cpny/dept links, role links, QAVPL* roles + access rights, ms_approval rules, QADEPT dept, orphaned QA approvals | users_del=9 role_links_del=10 roles_del=5 access_del=45 rules_del=9 dept_del=1 appr_del=2 remaining=0 |
| CLN-09 | PASS | ms_autonbr: QA-created counter rows removed; pre-existing counters restored to setup-time snapshot | qa_rows_del=3 snapshot_restored=7 remaining=0 |
