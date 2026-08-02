# TASK: Training Master List - Add Setup Button, MsLndPlaces Model & Doctype TE Category CRUD

## Objective
Enhance the Master Training module by adding a **Setup** button adjacent to the **Add Training** button. Implement a full CRUD system for Training Places (`ms_lnd_places`) and update category management for `ms_category` filtered strictly by `doctype = 'TE'`.

---

## 1. Database & Model Creation (`ms_lnd_places`)

1. **Migration**: Create a migration for `ms_lnd_places` with the following schema:
   - `id` (bigIncrements / Primary Key)
   - `places_id` (string, unique / auto-generated identifier if applicable)
   - `places_name` (string)
   - `places_address` (text, nullable)
   - `status` (string/char, default active)
   - Audit Columns: `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_by`, `deleted_at` (SoftDeletes)

2. **Model (`app/Models/MsLndPlaces.php`)**:
   - Create `MsLndPlaces` with `SoftDeletes` trait enabled.
   - Define `$fillable` array matching all schema attributes.

---

## 2. Category Handling (`ms_category`)

1. Inspect `app/Models/MsCategory.php` using `php artisan tinker` or model reference to verify existing column structures.
2. Ensure all Category queries inside the Training Setup feature **filter strictly by `doctype = 'TE'`**.
3. When creating or updating records in `ms_category` via the Training Setup menu, **force set `doctype = 'TE'`**.

---

## 3. UI Implementation: "Setup" Button & Modal/Blade Layout

1. **Button Placement**:
   - Locate the Master Training view template (e.g., `resources/views/pages/training/index.blade.php` or similar master training blade).
   - Place a **Setup** button immediately to the **left** of the **Add Training** button.

2. **Setup Interface**:
   - Create a dedicated Setup view or Tabbed Modal inside the view that contains two CRUD sub-sections/tabs:
     - **Tab 1: Training Places (`ms_lnd_places`)**
     - **Tab 2: Training Categories (`ms_category` where `doctype = 'TE'`)**

3. **CRUD Features for Both Tabs**:
   - **Data Table / List View**: Render active records with search/pagination.
   - **Create / Edit Form Modal**:
     - For Places: `places_name`, `places_address`, `status`.
     - For Categories: Category name/code, ensuring `doctype = 'TE'` is attached automatically.
   - **Delete (Soft Delete)**: Action to remove items safely.

---

## 4. Backend Controller Logic

1. Create or update controller methods (e.g., in `TrainingController.php` or a dedicated `TrainingSetupController.php`):
   - `getPlaces()` / `storePlace()` / `updatePlace()` / `deletePlace()`
   - `getCategories()` / `storeCategory()` / `updateCategory()` / `deleteCategory()`
2. Ensure proper authorization checks (`Auth::user()`) and auto-populate `created_by` / `updated_by` fields.

---

## 5. Execution Verification
- Clear view cache (`php artisan view:clear`).
- Verify that saving a new category assigns `doctype = 'TE'`.
- Verify that creating, updating, and soft-deleting `ms_lnd_places` records work seamlessly.
