<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserEng;
use App\Models\CompanyEng;
use App\Models\DepartmentEng;
use App\Models\DepartmentUserEng;
use App\Models\CompanyEngRole;
use App\Models\PositionEng;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class UsersEngController extends Controller
{
    public function index()
    {
        $company = CompanyEng::select('Company_id','Company_name')->get();            
        // $departement = DepartmentEng::select('id','Department_name')->get();
        $departement = DepartmentEng::with('company')->select('id','Department_name','company_id')->get();
        $position = PositionEng::get();
        return view('engineering.users.users', compact('company', 'departement','position'));
    }
  
   
    public function json()
    {
        $users = UserEng::with(['departments', 'position']) // <-- tambahkan relasi posisi
            ->select(['id', 'name', 'username', 'position_id', 'active_status'])
            ->latest()
            ->get();

        $usersFormatted = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'position_id' => $user->position->position_name ?? '-', // <- ambil nama posisi
                'active_status' => $user->active_status,
                'department' => $user->departments->pluck('Department_name')->implode(', '),
            ];
        });

        return response()->json(['data' => $usersFormatted]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            // 'username' => 'required|string|unique:mysql4.users,username',
            'email'    => 'required|email|unique:mysql4.users,email',
            'password'      => 'nullable|string|min:6',
            'companyid'     => 'required|array',
            'departmentid'  => 'required|array',
            'position_id'   => 'required|integer',
            'npk'           => 'nullable|string',
            // 'email'         => 'required|email|unique:user_engs,email',
        ]);

        DB::beginTransaction();
        try {
            // Simpan User
            $user = new UserEng();
            $user->username     = $request->username;
            $user->name         = $request->name;
            $user->email        = $request->email;
            $user->npk          = $request->npk;
            $user->position_id  = $request->position_id;
            $user->Attendance_Type = 'GPS';
            $user->warranty_alarm = 'off';
            $user->user_img = 'people.png';
            $user->active_status = '1';
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $lastUpdatedBy = auth()->user()->id ?? '';

            // Simpan ke companyrole
            foreach ($request->companyid as $companyId) {
                CompanyEngRole::create([
                    'user_id'        => $user->id,
                    'company_id'     => $companyId,
                    'active_status'  => '1',
                    'Last_update_By' => $lastUpdatedBy,
                ]);
            }

            // Simpan ke department_user
            foreach ($request->departmentid as $deptId) {
                foreach ($request->companyid as $companyId) {
                    DepartmentUserEng::create([
                        'user_id'        => $user->id,
                        'department_id'  => $deptId,
                        'company_id'     => $companyId,
                        'Last_update_By' => $lastUpdatedBy,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'User created successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
    public function edit($id)
    {
        $user = UserEng::with(['departments', 'companyroles'])->findOrFail($id);

        return response()->json([
            'id'           => $user->id,
            'name'         => $user->name,
            'username'     => $user->username,
            'email'        => $user->email,
            'npk'          => $user->NPK,
            'position_id'  => $user->position_id,
            'companyid'    => $user->companyroles->pluck('company_id'),     // array of selected companies
            'departmentid' => $user->departments->pluck('id'),              // array of selected departments
        ]);
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'username'      => 'required|string|unique:mysql4.users,username,' . $id,
            'email'         => 'required|email|unique:mysql4.users,email,' . $id,
            'name'          => 'required|string',
            'position_id'   => 'required|integer',
            'companyid'     => 'required|array',
            'departmentid'  => 'required|array',
            'npk'           => 'nullable|string',
            'password'      => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $user = UserEng::findOrFail($id);

            // Update data user
            $user->username     = $request->username;
            $user->name         = $request->name;
            $user->email        = $request->email;
            $user->NPK          = $request->npk;
            $user->position_id  = $request->position_id;
            $user->Attendance_Type = 'GPS'; // default, ubah jika dinamis
            $user->warranty_alarm  = 'off';
            $user->user_img        = 'people.png';
            $user->active_status   = '1';

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $lastUpdatedBy = auth()->user()->id ?? '';

            // 🔁 Sync companyrole: hapus semua lalu insert ulang
            CompanyEngRole::where('user_id', $user->id)->delete();
            foreach ($request->companyid as $companyId) {
                CompanyEngRole::create([
                    'user_id'        => $user->id,
                    'company_id'     => $companyId,
                    'active_status'  => '1',
                    'Last_update_By' => $lastUpdatedBy,
                ]);
            }

            // 🔁 Sync department_user: hapus semua lalu insert ulang
            DepartmentUserEng::where('user_id', $user->id)->delete();
            foreach ($request->departmentid as $deptId) {
                foreach ($request->companyid as $companyId) {
                    DepartmentUserEng::create([
                        'user_id'        => $user->id,
                        'department_id'  => $deptId,
                        'company_id'     => $companyId,
                        'Last_update_By' => $lastUpdatedBy,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'User updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        $screen = UserEng::findOrFail($id);
        $screen->update(['active_status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json(['message' => 'Current password does not match'], 422);
        }

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

}
