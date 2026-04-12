<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\MsCompany;
// use App\Models\MsLocation;
// use App\Models\MsSubLocation;
// use App\Models\MsWorktypeDept;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrRfp;
use App\Models\TrPO;
use App\Models\TrCS;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrRfpStaging;
use App\Models\TrRfpStagingAttachment;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Userdept;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Mail;
use PDF;
use Vinkla\Hashids\Facades\Hashids;

class RfpController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_filter(array_map('trim', explode(',', $user->cpny_id)));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        if (is_string($user->department_id)) {
            $deptIds = array_filter(array_map('trim', explode(',', $user->department_id)));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $baseQuery = TrRfp::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all        = (clone $baseQuery)->count();
        $onProgress = (clone $baseQuery)->where('status', 'P')->count();
        $reject     = (clone $baseQuery)->where('status', 'R')->count();
        $revise     = (clone $baseQuery)->where('status', 'D')->count();
        $completed  = (clone $baseQuery)->where('status', 'C')->count();

        $hasRfpAllAccess = $user->hasRole('FINACCESS');
        $hasApFinAccess = $user->hasRole('APFINACCESS');
        $hasApTreAccess = $user->hasRole('APTREACCESS');
       
        $rfpAll = 0;
        if ($hasRfpAllAccess) {
            $rfpAll = TrRfp::query()
                ->whereIn('cpny_id', $cpnyIds)
                ->whereIn('status', ['C'])
                ->count();
        }

        return view('pages.rfp.rfp', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'rfpAll',
            'hasRfpAllAccess',
            'hasApFinAccess',
            'hasApTreAccess'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_filter(array_map('trim', explode(',', $user->cpny_id)));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        if (is_string($user->department_id)) {
            $deptIds = array_filter(array_map('trim', explode(',', $user->department_id)));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');
        $scope  = (string) $request->query('scope', '');

        $baseTable = (new TrRfp())->getTable(); // tr_rfp

        // mapping index order DataTables ke kolom DB
        $columns = [
            1  => 'rfp.rfp_id',
            2  => 'rfp.rfp_date',
            3  => 'rfp.cpny_id',
            4  => 'rfp.department_id',
            5  => 'rfp.sppbjkt_id', // untuk kolom gabungan sppbjkt/cs
            6  => 'rfp.ponbr',      // untuk kolom gabungan ponbr/kontrak
            7  => 'rfp.ir_id',
            8  => 'rfp.vendor_name',
            9  => 'rfp.keperluan',
            10 => 'rfp.rfp_amount',
            11 => 'rfp.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'rfp.rfp_date';

        $base = TrRfp::from($baseTable . ' as rfp')
            ->whereIn('rfp.cpny_id', $cpnyIds)
            ->when(
                $scope !== 'rfp_all',
                fn ($q) => $q->whereIn('rfp.department_id', $deptIds)
            )
            ->when(
                $scope === 'rfp_all',
                fn ($q) => $q->whereIn('rfp.status', ['C'])
            );

        if ($status !== '') {
            $base->where('rfp.status', $status);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('rfp.rfp_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.department_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.sppbjkt_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.cs_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.ponbr', 'ilike', "%{$search}%")
                    ->orWhere('rfp.kontrak_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.ir_id', 'ilike', "%{$search}%")
                    ->orWhere('rfp.vendor_name', 'ilike', "%{$search}%")
                    ->orWhere('rfp.keperluan', 'ilike', "%{$search}%")
                    ->orWhere('rfp.status', 'ilike', "%{$search}%")
                    ->orWhere('rfp.created_by', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
            'rfp.id',
            'rfp.rfp_id',
            'rfp.rfp_date',
            'rfp.cpny_id',
            'rfp.department_id',
            'rfp.sppbjkt_id',
            'rfp.cs_id',
            'rfp.ponbr',
            'rfp.kontrak_id',
            'rfp.ir_id',
            'rfp.vendor_name',
            'rfp.keperluan',
            'rfp.rfp_amount',
            'rfp.status',
            'rfp.status_receive',
            'rfp.user_receive',
            'rfp.receive_date',
            'rfp.status_payment',
            'rfp.user_payment',
            'rfp.payment_date',
            'rfp.created_by'
        )
        ->orderBy($orderCol, $orderDir)
        ->orderBy('rfp.rfp_id', 'desc')
        ->skip($start)
        ->take($length)
        ->get();

        $data->transform(function ($row) {
            $row->sppbjkt_cs = collect([$row->sppbjkt_id, $row->cs_id])
                ->filter(fn ($v) => !empty($v))
                ->implode(' - ');

            $row->po_kontrak = collect([$row->ponbr, $row->kontrak_id])
                ->filter(fn ($v) => !empty($v))
                ->implode(' / ');

            $statusReceive = strtoupper(trim((string) ($row->status_receive ?? 'P')));
            $statusPayment = strtoupper(trim((string) ($row->status_payment ?? 'P')));

            if ($statusReceive === 'P' && $statusPayment === 'P') {
                $row->finance_flow_status_text = 'Waiting User';
            } elseif ($statusReceive === 'C' && $statusPayment === 'P') {
                $row->finance_flow_status_text = 'Finance Received';
            } elseif ($statusReceive === 'C' && $statusPayment === 'C') {
                $row->finance_flow_status_text = 'Treasury Received';
            } else {
                $row->finance_flow_status_text = 'Waiting User';
            }

            $row->action_state = ($statusReceive === 'C') ? 'treasury' : 'received';

            $row->receive_button_text = !empty($row->user_receive) ? 'Rollback' : 'Update Received';
            $row->treasury_button_text = !empty($row->user_payment) ? 'Rollback' : 'Update Treasury';

            $row->eid = Hashids::encode($row->id);
            unset($row->id);

            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function showRfp($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $rfp = TrRfp::with([
            'creator:username,name',
        ])->findOrFail($id);

        // ===== Type Payment Logic
        $typepayment = '';

        $typePo = strtoupper(trim((string) $rfp->type_po));
        $typePaymentInvreg = strtolower(trim((string) $rfp->type_payment_invreg));

        $rfpBase = (float) ($rfp->rfp_base_amount ?? 0);
        $poBase  = (float) ($rfp->pobaseamount ?? 0); // ⚠️ pastikan field ini ada di DB

        if ($typePo === 'PO') {
            $typepayment = 'PO - STTB';

        } elseif ($typePo === 'SPK' && $typePaymentInvreg === 'full_payment_dengan_retensi') {
            $typepayment = 'SPK - Full Payment dengan Retensi';

        } elseif ($typePo === 'SPK' && $typePaymentInvreg === 'full_payment_tanpa_retensi') {
            $typepayment = 'SPK - Full Payment tanpa Retensi';

        } elseif ($typePo === 'SPK' && in_array($typePaymentInvreg, ['partial', 'partial_tanpa_retensi'])) {

            $pct = 0;
            if ($poBase > 0) {
                $pct = ($rfpBase / $poBase) * 100;
            }

            $typepayment = 'SPK - Partial ' . number_format($pct, 2, ',', '.') . ' %';

        } elseif ($typePo === 'SPK' && $typePaymentInvreg === 'retensi') {
            $typepayment = 'SPK - Retensi';

        } elseif ($typePo === 'KONTRAK') {

            $period = (string) ($rfp->period_payment ?? '');

            if (strlen($period) >= 7) {
                $typepayment = 'Payment Periode ' 
                    . substr($period, 5, 2) . '-' . substr($period, 0, 4);
            } else {
                $typepayment = 'Payment Periode -';
            }

        }

        $ponbr     = trim((string) ($rfp->ponbr ?? ''));
        $cpnyId    = trim((string) ($rfp->cpny_id ?? ''));
        $csid      = trim((string) ($rfp->cs_id ?? ''));
        $sppbjktid = trim((string) ($rfp->sppbjkt_id ?? ''));

        // ===== Link ke PO
        $poUrl = null;
        if ($ponbr !== '') {
            $poQuery = TrPO::query()
                ->whereRaw('TRIM(ponbr) = ?', [$ponbr]);

            if ($cpnyId !== '') {
                $poId = (clone $poQuery)
                    ->whereRaw('TRIM(cpny_id) = ?', [$cpnyId])
                    ->orderByDesc('id')
                    ->value('id');

                if (!$poId) {
                    $poId = (clone $poQuery)
                        ->orderByDesc('id')
                        ->value('id');
                }
            } else {
                $poId = (clone $poQuery)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($poId) {
                $poHash = Hashids::encode($poId);
                $poUrl = url("/showpo/{$poHash}");
            }
        }

        // ===== Link ke CS
        $csUrl = null;
        if ($csid !== '') {
            $csQuery = TrCS::query()
                ->whereRaw('TRIM(csid) = ?', [$csid]);

            if ($cpnyId !== '') {
                $csId = (clone $csQuery)
                    ->whereRaw('TRIM(cpny_id) = ?', [$cpnyId])
                    ->orderByDesc('id')
                    ->value('id');

                if (!$csId) {
                    $csId = (clone $csQuery)
                        ->orderByDesc('id')
                        ->value('id');
                }
            } else {
                $csId = (clone $csQuery)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl = url("/showcs/{$csHash}");
            }
        }

        // ===== Link ke SPPB/J/K/T
        $sppbjktUrl = null;
        $docPrefix = strtoupper(substr($sppbjktid, 0, 2));

        $routeMap = [
            'PB' => 'showsppbs',
            'PJ' => 'showsppjs',
            'PK' => 'showsppks',
            'PT' => 'showsppts',
        ];

        if ($sppbjktid !== '' && isset($routeMap[$docPrefix])) {
            $docId = null;

            if ($docPrefix === 'PB') {
                $docId = TrSPPB::whereRaw('TRIM(sppbid) = ?', [$sppbjktid])->value('id');
            } elseif ($docPrefix === 'PJ') {
                $docId = TrSPPJ::whereRaw('TRIM(sppjid) = ?', [$sppbjktid])->value('id');
            } elseif ($docPrefix === 'PK') {
                $docId = TrSPPK::whereRaw('TRIM(sppkid) = ?', [$sppbjktid])->value('id');
            } elseif ($docPrefix === 'PT') {
                $docId = TrSPPT::whereRaw('TRIM(spptid) = ?', [$sppbjktid])->value('id');
            }

            if (!empty($docId)) {
                $sppbjktHash = Hashids::encode($docId);
                $sppbjktUrl = url('/' . $routeMap[$docPrefix] . '/' . $sppbjktHash);
            }
        }

        $rows = TrAttachment::where('refnbr', $rfp->rfp_id)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];

        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);

        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/') . '/' . $r->filename;
            $object = $bucket->object($objectPath);

            $signedUrl = null;

            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', [
                    'path' => $objectPath,
                    'error' => $e->getMessage()
                ]);
            }

            return (object) [
                'display_name' => $r->attachment_name ?: $r->filename,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,                
            ];
        });

        $baseUrl = 'https://vendorportal-attachment.s3.ap-southeast-1.amazonaws.com/';

        $stagingAttachments = TrRfpStagingAttachment::where('irid', $rfp->ir_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) use ($baseUrl) {

                $path = trim($r->file_location, '/'); // bersihin slash depan/belakang
                $file = trim($r->filename, '/');

                $url = null;
                if ($path && $file) {
                    $url = $baseUrl . $path . '/' . $file;
                }

                return (object) [
                    'display_name' => $r->document_name ?: $r->filename,
                    'created_by'   => $r->created_by,
                    'created_at'   => $r->created_at,
                    'url'          => $url,
                    'is_staging'   => true,
                ];
            });

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = $canUpload = $rfp->status === 'P';

        $userdept = Userdept::where('username', '=', $user->username)->get();
        $userdept2 = Userdept::where('username', '=', $user->username)->first();

        return view('pages.rfp.showrfp', compact(
            'rfp',
            'attachments',
            'stagingAttachments', 
            'hash',
            'canUpload',
            'userdept',
            'userdept2',
            'poUrl',
            'csUrl',
            'sppbjktUrl',
            'typepayment'
        ));
    }

    public function updateReceived($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        abort_if(!$user, 401);

        if (!$user->hasRole('APFINACCESS')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update or rollback receive.'
            ], 403);
        }

        $rfp = TrRfp::findOrFail($id);

        if (!empty($rfp->user_receive)) {
            $rfp->user_receive   = '';
            $rfp->receive_date   = null;
            $rfp->status_receive = 'P';
            $rfp->updated_by     = $user->username ?? $user->name;
            $rfp->updated_at     = now();
            $rfp->save();

            return response()->json([
                'success' => true,
                'message' => 'Receive rollback successfully.',
            ]);
        }

        $rfp->user_receive   = $user->username ?? $user->name;
        $rfp->receive_date   = now();
        $rfp->status_receive = 'C';
        $rfp->updated_by     = $user->username ?? $user->name;
        $rfp->updated_at     = now();
        $rfp->save();

        return response()->json([
            'success' => true,
            'message' => 'Receive updated successfully.',
        ]);
    }

    public function updateTreasury($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        abort_if(!$user, 401);

        if (!$user->hasRole('APTREACCESS')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update or rollback payment.'
            ], 403);
        }

        $rfp = TrRfp::findOrFail($id);

        if (!empty($rfp->user_payment)) {
            $rfp->user_payment   = '';
            $rfp->payment_date   = null;
            $rfp->status_payment = 'P';
            $rfp->updated_by     = $user->username ?? $user->name;
            $rfp->updated_at     = now();
            $rfp->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment rollback successfully.',
            ]);
        }

        $rfp->user_payment   = $user->username ?? $user->name;
        $rfp->payment_date   = now();
        $rfp->status_payment = 'C';
        $rfp->updated_by     = $user->username ?? $user->name;
        $rfp->updated_at     = now();
        $rfp->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.',
        ]);
    }
    public function approveWo(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'WO';

        $wo = TrRfp::with('creator')->where('woid', $docid)->first();
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        $eid = Hashids::encode($wo->id);
        $docUrl = url('/showwos/'.$eid);
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $wo->woid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($wo, $fullname, $docUrl) {
                $wo->status = 'C';
                $wo->status_pekerjaan = 'H';
                $wo->completed_by = $wo->completed_by ?: auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $wo->woid,
                    'WO',
                    'C',
                    $wo->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $wo->cpny_id ?? $wo->cpnyid ?? '',
                        'deptname' => $wo->department_id ?? $wo->departementid ?? '',
                        'date' => $wo->wodate,
                        'info' => $wo->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($wo, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $wo->woid,
                    'WO',
                    'P',
                    'WO',
                    $docUrl,
                    [
                        'info' => $wo->keperluan,
                        'createdby' => $wo->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $wo->completed_by = auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectWo(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'WO';

        $wo = TrRfp::with('creator')->where('woid', $docid)->first();
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        $eid = Hashids::encode($wo->id);
        $docUrl = url('/showwos/'.$eid);
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $wo->woid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($wo, $fullname, $docUrl) {
                $wo->status = 'R';
                $wo->completed_by = auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $wo->woid,
                    'WO',
                    'R',
                    $wo->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $wo->cpny_id ?? $wo->cpnyid ?? '',
                        'deptname' => $wo->department_id ?? $wo->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $wo->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($wo->id, 'WO', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'WO rejected successfully']);
    }

    public function reviseWo(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'WO';

        $wo = TrRfp::with('creator')->where('woid', $docid)->first();
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        $eid = Hashids::encode($wo->id);
        $docUrl = url('/showwos/'.$eid);
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $wo->woid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($wo, $fullname, $docUrl) {
                // === HEADER WO -> D ===
                $wo->status = 'D';
                $wo->completed_by = auth()->user()->username;
                $wo->completed_at = $now;
                $wo->save();

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $wo->woid,
                    'WO',
                    'D',
                    $wo->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $wo->cpny_id ?? $wo->cpnyid ?? '',
                        'deptname' => $wo->department_id ?? $wo->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $wo->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($wo->id, 'WO', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'WO revised successfully']);
    }

    // // public function approveWo(Request $request, $docid)
    // // {
    // //     $now  = Carbon::now();
    // //     $user = $request->user();

    // //     // $wo = TrRfp::where('woid', $docid)->first();
    // //     $wo = TrRfp::with('creator')
    // //         ->where('woid', $docid)
    // //         ->first();
    // //     $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

    // //     if (!$wo) {
    // //         return response()->json(['success' => false, 'message' => 'WO not found'], 404);
    // //     }

    // //     // pastikan user memang approver aktif (status P) di doc ini
    // //     $tApproval = T_approval::where('docid', $wo->woid)
    // //         ->where('status', 'P')
    // //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    // //         ->whereNotNull('aprvdatebefore')
    // //         ->orderBy('aprvid', 'ASC')
    // //         ->first();

    // //     if (!$tApproval) {
    // //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    // //     }

    // //     DB::beginTransaction();
    // //     try {
    // //         // Set current approver -> Approved
    // //         $tApproval->status         = 'A';
    // //         $tApproval->aprvdateafter  = $now;
    // //         $tApproval->aprvusername   = $user->username;
    // //         $tApproval->name           = $user->name;
    // //         $tApproval->save();

    // //         // Update header informasi "terakhir diproses"
    // //         $wo->completed_by = $user->username;
    // //         $wo->completed_at = $now;
    // //         $wo->save();

    // //         // Hitung sisa pending setelah approve ini
    // //         $pendingCount = T_approval::where('docid', $wo->woid)
    // //             ->where('status', 'P')
    // //             ->count();

    // //         // Pemetaan judul sesuai status
    // //         $subjectMap = [
    // //             'P' => 'Waiting Approval',
    // //             'R' => 'Rejected Approval',
    // //             'D' => 'Revise Approval',
    // //             'A' => 'Approved',
    // //             'C' => 'Completed',
    // //         ];

    // //         $eid = Hashids::encode($wo->id);

    // //         if ($pendingCount === 0) {
    // //             // Tidak ada approver lagi -> dokumen complete
    // //             $wo->status       = 'C';
    // //             $wo->completed_by = $user->username;
    // //             $wo->completed_at = $now;
    // //             $wo->save();

    // //             // Kirim email ke requester (creator)
    // //             $status        = 'C';
    // //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    // //             $data = [
    // //                 'docid'     => $wo->woid,
    // //                 'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
    // //                 'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
    // //                 'date'      => $wo->wodate,
    // //                 'fullname'  => $fullname,  // nama penerima di email
    // //                 'name'      => $fullname,  // fallback
    // //                 'createdby' => $fullname,
    // //                 'docname'   => 'WO',
    // //                 'info'      => $wo->keperluan,
    // //                 'status'    => $status,
    // //                 'url'       => url('/showwos/' . $eid),
    // //             ];

    // //             $recipients = User::where('username', $wo->created_by)
    // //                 ->where('status', 'A')
    // //                 ->get();

    // //             foreach ($recipients as $rcp) {
    // //                 try {
    // //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    // //                         $to = $rcp->notification_email ?? $rcp->email; // pakai field yang memang ada
    // //                         $message->to($to)
    // //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //                     });
    // //                 } catch (\Throwable $e) {
    // //                     Log::error('Failed sending WO completion email', ['error' => $e->getMessage()]);
    // //                 }
    // //             }
    // //         } else {
    // //             // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
    // //             $next = T_approval::where('docid', $wo->woid)
    // //                 ->where('status', 'P')
    // //                 ->orderBy('aprvid', 'ASC')
    // //                 ->first();

    // //             if ($next) {
    // //                 // Stempel "datebefore" untuk approver berikutnya
    // //                 $next->aprvdatebefore = $now;
    // //                 $next->save();

    // //                 // Kirim email ke semua username yang ada di kolom aprvusername (dipisah koma)
    // //                 $status        = 'P';
    // //                 $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    // //                 $data = [
    // //                     'docid'     => $next->docid,
    // //                     'cpnyid'    => $next->aprvcpnyid,
    // //                     'deptname'  => $next->aprvdeptid,
    // //                     'date'      => $next->aprvdatebefore,
    // //                     'fullname'  => $next->name,
    // //                     'name'      => $next->name,
    // //                     'createdby' => $wo->created_by,
    // //                     'docname'   => 'WO',
    // //                     'info'      => $wo->keperluan,
    // //                     'status'    => $status,
    // //                     'url'       => url('/showwos/' . $eid),
    // //                 ];

    // //                 $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
    // //                 if (!empty($usernames)) {
    // //                     $recipients = User::whereIn('username', $usernames)
    // //                         ->where('status', 'A')
    // //                         ->get();

    // //                     foreach ($recipients as $rcp) {
    // //                         try {
    // //                             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    // //                                 $to = $rcp->notification_email ?? $rcp->email;
    // //                                 $message->to($to)
    // //                                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //                             });
    // //                         } catch (\Throwable $e) {
    // //                             Log::error('Failed sending WO waiting-approval email', ['error' => $e->getMessage()]);
    // //                         }
    // //                     }
    // //                 } else {
    // //                     Log::warning('Next approver has empty aprvusername list', ['docid' => $wo->woid]);
    // //                 }
    // //             }
    // //         }

    // //         DB::commit();
    // //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    // //     } catch (\Throwable $e) {
    // //         DB::rollBack();
    // //         Log::error('Approve WO failed', ['error' => $e->getMessage()]);
    // //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    // //     }
    // // }

    // // public function rejectWo(Request $request, $docid)
    // // {
    // //     $now  = Carbon::now();
    // //     $user = $request->user();

    // //     // $wo = TrRfp::where('woid', $docid)->first();
    // //     $wo = TrRfp::with('creator')
    // //         ->where('woid', $docid)
    // //         ->first();
    // //     $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

    // //     if (!$wo) {
    // //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    // //     }

    // //     // Validasi: user harus approver aktif (status P) pada dokumen ini
    // //     $tApproval = T_approval::where('docid', $wo->woid)
    // //         ->where('status', 'P')
    // //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    // //         ->whereNotNull('aprvdatebefore')
    // //         ->orderBy('aprvid', 'ASC')
    // //         ->first();

    // //     if (!$tApproval) {
    // //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    // //     }

    // //     DB::beginTransaction();
    // //     try {
    // //         // Tandai approval saat ini sebagai Rejected
    // //         $tApproval->status        = 'R';
    // //         $tApproval->aprvdateafter = $now;
    // //         $tApproval->aprvusername  = $user->username; // catat siapa yang reject
    // //         $tApproval->name          = $user->name;
    // //         $tApproval->save();

    // //         // Update header WO
    // //         $wo->status       = 'R';
    // //         $wo->completed_by = $user->username;
    // //         $wo->completed_at = $now;
    // //         $wo->save();

    // //         // Batalkan semua approval yang masih pending
    // //         T_approval::where('docid', $wo->woid)
    // //             ->where('status', 'P')
    // //             ->update(['status' => 'X']);

    // //         DB::commit();
    // //     } catch (\Throwable $e) {
    // //         DB::rollBack();
    // //         Log::error('Reject WO failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    // //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    // //     }

    // //     // === Kirim Email ke requester (creator) ===
    // //     $status = 'R'; // Rejected
    // //     $subjectMap = [
    // //         'P' => 'Waiting Approval',
    // //         'R' => 'Rejected Approval',
    // //         'D' => 'Revise Approval',
    // //         'A' => 'Approved',
    // //         'C' => 'Completed',
    // //     ];
    // //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    // //     $eid = Hashids::encode($wo->id);

    // //     $data = [
    // //         'docid'     => $wo->woid,
    // //         'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
    // //         'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
    // //         'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
    // //         'fullname'  => $fullname,               // view email kita pakai $fullname
    // //         'name'      => $fullname,               // fallback jika view pakai $name
    // //         'createdby' => $fullname,
    // //         'docname'   => 'WO',
    // //         'info'      => $wo->keperluan,
    // //         'status'    => $status,
    // //         'url'       => url('/showwos/' . $eid),
    // //     ];

    // //     $recipients = User::where('username', $wo->created_by)
    // //         ->where('status', 'A')
    // //         ->get();

    // //     foreach ($recipients as $rcp) {
    // //         try {
    // //             $to = $rcp->notification_email ?? $rcp->email; // sesuaikan field yang tersedia
    // //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    // //                 $message->to($to)
    // //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //             });
    // //         } catch (\Throwable $e) {
    // //             Log::error('Failed sending WO rejected email', [
    // //                 'docid' => $data['docid'],
    // //                 'to'    => $rcp->username,
    // //                 'error' => $e->getMessage()
    // //             ]);
    // //         }
    // //     }

    // //     // Simpan komentar penolakan (jika ada)
    // //     try {
    // //         app('App\Http\Controllers\SendCommentController')
    // //             ->sendmsg($wo->id, 'WO', $request);
    // //     } catch (\Throwable $e) {
    // //         Log::warning('SendComment after reject failed', [
    // //             'docid' => $wo->woid,
    // //             'error' => $e->getMessage()
    // //         ]);
    // //     }

    // //     return response()->json(['success' => true, 'message' => 'WO rejected successfully']);
    // // }

    // // public function reviseWo(Request $request, $docid)
    // // {
    // //     $now  = Carbon::now();
    // //     $user = $request->user();

    // //     // $wo = TrRfp::where('woid', $docid)->first();
    // //     $wo = TrRfp::with('creator')
    // //         ->where('woid', $docid)
    // //         ->first();
    // //     $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

    // //     if (!$wo) {
    // //         return response()->json(['success' => false, 'message' => 'WO not found'], 404);
    // //     }

    // //     // Pastikan user adalah approver aktif (status P) dokumen ini
    // //     $tApproval = T_approval::where('docid', $wo->woid)
    // //         ->where('status', 'P')
    // //         ->where('aprvusername', 'ilike', "%{$user->username}%")
    // //         ->whereNotNull('aprvdatebefore')
    // //         ->orderBy('aprvid', 'ASC')
    // //         ->first();

    // //     if (!$tApproval) {
    // //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    // //     }

    // //     DB::beginTransaction();
    // //     try {
    // //         // Tandai approval saat ini sebagai Revise (D)
    // //         $tApproval->status        = 'D';
    // //         $tApproval->aprvdateafter = $now;
    // //         $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
    // //         $tApproval->name          = $user->name;
    // //         $tApproval->save();

    // //         // Update header WO
    // //         $wo->status       = 'D';
    // //         $wo->completed_by = $user->username;        // mengikuti pola existing
    // //         $wo->completed_at = $now;
    // //         $wo->save();

    // //         // Batalkan approval lain yang masih pending
    // //         T_approval::where('docid', $wo->woid)
    // //             ->where('status', 'P')
    // //             ->update(['status' => 'X']);

    // //         DB::commit();
    // //     } catch (\Throwable $e) {
    // //         DB::rollBack();
    // //         Log::error('Revise WO failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    // //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    // //     }

    // //     // === Kirim email ke requester (creator) ===
    // //     $status = 'D'; // Revise
    // //     $subjectMap = [
    // //         'P' => 'Waiting Approval',
    // //         'R' => 'Rejected Approval',
    // //         'D' => 'Revise Approval',
    // //         'A' => 'Approved',
    // //         'C' => 'Completed',
    // //     ];
    // //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    // //     $eid = Hashids::encode($wo->id);

    // //     $data = [
    // //         'docid'     => $wo->woid,
    // //         'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
    // //         'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
    // //         'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
    // //         'fullname'  => $fullname,             // template email pakai $fullname
    // //         'name'      => $fullname,             // fallback jika view pakai $name
    // //         'createdby' => $fullname,
    // //         'docname'   => 'WO',
    // //         'info'      => $wo->keperluan,
    // //         'status'    => $status,
    // //         'url'       => url('/showwos/' . $eid),
    // //     ];

    // //     $recipients = User::where('username', $wo->created_by)
    // //         ->where('status', 'A')
    // //         ->get();

    // //     foreach ($recipients as $rcp) {
    // //         try {
    // //             $to = $rcp->notification_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
    // //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    // //                 $message->to($to)
    // //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
    // //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    // //             });
    // //         } catch (\Throwable $e) {
    // //             Log::error('Failed sending WO revise email', [
    // //                 'docid' => $data['docid'],
    // //                 'to'    => $rcp->username,
    // //                 'error' => $e->getMessage()
    // //             ]);
    // //         }
    // //     }

    // //     // Simpan komentar revisi (jika ada)
    // //     try {
    // //         app('App\Http\Controllers\SendCommentController')
    // //             ->sendmsg($wo->id, 'WO', $request);
    // //     } catch (\Throwable $e) {
    // //         Log::warning('SendComment after revise failed', [
    // //             'docid' => $wo->woid,
    // //             'error' => $e->getMessage()
    // //         ]);
    // //     }

    // //     return response()->json(['success' => true, 'message' => 'WO revised successfully']);
    // // }

    // // public function checkApproval($id, $action)
    // // {
    // //     $user = Auth::user(); // Ambil user yang login
    // //     // dd($action);
    // //     // Query dasar untuk pengecekan
    // //     $query = T_approval::where('docid', $id)
    // //                 ->where('aprvusername', 'ilike', '%' . $user->username . '%')
    // //                 ->where('status', 'P');

    // //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    // //     if (in_array($action, ['reject', 'revise','approve'])) {
    // //         $query->whereNotNull('aprvdatebefore');
    // //     }

    // //     // Cek apakah user bisa melakukan aksi
    // //     $canPerformAction = $query->exists();

    // //     return response()->json(['canPerformAction' => $canPerformAction]);
    // // }

    // public function tracking($hash)
    // {
    //     $id = Hashids::decode($hash)[0] ?? null;
    //     abort_if(!$id, 404);

    //     $wo = TrRfp::findOrFail($id);

    //     $getName = function (?string $username) {
    //         if (!$username) {
    //             return null;
    //         }
    //         $u = User::where('username', $username)->first();

    //         return $u->name ?? $username;
    //     };

    //     $createdByName = $getName($wo->created_by ?? null);
    //     $createdAt = $wo->created_at ? \Carbon\Carbon::parse($wo->created_at)->format('Y-m-d H:i') : null;

    //     $completedByName = $getName($wo->completed_by ?? null);
    //     $completedAt = $wo->completed_at ? \Carbon\Carbon::parse($wo->completed_at)->format('Y-m-d H:i') : null;

    //     // kolom opsional, kalau tidak ada biarkan null
    //     $rejectedByName = $getName($wo->rejected_by ?? null);
    //     $rejectedAt = isset($wo->rejected_at) ? \Carbon\Carbon::parse($wo->rejected_at)->format('Y-m-d H:i') : null;

    //     $revisedByName = $getName($wo->revised_by ?? null);
    //     $revisedAt = isset($wo->revised_at) ? \Carbon\Carbon::parse($wo->revised_at)->format('Y-m-d H:i') : null;

    //     $status = (string) ($wo->status ?? '');
    //     $labelMap = [
    //         'P' => 'Waiting approval',
    //         'R' => 'Rejected',
    //         'D' => 'Revise',
    //         'C' => 'Completed',
    //     ];
    //     $statusLabel = $labelMap[$status] ?? $status;

    //     // selalu mulai dari Submitted
    //     $steps = [[
    //         'key' => 'submitted',
    //         'title' => 'WO',
    //         'status' => 'C',              // dibuat = completed
    //         'status_label' => 'Submitted',
    //         'by' => $createdByName,
    //         'at' => $createdAt,
    //     ]];

    //     switch ($status) {
    //         case 'P':
    //             // masih menunggu/berjalan → tampilkan Approval saja
    //             $steps[] = [
    //                 'key' => 'approval',
    //                 'title' => 'Approval',
    //                 'status' => 'P',
    //                 'status_label' => 'Waiting approval',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         case 'R':
    //             // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
    //             $steps[] = [
    //                 'key' => 'rejected',
    //                 'title' => 'Rejected',
    //                 'status' => 'R',
    //                 'status_label' => 'Rejected',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         case 'D':
    //             // REVISE → Submitted → Revise
    //             $steps[] = [
    //                 'key' => 'revise',
    //                 'title' => 'Revise',
    //                 'status' => 'D',
    //                 'status_label' => 'Revise',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         case 'C':
    //             // SELESAI → bisa langsung Submitted → Completed
    //             // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
    //             // tambahkan step 'approval' sebelum 'completed')
    //             $steps[] = [
    //                 'key' => 'completed',
    //                 'title' => 'Completed',
    //                 'status' => 'C',
    //                 'status_label' => 'Completed',
    //                 'by' => $completedByName,
    //                 'at' => $completedAt,
    //             ];
    //             break;

    //         default:
    //             // status tidak dikenal → biarkan hanya Submitted
    //             break;
    //     }

    //     return response()->json([
    //         'doc' => $wo->woid ?? (string) $wo->id,
    //         'steps' => $steps,
    //         'status' => $status,
    //         'status_label' => $statusLabel,
    //     ]);
    // }

    public function tracking($hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $wo = \App\Models\TrRfp::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $steps = [];

        // ======================
        // 1. SUBMITTED
        // ======================
        $steps[] = [
            'type' => 'header',
            'title' => 'WO Submitted',
            'status' => 'C',
            'status_label' => 'Submitted',
            'by' => $getName($wo->created_by),
            'at' => optional($wo->created_at)->format('Y-m-d H:i'),
        ];

        // ======================
        // 2. GET APPROVALS
        // ======================
        $all = \App\Models\TrApproval::where('refnbr', $wo->woid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->get();

        // ======================
        // 3. GROUP INTO CYCLES
        // ======================
        $groups = $all->groupBy(function ($a) {
            return \Carbon\Carbon::parse($a->created_at)->format('Y-m-d H:i:s');
        });

        $hasMultipleCycle = $groups->count() > 1;
        $cycleIndex = 1;

        foreach ($groups as $group) {

            // ✅ SHOW cycle only if needed
            if ($hasMultipleCycle) {
                $steps[] = [
                    'type' => 'cycle',
                    'title' => 'Cycle ' . $cycleIndex,
                ];
            }

            // sort by level
            $sorted = $group->sortBy(fn($a) => (float)$a->aprv_leveling);

            foreach ($sorted as $a) {

                $map = match ($a->status) {
                    'A' => ['label' => 'Approved', 'status' => 'C'],
                    'P' => ['label' => 'Waiting Approval', 'status' => 'P'],
                    'R' => ['label' => 'Rejected', 'status' => 'R'],
                    'D' => ['label' => 'Revised', 'status' => 'D'],
                    'X' => ['label' => 'Cancelled', 'status' => 'X'],
                    default => ['label' => 'Pending', 'status' => '_']
                };

                $steps[] = [
                    'type' => 'approval',
                    'title' => 'Approval Lv ' . $a->aprv_leveling,
                    'status' => $map['status'],          // ✅ clean status
                    'status_label' => $map['label'],     // ✅ clean label
                    'by' => $getName($a->aprv_username),
                    'at' => $a->aprv_dateafter
                        ? \Carbon\Carbon::parse($a->aprv_dateafter)->format('Y-m-d H:i')
                        : null,
                ];
            }

            $cycleIndex++;
        }

        // ======================
        // 4. FINAL STATUS
        // ======================
        if ($wo->status === 'C') {
            $steps[] = [
                'type' => 'footer',
                'title' => 'Completed',
                'status' => 'C',
                'status_label' => 'Completed',
                'by' => $getName($wo->completed_by),
                'at' => optional($wo->completed_at)->format('Y-m-d H:i'),
            ];
        }

        if ($wo->status === 'R') {
            $steps[] = [
                'type' => 'footer',
                'title' => 'Rejected',
                'status' => 'R',
                'status_label' => 'Rejected',
                'by' => $getName($wo->completed_by),
                'at' => optional($wo->completed_at)->format('Y-m-d H:i'),
            ];
        }

        if ($wo->status === 'D') {
            $steps[] = [
                'type' => 'footer',
                'title' => 'Revised',
                'status' => 'D',
                'status_label' => 'Revised',
                'by' => $getName($wo->completed_by),
                'at' => optional($wo->completed_at)->format('Y-m-d H:i'),
            ];
        }

        return response()->json([
            'doc' => $wo->woid,
            'steps' => array_values($steps),
        ]);
    }

    public function printWo(Request $request, $hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!\Auth::check()) {
            return redirect()->route('login');
        }

        $wo = TrRfp::with([
            'worktype',      // MsWorktype
            'subworktype',   // MsSubworktype
            'location',      // MsLocation
            'sublocation',   // MsSubLocation
            'creator:username,name',
        ])->findOrFail($id);

        // $approval = TrApproval::query()
        //     ->where('refnbr', $wo->woid)          // dulu: docid
        //     ->where('status', '<>', 'X')
        //     ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
        //     ->orderBy('created_at', 'ASC')            // tie-breaker kalau leveling sama
        //     ->get();
        $refnbr = $wo->woid;
        $apprTable = (new TrApproval())->getTable(); // "tr_approval"

        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->reorder()
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);

        $approve_count = $approval->count();

        $company = MsCompany::where('cpny_id', $wo->cpny_id)->first();

        // mapping status
        $status_map = [
            'R' => 'Rejected',
            'C' => 'Completed',
            'D' => 'Hold',
            'X' => 'Cancel',
            'P' => 'On Progress',
        ];
        $status_doc = $status_map[$wo->status] ?? 'On Progress';

        // pilih varian tampilan
        $variant = $request->query('variant', 'default'); // default | tenant
        $view = $variant === 'tenant'
            ? 'pages.wos.pdf_wos_tenant'
            : 'pages.wos.pdf_wos';

        $data = [
            'title' => $variant === 'tenant' ? 'Work Order (Tenant)' : 'Work Order (WO)',
            'doc_type' => 'WO',
            'docid' => $wo->woid,
            'department_id' => $wo->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'cpnyid' => $wo->cpny_id,
            'created_by_username' => $wo->created_by,
            'created_by_name' => ucwords(strtolower(optional($wo->creator)->name)),
            'created_at_fmt' => optional($wo->created_at)->format('d F Y'),
            'req_date_fmt' => optional($wo->created_at)->format('d M Y H:i'),
            'wodate' => \Carbon\Carbon::parse($wo->wodate)->format('d F Y'),
            'keperluan' => $wo->keperluan,
            'status_doc' => $status_doc,
            'budget_use' => $wo->budget_use,
            // info tambahan yang sering dipakai di template
            'wotype' => $wo->wotype,                      // disimpan string category_name
            'worequest' => $wo->worequest,                   // disimpan string category_name
            'worktype_name' => optional($wo->worktype)->worktype_name,
            'subworktype_name' => optional($wo->subworktype)->subworktype_name,
            'location_name' => optional($wo->location)->location_name,
            'sub_location_name' => optional($wo->sublocation)->sub_location_name,
            'picrequester' => $wo->picrequester,
            'biaya_wo' => number_format($wo->biaya_wo, 0, ',', '.'),
        ];

        $pdf = \PDF::loadView($view, array_merge($data, [
            'approval' => $approval,
            'approve_count' => $approve_count,
        ]));

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        $suffix = $variant === 'tenant' ? '_tenant' : '';

        return $pdf->stream("pdf_wos{$suffix}_{$wo->woid}.pdf");
    }

    public function woJobs()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 📌 Company bisa multi (cpny1,cpny2,...)
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // 📌 Department juga bisa multi (IT,HRD,...)
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }
        // dd($deptIds);
        // Kalau salah satu kosong → tidak ada data
        if (empty($cpnyIds) || empty($deptIds)) {
            $all = $onProgress = $cancel = $completed = $wojobs = 0;

            return view('pages.wos.wojobs', compact('all', 'onProgress', 'cancel', 'wojobs', 'completed'));
        }

        $base = TrRfp::from('tr_wo as wo')
            ->join('ms_worktype_dept as wtd', function ($j) {
                $j->on('wtd.worktypeid', '=', 'wo.worktypeid');
            })
            ->whereIn('wo.cpny_id', $cpnyIds)          // 🔥 filter company
            ->whereIn('wtd.department_id', $deptIds)   // 🔥 filter department
            ->where('wo.status', 'C');                 // dokumen closed saja

        // Hitung pakai DISTINCT woid
        $all = (clone $base)->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $onProgress = (clone $base)->where('wo.status_pekerjaan', 'P')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $cancel = (clone $base)->where('wo.status_pekerjaan', 'X')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $completed = (clone $base)->where('wo.status_pekerjaan', 'C')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');
        $wojobs = (clone $base)->where('wo.status_pekerjaan', 'H')->selectRaw('COUNT(DISTINCT wo.woid) AS c')->value('c');

        return view('pages.wos.wojobs', compact('all', 'onProgress', 'cancel', 'wojobs', 'completed'));
    }

    public function jsonJobs(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        // Company multi
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // Department multi
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        if (empty($cpnyIds) || empty($deptIds)) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $jobStatus = (string) $request->query('job_status', '');
        $businessUnit = (string) $request->query('business_unit', '');

        $columns = [
            0 => 'wo.woid',
            1 => 'wo.wodate',
            2 => 'wo.cpny_id',
            3 => 'wo.department_id',
            4 => 'wt.worktype_name',
            5 => 'wo.worequest',
            6 => 'wo.keperluan',
            7 => 'wo.status_pekerjaan',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'wo.woid';

        $base = TrRfp::from('tr_wo as wo')

            ->leftJoin('ms_worktype as wt', function ($j) {
                $j->on('wt.worktypeid', '=', 'wo.worktypeid');
            })

            ->join('ms_worktype_dept as wtd', function ($j) {
                $j->on('wtd.worktypeid', '=', 'wo.worktypeid');
            })

            // LOCATION
            ->leftJoin('ms_location as loc', function ($j) {
                $j->on('loc.location_id', '=', 'wo.location_id');
            })

            // SUB LOCATION
            ->leftJoin('ms_sub_location as subloc', function ($j) {
                $j->on('subloc.sub_location_id', '=', 'wo.sub_location_id');
            })

            ->whereIn('wo.cpny_id', $cpnyIds)
            ->whereIn('wtd.department_id', $deptIds);

        // Filter job status
        if ($jobStatus !== '') {
            $base->where('wo.status_pekerjaan', $jobStatus);
        }

        if ($businessUnit !== '') {
            $base->where('wo.budget_business_unit_id', $businessUnit);
        }

        $recordsTotal = (clone $base)->distinct()->count('wo.woid');

        // Search
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('wo.woid', 'ilike', "%{$search}%")
                  ->orWhere('wo.cpny_id', 'ilike', "%{$search}%")
                  ->orWhere('wo.department_id', 'ilike', "%{$search}%")
                  ->orWhere('wt.worktype_name', 'ilike', "%{$search}%")
                  ->orWhere('wo.worequest', 'ilike', "%{$search}%")
                  ->orWhere('wo.keperluan', 'ilike', "%{$search}%")
                  ->orWhere('wo.status_pekerjaan', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct()->count('wo.woid');

        $data = $base->select(
            'wo.id',
            'wo.woid',
            'wo.wodate',
            'wo.cpny_id',
            'wo.department_id',

            'wo.pic_wo',

            'wt.worktype_name',
            'wo.worequest',
            'wo.keperluan',

            'wo.budget_business_unit_id',

            'loc.location_name',

            // FIXED COLUMN NAME
            'subloc.sub_location_name as sublocation_name',

            'wo.status',
            'wo.status_pekerjaan',
            'wo.created_by'
        )
            ->orderBy($orderCol, $orderDir)
            ->orderBy('wo.woid', 'desc')
            ->distinct('wo.woid')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function businessUnits()
    {
        $user = Auth::user();

        $cpnyIds = is_string($user->cpny_id)
            ? array_map('trim', explode(',', $user->cpny_id))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_map('trim', explode(',', $user->department_id))
            : (array) $user->department_id;

        $data = TrRfp::from('tr_wo as wo')
            ->join('ms_worktype_dept as wtd', function ($j) {
                $j->on('wtd.worktypeid', '=', 'wo.worktypeid');
            })
            ->whereIn('wo.cpny_id', $cpnyIds)
            ->whereIn('wtd.department_id', $deptIds)
            ->whereNotNull('wo.budget_business_unit_id')
            ->distinct()
            ->pluck('wo.budget_business_unit_id');

        return response()->json($data);
    }

    // POST /wo/{woid}/process
    public function processWo($woid)
    {
        $user = auth()->user();

        $wo = TrRfp::where('woid', $woid)->firstOrFail();

        if ($wo->pic_wo) {
            return response()->json([
                'success' => false,
                'message' => 'WO already processed.',
            ], 400);
        }

        $wo->pic_wo = $user->username;

        // REMOVE this if column does not exist
        // $wo->pic_department = $user->department_id ?? null;

        $wo->status_pekerjaan = 'P';

        $wo->save();

        return response()->json([
            'success' => true,
            'pic_wo' => $wo->pic_wo,
            'status_pekerjaan' => $wo->status_pekerjaan,
        ]);
    }

    // POST /wo/{woid}/job-status
    public function updateJobStatus(Request $req, $woid)
    {
        $req->validate([
            'status_pekerjaan' => 'required|in:P,X,C',
            'pic_wo_comment' => 'nullable|string',
            'pic_department' => 'nullable|string',
            'flag_sppbjkt' => 'nullable',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        $wo = TrRfp::where('woid', $woid)->firstOrFail();

        // =========================
        // UPDATE JOB STATUS
        // =========================
        $wo->status_pekerjaan = $req->status_pekerjaan;
        $wo->pic_wo_comment = $req->pic_wo_comment;
        $wo->pic_department = $req->pic_department;

        // =========================
        // COMPLETED TIMESTAMP
        // =========================
        if ($req->status_pekerjaan === 'C') {
            $wo->pic_completed_wo = now();
        }

        // =========================
        // FLAG NORMALIZATION
        // =========================
        $flag = filter_var($req->input('flag_sppbjkt'), FILTER_VALIDATE_BOOLEAN)
                || $req->input('flag_sppbjkt') == 1;

        $wo->flag_sppbjkt = $flag ? 'Y' : 'N';

        $wo->save();

        // =========================
        // ATTACHMENT UPLOAD
        // =========================
        if ($req->hasFile('attachment')) {
            $meta = [
                'refnbr' => $wo->woid,
                'doctype' => 'WO',
                'cpnyid' => $wo->cpny_id,
                'departementid' => $wo->department_id,
                'base_folder' => 'att-purchasing-app/wo-job',
                'created_by' => auth()->user()->username,
            ];

            $files = [$req->file('attachment')];

            try {
                app(TrAttachmentController::class)->uploadInternal($meta, $files);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment upload failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Job status updated.',
            'data' => [
                'status_pekerjaan' => $wo->status_pekerjaan,
                'pic_department' => $wo->pic_department,
                'pic_wo_comment' => $wo->pic_wo_comment,
                'pic_completed_wo' => $wo->pic_completed_wo,
                'flag_sppbjkt' => $wo->flag_sppbjkt,
            ],
        ]);
    }
}
