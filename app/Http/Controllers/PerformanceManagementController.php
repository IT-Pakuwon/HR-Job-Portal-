<?php

namespace App\Http\Controllers;

use App\Models\UserDas;
use App\Models\Users_talenta;
use App\Models\ViewUsersTalenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PerformanceManagementController extends Controller
{
    // Columns that exist identically on both view_users_talenta (pgsql2, live)
    // and users_talenta (mysql2, local cache) — id/created_user/updated_user/
    // created_at/updated_at are bookkeeping columns, not Talenta content, so
    // they're excluded here and from what gets copied on sync.
    private const COMPARABLE_FIELDS = [
        'created_talenta', 'updated_talenta', 'first_name', 'last_name', 'email',
        'identity_type', 'identity_number', 'expired_date_identity_id', 'postal_code',
        'address', 'current_address', 'birth_place', 'birth_date', 'phone', 'mobile_phone',
        'gender', 'marital_status', 'religion', 'avatar', 'employee_id', 'company_id',
        'organization_id', 'organization_name', 'job_position_id', 'job_position',
        'job_level', 'employment_status', 'end_date', 'branch_id', 'branch', 'join_date',
        'length_of_service', 'grade', 'class', 'approval_line', 'status_talenta',
        'resign_date', 'avatar_local', 'status',
    ];

    // Subset of COMPARABLE_FIELDS actually used to flag a row as "changed".
    // Excluded on purpose (still copied on sync, just not used to detect drift):
    // - avatar: OSS-signed URL, signature/expiry rotate on every fetch even when the photo hasn't changed
    // - length_of_service: computed from today's date, so it differs daily by construction
    // - grade/class: the view emits '0' as a not-set placeholder where the local cache has '' — neither
    //   side has ever carried a real grade/class value, so comparing them is pure formatting noise
    // - created_talenta/updated_talenta: Talenta's internal touch timestamps, not visible HR content
    private const DIFF_FIELDS = [
        'first_name', 'last_name', 'email', 'identity_type', 'identity_number',
        'expired_date_identity_id', 'postal_code', 'address', 'current_address',
        'birth_place', 'birth_date', 'phone', 'mobile_phone', 'gender', 'marital_status',
        'religion', 'employee_id', 'company_id', 'organization_id', 'organization_name',
        'job_position_id', 'job_position', 'job_level', 'employment_status', 'end_date',
        'branch_id', 'branch', 'join_date', 'approval_line', 'status_talenta',
        'resign_date', 'status',
    ];

    public function index()
    {
        return view('pages.performance_management.performance_management');
    }

    // =========================================================
    // Tab 1: Comparison (view_users_talenta live vs users_talenta local)
    // =========================================================

    public function compareLiveJson()
    {
        $local = Users_talenta::query()->get()->keyBy('user_id');

        $rows = ViewUsersTalenta::query()->orderBy('user_id')->get()->map(function ($view) use ($local) {
            $old = $local->get($view->user_id);
            [$status, $changedFields] = $this->diffStatus($view, $old);

            return [
                'user_id' => $view->user_id,
                'name' => trim("{$view->first_name} {$view->last_name}"),
                'employee_id' => $view->employee_id,
                'job_position' => $view->job_position,
                'job_level' => $view->job_level,
                'organization_name' => $view->organization_name,
                'status_talenta' => $view->status_talenta,
                'local_status' => $old->status ?? null,
                'diff_status' => $status,
                'changed_fields' => $changedFields,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function compareLocalJson()
    {
        $live = ViewUsersTalenta::query()->get()->keyBy('user_id');

        $rows = Users_talenta::query()->orderBy('user_id')->get()->map(function ($old) use ($live) {
            $view = $live->get($old->user_id);
            [$status, $changedFields] = $view ? $this->diffStatus($view, $old) : ['local_only', []];

            return [
                'user_id' => $old->user_id,
                'name' => trim("{$old->first_name} {$old->last_name}"),
                'employee_id' => $old->employee_id,
                'job_position' => $old->job_position,
                'job_level' => $old->job_level,
                'organization_name' => $old->organization_name,
                'status_talenta' => $old->status_talenta,
                'status' => $old->status,
                'diff_status' => $status,
                'changed_fields' => $changedFields,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    private function isResigned(?string $statusTalenta): bool
    {
        return $statusTalenta && stripos($statusTalenta, 'resign') !== false;
    }

    // Local Cache row whose Talenta status is Resigned but whose local `status` is still
    // active — deactivating it sets status='X' so the cached record reflects reality.
    public function deactivateLocal(Request $request)
    {
        $request->validate(['user_id' => 'required']);

        $local = Users_talenta::where('user_id', $request->user_id)->first();

        if (!$local) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan di Local Cache'], 404);
        }

        if (!$this->isResigned($local->status_talenta)) {
            return response()->json(['success' => false, 'message' => 'Data ini belum berstatus Resigned'], 422);
        }

        if ($local->status === 'X') {
            return response()->json(['success' => false, 'message' => 'Data ini sudah non-aktif'], 422);
        }

        $local->update([
            'status' => 'X',
            'updated_user' => Auth::user()->username ?? 'system',
        ]);

        return response()->json(['success' => true]);
    }

    // Fields that have a matching old_* column on users_talenta (populated by das's
    // own external sync historically). When a sync overwrites one of these, whatever
    // was there right before gets archived into its old_* column first, so "Changed"
    // syncs don't just clobber data — they preserve a one-step-back history.
    private const HISTORY_FIELD_MAP = [
        'organization_id' => 'old_organization_id',
        'organization_name' => 'old_organization_name',
        'job_position_id' => 'old_job_position_id',
        'job_position' => 'old_job_position',
        'job_level' => 'old_job_level',
    ];

    // diff_status is 3-way, not just changed/unchanged:
    // - 'changed'   : a HISTORY_FIELD_MAP field differs (org/position/level) — actionable,
    //                 has an Update button, and syncing archives the old value first.
    // - 'different' : only non-tracked fields differ (email, phone, address, approval_line,
    //                 etc.) — informational only, no Update button. These fields have no
    //                 old_* column to archive into, and aren't worth a sync on their own
    //                 (e.g. an email change alone doesn't warrant overwriting the row).
    // - 'same'      : nothing differs.
    private function diffStatus(ViewUsersTalenta $view, ?Users_talenta $old): array
    {
        if (!$old) {
            return ['new', self::DIFF_FIELDS];
        }

        $coreFields = array_keys(self::HISTORY_FIELD_MAP);
        $otherFields = array_diff(self::DIFF_FIELDS, $coreFields);

        $isDifferent = fn ($field) => (string) $view->{$field} !== (string) $old->{$field};

        $coreChanged = array_values(array_filter($coreFields, $isDifferent));
        $otherChanged = array_values(array_filter($otherFields, $isDifferent));
        $allChanged = array_merge($coreChanged, $otherChanged);

        if (!empty($coreChanged)) {
            return ['changed', $allChanged];
        }

        if (!empty($otherChanged)) {
            return ['different', $allChanged];
        }

        return ['same', []];
    }

    private function syncFromView(ViewUsersTalenta $view): Users_talenta
    {
        $old = Users_talenta::where('user_id', $view->user_id)->first();

        $attributes = collect(self::COMPARABLE_FIELDS)
            ->mapWithKeys(fn ($field) => [$field => $view->{$field}])
            ->all();

        if ($old) {
            foreach (self::HISTORY_FIELD_MAP as $field => $historyField) {
                if ((string) $old->{$field} !== (string) $view->{$field}) {
                    $attributes[$historyField] = $old->{$field};
                }
            }
        }

        $attributes['updated_user'] = Auth::user()->username ?? 'system';

        return Users_talenta::updateOrCreate(
            ['user_id' => $view->user_id],
            $attributes + ['created_user' => Auth::user()->username ?? 'system']
        );
    }

    public function syncOne(Request $request)
    {
        $request->validate(['user_id' => 'required']);

        $view = ViewUsersTalenta::where('user_id', $request->user_id)->first();

        if (!$view) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan di Talenta'], 404);
        }

        $old = Users_talenta::where('user_id', $view->user_id)->first();
        [$status] = $this->diffStatus($view, $old);

        if (!in_array($status, ['new', 'changed'])) {
            return response()->json(['success' => false, 'message' => 'Data ini tidak perlu di-sync (sama, atau hanya berbeda pada informasi non-utama)'], 422);
        }

        $this->syncFromView($view);

        return response()->json(['success' => true]);
    }

    public function syncAll()
    {
        $local = Users_talenta::query()->get()->keyBy('user_id');
        $count = 0;

        DB::connection('mysql')->beginTransaction();

        try {
            ViewUsersTalenta::query()->orderBy('user_id')->chunk(200, function ($views) use ($local, &$count) {
                foreach ($views as $view) {
                    $old = $local->get($view->user_id);
                    [$status] = $this->diffStatus($view, $old);

                    if (in_array($status, ['new', 'changed'])) {
                        $this->syncFromView($view);
                        $count++;
                    }
                }
            });

            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal sync semua data', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'synced' => $count]);
    }

    // =========================================================
    // Tab 2: User (create das `users` account from a Talenta person)
    // =========================================================

    public function userCandidatesJson()
    {
        $linkedUserIds = UserDas::query()
            ->whereNotNull('user_id_talenta')
            ->pluck('user_id_talenta');

        $rows = ViewUsersTalenta::query()
            ->whereNotIn('user_id', $linkedUserIds)
            ->orderBy('first_name')
            ->get()
            ->reject(fn ($view) => $this->isResigned($view->status_talenta))
            ->map(function ($view) {
                return [
                    'user_id' => $view->user_id,
                    'name' => trim("{$view->first_name} {$view->last_name}"),
                    'employee_id' => $view->employee_id,
                    'email' => $view->email,
                    'job_position' => $view->job_position,
                    'job_level' => $view->job_level,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'username' => 'required|string|max:100|unique:mysql2.users,username',
            'password' => 'nullable|string|min:6',
            'email' => 'nullable|email|max:150',
        ]);

        $view = ViewUsersTalenta::where('user_id', $request->user_id)->first();

        if (!$view) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan di Talenta'], 404);
        }

        if (UserDas::where('user_id_talenta', $view->user_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User untuk data ini sudah pernah dibuat'], 422);
        }

        try {
            $user = UserDas::create([
                'name' => trim("{$view->first_name} {$view->last_name}"),
                'username' => trim($request->username),
                'npk' => $view->employee_id,
                'password' => Hash::make($request->password ?: 'pakuwon1234#'),
                'email' => $request->email ?: 'noemail@test.com',
                'role' => 'User',
                'user_id_talenta' => $view->user_id,
                'job_level' => $view->job_level,
                'position' => $view->job_position,
                'status' => 'A',
                'created_user' => Auth::user()->username ?? 'system',
            ]);

            return response()->json(['success' => true, 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal membuat user', 'error' => $e->getMessage()], 500);
        }
    }

    // Existing das `users` accounts whose linked Talenta person (via user_id_talenta) has
    // since resigned (checked against Talenta Live, the freshest source), and that aren't
    // already deactivated.
    public function resignedUsersJson()
    {
        $live = ViewUsersTalenta::query()
            ->get()
            ->keyBy('user_id');

        $rows = UserDas::query()
            ->whereNotNull('user_id_talenta')
            ->where('status', '!=', 'X')
            ->get()
            ->filter(fn ($user) => $live->has($user->user_id_talenta) && $this->isResigned($live->get($user->user_id_talenta)->status_talenta))
            ->map(function ($user) use ($live) {
                $view = $live->get($user->user_id_talenta);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'npk' => $user->npk,
                    'position' => $user->position,
                    'job_level' => $user->job_level,
                    'status_talenta' => $view->status_talenta,
                ];
            })
            ->values();

        return response()->json(['data' => $rows]);
    }

    public function deactivateUser(Request $request)
    {
        $request->validate(['id' => 'required']);

        $user = UserDas::find($request->id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if (!$user->user_id_talenta) {
            return response()->json(['success' => false, 'message' => 'User ini tidak terhubung ke data Talenta'], 422);
        }

        $view = ViewUsersTalenta::where('user_id', $user->user_id_talenta)->first();

        if (!$view || !$this->isResigned($view->status_talenta)) {
            return response()->json(['success' => false, 'message' => 'Data Talenta untuk user ini belum berstatus Resigned'], 422);
        }

        if ($user->status === 'X') {
            return response()->json(['success' => false, 'message' => 'User ini sudah non-aktif'], 422);
        }

        $user->update([
            'status' => 'X',
            'updated_user' => Auth::user()->username ?? 'system',
        ]);

        return response()->json(['success' => true]);
    }

    // =========================================================
    // Tab 3: Duplicates (same name + birth_date + identity_number, 2+ records)
    // =========================================================

    // Same (name, birth_date, identity_number) triple used to decide two Talenta
    // records belong to the same physical person. Shared by findDuplicateGroups()
    // (detection) and migrateDuplicate() (re-validates before writing).
    private function duplicateKey(ViewUsersTalenta $row): string
    {
        return strtolower(trim($row->first_name . ' ' . $row->last_name))
            . '|' . $row->birth_date
            . '|' . trim($row->identity_number);
    }

    // Groups a collection of Users_talenta/ViewUsersTalenta rows by
    // (first_name, last_name, birth_date, identity_number) and returns only the
    // groups with 2+ members — i.e. the same physical person appearing more than
    // once in the table. Rows with a blank name/birth_date/identity_number are
    // skipped, since a blank field trivially "matches" every other blank and would
    // otherwise produce one enormous false-positive group.
    private function findDuplicateGroups($rows): array
    {
        $groups = $rows
            ->filter(function ($row) {
                return trim((string) $row->first_name) !== ''
                    && trim((string) $row->birth_date) !== ''
                    && trim((string) $row->identity_number) !== '';
            })
            ->groupBy(function ($row) {
                return strtolower(trim($row->first_name . ' ' . $row->last_name))
                    . '|' . $row->birth_date
                    . '|' . trim($row->identity_number);
            })
            ->filter(fn ($group) => $group->count() > 1);

        $result = [];
        $groupId = 0;

        foreach ($groups as $group) {
            $groupId++;

            foreach ($group as $row) {
                $result[] = [
                    'group_id' => $groupId,
                    'group_size' => $group->count(),
                    'user_id' => $row->user_id,
                    'name' => trim("{$row->first_name} {$row->last_name}"),
                    'birth_date' => $row->birth_date,
                    'identity_number' => $row->identity_number,
                    'employee_id' => $row->employee_id,
                    'job_position' => $row->job_position,
                    'organization_name' => $row->organization_name,
                    'status_talenta' => $row->status_talenta,
                ];
            }
        }

        return $result;
    }

    public function duplicatesLocalJson()
    {
        return response()->json(['data' => $this->findDuplicateGroups(Users_talenta::query()->get())]);
    }

    public function duplicatesLiveJson()
    {
        return response()->json(['data' => $this->findDuplicateGroups(ViewUsersTalenta::query()->get())]);
    }

    // Consolidates a duplicate-person group onto one "target" NPK. Handles the
    // real-world case this exists for: someone resigns and later comes back, gets
    // a brand-new Talenta record/NPK instead of their old one being reactivated —
    // so their das login account and hr_ms_approval config are still sitting on the
    // stale, Resigned NPK and need to move to the new one.
    //
    // Talenta Live (view_users_talenta) is the source of truth for verifying the
    // group and for the target's current employee_id/position/job_level — not the
    // local cache — regardless of which duplicates table (Local or Live) the click
    // came from, since both list the same underlying Talenta person records.
    public function migrateDuplicate(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required',
            'source_user_ids' => 'required|array|min:1',
        ]);

        $target = ViewUsersTalenta::where('user_id', $request->target_user_id)->first();

        if (!$target) {
            return response()->json(['success' => false, 'message' => 'Data target tidak ditemukan di Talenta Live'], 404);
        }

        $sources = ViewUsersTalenta::whereIn('user_id', $request->source_user_ids)->get();

        if ($sources->count() !== count($request->source_user_ids)) {
            return response()->json(['success' => false, 'message' => 'Salah satu data source tidak ditemukan di Talenta Live'], 404);
        }

        $targetKey = $this->duplicateKey($target);

        foreach ($sources as $source) {
            if ($this->duplicateKey($source) !== $targetKey) {
                return response()->json([
                    'success' => false,
                    'message' => "Data NPK {$source->employee_id} bukan duplikat dari target (nama/tanggal lahir/no. identitas berbeda) — dibatalkan demi keamanan data",
                ], 422);
            }
        }

        $notes = [];
        $username = Auth::user()->username ?? 'system';

        // --- Step 1: repoint das `users` accounts (mysql2/das_voucher) linked to any source NPK ---
        foreach ($sources as $source) {
            $accounts = UserDas::where('user_id_talenta', $source->user_id)->get();

            foreach ($accounts as $account) {
                $account->update([
                    'npk' => $target->employee_id,
                    'user_id_talenta' => $target->user_id,
                    'position' => $target->job_position,
                    'job_level' => $target->job_level,
                    'updated_user' => $username,
                ]);
                $notes[] = "Akun '{$account->username}' dipindah dari NPK {$source->employee_id} ke {$target->employee_id}";
            }
        }

        // --- Step 2: deactivate the source(s) in the local cache (mysql/das) ---
        foreach ($sources as $source) {
            Users_talenta::where('user_id', $source->user_id)
                ->where('status', '!=', 'X')
                ->update(['status' => 'X', 'updated_user' => $username]);
        }

        // --- Step 3: migrate hr_ms_approval (mysql/das) ---
        DB::connection('mysql')->beginTransaction();

        try {
            foreach ($sources as $source) {
                $oldRow = DB::connection('mysql')->table('hr_ms_approval')
                    ->where('employee_id', $source->employee_id)
                    ->first();

                if ($oldRow) {
                    $newRow = DB::connection('mysql')->table('hr_ms_approval')
                        ->where('employee_id', $target->employee_id)
                        ->first();

                    // A target row counts as "not yet configured" whether it's missing
                    // entirely or exists but every approval_line is blank — both cases
                    // are safe to backfill from the old NPK's approval chain. Real data
                    // showed rows bulk-created empty for new NPKs, so "exists" alone
                    // isn't a reliable signal that it's actually configured.
                    $newRowIsBlank = !$newRow || (
                        trim((string) $newRow->approval_line1) === ''
                        && trim((string) $newRow->approval_line2) === ''
                        && trim((string) $newRow->approval_line3) === ''
                        && trim((string) $newRow->approval_line4) === ''
                    );

                    if ($newRowIsBlank) {
                        $payload = [
                            'approval_line1' => $oldRow->approval_line1,
                            'approval_line2' => $oldRow->approval_line2,
                            'approval_line3' => $oldRow->approval_line3,
                            'approval_line4' => $oldRow->approval_line4,
                            'status' => 'A',
                            'updated_user' => $username,
                            'updated_at' => now(),
                        ];

                        if ($newRow) {
                            DB::connection('mysql')->table('hr_ms_approval')
                                ->where('employee_id', $target->employee_id)
                                ->update($payload);
                            $notes[] = "hr_ms_approval NPK {$target->employee_id} sudah ada tapi kosong — diisi dari NPK lama {$source->employee_id}";
                        } else {
                            DB::connection('mysql')->table('hr_ms_approval')->insert($payload + [
                                'employee_id' => $target->employee_id,
                                'created_user' => $username,
                                'created_at' => now(),
                            ]);
                            $notes[] = "hr_ms_approval baru dibuat untuk NPK {$target->employee_id} (disalin dari {$source->employee_id})";
                        }
                    } else {
                        $notes[] = "hr_ms_approval untuk NPK {$target->employee_id} sudah ada dan terisi — tidak ditimpa";
                    }

                    DB::connection('mysql')->table('hr_ms_approval')
                        ->where('employee_id', $source->employee_id)
                        ->update(['status' => 'X', 'updated_user' => $username, 'updated_at' => now()]);
                    $notes[] = "hr_ms_approval NPK lama {$source->employee_id} di-nonaktifkan (status X)";
                }

                // Anyone using the old NPK as an approver (approval_line1-4) gets repointed
                // to the new NPK, so their approval chain doesn't silently break.
                foreach (['approval_line1', 'approval_line2', 'approval_line3', 'approval_line4'] as $col) {
                    $updated = DB::connection('mysql')->table('hr_ms_approval')
                        ->where('employee_id', '!=', $source->employee_id)
                        ->where($col, $source->employee_id)
                        ->update([$col => $target->employee_id, 'updated_user' => $username, 'updated_at' => now()]);

                    if ($updated) {
                        $notes[] = "{$updated} baris hr_ms_approval dengan {$col} = {$source->employee_id} diarahkan ulang ke {$target->employee_id}";
                    }
                }
            }

            DB::connection('mysql')->commit();
        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'UserDas & local cache sudah dipindah, tapi update hr_ms_approval gagal — cek data secara manual',
                'error' => $e->getMessage(),
                'notes' => $notes,
            ], 500);
        }

        return response()->json(['success' => true, 'notes' => $notes]);
    }
}
