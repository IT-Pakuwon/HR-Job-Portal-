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
