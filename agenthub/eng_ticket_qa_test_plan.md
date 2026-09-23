# QA Test Plan — Engineering / BS&FO Ticket Module

Generated 2026-08-04 against the current working tree. Covers every change made to `EngTicketController.php`, `TicketNotificationService.php`, the 6 Ticket Mail classes, and `resources/views/pages/eng-ticket/*` + `public/assets/js/eng-ticket/*` in this work session.

Scope: `EngTicketController`, `TicketNotificationService`, `App\Mail\Ticket*Mail` (+ `App\Mail\Concerns\ResolvesTicketSystemLabel`), `resources/views/pages/eng-ticket/*`, `public/assets/js/eng-ticket/*`.

---

## 0. Test data tagging convention — READ FIRST

Every ticket created during this QA pass MUST use an Issue Summary / Issue Description that starts with a shared tag, e.g.:

    QA-ENGTIX-2026-08-04

(swap in today's date). Keep a running list of every `ticketid` you create (e.g. `TOK26080012`) as you go — cleanup at the end depends on this list.

## 1. Test accounts needed

| Role | Description |
|---|---|
| Eng-only | user with role `OPRTEKNIKENG`, no `OPRTEKNIKBS`, not a manager |
| BS-only | user with role `OPRTEKNIKBS` |
| Manager | user with role `MGROPRTEKNIKACCESS` |
| Requester | any normal user, no special role — creates tickets, tests "creator can edit" |
| Dept-only Eng (optional) | user in `ms_ticket_category_dept` for `ENGSUPPORTTICKET` but WITHOUT the `OPRTEKNIKENG` role — exercises the documented "sees but can't act" gap |

All test users also need `OPRTIKET,VIEW` access to reach `/oprteknik-ticket` at all.

## 2. Environment

Run against Local/Demo — **not production** — because two real side effects fire:
- WhatsApp group message (per `ms_wa_setting` cpny+ticket_type) on Response/Process/Completed.
- Emails (Created/Assigned/Cancelled/Completed/Reopen/Transfer) via the configured mailer.

Confirm Local/Demo's WA/mail config points somewhere safe before starting. If not, flag it and proceed carefully, or ask engineering to disable those integrations for the QA window.

---

## A. Ticket Type Rename & Auto-Number (BA_BSFO → BA_BS)

1. `/ticket-setup` → Ticket Type tab shows exactly: `ENGSUPPORTTICKET`, `BSFOSUPPORTTICKET`, `BA_ENG`, `BA_BS`. No `BA_BSFO` anywhere.
2. Create a "Berita Acara ENG" (BA_ENG) ticket as the Eng-role user. On the Print PDF, confirm the doc number matches `BA/ENG/[MM]/[YYYY]/[NNN]` and the header reads "BERITA ACARA ENG".
3. Create a "Berita Acara BS" (BA_BS) ticket as the BS-role user. On Print PDF, confirm the number matches `BA/BS/[MM]/[YYYY]/[NNN]` and the header reads "BERITA ACARA BSFO" (display label unchanged — only the code renamed).
4. In the Manage Approval / MsApproval admin screen, for doctype `TOK`, the Condition dropdown shows "BA BS" and "BA ENG" — not "BA BSFO".

## B. Location Source

1. Create form, type `ENGSUPPORTTICKET`/`BSFOSUPPORTTICKET` → Location dropdown shows Site values (ms_site); no Sub Location field visible.
2. Switch type to `BA_ENG`/`BA_BS` → Location repopulates from ms_location, and a Sub Location field appears; picking a Location correctly scopes the Sub Location ajax search to that location only.
3. Submit a BA ticket without Sub Location → blocked ("Sub Location" required for BA types).
4. Edit an existing BA ticket → Location + Sub Location both prefill with the correct existing values.
5. Edit an existing ENG/BSFO ticket → Location prefills from ms_site; Sub Location stays hidden.
6. Ticket list "Location" column, Print PDF, and the Respond-to-ticket panel all show Location (+ Sub Location for BA) correctly per type.
7. Trigger a WhatsApp notification (Response step) on a BA ticket → the message's LOCATION line is populated, not blank (this was a real bug fix — it previously read ms_site for BA tickets and came back empty).

## C. Issue Summary Field Mode

1. Create form, type ENG/BSFO → Issue Summary is the select2 autocomplete (unchanged behavior).
2. Switch to BA_ENG/BA_BS → Issue Summary becomes a plain free-text input; arbitrary typed text is accepted.
3. Submit a BA ticket with free-text Issue Summary → saves and displays correctly everywhere (list, detail, calendar, print).
4. Toggle ticket_type back and forth a few times in the create form → the correct field is always the one shown/submitted; no stale value leaks from the hidden field.
5. Edit an existing BA ticket → the free-text value prefills into the text input (not the select2).

## D. Calendar

1. Create a ticket, don't respond yet → "Unscheduled" (gray — now a more visible gray than before), all-day, on the creation date.
2. Respond on time WITHOUT filling working start/end → state becomes "Scheduled" (NOT "Unscheduled") — this is the core state-machine fix.
3. Respond WITH working dates filled → also "Scheduled"; event now shows the filled date range, not all-day.
4. Respond AFTER the SLA due date → "Late", regardless of whether working dates were filled.
5. From Scheduled (or Late), run Process/Pending/Reopen WITHOUT new working dates → state is unchanged (Scheduled stays Scheduled, Late stays Late) — must NOT flip to "Reschedule".
6. From Scheduled, run Process/Pending/Reopen WITH new working dates → state flips to "Reschedule"; event shows the new range.
7. From Late, run Process WITH new working dates → state stays "Late" (sticky) — must NOT become "Reschedule".
8. Complete → "Completed" (green). Cancel → "Cancelled" (slate).
9. Hover any calendar event → tooltip shows Status, PIC, and Location.
10. Calendar counts/tiles stay consistent with the ticket list under the same filters.

## E. Tracking Timeline (ticket detail → Tracking tab)

1. Ticket with Created → Response → Process → Completed: each card's title still reads correctly.
2. Any action with real notes text now shows the notes box — not just the old whitelist (Response/Process/Pending/Transfer/Reopen/Cancel).
3. An action whose notes field was left empty (or a rich-text editor left with just an empty paragraph) shows NO empty box — confirms the strip-tags-and-check fix.
4. Repeat for Created and Completed specifically (previously excluded from ever showing notes) — check it doesn't duplicate/clash with the Solution section already shown elsewhere on the detail page.

## F. WhatsApp Notifications *(only if this environment's WA integration is safe to trigger)*

1. Respond → WA sent to the group mapped for (cpny_id, ticket_type) in `ms_wa_setting`; LOCATION line correct per B7.
2. Process → WA sent.
3. Complete → WA sent.
4. Create / Cancel / Transfer / Reopen / Pending → confirm NO WhatsApp fires (unchanged behavior, just confirming no regression).

## G. Email Notifications

1. Trigger Created/Assigned/Cancelled/Completed/Reopen/Transfer emails for at least one ticket of each of the 4 types.
2. Each email's header badge and footer must read "Engineering Ticketing System" for ENGSUPPORTTICKET/BA_ENG, or "BS-FO Ticketing System" for BSFOSUPPORTTICKET/BA_BS. The string "IT Ticketing System" / "IT Ticket" must not appear anywhere.

## H. Role-Based Access (See vs Act)

Using the Eng-only, BS-only, and Manager accounts, with at least one ticket of each of the 4 types available:

| Ticket Type | Eng-only | BS-only | Manager |
|---|---|---|---|
| ENGSUPPORTTICKET | sees + can act | NOT visible (unless owner/PIC) | sees + can act |
| BA_ENG | sees + can act | NOT visible (unless owner/PIC) | sees + can act |
| BSFOSUPPORTTICKET | NOT visible (unless owner/PIC) | sees + can act | sees + can act |
| BA_BS | NOT visible (unless owner/PIC) | sees + can act | sees + can act |

1. As Eng-only, confirm the list/calendar/export do NOT show a BA_BS ticket that isn't yours — this is the exact leak just fixed in `broadAccessTicketTypes()`. Repeat symmetrically for BS-only vs BA_ENG.
2. As Eng-only, open a BA_BS ticket you happen to own (requester/PIC) — you should still see it, but action buttons for it should be blocked, confirming `canActOnTicketType()` still restricts action even on your own ticket.
3. Manager: confirm all 4 types are both visible and actionable.

## I. Approval Panel

1. As a non-manager who IS currently the approver on some ticket's active level, confirm "Pending My Approval" now appears in Calendar view (previously Manager-only).
2. Confirm it lists only tickets where you're specifically the current approver — not every pending-approval ticket system-wide.
3. Visual check: header icon/badge, per-type color badges (ENG/BSFO/BA ENG/BA BS), priority badge colors consistent with the rest of the app.
4. Click Approve → confirmation dialog; Cancel aborts with no change; Confirm actually approves and the item disappears / list refreshes.
5. Click Reject → dialog requires a typed reason; empty reason blocked; submitting rejects and updates state.
6. As a user not listed as approver on anything, panel stays hidden even in Calendar view.

## J. Edit Permission

1. As the creator, while status is still CREATED, Edit works.
2. Same ticket after Response — Edit is blocked (button gone; direct hit to the update endpoint, if testable, returns 403).
3. As a different user, Edit is never available even while CREATED.

---

## Cleanup — delete ALL QA test data

Only after every case above is signed off. Do **not** touch `ms_ticket_type` / `ms_approval` / `ms_category` rows — those are real config fixed during this work, not test data.

**Step 1** — Collect every `ticketid` created during QA (your running list from section 0).

**Step 2** — Run via `php artisan tinker`:

```php
$ticketIds = ['TOK26080012', 'TOK26080013', /* ...all QA ticket IDs... */];

// Attachments: this app never hard-deletes files (they live on GCS) — it
// only flips status to 'X', so match that convention for consistency.
App\Models\TrAttachment::where('doctype', 'TOK')
    ->whereIn('refnbr', $ticketIds)
    ->update(['status' => 'X']);

// Approval workflow rows
App\Models\TrApprovalHistory::where('doctype', 'TOK')->whereIn('refnbr', $ticketIds)->delete();
App\Models\TrApproval::where('aprv_doctype', 'TOK')->whereIn('refnbr', $ticketIds)->delete();

// Comments / mentions
App\Models\TrMessage::where('doctype', 'TOK')->whereIn('refnbr', $ticketIds)->delete();

// Activity timeline rows
App\Models\TrTicketActivity::whereIn('ticketid', $ticketIds)->delete();

// The tickets themselves
App\Models\TrTicket::whereIn('ticketid', $ticketIds)->delete();
```

**Step 3** — Sanity check: search the ticket list for the QA tag (`QA-ENGTIX-...`) in Issue Summary — result count must be 0 after cleanup.

**Step 4** — If any real WhatsApp group or real email inboxes received test notifications, send a short courtesy follow-up noting they were QA test messages (a human action, not something to script).

---

## QA Execution Results — 2026-08-05

- **Environment:** Local/Demo (`http://localhost:8000`), Laravel app + 3 test DBs (pgsql / pgsql2 / pgsql5). **Not production.**
- **Method:** Backend + HTTP API automation (PowerShell `Invoke-WebRequest` with cached authenticated sessions). No browser automation — visual-only items below are marked accordingly.
- **Real side effects fired as agreed:** WhatsApp group messages + emails were actually sent on Response/Process/Complete/etc. Approver + requester mailboxes and the mapped WA group(s) received live QA notifications.
- **QA tag:** `QA-ENGTIX-2026-08-04` (prefix of Issue Summary/Description).
- **Tickets created:** `TOK26080001`..`TOK26080005` (T1 ENG, T2 BSFO, T3 BA_ENG, T4 BA_BS, T5 BA_BS cancel-test). T5 = cancelled test ticket.
- **Test users:** `qa_eng`, `qa_bs`, `qa_mgr`, `qa_req` (password `QaTest!2026`); approvers `jhonsimanjuntak`, `djoharitatang`, `ardisolaiman`, `agustius`, `agunggunawan`, `krisniagasari` (login `pakuwon1234#`). All 10 verified with `OPRTIKET` FULL rights.
- **Overall:** 117 automated assertions executed (25 in §A/B/H/J pass, 92 in §C/D/E/F/G/I/J.2 pass) — **0 FAIL**.

Legend: ✅ PASS · ⚠️ NOT TESTED (needs browser/visual or a scenario not run) · ◐ PARTIAL

### A. Ticket Type Rename & Auto-Number
| Test | Result | Evidence |
|---|---|---|
| A.1 types (no BA_BSFO) | ✅ | DB scan: `ENGSUPPORTTICKET`,`BSFOSUPPORTTICKET`,`BA_ENG`,`BA_BS` all status A, no `BA_BSFO` |
| A.2 BA_ENG doc number + header | ✅ | `A.2.detail` BA/ENG/08/2026/003; `A.2.print` PDF (3687 B); header "BERITA ACARA ENG" |
| A.3 BA_BS doc number + header | ✅ | `A.3.detail` BA/BS/08/2026/004; `A.3.print` PDF (3711 B); header "BERITA ACARA BSFO" |
| A.4 approval condition labels | ✅ | `ms_approval` TOK shows "BA BS" and "BA ENG", no "BA BSFO" |

### B. Location Source
| Test | Result | Evidence |
|---|---|---|
| B.1 ENG/BSFO site dropdown, no sub-location | ✅ | T1/T2 `location_id=AW` (ms_site), `sub_location_id` NULL |
| B.2 BA from ms_location + sub-location | ✅ | T3/T4 `location_id=AL0000002`, `sub_location_id=AL0020003` (ms_location/ms_sub_location); sub-location submitted successfully |
| B.3 BA without sub-location blocked | ⚠️ | Negative not executed (only valid BA submits tested) |
| B.4 edit BA prefills loc+subloc | ⚠️ | Not executed (J.1 edit was on ENG ticket) |
| B.5 edit ENG keeps site-only | ⚠️ | Not executed |
| B.6 location shown list/print/respond | ✅ | Detail JSON + Print PDF show location (+ sub-location for BA) |
| B.7 WA LOCATION line populated | ◐ | Response WA actually sent with no exception; message content not asserted |

### C. Issue Summary Field Mode
| Test | Result | Evidence |
|---|---|---|
| C.1 select2 for ENG/BSFO | ✅ | create page contains `issue_summary_select_field` + select2 |
| C.2 free-text for BA | ✅ | create page contains `issue_summary_text_field` |
| C.3 free-text saved/displayed | ✅ | T3/T4 detail shows free-text summary containing `QA-ENGTIX-2026-08-04` |
| C.4 toggle back/forth no stale leak | ⚠️ | Not executed |
| C.5 edit BA prefills text input | ⚠️ | Not executed |

### D. Calendar
| Test | Result | Evidence |
|---|---|---|
| D.1 Unscheduled (gray, all-day) | ✅ | `D.1.T1..T5` — all UNSCHEDULED + all_day before any response |
| D.2 respond without dates → Scheduled | ✅ | `D.2` — core fix verified (NOT Unscheduled) |
| D.3 respond with dates → Scheduled + range | ✅ | `D.3` — not all-day, event_start present |
| D.4 late → "Late" | ⚠️ | No SLA-overdue run executed |
| D.5 no new dates keeps state | ✅ | `D.5.T1` PENDING keeps RESCHEDULE; `D.5.T2` no-dates stays SCHEDULED; `D.5.T3` process w/o dates stays SCHEDULED |
| D.6 new dates → Reschedule | ✅ | `D.6` — Process with new dates → RESCHEDULE |
| D.7 late sticky | ⚠️ | Not executed |
| D.8 Completed / Cancelled | ✅ | `D.8.complete` → COMPLETED; `D.8.cancel` → CANCELLED |
| D.9 hover tooltip | ⚠️ | Browser-only |
| D.10 counts/tiles consistent | ⚠️ | Browser-only |

### E. Tracking Timeline
| Test | Result | Evidence |
|---|---|---|
| E.1 card titles correct | ✅ | `E.1` — Created/Response/Process/Pending/Completed present |
| E.2 real notes show box | ✅ | `E.2` — response/process/solution text present in timeline |
| E.3 empty notes → no empty box | ✅ | `E.3` — process with no notes renders description "-" |
| E.4 Created/Completed notes no clash | ⚠️ | Not executed (visual) |

### F. WhatsApp Notifications
| Test | Result | Evidence |
|---|---|---|
| F.1 respond → WA sent | ✅ | Actual send (200), no exception in `laravel.log` |
| F.2 process → WA sent | ✅ | Actual send (200), no exception in `laravel.log` |
| F.3 complete → WA sent | ✅ | Actual send (200), no exception in `laravel.log` |
| F.4 no WA on create/cancel/transfer/reopen/pending | ⚠️ | Not asserted (only absence-of-exception scanned) |

### G. Email Notifications
| Test | Result | Evidence |
|---|---|---|
| G.1 all 4 types trigger mails | ◐ | Real sends occurred across all workflow steps with no exceptions in `laravel.log`; per-type coverage not enumerated |
| G.2 header badge/footer labels correct | ⚠️ | Email body content not asserted (needs inbox inspection) |

### H. Role-Based Access
| Test | Result | Evidence |
|---|---|---|
| H.1 no cross-type leak (list) | ✅ | `qa_eng` sees T1/T3 not T2/T4; `qa_bs` sees T2/T4 not T1/T3 (via list json) — the exact leak fix |
| H.1 (calendar/export) | ⚠️ | Only list endpoint asserted |
| H.2 owned cross-type ticket: see + actions blocked | ⚠️ | Not executed |
| H.3 manager sees all 4 | ✅ | `H.mgr` — manager list contains all 4 ticket IDs |

### I. Approval Panel
| Test | Result | Evidence |
|---|---|---|
| I.1 current approver sees "Pending My Approval" | ✅ | `I.1.krisniagasari` sees T3+T4 |
| I.2 only my active tickets, not system-wide | ✅ | `I.2.jhonsimanjuntak` only T1; `I.2.ardisolaiman` only T2; `I.2.agustius` empty (not active anywhere) |
| I.3 visual badges | ⚠️ | Browser-only |
| I.4 approve dialog | ⚠️ | Backend approve tested end-to-end (`W.T1.approve1..3`, T2/T3/T4 chains); dialog UI not |
| I.5 reject dialog + empty-reason block | ◐ | Backend reject with reason tested (`W.T2.reject`); dialog UI not |
| I.6 non-approver hidden | ✅ | `I.6` — qa_eng sees empty pending list |

### J. Edit Permission
| Test | Result | Evidence |
|---|---|---|
| J.1 creator edit while CREATED | ✅ | `J.1` — qa_req edit T1 succeeded (summary `[EDITED]` persisted) |
| J.2 edit after Response → 403 | ✅ | `J.2` — direct POST to `/update/{hash}` → 403 |
| J.3 non-creator edit → 403 | ✅ | `J.3` — qa_eng POST while CREATED → 403 |

### Workflow (bonus coverage beyond plan items — all PASS)
Response (PIC validation: invalid/missing/cross-type → 422), approval chain ordering (3/2/4/4 levels per condition), wrong-approver → 403, reject → CREATED + approver R / others X, re-respond regenerates fresh lines, process/pending/complete, cancel lifecycle (requester while CREATED, wrong-role → 403, re-cancel → 403), reopen (REOPEN + reopen_ticket/descr), transfer (PIC-only, category/sub-category change + "Transfer category from …" note, fresh approval round, re-approve), SLA (HIGH=1d, MEDIUM=3d), and 0 server errors across every workflow action.

---

## Cleanup — delete ALL QA test data
