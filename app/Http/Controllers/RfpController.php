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

        $financeReceived = (clone $baseQuery)
            ->where('status', 'C')
            ->where('status_receive', 'C')
            ->where(function($q){
                $q->whereNull('status_payment')
                ->orWhere('status_payment', 'P');
            })
            ->count();

        $treasuryReceived = (clone $baseQuery)
            ->where('status', 'C')
            ->where('status_receive', 'C')
            ->where('status_payment', 'C')
            ->count();

        return view('pages.rfp.rfp', compact(
            'all',
            'onProgress',
            'reject',
            'revise',
            'completed',
            'rfpAll',
            'hasRfpAllAccess',
            'hasApFinAccess',
            'hasApTreAccess',
            'financeReceived',
            'treasuryReceived'
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
            ->when($scope === 'finance_received', function ($q) {
                $q->where('rfp.status', 'C')
                ->where('rfp.status_receive', 'C')
                ->where(function ($q2) {
                    $q2->whereNull('rfp.status_payment')
                        ->orWhere('rfp.status_payment', 'P');
                });
            })

            ->when($scope === 'treasury_received', function ($q) {
                $q->where('rfp.status', 'C')
                ->where('rfp.status_receive', 'C')
                ->where('rfp.status_payment', 'C');
            })
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

        $rfpSteps = collect();

        // 1. CREATED
        $rfpSteps->push([
            'order' => 1,
            'description' => 'RFP Created',
            'user' => $rfp->created_by,
            'date' => $rfp->created_at,
            'status' => 'Done',
        ]);

        // 2. FINANCE RECEIVED
        $rfpSteps->push([
            'order' => 2,
            'description' => 'Finance Received',
            'user' => $rfp->user_receive ?? '-',
            'date' => $rfp->receive_date,
            'status' => $rfp->status_receive === 'C' ? 'Done' : 'Pending',
        ]);

        // 3. TREASURY PAYMENT
        $rfpSteps->push([
            'order' => 3,
            'description' => 'Treasury Payment',
            'user' => $rfp->user_payment ?? '-',
            'date' => $rfp->payment_date,
            'status' => $rfp->status_payment === 'C' ? 'Done' : 'Pending',
        ]);

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
            'typepayment',
            'rfpSteps'
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

        $updatedBy = $user->username ?? $user->name;

        // Jika sudah receive, maka rollback
        if (!empty($rfp->user_receive) && !empty($rfp->receive_date)) {
            $rfp->user_receive   = null;
            $rfp->receive_date   = null;
            $rfp->status_receive = 'P';
            $rfp->updated_by     = $updatedBy;
            $rfp->updated_at     = now();
            $rfp->save();

            return response()->json([
                'success' => true,
                'message' => 'Receive rollback successfully.',
            ]);
        }

        // Jika belum receive, maka update receive
        $rfp->user_receive   = $updatedBy;
        $rfp->receive_date   = now();
        $rfp->status_receive = 'C';
        $rfp->updated_by     = $updatedBy;
        $rfp->updated_at     = now();
        $rfp->save();

        return response()->json([
            'success' => true,
            'message' => 'Receive updated successfully.',
        ]);
    }

    public function updateTreasury_xxx($hash)
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

        if ($rfp->status_receive !== 'C' && empty($rfp->user_payment) && empty($rfp->payment_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Finance receive belum completed.'
            ], 422);
        }

        $updatedBy = $user->username ?? $user->name;

        if (!empty($rfp->user_payment) && !empty($rfp->payment_date)) {
            $rfp->user_payment   = null;
            $rfp->payment_date   = null;
            $rfp->status_payment = 'P';
            $rfp->updated_by     = $updatedBy;
            $rfp->updated_at     = now();
            $rfp->save();

            return response()->json([
                'success' => true,
                'message' => 'Treasury rollback successfully.',
            ]);
        }

        $rfp->user_payment   = $updatedBy;
        $rfp->payment_date   = now();
        $rfp->status_payment = 'C';
        $rfp->updated_by     = $updatedBy;
        $rfp->updated_at     = now();
        $rfp->save();

        return response()->json([
            'success' => true,
            'message' => 'Treasury updated successfully.',
        ]);
    }
    public function approveRfp(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'RP';

        $rfp = TrRfp::with('creator')->where('rfp_id', $docid)->first();
        if (!$rfp) {
            return response()->json(['success' => false, 'message' => 'RP not found'], 404);
        }

        $eid = Hashids::encode($rfp->id);
        $docUrl = url('/showrfp/'.$eid);
        $fullname = data_get($rfp, 'creator.name') ?: $rfp->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $rfp->rfp_id,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($rfp, $fullname, $docUrl) {
                $rfp->status = 'C';               
                $rfp->completed_by = $rfp->completed_by ?: auth()->user()->username;
                $rfp->completed_at = $now;
                $rfp->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $rfp->rfp_id,
                    'RFP',
                    'C',
                    $rfp->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $rfp->cpny_id ?? $rfp->cpnyid ?? '',
                        'deptname' => $rfp->department_id ?? $rfp->departementid ?? '',
                        'date' => $rfp->rfp_date,
                        'info' => $rfp->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($rfp, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $rfp->rfp_id,
                    'RP',
                    'P',
                    'RFP',
                    $docUrl,
                    [
                        'info' => $rfp->keperluan,
                        'createdby' => $rfp->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $rfp->completed_by = auth()->user()->username;
                $rfp->completed_at = $now;
                $rfp->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectRfp(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'RP';

        $rfp = TrRfp::with('creator')->where('rfp_id', $docid)->first();
        if (!$rfp) {
            return response()->json(['success' => false, 'message' => 'RP not found'], 404);
        }

        $eid = Hashids::encode($rfp->id);
        $docUrl = url('/showrfp/'.$eid);
        $fullname = data_get($rfp, 'creator.name') ?: $rfp->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $rfp->rfp_id,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($rfp, $fullname, $docUrl) {
                $rfp->status = 'R';
                $rfp->completed_by = auth()->user()->username;
                $rfp->completed_at = $now;
                $rfp->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $rfp->rfp_id,
                    'RFP',
                    'R',
                    $rfp->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $rfp->cpny_id ?? $rfp->cpnyid ?? '',
                        'deptname' => $rfp->department_id ?? $rfp->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $rfp->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($rfp->id, 'RP', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success' => true, 'message' => 'RP rejected successfully']);
    }

    public function reviseRfp(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'RP';

        $rfp = TrRfp::with('creator')->where('rfp_id', $docid)->first();
        if (!$rfp) {
            return response()->json(['success' => false, 'message' => 'RP not found'], 404);
        }

        $eid = Hashids::encode($rfp->id);
        $docUrl = url('/showrfp/'.$eid);
        $fullname = data_get($rfp, 'creator.name') ?: $rfp->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $rfp->rfp_id,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($rfp, $fullname, $docUrl) {
                // === HEADER RP -> D ===
                $rfp->status = 'D';
                $rfp->completed_by = auth()->user()->username;
                $rfp->completed_at = $now;
                $rfp->save();

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $rfp->rfp_id,
                    'RFP',
                    'D',
                    $rfp->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $rfp->cpny_id ?? $rfp->cpnyid ?? '',
                        'deptname' => $rfp->department_id ?? $rfp->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $rfp->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($rfp->id, 'RP', request());
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

        return response()->json(['success' => true, 'message' => 'RP revised successfully']);
    }
   
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

    public function printPdfRfp($hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!\Auth::check()) {
            return redirect()->route('login');
        }

        $rfp = TrRfp::with(['creator:username,name'])->findOrFail($id);

        // =========================
        // APPROVAL
        // =========================
        $approval = TrApproval::where('refnbr', $rfp->rfp_id)
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->get();

        // =========================
        // FORMAT DATE
        // =========================
        $rfp->rfp_date_fmt = optional($rfp->rfp_date)->format('d M Y');
        $rfp->receive_date_fmt = optional($rfp->receive_date)->format('d M Y H:i');
        $rfp->payment_date_fmt = optional($rfp->payment_date)->format('d M Y H:i');

        // =========================
        // TERBILANG
        // =========================
        $rfp->terbilang = trim($this->terbilang((int)$rfp->rfp_amount)) . ' Rupiah';

        // =========================
        // STATUS DOC (FOR COLOR)
        // =========================
        $status_doc = match ($rfp->status) {
            'P' => 'Waiting Approval',
            'R' => 'Rejected',
            'D' => 'Revised',
            'C' => 'Completed',
            default => 'Unknown',
        };

        // =========================
        // APPROVAL COUNT
        // =========================
        $approve_count = $approval->count();

        // =========================
        // CREATED INFO
        // =========================
        $created_by_name = $rfp->creator->name ?? null;
        $created_by_username = $rfp->created_by;
        $req_date_fmt = optional($rfp->created_at)->format('d M Y H:i');
        $company = MsCompany::where('cpny_id', $rfp->cpny_id)->first();
        $cpny_name = $company->cpny_name ?? '';

        // =========================
        // LOAD PDF
        // =========================
        $pdf = \PDF::loadView('pages.rfp.pdf_rfp', [
            'rfp' => $rfp,
            'approval' => $approval,
            'status_doc' => $status_doc,
            'approve_count' => $approve_count,
            'created_by_name' => $created_by_name,
            'created_by_username' => $created_by_username,
            'req_date_fmt' => $req_date_fmt,
            'cpny_name' => $cpny_name,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("RFP_{$rfp->rfp_id}.pdf");
    }

    private function terbilang($angka)
    {
        $angka = abs($angka);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];

        if ($angka < 12) {
            return " " . $huruf[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . " Belas";
        } elseif ($angka < 100) {
            return $this->terbilang($angka / 10) . " Puluh" . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return " Seratus" . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang($angka / 100) . " Ratus" . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return " Seribu" . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang($angka / 1000) . " Ribu" . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang($angka / 1000000) . " Juta" . $this->terbilang($angka % 1000000);
        } else {
            return "Terlalu Besar";
        }
    }


}
