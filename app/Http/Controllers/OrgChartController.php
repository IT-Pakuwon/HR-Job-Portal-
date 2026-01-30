<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrSto; 
use Illuminate\Support\Facades\DB;
use App\Models\StoEmployee;
use App\Models\StoDepartement;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\JobLevel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use Mail;
use PDF;


class OrgChartController extends Controller
{
    public function index(Request $request)
    {
        $companies = MsCompany::select('cpny_id')->get();
        $departements = MsDepartment::select('department_id')->get();
        $joblevel = JobLevel::select('title_level')->get();
        // $sto_id = $this->insert_sto_autonbr($request);
        // $sto = TrSto::where('sto_id', $sto_id)->first();
                
        return view('pages.manpowers.orgchart', compact('companies','departements','joblevel'));
    }

    public function jsonxxx()
    {
        $departments = StoDepartement::where('status', 'A')
            ->with(['hr_ms_sto_employee' => function ($query) {
                $query->where('status', 'A');
            }])
            ->get();

        $data = [];

        foreach ($departments as $dept) {    

            $memberList = $dept->hr_ms_sto_employee->map(function ($m) {
                return [
                    'name' => $m->employee_name,
                    'company' => $m->employee_company,
                    'position' => $m->employee_position,
                    'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                ];
            });
        
            $data[] = [
                'id' => $dept->departement_id,
                'parentId' => $dept->parent_id,
                'name' => $dept->departement_name,
                'position' => 'Department',
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];


        }

        return response()->json($data);
    }

    public function json()
    {
        $user = Auth::user();
        if (!$user || !$user->departmentid) {
            return response()->json(['error' => 'Unauthorized or department not found'], 403);
        }

        // $userDept = $user->departmentid;
        $userDept = 'ENGINEERING';


        $root = StoDepartement::where('departement_name', $userDept)
            ->where('status', 'A')
            ->first();

        if (!$root) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        $allDepartments = StoDepartement::where('status', 'A')->get();

        function getDescendants($departments, $parentId) {
            $result = collect();
            foreach ($departments as $dept) {
                if ($dept->parent_id == $parentId) {
                    $result->push($dept);
                    $result = $result->merge(getDescendants($departments, $dept->departement_id));
                }
            }
            return $result;
        }

        $filtered = collect([$root])->merge(getDescendants($allDepartments, $root->departement_id));
    
        $data = [];

        foreach ($filtered as $dept) {
            $memberList = $dept->hr_ms_sto_employee->map(function ($m) {
                return [
                    'name' => $m->employee_name,
                    'company' => $m->employee_company,
                    'position' => $m->employee_position,
                    'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
                ];
            });

            $data[] = [
                'id' => $dept->departement_id,
                'parentId' => $dept->parent_id,
                'name' => $dept->departement_name,
                'position' => 'Department',
                'members' => $memberList->toArray(),
                'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        }

        return response()->json($data);
    }



    public function store(Request $request)
    {
        // dd($request->all());
        // Tentukan apakah ini request untuk Employee atau Departement berdasarkan field yang dikirim
       if ($request->has('full_name')) {
            $validator = Validator::make($request->all(), [
                'approval_line' => 'required|integer',
                'full_name' => 'required|string|max:100',
                'job_position' => 'required|string|max:100',
                'cpnyid' => 'required|string',
                'qty' => 'nullable|integer|min:1' // tambahkan validasi qty jika dikirim
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $qty = $request->input('qty', 1); // default = 1 jika tidak ada

            for ($i = 0; $i < $qty; $i++) {
                $employee = new StoEmployee();
                $employee->departement_id = $request->approval_line;
                $employee->employee_name = $request->full_name;
                $employee->employee_position = $request->job_position;
                $employee->employee_company = $request->cpnyid;           
                $employee->status = 'A';
                $employee->save();
            }

            return response()->json([
                'success' => true,
                'type' => 'employee',
                'message' => $qty . ' employee(s) saved'
            ]);

        } elseif ($request->has('departement_name')) {
            // === Menyimpan Departement ===
            $validator = Validator::make($request->all(), [
                'departement_name' => 'required|string|max:100',
                'parent_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // ✅ Cek ke tabel master Dept (bukan StoDepartement)
            $existingDept = MsDepartment::whereRaw('LOWER(department_id) = ?', [strtolower($request->departement_name)])
                ->first();

            if (!$existingDept) {
                // ✅ Jika tidak ditemukan, insert ke Dept
                $newDept = new MsDepartment();
                $newDept->deptname = $request->departement_name;
                $newDept->status = 'A'; // jika ada field status
                $newDept->save();
            }

            $departement = new StoDepartement();
            $departement->departement_name = $request->departement_name;
            $departement->parent_id = $request->approval_line ?? null;
            $departement->status = 'A';
            $departement->save();
            
            // Update departement_id dengan ID yang baru saja dibuat
            $departement->departement_id = $departement->id;
            $departement->save();

            return response()->json(['success' => true, 'type' => 'departement']);
        }

        return response()->json(['error' => 'Invalid request data'], 400);
    }

    public function getEmployeesByDept($dept_id)
    {
        $employees = StoEmployee::where('departement_id', $dept_id)
            ->where('status', 'A')
            ->get(['employee_name', 'employee_company', 'employee_position', 'image']);

        return response()->json($employees);
    }

    public function insert_sto_autonbr($request)
    {
        DB::beginTransaction();
        try {
            $doctype = 'STO';
            $datenow = Carbon::now()->format('Y-m-d');
            $now = Carbon::now();
            $year = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $user = Auth::user();

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

            $sto = new TrSto();
            $sto->sto_id = $docid;
            $sto->sto_date = $datenow;
            $sto->user = $user->username;
            $sto->created_user = $user->username;
            $sto->status = 'H';
            $sto->save();

            DB::commit();
            return $docid; // <--- ini kuncinya
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }


    public function storeSubmit(Request $request)
    {
        $isDraft = $request->input('action') === 'draft';

        DB::beginTransaction();
        try {
            $doctype = 'STO';
            $datenow = Carbon::now()->format('Y-m-d');
            $now = Carbon::now();
            $year = $now->year;
            $month = str_pad($now->month, 2, '0', STR_PAD_LEFT);

           
            // Ambil atau buat nomor urut dokumen
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%05d", $urutan);

            $sto = new TrSto();
            $sto->sto_id = $request->sto_id;
            $sto->sto_date = $request->sto_date;
            $sto->user = Auth::user()->name ?? 'System';
            $sto->status = $isDraft ? 'Draft' : 'Submitted';

            $sto->save();

                DB::commit();
                return response()->json(['success' => true]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Gagal menyimpan Transaksi Payroll',
                    'message' => $e->getMessage()
                ], 500);
            }


        $sto = new TrSto();
        $sto->sto_id = $request->sto_id;
        $sto->sto_date = $request->sto_date;
        $sto->user = Auth::user()->name ?? 'System';
        $sto->status = $isDraft ? 'Draft' : 'Submitted';

        $sto->save();

        return response()->json([
            'success' => true,
            'message' => $isDraft ? 'Draft berhasil disimpan.' : 'Data berhasil disubmit.',
        ]);
    }




}
