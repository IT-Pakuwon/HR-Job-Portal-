<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsInventory;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrApprovalHistory;
use App\Models\TrAttachment;
use App\Models\TrItrecommend;
use App\Models\TrItrecommendDetail;
use App\Models\TrMessage;
use App\Models\TrTicket;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $q = TrItrecommend::query()
            ->when(!$isITHardware, function ($query) use ($cpnyIds, $deptIds) {
                $query->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds);
            });

        $all = (clone $q)->whereNotIn('status', ['L'])->count();
        $waitingIT = (clone $q)->whereIn('status', ['W', 'I'])->count();
        $waitingApproval = (clone $q)->where('status', 'P')->count();
        $reject = (clone $q)->where('status', 'R')->count();
        $revise = (clone $q)->where('status', 'D')->count();
        $completed = (clone $q)->where('status', 'C')->count();

        $usercpny = Usercpny::where('username', $user->username)->get();
        $userdept = Userdept::where('username', $user->username)->get();

        $ticketDeptIds = $userdept->pluck('department_id')
            ->merge($deptIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $tickets = TrTicket::query()
            ->whereIn('department_id', $ticketDeptIds)
            ->whereIn('status', ['P', 'C'])
            ->whereNotIn('status_pekerjaan', ['CREATED', 'CANCEL', 'REJECT'])
            ->orderByDesc('ticketdate')
            ->limit(50)
            ->get([
                'ticketid',
                'issue_summary',
            ]);

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

        $isITHardware = SysUserRole::where('username', $user->username)
            ->where('role_id', 'ITHARDWARE')
            ->exists();

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
            ->whereNotIn('status', ['L'])
            ->when(!$isITHardware, function ($query) use ($cpnyIds, $deptIds) {
                $query->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds);
            });

        if ($status !== '') {
            $statuses = array_filter(
                array_map('trim', explode(',', $status))
            );

            $base->whereIn('status', $statuses);
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

        $data->transform(function ($row) use ($isITHardware, $user) {
            $row->eid = Hashids::encode($row->id);

            $row->can_process = (
                $isITHardware
                && in_array($row->status, ['W', 'I'])
            );

            $row->can_edit = (
                $row->created_by === $user->username
                && in_array($row->status, ['D'])
            );

            $row->can_cancel = (
                $row->created_by === $user->username
                && in_array($row->status, ['D'])
            );

            $row->can_upload_attachment =
            (
                $row->created_by === $user->username
                || $row->recommend_pic === $user->username
            )
            && !in_array(
                $row->status,
                ['C', 'R', 'X']
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

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $ticketDeptIds = \App\Models\Userdept::where('username', $user->username)
            ->pluck('department_id')
            ->merge($deptIds)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $data = TrTicket::query()
            ->whereIn('department_id', $ticketDeptIds)
            ->whereIn('status', ['P', 'C'])
            ->whereNotIn('status_pekerjaan', ['CREATED', 'CANCEL', 'REJECT'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('ticketid', 'ilike', "%{$q}%")
                        ->orWhere('issue_summary', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('ticketdate')
            ->limit(20)
            ->get([
                'ticketid',
                'user_peminta',
                'issue_summary',
            ]);

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
                    'cpny_id' => $request->cpny_id,
                    'department_id' => $request->department_id,
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

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $header->docid,
                    'doctype' => $this->doctype,
                    'cpny_id' => $header->cpny_id,
                    'department_id' => $header->department_id,
                    'base_folder' => 'att-it-recommendation',
                    'created_by' => auth()->user()->username,
                ];

                app(TrAttachmentController::class)
                    ->uploadInternal(
                        $meta,
                        (array) $request->file('attachments')
                    );
            }

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

            'attachments.*' => 'nullable|file|max:5120',
        ]);
        DB::connection('pgsql5')->beginTransaction();

        try {
            // Archive completed/revised approval steps to history before resetting
            TrApproval::where('refnbr', $header->docid)
                ->whereIn('status', ['A', 'D', 'R'])
                ->get()
                ->each(function ($aprv) {
                    TrApprovalHistory::create($aprv->only($aprv->getFillable()));
                });

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

            if ($request->hasFile('attachments')) {
                app(TrAttachmentController::class)
                    ->uploadInternal(
                        [
                            'refnbr' => $header->docid,
                            'doctype' => $this->doctype,
                            'cpny_id' => $header->cpny_id,
                            'department_id' => $header->department_id,
                            'base_folder' => 'att-it-recommendation',
                            'created_by' => $user->username,
                        ],
                        (array) $request->file('attachments')
                    );
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

    public function uploadAttachment(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        abort_if(
            in_array($header->status, ['C', 'R', 'X']),
            403,
            'Document already closed.'
        );

        $user = auth()->user();

        $canManageAttachment =
            $header->created_by === $user->username
            || $header->recommend_pic === $user->username;

        abort_unless(
            $canManageAttachment,
            403,
            'You are not allowed to manage attachments.'
        );

        $request->validate([
            'attachments' => ['required', 'array'],
            'attachments.*' => [
                'file',
                'max:5120',
            ],
        ]);

        $meta = [
            'refnbr' => $header->docid,
            'doctype' => $this->doctype,
            'cpny_id' => $header->cpny_id,
            'department_id' => $header->department_id,
            'base_folder' => 'att-it-recommendation',
            'created_by' => $user->username,
        ];

        app(TrAttachmentController::class)
            ->uploadInternal(
                $meta,
                (array) $request->file('attachments')
            );

        return response()->json([
            'success' => true,
            'message' => 'Attachment uploaded successfully.',
        ]);
    }

    public function deleteAttachment(TrAttachment $attachment)
    {
        $header = TrItrecommend::where(
            'docid',
            $attachment->refnbr
        )->firstOrFail();

        abort_if(
            in_array($header->status, ['C', 'R', 'X']),
            403,
            'Document already closed.'
        );

        $user = auth()->user();

        $canManageAttachment =
            $header->created_by === $user->username
            || $header->recommend_pic === $user->username;

        abort_unless(
            $canManageAttachment,
            403,
            'You are not allowed to manage attachments.'
        );

        DB::transaction(function () use ($attachment) {
            try {
                if ($attachment->filename_stored) {
                    Storage::disk('gcs')->delete(
                        $attachment->filename_stored
                    );
                }
            } catch (\Throwable $e) {
                Log::warning(
                    'Failed delete attachment file : '
                    .$e->getMessage()
                );
            }

            $attachment->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.',
        ]);
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

            TrMessage::create([
                'refnbr' => $header->docid,
                'doctype' => $this->doctype,
                'message_date' => now(),
                'cpny_id' => $header->cpny_id,
                'department_id' => $header->department_id,
                'username' => $user->username,
                'name' => $user->name,
                'message' => $request->note,
                'status' => 'I',
                'created_by' => $user->username,
            ]);

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
                        $header->docid,
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

            TrMessage::create([
                'refnbr' => $header->docid,
                'doctype' => $this->doctype,
                'message_date' => now(),
                'cpny_id' => $header->cpny_id,
                'department_id' => $header->department_id,
                'username' => auth()->user()->username,
                'name' => auth()->user()->name,
                'message' => $request->note,
                'status' => 'D',
                'created_by' => auth()->user()->username,
            ]);

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

        $keywords = [
            'perbaikan laptop',
            'perbaikan printer',
            'material perbaikan',
            'material pekerjaan',
            'installasi kabel jaringan',
            'jasa perbaikan',
            'jasa pekerjaan',
        ];

        $data = MsInventory::query()
            ->where('status', 'A')
            ->where(function ($query) use ($keywords) {
                $query->where(function ($ns) {
                    $ns->where('item_type', 'NS')
                        ->where('item_category', 'Komputer');
                });

                $query->orWhere(function ($se) use ($keywords) {
                    $se->where('item_type', 'SE')
                        ->whereIn('item_category', ['Komputer', 'Jasa'])
                        ->where(function ($desc) use ($keywords) {
                            foreach ($keywords as $keyword) {
                                $desc->orWhere('inventory_descr', 'ilike', "%{$keyword}%");
                            }
                        });
                });
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('inventoryid', 'ilike', "%{$q}%")
                        ->orWhere('inventory_descr', 'ilike', "%{$q}%");
                });
            })
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

        $ticketHash = null;

        if ($header->ticketnbr) {
            $ticket = TrTicket::query()
                ->where('ticketid', $header->ticketnbr)
                ->first();

            if ($ticket) {
                $ticketHash = Hashids::encode($ticket->id);
            }
        }

        $header->ticket_hash = $ticketHash;

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

        $attachmentResponse = app(TrAttachmentController::class)
            ->listAttachments(request(), $this->doctype, $header->docid);

        $attachments = collect(
            $attachmentResponse->getData(true)['attachments'] ?? []
        )->map(function ($att) {
            $att['signed_url'] = $att['url'] ?? null;

            return $att;
        });

        $canApprove = TrApproval::query()
            ->where('refnbr', $header->docid)
            ->where('status', 'P')
            ->get()
            ->contains(function ($row) use ($user) {
                $users = collect(
                    preg_split('/[,;|]/', $row->aprv_username)
                )
                ->map(fn ($x) => strtolower(trim($x)));

                return $users->contains(
                    strtolower($user->username)
                );
            });
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

        $fmtUsers = fn(?string $u): string => $u
            ? implode(', ', array_filter(array_map('trim', preg_split('/[,;|]/', $u))))
            : '';

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

        // IT-initiated revisions sent back to requester (status 'I')
        $itRevisions = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->where('status', 'I')
            ->orderBy('message_date')
            ->get();

        // Fallback for legacy records (old code stored with refnbr = numeric id)
        if ($itRevisions->isEmpty() && $header->status === 'D') {
            $legacyMsg = TrMessage::query()
                ->where('refnbr', (string) $header->id)
                ->where('doctype', $this->doctype)
                ->whereNotNull('message')
                ->where('message', '!=', '')
                ->orderBy('message_date')
                ->first();

            $push(
                'IT Revision Requested',
                $legacyMsg?->username ?? $header->updated_by,
                $legacyMsg?->message_date ?? $header->updated_at,
                'I',
                'IT Revision Requested',
                $legacyMsg?->message,
                2
            );
        }

        $itRevisionCount = $itRevisions->count();

        foreach ($itRevisions as $index => $msg) {
            $push(
                'IT Revision Requested',
                $msg->username,
                $msg->message_date,
                'I',
                'IT Revision Requested',
                $msg->message,
                2
            );

            // Show "Resubmitted" only when the revision was actually followed by a resubmit
            // (i.e. not the latest pending revision while doc is still in 'D' status).
            $isLastRevision = ($index === $itRevisionCount - 1);
            if (!$isLastRevision || $header->status !== 'D') {
                $push(
                    'Resubmitted',
                    $header->created_by,
                    $msg->message_date ? Carbon::parse($msg->message_date)->addSecond() : null,
                    'RS',
                    'Resubmitted',
                    null,
                    3
                );
            }
        }

        // Approval-initiated revisions sent back to IT (status 'D')
        $approvalRevisionMessages = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->where('status', 'D')
            ->orderBy('message_date')
            ->get();

        foreach ($approvalRevisionMessages as $msg) {
            $push(
                'Revision Requested',
                $msg->username,
                $msg->message_date,
                'D',
                'Revision Requested',
                $msg->message,
                5
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
                ->min('aprv_datebefore');

            $push(
                'IT Review',
                $header->recommend_pic,
                $processDate
                    ? Carbon::parse($processDate)
                    : $header->updated_at,
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
             ->orderByRaw('CAST(aprv_leveling AS NUMERIC)')
             ->get();

        foreach ($approvals as $row) {
            if ($row->status === 'A') {
                $push(
                    'Approved',
                    $fmtUsers($row->aprv_username),
                    $row->aprv_dateafter
                        ? Carbon::parse($row->aprv_dateafter)
                        : null,
                    'A',
                    'Approved',
                    $row->aprv_purpose,
                    5
                );

                continue;
            }

            if ($row->status === 'P') {
                if ($row->aprv_datebefore) {
                    $push(
                        'Waiting Approval',
                        $fmtUsers($row->aprv_username),
                        Carbon::parse($row->aprv_datebefore),
                        'P',
                        'Waiting Approval',
                        null,
                        6
                    );
                } else {
                    // Level belum aktif (date belum di-set) — tetap tampilkan sebagai upcoming step
                    $timeline[] = [
                        'title' => 'Waiting Approval',
                        'description' => $fmtUsers($row->aprv_username),
                        'date' => null,
                        'raw_date' => null,
                        'status' => 'P',
                        'label' => 'Waiting Approval',
                        'note' => null,
                        'sort_order' => 9,
                    ];
                }

                continue;
            }

            if ($row->status === 'D') {
                $push(
                    'Revision Requested',
                    $fmtUsers($row->aprv_username),
                    $row->aprv_dateafter
                        ? Carbon::parse($row->aprv_dateafter)
                        : $row->updated_at,
                    'D',
                    'Revision Requested',
                    $row->aprv_purpose,
                    6
                );

                continue;
            }

            if ($row->status === 'R') {
                $push(
                    'Rejected',
                    $fmtUsers($row->aprv_username),
                    $row->aprv_dateafter
                        ? Carbon::parse($row->aprv_dateafter)
                        : $row->updated_at,
                    'R',
                    'Rejected',
                    $row->aprv_purpose,
                    6
                );

                continue;
            }

            // Future/not-yet-started approval step — bypass $push() since date is null
            $timeline[] = [
                'title' => 'Waiting Approval',
                'description' => $fmtUsers($row->aprv_username),
                'date' => null,
                'raw_date' => null,
                'status' => 'P',
                'label' => 'Waiting Approval',
                'note' => null,
                'sort_order' => 9,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | HISTORICAL APPROVALS (from previous process cycles via TrApprovalHistory)
        |--------------------------------------------------------------------------
        */

        $histApprovals = TrApprovalHistory::query()
            ->where('refnbr', $header->docid)
            ->whereIn('status', ['A', 'D', 'R'])
            ->orderBy('aprv_dateafter')
            ->get();

        foreach ($histApprovals as $row) {
            if ($row->status === 'A') {
                $push('Approved', $fmtUsers($row->aprv_username),
                    $row->aprv_dateafter ? Carbon::parse($row->aprv_dateafter) : null,
                    'A', 'Approved', $row->aprv_purpose, 5);
            } elseif ($row->status === 'D') {
                $push('Revision Requested', $fmtUsers($row->aprv_username),
                    $row->aprv_dateafter ? Carbon::parse($row->aprv_dateafter) : null,
                    'D', 'Revision Requested', $row->aprv_purpose, 5);
            } elseif ($row->status === 'R') {
                $push('Rejected', $fmtUsers($row->aprv_username),
                    $row->aprv_dateafter ? Carbon::parse($row->aprv_dateafter) : null,
                    'R', 'Rejected', $row->aprv_purpose, 5);
            }
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
            ->sortBy(function ($row) {
                $date = $row['raw_date'];

                if (!$date) {
                    $ts = PHP_INT_MAX;
                } elseif ($date instanceof Carbon) {
                    $ts = $date->timestamp;
                } else {
                    $ts = Carbon::parse($date)->timestamp;
                }

                return sprintf('%020d%02d', $ts, (int) ($row['sort_order'] ?? 0));
            })
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

            /*
            |------------------------------------------------------------------
            | Comment notification
            |------------------------------------------------------------------
            */
            try {
                $usernames = collect([
                    $header->user_peminta,
                    $header->created_by,
                ])->filter()->unique();

                $approverUsernames = TrApproval::where('refnbr', $header->docid)
                    ->pluck('aprv_username');

                $usernames = $usernames->merge($approverUsernames)
                    ->filter()
                    ->unique()
                    ->reject(fn ($u) => $u === $user->username);

                $commenterEmail = $user->notification_email ?: $user->email;
                $commenterName = $user->name ?? $user->username;

                foreach ($usernames as $username) {
                    $recipient = User::where('username', $username)->first();
                    $email = $recipient?->notification_email ?: $recipient?->email;

                    if (!$email || $email === $commenterEmail) {
                        continue;
                    }

                    try {
                        Mail::to($email)->send(
                            new \App\Mail\CommentNotificationMail(
                                $this->doctype,
                                $header->docid,
                                $commenterName,
                                $request->message,
                                'ITR'
                            )
                        );
                    } catch (\Throwable $e) {
                        Log::warning('ITR Comment Mail Failed', [
                            'docid' => $header->docid,
                            'email' => $email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('ITR comment notification failed', [
                    'docid' => $header->docid,
                    'error' => $e->getMessage(),
                ]);
            }

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

    public function comments($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        $comments = TrMessage::query()
            ->where('refnbr', $header->docid)
            ->where('doctype', $this->doctype)
            ->orderBy('id')
            ->get();

        return response()->json($comments);
    }

    public function print($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;

        abort_if(!$id, 404);

        $header = TrItrecommend::findOrFail($id);

        $details = TrItrecommendDetail::where('docid', $header->docid)->get();

        $approvals = TrApproval::where('refnbr', $header->docid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS NUMERIC)')
            ->orderBy('id')
            ->get();

        $attachmentResponse = app(TrAttachmentController::class)
            ->listAttachments(request(), $this->doctype, $header->docid);

        $attachments = $attachmentResponse->getData(true)['attachments'] ?? [];

        $imageExts = ['jpg', 'jpeg', 'png'];
        foreach ($attachments as &$att) {
            $ext = strtolower($att['extention'] ?? '');
            if (in_array($ext, $imageExts) && !empty($att['url'])) {
                try {
                    $bytes = file_get_contents($att['url']);
                    $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                    $att['base64'] = 'data:'.$mime.';base64,'.base64_encode($bytes);
                } catch (\Throwable $e) {
                    $att['base64'] = null;
                }
            }
        }
        unset($att);

        $pdf = \PDF::loadView(
            'pages.it_recommendation.pdf_it_recommendation',
            compact('header', 'details', 'approvals', 'attachments')
        )->setPaper('a4', 'portrait');

        return $pdf->stream("IT-RECOMMENDATION-{$header->docid}.pdf");
    }
}
