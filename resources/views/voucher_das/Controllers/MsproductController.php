<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\Msproduct;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Company;
use App\Models\Category;
use App\Models\Mswhs;
use App\Models\Autonbr;
use App\Models\Msproductdetail;
use App\Models\Attachment;
use App\Models\Usercpny;
use App\Models\MsAging;
use App\Models\Viewproducttargetdate;

use DataTables;

class MsproductController extends Controller
{      
     
    public function msproduct(Request $request)
    {        
        
        $title = 'Master Stock';
        $user = Auth::user();       
        // $company = Company::all();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();  
        $category = Category::where('doctype','VPL')
            ->where('status','A')
            ->get();
        $multicpnyid = explode(',', $user->companyid);
        
        
        if ($request->ajax()) {   
             
            if ($user->role == 'admin') {
                $data = Msproduct::all();
            }else{
                $data = Msproduct::whereIn('cpnyid', $multicpnyid)                                     
                    ->get();  
            }
            
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
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-sm editMsproduct" style="background-color:#FFCD05; color:white">Edit</a>';                   
                    $btn = $btn.' <a href="/viewproduct_'.$row->id.'" target="_blank" data-toggle="tooltip" data-original-title="View" class="btn btn-primary btn-sm">View Detail</a>';

                    return $btn;
                })
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('msproduct.msproduct', compact('title','usercpny','usercpny2','category'));
        
    }

    public function edit_product($id)
    {
        $msproduct = Msproduct::find($id);      
          
        // return response()->json($msproduct);
        return response()->json([
            'msproduct' => $msproduct            
        ]);
    }

    public function save_product(Request $request)
    {
        // dd($request->all());          
        $key_id = $request->key_id;
        $msproduct = Msproduct::find($key_id);    
        $user = Auth::user(); 
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;

        // if ($request->product_type == 'P') {
        //     $vp_type = 'Product';
        // } else if ($request->product_type == 'V') {
        //     $vp_type = 'Voucher';
        // } else {
        //     $vp_type = 'Voucher';
        // }
        
        $autonbr = Autonbr::where('doctype', $request->product_type)    
            ->where('year',$request->cpnyid)           
            ->where('status', 'A')
            ->first();
        $pv_code = $autonbr->doctype.$autonbr->month;
        
        if ($autonbr->number == 0) {
            $urutan = 1;                
            $product_id = $pv_code . '000' . $urutan;
        } else {
            $urutan = $autonbr->number;
            $urutan++;                
            $product_id = $pv_code . sprintf("%04s", $urutan);
        }
        
        //update ms_autonbr
        $autonbr->number = $urutan;
        $autonbr->save();

        // dd($product_id);
        if($key_id <> null){     
            $msproduct->cpnyid =$request->cpnyid; 
            $msproduct->product_name =$request->product_name;
            $msproduct->product_type =$request->product_type;  
            $msproduct->product_category =$request->product_category; 
            $msproduct->product_source_type =$request->product_source_type; 
            $msproduct->product_source_company =strtoupper($request->product_source_company);  
            $msproduct->product_source_tenant =strtoupper($request->product_source_tenant); 
            $msproduct->product_remark =$request->product_remark;  
            $msproduct->product_value =$request->product_value; 
            $msproduct->product_uom = strtoupper($request->product_uom); 
            $msproduct->product_check_exp = $request->product_check_exp;
            $msproduct->status = $request->status;
            $msproduct->updated_user = $user->username;
            $msproduct->updated_at = $datestamp;
            $msproduct->save();            
           
        }else{
            $msproduct = new Msproduct();
            $msproduct->product_id =$product_id;  
            $msproduct->cpnyid =$request->cpnyid; 
            $msproduct->product_name =$request->product_name;
            $msproduct->product_type =$request->product_type;  
            $msproduct->product_category =$request->product_category; 
            $msproduct->product_source_type =$request->product_source_type; 
            $msproduct->product_source_company =strtoupper($request->product_source_company);  
            $msproduct->product_source_tenant =strtoupper($request->product_source_tenant); 
            $msproduct->product_remark =$request->product_remark;  
            $msproduct->product_value =$request->product_value; 
            $msproduct->product_uom = strtoupper($request->product_uom); 
            $msproduct->product_check_exp = $request->product_check_exp;
            $msproduct->status = 'A';
            $msproduct->created_user = $user->username;
            $msproduct->created_at = $datestamp;
            $msproduct->save();            
            
        }      
        return response()->json(['success'=>'Message Save Successfully.']);       
        
    }

    public function viewproduct($id)
    {
        $msproduct = Msproduct::find($id);    
        $user = Auth::user(); 
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;

        $msproductdetail = Msproductdetail::where('product_id', $msproduct->product_id)               
            ->where('status', 'A')
            ->orderBy('cpnyid', 'asc')
            ->orderBy('product_id', 'asc')
            ->orderBy('expired_date', 'asc')
            ->orderBy('whs_id', 'asc')
            ->get();

        $attachment = Attachment::where('docid',$msproduct->product_id)    
            ->get();

        $mswhs = Mswhs::all();

        return view('msproduct.viewproduct', compact('msproduct','msproductdetail','mswhs','attachment'));
    }

    public function saveProductDetail(Request $request)
    {
        $product_id = $request->input('product_id'); // Get product ID from hidden input

        foreach ($request->addmore as $key => $value) {
            Msproductdetail::create([
                'product_id' => $product_id,
                'qty_available' => $value['qty'],
                'expired_date' => $value['expired_date'],
                'whs_id' => $value['source_whs'],
                'status' => 'A',
                'created_user' => Auth::user()->username,
                'created_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => 'Product details added successfully.']);
    }

    public function saveProductAttach(Request $request)
    {
        $dt = Carbon::now();
        $user = Auth::user(); 
        $year = $dt->year;
        $product_id = $request->input('product_id'); // Get product ID from hidden input

        if ($request->hasfile('attachment')) {

            foreach ($request->file('attachment') as $file) {

                $randomNumber = random_int(100000, 999999);

                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $attachfile = $randomNumber . '-' . $file->getClientOriginalName();

                //attach to folder                    
                $folder_attach = public_path() . '/attachment/'.$year;
                $config['upload_path'] = $folder_attach;                   
                if(!is_dir($folder_attach))
                {
                    mkdir($folder_attach, 0777);
                }
                // $folder_upload = public_path() . '/attachment';
                $folder_upload = $folder_attach;
                $file->move($folder_upload, $attachfile);

                //insert to table attachment
                $attach = new Attachment();
                $attach->docid = $product_id;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->name;
                $attach->save();
            }
        }

        return response()->json(['success' => 'Attachment added successfully.']);
    }

    public function getCategoryproduct(Request $request)
    {
        $type = $request->input('type'); // Ambil nilai product_typex dari frontend

        if ($type === 'V') {
            $categories = Category::where('type', 'V')->get(); // Jika Voucher, ambil kategori A
        } else if ($type === 'P') {
            $categories = Category::where('type', 'P')->get(); // Jika Product, ambil kategori B
        } else {
            return response()->json(['error' => 'Invalid category type'], 400);
        }

        return response()->json($categories);
    }

    public function producttarget(Request $request)
    {        
        
        $title = 'Master Stock Target Date';
        $user = Auth::user();       
        // $company = Company::all();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();  
        $category = Category::where('doctype','VPL')
            ->where('status','A')
            ->get();
        $multicpnyid = explode(',', $user->companyid);
        
        
        if ($request->ajax()) {   
             
            if ($user->role == 'admin') {
                $data = Viewproducttargetdate::all();
            }else{
                $data = Viewproducttargetdate::whereIn('cpnyid', $multicpnyid)                                     
                    ->get();  
            }
            
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
                    $btn = '<a href="javascript:void(0)" data-id="'.$row->product_id.'" class="btn btn-sm btn-primary detailBtn">Detail</a>';                   
                    return $btn;
                })
                
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('msproduct.producttarget', compact('title','usercpny','usercpny2','category'));
        
    }

    public function getProductDetails($product_id)
    {
        $details = Msproductdetail::where('product_id', $product_id)->get(['product_id', 'expired_date', 'target_date','cpnyid','whs_id','qty_available']);
        return response()->json($details);
    }


    public function updateTargetDate(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([          
            'target_date' => 'required|date',
        ]);

        $updated = Msproductdetail::where('product_id', $request->product_id)
                    ->update(['target_date' => $request->target_date]);

        return response()->json(['message' => 'Target date updated']);
    }

    public function setupaging(Request $request)
    {        
        
        $title = 'Setup Aging';
        $user = Auth::user();       
        // $company = Company::all();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();  
        $category = Category::where('doctype','VPL')
            ->where('status','A')
            ->get();
        $multicpnyid = explode(',', $user->companyid);
        
        
        if ($request->ajax()) {   
             
            // if ($user->role == 'admin') {
                $data = MsAging::all();
            // }else{
            //     $data = MsAging::whereIn('cpnyid', $multicpnyid)                                     
            //         ->get();  
            // }
            
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
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-sm editMsproduct" style="background-color:#FFCD05; color:white">Edit</a>';                   
                    
                    return $btn;
                })
                
                ->rawColumns(['status','action'])                                           
                ->make(true);
        }
        
        return view('msproduct.setupaging', compact('title','usercpny','usercpny2','category'));
        
    }

    public function edit_aging($id)
    {
        $setupaging = MsAging::find($id);      
          
        // return response()->json($setupaging);
        return response()->json([
            'setupaging' => $setupaging            
        ]);
    }

    public function save_aging(Request $request)
    {
        // dd($request->all());          
        $key_id = $request->key_id;
        $msaging = MsAging::find($key_id);    
        $user = Auth::user(); 
        $datestamp = Carbon::now()->toDateTimeString();
   
        // dd($product_id);
        if($key_id <> null){     
            $msaging->age_descr =$request->age_descr; 
            $msaging->start_age =$request->start_age;
            $msaging->end_age =$request->end_age;  
            $msaging->order_age =$request->order_age;           
            $msaging->status = $request->status;
            $msaging->updated_user = $user->username;
            $msaging->updated_at = $datestamp;
            $msaging->save();            
           
        }else{
            $msaging = new MsAging();              
            $msaging->age_descr =$request->age_descr; 
            $msaging->start_age =$request->start_age;
            $msaging->end_age =$request->end_age;  
            $msaging->order_age =$request->order_age;   
            $msaging->status = 'A';
            $msaging->created_user = $user->username;
            $msaging->created_at = $datestamp;
            $msaging->save();            
            
        }      
        return response()->json(['success'=>'Message Save Successfully.']);       
        
    }
    
}
