# QA Test Plan — Legal Agreement Follow-up Workflow (Surat 1 / Surat 2 / Escalation)

Generated 2026-09-18 against the current working tree. Covers the full state machine built this session across:
`app/Http/Controllers/LegalAgreementController.php`, `app/Services/LegalAgreementNotificationService.php`,
`app/Services/DocumentNotificationService.php` (block 14), `app/Console/Commands/ProcessAgreementFollowups.php`
(registered in `app/Console/Kernel.php`), `app/Mail/Agreement*Mail.php`, `app/Mail/Concerns/RendersAgreementLetters.php`,
`app/Models/TrAgreement*.php`, and `resources/views/pages/legal-agreement/**`.

## 0. Test identity — READ FIRST

Every role on every test agreement uses the **same** identity, per explicit instruction:

- `created_user` = `bedriamaail`
- `pic_legal` = `bedriamaail`
- `pic_leasing` = `bedriamaail`
- `pic_penyewa` = `BEDRIA MASHYANDA MAAIL`, `pic_email_penyewa` = `bedriamaail@pakuwon.com`

**Known quirk, accepted as-is (do not change the real user record):** the real `users` row for username
`bedriamaail` has `notification_email = rikiparahat@pakuwon.com`, and `getUserEmail()` in
`LegalAgreementNotificationService` always prefers `notification_email` over `email`. So:
- Any email resolved through a **username lookup** (Created User / PIC Legal / PIC Leasing To/Cc/Bcc slots) lands in
  `rikiparahat@pakuwon.com`, not `bedriamaail@pakuwon.com`.
- The **PIC Penyewa** slot on Surat 1 / Surat 2 (`pic_email_penyewa`, a plain string column, not a lookup) correctly
  lands in `bedriamaail@pakuwon.com`.

Do not "fix" this by editing the real user row — it was explicitly decided to leave it alone. Just note in the
results which address each email actually reached.

Tag every test agreement's `business_name` as `QA-LGA-2026-09-18 <short label>` and record every `agreement_id` /
internal `id` you create as you go — cleanup at the end depends on this list.

## 1. Environment

`APP_ENV=local`, but `MAIL_MAILER=smtp` points at the real company mail server (`mx5.pakuwon.com`) — **emails sent
during this QA pass are real, not fake/logged**. That's intentional; the point is to verify actual delivery/content.
Do not use `Mail::fake()` for these test cases — the whole point is real sends.

The `agreement:process-followups` scheduler command drives Surat 1 → Surat 2 → Escalation automatically based on
calendar days elapsed since the relevant timestamp. Since you can't wait 14 real days, **simulate elapsed time by
backdating** `psm_or_addendum_delivery_date` (on `tr_agreement`) and the `response_date` of the `SURAT1_SENT` /
`SURAT2_SENT` rows in `tr_agreement_activity`, then run the command for real. This exercises the actual scheduler
logic end-to-end rather than just calling `sendSurat1()`/`sendSurat2()`/`sendEscalationEmail()` directly.

## 2. How to create/manipulate test data

There is no seeded/API test harness for this — drive it either through the real routes (`POST /legal-agreement/store`,
`/hold/{hash}`, `/activate/{hash}`, `/complete/{hash}`) under an authenticated session, or, more reliably for a
script, by instantiating `App\Models\TrAgreement` directly and calling the actual controller methods
(`app(\App\Http\Controllers\LegalAgreementController::class)`) the same way `store()`/`holdAgreement()`/
`activateAgreement()` do, via a one-off PHP script bootstrapped like:

```php
$app = require 'D:\Project IT\HR-Job-Portal-\bootstrap\app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
```

Whichever you choose, go through the **real code paths** (controller methods / `Mail` sends / `transitionStep()`),
not hand-crafted DB inserts — the point is to prove the actual implementation works, including validation, mail
envelopes, activity logging, and `tr_agreement_hist` archiving.

`bukti_pengiriman` uploads require a real file — any small `.pdf`/`.jpg` under 5MB works; GCS credentials are already
configured (the session already verified GCS reads/writes work).

## 3. Test cases

### A. Create & Activation (workflow rule 1)

| ID | Steps | Expected |
|---|---|---|
| LGA-01 | Create an agreement (Steps 1–3) with all PIC/tenant/creator = the test identity, delivery date = today, a real Proof of Delivery file. | `agreement_step_id=ACTIVE`, `status=P`, `agreement_id` matches `AFU{YY}{MM}{4-digit-seq}`. `agreementCycleInfo()` returns `cycle=AWAL, days_elapsed=0, days_threshold=14`. |
| LGA-02 | Check the email that fired. | One email, To=Created User (bedriamaail → lands at rikiparahat@pakuwon.com), Cc=PIC Leasing, Bcc=PIC Legal (same address, still one email). Subject `[LEGAL AGREEMENT][SENT] ... PSM/Addendum Delivered to Tenant`. |

### B. Periode Awal — before H+14 (rule 2a–c)

| ID | Steps | Expected |
|---|---|---|
| LGA-03 | On a fresh LGA-01 agreement, call Complete with a reason. | `agreement_step_id=COMPLETED`, `status=C`. `tr_agreement_activity` gets a row with `response_date=now` (this **is** "Tanggal Pengembalian" — there's no separate column). Completed email fires (To=Creator, Cc=Leasing, Bcc=Legal). |
| LGA-04 | On a fresh LGA-01 agreement, call Hold with a reason. | `agreement_step_id=HOLD`. Activity row logs the reason + `response_date=now` (= Tanggal HOLD). Hold email fires. `agreementCycleInfo()` now returns `cycle=null` (counting stopped). |
| LGA-05 | From LGA-04's HOLD agreement, call Activate **without** `psm_or_addendum_delivery_date` or a file. | **422** — both are now hard-required on Activate (this was a deliberate fix this session; confirm it actually rejects). |
| LGA-06 | From the same HOLD agreement, call Activate **with** a new delivery date (e.g. today) + a new Proof of Delivery file. | `agreement_step_id=ACTIVE`. `renewal_sequence` incremented by 1. A new row appears in `tr_agreement_hist` (`hist_agreement_id = {agreement_id}R{old_seq}`) snapshotting the pre-revision PSM/Addendum info. Old attachment stays in `tr_agreement_attachment` tagged with the old `renewal_sequence`; new attachment tagged with the new one — both visible in the agreement's attachment history. `agreementCycleInfo()` shows a **fresh** `cycle=AWAL, days_elapsed=0` using the *new* delivery date. Activated email fires. |

### C. Reminder 1 (rule 3a–d)

| ID | Steps | Expected |
|---|---|---|
| LGA-07 | Take a fresh ACTIVE agreement (no `SURAT1_SENT` yet). Backdate `psm_or_addendum_delivery_date` to 15 days ago. Run `php artisan agreement:process-followups`. | `sendSurat1()` fires: email To=`pic_email_penyewa` (bedriamaail@pakuwon.com), Cc=PIC Legal+PIC Leasing, Bcc=Created User. Attaches `Surat-1-{id}.pdf` + `Tanda-Terima-{id}.*` (the original Proof of Delivery). A `tr_agreement_activity` row with `status_pekerjaan=SURAT1_SENT`, `response_date≈now`, tagged with the agreement's current `agreement_step_order`. `agreementCycleInfo()` now shows `cycle=REMINDER1, days_elapsed=0, days_threshold=14`. |
| LGA-08 | Open the Surat 1 PDF attachment (or re-render it) and sanity check content: tenant name, PSM/Addendum number, original delivery date, and a 14-day return deadline computed from *today* (Surat 1's send date), all in Bahasa Indonesia. | Content matches; no blank/garbled dates (there was a real `optional()->format()` bug on this exact template earlier in the session — confirm it's still fixed). |
| LGA-09 | Run the scheduler again immediately (same day). | No second Surat 1 — `SURAT1_SENT` already exists for this `agreement_step_order`, so the cycle check moves to (and fails) the Surat 2 threshold instead. No duplicate email. |
| LGA-10 | From the REMINDER1 agreement, Complete. | Same as LGA-03 — DONE, timers stop, no further Surat 2/Escalation ever (agreement no longer ACTIVE so the scheduler skips it permanently). |
| LGA-11 | From a REMINDER1 agreement, Hold with a reason, then Activate with a new delivery date + new file (same as LGA-06). | Reset confirmed at a **later** stage: after reactivation, `surat1SentAt()` must return **null** again (the old `SURAT1_SENT` row's `agreement_step_order` no longer matches the bumped current order), so `agreementCycleInfo()` reports `cycle=AWAL` fresh — not `REMINDER1`. This is the critical proof that a mid-cycle revision truly restarts the whole cascade, not just the delivery date. |

### D. Reminder 2 (rule 4a–d)

| ID | Steps | Expected |
|---|---|---|
| LGA-12 | On the LGA-07 agreement (now REMINDER1), backdate the `SURAT1_SENT` activity's `response_date` to 15 days ago. Run the scheduler. | `sendSurat2()` fires: same To/Cc/Bcc pattern as Surat 1, attaches `Surat-2-{id}.pdf` **and** a regenerated copy of `Surat-1-{id}.pdf`. New activity row `SURAT2_SENT`. `agreementCycleInfo()` → `cycle=REMINDER2, days_elapsed=0, days_threshold=7`. |
| LGA-13 | Check the reconstructed Surat 1 copy attached to Surat 2. | Its content (dates, letter number) matches what Surat 1 actually said when it was sent — reconstructed from the `SURAT1_SENT` activity timestamp, not from "now". |
| LGA-14 | Complete / Hold+reactivate from REMINDER2 — mirror LGA-10 / LGA-11. | Same guarantees, one stage further in. |

### E. Escalation (rule 5a–d)

| ID | Steps | Expected |
|---|---|---|
| LGA-15 | On the LGA-12 agreement (REMINDER2), backdate the `SURAT2_SENT` activity's `response_date` to 8 days ago. Run the scheduler. | `sendEscalationEmail()` fires **first**, then (only after the send attempt completes) `transitionStep()` moves it to `ESCALATED`. Email: To=PIC Leasing, Cc=PIC Legal, Bcc=Created User — **no tenant**. Attaches a reconstructed `Surat-2-{id}.pdf`. `status` stays `'P'` (open). |
| LGA-16 | Run the scheduler again (same day, then simulate another day by re-running with no further backdating). | No repeat escalation and no more Surat 1/2 — the agreement is no longer `ACTIVE`, so the scheduler's base query excludes it entirely from here on. |
| LGA-17 | Check the in-app bell notification for `bedriamaail` (`DocumentNotificationService::buildForUser('bedriamaail')`). | An `AGR_ESCALATED` entry appears ("This agreement was automatically escalated..."). Re-check after LGA-07/LGA-12 too — `AGR_SURAT1`/`AGR_SURAT2` should have appeared and then disappeared as the agreement moved past each stage. |
| LGA-18 | From ESCALATED, Complete. | DONE, same guarantees as LGA-03. |
| LGA-19 | From ESCALATED, Hold with a reason, then Activate with a new delivery date + file. | Same full reset as LGA-06/LGA-11, one stage further — confirms `hold`/`activate` still list `ESCALATED` as a valid source step. |
| LGA-20 | Confirm the manual "Escalate" button/route is gone. | `POST /legal-agreement/escalate/{hash}` and `GET /escalate-legal-agreement/{eid}` both **404** (route removed this session). No "Escalate" button in the UI action list for an ACTIVE agreement. |

### F. Cross-cutting

| ID | Steps | Expected |
|---|---|---|
| LGA-21 | Pick a delivery date such that day 14 falls on a Saturday or Sunday. | The reminder still fires exactly at day 14 — no business-day skipping (`diffInDays` is plain calendar arithmetic; confirm no weekend/holiday logic was accidentally introduced). |
| LGA-22 | Hit `GET /legal-agreement/json` (the list DataTable endpoint) for a test agreement at each stage (AWAL/REMINDER1/REMINDER2/ESCALATED). | The `cycle_info` field in the JSON response matches what `agreementCycleInfo()` computes directly — list UI and scheduler can't disagree since they call the same method. |
| LGA-23 | Confirm `TrAgreementHist` rows created in LGA-06/11/19 have `hist_agreement_id` values that don't collide (`{agreement_id}R{seq}` with increasing `seq`). | No duplicate-key errors across multiple revisions of the same test agreement. |

## 4. Cleanup

At the end, hard-delete every row you created, addressed by the exact `agreement_id`/`id` list you kept — **not** a
wildcard — from: `tr_agreement`, `tr_agreement_activity`, `tr_agreement_attachment`, `tr_agreement_hist`. Leave
`ms_autonbr`'s `AFU` counter as-is (it just means future doc numbers skip ahead — harmless, and it's a shared
sequence other work may also be touching). GCS objects uploaded for test attachments can be left behind (a handful
of files in `att-agreement-legal/{year}/` — not worth the risk of deleting the wrong object).
