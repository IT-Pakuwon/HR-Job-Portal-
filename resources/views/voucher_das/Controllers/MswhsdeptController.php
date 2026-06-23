<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\Mswhsdept;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use DataTables;
use App\Models\Company;
use App\Models\dept;

class MswhsdeptController extends Controller
{      
    
    public function mswhsdept(Request $request)
    {        
        $tittle = 'Warehouse Dept';
        $user = Auth::user();       
        $company = Company::all();
        $dept = Dept::all();

        if ($request->ajax()) {                          
             
            $data = Mswhsdept::all(); 
            
            return Datatables::of($data)
                ->addIndexColumn()                                
                ->addColumn('status', function($row){                                     
                    if ($row->status == 'A') {                                        
                        $btn = '<a href="javascript:void(0)" class="label label-success">Active</a>';                                  
                    }else{                                      
                        $btn = '<a href="javascript:void(0)" class="label label-danger">In Active</a>';
                    }      
                    return $btn;
                })
                ->addColumn('action', function($row){   
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-sm editMswhsdept" style="background-color:#FFCD05; color:white">Edit</a>';
                    // $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteLimit">Delete</a>';
                    return $btn;
                })
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('mswhsdept.mswhsdept', compact('tittle','company','dept'));
        
    }

    public function edit_whsdept($id)
    {
        $mswhsdept = Mswhsdept::find($id);
        return response()->json($mswhsdept);
    }

    public function save_whsdept(Request $request)
    {
        // dd($request->all());
        $key_id = $request->key_id;
        $mswhsdept = Mswhsdept::find($key_id);    
        $user = Auth::user(); 
        $datestamp = Carbon::now()->toDateTimeString();
       
        if($key_id <> null){              
            $mswhsdept->whs_id =$request->whs_id;  
            $mswhsdept->cpnyid =$request->cpnyid; 
            $mswhsdept->department_id =$request->dept;          
            $mswhsdept->whs_type =$request->whs_type; 
            $mswhsdept->status =  $request->status;
            $mswhsdept->updated_user = $user->username;
            $mswhsdept->updated_at = $datestamp;
            $mswhsdept->save();
        }else{
            $mswhsdept = new Mswhsdept();
            $mswhsdept->whs_id =$request->whs_id;  
            $mswhsdept->cpnyid =$request->cpnyid; 
            $mswhsdept->department_id =$request->dept;          
            $mswhsdept->whs_type =$request->whs_type; 
            $mswhsdept->status = $request->status;
            $mswhsdept->created_user = $user->username;
            $mswhsdept->created_at = $datestamp;
            $mswhsdept->save();
        }      
        return response()->json(['success'=>'Message Save Successfully.']);       
        
    }
    
}
