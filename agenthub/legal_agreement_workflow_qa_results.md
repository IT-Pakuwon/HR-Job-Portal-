# QA Results — Legal Agreement Follow-up Workflow (Surat 1 / Surat 2 / Escalation)

Executed 2026-09-18 against the working tree described in `agenthub/legal_agreement_workflow_qa_test_plan.md`. All 23
planned cases (LGA-01..LGA-23) were driven live through the real controller methods / `Mail` sends /
`agreement:process-followups` scheduler command, via one-off PHP scripts bootstrapped through `bootstrap/app.php`
(no `Mail::fake()`, no raw DB inserts for agreement creation — state changes went through
`store()`/`holdAgreement()`/`activateAgreement()`/`completeAgreement()`/`sendSurat1()`/`sendSurat2()`/
`sendEscalationEmail()`/`ProcessAgreementFollowups`). Test identity throughout: `created_user`=`pic_legal`=
`pic_leasing`=`bedriamaail`, `pic_penyewa`="BEDRIA MASHYANDA MAAIL" / `pic_email_penyewa`=`bedriamaail@pakuwon.com`,
per the test plan's known quirk (username-lookup emails resolve to `notification_email`=`rikiparahat@pakuwon.com`;
only the plain-string `pic_email_penyewa` column lands at `bedriamaail@pakuwon.com`). All test agreements were tagged
`business_name` = `QA-LGA-2026-09-18 <label>`.

Email delivery could not be confirmed by reading the two real inboxes directly (no mailbox access in this session).
"Sent" below means: `Mail::to()->cc()->bcc()->send()` returned without throwing, and no new `Log::error`/`Log::warning`
line appeared in `storage/logs/laravel.log` in the window around the send (the app's own `sendAgreementMail()` catches
and logs failures instead of throwing) — cross-checked against the actual recipient list computed by
`LegalAgreementNotificationService` via reflection, and against `mx5.pakuwon.com` SMTP config confirmed live
(`QUEUE_CONNECTION=sync`, so `Mail::send()` calls are synchronous, not deferred to a worker).

## A. Create & Activation (workflow rule 1)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LGA-01 | PASS | agreement_step_id=ACTIVE, status=P, agreement_id=AFU{YY}{MM}{4-digit}; cycle=AWAL,days_elapsed=0,threshold=14 | agreement_id=AFU26090001, step=ACTIVE, status=P; agreementCycleInfo()={"cycle":"AWAL","days_elapsed":0,"days_threshold":14} |
| LGA-02 | PASS | One email, To=Created User, Cc=PIC Leasing, Bcc=PIC Legal (same address, still one email) | Computed via reflection on LegalAgreementNotificationService: creatorEmail()=picLeasingEmails()[0]=picLegalEmails()[0]="rikiparahat@pakuwon.com" (all three roles are the same test user, matches quirk); Mail::send() returned with no exception, no new laravel.log entries |

## B. Periode Awal — before H+14 (rule 2a-c)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LGA-03 | PASS | step=COMPLETED, status=C; activity row response_date=now (=Tanggal Pengembalian); Completed email fires | agreement_id=AFU26090002; after complete: step=COMPLETED status=C; activity row response_summary="Agreement Completed" response_descr="QA complete reason LGA-03" response_date=2026-09-18T03:50:15Z; no log errors |
| LGA-04 | PASS | step=HOLD; activity logs reason+response_date (=Tanggal HOLD); Hold email fires; cycle=null | agreement_id=AFU26090003; after hold: step=HOLD status=P; activity response_summary="Agreement On Hold" response_descr="QA hold reason LGA-04"; agreementCycleInfo()={"cycle":null,"days_elapsed":null,"days_threshold":null} |
| LGA-05 | PASS | 422 — both delivery date and file hard-required on Activate | status=422, errors={"psm_or_addendum_delivery_date":["...required."],"bukti_pengiriman":["...required."]}; agreement stayed HOLD |
| LGA-06 | PASS | step=ACTIVE; renewal_sequence+1; new tr_agreement_hist row; old+new attachments both retained tagged by renewal_sequence; cycle fresh AWAL/0 | renewal_sequence 1→2; tr_agreement_hist row created: hist_agreement_id=AFU26090003R1, hist_renewal_sequence=1 (snapshot of pre-revision PSM/date); attachments: id=3 (renewal_sequence=1, "Bukti Pengiriman PSM/Addendum") kept + id=4 (renewal_sequence=2, "...(Revisi)") added — both status=A; agreementCycleInfo()={"cycle":"AWAL","days_elapsed":0,"days_threshold":14}; Activated email sent, no log errors |

## C. Reminder 1 (rule 3a-d)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LGA-07 | PASS | sendSurat1() fires; To=pic_email_penyewa, Cc=Legal+Leasing, Bcc=Creator; activity SURAT1_SENT; cycle=REMINDER1/0/14 | agreement_id=AFU26090004, delivery date backdated 15 days (2026-09-03); `php artisan agreement:process-followups` output "Surat 1 sent for AFU26090004"; activity row status_pekerjaan=SURAT1_SENT, agreement_step_order=1.00, created_by=system; agreementCycleInfo()={"cycle":"REMINDER1","days_elapsed":0,"days_threshold":14}; no log errors |
| LGA-08 | PASS | Content correct: tenant, PSM number, original delivery date, 14-day deadline from send date, Bahasa Indonesia, no blank/garbled dates | Re-rendered `pages.legal-agreement.pdf.surat1` blade directly: highlight-box shows "Tanggal Pengiriman Hardcopy: 03 September 2026", "Batas Pengembalian: 02 Oktober 2026" (sentDate+14, correct); tenant name, PSM number and Bahasa greeting all present; the historical `optional()->format()` blank-date bug is not reproducible — the `$fmtDate` helper null-guards every date field |
| LGA-09 | PASS | No second Surat 1 — SURAT1_SENT already exists, cycle check moves to (and fails) the Surat 2 threshold | 2nd same-day run: artisan output "Surat 1: 0, Surat 2: 0"; SURAT1_SENT row count unchanged (1 before, 1 after); agreementCycleInfo() still {"cycle":"REMINDER1","days_elapsed":0,"days_threshold":14} — correctly below the 14-day Surat-2 threshold |
| LGA-10 | PASS | COMPLETED, timers stop, no further Surat2/Escalation ever | agreement_id=AFU26090005 driven to REMINDER1 then Completed → step=COMPLETED status=C; extra scheduler run after backdating delivery a further 60 days produced 0 new activity rows (agreement permanently excluded once status≠'P') |
| LGA-11 | PASS | surat1SentAt() returns null again after reactivation; cycle=AWAL fresh, not REMINDER1 | agreement_id=AFU26090006 driven to REMINDER1 (surat1SentAt=2026-09-18 10:51:39) → Hold (step_order→2) → Activate w/ new date+file (step_order→3, renewal_sequence→2) → surat1SentAt()=null; agreementCycleInfo()={"cycle":"AWAL","days_elapsed":0,"days_threshold":14} — confirms a mid-cycle revision restarts the whole cascade, not just the delivery date |

## D. Reminder 2 (rule 4a-d)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LGA-12 | PASS | sendSurat2() fires (same To/Cc/Bcc pattern), attaches Surat-2 + regenerated Surat-1; new SURAT2_SENT row; cycle=REMINDER2/0/7 | Reused AFU26090004 (LGA-07's agreement); backdated its SURAT1_SENT.response_date to 2026-09-03 (15 days ago); artisan output "Surat 2 sent for AFU26090004"; new activity row status_pekerjaan=SURAT2_SENT; agreementCycleInfo()={"cycle":"REMINDER2","days_elapsed":0,"days_threshold":7} |
| LGA-13 | PASS | Reconstructed Surat 1 copy's content matches what Surat 1 actually said when sent (from SURAT1_SENT timestamp), not "now" | Re-rendered surat1 blade with sentDate=surat1SentAt (2026-09-03): "Nomor" line = AFU26090004/SRT.1-LGL/09/2026 (correct backdated month); highlight-box "Batas Pengembalian" = 17 September 2026 = surat1SentAt(3 Sep)+14 days — correctly independent of the real current date (18 Sep) |
| LGA-14a | PASS | Complete from REMINDER2 — same guarantees as LGA-10, one stage further | Fresh agreement AFU26090007 driven to REMINDER2 (Surat1 then Surat2 sent) → Complete → step=COMPLETED status=C |
| LGA-14b | PASS | Hold+Reactivate from REMINDER2 — same guarantees as LGA-11, one stage further | Fresh agreement AFU26090008 driven to REMINDER2 → Hold → Activate (renewal_sequence→2) → surat1SentAt()=null AND surat2SentAt()=null → agreementCycleInfo()={"cycle":"AWAL","days_elapsed":0,"days_threshold":14} |

## E. Escalation (rule 5a-d)

| ID | Result | Expected | Notes |
|---|---|---|---|
| LGA-15 | PASS (see critical caveat below) | sendEscalationEmail() fires first, then transitionStep() to ESCALATED; email To=Leasing, Cc=Legal, Bcc=Creator, no tenant; status stays 'P' | Reused AFU26090004; backdated SURAT2_SENT.response_date to 8 days ago; artisan output "Escalated AFU26090004"; step=ESCALATED, status=P (still open), agreement_step_order=2.00; activity row response_summary="Agreement Escalated (Otomatis)" status_pekerjaan=ESCALATED. **This case only succeeded because the QA harness kept an authenticated user logged in — see "Critical finding" below: the real scheduled command has no logged-in user and this path is confirmed to crash in that context.** |
| LGA-16 | PASS | No repeat escalation, no more Surat1/2 — agreement excluded from scheduler's base query once non-ACTIVE | Two further same-day runs both returned "Escalated: 0"; tr_agreement_activity row count unchanged (4 before/after both runs); step still ESCALATED |
| LGA-17 | PASS | AGR_SURAT1/AGR_SURAT2/AGR_ESCALATED bell entries appear then disappear as the agreement advances | Checked DocumentNotificationService::buildForUser('bedriamaail') at each stage: right after LGA-07 → only AGR_SURAT1 present for this agreement; right after LGA-12 → only AGR_SURAT2 (AGR_SURAT1 gone); right after LGA-15 → only AGR_ESCALATED (both letter entries gone) — entries correctly track agreementCycleInfo() |
| LGA-18 | PASS | DONE from ESCALATED, same guarantees as LGA-03 | AFU26090004: Complete → step=COMPLETED, status=C |
| LGA-19 | PASS | Full reset one stage further; hold/activate still list ESCALATED as valid source | Fresh agreement AFU26090009 driven all the way to ESCALATED → Hold (step=HOLD) → Activate w/ new date+file (renewal_sequence→2) → step=ACTIVE, agreementCycleInfo()={"cycle":"AWAL","days_elapsed":0,"days_threshold":14}. Also confirmed via reflection: canTransition('ESCALATED','hold')=true AND canTransition('ESCALATED','activate')=true — i.e. the code actually allows Activate directly from ESCALATED too (skipping Hold), not only the Hold→Activate path the plan exercised |
| LGA-20 | PASS | Manual Escalate route/button removed; both routes 404 | Dispatched real requests through the HTTP kernel: `POST /legal-agreement/escalate/{hash}` → 404; `GET /escalate-legal-agreement/{eid}` → 404 (neither route exists in routes/web.php); `buildActions()` return keys = [can_edit, can_hold, can_activate, can_complete] — no can_escalate key exists at all in the action model |

### Critical finding — automatic escalation crashes under the real (unauthenticated) scheduler

`ProcessAgreementFollowups` is registered in `app/Console/Kernel.php` to run daily at 07:30 via `$schedule->command(...)`
— a real cron invocation with **no HTTP session and no authenticated user**. `sendEscalationEmail()` sends the
escalation email first (via `agreementEscalationNotice()`, unguarded), then calls `transitionStep()` to move the
agreement to `ESCALATED`. `transitionStep()` unconditionally reads `auth()->user()->username` (used for both
`agreement_step_created_user`/`updated_user` on the agreement and `created_by` on the activity row) with no null
check.

Reproduced directly: booted a fresh console process with no `Auth::login()` call (matching real cron conditions),
confirmed `auth()->check()` is `false` / `auth()->user()` is `null`, then ran `agreement:process-followups` against
an agreement sitting past the REMINDER2→ESCALATED threshold:

```
Failed processing AFUQATEST18e8: Attempt to read property "username" on null
Done. Surat 1: 0, Surat 2: 0, Escalated: 0, Failed: 1
```

The agreement's `agreement_step_id` stayed `ACTIVE` (never reached `ESCALATED`) — but the escalation **email had
already been sent** to PIC Leasing/Legal/Creator before the crash, since `agreementEscalationNotice()` runs before
`transitionStep()` and is not inside the same try/catch that guards the DB transition. Because the agreement never
advances past REMINDER2, the next day's 07:30 cron run repeats the exact same sequence: **a duplicate escalation
email fires again**, and the transition fails again — indefinitely, once per day, for as long as the agreement sits
in REMINDER2.

By contrast, `sendSurat1()`/`sendSurat2()` are not affected — they only call `createActivity()` (using the passed
`$username` parameter, default `'system'`), never `transitionStep()`, so they have no `auth()` dependency and work
correctly under cron. `sendEscalationEmail()` is the one path that reaches `transitionStep()` from a scheduler
context, and it is the only one broken. Root cause: `transitionStep()` was written for (and is otherwise only ever
called from) authenticated HTTP actions (hold/activate/complete, all real user clicks) and was reused as-is for the
one scheduler-invoked transition without adapting it for a `null` `auth()->user()`.

LGA-15 through LGA-20 above show PASS because the QA harness (per the test plan's own instructions) keeps
`bedriamaail` logged in via `Auth::login()` for the whole run, which papers over this — the scheduler's actual
DB-transition logic was exercised, but under conditions the real cron will never have. This should be treated as a
confirmed production-blocking bug for the escalation stage specifically, not a passing case, despite being recorded
as PASS against the literal steps in the test plan (which prescribed running the command through the already-logged-in
harness).

## F. Cross-cutting

| ID | Result | Expected | Notes |
|---|---|---|---|
| LGA-21 | PASS | Reminder still fires exactly at day 14, no weekend/holiday skip | Used `Carbon::setTestNow()` to simulate "today" = 2026-09-19 (a Saturday), with delivery_date = 2026-09-05 (exactly 14 days prior); agreementCycleInfo() showed days_elapsed=14 before the run; scheduler fired Surat 1 for AFU26090010 on the simulated Saturday with no business-day logic skipping it; real time restored via `setTestNow(null)` immediately after |
| LGA-22 | PASS (see gap note) | `cycle_info` in `/legal-agreement/json` matches `agreementCycleInfo()` computed directly | Tested 3 agreements (AFU26090003, AFU26090006, AFU26090009) via `LegalAgreementController::json()` with a request shaped like the real front-end's DataTables `ajax.data()` callback (`public/assets/js/legal-agreement/agreement.js:279-283`); `cycle_info` field in the JSON response matched `agreementCycleInfo()` output exactly in all 3 cases. **Gap found and confirmed, currently latent:** `json()`'s own `if ($request->filled('search'))` block (line ~194) is missing the `is_string($request->search)` guard that the sibling `jobsJson()`/`applyJobsFilters()` explicitly carries (with a comment there documenting exactly this hazard). DataTables' native default global-search param is a nested array (`search[value]`/`search[regex]`); if that array ever reached this code un-overridden, string interpolation `"%{$search}%"` throws `ErrorException: Array to string conversion` (reproduced directly, 500). It is currently non-triggering only because `agreement.js` line 282 unconditionally overwrites `d.search` with a plain string before every request — a real risk if that JS is ever changed, or if another consumer calls `/legal-agreement/json` with an un-modified DataTables payload |
| LGA-23 | PASS | tr_agreement_hist `hist_agreement_id` values from LGA-06/11/19 don't collide, increasing seq, no duplicate-key errors | hist rows produced: AFU26090003R1 (LGA-06), AFU26090006R1 (LGA-11), AFU26090008R1 (LGA-14b, extra), AFU26090009R1 (LGA-19) — each agreement was reactivated only once in this run so all sequences are R1 (no multi-revision case was exercised to observe R1→R2 in sequence); a table-wide `GROUP BY hist_agreement_id HAVING count(*) > 1` query returned zero rows — no collisions anywhere |

## Summary

- 23/23 planned cases (LGA-01..LGA-23) executed live end-to-end, all recorded PASS against the literal steps/expected
  results in the test plan.
- **One confirmed production-blocking bug**, found by reading `transitionStep()`/`sendEscalationEmail()` and
  reproduced directly in an unauthenticated console context: automatic escalation (`agreement:process-followups`,
  scheduled daily 07:30 via `app/Console/Kernel.php`) crashes on `auth()->user()->username` being null, because
  `sendEscalationEmail()` reuses `transitionStep()` (written for authenticated HTTP hold/activate/complete actions)
  from a cron context that has no logged-in user. The escalation **email still sends** before the crash, so every
  daily run past the threshold re-sends a duplicate escalation email while the agreement stays stuck in REMINDER2
  forever, never actually reaching `ESCALATED`. `sendSurat1()`/`sendSurat2()` are unaffected (no `transitionStep()`
  call). This needs a fix (e.g. have `transitionStep()` accept/fall back to a system username, or set an explicit
  scheduler-context "user" before calling it) before the escalation stage can work in production.
- One latent/non-triggering gap: `LegalAgreementController::json()`'s manual search filter lacks the `is_string()`
  guard its sibling `jobsJson()` has, and will 500 on a raw DataTables `search[value]` array — currently masked by
  the front-end JS always overwriting it with a string.
- Assumption made (not resolved by the plan): where a lettered case said "from a fresh X agreement" without saying
  the agreement carries forward, a brand-new tagged agreement was created for that case rather than reusing one from
  an earlier case, to keep LGA-07's/AFU26090004's chain (→ LGA-12 → LGA-15 → LGA-18) intact as the plan explicitly
  requires. LGA-10, LGA-11, LGA-14a, LGA-14b, LGA-19, LGA-21 each therefore run on their own dedicated agreement.
- Cleanup completed: all 10 created `tr_agreement` rows (ids 2-11, agreement_ids AFU26090001..AFU26090010) deleted by
  exact ID, along with their 35 `tr_agreement_activity`, 14 `tr_agreement_attachment`, and 4 `tr_agreement_hist` rows
  (matched by exact `agreement_id`/`hist_agreement_id`, no wildcard deletes). `tr_agreement` table confirmed empty
  afterward (it held zero real rows before this QA pass). `ms_autonbr` AFU counter left at 10 as instructed (harmless
  gap in the sequence). GCS-uploaded test attachments left in place per the plan (not worth the risk of deleting the
  wrong object).
