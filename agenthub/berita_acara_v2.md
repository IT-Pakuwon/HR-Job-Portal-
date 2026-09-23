# TASK: Refactor Engineering Ticket Types to 'Berita Acara BSFO' & 'Berita Acara ENG', Approval Conditions, and Dynamic PDF/Auto-Numbering

## Objective
Update the Engineering Ticket system (`app/Http/Controllers/EngTicketController.php`, associated Blade views, PDF templates, and Models) to support dedicated Berita Acara ticket types, simplified category mappings, condition-based approval routing, dynamic PDF headers, and custom document auto-numbering formats.

---

## 1. Ticket Type, Category & Sub-Category Mappings

### Ticket Types:
- `Berita Acara BSFO`
- `Berita Acara ENG`
- *(Retain existing legacy types)*

### Categories (Applicable for both Berita Acara types):
- `New Purchase`
- `Replacement`

### Sub-Categories:
- `Plumbing`
- *(And other existing engineering sub-categories/disciplines)*

---

## 2. Dynamic Approval Routing Logic (`EngTicketController.php`)

Update approval evaluation during ticket submission (`store` / `update`):
- If `Type == 'Berita Acara BSFO'`: Assign condition **`BA BSFO`**.
- If `Type == 'Berita Acara ENG'`: Assign condition **`BA ENG`**.
- Ensure approval engine handles both **Normal Approval** (sequential hierarchy) and **Condition Approval** based on these exact condition keys (`BA BSFO` vs. `BA ENG`).

---

## 3. Auto-Numbering & PDF Generation Engine

### A. Document Auto-Numbering Engine
When a ticket is generated/approved for these new types, generate an auto-numbered reference string in the following formats:
- **For `Berita Acara BSFO`**: `000/BA-BSFO/[DEPT_OR_CODE]/[MONTH]/[YEAR]`
- **For `Berita Acara ENG`**: `000/BA-ENG/[DEPT_OR_CODE]/[MONTH]/[YEAR]`

### B. Dynamic PDF Export/Print Layout
In the PDF view rendering template (e.g., inside `EngTicketController.php` print methods or dedicated Blade PDF views):
- **Dynamic Header / Title**: Dynamically set the document title to **"BERITA ACARA BSFO"** or **"BERITA ACARA ENG"** matching the selected type.
- **Header Block**: Display the newly generated custom Auto-Number string prominently in the document metadata section.

---

## 4. Implementation Steps & Scope

1. **Controller (`app/Http/Controllers/EngTicketController.php`)**:
   - Update `store()`, `update()`, and PDF print/export methods.
   - Inject logic for auto-number string construction (`BA-BSFO` vs `BA-ENG`).
   - Route approval workflows based directly on the Ticket Type string.

2. **Form Views (`resources/views/...`)**:
   - Update form selection dropdowns/JavaScript cascading filters for Ticket Type, Category, and Sub-Category.

3. **PDF Template Views (`resources/views/.../pdf.blade.php`)**:
   - Add conditional logic to render the appropriate document title and display the formatted auto-number string.
