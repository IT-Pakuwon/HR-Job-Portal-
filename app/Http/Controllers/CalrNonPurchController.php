<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\HasAutonbr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;

use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrRfpNonPurch;
use App\Models\TrCalrNonPurch;
use App\Models\SysUserRole;
use App\Models\TrRfpNonPurchDetail;
use App\Models\MsCompany;
use App\Models\MsPurchSetting;

class CalrNonPurchController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $u = $user->username ?? '';

        $cpnyList = is_string($user->cpny_id)
            ? array_values(array_filter(array_map('trim', explode(',', $user->cpny_id))))
            : (array) $user->cpny_id;

        $deptList = is_string($user->department_id)
            ? array_values(array_filter(array_map('trim', explode(',', $user->department_id))))
            : (array) $user->department_id;

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $hasApFinAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'APFINACCESS')
            ->exists();

        $hasApTreAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'APTREACCESS')
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | CALR Jobs
        |--------------------------------------------------------------------------
        */
        $calrjobs = TrRfpNonPurch::query()
            ->whereIn('cpny_id', $cpnyList)
            ->whereIn('department_id', $deptList)
            ->where('rfpnonpurchase_type', 'RCA')
            ->where('status', 'C')
            ->where('created_by', $u)
            ->whereNull('calrid')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Normal CALR List
        |--------------------------------------------------------------------------
        | Non FINACCESS hanya lihat dokumen sendiri.
        | FINACCESS bisa lihat semua sesuai company + department.
        */
        $baseCalr = TrCalrNonPurch::query()
            ->whereIn('cpny_id', $cpnyList)
            ->whereIn('department_id', $deptList)
            ->when(!$isFinanceAccess, function ($q) use ($u) {
                $q->where('created_by', $u);
            });

        $all = (clone $baseCalr)->count();
        $onProgress = (clone $baseCalr)->where('status', 'P')->count();
        $completed = (clone $baseCalr)->where('status', 'C')->count();
        $rejected = (clone $baseCalr)->where('status', 'R')->count();
        $revise = (clone $baseCalr)->where('status', 'D')->count();

        /*
        |--------------------------------------------------------------------------
        | CALR Finance
        |--------------------------------------------------------------------------
        | Sama seperti RFP Non Purch:
        | - hanya user FINACCESS
        | - hanya filter cpny_id
        | - status = C
        | - tanpa department
        | - tanpa created_by
        */
        $calrFinance = 0;

        if ($isFinanceAccess) {
            $calrFinance = TrCalrNonPurch::query()
                ->whereIn('cpny_id', $cpnyList)
                ->where('status', 'C')
                ->count();
        }

        return view('pages.calrnonpurch.calrnonpurch', compact(
            'calrjobs',
            'onProgress',
            'completed',
            'all',
            'rejected',
            'revise',
            'calrFinance',
            'isFinanceAccess',
            'hasApFinAccess',
            'hasApTreAccess'
        ));
    }
    public function index_xxx()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $u = $user->username ?? '';

        $cpnyRaw = $user->cpny_id ?? '';
        $deptRaw = $user->department_id ?? '';

        $cpnyList = $cpnyRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw))))
            : [];

        $deptList = $deptRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $deptRaw))))
            : [];

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $hasApFinAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'APFINACCESS')
            ->exists();

        $hasApTreAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'APTREACCESS')
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | CALR Jobs Non Purchase
        |--------------------------------------------------------------------------
        | Dari RFP Non Purchase yang:
        | - status = C
        | - created_by = user login
        | - belum punya calrid
        | - sesuai company dan department user login
        */
        $calrjobs = TrRfpNonPurch::query()
            ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                $q->whereIn('cpny_id', $cpnyList);
            })
            ->when(!empty($deptList), function ($q) use ($deptList) {
                $q->whereIn('department_id', $deptList);
            })
            ->where('rfpnonpurchase_type', 'RCA')
            ->where('status', 'C')
            ->where('created_by', $u)
            ->whereNull('calrid')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Helper created_by filter
        |--------------------------------------------------------------------------
        | Kalau FINACCESS bisa lihat semua sesuai company + department.
        | Kalau bukan FINACCESS hanya lihat yang dibuat sendiri.
        */
        $filterCreator = function ($q) use ($isFinanceAccess, $u) {
            if (!$isFinanceAccess) {
                $q->where('created_by', $u);
            }
        };

        $baseCalr = function () use ($cpnyList, $deptList) {
            return TrCalrNonPurch::query()
                ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->when(!empty($deptList), function ($q) use ($deptList) {
                    $q->whereIn('department_id', $deptList);
                });
        };

        $onProgress = $baseCalr()
            ->where('status', 'P')
            ->where($filterCreator)
            ->count();

        $completed = $baseCalr()
            ->where('status', 'C')
            ->where($filterCreator)
            ->count();

        $rejected = $baseCalr()
            ->where('status', 'R')
            ->where($filterCreator)
            ->count();

        $revise = $baseCalr()
            ->where('status', 'D')
            ->where($filterCreator)
            ->count();

        $all = $baseCalr()
            ->where($filterCreator)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | CALR Finance
        |--------------------------------------------------------------------------
        | Khusus FINACCESS.
        | Filter hanya company user login, tanpa department dan tanpa created_by.
        */
        $calrFinance = 0;

        if ($isFinanceAccess) {
            $calrFinance = TrCalrNonPurch::query()
                ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->where('status', 'C')
                ->count();
        }

        return view('pages.calrnonpurch.calrnonpurch', compact(
            'calrjobs',
            'onProgress',
            'completed',
            'all',
            'rejected',
            'revise',
            'calrFinance',
            'isFinanceAccess',
            'hasApFinAccess',
            'hasApTreAccess'
        ));
    }

    public function json(Request $req)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'draw' => (int) $req->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $scope = strtolower((string) $req->query('scope', 'calrjobs'));
        $u = $user->username ?? '';

        $cpnyList = is_string($user->cpny_id)
            ? array_values(array_filter(array_map('trim', explode(',', $user->cpny_id))))
            : (array) $user->cpny_id;

        $deptList = is_string($user->department_id)
            ? array_values(array_filter(array_map('trim', explode(',', $user->department_id))))
            : (array) $user->department_id;

        $draw = (int) $req->input('draw', 1);
        $start = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        if ($scope === 'calrjobs') {
            /*
            |--------------------------------------------------------------------------
            | CALR Jobs
            |--------------------------------------------------------------------------
            */
            $base = TrRfpNonPurch::query()
                ->whereIn('cpny_id', $cpnyList)
                ->whereIn('department_id', $deptList)
                ->where('rfpnonpurchase_type', 'RCA')
                ->where('status', 'C')
                ->where('created_by', $u)
                ->whereNull('calrid')
                ->select([
                    'id',
                    'rfpnonpurchaseid',
                    'imnonpurchaseid',
                    'rfpnonpurchasedate',
                    'datediperlukan',
                    'datepenyelesaian',
                    'cpny_id',
                    'department_id',
                    'location_id',
                    'user_peminta',
                    'rfpnonpurchase_type',
                    'pleasepayto',
                    'keperluan',
                    'amountrequestpayment',
                    'status',
                    'created_by',
                    'created_at',
                ]);

            $orderColumns = [
                0 => 'rfpnonpurchaseid',
                1 => 'rfpnonpurchaseid',
                2 => 'rfpnonpurchasedate',
                3 => 'imnonpurchaseid',
                4 => 'cpny_id',
                5 => 'department_id',
                6 => 'pleasepayto',
                7 => 'amountrequestpayment',
                8 => 'created_by',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('rfpnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('imnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('cpny_id', 'ilike', "%{$search}%")
                        ->orWhere('department_id', 'ilike', "%{$search}%")
                        ->orWhere('user_peminta', 'ilike', "%{$search}%")
                        ->orWhere('rfpnonpurchase_type', 'ilike', "%{$search}%")
                        ->orWhere('pleasepayto', 'ilike', "%{$search}%")
                        ->orWhere('keperluan', 'ilike', "%{$search}%")
                        ->orWhere('created_by', 'ilike', "%{$search}%")
                        ->orWhereRaw("TO_CHAR(rfpnonpurchasedate, 'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountrequestpayment AS TEXT) ILIKE ?", ["%{$search}%"]);
                });
            }
        } else {
            /*
            |--------------------------------------------------------------------------
            | Existing CALR Non Purchase
            |--------------------------------------------------------------------------
            */
            $base = TrCalrNonPurch::query()
                ->whereIn('cpny_id', $cpnyList);

            /*
            |--------------------------------------------------------------------------
            | Samakan dengan RFP Non Purch
            |--------------------------------------------------------------------------
            | calrfinance:
            | - hanya FINACCESS
            | - hanya cpny_id
            | - status C
            | - tanpa department
            | - tanpa created_by
            |
            | scope lain:
            | - filter department
            | - non FINACCESS filter created_by
            */
            if ($scope === 'calrfinance') {
                if (!$isFinanceAccess) {
                    return response()->json([
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                    ]);
                }

                $base->where('status', 'C');
            } else {
                $base->whereIn('department_id', $deptList);

                if (!$isFinanceAccess) {
                    $base->where('created_by', $u);
                }

                if ($scope === 'onprogress') {
                    $base->where('status', 'P');
                } elseif ($scope === 'completed') {
                    $base->where('status', 'C');
                } elseif ($scope === 'rejected') {
                    $base->where('status', 'R');
                } elseif ($scope === 'revise') {
                    $base->where('status', 'D');
                }
            }

            $base->select([
                'id',
                'calrnonpurchaseid',
                'rfpnonpurchaseid',
                'calrnonpurchasedate',
                'datebataspenyelesaian',
                'cpny_id',
                'department_id',
                'location_id',
                'user_peminta',
                'keperluan',
                'amountrfp',
                'amountsettlement',
                'amountdiff',
                'status',
                'userreceive',
                'receivedate',
                'statusreceive',
                'userpayment',
                'paymentdate',
                'paymenttype',
                'amountpayment',
                'amountpenyelesaian',
                'statuspayment',
                'created_by',
                'created_at',
            ]);

            $orderColumns = [
                0 => 'calrnonpurchaseid',
                1 => 'calrnonpurchaseid',
                2 => 'calrnonpurchasedate',
                3 => 'rfpnonpurchaseid',
                4 => 'cpny_id',
                5 => 'department_id',
                6 => 'amountrfp',
                7 => 'amountsettlement',
                8 => 'amountdiff',
                9 => 'created_by',
                10 => 'status',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('calrnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('rfpnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('cpny_id', 'ilike', "%{$search}%")
                        ->orWhere('department_id', 'ilike', "%{$search}%")
                        ->orWhere('user_peminta', 'ilike', "%{$search}%")
                        ->orWhere('keperluan', 'ilike', "%{$search}%")
                        ->orWhere('created_by', 'ilike', "%{$search}%")
                        ->orWhereRaw("TO_CHAR(calrnonpurchasedate, 'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountrfp AS TEXT) ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountsettlement AS TEXT) ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountdiff AS TEXT) ILIKE ?", ["%{$search}%"]);
                });
            }
        }

        $recordsTotal = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', ($scope === 'calrjobs' ? 2 : 1));
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? ($scope === 'calrjobs' ? 'rfpnonpurchaseid' : 'calrnonpurchasedate');

        $rows = $base->orderBy($orderCol, $orderDir)
            ->orderBy($scope === 'calrjobs' ? 'rfpnonpurchaseid' : 'calrnonpurchaseid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Format rows
        |--------------------------------------------------------------------------
        */
        $rows->transform(function ($row) use ($scope) {
            if ($scope === 'calrjobs') {
                $row->rfpnonpurchase_eid = Hashids::encode((string) $row->id);

                $row->rfpnonpurchasedate_fmt = $row->rfpnonpurchasedate
                    ? Carbon::parse($row->rfpnonpurchasedate)->format('Y-m-d')
                    : null;

                $row->amountrequestpayment_fmt = number_format((float) ($row->amountrequestpayment ?? 0), 2, '.', ',');
            } else {
                $row->calrnonpurchase_eid = Hashids::encode((string) $row->id);

                $row->calrnonpurchasedate_fmt = $row->calrnonpurchasedate
                    ? Carbon::parse($row->calrnonpurchasedate)->format('Y-m-d')
                    : null;

                $row->amountrfp_fmt = number_format((float) ($row->amountrfp ?? 0), 2, '.', ',');
                $row->amountsettlement_fmt = number_format((float) ($row->amountsettlement ?? 0), 2, '.', ',');
                $row->amountdiff_fmt = number_format((float) ($row->amountdiff ?? 0), 2, '.', ',');

                /*
                |--------------------------------------------------------------------------
                | Finance Flow Text
                |--------------------------------------------------------------------------
                */
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
            }

            unset($row->id);

            return $row;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function json_xxx(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'calrjobs'));

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'draw' => (int) $req->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $u = $user->username ?? '';

        $cpnyRaw = $user->cpny_id ?? '';
        $deptRaw = $user->department_id ?? '';

        $cpnyList = $cpnyRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw))))
            : [];

        $deptList = $deptRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $deptRaw))))
            : [];

        $draw = (int) $req->input('draw', 1);
        $start = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        if ($scope === 'calrjobs') {
            /*
            |--------------------------------------------------------------------------
            | CALR Jobs Non Purchase
            |--------------------------------------------------------------------------
            | Source: tr_rfp_nonpurchase
            */
            $base = TrRfpNonPurch::query()
                ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->when(!empty($deptList), function ($q) use ($deptList) {
                    $q->whereIn('department_id', $deptList);
                })
                ->where('rfpnonpurchase_type', 'RCA')
                ->where('status', 'C')
                ->where('created_by', $u)
                ->whereNull('calrid')
                ->select([
                    'id',
                    'rfpnonpurchaseid',
                    'imnonpurchaseid',
                    'rfpnonpurchasedate',
                    'datediperlukan',
                    'datepenyelesaian',
                    'cpny_id',
                    'department_id',
                    'location_id',
                    'user_peminta',
                    'rfpnonpurchase_type',
                    'pleasepayto',
                    'keperluan',
                    'amountrequestpayment',
                    'status',
                    'created_by',
                    'created_at',
                ]);

            $orderColumns = [
                0 => 'rfpnonpurchaseid',
                1 => 'rfpnonpurchaseid',
                2 => 'rfpnonpurchasedate',
                3 => 'imnonpurchaseid',
                4 => 'cpny_id',
                5 => 'department_id',
                6 => 'pleasepayto',
                7 => 'amountrequestpayment',
                8 => 'created_by',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('rfpnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('imnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('cpny_id', 'ilike', "%{$search}%")
                        ->orWhere('department_id', 'ilike', "%{$search}%")
                        ->orWhere('user_peminta', 'ilike', "%{$search}%")
                        ->orWhere('rfpnonpurchase_type', 'ilike', "%{$search}%")
                        ->orWhere('pleasepayto', 'ilike', "%{$search}%")
                        ->orWhere('keperluan', 'ilike', "%{$search}%")
                        ->orWhere('created_by', 'ilike', "%{$search}%")
                        ->orWhereRaw("TO_CHAR(rfpnonpurchasedate, 'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountrequestpayment AS TEXT) ILIKE ?", ["%{$search}%"]);
                });
            }
        } else {
            /*
            |--------------------------------------------------------------------------
            | Existing CALR Non Purchase
            |--------------------------------------------------------------------------
            | Source: tr_calr_nonpurchase
            */
            $base = TrCalrNonPurch::query()
                ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                });

            if ($scope !== 'calrfinance') {
                $base->when(!empty($deptList), function ($q) use ($deptList) {
                    $q->whereIn('department_id', $deptList);
                });
            }

            $filterCreator = function ($q) use ($isFinanceAccess, $u) {
                if (!$isFinanceAccess) {
                    $q->where('created_by', $u);
                }
            };

            if ($scope === 'calrfinance') {
                if (!$isFinanceAccess) {
                    return response()->json([
                        'draw' => $draw,
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                    ]);
                }

                $base->where('status', 'C');

                // khusus CALR Finance:
                // hanya filter cpny_id, tanpa department, tanpa created_by, tanpa status
            } elseif ($scope === 'onprogress') {
                $base->where('status', 'P')->where($filterCreator);
            } elseif ($scope === 'completed') {
                $base->where('status', 'C')->where($filterCreator);
            } elseif ($scope === 'rejected') {
                $base->where('status', 'R')->where($filterCreator);
            } elseif ($scope === 'revise') {
                $base->where('status', 'D')->where($filterCreator);
            } else {
                $base->where($filterCreator);
            }

            $base->select([
            'id',
            'calrnonpurchaseid',
            'rfpnonpurchaseid',
            'calrnonpurchasedate',
            'datebataspenyelesaian',
            'cpny_id',
            'department_id',
            'location_id',
            'user_peminta',
            'keperluan',
            'amountrfp',
            'amountsettlement',
            'amountdiff',
            'status',

            // wajib untuk CALR Finance
            'userreceive',
            'receivedate',
            'statusreceive',
            'userpayment',
            'paymentdate',
            'paymenttype',
            'amountpayment',
            'amountpenyelesaian',
            'statuspayment',

            'created_by',
            'created_at',
        ]);

            $orderColumns = [
                0 => 'calrnonpurchaseid',
                1 => 'calrnonpurchaseid',
                2 => 'calrnonpurchasedate',
                3 => 'rfpnonpurchaseid',
                4 => 'cpny_id',
                5 => 'department_id',
                6 => 'amountrfp',
                7 => 'amountsettlement',
                8 => 'amountdiff',
                9 => 'created_by',
                10 => 'status',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('calrnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('rfpnonpurchaseid', 'ilike', "%{$search}%")
                        ->orWhere('cpny_id', 'ilike', "%{$search}%")
                        ->orWhere('department_id', 'ilike', "%{$search}%")
                        ->orWhere('user_peminta', 'ilike', "%{$search}%")
                        ->orWhere('keperluan', 'ilike', "%{$search}%")
                        ->orWhere('created_by', 'ilike', "%{$search}%")
                        ->orWhereRaw("TO_CHAR(calrnonpurchasedate, 'YYYY-MM-DD') ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountrfp AS TEXT) ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountsettlement AS TEXT) ILIKE ?", ["%{$search}%"])
                        ->orWhereRaw("CAST(amountdiff AS TEXT) ILIKE ?", ["%{$search}%"]);
                });
            }
        }

        $recordsTotal = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $orderCol = $orderColumns[$orderIdx] ?? (
            $scope === 'calrjobs'
                ? 'rfpnonpurchasedate'
                : 'calrnonpurchasedate'
        );

        $rows = $base
            ->orderBy($orderCol, $orderDir)
            ->orderBy(
                $scope === 'calrjobs' ? 'rfpnonpurchaseid' : 'calrnonpurchaseid',
                'desc'
            )
            ->skip($start)
            ->take($length)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Format Response
        |--------------------------------------------------------------------------
        */
        $rows->transform(function ($r) use ($scope) {
            if ($scope === 'calrjobs') {
                $r->rfpnonpurchase_eid = Hashids::encode((string) $r->id);

                $r->rfpnonpurchasedate_fmt = $r->rfpnonpurchasedate
                    ? Carbon::parse($r->rfpnonpurchasedate)->format('Y-m-d')
                    : null;

                $r->datediperlukan_fmt = $r->datediperlukan
                    ? Carbon::parse($r->datediperlukan)->format('Y-m-d')
                    : null;

                $r->datepenyelesaian_fmt = $r->datepenyelesaian
                    ? Carbon::parse($r->datepenyelesaian)->format('Y-m-d')
                    : null;

                $r->amountrequestpayment_fmt = number_format((float) ($r->amountrequestpayment ?? 0), 2);
            } else {
                $r->calrnonpurchase_eid = Hashids::encode((string) $r->id);

                $r->calrnonpurchasedate_fmt = $r->calrnonpurchasedate
                    ? Carbon::parse($r->calrnonpurchasedate)->format('Y-m-d')
                    : null;

                $r->datebataspenyelesaian_fmt = $r->datebataspenyelesaian
                    ? Carbon::parse($r->datebataspenyelesaian)->format('Y-m-d')
                    : null;

                $r->amountrfp_fmt = number_format((float) ($r->amountrfp ?? 0), 2);
                $r->amountsettlement_fmt = number_format((float) ($r->amountsettlement ?? 0), 2);
                $r->amountdiff_fmt = number_format((float) ($r->amountdiff ?? 0), 2);

                if (
                    ($r->statuspayment === 'C') ||
                    (!empty($r->userpayment) && !empty($r->paymentdate))
                ) {
                    $r->finance_flow_status_text = 'Treasury Received';
                } elseif (
                    ($r->statusreceive === 'C') ||
                    (!empty($r->userreceive) && !empty($r->receivedate))
                ) {
                    $r->finance_flow_status_text = 'Finance Received';
                } else {
                    $r->finance_flow_status_text = 'Waiting User';
                }
            }

            return $r;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function createCalrNonPurch(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $u = $user->username ?? '';

        $hash = $request->query('rfpnonpurchase');

        if (!$hash) {
            abort(404, 'RFP Non Purchase parameter not found.');
        }

        $id = Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            abort(404, 'Invalid RFP Non Purchase ID.');
        }

        $cpnyRaw = $user->cpny_id ?? '';
        $deptRaw = $user->department_id ?? '';

        $cpnyList = $cpnyRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $cpnyRaw))))
            : [];

        $deptList = $deptRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $deptRaw))))
            : [];

        $header = TrRfpNonPurch::query()
            ->with(['creator:username,name', 'groupbiaya'])
            ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                $q->whereIn('cpny_id', $cpnyList);
            })
            ->when(!empty($deptList), function ($q) use ($deptList) {
                $q->whereIn('department_id', $deptList);
            })
            ->where('id', $id)
            ->where('rfpnonpurchase_type', 'RCA')
            ->where('status', 'C')
            ->where('created_by', $u)
            ->where(function ($q) {
                $q->whereNull('calrid')
                ->orWhere('calrid', '');
            })
            ->firstOrFail();

        return view('pages.calrnonpurch.createcalrnonpurch', compact('header'));
    }

    public function storeCalrNonPurch(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $username = $user->username ?? 'system';

        $dt = now();
        $year = $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype = 'CAR';

        /*
        |--------------------------------------------------------------------------
        | Helper convert number
        |--------------------------------------------------------------------------
        | Support:
        | 100000
        | 100.000
        | 100.000,50
        | -100000
        | -100.000
        | -100.000,50
        */
        $toFloat = function ($v): float {
            if ($v === null || $v === '') {
                return 0;
            }

            $s = trim((string) $v);
            $s = preg_replace('/\s+/', '', $s);

            $hasComma = str_contains($s, ',');
            $hasDot = str_contains($s, '.');

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');

                if ($lastComma > $lastDot) {
                    // 19.000.000,00 atau -19.000.000,00
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    // 19,000,000.00 atau -19,000,000.00
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                // 19000000,00 atau -19000000,00
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                // 19000000.00 atau 19.000.000
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : 0;
        };

        $request->validate([
            'rfpnonpurchaseid' => ['required', 'string'],
            'description' => ['required', 'array', 'min:1'],
            'description.*' => ['required', 'string'],
            'price' => ['required', 'array', 'min:1'],
            'price.*' => ['required'],
        ]);

        $approvalCtl = app(ApprovalController::class);

        DB::connection('pgsql')->beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Ambil RFP Non Purchase asal
            |--------------------------------------------------------------------------
            */
            $rfp = TrRfpNonPurch::query()
                ->where('rfpnonpurchaseid', $request->rfpnonpurchaseid)
                ->where('rfpnonpurchase_type', 'RCA')
                ->where('status', 'C')
                ->where(function ($q) {
                    $q->whereNull('calrid')
                    ->orWhere('calrid', '');
                })
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Validasi line approval sebelum simpan
            |--------------------------------------------------------------------------
            */
            $approvalCtl->loadLines(
                $doctype,
                $rfp->cpny_id,
                $rfp->department_id
            );

            /*
            |--------------------------------------------------------------------------
            | Generate DOC ID: CAR + YY + MM + running number
            |--------------------------------------------------------------------------
            */
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'CALR Non Purchase'
            );

            $docid = $doctype . substr($year, 2) . $month . sprintf('%03d', $auto['next']);

            /*
            |--------------------------------------------------------------------------
            | Hitung total settlement dari detail price[]
            |--------------------------------------------------------------------------
            */
            $descs = $request->description ?? [];
            $prices = $request->price ?? [];

            $amountSettlement = 0;
            $validRows = 0;

            foreach ($descs as $i => $desc) {
                $desc = trim((string) ($desc ?? ''));
                $priceRaw = $prices[$i] ?? null;

                if ($desc === '' || $priceRaw === null || $priceRaw === '') {
                    continue;
                }

                $price = $toFloat($priceRaw);

                $amountSettlement += $price;
                $validRows++;
            }

            if ($validRows <= 0) {
                throw new \Exception('Minimal 1 detail harus diisi.');
            }

            $amountRfp = (float) ($rfp->amountrequestpayment ?? 0);

            /*
            |--------------------------------------------------------------------------
            | Sisa / (Kurang) Pembayaran
            |--------------------------------------------------------------------------
            | Jika Amount RCA 100 dan Total Amount 80,
            | maka sisa = 20.
            |
            | Jika Total Amount 120,
            | maka kurang = -20.
            */
            $amountDiff = $amountRfp - $amountSettlement;

            /*
            |--------------------------------------------------------------------------
            | Insert Header CALR Non Purchase
            |--------------------------------------------------------------------------
            */
            $header = new TrCalrNonPurch();

            $header->calrnonpurchaseid = $docid;
            $header->rfpnonpurchaseid = $rfp->rfpnonpurchaseid;
            $header->calrnonpurchasedate = $dt->toDateString();
            $header->datebataspenyelesaian = $rfp->datepenyelesaian;

            $header->cpny_id = $rfp->cpny_id;
            $header->department_id = $rfp->department_id;
            $header->location_id = $rfp->location_id;
            $header->user_peminta = $rfp->user_peminta;
            $header->keperluan = $rfp->keperluan;

            $header->amountrfp = $amountRfp;
            $header->amountsettlement = $amountSettlement;
            $header->amountdiff = $amountDiff;

            $header->status = 'P';
            $header->created_by = $username;
            $header->created_at = $dt;
            $header->save();

            /*
            |--------------------------------------------------------------------------
            | Insert Detail ke TrRfpNonPurchDetail
            |--------------------------------------------------------------------------
            | Karena model detail yang diberikan adalah TrRfpNonPurchDetail,
            | maka refid diisi dengan nomor CAR agar detail settlement bisa ditrace.
            */
            $budgetRfca = TrRfpNonPurchDetail::query()
                ->where('rfpnonpurchaseid', $rfp->rfpnonpurchaseid)
                ->where('refid', 'BUDGET-RFCA')
                ->orderBy('id')
                ->first();

            foreach ($descs as $i => $desc) {
                $desc = trim((string) ($desc ?? ''));
                $priceRaw = $prices[$i] ?? null;

                if ($desc === '' || $priceRaw === null || $priceRaw === '') {
                    continue;
                }

                $price = $toFloat($priceRaw);

                TrRfpNonPurchDetail::create([
                    'rfpnonpurchaseid' => $rfp->rfpnonpurchaseid,
                    'keperluan_detail' => $desc,

                    // amount_request asal tidak diinput di view CALR
                    'amount_request' => 0,

                    // price dari CALR detail
                    'amount_request_penyelesaian' => $price,

                    /*
                    |--------------------------------------------------------------------------
                    | Budget diambil dari detail RCA awal refid = BUDGET-RFCA
                    |--------------------------------------------------------------------------
                    | Jika tidak ketemu, fallback ke default lama.
                    |--------------------------------------------------------------------------
                    */
                    'budget_perpost' => $budgetRfca->budget_perpost ?? $year,
                    'budget_cpny_id' => $budgetRfca->budget_cpny_id ?? $rfp->cpny_id,
                    'budget_business_unit_id' => $budgetRfca->budget_business_unit_id ?? null,
                    'budget_department_fin_id' => $budgetRfca->budget_department_fin_id ?? null,
                    'budget_account_id' => $budgetRfca->budget_account_id ?? null,
                    'budget_activity_id' => $budgetRfca->budget_activity_id ?? null,
                    'budget_activity_descr' => $budgetRfca->budget_activity_descr ?? null,

                    // refid untuk menandai detail ini milik CAR/CALR mana
                    'refid' => $docid,

                    'status' => 'P',
                    'created_by' => $username,
                    'created_at' => $dt,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update RFP asal supaya tidak muncul lagi di CALR Jobs
            |--------------------------------------------------------------------------
            */
            $rfp->calrid = $docid;
            $rfp->updated_by = $username;
            $rfp->updated_at = $dt;
            $rfp->save();

            /*
            |--------------------------------------------------------------------------
            | Generate Approval
            |--------------------------------------------------------------------------
            */
            $ctx = [
                'ignore_nominal' => false,
                'grand_total' => abs((float) $amountSettlement),
            ];

            [$firstApprovalUsernames] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $rfp->cpny_id,
                $rfp->department_id,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Upload Attachment
            |--------------------------------------------------------------------------
            */
            $uploadResult = null;

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $rfp->cpny_id,
                    'departementid' => $rfp->department_id,
                    'base_folder' => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::connection('pgsql')->rollBack();

                    return response()->json([
                        'message' => 'Failed to create CALR Non Purchase',
                        'error' => 'Gagal upload attachment: ' . $e->getMessage(),
                    ], 500);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Notify First Approver
            |--------------------------------------------------------------------------
            */
            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status,
                'CALR Non Purchase',
                url('/showcalrnonpurch/' . $eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                    'rfpnonpurchaseid' => $rfp->rfpnonpurchaseid,
                    'amountrfp' => $amountRfp,
                    'amountsettlement' => $amountSettlement,
                    'amountdiff' => $amountDiff,
                ]
            );

            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'CALR Non Purchase created successfully',
                'docid' => $docid,
                'calrnonpurchaseid' => $docid,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed to create CALR Non Purchase',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function showCalrNonPurch($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $calr = TrCalrNonPurch::with([
            'creator:username,name',
        ])->findOrFail($id);

        $rfp = TrRfpNonPurch::with([
            'creator:username,name',
            'groupbiaya',
        ])
            ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
            ->first();

        $details = TrRfpNonPurchDetail::query()
            ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
            ->where('refid', $calr->calrnonpurchaseid)
            ->orderBy('id', 'asc')
            ->get();

        $doctype = 'CAR';
        $refnbr = $calr->calrnonpurchaseid;

        $canUpload = in_array($calr->status, ['P', 'D']);

        $loginUsername = $user->username ?? $user->name ?? null;
        $isApprover = TrApproval::where('refnbr', $calr->calrnonpurchaseid)
            ->where('aprv_doctype', 'CAR')
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->get()
            ->contains(function ($row) use ($loginUsername) {
                $list = preg_split('/[;,]/', (string) $row->aprv_username);
                $list = array_map('trim', $list);
                return in_array(strtolower((string) $loginUsername), array_map('strtolower', $list), true);
            });

        /*
        |--------------------------------------------------------------------------
        | CALR Progress Steps
        |--------------------------------------------------------------------------
        | Source dari TrCalrNonPurch.
        */
        $calrnonpurchSteps = [
            [
                'order' => 1,
                'description' => 'CALR Created',
                'user' => $calr->created_by ?? '-',
                'date' => $calr->created_at,
                'status' => 'Done',
            ],
            [
                'order' => 2,
                'description' => 'CALR Approval',
                'user' => $calr->completed_by ?? '-',
                'date' => $calr->completed_at,
                'status' => match ($calr->status) {
                    'C' => 'Done',
                    'R' => 'Rejected',
                    'D' => 'Revise',
                    default => 'Pending',
                },
            ],
            [
                'order' => 3,
                'description' => 'Finance Received',
                'user' => $calr->userreceive ?? '-',
                'date' => $calr->receivedate,
                'status' => $calr->statusreceive === 'C'
                    ? 'Done'
                    : 'Pending',
            ],
            [
                'order' => 4,
                'description' => 'Treasury Payment',
                'user' => $calr->userpayment ?? '-',
                'date' => $calr->paymentdate,
                'status' => $calr->statuspayment === 'C'
                    ? 'Done'
                    : 'Pending',
            ],
        ];

        return view('pages.calrnonpurch.showcalrnonpurch', compact(
            'calr',
            'rfp',
            'calrnonpurchSteps',
            'details',
            'hash',
            'doctype',
            'refnbr',
            'canUpload',
            'isApprover'
        ));
    }   

    public function rejectCalrNonPurch(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'CAR';

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $calr = \App\Models\TrCalrNonPurch::with('creator')
            ->where('calrnonpurchaseid', $docid)
            ->first();

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found',
            ], 404);
        }

        $eid = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl = url('/showcalrnonpurch/' . $eid);

        $fullname = data_get($calr, 'creator.name') ?: $calr->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $calr->calrnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($calr, $fullname, $docUrl, $doctype) {

                $calr->status = 'R';
                $calr->completed_by = auth()->user()->username;
                $calr->completed_at = $now;
                $calr->save();

                /*
                |--------------------------------------------------------------------------
                | Optional: update detail settlement RCA
                |--------------------------------------------------------------------------
                | Karena detail CALR Non Purchase disimpan di TrRfpNonPurchDetail
                | dengan refid = calrnonpurchaseid.
                */
                \App\Models\TrRfpNonPurchDetail::where('refid', $calr->calrnonpurchaseid)
                    ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
                    ->update([
                        'status' => 'R',
                        'updated_by' => auth()->user()->username,
                        'updated_at' => $now,
                    ]);

                \App\Models\TrRfpNonPurch::where('calrid', $calr->calrnonpurchaseid)                    
                    ->update([
                        'calrid' => '',
                        'updated_by' => auth()->user()->username,
                        'updated_at' => $now,
                    ]);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrnonpurchaseid,
                    'CALR Non Purchase',
                    'R',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $calr->cpny_id ?? '',
                        'deptname' => $calr->department_id ?? '',
                        'date' => $now->toDateString(),
                        'info' => $calr->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                        'rfpnonpurchaseid' => $calr->rfpnonpurchaseid ?? '',
                        'amountrfp' => $calr->amountrfp ?? 0,
                        'amountsettlement' => $calr->amountsettlement ?? 0,
                        'amountdiff' => $calr->amountdiff ?? 0,
                    ]
                );

                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($calr->id, $doctype, request());
                } catch (\Throwable $e) {
                    // sengaja diabaikan supaya proses reject tetap berhasil
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
            'message' => 'CALR Non Purchase rejected successfully',
        ]);
    }

    public function reviseCalrNonPurch(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $doctype = 'CAR';

        $calr = \App\Models\TrCalrNonPurch::with('creator')
            ->where('calrnonpurchaseid', $docid)
            ->first();

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found',
            ], 404);
        }

        $eid = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl = url('/showcalrnonpurch/' . $eid);

        $fullname = optional($calr->creator)->name
            ?: $calr->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $calr->calrnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($calr, $doctype, $fullname, $docUrl, $request, $user) {
                /*
                |--------------------------------------------------------------------------
                | Update Header CALR Non Purchase
                |--------------------------------------------------------------------------
                */
                $calr->status = 'D';
                $calr->completed_by = $user->username;
                $calr->completed_at = $now;
                $calr->updated_by = $user->username;
                $calr->updated_at = $now;
                $calr->save();

                /*
                |--------------------------------------------------------------------------
                | Update Detail CALR Non Purchase
                |--------------------------------------------------------------------------
                | Detail settlement disimpan di TrRfpNonPurchDetail
                | dengan refid = calrnonpurchaseid.
                */
                \App\Models\TrRfpNonPurchDetail::where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
                    ->where('refid', $calr->calrnonpurchaseid)
                    ->update([
                        'status' => 'D',
                        'updated_by' => $user->username,
                        'updated_at' => $now,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Notify Requester
                |--------------------------------------------------------------------------
                */
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrnonpurchaseid,
                    'CALR Non Purchase',
                    'D',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $calr->cpny_id ?? '',
                        'deptname' => $calr->department_id ?? '',
                        'date' => $now->toDateTimeString(),
                        'info' => $calr->keperluan ?? '',
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                        'docname' => 'CALR Non Purchase',
                        'rfpnonpurchaseid' => $calr->rfpnonpurchaseid ?? '',
                        'amountrfp' => $calr->amountrfp ?? 0,
                        'amountsettlement' => $calr->amountsettlement ?? 0,
                        'amountdiff' => $calr->amountdiff ?? 0,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Save Comment
                |--------------------------------------------------------------------------
                */
                try {
                    app(\App\Http\Controllers\SendCommentController::class)
                        ->sendmsg($calr->id, $doctype, $request);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to save revise comment CALR Non Purchase', [
                        'docid' => $calr->calrnonpurchaseid,
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
            'message' => 'CALR Non Purchase revised successfully',
        ]);
    }

    public function approveCalrNonPurch_xxx(Request $request, $docid)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $doctype = 'CAR';
        $docName = 'CALR Non Purchase';

        $calr = \App\Models\TrCalrNonPurch::with('creator')
            ->where('calrnonpurchaseid', $docid)
            ->first();

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found',
            ], 404);
        }

        $rfpnonpurch = \App\Models\TrRfpNonPurch::with('creator')
            ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
            ->first();

        $eid = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl = url('/showcalrnonpurch/' . $eid);

        $fullname = optional($calr->creator)->name
            ?: $calr->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $calr->calrnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            /*
            |--------------------------------------------------------------------------
            | FINAL APPROVAL
            |--------------------------------------------------------------------------
            */
            function (string $refnbr, \Carbon\Carbon $now) use (
                $calr,
                $rfpnonpurch,
                $doctype,
                $docName,
                $fullname,
                $docUrl,
                $user
            ) {
                /*
                |--------------------------------------------------------------------------
                | Update Header CALR Non Purchase
                |--------------------------------------------------------------------------
                */
                $calr->status = 'C';
                $calr->statusreceive = 'P';
                $calr->statuspayment = 'P';                
                $calr->completed_by = $user->username;
                $calr->completed_at = $now;
                $calr->updated_by = $user->username;
                $calr->updated_at = $now;
                $calr->save();

                /*
                |--------------------------------------------------------------------------
                | Update Detail CALR Non Purchase
                |--------------------------------------------------------------------------
                | Detail settlement disimpan di TrRfpNonPurchDetail
                | dengan refid = calrnonpurchaseid.
                */
                \App\Models\TrRfpNonPurchDetail::where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
                    ->where('refid', $calr->calrnonpurchaseid)
                    ->update([
                        'status' => 'C',
                        'updated_by' => $user->username,
                        'updated_at' => $now,
                    ]);

         
                /*
                |--------------------------------------------------------------------------
                | Email to Requester
                |--------------------------------------------------------------------------
                */
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrnonpurchaseid,
                    $doctype,
                    'C',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $calr->cpny_id ?? '',
                        'deptname' => $calr->department_id ?? '',
                        'date' => $now->toDateTimeString(),
                        'info' => $calr->keperluan ?? '',
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                        'docname' => $docName,
                        'rfpnonpurchaseid' => $calr->rfpnonpurchaseid ?? '',
                        'amountrfp' => $calr->amountrfp ?? 0,
                        'amountsettlement' => $calr->amountsettlement ?? 0,
                        'amountdiff' => $calr->amountdiff ?? 0,
                    ]
                );
            },

            /*
            |--------------------------------------------------------------------------
            | NEXT APPROVER
            |--------------------------------------------------------------------------
            */
            function ($next, \Carbon\Carbon $now) use (
                $calr,
                $rfpnonpurch,
                $doctype,
                $docName,
                $docUrl,
                $user
            ) {
                /*
                |--------------------------------------------------------------------------
                | Approver Email
                |--------------------------------------------------------------------------
                */
                $usernames = str_replace(';', ',', (string) $next->aprv_username);

                $approvers = array_filter(
                    array_map('trim', explode(',', $usernames))
                );

                $approverEmails = \App\Models\User::query()
                    ->whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('notification_email')
                    ->filter()
                    ->toArray();

                /*
                |--------------------------------------------------------------------------
                | Kepada dari RCA asal
                |--------------------------------------------------------------------------
                */
                $kepadaEmails = [];

                if ($rfpnonpurch && !empty($rfpnonpurch->imnonpurchase_kepada)) {
                    $kepadaUsers = array_filter(
                        array_map('trim', explode(',', (string) $rfpnonpurch->imnonpurchase_kepada))
                    );

                    $kepadaEmails = \App\Models\User::query()
                        ->whereIn('username', $kepadaUsers)
                        ->where('status', 'A')
                        ->pluck('notification_email')
                        ->filter()
                        ->toArray();
                }

                /*
                |--------------------------------------------------------------------------
                | Tembusan dari RCA asal
                |--------------------------------------------------------------------------
                */
                $ccEmails = [];

                if ($rfpnonpurch && !empty($rfpnonpurch->imnonpurchase_tembusan)) {
                    $tembusanUsers = array_filter(
                        array_map('trim', explode(',', (string) $rfpnonpurch->imnonpurchase_tembusan))
                    );

                    $ccEmails = \App\Models\User::query()
                        ->whereIn('username', $tembusanUsers)
                        ->where('status', 'A')
                        ->pluck('notification_email')
                        ->filter()
                        ->toArray();
                }

                /*
                |--------------------------------------------------------------------------
                | Merge To Email
                |--------------------------------------------------------------------------
                */
                $toEmails = array_unique(array_merge(
                    $approverEmails,
                    $kepadaEmails
                ));

                /*
                |--------------------------------------------------------------------------
                | Send Email to Next Approver
                |--------------------------------------------------------------------------
                */
                if (!empty($toEmails)) {
                    $mailData = [
                        'docid' => $calr->calrnonpurchaseid,
                        'cpnyid' => $calr->cpny_id,
                        'deptname' => $calr->department_id,
                        'date' => $now->toDateTimeString(),
                        'name' => $calr->created_by,
                        'status' => 'P',
                        'docname' => $docName,
                        'url' => $docUrl,
                        'info' => $calr->keperluan,
                        'createdby' => $calr->created_by,
                        'rfpnonpurchaseid' => $calr->rfpnonpurchaseid,
                        'amountrfp' => $calr->amountrfp,
                        'amountsettlement' => $calr->amountsettlement,
                        'amountdiff' => $calr->amountdiff,
                    ];

                    \Illuminate\Support\Facades\Mail::send(
                        'emails.mailapprovenew',
                        $mailData,
                        function ($message) use (
                            $toEmails,
                            $ccEmails,
                            $calr,
                            $docName
                        ) {
                            $message->to($toEmails);

                            if (!empty($ccEmails)) {
                                $message->cc($ccEmails);
                            }

                            $message->subject(
                                $calr->calrnonpurchaseid .
                                ' - WaitingApproval ' .
                                $docName
                            )->from(
                                config('mail.from.address'),
                                config('app.name')
                            );
                        }
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Track Last Process
                |--------------------------------------------------------------------------
                */
                $calr->completed_by = $user->username;
                $calr->completed_at = $now;
                $calr->updated_by = $user->username;
                $calr->updated_at = $now;
                $calr->save();
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

    public function approveCalrNonPurch(Request $request, $docid)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Doctype Approval CALR Non Purchase
        |--------------------------------------------------------------------------
        | Sesuaikan kalau di approval kamu pakai 'CALR' bukan 'CAR'.
        |--------------------------------------------------------------------------
        */
        $doctype = 'CAR';

        $calr = TrCalrNonPurch::with('creator')
            ->where('calrnonpurchaseid', $docid)
            ->first();

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found',
            ], 404);
        }

        $eid = Hashids::encode($calr->id);
        $docUrl = url('/showcalrnonpurch/' . $eid);
        $fullname = data_get($calr, 'creator.name') ?: $calr->created_by;

        /*
        |--------------------------------------------------------------------------
        | LOGIKA IM BUDGET
        |--------------------------------------------------------------------------
        | Ambil approval level user saat ini.
        |--------------------------------------------------------------------------
        */
        $uname = (string) ($user->username ?? '');

        $pending = TrApproval::where('refnbr', $calr->calrnonpurchaseid)
            ->where('aprv_doctype', $doctype)
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->whereRaw('aprv_username ILIKE ?', [
                '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $uname) . '%'
            ])
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        $currentLevel = (float) ($pending->aprv_leveling ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Setting level generate IM Budget
        |--------------------------------------------------------------------------
        | Ganti IMGENCALRNP kalau setting_id kamu beda.
        |--------------------------------------------------------------------------
        */
        $imGenerateLevel = (float) (MsPurchSetting::where('setting_id', 'IMGENCALRNP')
            ->value('setting_value_int') ?? 0);

        $flagIM = $this->isTruthy($calr->flag_imbudget ?? false);
        $existingIM = $calr->imbudgetid ?? null;
        $statusIM = $calr->status_imbudget ?? null;

        $needCheckIM = $flagIM
            && $imGenerateLevel > 0
            && $currentLevel >= $imGenerateLevel;

        $needGenerateNow = $needCheckIM
            && empty($existingIM);

        /*
        |--------------------------------------------------------------------------
        | Jika IM sudah ada tapi belum complete, stop approve
        |--------------------------------------------------------------------------
        */
        if ($needCheckIM && !empty($existingIM) && $statusIM !== 'C') {
            return response()->json([
                'success' => false,
                'code' => 'IM_IN_PROGRESS',
                'message' => 'Tidak bisa approve. Masih On Progress IM Budget.',
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | Jika perlu generate IM dan belum confirm, return popup konfirmasi
        |--------------------------------------------------------------------------
        */
        if ($needGenerateNow) {
            if (!$request->boolean('confirm_generate_im')) {
                return response()->json([
                    'success' => true,
                    'need_confirm_generate_im' => true,
                    'message' => 'Generate IM Budget sekarang?',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | User confirm generate IM Budget
            |--------------------------------------------------------------------------
            */
            try {
                /*
                |--------------------------------------------------------------------------
                | Panggil function generate IM Budget dari CALR Non Purchase
                |--------------------------------------------------------------------------
                | Pastikan function ini sudah ada.
                |--------------------------------------------------------------------------
                */
                $imbudget = app(IMBudgetController::class)
                    ->generateIMBudgetFromCalrNonPurch($calr, $user, now());

                if (!$imbudget || empty($imbudget->imbudgetid)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal generate IM Budget dari CALR Non Purchase.',
                    ], 500);
                }

                /*
                |--------------------------------------------------------------------------
                | Pastikan status IM Hold
                |--------------------------------------------------------------------------
                */
                $imbudget->status = 'H';
                $imbudget->save();

                /*
                |--------------------------------------------------------------------------
                | Update CALR source
                |--------------------------------------------------------------------------
                */
                $calr->imbudgetid = $imbudget->imbudgetid;
                $calr->status_imbudget = 'H';
                $calr->updated_by = $user->username;
                $calr->updated_at = now();
                $calr->save();

                return response()->json([
                    'success' => true,
                    'code' => 'IM_CREATED_HOLD',
                    'message' => "IM Budget berhasil dibuat ({$imbudget->imbudgetid}) dan di-HOLD.",
                    'imbudgetid' => $imbudget->imbudgetid,
                    'imbudget_show_url' => url('/showcalrnonpurch/' . $eid),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Generate IM from approveCalrNonPurch failed', [
                    'calrnonpurchaseid' => $calr->calrnonpurchaseid,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate IM Budget: ' . $e->getMessage(),
                ], 500);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lanjut approve normal
        |--------------------------------------------------------------------------
        | Kalau flag IM true dan IM sudah C, baru akan masuk ke sini.
        |--------------------------------------------------------------------------
        */
        $result = app(ApprovalController::class)->approveStep(
            $calr->calrnonpurchaseid,
            $doctype,
            $user->username,
            $user->name,

            /*
            |--------------------------------------------------------------------------
            | COMPLETE CALLBACK
            |--------------------------------------------------------------------------
            */
            function (string $refnbr, Carbon $now) use ($calr, $fullname, $docUrl) {
                $username = auth()->user()->username ?? 'system';

                $calr->status = 'C';
                $calr->completed_by = $calr->completed_by ?: $username;
                $calr->completed_at = $now;
                $calr->updated_by = $username;
                $calr->updated_at = $now;
                $calr->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrnonpurchaseid,
                    'CALR Non Purchase',
                    'C',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $calr->cpny_id ?? '',
                        'deptname' => $calr->department_id ?? '',
                        'date' => $calr->calrnonpurchasedate,
                        'info' => $calr->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            /*
            |--------------------------------------------------------------------------
            | NEXT APPROVER CALLBACK
            |--------------------------------------------------------------------------
            */
            function ($next, Carbon $now) use ($calr, $docUrl) {
                app(ApprovalController::class)->notifyFirstApprover(
                    $calr->calrnonpurchaseid,
                    'CAR',
                    'P',
                    'CALR Non Purchase',
                    $docUrl,
                    [
                        'info' => $calr->keperluan,
                        'createdby' => $calr->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                $calr->completed_by = auth()->user()->username;
                $calr->completed_at = $now;
                $calr->save();
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
            'message' => 'CALR Non Purchase approved successfully',
        ]);
    }

    public function editCalrNonPurch($hash)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $calr = TrCalrNonPurch::query()
            ->where('id', $id)
            ->where('status', 'D')
            ->firstOrFail();

        $rows = TrAttachment::where('refnbr', $calr->calrnonpurchaseid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object     = $bucket->object($objectPath);
            $signedUrl  = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
            return (object) [
                'id'          => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        if ($calr->created_by !== $user->username) {
            abort(403, 'You are not allowed to edit this document.');
        }

        $header = TrRfpNonPurch::query()
            ->with(['creator:username,name', 'groupbiaya'])
            ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
            ->firstOrFail();

        $details = TrRfpNonPurchDetail::query()
            ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
            ->where('refid', $calr->calrnonpurchaseid)
            ->orderBy('id', 'asc')
            ->get();

        $calr_eid = Hashids::encode((string) $calr->id);

        return view('pages.calrnonpurch.editcalrnonpurch', [
            'calr' => $calr,
            'header' => $header,
            'details' => $details,
            'calr_eid' => $calr_eid,
            'hash' => $hash,
            'attachments' => $attachments,
        ]);
    }

    public function updateCalrNonPurch(Request $request, $hash)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $id = Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'message' => 'Invalid CALR Non Purchase ID.',
            ], 404);
        }

        $username = $user->username ?? 'system';
        $doctype = 'CAR';
        $dt = now();

        $toFloat = function ($v): float {
            if ($v === null || $v === '') {
                return 0;
            }

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
            } elseif ($hasDot) {
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }

            return is_numeric($s) ? (float) $s : 0;
        };

        $request->validate([
            'description' => ['required', 'array', 'min:1'],
            'description.*' => ['required', 'string'],
            'price' => ['required', 'array', 'min:1'],
            'price.*' => ['required'],
        ]);

        DB::connection('pgsql')->beginTransaction();

        try {
            $calr = TrCalrNonPurch::query()
                ->where('id', $id)
                ->where('status', 'D')
                ->lockForUpdate()
                ->firstOrFail();

            if ($calr->created_by !== $username) {
                DB::connection('pgsql')->rollBack();

                return response()->json([
                    'message' => 'You are not allowed to update this document.',
                ], 403);
            }

            $rfp = TrRfpNonPurch::query()
                ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
                ->firstOrFail();

            $docid = $calr->calrnonpurchaseid;

            $descs = $request->description ?? [];
            $prices = $request->price ?? [];

            $amountSettlement = 0;
            $validRows = 0;

            foreach ($descs as $i => $desc) {
                $desc = trim((string) ($desc ?? ''));
                $priceRaw = $prices[$i] ?? null;

                if ($desc === '' || $priceRaw === null || $priceRaw === '') {
                    continue;
                }

                $price = $toFloat($priceRaw);

                $amountSettlement += $price;
                $validRows++;
            }

            if ($validRows <= 0) {
                throw new \Exception('Minimal 1 detail harus diisi.');
            }

            $amountRfp = (float) ($calr->amountrfp ?? $rfp->amountrequestpayment ?? 0);
            $amountDiff = $amountRfp - $amountSettlement;

            /*
            |--------------------------------------------------------------------------
            | Approval setup
            |--------------------------------------------------------------------------
            */
            $approvalCtl = app(ApprovalController::class);

            $approvalCtl->loadLines(
                $doctype,
                $calr->cpny_id,
                $calr->department_id
            );

            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */
            $calr->amountsettlement = $amountSettlement;
            $calr->amountdiff = $amountDiff;
            $calr->status = 'P';
            $calr->completed_by = null;
            $calr->completed_at = null;
            $calr->updated_by = $username;
            $calr->updated_at = $dt;
            $calr->save();

            /*
            |--------------------------------------------------------------------------
            | Re-create Detail
            |--------------------------------------------------------------------------
            */
            TrRfpNonPurchDetail::query()
                ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
                ->where('refid', $calr->calrnonpurchaseid)
                ->delete();

            foreach ($descs as $i => $desc) {
                $desc = trim((string) ($desc ?? ''));
                $priceRaw = $prices[$i] ?? null;

                if ($desc === '' || $priceRaw === null || $priceRaw === '') {
                    continue;
                }

                $price = $toFloat($priceRaw);

                TrRfpNonPurchDetail::create([
                    'rfpnonpurchaseid' => $calr->rfpnonpurchaseid,
                    'keperluan_detail' => $desc,
                    'amount_request' => 0,
                    'amount_request_penyelesaian' => $price,

                    'budget_perpost' => $dt->year,
                    'budget_cpny_id' => $calr->cpny_id,
                    'budget_business_unit_id' => null,
                    'budget_department_fin_id' => null,
                    'budget_account_id' => null,
                    'budget_activity_id' => null,
                    'budget_activity_descr' => null,

                    'refid' => $calr->calrnonpurchaseid,
                    'status' => 'P',
                    'created_by' => $username,
                    'created_at' => $dt,
                    'updated_by' => $username,
                    'updated_at' => $dt,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Regenerate Approval
            |--------------------------------------------------------------------------
            | Karena dokumen sebelumnya status Revise, approval lama sebaiknya
            | dibatalkan dulu agar alur approval baru bersih.
            */
            // TrApproval::query()
            //     ->where('refnbr', $docid)
            //     ->where('aprv_doctype', $doctype)
            //     ->where('status', '<>', 'X')
            //     ->update([
            //         'status' => 'X',
            //         'updated_by' => $username,
            //         'updated_at' => $dt,
            //     ]);

            $ctx = [
                'ignore_nominal' => false,
                'grand_total' => abs((float) $amountSettlement),
            ];

            [$firstApprovalUsernames] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $calr->cpny_id,
                $calr->department_id,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $calr->completed_by = $firstApprovalUsernames;
                $calr->completed_at = $dt;
                $calr->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Upload Attachment Baru Jika Ada
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $calr->cpny_id,
                    'departementid' => $calr->department_id,
                    'base_folder' => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::connection('pgsql')->rollBack();

                    return response()->json([
                        'message' => 'Failed to update CALR Non Purchase',
                        'error' => 'Gagal upload attachment: ' . $e->getMessage(),
                    ], 500);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Notify First Approver
            |--------------------------------------------------------------------------
            */
            $eid = Hashids::encode($calr->id);

            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $calr->status,
                'CALR Non Purchase',
                url('/showcalrnonpurch/' . $eid),
                [
                    'info' => $calr->keperluan,
                    'createdby' => $calr->created_by,
                    'date' => $dt->toDateTimeString(),
                    'rfpnonpurchaseid' => $rfp->rfpnonpurchaseid,
                    'amountrfp' => $amountRfp,
                    'amountsettlement' => $amountSettlement,
                    'amountdiff' => $amountDiff,
                ]
            );

            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'CALR Non Purchase updated successfully',
                'docid' => $docid,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();

            report($e);

            return response()->json([
                'message' => 'Failed to update CALR Non Purchase',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function receivedCalrNonPurch(Request $request, $hash)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $hasAccess = SysUserRole::where('username', $user->username)
            ->where('role_id', 'APFINACCESS')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update received finance.',
            ], 403);
        }

        $id = Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CALR Non Purchase ID.',
            ], 404);
        }

        $actionType = $request->input('action_type', 'update');

        $calr = TrCalrNonPurch::find($id);
       

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found.',
            ], 404);
        }

        if ($actionType === 'rollback') {
            $calr->userreceive = null;
            $calr->receivedate = null;
            $calr->statusreceive = null;
            $calr->updated_by = $user->username;
            $calr->updated_at = now();
            $calr->save();

            return response()->json([
                'success' => true,
                'message' => 'Finance received rollback successfully.',
            ]);
        }

        $calr->userreceive = $user->username;
        $calr->receivedate = now();
        $calr->statusreceive = 'C';
        $calr->updated_by = $user->username;
        $calr->updated_at = now();
        $calr->save();

        return response()->json([
            'success' => true,
            'message' => 'Finance received updated successfully.',
        ]);
    }

    public function treasuryCalrNonPurch(Request $request, $hash)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $hasAccess = SysUserRole::where('username', $user->username)
            ->where('role_id', 'APTREACCESS')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update treasury.',
            ], 403);
        }

        $id = Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CALR Non Purchase ID.',
            ], 404);
        }

        $actionType = $request->input('action_type', 'update');

        $calr = TrCalrNonPurch::find($id);

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found.',
            ], 404);
        }

        $isReceiveCompleted = $calr->statusreceive === 'C'
            || (!empty($calr->userreceive) && !empty($calr->receivedate));

        if (!$isReceiveCompleted) {
            return response()->json([
                'success' => false,
                'message' => 'Finance received must be completed before treasury update.',
            ], 422);
        }

        if ($actionType === 'rollback') {
            $calr->userpayment = null;
            $calr->paymentdate = null;
            $calr->statuspayment = null;
            $calr->amountpayment = null;
            $calr->amountpenyelesaian = null;
            $calr->updated_by = $user->username;
            $calr->updated_at = now();
            $calr->save();

            return response()->json([
                'success' => true,
                'message' => 'Treasury rollback successfully.',
            ]);
        }

        $calr->userpayment = $user->username;
        $calr->paymentdate = now();
        $calr->statuspayment = 'C';
        $calr->amountpayment = $calr->amountsettlement;
        $calr->amountpenyelesaian = $calr->amountsettlement;
        $calr->updated_by = $user->username;
        $calr->updated_at = now();
        $calr->save();

        return response()->json([
            'success' => true,
            'message' => 'Treasury updated successfully.',
        ]);
    }

    public function reminderCalrNonPurch(Request $request, $hash)
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

        $id = Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CALR Non Purchase ID.',
            ], 404);
        }

        $calr = TrCalrNonPurch::query()
            ->where('id', $id)
            ->first();

        if (!$calr) {
            return response()->json([
                'success' => false,
                'message' => 'CALR Non Purchase not found.',
            ], 404);
        }

        $doctype = 'CAR';

        try {
            $request->merge([
                'docid' => $calr->calrnonpurchaseid,
                'doc_no' => $calr->calrnonpurchaseid,
                'comment' => $request->message,
                'reason' => $request->message,
            ]);

            app(\App\Http\Controllers\SendCommentController::class)
                ->sendmsg((int) $calr->id, $doctype, $request);

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

    public function financeReviseCalrNonPurch(Request $request, $hash)
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

        $id = Hashids::decode($hash)[0] ?? null;

        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CALR Non Purchase ID.',
            ], 404);
        }

        DB::connection('pgsql')->beginTransaction();
        DB::connection('pgsql2')->beginTransaction();

        try {
            $calr = TrCalrNonPurch::query()
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$calr) {
                DB::connection('pgsql')->rollBack();
                DB::connection('pgsql2')->rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'CALR Non Purchase not found.',
                ], 404);
            }

            $doctype = 'CAR';

            /*
            |--------------------------------------------------------------------------
            | Update CALR status menjadi Revise
            |--------------------------------------------------------------------------
            */
            $calr->status = 'D';
            $calr->updated_by = $user->username;
            $calr->updated_at = now();
            $calr->completed_by = $user->username;
            $calr->completed_at = now();
            $calr->save();

            /*
            |--------------------------------------------------------------------------
            | Insert approval row sebagai log revise finance
            |--------------------------------------------------------------------------
            */
            $lastApproval = TrApproval::query()
                ->where('refnbr', $calr->calrnonpurchaseid)
                ->where('aprv_doctype', $doctype)
                ->where('status', '<>', 'X')
                ->orderByDesc('id')
                ->first();

            TrApproval::create([
                'refnbr' => $calr->calrnonpurchaseid,
                'aprv_leveling' => $lastApproval->aprv_leveling ?? 0,
                'aprv_doctype' => $doctype,
                'aprv_cpnyid' => $calr->cpny_id,
                'aprv_departementid' => $calr->department_id,
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
            | Save comment/message
            |--------------------------------------------------------------------------
            */
            $request->merge([
                'docid' => $calr->calrnonpurchaseid,
                'doc_no' => $calr->calrnonpurchaseid,
                'comment' => $request->message,
                'reason' => $request->message,
            ]);

            app(\App\Http\Controllers\SendCommentController::class)
                ->sendmsg((int) $calr->id, $doctype, $request);

            DB::connection('pgsql2')->commit();
            DB::connection('pgsql')->commit();

            return response()->json([
                'success' => true,
                'message' => 'CALR Non Purchase revised successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            DB::connection('pgsql2')->rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revise CALR Non Purchase.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function printPdfCalrNonPurch($hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!\Auth::check()) {
            return redirect()->route('login');
        }

        $calr = TrCalrNonPurch::with(['creator:username,name'])->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Detail settlement CALR
        |--------------------------------------------------------------------------
        | Detail CALR Non Purchase disimpan di tr_rfp_nonpurchase_detail
        | dengan:
        | - rfpnonpurchaseid = ID RCA/RFP asal
        | - refid = calrnonpurchaseid
        */
        $details = TrRfpNonPurchDetail::query()
            ->where('rfpnonpurchaseid', $calr->rfpnonpurchaseid)
            ->where('refid', $calr->calrnonpurchaseid)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Approval
        |--------------------------------------------------------------------------
        */
        $approval = TrApproval::where('refnbr', $calr->calrnonpurchaseid)
            ->where('status', '<>', 'X')
            ->orderBy('aprv_leveling')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Format date
        |--------------------------------------------------------------------------
        */
        $calr->calrnonpurch_date_fmt = $calr->calrnonpurchasedate
            ? \Carbon\Carbon::parse($calr->calrnonpurchasedate)->format('d M Y')
            : '-';

        $calr->receivedate_fmt = $calr->receivedate
            ? \Carbon\Carbon::parse($calr->receivedate)->format('d M Y H:i')
            : '-';

        $calr->paymentdate_fmt = $calr->paymentdate
            ? \Carbon\Carbon::parse($calr->paymentdate)->format('d M Y H:i')
            : '-';

        /*
        |--------------------------------------------------------------------------
        | Terbilang
        |--------------------------------------------------------------------------
        */
        $calr->terbilang = trim($this->terbilang((int) $calr->amountsettlement)) . ' Rupiah';

        /*
        |--------------------------------------------------------------------------
        | Status document
        |--------------------------------------------------------------------------
        */
        $status_doc = match ($calr->status) {
            'P' => 'On Progress',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
            'X' => 'Cancel',
            default => 'Unknown',
        };

        $approve_count = $approval->count();

        /*
        |--------------------------------------------------------------------------
        | Creator
        |--------------------------------------------------------------------------
        */
        $created_by_name = $calr->creator->name ?? null;
        $created_by_username = $calr->created_by;
        $req_date_fmt = $calr->created_at
            ? \Carbon\Carbon::parse($calr->created_at)->format('d M Y H:i')
            : '-';

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */
        $company = MsCompany::where('cpny_id', $calr->cpny_id)->first();
        $cpny_name = $company->cpny_name ?? $calr->cpny_id;

        $pdf = \PDF::loadView('pages.calrnonpurch.pdf_calrnonpurch', [
            'calr' => $calr,
            'details' => $details,
            'approval' => $approval,
            'status_doc' => $status_doc,
            'approve_count' => $approve_count,
            'created_by_name' => $created_by_name,
            'created_by_username' => $created_by_username,
            'req_date_fmt' => $req_date_fmt,
            'cpny_name' => $cpny_name,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("CALR_{$calr->calrnonpurchaseid}.pdf");
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

    private function isTruthy($value): bool
    {
        return in_array(
            strtolower(trim((string) $value)),
            ['1', 'true', 't', 'yes', 'y', 'on'],
            true
        );
    }
}