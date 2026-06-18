<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\MsCompany;
// use App\Models\TrApproval;
use App\Models\MsApprovalGroupBiaya;
use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrRfpNonPurch;
use App\Models\TrRfpNonPurchDetail;
use App\Models\SysUserRole;
use App\Models\MsGroupbiayaNonPurch;
use App\Models\BusinessUnit;
use App\Models\TrRfpNonPurchDeposit;
use App\Models\TrPO;
use App\Models\TrCS;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrRfpNonPurchStaging;
use App\Models\TrRfpNonPurchStagingAttachment;
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
use App\Models\BudgetDetail;
use App\Models\MsPurchSetting;
use App\Models\TrIMBudget;

class RfpNonPurchController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        $baseQuery = TrRfpNonPurch::query()
            ->whereIn('cpny_id', $cpnyIds)
            ->whereIn('department_id', $deptIds);

        $all        = (clone $baseQuery)->count();
        $onProgress = (clone $baseQuery)->where('status', 'P')->count();
        $reject     = (clone $baseQuery)->where('status', 'R')->count();
        $revise     = (clone $baseQuery)->where('status', 'D')->count();
        $completed  = (clone $baseQuery)->where('status', 'C')->count();

        $hasRfpAllAccess = $user->hasRole('FINACCESS');
        $hasApFinAccess  = $user->hasRole('APFINACCESS');
        $hasApTreAccess  = $user->hasRole('APTREACCESS');

        $rfpAll = 0;
        if ($hasRfpAllAccess) {
            $rfpAll = TrRfpNonPurch::whereIn('cpny_id', $cpnyIds)
                ->where('status', 'C')
                ->count();
        }

        // 🔥 TETAP DIPAKAI
        $financeReceived = (clone $baseQuery)
            ->where('status', 'C')
            ->where('statusreceive', 'C')
            ->where(function ($q) {
                $q->whereNull('statuspayment')
                ->orWhere('statuspayment', 'P');
            })
            ->count();

        $treasuryReceived = (clone $baseQuery)
            ->where('status', 'C')
            ->where('statusreceive', 'C')
            ->where('statuspayment', 'C')
            ->count();

        return view('pages.rfpnonpurch.rfpnonpurch', compact(
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

        $cpnyIds = is_string($user->cpny_id)
            ? array_filter(array_map('trim', explode(',', $user->cpny_id)))
            : (array) $user->cpny_id;

        $deptIds = is_string($user->department_id)
            ? array_filter(array_map('trim', explode(',', $user->department_id)))
            : (array) $user->department_id;

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');
        $scope  = (string) $request->query('scope', '');

        $base = TrRfpNonPurch::from('tr_rfp_nonpurchase as r')
            ->leftJoin('ms_groupbiaya_nonpurchase as g', 'r.groupbiaya_id', '=', 'g.groupbiaya_id')
            ->whereIn('r.cpny_id', $cpnyIds)
            ->when(
                $scope !== 'rfp_all',
                fn ($q) => $q->whereIn('r.department_id', $deptIds)
            )

            // 🔥 FINANCE FLOW TETAP
            ->when($scope === 'finance_received', function ($q) {
                $q->where('r.status', 'C')
                ->where('r.statusreceive', 'C')
                ->where(function ($q2) {
                    $q2->whereNull('r.statuspayment')
                        ->orWhere('r.statuspayment', 'P');
                });
            })

            ->when($scope === 'treasury_received', function ($q) {
                $q->where('r.status', 'C')
                ->where('r.statusreceive', 'C')
                ->where('r.statuspayment', 'C');
            })

            ->when(
                $scope === 'rfp_all',
                fn ($q) => $q->where('r.status', 'C')
            );

        if ($status !== '') {
            $base->where('r.status', $status);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('r.rfpnonpurchaseid', 'ilike', "%{$search}%")
                ->orWhere('r.cpny_id', 'ilike', "%{$search}%")
                ->orWhere('r.department_id', 'ilike', "%{$search}%")
                ->orWhere('r.user_peminta', 'ilike', "%{$search}%")
                ->orWhere('g.groupbiayadescr', 'ilike', "%{$search}%")
                ->orWhere('r.pleasepayto', 'ilike', "%{$search}%")
                ->orWhere('r.keperluan', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
                'r.id',
                'r.rfpnonpurchaseid',
                'r.datediperlukan',
                'r.cpny_id',
                'r.department_id',
                'r.user_peminta',
                'g.groupbiayadescr',
                'r.pleasepayto',
                'r.keperluan',
                'r.amountrequestpayment',
                'r.status',
                'r.statusreceive',
                'r.userreceive',
                'r.receivedate',
                'r.statuspayment',
                'r.userpayment',
                'r.paymentdate',
                'r.created_by'
            )
            ->orderBy('r.rfpnonpurchaseid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($row) {

            // 🔥 FINANCE FLOW TEXT (TETAP DIPAKAI)
            $sr = strtoupper(trim($row->statusreceive ?? 'P'));
            $sp = strtoupper(trim($row->statuspayment ?? 'P'));

            if ($sr === 'P' && $sp === 'P') {
                $row->finance_flow_status_text = 'Waiting User';
            } elseif ($sr === 'C' && $sp === 'P') {
                $row->finance_flow_status_text = 'Finance Received';
            } elseif ($sr === 'C' && $sp === 'C') {
                $row->finance_flow_status_text = 'Treasury Received';
            } else {
                $row->finance_flow_status_text = 'Waiting User';
            }

            $row->action_state = ($sr === 'C') ? 'treasury' : 'received';

            $row->receive_button_text = !empty($row->userreceive) ? 'Rollback' : 'Update Received';
            $row->treasury_button_text = !empty($row->userpayment) ? 'Rollback' : 'Update Treasury';

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

    public function createRfpNonPurch()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $usercpny = Usercpny::where('username', $user->username)
            ->get();

        $usercpny2 = Usercpny::where('username', $user->username)
            ->first();
        $userdept = Userdept::where('username', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', $user->username)
            ->first();

        $akses_stock = SysUserRole::where('username', $user->username)
            ->where('role_id', 'WHSACCESS')
            ->first();

        $groupbiaya = MsGroupbiayaNonPurch::where('status', 'A')
            ->orderBy('groupbiayadescr')
            ->get();

        $kepada = User::query()
            ->whereNotNull('username')
            ->where('status', 'A')
            ->select('username', 'name')
            ->orderBy('name')
            ->get();

        $tembusan = User::query()
            ->whereNotNull('username')
            ->where('status', 'A')
            ->select('username', 'name')
            ->orderBy('name')
            ->get();

        return view('pages.rfpnonpurch.createrfpnonpurch', compact('usercpny', 'usercpny2', 'userdept', 'userdept2', 'akses_stock', 'groupbiaya', 'kepada', 'tembusan'));
    }
    
    public function storeRfpNonPurch(Request $request)
    {
      
        $user = $request->user();
        $username = $user->username ?? 'system';

        $dt = now();
        $year = $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        $doctype = strtoupper($request->rfpnonpurchase_type ?? '');

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            return response()->json([
                'message' => 'Type Payment tidak valid. Pilih RFP atau RCA.',
            ], 422);
        }

        $docName = $doctype === 'RCA'
            ? 'RCA Non Purchase'
            : 'RFP Non Purchase';

        $toFloat = function ($v): float {
            if ($v === null || $v === '') return 0;

            $s = trim((string) $v);
            $s = preg_replace('/\s+/', '', $s);

            $hasComma = str_contains($s, ',');
            $hasDot = str_contains($s, '.');

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');

                if ($lastComma > $lastDot) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot && substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            }

            return is_numeric($s) ? (float) $s : 0;
        };

        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        DB::beginTransaction();

        try {
            // =========================
            // GENERATE DOC ID
            // =========================
            $auto = $this->nextAutonbr($doctype, $year, $month, $username, $docName);
            $docid = $doctype . substr($year, 2) . $month . sprintf('%03d', $auto['next']);

            // =========================
            // HEADER
            // =========================
            $header = new TrRfpNonPurch();
            $header->rfpnonpurchaseid = $docid;
            $header->rfpnonpurchasedate = $dt->toDateString();

            $header->cpny_id = $request->cpnyid;
            $header->department_id = $request->departementid;
            $header->user_peminta = $username;

            $header->rfpnonpurchase_type = $doctype;
            $header->groupbiaya_id = $request->groupbiaya_id;

            $header->datediperlukan = $request->datediperlukan;
            $header->datepenyelesaian = $doctype === 'RCA'
                ? $request->datepenyelesaian
                : null;

            $header->pleasepayto = $request->pleasepayto;
            $header->keperluan = $request->keperluan;
           
            $kepada = $request->input('rfpnonpurchase_kepada', []);
            $tembusan = $request->input('rfpnonpurchase_tembusan', []);

            $header->imnonpurchase_kepada = is_array($kepada)
                ? implode(',', array_filter($kepada))
                : $kepada;

            $header->imnonpurchase_tembusan = is_array($tembusan)
                ? implode(',', array_filter($tembusan))
                : $tembusan;

            $header->amountrequestpayment = $doctype === 'RCA'
                ? $toFloat($request->amountrequestpayment)
                : 0;

            $header->status = 'P';
            $header->created_by = $username;
            $header->save();

            // =========================
            // DEPOSIT
            // =========================
            $isDeposit = MsGroupbiayaNonPurch::query()
                ->where('groupbiaya_id', $request->groupbiaya_id)
                ->where('status', 'A')
                ->where(function ($q) {
                    $q->where('is_deposit', true)
                    ->orWhere('is_deposit', 't')
                    ->orWhere('is_deposit', 1);
                })
                ->exists();

            if ($isDeposit) {

                TrRfpNonPurchDeposit::create([
                    'rfpnonpurchaseid' => $docid,
                    'cpny_id'          => $request->cpnyid,

                    'custid'           => $request->custid,
                    'customername'     => $request->customername,
                    'storename'        => $request->storename,
                    'unitid'           => $request->unitid,
                    'transferto'       => $request->transferto,
                    'bankname'         => $request->bankname,
                    'bankacct'         => $request->bankacct,

                    'status'           => 'A',
                    'created_by'       => $username,
                ]);
            }

            // // =========================
            // // DETAIL ONLY RFP
            // // =========================
            // $totalAmountRequest = 0;

            // if ($doctype === 'RFP') {
            //     $descs = $request->rfpnonpurchase_descr ?? [];
            //     $prices = $request->price ?? [];

            //     $coaIds = $request->coa_id ?? [];
            //     $activityIds = $request->activity_id ?? [];
            //     $busUnitIds = $request->business_unit_id_detail ?? [];
            //     $deptFinIds = $request->department_fin_id ?? [];
            //     $actDescrs = $request->activity_descr ?? [];

            //     $rowCount = count($descs);

            //     for ($i = 0; $i < $rowCount; $i++) {
            //         $desc = trim($descs[$i] ?? '');
            //         $amount = $toFloat($prices[$i] ?? 0);

            //         if (!$desc || $amount <= 0) {
            //             continue;
            //         }

            //         $totalAmountRequest += $amount;

            //         TrRfpNonPurchDetail::create([
            //             'rfpnonpurchaseid' => $docid,
            //             'keperluan_detail' => $desc,
            //             'amount_request' => $amount,

            //             'budget_perpost' => $year,
            //             'budget_cpny_id' => $request->cpnyid,
            //             'budget_business_unit_id' => $busUnitIds[$i] ?? null,
            //             'budget_department_fin_id' => $deptFinIds[$i] ?? null,
            //             'budget_account_id' => $coaIds[$i] ?? null,
            //             'budget_activity_id' => $activityIds[$i] ?? null,
            //             'budget_activity_descr' => $actDescrs[$i] ?? null,

            //             'status' => 'P',
            //             'created_by' => $username,
            //         ]);
            //     }

            //     $header->amountrequestpayment = $totalAmountRequest;
            //     $header->save();
            // }

            // if ($doctype === 'RCA') {
            //     $header->amountrequestpayment = $toFloat($request->amountrequestpayment);
            //     $header->save();
            // }

            // =========================
            // DETAIL RFP / RCA
            // =========================
            $totalAmountRequest = 0;

            $descs = $request->rfpnonpurchase_descr ?? [];
            $prices = $request->price ?? [];

            $coaIds = $request->coa_id ?? [];
            $activityIds = $request->activity_id ?? [];
            $busUnitIds = $request->business_unit_id_detail ?? [];
            $deptFinIds = $request->department_fin_id ?? [];
            $actDescrs = $request->activity_descr ?? [];

            $rowCount = count($prices);
            $insertedDetail = 0;

            for ($i = 0; $i < $rowCount; $i++) {
                $desc = trim((string) ($descs[$i] ?? ''));
                $amount = $toFloat($prices[$i] ?? 0);

                if ($amount <= 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Khusus RFP, description wajib.
                | Khusus RCA, keperluan_detail dibuat null.
                |--------------------------------------------------------------------------
                */
                if ($doctype === 'RFP' && $desc === '') {
                    continue;
                }

                $totalAmountRequest += $amount;
                $insertedDetail++;

                TrRfpNonPurchDetail::create([
                    'rfpnonpurchaseid' => $docid,

                    // RFP simpan description, RCA null
                    'keperluan_detail' => $doctype === 'RCA'
                        ? null
                        : $desc,

                    // RCA isi refid BUDGET-RFCA
                    'refid' => $doctype === 'RCA'
                        ? 'BUDGET-RFCA'
                        : null,

                    'amount_request' => $amount,

                    'budget_perpost' => $year,
                    'budget_cpny_id' => $request->cpnyid,
                    'budget_business_unit_id' => $busUnitIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_account_id' => $coaIds[$i] ?? null,
                    'budget_activity_id' => $activityIds[$i] ?? null,
                    'budget_activity_descr' => $actDescrs[$i] ?? null,

                    'status' => 'P',
                    'created_by' => $username,
                ]);
            }

            if ($insertedDetail <= 0) {
                DB::rollBack();

                return response()->json([
                    'message' => "Minimal 1 detail {$doctype} harus diisi.",
                ], 422);
            }

            // Total header dari detail, baik RFP maupun RCA
            $header->amountrequestpayment = $totalAmountRequest;
            $header->save();

            // =========================
            // APPROVAL
            // =========================
            $ctx = [
                'ignore_nominal' => false,
                'grand_total' => (float) $header->amountrequestpayment,
            ];
            
            [$firstApprovalUsernames] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

           
            // =========================
            // APPROVAL GROUP BIAYA
            // ADD / DEL approval berdasarkan groupbiaya_id
            // =========================
            $groupApprovalRules = MsApprovalGroupBiaya::query()
                ->where('aprv_doctype', $doctype)
                ->where('aprv_cpnyid', $request->cpnyid)
                ->where('aprv_departementid', $request->departementid)
                ->where('aprv_groupbiaya', $request->groupbiaya_id)
                ->where('status', 'A')
                ->get();

            foreach ($groupApprovalRules as $rule) {
                $condition = strtoupper(trim($rule->aprv_typecondition ?? ''));

                if ($condition === 'DEL') {
                    TrApproval::query()
                        ->where('refnbr', $docid)
                        ->where('aprv_doctype', $doctype)
                        ->where('aprv_cpnyid', $request->cpnyid)
                        ->where('aprv_departementid', $request->departementid)
                        ->where('aprv_leveling', $rule->aprv_leveling)
                        // ->where('aprv_username', $rule->aprv_username)
                        ->where('status', 'P')
                        ->delete();
                }

                if ($condition === 'ADD') {
                    $exists = TrApproval::query()
                        ->where('refnbr', $docid)
                        ->where('aprv_doctype', $doctype)
                        ->where('aprv_cpnyid', $request->cpnyid)
                        ->where('aprv_departementid', $request->departementid)
                        ->where('aprv_leveling', $rule->aprv_leveling)
                        // ->where('aprv_username', $rule->aprv_username)
                        ->exists();

                    if (!$exists) {
                        TrApproval::create([
                            'refnbr'              => $docid,
                            'aprv_leveling'       => $rule->aprv_leveling,
                            'aprv_doctype'        => $doctype,
                            'aprv_cpnyid'         => $request->cpnyid,
                            'aprv_departementid'  => $request->departementid,
                            'aprv_username'       => $rule->aprv_username,
                            'aprv_name'           => $rule->aprv_name,
                            'aprv_datebefore'     => $dt,
                            'aprv_type'           => 'Normal',
                            'status'              => 'P',
                            'created_by'          => $username,
                        ]);
                    }
                }
            }

            // Ambil ulang first approval setelah ADD / DEL
            $firstPendingAfterGroup = TrApproval::query()
                ->where('refnbr', $docid)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->orderByRaw('CAST(aprv_leveling AS DECIMAL(10,2)) ASC')
                ->first();

            if ($firstPendingAfterGroup) {
                $header->completed_by = $firstPendingAfterGroup->aprv_username;
                $header->completed_at = $dt;
                $header->save();
            }
           
            // =========================
            // ATTACHMENT
            // =========================
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->cpnyid,
                    'departementid' => $request->departementid,
                    'base_folder' => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by' => $username,
                ];

                try {
                    app(TrAttachmentController::class)
                        ->uploadInternal($meta, (array) $request->file('attachments'));
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Failed upload attachment',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }         

            // =========================
            // EMAIL NOTIFICATION (CUSTOM)
            // =========================
            $firstPending = TrApproval::query()
                ->where('refnbr', $docid)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->orderByRaw('CAST(aprv_leveling AS DECIMAL(10,2)) ASC')
                ->first();

            if ($firstPending) {

                // =========================
                // APPROVER EMAIL
                // =========================
                $usernames = str_replace(';', ',', (string)$firstPending->aprv_username);
                $approvers = array_filter(array_map('trim', explode(',', $usernames)));

                $approverEmails = User::query()
                    ->whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                // =========================
                // KEPADA (TO tambahan)
                // =========================
                $kepadaUsers = explode(',', (string)$header->imnonpurchase_kepada);

                $kepadaEmails = User::query()
                    ->whereIn('username', $kepadaUsers)
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                // =========================
                // TEMBUSAN (CC)
                // =========================
                $tembusanUsers = explode(',', (string)$header->imnonpurchase_tembusan);

                $ccEmails = User::query()
                    ->whereIn('username', $tembusanUsers)
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                // =========================
                // MERGE EMAIL
                // =========================
                $toEmails = array_unique(array_merge($approverEmails, $kepadaEmails));

                // =========================
                // EMAIL DATA
                // =========================
                $eid = Hashids::encode($header->id);

                $mailData = [
                    'docid'     => $docid,
                    'cpnyid'    => $header->cpny_id,
                    'deptname'  => $header->department_id,
                    'date'      => $dt->toDateTimeString(),
                    'name'      => $username,
                    'status'    => $header->status,
                    'docname'   => $docName,
                    'url'       => url('/showrfpnonpurch/' . $eid),
                    'info'      => $header->keperluan,
                    'createdby' => $username,
                ];

                // =========================
                // SEND EMAIL
                // =========================
                if (!empty($toEmails)) {
                    Mail::send('emails.mailapprovenew', $mailData, function ($message) use ($toEmails, $ccEmails, $docid, $docName) {

                        $message->to($toEmails);

                        if (!empty($ccEmails)) {
                            $message->cc($ccEmails);
                        }

                        $message->subject($docid . ' - WaitingApproval ' . $docName)
                            ->from(config('mail.from.address'), config('app.name'));
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message' => $docName . ' created successfully',
                'docid' => $docid,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create ' . $docName,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function showRfpNonPurch($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $rfpnonpurch = TrRfpNonPurch::query()
            ->leftJoin('ms_groupbiaya_nonpurchase as gb', 'gb.groupbiaya_id', '=', 'tr_rfp_nonpurchase.groupbiaya_id')
            ->with(['creator:username,name'])
            ->where('tr_rfp_nonpurchase.id', $id)
            ->select([
                'tr_rfp_nonpurchase.*',
                'gb.groupbiayadescr as groupbiaya_descr'
            ])
            ->firstOrFail();

        $doctype = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            $doctype = 'RFP';
        }

        $docid = $rfpnonpurch->rfpnonpurchaseid;

        // =========================
        // IM BUDGET LINK
        // =========================
        $imbudgetUrl = null;
        $imbudgetHash = null;

        if (!empty($rfpnonpurch->imbudgetid)) {
            $imbudget = TrIMBudget::query()
                ->where('imbudgetid', $rfpnonpurch->imbudgetid)
                ->first();

            if ($imbudget) {
                $imbudgetHash = Hashids::encode($imbudget->id);
                $imbudgetUrl = url('/showimbudgets/' . $imbudgetHash);
            }
        }

        // =========================
        // DETAIL ONLY RFP
        // =========================
        $details = collect();

        // if ($doctype === 'RFP') {
            // $details = TrRfpNonPurchDetail::query()
            //     ->where('rfpnonpurchaseid', $docid)
            //     ->orderBy('id')
            //     ->get();
            $detailsQuery = TrRfpNonPurchDetail::query()
                ->where('rfpnonpurchaseid', $docid);

            if ($doctype === 'RCA') {
                $detailsQuery->where('refid', 'BUDGET-RFCA');
            }

            $details = $detailsQuery
                ->orderBy('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Mapping Budget Detail
            |--------------------------------------------------------------------------
            | Dipakai untuk tooltip budget di view:
            | totalbudget, totalbudget_add, total_reserve, total_used, account_descr
            */
            $budgets = BudgetDetail::leftJoin('ms_coa', function ($join) {
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

            foreach ($budgets as $b) {
                $key = implode('|', [
                    (string) $b->cpny_id,
                    (string) $b->business_unit_id,
                    (string) $b->department_fin_id,
                    (string) $b->account_id,
                    (string) $b->activity_descr,
                    (string) $b->perpost,
                ]);

                $budgetMap[$key] = $b;
            }

            foreach ($details as $item) {
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
                    $additional  = (float) ($budget->totalbudget_add ?? 0);
                    $reserved    = (float) ($budget->total_reserve ?? 0);
                    $used        = (float) ($budget->total_used ?? 0);

                    $item->budget_remaining = $budgetValue + $additional - $reserved - $used;
                } else {
                    $item->budget_data = null;
                    $item->account_descr = null;
                    $item->budget_remaining = 0;
                }
            }
        // }

        // =========================
        // ATTACHMENTS
        // =========================
        $rows = TrAttachment::where('refnbr', $docid)
            ->where('doctype', $doctype)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $attachments = collect();

        if ($rows->isNotEmpty()) {
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
        }

        $stagingAttachments = collect();

        // =========================
        // USER ACCESS
        // =========================
        $canUpload = $rfpnonpurch->status === 'P';

        $userdept = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // =========================
        // PROGRESS STEPS
        // =========================
        $rfpnonpurchSteps = collect();

        $rfpnonpurchSteps->push([
            'order' => 1,
            'description' => $doctype . ' Created',
            'user' => $rfpnonpurch->created_by ?: '-',
            'date' => $rfpnonpurch->created_at,
            'status' => 'Done',
        ]);

        $rfpnonpurchSteps->push([
            'order' => 2,
            'description' => 'Approval Process',
            'user' => $rfpnonpurch->completed_by ?: '-',
            'date' => $rfpnonpurch->completed_at,
            'status' => $rfpnonpurch->status === 'P' ? 'Pending' : 'Done',
        ]);

        $rfpnonpurchSteps->push([
            'order' => 3,
            'description' => 'Finance Received',
            'user' => $rfpnonpurch->userreceive ?: '-',
            'date' => $rfpnonpurch->receivedate,
            'status' => $rfpnonpurch->statusreceive === 'C' ? 'Done' : 'Pending',
        ]);

        $rfpnonpurchSteps->push([
            'order' => 4,
            'description' => 'Treasury Payment',
            'user' => $rfpnonpurch->userpayment ?: '-',
            'date' => $rfpnonpurch->paymentdate,
            'status' => $rfpnonpurch->statuspayment === 'C' ? 'Done' : 'Pending',
        ]);

        // =========================
        // DEPOSIT
        // =========================
        $deposit = TrRfpNonPurchDeposit::query()
            ->where('rfpnonpurchaseid', $docid)
            ->first();

        return view('pages.rfpnonpurch.showrfpnonpurch', compact(
            'rfpnonpurch',
            'details',
            'attachments',
            'stagingAttachments',
            'hash',
            'canUpload',
            'userdept',
            'userdept2',
            'rfpnonpurchSteps',
            'deposit',
            'imbudgetUrl',
            'imbudgetHash'
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

        $rfpnonpurch = TrRfpNonPurch::findOrFail($id);

        $updatedBy = $user->username ?? $user->name;
        $now = now();

        // Jika sudah receive, maka rollback
        if (!empty($rfpnonpurch->userreceive) && !empty($rfpnonpurch->receivedate)) {
            $rfpnonpurch->userreceive = null;
            $rfpnonpurch->receivedate = null;
            $rfpnonpurch->statusreceive = 'P';
            $rfpnonpurch->updated_by = $updatedBy;
            $rfpnonpurch->updated_at = $now;
            $rfpnonpurch->save();

            return response()->json([
                'success' => true,
                'message' => 'Receive rollback successfully.',
                'data' => [
                    'userreceive' => null,
                    'receivedate' => null,
                    'statusreceive' => 'P',
                ],
            ]);
        }

        // Jika belum receive, maka update receive
        $rfpnonpurch->userreceive = $updatedBy;
        $rfpnonpurch->receivedate = $now;
        $rfpnonpurch->statusreceive = 'C';
        $rfpnonpurch->updated_by = $updatedBy;
        $rfpnonpurch->updated_at = $now;
        $rfpnonpurch->save();

        return response()->json([
            'success' => true,
            'message' => 'Receive updated successfully.',
            'data' => [
                'userreceive' => $rfpnonpurch->userreceive,
                'receivedate' => optional($rfpnonpurch->receivedate)->toDateTimeString(),
                'statusreceive' => $rfpnonpurch->statusreceive,
            ],
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

        $rfpnonpurch = TrRfpNonPurch::findOrFail($id);

        $updatedBy = $user->username ?? $user->name;
        $now = now();

        // Treasury hanya boleh kalau Finance Receive sudah complete,
        // kecuali sedang rollback treasury yang sudah pernah payment.
        $hasTreasuryPayment = !empty($rfpnonpurch->userpayment) && !empty($rfpnonpurch->paymentdate);

        if ($rfpnonpurch->statusreceive !== 'C' && !$hasTreasuryPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Finance receive belum completed.'
            ], 422);
        }

        // Jika sudah payment, maka rollback
        if ($hasTreasuryPayment) {
            $rfpnonpurch->userpayment = null;
            $rfpnonpurch->paymentdate = null;
            $rfpnonpurch->statuspayment = 'P';
            $rfpnonpurch->updated_by = $updatedBy;
            $rfpnonpurch->updated_at = $now;
            $rfpnonpurch->save();

            return response()->json([
                'success' => true,
                'message' => 'Treasury rollback successfully.',
                'data' => [
                    'userpayment' => null,
                    'paymentdate' => null,
                    'statuspayment' => 'P',
                ],
            ]);
        }

        // Jika belum payment, maka update treasury
        $rfpnonpurch->userpayment = $updatedBy;
        $rfpnonpurch->paymentdate = $now;
        $rfpnonpurch->statuspayment = 'C';
        $rfpnonpurch->updated_by = $updatedBy;
        $rfpnonpurch->updated_at = $now;
        $rfpnonpurch->save();

        return response()->json([
            'success' => true,
            'message' => 'Treasury updated successfully.',
            'data' => [
                'userpayment' => $rfpnonpurch->userpayment,
                'paymentdate' => optional($rfpnonpurch->paymentdate)->toDateTimeString(),
                'statuspayment' => $rfpnonpurch->statuspayment,
            ],
        ]);
    }
    public function approveRfpNonPurch(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $rfpnonpurch = TrRfpNonPurch::with('creator')
            ->where('rfpnonpurchaseid', $docid)
            ->first();

        if (!$rfpnonpurch) {
            return response()->json([
                'success' => false,
                'message' => 'RFP/RCA Non Purchase not found',
            ], 404);
        }

        $doctype = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document type',
            ], 422);
        }

        $docName = $doctype . ' Non Purchase';

        $eid = Hashids::encode($rfpnonpurch->id);
        $docUrl = url('/showrfpnonpurch/' . $eid);

        $fullname = optional($rfpnonpurch->creator)->name
            ?: $rfpnonpurch->created_by;

        /*
        |--------------------------------------------------------------------------
        | CHECK IM BUDGET BEFORE APPROVE
        |--------------------------------------------------------------------------
        | Rule:
        | - Cek flag_imbudget = true
        | - Cek setting IMGENRFPNP
        | - Generate IM Budget saat approval level = setting_value_int
        */
        $flagIMBudget = $this->isTruthy($rfpnonpurch->flag_imbudget ?? false);

        $imGenerateLevel = (float) (MsPurchSetting::query()
            ->where('setting_id', 'IMGENRFPNP')
            ->value('setting_value_int') ?? 0);

        if ($flagIMBudget && $imGenerateLevel > 0) {

            $currentApproval = TrApproval::query()
                ->where('refnbr', $rfpnonpurch->rfpnonpurchaseid)
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

                $statusIM = strtoupper(trim((string) ($rfpnonpurch->status_imbudget ?? '')));
                $imbudgetId = trim((string) ($rfpnonpurch->imbudgetid ?? ''));

                /*
                |--------------------------------------------------------------------------
                | Kalau IM masih on progress, approve diblok dulu
                |--------------------------------------------------------------------------
                */
                if ($imbudgetId !== '' && !in_array($statusIM, ['C', 'COMPLETED'], true)) {
                    return response()->json([
                        'success' => false,
                        'code' => 'IM_IN_PROGRESS',
                        'message' => 'Masih On Progress IM Budget.',
                    ], 200);
                }

                /*
                |--------------------------------------------------------------------------
                | Kalau belum ada IM, minta confirm dari user
                |--------------------------------------------------------------------------
                */
                if ($imbudgetId === '' && !$request->boolean('confirm_generate_im')) {
                    return response()->json([
                        'success' => false,
                        'need_confirm_generate_im' => true,
                        'message' => 'Dokumen ini membutuhkan IM Budget. Generate IM Budget sekarang?',
                    ], 200);
                }

                /*
                |--------------------------------------------------------------------------
                | Kalau user confirm, generate IM Budget lalu HOLD approval
                |--------------------------------------------------------------------------
                */
                if ($imbudgetId === '' && $request->boolean('confirm_generate_im')) {

                    DB::connection('pgsql')->beginTransaction();

                    try {
                        /*
                        |--------------------------------------------------------------------------
                        | PANGGIL FUNCTION GENERATE IM BUDGET MILIK KAMU DI SINI
                        |--------------------------------------------------------------------------
                        | Sesuaikan nama function generate-nya.
                        | Function ini idealnya return object TrIMBudget.
                        */
                        // $imbudget = $this->generateIMBudgetFromRfpNonPurch(
                        //     $rfpnonpurch,
                        //     $user,
                        //     now()
                        // );
                        $imbudget = app(IMBudgetController::class)->generateIMBudgetFromRfpNonPurch(
                            $rfpnonpurch,
                            $user,
                            now()
                        );

                        $rfpnonpurch->imbudgetid = $imbudget->imbudgetid;
                        $rfpnonpurch->status_imbudget = 'H';
                        $rfpnonpurch->updated_by = $user->username;
                        $rfpnonpurch->updated_at = now();
                        $rfpnonpurch->save();

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
        }

        $result = app(ApprovalController::class)->approveStep(
            $rfpnonpurch->rfpnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            // ====================================
            // FINAL APPROVAL
            // ====================================
            function (string $refnbr, \Carbon\Carbon $now) use (
                $rfpnonpurch,
                $doctype,
                $docName,
                $fullname,
                $docUrl,
                $user
            ) {
                $rfpnonpurch->statusreceive = 'P';
                $rfpnonpurch->statuspayment = 'P';
                $rfpnonpurch->status = 'C';
                $rfpnonpurch->completed_by = $rfpnonpurch->completed_by ?: $user->username;
                $rfpnonpurch->completed_at = $now;
                $rfpnonpurch->updated_by = $user->username;
                $rfpnonpurch->save();

                // =========================
                // EMAIL TO REQUESTER
                // =========================
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $rfpnonpurch->rfpnonpurchaseid,
                    $doctype,
                    'C',
                    $rfpnonpurch->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $rfpnonpurch->cpny_id ?? '',
                        'deptname'  => $rfpnonpurch->department_id ?? '',
                        'date'      => $now->toDateTimeString(),
                        'info'      => $rfpnonpurch->keperluan ?? '',
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                        'docname'   => $docName,
                    ]
                );
            },

            // ====================================
            // NEXT APPROVER
            // ====================================
            function ($next, \Carbon\Carbon $now) use (
                $rfpnonpurch,
                $doctype,
                $docName,
                $docUrl,
                $user
            ) {

                // =========================
                // APPROVER EMAIL
                // =========================
                $usernames = str_replace(';', ',', (string) $next->aprv_username);

                $approvers = array_filter(
                    array_map('trim', explode(',', $usernames))
                );

                $approverEmails = User::query()
                    ->whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                // =========================
                // KEPADA
                // =========================
                $kepadaUsers = explode(',', (string) $rfpnonpurch->imnonpurchase_kepada);

                $kepadaEmails = User::query()
                    ->whereIn('username', $kepadaUsers)
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                // =========================
                // TEMBUSAN
                // =========================
                $tembusanUsers = explode(',', (string) $rfpnonpurch->imnonpurchase_tembusan);

                $ccEmails = User::query()
                    ->whereIn('username', $tembusanUsers)
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                // =========================
                // MERGE EMAIL
                // =========================
                $toEmails = array_unique(array_merge(
                    $approverEmails,
                    $kepadaEmails
                ));

                // =========================
                // SEND EMAIL
                // =========================
                if (!empty($toEmails)) {

                    $mailData = [
                        'docid'     => $rfpnonpurch->rfpnonpurchaseid,
                        'cpnyid'    => $rfpnonpurch->cpny_id,
                        'deptname'  => $rfpnonpurch->department_id,
                        'date'      => $now->toDateTimeString(),
                        'name'      => $rfpnonpurch->created_by,
                        'status'    => 'P',
                        'docname'   => $docName,
                        'url'       => $docUrl,
                        'info'      => $rfpnonpurch->keperluan,
                        'createdby' => $rfpnonpurch->created_by,
                    ];

                    Mail::send(
                        'emails.mailapprovenew',
                        $mailData,
                        function ($message) use (
                            $toEmails,
                            $ccEmails,
                            $rfpnonpurch,
                            $docName
                        ) {

                            $message->to($toEmails);

                            if (!empty($ccEmails)) {
                                $message->cc($ccEmails);
                            }

                            $message->subject(
                                $rfpnonpurch->rfpnonpurchaseid .
                                ' - WaitingApproval ' .
                                $docName
                            )->from(
                                config('mail.from.address'),
                                config('app.name')
                            );
                        }
                    );
                }

                // =========================
                // TRACK LAST PROCESS
                // =========================
                $rfpnonpurch->completed_by = $user->username;
                $rfpnonpurch->completed_at = $now;
                $rfpnonpurch->updated_by = $user->username;
                $rfpnonpurch->save();
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => $docName . ' approved successfully',
        ]);
    }

    public function rejectRfpNonPurch(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $rfpnonpurch = TrRfpNonPurch::with('creator')
            ->where('rfpnonpurchaseid', $docid)
            ->first();

        if (!$rfpnonpurch) {
            return response()->json([
                'success' => false,
                'message' => 'RFP/RCA Non Purchase not found',
            ], 404);
        }

        $doctype = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document type',
            ], 422);
        }

        $eid = Hashids::encode($rfpnonpurch->id);
        $docUrl = url('/showrfpnonpurch/' . $eid);

        $fullname = optional($rfpnonpurch->creator)->name
            ?: $rfpnonpurch->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $rfpnonpurch->rfpnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($rfpnonpurch, $doctype, $fullname, $docUrl, $request, $user) {
                $rfpnonpurch->status = 'R';
                $rfpnonpurch->completed_by = $user->username;
                $rfpnonpurch->completed_at = $now;
                $rfpnonpurch->updated_by = $user->username;
                $rfpnonpurch->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $rfpnonpurch->rfpnonpurchaseid,
                    $doctype,
                    'R',
                    $rfpnonpurch->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $rfpnonpurch->cpny_id ?? '',
                        'deptname'  => $rfpnonpurch->department_id ?? '',
                        'date'      => $now->toDateTimeString(),
                        'info'      => $rfpnonpurch->keperluan ?? '',
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                        'docname'   => $doctype . ' Non Purchase',
                    ]
                );

                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($rfpnonpurch->id, $doctype, $request);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to save reject comment RFP Non Purchase', [
                        'docid' => $rfpnonpurch->rfpnonpurchaseid,
                        'doctype' => $doctype,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Reject failed',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => $doctype . ' Non Purchase rejected successfully',
        ]);
    }
    public function reviseRfpNonPurch(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $rfpnonpurch = TrRfpNonPurch::with('creator')
            ->where('rfpnonpurchaseid', $docid)
            ->first();

        if (!$rfpnonpurch) {
            return response()->json([
                'success' => false,
                'message' => 'RFP/RCA Non Purchase not found',
            ], 404);
        }

        $doctype = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document type',
            ], 422);
        }

        $eid = Hashids::encode($rfpnonpurch->id);
        $docUrl = url('/showrfpnonpurch/' . $eid);

        $fullname = optional($rfpnonpurch->creator)->name
            ?: $rfpnonpurch->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $rfpnonpurch->rfpnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($rfpnonpurch, $doctype, $fullname, $docUrl, $request, $user) {
                $rfpnonpurch->status = 'D';
                $rfpnonpurch->completed_by = $user->username;
                $rfpnonpurch->completed_at = $now;
                $rfpnonpurch->updated_by = $user->username;
                $rfpnonpurch->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $rfpnonpurch->rfpnonpurchaseid,
                    $doctype,
                    'D',
                    $rfpnonpurch->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $rfpnonpurch->cpny_id ?? '',
                        'deptname'  => $rfpnonpurch->department_id ?? '',
                        'date'      => $now->toDateTimeString(),
                        'info'      => $rfpnonpurch->keperluan ?? '',
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                        'docname'   => $doctype . ' Non Purchase',
                    ]
                );

                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($rfpnonpurch->id, $doctype, $request);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to save revise comment RFP Non Purchase', [
                        'docid' => $rfpnonpurch->rfpnonpurchaseid,
                        'doctype' => $doctype,
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
            'message' => $doctype . ' Non Purchase revised successfully',
        ]);
    }

    public function editRfpNonPurch($hash)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        // =========================
        // HEADER
        // =========================
        $rfpnonpurch = TrRfpNonPurch::with([
            'creator:username,name',
            'groupbiaya:groupbiaya_id,groupbiayadescr'
        ])->findOrFail($id);

        // =========================
        // DETAIL
        // =========================
        $rfpnonpurchasedetail = TrRfpNonPurchDetail::query()
            ->where('rfpnonpurchaseid', $rfpnonpurch->rfpnonpurchaseid)
            ->orderBy('id')
            ->get();

        // =========================
        // BUSINESS UNIT (ambil dari detail)
        // =========================
        $detailBuIds = $rfpnonpurchasedetail
            ->pluck('budget_business_unit_id')
            ->filter(fn($v) => filled($v))
            ->unique()
            ->values();

        $selectedBuId = $detailBuIds->first();

        $selectedBuName = null;

        if ($selectedBuId) {
            $bu = BusinessUnit::query()
                ->where('business_unit_id', $selectedBuId)
                ->first();

            $selectedBuName = $bu->business_unit_name ?? null;
        }

        // inject temporary property ke object header
        $rfpnonpurch->business_unit_id = $selectedBuId;
        $rfpnonpurch->business_unit_name = $selectedBuName;

        // =========================
        // USER DATA
        // =========================
        $usercpny = Usercpny::query()
            ->where('username', $user->username)
            ->orderBy('cpny_id')
            ->get();

        $usercpny2 = Usercpny::query()
            ->where('username', $user->username)
            ->first();

        $userdept = Userdept::query()
            ->where('username', $user->username)
            ->orderBy('department_id')
            ->get();

        $userdept2 = Userdept::query()
            ->where('username', $user->username)
            ->first();

        // =========================
        // GROUP BIAYA
        // =========================
        $groupbiaya = MsGroupbiayaNonPurch::query()
            ->where('status', 'A')
            ->orderBy('groupbiayadescr')
            ->get();

        // =========================
        // USER KEPADA / TEMBUSAN
        // =========================
        $kepada = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get();

        $tembusan = User::query()
            ->where('status', 'A')
            ->orderBy('name')
            ->get();

        // =========================
        // ATTACHMENT (GCS SIGNED URL)
        // =========================
        $rows = TrAttachment::query()
            ->where('refnbr', $rfpnonpurch->rfpnonpurchaseid)
            ->where('status', 'A')
            ->orderByDesc('created_at')
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
                    'path'  => $objectPath,
                    'error' => $e->getMessage()
                ]);
            }

            return (object) [
                'id'           => $r->id,
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

        // =========================
        // DEPOSIT INFO
        // =========================
        $deposit = TrRfpNonPurchDeposit::query()
            ->where('rfpnonpurchaseid', $rfpnonpurch->rfpnonpurchaseid)
            ->where('status', 'A')
            ->first();
        
        $rfpnonpurch->custid       = $deposit->custid ?? null;
        $rfpnonpurch->customername = $deposit->customername ?? null;
        $rfpnonpurch->storename    = $deposit->storename ?? null;
        $rfpnonpurch->unitid       = $deposit->unitid ?? null;
        $rfpnonpurch->transferto   = $deposit->transferto ?? null;
        $rfpnonpurch->bankname     = $deposit->bankname ?? null;
        $rfpnonpurch->bankacct     = $deposit->bankacct ?? null;
  
        return view('pages.rfpnonpurch.editrfpnonpurch', compact(
            'rfpnonpurch',
            'rfpnonpurchasedetail',
            'usercpny',
            'usercpny2',
            'userdept',
            'userdept2',
            'groupbiaya',
            'kepada',
            'tembusan',
            'attachments',
            'hash'
        ));
    }

    public function updateRfpNonPurch(Request $request, $hash)
    {
        $user = $request->user();
        $username = $user->username ?? 'system';

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $header = TrRfpNonPurch::findOrFail($id);

        $dt = now();
        $year = $dt->year;

        $doctype = strtoupper($request->rfpnonpurchase_type ?? '');

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            return response()->json([
                'message' => 'Type Payment tidak valid. Pilih RFP atau RCA.',
            ], 422);
        }

        $docName = $doctype === 'RCA'
            ? 'RCA Non Purchase'
            : 'RFP Non Purchase';

        $toFloat = function ($v): float {
            if ($v === null || $v === '') return 0;

            $s = trim((string) $v);
            $s = preg_replace('/\s+/', '', $s);

            $hasComma = str_contains($s, ',');
            $hasDot = str_contains($s, '.');

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');

                if ($lastComma > $lastDot) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot && substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            }

            return is_numeric($s) ? (float) $s : 0;
        };

        $approvalCtl = app(ApprovalController::class);

        DB::beginTransaction();

        try {
            $docid = $header->rfpnonpurchaseid;

            // =========================
            // UPDATE HEADER
            // =========================
            $header->cpny_id = $request->cpnyid;
            $header->department_id = $request->departementid;
            $header->rfpnonpurchase_type = $doctype;
            $header->groupbiaya_id = $request->groupbiaya_id;
            $header->datediperlukan = $request->datediperlukan;
            $header->datepenyelesaian = $doctype === 'RCA'
                ? $request->datepenyelesaian
                : null;

            $header->pleasepayto = $request->pleasepayto;
            $header->keperluan = $request->keperluan;

            $kepada = $request->input('rfpnonpurchase_kepada', []);
            $tembusan = $request->input('rfpnonpurchase_tembusan', []);

            $header->imnonpurchase_kepada = is_array($kepada)
                ? implode(',', array_filter($kepada))
                : $kepada;

            $header->imnonpurchase_tembusan = is_array($tembusan)
                ? implode(',', array_filter($tembusan))
                : $tembusan;

            $header->amountrequestpayment = $doctype === 'RCA'
                ? $toFloat($request->amountrequestpayment)
                : 0;

            // setelah revise/draft submit ulang jadi P
            $header->status = 'P';
            $header->completed_by = null;
            $header->completed_at = null;
            $header->updated_by = $username;
            $header->updated_at = $dt;
            $header->save();

            // =========================
            // UPDATE DEPOSIT
            // =========================
            $groupBiaya = MsGroupbiayaNonPurch::query()
                ->where('groupbiaya_id', $request->groupbiaya_id)
                ->first();

            $isDeposit = (
                ($groupBiaya->is_deposit ?? false) === true ||
                ($groupBiaya->is_deposit ?? null) === 't' ||
                ($groupBiaya->is_deposit ?? null) == 1
            );

            if ($isDeposit) {

                TrRfpNonPurchDeposit::updateOrCreate(
                    [
                        'rfpnonpurchaseid' => $docid,
                    ],
                    [
                        'cpny_id'      => $request->cpnyid,
                        'custid'       => $request->custid,
                        'customername' => $request->customername,
                        'storename'    => $request->storename,
                        'unitid'       => $request->unitid,
                        'transferto'   => $request->transferto,
                        'bankname'     => $request->bankname,
                        'bankacct'     => $request->bankacct,
                        'status'       => 'A',
                        'updated_by'   => $username,
                        'updated_at'   => $dt,
                    ]
                );

            } else {

                // kalau group biaya bukan deposit
                TrRfpNonPurchDeposit::query()
                    ->where('rfpnonpurchaseid', $docid)
                    ->delete();
            }

            // =========================
            // DELETE DETAIL LAMA
            // =========================
            TrRfpNonPurchDetail::query()
                ->where('rfpnonpurchaseid', $docid)
                ->delete();

            // =========================
            // INSERT DETAIL BARU ONLY RFP
            // =========================
            $totalAmountRequest = 0;

            if ($doctype === 'RFP') {
                $descs = $request->rfpnonpurchase_descr ?? [];
                $prices = $request->price ?? [];

                $coaIds = $request->coa_id ?? [];
                $activityIds = $request->activity_id ?? [];
                $busUnitIds = $request->business_unit_id_detail ?? [];
                $deptFinIds = $request->department_fin_id ?? [];
                $actDescrs = $request->activity_descr ?? [];

                $rowCount = count($descs);

                for ($i = 0; $i < $rowCount; $i++) {
                    $desc = trim($descs[$i] ?? '');
                    $amount = $toFloat($prices[$i] ?? 0);

                    if (!$desc || $amount <= 0) {
                        continue;
                    }

                    $totalAmountRequest += $amount;

                    TrRfpNonPurchDetail::create([
                        'rfpnonpurchaseid' => $docid,
                        'keperluan_detail' => $desc,
                        'amount_request' => $amount,

                        'budget_perpost' => $year,
                        'budget_cpny_id' => $request->cpnyid,
                        'budget_business_unit_id' => $busUnitIds[$i] ?? null,
                        'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                        'budget_account_id' => $coaIds[$i] ?? null,
                        'budget_activity_id' => $activityIds[$i] ?? null,
                        'budget_activity_descr' => $actDescrs[$i] ?? null,

                        'status' => 'P',
                        'created_by' => $username,
                    ]);
                }

                $header->amountrequestpayment = $totalAmountRequest;
                $header->save();
            }

            if ($doctype === 'RCA') {
                $header->amountrequestpayment = $toFloat($request->amountrequestpayment);
                $header->save();
            }

            // =========================
            // RESET APPROVAL LAMA
            // =========================
            // TrApproval::query()
            //     ->where('refnbr', $docid)
            //     ->delete();

            // =========================
            // APPROVAL BARU
            // =========================
            $approvalCtl->loadLines(
                $doctype,
                $request->cpnyid,
                $request->departementid
            );

            $ctx = [
                'ignore_nominal' => false,
                'grand_total' => (float) $header->amountrequestpayment,
            ];

            [$firstApprovalUsernames] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

            // =========================
            // APPROVAL GROUP BIAYA
            // ADD / DEL approval berdasarkan groupbiaya_id
            // =========================
            $groupApprovalRules = MsApprovalGroupBiaya::query()
                ->where('aprv_doctype', $doctype)
                ->where('aprv_cpnyid', $request->cpnyid)
                ->where('aprv_departementid', $request->departementid)
                ->where('aprv_groupbiaya', $request->groupbiaya_id)
                ->where('status', 'A')
                ->get();

            foreach ($groupApprovalRules as $rule) {
                $condition = strtoupper(trim($rule->aprv_typecondition ?? ''));

                if ($condition === 'DEL') {
                    TrApproval::query()
                        ->where('refnbr', $docid)
                        ->where('aprv_doctype', $doctype)
                        ->where('aprv_cpnyid', $request->cpnyid)
                        ->where('aprv_departementid', $request->departementid)
                        ->where('aprv_leveling', $rule->aprv_leveling)
                        ->where('status', 'P')
                        ->delete();
                }

                if ($condition === 'ADD') {
                    $exists = TrApproval::query()
                        ->where('refnbr', $docid)
                        ->where('aprv_doctype', $doctype)
                        ->where('aprv_cpnyid', $request->cpnyid)
                        ->where('aprv_departementid', $request->departementid)
                        ->where('aprv_leveling', $rule->aprv_leveling)
                        ->exists();

                    if (!$exists) {
                        TrApproval::create([
                            'refnbr' => $docid,
                            'aprv_leveling' => $rule->aprv_leveling,
                            'aprv_doctype' => $doctype,
                            'aprv_cpnyid' => $request->cpnyid,
                            'aprv_departementid' => $request->departementid,
                            'aprv_username' => $rule->aprv_username,
                            'aprv_name' => $rule->aprv_name,
                            'aprv_datebefore' => $dt,
                            'aprv_type' => 'Normal',
                            'status' => 'P',
                            'created_by' => $username,
                        ]);
                    }
                }
            }

            // =========================
            // UPDATE FIRST PENDING KE HEADER
            // =========================
            $firstPendingAfterGroup = TrApproval::query()
                ->where('refnbr', $docid)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->orderByRaw('CAST(aprv_leveling AS DECIMAL(10,2)) ASC')
                ->first();

            if ($firstPendingAfterGroup) {
                $header->completed_by = $firstPendingAfterGroup->aprv_username;
                $header->completed_at = $dt;
                $header->save();
            }

            // =========================
            // ATTACHMENT
            // =========================
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $request->cpnyid,
                    'departementid' => $request->departementid,
                    'base_folder' => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by' => $username,
                ];

                try {
                    app(TrAttachmentController::class)
                        ->uploadInternal($meta, (array) $request->file('attachments'));
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Failed upload attachment',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }

            // =========================
            // EMAIL NOTIFICATION CUSTOM
            // =========================
            $firstPending = TrApproval::query()
                ->where('refnbr', $docid)
                ->where('aprv_doctype', $doctype)
                ->where('status', 'P')
                ->orderByRaw('CAST(aprv_leveling AS DECIMAL(10,2)) ASC')
                ->first();

            if ($firstPending) {
                $usernames = str_replace(';', ',', (string) $firstPending->aprv_username);
                $approvers = array_filter(array_map('trim', explode(',', $usernames)));

                $approverEmails = User::query()
                    ->whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                $kepadaUsers = array_filter(array_map('trim', explode(',', (string) $header->imnonpurchase_kepada)));

                $kepadaEmails = User::query()
                    ->whereIn('username', $kepadaUsers)
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                $tembusanUsers = array_filter(array_map('trim', explode(',', (string) $header->imnonpurchase_tembusan)));

                $ccEmails = User::query()
                    ->whereIn('username', $tembusanUsers)
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                $toEmails = array_unique(array_merge($approverEmails, $kepadaEmails));

                $eid = Hashids::encode($header->id);

                $mailData = [
                    'docid' => $docid,
                    'cpnyid' => $header->cpny_id,
                    'deptname' => $header->department_id,
                    'date' => $dt->toDateTimeString(),
                    'name' => $username,
                    'status' => $header->status,
                    'docname' => $docName,
                    'url' => url('/showrfpnonpurch/' . $eid),
                    'info' => $header->keperluan,
                    'createdby' => $username,
                ];

                if (!empty($toEmails)) {
                    Mail::send('emails.mailapprovenew', $mailData, function ($message) use ($toEmails, $ccEmails, $docid, $docName) {
                        $message->to($toEmails);

                        if (!empty($ccEmails)) {
                            $message->cc($ccEmails);
                        }

                        $message->subject($docid . ' - WaitingApproval ' . $docName)
                            ->from(config('mail.from.address'), config('app.name'));
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message' => $docName . ' updated and resubmitted successfully',
                'docid' => $docid,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to update ' . $docName,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    
   
    public function printPdfRfpNonPurch($hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!\Auth::check()) {
            return redirect()->route('login');
        }

        $rfpnonpurch = TrRfpNonPurch::with(['creator:username,name'])->findOrFail($id);

        // =========================
        // APPROVAL
        // =========================
        $approval = TrApproval::where('refnbr', $rfpnonpurch->rfpnonpurchaseid)
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->get();

        // =========================
        // FORMAT DATE
        // =========================
        $rfpnonpurch->rfpnonpurch_date_fmt = optional($rfpnonpurch->rfpnonpurchasedate)->format('d M Y');
        $rfpnonpurch->receive_date_fmt = optional($rfpnonpurch->receive_date)->format('d M Y H:i');
        $rfpnonpurch->payment_date_fmt = optional($rfpnonpurch->payment_date)->format('d M Y H:i');

        // =========================
        // TERBILANG
        // =========================
        $rfpnonpurch->terbilang = trim($this->terbilang((int)$rfpnonpurch->amountrequestpayment)) . ' Rupiah';

        // =========================
        // STATUS DOC (FOR COLOR)
        // =========================
        $status_doc = match ($rfpnonpurch->status) {
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
        $created_by_name = $rfpnonpurch->creator->name ?? null;
        $created_by_username = $rfpnonpurch->created_by;
        $req_date_fmt = optional($rfpnonpurch->created_at)->format('d M Y H:i');
        $company = MsCompany::where('cpny_id', $rfpnonpurch->cpny_id)->first();
        $cpny_name = $company->cpny_name ?? '';

        // =========================
        // LOAD PDF
        // =========================
        $pdf = \PDF::loadView('pages.rfpnonpurch.pdf_rfpnonpurch', [
            'rfpnonpurch' => $rfpnonpurch,
            'approval' => $approval,
            'status_doc' => $status_doc,
            'approve_count' => $approve_count,
            'created_by_name' => $created_by_name,
            'created_by_username' => $created_by_username,
            'req_date_fmt' => $req_date_fmt,
            'cpny_name' => $cpny_name,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("RFP_{$rfpnonpurch->rfpnonpurchaseid}.pdf");
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

    public function reminderRfpNonPurch(Request $request, $hash)
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
                'message' => 'Invalid RFP Non Purchase ID.',
            ], 404);
        }

        $rfpnonpurch = \App\Models\TrRfpNonPurch::query()
            ->where('id', $id)
            ->first();

        if (!$rfpnonpurch) {
            return response()->json([
                'success' => false,
                'message' => 'RFP/RCA Non Purchase not found.',
            ], 404);
        }

        $doctype = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

        if (!in_array($doctype, ['RFP', 'RCA'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document type.',
            ], 422);
        }

        try {
            $request->merge([
                'docid' => $rfpnonpurch->rfpnonpurchaseid,
                'doc_no' => $rfpnonpurch->rfpnonpurchaseid,
                'comment' => $request->message,
                'reason' => $request->message,
            ]);

            app(\App\Http\Controllers\SendCommentController::class)
                ->sendmsg((int) $rfpnonpurch->id, $doctype, $request);

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

    public function financeReviseRfpNonPurch(Request $request, $hash)
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
                'message' => 'Invalid RFP Non Purchase ID.',
            ], 404);
        }

        \Illuminate\Support\Facades\DB::connection('pgsql')->beginTransaction();

        try {
            $rfpnonpurch = \App\Models\TrRfpNonPurch::query()
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$rfpnonpurch) {
                \Illuminate\Support\Facades\DB::connection('pgsql')->rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'RFP/RCA Non Purchase not found.',
                ], 404);
            }

            $doctype = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

            if (!in_array($doctype, ['RFP', 'RCA'])) {
                \Illuminate\Support\Facades\DB::connection('pgsql')->rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document type.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Update RFP/RCA status menjadi Revise
            |--------------------------------------------------------------------------
            */
            $rfpnonpurch->status = 'D';
            $rfpnonpurch->statusreceive = null;
            $rfpnonpurch->statuspayment = null;
            $rfpnonpurch->updated_by = $user->username;
            $rfpnonpurch->updated_at = now();
            $rfpnonpurch->completed_by = $user->username;
            $rfpnonpurch->completed_at = now();
            $rfpnonpurch->save();

            /*
            |--------------------------------------------------------------------------
            | Insert row baru ke TrApproval sebagai log revise finance
            |--------------------------------------------------------------------------
            */
            $lastApproval = \App\Models\TrApproval::query()
                ->where('refnbr', $rfpnonpurch->rfpnonpurchaseid)
                ->where('aprv_doctype', $doctype)
                ->where('status', '<>', 'X')
                ->orderByDesc('id')
                ->first();

            \App\Models\TrApproval::create([
                'refnbr' => $rfpnonpurch->rfpnonpurchaseid,
                'aprv_leveling' => $lastApproval->aprv_leveling ?? 0,
                'aprv_doctype' => $doctype,
                'aprv_cpnyid' => $rfpnonpurch->cpny_id,
                'aprv_departementid' => $rfpnonpurch->department_id,
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
                'created_by' => $user->username,
                'updated_by' => $user->username,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Save message/comment
            |--------------------------------------------------------------------------
            */
            $request->merge([
                'docid' => $rfpnonpurch->rfpnonpurchaseid,
                'doc_no' => $rfpnonpurch->rfpnonpurchaseid,
                'comment' => $request->message,
                'reason' => $request->message,
            ]);

            app(\App\Http\Controllers\SendCommentController::class)
                ->sendmsg((int) $rfpnonpurch->id, $doctype, $request);

            \Illuminate\Support\Facades\DB::connection('pgsql')->commit();

            return response()->json([
                'success' => true,
                'message' => $doctype . ' Non Purchase revised successfully.',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::connection('pgsql')->rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revise RFP/RCA Non Purchase.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function isTruthy($value): bool
    {
        return in_array(
            strtolower(trim((string) $value)),
            ['1', 'true', 't', 'yes', 'y'],
            true
        );
    }

    

}
