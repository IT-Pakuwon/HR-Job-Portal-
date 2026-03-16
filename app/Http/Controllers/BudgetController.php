<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\DepartmentFin;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use Mail;
use App\Imports\MsBudgetTempImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Models\MsBudgetTemp;
use App\Models\BusinessUnit;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use App\Models\SysUserRole;
use App\Models\SysAccessRight;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Http\Controllers\Traits\HasAutonbr;



class BudgetController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $all = $this->applyBudgetFilter(Budget::query())->count();

        $onProgress = $this->applyBudgetFilter(
                            Budget::where('status', 'P')
                        )->count();

        $reject = $this->applyBudgetFilter(
                            Budget::where('status', 'R')
                        )->count();

        $revise = $this->applyBudgetFilter(
                            Budget::where('status', 'D')
                        )->count();

        $completed = $this->applyBudgetFilter(
                            Budget::where('status', 'C')
                        )->count();

        $businessUnits = BusinessUnit::orderBy('business_unit_name')->get();
        $departments = DepartmentFin::select('department_fin_id', 'department_name')
            ->groupBy('department_fin_id', 'department_name')
            ->orderBy('department_name')
            ->get();

        return view(
            'pages.budgets.budgets',
            compact('all', 'onProgress', 'reject', 'revise', 'completed', 'businessUnits', 'departments')
        );
    }

    public function json(Request $request)
    {
        $status         = $request->query('status');
        $businessUnit   = $request->query('business_unit');
        $department     = $request->query('department');

        $query = Budget::with(['businessUnit', 'departmentFin']);

        // ✅ Apply role-based access filter first
        $query = $this->applyBudgetFilter($query);

        // ✅ Status filter
        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }

        // ✅ Business Unit filter
        if (!empty($businessUnit)) {
            $query->where('business_unit_id', $businessUnit);
        }

        // ✅ Department filter
        if (!empty($department)) {
            $query->where('department_fin_id', $department);
            // ⚠️ change column name if different in your DB
        }

        $budget = $query->orderBy('id', 'desc')->get();

        $budget->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            $row->business_unit_name = $row->businessUnit->business_unit_name ?? null;
            $row->department_name    = $row->departmentFin->department_name ?? null;

            unset($row->businessUnit, $row->departmentFin);
            return $row;
        });

        return response()->json(['data' => $budget]);
    }

    private function applyBudgetFilter($query)
    {
        $user = Auth::user();

        // cpny_id bisa "AW" atau "AW,EP,PSA,GPS"
        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // department_id juga bisa multi, tapi di debug sudah "IT"
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        // Normal: ambil mapping dept -> dept_fin
        $departmentFinIds = MsDepartment::whereIn('department_id', $deptIds)
            ->distinct()
            ->pluck('department_fin_id')
            ->toArray();

        $full = $this->hasFullAccess();


        // Kalau FULLACCESS → filter company saja
        if ($full) {
            return $query->whereIn('cpny_id', $cpnyIds);
        }

        // Normal → filter company + department_fin_id
        return $query->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_fin_id', $departmentFinIds);
    }

    private function hasFullAccess()
    {
        $user = Auth::user();

        $roleIds = SysUserRole::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('role_id');

        // DEBUG SEMENTARA
        // dd(['username' => $user->username, 'roleIds' => $roleIds]);

        if ($roleIds->isEmpty()) {
            return false;
        }

        $q = SysAccessRight::whereIn('role_id', $roleIds)
            ->where('screen_id', 'BUDGET')
            ->where('access_name', 'FULLACCESS')
            ->where('access_right', true)
            ->where('status', 'A');

        return $q->exists();
    }

    public function createBudget()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ambil daftar company yg user boleh
        $userCpnyIds = Usercpny::where('username', $user->username)
            ->where('status', 'A') // kalau ada kolom status
            ->pluck('cpny_id')
            ->map(fn($x) => strtoupper(trim($x)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // company dropdown hanya dari akses user
        $companies = MsCompany::select('cpny_id','cpny_name')
            ->where('status', 'A')
            ->when(!empty($userCpnyIds), fn($q) => $q->whereIn('cpny_id', $userCpnyIds))
            ->orderBy('cpny_name')
            ->get();

        $temp_id = session('import_temp_id');

        $tempData = [];
        if ($temp_id) {
            $tempData = MsBudgetTemp::where('temp_budget_id', $temp_id)->get();
        }

        return view('pages.budgets.createbudgets', compact('companies','tempData','temp_id'));
    }

    public function createBudget_xxx()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        $user = request()->user();

        $companies = MsCompany::select('cpny_id','cpny_name')->where('status','A')->get();

        $usercpny = Usercpny::where('username', $user->username)->pluck('cpny_id')->toArray();

        $temp_id = session('import_temp_id'); // ambil dari session

        $tempData = [];
        if ($temp_id) {
            $tempData = MsBudgetTemp::where('temp_budget_id', $temp_id)->get();
        }


        return view('pages.budgets.createbudgets', compact('companies','tempData','temp_id','usercpny'));
    }


    public function import(Request $request, $hash = null)
    {
        $request->validate([
            'file'              => 'required|mimes:xlsx,xls,csv',
            'cpny_id'           => 'required',
            'business_unit_id'  => 'required',
            'department_fin_id' => 'required',
        ]);

        // Jika edit, decode hash -> ambil Budget
        $budget = null;
        if ($hash) {
            $decoded = Hashids::decode($hash);
            $id = $decoded[0] ?? null;
            abort_if(!$id, 404, 'Invalid budget hash.');
            $budget = Budget::findOrFail($id);
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id  = Str::uuid()->toString();

            MsBudgetTemp::where('created_by', $username)->delete();

            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            $perpostFromExcel = null;

            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $spreadsheet = IOFactory::load($file->getPathname());

                // =========================
                // ✅ AMBIL PERPOST DARI ROW 1
                // =========================
                $sheet0 = $spreadsheet->getSheet(0);

                // Asumsi perpost ada di A2 (paling umum kalau row1 berisi perpost)
                $a1 = $sheet0->getCell('A2')->getValue();
                if ($a1 !== null && $a1 !== '') {
                    // ambil angka 4 digit (misal 2026) kalau formatnya "2026" atau "Perpost: 2026"
                    if (preg_match('/\b(19|20)\d{2}\b/', (string)$a1, $m)) {
                        $perpostFromExcel = (int)$m[0];
                    } elseif (is_numeric($a1)) {
                        $perpostFromExcel = (int)$a1;
                    }
                }

                // fallback: scan row 1 beberapa kolom kalau A2 bukan perpost
                if (!$perpostFromExcel) {
                    $highestCol = $sheet0->getHighestDataColumn();
                    for ($col = 'A'; $col <= $highestCol; $col++) {
                        $v = $sheet0->getCell("{$col}1")->getValue();
                        if ($v === null || $v === '') continue;

                        if (preg_match('/\b(19|20)\d{2}\b/', (string)$v, $m)) {
                            $perpostFromExcel = (int)$m[0];
                            break;
                        }
                    }
                }

                if (!$perpostFromExcel) {
                    throw new \RuntimeException("Perpost (tahun) tidak ditemukan di row 1 Excel. Pastikan row 1 berisi tahun (mis: 2026).");
                }

                // =========================
                // ✅ CEK BUDGET SUDAH ADA?
                // =========================
                // $exists = Budget::query()
                //     ->where('cpny_id', $request->cpny_id)
                //     ->where('business_unit_id', $request->business_unit_id)
                //     ->where('department_fin_id', $request->department_fin_id)
                //     ->where('status', 'C')
                //     ->where('perpost', $perpostFromExcel)
                //     ->when($budget, function ($q) use ($budget) {
                //         // kalau edit mode, abaikan record yang sedang diedit
                //         $q->where('id', '<>', $budget->id);
                //     })
                //     ->exists();

                // if ($exists) {
                //     throw new \RuntimeException(
                //         "Import ditolak: Budget sudah ada untuk Perpost {$perpostFromExcel} (status C) pada company/BU/Dept tersebut."
                //     );
                // }

                // =========================
                // ✅ VALIDASI: TOLAK FORMULA
                // =========================
                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $highestRow = $sheet->getHighestDataRow();
                    $highestCol = $sheet->getHighestDataColumn();

                    for ($row = 1; $row <= $highestRow; $row++) {
                        for ($col = 'A'; $col <= $highestCol; $col++) {
                            $cell = $sheet->getCell("{$col}{$row}");
                            $raw  = $cell->getValue();

                            if ($raw === null || $raw === '') continue;

                            if ($cell->isFormula()) {
                                throw new \RuntimeException(
                                    "Import budget gagal: file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. " .
                                    "Silakan ubah menjadi nilai (Copy → Paste Values)."
                                );
                            }

                            if (is_string($raw) && str_starts_with(ltrim($raw), '=')) {
                                throw new \RuntimeException(
                                    "Import budget gagal: file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. " .
                                    "Silakan Paste Values lalu import ulang."
                                );
                            }
                        }
                    }
                }
            } else {
                // CSV: kalau mau, kamu bisa tentukan perpost dari input form / kolom tertentu.
                // Untuk sekarang, biarkan lewat atau paksa input perpost dari form.
                // throw new \RuntimeException("Untuk CSV, perpost harus diinput manual (belum didukung baca row1).");
            }

            // Import ke temp
            Excel::import(
                new MsBudgetTempImport(
                    $temp_id,
                    $request->cpny_id,
                    $request->business_unit_id,
                    $request->department_fin_id,
                    $username
                ),
                $file
            );

            session(['import_temp_id' => $temp_id]);

            return $budget
                ? redirect()->route('budget.edit', $hash)->with('success', 'Data berhasil di-import (edit mode).')
                : redirect()->route('budget.create')->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }


    public function import_xxx(Request $request, $hash = null)
    {
        $request->validate([
            'file'              => 'required|mimes:xlsx,xls,csv',
            'cpny_id'           => 'required',
            'business_unit_id'  => 'required',
            'department_fin_id' => 'required',
        ]);

        // Jika edit, decode hash -> ambil Budget
        $budget = null;
        if ($hash) {
            $decoded = Hashids::decode($hash);
            $id = $decoded[0] ?? null;
            abort_if(!$id, 404, 'Invalid budget hash.');
            $budget = Budget::findOrFail($id);
        }

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id  = Str::uuid()->toString();

            MsBudgetTemp::where('created_by', $username)->delete();

            // =========================
            // ✅ VALIDASI: TOLAK FORMULA
            // =========================
            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $spreadsheet = IOFactory::load($file->getPathname());

                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $highestRow = $sheet->getHighestDataRow();
                    $highestCol = $sheet->getHighestDataColumn();

                    for ($row = 1; $row <= $highestRow; $row++) {
                        for ($col = 'A'; $col <= $highestCol; $col++) {
                            $cell = $sheet->getCell("{$col}{$row}");
                            $raw  = $cell->getValue();

                            if ($raw === null || $raw === '') continue;

                            // rumus asli excel
                            if ($cell->isFormula()) {
                                throw new \RuntimeException(
                                    "Import budget gagal: file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. " .
                                    "Silakan ubah menjadi nilai (Copy → Paste Values)."
                                );
                            }

                            // jaga-jaga jika tersimpan sebagai string diawali '='
                            if (is_string($raw) && str_starts_with(ltrim($raw), '=')) {
                                throw new \RuntimeException(
                                    "Import budget gagal: file Excel mengandung rumus pada sheet '{$sheet->getTitle()}' cell {$col}{$row}. " .
                                    "Silakan Paste Values lalu import ulang."
                                );
                            }
                        }
                    }
                }
            }

            // Import ke temp
            Excel::import(
                new MsBudgetTempImport(
                    $temp_id,
                    $request->cpny_id,
                    $request->business_unit_id,
                    $request->department_fin_id,
                    $username
                ),
                $file
            );

            session(['import_temp_id' => $temp_id]);

            // redirect
            return $budget
                ? redirect()->route('budget.edit', $hash)
                    ->with('success', 'Data berhasil di-import (edit mode).')
                : redirect()->route('budget.create')
                    ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }


    public function getBusinessUnits($cpny_id)
    {
        $units = BusinessUnit::where('cpny_id', $cpny_id)->get();

        return response()->json($units);
    }

    public function storeBudget(Request $request)
    {
        // $doctype = 'BD';

        // 1) Ambil data temp (header + detail)
        $tempId   = $request->input('temp_id');
        $tempData = MsBudgetTemp::where('temp_budget_id', $tempId)->get();
        $tempHead = $tempData->first();

        if (!$tempHead) {
            return response()->json(['message' => 'Tidak ada data budget import ditemukan!'], 422);
        }

        // // 2) Siapkan helper
        // $user     = $request->user();
        // $username = $user->username ?? 'system';

        // $dt    = Carbon::now();
        // $year  = (int) $dt->year;
        // $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype  = 'BD';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt        = Carbon::now();
        $year      = (int) $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // 3) Pastikan approval lines tersedia (pakai cpny & dept dari temp)
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $tempHead->cpny_id, $tempHead->department_fin_id);

        DB::beginTransaction();
        try {
            // 4) Generate autonumber BDYYMMNNNN (lock)
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year'    => $year,
            //         'month'   => $month,
            //         'status'  => 'A',
            //         'number'  => 1,
            //     ]);
            //     $urutan = 1;
            // } else {
            //     $urutan = $autonbr->number + 1;
            //     $autonbr->update(['number' => $urutan]);
            // }

            // $tglbln = substr($year, 2) . $month; // YYMM
            // $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'BUDGET'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string)$year, 2) . $month;   // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // 5) Buat header Budget
            $totalBudget = (float) $tempData->sum('totalbudget');

            $budget = Budget::create([
                'budget_id'         => $docid,
                'budget_date'       => $dt->toDateString(),
                'perpost'           => $tempHead->perpost,             // YYYY
                'cpny_id'           => $tempHead->cpny_id,
                'business_unit_id'  => $tempHead->business_unit_id,
                'department_fin_id' => $tempHead->department_fin_id,
                'totalbudget'       => $totalBudget,
                'created_by'        => $username,
                'status'            => 'P',
            ]);

            // 6) Buat detail BudgetDetail
            foreach ($tempData as $row) {
                BudgetDetail::create([
                    'budget_id'          => $docid,
                    'perpost'            => $row->perpost,            // YYYY
                    'cpny_id'            => $row->cpny_id,
                    'business_unit_id'   => $row->business_unit_id,
                    'department_fin_id'  => $row->department_fin_id,
                    'account_id'         => $row->account_id,
                    'activity_id'        => $row->activity_id,
                    'activity_descr'     => $row->activity_descr,
                    'activity_detail'    => $row->activity_detail,
                    'qty_budget'         => $row->qty_budget,
                    'unit_price_budget'  => $row->unit_price_budget,
                    'totalbudget'        => $row->totalbudget,

                    'period01_budget'    => $row->period01_budget,
                    'period02_budget'    => $row->period02_budget,
                    'period03_budget'    => $row->period03_budget,
                    'period04_budget'    => $row->period04_budget,
                    'period05_budget'    => $row->period05_budget,
                    'period06_budget'    => $row->period06_budget,
                    'period07_budget'    => $row->period07_budget,
                    'period08_budget'    => $row->period08_budget,
                    'period09_budget'    => $row->period09_budget,
                    'period10_budget'    => $row->period10_budget,
                    'period11_budget'    => $row->period11_budget,
                    'period12_budget'    => $row->period12_budget,

                    // add
                    'period01_budget_add' => 0,
                    'period02_budget_add' => 0,
                    'period03_budget_add' => 0,
                    'period04_budget_add' => 0,
                    'period05_budget_add' => 0,
                    'period06_budget_add' => 0,
                    'period07_budget_add' => 0,
                    'period08_budget_add' => 0,
                    'period09_budget_add' => 0,
                    'period10_budget_add' => 0,
                    'period11_budget_add' => 0,
                    'period12_budget_add' => 0,

                    // reserve
                    'period01_reserve'    => 0,
                    'period02_reserve'    => 0,
                    'period03_reserve'    => 0,
                    'period04_reserve'    => 0,
                    'period05_reserve'    => 0,
                    'period06_reserve'    => 0,
                    'period07_reserve'    => 0,
                    'period08_reserve'    => 0,
                    'period09_reserve'    => 0,
                    'period10_reserve'    => 0,
                    'period11_reserve'    => 0,
                    'period12_reserve'    => 0,

                    // used
                    'period01_used'       => 0,
                    'period02_used'       => 0,
                    'period03_used'       => 0,
                    'period04_used'       => 0,
                    'period05_used'       => 0,
                    'period06_used'       => 0,
                    'period07_used'       => 0,
                    'period08_used'       => 0,
                    'period09_used'       => 0,
                    'period10_used'       => 0,
                    'period11_used'       => 0,
                    'period12_used'       => 0,

                    'created_by'          => $username,
                    'status'              => 'P',
                ]);
            }

            // 7) Hapus temp
            MsBudgetTemp::where('temp_budget_id', $tempId)->delete();

            // 8) Generate TrApproval (cek NOMINAL saja → pakai grand_total = totalbudget)
            $ctx = [
                'ignore_nominal' => false,
                'grand_total'    => (float) $totalBudget,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $budget->budget_id,                 // refnbr
                $doctype,                           // 'BD'
                $tempHead->cpny_id,
                $tempHead->department_fin_id,
                $username,
                $ctx,
                $dt
            );

            // opsional: simpan hint approver pertama (jika kolom ada di budgets)
            if ($firstApprovalUsernames && \Illuminate\Support\Facades\Schema::hasColumn($budget->getTable(), 'completed_by')) {
                $budget->completed_by = $firstApprovalUsernames;
                $budget->completed_at = $dt;
                $budget->save();
            }

            // 9) Attachments (opsional)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $budget->budget_id,
                    'doctype'       => $doctype,
                    'cpnyid'        => $tempHead->cpny_id,
                    'departementid' => $tempHead->department_fin_id,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader     = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create BD',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null;
            }

            // 10) Email approver pertama (status 'P')
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($budget->id);
            $approvalCtl->notifyFirstApprover(
                $budget->budget_id,
                $doctype,
                $budget->status,                     // 'P'
                'Budget',
                url('/showbudgets/' . $eid),
                [
                    'info'      => 'Budget '.$tempHead->cpny_id.' Dept '.$tempHead->department_fin_id.' Perpost '.$tempHead->perpost,
                    'createdby' => $budget->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'budget'  => $budget,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan budget',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    public function editBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $budget = Budget::findOrFail($id);

        $userCpnyIds = Usercpny::where('username', $user->username)
        ->where('status', 'A') // kalau ada kolom status
        ->pluck('cpny_id')
        ->map(fn($x) => strtoupper(trim($x)))
        ->filter()
        ->unique()
        ->values()
        ->toArray();

        // company dropdown hanya dari akses user
        $companies = MsCompany::select('cpny_id','cpny_name')
            ->where('status', 'A')
            ->when(!empty($userCpnyIds), fn($q) => $q->whereIn('cpny_id', $userCpnyIds))
            ->orderBy('cpny_name')
            ->get();

        // $companies     = MsCompany::select('cpny_id', 'cpny_name')
        //                 ->where('status','A')->get();

        // business‑unit untuk company yg sedang diedit
        $businessUnits = BusinessUnit::where('cpny_id', $budget->cpny_id)
                        ->select('business_unit_id','business_unit_name')
                        ->get();

        $departements  = MsDepartment::select('department_id','department_name')->get();

        $budget_detail = BudgetDetail::where('budget_id', $budget->budget_id)
            ->get();
        $temp_id  = session('import_temp_id');
        $tempData = $temp_id ? MsBudgetTemp::where('temp_budget_id', $temp_id)->get() : [];

        $rows = TrAttachment::where('refnbr', $budget->budget_id)
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

        return view('pages.budgets.editbudgets', compact(
            'budget',
            'budget_detail',
            'companies',
            'businessUnits',
            'departements',
            'temp_id',
            'tempData',
            'attachments',
            'hash'
        ));
    }

    public function updateBudget(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'BD tidak ditemukan.');

        $doctype   = 'BD';
        $user      = $request->user();
        $username  = $user->username ?? 'system';
        $now       = Carbon::now();
        $datenow   = $now->toDateString();

        // 1) Ambil header existing
        $budget = Budget::findOrFail($id);

        // 2) Ambil temp (opsional)
        $temp_id  = $request->input('temp_id') ?: session('import_temp_id');
        $tempData = $temp_id ? MsBudgetTemp::where('temp_budget_id', $temp_id)->get() : collect();
        $useTemp  = $tempData->isNotEmpty();
        $tempHead = $useTemp ? $tempData->first() : null;

        // 3) Header meta yg dipakai
        $cpnyId  = $useTemp ? $tempHead->cpny_id           : ($request->input('cpny_id')           ?? $budget->cpny_id);
        $deptId  = $useTemp ? $tempHead->department_fin_id : ($request->input('department_fin_id') ?? $budget->department_fin_id);
        $buId    = $useTemp ? $tempHead->business_unit_id  : ($request->input('business_unit_id')  ?? $budget->business_unit_id);
        $perpost = $useTemp ? $tempHead->perpost           : $budget->perpost; // tetap bila tanpa import

        // 4) Pastikan line approval ada (pakai cpny/dept yang dipakai dokumen)
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyId, $deptId);

        DB::beginTransaction();
        try {
            // 5) Hitung total budget
            $totalBudget = $useTemp
                ? (float) $tempData->sum('totalbudget')
                : (float) BudgetDetail::where('budget_id', $budget->budget_id)->sum('totalbudget');

            // 6) Update header
            $budget->update([
                'budget_date'       => $datenow,
                'perpost'           => $perpost,
                'cpny_id'           => $cpnyId,
                'business_unit_id'  => $buId,
                'department_fin_id' => $deptId,
                'totalbudget'       => $totalBudget,
                'status'            => 'P',
                'updated_by'        => $username,
            ]);

            // 7) Jika ada import → replace detail dengan kolom lengkap (add/reserve/used = 0)
            if ($useTemp) {
                BudgetDetail::where('budget_id', $budget->budget_id)->delete();

                foreach ($tempData as $row) {
                    BudgetDetail::create([
                        'budget_id'          => $budget->budget_id,
                        'perpost'            => $row->perpost,
                        'cpny_id'            => $row->cpny_id,
                        'business_unit_id'   => $row->business_unit_id,
                        'department_fin_id'  => $row->department_fin_id,
                        'account_id'         => $row->account_id,
                        'activity_id'        => $row->activity_id,
                        'activity_descr'     => $row->activity_descr,
                        'activity_detail'    => $row->activity_detail,
                        'qty_budget'         => $row->qty_budget,
                        'unit_price_budget'  => $row->unit_price_budget,
                        'totalbudget'        => $row->totalbudget,

                        'period01_budget'    => $row->period01_budget,
                        'period02_budget'    => $row->period02_budget,
                        'period03_budget'    => $row->period03_budget,
                        'period04_budget'    => $row->period04_budget,
                        'period05_budget'    => $row->period05_budget,
                        'period06_budget'    => $row->period06_budget,
                        'period07_budget'    => $row->period07_budget,
                        'period08_budget'    => $row->period08_budget,
                        'period09_budget'    => $row->period09_budget,
                        'period10_budget'    => $row->period10_budget,
                        'period11_budget'    => $row->period11_budget,
                        'period12_budget'    => $row->period12_budget,

                        // add (default 0)
                        'period01_budget_add' => 0, 'period02_budget_add' => 0, 'period03_budget_add' => 0,
                        'period04_budget_add' => 0, 'period05_budget_add' => 0, 'period06_budget_add' => 0,
                        'period07_budget_add' => 0, 'period08_budget_add' => 0, 'period09_budget_add' => 0,
                        'period10_budget_add' => 0, 'period11_budget_add' => 0, 'period12_budget_add' => 0,

                        // reserve (default 0)
                        'period01_reserve' => 0, 'period02_reserve' => 0, 'period03_reserve' => 0,
                        'period04_reserve' => 0, 'period05_reserve' => 0, 'period06_reserve' => 0,
                        'period07_reserve' => 0, 'period08_reserve' => 0, 'period09_reserve' => 0,
                        'period10_reserve' => 0, 'period11_reserve' => 0, 'period12_reserve' => 0,

                        // used (default 0)
                        'period01_used' => 0, 'period02_used' => 0, 'period03_used' => 0,
                        'period04_used' => 0, 'period05_used' => 0, 'period06_used' => 0,
                        'period07_used' => 0, 'period08_used' => 0, 'period09_used' => 0,
                        'period10_used' => 0, 'period11_used' => 0, 'period12_used' => 0,

                        'created_by'         => $username,
                        'status'             => 'P',
                    ]);
                }

                // hapus temp setelah dipakai
                MsBudgetTemp::where('temp_budget_id', $temp_id)->delete();
            }

            // 8) Generate TrApproval (cek Nominal saja)
            $ctx = [
                'ignore_nominal' => false,
                'grand_total'    => $totalBudget,
            ];
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $budget->budget_id,     // refnbr
                $doctype,               // 'BD'
                $cpnyId,
                $deptId,
                $username,
                $ctx,
                $now
            );

            // opsional: simpan hint approver pertama kalau kolom ada
            if ($firstApprovalUsernames && \Illuminate\Support\Facades\Schema::hasColumn($budget->getTable(), 'completed_by')) {
                $budget->completed_by = $firstApprovalUsernames;
                $budget->completed_at = $now;
                $budget->save();
            }

            // 9) Upload attachments (optional)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $budget->budget_id,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to update BD',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // 10) Notifikasi approver pertama
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($budget->id);
            $approvalCtl->notifyFirstApprover(
                $budget->budget_id,
                $doctype,
                $budget->status, // 'P'
                'Budget',
                url('/showbudgets/' . $eid),
                [
                    'info'      => 'Budget '.$cpnyId.' Dept '.$deptId.' Perpost '.$perpost,
                    'createdby' => $budget->created_by,
                    'date'      => $now->toDateTimeString(),
                ]
            );

            DB::commit();
            return response()->json(['success' => true, 'budget' => $budget]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan budget',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }



    public function showBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // $budget = Budget::findOrFail($id);
        $budget = Budget::with([
            'businessUnit',
            'departmentFin',
            'creator'
        ])->findOrFail($id);


        $budgetdetail = BudgetDetail::where('budget_id', $budget->budget_id)
            ->get();

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload     = $budget->created_by === $loginUsername;


        return view('pages.budgets.showbudgets', compact('budget','budgetdetail','hash','canUpload'));
    }



    public function approveBudget(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BD';

        $budget = Budget::with('creator')->where('budget_id', $docid)->first();
        if (!$budget) return response()->json(['success'=>false,'message'=>'Budget not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($budget->id);
        $docUrl   = url('/showbudgets/' . $eid);
        $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $budget->budget_id,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($budget, $fullname, $docUrl) {
                $budget->status       = 'C';
                $budget->completed_by = $budget->completed_by ?: auth()->user()->username;
                $budget->completed_at = $now;
                $budget->save();

                Budgetdetail::where('budget_id', $budget->budget_id)->update(['status' => 'C']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $budget->budget_id,
                    'Budget',
                    'C',
                    $budget->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $budget->cpny_id ?? $budget->cpnyid ?? '',
                        'deptname' => $budget->department_id ?? $budget->departementid ?? '',
                        'date'     => $budget->budgetdate,
                        'info'     => $budget->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($budget, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $budget->budget_id,
                    'BD',
                    'P',
                    'Budget',
                    $docUrl,
                    [
                        'info'      => $budget->keperluan,
                        'createdby' => $budget->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $budget->completed_by = auth()->user()->username;
                $budget->completed_at = $now;
                $budget->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function rejectBudget(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BD';

        $budget = \App\Models\Budget::with('creator')->where('budget_id', $docid)->first();
        if (!$budget) return response()->json(['success'=>false,'message'=>'Budget not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($budget->id);
        $docUrl   = url('/showbudgets/' . $eid);
        $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $budget->budget_id,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($budget, $fullname, $docUrl) {
                $budget->status       = 'R';
                $budget->completed_by = auth()->user()->username;
                $budget->completed_at = $now;
                $budget->save();

                // optional: tandai detail R
                // \App\Models\Budgetdetail::where('budget_id', $budget->budget_id)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $budget->budget_id,
                    'Budget',
                    'R',
                    $budget->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $budget->cpny_id ?? $budget->cpnyid ?? '',
                        'deptname' => $budget->department_id ?? $budget->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $budget->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($budget->id, 'BD', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Budget rejected successfully']);
    }

    public function reviseBudget(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BD';

        $budget = \App\Models\Budget::with('creator')->where('budget_id', $docid)->first();
        if (!$budget) return response()->json(['success'=>false,'message'=>'Budget not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($budget->id);
        $docUrl   = url('/showbudgets/' . $eid);
        $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $budget->budget_id,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($budget, $fullname, $docUrl) {
                // === HEADER Budget -> D ===
                $budget->status       = 'D';
                $budget->completed_by = auth()->user()->username;
                $budget->completed_at = $now;
                $budget->save();

                // (opsional) DETAIL -> D
                // \App\Models\Budgetdetail::where('budget_id', $budget->budget_id)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $budget->budget_id,
                    'Budget',
                    'D',
                    $budget->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $budget->cpny_id ?? $budget->cpnyid ?? '',
                        'deptname' => $budget->department_id ?? $budget->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $budget->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($budget->id, 'BD', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'Budget revised successfully']);
    }


    // public function approveBudget(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // eager load creator jika ada relasinya
    //     $budget = Budget::with('creator')   // pastikan relasi creator() ada, atau ganti sesuai relasi Anda
    //         ->where('budget_id', $docid)
    //         ->first();

    //     if (!$budget) {
    //         return response()->json(['success' => false, 'message' => 'Budget not found'], 404);
    //     }

    //     // ambil nama lengkap pembuat dokumen (fallback ke created_by)
    //     $fullname = data_get($budget, 'creator.name') ?: ($budget->created_by ?? '');

    //     // pastikan user adalah approver aktif (status P) di doc ini
    //     $tApproval = T_approval::where('docid', $budget->budget_id)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Set current approver -> Approved
    //         $tApproval->status         = 'A';
    //         $tApproval->aprvdateafter  = $now;
    //         $tApproval->aprvusername   = $user->username;
    //         $tApproval->name           = $user->name;
    //         $tApproval->save();

    //         // Update header informasi "terakhir diproses"
    //         $budget->completed_by = $user->username;
    //         $budget->completed_at = $now;
    //         $budget->save();

    //         // Hitung sisa pending setelah approve ini
    //         $pendingCount = T_approval::where('docid', $budget->budget_id)
    //             ->where('status', 'P')
    //             ->count();

    //         // Pemetaan judul sesuai status
    //         $subjectMap = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];

    //         if ($pendingCount === 0) {
    //             // Tidak ada approver lagi -> dokumen complete
    //             $budget->status       = 'C';
    //             $budget->completed_by = $user->username;
    //             $budget->completed_at = $now;
    //             $budget->save();

    //             // Close semua detail
    //             $details = BudgetDetail::where('budget_id', $budget->budget_id)->get();
    //             foreach ($details as $d) {
    //                 $d->status = 'C';
    //                 $d->save();
    //             }

    //             // Email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $eid = Hashids::encode($budget->id);

    //             $data = [
    //                 'docid'     => $budget->budget_id,
    //                 'cpnyid'    => $budget->cpny_id ?? '',
    //                 'deptname'  => $budget->department_fin_id ?? '',
    //                 'date'      => $budget->perpost ?? $now,
    //                 'fullname'  => $fullname,  // nama penerima di email
    //                 'name'      => $fullname,  // fallback
    //                 'createdby' => $fullname,
    //                 'docname'   => 'Budget',
    //                 'info'      => 'Budget Company ' . ($budget->cpny_id ?? '') . ' Department ' . ($budget->department_fin_id ?? '') . ' ' . ($budget->perpost ?? ''),
    //                 'status'    => $status,
    //                 'url'       => url('/showbudgets/' . $eid),
    //             ];

    //             // kirim ke pembuat dokumen
    //             $recipients = User::where('username', $budget->created_by ?? '')
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     $to = $rcp->notification_email ?? $rcp->email;
    //                     if ($to) {
    //                         Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                             $message->to($to)
    //                                 ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Budget')
    //                                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                         });
    //                     }
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending Budget completion email', ['docid' => $budget->budget_id, 'error' => $e->getMessage()]);
    //                 }
    //             }
    //         } else {
    //             // Masih ada approver berikutnya -> cari level berikutnya
    //             $next = T_approval::where('docid', $budget->budget_id)
    //                 ->where('status', 'P')
    //                 ->orderBy('aprvid', 'ASC')
    //                 ->first();

    //             if ($next) {
    //                 // Stempel "datebefore" untuk approver berikutnya
    //                 $next->aprvdatebefore = $now;
    //                 $next->save();

    //                 // Email ke approver berikutnya
    //                 $status        = 'P';
    //                 $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //                 $data = [
    //                     'docid'     => $next->docid,
    //                     'cpnyid'    => $next->aprvcpnyid,
    //                     'deptname'  => $next->aprvdeptid,
    //                     'date'      => $next->aprvdatebefore,
    //                     'fullname'  => $next->name,
    //                     'name'      => $next->name,
    //                     'createdby' => $budget->created_by ?? '',
    //                     'docname'   => 'Budget',
    //                     'info'      => 'Budget Company ' . ($budget->cpny_id ?? '') . ' Department ' . ($budget->department_fin_id ?? '') . ' ' . ($budget->perpost ?? ''),
    //                     'status'    => $status,
    //                     'url'       => url('/showbudgets/' . $budget->id),
    //                 ];

    //                 $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
    //                 if (!empty($usernames)) {
    //                     $recipients = User::whereIn('username', $usernames)
    //                         ->where('status', 'A')
    //                         ->get();

    //                     foreach ($recipients as $rcp) {
    //                         try {
    //                             $to = $rcp->notification_email ?? $rcp->email;
    //                             if ($to) {
    //                                 Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                                     $message->to($to)
    //                                         ->subject($data['docid'] . ' - ' . $subjectSuffix . ' Budget')
    //                                         ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                                 });
    //                             }
    //                         } catch (\Throwable $e) {
    //                             Log::error('Failed sending Budget waiting-approval email', ['docid' => $budget->budget_id, 'error' => $e->getMessage()]);
    //                         }
    //                     }
    //                 } else {
    //                     Log::warning('Next approver has empty aprvusername list', ['docid' => $budget->budget_id]);
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve Budget failed', ['docid' => $budget->budget_id, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }


    // public function rejectBudget(Request $request, $docid)
    // {

    //     // dd($request->all());
    //     $datestamp = Carbon::now()->toDateTimeString();
    //     $user = request()->user(); // Ambil user yang login

    //     // $budget = Budget::where('budget_id', $docid)->first();
    //     $budget = Budget::with('creator')
    //         ->where('budget_id', $docid)
    //         ->first();
    //     $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;


    //     if (!$budget) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }

    //     // Cek apakah user memiliki akses untuk approve
    //     $t_approval = T_approval::where('docid', $budget->budget_id)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%" . $user->username . "%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->first();
    //     // dd($t_approval);
    //     if ($t_approval == null) {
    //         return response()->json(['success' => false, 'message' => "You Can't Rejected!"], 403);
    //     } else {
    //         $t_approval->status = 'R';
    //         $t_approval->aprvdateafter = $datestamp;
    //         $t_approval->save();

    //         $budget->status = 'R';
    //         $budget->save();
    //     }

    //     $t_aprv_sisa = T_approval::where('docid', '=', $budget->budget_id)
    //         ->where('status', '=', 'P')
    //         ->get();

    //     foreach ($t_aprv_sisa as $t_aprv) {
    //         $t_aprv->status = 'X';
    //         $t_aprv->save();
    //     }

    //     $status = 'R'; // Rejected
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //     $eid = Hashids::encode($budget->id);

    //     //send email
    //     $data = array(
    //         'docid' => $t_approval->docid,
    //         'cpnyid' => $t_approval->aprvcpnyid,
    //         'deptname' => $t_approval->aprvdeptid,
    //         'date' => $t_approval->aprvdatebefore,
    //         'fullname'  => $fullname,
    //         'name'      => $fullname,
    //         'createdby' => $fullname,
    //         'docname'   => 'Budget',
    //         'status'    => $status,
    //         'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,
    //         'url' => url('/showbudgets/' . $eid)

    //     );


    //     $email_it = User::where('username', $budget->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //     foreach ($email_it as $emailsit) {
    //         Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {

    //             $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Rejected Budget');
    //             $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //         });
    //     }

    //     $id = $budget->id;
    //     $doctype ='BD';
    //     app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

    //     return response()->json(['success' => true, 'message' => 'Budget rejected successfully']);
    // }

    // public function reviseBudget(Request $request, $docid)
    // {

    //     // dd($request->all());
    //     $datestamp = Carbon::now()->toDateTimeString();
    //     $user = request()->user(); // Ambil user yang login

    //     // $budget = Budget::where('budget_id', $docid)->first();
    //     $budget = Budget::with('creator')
    //         ->where('budget_id', $docid)
    //         ->first();
    //     $fullname = data_get($budget, 'creator.name') ?: $budget->created_by;


    //     if (!$budget) {
    //         return response()->json(['success' => false, 'message' => 'Budget not found'], 404);
    //     }

    //     // Cek apakah user memiliki akses untuk approve
    //     $t_approval = T_approval::where('docid', $budget->budget_id)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%" . $user->username . "%")
    //         ->whereNotNull('aprvdatebefore')
    //         ->first();
    //     // dd($t_approval);
    //     if ($t_approval == null) {
    //         return response()->json(['success' => false, 'message' => "You Can't Revise!"], 403);
    //     } else {
    //         $t_approval->status = 'D';
    //         $t_approval->aprvdateafter = $datestamp;
    //         $t_approval->save();

    //         $budget->status = 'D';
    //         $budget->save();
    //     }

    //     $t_aprv_sisa = T_approval::where('docid', '=', $budget->budget_id)
    //         ->where('status', '=', 'P')
    //         ->get();

    //     foreach ($t_aprv_sisa as $t_aprv) {
    //         $t_aprv->status = 'X';
    //         $t_aprv->save();
    //     }

    //     $status = 'D'; // Revise
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //     $eid = Hashids::encode($budget->id);

    //     //send email
    //     $data = array(
    //         'docid' => $t_approval->docid,
    //         'cpnyid' => $t_approval->aprvcpnyid,
    //         'deptname' => $t_approval->aprvdeptid,
    //         'date' => $t_approval->aprvdatebefore,
    //         'fullname'  => $fullname,
    //         'name'      => $fullname,
    //         'createdby' => $fullname,
    //         'docname'   => 'Budget',
    //         'status'    => $status,
    //         'info' => 'Budget Company ' . $budget->cpny_id . ' Department ' . $budget->department_fin_id . ' ' . $budget->perpost,
    //         'url' => url('/showbudgets/' . $eid)

    //     );


    //     $email_it = User::where('username', $budget->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //     foreach ($email_it as $emailsit) {
    //         Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $emailsit) {

    //             $message->to($emailsit->notification_email)->subject($data['docid'] . ' - Revise Budget');
    //             $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //         });
    //     }

    //     $id = $budget->id;
    //     $doctype ='BD';
    //     app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

    //     return response()->json(['success' => true, 'message' => 'Budget revise successfully']);
    // }

    // public function checkApprovalx($id)
    // {
    //     // Ambil user yang sedang login
    //     $user = Auth::user();

    //     // Cek apakah user login ada di table trx_approval dengan status 'P'
    //     $approval = T_approval::where('docid', $id)
    //         ->where('aprvusername', 'like', '%' . $user->username . '%')
    //         ->where('status', 'P')
    //         ->whereNotNull('aprvdatebefore')
    //         ->exists();

    //     return response()->json(['canReject' => $approval]);


    // }

    // public function checkApproval($id, $action)
    // {
    //     $user = Auth::user(); // Ambil user yang login
    //     // dd($action);
    //     // Query dasar untuk pengecekan
    //     $query = T_approval::where('docid', $id)
    //                 ->where('aprvusername', 'like', '%' . $user->username . '%')
    //                 ->where('status', 'P');

    //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    //     if (in_array($action, ['reject', 'revise','approve'])) {
    //         $query->whereNotNull('aprvdatebefore');
    //     }

    //     // Cek apakah user bisa melakukan aksi
    //     $canPerformAction = $query->exists();

    //     return response()->json(['canPerformAction' => $canPerformAction]);
    // }


    public function getSitesByCompany($cpnyid)
    {
        // $sites = Site::where('cpnyid', $cpnyid)
        //     ->select('id', 'site')
        //     ->get();
        $sites = Site::select('id', 'site')
            ->get();

        return response()->json($sites);
    }

    public function printBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil BDGET + relasi yang dibutuhkan
       $budget = Budget::findOrFail($id);

        // Detail baris BDGET
        $budgetdetail = BudgetDetail::where('budget_id', $budget->budget_id)
            ->get();

        $refnbr    = $budget->budget_id;
        $apprTable = (new TrApproval)->getTable(); // "tr_approval"

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

        // Company (handle null)
        $company = MsCompany::where('cpny_id', $budget->cpny_id)->first();

        // Mapping status dokumen
        switch ($budget->status) {
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
            'title'               => 'Budget Report',
            'doc_type'            => 'BUDGET',
            'docid'               => $budget->budget_id,
            'department_id'       => $budget->department_id,
            'cpnyname'            => $company->cpny_name,
            'perpost'             => $budget->perpost,

            'created_by_username' => $budget->created_by,
            'created_by_name'     => ucwords(strtolower(optional($budget->creator)->name)),
            'created_at_fmt'      => optional($budget->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($budget->created_at)->format('d M Y H:i'),
            'budgetdate'            => \Carbon\Carbon::parse($budget->budgetdate)->format('d F Y'),
            // konten
            'keperluan'           => $budget->perpost,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($budget->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.budgets.pdf_budgets',
            array_merge($data, [
                'detail'         => $budgetdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_budgets_{$budget->budget_id}.pdf");
    }



















}
