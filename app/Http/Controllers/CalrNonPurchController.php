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

use App\Models\TrApproval;
use App\Models\TrAttachment;
use App\Models\TrRfpNonPurch;
use App\Models\TrCalrNonPurch;
use App\Models\SysUserRole;
use App\Models\TrRfpNonPurchDetail;

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

        return view('pages.calrnonpurch.calrnonpurch', compact(
            'calrjobs',
            'onProgress',
            'completed',
            'all',
            'rejected',
            'revise'
        ));
    }

    public function json(Request $req)
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
                })
                ->when(!empty($deptList), function ($q) use ($deptList) {
                    $q->whereIn('department_id', $deptList);
                });

            $filterCreator = function ($q) use ($isFinanceAccess, $u) {
                if (!$isFinanceAccess) {
                    $q->where('created_by', $u);
                }
            };

            if ($scope === 'onprogress') {
                $base->where('status', 'P')->where($filterCreator);
            } elseif ($scope === 'completed') {
                $base->where('status', 'C')->where($filterCreator);
            } elseif ($scope === 'rejected') {
                $base->where('status', 'R')->where($filterCreator);
            } elseif ($scope === 'revise') {
                $base->where('status', 'D')->where($filterCreator);
            } else {
                // all
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

                $r->amountrequestpayment_fmt = number_format(
                    (float) ($r->amountrequestpayment ?? 0),
                    2
                );
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

                    'budget_perpost' => $year,
                    'budget_cpny_id' => $rfp->cpny_id,
                    'budget_business_unit_id' => null,
                    'budget_department_fin_id' => null,
                    'budget_account_id' => null,
                    'budget_activity_id' => null,
                    'budget_activity_descr' => null,

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

        return view('pages.calrnonpurch.showcalrnonpurch', compact(
            'calr',
            'rfp',
            'details',
            'hash',
            'doctype',
            'refnbr',
            'canUpload'
        ));
    }
}