<?php

namespace App\Http\Controllers;

use App\Models\TrApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageApprovalController extends Controller
{
    // 'AGD' is a hard block on Set Status entirely — its tr_approval rows are
    // written at creation time but aren't the authoritative approval state (the
    // real reject/approve flow only touches the separate legacy T_approval
    // table), so editing them here would be misleading. See config/approval_doctypes.php.
    protected array $hardExcluded = ['AGD'];

    // Doctypes whose header model we don't trust to safely receive an automatic
    // status='P'/completed_by/completed_at reset (e.g. unconfirmed schema, or —
    // like 'TOK' — a reject flow that already manages its own "waiting" state
    // via a different field). tr_approval itself can still be edited freely for
    // these; only the header-sync-to-Waiting step is skipped.
    protected array $headerSyncExcluded = ['TOK'];

    protected array $statusLabels = ['P' => 'Waiting', 'A' => 'Approved', 'R' => 'Rejected', 'D' => 'Revise', 'X' => 'Cancelled'];

    public function __construct(protected ApprovalController $approvalEngine)
    {
    }

    protected function doctypeMap(): array
    {
        return config('approval_doctypes', []);
    }

    public function index()
    {
        $doctypes = collect($this->doctypeMap())
            ->map(fn ($entry, $code) => ['doctype' => $code, 'doctype_descr' => $entry['label']])
            ->sortBy('doctype')
            ->values();

        $users = User::select('username', 'name')
            ->where('status', 'A')
            ->orderBy('username')
            ->get();

        // Includes inactive users too — an approver on an older document may no
        // longer be active, and the Rollback/Edit Line pickers still need to be
        // able to display/select them (e.g. to confirm what's currently set).
        $allUsers = User::select('username', 'name', 'status')
            ->orderBy('username')
            ->get();

        return view('pages.manage-approval.manage-approvals', [
            'doctypes' => $doctypes,
            'users'    => $users,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Resolve a list of usernames to a comma-joined display-name string via
     * ms_user, falling back to the username itself for any that don't match
     * (e.g. a stale/typo'd entry that predates this tool).
     */
    protected function resolveNames(array $usernames): string
    {
        if (empty($usernames)) {
            return '';
        }

        $nameMap = User::whereIn('username', $usernames)->pluck('name', 'username');

        return implode(',', array_map(
            fn ($u) => $nameMap[$u] ?? $u,
            $usernames
        ));
    }

    public function search(Request $request)
    {
        $refnbr   = trim((string) $request->query('refnbr', ''));
        $doctype  = trim((string) $request->query('doctype', ''));
        $username = trim((string) $request->query('username', ''));
        $status   = trim((string) $request->query('status', ''));

        // This is an admin diagnostic tool over a table with tens of thousands of
        // historical rows across ~26 doctypes — refuse to dump the whole table.
        if ($refnbr === '' && $doctype === '' && $username === '' && $status === '') {
            return response()->json([
                'data'    => [],
                'message' => 'Enter a Refnbr, Doctype, Username, or Status filter to search.',
            ]);
        }

        // A single refnbr+doctype can have more than one "generation" of approval
        // rows (e.g. a Revise recreates the whole chain). Ordering by level alone
        // interleaves generations together, which reads as broken/random — group
        // by refnbr, then by creation order (created_at, then id as a tiebreak for
        // same-timestamp batches) so each generation's steps stay together and
        // read top-to-bottom in the order they actually happened; level ascends
        // naturally within a generation since rows are inserted in level order.
        $query = TrApproval::query()
            ->when($refnbr !== '', fn ($q) => $q->where('refnbr', 'ilike', "%{$refnbr}%"))
            ->when($doctype !== '', fn ($q) => $q->where('aprv_doctype', $doctype))
            ->when($username !== '', fn ($q) => $q->where('aprv_username', 'ilike', "%{$username}%"))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('refnbr')
            ->orderBy('created_at')
            ->orderBy('id');

        $rows = $query->limit(500)->get();

        $map = $this->doctypeMap();
        $headerCache = [];

        $data = $rows->map(function (TrApproval $row) use ($map, &$headerCache) {
            $doctype = $row->aprv_doctype;
            $entry   = $map[$doctype] ?? null;

            $headerStatus = null;
            if ($entry) {
                $cacheKey = $doctype . '|' . $row->refnbr;
                if (!array_key_exists($cacheKey, $headerCache)) {
                    $header = $entry['model']::where($entry['refnbr_col'], $row->refnbr)->first(['status']);
                    $headerCache[$cacheKey] = $header?->status;
                }
                $headerStatus = $headerCache[$cacheKey];
            }

            return [
                'id'               => $row->id,
                'refnbr'           => $row->refnbr,
                'aprv_doctype'     => $doctype,
                'doctype_label'    => $entry['label'] ?? $doctype,
                'aprv_leveling'    => $row->aprv_leveling,
                'aprv_username'    => $row->aprv_username,
                'aprv_name'        => $row->aprv_name,
                'status'           => $row->status,
                'header_status'    => $headerStatus,
                'set_status_allowed' => !in_array($doctype, $this->hardExcluded, true),
                'updated_by'       => $row->updated_by,
                'updated_at'       => optional($row->updated_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'data'     => $data,
            'total'    => $data->count(),
            'capped'   => $data->count() >= 500,
        ]);
    }

    public function setStatus(Request $request, $id)
    {
        $preview   = $request->boolean('preview');
        $newStatus = strtoupper(trim((string) $request->input('new_status', '')));

        if (!array_key_exists($newStatus, $this->statusLabels)) {
            return response()->json(['success' => false, 'message' => 'Pick a valid status.'], 422);
        }

        $row = TrApproval::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Approval row not found.'], 404);
        }

        $doctype = $row->aprv_doctype;
        $refnbr  = $row->refnbr;

        if (in_array($doctype, $this->hardExcluded, true)) {
            return response()->json([
                'success' => false,
                'message' => "Status can't be changed for document type {$doctype} here — its approvals are managed on a separate legacy system.",
            ], 400);
        }

        if ($row->status === $newStatus) {
            return response()->json(['success' => false, 'message' => 'This step is already ' . $this->statusLabels[$newStatus] . '.'], 422);
        }

        $entry = $this->doctypeMap()[$doctype] ?? null;

        // Header is only ever auto-synced when the new status is Waiting — that's
        // the one transition we can safely infer a header state for (back to
        // in-progress). Any other target (Approved/Rejected/Revise/Cancelled) on
        // an individual step doesn't by itself tell us what the whole document's
        // header status should become (e.g. "all steps approved" needs checking
        // every other step too), so we leave the header untouched and say so.
        $syncHeader = $newStatus === 'P' && $entry && !in_array($doctype, $this->headerSyncExcluded, true);

        $header = null;
        if ($entry) {
            $header = $entry['model']::where($entry['refnbr_col'], $refnbr)->first();
            if ($syncHeader && !$header) {
                return response()->json(['success' => false, 'message' => 'Document header not found.'], 404);
            }
        }

        $leavesApproved = $row->status === 'A' && $newStatus !== 'A';

        // A single refnbr+doctype can have more than one "generation" of rows
        // (e.g. a Revise recreates the whole chain from scratch — see the note
        // in search()). Sibling cascades below must stay within the SAME
        // generation as $row, or they'd reach across into an unrelated,
        // possibly long-dead batch just because it happens to share a level
        // number or a Cancelled status. There's no explicit batch/generation id
        // column, so this pins to rows created within the same second as $row
        // — everything in one generateForDocument()/reject() call lands in a
        // single request and shares (at worst) millisecond-apart timestamps.
        $sameGeneration = fn ($q) => $q->whereRaw(
            "date_trunc('second', created_at) = date_trunc('second', ?::timestamp)",
            [$row->created_at]
        );

        // Two independent reasons a sibling step might need to change too:
        // 1) this step is leaving Approved — every later step in the same
        //    generation that was already Approved or Cancelled can't validly
        //    stay that way once an earlier step is no longer approved, so they
        //    go back to "not yet reached".
        // 2) the new status is Waiting and there are Cancelled siblings in the
        //    same generation — those were bulk-cancelled by the same
        //    reject/revise event this step was part of, so un-cancelling this
        //    step should restore them too.
        $siblingIds = collect();
        if ($leavesApproved) {
            $siblingIds = $siblingIds->merge(
                TrApproval::where('refnbr', $refnbr)
                    ->where('aprv_doctype', $doctype)
                    ->where('id', '!=', $row->id)
                    ->whereIn('status', ['A', 'X'])
                    ->whereRaw('CAST(aprv_leveling AS numeric) > CAST(? AS numeric)', [$row->aprv_leveling])
                    ->tap($sameGeneration)
                    ->pluck('id')
            );
        }
        if ($newStatus === 'P') {
            $siblingIds = $siblingIds->merge(
                TrApproval::where('refnbr', $refnbr)
                    ->where('aprv_doctype', $doctype)
                    ->where('id', '!=', $row->id)
                    ->where('status', 'X')
                    ->tap($sameGeneration)
                    ->pluck('id')
            );
        }
        $siblingIds = $siblingIds->unique()->values();

        // If a NEWER generation already exists for this refnbr+doctype (e.g. the
        // document was revised and resubmitted after this row's batch), setting
        // this row back to Waiting would create a second, competing "active"
        // chain — the approval engine only expects one. Flag it loudly rather
        // than silently letting an admin recreate a duplicate live chain.
        $hasNewerGeneration = $newStatus === 'P' && TrApproval::where('refnbr', $refnbr)
            ->where('aprv_doctype', $doctype)
            ->where('created_at', '>', $row->created_at)
            ->exists();

        if ($preview) {
            $warning = 'This directly edits live approval data and does NOT reverse any business side effects that '
                . 'happened on the previous transition (e.g. released stock, reopened PO terms, released budget/RFCA '
                . 'holds, reversed reservations, downstream integrations triggered by completion). Verify those '
                . 'manually for this document type.';

            if (!$siblingIds->isEmpty()) {
                $warning .= ' This will also reset ' . $siblingIds->count() . ' related step(s) from the same batch back to Waiting.';
            }
            if ($newStatus !== 'P') {
                $warning .= ' The document header status will NOT be changed automatically for this target status — update it manually if needed.';
            } elseif (!$entry) {
                $warning .= ' Header sync isn\'t configured for this document type — only the approval record will change.';
            } elseif (!$syncHeader) {
                $warning .= " Header sync is disabled for {$doctype} — only the approval record will change.";
            }
            if ($hasNewerGeneration) {
                $warning .= ' ⚠ A newer version of this approval chain already exists for this document (it was likely revised/resubmitted after this step). '
                    . 'Setting this older step back to Waiting will create a second, competing active chain — almost certainly not what you want. Double-check before confirming.';
            }

            return response()->json([
                'success' => true,
                'preview' => true,
                'target'  => [
                    'id'            => $row->id,
                    'aprv_leveling' => $row->aprv_leveling,
                    'aprv_username' => $row->aprv_username,
                    'aprv_name'     => $row->aprv_name,
                    'status'        => $row->status,
                ],
                'siblings_affected'    => $siblingIds,
                'header_status'        => $header?->status,
                'header_will_change'   => $syncHeader,
                'newer_generation_conflict' => $hasNewerGeneration,
                'warning' => $warning,
            ]);
        }

        $newUsername = null;
        $newName     = null;
        if ($request->filled('aprv_username')) {
            $usernames = array_values(array_filter(array_map('trim', explode(',', $request->input('aprv_username')))));

            if (empty($usernames)) {
                return response()->json(['success' => false, 'message' => 'Pick at least one approver.'], 422);
            }

            $knownCount = User::whereIn('username', $usernames)->count();
            if ($knownCount !== count($usernames)) {
                return response()->json(['success' => false, 'message' => 'One or more selected usernames were not found in ms_user.'], 422);
            }

            $newUsername = implode(',', $usernames);
            $newName     = $this->resolveNames($usernames);
        }

        $actor = Auth::user()?->username ?? 'system';
        $now   = now();

        $originalStatus        = $row->status;
        $originalAprvDateAfter = $row->aprv_dateafter;
        // Snapshot siblings fully (not just IDs) — clearing their timestamps
        // needs a precise value to restore if the header write fails after this
        // commits, not just a guessed status.
        $siblingSnapshots = TrApproval::whereIn('id', $siblingIds)->get()->map(fn ($s) => [
            'id'              => $s->id,
            'status'          => $s->status,
            'aprv_dateafter'  => $s->aprv_dateafter,
            'aprv_datebefore' => $s->aprv_datebefore,
        ])->all();

        try {
            DB::connection('pgsql2')->transaction(function () use ($row, $newStatus, $newUsername, $newName, $actor, $now, $siblingIds) {
                $row->status = $newStatus;
                if ($newStatus === 'P') {
                    $row->aprv_dateafter = null;
                    if (empty($row->aprv_datebefore)) {
                        $row->aprv_datebefore = $now;
                    }
                } else {
                    $row->aprv_dateafter = $now;
                }
                if ($newUsername !== null) {
                    $row->aprv_username = $newUsername;
                }
                if ($newName !== null) {
                    $row->aprv_name = $newName;
                }
                $row->updated_by = $actor;
                $row->save();

                if (!$siblingIds->isEmpty()) {
                    // Always resets these to Waiting/queued regardless of which
                    // of the two reasons above pulled them in — both cases start
                    // from a state where the sibling was never actually reached
                    // (X) or is no longer validly ahead of this step (A/X).
                    TrApproval::whereIn('id', $siblingIds)->update([
                        'status'          => 'P',
                        'aprv_dateafter'  => null,
                        'aprv_datebefore' => null,
                        'updated_by'      => $actor,
                        'updated_at'      => $now,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Failed to update approval record.'], 500);
        }

        if ($syncHeader) {
            try {
                DB::connection($header->getConnectionName())->transaction(function () use ($header) {
                    $header->status       = 'P';
                    $header->completed_by = null;
                    $header->completed_at = null;
                    $header->save();
                });
            } catch (\Throwable $e) {
                report($e);

                // Best-effort compensation: no distributed transaction exists
                // across pgsql2 (tr_approval) and the header's own connection, so
                // if the header write fails after tr_approval already committed,
                // we must manually reverse the tr_approval write rather than
                // leave the two databases silently diverged.
                try {
                    DB::connection('pgsql2')->transaction(function () use ($row, $originalStatus, $originalAprvDateAfter, $siblingSnapshots) {
                        $row->status         = $originalStatus;
                        $row->aprv_dateafter = $originalAprvDateAfter;
                        $row->save();

                        foreach ($siblingSnapshots as $snap) {
                            TrApproval::where('id', $snap['id'])->update([
                                'status'          => $snap['status'],
                                'aprv_dateafter'  => $snap['aprv_dateafter'],
                                'aprv_datebefore' => $snap['aprv_datebefore'],
                            ]);
                        }
                    });
                } catch (\Throwable $compensationError) {
                    report($compensationError);
                    Log::error('Manage Approval setStatus: compensation failed, data may be inconsistent', [
                        'refnbr'         => $refnbr,
                        'doctype'        => $doctype,
                        'tr_approval_id' => $row->id,
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Header status update failed — approval record changes were reverted. Please try again or contact IT.',
                ], 500);
            }
        }

        return response()->json(['success' => true, 'refnbr' => $refnbr, 'doctype' => $doctype]);
    }

    public function updateLine(Request $request, $id)
    {
        $row = TrApproval::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Approval row not found.'], 404);
        }

        $request->validate([
            'aprv_username'   => 'required|string|max:500',
            'aprv_username.*' => 'string',
        ]);

        $usernames = array_values(array_filter(array_map('trim', explode(',', $request->input('aprv_username')))));
        if (empty($usernames)) {
            return response()->json(['success' => false, 'message' => 'Pick at least one approver.'], 422);
        }

        $knownCount = User::whereIn('username', $usernames)->count();
        if ($knownCount !== count($usernames)) {
            return response()->json(['success' => false, 'message' => 'One or more selected usernames were not found in ms_user.'], 422);
        }

        $row->aprv_username = implode(',', $usernames);
        $row->aprv_name      = $this->resolveNames($usernames);
        $row->updated_by     = Auth::user()?->username ?? 'system';
        $row->save();

        return response()->json(['success' => true]);
    }

    public function transferPreview(Request $request)
    {
        $request->validate([
            'from_username'   => 'required|string',
            'to_usernames'    => 'required|array|min:1',
            'to_usernames.*'  => 'required|string',
        ]);

        $from        = strtolower(trim($request->input('from_username')));
        $toUsernames = array_values(array_unique(array_map('trim', $request->input('to_usernames'))));
        $doctype     = trim((string) $request->input('doctype', ''));
        $refnbr      = trim((string) $request->input('refnbr', ''));

        if (in_array($from, array_map('strtolower', $toUsernames), true)) {
            return response()->json(['success' => false, 'message' => 'To User can\'t include the From User.'], 422);
        }

        $rows = TrApproval::query()
            ->where('status', 'P')
            ->where('aprv_username', 'ilike', "%{$from}%")
            ->when($doctype !== '', fn ($q) => $q->where('aprv_doctype', $doctype))
            ->when($refnbr !== '', fn ($q) => $q->where('refnbr', $refnbr))
            ->get()
            ->filter(fn (TrApproval $row) => in_array($from, $this->approvalEngine->normalizeApproverList($row->aprv_username), true))
            ->values();

        $toUsers  = User::whereIn('username', $toUsernames)->get()->keyBy('username');
        $missing  = array_values(array_diff($toUsernames, $toUsers->keys()->all()));
        $inactive = $toUsers->filter(fn ($u) => $u->status !== 'A')->pluck('username')->values()->all();

        $warnings = [];
        if (!empty($missing)) {
            $warnings[] = 'Not found in ms_user: ' . implode(', ', $missing);
        }
        if (!empty($inactive)) {
            $warnings[] = 'Not currently active: ' . implode(', ', $inactive);
        }

        return response()->json([
            'success' => true,
            'rows'    => $rows->map(fn ($row) => [
                'id'                 => $row->id,
                'refnbr'             => $row->refnbr,
                'aprv_doctype'       => $row->aprv_doctype,
                'aprv_leveling'      => $row->aprv_leveling,
                'aprv_username'      => $row->aprv_username,
                'aprv_name'          => $row->aprv_name,
                'status'             => $row->status,
                'aprv_cpnyid'        => $row->aprv_cpnyid,
                'aprv_departementid' => $row->aprv_departementid,
                'created_at'         => optional($row->created_at)->toDateTimeString(),
            ]),
            'to_users' => $toUsers->map(fn ($u) => [
                'username' => $u->username,
                'name'     => $u->name,
                'active'   => $u->status === 'A',
            ])->values(),
            'warning' => empty($warnings) ? null : implode(' ', $warnings),
        ]);
    }

    public function transferConfirm(Request $request)
    {
        $request->validate([
            'from_username'   => 'required|string',
            'to_usernames'    => 'required|array|min:1',
            'to_usernames.*'  => 'required|string',
            'row_ids'         => 'required|array|min:1',
            'row_ids.*'       => 'integer',
        ]);

        $from        = strtolower(trim($request->input('from_username')));
        $toUsernames = array_values(array_unique(array_map('trim', $request->input('to_usernames'))));
        $toNameMap   = User::whereIn('username', $toUsernames)->pluck('name', 'username');
        $toNames     = array_map(fn ($u) => $toNameMap[$u] ?? $u, $toUsernames);
        $actor       = Auth::user()?->username ?? 'system';
        $now         = now();

        $changed = 0;
        $skipped = [];

        DB::connection('pgsql2')->transaction(function () use ($request, $from, $toUsernames, $toNames, $actor, $now, &$changed, &$skipped) {
            $rows = TrApproval::whereIn('id', $request->input('row_ids'))
                ->where('status', 'P')
                ->get();

            foreach ($rows as $row) {
                [$delimiter, $usernameTokens] = $this->splitPreserve($row->aprv_username);

                $matchIndex = null;
                foreach ($usernameTokens as $i => $token) {
                    if (strtolower($token) === $from) {
                        $matchIndex = $i;
                        break;
                    }
                }

                if ($matchIndex === null) {
                    $skipped[] = $row->id;
                    continue;
                }

                $originalUsernameCount = count($usernameTokens);
                // Expand the matched token into every selected "to" user (e.g.
                // splitting one resigned approver's workload across two people).
                array_splice($usernameTokens, $matchIndex, 1, $toUsernames);
                $row->aprv_username = implode($delimiter, $usernameTokens);

                [$nameDelimiter, $nameTokens] = $this->splitPreserve($row->aprv_name);
                if (count($nameTokens) === $originalUsernameCount) {
                    array_splice($nameTokens, $matchIndex, 1, $toNames);
                    $row->aprv_name = implode($nameDelimiter, $nameTokens);
                }

                $row->updated_by = $actor;
                $row->updated_at = $now;
                $row->save();
                $changed++;
            }
        });

        return response()->json([
            'success'      => true,
            'rows_changed' => $changed,
            'skipped'      => $skipped,
        ]);
    }

    protected function splitPreserve(?string $raw): array
    {
        $raw = (string) $raw;
        if ($raw === '') {
            return [',', []];
        }

        $delimiter = str_contains($raw, ';') ? ';' : ',';
        $tokens    = array_map('trim', explode($delimiter, $raw));

        return [$delimiter, $tokens];
    }
}
