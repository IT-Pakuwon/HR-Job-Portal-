<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Models\Tenancy\TsApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TsApprovalController extends Controller
{
    public function json()
    {
        $approvals = TsApproval::with('site:siteid,sitename')
            ->select(['id', 'doctype', 'siteid', 'departmentid', 'urutan', 'username', 'accesstype', 'conditiontype', 'status'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $approvals]);
    }

    /**
     * Approver candidates = the tenancy user list (mysql5.user, tenants excluded).
     * Those rows are copies of ms_user (same username), so each is flagged with whether its
     * username still exists as an active ms_user - the account the person actually logs in with.
     */
    public function approvers()
    {
        $users = DB::connection('mysql5')->table('user')
            ->where(fn ($q) => $q->whereNull('usertype')->orWhere('usertype', '<>', 'TENANT'))
            ->orderBy('name')
            ->get(['username', 'name', 'usertype', 'siteid', 'status']);

        $inMs = DB::connection('pgsql2')->table('ms_user')
            ->where('status', 'A')
            ->whereIn(DB::raw('lower(username)'), $users->pluck('username')->map(fn ($u) => mb_strtolower($u))->all())
            ->pluck('username')->map(fn ($u) => mb_strtolower($u))->flip();

        return response()->json(['data' => $users->map(fn ($u) => [
            'username'  => $u->username,
            'name'      => $u->name,
            'usertype'  => $u->usertype,
            'siteid'    => $u->siteid,
            'active'    => $u->status === 'A',
            'in_ms_user' => $inMs->has(mb_strtolower($u->username)),
        ])->values()]);
    }

    /** Every comma-separated approver must be a tenancy user; returns the ones that are not. */
    private function unknownApprovers(array $usernameFields): array
    {
        $names = collect($usernameFields)
            ->flatMap(fn ($v) => explode(',', (string) $v))
            ->map(fn ($u) => trim($u))->filter()->unique();

        $known = DB::connection('mysql5')->table('user')
            ->where(fn ($q) => $q->whereNull('usertype')->orWhere('usertype', '<>', 'TENANT'))
            ->whereIn(DB::raw('lower(username)'), $names->map(fn ($u) => mb_strtolower($u))->all())
            ->pluck('username')->map(fn ($u) => mb_strtolower($u))->flip();

        return $names->reject(fn ($u) => $known->has(mb_strtolower($u)))->values()->all();
    }

    private function rejectUnknownApprovers(array $usernameFields)
    {
        $missing = $this->unknownApprovers($usernameFields);
        if (! $missing) return null;

        return response()->json([
            'error'   => 'Approver not in tenancy user list',
            'message' => 'Not in the tenancy user list: ' . implode(', ', $missing) . '. Copy them from ms_user first (Tenancy > User > Copy from ms_user).',
        ], 422);
    }

    public function doctypes()
    {
        $doctypes = DB::connection('mysql5')->table('msdoctype')
            ->where('status', 'A')
            ->orderBy('doctype')
            ->get(['doctype', 'documentname']);

        return response()->json(['data' => $doctypes]);
    }

    public function store(Request $request)
    {
        $base = [
            'doctype'      => 'required|string|max:5|exists:mysql5.msdoctype,doctype',
            'siteid'       => 'required|string|max:5|exists:mysql5.mssite,siteid',
            'departmentid' => 'nullable|string|max:50',
            'accesstype'   => 'required|string|max:10',
        ];

        // Multi-step add: steps[] = [urutan, username, conditiontype]. Falls back to the single-row fields.
        if (is_array($request->input('steps'))) {
            $request->validate($base + [
                'steps'                 => 'required|array|min:1|max:20',
                'steps.*.urutan'        => 'required|numeric|min:0|max:99.9',
                'steps.*.username'      => 'required|string|max:255',
                'steps.*.conditiontype' => 'required|string|max:50',
            ]);
            $steps = array_values($request->input('steps'));
        } else {
            $request->validate($base + [
                'urutan'        => 'required|numeric|min:0|max:99.9',
                'username'      => 'required|string|max:255',
                'conditiontype' => 'required|string|max:50',
            ]);
            $steps = [[
                'urutan'        => $request->urutan,
                'username'      => $request->username,
                'conditiontype' => $request->conditiontype,
            ]];
        }

        if ($bad = $this->rejectUnknownApprovers(array_column($steps, 'username'))) return $bad;

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();
            $created = [];

            foreach ($steps as $step) {
                $created[] = TsApproval::create([
                    'doctype'             => $request->doctype,
                    'siteid'              => $request->siteid,
                    'departmentid'        => $request->departmentid,
                    'urutan'              => $step['urutan'],
                    'username'            => $step['username'],
                    'accesstype'          => $request->accesstype,
                    'conditiontype'       => $step['conditiontype'],
                    'status'              => 'A',
                    'created_user'        => $loginUser->username ?? 'system',
                    'created_datetime'    => now(),
                    'lastupdate_user'     => $loginUser->username ?? 'system',
                    'lastupdate_datetime' => now(),
                ]);
            }

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true, 'count' => count($created), 'approval' => $created[0] ?? null]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan approval',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

public function edit($id)
    {
        $approval = TsApproval::findOrFail($id);

        return response()->json([
            'id'            => $approval->id,
            'doctype'       => $approval->doctype,
            'siteid'        => $approval->siteid,
            'departmentid'  => $approval->departmentid,
            'urutan'        => $approval->urutan,
            'username'      => $approval->username,
            'accesstype'    => $approval->accesstype,
            'conditiontype' => $approval->conditiontype,
            'status'        => $approval->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $approval = TsApproval::findOrFail($id);

        $request->validate([
            'doctype'       => 'required|string|max:5|exists:mysql5.msdoctype,doctype',
            'siteid'        => 'required|string|max:5|exists:mysql5.mssite,siteid',
            'departmentid'  => 'nullable|string|max:50',
            'urutan'        => 'required|numeric|min:0|max:99.9',
            'username'      => 'required|string|max:255',
            'accesstype'    => 'required|string|max:10',
            'conditiontype' => 'required|string|max:50',
        ]);

        if ($bad = $this->rejectUnknownApprovers([$request->username])) return $bad;

        DB::connection('mysql5')->beginTransaction();
        try {
            $loginUser = Auth::user();

            $approval->update([
                'doctype'             => $request->doctype,
                'siteid'              => $request->siteid,
                'departmentid'        => $request->departmentid,
                'urutan'              => $request->urutan,
                'username'            => $request->username,
                'accesstype'          => $request->accesstype,
                'conditiontype'       => $request->conditiontype,
                'lastupdate_user'     => $loginUser->username ?? 'system',
                'lastupdate_datetime' => now(),
            ]);

            DB::connection('mysql5')->commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::connection('mysql5')->rollBack();
            return response()->json([
                'error'   => 'Gagal update approval',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $approval = TsApproval::findOrFail($id);
        $newStatus = request('status'); // A / X

        $approval->update([
            'status'              => $newStatus,
            'lastupdate_user'     => Auth::user()->username ?? 'system',
            'lastupdate_datetime' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
