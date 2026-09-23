# TASK: Complete Refactor of L&D Training Registration Engine, Batch Multi-Participant Flow & Approval Architecture

## Objective
Refactor the Training Registration module from the legacy `tr_training_registration` schema to the new `tr_lnd_training_registration` and `tr_lnd_training_attendance` tables. Implement batch registration (self + colleagues), automated company/department data capture, preview modal, observer-driven unique barcode generation, and 24-hour waitlist offer expiry mechanics.

---

## 1. Database Schemas & Eloquent Models (i already have this on the database you can check via tinker)

### A. `TrLndTrainingRegistration` (`app/Models/TrLndTrainingRegistration.php`)
- **Table Name**: `tr_lnd_training_registration` (Uses `SoftDeletes`)
- **Fields**:
  - `id` (PK)
  - `training_regist_id` (String: Shared document number across participants registered in the same batch)
  - `training_regist_date` (Date/Timestamp)
  - `training_id`, `training_detail_id`, `schedule_id`, `schedule_date`
  - `cpny_id`, `department_id` (Populated from participant's `orgn_company_id` and `orgn_department_id` in `ms_user`)
  - `user_registration` (String: Username of the participant for this specific row)
  - `qty_registration` (Integer: Strictly set to `1` per row)
  - `status` (String: Approval status ONLY $\rightarrow$ `'P'` Pending, `'C'` Approved/Complete, `'R'` Rejected)
  - `status_registration` (String: Registration lifecycle $\rightarrow$ `'W'` Waitlisted, `'O'` Offered, `'X'` Cancelled, `null` Normal/In-approval)
  - `process_registration_user`, `process_registration_date` (Timestamp stamp for state changes; waitlist 24h offer expiry computed as `process_registration_date + 24 hours`)
  - `attendance_code` (String: Unique barcode generated automatically per row upon approval)
  - `completed_by`, `completed_at` (Per-person attendance completion marker)
  - Audit Columns: `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_by`, `deleted_at`

### B. `TrLndTrainingAttendance` (`app/Models/TrLndTrainingAttendance.php`)
- **Table Name**: `tr_lnd_training_attendance` (Uses `SoftDeletes`)
- **Fields**: `id`, `training_regist_id` (FK to shared document string), `attendance_datetime`, `status`, `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_by`, `deleted_at`

---

## 2. Comprehensive Registration & Approval Flow

### Step 1: Participant Selection (Self vs. Colleague Batch)
1. **Self-Registration (Default)**:
   - Sets `user_registration` = `Auth::user()->username`.
   - Pulls `cpny_id` = `Auth::user()->orgn_company_id` and `department_id` = `Auth::user()->orgn_department_id`.
   - `qty_registration` = `1`.
2. **Register for Colleagues (Checkbox Option)**:
   - When checked, display a multi-select user picker populated **ONLY with users from `ms_user` who share the exact same `orgn_company_id` and `orgn_department_id` as the logged-in user**.
   - Submitter can select colleagues (and optionally themselves).
   - For every selected participant, create **1 distinct row** in `tr_lnd_training_registration`.
   - Each row gets its respective participant's `orgn_company_id` and `orgn_department_id`.
   - All rows created in the batch **SHARE the same `training_regist_id`** (Document Code) and have `qty_registration = 1`.

### Step 2: Quota & Duplicate Check Engine
1. **Quota Validation**:
   - Query `MsLndTrainingQuota` by `schedule_id` (FIX: Do not use `schedule_detail_id`).
   - Evaluate `Available Quota` against `Batch Count`.
   - If `Available Quota < Batch Count`, alert user in UI before submission (*"Not enough quota seats available for all selected participants"*).
2. **Duplicate Prevention Guardrail**:
   - Validate that no active registration (`status_registration != 'X'`) already exists for any selected `user_registration` on the target `schedule_id`.

### Step 3: Confirmation Preview Modal
- Before executing the POST submission, trigger a **Preview Modal** displaying:
  - **Training Title & Schedule Date**
  - **Participant Table**: Name, Username, Origin Company (`cpny_id`), Origin Department (`department_id`), Qty (`1`).
  - **Total Batch Count**: (e.g., *"Total Registering: 3 Participants"*).
  - **Submission Notice**: *"Submitting will initiate approval routing under Document #[TR_REG_ID] for all listed participants."*

### Step 4: Batch Document Approval & Observer Barcode Generation
1. **Approval Execution**:
   - `ApprovalController` processes approval keyed by `training_regist_id` (Document Code).
   - When the approver approves, **all rows sharing that `training_regist_id` transition `status` to `'C'` simultaneously**.
2. **Observer-Driven Barcode Generation**:
   - Implement `TrLndTrainingRegistrationObserver.php`:
     - Listen for `updating` event: If `status` becomes `'C'` and `attendance_code` is null, generate a unique barcode string (e.g., `TRN-[RANDOM_10]`).
     - **Result**: Guarantees every individual row gets its own unique barcode regardless of how approval is triggered.

### Step 5: Waitlist 24-Hour Expiry Mechanics
- When a slot opens, `TrainingRegistrationService::offerSlot()` sets `status_registration = 'O'` and updates `process_registration_date = now()`.
- `ExpireWaitlistOffers` cron command checks rows with `status_registration = 'O'` where `process_registration_date + 24 hours < now()`.
- If expired, set `status_registration = 'X'` and automatically trigger waitlist promotion for the next queued candidate.

### Step 6: Attendance Logging
- On event day, scanning a barcode updates `completed_by` / `completed_at` on the participant's `tr_lnd_training_registration` row, and appends an event log entry in `tr_lnd_training_attendance` referencing the shared `training_regist_id`.

---

## 3. Files & Implementation Scope

1. **Models & Observers**:
   - `app/Models/TrLndTrainingRegistration.php` (Updated table, soft deletes, status constants).
   - `app/Models/TrLndTrainingAttendance.php` (Created).
   - `app/Observers/TrLndTrainingRegistrationObserver.php` (Barcode generator).
   - Register observer in `AppServiceProvider.php`.
2. **Controllers**:
   - `app/Http/Controllers/TrainingRegistrationController.php`: Rewrite `json()`, `register()`, `cancel()`, `acceptOffer()`, `declineOffer()`, `approve()`, `reject()`, `barcodeStatus()`, `barcodeImage()`, `myRegistrations()`. Fix `schedule_id` quota lookup.
   - `app/Http/Controllers/TrainingAttendanceController.php`: Update `scan()`, `markAttend()`, `roster()`, `afterEvent()`, and exports to write/read `completed_at` and `user_registration`.
3. **Services & Commands**:
   - `app/Services/TrainingRegistrationService.php`
   - `app/Services/TrainingWaitlistNotifier.php`
   - `app/Console/Commands/ExpireWaitlistOffers.php`
   - `app/Exports/TrainingAttendanceExport.php`
4. **Blade Views & JS**:
   - Update training registration blade view to add "Register for Colleague(s)" toggle.
   - Add AJAX user fetch endpoint filtered by current user's `orgn_company_id` and `orgn_department_id`.
   - Build Confirmation Preview Modal before final submit.
