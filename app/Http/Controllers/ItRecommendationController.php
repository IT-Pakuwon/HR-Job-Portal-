<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsInventory;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrItrecommend;
use App\Models\TrItrecommendDetail;
use App\Models\TrMessage;
use App\Models\TrTicket;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class ItRecommendationController extends Controller
{
    use HasAutonbr;

    protected string $doctype = 'ITR';

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $q = TrItrecommend::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all = (clone $q)->count();
        $waitingIT = (clone $q)->whereIn('status', ['W', 'I'])->count();
        $waitingApproval = (clone $q)->where('status', 'P')->count();
        $reject = (clone $q)->where('status', 'R')->count();
        $revise = (clone $q)->where('status', 'D')->count();
        $completed = (clone $q)->where('status', 'C')->count();

        $usercpny = Usercpny::where('username', $user->username)->get();
        $userdept = Userdept::where('username', $user->username)->get();

        $tickets = TrTicket::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds)
            ->whereIn('status', ['W', 'P']) // optional: only active tickets
            ->orderByDesc('ticketdate')
            ->limit(50)
            ->get(['ticketid', 'issue_summary']);

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        return view('pages.it_recommendation.it_recommendation', compact(
            'all',
            'waitingIT',
            'waitingApproval',
            'reject',
            'revise',
            'completed',
            'usercpny',
            'userdept',
            'tickets',
            'isITHardware'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');

        $columns = [
            0 => 'docid',
            1 => 'itrecommend_date',
            2 => 'ticketnbr',
            3 => 'cpny_id',
            4 => 'department_id',
            5 => 'user_peminta',
            6 => 'keperluan',
            7 => 'recommend_pic',
            8 => 'status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);

        $orderDir = $request->input('order.0.dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $orderCol = $columns[$orderIdx] ?? 'docid';

        $base = TrItrecommend::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        if ($status !== '') {
            $base->where('status', $status);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('docid', 'ilike', "%{$search}%")
                    ->orWhere('ticketnbr', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('department_id', 'ilike', "%{$search}%")
                    ->orWhere('keperluan', 'ilike', "%{$search}%")
                    ->orWhere('recommend_pic', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base
            ->orderBy($orderCol, $orderDir)
            ->orderBy('docid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        $data->transform(function ($row) use ($isITHardware, $user) {
            $row->eid = Hashids::encode($row->id);

            $row->can_process = (
                $isITHardware
                && in_array($row->status, ['W', 'I'])
            );

            $row->process_label = match ($row->status) {
                'W' => 'Process',
                'I' => 'Edit Recommendation',
                default => 'Process',
            };

            $row->can_edit = (
                $row->created_by === $user->username
                && in_array($row->status, ['D'])
            );

            $row->can_cancel = (
                $row->created_by === $user->username
                && in_array($row->status, ['D'])
            );

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function ticketSearch(Request $request)
    {
        $q = trim($request->q ?? '');

        $user = auth()->user();

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $data = TrTicket::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('ticketid', 'ilike', "%{$q}%")
                        ->orWhere('issue_summary', 'ilike', "%{$q}%");
                });
            })
            ->limit(20)
            ->get(['ticketid', 'issue_summary']);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpny_id' => 'required|string',
            'department_id' => 'required|string',
            'ticketnbr' => 'required|string',
            'keperluan' => 'required|string|min:5',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();

        DB::connection('pgsql5')->beginTransaction();

        try {
            $dt = now();

            $auto = $this->nextAutonbr(
                $this->doctype,
                (int) $dt->year,
                str_pad($dt->month, 2, '0', STR_PAD_LEFT),
                $user->username,
                'IT Recommendation'
            );

            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $dt->year, 2)
                .str_pad($dt->month, 2, '0', STR_PAD_LEFT);

            $docid = $this->doctype.$tglbln.sprintf('%04d', $urutan);

            $header = new TrItrecommend();

            $header->docid = $docid;
            $header->itrecommend_date = now()->toDateString();
            $header->cpny_id = $request->cpny_id;
            $header->department_id = $request->department_id;
            $header->location_id = $request->location_id;
            $header->user_peminta = $user->username;
            $header->keperluan = $request->keperluan;
            $header->assetnbr = $request->assetnbr;
            $header->ticketnbr = $request->ticketnbr;

            // W = Waiting IT Review
            $header->status = 'W';

            $header->created_by = $user->username;

            $header->save();

            $eid = Hashids::encode($header->id);

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $this->doctype,
                    'cpnyid' => $request->cpny_id,
                    'departementid' => $request->department_id,
                    'base_folder' => 'att-it-recommendation',
                    'created_by' => $user->username,
                ];

                app(TrAttachmentController::class)
                    ->uploadInternal(
                        $meta,
                        (array) $request->file('attachments')
                    );
            }

            DB::connection('pgsql5')->commit();
            $this->notifyITHardware(
                $header->docid,
                'Waiting IT Review',
                url('/processitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Created successfully',
                'docid' => $docid,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $hash)
    {
        $request->validate([
            'cpny_id' => 'required|string',
            'department_id' => 'required|string',
            'ticketnbr' => 'required|string',
            'keperluan' => 'required|string|min:5',
        ]);

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        if (
            $header->created_by !== auth()->user()->username
            || !in_array($header->status, ['D'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Document cannot be edited',
            ], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $header->cpny_id = $request->cpny_id;
            $header->department_id = $request->department_id;
            $header->ticketnbr = $request->ticketnbr;
            $header->assetnbr = $request->assetnbr;
            $header->keperluan = $request->keperluan;

            $header->status = 'W';

            $header->updated_by = auth()->user()->username;

            $header->recommend_type = null;
            $header->waranty = null;
            $header->recommendation = null;
            $header->recommend_pic = null;

            $header->save();

            TrApproval::where('refnbr', $header->docid)->delete();

            TrItrecommendDetail::where('docid', $header->docid)->delete();

            DB::connection('pgsql5')->commit();

            $eid = Hashids::encode($header->id);

            $this->notifyITHardware(
                $header->docid,
                'Waiting IT Review',
                url('/processitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function process(Request $request, $hash)
    {
        $user = auth()->user();

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        if (!$isITHardware) {
            return response()->json([
                'success' => false,
                'message' => 'Only IT Hardware can process',
            ], 403);
        }

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        if (!in_array($header->status, ['W', 'I'])) {
            return response()->json([
                'success' => false,
                'message' => 'Document cannot be processed',
            ], 422);
        }

        $request->validate([
            'recommend_type' => 'required|string',
            'recommendation' => 'required|string|min:5',
            'waranty' => 'nullable|string',
            'details' => 'required|array|min:1',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            // reset approval
            TrApproval::where('refnbr', $header->docid)->delete();

            // reset detail
            TrItrecommendDetail::where('docid', $header->docid)->delete();

            // foreach ($request->details as $row) {
            //     $inventory = MsInventory::query()
            //         ->where('inventoryid', $row['inventoryid'])
            //         ->where('item_type', 'NS')
            //         ->where('status', 'A')
            //         ->first();

            //     if (!$inventory) {
            //         continue;
            //     }

            //     TrItrecommendDetail::create([
            //         'docid' => $header->docid,
            //         'recommend_descr' => $inventory->inventory_descr,
            //         'qty' => $row['qty'] ?? 1,
            //         'uom' => $inventory->purchase_unit,
            //         'category' => $inventory->item_category,
            //         'subcategory' => $inventory->item_sub_class,
            //         'recommend_note' => $row['recommend_note'] ?? null,
            //         'status' => 'A',
            //         'created_by' => $user->username,
            //     ]);
            // }

            foreach ($request->details as $row) {
                if (empty($row['recommend_descr'])) {
                    continue;
                }

                TrItrecommendDetail::create([
                    'docid' => $header->docid,
                    'recommend_descr' => $row['recommend_descr'],
                    'qty' => $row['qty'] ?? 1,
                    'uom' => $row['uom'] ?? null,
                    'category' => $row['category'] ?? null,
                    'subcategory' => $row['subcategory'] ?? null,
                    'recommend_note' => $row['recommend_note'] ?? null,
                    'status' => 'A',
                    'created_by' => $user->username,
                ]);
            }

            if (
                !TrItrecommendDetail::where('docid', $header->docid)->exists()
            ) {
                throw new \Exception('No valid inventory selected');
            }

            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                $this->doctype,
                $header->cpny_id,
                $header->department_id
            );

            [$firstApprovalUsernames] =
                $approvalCtl->generateForDocument(
                    $header->docid,
                    $this->doctype,
                    $header->cpny_id,
                    $header->department_id,
                    $user->username,
                    ['ignore_nominal' => true],
                    now()
                );

            $header->recommend_type = $request->recommend_type;
            $header->waranty = $request->waranty;
            $header->recommendation = $request->recommendation;
            $header->recommend_pic = $user->username;

            // waiting approval
            $header->status = 'P';

            $header->updated_by = $user->username;
            $header->save();

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $header->docid,
                $this->doctype,
                'P',
                'IT Recommendation',
                url('/showitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Processed successfully',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function notifyITHardware(
        string $docid,
        string $status,
        string $url,
        array $extra = []
    ) {
        $users = User::query()
            ->where('status', 'A')
            ->whereIn(
                'username',
                SysUserRole::query()
                    ->where('role_id', 'ITHARDWARE')
                    ->distinct()
                    ->pluck('username')
            )
            ->get();

        foreach ($users as $user) {
            $to = $user->notification_email ?? $user->email;

            if (!$to) {
                continue;
            }

            try {
                Mail::send(
                    'emails.mailapprovenew',
                    [
                        'docid' => $docid,
                        'docname' => 'IT Recommendation',
                        'status' => $status,
                        'url' => $url,
                        'info' => $extra['info'] ?? '',
                        'createdby' => $extra['createdby'] ?? '',
                        'date' => $extra['date'] ?? now(),
                    ],
                    function ($message) use ($to, $docid, $status) {
                        $message->to($to)
                            ->subject(
                                "{$docid} - {$status} IT Recommendation"
                            )
                            ->from(
                                'digitalserver@pakuwon.com',
                                'Pakuwon System'
                            );
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Failed send IT email', [
                    'error' => $e->getMessage(),
                    'docid' => $docid,
                ]);
            }
        }
    }

    private function notifyRequester(
        string $username,
        string $docid,
        string $status,
        string $url,
        array $extra = []
    ) {
        $user = User::where('username', $username)
            ->where('status', 'A')
            ->first();

        if (!$user) {
            return;
        }

        $to = $user->notification_email ?? $user->email;

        if (!$to) {
            return;
        }

        try {
            Mail::send(
                'emails.mailapprovenew',
                [
                    'docid' => $docid,
                    'docname' => 'IT Recommendation',
                    'status' => $status,
                    'url' => $url,
                    'info' => $extra['info'] ?? '',
                    'createdby' => $extra['createdby'] ?? '',
                    'date' => $extra['date'] ?? now(),
                ],
                function ($message) use ($to, $docid, $status) {
                    $message->to($to)
                        ->subject(
                            "{$docid} - {$status} IT Recommendation"
                        )
                        ->from(
                            'digitalserver@pakuwon.com',
                            'Pakuwon System'
                        );
                }
            );
        } catch (\Throwable $e) {
            Log::error('Failed send requester email', [
                'error' => $e->getMessage(),
                'docid' => $docid,
            ]);
        }
    }

    public function itRevise(Request $request, $hash)
    {
        $request->validate([
            'note' => 'required|string|min:3',
        ]);

        $user = auth()->user();

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        if (!$isITHardware) {
            return response()->json([
                'success' => false,
                'message' => 'Only IT Hardware can revise',
            ], 403);
        }

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        if (!in_array($header->status, ['W', 'I'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document status',
            ], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $header->status = 'D';
            $header->updated_by = $user->username;

            $header->save();
            $eid = Hashids::encode($header->id);

            try {
                $request->merge([
                    'reason' => $request->note,
                    'status' => 'I',
                ]);
                app('App\Http\Controllers\SendCommentController')
                    ->sendmsg(
                        $header->id,
                        $this->doctype,
                        $request
                    );
            } catch (\Throwable $e) {
                Log::warning('Failed save IT revise comment', [
                    'docid' => $header->docid,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::connection('pgsql5')->commit();

            $this->notifyRequester(
                $header->created_by,
                $header->docid,
                'Revise Request',
                url('/edititrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Document revised',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function itReject(Request $request, $hash)
    {
        $request->validate([
            'note' => 'required|string|min:3',
        ]);

        $user = auth()->user();

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        if (!$isITHardware) {
            return response()->json([
                'success' => false,
                'message' => 'Only IT Hardware can reject',
            ], 403);
        }

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        // $header = TrItrecommend::where('docid', $docid)->firstOrFail();

        if (!in_array($header->status, ['W', 'I'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document status',
            ], 422);
        }
        DB::connection('pgsql5')->beginTransaction();

        try {
            TrApproval::where('refnbr', $header->docid)
                ->update([
                    'status' => 'X',
                ]);

            $header->status = 'R';
            $header->updated_by = $user->username;

            $header->save();

            $eid = Hashids::encode($header->id);

            try {
                $request->merge([
                    'reason' => $request->note,
                    'status' => 'R',
                ]);
                app('App\Http\Controllers\SendCommentController')
                    ->sendmsg(
                        $header->id,
                        $this->doctype,
                        $request
                    );
            } catch (\Throwable $e) {
                Log::warning('Failed save IT reject comment', [
                    'docid' => $header->docid,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::connection('pgsql5')->commit();

            $this->notifyRequester(
                $header->created_by,
                $header->docid,
                'Rejected',
                url('/showitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Document rejected',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function approve(Request $request, $docid)
    {
        $header = TrItrecommend::where('docid', $docid)->firstOrFail();

        if ($header->status !== 'P') {
            return response()->json([
                'success' => false,
                'message' => 'Document is not waiting approval',
            ], 422);
        }
        DB::connection('pgsql5')->beginTransaction();

        try {
            $approvalCtl = app(ApprovalController::class);

            $result = $approvalCtl->approveStep(
                $docid,
                $this->doctype,
                auth()->user()->username,
                auth()->user()->name,

                function ($refnbr, $now) use ($docid) {
                    $header = TrItrecommend::where('docid', $docid)
                        ->firstOrFail();

                    $header->status = 'C';
                    $header->completed_by = auth()->user()->username;
                    $header->completed_at = $now;
                    $header->updated_by = auth()->user()->username;

                    $header->save();
                },

                function ($next, $now) use ($docid) {
                    $header = TrItrecommend::where('docid', $docid)
                        ->firstOrFail();

                    $header->completed_by = auth()->user()->username;
                    $header->completed_at = $now;
                    $header->updated_by = auth()->user()->username;

                    $header->save();
                }
            );

            if (!$result['ok']) {
                throw new \Exception($result['message'] ?? 'Approval failed');
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Document approved',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, $docid)
    {
        $request->validate([
            'note' => 'required|string|min:3',
        ]);

        $header = TrItrecommend::where('docid', $docid)->firstOrFail();

        if ($header->status !== 'P') {
            return response()->json([
                'success' => false,
                'message' => 'Document is not waiting approval',
            ], 422);
        }

        $eid = Hashids::encode($header->id);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $header = TrItrecommend::where('docid', $docid)
                ->firstOrFail();

            $approvalCtl = app(ApprovalController::class);

            $result = $approvalCtl->rejectStep(
                $docid,
                $this->doctype,
                auth()->user()->username,
                auth()->user()->name,

                function ($refnbr, $now) use ($header, $request) {
                    $header->status = 'R';
                    $header->updated_by = auth()->user()->username;
                    $header->completed_by = auth()->user()->username;
                    $header->completed_at = $now;

                    $header->save();

                    TrApproval::where('refnbr', $header->docid)
                        ->where('aprv_username', auth()->user()->username)
                        ->where('status', 'R')
                        ->latest('id')
                        ->first()
                        ?->update([
                            'aprv_purpose' => $request->note,
                        ]);

                    TrApproval::where('refnbr', $header->docid)
                        ->where('status', 'P')
                        ->update([
                            'status' => 'X',
                        ]);
                }
            );

            if (!$result['ok']) {
                throw new \Exception($result['message'] ?? 'Reject failed');
            }

            DB::connection('pgsql5')->commit();

            $this->notifyRequester(
                $header->created_by,
                $header->docid,
                'Rejected',
                url('/showitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            $this->notifyITHardware(
                $header->docid,
                'Rejected',
                url('/showitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Document rejected',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function revise(Request $request, $docid)
    {
        $request->validate([
            'note' => 'required|string|min:3',
        ]);

        $header = TrItrecommend::where('docid', $docid)
            ->firstOrFail();

        if ($header->status !== 'P') {
            return response()->json([
                'success' => false,
                'message' => 'Document is not waiting approval',
            ], 422);
        }

        $eid = Hashids::encode($header->id);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $approvalCtl = app(ApprovalController::class);

            $result = $approvalCtl->reviseStep(
                $docid,
                $this->doctype,
                auth()->user()->username,
                auth()->user()->name,

                function ($refnbr, $now) use ($header, $request) {
                    TrApproval::where('refnbr', $header->docid)
                        ->where('aprv_username', auth()->user()->username)
                        ->where('status', 'D')
                        ->latest('id')
                        ->first()
                        ?->update([
                            'aprv_purpose' => $request->note,
                        ]);
                }
            );

            if (!$result['ok']) {
                throw new \Exception($result['message'] ?? 'Revise failed');
            }

            $header->status = 'I';
            $header->updated_by = auth()->user()->username;
            $header->completed_by = null;
            $header->completed_at = null;

            $header->save();

            DB::connection('pgsql5')->commit();

            $this->notifyRequester(
                $header->created_by,
                $header->docid,
                'Waiting IT Revision',
                url('/showitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            $this->notifyITHardware(
                $header->docid,
                'Waiting IT Revision',
                url('/processitrecommendation/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => now()->toDateTimeString(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Document revised',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function inventorySearch(Request $request)
    {
        $q = trim($request->q ?? '');

        $data = MsInventory::query()
            ->where('item_type', 'NS')
            ->where('status', 'A')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('inventoryid', 'ilike', "%{$q}%")
                        ->orWhere('inventory_descr', 'ilike', "%{$q}%");
                });
            })
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    public function cancel($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        if (
            $header->created_by !== auth()->user()->username
            || !in_array($header->status, ['D'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Document cannot be cancelled',
            ], 422);
        }

        $header->status = 'X';
        $header->updated_by = auth()->user()->username;

        $header->save();

        return response()->json([
            'success' => true,
            'message' => 'Document cancelled',
        ]);
    }

    public function detail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | ACCESS VALIDATION
        |--------------------------------------------------------------------------
        */

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        $canAccess = (
            (
                in_array($header->cpny_id, $cpnyIds)
                && in_array($header->department_id, $deptIds)
            )
            || $header->created_by === $user->username
            || $isITHardware
            || TrApproval::where('refnbr', $header->docid)
                ->where('aprv_username', $user->username)
                ->exists()
        );

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $details = TrItrecommendDetail::where('docid', $header->docid)
            ->get();

        $approvals = TrApproval::where('refnbr', $header->docid)
            ->orderBy('id')
            ->get();

        $attachments = TrAttachment::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->orderByDesc('id')
            ->get();

        $attachments->transform(function ($row) {
            try {
                $filepath = trim($row->folder, '/').'/'.$row->filename;

                $row->signed_url = app(TrAttachmentController::class)
                    ->getSignedUrl($filepath);
            } catch (\Throwable $e) {
                Log::error('Attachment signed url failed', [
                    'docid' => $row->refnbr,
                    'error' => $e->getMessage(),
                ]);

                $row->signed_url = null;
            }

            return $row;
        });

        $canApprove = TrApproval::query()
            ->where('refnbr', $header->docid)
            ->where('aprv_username', $user->username)
            ->where('status', 'P')
            ->exists();

        $messages = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->whereIn('status', ['I', 'R'])
            ->get();

        $approvalNotes = TrApproval::query()
            ->where('refnbr', $header->docid)
            ->whereIn('status', ['D', 'R'])
            ->get();

        $notes = collect();

        foreach ($messages as $m) {
            $notes->push([
                'type' => $m->status === 'I' ? 'IT Revise' : 'IT Reject',
                'user' => $m->username,
                'note' => $m->message,
                'date' => $m->message_date,
            ]);
        }

        foreach ($approvalNotes as $a) {
            $notes->push([
                'type' => $a->status === 'D' ? 'Approval Revise' : 'Approval Reject',
                'user' => $a->aprv_username,
                'note' => $a->aprv_purpose,
                'date' => $a->updated_at,
            ]);
        }

        $notes = $notes->sortByDesc('date')->values();

        return response()->json([
            'header' => $header,
            'details' => $details,
            'approvals' => $approvals,
            'attachments' => $attachments,

            'permissions' => [
                'can_edit' => (
                    $header->created_by === $user->username
                    && in_array($header->status, ['D'])
                ),

                'can_cancel' => (
                    $header->created_by === $user->username
                    && in_array($header->status, ['D'])
                ),

                'can_process' => (
                    $isITHardware
                    && in_array($header->status, ['W', 'I'])
                ),

                'can_approve' => $canApprove,

                'notes' => $notes,
            ],
        ]);
    }

    public function tracking($docid)
    {
        $header = TrItrecommend::where('docid', $docid)
            ->firstOrFail();

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | ACCESS VALIDATION
        |--------------------------------------------------------------------------
        */

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        $canAccess = (
            (
                in_array($header->cpny_id, $cpnyIds)
                && in_array($header->department_id, $deptIds)
            )
            || $header->created_by === $user->username
            || $isITHardware
            || TrApproval::where('refnbr', $header->docid)
                ->where('aprv_username', $user->username)
                ->exists()
        );

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $timeline = [];

        /*
        |--------------------------------------------------------------------------
        | HELPER
        |--------------------------------------------------------------------------
        */

        $push = function (
            $title,
            $description,
            $date,
            $status,
            $label,
            $note = null,
            $sort = 0
        ) use (&$timeline) {
            if (!$date) {
                return;
            }

            $timeline[] = [
                'title' => $title,

                'description' => $description ?? '-',

                'date' => optional($date)
                    ->format('d M Y H:i'),

                'raw_date' => $date,

                'status' => $status,

                'label' => $label,

                'note' => $note,

                'sort_order' => $sort,
            ];
        };

        /*
        |--------------------------------------------------------------------------
        | SUBMITTED
        |--------------------------------------------------------------------------
        */

        $push(
            'Submitted',
            $header->created_by,
            $header->created_at,
            'S',
            'Submitted',
            null,
            1
        );

        /*
        |--------------------------------------------------------------------------
        | IT REVISION FLOW
        |--------------------------------------------------------------------------
        */

        $itRevisions = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->where('status', 'I')
            ->orderBy('message_date')
            ->get();

        foreach ($itRevisions as $msg) {
            $push(
                'IT Revision Requested',
                $msg->username,
                $msg->message_date,
                'I',
                'Waiting IT Revision',
                $msg->message,
                2
            );

            $push(
                'Resubmitted',
                $header->created_by,
                optional($msg->message_date)?->copy()->addSecond(),
                'RS',
                'Resubmitted',
                null,
                3
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IT PROCESS
        |--------------------------------------------------------------------------
        */

        if ($header->recommend_pic) {
            $processDate = TrApproval::query()
                ->where('refnbr', $header->docid)
                ->where('status', '!=', 'X')
                ->min('created_at');

            $processDate = $processDate
                ? \Carbon\Carbon::parse($processDate)
                : $header->updated_at;

            $push(
                'IT Review',
                $header->recommend_pic,
                $processDate,
                'IT',
                'Processed',
                null,
                4
            );
        }

        /*
        |--------------------------------------------------------------------------
        | APPROVAL FLOW
        |--------------------------------------------------------------------------
        */

        $approvals = TrApproval::query()
            ->where('refnbr', $header->docid)
            ->where('status', '!=', 'X')
            ->orderBy('id')
            ->get();

        foreach ($approvals as $row) {
            /*
            |--------------------------------------------------------------------------
            | WAITING APPROVAL
            |--------------------------------------------------------------------------
            */

            if ($row->status === 'P') {
                $push(
                    'Waiting Approval',
                    $row->aprv_username,
                    $row->created_at,
                    'P',
                    'Waiting Approval',
                    null,
                    5
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | APPROVED
            |--------------------------------------------------------------------------
            */

            if ($row->status === 'A') {
                $push(
                    'Approved',
                    $row->aprv_username,
                    $row->updated_at,
                    'A',
                    'Approved',
                    $row->aprv_purpose,
                    6
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | REVISION REQUESTED
            |--------------------------------------------------------------------------
            */

            if ($row->status === 'D') {
                $push(
                    'Revision Requested',
                    $row->aprv_username,
                    $row->updated_at,
                    'D',
                    'Revision Requested',
                    $row->aprv_purpose,
                    6
                );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | REJECTED
            |--------------------------------------------------------------------------
            */

            if ($row->status === 'R') {
                $push(
                    'Rejected',
                    $row->aprv_username,
                    $row->updated_at,
                    'R',
                    'Rejected',
                    $row->aprv_purpose,
                    6
                );

                continue;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | COMPLETED
        |--------------------------------------------------------------------------
        */

        if ($header->status === 'C') {
            $push(
                'Completed',
                $header->completed_by,
                $header->completed_at,
                'C',
                'Completed',
                null,
                7
            );
        }

        /*
        |--------------------------------------------------------------------------
        | REJECTED BY IT
        |--------------------------------------------------------------------------
        */

        $rejectMsg = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->where('status', 'R')
            ->latest('message_date')
            ->first();

        if ($rejectMsg) {
            $push(
                'Rejected by IT',
                $rejectMsg->username,
                $rejectMsg->message_date ?? $header->updated_at,
                'R',
                'Rejected',
                $rejectMsg->message,
                8
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CANCELLED
        |--------------------------------------------------------------------------
        */

        if ($header->status === 'X') {
            $cancelNote = TrMessage::query()
                ->where('refnbr', $header->docid)
                ->where('doctype', $this->doctype)
                ->where('status', 'X')
                ->latest('message_date')
                ->first();

            $push(
                'Cancelled',
                $header->updated_by,
                $header->updated_at,
                'X',
                'Cancelled',
                $cancelNote?->message,
                99
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL SORT
        |--------------------------------------------------------------------------
        */

        $timeline = collect($timeline)
            ->sortBy([
                ['raw_date', 'asc'],
                ['sort_order', 'asc'],
            ])
            ->values()
            ->map(function ($row) {
                unset($row['raw_date']);
                unset($row['sort_order']);

                return $row;
            })
            ->toArray();

        return response()->json($timeline);
    }

    public function comment(Request $request, $hash)
    {
        $request->validate([
            'message' => 'required|string|min:2',
        ]);

        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | ACCESS VALIDATION
        |--------------------------------------------------------------------------
        */

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        $canAccess = (
            (
                in_array($header->cpny_id, $cpnyIds)
                && in_array($header->department_id, $deptIds)
            )
            || $header->created_by === $user->username
            || $isITHardware
            || TrApproval::where('refnbr', $header->docid)
                ->where('aprv_username', $user->username)
                ->exists()
        );

        if (!$canAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            TrMessage::create([
                'refnbr' => $header->docid,
                'doctype' => $this->doctype,
                'message_date' => now(),
                'cpny_id' => $header->cpny_id,
                'department_id' => $header->department_id,
                'username' => $user->username,
                'name' => $user->name,
                'message' => $request->message,
                'status' => $header->status,
                'created_by' => $user->username,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Comment submitted',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function comments($docid)
    {
        $header = TrItrecommend::where('docid', $docid)
            ->firstOrFail();

        $comments = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->orderBy('id')
            ->get();

        return response()->json($comments);
    }
}
