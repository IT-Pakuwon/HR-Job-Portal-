<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\Mssource;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Company;
use DataTables;

class MssourceController extends Controller
{      
     
    public function mssource(Request $request)
    {        
        $tittle = 'Warehouse';
        $user = Auth::user();       
        $company = Company::all();

        if ($request->ajax()) {   
                       
            // $data = Mssource::where('status', 'A')                                       
            //     ->get();    
            $data = Mssource::all(); 
            
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
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-sm editMssource" style="background-color:#FFCD05; color:white">Edit</a>';
                    // $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteLimit">Delete</a>';
                    return $btn;
                })
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('mssource.mssource', compact('tittle','company'));
        
    }

    public function edit_source($id)
    {
        $mssource = Mssource::find($id);
        return response()->json($mssource);
    }

    public function save_source(Request $request)
    {
        // dd($request->all());
        $key_id = $request->key_id;
        $mssource = Mssource::find($key_id);    
        $user = Auth::user(); 
        $datestamp = Carbon::now()->toDateTimeString();
       
        if($key_id <> null){              
            $mssource->source_receive_id =$request->source_receive_id;  
            $mssource->cpnyid =$request->cpnyid; 
            $mssource->source_receive_name =$request->source_receive_name;   
            $mssource->status =  $request->status;
            $mssource->updated_user = $user->username;
            $mssource->updated_at = $datestamp;
            $mssource->save();
        }else{
            $mssource = new Mssource();
            $mssource->source_receive_id =$request->source_receive_id;  
            $mssource->cpnyid =$request->cpnyid; 
            $mssource->source_receive_name =$request->source_receive_name; 
            $mssource->status = $request->status;
            $mssource->created_user = $user->username;
            $mssource->created_at = $datestamp;
            $mssource->save();
        }      
        return response()->json(['success'=>'Message Save Successfully.']);       
        
    }
    
}
