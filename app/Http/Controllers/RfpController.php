<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\BudgetDetail;
use App\Models\MsPurchSetting;
use App\Models\MsCompany;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrApprovalHistory;
use App\Models\TrAttachment;
use App\Models\TrIMBudget;
use App\Models\TrRfp;
use App\Models\TrRfpKontrakBudget;
use App\Models\TrKontrakBudget;
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
use App\Models\TrBast;
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

        $hasRfpAllAccess = $user->hasRole('FINACCESS');
        $hasApFinAccess = $user->hasRole('APFINACCESS');
        $hasApTreAccess = $user->hasRole('APTREACCESS');

        $baseQuery = TrRfp::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all        = (clone $baseQuery)->count();
        $onProgress = (clone $baseQuery)->where('status', 'P')->count();
        $reject     = (clone $baseQuery)->where('status', 'R')->count();
        $revise     = (clone $baseQuery)->where('status', 'D')->count();
        $hold       = (clone $baseQuery)->where('status', 'H')->count();
        $completed  = (clone $baseQuery)->where('status', 'C')->count();

        $rfpAll = 0;
        $rfpFinance = 0;
        if ($hasRfpAllAccess) {
            $rfpAll = TrRfp::query()
                ->whereIn('cpny_id', $cpnyIds)
                ->count();

            $rfpFinance = TrRfp::query()
                ->whereIn('cpny_id', $cpnyIds)
                ->where('status', 'C')
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
            'hold',
            'completed',
            'rfpAll',
            'rfpFinance',
            'cpnyIds',
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
        $financeCpny = trim((string) $request->query('finance_cpny', ''));
        $financeStatus = trim((string) $request->query('finance_status', ''));
        $typePo = strtoupper(trim((string) $request->query('type_po', '')));
        $hasRfpAllAccess = $user->hasRole('FINACCESS');

        if (in_array($scope, ['rfp_all', 'rfp_finance'], true) && !$hasRfpAllAccess) {
            $scope = '';
        }

        $baseTable = (new TrRfp())->getTable(); // tr_rfp

        // mapping index order DataTables ke kolom DB
        $columns = [
            1  => 'rfp.rfp_id',
            2  => 'rfp.rfp_date',
            3  => 'rfp.cpny_id',
            4  => 'rfp.department_id',
            5  => 'rfp.sppbjkt_id', // untuk kolom gabungan sppbjkt/cs
            6  => 'rfp.ponbr',      // untuk kolom gabungan ponbr/kontrak
            7  => 'rfp.type_po',
            8  => 'rfp.ir_id',
            9  => 'rfp.vendor_name',
            10 => 'rfp.keperluan',
            11 => 'rfp.rfp_amount',
            13 => 'rfp.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'rfp.rfp_id';

        $base = TrRfp::from($baseTable . ' as rfp')
            ->whereIn('rfp.cpny_id', $cpnyIds)
            ->when(
                !in_array($scope, ['rfp_all', 'rfp_finance'], true),
                fn ($q) => $q->whereIn('rfp.department_id', $deptIds)
            )
            ->when($scope === 'rfp_finance', function ($q) {
                $q->where('rfp.status', 'C');
            })
            ->when($scope === 'rfp_finance' && $financeCpny !== '' && in_array($financeCpny, $cpnyIds, true), function ($q) use ($financeCpny) {
                $q->where('rfp.cpny_id', $financeCpny);
            })
            ->when($scope === 'rfp_finance' && $financeStatus === 'waiting_user', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('rfp.status_receive')
                        ->orWhere('rfp.status_receive', 'P');
                })->where(function ($q2) {
                    $q2->whereNull('rfp.status_payment')
                        ->orWhere('rfp.status_payment', 'P');
                });
            })
            ->when($scope === 'rfp_finance' && $financeStatus === 'finance_received', function ($q) {
                $q->where('rfp.status_receive', 'C')
                    ->where(function ($q2) {
                        $q2->whereNull('rfp.status_payment')
                            ->orWhere('rfp.status_payment', 'P');
                    });
            })
            ->when($scope === 'rfp_finance' && $financeStatus === 'treasury_received', function ($q) {
                $q->where('rfp.status_receive', 'C')
                    ->where('rfp.status_payment', 'C');
            })
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
            ;

        if ($status !== '') {
            $base->where('rfp.status', $status);
        }

        if ($typePo !== '') {
            $base->whereRaw("UPPER(TRIM(COALESCE(rfp.type_po, ''))) = ?", [$typePo]);
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
                    ->orWhere('rfp.type_po', 'ilike', "%{$search}%")
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
            'rfp.type_po',
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
        $hasApFinAccess = $user->hasRole('APFINACCESS');
        $hasApTreAccess = $user->hasRole('APTREACCESS');

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

        //link bast        
        $bastUrl = null;

        $bastQuery = TrBast::query();

        if (!empty($rfp->bastid)) {
            $bastQuery->whereRaw('TRIM(bastid) = ?', [trim($rfp->bastid)]);
        } else {
            $bastQuery->whereRaw('TRIM(ponbr) = ?', [$ponbr]);

            if ($cpnyId !== '') {
                $bastQuery->whereRaw('TRIM(cpny_id) = ?', [$cpnyId]);
            }
        }

        $bastId = $bastQuery
            ->orderByDesc('id')
            ->value('id');
        
        if ($bastId) {
            $bastHash = Hashids::encode($bastId);
            $bastUrl = url("/showbast/{$bastHash}");
        }

        $imbudgetUrl = null;
        $imbudgetId = trim((string) ($rfp->imbudgetid ?? ''));

        if ($imbudgetId !== '') {
            $imbudget = TrIMBudget::query()
                ->where('imbudgetid', $imbudgetId)
                ->orderByDesc('id')
                ->first();

            if ($imbudget) {
                $imbudgetHash = Hashids::encode($imbudget->id);
                $imbudgetUrl = url('/showimbudgets/' . $imbudgetHash);
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
                    // 'created_by'   => $r->created_by,
                    // 'created_at'   => $r->created_at,
                    'url'          => $url,
                    'is_staging'   => true,
                ];
            });

        $terbilang = $this->terbilangRfpAmount($rfp->rfp_amount);
        $kontrakBudgets = TrRfpKontrakBudget::where('rfp_id', $rfp->rfp_id)
            ->where('status', '<>', 'X')
            ->orderBy('budget_perpost')
            ->orderBy('budget_account_id')
            ->get();

        $budgetRows = BudgetDetail::leftJoin('ms_coa', function ($join) {
                $join->on('ms_budget.account_id', '=', 'ms_coa.account_id')
                    ->on('ms_budget.cpny_id', '=', 'ms_coa.cpny_id');
            })
            ->where('ms_budget.status', 'C')
            ->select(
                'ms_budget.cpny_id',
                'ms_budget.business_unit_id',
                'ms_budget.department_fin_id',
                'ms_budget.account_id',
                'ms_budget.activity_id',
                'ms_budget.activity_descr',
                'ms_budget.perpost',
                'ms_budget.totalbudget',
                'ms_budget.totalbudget_add',
                'ms_budget.total_reserve',
                'ms_budget.total_used',
                'ms_coa.account_descr as account_descr'
            )
            ->get();

        $budgetMap = [];

        foreach ($budgetRows as $row) {
            $key = implode('|', [
                (string) $row->cpny_id,
                (string) $row->business_unit_id,
                (string) $row->department_fin_id,
                (string) $row->account_id,
                (string) $row->activity_descr,
                (string) $row->perpost,
            ]);

            $budgetMap[$key] = $row;
        }

        foreach ($kontrakBudgets as $item) {
            $key = implode('|', [
                (string) $item->budget_cpny_id,
                (string) $item->budget_business_unit_id,
                (string) $item->budget_department_fin_id,
                (string) $item->budget_account_id,
                (string) $item->budget_activity_descr,
                (string) $item->budget_perpost,
            ]);

            if (isset($budgetMap[$key])) {
                $budget = $budgetMap[$key];

                $item->budget_data = $budget;
                $item->account_descr = $budget->account_descr;

                $budgetValue = (float) ($budget->totalbudget ?? 0);
                $additional = (float) ($budget->totalbudget_add ?? 0);
                $reserved = (float) ($budget->total_reserve ?? 0);
                $used = (float) ($budget->total_used ?? 0);

                $item->budget_remaining = $budgetValue + $additional - $reserved - $used;
            } else {
                $item->budget_data = null;
                $item->account_descr = null;
                $item->budget_remaining = 0;
            }
        }

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = $rfp->status === 'P';

        $isApprover = TrApproval::where('refnbr', $rfp->rfp_id)
            ->where('aprv_doctype', 'RP')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->get()
            ->contains(function ($row) use ($loginUsername) {
                $list = preg_split('/[;,]/', (string) $row->aprv_username);
                $list = array_map('trim', $list);
                return in_array(strtolower((string) $loginUsername), array_map('strtolower', $list), true);
            });

        $userdept = Userdept::where('username', '=', $user->username)->get();
        $userdept2 = Userdept::where('username', '=', $user->username)->first();

        // RFP Purchase tidak menyimpan imbudgetid/status_imbudget di header,
        // jadi IM Budget yang blocking dicek lewat tr_imbudget.rfp_id (lihat approveRfp()).
        $blockingIMBudget = TrIMBudget::query()
            ->where('rfp_id', $rfp->rfp_id)
            ->where('doctype', 'RP')
            ->where('status', '<>', 'X')
            ->orderByDesc('id')
            ->first();

        $hasBlockingIM = $blockingIMBudget
            && !in_array(strtoupper(trim((string) $blockingIMBudget->status)), ['C', 'COMPLETED'], true);
        $imBlockingId = $blockingIMBudget->imbudgetid ?? null;
        $imBlockingStatus = $blockingIMBudget->status ?? null;

        $rfpSteps = collect();
        $createdStepUser = strtoupper(trim((string) $rfp->type_po)) === 'KONTRAK'
            ? ($rfp->user_peminta ?: '-')
            : ($rfp->created_by ?: '-');

        // 1. CREATED
        $rfpSteps->push([
            'order' => 1,
            'description' => 'RFP Created',
            'user' => $createdStepUser,
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
            'isApprover',
            'hasBlockingIM',
            'imBlockingId',
            'imBlockingStatus',
            'userdept',
            'userdept2',
            'poUrl',
            'csUrl',
            'sppbjktUrl',
            'bastUrl',
            'imbudgetUrl',
            'typepayment',
            'rfpSteps',
            'terbilang',
            'kontrakBudgets',
            'hasApFinAccess',
            'hasApTreAccess'
        ));
    }

    public function createRfpKontrakBudget($hash)
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

        abort_if(strtoupper(trim((string) $rfp->type_po)) !== 'KONTRAK', 404);

        $budgets = TrRfpKontrakBudget::where('rfp_id', $rfp->rfp_id)
            ->orderBy('budget_perpost')
            ->orderBy('budget_account_id')
            ->get();
        $this->attachKontrakBudgetRemaining($budgets);

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
                Log::warning('Signed URL gagal', [
                    'path' => $objectPath,
                    'error' => $e->getMessage(),
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
                $path = trim($r->file_location, '/');
                $file = trim($r->filename, '/');

                $url = null;
                if ($path && $file) {
                    $url = $baseUrl . $path . '/' . $file;
                }

                return (object) [
                    'display_name' => $r->document_name ?: $r->filename,
                    'created_by'   => null,
                    'created_at'   => $r->created_at,
                    'url'          => $url,
                    'is_staging'   => true,
                ];
            });

        $terbilang = $this->terbilangRfpAmount($rfp->rfp_amount);

        return view('pages.rfp.createrfpkontrakbudget', compact(
            'rfp',
            'budgets',
            'attachments',
            'stagingAttachments',
            'hash',
            'terbilang'
        ));
    }

    public function editRfpKontrakBudget($hash)
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

        $budgets = TrRfpKontrakBudget::where('rfp_id', $rfp->rfp_id)
            ->orderBy('budget_perpost')
            ->orderBy('budget_account_id')
            ->get();
        $this->attachKontrakBudgetRemaining($budgets);

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
                Log::warning('Signed URL gagal', [
                    'path' => $objectPath,
                    'error' => $e->getMessage(),
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
                $path = trim($r->file_location, '/');
                $file = trim($r->filename, '/');

                $url = null;
                if ($path && $file) {
                    $url = $baseUrl . $path . '/' . $file;
                }

                return (object) [
                    'display_name' => $r->document_name ?: $r->filename,
                    'created_by'   => null,
                    'created_at'   => $r->created_at,
                    'url'          => $url,
                    'is_staging'   => true,
                ];
            });

        $terbilang = $this->terbilangRfpAmount($rfp->rfp_amount);

        return view('pages.rfp.editrfpkontrakbudget', compact(
            'rfp',
            'budgets',
            'attachments',
            'stagingAttachments',
            'hash',
            'terbilang'
        ));
    }

    public function submitRfpKontrakBudget(Request $request, $hash)
    {
        return $this->saveRfpKontrakBudget(
            $request,
            $hash,
            true,
            'RFP Kontrak Budget submitted successfully.',
            'Failed to submit RFP Kontrak Budget.'
        );
    }

    public function updateRfpKontrakBudget(Request $request, $hash)
    {
        return $this->saveRfpKontrakBudget(
            $request,
            $hash,
            false,
            'RFP updated successfully.',
            'Failed to update RFP.'
        );
    }

    public function cancelRfpKontrakBudget(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = $request->user() ?: Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        DB::beginTransaction();

        try {
            $rfp = TrRfp::lockForUpdate()->findOrFail($id);

            if ($rfp->status === 'X') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'RFP ini sudah di-cancel.',
                ], 422);
            }

            $rfp->status = 'X';
            $rfp->updated_by = $user->username ?? $user->name ?? Auth::id();
            $rfp->updated_at = Carbon::now('Asia/Jakarta');
            $rfp->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RFP berhasil di-cancel.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal cancel RFP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function kontrakBudgetOptions(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        abort_if(!$user, 401);

        $rfp = TrRfp::findOrFail($id);
        $kontrakId = trim((string) ($rfp->kontrak_id ?? ''));
        if ($kontrakId === '') {
            $kontrakId = trim((string) $request->get('kontrakid', ''));
        }
        $search = trim((string) $request->get('search', ''));
        $page = max((int) $request->get('page', 1), 1);
        $perPage = min(max((int) $request->get('per_page', 10), 1), 100);

        if ($kontrakId === '') {
            return response()->json([
                'data' => [],
                'total' => 0,
                'page' => $page,
                'per_page' => $perPage,
                'message' => 'Kontrak ID tidak tersedia.',
            ]);
        }

        $query = TrKontrakBudget::query()
            ->from('tr_kontrak_budget as kb')
            ->leftJoin('ms_budget as b', function ($join) {
                $join->on('b.cpny_id', '=', 'kb.budget_cpny_id')
                    ->on('b.business_unit_id', '=', 'kb.budget_business_unit_id')
                    ->on('b.department_fin_id', '=', 'kb.budget_department_fin_id')
                    ->on('b.account_id', '=', 'kb.budget_account_id')
                    ->on('b.activity_id', '=', 'kb.budget_activity_id')
                    ->on('b.activity_descr', '=', 'kb.budget_activity_descr')
                    ->on('b.perpost', '=', 'kb.budget_perpost')
                    ->where('b.status', 'C');
            })
            ->leftJoin('ms_coa as c', function ($join) {
                $join->on('c.account_id', '=', 'kb.budget_account_id')
                    ->on('c.cpny_id', '=', 'kb.budget_cpny_id');
            })
            ->leftJoin('ms_activity as a', function ($join) {
                $join->on('a.activity_id', '=', 'kb.budget_activity_id')
                    ->on('a.cpny_id', '=', 'kb.budget_cpny_id');
            })
            ->where('kb.kontrakid', $kontrakId)
            ->where(function ($q) {
                $q->whereNull('kb.status')
                    ->orWhere('kb.status', '<>', 'X');
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kb.budget_account_id', 'ilike', "%{$search}%")
                    ->orWhere('c.account_descr', 'ilike', "%{$search}%")
                    ->orWhere('kb.budget_activity_id', 'ilike', "%{$search}%")
                    ->orWhere('kb.budget_activity_descr', 'ilike', "%{$search}%")
                    ->orWhere('kb.budget_business_unit_id', 'ilike', "%{$search}%")
                    ->orWhere('kb.budget_department_fin_id', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('kb.budget_account_id')
            ->orderBy('kb.budget_activity_descr')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'kb.budget_cpny_id as cpny_id',
                'kb.budget_business_unit_id as business_unit_id',
                'kb.budget_department_fin_id as department_fin_id',
                'kb.budget_account_id as account_id',
                'c.account_descr',
                'kb.budget_activity_id as activity_id',
                'kb.budget_activity_descr as activity_descr',
                'a.activity_descr as act_descr',
                'kb.budget_perpost as perpost',
                DB::raw('COALESCE(b.totalbudget, 0) as totalbudget'),
                DB::raw('COALESCE(b.totalbudget_add, 0) as totalbudget_add'),
                DB::raw('COALESCE(b.total_reserve, 0) as total_reserve'),
                DB::raw('COALESCE(b.total_used, 0) as total_used'),
                DB::raw('(COALESCE(b.totalbudget, 0) + COALESCE(b.totalbudget_add, 0)) as availablebudget'),
                DB::raw('(COALESCE(b.total_reserve, 0) + COALESCE(b.total_used, 0)) as usedbudget'),
                DB::raw('((COALESCE(b.totalbudget, 0) + COALESCE(b.totalbudget_add, 0)) - (COALESCE(b.total_reserve, 0) + COALESCE(b.total_used, 0))) as remaining'),
            ]);

        return response()->json([
            'data' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    private function attachKontrakBudgetRemaining($kontrakBudgets): void
    {
        if ($kontrakBudgets->isEmpty()) {
            return;
        }

        $budgetRows = BudgetDetail::query()
            ->where('status', 'C')
            ->select(
                'cpny_id',
                'business_unit_id',
                'department_fin_id',
                'account_id',
                'activity_descr',
                'perpost',
                'totalbudget',
                'totalbudget_add',
                'total_reserve',
                'total_used'
            )
            ->get();

        $budgetMap = [];

        foreach ($budgetRows as $row) {
            $key = implode('|', [
                (string) $row->cpny_id,
                (string) $row->business_unit_id,
                (string) $row->department_fin_id,
                (string) $row->account_id,
                (string) $row->activity_descr,
                (string) $row->perpost,
            ]);

            $budgetMap[$key] = $row;
        }

        foreach ($kontrakBudgets as $item) {
            $key = implode('|', [
                (string) $item->budget_cpny_id,
                (string) $item->budget_business_unit_id,
                (string) $item->budget_department_fin_id,
                (string) $item->budget_account_id,
                (string) $item->budget_activity_descr,
                (string) $item->budget_perpost,
            ]);

            if (!isset($budgetMap[$key])) {
                $item->budget_remaining = 0;
                continue;
            }

            $budget = $budgetMap[$key];
            $budgetValue = (float) ($budget->totalbudget ?? 0);
            $additional = (float) ($budget->totalbudget_add ?? 0);
            $reserved = (float) ($budget->total_reserve ?? 0);
            $used = (float) ($budget->total_used ?? 0);

            $item->budget_remaining = $budgetValue + $additional - $reserved - $used;
        }
    }

    private function saveRfpKontrakBudget(Request $request, $hash, bool $requireKontrak, string $successMessage, string $errorMessage)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $rfp = TrRfp::findOrFail($id);
        $isKontrak = strtoupper(trim((string) $rfp->type_po)) === 'KONTRAK';

        if ($requireKontrak && !$isKontrak) {
            return response()->json([
                'message' => 'Submit budget hanya untuk RFP type KONTRAK.',
            ], 422);
        }

        if ($isKontrak) {
            $request->validate([
                'budget_perpost' => ['required', 'array', 'min:1'],
                'budget_perpost.*' => ['required'],
                'budget_cpny_id' => ['required', 'array', 'min:1'],
                'budget_cpny_id.*' => ['required'],
                'budget_business_unit_id' => ['required', 'array', 'min:1'],
                'budget_business_unit_id.*' => ['required'],
                'budget_department_fin_id' => ['required', 'array', 'min:1'],
                'budget_department_fin_id.*' => ['required'],
                'budget_account_id' => ['required', 'array', 'min:1'],
                'budget_account_id.*' => ['required'],
                'budget_activity_id' => ['required', 'array', 'min:1'],
                'budget_activity_id.*' => ['required'],
                'budget_activity_descr' => ['required', 'array', 'min:1'],
                'budget_activity_descr.*' => ['required'],
                'rfp_base_amount' => ['required', 'array', 'min:1'],
                'rfp_base_amount.*' => ['required'],
            ]);
        }

        $doctype = 'RP';
        $docid = $rfp->rfp_id;
        $docName = 'RFP';
        $username = $user->username ?? 'system';
        $dt = Carbon::now('Asia/Jakarta');

        $toFloat = function ($value): float {
            $value = trim((string) $value);

            if ($value === '') {
                return 0;
            }

            if (str_contains($value, ',') && str_contains($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } elseif (str_contains($value, ',')) {
                $value = str_replace(',', '.', $value);
            }

            return is_numeric($value) ? (float) $value : 0;
        };

        DB::beginTransaction();

        try {
            $approvalCtl = app(ApprovalController::class);
            $approvalCtl->loadLines($doctype, $rfp->cpny_id, $rfp->department_id);

            if ($isKontrak) {
                TrRfpKontrakBudget::where('rfp_id', $rfp->rfp_id)->delete();

                $perposts = $request->input('budget_perpost', []);
                $budgetCpnyIds = $request->input('budget_cpny_id', []);
                $businessUnitIds = $request->input('budget_business_unit_id', []);
                $departmentFinIds = $request->input('budget_department_fin_id', []);
                $accountIds = $request->input('budget_account_id', []);
                $activityIds = $request->input('budget_activity_id', []);
                $activityDescrs = $request->input('budget_activity_descr', []);
                $amounts = $request->input('rfp_base_amount', []);

                $rfpBaseAmount = $toFloat($rfp->rfp_base_amount ?? 0);
                $totalAmount = 0;
                $inserted = 0;
                $validRows = [];

                foreach ($accountIds as $i => $accountId) {
                    $accountId = trim((string) $accountId);

                    if ($accountId === '') {
                        continue;
                    }

                    $amount = $toFloat($amounts[$i] ?? 0);

                    if ($amount <= 0) {
                        DB::rollBack();

                        return response()->json([
                            'message' => 'Amount budget harus lebih besar dari 0.',
                            'errors' => [
                                "rfp_base_amount.{$i}" => ['Amount budget harus lebih besar dari 0.'],
                            ],
                        ], 422);
                    }

                    $totalAmount += $amount;
                    $validRows[] = [
                        'index' => $i,
                        'account_id' => $accountId,
                        'amount' => $amount,
                    ];
                }

                if (count($validRows) <= 0) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Minimal 1 detail budget harus dipilih.',
                    ], 422);
                }

                if ($rfpBaseAmount > 0 && abs($totalAmount - $rfpBaseAmount) > 0.01) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Total amount budget harus sama dengan RFP base amount.',
                        'errors' => [
                            'rfp_base_amount' => ['Total amount budget harus sama dengan RFP base amount.'],
                        ],
                    ], 422);
                }

                foreach ($validRows as $row) {
                    $i = $row['index'];

                    TrRfpKontrakBudget::create([
                        'rfp_id' => $rfp->rfp_id,
                        'cpny_id' => $rfp->cpny_id,
                        'budget_perpost' => $perposts[$i] ?? null,
                        'budget_cpny_id' => $budgetCpnyIds[$i] ?? null,
                        'budget_business_unit_id' => $businessUnitIds[$i] ?? null,
                        'budget_department_fin_id' => $departmentFinIds[$i] ?? null,
                        'budget_account_id' => $row['account_id'],
                        'budget_activity_id' => $activityIds[$i] ?? null,
                        'budget_activity_descr' => $activityDescrs[$i] ?? null,
                        'rfp_base_amount' => $row['amount'],
                        'status' => 'A',
                        'created_by' => $username,
                        'created_at' => $dt,
                    ]);

                    $inserted++;
                }

                if ($inserted <= 0) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Minimal 1 detail budget harus dipilih.',
                    ], 422);
                }

                $this->reserveBudget($doctype, $docid, $request->cpnyid, 'Submit', $username);
            }

            TrApproval::query()
                ->where('refnbr', $rfp->rfp_id)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->delete();

            $ctx = [
                'ignore_nominal' => false,
                'grand_total' => (float) ($rfp->rfp_amount ?? $rfp->rfp_base_amount ?? 0),
            ];

            $approvalCtl->generateForDocument(
                $rfp->rfp_id,
                $doctype,
                $rfp->cpny_id,
                $rfp->department_id,
                $rfp->created_by ?? $username,
                $ctx,
                $dt
            );

            $firstPending = TrApproval::query()
                ->where('refnbr', $rfp->rfp_id)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->orderByRaw('CAST(aprv_leveling AS DECIMAL(10,2)) ASC')
                ->first();

            if (!$firstPending) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Approval line tidak valid / tidak ditemukan.',
                ], 422);
            }

            $rfp->status = 'P';
            $rfp->completed_by = $firstPending->aprv_username;
            $rfp->completed_at = $dt;
            $rfp->updated_by = $username;
            $rfp->updated_at = $dt;
            $rfp->save();

            $approverUsernames = str_replace(';', ',', (string) $firstPending->aprv_username);
            $approvers = array_filter(array_map('trim', explode(',', $approverUsernames)));

            $toEmails = User::query()
                ->whereIn('username', $approvers)
                ->where('status', 'A')
                ->pluck('notification_email')
                ->filter(fn ($email) => trim((string) $email) !== '')
                ->unique()
                ->values()
                ->toArray();

            $mailData = [
                'docid' => $rfp->rfp_id,
                'cpnyid' => $rfp->cpny_id ?? '',
                'deptname' => $rfp->department_id ?? '',
                'date' => $dt->toDateTimeString(),
                'name' => $username,
                'status' => $rfp->status,
                'docname' => $docName,
                'url' => url('/showrfp/' . $hash),
                'info' => $rfp->keperluan ?: $rfp->ir_note,
                'createdby' => $rfp->created_by ?: $username,
            ];

            if (!empty($toEmails)) {
                Mail::send('emails.mailapprovenew', $mailData, function ($message) use ($toEmails, $rfp, $docName) {
                    $message->to($toEmails)
                        ->subject($rfp->rfp_id . ' - WaitingApproval ' . $docName)
                        ->from(config('mail.from.address'), config('app.name'));
                });
            }

            DB::commit();

            return response()->json([
                'message' => $successMessage,
                'docid' => $rfp->rfp_id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            return response()->json([
                'message' => $e->getMessage() ?: $errorMessage,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode === 422 ? 422 : 500);
        }
    }

    private function terbilangRfpAmount($amount): string
    {
        $amount = (int) round((float) ($amount ?? 0));

        return trim($this->terbilang($amount)) . ' Rupiah';
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $rfp = TrRfp::with('creator')->where('rfp_id', $docid)->first();
        if (!$rfp) {
            return response()->json(['success' => false, 'message' => 'RP not found'], 404);
        }

        $doctype = 'RP';
        $docName = 'RFP';
        $eid = Hashids::encode($rfp->id);
        $docUrl = url('/showrfp/' . $eid);
        $fullname = optional($rfp->creator)->name ?: $rfp->created_by;

        /*
        |----------------------------------------------------------------------
        | CHECK IM BUDGET BEFORE APPROVE
        |----------------------------------------------------------------------
        | RFP Purchase tidak menyimpan flag IM di header, jadi cek kebutuhan dari
        | detail kontrak budget dan cek dokumen IM Budget lewat tr_imbudget.rfp_id.
        */
        $needsIMBudget = $this->needsIMBudgetFromRfpKontrakBudget($rfp->rfp_id);

        $existingIMBudget = TrIMBudget::query()
            ->where('rfp_id', $rfp->rfp_id)
            ->where('doctype', $doctype)
            ->where('status', '<>', 'X')
            ->orderByDesc('id')
            ->first();

        if ($existingIMBudget) {
            $statusIM = strtoupper(trim((string) ($existingIMBudget->status ?? '')));

            if (!in_array($statusIM, ['C', 'COMPLETED'], true)) {
                return response()->json([
                    'success' => false,
                    'code' => 'IM_IN_PROGRESS',
                    'message' => 'Masih On Progress IM Budget.',
                    'imbudgetid' => $existingIMBudget->imbudgetid,
                    'imbudget_show_url' => url('/showimbudgets/' . Hashids::encode($existingIMBudget->id)),
                ], 200);
            }
        }

        $imGenerateLevel = (float) (
            MsPurchSetting::query()
                ->where('setting_id', 'IMGENRFP')
                ->value('setting_value_int')          
            ?? 0
        );

        if ($needsIMBudget && !$existingIMBudget && $imGenerateLevel > 0) {
            $currentApproval = TrApproval::query()
                ->where('refnbr', $rfp->rfp_id)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->where(function ($q) use ($user) {
                    $q->where('aprv_username', $user->username)
                        ->orWhereRaw("? = ANY(string_to_array(REPLACE(aprv_username, ';', ','), ','))", [$user->username]);
                })
                ->orderBy('aprv_leveling')
                ->first();

            $currentLevel = (float) ($currentApproval->aprv_leveling ?? 0);

            if ($currentApproval && $currentLevel == $imGenerateLevel) {
                if (!$request->boolean('confirm_generate_im')) {
                    return response()->json([
                        'success' => false,
                        'need_confirm_generate_im' => true,
                        'message' => 'Dokumen ini membutuhkan IM Budget. Generate IM Budget sekarang?',
                    ], 200);
                }

                DB::connection('pgsql')->beginTransaction();

                try {
                    $imbudget = app(IMBudgetController::class)->generateIMBudgetFromRfp(
                        $rfp,
                        $user,
                        now()
                    );

                    DB::connection('pgsql')->commit();

                    return response()->json([
                        'success' => true,
                        'code' => 'IM_CREATED_HOLD',
                        'message' => 'IM Budget berhasil dibuat dan status approval di-HOLD.',
                        'imbudgetid' => $imbudget->imbudgetid,
                        'imbudget_show_url' => url('/showimbudgets/' . Hashids::encode($imbudget->id)),
                    ], 200);
                } catch (\Throwable $e) {
                    DB::connection('pgsql')->rollBack();
                    report($e);

                    return response()->json([
                        'success' => false,
                        'message' => config('app.debug')
                            ? $e->getMessage()
                            : 'Gagal generate IM Budget.',
                    ], 500);
                }
            }
        }

        $result = app(ApprovalController::class)->approveStep(
            $rfp->rfp_id,
            $doctype,
            $user->username,
            $user->name,

            // FINAL APPROVAL
            function (string $refnbr, \Carbon\Carbon $now) use (
                $rfp,
                $doctype,
                $docName,
                $fullname,
                $docUrl,
                $user
            ) {
                $rfp->status_receive = 'P';
                $rfp->status_payment = 'P';
                $rfp->status = 'C';
                $rfp->completed_by = $rfp->completed_by ?: $user->username;
                $rfp->completed_at = $now;
                $rfp->updated_by = $user->username;
                $rfp->save();

                $this->sendCompletedRfpEmail(
                    $rfp,
                    $doctype,
                    $docName,
                    $docUrl,
                    $fullname,
                    $now
                );
            },

            // NEXT APPROVER
            function ($next, \Carbon\Carbon $now) use (
                $rfp,
                $doctype,
                $docName,
                $docUrl,
                $user
            ) {
                $this->sendWaitingApprovalRfpEmail($next, $rfp, $doctype, $docName, $docUrl, $now);

                $rfp->completed_by = $user->username;
                $rfp->completed_at = $now;
                $rfp->updated_by = $user->username;
                $rfp->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success' => true, 'message' => $docName . ' approved successfully']);
    }

    private function needsIMBudgetFromRfpKontrakBudget(string $docid): bool
    {
        $details = TrRfpKontrakBudget::query()
            ->where('rfp_id', $docid)
            ->where('status', '<>', 'X')
            ->get();

        foreach ($details as $detail) {
            $amount = (float) ($detail->rfp_base_amount ?? 0);

            if ($amount <= 0) {
                continue;
            }

            $budget = BudgetDetail::query()
                ->where('perpost', $detail->budget_perpost)
                ->where('cpny_id', $detail->budget_cpny_id)
                ->where('status', 'C')
                ->when($detail->budget_business_unit_id, fn ($q) => $q->where('business_unit_id', $detail->budget_business_unit_id))
                ->when($detail->budget_department_fin_id, fn ($q) => $q->where('department_fin_id', $detail->budget_department_fin_id))
                ->when($detail->budget_account_id, fn ($q) => $q->where('account_id', $detail->budget_account_id))
                ->when($detail->budget_activity_descr, fn ($q) => $q->where('activity_descr', $detail->budget_activity_descr))
                ->when($detail->budget_activity_id, fn ($q) => $q->where('activity_id', $detail->budget_activity_id))
                ->first();

            if (!$budget) {
                return true;
            }

            $remain = ((float) ($budget->totalbudget ?? 0) + (float) ($budget->totalbudget_add ?? 0))
                - ((float) ($budget->total_reserve ?? 0) + (float) ($budget->total_used ?? 0));

            $availableBeforeCurrentReserve = max(0, $remain + $amount);

            if ($amount > $availableBeforeCurrentReserve) {
                return true;
            }
        }

        return false;
    }

    private function getApFinCcEmails(string $cpnyId): array
    {
        $ccUsernames = SysUserRole::query()
            ->where('role_id', 'APFINACCESS')
            ->where('status', 'A')
            ->pluck('username')
            ->filter()
            ->unique()
            ->values();

        return User::query()
            ->whereIn('username', $ccUsernames)
            ->whereRaw(
                "? = ANY(string_to_array(REPLACE(COALESCE(cpny_id, ''), ' ', ''), ','))",
                [trim($cpnyId)]
            )
            ->where('status', 'A')
            ->pluck('notification_email')
            ->filter(fn ($email) => trim((string) $email) !== '')
            ->unique()
            ->values()
            ->toArray();
    }

    private function sendWaitingApprovalRfpEmail($next, TrRfp $rfp, string $doctype, string $docName, string $docUrl, \Carbon\Carbon $now): void
    {
        $usernames = str_replace(';', ',', (string) $next->aprv_username);
        $approvers = array_filter(array_map('trim', explode(',', $usernames)));

        $toEmails = User::query()
            ->whereIn('username', $approvers)
            ->where('status', 'A')
            ->pluck('notification_email')
            ->filter(fn ($email) => trim((string) $email) !== '')
            ->unique()
            ->values()
            ->toArray();

        if (empty($toEmails)) {
            return;
        }

        $ccEmails = $this->getApFinCcEmails((string) $rfp->cpny_id);

        $mailData = [
            'docid'     => $rfp->rfp_id,
            'cpnyid'    => $rfp->cpny_id ?? '',
            'deptname'  => $rfp->department_id ?? '',
            'date'      => $now->toDateTimeString(),
            'name'      => $rfp->created_by,
            'status'    => 'P',
            'docname'   => $docName,
            'url'       => $docUrl,
            'info'      => $rfp->keperluan ?? '',
            'createdby' => $rfp->created_by,
        ];

        Mail::send('emails.mailapprovenew', $mailData, function ($message) use (
            $toEmails,
            $ccEmails,
            $rfp,
            $docName
        ) {
            $message->to($toEmails);

            if (!empty($ccEmails)) {
                $message->cc($ccEmails);
            }

            $message->subject($rfp->rfp_id . ' - WaitingApproval ' . $docName)
                ->from(config('mail.from.address'), config('app.name'));
        });
    }

    private function sendCompletedRfpEmail(
        TrRfp $rfp,
        string $doctype,
        string $docName,
        string $docUrl,
        string $fullname,
        \Carbon\Carbon $now
    ): void {
        $requesterEmail = User::query()
            ->where('username', $rfp->created_by)
            ->where('status', 'A')
            ->value('notification_email');

        $requesterEmail = trim((string) $requesterEmail);

        if ($requesterEmail === '') {
            return;
        }

        $ccEmails = $this->getApFinCcEmails((string) $rfp->cpny_id);

        $mailData = [
            'docid'     => $rfp->rfp_id,
            'cpnyid'    => $rfp->cpny_id ?? '',
            'deptname'  => $rfp->department_id ?? '',
            'date'      => $now->toDateTimeString(),
            'name'      => $fullname,
            'status'    => 'C',
            'docname'   => $docName,
            'url'       => $docUrl,
            'info'      => $rfp->keperluan ?? '',
            'createdby' => $fullname,
        ];

        $pdf = $this->buildRfpPdf($rfp);
        $pdfFilename = 'RFP_' . $rfp->rfp_id . '.pdf';

        Mail::send('emails.mailapprovehold', $mailData, function ($message) use (
            $requesterEmail,
            $ccEmails,
            $rfp,
            $docName,
            $pdf,
            $pdfFilename
        ) {
            $message->to($requesterEmail);

            if (!empty($ccEmails)) {
                $message->cc($ccEmails);
            }

            $message->subject($rfp->rfp_id . ' - Completed ' . $docName)
                ->from(config('mail.from.address'), config('app.name'))
                ->attachData($pdf->output(), $pdfFilename, [
                    'mime' => 'application/pdf',
                ]);
        });
    }

    private function buildRfpPdf(TrRfp $rfp)
    {
        $rfp->loadMissing(['creator:username,name']);

        $approval = TrApproval::where('refnbr', $rfp->rfp_id)
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->orderBy('id')
            ->get();

        if ($approval->isEmpty()) {
            $approval = TrApprovalHistory::where('refnbr', $rfp->rfp_id)
                ->where('status', '<>', 'X')
                ->orderBy('aprv_leveling')
                ->orderBy('id')
                ->get();
        }

        $rfp->rfp_date_fmt = $rfp->rfp_date
            ? Carbon::parse($rfp->rfp_date)->format('d M Y')
            : '-';

        $rfp->receive_date_fmt = $rfp->receive_date
            ? Carbon::parse($rfp->receive_date)->format('d M Y H:i')
            : '-';

        $rfp->payment_date_fmt = $rfp->payment_date
            ? Carbon::parse($rfp->payment_date)->format('d M Y H:i')
            : '-';

        $rfp->terbilang = trim($this->terbilang((int) $rfp->rfp_amount)) . ' Rupiah';

        $statusMap = [
            'P' => 'On Progress',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
            'X' => 'Cancel',
        ];

        $status_doc = $statusMap[$rfp->status] ?? 'Unknown';

        $approve_count = $approval->count();
        $created_by_name = $rfp->creator->name ?? null;
        $created_by_username = $rfp->created_by;
        $req_date_fmt = $rfp->created_at ? Carbon::parse($rfp->created_at)->format('d M Y H:i') : '-';
        $company = MsCompany::where('cpny_id', $rfp->cpny_id)->first();
        $cpny_name = $company->cpny_name ?? '';

        $pdf = PDF::loadView('pages.rfp.pdf_rfp', [
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

        return $pdf;
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $rfp = TrRfp::with('creator')->where('rfp_id', $docid)->first();

        if (!$rfp) {
            return response()->json([
                'success' => false,
                'message' => 'RP not found'
            ], 404);
        }

        $eid = Hashids::encode($rfp->id);
        $docUrl = url('/showrfp/' . $eid);
        $fullname = data_get($rfp, 'creator.name') ?: $rfp->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $rfp->rfp_id,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($request, $rfp, $fullname, $docUrl, $doctype, $user, $docid) {

                // =========================
                // 1) HEADER RP -> D
                // =========================
                $rfp->status = 'D';
                $rfp->completed_by = auth()->user()->username;
                $rfp->completed_at = $now;
                $rfp->updated_by = $user->username;
                $rfp->save();

                $this->reserveBudget(
                    $doctype,
                    $docid,
                    $request->cpnyid ?? $rfp->cpny_id,
                    'Revise',
                    $user->username
                );

                // =========================
                // 2) Email ke requester revise
                // =========================
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
                        'createdby' => $fullname,
                    ]
                );

                // =========================
                // 3) Simpan komentar revise
                // =========================
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($rfp->id, 'RP', $request);
                } catch (\Throwable $e) {
                    \Log::warning('[reviseRfp] sendmsg failed', [
                        'rfp_id' => $rfp->rfp_id,
                        'error' => $e->getMessage(),
                    ]);
                }

            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'RP revised successfully',
        ]);
    }


    

    public function reviseRfp_normal(Request $request, $docid)
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
            ->orderBy('id')
            ->get();

        // Jika tr_approval kosong, ambil dari tr_approval_history
        if ($approval->isEmpty()) {
            $approval = TrApprovalHistory::where('refnbr', $rfp->rfp_id)
                ->where('status', '<>', 'X')
                ->orderBy('aprv_leveling')
                ->orderBy('id')
                ->get();
        }

        // =========================
        // FORMAT DATE
        // =========================
        $rfp->rfp_date_fmt = $rfp->rfp_date
            ? \Carbon\Carbon::parse($rfp->rfp_date)->format('d M Y')
            : '-';

        $rfp->receive_date_fmt = $rfp->receive_date
            ? \Carbon\Carbon::parse($rfp->receive_date)->format('d M Y H:i')
            : '-';

        $rfp->payment_date_fmt = $rfp->payment_date
            ? \Carbon\Carbon::parse($rfp->payment_date)->format('d M Y H:i')
            : '-';

        // =========================
        // TERBILANG
        // =========================
        $rfp->terbilang = trim($this->terbilang((int) $rfp->rfp_amount)) . ' Rupiah';

        // =========================
        // STATUS DOC
        // =========================
        $status_doc = match ($rfp->status) {
            'P' => 'On Progress',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
            'X' => 'Cancel',
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

        $req_date_fmt = $rfp->created_at
            ? \Carbon\Carbon::parse($rfp->created_at)->format('d M Y H:i')
            : '-';

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
   
   
    public function printPdfRfp_xxx($hash)
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

    public function reminderRfp(Request $request, $hash)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $request->validate([
            'message' => ['required', 'string'],
        ]);

        $id = \Vinkla\Hashids\Facades\Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid RFP ID.',
            ], 404);
        }

        $rfp = \App\Models\TrRfp::query()
            ->where('id', $id)
            ->first();

        if (!$rfp) {
            return response()->json([
                'success' => false,
                'message' => 'RFP not found.',
            ], 404);
        }

        $doctype = 'RP';

        try {
            $request->merge([
                'docid' => $rfp->rfp_id,
                'doc_no' => $rfp->rfp_id,
                'comment' => $request->message,
                'reason' => $request->message,
            ]);

            app(\App\Http\Controllers\SendCommentController::class)
                ->sendmsg((int) $rfp->id, $doctype, $request);

            return response()->json([
                'success' => true,
                'message' => 'Reminder message sent successfully.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder message.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function financeReviseRfp(Request $request, $hash)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $request->validate([
            'message' => ['required', 'string'],
        ]);

        $id = \Vinkla\Hashids\Facades\Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid RFP ID.',
            ], 404);
        }

        \Illuminate\Support\Facades\DB::connection('pgsql')->beginTransaction();
        \Illuminate\Support\Facades\DB::connection('pgsql2')->beginTransaction();

        try {
            $rfp = \App\Models\TrRfp::query()
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$rfp) {
                \Illuminate\Support\Facades\DB::connection('pgsql')->rollBack();
                \Illuminate\Support\Facades\DB::connection('pgsql2')->rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'RFP not found.',
                ], 404);
            }

            $doctype = 'RP';

            /*
            |--------------------------------------------------------------------------
            | Update RFP status menjadi Revise
            |--------------------------------------------------------------------------
            */
            $rfp->status = 'D';
            $rfp->updated_by = $user->username;
            $rfp->updated_at = now();
            $rfp->completed_by = $user->username;
            $rfp->completed_at = now();
            $rfp->save();

            /*
            |--------------------------------------------------------------------------
            | Insert approval row sebagai log revise finance
            |--------------------------------------------------------------------------
            */
            $lastApproval = \App\Models\TrApproval::query()
                ->where('refnbr', $rfp->rfp_id)
                ->where('aprv_doctype', $doctype)
                ->where('status', '<>', 'X')
                ->orderByDesc('id')
                ->first();

            \App\Models\TrApproval::create([
                'refnbr' => $rfp->rfp_id,
                'aprv_leveling' => $lastApproval->aprv_leveling ?? 0,
                'aprv_doctype' => $doctype,
                'aprv_cpnyid' => $rfp->cpny_id,
                'aprv_departementid' => $rfp->department_id,
                'aprv_username' => $user->username,
                'aprv_name' => $user->name ?? $user->username,
                'aprv_datebefore' => now(),
                'aprv_dateafter' => now(),
                'aprv_type' => $lastApproval->aprv_type ?? null,
                'aprv_condition' => $lastApproval->aprv_condition ?? null,
                'aprv_start_nominal' => $lastApproval->aprv_start_nominal ?? null,
                'aprv_end_nominal' => $lastApproval->aprv_end_nominal ?? null,
                'aprv_duration' => $lastApproval->aprv_duration ?? null,
                'aprv_purpose' => $request->message,
                'status' => 'D',
                'created_by' => $rfp->created_by,
                'updated_by' => $user->username,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Save comment/message
            |--------------------------------------------------------------------------
            */
            $request->merge([
                'docid' => $rfp->rfp_id,
                'doc_no' => $rfp->rfp_id,
                'comment' => $request->message,
                'reason' => $request->message,
            ]);

            app(\App\Http\Controllers\SendCommentController::class)
                ->sendmsg((int) $rfp->id, $doctype, $request);

            \Illuminate\Support\Facades\DB::connection('pgsql2')->commit();
            \Illuminate\Support\Facades\DB::connection('pgsql')->commit();

            return response()->json([
                'success' => true,
                'message' => 'RFP revised successfully.',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::connection('pgsql')->rollBack();
            \Illuminate\Support\Facades\DB::connection('pgsql2')->rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revise RFP.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function reserveBudget(string $doctype, string $docid, string $cpnyId, string $activity, string $username): void
    {
        // Panggil PostgreSQL Stored Procedure: sp_process_budget(doctype, docid, activity, user)
        // Contoh: CALL sp_process_budget('CS','CS25120001','Submit','williemhalim');
        DB::connection('pgsql')->statement(
            'CALL public.sp_process_budget(?, ?, ?, ?,?)',
            [strtoupper($doctype), $docid, $cpnyId, $activity, $username]
        );
    }


}
