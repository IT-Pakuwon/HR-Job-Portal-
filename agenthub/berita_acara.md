# TASK: Add 'Berita Acara' Ticket Type & Conditional Approval Routing

## Objective
Update the Engineering Ticket system (`app/Http/Controllers/EngTicketController.php` and associated views/models) to introduce a new ticket type named 'Berita Acara' with specific Category/Sub-Category mappings and dynamic conditional approval routing.

---

## 1. Type, Category & Sub-Category Options
Add/configure the following ticket structures:
- **Ticket Type**: `Berita Acara`
- **Categories for Berita Acara**:
  - `BSFO`
  - `ENGINEERING`
- **Sub-Categories (for both BSFO & ENGINEERING categories)**:
  - `New Purchase`
  - `Replacement`

---

## 2. Dynamic Approval Routing Logic (`EngTicketController.php`)

### Conditions:
Update the controller logic where approvals are created or evaluated:
1. **Existing Types (BSFO / ENG)**: Maintain their existing approval routing.
2. **New Type ('Berita Acara')**: Route the conditional approval steps based on the selected **Type** + **Category**:
   - If `Type == 'Berita Acara'` AND `Category == 'BSFO'`: Apply condition **`BA BSFO`**.
   - If `Type == 'Berita Acara'` AND `Category == 'ENGINEERING'`: Apply condition **`BA ENG`**.

### Approval Types:
Ensure the approval engine handles both:
- **Normal Approval**: Standard sequential hierarchy.
- **Condition Approval**: Evaluates approval conditions based on ticket properties (`BA BSFO` vs. `BA ENG`).

---

## 3. Scope & Implementation Steps

1. **Controller (`app/Http/Controllers/EngTicketController.php`)**:
   - Update `store()` and `update()` methods to handle the new `Berita Acara` type and evaluate approval conditions (`BA BSFO` / `BA ENG`).
   - Pass updated Category and Sub-Category lists to the view or API endpoint.

2. **Form Views (`resources/views/...`)**:
   - Update ticket creation/edit views to dynamically filter Categories and Sub-Categories when `Berita Acara` is selected as the Ticket Type.

3. **Models / Approval Master Data**:
   - Update relevant models or database lookups if approval templates or conditions are stored in master tables.
