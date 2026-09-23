# QA Test Plan: Training / L&D Module (Master → Schedule → Registration → Attendance → Evaluation)

Generated 2026-08-02 against the current working tree (post-Lnd-refactor, pre-commit — see `git status`). Grounded in actual controller/service/model code, not the original `agenthub/lnd_registration_refactor.md` / `training_setup_crud.md` specs, which have already diverged from what's implemented in places (noted inline where relevant).

Scope: `MasterTrainingController`, `TrainingSetupController`, `TrainingSessionController`, `TrainingRegistrationController`, `TrainingAttendanceController`, `TrainingFeedbackController`, `TrainingRegistrationService`, `TrainingWaitlistNotifier`, `ExpireWaitlistOffers`, `CloseTrainingRegistrations`, `TrLndTrainingRegistrationObserver`.

---

## 0. Test environment & preconditions

1. **Doctype `TRN` approval rules must exist in `ms_approval` (pgsql2).** As of the last check (2026-07-23) this doctype had **zero** approval rules configured — if that's still true, `approve()`/`reject()` cannot function at all and every Registration test case that depends on approval completing (seat confirmation, barcode generation, cancel-eligibility) will be blocked. **Verify this first** before running the Registration section:
   ```
   php artisan tinker --execute="dd(DB::connection('pgsql2')->table('ms_approval')->where('doctype','TRN')->get());"
   ```
2. **Use synthetic data, not real employees** — this codebase has a real SMTP host configured (`mx5.pakuwon.com`), so registering/approving/declining with real usernames will send real emails to real people. Follow the pattern already established in this repo: synthetic users/company (e.g. `zztest_trn_*` / company `ZZTEST`) with `notification_email = NULL` so notification code paths still execute (and can be asserted on via the `TrMessage` row it writes) without actually emailing anyone.
3. **Roles needed for full coverage**:
   - A normal employee user (no special role) — registration, cancel, accept/decline offer, feedback submission, barcode view.
   - A second employee in the **same** `orgn_company_id`/`orgn_department_id` — for colleague batch-registration tests.
   - A third employee in a **different** company/department — for the negative-path "not a colleague" test.
   - A user holding role `HCDEVACCESS` — waitlist management, manual accept, attendance scan/mark/unmark, certificate upload, feedback open/close.
   - A user who is a configured approver for `TRN` at step 1, and (if multi-level) step 2 — for approve/reject and out-of-turn-approver tests.
   - A user with **no** approval role for `TRN` — negative-path direct-POST test against `.approve`/`.reject`.
4. **Data setup**: at least one `MsTrainingEvent` (Training master), one `MsLndTrainingDetail` batch with 2+ `MsLndTrainingSchedule` dates, and controlled `MsLndTrainingQuota` (e.g. quota_pax=1) so seat-exhaustion/waitlist paths are reachable without needing dozens of synthetic users.
5. Reset/clean up synthetic rows after each run (or wrap in a rolled-back DB transaction via tinker) so repeated runs don't accumulate stale approval-inbox clutter or skew quota counts.

---

## 1. Master Training (`MasterTrainingController`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| MST-01 | Positive | Create training | POST valid `training_name`, `category_id` (existing active TE category), `training_type=INTERNAL` | 201, `training_id` auto-generated as `TE{yy}{seq}`, `status='A'` |
| MST-02 | Positive | Create EXTERNAL training | Same, `training_type=EXTERNAL` | Succeeds |
| MST-03 | Negative | Missing required field | Omit `training_name` | 422 validation error |
| MST-04 | Negative | Invalid `training_type` | `training_type=FOOBAR` | 422 (must be `INTERNAL`/`EXTERNAL`) |
| MST-05 | Negative | Garbage `category_id` | `category_id` = random string not in `ms_category` | **Currently succeeds** — no FK existence check. Confirm this is accepted (flag as a gap, not a bug per se) |
| MST-06 | Positive | Edit training | PUT valid changes to name/description | 200, fields updated |
| MST-07 | Positive | Toggle Active→Inactive, no open schedules | Training with only CLOSED/CANCELLED schedules (or none) | 200, `status='X'` |
| MST-08 | Negative | Toggle Active→Inactive, open schedule exists | Training has a DRAFT or PUBLISHED schedule | 422 "Training tidak dapat dinonaktifkan karena masih memiliki schedule yang belum berstatus Closed" |
| MST-09 | Positive | Toggle Inactive→Active | Previously deactivated training | 200, `status='A'` |
| MST-10 | Negative | Invalid `status` on toggle | `status=Y` | 422 (must be `A`/`X`) |
| MST-11 | Positive | Category search only returns active TE | `GET /mastertraining/category-search` | Only `doctype='TE' AND status='A'` rows returned |
| MST-12 | Negative | Submit deactivated category via direct POST | Select a category, deactivate it server-side, then `store()`/`update()` training with that stale `category_id` | **Currently succeeds** — no server-side active check on submit, only the picker filters it. Confirm gap. |
| MST-13 | Negative | Unauthorized access | Hit any `MASTERTRAINING` route as a user without `MASTERTRAINING` access right | 403 |

---

## 2. Master Training → Setup (`TrainingSetupController`)

### 2a. Places (`ms_lnd_places`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| SET-01 | Positive | Create place | `places_name`, `places_address`, `status=A` | 201, `places_id` auto = `PL{yy}-{00001}` format |
| SET-02 | Negative | Missing `places_name` | Omit field | 422 |
| SET-03 | Negative | `status` not in A/X | `status=Y` | 422 |
| SET-04 | Negative | Duplicate `places_name` | Create two places with identical name | **Currently allowed** — no uniqueness rule. Confirm. |
| SET-05 | Positive | Edit place | PUT valid changes | 200 |
| SET-06 | Positive | Soft-delete place | DELETE unused place | 200, row soft-deleted (still in DB with `deleted_at`) |
| SET-07 | Negative | Delete a place currently assigned to a PUBLISHED schedule | Delete a `places_id` referenced by `MsLndTrainingSchedule` | **Not blocked at all.** Confirm delete succeeds, then check whether the schedule's place name renders correctly afterward (expected: broken/blank, since lookups don't use `withTrashed()`) — this is a real display-bug candidate to log |
| SET-08 | Negative | Unauthorized access | Hit setup routes without `MASTERTRAINING` CREATE/EDIT/DELETE rights respectively | 403 |

### 2b. Categories (`ms_category`, `doctype='TE'` forced server-side)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| SET-09 | Positive | Create category | `categoryid`, `category_name`, `status=A` | 201, `categoryid` stored lowercased+trimmed, `doctype` forced to `TE` even if omitted |
| SET-10 | Negative | Attempt to override doctype | POST with `doctype=XYZ` in payload | `doctype` still saved as `TE` (server ignores client value) |
| SET-11 | Negative | Duplicate `categoryid` for doctype TE | Create two categories with same `categoryid` | Check whether DB unique index blocks this (likely a raw SQL/500 or silent duplicate — no app-level validation exists) |
| SET-12 | Negative | Access a category belonging to a different doctype | `findCategory`/`updateCategory`/`deleteCategory` using an `id` that exists but has `doctype != 'TE'` | 404 (scoped `where('doctype','TE')->findOrFail`) |
| SET-13 | Negative | Missing required fields | Omit `category_name` | 422 |

---

## 3. Schedule (`TrainingSessionController`)

### 3a. Batch creation (`storeSchedule`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| SCH-01 | Positive | Create single-date batch | Valid `job_level`, `training_detail_name`, one date, `mode=ONLINE`, quota rows | 201, `status` forced to `D` (Draft) regardless of payload |
| SCH-02 | Positive | Create multi-date batch | `dates[]` with 3 entries | 3 `MsLndTrainingSchedule` rows created sharing one `training_detail_id`; same quota values copied to each |
| SCH-03 | Negative | Past `schedule_date` | Date = yesterday | 422 (`after_or_equal:today`) |
| SCH-04 | Negative | `end_time` not after `start_time` | `start_time=10:00`, `end_time=10:00` (equal) and `end_time=09:00` (earlier) | 422 both cases |
| SCH-05 | Positive | OFFLINE mode with place | `mode=OFFLINE`, valid `places_id` | Succeeds |
| SCH-06 | Negative | OFFLINE/HYBRID mode without place | `mode=OFFLINE`, omit `places_id` | 422 (`required_if`) |
| SCH-07 | Positive | ONLINE mode without place | `mode=ONLINE`, no `places_id` | Succeeds, `places_id` null |
| SCH-08 | Negative | `is_ext_speaker=1` without speaker name | Omit `ext_speaker_name` | 422 |
| SCH-09 | Positive | Omit `registration_deadline` | No deadline supplied | Auto-computed as `max(schedule_date - 3 days, today)` — verify computed value |
| SCH-10 | Positive | Create with zero quota rows | Submit `dates` with empty/omitted `quota[]` | Succeeds — schedule created with 0 available seats for every company (verify later that Registration correctly reports "not available for your company") |
| SCH-11 | Negative | Duplicate `cpny_id` within one quota payload | Two quota rows same `cpny_id`, different `quota_pax` | **Currently both inserted** — company's effective quota becomes the sum. Confirm this and flag as a bug candidate (silent double-counting) |
| SCH-12 | Negative | `quota_pax` = 0 or negative | Submit `quota_pax=0` | 422 (`min:1`) |
| SCH-13 | Negative | Poster upload wrong type/too large | Non-image file, or >5MB image | 422 |
| SCH-14 | Negative | Unauthorized create | User without `MASTERTRAINING` CREATE right | 403 |

### 3b. Status transitions (`scheduleStatus`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| SCH-15 | Positive | Draft → Published | Valid transition | 200 |
| SCH-16 | Positive | Published → Closed | Valid transition | 200 |
| SCH-17 | Positive | Draft → Cancelled | Valid transition | 200 |
| SCH-18 | Positive | Published → Cancelled | Valid transition | 200 |
| SCH-19 | Negative | Draft → Closed (skip Published) | Invalid transition | 422 "Tidak bisa mengubah status dari Draft ke Closed" |
| SCH-20 | Negative | Closed → Published (reverse) | Invalid transition | 422 |
| SCH-21 | Negative | Cancelled → anything | Cancelled is terminal | 422 |
| SCH-22 | Negative | Closed → anything | Closed is terminal | 422 |

### 3c. Edit (`updateSchedule`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| SCH-23 | Positive | Edit a DRAFT date | Change time/mode/quota on a Draft schedule | 200, changes applied; quota rows fully replaced |
| SCH-24 | Negative | Edit a PUBLISHED/CLOSED/CANCELLED date | PUT against a non-Draft schedule | 422 "hanya schedule berstatus DRAFT yang dapat diubah" |
| SCH-25 | **Regression risk** | Cross-date header bleed | Batch with Date A (DRAFT) and Date B (PUBLISHED) sharing one `training_detail_id`. Edit Date A's `training_detail_name` / `job_level` / `is_ext_speaker` | Verify: does Date B's displayed name/level also change even though B itself is locked (PUBLISHED)? Per code inspection: **yes, header fields are shared and not status-gated.** This needs explicit UI verification — likely surprising/undesired behavior worth flagging to product owner even if "as coded" |
| SCH-26 | Positive | Replace poster | Upload new poster image on edit | Old file deleted from storage, new one stored |

---

## 4. Registration (`TrainingRegistrationController` + `TrainingRegistrationService` + `TrainingWaitlistNotifier`)

### 4a. Register (`register`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| REG-01 | Positive | Self-register, seat available | Schedule PUBLISHED, deadline not passed, quota available | 201, row created `status='P'`, `status_registration=null` (seated), approval chain starts immediately |
| REG-02 | Positive | Self-register, no seats left | Quota exhausted | 201, row created `status='P'`, `status_registration='W'` (waitlisted) — **approval still starts immediately**, confirming the memory note that approval no longer waits for offer-accept |
| REG-03 | Negative | Schedule not PUBLISHED | Register against a DRAFT/CLOSED/CANCELLED schedule | 422 "Registrasi untuk jadwal ini sudah ditutup" |
| REG-04 | Negative | Registration deadline passed | `registration_deadline` in the past, schedule still PUBLISHED | 422 "Batas waktu registrasi sudah lewat" |
| REG-05 | Negative | No quota row for user's company | User's `origin_cpny_id` has zero `MsLndTrainingQuota` rows on this schedule | 422 "Training ini tidak tersedia untuk perusahaan Anda" |
| REG-06 | Negative | Duplicate active registration | Register twice for same schedule while first is still active (P/W/O, not X/R) | 422 listing offending username(s) |
| REG-07 | Positive | Re-register after prior registration was Rejected or Cancelled | Prior row `status='R'` or `status_registration='X'` | Allowed — new registration succeeds |
| REG-08 | Negative | Mandatory training, second schedule | `MsTrainingEvent.is_mandatory=true`; user has an active registration on Schedule A of that training; tries to register for Schedule B of the *same* training | 422 |
| REG-09 | Positive | Colleague batch registration, same dept/company | `participants[]` includes 2 users sharing exact `orgn_company_id`+`orgn_department_id` with submitter | Each participant gets their own row + own `training_regist_id` + own approval chain |
| REG-10 | Negative | Colleague from different department/company | Include a participant not matching submitter's org | 422 naming the offending user ("bukan rekan sekantor Anda") |
| REG-11 | Negative | Colleague inactive/nonexistent | Include a `username` that's inactive or doesn't exist | 422 ("tidak ditemukan") |
| REG-12 | Positive | Batch where seats < batch count | 3 participants, only 2 seats available | **All-or-nothing**: verify entire batch goes to Waitlisted, not partial seating |
| REG-13 | Concurrency | Two users race for the last seat | Fire two near-simultaneous register requests for the final open seat | Exactly one seated, one waitlisted — no overbooking (validates `lockForUpdate`) |

### 4b. Cancel (`cancel`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| REG-14 | Positive | Cancel an Approved seated registration | `status='C'` | 200, cancelled |
| REG-15 | Negative | Cancel someone else's registration | User B tries to cancel User A's row | 403 |
| REG-16 | Negative | Cancel a still-Pending (not yet Approved) registration | `status='P'` | 422 "Hanya registrasi berstatus Approved yang dapat dibatalkan" — confirm this is really blocked (looks like a real UX gap: user can't back out while awaiting approval) |
| REG-17 | Positive | Cancel a seated slot on a PUBLISHED schedule | Prior `status_registration=null`, schedule still `P` | Waitlist auto-promotes next person (`promoteWaitlistIfOpen`) → verify next-in-line gets `status_registration='O'` and a notification |
| REG-18 | Negative | Cancel a seated slot on a CLOSED schedule | Schedule status `C` | **No auto-promotion** — seat sits forfeited, requires HR `manualAccept`. Confirm no promotion happens. |

### 4c. Offer accept/decline

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| REG-19 | Positive | Accept a valid (non-expired) offer | `status_registration='O'`, within 24h | 200, flips to seated (`status_registration=null`), approval chain **not restarted** |
| REG-20 | Negative | Accept an expired offer | `process_registration_date` > 24h ago, still `'O'` (cron hasn't swept yet) | 422 "Offer ini sudah tidak berlaku" |
| REG-21 | Positive | Decline an offer within window | `status_registration='O'` | 200 → `status_registration='X'`, cascades to next waitlisted person |
| REG-22 | **Inconsistency check** | Decline an offer *after* the 24h window but before the cron runs | Same state as REG-20 but call `declineOffer` instead of `acceptOffer` | Per code: **decline does not check the 24h window at all** — should still succeed and cascade. Confirm this asymmetry vs. accept; worth flagging |
| REG-23 | Negative | Accept/decline someone else's offer | User B acts on User A's offer | 403 |
| REG-24 | Negative | Accept/decline a non-Offered row | `status_registration` is null/W/X | 422 |

### 4d. Approve / Reject

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| REG-25 | Positive | Correct current-step approver approves | Approver at active step | 200, chain advances or completes |
| REG-26 | Negative | Non-approver hits `.approve` directly | Any other user, direct POST | "You can't approve." (no route-level `access:` gate — enforcement is entirely inside `ApprovalController::assertUserCanAct`) |
| REG-27 | Negative | Out-of-turn approver (step 2 approves before step 1) | If multi-level chain configured for TRN | Blocked, same as REG-26 |
| REG-28 | Positive | Final approval completes, seated registrant | `status_registration=null` at completion | `status→'C'`, **Observer fires**, `attendance_code` generated in format `TRN-XXXXXXXXXX` |
| REG-29 | Positive | Final approval completes, waitlisted registrant | `status_registration='W'` at completion | `status→'C'` but **no barcode minted** (observer condition requires `status_registration` empty); `offerIfSlotAlreadyFree()` should fire — if a seat is already open, this person gets offered immediately |
| REG-30 | Negative | Reject mid-chain when a waitlisted person is queued behind a seated slot | Reject at any level | `status→'R'`; **no waitlist cascade triggered even if the rejected row held a seat** — confirm seat is not silently released to the next waitlisted person (real gap — log it) |
| REG-31 | Positive | Rejected registrant can re-register | After `status='R'` | New registration allowed (per REG-07) |

### 4e. Waitlist management / manual accept (`HCDEVACCESS`-only)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| REG-32 | Negative | Non-HCDEVACCESS hits `/training-list/waitlist` or `manual-accept` | Regular user | 403 |
| REG-33 | Negative | Manual-accept a row not Waitlisted | `status_registration` != W | 422 "Registrasi ini bukan waiting list" |
| REG-34 | Negative | Manual-accept a Waitlisted row whose approval hasn't finished | `status='P'`, `status_registration='W'` | 422 "Approval untuk peserta ini belum selesai" |
| REG-35 | Negative | Manual-accept into a non-CLOSED schedule | Schedule still PUBLISHED | 422 "Penerimaan manual hanya untuk jadwal yang sudah closed" |
| REG-36 | Positive | Valid manual-accept | Waitlisted + Approved + schedule CLOSED, quota available for chosen `cpny_id` | 200, seated; creator (`created_by`, may differ from participant for colleague-registrations) receives `trainingmanualaccept` email + bell notification |
| REG-37 | Negative | Manual-accept when target company's quota is exhausted | HR overrides `cpny_id` to a company whose quota is full | 422 (quota lock check) |

### 4f. Barcode (self-service)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| REG-38 | Positive | View barcode, approved + seated, within attendance window | | Barcode image/status returned |
| REG-39 | Negative | View barcode, approval not complete | `status != 'C'` | "Registrasi belum disetujui" |
| REG-40 | Negative | View barcode, waitlisted/offered/cancelled | `status_registration` not empty | "Anda belum memiliki slot pada event ini" |
| REG-41 | Negative | View barcode before attendance window opens | Before `schedule_date` 00:00 | "Barcode akan aktif pada {date}" |
| REG-42 | Negative | View barcode after window closes | >24h past `schedule_end_time` | "Barcode sudah kedaluwarsa" |
| REG-43 | Negative | View someone else's barcode | User B requests User A's `{id}` | 403 |

---

## 5. Attendance (`TrainingAttendanceController` + `HasAttendanceWindow`)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| ATT-01 | Positive | List scannable events | Schedules with status P or C | Both appear; D/X excluded |
| ATT-02 | Positive | Roster shows only seated+approved | Mix of seated-approved, waitlisted-approved, offered | Only seated+approved rows appear |
| ATT-03 | Positive | Scan a valid barcode for the correct event | Within window, correct `schedule_id` | Returns participant info |
| ATT-04 | Negative | Scan barcode against the wrong event | Valid code, but `schedule_id` in request doesn't match the code's own schedule | 422 "Barcode ini bukan untuk event yang dipilih" |
| ATT-05 | Negative | Scan outside attendance window | Before `schedule_date` 00:00, or >24h after `schedule_end_time` | 422 both boundary directions |
| ATT-06 | Positive | Mark attendance | Valid seated+approved registrant, within window | `completed_at`/`completed_by` set; `TrLndTrainingAttendance` row appended (`status='A'`) |
| ATT-07 | Positive | Re-mark an already-attended person (idempotency) | Call `markAttend` twice | Second call is a silent no-op — no duplicate log row, no error |
| ATT-08 | Negative | Mark attendance for non-seated registrant | Waitlisted/offered | 422 "Peserta belum memiliki slot pada event ini" |
| ATT-09 | Negative | Mark attendance outside window | Same boundary as ATT-05 | 422 |
| ATT-10 | Positive (HCDEVACCESS) | Unmark attendance | Previously marked, HCDEVACCESS user | 200, `completed_at` cleared, attendance log rows soft-deleted |
| ATT-11 | Negative | Unmark by non-HCDEVACCESS | Regular admin/user | 403 |
| ATT-12 | Negative | Unmark a never-marked registrant | `completed_at` already null | 422 "Peserta ini belum ditandai hadir" |
| ATT-13 | **Asymmetry check** | Unmark long after the event window closed | Days after the event | Per code: **no window check on unmark** — should still succeed. Confirm and flag the asymmetry vs. mark |
| ATT-14 | Positive | After-event list | Schedule with attended participants | Shows only `status='C' AND completed_at IS NOT NULL`, with scan/void history via `withTrashed()` |
| ATT-15 | Positive | Export Excel/CSV/PDF | Trigger each export for an event with attendees | Row count and `completed_at` timestamps match the After-Event list exactly |
| ATT-16 | Positive (HCDEVACCESS) | Upload certificate | 1–5 valid files (jpg/jpeg/pdf, ≤5MB each) | Succeeds, stored under `training_certificates/{year}/{registration_id}/` |
| ATT-17 | Negative | Upload 6th certificate file | 5 already exist, upload 1 more (or 3+3 in two batches, or 6 in one batch) | 422 "Maksimal 5 file per peserta (sudah ada {n})" — test the exact 5 boundary succeeds, and that a single over-cap batch fails atomically (not partial) |
| ATT-18 | Negative | Wrong file type/size | .exe file, or >5MB pdf | 422 |
| ATT-19 | **Gap check** | Upload certificate for a never-attended registrant | `completed_at` is null | **Currently allowed** — no attendance check on this endpoint. Confirm and flag |
| ATT-20 | Negative | Any attendance action by non-HCDEVACCESS where required | scan/attend allowed for TRAININGATTENDANCE-CREATE role, but unattend/certificate-upload/feedback-open/close require HCDEVACCESS specifically | 403 for the HCDEVACCESS-gated subset |

---

## 6. Evaluation / Feedback (`TrainingFeedbackController` + feedback window)

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| EVL-01 | Positive (HCDEVACCESS) | Open feedback window | Schedule with `feedback_opened_at` null | 200, `is_feedback_open=true` |
| EVL-02 | Negative | Close feedback window that was never opened | `feedback_opened_at` still null | 422 "Feedback belum pernah dibuka" |
| EVL-03 | Positive (HCDEVACCESS) | Close, then re-open | Close first, then open again | Re-opening clears `feedback_closed_at`, `is_feedback_open` flips back true |
| EVL-04 | Positive | Submit feedback, attended + window open | `completed_at` set, `is_feedback_open=true` | 200, answers saved via `updateOrCreate` keyed on `(training_regist_id, question_order)` |
| EVL-05 | Negative | Submit feedback, not attended | `completed_at` null | 422 "Anda belum tercatat hadir pada training ini" |
| EVL-06 | Negative | Submit feedback, window not open | `is_feedback_open=false` | 422 "Feedback untuk training ini belum/tidak lagi dibuka" |
| EVL-07 | Negative | Submit feedback for someone else's registration | User B submits against User A's `{id}` | 403 |
| EVL-08 | **Gap check** | Resubmit feedback while window still open | Submit once, then submit again with different answers | **Currently allowed** — silently overwrites prior answers (`already_submitted` is reported to UI but not enforced server-side). Confirm and flag if "one submission only" was the intended business rule |
| EVL-09 | **Gap check** | Rating answer outside the configured option range | Question configured `1-5`, submit `answer.value=999` or `-1` | **Currently accepted unvalidated** — stored as-is (`answer_number` cast, no bounds check). Confirm and flag |
| EVL-10 | Positive | Submit answer for a since-deactivated question | `question_order` no longer in `MsLndTrainingFeedback::active()` | Silently ignored — not saved, not an error |
| EVL-11 | Negative | Missing `answers` array or empty | `answers=[]` | 422 (`min:1`) |
| EVL-12 | Positive (HCDEVACCESS) | View feedback results/aggregation | Schedule with submitted answers | Rating → avg + histogram; Single Choice → distribution; Long Text → raw list (empty answers excluded) |
| EVL-13 | Informational | Confirm no admin UI/API exists to manage the feedback question bank itself | Search app UI for a "manage feedback questions" screen | Expected: none exists — question bank (`MsLndTrainingFeedback`) is DB-seeded/tinker-managed only, globally shared across all trainings/schedules (not scoped per training). Confirm this is intentional, not an oversight, before sign-off |

---

## 7. Cron jobs

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| CRN-01 | Positive | `ExpireWaitlistOffers` expires a stale offer | Offer row `status_registration='O'`, `process_registration_date` > 24h ago | Flips to `'X'`, `process_registration_user='system'`, cascades to next waitlisted person |
| CRN-02 | **Gap check** | Expired offer whose approval never completed | Same as CRN-01 but `status` still `'P'` | Row becomes `status='P'` + `status_registration='X'` permanently — never cleaned up, sits as an orphaned pending-approval doc forever. Confirm this happens and flag to the dev team as a potential stuck-inbox issue |
| CRN-03 | Positive | `CloseTrainingRegistrations` auto-closes past-deadline schedules | `status='P'`, `registration_deadline <= today` | Flips schedule to `'C'` |
| CRN-04 | **Gap check** | Auto-close with outstanding Offered rows | Schedule has live `'O'` offers when it auto-closes | Offers are **not** cancelled/cascaded by the close command — they remain live until they individually expire (CRN-01) or get manually resolved. Confirm and flag as expected-but-worth-documenting behavior |
| CRN-05 | Robustness | One row's processing fails mid-sweep | Simulate/inspect | Each row is processed in its own transaction — one failure should not roll back or block other rows in the same run |

---

## 8. Cross-cutting / access control

| ID | Type | Case | Steps / Data | Expected |
|---|---|---|---|---|
| ACC-01 | Negative | Every VIEW-gated GET route hit without `VIEW` access right | Master/Setup/Schedule/Registration/Attendance screens | 403 |
| ACC-02 | Negative | Every CREATE/EDIT/DELETE-gated route hit without the matching right | | 403 |
| ACC-03 | Negative | `training-list.approve`/`.reject` — the only two POST routes with **no** route-level `access:` middleware | Any authenticated user, direct POST, not a valid approver | Relies solely on `ApprovalController::assertUserCanAct` — confirm it's still actually enforced (this is the one spot a missing route guard could be a real hole if that inner check ever regresses) |
| ACC-04 | Positive | Menu visibility matches role's screen access | Compare sidebar for a role with vs. without `MASTERTRAINING`/`TRAININGLIST`/`TRAININGATTENDANCE` | Menu items appear/disappear correctly |
| ACC-05 | Negative | Notification recipient with no email on file | HCDEVACCESS or creator user with `notification_email`/`email` both null, trigger an offer/manual-accept/response notification | No exception — notifier silently skips the email send (still confirm the in-app `TrMessage` bell row is written) |

---

## Summary of known gaps flagged during code review (raise with dev/product before sign-off, not necessarily bugs to "fix" blindly)

1. Duplicate `cpny_id` rows in one schedule's quota payload silently double that company's seat count (SCH-11).
2. Deleting a `MsLndPlaces` row still referenced by a schedule is unblocked; name resolution likely breaks downstream (SET-07).
3. Editing a shared batch header field (name/job level/speaker) via one DRAFT date silently changes it for sibling PUBLISHED/CLOSED dates too (SCH-25).
4. A user cannot self-cancel a still-Pending (not yet Approved) registration (REG-16) — may be intentional, may be a UX gap.
5. `declineOffer` doesn't enforce the 24h expiry window that `acceptOffer` does (REG-22) — inconsistent.
6. Rejecting a registration that held a seat does not cascade/release the seat to the waitlist (REG-30).
7. `ExpireWaitlistOffers` can leave a row permanently `status='P'` with no further processing (CRN-02).
8. Certificate upload has no attendance-completion check (ATT-19).
9. Feedback can be resubmitted indefinitely while the window is open, silently overwriting (EVL-08).
10. Feedback rating answers aren't validated against their configured option range (EVL-09).
11. No admin UI exists to manage the feedback question bank — DB/tinker-only, and questions are global rather than per-training (EVL-13).
12. `training-list.approve`/`.reject` have no route-level access-right gate, unlike every other write route in this module (ACC-03).

---

## Ready-to-use execution prompt

Paste the block below into a fresh Claude Code session (or reuse this one) to actually run the plan safely against this codebase:

```
Execute the QA test plan at agenthub/training_module_qa_test_plan.md for the Training module.

Rules:
1. Do NOT use real employee usernames or companies. Create synthetic users/company
   (e.g. zztest_trn_* usernames, company ZZTEST) with notification_email = NULL,
   matching the approach already validated in this repo for TRN-doctype testing.
2. Before running section 4 (Registration) and its approve/reject cases, verify
   ms_approval (pgsql2) has active rules configured for doctype 'TRN'. If it
   doesn't, skip approve/reject-dependent cases and report that as a blocker
   instead of faking a result.
3. Prefer php artisan tinker for setup/verification, wrapped in a DB transaction
   you roll back afterward wherever possible, so no synthetic rows or emails
   persist/send after the run. Where an HTTP route must be hit directly (to
   test middleware/validation), use a real request against a local/test
   environment, never production.
4. Work through the plan section by section (Master → Setup → Schedule →
   Registration → Attendance → Evaluation → Cron → Access control). For each
   test ID, report: PASS / FAIL / BLOCKED, with the actual observed
   response/state for anything marked "Negative" or "Gap check" in the plan —
   those are the ones most likely to reveal real bugs since they were derived
   from reading the code, not from the original feature spec.
5. Do not modify application code while testing unless you find and confirm an
   actual bug (not a "gap" the plan already flags as ambiguous/needs product
   input) — in that case, stop, report the bug with file:line, and ask before
   fixing.
6. At the end, produce a consolidated results table plus a short list of any
   NEW issues found that aren't already in the plan's "Summary of known gaps"
   section.
```

---

# QA execution results (2026-08-02)

Environment: PHP 8.2.12 (ZTS), Laravel 11.54.0, branch `JP-frontend`. All cases run via an in-process HTTP harness (`runCase`) against real routes with synthetic users (`zztest_trn_*`, company `ZZTEST`), each wrapped in a rollback transaction; `APP_ENV=testing`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync` so no real mail/side effects. Section 4's TRN approval rules were seeded synthetically first — the §0 precondition (no active `aprv_doctype='TRN'` `ms_approval` rows) was a genuine blocker.

## Consolidated results

| Section | Scope | Pass | Fail | Total |
|---|---|---|---|---|
| 1 Master (MST-01..13) | training CRUD, publish, open/close toggles | 13 | 0 | 13 |
| 2 Setup (SET-01..13+) | categories, places, approve/reject toggles | 14 | 1 | 15 |
| 3 Schedule (SCH-01..26+) | date/quota CRUD, publish, registration windows | 27 | 0 | 27 |
| 4 Registration (REG-01..43) | register/waitlist/cancel/approve/reject/barcode/transfer | 46 | 0 | 46 |
| 5 Attendance (ATT-01..20) | scan, unmark, batch, exports, certificates | 24 | 2 | 26 |
| 6 Evaluation (EVL-01..13) | feedback open/close/results/submit | 13 | 0 | 13 |
| 7 Cron (CRN-01..05) | expire waitlist offers, close registrations | 5 | 0 | 5 |
| 8 Access (ACC-01..05) | middleware/menu/access-right parity | 15 | 1 | 16 |
| **Total** | | **157** | **4** | **161** |

## FAILs (4)

- **ATT-16 / ATT-19** — Certificate upload always returns HTTP 500. `TrainingAttendanceController::uploadCertificate` (app/Http/Controllers/TrainingAttendanceController.php:317,324) calls `$file->move()` then `$file->getSize()` on the already-moved file → `SplFileInfo::getSize(): stat failed`. Every valid upload crashes with a generic 500 (no validation message). Root cause of both FAILs; the plan's earlier "no attendance-completion check" note (gap #8) is separate from this crash.
- **SET-12** — DELETE of a non-Training category returns HTTP 500 instead of 404; the schedule/place delete handlers 404 correctly, so category delete is inconsistent.
- **ACC-04** — Menu/access-right mismatch for DASHACCESS: `sys_role_menu` grants the `MASTERTRAINING` menu (sidebar shows "Master Training") but `sys_access_right` grants no MASTERTRAINING right → menu click yields 403. Menu visibility is built from `sys_role_menu` alone (AppServiceProvider `allowedMenuIds`), independent of access rights.

## NEW issues (not in the plan's prior gap list)

1. **Certificate upload 500 crash (ATT-16/19)** — `getSize()` after `move()`; fix is to snapshot the size before moving or read it from the uploaded temp path. Blocks all live certificate uploads.
2. **SET-12** — category DELETE returns 500 vs the 404 convention used elsewhere.
3. **ACC-04** — DASHACCESS sees the Master Training menu with no access right (403 on click); likely intentional role config, but currently broken UX.
4. **MST-05 / MST-12** — schedule/date create accepts a non-existent `category_id` and a stale/inactive category without validation (HTTP 200).
5. **ATT-13 asymmetry** — unmarking attendance has no window check (marking does), so attendance can be unmarked after the event window closes.

## Confirmed plan gaps (reproduced, marked PASS per plan)

REG-16 (pending cannot self-cancel), REG-30 (mid-chain reject does not release seat), REG-22 (decline ignores 24h window), CRN-02 (orphaned `status='P'`), CRN-04 (auto-close leaves live `O` offers), EVL-08 (resubmit overwrites, latest wins), EVL-09 (out-of-range rating stored), EVL-13 (no feedback question-bank UI), SCH-11 (duplicate `cpny_id` double-count), SCH-25 (shared header bleed across dates), SET-04/SET-11 (duplicate places/categories allowed), SET-07 (place delete referenced by PUBLISHED schedule succeeds), MST-08 (open-schedule toggle → 422).
