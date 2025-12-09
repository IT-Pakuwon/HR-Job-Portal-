<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Company;
use App\Models\Dept;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrIMBudget;
use App\Models\TrIMBudgetdetail;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use Mail;
use Illuminate\Support\Facades\Log;
use PDF;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use App\Models\BudgetDetail;

class IMBudgetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $u       = $user->username ?? '';
        $deptId  = $user->department_id ?? null;
        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // Cek role FINACCESS
        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        // Base filter: always filter by cpny_id; tambah department_id jika bukan FINACCESS
        $baseFilter = function ($q) use ($cpnyList, $isFinanceAccess, $deptId) {
            if (!empty($cpnyList)) {
                $q->whereIn('cpny_id', $cpnyList);
            }
            if (!$isFinanceAccess && $deptId) {
                $q->where('department_id', $deptId);
            }
        };

        $all = TrIMBudget::where($baseFilter)->count();

        $onProgress = TrIMBudget::where($baseFilter)
            ->where('status', 'P')
            ->count();

        $hold = TrIMBudget::where($baseFilter)
            ->where('status', 'H')
            ->count();

        $revise = TrIMBudget::where($baseFilter)
            ->where('status', 'D')
            ->count();

        $reject = TrIMBudget::where($baseFilter)
            ->where('status', 'R')
            ->count();

        $cancel = TrIMBudget::where($baseFilter)
            ->where('status', 'X')
            ->count();

        $completed = TrIMBudget::where($baseFilter)
            ->where('status', 'C')
            ->count();

        return view('pages.imbudgets.imbudgets', compact(
            'all',
            'onProgress',
            'reject',
            'cancel',
            'completed',
            'hold',
            'revise'
        ));
    }


    public function json(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
        }

        $u        = $user->username ?? '';
        $deptId   = $user->department_id ?? null;
        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // cek FINACCESS
        $isFinanceAccess = SysUserRole::where('username', $u)
            ->where('role_id', 'FINACCESS')
            ->exists();

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // bisa 'H', 'D', 'H,D', atau ''

        $baseTable = (new TrIMBudget)->getTable(); // "tr_imbudget"

        $columns = [
            0 => 'imb.imbudgetid',
            1 => 'imb.imbudgetdate',
            2 => 'imb.csid',
            3 => 'imb.sppbjktid',
            4 => 'imb.cpny_id',
            5 => 'imb.user_peminta',
            6 => 'imb.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'imb.imbudgetdate';

        $base = TrIMBudget::from($baseTable . ' as imb')
            ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                $q->whereIn('imb.cpny_id', $cpnyList);
            })
            ->when(!$isFinanceAccess && $deptId, function ($q) use ($deptId) {
                $q->where('imb.department_id', $deptId);
            });

        // === Filter status (dukung CSV: "H,D") ===
        if ($status !== '') {
            $statuses = array_values(array_filter(array_map('trim', explode(',', $status))));
            if (!empty($statuses)) {
                $base->whereIn('imb.status', $statuses);
            }
        }

        // total sebelum filter search
        $recordsTotal = (clone $base)->count();

        // search (PostgreSQL ILIKE)
        if ($search !== '') {
            $like = "%{$search}%";
            $base->where(function ($q) use ($like) {
                $q->where('imb.imbudgetid', 'ilike', $like)
                    ->orWhere('imb.csid', 'ilike', $like)
                    ->orWhere('imb.sppbjktid', 'ilike', $like)
                    ->orWhere('imb.cpny_id', 'ilike', $like)
                    ->orWhere('imb.user_peminta', 'ilike', $like)
                    ->orWhere('imb.status', 'ilike', $like);
            });
        }

        // total setelah search
        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
                'imb.id as rid',
                'imb.imbudgetid',
                'imb.imbudgetdate',
                'imb.csid',
                'imb.sppbjktid',
                'imb.cpny_id',
                'imb.user_peminta',
                'imb.status',
                'imb.created_by'
            )
            ->orderBy($orderCol, $orderDir)
            ->orderBy('imb.imbudgetid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $rows->transform(function ($row) {
            $row->imbudgetdate = $row->imbudgetdate
                ? \Carbon\Carbon::parse($row->imbudgetdate)->format('Y-m-d')
                : null;

            // Hashids berbasis PK numerik
            $row->eid = null;
            if (!is_null($row->rid)) {
                $row->eid = \Vinkla\Hashids\Facades\Hashids::encode((int) $row->rid);
            }
            if (!$row->eid && $row->imbudgetid) {
                $row->eid = rawurlencode($row->imbudgetid); // fallback aman
            }
            unset($row->rid);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }



    
    
    public function generateIMBudget(Request $request) 
    {
        // --- Ambil CS header & detail ---
        $csid = $request->input('csid', 'CS25100015'); // default contoh
        $cs = TrCS::where('csid', $csid)->first();
        if (!$cs) {
            return response()->json([
                'success' => false,
                'message' => "CS dengan csid '{$csid}' tidak ditemukan."
            ], 404);
        }
        $rows = TrCSdetail::where('csid', $csid)->get();

        // --- Konfigurasi dasar ---
        $doctype  = 'IM';
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        // helper angka lokal
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') return null;
            $s = preg_replace('/\s+/', '', (string)$v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot   = strpos($s, '.') !== false;
            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot   = strrpos($s, '.');
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
            return is_numeric($s) ? (float)$s : null;
        };

        // --- Mapping kolom dari CS header ---
        $cpnyid        = $cs->cpny_id         ?? $cs->cpnyid         ?? $request->input('cpnyid');
        $departementid = $cs->departementid   ?? $cs->department_id  ?? $request->input('departementid');
        $perpost       = $cs->budget_perpost  ?? $cs->perpost        ?? $request->input('perpost');
        $sppbjktid     = $cs->sppbjktid       ?? $request->input('sppbjktid');
        $user_peminta  = $cs->user_peminta    ?? $request->input('user_peminta', $username);
        $imbudgetnote  = $cs->imbudgetnote    ?? $cs->csnote         ?? $cs->note ?? $request->input('imbudgetnote');

        // === Approval engine (pakai cpny/dept hasil mapping)
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyid, $departementid);

        DB::beginTransaction();
        try {
            // === autonbr & docid ===
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr($year, 2) . $month;   // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // === 1) HEADER IMBudget ===
            $header = new TrIMBudget();
            $header->imbudgetid               = $docid;
            $header->imbudgetdate             = $dt->toDateString();
            $header->csid                     = $csid;
            $header->sppbjktid                = $sppbjktid;
            $header->cpny_id                  = $cpnyid;
            $header->department_id            = $departementid;
            $header->user_peminta             = $user_peminta;
            $header->imbudgetnote             = $imbudgetnote;
            $header->budget_perpost           = $perpost;
            $header->total_budget_needed      = 0;
            $header->total_budget_requested   = 0;
            $header->status                   = 'P';
            $header->created_by               = $username;
            $header->save();

            // === 2) AGREGASI DETAIL CS → GROUPING
            // Tentukan cara hitung amount per baris CSdetail:
            $rowAmount = function($d) use ($toFloat) {
                // Cari vendor yang selected (1..6)
                for ($i=1;$i<=6;$i++){
                    $sel = (bool) ($d->{"vendor{$i}selected"} ?? false);
                    if ($sel) {
                        $tot = $toFloat($d->{"vendortotalprice{$i}"} ?? null);
                        if ($tot !== null) return max($tot, 0.0);
                    }
                }
                // fallback: qty * last price
                $qty   = $toFloat($d->qty) ?? 0.0;
                $price = $toFloat($d->inventory_last_price) ?? 0.0;
                return max($qty * $price, 0.0);
            };

            // key grouping
            $groups = []; // key => ['sum'=>..., 'sample'=>TrCSdetail]
            foreach ($rows as $d) {
                // mapping kunci budget (dengan fallback)
                $g_perpost   = $d->budget_perpost               ?? $perpost;
                $g_cpny      = $d->budget_cpny_id               ?? $cpnyid;
                $g_bu        = $d->budget_business_unit_id      ?? null;
                $g_deptfin   = $d->budget_department_fin_id     ?? null;
                $g_account   = $d->budget_account_id            ?? null;
                $g_activity  = $d->budget_activity_id           ?? null;
                $g_actdescr  = $d->budget_activity_descr        ?? null;

                $amount = $rowAmount($d);
                if ($amount <= 0) continue;

                $key = implode('|', [
                    (string)$g_perpost,
                    (string)$g_cpny,
                    (string)$g_bu,
                    (string)$g_deptfin,
                    (string)$g_account,
                    (string)$g_activity,
                ]);

                if (!isset($groups[$key])) {
                    $groups[$key] = [
                        'sum'      => 0.0,
                        'perpost'  => $g_perpost,
                        'cpny'     => $g_cpny,
                        'bu'       => $g_bu,
                        'deptfin'  => $g_deptfin,
                        'account'  => $g_account,
                        'activity' => $g_activity,
                        'actdescr' => $g_actdescr,
                    ];
                }
                $groups[$key]['sum'] += $amount;
            }

            if (empty($groups)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Tidak ada nilai expense yang valid untuk CS {$csid}.",
                ], 422);
            }

            // Helper ambil month index dari perpost (boleh 202510/2025-10/2025-10-xx)
            $parseMonthIndex = function($perpost): int {
                // terima "YYYYMM" atau "YYYY-MM" atau "YYYY-MM-DD"
                $s = preg_replace('/[^0-9]/', '', (string)$perpost); // buang non-digit
                // ambil 2 digit terakhir sebagai bulan
                $mm = (int)substr($s, -2);
                if ($mm < 1 || $mm > 12) $mm = (int)date('n');
                return $mm;
            };

            // Helper ambil remain dari ms_budget periode terkait
            $getMonthlyRemain = function($perpost, $cpny, $bu, $deptfin, $account, $activity): float {
                $mm = $parseMonthIndex = function($p){
                    $s = preg_replace('/[^0-9]/', '', (string)$p);
                    $mm = (int)substr($s, -2);
                    if ($mm < 1 || $mm > 12) $mm = (int)date('n');
                    return $mm;
                };
                $month = $mm($perpost);
                $idx = str_pad($month, 2, '0');

                $row = BudgetDetail::where('perpost', $perpost)
                    ->where('cpny_id', $cpny)
                    ->when($bu,      fn($q)=>$q->where('business_unit_id', $bu))
                    ->when($deptfin, fn($q)=>$q->where('department_fin_id', $deptfin))
                    ->when($account, fn($q)=>$q->where('account_id', $account))
                    ->when($activity,fn($q)=>$q->where('activity_id', $activity))
                    ->first();

                if (!$row) return 0.0;

                // field dinamis per bulan
                $bgt   = (float)($row->{"period{$idx}_budget"}      ?? 0);
                $bgtA  = (float)($row->{"period{$idx}_budget_add"}  ?? 0);
                $used  = (float)($row->{"period{$idx}_used"}        ?? 0);
                $resv  = (float)($row->{"period{$idx}_reserve"}     ?? 0);

                $remain = ($bgt + $bgtA) - ($used + $resv);
                return max($remain, 0.0);
            };

            // === 3) INSERT DETAIL hasil GROUP & hitung totals
            $sumNeeded    = 0.0;
            $sumRequested = 0.0;

            foreach ($groups as $g) {
                $expense = (float)$g['sum'];
                $remain  = (float)$getMonthlyRemain(
                    $g['perpost'], $g['cpny'], $g['bu'], $g['deptfin'], $g['account'], $g['activity']
                );
                $needed  = max($expense - $remain, 0.0);

                // Simpan satu baris detail per GRUP
                $detail = new TrIMBudgetdetail();
                $detail->imbudgetid                   = $docid;
                $detail->csid                         = $csid;
                $detail->sppbjktid                    = $sppbjktid;

                $detail->budget_perpost               = $g['perpost'];
                $detail->budget_cpny_id               = $g['cpny'];
                $detail->budget_business_unit_id      = $g['bu'];
                $detail->budget_department_fin_id     = $g['deptfin'];
                $detail->budget_account_id            = $g['account'];
                $detail->budget_activity_id           = $g['activity'];
                $detail->budget_activity_descr        = $g['actdescr'];

                // kolom hasil perhitungan agregat
                $detail->amount_expense               = $expense; // <-- pastikan kolom tersedia
                $detail->budget_remain                = $remain;  // <-- pastikan kolom tersedia
                $detail->budget_needed                = $needed;  // aturan: hanya jika expense > remain

                // kompatibilitas: pakai requested = expense (kalau masih dipakai di header)
                $detail->budget_requested             = $expense;

                $detail->note                         = null;
                $detail->status                       = 'P';
                $detail->created_by                   = $username;
                $detail->save();

                $sumRequested += $expense;
                $sumNeeded    += $needed;
            }

            // === 4) update total header ===
            $header->total_budget_requested = $sumRequested;
            $header->total_budget_needed    = $sumNeeded;
            $header->save();

            // === 5) Generate TrApproval NORMAL via ApprovalController ===
            $ctx = ['ignore_nominal' => true];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $cpnyid,
                $departementid,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            $eid = Hashids::encode($header->id);
            // === 6) Notifikasi approver pertama
            // if ($linesCount > 0) {
            //     $approvalCtl->notifyFirstApprover(
            //         $docid,
            //         $doctype,
            //         $header->status,
            //         'IMBudget',
            //         url('/showimbudgets/' . $eid),
            //         [
            //             'info'      => $imbudgetnote,
            //             'createdby' => $header->created_by,
            //             'date'      => $dt->toDateTimeString(),
            //         ]
            //     );
            // }

            $status     = $header->status;
            $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];

            $data = [
                'docid'     => $docid,
                'cpnyid'    => $header->cpny_id,
                'deptname'  => $header->department_id,
                'date'      => $header->imbudgetdate,
                'name'      => $header->user_peminta,
                'createdby' => 'system',
                'info'      => 'Request IM Budget Department '.$header->department_id,
                'status'    => $status,
                'docname'   => 'IM Budget',
                'url'       => url('/editimbudgets/' . $eid),
            ];

            $approvers = array_filter(array_map('trim', explode(',', (string)$header->user_peminta)));
            $emails    = User::whereIn('username', $approvers)->where('status', 'A')->pluck('notification_email');

            foreach ($emails as $email) {
                \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectMap, $status) {
                    $message->to($email)
                        ->subject($data['docid'].' - '.($subjectMap[$status] ?? 'Notification').' IM Budget')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }

            DB::commit();

            return response()->json([
                'success'                => true,
                'message'                => 'IMBudget created successfully',
                'imbudgetid'             => $docid,
                'total_budget_needed'    => $sumNeeded,
                'total_budget_requested' => $sumRequested,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create IMBudget',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


   
    public function editIMBudget($hash)
    {
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imbudget = TrIMBudget::findOrFail($id);

        // Ambil detail + eager load relasi lokasi & sublokasi
        $imbudgetdetail = TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)
            ->get();

        $user   = request()->user();
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $rows = TrAttachment::where('refnbr', $imbudget->imbudgetid)
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
             

        return view('pages.imbudgets.editimbudgets', compact(
            'imbudget','imbudgetdetail','usercpny','usercpny2','userdept','userdept2','hash','attachments'
        ));
    }

    public function updateIMBudget(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'IM tidak ditemukan.');

        $user      = $request->user();
        $dt        = Carbon::now();
        $doctype   = 'IM';
        $username  = $user->username ?? 'system';

        // helper angka "1.234,56" => 1234.56
        $toFloat = function ($v): float {
            if ($v === null || $v === '') return 0.0;
            $s = preg_replace('/\s+/', '', (string)$v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot   = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                // anggap format ID: titik = thousand, koma = decimal
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot && substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            }
            return is_numeric($s) ? (float)$s : 0.0;
        };

        // Ambil header IM
        $header = TrIMBudget::findOrFail($id);

        // Validasi minimal (kalau perlu)
        // $request->validate([...]);

        // Data header dari form
        $cpnyId   = $request->input('cpnyid');
        $deptId   = $request->input('departementid');
        $perpost  = $request->input('perpost');
        $imbudgetnote= $request->input('imbudgetnote');

        // Arrays detail dari form (edit versi ringkas)
        $detailIds          = array_values($request->input('detail_id', []));
        $coaIds             = array_values($request->input('budget_account_id', []));
        $actIds             = array_values($request->input('budget_activity_id', []));
        $actDescrs          = array_values($request->input('budget_activity_descr', []));
        $amountExpensesVis  = array_values($request->input('amount_expense', []));     // numeric hidden
        $budgetRemainsVis   = array_values($request->input('budget_remain', []));      // numeric hidden
        $budgetNeededsVis   = array_values($request->input('budget_needed', []));      // numeric hidden
        $budgetRequesteds   = array_values($request->input('budget_requested', []));   // editable (ID format)
        $notes              = array_values($request->input('note', []));               // editable

        // Pastikan line approval tersedia untuk konteks IM
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyId, $deptId);

        DB::beginTransaction();
        try {
            // 1) Update HEADER
            $header->cpny_id        = $cpnyId;
            $header->department_id  = $deptId;
            $header->budget_perpost = $perpost;
            $header->imbudgetnote      = $imbudgetnote;
            $header->status         = 'P';               // submit approval dari mode edit
            $header->updated_by     = $username;
            $header->save();

            // 2) Update DETAIL (hanya request & note, angka lain readonly)
            $rowCount = max(count($detailIds), count($budgetRequesteds));
            $totalRequested = 0.0;
            $totalNeeded    = 0.0; // bila ingin ikut diakumulasi dari hidden

            for ($i = 0; $i < $rowCount; $i++) {
                $detailId     = $detailIds[$i]          ?? null;
                $budgetReqVis = $budgetRequesteds[$i]   ?? null; // "1.234,56"
                $note         = $notes[$i]              ?? null;

                // Hidden numerik (pastikan numeric double di DB)
                $amountExpense = (float) ($amountExpensesVis[$i] ?? 0);
                $budgetRemain  = (float) ($budgetRemainsVis[$i]  ?? 0);
                $budgetNeeded  = (float) ($budgetNeededsVis[$i]  ?? 0);

                // Parse budget requested display -> float
                $budgetRequested = $toFloat($budgetReqVis);

                // Simpan hanya jika detail id valid
                if ($detailId) {
                    $detail = TrIMBudgetdetail::where('id', $detailId)
                        ->where('imbudgetid', $header->imbudgetid)
                        ->first();

                    if ($detail) {
                        $detail->budget_requested   = $budgetRequested;
                        $detail->note               = $note;

                        // Kalau ingin “sinkronisasi tampilan” (opsional): simpan readonly juga (aman)
                        // $detail->amount_expense  = $amountExpense;
                        // $detail->budget_remain   = $budgetRemain;
                        // $detail->budget_needed   = $budgetNeeded;

                        $detail->updated_by         = $username;
                        $detail->save();

                        $totalRequested += (float)$detail->budget_requested;
                        $totalNeeded    += (float)($detail->budget_needed ?? 0);
                    }
                }
            }

            // 3) Update total header
            $header->total_budget_requested = $totalRequested;
            // Opsional: jika mau tampilan ringkas “needed” ikut terisi di header
            if ($totalNeeded > 0) {
                $header->total_budget_needed = $totalNeeded;
            }
            $header->save();

            // 4) Generate TrApproval utk dokumen IM
            $ctx = [
                'ignore_nominal' => false,
                'grand_total'    => (float)$totalRequested, // kalau engine perlu total
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $header->imbudgetid,   // refnbr IM
                $doctype,              // 'IM'
                $cpnyId,
                $deptId,
                $username,
                $ctx,
                $dt
            );

            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // 5) Attachment (opsional)
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $header->imbudgetid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files = (array)$request->file('attachments');
                $uploader = app(TrAttachmentController::class);
                $uploadResult = $uploader->uploadInternal($meta, $files);
            }

            // 6) Notif approver pertama (kalau ada line)
            if ($linesCount > 0) {
                $eidIM = Hashids::encode($header->id); // hash id numerik TrIMBudget
                $approvalCtl->notifyFirstApprover(
                    $header->imbudgetid,
                    $doctype,
                    $header->status, // 'P'
                    'IMBudget',
                    url('/showimbudgets/' . $eidIM),
                    [
                        'info'      => $header->imbudgetnote ?? '',
                        'createdby' => $header->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
                );
            }

            DB::commit();
            return response()->json([
                'message' => 'IMBudget updated & submitted successfully',
                'total_budget_requested' => $totalRequested,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Update failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

      
 

    public function showIMBudget($hash)
    {        
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        // $imbudget = TrIMBudget::findOrFail($id);
        $imbudget = TrIMBudget::with([           
            'creator:username,name'
        ])
        ->findOrFail($id);        

        $imbudgetdetail = TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)
            ->get();
        
        $approval = T_approval::where('docid', $imbudget->imbudgetid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        // $attachment = Attachment::where('docid', $imbudget->imbudgetid)    
        //     ->where('status','A')        
        //     ->get();    
        
        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $imbudget->imbudgetid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // siapkan Signed URL dari GCS
        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        // map jadi data siap pakai di view
        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/wo/2025/xxxx-file.pdf
            $object     = $bucket->object($objectPath);

            // Signed URL 10 menit
            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                // kalau gagal signed URL, biarkan null; di UI tampilkan nama saja
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [                
                'display_name' => $r->attachment_name,         // nama yang enak dibaca
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,                  // bisa null jika gagal
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });
        
       
        return view('pages.imbudgets.showimbudgets', compact('imbudget','approval','attachments','imbudgetdetail','hash'));
    }

    

    public function approveIMBudget(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IM';

        $imbudget = TrIMBudget::with('creator')->where('imbudgetid', $docid)->first();
        if (!$imbudget) return response()->json(['success'=>false,'message'=>'IMBudget not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($imbudget->id);
        $docUrl   = url('/showimbudgets/' . $eid);
        $fullname = data_get($imbudget, 'creator.name') ?: $imbudget->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $imbudget->imbudgetid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($imbudget, $fullname, $docUrl) {
                $imbudget->status       = 'C';
                $imbudget->completed_by = $imbudget->completed_by ?: auth()->user()->username;
                $imbudget->completed_at = $now;
                $imbudget->save();

                TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)->update(['status' => 'C']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $imbudget->imbudgetid,
                    'IMBudget',
                    'C',
                    $imbudget->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $imbudget->cpny_id ?? $imbudget->cpnyid ?? '',
                        'deptname' => $imbudget->department_id ?? $imbudget->departementid ?? '',
                        'date'     => $imbudget->imbudgetdate,
                        'info'     => $imbudget->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($imbudget, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $imbudget->imbudgetid,
                    'IM',
                    'P',
                    'IMBudget',
                    $docUrl,
                    [
                        'info'      => $imbudget->keperluan,
                        'createdby' => $imbudget->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $imbudget->completed_by = auth()->user()->username;
                $imbudget->completed_at = $now;
                $imbudget->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function rejectIMBudget(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IM';

        $imbudget = \App\Models\TrIMBudget::with('creator')->where('imbudgetid', $docid)->first();
        if (!$imbudget) return response()->json(['success'=>false,'message'=>'IMBudget not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($imbudget->id);
        $docUrl   = url('/showimbudgets/' . $eid);
        $fullname = data_get($imbudget, 'creator.name') ?: $imbudget->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $imbudget->imbudgetid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($imbudget, $fullname, $docUrl) {
                $imbudget->status       = 'R';
                $imbudget->completed_by = auth()->user()->username;
                $imbudget->completed_at = $now;
                $imbudget->save();

                // optional: tandai detail R
                // \App\Models\TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $imbudget->imbudgetid,
                    'IMBudget',
                    'R',
                    $imbudget->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $imbudget->cpny_id ?? $imbudget->cpnyid ?? '',
                        'deptname' => $imbudget->department_id ?? $imbudget->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $imbudget->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($imbudget->id, 'IM', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'IMBudget rejected successfully']);
    }

    public function reviseIMBudget(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'IM';

        $imbudget = \App\Models\TrIMBudget::with('creator')->where('imbudgetid', $docid)->first();
        if (!$imbudget) return response()->json(['success'=>false,'message'=>'IMBudget not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($imbudget->id);
        $docUrl   = url('/showimbudgets/' . $eid);
        $fullname = data_get($imbudget, 'creator.name') ?: $imbudget->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $imbudget->imbudgetid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($imbudget, $fullname, $docUrl) {
                // === HEADER IMBudget -> D ===
                $imbudget->status       = 'D';
                $imbudget->completed_by = auth()->user()->username;
                $imbudget->completed_at = $now;
                $imbudget->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $imbudget->imbudgetid,
                    'IMBudget',
                    'D',
                    $imbudget->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $imbudget->cpny_id ?? $imbudget->cpnyid ?? '',
                        'deptname' => $imbudget->department_id ?? $imbudget->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $imbudget->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($imbudget->id, 'IM', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'IMBudget revised successfully']);
    }
    
    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imbudget = TrIMBudget::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($imbudget->created_by ?? null);
        $createdAt     = $imbudget->created_at ? \Carbon\Carbon::parse($imbudget->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($imbudget->completed_by ?? null);
        $completedAt     = $imbudget->completed_at ? \Carbon\Carbon::parse($imbudget->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($imbudget->rejected_by ?? null);
        $rejectedAt      = isset($imbudget->rejected_at) ? \Carbon\Carbon::parse($imbudget->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($imbudget->revised_by ?? null);
        $revisedAt       = isset($imbudget->revised_at) ? \Carbon\Carbon::parse($imbudget->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($imbudget->status ?? '');
        $labelMap = [
            'P' => 'Waiting approval',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
        ];
        $statusLabel = $labelMap[$status] ?? $status;

        // selalu mulai dari Submitted
        $steps = [[
            'key'          => 'submitted',
            'title'        => 'IMBudget',
            'status'       => 'C',              // dibuat = completed
            'status_label' => 'Submitted',
            'by'           => $createdByName,
            'at'           => $createdAt,
        ]];

        switch ($status) {
            case 'P':
                // masih menunggu/berjalan → tampilkan Approval saja
                $steps[] = [
                    'key'          => 'approval',
                    'title'        => 'Approval',
                    'status'       => 'P',
                    'status_label' => 'Waiting approval',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'R':
                // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
                $steps[] = [
                    'key'          => 'rejected',
                    'title'        => 'Rejected',
                    'status'       => 'R',
                    'status_label' => 'Rejected',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'D':
                // REVISE → Submitted → Revise
                $steps[] = [
                    'key'          => 'revise',
                    'title'        => 'Revise',
                    'status'       => 'D',
                    'status_label' => 'Revise',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'C':
                // SELESAI → bisa langsung Submitted → Completed
                // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
                // tambahkan step 'approval' sebelum 'completed')
                $steps[] = [
                    'key'          => 'completed',
                    'title'        => 'Completed',
                    'status'       => 'C',
                    'status_label' => 'Completed',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            default:
                // status tidak dikenal → biarkan hanya Submitted
                break;
        }

        return response()->json([
            'doc'   => $imbudget->imbudgetid ?? (string)$imbudget->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printIMBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil IMBudget + relasi yang dibutuhkan
        $imbudget = TrIMBudget::with([           
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris IMBudget
        $imbudgetdetail = TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)
            ->get();

        // Approval list (non-cancelled)
        $approval = TrApproval::query()
            ->where('refnbr', $imbudget->imbudgetid)          // dulu: docid
            ->where('status', '<>', 'X')           
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at', 'ASC')            // tie-breaker kalau leveling sama
            ->get();
        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $imbudget->cpny_id)->first();

        // Mapping status dokumen
        switch ($imbudget->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        $data = [
            'title'               => 'Internal Memo Budget',
            'doc_type'            => 'IMBudget',
            'docid'               => $imbudget->imbudgetid,
            'department_id'       => $imbudget->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $imbudget->created_by,
            'created_by_name'     => ucwords(strtolower(optional($imbudget->creator)->name)),
            'created_at_fmt'      => optional($imbudget->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($imbudget->created_at)->format('d M Y H:i'),
            'imbudgetdate'            => \Carbon\Carbon::parse($imbudget->imbudgetdate)->format('d F Y'),
            // konten
            'imbudgetnote'           => $imbudget->imbudgetnote,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($imbudget->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.imbudgets.pdf_imbudgets',
            array_merge($data, [
                'detail'         => $imbudgetdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_imbudgets_{$imbudget->imbudgetid}.pdf");
    }





    






}
