<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Dept;
use App\Models\Groups;
use App\Models\Usercpny;
use App\Models\Userdept;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        $company = Company::select('cpnyid')->get();        
        $departement = Dept::select('deptname')->get();
        $groups = Groups::get();
        return view('pages.users.users', compact('company', 'departement','groups'));
    }
  
    public function json()
    {
        $users = User::select(['id', 'name', 'username', 'email','companyid','departmentid','status'])
            ->latest()
            ->get();

        return response()->json(['data' => $users]);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',           
            'email' => 'required',
            'companyid' => 'required',
            'departmentid' => 'required',
        ]);
        DB::beginTransaction();
        try {
            
            $user = Auth::user();
            $company = Company::all();
            $cpnyids = $request->input('companyid');
            $company->appreance = implode(',', $cpnyids);
            if($cpnyids <> null){
                $company->appreance = implode(',', $cpnyids);
            }else{
                $company->appreance = '';
            }

            $dept = Dept::all();
            $deptnames = $request->input('departmentid');
            if($deptnames <> null){
                $dept->appreance = implode(',', $deptnames);
            }else{
                $dept->appreance = '';
            }
           
            $plaintext_password = "pakuwon1234#";
            $password = password_hash($plaintext_password, PASSWORD_DEFAULT);

            $email = $request->email;
            $username = explode('@', $email)[0];

            $users = User::create([
                'name' => strtoupper($request->name),
                'email' => $email,
                'companyid' => $company->appreance,
                'departmentid' => $dept->appreance,
                'username' => $username,
                'password' => $password,
                'groups' => $request->groups,
                'test_email' => $email,
                'jabatan' => $request->jabatan,     
                'role' => $request->role,          
                'npk' => $request->npk,
                'created_user' => $user->username,
                'status' => 'A',
            ]);  

            $usernames = $request->input('username');
          
            //insert usercpny
            foreach ($cpnyids as $cpnyid) {
                $usercpny = new Usercpny();
                $usercpny->username =  $username;
                $usercpny->cpnyid = $cpnyid;
                $usercpny->created_user = $user->username;
                $usercpny->status = 'A';
                $usercpny->save();
                
            }
            foreach ($deptnames as $deptname) {
                $userdept = new Userdept();
                $userdept->username =  $username;
                $userdept->deptname = $deptname;
                $userdept->created_user = $user->username;
                $userdept->status = 'A';
                $userdept->save();
                
            }

            DB::commit();
            return response()->json(['success' => true, 'users' => $users]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan users', 'message' => $e->getMessage()], 500);
        }
        
    }
   
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'npk' => $user->npk,
            'jabatan' => $user->jabatan,
            'groups' => $user->groups,
            'role' => $user->role ?? null,
            'companyid' => explode(',', $user->companyid),
            'departmentid' => explode(',', $user->departmentid),
        ]);
    }


    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',           
            'email' => 'required',
            'companyid' => 'required',
            'departmentid' => 'required',
        ]);
        DB::beginTransaction();
        try {
            
            $user = Auth::user();
            $company = Company::all();
            $cpnyids = $request->input('companyid');
            $company->appreance = implode(',', $cpnyids);
            if($cpnyids <> null){
                $company->appreance = implode(',', $cpnyids);
            }else{
                $company->appreance = '';
            }

            $dept = Dept::all();
            $deptnames = $request->input('departmentid');
            if($deptnames <> null){
                $dept->appreance = implode(',', $deptnames);
            }else{
                $dept->appreance = '';
            }
           
            $plaintext_password = "pakuwon1234#";
            $password = password_hash($plaintext_password, PASSWORD_DEFAULT);

            $email = $request->email;
            $username = explode('@', $email)[0];

            $users = User::findOrFail($id);

            $users -> update([
                'name' => strtoupper($request->name),
                'email' => $email,
                'companyid' => $company->appreance,
                'departmentid' => $dept->appreance,
                'username' => $username,
                'password' => $password,
                'groups' => $request->groups,
                'test_email' => $email,
                'jabatan' => $request->jabatan,     
                'role' => $request->role,          
                'npk' => $request->npk,
                'updated_user' => $user->username,
                'status' => 'A',
            ]);  

            $usernames = $request->input('username');
          
            //insert usercpny
            foreach ($cpnyids as $cpnyid) {
                $usercpny = new Usercpny();
                $usercpny->username =  $username;
                $usercpny->cpnyid = $cpnyid;
                $usercpny->created_user = $user->username;
                $usercpny->status = 'A';
                $usercpny->save();
                
            }
            foreach ($deptnames as $deptname) {
                $userdept = new Userdept();
                $userdept->username =  $username;
                $userdept->deptname = $deptname;
                $userdept->created_user = $user->username;
                $userdept->status = 'A';
                $userdept->save();
                
            }

            DB::commit();
            return response()->json(['success' => true, 'users' => $users]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan users', 'message' => $e->getMessage()], 500);
        }
        
    }

    public function toggleStatus($id)
    {
        $screen = User::findOrFail($id);
        $screen->update(['status' => request('status')]);

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
