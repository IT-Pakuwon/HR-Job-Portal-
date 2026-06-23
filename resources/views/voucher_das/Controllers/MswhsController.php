<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\Mswhs;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Company;
use DataTables;

class MswhsController extends Controller
{      
     
    public function mswhs(Request $request)
    {        
        $tittle = 'Warehouse';
        $user = Auth::user();       
        $company = Company::all();

        if ($request->ajax()) {   
                       
            // $data = Mswhs::where('status', 'A')                                       
            //     ->get();    
            $data = Mswhs::all(); 
            
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
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-sm editMswhs" style="background-color:#FFCD05; color:white">Edit</a>';
                    // $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteLimit">Delete</a>';
                    return $btn;
                })
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('mswhs.mswhs', compact('tittle','company'));
        
    }

    public function edit_whs($id)
    {
        $mswhs = Mswhs::find($id);
        return response()->json($mswhs);
    }

    public function save_whs(Request $request)
    {
        // dd($request->all());
        $key_id = $request->key_id;
        $mswhs = Mswhs::find($key_id);    
        $user = Auth::user(); 
        $datestamp = Carbon::now()->toDateTimeString();
       
        if($key_id <> null){              
            $mswhs->whs_id =$request->whs_id;  
            $mswhs->cpnyid =$request->cpnyid; 
            $mswhs->whs_name =$request->whs_name;          
            $mswhs->whs_type =$request->whs_type; 
            $mswhs->status =  $request->status;
            $mswhs->updated_user = $user->username;
            $mswhs->updated_at = $datestamp;
            $mswhs->save();
        }else{
            $mswhs = new Mswhs();
            $mswhs->whs_id =$request->whs_id;  
            $mswhs->cpnyid =$request->cpnyid; 
            $mswhs->whs_name =$request->whs_name;          
            $mswhs->whs_type =$request->whs_type; 
            $mswhs->status = $request->status;
            $mswhs->created_user = $user->username;
            $mswhs->created_at = $datestamp;
            $mswhs->save();
        }      
        return response()->json(['success'=>'Message Save Successfully.']);       
        
    }
    
}
